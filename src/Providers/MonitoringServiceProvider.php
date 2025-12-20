<?php

/**
 * Monitoring Service Provider
 * 
 * Registers monitoring and reporting services
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class MonitoringServiceProvider implements ServiceProviderInterface
{
    /**
     * Register monitoring services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Performance Monitor
        $container->singleton(
            \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance($optionsRepo);
            }
        );
        
        // System Monitor
        $container->singleton(
            \FP\PerfSuite\Services\Monitoring\SystemMonitor::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return \FP\PerfSuite\Services\Monitoring\SystemMonitor::instance($optionsRepo);
            }
        );
        
        // Performance Analyzer
        $container->singleton(
            \FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer(
                    $c->get(\FP\PerfSuite\Services\Cache\PageCache::class),
                    $c->get(\FP\PerfSuite\Services\Cache\Headers::class),
                    $c->get(\FP\PerfSuite\Services\Assets\Optimizer::class),
                    $c->get(\FP\PerfSuite\Services\DB\Cleaner::class),
                    $c->get(\FP\PerfSuite\Services\Monitoring\PerformanceMonitor::class),
                    $optionsRepo
                );
            }
        );
        
        // Recommendation Applicator
        $container->singleton(
            \FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Monitoring\RecommendationApplicator(
                    $c->get(\FP\PerfSuite\Services\Cache\PageCache::class),
                    $c->get(\FP\PerfSuite\Services\Cache\Headers::class),
                    $c->get(\FP\PerfSuite\Services\Assets\Optimizer::class),
                    $c->get(\FP\PerfSuite\Services\DB\Cleaner::class)
                );
            }
        );
        
        // Core Web Vitals Monitor
        $container->singleton(
            \FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor(true, true, true, $optionsRepo);
            }
        );
        
        // Scheduled Reports
        $container->singleton(
            \FP\PerfSuite\Services\Reports\ScheduledReports::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Reports\ScheduledReports($optionsRepo, $logger);
            }
        );
        
        // Scorer
        $container->singleton(
            \FP\PerfSuite\Services\Score\Scorer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
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
                    $c->get(\FP\PerfSuite\Services\Compression\CompressionManager::class),
                    $optionsRepo
                );
            }
        );
        
        // Preset Manager
        $container->singleton(
            \FP\PerfSuite\Services\Presets\Manager::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Presets\Manager(
                    $c->get(\FP\PerfSuite\Services\Cache\PageCache::class),
                    $c->get(\FP\PerfSuite\Services\Cache\Headers::class),
                    $c->get(\FP\PerfSuite\Services\Assets\Optimizer::class),
                    $c->get(\FP\PerfSuite\Services\DB\Cleaner::class),
                    $c->get(\FP\PerfSuite\Services\Logs\DebugToggler::class),
                    $optionsRepo,
                    $logger
                );
            }
        );
        
        // Log Services
        $container->singleton(
            \FP\PerfSuite\Services\Logs\DebugToggler::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Logs\DebugToggler(
                    $c->get(\FP\PerfSuite\Utils\Fs::class),
                    $c->get(\FP\PerfSuite\Utils\Env::class),
                    $optionsRepo,
                    $logger
                );
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Logs\RealtimeLog::class,
            function(Container $c) {
                return new \FP\PerfSuite\Services\Logs\RealtimeLog(
                    $c->get(\FP\PerfSuite\Services\Logs\DebugToggler::class)
                );
            }
        );
        
        // Security Services
        $container->singleton(
            \FP\PerfSuite\Services\Security\HtaccessSecurity::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                // HtaccessSecurity needs Htaccess and Env utilities (used internally)
                $htaccess = $c->has(\FP\PerfSuite\Utils\Htaccess::class)
                    ? $c->get(\FP\PerfSuite\Utils\Htaccess::class)
                    : null;
                $env = $c->has(\FP\PerfSuite\Utils\Env::class)
                    ? $c->get(\FP\PerfSuite\Utils\Env::class)
                    : null;
                return new \FP\PerfSuite\Services\Security\HtaccessSecurity(
                    true,
                    true,
                    $optionsRepo,
                    $logger
                );
            }
        );
        
        // Mobile Services
        $container->singleton(
            \FP\PerfSuite\Services\Mobile\TouchOptimizer::class,
            fn() => new \FP\PerfSuite\Services\Mobile\TouchOptimizer()
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Mobile\MobileCacheManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Mobile\MobileCacheManager($optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Mobile\ResponsiveImageManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Mobile\ResponsiveImageManager($optionsRepo);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Mobile\MobileOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Mobile\MobileOptimizer(true, true, true, $optionsRepo);
            }
        );
        
        // PWA Services
        $container->singleton(
            \FP\PerfSuite\Services\PWA\ServiceWorkerManager::class,
            fn() => new \FP\PerfSuite\Services\PWA\ServiceWorkerManager()
        );
        
        // AI Analyzer
        $container->singleton(
            \FP\PerfSuite\Services\AI\Analyzer::class,
            fn() => new \FP\PerfSuite\Services\AI\Analyzer()
        );
    }
    
    /**
     * Boot monitoring services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Monitoring services will be initialized based on enabled state
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 30; // As per plan: MonitoringServiceProvider priority 30
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









