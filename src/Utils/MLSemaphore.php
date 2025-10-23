<?php

namespace FP\PerfSuite\Utils;

/**
 * ML Semaphore Utility
 * 
 * Gestisce semafori per operazioni ML/AI per prevenire race conditions
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class MLSemaphore
{
    private const SEMAPHORE_PREFIX = 'fp_ps_ml_sem_';
    private const LOCK_TIMEOUT = 30; // 30 secondi timeout
    
    /**
     * Acquisisce semaforo per operazione ML
     * 
     * @param string $operation Nome operazione (es: 'pattern_learning', 'anomaly_detection')
     * @param int $timeout Timeout in secondi (default: 30)
     * @return bool True se acquisito, false se timeout o errore
     */
    public static function acquire(string $operation, int $timeout = self::LOCK_TIMEOUT): bool
    {
        $semaphoreKey = self::SEMAPHORE_PREFIX . sanitize_key($operation);
        $lockFile = sys_get_temp_dir() . '/' . $semaphoreKey . '.lock';
        
        $startTime = time();
        
        while (time() - $startTime < $timeout) {
            $lock = fopen($lockFile, 'c+');
            
            if (!$lock) {
                Logger::error('Failed to create ML semaphore lock file', [
                    'operation' => $operation,
                    'lockFile' => $lockFile
                ]);
                return false;
            }
            
            // Try to acquire exclusive lock (non-blocking)
            if (flock($lock, LOCK_EX | LOCK_NB)) {
                // Write PID and timestamp to lock file
                fwrite($lock, json_encode([
                    'pid' => getmypid(),
                    'timestamp' => time(),
                    'operation' => $operation
                ]));
                fflush($lock);
                
                // Store lock resource for later release
                self::storeLockResource($operation, $lock);
                
                Logger::debug('ML semaphore acquired', [
                    'operation' => $operation,
                    'pid' => getmypid()
                ]);
                
                return true;
            }
            
            fclose($lock);
            
            // Check if lock file is stale (older than 5 minutes)
            if (file_exists($lockFile)) {
                $lockData = @json_decode(@file_get_contents($lockFile), true);
                if ($lockData && isset($lockData['timestamp'])) {
                    $age = time() - $lockData['timestamp'];
                    if ($age > 300) { // 5 minutes
                        @unlink($lockFile);
                        Logger::warning('Removed stale ML semaphore lock', [
                            'operation' => $operation,
                            'age' => $age
                        ]);
                        continue;
                    }
                }
            }
            
            // Wait 100ms before retry
            usleep(100000);
        }
        
        Logger::warning('ML semaphore acquisition timeout', [
            'operation' => $operation,
            'timeout' => $timeout
        ]);
        
        return false;
    }
    
    /**
     * Rilascia semaforo ML
     * 
     * @param string $operation Nome operazione
     * @return bool True se rilasciato correttamente
     */
    public static function release(string $operation): bool
    {
        $semaphoreKey = self::SEMAPHORE_PREFIX . sanitize_key($operation);
        $lockFile = sys_get_temp_dir() . '/' . $semaphoreKey . '.lock';
        
        $lock = self::getLockResource($operation);
        
        if ($lock) {
            flock($lock, LOCK_UN);
            fclose($lock);
            self::removeLockResource($operation);
        }
        
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }
        
        Logger::debug('ML semaphore released', ['operation' => $operation]);
        return true;
    }
    
    /**
     * Controlla se semaforo è acquisito
     * 
     * @param string $operation Nome operazione
     * @return bool True se acquisito
     */
    public static function isAcquired(string $operation): bool
    {
        $semaphoreKey = self::SEMAPHORE_PREFIX . sanitize_key($operation);
        $lockFile = sys_get_temp_dir() . '/' . $semaphoreKey . '.lock';
        
        return file_exists($lockFile);
    }
    
    /**
     * Ottiene informazioni sul semaforo
     * 
     * @param string $operation Nome operazione
     * @return array|null Informazioni semaforo o null se non acquisito
     */
    public static function getInfo(string $operation): ?array
    {
        $semaphoreKey = self::SEMAPHORE_PREFIX . sanitize_key($operation);
        $lockFile = sys_get_temp_dir() . '/' . $semaphoreKey . '.lock';
        
        if (!file_exists($lockFile)) {
            return null;
        }
        
        $data = @json_decode(@file_get_contents($lockFile), true);
        return $data ?: null;
    }
    
    /**
     * Pulisce tutti i semafori ML
     * 
     * @return int Numero di semafori puliti
     */
    public static function cleanupAll(): int
    {
        $tempDir = sys_get_temp_dir();
        $pattern = $tempDir . '/' . self::SEMAPHORE_PREFIX . '*.lock';
        $files = glob($pattern);
        
        $cleaned = 0;
        foreach ($files as $file) {
            if (@unlink($file)) {
                $cleaned++;
            }
        }
        
        Logger::info('ML semaphores cleaned up', ['count' => $cleaned]);
        return $cleaned;
    }
    
    /**
     * Store lock resource for later release
     */
    private static function storeLockResource(string $operation, $lock): void
    {
        static $lockResources = [];
        $lockResources[$operation] = $lock;
    }
    
    /**
     * Get stored lock resource
     */
    private static function getLockResource(string $operation)
    {
        static $lockResources = [];
        return $lockResources[$operation] ?? null;
    }
    
    /**
     * Remove stored lock resource
     */
    private static function removeLockResource(string $operation): void
    {
        static $lockResources = [];
        unset($lockResources[$operation]);
    }
}
