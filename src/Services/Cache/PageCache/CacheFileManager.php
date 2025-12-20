<?php

namespace FP\PerfSuite\Services\Cache\PageCache;

use FP\PerfSuite\Utils\ErrorHandler;

/**
 * Gestisce le operazioni sui file di cache
 * 
 * @package FP\PerfSuite\Services\Cache\PageCache
 * @author Francesco Passeri
 */
class CacheFileManager
{
    private string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * Ottiene il percorso del file cache per una chiave
     */
    public function getCacheFile(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }

    /**
     * SICUREZZA: Verifica che il file sia nella directory cache autorizzata
     */
    public function isValidCacheFile(string $file): bool
    {
        $realCacheDir = realpath($this->cacheDir);
        $realFile = realpath(dirname($file));
        
        if ($realCacheDir === false || $realFile === false) {
            return false;
        }
        
        return strpos($realFile, $realCacheDir) === 0;
    }

    /**
     * SICUREZZA: Unserialize sicuro per prevenire object injection
     * 
     * @param string $data Dati serializzati da deserializzare
     * @return array|false Dati deserializzati o false in caso di errore
     */
    public function safeUnserialize(string $data)
    {
        if (empty($data) || !is_string($data)) {
            ErrorHandler::handleSilently(
                new \RuntimeException('Invalid data type for unserialize'),
                'CacheFileManager unserialize'
            );
            return false;
        }
        
        // SECURITY FIX: Usa allowed_classes => false per prevenire object injection
        // PHP 7.0+ supporta questo parametro
        try {
            // Usa unserialize con opzioni sicure
            $result = @unserialize($data, ['allowed_classes' => false]);
            
            // Verifica che il risultato sia un array valido
            if (!is_array($result)) {
                ErrorHandler::handleSilently(
                    new \InvalidArgumentException('Invalid cache data format - expected array'),
                    'CacheFileManager validate'
                );
                return false;
            }
            
            // Verifica struttura attesa
            if (!isset($result['content']) || !isset($result['expires'])) {
                ErrorHandler::handleSilently(
                    new \InvalidArgumentException('Invalid cache data structure'),
                    'CacheFileManager validate'
                );
                return false;
            }
            
            // Valida il tipo di expires
            if (!is_numeric($result['expires'])) {
                ErrorHandler::handleSilently(
                    new \InvalidArgumentException('Invalid expires value'),
                    'CacheFileManager validate'
                );
                return false;
            }
            
            return $result;
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'CacheFileManager unserialize');
            return false;
        }
    }
}
















