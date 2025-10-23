<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Handlers;

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
    public function handlePost(array &$settings, array &$fontSettings, array &$thirdPartySettings): string
    {
        if (!isset($_POST['fp_ps_assets_nonce']) || !wp_verify_nonce($_POST['fp_ps_assets_nonce'], 'fp-ps-assets')) {
            return '';
        }

        $message = '';
        
        // Gestione errori per prevenire pagine vuote
        try {
            // Handle success messages from redirects
            if (isset($_GET['msg'])) {
                switch ($_GET['msg']) {
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

            // Handle auto-detection actions
            if (isset($_POST['auto_detect_exclude_js'])) {
                $optimizer = new Optimizer();
                $smartDetector = new SmartExclusionDetector();
                $result = $smartDetector->detectExcludeJs();
                set_transient('fp_ps_exclude_js_detected', $result, HOUR_IN_SECONDS);
                $message = __('JavaScript exclusions detected successfully!', 'fp-performance-suite');
            }

            if (isset($_POST['auto_detect_exclude_css'])) {
                $optimizer = new Optimizer();
                $smartDetector = new SmartExclusionDetector();
                $result = $smartDetector->detectExcludeCss();
                set_transient('fp_ps_exclude_css_detected', $result, HOUR_IN_SECONDS);
                $message = __('CSS exclusions detected successfully!', 'fp-performance-suite');
            }

            if (isset($_POST['apply_js_exclusions'])) {
                $optimizer = new Optimizer();
                $smartDetector = new SmartExclusionDetector();
                $result = $smartDetector->detectExcludeJs();
                set_transient('fp_ps_exclude_js_detected', $result, HOUR_IN_SECONDS);
                $message = __('JavaScript exclusions applied successfully!', 'fp-performance-suite');
            }

            if (isset($_POST['apply_css_exclusions'])) {
                $optimizer = new Optimizer();
                $smartDetector = new SmartExclusionDetector();
                $result = $smartDetector->detectExcludeCss();
                set_transient('fp_ps_exclude_css_detected', $result, HOUR_IN_SECONDS);
                $message = __('CSS exclusions applied successfully!', 'fp-performance-suite');
            }

            if (isset($_POST['auto_detect_critical_assets'])) {
                $optimizer = new Optimizer();
                $assetsDetector = new CriticalAssetsDetector();
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
        
        } catch (\Exception $e) {
            // Log dell'errore per debug
            error_log('FP Performance Suite - PostHandler Error: ' . $e->getMessage());
            
            // Ritorna un messaggio di errore user-friendly
            return sprintf(
                __('Errore durante il salvataggio: %s. Contatta il supporto se il problema persiste.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleJavaScriptForm(array &$settings): string
    {
        try {
            $optimizer = new Optimizer();
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
            
        } catch (\Exception $e) {
            error_log('FP Performance Suite - JavaScript Form Error: ' . $e->getMessage());
            return sprintf(
                __('Error saving JavaScript settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleCssForm(array &$settings): string
    {
        try {
            $optimizer = new Optimizer();
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
            
            // Handle Unused CSS settings
            $unusedCssOptimizer = new UnusedCSSOptimizer();
            $unusedCssOptimizer->updateSettings([
                'enabled' => !empty($_POST['unusedcss_enabled']),
                'remove_unused_css' => !empty($_POST['unusedcss_remove_unused_css']),
                'defer_non_critical' => !empty($_POST['unusedcss_defer_non_critical']),
            ]);

            // Handle Critical CSS settings
            $criticalCssService = new CriticalCss();
            if (isset($_POST['critical_css'])) {
                $result = $criticalCssService->update(wp_unslash($_POST['critical_css']));
                if (!$result['success']) {
                    throw new \Exception($result['error'] ?? 'Errore nel salvataggio del Critical CSS');
                }
            }
            
            $settings = $optimizer->settings();
            
            return __('CSS settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Exception $e) {
            error_log('FP Performance Suite - CSS Form Error: ' . $e->getMessage());
            return sprintf(
                __('Error saving CSS settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleThirdPartyForm(array &$thirdPartySettings): string
    {
        try {
            $thirdPartyScripts = new ThirdPartyScriptManager();
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
            
        } catch (\Exception $e) {
            error_log('FP Performance Suite - Third Party Form Error: ' . $e->getMessage());
            return sprintf(
                __('Error saving Third-Party settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleHttp2PushForm(): string
    {
        try {
            $http2Push = new Http2ServerPush();
            $http2Push->updateSettings([
                'enabled' => !empty($_POST['http2_push_enabled']),
                'push_critical_css' => !empty($_POST['http2_push_css']),
                'push_critical_js' => !empty($_POST['http2_push_js']),
                'push_fonts' => !empty($_POST['http2_push_fonts']),
                'max_push_assets' => (int) ($_POST['http2_max_resources'] ?? 10),
            ]);
            
            return __('HTTP/2 Server Push settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Exception $e) {
            error_log('FP Performance Suite - HTTP/2 Push Form Error: ' . $e->getMessage());
            return sprintf(
                __('Error saving HTTP/2 Push settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleSmartDeliveryForm(): string
    {
        try {
            $smartDelivery = new SmartAssetDelivery();
            $smartDelivery->updateSettings([
                'enabled' => !empty($_POST['smart_delivery_enabled']),
                'adapt_images' => !empty($_POST['smart_adaptive_images']),
                'adapt_videos' => !empty($_POST['smart_adaptive_videos']),
                'slow_quality' => (int) ($_POST['smart_quality_slow'] ?? 60),
                'fast_quality' => (int) ($_POST['smart_quality_fast'] ?? 85),
            ]);
            
            return __('Smart Asset Delivery settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Exception $e) {
            error_log('FP Performance Suite - Smart Delivery Form Error: ' . $e->getMessage());
            return sprintf(
                __('Error saving Smart Delivery settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }

    private function handleUnusedCssForm(): string
    {
        $unusedCssOptimizer = new UnusedCSSOptimizer();
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
        $criticalCssService = new CriticalCss();
        if (isset($_POST['critical_css'])) {
            $criticalCssService->update(wp_unslash($_POST['critical_css']));
            return __('Critical CSS saved successfully!', 'fp-performance-suite');
        }
        return __('Critical CSS could not be saved.', 'fp-performance-suite');
    }

    private function handleCriticalPathFontsForm(array &$fontSettings): string
    {
        $criticalPathOptimizer = new \FP\PerfSuite\Services\Assets\CriticalPathOptimizer();
        $criticalPathOptimizer->updateSettings([
            'enabled' => !empty($_POST['critical_path_enabled']),
            'preload_critical_fonts' => !empty($_POST['preload_critical_fonts']),
            'optimize_google_fonts' => !empty($_POST['optimize_google_fonts']),
            'preconnect_providers' => !empty($_POST['preconnect_providers']),
            'inject_font_display' => !empty($_POST['inject_font_display']),
            'add_resource_hints' => !empty($_POST['add_resource_hints']),
        ]);
        
        $fontOptimizer = new FontOptimizer();
        $fontOptimizer->updateSettings([
            'preload_fonts' => !empty($_POST['preload_fonts']),
        ]);
        
        return __('Font & Critical Path settings saved successfully!', 'fp-performance-suite');
    }

    private function handleScriptDetectorForm(): string
    {
        $thirdPartyScripts = new ThirdPartyScriptManager();
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
            $key = sanitize_key($_POST['custom_key'] ?? '');
            if ($scriptDetector->removeCustomScript($key)) {
                return __('Custom script removed.', 'fp-performance-suite');
            }
        }
        
        return '';
    }

    private function handleAdvancedJsOptimizationForm(): string
    {
        // Handle unused optimization settings
        if (isset($_POST['unused_optimization'])) {
            $unusedOptimizer = new UnusedJavaScriptOptimizer();
            $unusedOptimizer->update($_POST['unused_optimization']);
        }

        // Handle code splitting settings
        if (isset($_POST['code_splitting'])) {
            $codeSplittingManager = new CodeSplittingManager();
            $codeSplittingManager->update($_POST['code_splitting']);
        }

        // Handle tree shaking settings
        if (isset($_POST['tree_shaking'])) {
            $treeShaker = new JavaScriptTreeShaker();
            $treeShaker->update($_POST['tree_shaking']);
        }

        return __('Advanced JavaScript optimization settings saved successfully!', 'fp-performance-suite');
    }

    private function handleMainToggleForm(array &$settings): string
    {
        try {
            $optimizer = new Optimizer();
            
            // Get current settings
            $currentSettings = $optimizer->settings();
            
            // Update the main enabled flag
            $currentSettings['enabled'] = !empty($_POST['assets_enabled']);
            
            // Save the updated settings
            $optimizer->update($currentSettings);
            
            // Update the settings array for the view
            $settings = $optimizer->settings();
            
            // Return success message instead of redirect to avoid page issues
            return __('Asset optimization settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Exception $e) {
            // Log the error for debugging
            error_log('FP Performance Suite - Main Toggle Error: ' . $e->getMessage());
            
            // Return error message instead of redirect
            return sprintf(
                __('Error saving settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }
}
