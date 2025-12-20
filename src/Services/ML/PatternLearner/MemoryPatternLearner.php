<?php

namespace FP\PerfSuite\Services\ML\PatternLearner;

/**
 * Apprende pattern di memoria
 * 
 * @package FP\PerfSuite\Services\ML\PatternLearner
 * @author Francesco Passeri
 */
class MemoryPatternLearner
{
    private StatisticsCalculator $calculator;

    public function __construct(StatisticsCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Apprende pattern di memoria
     */
    public function learn(array $data): array
    {
        $patterns = [];
        
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
                'confidence' => $this->calculator->calculateConfidence($memory_spikes),
                'data' => $memory_spikes,
                'recommended_action' => 'Identificare e ottimizzare operazioni che causano picchi'
            ];
        }
        
        return $patterns;
    }

    /**
     * Rileva crescita memoria
     */
    private function detectMemoryGrowth(array $data): array
    {
        $memory_usage = array_column($data, 'memory_usage');
        $timestamps = array_column($data, 'timestamp');
        
        // Calcola trend lineare
        $trend = $this->calculator->calculateLinearTrend($timestamps, $memory_usage);
        
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
        $memUsageCount = count($memory_usage);
        $avg_memory = $memUsageCount > 0 ? array_sum($memory_usage) / $memUsageCount : 0;
        $std_dev = $this->calculator->calculateStandardDeviation($memory_usage);
        
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
}















