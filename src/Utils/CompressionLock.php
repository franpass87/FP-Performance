<?php

namespace FP\PerfSuite\Utils;

/**
 * Compression Lock Utility
 * 
 * Gestisce file locks per operazioni di compressione per prevenire corruzione
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CompressionLock
{
    private const LOCK_PREFIX = 'fp_ps_compression_lock_';
    private const LOCK_TIMEOUT = 60; // 60 secondi timeout
    
    /**
     * Acquisisce lock per operazione di compressione
     * 
     * @param string $operation Tipo operazione (es: 'image_compress', 'media_optimize')
     * @param string $filePath Path del file da processare
     * @param int $timeout Timeout in secondi (default: 60)
     * @return resource|false Lock resource o false se fallito
     */
    public static function acquire(string $operation, string $filePath, int $timeout = self::LOCK_TIMEOUT)
    {
        $lockKey = self::LOCK_PREFIX . sanitize_key($operation) . '_' . md5($filePath);
        $lockFile = sys_get_temp_dir() . '/' . $lockKey . '.lock';
        
        $startTime = time();
        
        while (time() - $startTime < $timeout) {
            $lock = fopen($lockFile, 'c+');
            
            if (!$lock) {
                Logger::error('Failed to create compression lock file', [
                    'operation' => $operation,
                    'filePath' => $filePath,
                    'lockFile' => $lockFile
                ]);
                return false;
            }
            
            // Try to acquire exclusive lock (non-blocking)
            if (flock($lock, LOCK_EX | LOCK_NB)) {
                // Write lock info
                fwrite($lock, json_encode([
                    'pid' => getmypid(),
                    'timestamp' => time(),
                    'operation' => $operation,
                    'filePath' => $filePath
                ]));
                fflush($lock);
                
                Logger::debug('Compression lock acquired', [
                    'operation' => $operation,
                    'filePath' => basename($filePath),
                    'pid' => getmypid()
                ]);
                
                return $lock;
            }
            
            fclose($lock);
            
            // Check if lock file is stale (older than 5 minutes)
            if (file_exists($lockFile)) {
                $lockData = @json_decode(@file_get_contents($lockFile), true);
                if ($lockData && isset($lockData['timestamp'])) {
                    $age = time() - $lockData['timestamp'];
                    if ($age > 300) { // 5 minutes
                        @unlink($lockFile);
                        Logger::warning('Removed stale compression lock', [
                            'operation' => $operation,
                            'filePath' => basename($filePath),
                            'age' => $age
                        ]);
                        continue;
                    }
                }
            }
            
            // Wait 100ms before retry
            usleep(100000);
        }
        
        Logger::warning('Compression lock acquisition timeout', [
            'operation' => $operation,
            'filePath' => basename($filePath),
            'timeout' => $timeout
        ]);
        
        return false;
    }
    
    /**
     * Rilascia lock di compressione
     * 
     * @param resource $lock Lock resource
     * @param string $operation Tipo operazione
     * @param string $filePath Path del file
     * @return bool True se rilasciato correttamente
     */
    public static function release($lock, string $operation, string $filePath): bool
    {
        if (!$lock || !is_resource($lock)) {
            return false;
        }
        
        $lockKey = self::LOCK_PREFIX . sanitize_key($operation) . '_' . md5($filePath);
        $lockFile = sys_get_temp_dir() . '/' . $lockKey . '.lock';
        
        flock($lock, LOCK_UN);
        fclose($lock);
        
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }
        
        Logger::debug('Compression lock released', [
            'operation' => $operation,
            'filePath' => basename($filePath)
        ]);
        
        return true;
    }
    
    /**
     * Controlla se file è bloccato per compressione
     * 
     * @param string $operation Tipo operazione
     * @param string $filePath Path del file
     * @return bool True se bloccato
     */
    public static function isLocked(string $operation, string $filePath): bool
    {
        $lockKey = self::LOCK_PREFIX . sanitize_key($operation) . '_' . md5($filePath);
        $lockFile = sys_get_temp_dir() . '/' . $lockKey . '.lock';
        
        return file_exists($lockFile);
    }
    
    /**
     * Ottiene informazioni sul lock
     * 
     * @param string $operation Tipo operazione
     * @param string $filePath Path del file
     * @return array|null Informazioni lock o null se non bloccato
     */
    public static function getLockInfo(string $operation, string $filePath): ?array
    {
        $lockKey = self::LOCK_PREFIX . sanitize_key($operation) . '_' . md5($filePath);
        $lockFile = sys_get_temp_dir() . '/' . $lockKey . '.lock';
        
        if (!file_exists($lockFile)) {
            return null;
        }
        
        $data = @json_decode(@file_get_contents($lockFile), true);
        return $data ?: null;
    }
    
    /**
     * Pulisce tutti i lock di compressione
     * 
     * @return int Numero di lock puliti
     */
    public static function cleanupAll(): int
    {
        $tempDir = sys_get_temp_dir();
        $pattern = $tempDir . '/' . self::LOCK_PREFIX . '*.lock';
        $files = glob($pattern);
        
        $cleaned = 0;
        foreach ($files as $file) {
            if (@unlink($file)) {
                $cleaned++;
            }
        }
        
        Logger::info('Compression locks cleaned up', ['count' => $cleaned]);
        return $cleaned;
    }
    
    /**
     * Pulisce lock scaduti (più vecchi di 5 minuti)
     * 
     * @return int Numero di lock puliti
     */
    public static function cleanupStale(): int
    {
        $tempDir = sys_get_temp_dir();
        $pattern = $tempDir . '/' . self::LOCK_PREFIX . '*.lock';
        $files = glob($pattern);
        
        $cleaned = 0;
        $cutoff = time() - 300; // 5 minutes ago
        
        foreach ($files as $file) {
            $data = @json_decode(@file_get_contents($file), true);
            if ($data && isset($data['timestamp']) && $data['timestamp'] < $cutoff) {
                if (@unlink($file)) {
                    $cleaned++;
                }
            }
        }
        
        if ($cleaned > 0) {
            Logger::info('Stale compression locks cleaned up', ['count' => $cleaned]);
        }
        
        return $cleaned;
    }
}
