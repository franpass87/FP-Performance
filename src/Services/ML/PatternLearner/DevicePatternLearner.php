<?php

namespace FP\PerfSuite\Services\ML\PatternLearner;

/**
 * Apprende pattern per dispositivo
 * 
 * @package FP\PerfSuite\Services\ML\PatternLearner
 * @author Francesco Passeri
 */
class DevicePatternLearner
{
    private StatisticsCalculator $calculator;

    public function __construct(StatisticsCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Apprende pattern per dispositivo
     */
    public function learn(array $data): array
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
                    'confidence' => $this->calculator->calculateConfidence($device_differences),
                    'data' => $device_differences,
                    'recommended_action' => 'Ottimizzare specificamente per dispositivo con performance peggiori'
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
        
        $mobileCount = count($mobile_load_times);
        $desktopCount = count($desktop_load_times);
        
        $mobile_avg = $mobileCount > 0 ? array_sum($mobile_load_times) / $mobileCount : 0;
        $desktop_avg = $desktopCount > 0 ? array_sum($desktop_load_times) / $desktopCount : 0;
        
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
}















