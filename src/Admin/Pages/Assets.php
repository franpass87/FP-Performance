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
use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
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
use function sanitize_textarea_field;
use function sanitize_text_field;
use function wp_unslash;

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
            
            // Debug: Log current settings
            error_log('FP Performance Suite - Current settings loaded: ' . print_r($settings, true));
            
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
                <!-- INTRO BOX -->
                <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                        📦 <?php esc_html_e('Assets Optimization', 'fp-performance-suite'); ?>
                    </h2>
                    <p style="margin: 0; font-size: 16px; line-height: 1.6; opacity: 0.95;">
                        <?php esc_html_e('Ottimizza JavaScript, CSS, Fonts e risorse di terze parti per velocizzare drasticamente il caricamento delle pagine.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <?php if ($message) : ?>
                    <div class="notice notice-success is-dismissible" style="margin: 20px 0; padding: 15px; background: #d1e7dd; border: 1px solid #a3cfbb; border-radius: 6px;">
                        <p style="margin: 0; color: #0f5132; font-weight: 500;">
                            <strong>✅ <?php echo esc_html($message); ?></strong>
                        </p>
                    </div>
                <?php endif; ?>

            <!-- Intelligence Integration Section -->
            <?php if (get_option('fp_ps_intelligence_enabled', false)) : ?>
            <div class="fp-ps-card" style="margin-bottom: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h2 style="margin-top: 0; color: white;">🧠 <?php esc_html_e('Intelligent Asset Optimization', 'fp-performance-suite'); ?></h2>
                <p style="color: rgba(255,255,255,0.9); margin-bottom: 20px;">
                    <?php esc_html_e('Use our AI-powered system for automatic asset optimization, exclusions, and performance-based recommendations.', 'fp-performance-suite'); ?>
                </p>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-intelligence'); ?>" class="button button-primary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                        🎯 <?php esc_html_e('Open Intelligence Dashboard', 'fp-performance-suite'); ?>
                    </a>
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                        <button type="submit" name="use_intelligence_detection" class="button button-secondary" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white;">
                            ⚡ <?php esc_html_e('Auto-Optimize Assets', 'fp-performance-suite'); ?>
                        </button>
                    </form>
                </div>
                <div style="margin-top: 15px; padding: 10px; background: rgba(255,255,255,0.1); border-radius: 4px; font-size: 13px;">
                    <strong>💡 <?php esc_html_e('Tip:', 'fp-performance-suite'); ?></strong>
                    <?php esc_html_e('The Intelligence Dashboard provides automatic detection, performance-based exclusions, and smart optimization recommendations.', 'fp-performance-suite'); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Toggle for Asset Optimization -->
            <div class="fp-ps-card" style="margin-bottom: 20px; background: #f8f9fa; border: 2px solid #e9ecef;">
                <h2 style="margin-top: 0; color: #495057;">⚡ <?php esc_html_e('Asset Optimization Control', 'fp-performance-suite'); ?></h2>
                <form method="post">
                    <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                    <input type="hidden" name="form_type" value="main_toggle" />
                    
                    <label class="fp-ps-toggle" style="display: flex; align-items: flex-start; gap: 10px; font-size: 16px; margin-bottom: 15px;">
                        <input type="checkbox" name="assets_enabled" value="1" <?php checked($settings['enabled'], true); ?> style="transform: scale(1.2); margin-top: 2px; flex-shrink: 0;" />
                        <span class="info" style="text-align: left; flex: 1;">
                            <strong style="display: block;"><?php esc_html_e('Enable Asset Optimization', 'fp-performance-suite'); ?></strong>
                            <small style="color: #6c757d; display: block; margin-top: 4px;">
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
            
            // Check for JavaScript form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'javascript') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $optimizer = $this->container->get(Optimizer::class);
                    $excludeJs = isset($_POST['exclude_js']) 
                        ? wp_unslash($_POST['exclude_js']) 
                        : (!empty($settings['exclude_js']) ? $settings['exclude_js'] : '');
                    
                    $optimizer->update([
                        'defer_js' => !empty($_POST['defer_js']),
                        'async_js' => !empty($_POST['async_js']),
                        'combine_js' => !empty($_POST['combine_js']),
                        'remove_emojis' => !empty($_POST['remove_emojis']),
                        'minify_inline_js' => !empty($_POST['minify_inline_js']),
                        'exclude_js' => $excludeJs,
                    ]);
                    
                    $settings = $optimizer->settings();
                    return __('JavaScript settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for CSS form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'css') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $optimizer = $this->container->get(Optimizer::class);
                    $excludeCss = isset($_POST['exclude_css']) 
                        ? wp_unslash($_POST['exclude_css']) 
                        : (!empty($settings['exclude_css']) ? $settings['exclude_css'] : '');
                    
                    $optimizer->update([
                        'combine_css' => !empty($_POST['combine_css']),
                        'minify_inline_css' => !empty($_POST['minify_inline_css']),
                        'remove_comments' => !empty($_POST['remove_comments']),
                        'optimize_google_fonts' => !empty($_POST['optimize_google_fonts_assets']),
                        'exclude_css' => $excludeCss,
                    ]);
                    
                    $settings = $optimizer->settings();
                    return __('CSS settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Third Party form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'third_party') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
                    $thirdPartyScripts->updateSettings([
                        'enabled' => !empty($_POST['third_party_enabled']),
                        'auto_detect' => !empty($_POST['third_party_auto_detect']),
                        'exclude_critical' => !empty($_POST['third_party_exclude_critical']),
                        'delay_loading' => !empty($_POST['third_party_delay_loading']),
                        'custom_scripts' => isset($_POST['third_party_custom_scripts']) ? wp_unslash($_POST['third_party_custom_scripts']) : '',
                    ]);
                    
                    $thirdPartySettings = $thirdPartyScripts->settings();
                    return __('Third Party settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for HTTP2 Push form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'http2_push') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $http2Push = $this->container->get(Http2ServerPush::class);
                    $http2Push->updateSettings([
                        'enabled' => !empty($_POST['http2_push_enabled']),
                        'critical_css' => !empty($_POST['http2_push_critical_css']),
                        'critical_js' => !empty($_POST['http2_push_critical_js']),
                        'critical_fonts' => !empty($_POST['http2_push_critical_fonts']),
                    ]);
                    
                    return __('HTTP/2 Server Push settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Smart Delivery form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'smart_delivery') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $smartDelivery = $this->container->get(SmartAssetDelivery::class);
                    $smartDelivery->updateSettings([
                        'enabled' => !empty($_POST['smart_delivery_enabled']),
                        'adapt_images' => !empty($_POST['smart_adaptive_images']),
                        'adapt_videos' => !empty($_POST['smart_adaptive_videos']),
                        'slow_quality' => (int) ($_POST['smart_quality_slow'] ?? 60),
                        'fast_quality' => (int) ($_POST['smart_quality_fast'] ?? 85),
                    ]);
                    
                    return __('Smart Asset Delivery settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Unused CSS form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'unusedcss') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $unusedCssOptimizer = new UnusedCSSOptimizer();
                    
                    // Debug: Log Unused CSS POST data
                    error_log('FP Performance Suite - Unused CSS POST data: ' . print_r($_POST, true));
                    
                    $unusedCssOptimizer->updateSettings([
                        'enabled' => !empty($_POST['unusedcss_enabled']),
                        'remove_unused_css' => !empty($_POST['unusedcss_remove_unused_css']),
                        'defer_non_critical' => !empty($_POST['unusedcss_defer_non_critical']),
                        'inline_critical' => !empty($_POST['unusedcss_inline_critical']),
                        'enable_css_purging' => !empty($_POST['unusedcss_enable_css_purging']),
                        'critical_css' => isset($_POST['unusedcss_critical_css']) ? sanitize_textarea_field(wp_unslash($_POST['unusedcss_critical_css'])) : '',
                    ]);
                    
                    // Debug: Log saved settings
                    $savedSettings = $unusedCssOptimizer->getSettings();
                    error_log('FP Performance Suite - Unused CSS settings saved: ' . print_r($savedSettings, true));
                    
                    return __('Unused CSS settings saved successfully!', 'fp-performance-suite');
                } else {
                    error_log('FP Performance Suite - Unused CSS nonce verification failed');
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Critical CSS form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'criticalcss') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $criticalCssService = new CriticalCss();
                    if (isset($_POST['critical_css'])) {
                        $result = $criticalCssService->update(wp_unslash($_POST['critical_css']));
                        if (!$result['success']) {
                            return __('Error saving Critical CSS: ' . ($result['error'] ?? 'Unknown error'), 'fp-performance-suite');
                        }
                    }
                    
                    return __('Critical CSS settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Critical Path Fonts form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'critical_path_fonts') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $fontOptimizer = $this->container->get(FontOptimizer::class);
                    $fontOptimizer->updateSettings([
                        'enabled' => !empty($_POST['fonts_enabled']),
                        'preload_critical' => !empty($_POST['fonts_preload_critical']),
                        'display_swap' => !empty($_POST['fonts_display_swap']),
                        'subset_critical' => !empty($_POST['fonts_subset_critical']),
                        'critical_fonts' => isset($_POST['fonts_critical_fonts']) ? wp_unslash($_POST['fonts_critical_fonts']) : '',
                    ]);
                    
                    $fontSettings = $fontOptimizer->getSettings();
                    return __('Critical Path Fonts settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Script Detector form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'script_detector') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
                    $scriptDetector = new ThirdPartyScriptDetector($thirdPartyScripts);
                    
                    if (isset($_POST['action_scan'])) {
                        $scriptDetector->scanHomepage();
                        return __('Scan completed! Check suggestions below.', 'fp-performance-suite');
                    } elseif (isset($_POST['action_add_custom'])) {
                        $scriptDetector->addCustomScript([
                            'name' => sanitize_text_field($_POST['script_name'] ?? ''),
                            'patterns' => array_filter(array_map('trim', explode("\n", wp_unslash($_POST['script_patterns'] ?? '')))),
                            'enabled' => !empty($_POST['script_enabled']),
                            'delay' => !empty($_POST['script_delay']),
                        ]);
                        return __('Custom script added successfully!', 'fp-performance-suite');
                    }
                    
                    return __('Script Detector settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Critical Path Fonts form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'critical_path_fonts') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    $criticalPathOptimizer = new \FP\PerfSuite\Services\Assets\CriticalPathOptimizer();
                    $criticalPathOptimizer->updateSettings([
                        'enabled' => !empty($_POST['critical_path_enabled']),
                        'preload_critical_fonts' => !empty($_POST['preload_critical_fonts']),
                        'optimize_google_fonts' => !empty($_POST['optimize_google_fonts']),
                        'preconnect_providers' => !empty($_POST['preconnect_providers']),
                        'inject_font_display' => !empty($_POST['inject_font_display']),
                        'add_resource_hints' => !empty($_POST['add_resource_hints']),
                    ]);
                    
                    $fontOptimizer = new \FP\PerfSuite\Services\Assets\FontOptimizer();
                    $fontOptimizer->updateSettings([
                        'preload_fonts' => !empty($_POST['preload_fonts']),
                    ]);
                    
                    return __('Font & Critical Path settings saved successfully!', 'fp-performance-suite');
                } else {
                    return __('Error: Nonce verification failed. Please try again.', 'fp-performance-suite');
                }
            }
            
            // Check for Advanced JS Optimization form
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'advanced_js_optimization') {
                if (isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
                    // Unused JavaScript Optimizer
                    if (isset($_POST['unused_optimization'])) {
                        $unusedJsOptimizer = new UnusedJavaScriptOptimizer();
                        $unusedJsOptimizer->updateSettings([
                            'enabled' => !empty($_POST['unused_optimization']['enabled']),
                        ]);
                    }
                    
                    // Code Splitting Manager
                    if (isset($_POST['code_splitting'])) {
                        $codeSplittingManager = new \FP\PerfSuite\Services\Assets\CodeSplittingManager();
                        $codeSplittingManager->updateSettings([
                            'enabled' => !empty($_POST['code_splitting']['enabled']),
                        ]);
                    }
                    
                    // Tree Shaking
                    if (isset($_POST['tree_shaking'])) {
                        $treeShaker = new \FP\PerfSuite\Services\Assets\JavaScriptTreeShaker();
                        $treeShaker->updateSettings([
                            'enabled' => !empty($_POST['tree_shaking']['enabled']),
                        ]);
                    }
                    
                    return __('Advanced JS Optimization settings saved successfully!', 'fp-performance-suite');
                } else {
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
