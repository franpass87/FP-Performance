<?php

namespace FP\PerfSuite\Admin\Pages\Database\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Services\DB\PluginSpecificOptimizer;
use FP\PerfSuite\Services\DB\DatabaseReportService;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;

/**
 * Render della tab Operations per Database page
 * 
 * @package FP\PerfSuite\Admin\Pages\Database\Tabs
 * @author Francesco Passeri
 */
class OperationsTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab Operations
     */
    public function render(): string
    {
        // Error handling robusto per prevenire pagina vuota
        try {
            $cleaner = $this->container->get(Cleaner::class);
        } catch (\Throwable $e) {
            return $this->renderError('Cleaner service non disponibile: ' . $e->getMessage());
        }
        
        // Servizi avanzati - verifica disponibilità (v1.4.0)
        $queryMonitor = $this->container->has(DatabaseQueryMonitor::class) 
            ? $this->container->get(DatabaseQueryMonitor::class) 
            : null;
        $optimizer = $this->container->has(DatabaseOptimizer::class) 
            ? $this->container->get(DatabaseOptimizer::class) 
            : null;
        
        try {
            $objectCache = $this->container->get(ObjectCacheManager::class);
        } catch (\Throwable $e) {
            return $this->renderError('ObjectCacheManager service non disponibile: ' . $e->getMessage());
        }
        
        // Crea istanze dirette se le classi esistono
        $pluginOptimizer = class_exists('FP\\PerfSuite\\Services\\DB\\PluginSpecificOptimizer') 
            ? new PluginSpecificOptimizer() 
            : null;
        $reportService = class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseReportService') 
            ? new DatabaseReportService() 
            : null;
        
        $message = '';
        $results = [];
        
        // Gestione form submissions (spostata in FormHandler)
        // Qui solo rendering
        
        ob_start();
        include FP_PERF_SUITE_DIR . '/views/admin/database/operations-tab.php';
        return ob_get_clean();
    }

    /**
     * Render di un errore
     */
    private function renderError(string $message): string
    {
        return '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    }
}
















