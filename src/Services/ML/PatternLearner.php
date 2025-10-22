<?php

namespace FP\PerfSuite\Services\ML;

use FP\PerfSuite\Utils\Logger;

/**
 * Pattern Learner Service
 * 
 * Apprende pattern dai dati di performance per migliorare predizioni
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PatternLearner
{
    private const OPTION = 'fp_ps_pattern_learner';

    /**
     * Apprende pattern dai dati di performance
     */
    public function learnPatterns(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $patterns = [];
        
        // Pattern di carico
        $patterns = array_merge($patterns, $this->learnLoadPatterns($data));
        
        // Pattern di memoria
        $patterns = array_merge($patterns, $this->learnMemoryPatterns($data));
        
        // Pattern di errori
        $patterns = array_merge($patterns, $this->learnErrorPatterns($data));
        
        // Pattern temporali
        $patterns = array_merge($patterns, $this->learnTemporalPatterns($data));
        
        // Pattern per dispositivo
        $patterns = array_merge($patterns, $this->learnDevicePatterns($data));
        
        // Salva pattern appresi
        $this->saveLearnedPatterns($patterns);
        
        Logger::info('Patterns learned', ['patterns_count' => count($patterns)]);
        
        return $patterns;
    }

    /**
     * Apprende pattern di carico
     */
    private function learnLoadPatterns(array $data): array
    {
        $patterns = [];
        
        // Calcola statistiche di carico
        $loads = array_column($data, 'server_load');
        $avg_load = array_sum($loads) / count($loads);
        $max_load = max($loads);
        $min_load = min($loads);
        
        // Pattern: carico elevato in orari specifici
        $high_load_times = $this->findHighLoadTimes($data);
        if (!empty($high_load_times)) {
            $patterns[] = [
                'type' => 'high_load_times',
                'description' => 'Carico elevato rilevato in orari specifici',
                'confidence' => $this->calculateConfidence($high_load_times),
                'data' => $high_load_times,
                'recommended_action' => 'Implementare cache aggiuntiva negli orari critici'
            ];
        }
        
        // Pattern: correlazione tra plugin e carico
        $plugin_load_correlation = $this->findPluginLoadCorrelation($data);
        if (!empty($plugin_load_correlation)) {
            $patterns[] = [
                'type' => 'plugin_load_correlation',
                'description' => 'Correlazione tra plugin attivi e carico server',
                'confidence' => $this->calculateConfidence($plugin_load_correlation),
                'data' => $plugin_load_correlation,
                'recommended_action' => 'Ottimizzare plugin che causano carico elevato'
            ];
        }
        
        return $patterns;
    }

    /**
     * Apprende pattern di memoria
     */
    private function learnMemoryPatterns(array $data): array
    {
        $patterns = [];
        
        // Calcola statistiche di memoria
        $memory_usage = array_column($data, 'memory_usage');
        $avg_memory = array_sum($memory_usage) / count($memory_usage);
        $max_memory = max($memory_usage);
        
        // Pattern: crescita memoria nel tempo
        $memory_growth = $this->detectMemoryGrowth($data);
        if ($memory_growth['trend'] === 'increasing') {
            $patterns[] = [
                'type' => 'memory_growth',
                'description' => 'Crescita costante dell\'uso di memoria',
                'confidence' => $memory_growth['confidence'],
                'data' => $memory_growth,
                'recommended_action' => 'Implementare garbage collection e ottimizzazioni memoria'
            ];
        }
        
        // Pattern: picchi di memoria
        $memory_spikes = $this->detectMemorySpikes($data);
        if (!empty($memory_spikes)) {
            $patterns[] = [
                'type' => 'memory_spikes',
                'description' => 'Picchi di memoria rilevati',
                'confidence' => $this->calculateConfidence($memory_spikes),
                'data' => $memory_spikes,
                'recommended_action' => 'Identificare e ottimizzare operazioni che causano picchi'
            ];
        }
        
        return $patterns;
    }

    /**
     * Apprende pattern di errori
     */
    private function learnErrorPatterns(array $data): array
    {
        $patterns = [];
        
        // Calcola statistiche di errori
        $errors = array_column($data, 'error_count');
        $total_errors = array_sum($errors);
        $avg_errors = $total_errors / count($errors);
        
        if ($avg_errors > 0) {
            // Pattern: errori ricorrenti
            $recurring_errors = $this->findRecurringErrors($data);
            if (!empty($recurring_errors)) {
                $patterns[] = [
                    'type' => 'recurring_errors',
                    'description' => 'Errori ricorrenti rilevati',
                    'confidence' => $this->calculateConfidence($recurring_errors),
                    'data' => $recurring_errors,
                    'recommended_action' => 'Risolvere cause comuni degli errori'
                ];
            }
            
            // Pattern: correlazione errori e carico
            $error_load_correlation = $this->findErrorLoadCorrelation($data);
            if ($error_load_correlation['correlation'] > 0.7) {
                $patterns[] = [
                    'type' => 'error_load_correlation',
                    'description' => 'Correlazione tra errori e carico server',
                    'confidence' => $error_load_correlation['correlation'],
                    'data' => $error_load_correlation,
                    'recommended_action' => 'Ridurre carico per diminuire errori'
                ];
            }
        }
        
        return $patterns;
    }

    /**
     * Apprende pattern temporali
     */
    private function learnTemporalPatterns(array $data): array
    {
        $patterns = [];
        
        // Pattern: performance per giorno della settimana
        $daily_patterns = $this->analyzeDailyPatterns($data);
        if (!empty($daily_patterns)) {
            $patterns[] = [
                'type' => 'daily_patterns',
                'description' => 'Pattern di performance per giorno della settimana',
                'confidence' => $this->calculateConfidence($daily_patterns),
                'data' => $daily_patterns,
                'recommended_action' => 'Ottimizzare per giorni con performance peggiori'
            ];
        }
        
        // Pattern: performance per ora del giorno
        $hourly_patterns = $this->analyzeHourlyPatterns($data);
        if (!empty($hourly_patterns)) {
            $patterns[] = [
                'type' => 'hourly_patterns',
                'description' => 'Pattern di performance per ora del giorno',
                'confidence' => $this->calculateConfidence($hourly_patterns),
                'data' => $hourly_patterns,
                'recommended_action' => 'Implementare cache dinamica per orari critici'
            ];
        }
        
        return $patterns;
    }

    /**
     * Apprende pattern per dispositivo
     */
    private function learnDevicePatterns(array $data): array
    {
        $patterns = [];
        
        // Separa dati per dispositivo
        $mobile_data = array_filter($data, fn($d) => !empty($d['is_mobile']));
        $desktop_data = array_filter($data, fn($d) => empty($d['is_mobile']));
        
        if (!empty($mobile_data) && !empty($desktop_data)) {
            // Pattern: differenze performance mobile vs desktop
            $device_differences = $this->analyzeDeviceDifferences($mobile_data, $desktop_data);
            if (!empty($device_differences)) {
                $patterns[] = [
                    'type' => 'device_differences',
                    'description' => 'Differenze significative tra mobile e desktop',
                    'confidence' => $this->calculateConfidence($device_differences),
                    'data' => $device_differences,
                    'recommended_action' => 'Ottimizzare specificamente per dispositivo con performance peggiori'
                ];
            }
        }
        
        return $patterns;
    }

    /**
     * Trova orari con carico elevato
     */
    private function findHighLoadTimes(array $data): array
    {
        $high_load_times = [];
        
        foreach ($data as $point) {
            if ($point['server_load'] > 0.8) {
                $hour = date('H', $point['timestamp']);
                $high_load_times[$hour] = ($high_load_times[$hour] ?? 0) + 1;
            }
        }
        
        // Filtra solo orari con almeno 3 occorrenze
        return array_filter($high_load_times, fn($count) => $count >= 3);
    }

    /**
     * Trova correlazione tra plugin e carico
     */
    private function findPluginLoadCorrelation(array $data): array
    {
        $correlations = [];
        
        // Raggruppa per numero di plugin attivi
        $plugin_groups = [];
        foreach ($data as $point) {
            $plugin_count = $point['active_plugins'] ?? 0;
            $plugin_groups[$plugin_count][] = $point['server_load'];
        }
        
        // Calcola correlazione
        foreach ($plugin_groups as $plugin_count => $loads) {
            if (count($loads) >= 5) { // Almeno 5 punti dati
                $avg_load = array_sum($loads) / count($loads);
                $correlations[$plugin_count] = $avg_load;
            }
        }
        
        return $correlations;
    }

    /**
     * Rileva crescita memoria
     */
    private function detectMemoryGrowth(array $data): array
    {
        $memory_usage = array_column($data, 'memory_usage');
        $timestamps = array_column($data, 'timestamp');
        
        // Calcola trend lineare
        $trend = $this->calculateLinearTrend($timestamps, $memory_usage);
        
        return [
            'trend' => $trend['slope'] > 0 ? 'increasing' : ($trend['slope'] < 0 ? 'decreasing' : 'stable'),
            'slope' => $trend['slope'],
            'confidence' => $trend['r_squared'],
            'data_points' => count($memory_usage)
        ];
    }

    /**
     * Rileva picchi di memoria
     */
    private function detectMemorySpikes(array $data): array
    {
        $memory_usage = array_column($data, 'memory_usage');
        $avg_memory = array_sum($memory_usage) / count($memory_usage);
        $std_dev = $this->calculateStandardDeviation($memory_usage);
        
        $spikes = [];
        foreach ($data as $point) {
            if ($point['memory_usage'] > $avg_memory + (2 * $std_dev)) {
                $spikes[] = [
                    'timestamp' => $point['timestamp'],
                    'memory_usage' => $point['memory_usage'],
                    'severity' => ($point['memory_usage'] - $avg_memory) / $std_dev
                ];
            }
        }
        
        return $spikes;
    }

    /**
     * Trova errori ricorrenti
     */
    private function findRecurringErrors(array $data): array
    {
        $error_times = [];
        
        foreach ($data as $point) {
            if ($point['error_count'] > 0) {
                $hour = date('H', $point['timestamp']);
                $error_times[$hour] = ($error_times[$hour] ?? 0) + $point['error_count'];
            }
        }
        
        // Filtra solo orari con errori frequenti
        return array_filter($error_times, fn($count) => $count >= 3);
    }

    /**
     * Trova correlazione errori e carico
     */
    private function findErrorLoadCorrelation(array $data): array
    {
        $errors = array_column($data, 'error_count');
        $loads = array_column($data, 'server_load');
        
        $correlation = $this->calculateCorrelation($errors, $loads);
        
        return [
            'correlation' => $correlation,
            'data_points' => count($data)
        ];
    }

    /**
     * Analizza pattern giornalieri
     */
    private function analyzeDailyPatterns(array $data): array
    {
        $daily_performance = [];
        
        foreach ($data as $point) {
            $day = date('N', $point['timestamp']); // 1 = Monday, 7 = Sunday
            $daily_performance[$day][] = $point['load_time'];
        }
        
        $patterns = [];
        foreach ($daily_performance as $day => $times) {
            if (count($times) >= 3) {
                $patterns[$day] = [
                    'avg_load_time' => array_sum($times) / count($times),
                    'samples' => count($times)
                ];
            }
        }
        
        return $patterns;
    }

    /**
     * Analizza pattern orari
     */
    private function analyzeHourlyPatterns(array $data): array
    {
        $hourly_performance = [];
        
        foreach ($data as $point) {
            $hour = date('H', $point['timestamp']);
            $hourly_performance[$hour][] = $point['load_time'];
        }
        
        $patterns = [];
        foreach ($hourly_performance as $hour => $times) {
            if (count($times) >= 5) {
                $patterns[$hour] = [
                    'avg_load_time' => array_sum($times) / count($times),
                    'samples' => count($times)
                ];
            }
        }
        
        return $patterns;
    }

    /**
     * Analizza differenze tra dispositivi
     */
    private function analyzeDeviceDifferences(array $mobile_data, array $desktop_data): array
    {
        $mobile_load_times = array_column($mobile_data, 'load_time');
        $desktop_load_times = array_column($desktop_data, 'load_time');
        
        $mobile_avg = array_sum($mobile_load_times) / count($mobile_load_times);
        $desktop_avg = array_sum($desktop_load_times) / count($desktop_load_times);
        
        $difference = abs($mobile_avg - $desktop_avg);
        $threshold = max($mobile_avg, $desktop_avg) * 0.2; // 20% di differenza
        
        if ($difference > $threshold) {
            return [
                'mobile_avg' => $mobile_avg,
                'desktop_avg' => $desktop_avg,
                'difference' => $difference,
                'mobile_samples' => count($mobile_load_times),
                'desktop_samples' => count($desktop_load_times)
            ];
        }
        
        return [];
    }

    /**
     * Calcola trend lineare
     */
    private function calculateLinearTrend(array $x, array $y): array
    {
        $n = count($x);
        if ($n < 2) {
            return ['slope' => 0, 'r_squared' => 0];
        }
        
        $sum_x = array_sum($x);
        $sum_y = array_sum($y);
        $sum_xy = 0;
        $sum_x2 = 0;
        $sum_y2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sum_xy += $x[$i] * $y[$i];
            $sum_x2 += $x[$i] * $x[$i];
            $sum_y2 += $y[$i] * $y[$i];
        }
        
        $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
        $intercept = ($sum_y - $slope * $sum_x) / $n;
        
        // Calcola R-squared
        $y_mean = $sum_y / $n;
        $ss_tot = 0;
        $ss_res = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $y_pred = $slope * $x[$i] + $intercept;
            $ss_tot += pow($y[$i] - $y_mean, 2);
            $ss_res += pow($y[$i] - $y_pred, 2);
        }
        
        $r_squared = 1 - ($ss_res / $ss_tot);
        
        return [
            'slope' => $slope,
            'intercept' => $intercept,
            'r_squared' => max(0, $r_squared)
        ];
    }

    /**
     * Calcola deviazione standard
     */
    private function calculateStandardDeviation(array $values): float
    {
        $n = count($values);
        if ($n < 2) {
            return 0;
        }
        
        $mean = array_sum($values) / $n;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / ($n - 1);
        
        return sqrt($variance);
    }

    /**
     * Calcola correlazione
     */
    private function calculateCorrelation(array $x, array $y): float
    {
        $n = count($x);
        if ($n < 2) {
            return 0;
        }
        
        $sum_x = array_sum($x);
        $sum_y = array_sum($y);
        $sum_xy = 0;
        $sum_x2 = 0;
        $sum_y2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sum_xy += $x[$i] * $y[$i];
            $sum_x2 += $x[$i] * $x[$i];
            $sum_y2 += $y[$i] * $y[$i];
        }
        
        $numerator = $n * $sum_xy - $sum_x * $sum_y;
        $denominator = sqrt(($n * $sum_x2 - $sum_x * $sum_x) * ($n * $sum_y2 - $sum_y * $sum_y));
        
        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    /**
     * Calcola confidence score
     */
    private function calculateConfidence(array $data): float
    {
        $count = count($data);
        
        if ($count < 3) {
            return 0.3;
        } elseif ($count < 10) {
            return 0.6;
        } elseif ($count < 20) {
            return 0.8;
        } else {
            return 0.9;
        }
    }

    /**
     * Salva pattern appresi
     */
    private function saveLearnedPatterns(array $patterns): void
    {
        update_option('fp_ps_ml_patterns', $patterns);
    }

    /**
     * Ottiene le impostazioni
     */
    private function settings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => true,
            'min_data_points' => 10,
            'confidence_threshold' => 0.7,
            'pattern_retention_days' => 30
        ]);
    }
}