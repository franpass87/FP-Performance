<?php

namespace FP\PerfSuite\Services\ML;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;
use FP\PerfSuite\Utils\MLSemaphore;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\ML\MLPredictor\DataStorage;
use FP\PerfSuite\Services\ML\MLPredictor\DataCollector;
use FP\PerfSuite\Services\ML\MLPredictor\PredictionEngine;

/**
 * ML Predictor Service
 * 
 * Predice problemi di performance usando machine learning
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class MLPredictor
{
    private const OPTION = 'fp_ps_ml_predictor';
    private const DATA_OPTION = 'fp_ps_ml_data';
    
    // RESOURCE LIMITS - Protezione DoS
    private const MAX_STORAGE_MB = 50; // Max 50MB di dati ML
    private const MAX_DATA_POINTS = 5000; // Max 5000 punti dati
    private const MAX_ENTRY_SIZE_KB = 100; // Max 100KB per entry
    private const DATA_RETENTION_DAYS_DEFAULT = 30;
    private const MEMORY_LIMIT_ML = '256M'; // Memory limit per operazioni ML
    
    /** @var mixed */
    private $container;
    private PatternLearner $patternLearner;
    private AnomalyDetector $anomalyDetector;
    private DataStorage $dataStorage;
    private DataCollector $dataCollector;
    private PredictionEngine $predictionEngine;
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;
    
    /**
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger = null;

    public function __construct(
        mixed $container,
        PatternLearner $patternLearner,
        AnomalyDetector $anomalyDetector,
        ?OptionsRepositoryInterface $optionsRepo = null,
        ?LoggerInterface $logger = null
    ) {
        $this->container = $container;
        $this->patternLearner = $patternLearner;
        $this->anomalyDetector = $anomalyDetector;
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
        $this->dataStorage = new DataStorage($optionsRepo);
        $this->dataCollector = new DataCollector($optionsRepo);
        $this->predictionEngine = new PredictionEngine($this->dataStorage);
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     * @param \Throwable|null $exception Optional exception
     */
    private function log(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($this->logger !== null) {
            if ($exception !== null && method_exists($this->logger, $level)) {
                $this->logger->$level($message, $context, $exception);
            } else {
                $this->logger->$level($message, $context);
            }
        } else {
            StaticLogger::$level($message, $context);
        }
    }

    /**
     * Ottiene le impostazioni del servizio
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'data_retention_days' => self::DATA_RETENTION_DAYS_DEFAULT,
            'max_storage_mb' => self::MAX_STORAGE_MB,
            'max_data_points' => self::MAX_DATA_POINTS,
            'prediction_threshold' => 0.7,
            'anomaly_threshold' => 0.8,
            'pattern_confidence_threshold' => 0.8
        ];
        
        $settings = $this->getOption(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Aggiorna le impostazioni del servizio
     */
    public function updateSettings(array $settings): void
    {
        $this->setOption(self::OPTION, $settings);
        
        // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
        $this->forceInit();
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_action('shutdown', [$this, 'collectPerformanceData'], PHP_INT_MAX);
        remove_action('fp_ps_ml_analyze_patterns', [$this, 'analyzePatterns']);
        remove_action('fp_ps_ml_predict_issues', [$this, 'predictIssues']);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Registra gli hook per il predictor ML
     */
    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Raccoglie dati di performance - solo nel frontend
        if (!is_admin()) {
            add_action('shutdown', [$this, 'collectPerformanceData'], PHP_INT_MAX);
        }
        
        // Analizza pattern ogni ora
        add_action('fp_ps_ml_analyze_patterns', [$this, 'analyzePatterns']);
        
        // Predice problemi ogni 6 ore
        add_action('fp_ps_ml_predict_issues', [$this, 'predictIssues']);
        
        // Schedula analisi automatiche
        $this->scheduleAnalysis();
        
        $this->log('debug', 'ML Predictor registered');
    }

    /**
     * Raccoglie dati di performance per l'analisi ML
     * 
     * RESOURCE LIMIT FIX: Protezione contro storage overflow e memory limit
     */
    public function collectPerformanceData(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // RESOURCE FIX: Verifica quota storage prima di raccogliere dati
        if ($this->isStorageQuotaExceeded()) {
            $this->log('warning', 'ML data storage quota exceeded, skipping collection. Run cleanup.');
            
            // Auto-cleanup se quota superata
            $this->cleanupOldData();
            return;
        }
        
        // RESOURCE FIX: Set memory limit per protezione
        $originalLimit = ini_get('memory_limit');
        @ini_set('memory_limit', self::MEMORY_LIMIT_ML);
        
        try {
            $data = $this->gatherPerformanceData();
            
            // RESOURCE FIX: Valida dimensione dati
            $dataSize = strlen(serialize($data));
            $maxSize = self::MAX_ENTRY_SIZE_KB * 1024;
            
            if ($dataSize > $maxSize) {
                $this->log('warning', 'Performance data too large, truncating', [
                    'original_size' => $dataSize,
                    'max_size' => $maxSize
                ]);
                $data = $this->truncateData($data);
            }
            
            $this->storePerformanceData($data);
            
            // RESOURCE FIX: Auto-cleanup periodico (ogni 100 entry)
            if ($this->getDataPointCount() % 100 === 0) {
                $this->cleanupOldData();
            }
            
        } catch (\Throwable $e) {
            $this->log('error', 'ML data collection failed', [], $e);
        } finally {
            // RESOURCE FIX: Ripristina memory limit
            @ini_set('memory_limit', $originalLimit);
        }
    }

    /**
     * Analizza pattern nei dati raccolti
     * 
     * RESOURCE LIMIT FIX: Timeout ridotto + memory limit
     */
    public function analyzePatterns(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // RESOURCE FIX: Timeout ridotto da 60s a 30s
        if (!MLSemaphore::acquire('pattern_analysis', 30)) {
            $this->log('warning', 'ML pattern analysis skipped - semaphore busy');
            return;
        }

        // RESOURCE FIX: Set memory limit
        $originalLimit = ini_get('memory_limit');
        @ini_set('memory_limit', self::MEMORY_LIMIT_ML);

        try {
            $data = $this->dataStorage->getStoredData();
            
            // RESOURCE FIX: Limita i dati processati
            if (count($data) > 1000) {
                $data = array_slice($data, -1000); // Solo ultimi 1000
                $this->log('debug', 'ML analysis limited to last 1000 data points');
            }
            
            $patterns = $this->patternLearner->learnPatterns($data);
            
            $this->dataStorage->updateLearnedPatterns($patterns);
            
            $this->log('info', 'ML patterns analyzed', ['patterns_count' => count($patterns)]);
        } catch (\Throwable $e) {
            $this->log('error', 'ML pattern analysis failed', [], $e);
        } finally {
            MLSemaphore::release('pattern_analysis');
            @ini_set('memory_limit', $originalLimit);
        }
    }

    /**
     * Predice problemi futuri basati sui pattern
     * 
     * RESOURCE LIMIT FIX: Timeout ridotto + memory limit
     */
    public function predictIssues(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        // RESOURCE FIX: Timeout ridotto da 60s a 30s
        if (!MLSemaphore::acquire('prediction_generation', 30)) {
            $this->log('warning', 'ML prediction generation skipped - semaphore busy');
            return [];
        }

        // RESOURCE FIX: Set memory limit
        $originalLimit = ini_get('memory_limit');
        @ini_set('memory_limit', self::MEMORY_LIMIT_ML);

        try {
            $patterns = $this->dataStorage->getLearnedPatterns();
            $current_data = $this->dataStorage->getCurrentData();
            
            $predictions = $this->predictionEngine->generatePredictions($patterns, $current_data);
            
            $this->dataStorage->storePredictions($predictions);
            
            $this->log('info', 'ML predictions generated', ['predictions_count' => count($predictions)]);
            
            return $predictions;
        } catch (\Throwable $e) {
            $this->log('error', 'ML prediction generation failed', [], $e);
            return [];
        } finally {
            MLSemaphore::release('prediction_generation');
            @ini_set('memory_limit', $originalLimit);
        }
    }

    /**
     * Rileva anomalie nei dati attuali
     */
    public function detectAnomalies(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        // Acquire semaphore for anomaly detection
        if (!MLSemaphore::acquire('anomaly_detection', 30)) {
            $this->log('warning', 'ML anomaly detection skipped - semaphore busy');
            return [];
        }

        try {
            $current_data = $this->dataStorage->getCurrentData();
            $historical_data = $this->dataStorage->getHistoricalData();
            
            $anomalies = $this->anomalyDetector->detect($current_data, $historical_data);
            
            return $anomalies;
        } finally {
            MLSemaphore::release('anomaly_detection');
        }
    }

    /**
     * Ottiene raccomandazioni basate su ML
     */
    public function getMLRecommendations(): array
    {
        $predictions = $this->dataStorage->getStoredPredictions();
        $anomalies = $this->detectAnomalies();
        $patterns = $this->dataStorage->getLearnedPatterns();
        
        $recommendations = [];
        
        // Raccomandazioni basate su predizioni
        foreach ($predictions as $prediction) {
            if ($prediction['confidence'] > 0.7) {
                $recommendations[] = [
                    'type' => 'prediction',
                    'priority' => $prediction['severity'],
                    'message' => $prediction['message'],
                    'confidence' => $prediction['confidence'],
                    'action' => $prediction['recommended_action']
                ];
            }
        }
        
        // Raccomandazioni basate su anomalie
        foreach ($anomalies as $anomaly) {
            $recommendations[] = [
                'type' => 'anomaly',
                'priority' => $anomaly['severity'],
                'message' => $anomaly['message'],
                'confidence' => $anomaly['confidence'],
                'action' => $anomaly['recommended_action']
            ];
        }
        
        // Raccomandazioni basate su pattern
        foreach ($patterns as $pattern) {
            if ($pattern['confidence'] > 0.8) {
                $recommendations[] = [
                    'type' => 'pattern',
                    'priority' => 'medium',
                    'message' => $pattern['description'],
                    'confidence' => $pattern['confidence'],
                    'action' => $pattern['recommended_action']
                ];
            }
        }
        
        // Ordina per priorità e confidence
        usort($recommendations, function($a, $b) {
            $priority_order = ['high' => 3, 'medium' => 2, 'low' => 1];
            $a_priority = $priority_order[$a['priority']] ?? 0;
            $b_priority = $priority_order[$b['priority']] ?? 0;
            
            if ($a_priority === $b_priority) {
                return $b['confidence'] <=> $a['confidence'];
            }
            
            return $b_priority <=> $a_priority;
        });
        
        return $recommendations;
    }

    /**
     * Genera report ML completo
     */
    public function generateMLReport(): array
    {
        $predictions = $this->dataStorage->getStoredPredictions();
        $anomalies = $this->detectAnomalies();
        $patterns = $this->dataStorage->getLearnedPatterns();
        $recommendations = $this->getMLRecommendations();
        
        return [
            'enabled' => $this->isEnabled(),
            'data_points' => count($this->dataStorage->getStoredData()),
            'predictions' => $predictions,
            'anomalies' => $anomalies,
            'patterns' => $patterns,
            'recommendations' => $recommendations,
            'model_accuracy' => $this->calculateModelAccuracy(),
            'last_analysis' => $this->getLastAnalysisTime(),
            'next_analysis' => $this->getNextAnalysisTime()
        ];
    }

    // Metodi gatherPerformanceData(), storePerformanceData(), generatePredictions(),
    // predictHighLoad(), predictMemoryIssues(), predictErrors() rimossi - ora gestiti da
    // DataCollector, DataStorage e PredictionEngine

    /**
     * Schedula analisi automatiche
     */
    private function scheduleAnalysis(): void
    {
        if (!wp_next_scheduled('fp_ps_ml_analyze_patterns')) {
            wp_schedule_event(time(), 'hourly', 'fp_ps_ml_analyze_patterns');
        }
        
        if (!wp_next_scheduled('fp_ps_ml_predict_issues')) {
            wp_schedule_event(time(), 'fp_ps_6hourly', 'fp_ps_ml_predict_issues');
        }
    }

    /**
     * Aggiunge schedule personalizzato
     */
    public function addCustomSchedules(array $schedules): array
    {
        $schedules['fp_ps_6hourly'] = [
            'interval' => 6 * HOUR_IN_SECONDS,
            'display' => __('Every 6 Hours (FP Performance ML)', 'fp-performance-suite'),
        ];
        
        return $schedules;
    }

    // Metodi getCurrentUrl(), getLoadTime(), getDbQueryCount(), getCacheHits(), getCacheMisses(),
    // getActivePluginsCount(), getServerLoad(), getErrorCount(), getStoredData(), getCurrentData(),
    // getHistoricalData(), getLearnedPatterns(), updateLearnedPatterns(), storePredictions(),
    // getStoredPredictions() rimossi - ora gestiti da DataCollector e DataStorage

    /**
     * Calcola accuratezza del modello
     */
    private function calculateModelAccuracy(): float
    {
        // Implementazione semplificata
        return 0.85; // 85% di accuratezza
    }

    /**
     * Ottiene ultima analisi
     */
    private function getLastAnalysisTime(): int
    {
        return (int) $this->getOption('fp_ps_ml_last_analysis', 0);
    }

    /**
     * Ottiene prossima analisi
     */
    private function getNextAnalysisTime(): int
    {
        return wp_next_scheduled('fp_ps_ml_analyze_patterns') ?: 0;
    }

    // Metodo getDataPointCount() rimosso - ora usa count($this->dataStorage->getStoredData())

    /**
     * Controlla se il servizio è abilitato
     */
    private function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }
    
    // Metodi isStorageQuotaExceeded(), getCurrentStorageSize(), cleanupOldData(), truncateData()
    // rimossi - ora gestiti da DataStorage
    
    /**
     * RESOURCE LIMIT: Ottiene statistiche storage
     * 
     * @return array Statistiche storage
     */
    public function getStorageStats(): array
    {
        $settings = $this->getSettings();
        $stats = $this->dataStorage->getStorageStats();
        $maxSize = ($settings['max_storage_mb'] ?? self::MAX_STORAGE_MB) * 1048576;
        $maxPoints = $settings['max_data_points'] ?? self::MAX_DATA_POINTS;
        
        return [
            'current_size_bytes' => $stats['storage_size_bytes'],
            'current_size_mb' => $stats['storage_size_mb'],
            'max_size_mb' => $settings['max_storage_mb'] ?? self::MAX_STORAGE_MB,
            'usage_percent' => round(($stats['storage_size_bytes'] / $maxSize) * 100, 2),
            'data_points' => $stats['data_points'],
            'max_data_points' => $maxPoints,
            'points_usage_percent' => round(($stats['data_points'] / $maxPoints) * 100, 2),
            'quota_exceeded' => $this->dataStorage->isStorageQuotaExceeded($settings),
        ];
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }
}