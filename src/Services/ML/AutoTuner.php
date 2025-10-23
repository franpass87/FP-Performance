<?php

namespace FP\PerfSuite\Services\ML;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\ServiceContainer;

/**
 * Auto Tuner Service
 * 
 * Auto-tuning automatico dei parametri basato su ML
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AutoTuner
{
    private const OPTION = 'fp_ps_auto_tuner';
    
    private ServiceContainer $container;
    private MLPredictor $predictor;
    private PatternLearner $patternLearner;

    public function __construct(
        ServiceContainer $container,
        MLPredictor $predictor,
        PatternLearner $patternLearner
    ) {
        $this->container = $container;
        $this->predictor = $predictor;
        $this->patternLearner = $patternLearner;
    }

    /**
     * Registra gli hook per l'auto-tuning
     */
    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Auto-tuning ogni 6 ore
        add_action('fp_ps_auto_tune', [$this, 'performAutoTuning']);
        
        // Schedula auto-tuning
        $this->scheduleAutoTuning();
        
        Logger::debug('Auto tuner registered');
    }

    /**
     * Esegue auto-tuning automatico
     */
    public function performAutoTuning(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $tuning_results = [];
        
        // Auto-tuning cache
        $tuning_results['cache'] = $this->tuneCacheSettings();
        
        // Auto-tuning database
        $tuning_results['database'] = $this->tuneDatabaseSettings();
        
        // Auto-tuning asset optimization
        $tuning_results['assets'] = $this->tuneAssetSettings();
        
        // Auto-tuning mobile
        $tuning_results['mobile'] = $this->tuneMobileSettings();
        
        // Salva risultati
        $this->saveTuningResults($tuning_results);
        
        Logger::info('Auto tuning completed', ['results' => $tuning_results]);
        
        return $tuning_results;
    }

    /**
     * Auto-tuning impostazioni cache
     */
    private function tuneCacheSettings(): array
    {
        $current_performance = $this->getCurrentPerformance();
        $recommendations = $this->predictor->getMLRecommendations();
        
        $tuning_results = [
            'tuned' => false,
            'changes' => [],
            'reason' => 'No tuning needed'
        ];
        
        // Tuning basato su predizioni
        foreach ($recommendations as $rec) {
            if ($rec['type'] === 'prediction' && strpos($rec['message'], 'carico') !== false) {
                // Aumenta durata cache per ridurre carico
                $new_duration = $this->calculateOptimalCacheDuration();
                if ($new_duration > $this->getCurrentCacheDuration()) {
                    $this->updateCacheDuration($new_duration);
                    $tuning_results['tuned'] = true;
                    $tuning_results['changes'][] = [
                        'setting' => 'cache_duration',
                        'old_value' => $this->getCurrentCacheDuration(),
                        'new_value' => $new_duration,
                        'reason' => 'High load prediction'
                    ];
                }
            }
        }
        
        // Tuning basato su pattern
        $patterns = $this->patternLearner->getLearnedPatterns();
        foreach ($patterns as $pattern) {
            if ($pattern['type'] === 'high_load_times') {
                // Implementa cache dinamica per orari critici
                $this->implementDynamicCaching($pattern['data']);
                $tuning_results['tuned'] = true;
                $tuning_results['changes'][] = [
                    'setting' => 'dynamic_caching',
                    'old_value' => 'disabled',
                    'new_value' => 'enabled',
                    'reason' => 'High load time pattern detected'
                ];
            }
        }
        
        return $tuning_results;
    }

    /**
     * Auto-tuning impostazioni database
     */
    private function tuneDatabaseSettings(): array
    {
        $tuning_results = [
            'tuned' => false,
            'changes' => [],
            'reason' => 'No tuning needed'
        ];
        
        $current_queries = $this->getCurrentDbQueryCount();
        $avg_queries = $this->getAverageDbQueryCount();
        
        // Se query sono troppe, ottimizza
        if ($current_queries > $avg_queries * 1.5) {
            $this->optimizeDatabaseQueries();
            $tuning_results['tuned'] = true;
            $tuning_results['changes'][] = [
                'setting' => 'query_optimization',
                'old_value' => 'basic',
                'new_value' => 'aggressive',
                'reason' => 'High query count detected'
            ];
        }
        
        // Tuning basato su memoria
        $memory_usage = memory_get_peak_usage(true);
        $avg_memory = $this->getAverageMemoryUsage();
        
        if ($memory_usage > $avg_memory * 1.3) {
            $this->optimizeMemoryUsage();
            $tuning_results['tuned'] = true;
            $tuning_results['changes'][] = [
                'setting' => 'memory_optimization',
                'old_value' => 'standard',
                'new_value' => 'aggressive',
                'reason' => 'High memory usage detected'
            ];
        }
        
        return $tuning_results;
    }

    /**
     * Auto-tuning impostazioni asset
     */
    private function tuneAssetSettings(): array
    {
        $tuning_results = [
            'tuned' => false,
            'changes' => [],
            'reason' => 'No tuning needed'
        ];
        
        $load_time = $this->getCurrentLoadTime();
        $avg_load_time = $this->getAverageLoadTime();
        
        // Se caricamento è lento, ottimizza asset
        if ($load_time > $avg_load_time * 1.2) {
            $this->optimizeAssetSettings();
            $tuning_results['tuned'] = true;
            $tuning_results['changes'][] = [
                'setting' => 'asset_optimization',
                'old_value' => 'standard',
                'new_value' => 'aggressive',
                'reason' => 'Slow load time detected'
            ];
        }
        
        // Tuning basato su dispositivo
        if (wp_is_mobile()) {
            $this->optimizeForMobile();
            $tuning_results['tuned'] = true;
            $tuning_results['changes'][] = [
                'setting' => 'mobile_optimization',
                'old_value' => 'disabled',
                'new_value' => 'enabled',
                'reason' => 'Mobile device detected'
            ];
        }
        
        return $tuning_results;
    }

    /**
     * Auto-tuning impostazioni mobile
     */
    private function tuneMobileSettings(): array
    {
        $tuning_results = [
            'tuned' => false,
            'changes' => [],
            'reason' => 'No tuning needed'
        ];
        
        if (!wp_is_mobile()) {
            return $tuning_results;
        }
        
        // Analizza performance mobile
        $mobile_performance = $this->analyzeMobilePerformance();
        
        if ($mobile_performance['score'] < 0.7) {
            $this->optimizeMobileSettings();
            $tuning_results['tuned'] = true;
            $tuning_results['changes'][] = [
                'setting' => 'mobile_optimization',
                'old_value' => 'basic',
                'new_value' => 'advanced',
                'reason' => 'Poor mobile performance detected'
            ];
        }
        
        return $tuning_results;
    }

    /**
     * Calcola durata cache ottimale
     */
    private function calculateOptimalCacheDuration(): int
    {
        $current_load = $this->getCurrentServerLoad();
        $avg_load = $this->getAverageServerLoad();
        
        // Se carico è alto, aumenta durata cache
        if ($current_load > $avg_load * 1.2) {
            return 3600; // 1 ora
        } elseif ($current_load > $avg_load * 1.1) {
            return 1800; // 30 minuti
        } else {
            return 900; // 15 minuti
        }
    }

    /**
     * Implementa cache dinamica
     */
    private function implementDynamicCaching(array $high_load_times): void
    {
        $settings = get_option('fp_ps_cache', []);
        $settings['dynamic_caching'] = true;
        $settings['high_load_times'] = $high_load_times;
        update_option('fp_ps_cache', $settings);
    }

    /**
     * Ottimizza query database
     */
    private function optimizeDatabaseQueries(): void
    {
        $settings = get_option('fp_ps_db', []);
        $settings['query_optimization'] = 'aggressive';
        $settings['enable_query_cache'] = true;
        $settings['query_cache_size'] = '64MB';
        update_option('fp_ps_db', $settings);
    }

    /**
     * Ottimizza uso memoria
     */
    private function optimizeMemoryUsage(): void
    {
        $settings = get_option('fp_ps_general', []);
        $settings['memory_optimization'] = 'aggressive';
        $settings['enable_garbage_collection'] = true;
        $settings['memory_limit'] = '512M';
        update_option('fp_ps_general', $settings);
    }

    /**
     * Ottimizza impostazioni asset
     */
    private function optimizeAssetSettings(): void
    {
        $settings = get_option('fp_ps_assets', []);
        $settings['optimization_level'] = 'aggressive';
        $settings['enable_minification'] = true;
        $settings['enable_compression'] = true;
        $settings['enable_combining'] = true;
        update_option('fp_ps_assets', $settings);
    }

    /**
     * Ottimizza per mobile
     */
    private function optimizeForMobile(): void
    {
        $settings = get_option('fp_ps_mobile', []);
        $settings['enabled'] = true;
        $settings['optimization_level'] = 'aggressive';
        $settings['enable_lazy_loading'] = true;
        $settings['enable_responsive_images'] = true;
        update_option('fp_ps_mobile', $settings);
    }

    /**
     * Ottimizza impostazioni mobile
     */
    private function optimizeMobileSettings(): void
    {
        $settings = get_option('fp_ps_mobile', []);
        $settings['optimization_level'] = 'advanced';
        $settings['enable_touch_optimization'] = true;
        $settings['enable_mobile_caching'] = true;
        $settings['disable_animations'] = true;
        update_option('fp_ps_mobile', $settings);
    }

    /**
     * Analizza performance mobile
     */
    private function analyzeMobilePerformance(): array
    {
        $load_time = $this->getCurrentLoadTime();
        $memory_usage = memory_get_peak_usage(true);
        $db_queries = $this->getCurrentDbQueryCount();
        
        // Calcola score (0-1)
        $load_score = max(0, 1 - ($load_time / 3.0)); // 3s = 0 score
        $memory_score = max(0, 1 - ($memory_usage / (256 * 1024 * 1024))); // 256MB = 0 score
        $query_score = max(0, 1 - ($db_queries / 100)); // 100 queries = 0 score
        
        $overall_score = ($load_score + $memory_score + $query_score) / 3;
        
        return [
            'score' => $overall_score,
            'load_score' => $load_score,
            'memory_score' => $memory_score,
            'query_score' => $query_score,
            'load_time' => $load_time,
            'memory_usage' => $memory_usage,
            'db_queries' => $db_queries
        ];
    }

    /**
     * Schedula auto-tuning
     */
    private function scheduleAutoTuning(): void
    {
        if (!wp_next_scheduled('fp_ps_auto_tune')) {
            wp_schedule_event(time(), 'fp_ps_6hourly', 'fp_ps_auto_tune');
        }
    }

    /**
     * Salva risultati tuning
     */
    private function saveTuningResults(array $results): void
    {
        $tuning_history = get_option('fp_ps_tuning_history', []);
        $tuning_history[] = [
            'timestamp' => time(),
            'results' => $results
        ];
        
        // Mantieni solo ultimi 50 tuning
        if (count($tuning_history) > 50) {
            $tuning_history = array_slice($tuning_history, -50);
        }
        
        update_option('fp_ps_tuning_history', $tuning_history);
    }

    /**
     * Ottiene performance corrente
     */
    private function getCurrentPerformance(): array
    {
        return [
            'load_time' => $this->getCurrentLoadTime(),
            'memory_usage' => memory_get_peak_usage(true),
            'server_load' => $this->getCurrentServerLoad(),
            'db_queries' => $this->getCurrentDbQueryCount()
        ];
    }

    /**
     * Ottiene tempo di caricamento corrente
     */
    private function getCurrentLoadTime(): float
    {
        return microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    }

    /**
     * Ottiene carico server corrente
     */
    private function getCurrentServerLoad(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0.0;
        }
        return 0.0;
    }

    /**
     * Ottiene conteggio query database corrente
     */
    private function getCurrentDbQueryCount(): int
    {
        global $wpdb;
        return $wpdb->num_queries ?? 0;
    }

    /**
     * Ottiene durata cache corrente
     */
    private function getCurrentCacheDuration(): int
    {
        $settings = get_option('fp_ps_cache', []);
        return $settings['duration'] ?? 900;
    }

    /**
     * Aggiorna durata cache
     */
    private function updateCacheDuration(int $duration): void
    {
        $settings = get_option('fp_ps_cache', []);
        $settings['duration'] = $duration;
        update_option('fp_ps_cache', $settings);
    }

    /**
     * Ottiene media query database
     */
    private function getAverageDbQueryCount(): float
    {
        $data = get_option('fp_ps_ml_data', []);
        if (empty($data)) {
            return 0;
        }
        
        $queries = array_column($data, 'db_queries');
        return array_sum($queries) / count($queries);
    }

    /**
     * Ottiene media uso memoria
     */
    private function getAverageMemoryUsage(): float
    {
        $data = get_option('fp_ps_ml_data', []);
        if (empty($data)) {
            return 0;
        }
        
        $memory = array_column($data, 'memory_usage');
        return array_sum($memory) / count($memory);
    }

    /**
     * Ottiene media tempo caricamento
     */
    private function getAverageLoadTime(): float
    {
        $data = get_option('fp_ps_ml_data', []);
        if (empty($data)) {
            return 0;
        }
        
        $load_times = array_column($data, 'load_time');
        return array_sum($load_times) / count($load_times);
    }

    /**
     * Ottiene media carico server
     */
    private function getAverageServerLoad(): float
    {
        $data = get_option('fp_ps_ml_data', []);
        if (empty($data)) {
            return 0;
        }
        
        $loads = array_column($data, 'server_load');
        return array_sum($loads) / count($loads);
    }

    /**
     * Genera report auto-tuning
     */
    public function generateTuningReport(): array
    {
        $tuning_history = get_option('fp_ps_tuning_history', []);
        $last_tuning = end($tuning_history);
        
        return [
            'enabled' => $this->isEnabled(),
            'last_tuning' => $last_tuning ? $last_tuning['timestamp'] : 0,
            'tuning_count' => count($tuning_history),
            'recent_changes' => $last_tuning ? $last_tuning['results'] : [],
            'next_tuning' => wp_next_scheduled('fp_ps_auto_tune')
        ];
    }

    /**
     * Ottiene le impostazioni
     */
    private function settings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'tuning_frequency' => '6hourly',
            'aggressive_mode' => false,
            'auto_apply_changes' => true,
            'tuning_threshold' => 0.1
        ]);
    }

    /**
     * Controlla se il servizio è abilitato
     */
    private function isEnabled(): bool
    {
        $settings = $this->settings();
        return !empty($settings['enabled']);
    }
}