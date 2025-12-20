<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Handlers;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector;
use FP\PerfSuite\Services\Assets\Http2ServerPush;
use FP\PerfSuite\Services\Assets\SmartAssetDelivery;
use FP\PerfSuite\Services\Assets\UnusedCSSOptimizer;
use FP\PerfSuite\Services\Assets\CriticalCss;
use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;
use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;
use FP\PerfSuite\Services\Intelligence\CriticalAssetsDetector;

use function __;
use function sanitize_key;
use function sanitize_text_field;
use function sprintf;
use function wp_unslash;
use function wp_verify_nonce;

class PostHandler
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    public function handlePost(array &$settings, array &$fontSettings, array &$thirdPartySettings): string
    {
        // Verifica che sia una richiesta POST
        if (!isset($_POST['fp_ps_assets_nonce']) || !wp_verify_nonce(wp_unslash($_POST['fp_ps_assets_nonce']), 'fp-ps-assets')) {
            return '';
        }

        $message = '';
        
        // Gestione errori per prevenire pagine vuote
        try {
            // Handle success messages from redirects
            if (isset($_GET['msg'])) {
                switch (sanitize_key(wp_unslash($_GET['msg']))) {
                    case 'js_excluded':
                        $message = __('JavaScript exclusions applied successfully!', 'fp-performance-suite');
                        break;
                    case 'css_excluded':
                        $message = __('CSS exclusions applied successfully!', 'fp-performance-suite');
                        break;
                    case 'assets_applied':
                        $count = (int) ($_GET['count'] ?? 0);
                        $message = sprintf(__('%d critical assets applied successfully!', 'fp-performance-suite'), $count);
                        break;
                    case 'third_party_saved':
                        $message = __('Third-Party Script settings saved successfully!', 'fp-performance-suite');
                        break;
                    case 'http2_push_saved':
                        $message = __('HTTP/2 Server Push settings saved successfully!', 'fp-performance-suite');
                        break;
                    case 'smart_delivery_saved':
                        $message = __('Smart Asset Delivery settings saved successfully!', 'fp-performance-suite');
                        break;
                    case 'css_saved':
                        $message = __('CSS settings saved successfully!', 'fp-performance-suite');
                        break;
                    case 'javascript_saved':
                        $message = __('JavaScript settings saved successfully!', 'fp-performance-suite');
                        break;
                    case 'main_toggle_saved':
                        $message = __('Asset optimization settings saved successfully!', 'fp-performance-suite');
                        break;
                }
            }

            // Handle intelligence integration redirects
            if (isset($_POST['use_intelligence_detection'])) {
                $redirect_url = admin_url('admin.php?page=fp-performance-suite-intelligence');
                wp_redirect($redirect_url);
                exit;
            }

            if (isset($_POST['auto_detect_critical_assets'])) {
                // Use dependency injection from container
                $optimizer = $this->container->has(Optimizer::class)
                    ? $this->container->get(Optimizer::class)
                    : new Optimizer();
                $assetsDetector = $this->container->has(CriticalAssetsDetector::class)
                    ? $this->container->get(CriticalAssetsDetector::class)
                    : new CriticalAssetsDetector();
                $result = $assetsDetector->autoApplyCriticalAssets(false, $optimizer);
                $count = $result['applied'] ?? 0;
                $message = sprintf(__('%d critical assets applied successfully!', 'fp-performance-suite'), $count);
            }

            // Handle form submissions
            $formType = sanitize_text_field($_POST['form_type'] ?? '');
            
            switch ($formType) {
                case 'main_toggle':
                    $message = $this->handleMainToggleForm($settings);
                    break;
                case 'javascript':
                    $message = $this->handleJavaScriptForm($settings);
                    break;
                case 'css':
                    $message = $this->handleCssForm($settings);
                    break;
                case 'third_party':
                    $message = $this->handleThirdPartyForm($thirdPartySettings);
                    break;
                case 'http2_push':
                    $message = $this->handleHttp2PushForm();
                    break;
                case 'smart_delivery':
                    $message = $this->handleSmartDeliveryForm();
                    break;
                case 'unusedcss':
                    $message = $this->handleUnusedCssForm();
                    break;
                case 'criticalcss':
                    $message = $this->handleCriticalCssForm();
                    break;
                case 'critical_path_fonts':
                    $message = $this->handleCriticalPathFontsForm($fontSettings);
                    break;
                case 'script_detector':
                    $message = $this->handleScriptDetectorForm();
                    break;
                case 'advanced_js_optimization':
                    $message = $this->handleAdvancedJsOptimizationForm();
                    break;
            }

            return $message;
        
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'PostHandler error');
            return sprintf(
                __('Errore durante il salvataggio: %s. Contatta il supporto se il problema persiste.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleJavaScriptForm(array &$settings): string
    {
        try {
            // Use dependency injection from container
            $optimizer = $this->container->has(Optimizer::class)
                ? $this->container->get(Optimizer::class)
                : new Optimizer();
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
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'JavaScript Form Error');
            return sprintf(
                __('Error saving JavaScript settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleCssForm(array &$settings): string
    {
        try {
            // Use dependency injection from container
            $optimizer = $this->container->has(Optimizer::class)
                ? $this->container->get(Optimizer::class)
                : new Optimizer();
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
            
            // Handle Unused CSS settings - use dependency injection
            $unusedCssOptimizer = $this->container->has(UnusedCSSOptimizer::class)
                ? $this->container->get(UnusedCSSOptimizer::class)
                : new UnusedCSSOptimizer();
            $unusedCssOptimizer->updateSettings([
                'enabled' => !empty($_POST['unusedcss_enabled']),
                'remove_unused_css' => !empty($_POST['unusedcss_remove_unused_css']),
                'defer_non_critical' => !empty($_POST['unusedcss_defer_non_critical']),
            ]);

            // Handle Critical CSS settings - use dependency injection
            $criticalCssService = $this->container->has(CriticalCss::class)
                ? $this->container->get(CriticalCss::class)
                : new CriticalCss();
            if (isset($_POST['critical_css'])) {
                $result = $criticalCssService->update(wp_unslash($_POST['critical_css']));
                if (!$result['success']) {
                    throw new \Exception($result['error'] ?? 'Errore nel salvataggio del Critical CSS');
                }
            }
            
            $settings = $optimizer->settings();
            
            return __('CSS settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'CSS Form Error');
            return sprintf(
                __('Error saving CSS settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleThirdPartyForm(array &$thirdPartySettings): string
    {
        try {
            $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
            $thirdPartyScripts->updateSettings([
                'enabled' => !empty($_POST['third_party_enabled']),
                'delay_all' => !empty($_POST['third_party_delay_all']),
                'delay_timeout' => (int) ($_POST['third_party_timeout'] ?? 5000),
                'load_on' => sanitize_text_field($_POST['third_party_load_on'] ?? 'interaction'),
                'scripts' => [
                    'google_analytics' => ['enabled' => !empty($_POST['third_party_ga']), 'delay' => true],
                    'facebook_pixel' => ['enabled' => !empty($_POST['third_party_fb']), 'delay' => true],
                    'google_ads' => ['enabled' => !empty($_POST['third_party_ads']), 'delay' => true],
                    'hotjar' => ['enabled' => !empty($_POST['third_party_hotjar']), 'delay' => true],
                    'intercom' => ['enabled' => !empty($_POST['third_party_intercom']), 'delay' => true],
                    'youtube' => ['enabled' => !empty($_POST['third_party_youtube']), 'delay' => true],
                    'linkedin_insight' => ['enabled' => !empty($_POST['third_party_linkedin']), 'delay' => true],
                    'twitter_pixel' => ['enabled' => !empty($_POST['third_party_twitter']), 'delay' => true],
                    'tiktok_pixel' => ['enabled' => !empty($_POST['third_party_tiktok']), 'delay' => true],
                    'pinterest_tag' => ['enabled' => !empty($_POST['third_party_pinterest']), 'delay' => true],
                    'hubspot' => ['enabled' => !empty($_POST['third_party_hubspot']), 'delay' => true],
                    'zendesk' => ['enabled' => !empty($_POST['third_party_zendesk']), 'delay' => true],
                    'drift' => ['enabled' => !empty($_POST['third_party_drift']), 'delay' => true],
                    'crisp' => ['enabled' => !empty($_POST['third_party_crisp']), 'delay' => true],
                    'tidio' => ['enabled' => !empty($_POST['third_party_tidio']), 'delay' => true],
                    'segment' => ['enabled' => !empty($_POST['third_party_segment']), 'delay' => true],
                    'mixpanel' => ['enabled' => !empty($_POST['third_party_mixpanel']), 'delay' => true],
                    'mailchimp' => ['enabled' => !empty($_POST['third_party_mailchimp']), 'delay' => true],
                    'stripe' => ['enabled' => !empty($_POST['third_party_stripe']), 'delay' => true],
                    'paypal' => ['enabled' => !empty($_POST['third_party_paypal']), 'delay' => true],
                    'recaptcha' => ['enabled' => !empty($_POST['third_party_recaptcha']), 'delay' => true],
                    'google_maps' => ['enabled' => !empty($_POST['third_party_gmaps']), 'delay' => true],
                    'microsoft_clarity' => ['enabled' => !empty($_POST['third_party_clarity']), 'delay' => true],
                    'vimeo' => ['enabled' => !empty($_POST['third_party_vimeo']), 'delay' => true],
                    'tawk_to' => ['enabled' => !empty($_POST['third_party_tawk']), 'delay' => true],
                    'optimizely' => ['enabled' => !empty($_POST['third_party_optimizely']), 'delay' => true],
                    'trustpilot' => ['enabled' => !empty($_POST['third_party_trustpilot']), 'delay' => true],
                    'klaviyo' => ['enabled' => !empty($_POST['third_party_klaviyo']), 'delay' => true],
                    'onetrust' => ['enabled' => !empty($_POST['third_party_onetrust']), 'delay' => true],
                    'calendly' => ['enabled' => !empty($_POST['third_party_calendly']), 'delay' => true],
                    'fullstory' => ['enabled' => !empty($_POST['third_party_fullstory']), 'delay' => true],
                    'snapchat_pixel' => ['enabled' => !empty($_POST['third_party_snapchat']), 'delay' => true],
                    'soundcloud' => ['enabled' => !empty($_POST['third_party_soundcloud']), 'delay' => true],
                    'klarna' => ['enabled' => !empty($_POST['third_party_klarna']), 'delay' => true],
                    'spotify' => ['enabled' => !empty($_POST['third_party_spotify']), 'delay' => true],
                    'livechat' => ['enabled' => !empty($_POST['third_party_livechat']), 'delay' => true],
                    'activecampaign' => ['enabled' => !empty($_POST['third_party_activecampaign']), 'delay' => true],
                    'userway' => ['enabled' => !empty($_POST['third_party_userway']), 'delay' => true],
                    'typeform' => ['enabled' => !empty($_POST['third_party_typeform']), 'delay' => true],
                ],
            ]);
            
            // Ricarica le impostazioni dopo il salvataggio
            $thirdPartySettings = $thirdPartyScripts->settings();
            
            return __('Third-Party Script settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Third Party Form Error');
            return sprintf(
                __('Error saving Third-Party settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleHttp2PushForm(): string
    {
        try {
            $http2Push = $this->container->get(Http2ServerPush::class);
            $http2Push->updateSettings([
                'enabled' => !empty($_POST['http2_push_enabled']),
                'push_critical_css' => !empty($_POST['http2_push_css']),
                'push_critical_js' => !empty($_POST['http2_push_js']),
                'push_fonts' => !empty($_POST['http2_push_fonts']),
                'max_push_assets' => (int) ($_POST['http2_max_resources'] ?? 10),
            ]);
            
            return __('HTTP/2 Server Push settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'HTTP/2 Push Form Error');
            return sprintf(
                __('Error saving HTTP/2 Push settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleSmartDeliveryForm(): string
    {
        try {
            $smartDelivery = $this->container->get(SmartAssetDelivery::class);
            $smartDelivery->updateSettings([
                'enabled' => !empty($_POST['smart_delivery_enabled']),
                'adapt_images' => !empty($_POST['smart_adaptive_images']),
                'adapt_videos' => !empty($_POST['smart_adaptive_videos']),
                'slow_quality' => (int) ($_POST['smart_quality_slow'] ?? 60),
                'fast_quality' => (int) ($_POST['smart_quality_fast'] ?? 85),
            ]);
            
            return __('Smart Asset Delivery settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Smart Delivery Form Error');
            return sprintf(
                __('Error saving Smart Delivery settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleUnusedCssForm(): string
    {
        // Use dependency injection from container
        $unusedCssOptimizer = $this->container->has(UnusedCSSOptimizer::class)
            ? $this->container->get(UnusedCSSOptimizer::class)
            : new UnusedCSSOptimizer();
        $unusedCssOptimizer->updateSettings([
            'enabled' => !empty($_POST['unusedcss_enabled']),
            'remove_unused_css' => !empty($_POST['unusedcss_remove_unused_css']),
            'defer_non_critical' => !empty($_POST['unusedcss_defer_non_critical']),
            'inline_critical' => !empty($_POST['unusedcss_inline_critical']),
            'enable_css_purging' => !empty($_POST['unusedcss_enable_css_purging']),
            'critical_css' => isset($_POST['unusedcss_critical_css']) ? sanitize_textarea_field(wp_unslash($_POST['unusedcss_critical_css'])) : '',
        ]);
        return __('Unused CSS settings saved.', 'fp-performance-suite');
    }

    private function handleCriticalCssForm(): string
    {
        // Use dependency injection from container
        $criticalCssService = $this->container->has(CriticalCss::class)
            ? $this->container->get(CriticalCss::class)
            : new CriticalCss();
        if (isset($_POST['critical_css'])) {
            $criticalCssService->update(wp_unslash($_POST['critical_css']));
            return __('Critical CSS saved successfully!', 'fp-performance-suite');
        }
        return __('Critical CSS could not be saved.', 'fp-performance-suite');
    }

    private function handleCriticalPathFontsForm(array &$fontSettings): string
    {
        // Use dependency injection from container
        $criticalPathOptimizer = $this->container->has(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class)
            ? $this->container->get(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class)
            : new \FP\PerfSuite\Services\Assets\CriticalPathOptimizer();
        $criticalPathOptimizer->updateSettings([
            'enabled' => !empty($_POST['critical_path_enabled']),
            'preload_critical_fonts' => !empty($_POST['preload_critical_fonts']),
            'optimize_google_fonts' => !empty($_POST['optimize_google_fonts']),
            'preconnect_providers' => !empty($_POST['preconnect_providers']),
            'inject_font_display' => !empty($_POST['inject_font_display']),
            'add_resource_hints' => !empty($_POST['add_resource_hints']),
        ]);
        
        $fontOptimizer = $this->container->has(FontOptimizer::class)
            ? $this->container->get(FontOptimizer::class)
            : new FontOptimizer();
        $fontOptimizer->updateSettings([
            'preload_fonts' => !empty($_POST['preload_fonts']),
        ]);
        
        // Re-registra i servizi per applicare immediatamente le modifiche
        $this->reregisterFontServices();
        
        return __('Font & Critical Path settings saved successfully!', 'fp-performance-suite');
    }
    
    /**
     * Re-registra i servizi di ottimizzazione font dopo il salvataggio
     */
    private function reregisterFontServices(): void
    {
        try {
            $container = \FP\PerfSuite\Plugin::container();
            
            // Re-registra Critical Path Optimizer se abilitato
            $criticalPathSettings = get_option('fp_ps_critical_path_optimization', []);
            if (!empty($criticalPathSettings['enabled'])) {
                if ($container->has(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class)) {
                    $criticalPathOptimizer = $container->get(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class);
                    $criticalPathOptimizer->register();
                }
            }
            
            // Re-registra Font Optimizer se abilitato
            $fontSettings = get_option('fp_ps_font_optimization', []);
            $fontOptimizationEnabled = get_option('fp_ps_font_optimization_enabled', false);
            
            if (!empty($fontSettings['enabled']) || $fontOptimizationEnabled || !empty($criticalPathSettings['enabled'])) {
                if ($container->has(\FP\PerfSuite\Services\Assets\FontOptimizer::class)) {
                    $fontOptimizer = $container->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class);
                    $fontOptimizer->register();
                }
            }
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Errore nella re-registrazione dei servizi font');
        }
    }

    private function handleScriptDetectorForm(): string
    {
        $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
        // Use dependency injection from container
        $scriptDetector = $this->container->has(ThirdPartyScriptDetector::class)
            ? $this->container->get(ThirdPartyScriptDetector::class)
            : new ThirdPartyScriptDetector($thirdPartyScripts);
        
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
        } elseif (isset($_POST['action_auto_add'])) {
            $hash = sanitize_text_field($_POST['script_hash'] ?? '');
            if ($scriptDetector->autoAddFromSuggestion($hash)) {
                return __('Script automatically added to managed list!', 'fp-performance-suite');
            }
        } elseif (isset($_POST['action_dismiss'])) {
            $hash = sanitize_text_field($_POST['script_hash'] ?? '');
            $scriptDetector->dismissScript($hash);
            return __('Suggestion dismissed.', 'fp-performance-suite');
        } elseif (isset($_POST['action_remove_custom'])) {
            $key = sanitize_key(wp_unslash($_POST['custom_key'] ?? ''));
            if ($scriptDetector->removeCustomScript($key)) {
                return __('Custom script removed.', 'fp-performance-suite');
            }
        }
        
        return '';
    }

    private function handleAdvancedJsOptimizationForm(): string
    {
        // BUGFIX #18a: Tutti e 3 i servizi avanzati JavaScript usano updateSettings(), non update()
        
        // Handle unused JavaScript optimization settings - use dependency injection
        if (isset($_POST['unused_optimization'])) {
            $unusedOptimizer = $this->container->has(UnusedJavaScriptOptimizer::class)
                ? $this->container->get(UnusedJavaScriptOptimizer::class)
                : new UnusedJavaScriptOptimizer();
            $unusedOptimizer->updateSettings($_POST['unused_optimization']);
        }

        // Handle code splitting settings - use dependency injection
        if (isset($_POST['code_splitting'])) {
            $codeSplittingManager = $this->container->has(CodeSplittingManager::class)
                ? $this->container->get(CodeSplittingManager::class)
                : new CodeSplittingManager();
            $codeSplittingManager->updateSettings($_POST['code_splitting']);
        }

        // Handle tree shaking settings - use dependency injection
        if (isset($_POST['tree_shaking'])) {
            $treeShaker = $this->container->has(JavaScriptTreeShaker::class)
                ? $this->container->get(JavaScriptTreeShaker::class)
                : new JavaScriptTreeShaker();
            $treeShaker->updateSettings($_POST['tree_shaking']);
        }

        return __('Advanced JavaScript optimization settings saved successfully!', 'fp-performance-suite');
    }

    private function handleMainToggleForm(array &$settings): string
    {
        try {
            // Use dependency injection from container
            $optimizer = $this->container->has(Optimizer::class)
                ? $this->container->get(Optimizer::class)
                : new Optimizer();
            
            // Get current settings
            $currentSettings = $optimizer->settings();
            
            // Update the main enabled flag - ensure we handle both checked and unchecked states
            $currentSettings['enabled'] = isset($_POST['assets_enabled']) && $_POST['assets_enabled'] === '1';
            
            // Save the updated settings
            $result = $optimizer->update($currentSettings);
            
            // Update the settings array for the view
            $settings = $optimizer->settings();
            
            // Verify the setting was actually saved
            $savedSettings = $optimizer->settings();
            
            // Return success message instead of redirect to avoid page issues
            return __('Asset optimization settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Main Toggle Error');
            return sprintf(
                __('Error saving settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }
}