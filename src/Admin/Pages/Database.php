<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Services\DB\PluginSpecificOptimizer;
use FP\PerfSuite\Services\DB\DatabaseReportService;
use FP\PerfSuite\Services\DB\QueryCacheManager;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Admin\Pages\Database\FormHandler;
use FP\PerfSuite\Admin\Pages\Database\Tabs\OperationsTab;
use FP\PerfSuite\Admin\Pages\Database\Tabs\QueryMonitorTab;
use FP\PerfSuite\Admin\Pages\Database\Tabs\QueryCacheTab;

use function __;
use function array_map;
use function date_i18n;
use function get_option;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function number_format_i18n;
use function printf;
use function sprintf;
use function sanitize_text_field;
use function selected;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Database extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-database';
    }

    public function title(): string
    {
        return __('Database Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Database', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Wrapper di sicurezza generale per prevenire pagina vuota
        try {
            // Determina la tab attiva
            $activeTab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'operations';
            $validTabs = ['operations', 'monitor', 'query_cache'];
            if (!in_array($activeTab, $validTabs, true)) {
                $activeTab = 'operations';
            }
            
            // Gestione dei form submissions
            $formHandler = new FormHandler($this->container);
            $formHandler->handle($activeTab);
            
            ob_start();
            ?>
            
            <?php
            // Mostra legenda rischi
            echo RiskLegend::renderLegend();
            ?>
            
            <?php
            // Render tabs navigation
            $this->renderTabsNavigation($activeTab);
            
            // Render content based on active tab
            switch ($activeTab) {
                case 'monitor':
                    $monitorTab = new QueryMonitorTab($this->container);
                    echo $monitorTab->render();
                    break;
                case 'query_cache':
                    $cacheTab = new QueryCacheTab($this->container);
                    echo $cacheTab->render();
                    break;
                case 'operations':
                default:
                    $operationsTab = new OperationsTab($this->container);
                    echo $operationsTab->render();
                    break;
            }
            
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Database page error');
            return $this->renderError(
                'Errore durante il rendering della pagina: ' . $e->getMessage() . 
                ' (File: ' . basename($e->getFile()) . ':' . $e->getLine() . ')'
            );
        }
    }
    
    // Metodi renderContent(), handleFormSubmissions(), renderOperationsTab(), renderQueryMonitorTab(), renderQueryCacheTab() rimossi - ora gestiti da Database/Tabs/* e Database/FormHandler
    
    /**
     * Renderizza messaggio di errore invece della pagina vuota
     */
    private function renderError(string $message): string
    {
        ob_start();
        ?>
        <div class="notice notice-error" style="margin: 20px 0; padding: 20px;">
            <h2><?php esc_html_e('Errore Caricamento Pagina Database', 'fp-performance-suite'); ?></h2>
            <p><strong><?php esc_html_e('Si è verificato un errore:', 'fp-performance-suite'); ?></strong></p>
            <p><code><?php echo esc_html($message); ?></code></p>
            <hr>
            <h3><?php esc_html_e('Possibili Soluzioni:', 'fp-performance-suite'); ?></h3>
            <ol>
                <li><?php esc_html_e('Disattiva e riattiva il plugin', 'fp-performance-suite'); ?></li>
                <li><?php esc_html_e('Verifica che tutti i file del plugin siano presenti', 'fp-performance-suite'); ?></li>
                <li><?php esc_html_e('Controlla il log degli errori PHP', 'fp-performance-suite'); ?></li>
                <li><?php esc_html_e('Contatta il supporto se il problema persiste', 'fp-performance-suite'); ?></li>
            </ol>
            
            <h3><?php esc_html_e('Informazioni per il Debug:', 'fp-performance-suite'); ?></h3>
            <ul style="font-family: monospace; font-size: 12px; background: #f5f5f5; padding: 15px;">
                <li><strong>PHP Version:</strong> <?php echo esc_html(PHP_VERSION); ?></li>
                <li><strong>WordPress Version:</strong> <?php echo esc_html(get_bloginfo('version')); ?></li>
                <li><strong>Plugin Version:</strong> <?php echo esc_html(FP_PERF_SUITE_VERSION); ?></li>
                <li><strong>Error:</strong> <?php echo esc_html($message); ?></li>
            </ul>
        </div>
        <?php
        return (string) ob_get_clean();
    }
    
    /**
     * Download report file
     */
    private function downloadReport(DatabaseReportService $reportService, string $format): void
    {
        $report = $reportService->generateCompleteReport();
        
        if ($format === 'json') {
            $content = $reportService->exportJSON($report);
            $filename = 'fp-performance-db-report-' . date('Y-m-d') . '.json';
            $contentType = 'application/json';
        } else {
            $content = $reportService->exportCSV($report);
            $filename = 'fp-performance-db-report-' . date('Y-m-d') . '.csv';
            $contentType = 'text/csv';
        }
        
        // BUGFIX: Verifica se headers già inviati per evitare warning
        if (headers_sent($file, $line)) {
            Logger::warning('Cannot send download headers - already sent', [
                'file' => $file,
                'line' => $line,
            ]);
            wp_die('Headers already sent. Cannot download report.');
        }
        
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }
    
    /**
     * Verifica se object cache è disponibile
     */
    private function isObjectCacheAvailable(): bool
    {
        // Verifica Redis
        if (class_exists('Redis')) {
            return true;
        }
        
        // Verifica Memcached
        if (class_exists('Memcached')) {
            return true;
        }
        
        // Verifica APCu
        if (function_exists('apcu_fetch')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Ottieni descrizione del driver
     */
    private function getDriverDescription(string $driver): string
    {
        $descriptions = [
            'redis' => __('Redis è il backend più performante. Ideale per siti ad alto traffico.', 'fp-performance-suite'),
            'memcached' => __('Memcached è veloce e affidabile. Ottimo per siti di medie dimensioni.', 'fp-performance-suite'),
            'apcu' => __('APCu è un cache in-memory PHP. Buono per hosting condivisi.', 'fp-performance-suite'),
            'none' => __('Nessun backend di object caching configurato.', 'fp-performance-suite'),
        ];
        
        return $descriptions[$driver] ?? $descriptions['none'];
    }
    
    private function renderTabsNavigation(string $activeTab): void
    {
        $baseUrl = admin_url('admin.php?page=fp-performance-suite-database');
        ?>
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="<?php echo esc_url($baseUrl . '&tab=operations'); ?>" 
               class="nav-tab <?php echo $activeTab === 'operations' ? 'nav-tab-active' : ''; ?>">
                🔧 <?php esc_html_e('Operations', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=monitor'); ?>" 
               class="nav-tab <?php echo $activeTab === 'monitor' ? 'nav-tab-active' : ''; ?>">
                📊 <?php esc_html_e('Query Monitor', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=query_cache'); ?>" 
               class="nav-tab <?php echo $activeTab === 'query_cache' ? 'nav-tab-active' : ''; ?>">
                ⚡ <?php esc_html_e('Query Cache', 'fp-performance-suite'); ?>
            </a>
        </div>
        <?php
    }
}
