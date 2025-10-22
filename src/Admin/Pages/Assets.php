<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Admin\Pages\AbstractPage;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector;
use FP\PerfSuite\Services\Assets\Http2ServerPush;
use FP\PerfSuite\Services\Assets\SmartAssetDelivery;
use FP\PerfSuite\Services\Assets\UnusedCSSOptimizer;
use FP\PerfSuite\Services\Assets\CriticalCss;
use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;
use FP\PerfSuite\Services\Intelligence\CriticalAssetsDetector;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Admin\ThemeHints;
use FP\PerfSuite\Admin\Components\StatusIndicator;
use FP\PerfSuite\Admin\Pages\Assets\Handlers\PostHandler;

use function __;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function sanitize_key;

class Assets extends AbstractPage
{
    private PostHandler $postHandler;
    private array $tabs = [];

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->postHandler = new PostHandler();
        $this->initializeTabs();
    }

    public function slug(): string
    {
        return 'fp-performance-suite-assets';
    }

    public function title(): string
    {
        return __('Assets Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Assets', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Initialize services
        $optimizer = $this->container->get(Optimizer::class);
        $fontOptimizer = $this->container->get(FontOptimizer::class);
        $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
        $scriptDetector = new ThirdPartyScriptDetector($thirdPartyScripts);
        $http2Push = $this->container->get(Http2ServerPush::class);
        $smartDelivery = $this->container->get(SmartAssetDelivery::class);
        
        // Load current settings
        $settings = $optimizer->settings();
        $fontSettings = $fontOptimizer->getSettings();
        $thirdPartySettings = $thirdPartyScripts->settings();
        
        // Smart detectors
        $smartDetector = new SmartExclusionDetector();
        $assetsDetector = new CriticalAssetsDetector();
        $themeDetector = $this->container->get(ThemeDetector::class);
        $hints = new ThemeHints($themeDetector);
        
        // Load cached detection results
        $criticalScripts = get_transient('fp_ps_critical_scripts_detected');
        $excludeCss = get_transient('fp_ps_exclude_css_detected');
        $excludeJs = get_transient('fp_ps_exclude_js_detected');
        $criticalAssets = get_transient('fp_ps_critical_assets_detected');
        
        // Handle POST requests
        $message = $this->postHandler->handlePost($settings, $fontSettings, $thirdPartySettings);
        
        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'javascript';
        $valid_tabs = ['javascript', 'css', 'fonts', 'thirdparty'];
        if (!in_array($current_tab, $valid_tabs, true)) {
            $current_tab = 'javascript';
        }

        ob_start();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Assets Optimization', 'fp-performance-suite'); ?></h1>
            
            <?php if ($message) : ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Tab Navigation -->
            <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
                <a href="?page=fp-performance-suite-assets&tab=javascript" 
                   class="nav-tab <?php echo $current_tab === 'javascript' ? 'nav-tab-active' : ''; ?>">
                    ⚡ <?php esc_html_e('JavaScript', 'fp-performance-suite'); ?>
                </a>
                <a href="?page=fp-performance-suite-assets&tab=css" 
                   class="nav-tab <?php echo $current_tab === 'css' ? 'nav-tab-active' : ''; ?>">
                    🎨 <?php esc_html_e('CSS', 'fp-performance-suite'); ?>
                </a>
                <a href="?page=fp-performance-suite-assets&tab=fonts" 
                   class="nav-tab <?php echo $current_tab === 'fonts' ? 'nav-tab-active' : ''; ?>">
                    🔤 <?php esc_html_e('Fonts', 'fp-performance-suite'); ?>
                </a>
                <a href="?page=fp-performance-suite-assets&tab=thirdparty" 
                   class="nav-tab <?php echo $current_tab === 'thirdparty' ? 'nav-tab-active' : ''; ?>">
                    🔌 <?php esc_html_e('Third-Party', 'fp-performance-suite'); ?>
                </a>
            </div>

            <!-- Tab Descriptions -->
            <?php if ($current_tab === 'javascript') : ?>
                <div class="fp-ps-tab-description" style="background: #fef3c7; border-left: 4px solid #eab308; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; color: #713f12;">
                        <strong>⚡ JavaScript:</strong> 
                        <?php esc_html_e('Ottimizza il caricamento e l\'esecuzione dei file JavaScript. Configura defer/async, combine, minify e gestisci esclusioni intelligenti per massimizzare le performance.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php elseif ($current_tab === 'css') : ?>
                <div class="fp-ps-tab-description" style="background: #e0f2fe; border-left: 4px solid #0ea5e9; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; color: #0c4a6e;">
                        <strong>🎨 CSS:</strong> 
                        <?php esc_html_e('Ottimizzazione completa CSS: base optimization, Google Fonts, rimozione CSS inutilizzato e Critical CSS inline per FCP ottimale.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php elseif ($current_tab === 'fonts') : ?>
                <div class="fp-ps-tab-description" style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; color: #78350f;">
                        <strong>🔤 Fonts:</strong> 
                        <?php esc_html_e('Ottimizza il caricamento dei web fonts per migliorare FCP (First Contentful Paint) e ridurre CLS (Cumulative Layout Shift).', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php elseif ($current_tab === 'thirdparty') : ?>
                <div class="fp-ps-tab-description" style="background: #e0e7ff; border-left: 4px solid #6366f1; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                    <p style="margin: 0; color: #3730a3;">
                        <strong>🔌 Third-Party:</strong> 
                        <?php esc_html_e('Gestisci script esterni (Google Analytics, Tag Manager, Facebook Pixel), HTTP/2 Server Push e Smart Asset Delivery con auto-detection intelligente.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Tab Content -->
            <?php
            $tabData = [
                'current_tab' => $current_tab,
                'settings' => $settings,
                'fontSettings' => $fontSettings,
                'thirdPartySettings' => $thirdPartySettings,
                'excludeJs' => $excludeJs,
                'excludeCss' => $excludeCss,
                'criticalScripts' => $criticalScripts,
                'criticalAssets' => $criticalAssets,
                'hints' => $hints,
                'container' => $this->container,
            ];

            echo $this->tabs[$current_tab]->render($tabData);
            ?>
        </div>

        <?php echo ThemeHints::renderTooltipScript(); ?>
        <?php
        return (string) ob_get_clean();
    }

    private function initializeTabs(): void
    {
        $this->tabs = [
            'javascript' => new Tabs\JavaScriptTab(),
            'css' => new Tabs\CssTab(),
            'fonts' => new Tabs\FontsTab(),
            'thirdparty' => new Tabs\ThirdPartyTab(),
        ];
    }
}
