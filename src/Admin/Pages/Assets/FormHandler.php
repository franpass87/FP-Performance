<?php

namespace FP\PerfSuite\Admin\Pages\Assets;

use FP\PerfSuite\Admin\Form\AbstractFormHandler;
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
use FP\PerfSuite\Utils\ErrorHandler;

use function __;
use function sanitize_key;
use function wp_unslash;

/**
 * Gestisce i form POST della pagina Assets
 * 
 * @package FP\PerfSuite\Admin\Pages\Assets
 * @author Francesco Passeri
 */
class FormHandler extends AbstractFormHandler
{

    /**
     * Gestisce le submission dei form
     * 
     * @param array &$settings Settings array (by reference)
     * @param array &$fontSettings Font settings array (by reference)
     * @param array &$thirdPartySettings Third party settings array (by reference)
     * @return string Messaggio di risultato
     */
    public function handle(array &$settings, array &$fontSettings, array &$thirdPartySettings): string
    {
        if (!$this->isPost()) {
            return '';
        }

        try {
            // Verifica capability utente
            if (!current_user_can(\FP\PerfSuite\Utils\Capabilities::required())) {
                return $this->errorMessage(__('Error: You do not have permission to perform this action.', 'fp-performance-suite'));
            }
            
            // Verifica nonce prima di tutto
            if (!isset($_POST['fp_ps_assets_nonce']) || !wp_verify_nonce(wp_unslash($_POST['fp_ps_assets_nonce']), 'fp-ps-assets')) {
                return $this->errorMessage(__('Error: Security check failed. Please refresh the page and try again.', 'fp-performance-suite'));
            }

            $formType = $this->sanitizeInput('form_type', 'text');
            
            if (empty($formType)) {
                // Se non c'è form_type, potrebbe essere un salvataggio diretto
                // Verifica se ci sono altri campi che indicano quale form salvare
                if (isset($_POST['assets_enabled'])) {
                    $formType = 'main_toggle';
                } elseif (isset($_POST['defer_js']) || isset($_POST['async_js'])) {
                    $formType = 'javascript';
                } elseif (isset($_POST['combine_css']) || isset($_POST['minify_css'])) {
                    $formType = 'css';
                } else {
                    return '';
                }
            }
            
            // Route to appropriate handler con gestione errori per ogni caso
            try {
                switch ($formType) {
                    case 'main_toggle':
                        return $this->handleMainToggle($settings);
                    case 'javascript':
                        return $this->handleJavaScriptForm($settings);
                    case 'css':
                        return $this->handleCssForm($settings);
                    case 'third_party':
                        return $this->handleThirdPartyForm($thirdPartySettings);
                    case 'http2_push':
                        return $this->handleHttp2PushForm();
                    case 'smart_delivery':
                        return $this->handleSmartDeliveryForm();
                    case 'unusedcss':
                        return $this->handleUnusedCssForm();
                    case 'criticalcss':
                        return $this->handleCriticalCssForm();
                    case 'script_detector':
                        return $this->handleScriptDetectorForm();
                    case 'critical_path_fonts':
                        return $this->handleCriticalPathFontsForm($fontSettings);
                    case 'advanced_js_optimization':
                        return $this->handleAdvancedJsOptimizationForm();
                    default:
                        return $this->errorMessage(sprintf(__('Unknown form type: %s', 'fp-performance-suite'), esc_html($formType)));
                }
            } catch (\Throwable $e) {
                // Log errore specifico per questo form type
                ErrorHandler::handle($e, "Assets form handling for type: {$formType}");
                return $this->errorMessage(
                    sprintf(
                        __('Error saving %s settings: %s', 'fp-performance-suite'),
                        esc_html($formType),
                        esc_html($e->getMessage())
                    )
                );
            }
        } catch (\Throwable $e) {
            // Errore generale nel form handler
            ErrorHandler::handle($e, 'Assets form handler initialization');
            return $this->errorMessage(
                sprintf(
                    __('Critical error: %s. Please check debug.log for details.', 'fp-performance-suite'),
                    esc_html($e->getMessage())
                )
            );
        }
    }

    private function handleMainToggle(array &$settings): string
    {
        try {
            if (!$this->container->has(Optimizer::class)) {
                return $this->errorMessage(__('Optimizer service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $optimizer = $this->container->get(Optimizer::class);
            $currentSettings = $optimizer->settings();
            
            // Corretto: gestisce sia stati checked che unchecked
            $currentSettings['enabled'] = isset($_POST['assets_enabled']) && sanitize_key(wp_unslash($_POST['assets_enabled'] ?? '')) === '1';
            
            $result = $optimizer->update($currentSettings);
            
            if (!$result) {
                return $this->errorMessage(__('Failed to save settings. Please try again.', 'fp-performance-suite'));
            }
            
            $settings = $optimizer->settings();
            
            return __('Asset optimization settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Assets main toggle save');
            return $this->errorMessage(
                sprintf(__('Error saving main toggle: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleJavaScriptForm(array &$settings): string
    {
        try {
            if (!$this->container->has(Optimizer::class)) {
                return $this->errorMessage(__('Optimizer service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $optimizer = $this->container->get(Optimizer::class);
            $excludeJs = $this->sanitizeInput('exclude_js', 'textarea') 
                ?? (!empty($settings['exclude_js']) ? $settings['exclude_js'] : '');
            
            // FIX: Gestisce correttamente le checkbox non selezionate
            // Se una checkbox non è presente nel POST, significa che è disattivata
            // Passiamo sempre il valore esplicito (true o false) per assicurarci che venga salvato correttamente
            $updateData = [
                'exclude_js' => $excludeJs,
            ];
            
            // Gestisce defer_js: se presente nel POST è true, altrimenti false esplicito
            // Quando una checkbox è selezionata, il valore nel POST è "1"
            // Quando non è selezionata, il campo non è presente nel POST
            $updateData['defer_js'] = isset($_POST['defer_js']) && sanitize_key(wp_unslash($_POST['defer_js'] ?? '')) === '1';
            
            // Gestisce async_js: se presente nel POST è true, altrimenti false esplicito
            // Quando una checkbox è selezionata, il valore nel POST è "1"
            // Quando non è selezionata, il campo non è presente nel POST
            $updateData['async_js'] = isset($_POST['async_js']) && sanitize_key(wp_unslash($_POST['async_js'] ?? '')) === '1';
            
            // Gestisce combine_js
            if (isset($_POST['combine_js'])) {
                $updateData['combine_js'] = $this->sanitizeInput('combine_js', 'bool') ?? true;
            } else {
                $updateData['combine_js'] = false;
            }
            
            // Gestisce remove_emojis
            if (isset($_POST['remove_emojis'])) {
                $updateData['remove_emojis'] = $this->sanitizeInput('remove_emojis', 'bool') ?? true;
            } else {
                $updateData['remove_emojis'] = false;
            }
            
            // Gestisce minify_inline_js
            if (isset($_POST['minify_inline_js'])) {
                $updateData['minify_inline_js'] = $this->sanitizeInput('minify_inline_js', 'bool') ?? true;
            } else {
                $updateData['minify_inline_js'] = false;
            }
            
            $result = $optimizer->update($updateData);
            
            // Nota: update() può restituire false anche quando i valori sono identici
            // Questo non è necessariamente un errore. Consideriamo sempre come successo
            // perché update() gestisce correttamente anche il caso in cui i valori sono identici
            // e restituisce false solo se c'è stato un errore fatale (che viene gestito dal try-catch)
            
            // FIX CRITICO: Pulisci solo la cache delle opzioni, non tutta la cache
            // wp_cache_flush() può causare problemi con altri plugin e temi
            wp_cache_delete('fp_ps_assets', 'options');
            wp_cache_delete('alloptions', 'options');
            
            // Aggiorna $settings senza chiamare settings() che potrebbe causare problemi
            // I valori sono già stati salvati correttamente da update()
            return __('JavaScript settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'JavaScript form save');
            return $this->errorMessage(
                sprintf(__('Error saving JavaScript settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleCssForm(array &$settings): string
    {
        try {
            if (!$this->container->has(Optimizer::class)) {
                return $this->errorMessage(__('Optimizer service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $optimizer = $this->container->get(Optimizer::class);
            $excludeCss = $this->sanitizeInput('exclude_css', 'textarea') 
                ?? (!empty($settings['exclude_css']) ? $settings['exclude_css'] : '');
            
            $result = $optimizer->update([
                'combine_css' => isset($_POST['combine_css']) ? ($this->sanitizeInput('combine_css', 'bool') ?? false) : false,
                'minify_inline_css' => isset($_POST['minify_inline_css']) ? ($this->sanitizeInput('minify_inline_css', 'bool') ?? false) : false,
                'remove_comments' => isset($_POST['remove_comments']) ? ($this->sanitizeInput('remove_comments', 'bool') ?? false) : false,
                'optimize_google_fonts' => isset($_POST['optimize_google_fonts_assets']) ? ($this->sanitizeInput('optimize_google_fonts_assets', 'bool') ?? false) : false,
                'exclude_css' => $excludeCss,
            ]);
            
            if (!$result) {
                return $this->errorMessage(__('Failed to save CSS settings. Please try again.', 'fp-performance-suite'));
            }
            
            $settings = $optimizer->settings();
            return __('CSS settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'CSS form save');
            return $this->errorMessage(
                sprintf(__('Error saving CSS settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleThirdPartyForm(array &$thirdPartySettings): string
    {
        try {
            if (!$this->container->has(ThirdPartyScriptManager::class)) {
                return $this->errorMessage(__('Third Party Script Manager service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
        
        // Prepare individual scripts settings
        $individualScripts = [];
        $scriptKeys = [
            'google_analytics', 'facebook_pixel', 'google_ads', 'hotjar', 'intercom', 
            'youtube', 'linkedin_insight', 'twitter_pixel', 'tiktok_pixel', 'pinterest_tag',
            'hubspot', 'zendesk', 'drift', 'crisp', 'tidio', 'segment', 'mixpanel',
            'mailchimp', 'stripe', 'paypal', 'recaptcha', 'google_maps', 'microsoft_clarity',
            'vimeo', 'tawk_to', 'optimizely', 'trustpilot', 'klaviyo', 'onetrust',
            'calendly', 'fullstory', 'snapchat_pixel', 'soundcloud', 'klarna', 'spotify',
            'livechat', 'activecampaign', 'userway', 'typeform', 'brevo', 'wonderpush'
        ];
        
        foreach ($scriptKeys as $key) {
            $postKey = 'third_party_' . str_replace('_', '', $key);
            $individualScripts[$key] = [
                'enabled' => isset($_POST[$postKey]) ? ($this->sanitizeInput($postKey, 'bool') ?? false) : false
            ];
        }
        
        $thirdPartyScripts->updateSettings([
            'enabled' => isset($_POST['third_party_enabled']) ? ($this->sanitizeInput('third_party_enabled', 'bool') ?? false) : false,
            'auto_detect' => isset($_POST['third_party_auto_detect']) ? ($this->sanitizeInput('third_party_auto_detect', 'bool') ?? false) : false,
            'exclude_critical' => isset($_POST['third_party_exclude_critical']) ? ($this->sanitizeInput('third_party_exclude_critical', 'bool') ?? false) : false,
            'delay_loading' => isset($_POST['third_party_delay_loading']) ? ($this->sanitizeInput('third_party_delay_loading', 'bool') ?? false) : false,
            'load_on' => $this->sanitizeInput('third_party_load_on', 'text') ?? 'interaction',
            'custom_scripts' => $this->sanitizeInput('third_party_custom_scripts', 'textarea') ?? '',
            'exclusions' => $this->sanitizeInput('third_party_exclusions', 'textarea') ?? '',
            'scripts' => $individualScripts,
        ]);
        
            $thirdPartySettings = $thirdPartyScripts->settings();
            return __('Third Party settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Third Party form save');
            return $this->errorMessage(
                sprintf(__('Error saving Third Party settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleHttp2PushForm(): string
    {
        try {
            if (!$this->container->has(Http2ServerPush::class)) {
                return $this->errorMessage(__('HTTP/2 Server Push service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $http2Push = $this->container->get(Http2ServerPush::class);
            $http2Push->updateSettings([
                'enabled' => isset($_POST['http2_push_enabled']) ? ($this->sanitizeInput('http2_push_enabled', 'bool') ?? false) : false,
                'critical_css' => isset($_POST['http2_push_critical_css']) ? ($this->sanitizeInput('http2_push_critical_css', 'bool') ?? false) : false,
                'critical_js' => isset($_POST['http2_push_critical_js']) ? ($this->sanitizeInput('http2_push_critical_js', 'bool') ?? false) : false,
                'critical_fonts' => isset($_POST['http2_push_critical_fonts']) ? ($this->sanitizeInput('http2_push_critical_fonts', 'bool') ?? false) : false,
            ]);
            
            return __('HTTP/2 Server Push settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'HTTP/2 Push form save');
            return $this->errorMessage(
                sprintf(__('Error saving HTTP/2 Push settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleSmartDeliveryForm(): string
    {
        try {
            if (!$this->container->has(SmartAssetDelivery::class)) {
                return $this->errorMessage(__('Smart Asset Delivery service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $smartDelivery = $this->container->get(SmartAssetDelivery::class);
            $smartDelivery->updateSettings([
                'enabled' => isset($_POST['smart_delivery_enabled']) ? ($this->sanitizeInput('smart_delivery_enabled', 'bool') ?? false) : false,
                'adapt_images' => isset($_POST['smart_adaptive_images']) ? ($this->sanitizeInput('smart_adaptive_images', 'bool') ?? false) : false,
                'adapt_videos' => isset($_POST['smart_adaptive_videos']) ? ($this->sanitizeInput('smart_adaptive_videos', 'bool') ?? false) : false,
                'slow_quality' => $this->sanitizeInput('smart_quality_slow', 'int') ?? 60,
                'fast_quality' => $this->sanitizeInput('smart_quality_fast', 'int') ?? 85,
            ]);
            
            return __('Smart Asset Delivery settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Smart Delivery form save');
            return $this->errorMessage(
                sprintf(__('Error saving Smart Delivery settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleUnusedCssForm(): string
    {
        try {
            if (!$this->container->has(UnusedCSSOptimizer::class)) {
                return $this->errorMessage(__('Unused CSS Optimizer service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $unusedCssOptimizer = $this->container->get(UnusedCSSOptimizer::class);
            
            $unusedCssOptimizer->updateSettings([
                'enabled' => isset($_POST['unusedcss_enabled']) ? ($this->sanitizeInput('unusedcss_enabled', 'bool') ?? false) : false,
                'remove_unused_css' => isset($_POST['unusedcss_remove_unused_css']) ? ($this->sanitizeInput('unusedcss_remove_unused_css', 'bool') ?? false) : false,
                'defer_non_critical' => isset($_POST['unusedcss_defer_non_critical']) ? ($this->sanitizeInput('unusedcss_defer_non_critical', 'bool') ?? false) : false,
                'inline_critical' => isset($_POST['unusedcss_inline_critical']) ? ($this->sanitizeInput('unusedcss_inline_critical', 'bool') ?? false) : false,
                'enable_css_purging' => isset($_POST['unusedcss_enable_css_purging']) ? ($this->sanitizeInput('unusedcss_enable_css_purging', 'bool') ?? false) : false,
                'critical_css' => $this->sanitizeInput('unusedcss_critical_css', 'textarea') ?? '',
            ]);
            
            return __('Unused CSS settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Unused CSS form save');
            return $this->errorMessage(
                sprintf(__('Error saving Unused CSS settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleCriticalCssForm(): string
    {
        try {
            if (!$this->container->has(CriticalCss::class)) {
                return $this->errorMessage(__('Critical CSS service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $criticalCssService = $this->container->get(CriticalCss::class);
            $criticalCss = $this->sanitizeInput('critical_css', 'textarea');
            if (!empty($criticalCss)) {
                $result = $criticalCssService->update($criticalCss);
                if (!$result['success']) {
                    return $this->errorMessage(__('Error saving Critical CSS: ', 'fp-performance-suite') . ($result['error'] ?? 'Unknown error'));
                }
            }
            
            return __('Critical CSS settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Critical CSS form save');
            return $this->errorMessage(
                sprintf(__('Error saving Critical CSS settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleScriptDetectorForm(): string
    {
        try {
            if (!$this->container->has(ThirdPartyScriptManager::class)) {
                return $this->errorMessage(__('Third Party Script Manager service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            if (!$this->container->has(ThirdPartyScriptDetector::class)) {
                return $this->errorMessage(__('Third Party Script Detector service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
            $scriptDetector = $this->container->get(ThirdPartyScriptDetector::class);
            
            if (isset($_POST['action_scan'])) {
                $scriptDetector->scanHomepage();
                return __('Scan completed! Check suggestions below.', 'fp-performance-suite');
            } elseif (isset($_POST['action_add_custom'])) {
                $patternsRaw = $this->sanitizeInput('script_patterns', 'textarea') ?? '';
                $scriptDetector->addCustomScript([
                    'name' => $this->sanitizeInput('script_name', 'text') ?? '',
                    'patterns' => !empty($patternsRaw) ? array_filter(array_map('trim', explode("\n", $patternsRaw))) : [],
                    'enabled' => isset($_POST['script_enabled']) ? ($this->sanitizeInput('script_enabled', 'bool') ?? false) : false,
                    'delay' => isset($_POST['script_delay']) ? ($this->sanitizeInput('script_delay', 'bool') ?? false) : false,
                ]);
                return __('Custom script added successfully!', 'fp-performance-suite');
            }
            
            return __('Script Detector settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Script Detector form save');
            return $this->errorMessage(
                sprintf(__('Error saving Script Detector settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleCriticalPathFontsForm(array &$fontSettings): string
    {
        try {
            if (!$this->container->has(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class)) {
                return $this->errorMessage(__('Critical Path Optimizer service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            if (!$this->container->has(FontOptimizer::class)) {
                return $this->errorMessage(__('Font Optimizer service not available. Please refresh the page.', 'fp-performance-suite'));
            }
            
            $criticalPathOptimizer = $this->container->get(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class);
            $criticalPathOptimizer->updateSettings([
                'enabled' => isset($_POST['critical_path_enabled']) ? ($this->sanitizeInput('critical_path_enabled', 'bool') ?? false) : false,
                'preload_critical_fonts' => isset($_POST['preload_critical_fonts']) ? ($this->sanitizeInput('preload_critical_fonts', 'bool') ?? false) : false,
                'optimize_google_fonts' => isset($_POST['optimize_google_fonts']) ? ($this->sanitizeInput('optimize_google_fonts', 'bool') ?? false) : false,
                'preconnect_providers' => isset($_POST['preconnect_providers']) ? ($this->sanitizeInput('preconnect_providers', 'bool') ?? false) : false,
                'inject_font_display' => isset($_POST['inject_font_display']) ? ($this->sanitizeInput('inject_font_display', 'bool') ?? false) : false,
                'add_resource_hints' => isset($_POST['add_resource_hints']) ? ($this->sanitizeInput('add_resource_hints', 'bool') ?? false) : false,
            ]);
            
            $fontOptimizer = $this->container->get(FontOptimizer::class);
            $fontOptimizer->updateSettings([
                'preload_fonts' => isset($_POST['preload_fonts']) ? ($this->sanitizeInput('preload_fonts', 'bool') ?? false) : false,
            ]);
            
            $fontSettings = $fontOptimizer->getSettings();
            return __('Font & Critical Path settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Critical Path Fonts form save');
            return $this->errorMessage(
                sprintf(__('Error saving Font & Critical Path settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }

    private function handleAdvancedJsOptimizationForm(): string
    {
        try {
            // Unused JavaScript Optimizer
            if (isset($_POST['unused_optimization'])) {
                if ($this->container->has(UnusedJavaScriptOptimizer::class)) {
                    $unusedJsOptimizer = $this->container->get(UnusedJavaScriptOptimizer::class);
                    $unusedJsOptimizer->updateSettings([
                        'enabled' => $this->sanitizeInput('unused_optimization', 'array')['enabled'] ?? false,
                    ]);
                }
            }
            
            // Code Splitting Manager
            if (isset($_POST['code_splitting'])) {
                if ($this->container->has(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class)) {
                    $codeSplittingManager = $this->container->get(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class);
                    $codeSplittingManager->updateSettings([
                        'enabled' => $this->sanitizeInput('code_splitting', 'array')['enabled'] ?? false,
                    ]);
                }
            }
            
            // Tree Shaking
            if (isset($_POST['tree_shaking'])) {
                if ($this->container->has(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class)) {
                    $treeShaker = $this->container->get(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class);
                    $treeShaker->updateSettings([
                        'enabled' => $this->sanitizeInput('tree_shaking', 'array')['enabled'] ?? false,
                    ]);
                }
            }
            
            return __('Advanced JS Optimization settings saved successfully!', 'fp-performance-suite');
        } catch (\Throwable $e) {
            ErrorHandler::handle($e, 'Advanced JS Optimization form save');
            return $this->errorMessage(
                sprintf(__('Error saving Advanced JS Optimization settings: %s', 'fp-performance-suite'), $e->getMessage())
            );
        }
    }
}















