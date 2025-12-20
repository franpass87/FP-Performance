<?php

namespace FP\PerfSuite\Services\ML\PatternLearner;

/**
 * Calcoli statistici per Pattern Learner
 * 
 * @package FP\PerfSuite\Services\ML\PatternLearner
 * @author Francesco Passeri
 */
class StatisticsCalculator
{
    /**
     * Calcola trend lineare
     */
    public function calculateLinearTrend(array $x, array $y): array
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
    public function calculateStandardDeviation(array $values): float
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
    public function calculateCorrelation(array $x, array $y): float
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
    public function calculateConfidence(array $data): float
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
}















