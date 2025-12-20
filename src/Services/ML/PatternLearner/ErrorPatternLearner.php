<?php

namespace FP\PerfSuite\Services\ML\PatternLearner;

/**
 * Apprende pattern di errori
 * 
 * @package FP\PerfSuite\Services\ML\PatternLearner
 * @author Francesco Passeri
 */
class ErrorPatternLearner
{
    private StatisticsCalculator $calculator;

    public function __construct(StatisticsCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Apprende pattern di errori
     */
    public function learn(array $data): array
    {
        $patterns = [];
        
        // Calcola statistiche di errori
        $errors = array_column($data, 'error_count');
        $total_errors = array_sum($errors);
        $errorsCount = count($errors);
        $avg_errors = $errorsCount > 0 ? $total_errors / $errorsCount : 0;
        
        if ($avg_errors > 0) {
            // Pattern: errori ricorrenti
            $recurring_errors = $this->findRecurringErrors($data);
            if (!empty($recurring_errors)) {
                $patterns[] = [
                    'type' => 'recurring_errors',
                    'description' => 'Errori ricorrenti rilevati',
                    'confidence' => $this->calculator->calculateConfidence($recurring_errors),
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
        return array_filter($error_times, fn($count) => $count >= 5);
    }

    /**
     * Trova correlazione tra errori e carico
     */
    private function findErrorLoadCorrelation(array $data): array
    {
        $errors = array_column($data, 'error_count');
        $loads = array_column($data, 'server_load');
        
        $correlation = $this->calculator->calculateCorrelation($loads, $errors);
        
        return [
            'correlation' => $correlation,
            'data_points' => count($data)
        ];
    }
}















