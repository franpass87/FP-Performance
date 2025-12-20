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
            $formType = $this->sanitizeInput('form_type', 'text');
            
            if (empty($formType)) {
                return '';
            }
            
            // Route to appropriate handler
            return match($formType) {
                'main_toggle' => $this->handleMainToggle($settings),
                'javascript' => $this->handleJavaScriptForm($settings),
                'css' => $this->handleCssForm($settings),
                'third_party' => $this->handleThirdPartyForm($thirdPartySettings),
                'http2_push' => $this->handleHttp2PushForm(),
                'smart_delivery' => $this->handleSmartDeliveryForm(),
                'unusedcss' => $this->handleUnusedCssForm(),
                'criticalcss' => $this->handleCriticalCssForm(),
                'script_detector' => $this->handleScriptDetectorForm(),
                'critical_path_fonts' => $this->handleCriticalPathFontsForm($fontSettings),
                'advanced_js_optimization' => $this->handleAdvancedJsOptimizationForm(),
                default => ''
            };
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Assets form handling');
        }
    }

    private function handleMainToggle(array &$settings): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $optimizer = $this->container->get(Optimizer::class);
        $currentSettings = $optimizer->settings();
        
        // Corretto: gestisce sia stati checked che unchecked
        $currentSettings['enabled'] = isset($_POST['assets_enabled']) && $_POST['assets_enabled'] === '1';
        
        // Debug: Log new settings
        // Debug log rimosso - usa ErrorHandler se necessario per logging strutturato
        
        $result = $optimizer->update($currentSettings);
        
        // Debug: Log result
        // Debug log rimosso - usa ErrorHandler se necessario per logging strutturato
        
        $settings = $optimizer->settings();
        
        // Debug: Log final settings
        // Debug log rimosso - usa ErrorHandler se necessario per logging strutturato
        
        return __('Asset optimization settings saved successfully!', 'fp-performance-suite');
    }

    private function handleJavaScriptForm(array &$settings): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $optimizer = $this->container->get(Optimizer::class);
        $excludeJs = $this->sanitizeInput('exclude_js', 'textarea') 
            ?? (!empty($settings['exclude_js']) ? $settings['exclude_js'] : '');
        
        $optimizer->update([
            'defer_js' => $this->sanitizeInput('defer_js', 'bool') ?? false,
            'async_js' => $this->sanitizeInput('async_js', 'bool') ?? false,
            'combine_js' => $this->sanitizeInput('combine_js', 'bool') ?? false,
            'remove_emojis' => $this->sanitizeInput('remove_emojis', 'bool') ?? false,
            'minify_inline_js' => $this->sanitizeInput('minify_inline_js', 'bool') ?? false,
            'exclude_js' => $excludeJs,
        ]);
        
        $settings = $optimizer->settings();
        return $this->successMessage(__('JavaScript settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleCssForm(array &$settings): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $optimizer = $this->container->get(Optimizer::class);
        $excludeCss = $this->sanitizeInput('exclude_css', 'textarea') 
            ?? (!empty($settings['exclude_css']) ? $settings['exclude_css'] : '');
        
        $optimizer->update([
            'combine_css' => $this->sanitizeInput('combine_css', 'bool') ?? false,
            'minify_inline_css' => $this->sanitizeInput('minify_inline_css', 'bool') ?? false,
            'remove_comments' => $this->sanitizeInput('remove_comments', 'bool') ?? false,
            'optimize_google_fonts' => $this->sanitizeInput('optimize_google_fonts_assets', 'bool') ?? false,
            'exclude_css' => $excludeCss,
        ]);
        
        $settings = $optimizer->settings();
        return $this->successMessage(__('CSS settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleThirdPartyForm(array &$thirdPartySettings): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
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
                'enabled' => $this->sanitizeInput($postKey, 'bool') ?? false
            ];
        }
        
        $thirdPartyScripts->updateSettings([
            'enabled' => $this->sanitizeInput('third_party_enabled', 'bool') ?? false,
            'auto_detect' => $this->sanitizeInput('third_party_auto_detect', 'bool') ?? false,
            'exclude_critical' => $this->sanitizeInput('third_party_exclude_critical', 'bool') ?? false,
            'delay_loading' => $this->sanitizeInput('third_party_delay_loading', 'bool') ?? false,
            'load_on' => $this->sanitizeInput('third_party_load_on', 'text') ?? 'interaction',
            'custom_scripts' => $this->sanitizeInput('third_party_custom_scripts', 'textarea') ?? '',
            'exclusions' => $this->sanitizeInput('third_party_exclusions', 'textarea') ?? '',
            'scripts' => $individualScripts,
        ]);
        
        $thirdPartySettings = $thirdPartyScripts->settings();
        return $this->successMessage(__('Third Party settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleHttp2PushForm(): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $http2Push = $this->container->get(Http2ServerPush::class);
        $http2Push->updateSettings([
            'enabled' => $this->sanitizeInput('http2_push_enabled', 'bool') ?? false,
            'critical_css' => $this->sanitizeInput('http2_push_critical_css', 'bool') ?? false,
            'critical_js' => $this->sanitizeInput('http2_push_critical_js', 'bool') ?? false,
            'critical_fonts' => $this->sanitizeInput('http2_push_critical_fonts', 'bool') ?? false,
        ]);
        
        return $this->successMessage(__('HTTP/2 Server Push settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleSmartDeliveryForm(): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $smartDelivery = $this->container->get(SmartAssetDelivery::class);
        $smartDelivery->updateSettings([
            'enabled' => $this->sanitizeInput('smart_delivery_enabled', 'bool') ?? false,
            'adapt_images' => $this->sanitizeInput('smart_adaptive_images', 'bool') ?? false,
            'adapt_videos' => $this->sanitizeInput('smart_adaptive_videos', 'bool') ?? false,
            'slow_quality' => $this->sanitizeInput('smart_quality_slow', 'int') ?? 60,
            'fast_quality' => $this->sanitizeInput('smart_quality_fast', 'int') ?? 85,
        ]);
        
        return $this->successMessage(__('Smart Asset Delivery settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleUnusedCssForm(): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $unusedCssOptimizer = $this->container->get(UnusedCSSOptimizer::class);
        
        $unusedCssOptimizer->updateSettings([
            'enabled' => $this->sanitizeInput('unusedcss_enabled', 'bool') ?? false,
            'remove_unused_css' => $this->sanitizeInput('unusedcss_remove_unused_css', 'bool') ?? false,
            'defer_non_critical' => $this->sanitizeInput('unusedcss_defer_non_critical', 'bool') ?? false,
            'inline_critical' => $this->sanitizeInput('unusedcss_inline_critical', 'bool') ?? false,
            'enable_css_purging' => $this->sanitizeInput('unusedcss_enable_css_purging', 'bool') ?? false,
            'critical_css' => $this->sanitizeInput('unusedcss_critical_css', 'textarea') ?? '',
        ]);
        
        return $this->successMessage(__('Unused CSS settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleCriticalCssForm(): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $criticalCssService = $this->container->get(CriticalCss::class);
        $criticalCss = $this->sanitizeInput('critical_css', 'textarea');
        if (!empty($criticalCss)) {
            $result = $criticalCssService->update($criticalCss);
            if (!$result['success']) {
                return $this->errorMessage(__('Error saving Critical CSS: ', 'fp-performance-suite') . ($result['error'] ?? 'Unknown error'));
            }
        }
        
        return $this->successMessage(__('Critical CSS settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleScriptDetectorForm(): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
        $scriptDetector = $this->container->get(ThirdPartyScriptDetector::class);
        
        if (isset($_POST['action_scan'])) {
            $scriptDetector->scanHomepage();
            return $this->successMessage(__('Scan completed! Check suggestions below.', 'fp-performance-suite'));
        } elseif (isset($_POST['action_add_custom'])) {
            $patternsRaw = $this->sanitizeInput('script_patterns', 'textarea') ?? '';
            $scriptDetector->addCustomScript([
                'name' => $this->sanitizeInput('script_name', 'text') ?? '',
                'patterns' => !empty($patternsRaw) ? array_filter(array_map('trim', explode("\n", $patternsRaw))) : [],
                'enabled' => $this->sanitizeInput('script_enabled', 'bool') ?? false,
                'delay' => $this->sanitizeInput('script_delay', 'bool') ?? false,
            ]);
            return $this->successMessage(__('Custom script added successfully!', 'fp-performance-suite'));
        }
        
        return $this->successMessage(__('Script Detector settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleCriticalPathFontsForm(array &$fontSettings): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        $criticalPathOptimizer = $this->container->get(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class);
        $criticalPathOptimizer->updateSettings([
            'enabled' => $this->sanitizeInput('critical_path_enabled', 'bool') ?? false,
            'preload_critical_fonts' => $this->sanitizeInput('preload_critical_fonts', 'bool') ?? false,
            'optimize_google_fonts' => $this->sanitizeInput('optimize_google_fonts', 'bool') ?? false,
            'preconnect_providers' => $this->sanitizeInput('preconnect_providers', 'bool') ?? false,
            'inject_font_display' => $this->sanitizeInput('inject_font_display', 'bool') ?? false,
            'add_resource_hints' => $this->sanitizeInput('add_resource_hints', 'bool') ?? false,
        ]);
        
        $fontOptimizer = $this->container->get(FontOptimizer::class);
        $fontOptimizer->updateSettings([
            'preload_fonts' => $this->sanitizeInput('preload_fonts', 'bool') ?? false,
        ]);
        
        $fontSettings = $fontOptimizer->getSettings();
        return $this->successMessage(__('Font & Critical Path settings saved successfully!', 'fp-performance-suite'));
    }

    private function handleAdvancedJsOptimizationForm(): string
    {
        if (!$this->verifyNonce('fp_ps_assets_nonce', 'fp-ps-assets')) {
            return $this->errorMessage(__('Error: Nonce verification failed. Please try again.', 'fp-performance-suite'));
        }

        // Unused JavaScript Optimizer
        if (isset($_POST['unused_optimization'])) {
            $unusedJsOptimizer = $this->container->get(UnusedJavaScriptOptimizer::class);
            $unusedJsOptimizer->updateSettings([
                'enabled' => $this->sanitizeInput('unused_optimization', 'array')['enabled'] ?? false,
            ]);
        }
        
        // Code Splitting Manager
        if (isset($_POST['code_splitting'])) {
            $codeSplittingManager = $this->container->get(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class);
            $codeSplittingManager->updateSettings([
                'enabled' => $this->sanitizeInput('code_splitting', 'array')['enabled'] ?? false,
            ]);
        }
        
        // Tree Shaking
        if (isset($_POST['tree_shaking'])) {
            $treeShaker = $this->container->get(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class);
            $treeShaker->updateSettings([
                'enabled' => $this->sanitizeInput('tree_shaking', 'array')['enabled'] ?? false,
            ]);
        }
        
        return $this->successMessage(__('Advanced JS Optimization settings saved successfully!', 'fp-performance-suite'));
    }
}















