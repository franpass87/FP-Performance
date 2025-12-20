<?php

/**
 * Intelligence Service Provider
 * 
 * Registers intelligence and smart features services
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

class IntelligenceServiceProvider implements ServiceProviderInterface
{
    /**
     * Register intelligence services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Smart Exclusion Detector
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\SmartExclusionDetector::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Intelligence\SmartExclusionDetector($optionsRepo);
            }
        );
        
        // Page Cache Auto Configurator
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Intelligence\PageCacheAutoConfigurator(
                    $c->get(\FP\PerfSuite\Services\Intelligence\SmartExclusionDetector::class),
                    $optionsRepo
                );
            }
        );
        
        // Performance Based Exclusion Detector
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector::class,
            fn() => new \FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector()
        );
        
        // Cache Auto Configurator
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator($optionsRepo);
            }
        );
        
        // Intelligence Reporter
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\IntelligenceReporter::class,
            fn() => new \FP\PerfSuite\Services\Intelligence\IntelligenceReporter()
        );
        
        // Asset Optimization Integrator
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(LoggerInterface::class)
                    ? $c->get(LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator($optionsRepo, $logger);
            }
        );
        
        // Critical Assets Detector
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\CriticalAssetsDetector::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Intelligence\CriticalAssetsDetector($optionsRepo);
            }
        );
        
        // CDN Exclusion Sync (with OptionsRepository injection)
        $container->singleton(
            \FP\PerfSuite\Services\Intelligence\CDNExclusionSync::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(LoggerInterface::class)
                    ? $c->get(LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Intelligence\CDNExclusionSync($optionsRepo, $logger);
            }
        );
    }
    
    /**
     * Boot intelligence services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Intelligence services will be initialized based on enabled state
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 30; // As per plan: IntelligenceServiceProvider priority 30
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
