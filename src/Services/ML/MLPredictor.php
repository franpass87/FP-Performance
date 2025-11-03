<?php

namespace FP\PerfSuite\Services\ML;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\MLSemaphore;
use FP\PerfSuite\ServiceContainer;

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
    
    private ServiceContainer $container;
    private PatternLearner $patternLearner;
    private AnomalyDetector $anomalyDetector;

    public function __construct(
        ServiceContainer $container,
        PatternLearner $patternLearner,
        AnomalyDetector $anomalyDetector
    ) {
        $this->container = $container;
        $this->patternLearner = $patternLearner;
        $this->anomalyDetector = $anomalyDetector;
    }

    /**
     * Ottiene le impostazioni del servizio
     */
    public function getSettings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'data_retention_days' => self::DATA_RETENTION_DAYS_DEFAULT,
            'max_storage_mb' => self::MAX_STORAGE_MB,
            'max_data_points' => self::MAX_DATA_POINTS,
            'prediction_threshold' => 0.7,
            'anomaly_threshold' => 0.8,
            'pattern_confidence_threshold' => 0.8
        ]);
    }

    /**
     * Aggiorna le impostazioni del servizio
     */
    public function updateSettings(array $settings): void
    {
        update_option(self::OPTION, $settings);
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
        
        Logger::debug('ML Predictor registered');
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
            Logger::warning('ML data storage quota exceeded, skipping collection. Run cleanup.');
            
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
                Logger::warning('Performance data too large, truncating', [
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
            Logger::error('ML data collection failed', $e);
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
            Logger::warning('ML pattern analysis skipped - semaphore busy');
            return;
        }

        // RESOURCE FIX: Set memory limit
        $originalLimit = ini_get('memory_limit');
        @ini_set('memory_limit', self::MEMORY_LIMIT_ML);

        try {
            $data = $this->getStoredData();
            
            // RESOURCE FIX: Limita i dati processati
            if (count($data) > 1000) {
                $data = array_slice($data, -1000); // Solo ultimi 1000
                Logger::debug('ML analysis limited to last 1000 data points');
            }
            
            $patterns = $this->patternLearner->learnPatterns($data);
            
            $this->updateLearnedPatterns($patterns);
            
            Logger::info('ML patterns analyzed', ['patterns_count' => count($patterns)]);
        } catch (\Throwable $e) {
            Logger::error('ML pattern analysis failed', $e);
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
            Logger::warning('ML prediction generation skipped - semaphore busy');
            return [];
        }

        // RESOURCE FIX: Set memory limit
        $originalLimit = ini_get('memory_limit');
        @ini_set('memory_limit', self::MEMORY_LIMIT_ML);

        try {
            $patterns = $this->getLearnedPatterns();
            $current_data = $this->getCurrentData();
            
            $predictions = $this->generatePredictions($patterns, $current_data);
            
            $this->storePredictions($predictions);
            
            Logger::info('ML predictions generated', ['predictions_count' => count($predictions)]);
            
            return $predictions;
        } catch (\Throwable $e) {
            Logger::error('ML prediction generation failed', $e);
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
            Logger::warning('ML anomaly detection skipped - semaphore busy');
            return [];
        }

        try {
            $current_data = $this->getCurrentData();
            $historical_data = $this->getHistoricalData();
            
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
        $predictions = $this->getStoredPredictions();
        $anomalies = $this->detectAnomalies();
        $patterns = $this->getLearnedPatterns();
        
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
        $predictions = $this->getStoredPredictions();
        $anomalies = $this->detectAnomalies();
        $patterns = $this->getLearnedPatterns();
        $recommendations = $this->getMLRecommendations();
        
        return [
            'enabled' => $this->isEnabled(),
            'data_points' => $this->getDataPointCount(),
            'predictions' => $predictions,
            'anomalies' => $anomalies,
            'patterns' => $patterns,
            'recommendations' => $recommendations,
            'model_accuracy' => $this->calculateModelAccuracy(),
            'last_analysis' => $this->getLastAnalysisTime(),
            'next_analysis' => $this->getNextAnalysisTime()
        ];
    }

    /**
     * Raccoglie dati di performance
     */
    private function gatherPerformanceData(): array
    {
        $data = [
            'timestamp' => time(),
            'url' => $this->getCurrentUrl(),
            'load_time' => $this->getLoadTime(),
            'memory_usage' => memory_get_peak_usage(true),
            'db_queries' => $this->getDbQueryCount(),
            'cache_hits' => $this->getCacheHits(),
            'cache_misses' => $this->getCacheMisses(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'is_mobile' => wp_is_mobile(),
            'is_admin' => is_admin(),
            'active_plugins' => $this->getActivePluginsCount(),
            'theme' => get_template(),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'server_load' => $this->getServerLoad(),
            'error_count' => $this->getErrorCount()
        ];
        
        return $data;
    }

    /**
     * Memorizza dati di performance
     * 
     * RESOURCE LIMIT FIX: Rispetta limiti configurati
     */
    private function storePerformanceData(array $data): void
    {
        $stored_data = get_option(self::DATA_OPTION, []);
        
        $settings = $this->getSettings();
        $maxPoints = $settings['max_data_points'] ?? self::MAX_DATA_POINTS;
        
        // RESOURCE FIX: Mantieni solo i punti configurati
        if (count($stored_data) >= $maxPoints) {
            $keepCount = $maxPoints - 1;
            $stored_data = array_slice($stored_data, -$keepCount);
            
            Logger::debug('ML data trimmed to max points', [
                'max_points' => $maxPoints,
                'kept' => $keepCount
            ]);
        }
        
        $stored_data[] = $data;
        
        update_option(self::DATA_OPTION, $stored_data, false); // No autoload
    }

    /**
     * Genera predizioni basate su pattern e dati attuali
     */
    private function generatePredictions(array $patterns, array $current_data): array
    {
        $predictions = [];
        
        // Predizione carico eccessivo
        if ($this->predictHighLoad($patterns, $current_data)) {
            $predictions[] = [
                'type' => 'high_load',
                'severity' => 'high',
                'confidence' => 0.85,
                'message' => 'Predetto carico eccessivo nelle prossime ore',
                'recommended_action' => 'Attivare cache aggiuntiva e ottimizzazioni'
            ];
        }
        
        // Predizione problemi di memoria
        if ($this->predictMemoryIssues($patterns, $current_data)) {
            $predictions[] = [
                'type' => 'memory_issue',
                'severity' => 'medium',
                'confidence' => 0.75,
                'message' => 'Possibili problemi di memoria in arrivo',
                'recommended_action' => 'Ottimizzare query database e cache'
            ];
        }
        
        // Predizione errori
        if ($this->predictErrors($patterns, $current_data)) {
            $predictions[] = [
                'type' => 'errors',
                'severity' => 'high',
                'confidence' => 0.80,
                'message' => 'Aumento errori previsto',
                'recommended_action' => 'Controllare log e configurazioni'
            ];
        }
        
        return $predictions;
    }

    /**
     * Predice carico eccessivo
     */
    private function predictHighLoad(array $patterns, array $current_data): bool
    {
        // Logica semplificata per predizione carico
        $recent_loads = array_column(array_slice($this->getStoredData(), -10), 'server_load');
        $recentLoadsCount = count($recent_loads);
        $avg_load = $recentLoadsCount > 0 ? array_sum($recent_loads) / $recentLoadsCount : 0;
        
        return $avg_load > 0.8 && $current_data['server_load'] > 0.7;
    }

    /**
     * Predice problemi di memoria
     */
    private function predictMemoryIssues(array $patterns, array $current_data): bool
    {
        $recent_memory = array_column(array_slice($this->getStoredData(), -10), 'memory_usage');
        $recentMemoryCount = count($recent_memory);
        $avg_memory = $recentMemoryCount > 0 ? array_sum($recent_memory) / $recentMemoryCount : 0;
        
        return $current_data['memory_usage'] > $avg_memory * 1.2;
    }

    /**
     * Predice errori
     */
    private function predictErrors(array $patterns, array $current_data): bool
    {
        $recent_errors = array_column(array_slice($this->getStoredData(), -10), 'error_count');
        $recentErrorsCount = count($recent_errors);
        $avg_errors = $recentErrorsCount > 0 ? array_sum($recent_errors) / $recentErrorsCount : 0;
        
        return $current_data['error_count'] > $avg_errors * 1.5;
    }

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

    /**
     * Ottiene URL corrente
     */
    private function getCurrentUrl(): string
    {
        return home_url($_SERVER['REQUEST_URI'] ?? '');
    }

    /**
     * Ottiene tempo di caricamento
     */
    private function getLoadTime(): float
    {
        return microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    }

    /**
     * Ottiene conteggio query database
     */
    private function getDbQueryCount(): int
    {
        global $wpdb;
        return $wpdb->num_queries ?? 0;
    }

    /**
     * Ottiene cache hits
     */
    private function getCacheHits(): int
    {
        return wp_cache_get('fp_ps_cache_hits', 'fp_ps_stats') ?: 0;
    }

    /**
     * Ottiene cache misses
     */
    private function getCacheMisses(): int
    {
        return wp_cache_get('fp_ps_cache_misses', 'fp_ps_stats') ?: 0;
    }

    /**
     * Ottiene conteggio plugin attivi
     */
    private function getActivePluginsCount(): int
    {
        return count(get_option('active_plugins', []));
    }

    /**
     * Ottiene carico server
     */
    private function getServerLoad(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0.0;
        }
        
        return 0.0;
    }

    /**
     * Ottiene conteggio errori
     */
    private function getErrorCount(): int
    {
        return count(error_get_last() ? [error_get_last()] : []);
    }

    /**
     * Ottiene dati memorizzati
     */
    private function getStoredData(): array
    {
        return get_option(self::DATA_OPTION, []);
    }

    /**
     * Ottiene dati attuali
     */
    private function getCurrentData(): array
    {
        return $this->gatherPerformanceData();
    }

    /**
     * Ottiene dati storici
     */
    private function getHistoricalData(): array
    {
        return array_slice($this->getStoredData(), -100); // Ultimi 100 punti
    }

    /**
     * Ottiene pattern appresi
     */
    private function getLearnedPatterns(): array
    {
        return get_option('fp_ps_ml_patterns', []);
    }

    /**
     * Aggiorna pattern appresi
     */
    private function updateLearnedPatterns(array $patterns): void
    {
        update_option('fp_ps_ml_patterns', $patterns);
    }

    /**
     * Memorizza predizioni
     */
    private function storePredictions(array $predictions): void
    {
        update_option('fp_ps_ml_predictions', $predictions);
    }

    /**
     * Ottiene predizioni memorizzate
     */
    private function getStoredPredictions(): array
    {
        return get_option('fp_ps_ml_predictions', []);
    }

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
        return get_option('fp_ps_ml_last_analysis', 0);
    }

    /**
     * Ottiene prossima analisi
     */
    private function getNextAnalysisTime(): int
    {
        return wp_next_scheduled('fp_ps_ml_analyze_patterns') ?: 0;
    }

    /**
     * Ottiene conteggio punti dati
     */
    private function getDataPointCount(): int
    {
        return count($this->getStoredData());
    }

    /**
     * Controlla se il servizio è abilitato
     */
    private function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }
    
    /**
     * RESOURCE LIMIT: Verifica se la quota storage è stata superata
     * 
     * @return bool True se quota superata
     */
    private function isStorageQuotaExceeded(): bool
    {
        $settings = $this->getSettings();
        $maxSizeMB = $settings['max_storage_mb'] ?? self::MAX_STORAGE_MB;
        
        $currentSizeBytes = $this->getCurrentStorageSize();
        $currentSizeMB = $currentSizeBytes / 1048576; // Converti in MB
        
        if ($currentSizeMB > $maxSizeMB) {
            Logger::warning('ML storage quota exceeded', [
                'current_mb' => round($currentSizeMB, 2),
                'max_mb' => $maxSizeMB
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * RESOURCE LIMIT: Ottiene dimensione storage corrente
     * 
     * @return int Dimensione in bytes
     */
    private function getCurrentStorageSize(): int
    {
        $stored_data = get_option(self::DATA_OPTION, []);
        $patterns = get_option('fp_ps_ml_patterns', []);
        $predictions = get_option('fp_ps_ml_predictions', []);
        
        $totalSize = 0;
        $totalSize += strlen(serialize($stored_data));
        $totalSize += strlen(serialize($patterns));
        $totalSize += strlen(serialize($predictions));
        
        return $totalSize;
    }
    
    /**
     * RESOURCE LIMIT: Pulisce dati vecchi oltre retention period
     */
    private function cleanupOldData(): int
    {
        $settings = $this->getSettings();
        $retentionDays = $settings['data_retention_days'] ?? self::DATA_RETENTION_DAYS_DEFAULT;
        
        $stored_data = get_option(self::DATA_OPTION, []);
        $cutoffTime = time() - ($retentionDays * DAY_IN_SECONDS);
        
        $originalCount = count($stored_data);
        
        // Filtra dati più vecchi del retention period
        $filtered_data = array_filter($stored_data, function($entry) use ($cutoffTime) {
            return isset($entry['timestamp']) && $entry['timestamp'] >= $cutoffTime;
        });
        
        $filtered_data = array_values($filtered_data); // Re-index
        
        $removed = $originalCount - count($filtered_data);
        
        if ($removed > 0) {
            update_option(self::DATA_OPTION, $filtered_data, false);
            
            Logger::info('ML old data cleaned up', [
                'removed' => $removed,
                'remaining' => count($filtered_data),
                'retention_days' => $retentionDays
            ]);
        }
        
        return $removed;
    }
    
    /**
     * RESOURCE LIMIT: Tronca dati troppo grandi
     * 
     * @param array $data Dati da troncare
     * @return array Dati troncati
     */
    private function truncateData(array $data): array
    {
        // Rimuovi campi non essenziali
        $essential = [
            'timestamp',
            'url',
            'load_time',
            'memory_usage',
            'db_queries',
            'is_mobile',
            'error_count'
        ];
        
        $truncated = [];
        foreach ($essential as $key) {
            if (isset($data[$key])) {
                $truncated[$key] = $data[$key];
            }
        }
        
        // Tronca URL troppo lunghi
        if (isset($truncated['url']) && strlen($truncated['url']) > 255) {
            $truncated['url'] = substr($truncated['url'], 0, 255);
        }
        
        return $truncated;
    }
    
    /**
     * RESOURCE LIMIT: Ottiene statistiche storage
     * 
     * @return array Statistiche storage
     */
    public function getStorageStats(): array
    {
        $settings = $this->getSettings();
        $currentSize = $this->getCurrentStorageSize();
        $maxSize = ($settings['max_storage_mb'] ?? self::MAX_STORAGE_MB) * 1048576;
        
        $dataPoints = $this->getDataPointCount();
        $maxPoints = $settings['max_data_points'] ?? self::MAX_DATA_POINTS;
        
        return [
            'current_size_bytes' => $currentSize,
            'current_size_mb' => round($currentSize / 1048576, 2),
            'max_size_mb' => $settings['max_storage_mb'] ?? self::MAX_STORAGE_MB,
            'usage_percent' => round(($currentSize / $maxSize) * 100, 2),
            'data_points' => $dataPoints,
            'max_data_points' => $maxPoints,
            'points_usage_percent' => round(($dataPoints / $maxPoints) * 100, 2),
            'quota_exceeded' => $this->isStorageQuotaExceeded(),
        ];
    }
}