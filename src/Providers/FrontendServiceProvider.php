<?php

/**
 * Frontend Service Provider
 * 
 * Registers frontend-only services (shortcodes, optimizations)
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\OptionHelper;
use FP\PerfSuite\Plugin;

class FrontendServiceProvider implements ServiceProviderInterface
{
    /**
     * Register frontend services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Shortcodes
        $container->singleton(
            \FP\PerfSuite\Shortcodes::class,
            fn() => new \FP\PerfSuite\Shortcodes()
        );
    }
    
    /**
     * Boot frontend services
     * 
     * Includes conditional loading logic from ServiceLoader for frontend services.
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        if (!$this->shouldLoad()) {
            return;
        }
        
        // Boot shortcodes
        $container->get(\FP\PerfSuite\Shortcodes::class)->boot();
        
        // Load frontend services conditionally (refactored from ServiceLoader)
        add_action('init', function() use ($container) {
            $this->loadFrontendServices($container);
        }, 10);
    }
    
    /**
     * Load frontend services conditionally based on options
     * 
     * Refactored from ServiceLoader::loadAssetServices() and related methods.
     * 
     * @param Container $container
     */
    private function loadFrontendServices(Container $container): void
    {
        // Core frontend services
        $this->loadCoreFrontendServices($container);
        
        // Asset optimization services
        $this->loadAssetServices($container);
        
        // Cache services (frontend)
        $this->loadCacheServices($container);
        
        // Mobile services
        $this->loadMobileServices($container);
        
        // Always active frontend services
        $this->loadAlwaysActiveServices($container);
    }
    
    /**
     * Load core frontend services
     * 
     * @param Container $container
     */
    private function loadCoreFrontendServices(Container $container): void
    {
        // Page Cache
        if (OptionHelper::isEnabled('fp_ps_page_cache_settings')) {
            $this->loadService($container, \FP\PerfSuite\Services\Cache\PageCache::class);
        }

        // Browser Cache / Headers
        if (OptionHelper::isEnabled('fp_ps_browser_cache')) {
            $this->loadService($container, \FP\PerfSuite\Services\Cache\Headers::class);
        }

        // Assets Optimizer
        if (OptionHelper::isEnabled('fp_ps_assets') || get_option('fp_ps_asset_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\Optimizer::class);
        }
    }
    
    /**
     * Load asset optimization services
     * 
     * @param Container $container
     */
    private function loadAssetServices(Container $container): void
    {
        // Batch DOM Updates
        if (get_option('fp_ps_batch_dom_updates_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\BatchDOMUpdater::class);
        }

        // CSS Optimization
        if (get_option('fp_ps_css_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\CSSOptimizer::class);
        }

        // jQuery Optimization
        if (get_option('fp_ps_jquery_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\jQueryOptimizer::class);
        }

        // Predictive Prefetching
        if (OptionHelper::isEnabled('fp_ps_predictive_prefetch')) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\PredictivePrefetching::class);
        }

        // Third-Party Script Management
        if (OptionHelper::isEnabled('fp_ps_third_party_scripts')) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class);
        }

        // Third-Party Script Detector
        if (get_option('fp_ps_third_party_detector_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class);
        }

        // Instant Page Loader
        if (OptionHelper::isEnabled('fp_ps_instant_page')) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\InstantPageLoader::class);
        }

        // Embed Facades
        if (OptionHelper::isEnabled('fp_ps_embed_facades')) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\EmbedFacades::class);
        }

        // Delayed JavaScript
        if (OptionHelper::isEnabled('fp_ps_delay_js')) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor::class);
        }

        // Lazy Loading
        $responsiveSettings = OptionHelper::get('fp_ps_responsive_images', []);
        if (!empty($responsiveSettings['enable_lazy_loading'])) {
            Plugin::registerServiceOnce(\FP\PerfSuite\Services\Assets\LazyLoadManager::class, function() use ($container) {
                $container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)->register();
            });
        }

        // Font Optimization
        $fontOptimizationEnabled = get_option('fp_ps_font_optimization_enabled', false);
        $fontSettings = OptionHelper::get('fp_ps_font_optimization', []);
        $criticalPathSettings = OptionHelper::get('fp_ps_critical_path_optimization', []);
        
        if ($fontOptimizationEnabled || !empty($fontSettings['enabled']) || !empty($criticalPathSettings['enabled'])) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\FontOptimizer::class);
        }

        // Image Optimization
        if (get_option('fp_ps_image_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\ImageOptimizer::class);
        }

        // Auto Font Optimization
        if (get_option('fp_ps_auto_font_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\AutoFontOptimizer::class);
        }

        // Lighthouse Font Optimization
        if (get_option('fp_ps_lighthouse_font_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class);
        }

        // Advanced JavaScript Optimizers
        $unusedJSSettings = OptionHelper::get('fp_ps_js_unused_optimizer', []);
        if (!empty($unusedJSSettings['enabled'])) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class);
        }

        $codeSplittingSettings = OptionHelper::get('fp_ps_js_code_splitter', []);
        if (!empty($codeSplittingSettings['enabled'])) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\CodeSplittingManager::class);
        }

        $treeShakerSettings = OptionHelper::get('fp_ps_js_tree_shaker', []);
        if (!empty($treeShakerSettings['enabled'])) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class);
        }

        // Critical Path Optimizer
        $criticalPathSettings = OptionHelper::get('fp_ps_critical_path_optimization', []);
        $assetsSettings = OptionHelper::get('fp_ps_assets', []);
        if (!empty($criticalPathSettings['enabled']) || !empty($assetsSettings['optimize_google_fonts'])) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class);
        }
        
        // HTTP/2 Server Push
        if (get_option('fp_ps_http2_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\Http2ServerPush::class);
        }

        // Critical CSS
        if (get_option('fp_ps_critical_css_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\CriticalCss::class);
            $this->loadService($container, \FP\PerfSuite\Services\Assets\CriticalCssAutomation::class);
        }

        // Smart Delivery
        if (get_option('fp_ps_smart_delivery_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\SmartAssetDelivery::class);
        }

        // Advanced Assets
        if (get_option('fp_ps_html_minification_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\HtmlMinifier::class);
        }

        if (get_option('fp_ps_script_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\ScriptOptimizer::class);
        }

        if (get_option('fp_ps_wordpress_optimization_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\WordPressOptimizer::class);
        }

        if (get_option('fp_ps_resource_hints_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class);
        }

        if (get_option('fp_ps_dependency_resolution_enabled', false)) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class);
        }
    }
    
    /**
     * Load cache services (frontend)
     * 
     * @param Container $container
     */
    private function loadCacheServices(Container $container): void
    {
        // External Resource Cache
        if (OptionHelper::isEnabled('fp_ps_external_cache')) {
            $this->loadService($container, \FP\PerfSuite\Services\Assets\ExternalResourceCacheManager::class);
        }
    }
    
    /**
     * Load mobile services
     * 
     * @param Container $container
     */
    private function loadMobileServices(Container $container): void
    {
        if (OptionHelper::isEnabled('fp_ps_mobile_optimizer')) {
            $this->loadService($container, \FP\PerfSuite\Services\Mobile\MobileOptimizer::class);
        }

        if (OptionHelper::isEnabled('fp_ps_touch_optimizer')) {
            $this->loadService($container, \FP\PerfSuite\Services\Mobile\TouchOptimizer::class);
        }

        if (OptionHelper::isEnabled('fp_ps_mobile_cache')) {
            $this->loadService($container, \FP\PerfSuite\Services\Mobile\MobileCacheManager::class);
        }

        if (OptionHelper::isEnabled('fp_ps_responsive_images')) {
            $this->loadService($container, \FP\PerfSuite\Services\Mobile\ResponsiveImageManager::class);
        }
    }
    
    /**
     * Load always active frontend services
     * 
     * @param Container $container
     */
    private function loadAlwaysActiveServices(Container $container): void
    {
        // Advanced Assets Optimization Services - Sempre attivi
        $this->loadService($container, \FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer::class);
        $this->loadService($container, \FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler::class);
        $this->loadService($container, \FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class);
        $this->loadService($container, \FP\PerfSuite\Services\Assets\RenderBlockingOptimizer::class);
        $this->loadService($container, \FP\PerfSuite\Services\Assets\DOMReflowOptimizer::class);

        // Theme Services - Sempre attivi
        $this->loadService($container, \FP\PerfSuite\Services\Assets\ThemeAssetConfiguration::class);
    }
    
    /**
     * Load a service if not already loaded
     * 
     * @param Container $container
     * @param string $serviceClass Service class name
     */
    private function loadService(Container $container, string $serviceClass): void
    {
        Plugin::registerServiceOnce($serviceClass, function() use ($container, $serviceClass) {
            if ($container->has($serviceClass)) {
                $service = $container->get($serviceClass);
                if (method_exists($service, 'register')) {
                    $service->register();
                }
            }
        });
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 70; // As per plan: FrontendServiceProvider priority 70
    }
    
    /**
     * Check if provider should load
     * 
     * Frontend provider loads only on frontend (not admin, not JSON/REST)
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        // Don't load in admin
        if (is_admin()) {
            return false;
        }
        
        // Don't load for JSON/REST requests
        if (function_exists('wp_is_json_request') && wp_is_json_request()) {
            return false;
        }
        
        // Don't load for AJAX requests (unless it's a frontend AJAX)
        if (defined('DOING_AJAX') && DOING_AJAX) {
            // Allow frontend AJAX but not admin AJAX
            if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/wp-admin/') !== false) {
                return false;
            }
        }
        
        return true;
    }
}









