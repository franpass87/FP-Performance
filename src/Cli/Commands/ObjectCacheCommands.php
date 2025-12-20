<?php

namespace FP\PerfSuite\Cli\Commands;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;

/**
 * Comandi WP-CLI per la gestione dell'object cache
 * 
 * @package FP\PerfSuite\Cli\Commands
 * @author Francesco Passeri
 */
class ObjectCacheCommands
{
    /**
     * Show object cache status
     */
    public function status(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            $info = $objectCache->getBackendInfo();
            $stats = $objectCache->getStatistics();
            
            \WP_CLI::log(\WP_CLI::colorize('%G=== Object Cache Status ===%n'));
            
            if ($info['available']) {
                \WP_CLI::log('Backend: ' . $info['name']);
                \WP_CLI::log('Available: Yes');
                \WP_CLI::log('Enabled: ' . ($info['enabled'] ? 'Yes' : 'No'));
                
                if ($stats['enabled']) {
                    \WP_CLI::log("\n" . \WP_CLI::colorize('%Y=== Statistics ===%n'));
                    \WP_CLI::log('Cache Hits: ' . number_format($stats['hits'] ?? 0));
                    \WP_CLI::log('Cache Misses: ' . number_format($stats['misses'] ?? 0));
                    \WP_CLI::log('Hit Ratio: ' . round($stats['ratio'] ?? 0, 2) . '%');
                }
            } else {
                \WP_CLI::warning('No object cache backend available (Redis, Memcached, APCu)');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to get object cache status: ' . $e->getMessage());
        }
    }
    
    /**
     * Enable object cache
     */
    public function enable(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            \WP_CLI::log('Enabling object cache...');
            
            $result = $objectCache->install();
            
            if ($result['success']) {
                \WP_CLI::success($result['message']);
            } else {
                \WP_CLI::error($result['message']);
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to enable object cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Disable object cache
     */
    public function disable(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            \WP_CLI::log('Disabling object cache...');
            
            $result = $objectCache->uninstall();
            
            if ($result['success']) {
                \WP_CLI::success($result['message']);
            } else {
                \WP_CLI::error($result['message']);
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to disable object cache: ' . $e->getMessage());
        }
    }
    
    /**
     * Flush object cache
     */
    public function flush(): void
    {
        try {
            $container = Plugin::container();
            $objectCache = $container->get(ObjectCacheManager::class);
            
            \WP_CLI::log('Flushing object cache...');
            
            if ($objectCache->flush()) {
                \WP_CLI::success('Object cache flushed successfully!');
            } else {
                \WP_CLI::error('Failed to flush object cache');
            }
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to flush object cache: ' . $e->getMessage());
        }
    }
}
















