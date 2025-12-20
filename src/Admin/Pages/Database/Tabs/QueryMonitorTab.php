<?php

namespace FP\PerfSuite\Admin\Pages\Database\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

/**
 * Render della tab Query Monitor per Database page
 * 
 * @package FP\PerfSuite\Admin\Pages\Database\Tabs
 * @author Francesco Passeri
 */
class QueryMonitorTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab Query Monitor
     */
    public function render(): string
    {
        ob_start();
        
        try {
            $queryMonitor = $this->container->get(DatabaseQueryMonitor::class);
            $monitorSettings = $queryMonitor->getSettings();
            $statistics = $queryMonitor->getStatistics();
        } catch (\Throwable $e) {
            $queryMonitor = null;
            $monitorSettings = [];
            $statistics = [];
        }
        
        include FP_PERF_SUITE_DIR . '/views/admin/database/query-monitor-tab.php';
        return ob_get_clean();
    }
}
















