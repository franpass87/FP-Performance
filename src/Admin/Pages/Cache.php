<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Assets\PredictivePrefetching;
use FP\PerfSuite\Services\Security\HtaccessSecurity;
use FP\PerfSuite\Services\PWA\ServiceWorkerManager;
use FP\PerfSuite\Services\Cache\EdgeCacheManager;
use FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator;
use FP\PerfSuite\Services\Assets\ExternalResourceCacheManager;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Admin\Pages\Cache\FormHandler;
use FP\PerfSuite\Admin\Pages\Cache\Tabs\PageCacheTab;
use FP\PerfSuite\Admin\Pages\Cache\Tabs\BrowserCacheTab;
use FP\PerfSuite\Admin\Pages\Cache\Tabs\PWATab;
use FP\PerfSuite\Admin\Pages\Cache\Tabs\EdgeCacheTab;
use FP\PerfSuite\Admin\Pages\Cache\Tabs\AutoConfigTab;
use FP\PerfSuite\Admin\Pages\Cache\Tabs\ExternalCacheTab;
use FP\PerfSuite\Admin\Pages\Cache\Tabs\ExclusionsTab;

use function __;
use function checked;
use function esc_attr;
use function esc_html_e;
use function esc_textarea;
use function printf;
use function sanitize_text_field;
use function wp_nonce_field;
use function wp_unslash;

class Cache extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-cache';
    }

    public function title(): string
    {
        return __('Cache Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Cache', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Determina la tab attiva
        $activeTab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'page';
        // BUGFIX #15b: Rimossa tab 'intelligence' - disponibile solo come pagina standalone
        $validTabs = ['page', 'browser', 'pwa', 'edge', 'auto', 'external', 'exclusions'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'page';
        }
        
        // Gestione dei form submissions
        $formHandler = new FormHandler($this->container);
        $message = $formHandler->handle($activeTab);
        
        ob_start();
        ?>
        
        <?php
        // Intro Box con PageIntro Component
        echo PageIntro::render(
            '🚀',
            __('Cache Management', 'fp-performance-suite'),
            __('Gestisci la cache del sito per migliorare drasticamente le prestazioni. Configura page cache, browser cache, PWA e Edge cache.', 'fp-performance-suite')
        );
        
        // Mostra legenda rischi
        echo RiskLegend::renderLegend();
        ?>
        
        <?php
        // Render tabs navigation
        $this->renderTabsNavigation($activeTab);
        
        // Render content based on active tab
        switch ($activeTab) {
            case 'browser':
                $browserTab = new BrowserCacheTab($this->container);
                echo $browserTab->render($message);
                break;
            case 'pwa':
                $pwaTab = new PWATab($this->container);
                echo $pwaTab->render();
                break;
            case 'edge':
                $edgeTab = new EdgeCacheTab($this->container);
                echo $edgeTab->render();
                break;
            case 'auto':
                $autoTab = new AutoConfigTab($this->container);
                echo $autoTab->render();
                break;
            case 'external':
                $externalTab = new ExternalCacheTab($this->container);
                echo $externalTab->render($message);
                break;
            case 'exclusions':
                $exclusionsTab = new ExclusionsTab($this->container);
                echo $exclusionsTab->render();
                break;
            default:
                $pageTab = new PageCacheTab($this->container);
                echo $pageTab->render();
                break;
        }
        
        return (string) ob_get_clean();
    }


    private function renderTabsNavigation(string $activeTab): void
    {
        $baseUrl = admin_url('admin.php?page=fp-performance-suite-cache');
        ?>
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="<?php echo esc_url($baseUrl . '&tab=page'); ?>" 
               class="nav-tab <?php echo $activeTab === 'page' ? 'nav-tab-active' : ''; ?>">
                📄 <?php esc_html_e('Page Cache', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=browser'); ?>" 
               class="nav-tab <?php echo $activeTab === 'browser' ? 'nav-tab-active' : ''; ?>">
                🌐 <?php esc_html_e('Browser Cache', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=pwa'); ?>" 
               class="nav-tab <?php echo $activeTab === 'pwa' ? 'nav-tab-active' : ''; ?>">
                📱 <?php esc_html_e('PWA', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=edge'); ?>" 
               class="nav-tab <?php echo $activeTab === 'edge' ? 'nav-tab-active' : ''; ?>">
                ☁️ <?php esc_html_e('Edge Cache', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=auto'); ?>" 
               class="nav-tab <?php echo $activeTab === 'auto' ? 'nav-tab-active' : ''; ?>">
                🤖 <?php esc_html_e('Auto Config', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=external'); ?>" 
               class="nav-tab <?php echo $activeTab === 'external' ? 'nav-tab-active' : ''; ?>">
                🌐 <?php esc_html_e('External Cache', 'fp-performance-suite'); ?>
            </a>
            <!-- BUGFIX #15b: Tab Intelligence rimossa - disponibile solo come pagina standalone nel menu principale -->
            <a href="<?php echo esc_url($baseUrl . '&tab=exclusions'); ?>" 
               class="nav-tab <?php echo $activeTab === 'exclusions' ? 'nav-tab-active' : ''; ?>">
                🎯 <?php esc_html_e('Smart Exclusions', 'fp-performance-suite'); ?>
            </a>
        </div>
        <?php
    }

    // Metodi render*Tab rimossi - ora gestiti da Cache\Tabs\*
}
