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
        
        $queryMonitor = null;
        $monitorSettings = [
            'enabled' => false,
            'slow_threshold' => 0.5,
            'log_queries' => false,
        ];
        $statistics = [
            'total_queries' => 0,
            'avg_time' => 0,
            'slow_queries' => 0,
            'total_time' => 0,
            'recent_slow' => [],
        ];
        
        try {
            // Verifica che il servizio sia disponibile nel container
            if (!$this->container->has(DatabaseQueryMonitor::class)) {
                throw new \RuntimeException('DatabaseQueryMonitor service not registered in container');
            }
            
            $queryMonitor = $this->container->get(DatabaseQueryMonitor::class);
            
            // Carica impostazioni se il servizio è disponibile
            if (method_exists($queryMonitor, 'getSettings')) {
                $monitorSettings = $queryMonitor->getSettings();
            }
            
            // Carica statistiche se il servizio è disponibile
            if (method_exists($queryMonitor, 'getStatistics')) {
                $statistics = $queryMonitor->getStatistics();
            }
            
            // Assicurati che le impostazioni e statistiche siano array validi
            if (!is_array($monitorSettings)) {
                $monitorSettings = [];
            }
            if (!is_array($statistics)) {
                $statistics = [];
            }
            
            // Merge con defaults per sicurezza
            $monitorSettings = array_merge([
                'enabled' => false,
                'slow_threshold' => 0.5,
                'log_queries' => false,
            ], $monitorSettings);
            
            $statistics = array_merge([
                'total_queries' => 0,
                'avg_time' => 0,
                'slow_queries' => 0,
                'total_time' => 0,
                'recent_slow' => [],
            ], $statistics);
            
        } catch (\Throwable $e) {
            // Log errore per debug
            if (function_exists('error_log')) {
                error_log(sprintf(
                    '[FP-Performance] QueryMonitorTab error: %s in %s:%d',
                    $e->getMessage(),
                    basename($e->getFile()),
                    $e->getLine()
                ));
            }
            
            // Mostra messaggio di errore dettagliato in debug mode
            if (defined('WP_DEBUG') && WP_DEBUG) {
                ?>
                <div class="notice notice-warning">
                    <p><strong><?php esc_html_e('Query Monitor Service:', 'fp-performance-suite'); ?></strong></p>
                    <p><?php echo esc_html($e->getMessage()); ?></p>
                    <p><small><?php echo esc_html(sprintf('%s:%d', basename($e->getFile()), $e->getLine())); ?></small></p>
                </div>
                <?php
            }
        }
        
        include FP_PERF_SUITE_DIR . '/views/admin/database/query-monitor-tab.php';
        return ob_get_clean();
    }
}
















