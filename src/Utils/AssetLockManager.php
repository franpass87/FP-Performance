<?php

namespace FP\PerfSuite\Utils;

use FP\PerfSuite\Utils\Logger;

/**
 * Asset Lock Manager
 * 
 * Gestisce i file locks per le operazioni di asset per prevenire race conditions
 * durante la scrittura di critical CSS, font optimization, image optimization, etc.
 * 
 * @since 1.6.0
 */
class AssetLockManager
{
    private const LOCK_TIMEOUT = 30; // 30 secondi timeout
    private const LOCK_DIR = 'fp-performance-locks';
    
    /**
     * BUGFIX: Shared static property per conservare i lock resources
     * Risolve il problema di variabili statiche locali che non condividono lo stato
     * @var array<string, resource>
     */
    private static array $lockResources = [];
    
    /**
     * Acquisisce un lock per un'operazione di asset
     * 
     * @param string $assetType Tipo di asset (critical_css, font_opt, image_opt, etc.)
     * @param string $identifier Identificatore specifico (opzionale)
     * @return bool True se lock acquisito, false altrimenti
     */
    public static function acquire(string $assetType, string $identifier = ''): bool
    {
        $lockKey = self::generateLockKey($assetType, $identifier);
        $lockFile = self::getLockFilePath($lockKey);
        
        $lock = fopen($lockFile, 'c+');
        if (!$lock) {
            Logger::error('Failed to create asset lock file', ['file' => $lockFile]);
            return false;
        }
        
        // Acquire exclusive lock (non-blocking)
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            Logger::debug('Asset operation locked by another process', [
                'asset_type' => $assetType,
                'identifier' => $identifier
            ]);
            return false; // Another process is working on this asset
        }
        
        // Write lock metadata
        $lockData = [
            'pid' => getmypid(),
            'timestamp' => time(),
            'asset_type' => $assetType,
            'identifier' => $identifier,
        ];
        
        fwrite($lock, wp_json_encode($lockData));
        fflush($lock); // Forza scrittura su disco
        
        // BUG FIX CRITICO: NON chiudere il lock qui!
        // Il lock deve rimanere aperto fino a release()
        // Salvalo in static storage come fa MLSemaphore
        self::storeLockResource($lockKey, $lock);
        
        Logger::debug('Asset lock acquired', [
            'asset_type' => $assetType,
            'identifier' => $identifier,
            'pid' => getmypid()
        ]);
        
        return true;
    }
    
    /**
     * Rilascia un lock per un'operazione di asset
     * 
     * @param string $assetType Tipo di asset
     * @param string $identifier Identificatore specifico (opzionale)
     * @return bool True se lock rilasciato
     */
    public static function release(string $assetType, string $identifier = ''): bool
    {
        $lockKey = self::generateLockKey($assetType, $identifier);
        $lockFile = self::getLockFilePath($lockKey);
        
        // BUG FIX: Usa il lock salvato in storage, non aprirne uno nuovo
        $lock = self::getLockResource($lockKey);
        
        if ($lock) {
            // Rilascia e chiudi il lock esistente
            flock($lock, LOCK_UN);
            fclose($lock);
            self::removeLockResource($lockKey);
        }
        
        // Rimuovi il file di lock
        if (file_exists($lockFile)) {
            @unlink($lockFile);
        }
        
        Logger::debug('Asset lock released', [
            'asset_type' => $assetType,
            'identifier' => $identifier
        ]);
        
        return true;
    }
    
    /**
     * Verifica se un lock è attivo per un'operazione di asset
     * 
     * @param string $assetType Tipo di asset
     * @param string $identifier Identificatore specifico (opzionale)
     * @return bool True se lock attivo
     */
    public static function isLocked(string $assetType, string $identifier = ''): bool
    {
        $lockKey = self::generateLockKey($assetType, $identifier);
        $lockFile = self::getLockFilePath($lockKey);
        
        if (!file_exists($lockFile)) {
            return false;
        }
        
        // Check if lock is stale (older than timeout)
        $lockData = @file_get_contents($lockFile);
        if ($lockData) {
            $data = json_decode($lockData, true);
            
            // BUGFIX: Verifica json_last_error() per evitare silent failures
            if (json_last_error() === JSON_ERROR_NONE && is_array($data) && isset($data['timestamp'])) {
                $age = time() - $data['timestamp'];
                if ($age > self::LOCK_TIMEOUT) {
                    // Stale lock, clean it up
                    @unlink($lockFile);
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Pulisce tutti i lock scaduti
     * 
     * @return int Numero di lock puliti
     */
    public static function cleanupStaleLocks(): int
    {
        $lockDir = self::getLockDirectory();
        if (!is_dir($lockDir)) {
            return 0;
        }
        
        $cleaned = 0;
        $files = glob($lockDir . '/*.lock');
        
        // BUGFIX: glob() può ritornare false in caso di errore
        if ($files === false) {
            Logger::warning('Failed to list lock files', ['dir' => $lockDir]);
            return 0;
        }
        
        foreach ($files as $file) {
            $lockData = @file_get_contents($file);
            if ($lockData) {
                $data = json_decode($lockData, true);
                
                // BUGFIX: Verifica json_last_error() per evitare silent failures
                if (json_last_error() === JSON_ERROR_NONE && is_array($data) && isset($data['timestamp'])) {
                    $age = time() - $data['timestamp'];
                    if ($age > self::LOCK_TIMEOUT) {
                        @unlink($file);
                        $cleaned++;
                    }
                }
            }
        }
        
        if ($cleaned > 0) {
            Logger::info('Cleaned up stale asset locks', ['count' => $cleaned]);
        }
        
        return $cleaned;
    }
    
    /**
     * Esegue un'operazione di asset con lock automatico
     * 
     * @param string $assetType Tipo di asset
     * @param string $identifier Identificatore specifico (opzionale)
     * @param callable $operation Operazione da eseguire
     * @return mixed Risultato dell'operazione o false se lock non acquisito
     */
    public static function executeWithLock(string $assetType, string $identifier, callable $operation)
    {
        if (!self::acquire($assetType, $identifier)) {
            return false;
        }
        
        try {
            $result = $operation();
            return $result;
        } finally {
            self::release($assetType, $identifier);
        }
    }
    
    /**
     * Genera una chiave di lock unica
     */
    private static function generateLockKey(string $assetType, string $identifier): string
    {
        $key = sanitize_key($assetType);
        if ($identifier) {
            $key .= '_' . sanitize_key($identifier);
        }
        return $key;
    }
    
    /**
     * Ottiene il path del file di lock
     */
    private static function getLockFilePath(string $lockKey): string
    {
        $lockDir = self::getLockDirectory();
        return $lockDir . '/' . $lockKey . '.lock';
    }
    
    /**
     * Ottiene la directory dei lock
     */
    private static function getLockDirectory(): string
    {
        $uploadDir = wp_upload_dir();
        $lockDir = $uploadDir['basedir'] . '/' . self::LOCK_DIR;
        
        if (!is_dir($lockDir)) {
            wp_mkdir_p($lockDir);
        }
        
        return $lockDir;
    }
    
    /**
     * Store lock resource for later release (BUG FIX)
     * Necessario per mantenere il lock aperto fino a release()
     */
    private static function storeLockResource(string $lockKey, $lock): void
    {
        self::$lockResources[$lockKey] = $lock;
    }
    
    /**
     * Get stored lock resource (BUG FIX)
     */
    private static function getLockResource(string $lockKey)
    {
        return self::$lockResources[$lockKey] ?? null;
    }
    
    /**
     * Remove stored lock resource (BUG FIX)
     */
    private static function removeLockResource(string $lockKey): void
    {
        unset(self::$lockResources[$lockKey]);
    }
}
