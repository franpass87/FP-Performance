<?php

namespace FP\PerfSuite\Admin\Menu;

use FP\PerfSuite\Admin\Pages\Compression;
use FP\PerfSuite\Admin\Pages\Overview;
use FP\PerfSuite\Admin\Pages\Cdn;
use FP\PerfSuite\Admin\Pages\MonitoringReports;
use FP\PerfSuite\ServiceContainer;

use function add_query_arg;
use function admin_url;
use function wp_safe_redirect;

/**
 * Gestisce i form handler per il salvataggio delle impostazioni
 * 
 * @package FP\PerfSuite\Admin\Menu
 * @author Francesco Passeri
 */
class FormHandlers
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Handler per il salvataggio delle impostazioni di compressione
     */
    public function handleCompressionSave(): void
    {
        $compressionPage = new Compression($this->container);
        $message = $compressionPage->handleSave();
        
        // Redirect with message
        $redirect_url = add_query_arg([
            'page' => 'fp-performance-suite-compression',
            'message' => urlencode($message)
        ], admin_url('admin.php'));
        
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Handler per l'esportazione CSV dalla pagina Overview
     */
    public function handleOverviewExportCsv(): void
    {
        $overviewPage = new Overview($this->container);
        $overviewPage->exportCsv();
    }

    /**
     * Handler per il salvataggio delle impostazioni CDN
     */
    public function handleCdnSave(): void
    {
        $cdnPage = new Cdn($this->container);
        $message = $cdnPage->handleSave();
        
        // Redirect with message
        $redirect_url = add_query_arg([
            'page' => 'fp-performance-suite-cdn',
            'message' => urlencode($message)
        ], admin_url('admin.php'));
        
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Handler per il salvataggio delle impostazioni Monitoring
     */
    public function handleMonitoringSave(): void
    {
        $monitoringPage = new MonitoringReports($this->container);
        $message = $monitoringPage->handleSave();
        
        // Redirect with message
        $redirect_url = add_query_arg([
            'page' => 'fp-performance-suite-monitoring',
            'message' => urlencode($message)
        ], admin_url('admin.php'));
        
        wp_safe_redirect($redirect_url);
        exit;
    }
}















