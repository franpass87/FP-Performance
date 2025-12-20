<?php

/**
 * Asset Service Provider
 * 
 * Registers all asset optimization services
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class AssetServiceProvider implements ServiceProviderInterface
{
    /**
     * Register asset services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Core Asset Optimizers
        $container->singleton(
            \FP\PerfSuite\Services\Assets\HtmlMinifier::class,
            fn() => new \FP\PerfSuite\Services\Assets\HtmlMinifier()
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ScriptOptimizer::class,
            fn() => new \FP\PerfSuite\Services\Assets\ScriptOptimizer()
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\WordPressOptimizer::class,
            fn() => new \FP\PerfSuite\Services\Assets\WordPressOptimizer()
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class,
            fn() => new \FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager()
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class,
            fn() => new \FP\PerfSuite\Services\Assets\Combiners\DependencyResolver()
        );
        
        // Main Asset Optimizer
        $container->singleton(
            \FP\PerfSuite\Services\Assets\Optimizer::class,
            function(Container $c) {
                return new \FP\PerfSuite\Services\Assets\Optimizer(
                    $c->get(\FP\PerfSuite\Utils\Semaphore::class),
                    $c->get(\FP\PerfSuite\Services\Assets\HtmlMinifier::class),
                    $c->get(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class),
                    $c->get(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class),
                    $c->get(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class),
                    $c->get(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class)
                );
            }
        );
        
        // External Resource Cache Manager
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ExternalResourceCacheManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\ExternalResourceCacheManager($optionsRepo);
            }
        );
        
        // Lazy Loading
        $container->singleton(
            \FP\PerfSuite\Services\Assets\LazyLoadManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\LazyLoadManager(true, true, true, $optionsRepo);
            }
        );
        
        // Font Optimization
        $container->singleton(
            \FP\PerfSuite\Services\Assets\FontOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\FontOptimizer(true, true, $optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\AutoFontOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\AutoFontOptimizer($optionsRepo, $logger);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(LoggerInterface::class)
                    ? $c->get(LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\LighthouseFontOptimizer($optionsRepo, $logger);
            }
        );
        
        // Image Optimization
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ImageOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\ImageOptimizer(true, $optionsRepo);
            }
        );
        
        // Responsive Image Optimizer (with OptionsRepository injection)
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer($optionsRepo);
            }
        );
        
        // Critical CSS
        $container->singleton(
            \FP\PerfSuite\Services\Assets\CriticalCss::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(LoggerInterface::class)
                    ? $c->get(LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\CriticalCss($optionsRepo, $logger);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\CriticalCssAutomation::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(LoggerInterface::class)
                    ? $c->get(LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\CriticalCssAutomation($optionsRepo, $logger);
            }
        );
        
        // Advanced Asset Services
        $container->singleton(
            \FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\UnusedCSSOptimizer($optionsRepo);
            }
        );
        
        // Register interface binding for UnusedCSSOptimizer
        $container->singleton(
            \FP\PerfSuite\Services\Assets\UnusedCSSOptimizerInterface::class,
            fn(Container $c) => $c->get(\FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class)
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\CSSOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\CSSOptimizer($optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\RenderBlockingOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\RenderBlockingOptimizer($optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\CriticalPathOptimizer($optionsRepo);
            }
        );
        
        // Smart Asset Delivery
        $container->singleton(
            \FP\PerfSuite\Services\Assets\SmartAssetDelivery::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\SmartAssetDelivery($optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\DOMReflowOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\DOMReflowOptimizer($optionsRepo);
            }
        );
        
        // Critical Page Detector (shared service)
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager\CriticalPageDetector::class,
            fn() => new \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager\CriticalPageDetector()
        );
        
        // JavaScript Optimizers
        $container->singleton(
            \FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $criticalPageDetector = $c->get(
                    \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager\CriticalPageDetector::class
                );
                return new \FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer(
                    false,
                    $optionsRepo,
                    $criticalPageDetector
                );
            }
        );
        
        // Register interface binding for UnusedJavaScriptOptimizer
        $container->singleton(
            \FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizerInterface::class,
            fn(Container $c) => $c->get(\FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class)
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\CodeSplittingManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\CodeSplittingManager(100000, true, $optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\JavaScriptTreeShaker(false, $optionsRepo);
            }
        );
        
        // Third Party Script Manager (with OptionsRepository injection)
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager($optionsRepo);
            }
        );
        
        // Third Party Script Detector
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class,
            function(Container $c) {
                return new \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector(
                    $c->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class)
                );
            }
        );
        
        // Media Lazy Load Manager
        $container->singleton(
            \FP\PerfSuite\Services\Media\LazyLoadManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Media\LazyLoadManager($optionsRepo);
            }
        );
        
        // New Features v1.8.0
        $container->singleton(
            \FP\PerfSuite\Services\Assets\InstantPageLoader::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\InstantPageLoader($optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\EmbedFacades::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\EmbedFacades($optionsRepo, $logger);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor($optionsRepo, $logger);
            }
        );
        
        // Compression
        $container->singleton(
            \FP\PerfSuite\Services\Compression\CompressionManager::class,
            function(Container $c) {
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Compression\CompressionManager(true, false, true, true, true, $logger);
            }
        );
        
        // CDN Manager
        $container->singleton(
            \FP\PerfSuite\Services\CDN\CdnManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(LoggerInterface::class)
                    ? $c->get(LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\CDN\CdnManager('cloudflare', '', '', $optionsRepo, $logger);
            }
        );
        
        // HTTP/2 Server Push
        $container->singleton(
            \FP\PerfSuite\Services\Assets\Http2ServerPush::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\Http2ServerPush($optionsRepo);
            }
        );
        
        // Predictive Prefetching
        $container->singleton(
            \FP\PerfSuite\Services\Assets\PredictivePrefetching::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\PredictivePrefetching('hover', 100, 5, $optionsRepo);
            }
        );
        
        // Responsive Image Ajax Handler
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler::class,
            fn() => new \FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler()
        );
        
        // Batch DOM Updater
        $container->singleton(
            \FP\PerfSuite\Services\Assets\BatchDOMUpdater::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Assets\BatchDOMUpdater($optionsRepo);
            }
        );
        
        // jQuery Optimizer
        $container->singleton(
            \FP\PerfSuite\Services\Assets\jQueryOptimizer::class,
            fn() => new \FP\PerfSuite\Services\Assets\jQueryOptimizer()
        );
    }
    
    /**
     * Boot asset services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Asset services will be initialized by ServiceLoader based on enabled state
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 40; // As per plan: AssetServiceProvider priority 40
    }
    
    /**
     * Check if provider should load
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return true;
    }
}

