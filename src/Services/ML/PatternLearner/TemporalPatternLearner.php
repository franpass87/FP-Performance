<?php

namespace FP\PerfSuite\Services\ML\PatternLearner;

/**
 * Apprende pattern temporali
 * 
 * @package FP\PerfSuite\Services\ML\PatternLearner
 * @author Francesco Passeri
 */
class TemporalPatternLearner
{
    private StatisticsCalculator $calculator;

    public function __construct(StatisticsCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Apprende pattern temporali
     */
    public function learn(array $data): array
    {
        $patterns = [];
        
        // Pattern: performance per giorno della settimana
        $daily_patterns = $this->analyzeDailyPatterns($data);
        if (!empty($daily_patterns)) {
            $patterns[] = [
                'type' => 'daily_patterns',
                'description' => 'Pattern di performance per giorno della settimana',
                'confidence' => $this->calculator->calculateConfidence($daily_patterns),
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
                'confidence' => $this->calculator->calculateConfidence($hourly_patterns),
                'data' => $hourly_patterns,
                'recommended_action' => 'Implementare cache dinamica per orari critici'
            ];
        }
        
        return $patterns;
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
            $timesCount = count($times);
            if ($timesCount >= 3) {
                $patterns[$day] = [
                    'avg_load_time' => array_sum($times) / $timesCount,
                    'samples' => $timesCount
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
            $timesCount = count($times);
            if ($timesCount >= 5) {
                $patterns[$hour] = [
                    'avg_load_time' => array_sum($times) / $timesCount,
                    'samples' => $timesCount
                ];
            }
        }
        
        return $patterns;
    }
}















