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
use FP\PerfSuite\Admin\Pages\Assets\Tabs\JavaScriptTab;
use FP\PerfSuite\Admin\Pages\Assets\Tabs\CssTab;
use FP\PerfSuite\Admin\Pages\Assets\Tabs\FontsTab;
use FP\PerfSuite\Admin\Pages\Assets\Tabs\ThirdPartyTab;

use function __;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function sanitize_key;

class Assets extends AbstractPage
{
    private array $tabs = [];

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
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
        try {
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
            if ('POST' === $_SERVER['REQUEST_METHOD']) {
                $message = $this->handleDirectFormSubmission($settings, $fontSettings, $thirdPartySettings);
            }
            
            // Debug: Log if we have a message
            if ($message) {
                error_log('FP Performance Suite - Assets page message: ' . $message);
            }
            
            // Get current tab
            $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'javascript';
            $valid_tabs = ['javascript', 'css', 'fonts', 'thirdparty'];
            if (!in_array($current_tab, $valid_tabs, true)) {
                $current_tab = 'javascript';
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('FP Performance Suite - Assets page initialization error: ' . $e->getMessage());
            error_log('FP Performance Suite - Assets page stack trace: ' . $e->getTraceAsString());
            
            // Return error message instead of empty page
            return '<div class="wrap"><div class="notice notice-error"><p><strong>Errore:</strong> ' . esc_html($e->getMessage()) . '</p></div></div>';
        }

        try {
            ob_start();
            ?>
            <div class="wrap">
                <h1><?php esc_html_e('Assets Optimization', 'fp-performance-suite'); ?></h1>
                
                <?php if ($message) : ?>
                    <div class="notice notice-success is-dismissible" style="margin: 20px 0; padding: 15px; background: #d1e7dd; border: 1px solid #a3cfbb; border-radius: 6px;">
                        <p style="margin: 0; color: #0f5132; font-weight: 500;">
                            <strong>✅ <?php echo esc_html($message); ?></strong>
                        </p>
                    </div>
                <?php endif; ?>

            <!-- Main Toggle for Asset Optimization -->
            <div class="fp-ps-card" style="margin-bottom: 20px; background: #f8f9fa; border: 2px solid #e9ecef;">
                <h2 style="margin-top: 0; color: #495057;">⚡ <?php esc_html_e('Asset Optimization Control', 'fp-performance-suite'); ?></h2>
                <form method="post">
                    <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                    <input type="hidden" name="form_type" value="main_toggle" />
                    
                    <label class="fp-ps-toggle" style="display: flex; align-items: center; gap: 10px; font-size: 16px; margin-bottom: 15px;">
                        <input type="checkbox" name="assets_enabled" value="1" <?php checked(!empty($settings['enabled'])); ?> style="transform: scale(1.2);" />
                        <span class="info">
                            <strong><?php esc_html_e('Enable Asset Optimization', 'fp-performance-suite'); ?></strong>
                            <br>
                            <small style="color: #6c757d;">
                                <?php esc_html_e('Master switch to enable/disable all asset optimization features. When disabled, no optimization will be applied.', 'fp-performance-suite'); ?>
                            </small>
                        </span>
                    </label>
                    
                    <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 10px; margin: 10px 0;">
                        <p style="margin: 0; font-size: 14px; color: #1565c0;">
                            <strong>ℹ️ <?php esc_html_e('Note:', 'fp-performance-suite'); ?></strong>
                            <?php esc_html_e('This is the main control for asset optimization. Individual features in the tabs below will only work when this is enabled.', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                    
                    <button type="submit" class="button button-primary" style="margin-top: 10px;">
                        <?php esc_html_e('Save Settings', 'fp-performance-suite'); ?>
                    </button>
                </form>
            </div>

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
            
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('FP Performance Suite - Assets page rendering error: ' . $e->getMessage());
            error_log('FP Performance Suite - Assets page rendering stack trace: ' . $e->getTraceAsString());
            
            // Return error message instead of empty page
            return '<div class="wrap"><div class="notice notice-error"><p><strong>Errore nel rendering:</strong> ' . esc_html($e->getMessage()) . '</p></div></div>';
        }
    }

    private function initializeTabs(): void
    {
        $this->tabs = [
            'javascript' => new JavaScriptTab(),
            'css' => new CssTab(),
            'fonts' => new FontsTab(),
            'thirdparty' => new ThirdPartyTab(),
        ];
    }

    /**
     * Fallback method to handle direct form submissions
     */
    private function handleDirectFormSubmission(array &$settings, array &$fontSettings, array &$thirdPartySettings): string
    {
        try {
            // Debug: Log POST data
            error_log('FP Performance Suite - POST data: ' . print_r($_POST, true));
            
            // Check for main toggle form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'main_toggle') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $optimizer = $this->container->get(Optimizer::class);
                    $currentSettings = $optimizer->settings();
                    
                    // Debug: Log current settings
                    error_log('FP Performance Suite - Current settings: ' . print_r($currentSettings, true));
                    
                    // Corretto: gestisce sia stati checked che unchecked
                    $currentSettings['enabled'] = isset($_POST['assets_enabled']) && $_POST['assets_enabled'] === '1';
                    
                    // Debug: Log new settings
                    error_log('FP Performance Suite - New settings: ' . print_r($currentSettings, true));
                    
                    $result = $optimizer->update($currentSettings);
                    
                    // Debug: Log result
                    error_log('FP Performance Suite - Update result: ' . ($result ? 'success' : 'failed'));
                    
                    $settings = $optimizer->settings();
                    
                    // Debug: Log final settings
                    error_log('FP Performance Suite - Final settings: ' . print_r($settings, true));
                    
                    return __('Asset optimization settings saved successfully!', 'fp-performance-suite');
                } else {
                    error_log('FP Performance Suite - Nonce verification failed');
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            return '';
            
        } catch (\Exception $e) {
            error_log('FP Performance Suite - Form submission error: ' . $e->getMessage());
            return __('Error saving settings: ' . $e->getMessage(), 'fp-performance-suite');
        }
    }
}
