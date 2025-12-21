<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Admin\Pages\MonitoringReports\FormHandler;
use FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs\PerformanceTab;
use FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs\LogsTab;
use FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs\DiagnosticsTab;

use function __;
use function esc_html_e;
use function sanitize_key;
use function sanitize_text_field;
use function in_array;
use function wp_unslash;

/**
 * Monitoring & Reports Page
 * 
 * Gestisce tutto il monitoraggio e reporting delle performance:
 * - Performance Monitoring
 * - Core Web Vitals Monitor
 * - Scheduled Reports
 * - Webhook Integration
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class MonitoringReports extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-monitoring';
    }

    public function title(): string
    {
        return __('Monitoring & Reports', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [
                __('Monitoraggio', 'fp-performance-suite'),
                __('Monitoring & Reports', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        // Determina tab attiva
        $current_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'performance';
        $valid_tabs = ['performance', 'logs', 'diagnostics'];
        if (!in_array($current_tab, $valid_tabs, true)) {
            $current_tab = 'performance';
        }
        
        // Handle form submission
        $message = '';
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['_wpnonce'])) {
            $formHandler = new FormHandler($this->container);
            $message = $formHandler->handle($this->capability());
        }

        // Check for messages from URL (from admin_post handlers)
        if (isset($_GET['message'])) {
            $message = sanitize_text_field(wp_unslash($_GET['message']));
        }
        
        // Check for legacy success message from URL
        if (isset($_GET['updated']) && sanitize_key(wp_unslash($_GET['updated'] ?? '')) === '1') {
            $message = __('Impostazioni monitoraggio salvate con successo!', 'fp-performance-suite');
        }

        // Check for legacy error message from URL
        if (isset($_GET['error']) && sanitize_key(wp_unslash($_GET['error'] ?? '')) === '1') {
            $message = isset($_GET['message']) 
                ? sanitize_text_field(wp_unslash($_GET['message'] ?? '')) 
                : __('Si è verificato un errore durante il salvataggio.', 'fp-performance-suite');
        }

        ob_start();
        ?>
        
        <?php
        // Intro Box con PageIntro Component
        echo PageIntro::render(
            '📈',
            __('Monitoring & Reports', 'fp-performance-suite'),
            __('Monitora le prestazioni del sito, analizza Core Web Vitals, visualizza trend e genera report dettagliati.', 'fp-performance-suite')
        );
        ?>
        
        <!-- Navigazione Tabs -->
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="?page=fp-performance-suite-monitoring&tab=performance" 
               class="nav-tab <?php echo $current_tab === 'performance' ? 'nav-tab-active' : ''; ?>">
                📈 <?php esc_html_e('Performance', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-monitoring&tab=logs" 
               class="nav-tab <?php echo $current_tab === 'logs' ? 'nav-tab-active' : ''; ?>">
                📝 <?php esc_html_e('Logs', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-monitoring&tab=diagnostics" 
               class="nav-tab <?php echo $current_tab === 'diagnostics' ? 'nav-tab-active' : ''; ?>">
                🔧 <?php esc_html_e('Diagnostics', 'fp-performance-suite'); ?>
            </a>
        </div>
        
        <?php
        // Render tab content
        switch ($current_tab) {
            case 'logs':
                $logsTab = new LogsTab();
                echo $logsTab->render();
                break;
            case 'diagnostics':
                $diagnosticsTab = new DiagnosticsTab();
                echo $diagnosticsTab->render();
                break;
            case 'performance':
            default:
                $performanceTab = new PerformanceTab($this->container);
                echo $performanceTab->render($message);
                break;
        }
        
        return (string) ob_get_clean();
    }
    
    // Metodi renderPerformanceTab(), renderMonitoringSection(), renderCoreWebVitalsSection(), 
    // renderReportsSection(), renderWebhookSection(), renderPerformanceBudgetSection(), 
    // renderErrorSection(), handleSave(), save*Settings(), renderLogsTab(), renderDiagnosticsTab() 
    // rimossi - ora gestiti da MonitoringReports/Tabs/*, MonitoringReports/Sections/* e MonitoringReports/FormHandler
}
