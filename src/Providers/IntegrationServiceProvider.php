<?php

/**
 * Integration Service Provider
 * 
 * Registers third-party integrations and compatibility services
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class IntegrationServiceProvider implements ServiceProviderInterface
{
    /**
     * Register integration services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Theme Detector
        $container->singleton(
            \FP\PerfSuite\Services\Compatibility\ThemeDetector::class,
            fn() => new \FP\PerfSuite\Services\Compatibility\ThemeDetector()
        );
        
        // Theme Compatibility
        $container->singleton(
            \FP\PerfSuite\Services\Compatibility\ThemeCompatibility::class,
            function(Container $c) {
                return new \FP\PerfSuite\Services\Compatibility\ThemeCompatibility(
                    $c,
                    $c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class)
                );
            }
        );
        
        // Compatibility Filters
        $container->singleton(
            \FP\PerfSuite\Services\Compatibility\CompatibilityFilters::class,
            function(Container $c) {
                return new \FP\PerfSuite\Services\Compatibility\CompatibilityFilters(
                    $c,
                    $c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class)
                );
            }
        );
        
        // Salient WPBakery Optimizer
        $container->singleton(
            \FP\PerfSuite\Services\Compatibility\SalientWPBakeryOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Compatibility\SalientWPBakeryOptimizer(
                    $c,
                    $c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class),
                    $optionsRepo,
                    $logger
                );
            }
        );
        
        // FP Plugins Integration
        $container->singleton(
            \FP\PerfSuite\Services\Compatibility\FPPluginsIntegration::class,
            fn() => new \FP\PerfSuite\Services\Compatibility\FPPluginsIntegration()
        );
        
        // WooCommerce Optimizer
        $container->singleton(
            \FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Compatibility\WooCommerceOptimizer($optionsRepo);
            }
        );
        
        // Theme Asset Configuration
        $container->singleton(
            \FP\PerfSuite\Services\Assets\ThemeAssetConfiguration::class,
            function(Container $c) {
                return new \FP\PerfSuite\Services\Assets\ThemeAssetConfiguration(
                    $c->get(\FP\PerfSuite\Services\Compatibility\ThemeDetector::class)
                );
            }
        );
    }
    
    /**
     * Boot integration services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Integration services will be initialized based on detected plugins/themes
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 20; // As per plan: IntegrationServiceProvider priority 20
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









