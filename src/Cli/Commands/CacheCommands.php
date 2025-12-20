<?php

namespace FP\PerfSuite\Cli\Commands;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Utils\Logger;

/**
 * Comandi WP-CLI per la gestione della cache
 * 
 * @package FP\PerfSuite\Cli\Commands
 * @author Francesco Passeri
 */
class CacheCommands
{
    /**
     * Clear page cache
     */
    public function clear(): void
    {
        try {
            $container = Plugin::container();
            $pageCache = $container->get(PageCache::class);

            \WP_CLI::log('Clearing page cache...');
            $pageCache->clear();

            \WP_CLI::success('Page cache cleared successfully!');
            Logger::info('Page cache cleared via WP-CLI');
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to clear cache: ' . $e->getMessage());
        }
    }

    /**
     * Show cache status
     */
    public function status(): void
    {
        try {
            $container = Plugin::container();
            $pageCache = $container->get(PageCache::class);
            $status = $pageCache->status();

            \WP_CLI::log('Cache Status:');
            \WP_CLI::log('  Enabled: ' . ($status['enabled'] ? 'Yes' : 'No'));
            \WP_CLI::log('  Cached files: ' . $status['files']);
        } catch (\Throwable $e) {
            \WP_CLI::error('Failed to get cache status: ' . $e->getMessage());
        }
    }
}
















