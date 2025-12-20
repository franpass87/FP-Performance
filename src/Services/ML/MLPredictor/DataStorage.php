<?php

namespace FP\PerfSuite\Services\ML\MLPredictor;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

use function serialize;
use function strlen;
use function time;
use function array_filter;
use function array_values;
use function count;
use function array_slice;

/**
 * Gestisce lo storage dei dati ML
 * 
 * @package FP\PerfSuite\Services\ML\MLPredictor
 * @author Francesco Passeri
 */
class DataStorage
{
    private const DATA_OPTION = 'fp_ps_ml_data';
    private const PATTERNS_OPTION = 'fp_ps_ml_patterns';
    private const PREDICTIONS_OPTION = 'fp_ps_ml_predictions';
    private const MAX_STORAGE_MB = 50;
    private const MAX_DATA_POINTS = 5000;
    private const DATA_RETENTION_DAYS_DEFAULT = 30;
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Memorizza dati di performance
     * 
     * RESOURCE LIMIT FIX: Rispetta limiti configurati
     */
    public function storePerformanceData(array $data, array $settings): void
    {
        $stored_data = $this->getStoredData();
        
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
        
        $this->setOption(self::DATA_OPTION, $stored_data); // No autoload
    }

    /**
     * Ottiene dati memorizzati
     */
    public function getStoredData(): array
    {
        return $this->getOption(self::DATA_OPTION, []);
    }

    /**
     * Ottiene dati attuali (ultimo punto)
     */
    public function getCurrentData(): array
    {
        $stored_data = $this->getStoredData();
        return !empty($stored_data) ? end($stored_data) : [];
    }

    /**
     * Ottiene dati storici (ultimi N punti)
     */
    public function getHistoricalData(int $limit = 100): array
    {
        $stored_data = $this->getStoredData();
        return array_slice($stored_data, -$limit);
    }

    /**
     * Ottiene pattern appresi
     */
    public function getLearnedPatterns(): array
    {
        return $this->getOption(self::PATTERNS_OPTION, []);
    }

    /**
     * Aggiorna pattern appresi
     */
    public function updateLearnedPatterns(array $patterns): void
    {
        $this->setOption(self::PATTERNS_OPTION, $patterns);
    }

    /**
     * Memorizza predizioni
     */
    public function storePredictions(array $predictions): void
    {
        $this->setOption(self::PREDICTIONS_OPTION, $predictions);
    }

    /**
     * Ottiene predizioni memorizzate
     */
    public function getStoredPredictions(): array
    {
        return $this->getOption(self::PREDICTIONS_OPTION, []);
    }

    /**
     * RESOURCE LIMIT: Verifica se la quota storage è stata superata
     * 
     * @return bool True se quota superata
     */
    public function isStorageQuotaExceeded(array $settings): bool
    {
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
    public function getCurrentStorageSize(): int
    {
        $stored_data = $this->getStoredData();
        $patterns = $this->getLearnedPatterns();
        $predictions = $this->getStoredPredictions();
        
        $totalSize = 0;
        $totalSize += strlen(serialize($stored_data));
        $totalSize += strlen(serialize($patterns));
        $totalSize += strlen(serialize($predictions));
        
        return $totalSize;
    }
    
    /**
     * RESOURCE LIMIT: Pulisce dati vecchi oltre retention period
     */
    public function cleanupOldData(array $settings): int
    {
        $retentionDays = $settings['data_retention_days'] ?? self::DATA_RETENTION_DAYS_DEFAULT;
        
        $stored_data = $this->getStoredData();
        $cutoffTime = time() - ($retentionDays * DAY_IN_SECONDS);
        
        $originalCount = count($stored_data);
        
        // Filtra dati più vecchi del retention period
        $filtered_data = array_filter($stored_data, function($entry) use ($cutoffTime) {
            return isset($entry['timestamp']) && $entry['timestamp'] >= $cutoffTime;
        });
        
        $filtered_data = array_values($filtered_data); // Re-index
        
        $removed = $originalCount - count($filtered_data);
        
        if ($removed > 0) {
            $this->setOption(self::DATA_OPTION, $filtered_data);
            
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
    public function truncateData(array $data): array
    {
        // Rimuovi campi non essenziali
        $essential = [
            'timestamp',
            'url',
            'load_time',
            'memory_usage',
            'db_queries',
            'cache_hits',
            'cache_misses',
            'server_load',
            'error_count'
        ];
        
        $truncated = [];
        foreach ($essential as $key) {
            if (isset($data[$key])) {
                $truncated[$key] = $data[$key];
            }
        }
        
        return $truncated;
    }

    /**
     * Ottiene statistiche storage
     */
    public function getStorageStats(): array
    {
        $stored_data = $this->getStoredData();
        $sizeBytes = $this->getCurrentStorageSize();
        
        return [
            'data_points' => count($stored_data),
            'storage_size_mb' => round($sizeBytes / 1048576, 2),
            'storage_size_bytes' => $sizeBytes
        ];
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }
}
















