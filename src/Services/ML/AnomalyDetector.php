<?php

namespace FP\PerfSuite\Services\ML;

use FP\PerfSuite\Utils\Logger;

/**
 * Anomaly Detector Service
 * 
 * Rileva anomalie nei dati di performance usando algoritmi ML
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class AnomalyDetector
{
    private const OPTION = 'fp_ps_anomaly_detector';

    /**
     * Rileva anomalie confrontando dati attuali con storici
     */
    public function detect(array $current_data, array $historical_data): array
    {
        if (empty($current_data) || empty($historical_data)) {
            return [];
        }

        $anomalies = [];
        
        // Anomalie di carico
        $anomalies = array_merge($anomalies, $this->detectLoadAnomalies($current_data, $historical_data));
        
        // Anomalie di memoria
        $anomalies = array_merge($anomalies, $this->detectMemoryAnomalies($current_data, $historical_data));
        
        // Anomalie di tempo di caricamento
        $anomalies = array_merge($anomalies, $this->detectLoadTimeAnomalies($current_data, $historical_data));
        
        // Anomalie di errori
        $anomalies = array_merge($anomalies, $this->detectErrorAnomalies($current_data, $historical_data));
        
        // Anomalie di query database
        $anomalies = array_merge($anomalies, $this->detectDbQueryAnomalies($current_data, $historical_data));
        
        // Filtra anomalie per threshold
        $anomalies = $this->filterAnomaliesByThreshold($anomalies);
        
        Logger::info('Anomalies detected', ['anomalies_count' => count($anomalies)]);
        
        return $anomalies;
    }

    /**
     * Rileva anomalie di carico server
     */
    private function detectLoadAnomalies(array $current_data, array $historical_data): array
    {
        $anomalies = [];
        
        $current_load = $current_data['server_load'] ?? 0;
        $historical_loads = array_column($historical_data, 'server_load');
        
        if (empty($historical_loads)) {
            return $anomalies;
        }
        
        $stats = $this->calculateStatistics($historical_loads);
        
        // Anomalia: carico eccessivo
        if ($current_load > $stats['mean'] + (2 * $stats['std_dev'])) {
            $anomalies[] = [
                'type' => 'high_load',
                'severity' => $this->calculateSeverity($current_load, $stats['mean'], $stats['std_dev']),
                'confidence' => $this->calculateConfidence($current_load, $stats),
                'message' => sprintf(
                    'Carico server anomalo: %.2f (media storica: %.2f)',
                    $current_load,
                    $stats['mean']
                ),
                'current_value' => $current_load,
                'historical_mean' => $stats['mean'],
                'historical_std_dev' => $stats['std_dev'],
                'recommended_action' => 'Controllare processi attivi e ottimizzare risorse'
            ];
        }
        
        // Anomalia: carico molto basso (possibile problema)
        if ($current_load < $stats['mean'] - (2 * $stats['std_dev']) && $current_load < 0.1) {
            $anomalies[] = [
                'type' => 'low_load',
                'severity' => 'medium',
                'confidence' => 0.7,
                'message' => sprintf(
                    'Carico server insolitamente basso: %.2f (media storica: %.2f)',
                    $current_load,
                    $stats['mean']
                ),
                'current_value' => $current_load,
                'historical_mean' => $stats['mean'],
                'recommended_action' => 'Verificare che il server stia funzionando correttamente'
            ];
        }
        
        return $anomalies;
    }

    /**
     * Rileva anomalie di memoria
     */
    private function detectMemoryAnomalies(array $current_data, array $historical_data): array
    {
        $anomalies = [];
        
        $current_memory = $current_data['memory_usage'] ?? 0;
        $historical_memory = array_column($historical_data, 'memory_usage');
        
        if (empty($historical_memory)) {
            return $anomalies;
        }
        
        $stats = $this->calculateStatistics($historical_memory);
        
        // Anomalia: uso memoria eccessivo
        if ($current_memory > $stats['mean'] + (2 * $stats['std_dev'])) {
            $anomalies[] = [
                'type' => 'high_memory',
                'severity' => $this->calculateSeverity($current_memory, $stats['mean'], $stats['std_dev']),
                'confidence' => $this->calculateConfidence($current_memory, $stats),
                'message' => sprintf(
                    'Uso memoria anomalo: %s (media storica: %s)',
                    size_format($current_memory),
                    size_format($stats['mean'])
                ),
                'current_value' => $current_memory,
                'historical_mean' => $stats['mean'],
                'historical_std_dev' => $stats['std_dev'],
                'recommended_action' => 'Ottimizzare query database e implementare garbage collection'
            ];
        }
        
        // Anomalia: crescita rapida memoria
        $recent_memory = array_slice($historical_memory, -5);
        if (count($recent_memory) >= 3) {
            $trend = $this->calculateTrend($recent_memory);
            if ($trend > 0.1) { // Crescita > 10% per punto
                $anomalies[] = [
                    'type' => 'memory_growth',
                    'severity' => 'high',
                    'confidence' => 0.8,
                    'message' => sprintf(
                        'Crescita rapida memoria rilevata: %.1f%% per punto dati',
                        $trend * 100
                    ),
                    'trend' => $trend,
                    'recommended_action' => 'Identificare e risolvere memory leak'
                ];
            }
        }
        
        return $anomalies;
    }

    /**
     * Rileva anomalie di tempo di caricamento
     */
    private function detectLoadTimeAnomalies(array $current_data, array $historical_data): array
    {
        $anomalies = [];
        
        $current_load_time = $current_data['load_time'] ?? 0;
        $historical_load_times = array_column($historical_data, 'load_time');
        
        if (empty($historical_load_times)) {
            return $anomalies;
        }
        
        $stats = $this->calculateStatistics($historical_load_times);
        
        // Anomalia: tempo di caricamento eccessivo
        if ($current_load_time > $stats['mean'] + (2 * $stats['std_dev'])) {
            $anomalies[] = [
                'type' => 'slow_load',
                'severity' => $this->calculateSeverity($current_load_time, $stats['mean'], $stats['std_dev']),
                'confidence' => $this->calculateConfidence($current_load_time, $stats),
                'message' => sprintf(
                    'Tempo caricamento anomalo: %.2fs (media storica: %.2fs)',
                    $current_load_time,
                    $stats['mean']
                ),
                'current_value' => $current_load_time,
                'historical_mean' => $stats['mean'],
                'historical_std_dev' => $stats['std_dev'],
                'recommended_action' => 'Ottimizzare asset, database e cache'
            ];
        }
        
        return $anomalies;
    }

    /**
     * Rileva anomalie di errori
     */
    private function detectErrorAnomalies(array $current_data, array $historical_data): array
    {
        $anomalies = [];
        
        $current_errors = $current_data['error_count'] ?? 0;
        $historical_errors = array_column($historical_data, 'error_count');
        
        if (empty($historical_errors)) {
            return $anomalies;
        }
        
        $stats = $this->calculateStatistics($historical_errors);
        
        // Anomalia: aumento errori
        if ($current_errors > $stats['mean'] + (2 * $stats['std_dev']) && $current_errors > 0) {
            $anomalies[] = [
                'type' => 'error_spike',
                'severity' => $this->calculateSeverity($current_errors, $stats['mean'], $stats['std_dev']),
                'confidence' => $this->calculateConfidence($current_errors, $stats),
                'message' => sprintf(
                    'Aumento anomalo errori: %d (media storica: %.1f)',
                    $current_errors,
                    $stats['mean']
                ),
                'current_value' => $current_errors,
                'historical_mean' => $stats['mean'],
                'historical_std_dev' => $stats['std_dev'],
                'recommended_action' => 'Controllare log errori e configurazioni'
            ];
        }
        
        return $anomalies;
    }

    /**
     * Rileva anomalie di query database
     */
    private function detectDbQueryAnomalies(array $current_data, array $historical_data): array
    {
        $anomalies = [];
        
        $current_queries = $current_data['db_queries'] ?? 0;
        $historical_queries = array_column($historical_data, 'db_queries');
        
        if (empty($historical_queries)) {
            return $anomalies;
        }
        
        $stats = $this->calculateStatistics($historical_queries);
        
        // Anomalia: troppe query database
        if ($current_queries > $stats['mean'] + (2 * $stats['std_dev'])) {
            $anomalies[] = [
                'type' => 'high_db_queries',
                'severity' => $this->calculateSeverity($current_queries, $stats['mean'], $stats['std_dev']),
                'confidence' => $this->calculateConfidence($current_queries, $stats),
                'message' => sprintf(
                    'Numero anomalo query database: %d (media storica: %.1f)',
                    $current_queries,
                    $stats['mean']
                ),
                'current_value' => $current_queries,
                'historical_mean' => $stats['mean'],
                'historical_std_dev' => $stats['std_dev'],
                'recommended_action' => 'Ottimizzare query e implementare caching'
            ];
        }
        
        return $anomalies;
    }

    /**
     * Calcola statistiche per un array di valori
     */
    private function calculateStatistics(array $values): array
    {
        $count = count($values);
        if ($count === 0) {
            return ['mean' => 0, 'std_dev' => 0, 'min' => 0, 'max' => 0];
        }
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;
        $std_dev = sqrt($variance);
        
        return [
            'mean' => $mean,
            'std_dev' => $std_dev,
            'min' => min($values),
            'max' => max($values),
            'count' => $count
        ];
    }

    /**
     * Calcola trend per un array di valori
     */
    private function calculateTrend(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0;
        }
        
        $first = $values[0];
        $last = $values[$count - 1];
        
        if ($first == 0) {
            return 0;
        }
        
        return ($last - $first) / $first;
    }

    /**
     * Calcola severità dell'anomalia
     */
    private function calculateSeverity(float $current_value, float $mean, float $std_dev): string
    {
        if ($std_dev == 0) {
            return 'low';
        }
        
        $z_score = abs($current_value - $mean) / $std_dev;
        
        if ($z_score >= 3) {
            return 'critical';
        } elseif ($z_score >= 2.5) {
            return 'high';
        } elseif ($z_score >= 2) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Calcola confidence score per l'anomalia
     */
    private function calculateConfidence(float $current_value, array $stats): float
    {
        if ($stats['std_dev'] == 0) {
            return 0.5;
        }
        
        $z_score = abs($current_value - $stats['mean']) / $stats['std_dev'];
        
        // Confidence basata su z-score e numero di campioni
        $base_confidence = min(0.95, $z_score / 3);
        $sample_factor = min(1.0, $stats['count'] / 50); // Più campioni = più confidence
        
        return $base_confidence * $sample_factor;
    }

    /**
     * Filtra anomalie per threshold
     */
    private function filterAnomaliesByThreshold(array $anomalies): array
    {
        $settings = $this->settings();
        $threshold = $settings['confidence_threshold'] ?? 0.7;
        
        return array_filter($anomalies, fn($anomaly) => $anomaly['confidence'] >= $threshold);
    }

    /**
     * Rileva anomalie in tempo reale
     */
    public function detectRealtimeAnomalies(array $current_data): array
    {
        // Ottieni dati storici recenti (ultime 24 ore)
        $historical_data = $this->getRecentHistoricalData();
        
        if (empty($historical_data)) {
            return [];
        }
        
        return $this->detect($current_data, $historical_data);
    }

    /**
     * Ottiene dati storici recenti
     */
    private function getRecentHistoricalData(): array
    {
        $all_data = get_option('fp_ps_ml_data', []);
        $cutoff_time = time() - (24 * HOUR_IN_SECONDS); // Ultime 24 ore
        
        return array_filter($all_data, fn($point) => $point['timestamp'] >= $cutoff_time);
    }

    /**
     * Genera report anomalie
     */
    public function generateAnomalyReport(): array
    {
        $current_data = $this->getCurrentData();
        $historical_data = $this->getRecentHistoricalData();
        
        $anomalies = $this->detect($current_data, $historical_data);
        
        return [
            'enabled' => $this->isEnabled(),
            'anomalies' => $anomalies,
            'anomalies_count' => count($anomalies),
            'critical_anomalies' => count(array_filter($anomalies, fn($a) => $a['severity'] === 'critical')),
            'high_anomalies' => count(array_filter($anomalies, fn($a) => $a['severity'] === 'high')),
            'last_detection' => time(),
            'historical_data_points' => count($historical_data)
        ];
    }

    /**
     * Ottiene dati attuali
     */
    private function getCurrentData(): array
    {
        return [
            'timestamp' => time(),
            'server_load' => $this->getServerLoad(),
            'memory_usage' => memory_get_peak_usage(true),
            'load_time' => $this->getLoadTime(),
            'error_count' => $this->getErrorCount(),
            'db_queries' => $this->getDbQueryCount()
        ];
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
     * Ottiene tempo di caricamento
     */
    private function getLoadTime(): float
    {
        return microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    }

    /**
     * Ottiene conteggio errori
     */
    private function getErrorCount(): int
    {
        return count(error_get_last() ? [error_get_last()] : []);
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
     * Ottiene le impostazioni
     */
    private function settings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'confidence_threshold' => 0.7,
            'z_score_threshold' => 2.0,
            'min_data_points' => 10,
            'detection_window_hours' => 24
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
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // AnomalyDetector non ha hook specifici da registrare
        // È utilizzato principalmente per analisi on-demand
    }
}