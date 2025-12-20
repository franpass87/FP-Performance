<?php

namespace FP\PerfSuite\Admin\Pages\Database\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\DB\QueryCacheManager;

/**
 * Render della tab Query Cache per Database page
 * 
 * @package FP\PerfSuite\Admin\Pages\Database\Tabs
 * @author Francesco Passeri
 */
class QueryCacheTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab Query Cache
     */
    public function render(): string
    {
        ob_start();
        
        try {
            $queryCache = $this->container->get(QueryCacheManager::class);
            $cacheSettings = $queryCache->getSettings();
            $stats = $queryCache->getStats();
        } catch (\Throwable $e) {
            $queryCache = null;
            $cacheSettings = [];
            $stats = [];
        }
        
        include FP_PERF_SUITE_DIR . '/views/admin/database/query-cache-tab.php';
        return ob_get_clean();
    }
}
















