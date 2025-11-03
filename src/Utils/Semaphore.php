<?php

namespace FP\PerfSuite\Utils;

/**
 * Semaphore - Sistema di locking per prevenire race conditions
 * 
 * Implementa un sistema di lock basato su file per operazioni concorrenti
 * 
 * @package FP\PerfSuite\Utils
 */
class Semaphore
{
    private const LOCK_DIR = 'fp-performance-locks';
    private const DEFAULT_TIMEOUT = 30; // secondi
    private const LOCK_STALE_TIME = 300; // 5 minuti - lock considerato stale
    
    private ?string $lockDir = null;
    
    /**
     * Inizializza la directory dei lock
     */
    private function initLockDir(): string
    {
        if ($this->lockDir !== null) {
            return $this->lockDir;
        }
        
        $uploadDir = wp_upload_dir();
        
        if (!is_array($uploadDir) || empty($uploadDir['basedir'])) {
            // Fallback su sys_get_temp_dir se uploads non disponibile
            $this->lockDir = sys_get_temp_dir() . '/' . self::LOCK_DIR;
        } else {
            $this->lockDir = $uploadDir['basedir'] . '/' . self::LOCK_DIR;
        }
        
        // Crea directory se non esiste
        if (!file_exists($this->lockDir)) {
            wp_mkdir_p($this->lockDir);
            
            // Proteggi con .htaccess
            $htaccessFile = $this->lockDir . '/.htaccess';
            if (!file_exists($htaccessFile)) {
                file_put_contents($htaccessFile, "Order deny,allow\nDeny from all\n");
            }
        }
        
        return $this->lockDir;
    }
    
    /**
     * Acquisisce un lock
     * 
     * @param string $key Chiave univoca del lock
     * @param int $timeout Timeout in secondi (default: 30)
     * @return bool True se lock acquisito, false se timeout
     */
    public function acquire(string $key, int $timeout = self::DEFAULT_TIMEOUT): bool
    {
        if (empty($key)) {
            Logger::error('Semaphore: chiave vuota fornita ad acquire()');
            return false;
        }
        
        $lockDir = $this->initLockDir();
        $lockFile = $lockDir . '/' . $this->sanitizeKey($key) . '.lock';
        
        $start = time();
        
        while (true) {
            // Controlla se il lock esiste
            if (!file_exists($lockFile)) {
                // Tenta di creare il lock
                if ($this->createLock($lockFile)) {
                    Logger::debug('Semaphore: lock acquisito', ['key' => $key]);
                    return true;
                }
            }
            
            // Lock esiste - verifica se è stale (bloccato da troppo tempo)
            if ($this->isLockStale($lockFile)) {
                Logger::warning('Semaphore: lock stale rilevato, rimuovo', [
                    'key' => $key,
                    'age' => time() - filemtime($lockFile)
                ]);
                
                // Rimuovi lock stale e riprova
                @unlink($lockFile);
                continue;
            }
            
            // Controlla timeout
            if (time() - $start >= $timeout) {
                Logger::warning('Semaphore: timeout acquisizione lock', [
                    'key' => $key,
                    'timeout' => $timeout
                ]);
                return false;
            }
            
            // Attendi prima di riprovare
            usleep(100000); // 100ms
        }
    }
    
    /**
     * Rilascia un lock
     * 
     * @param string $key Chiave del lock da rilasciare
     * @return bool True se rilasciato con successo
     */
    public function release(string $key): bool
    {
        if (empty($key)) {
            return false;
        }
        
        $lockDir = $this->initLockDir();
        $lockFile = $lockDir . '/' . $this->sanitizeKey($key) . '.lock';
        
        if (!file_exists($lockFile)) {
            // Lock già rilasciato
            return true;
        }
        
        $result = @unlink($lockFile);
        
        if ($result) {
            Logger::debug('Semaphore: lock rilasciato', ['key' => $key]);
        } else {
            Logger::error('Semaphore: impossibile rilasciare lock', ['key' => $key]);
        }
        
        return $result;
    }
    
    /**
     * Verifica se un lock esiste
     * 
     * @param string $key Chiave del lock
     * @return bool True se il lock esiste
     */
    public function isLocked(string $key): bool
    {
        if (empty($key)) {
            return false;
        }
        
        $lockDir = $this->initLockDir();
        $lockFile = $lockDir . '/' . $this->sanitizeKey($key) . '.lock';
        
        if (!file_exists($lockFile)) {
            return false;
        }
        
        // Verifica se stale
        if ($this->isLockStale($lockFile)) {
            @unlink($lockFile);
            return false;
        }
        
        return true;
    }
    
    /**
     * Crea un file di lock atomicamente
     * 
     * @param string $lockFile Path del file di lock
     * @return bool True se creato con successo
     */
    private function createLock(string $lockFile): bool
    {
        // Usa touch() con exclusive lock per atomicità
        $fp = @fopen($lockFile, 'x');
        
        if ($fp === false) {
            return false;
        }
        
        // Scrivi informazioni sul lock
        fwrite($fp, json_encode([
            'pid' => getmypid(),
            'time' => time(),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '',
        ]));
        
        fclose($fp);
        
        return true;
    }
    
    /**
     * Verifica se un lock è stale (vecchio)
     * 
     * @param string $lockFile Path del file di lock
     * @return bool True se il lock è considerato stale
     */
    private function isLockStale(string $lockFile): bool
    {
        if (!file_exists($lockFile)) {
            return false;
        }
        
        $mtime = @filemtime($lockFile);
        
        if ($mtime === false) {
            return false;
        }
        
        // Lock più vecchio di LOCK_STALE_TIME è considerato stale
        return (time() - $mtime) > self::LOCK_STALE_TIME;
    }
    
    /**
     * Sanitizza la chiave del lock per uso come filename
     * 
     * @param string $key Chiave da sanitizzare
     * @return string Chiave sanitizzata
     */
    private function sanitizeKey(string $key): string
    {
        // Usa md5 per garantire nome file valido
        return md5($key);
    }
    
    /**
     * Pulisce tutti i lock stale
     * 
     * @return int Numero di lock rimossi
     */
    public function cleanupStaleLocks(): int
    {
        $lockDir = $this->initLockDir();
        
        if (!is_dir($lockDir) || !is_readable($lockDir)) {
            return 0;
        }
        
        $locks = glob($lockDir . '/*.lock');
        
        if ($locks === false) {
            return 0;
        }
        
        $removed = 0;
        
        foreach ($locks as $lockFile) {
            if ($this->isLockStale($lockFile)) {
                if (@unlink($lockFile)) {
                    $removed++;
                }
            }
        }
        
        if ($removed > 0) {
            Logger::info('Semaphore: pulizia lock stale completata', ['removed' => $removed]);
        }
        
        return $removed;
    }
    
    /**
     * Restituisce informazioni sui lock attivi
     * 
     * @return array Array con informazioni sui lock
     */
    public function getActiveLocks(): array
    {
        $lockDir = $this->initLockDir();
        
        if (!is_dir($lockDir) || !is_readable($lockDir)) {
            return [];
        }
        
        $locks = glob($lockDir . '/*.lock');
        
        if ($locks === false) {
            return [];
        }
        
        $activeLocks = [];
        
        foreach ($locks as $lockFile) {
            if (!$this->isLockStale($lockFile)) {
                $content = @file_get_contents($lockFile);
                $data = $content ? json_decode($content, true) : [];
                
                $activeLocks[] = [
                    'file' => basename($lockFile),
                    'age' => time() - filemtime($lockFile),
                    'data' => $data ?: [],
                ];
            }
        }
        
        return $activeLocks;
    }
    
    /**
     * Metodo legacy per compatibilità backwards
     * 
     * @deprecated Usare acquire() e release() invece
     * @param string $key
     * @param string $color
     * @param string $description
     * @return array<string, string>
     */
    public function describe(string $key, string $color, string $description): array
    {
        return [
            'key' => $key,
            'color' => $color,
            'description' => $description,
        ];
    }
}
