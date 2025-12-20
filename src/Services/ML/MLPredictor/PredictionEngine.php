<?php

namespace FP\PerfSuite\Services\ML\MLPredictor;

/**
 * Genera predizioni basate su pattern e dati attuali
 * 
 * @package FP\PerfSuite\Services\ML\MLPredictor
 * @author Francesco Passeri
 */
class PredictionEngine
{
    private DataStorage $dataStorage;

    public function __construct(DataStorage $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * Genera predizioni basate su pattern e dati attuali
     */
    public function generatePredictions(array $patterns, array $current_data): array
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
        $recent_loads = array_column(array_slice($this->dataStorage->getStoredData(), -10), 'server_load');
        $recentLoadsCount = count($recent_loads);
        $avg_load = $recentLoadsCount > 0 ? array_sum($recent_loads) / $recentLoadsCount : 0;
        
        return $avg_load > 0.8 && ($current_data['server_load'] ?? 0) > 0.7;
    }

    /**
     * Predice problemi di memoria
     */
    private function predictMemoryIssues(array $patterns, array $current_data): bool
    {
        $memory_usage = $current_data['memory_usage'] ?? 0;
        $memory_limit = ini_get('memory_limit');
        
        // Converti memory_limit in bytes
        $limit_bytes = $this->convertToBytes($memory_limit);
        
        return $memory_usage > ($limit_bytes * 0.8);
    }

    /**
     * Predice errori
     */
    private function predictErrors(array $patterns, array $current_data): bool
    {
        $recent_errors = array_column(array_slice($this->dataStorage->getStoredData(), -10), 'error_count');
        $recentErrorsCount = count($recent_errors);
        $avg_errors = $recentErrorsCount > 0 ? array_sum($recent_errors) / $recentErrorsCount : 0;
        
        return $avg_errors > 5 && ($current_data['error_count'] ?? 0) > 3;
    }

    /**
     * Converte memory limit string in bytes
     */
    private function convertToBytes(string $val): int
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        
        switch ($last) {
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
        }
        
        return $val;
    }
}
















