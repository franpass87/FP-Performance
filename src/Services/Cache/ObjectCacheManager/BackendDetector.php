<?php

namespace FP\PerfSuite\Services\Cache\ObjectCacheManager;

/**
 * Rileva il backend di object caching disponibile
 * 
 * @package FP\PerfSuite\Services\Cache\ObjectCacheManager
 * @author Francesco Passeri
 */
class BackendDetector
{
    /**
     * Rileva il backend disponibile
     */
    public function detectAvailableBackend(): ?string
    {
        // Controlla Redis
        if (class_exists('Redis') || extension_loaded('redis')) {
            return 'redis';
        }
        
        // Controlla Memcached
        if (class_exists('Memcached') || extension_loaded('memcached')) {
            return 'memcached';
        }
        
        // Controlla APCu
        if (function_exists('apcu_store') && function_exists('apcu_fetch')) {
            return 'apcu';
        }
        
        return null;
    }

    /**
     * Ottiene informazioni sul backend
     */
    public function getBackendInfo(?string $backend): array
    {
        if (!$backend) {
            return [
                'available' => false,
                'enabled' => false,
                'backend' => null,
                'status' => 'not_available',
                'message' => 'Nessun backend di object caching disponibile sul server.',
            ];
        }
        
        $info = [
            'available' => true,
            'backend' => $backend,
        ];
        
        // Informazioni specifiche per backend
        switch ($backend) {
            case 'redis':
                $info['name'] = 'Redis';
                $info['description'] = 'Redis è il sistema di object caching più performante per WordPress.';
                break;
                
            case 'memcached':
                $info['name'] = 'Memcached';
                $info['description'] = 'Memcached è un sistema di caching ad alte prestazioni.';
                break;
                
            case 'apcu':
                $info['name'] = 'APCu';
                $info['description'] = 'APCu è un sistema di caching in-memory leggero.';
                break;
        }
        
        return $info;
    }
}















