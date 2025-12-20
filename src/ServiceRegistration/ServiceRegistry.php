<?php

namespace FP\PerfSuite\ServiceRegistration;

use FP\PerfSuite\ServiceContainer;

/**
 * Service Registry - Gestisce la registrazione di tutti i servizi nel container
 * 
 * @package FP\PerfSuite\ServiceRegistration
 * @author Francesco Passeri
 */
class ServiceRegistry
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Registra tutti i servizi nel container
     */
    public function registerAll(): void
    {
        if (!defined('FP_PERF_SUITE_FILE')) {
            define('FP_PERF_SUITE_FILE', dirname(dirname(__DIR__)) . '/fp-performance-suite.php');
        }

        $this->container->set(ServiceContainer::class, fn() => $this->container);
        
        $this->registerUtils();
        $this->registerAssetServices();
        $this->registerCacheServices();
        $this->registerDatabaseServices();
        $this->registerCompatibilityServices();
        $this->registerIntelligenceServices();
        $this->registerMonitoringServices();
        $this->registerMLServices();
        $this->registerMobileServices();
        $this->registerAdminServices();
        $this->registerAjaxHandlers();
        $this->registerEdgeCacheProviders();
    }

    /**
     * Registra servizi utility
     */
    private function registerUtils(): void
    {
        $this->container->set(\FP\PerfSuite\Utils\Fs::class, static fn() => new \FP\PerfSuite\Utils\Fs());
        $this->container->set(\FP\PerfSuite\Utils\Htaccess::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Utils\Htaccess($c->get(\FP\PerfSuite\Utils\Fs::class));
        });
        $this->container->set(\FP\PerfSuite\Utils\Env::class, static fn() => new \FP\PerfSuite\Utils\Env());
        $this->container->set(\FP\PerfSuite\Utils\Semaphore::class, static fn() => new \FP\PerfSuite\Utils\Semaphore());
        $this->container->set(\FP\PerfSuite\Utils\RateLimiter::class, static fn() => new \FP\PerfSuite\Utils\RateLimiter());
    }

    /**
     * Registra servizi di ottimizzazione asset
     */
    private function registerAssetServices(): void
    {
        // Core asset optimizers
        $this->container->set(\FP\PerfSuite\Services\Assets\HtmlMinifier::class, static fn() => new \FP\PerfSuite\Services\Assets\HtmlMinifier());
        $this->container->set(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\ScriptOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\WordPressOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class, static fn() => new \FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager());
        $this->container->set(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class, static fn() => new \FP\PerfSuite\Services\Assets\Combiners\DependencyResolver());
        
        // External Resource Cache
        $this->container->set(\FP\PerfSuite\Services\Assets\ExternalResourceCacheManager::class, static fn() => new \FP\PerfSuite\Services\Assets\ExternalResourceCacheManager());
        
        // PageSpeed optimization services
        $this->container->set(\FP\PerfSuite\Services\Assets\LazyLoadManager::class, static fn() => new \FP\PerfSuite\Services\Assets\LazyLoadManager());
        $this->container->set(\FP\PerfSuite\Services\Assets\FontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\FontOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\ImageOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\ImageOptimizer());
        
        // Auto Font Optimization
        $this->container->set(\FP\PerfSuite\Services\Assets\AutoFontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\AutoFontOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\LighthouseFontOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\LighthouseFontOptimizer());
        
        // Critical CSS
        $this->container->set(\FP\PerfSuite\Services\Assets\CriticalCss::class, static fn() => new \FP\PerfSuite\Services\Assets\CriticalCss());
        $this->container->set(\FP\PerfSuite\Services\Assets\CriticalCssAutomation::class, static fn() => new \FP\PerfSuite\Services\Assets\CriticalCssAutomation());
        
        // Advanced Asset Services
        $this->container->set(\FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler::class, static fn() => new \FP\PerfSuite\Services\Assets\ResponsiveImageAjaxHandler());
        $this->container->set(\FP\PerfSuite\Services\Assets\UnusedCSSOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\UnusedCSSOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\RenderBlockingOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\RenderBlockingOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\CriticalPathOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\CriticalPathOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\DOMReflowOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\DOMReflowOptimizer());
        
        // Advanced JavaScript Optimizers
        $this->container->set(\FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\CodeSplittingManager::class, static fn() => new \FP\PerfSuite\Services\Assets\CodeSplittingManager());
        $this->container->set(\FP\PerfSuite\Services\Assets\JavaScriptTreeShaker::class, static fn() => new \FP\PerfSuite\Services\Assets\JavaScriptTreeShaker());
        
        // Batch DOM Updates
        $this->container->set(\FP\PerfSuite\Services\Assets\BatchDOMUpdater::class, static fn() => new \FP\PerfSuite\Services\Assets\BatchDOMUpdater());
        $this->container->set(\FP\PerfSuite\Services\Assets\CSSOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\CSSOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Assets\jQueryOptimizer::class, static fn() => new \FP\PerfSuite\Services\Assets\jQueryOptimizer());
        
        // Third-Party Script Management
        $this->container->set(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class, static fn() => new \FP\PerfSuite\Services\Assets\ThirdPartyScriptManager());
        $this->container->set(\FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector(
                $c->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class)
            );
        });
        
        // Smart Asset Delivery
        $this->container->set(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class, static fn() => new \FP\PerfSuite\Services\Assets\SmartAssetDelivery());
        
        // HTTP/2 Server Push
        $this->container->set(\FP\PerfSuite\Services\Assets\Http2ServerPush::class, static fn() => new \FP\PerfSuite\Services\Assets\Http2ServerPush());
        
        // Predictive Prefetching
        $this->container->set(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class, static fn() => new \FP\PerfSuite\Services\Assets\PredictivePrefetching());
        
        // NEW FEATURES v1.7.0
        $this->container->set(\FP\PerfSuite\Services\Assets\InstantPageLoader::class, static fn() => new \FP\PerfSuite\Services\Assets\InstantPageLoader());
        $this->container->set(\FP\PerfSuite\Services\Assets\EmbedFacades::class, static fn() => new \FP\PerfSuite\Services\Assets\EmbedFacades());
        $this->container->set(\FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor::class, static fn() => new \FP\PerfSuite\Services\Assets\DelayedJavaScriptExecutor());
        
        // Theme Asset Configuration
        $this->container->set(\FP\PerfSuite\Services\Assets\ThemeAssetConfiguration::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Assets\ThemeAssetConfiguration($c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class));
        });
        
        // Main Optimizer (with dependencies)
        $this->container->set(\FP\PerfSuite\Services\Assets\Optimizer::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Assets\Optimizer(
                $c->get(\FP\PerfSuite\Utils\Semaphore::class),
                $c->get(\FP\PerfSuite\Services\Assets\HtmlMinifier::class),
                $c->get(\FP\PerfSuite\Services\Assets\ScriptOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\WordPressOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class),
                $c->get(\FP\PerfSuite\Services\Assets\Combiners\DependencyResolver::class)
            );
        });
    }

    /**
     * Registra servizi di cache
     */
    private function registerCacheServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\Cache\PageCache::class, static fn() => new \FP\PerfSuite\Services\Cache\PageCache());
        $this->container->set(\FP\PerfSuite\Services\Cache\Headers::class, static fn() => new \FP\PerfSuite\Services\Cache\Headers());
        $this->container->set(\FP\PerfSuite\Services\Cache\BrowserCache::class, static fn() => new \FP\PerfSuite\Services\Cache\BrowserCache());
        $this->container->set(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class, static fn() => new \FP\PerfSuite\Services\Cache\ObjectCacheManager());
        $this->container->set(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class, static fn() => new \FP\PerfSuite\Services\Cache\EdgeCacheManager());
    }

    /**
     * Registra servizi database
     */
    private function registerDatabaseServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\DB\Cleaner::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\DB\Cleaner();
        });
        $this->container->set(\FP\PerfSuite\Services\DB\DatabaseOptimizer::class, static fn() => new \FP\PerfSuite\Services\DB\DatabaseOptimizer());
        $this->container->set(\FP\PerfSuite\Services\DB\DatabaseQueryMonitor::class, static fn() => new \FP\PerfSuite\Services\DB\DatabaseQueryMonitor());
        $this->container->set(\FP\PerfSuite\Services\DB\PluginSpecificOptimizer::class, static fn() => new \FP\PerfSuite\Services\DB\PluginSpecificOptimizer());
        $this->container->set(\FP\PerfSuite\Services\DB\DatabaseReportService::class, static fn() => new \FP\PerfSuite\Services\DB\DatabaseReportService());
        $this->container->set(\FP\PerfSuite\Services\DB\QueryCacheManager::class, static fn() => new \FP\PerfSuite\Services\DB\QueryCacheManager());
    }

    /**
     * Registra servizi di compatibilità
     */
    private function registerCompatibilityServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class, static fn() => new \FP\PerfSuite\Services\Compatibility\ThemeDetector());
        $this->container->set(\FP\PerfSuite\Services\Compatibility\CompatibilityFilters::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Compatibility\CompatibilityFilters($c, $c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class));
        });
        $this->container->set(\FP\PerfSuite\Services\Compatibility\ThemeCompatibility::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Compatibility\ThemeCompatibility($c, $c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class));
        });
        $this->container->set(\FP\PerfSuite\Services\Compatibility\SalientWPBakeryOptimizer::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Compatibility\SalientWPBakeryOptimizer($c, $c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class));
        });
        $this->container->set(\FP\PerfSuite\Services\Compatibility\FPPluginsIntegration::class, static fn() => new \FP\PerfSuite\Services\Compatibility\FPPluginsIntegration());
        $this->container->set(\FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer::class, static fn() => new \FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer());
    }

    /**
     * Registra servizi intelligence
     */
    private function registerIntelligenceServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\Intelligence\SmartExclusionDetector::class, static fn() => new \FP\PerfSuite\Services\Intelligence\SmartExclusionDetector());
        $this->container->set(\FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator(
                $c->get(\FP\PerfSuite\Services\Intelligence\SmartExclusionDetector::class)
            );
        });
        $this->container->set(\FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector::class, static fn() => new \FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector());
        $this->container->set(\FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator::class, static fn() => new \FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator());
        $this->container->set(\FP\PerfSuite\Services\Intelligence\IntelligenceReporter::class, static fn() => new \FP\PerfSuite\Services\Intelligence\IntelligenceReporter());
        $this->container->set(\FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator::class, static fn() => new \FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator());
        $this->container->set(\FP\PerfSuite\Services\Intelligence\CDNExclusionSync::class, static fn() => new \FP\PerfSuite\Services\Intelligence\CDNExclusionSync());
    }

    /**
     * Registra servizi di monitoring
     */
    private function registerMonitoringServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class, static fn() => \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance());
        $this->container->set(\FP\PerfSuite\Services\Monitoring\SystemMonitor::class, static fn() => \FP\PerfSuite\Services\Monitoring\SystemMonitor::instance());
        $this->container->set(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class, static fn() => new \FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor());
        $this->container->set(\FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer(
                $c->get(\FP\PerfSuite\Services\Cache\PageCache::class),
                $c->get(\FP\PerfSuite\Services\Cache\Headers::class),
                $c->get(\FP\PerfSuite\Services\Assets\Optimizer::class),
                $c->get(\FP\PerfSuite\Services\DB\Cleaner::class),
                $c->get(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class)
            );
        });
        $this->container->set(\FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Monitoring\RecommendationApplicator(
                $c->get(\FP\PerfSuite\Services\Cache\PageCache::class),
                $c->get(\FP\PerfSuite\Services\Cache\Headers::class),
                $c->get(\FP\PerfSuite\Services\Assets\Optimizer::class),
                $c->get(\FP\PerfSuite\Services\DB\Cleaner::class)
            );
        });
    }

    /**
     * Registra servizi ML
     */
    private function registerMLServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\ML\PatternLearner::class, static fn() => new \FP\PerfSuite\Services\ML\PatternLearner());
        $this->container->set(\FP\PerfSuite\Services\ML\AnomalyDetector::class, static fn() => new \FP\PerfSuite\Services\ML\AnomalyDetector());
        $this->container->set(\FP\PerfSuite\Services\ML\MLPredictor::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\ML\MLPredictor(
                $c,
                $c->get(\FP\PerfSuite\Services\ML\PatternLearner::class),
                $c->get(\FP\PerfSuite\Services\ML\AnomalyDetector::class)
            );
        });
        $this->container->set(\FP\PerfSuite\Services\ML\AutoTuner::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\ML\AutoTuner(
                $c,
                $c->get(\FP\PerfSuite\Services\ML\MLPredictor::class),
                $c->get(\FP\PerfSuite\Services\ML\PatternLearner::class)
            );
        });
    }

    /**
     * Registra servizi mobile
     */
    private function registerMobileServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\Mobile\TouchOptimizer::class, static fn() => new \FP\PerfSuite\Services\Mobile\TouchOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Mobile\MobileCacheManager::class, static fn() => new \FP\PerfSuite\Services\Mobile\MobileCacheManager());
        $this->container->set(\FP\PerfSuite\Services\Mobile\ResponsiveImageManager::class, static fn() => new \FP\PerfSuite\Services\Mobile\ResponsiveImageManager());
        $this->container->set(\FP\PerfSuite\Services\Mobile\MobileOptimizer::class, static fn() => new \FP\PerfSuite\Services\Mobile\MobileOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Media\LazyLoadManager::class, static fn() => new \FP\PerfSuite\Services\Media\LazyLoadManager());
    }

    /**
     * Registra servizi admin
     */
    private function registerAdminServices(): void
    {
        $this->container->set(\FP\PerfSuite\Services\Admin\BackendOptimizer::class, static fn() => new \FP\PerfSuite\Services\Admin\BackendOptimizer());
        $this->container->set(\FP\PerfSuite\Services\Compression\CompressionManager::class, static fn() => new \FP\PerfSuite\Services\Compression\CompressionManager());
        $this->container->set(\FP\PerfSuite\Services\CDN\CdnManager::class, static fn() => new \FP\PerfSuite\Services\CDN\CdnManager());
        $this->container->set(\FP\PerfSuite\Services\Security\HtaccessSecurity::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Security\HtaccessSecurity(
                $c->get(\FP\PerfSuite\Utils\Htaccess::class),
                $c->get(\FP\PerfSuite\Utils\Env::class)
            );
        });
        $this->container->set(\FP\PerfSuite\Services\Logs\DebugToggler::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Logs\DebugToggler($c->get(\FP\PerfSuite\Utils\Fs::class), $c->get(\FP\PerfSuite\Utils\Env::class));
        });
        $this->container->set(\FP\PerfSuite\Services\Logs\RealtimeLog::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Logs\RealtimeLog($c->get(\FP\PerfSuite\Services\Logs\DebugToggler::class));
        });
        $this->container->set(\FP\PerfSuite\Services\Presets\Manager::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Presets\Manager(
                $c->get(\FP\PerfSuite\Services\Cache\PageCache::class),
                $c->get(\FP\PerfSuite\Services\Cache\Headers::class),
                $c->get(\FP\PerfSuite\Services\Assets\Optimizer::class),
                $c->get(\FP\PerfSuite\Services\DB\Cleaner::class),
                $c->get(\FP\PerfSuite\Services\Logs\DebugToggler::class),
                $c->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)
            );
        });
        $this->container->set(\FP\PerfSuite\Services\Score\Scorer::class, static function (ServiceContainer $c) {
            return new \FP\PerfSuite\Services\Score\Scorer(
                $c->get(\FP\PerfSuite\Services\Cache\PageCache::class),
                $c->get(\FP\PerfSuite\Services\Cache\Headers::class),
                $c->get(\FP\PerfSuite\Services\Assets\Optimizer::class),
                $c->get(\FP\PerfSuite\Services\DB\Cleaner::class),
                $c->get(\FP\PerfSuite\Services\Logs\DebugToggler::class),
                $c->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class),
                $c->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class),
                $c->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class),
                $c->get(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class),
                $c->get(\FP\PerfSuite\Services\CDN\CdnManager::class),
                $c->get(\FP\PerfSuite\Services\Assets\CriticalCss::class),
                $c->get(\FP\PerfSuite\Services\Compression\CompressionManager::class)
            );
        });
        $this->container->set(\FP\PerfSuite\Services\AI\Analyzer::class, static fn() => new \FP\PerfSuite\Services\AI\Analyzer());
        $this->container->set(\FP\PerfSuite\Services\Reports\ScheduledReports::class, static fn() => new \FP\PerfSuite\Services\Reports\ScheduledReports());
        $this->container->set(\FP\PerfSuite\Services\PWA\ServiceWorkerManager::class, static fn() => new \FP\PerfSuite\Services\PWA\ServiceWorkerManager());
        
        $this->container->set(\FP\PerfSuite\Admin\Assets::class, static fn() => new \FP\PerfSuite\Admin\Assets());
        $this->container->set(\FP\PerfSuite\Admin\Menu::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Admin\Menu($c));
        $this->container->set(\FP\PerfSuite\Admin\AdminBar::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Admin\AdminBar($c));
        $this->container->set(\FP\PerfSuite\Http\Routes::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Routes($c));
        $this->container->set(\FP\PerfSuite\Shortcodes::class, static fn() => new \FP\PerfSuite\Shortcodes());
    }

    /**
     * Registra handler AJAX
     */
    private function registerAjaxHandlers(): void
    {
        $this->container->set(\FP\PerfSuite\Http\Ajax\RecommendationsAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\RecommendationsAjax($c));
        $this->container->set(\FP\PerfSuite\Http\Ajax\CriticalCssAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\CriticalCssAjax($c));
        $this->container->set(\FP\PerfSuite\Http\Ajax\AIConfigAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\AIConfigAjax($c));
        $this->container->set(\FP\PerfSuite\Http\Ajax\SafeOptimizationsAjax::class, static fn(ServiceContainer $c) => new \FP\PerfSuite\Http\Ajax\SafeOptimizationsAjax($c));
    }

    /**
     * Registra provider Edge Cache
     */
    private function registerEdgeCacheProviders(): void
    {
        $this->container->set(\FP\PerfSuite\Services\Cache\EdgeCache\CloudflareProvider::class, static function (ServiceContainer $c) {
            $settings = $c->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class)->settings()['cloudflare'] ?? [];
            return new \FP\PerfSuite\Services\Cache\EdgeCache\CloudflareProvider(
                $settings['api_token'] ?? '',
                $settings['zone_id'] ?? '',
                $settings['email'] ?? ''
            );
        });
        $this->container->set(\FP\PerfSuite\Services\Cache\EdgeCache\CloudFrontProvider::class, static function (ServiceContainer $c) {
            $settings = $c->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class)->settings()['cloudfront'] ?? [];
            return new \FP\PerfSuite\Services\Cache\EdgeCache\CloudFrontProvider(
                $settings['access_key_id'] ?? '',
                $settings['secret_access_key'] ?? '',
                $settings['distribution_id'] ?? '',
                $settings['region'] ?? 'us-east-1'
            );
        });
        $this->container->set(\FP\PerfSuite\Services\Cache\EdgeCache\FastlyProvider::class, static function (ServiceContainer $c) {
            $settings = $c->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class)->settings()['fastly'] ?? [];
            return new \FP\PerfSuite\Services\Cache\EdgeCache\FastlyProvider(
                $settings['api_key'] ?? '',
                $settings['service_id'] ?? ''
            );
        });
    }
}

