<?php

/**
 * Database Service Provider
 * 
 * Registers all database-related services
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class DatabaseServiceProvider implements ServiceProviderInterface
{
    /**
     * Register database services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Database Cleaner
        $container->singleton(
            \FP\PerfSuite\Services\DB\Cleaner::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\DB\Cleaner(true, true, true, $optionsRepo);
            }
        );
        
        // Database Optimizer
        $container->singleton(
            \FP\PerfSuite\Services\DB\DatabaseOptimizer::class,
            function(Container $c) {
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\DB\DatabaseOptimizer(true, true, $logger);
            }
        );
        
        // Database Query Monitor
        $container->singleton(
            \FP\PerfSuite\Services\DB\DatabaseQueryMonitor::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\DB\DatabaseQueryMonitor($optionsRepo);
            }
        );
        
        // Plugin Specific Optimizer
        $container->singleton(
            \FP\PerfSuite\Services\DB\PluginSpecificOptimizer::class,
            fn() => new \FP\PerfSuite\Services\DB\PluginSpecificOptimizer()
        );
        
        // Query Cache Manager
        $container->singleton(
            \FP\PerfSuite\Services\DB\QueryCacheManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\DB\QueryCacheManager(3600, true, $optionsRepo);
            }
        );
        
        // Database Report Service
        $container->singleton(
            \FP\PerfSuite\Services\DB\DatabaseReportService::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\DB\DatabaseReportService($optionsRepo);
            }
        );
    }
    
    /**
     * Boot database services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Initialize services if needed
        $cleaner = $container->get(\FP\PerfSuite\Services\DB\Cleaner::class);
        if (method_exists($cleaner, 'init')) {
            $cleaner->init();
        }
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 40; // As per plan: DatabaseServiceProvider priority 40
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









