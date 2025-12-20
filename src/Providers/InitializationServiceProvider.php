<?php

/**
 * Initialization Service Provider
 * 
 * Handles plugin initialization tasks (default options, textdomain, cron schedules, etc.)
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Initialization\DefaultOptionsManager;
use FP\PerfSuite\Initialization\SafeDefaultsManager;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;
use FP\PerfSuite\ServiceRegistration\ServiceLoader;
use FP\PerfSuite\Plugin;

class InitializationServiceProvider implements ServiceProviderInterface
{
    /**
     * Register initialization services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // No services to register, only boot logic
    }
    
    /**
     * Boot initialization
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        add_action('init', function() use ($container) {
            // Load textdomain
            load_plugin_textdomain(
                'fp-performance-suite',
                false,
                dirname(plugin_basename(FP_PERF_SUITE_FILE)) . '/languages'
            );
            
            // Initialize default options for existing users
            // Use dependency injection from container
            $defaultOptionsManager = $container->has(DefaultOptionsManager::class)
                ? $container->get(DefaultOptionsManager::class)
                : new DefaultOptionsManager();
            $defaultOptionsManager->ensureDefaults();
            
            // Apply safe defaults if needed
            $safeDefaultsManager = $container->has(SafeDefaultsManager::class)
                ? $container->get(SafeDefaultsManager::class)
                : new SafeDefaultsManager();
            $safeDefaultsManager->maybeApply();
            
            // Force mobile options initialization if needed
            Plugin::forceMobileOptionsInitialization();
            
            // Add custom cron schedules for ML
            add_filter('cron_schedules', function($schedules) {
                $schedules['fp_ps_6hourly'] = [
                    'interval' => 6 * HOUR_IN_SECONDS,
                    'display' => __('Every 6 Hours (FP Performance ML)', 'fp-performance-suite'),
                ];
                return $schedules;
            });

            // Load enabled services conditionally
            // Use adapter for backward compatibility with ServiceLoader
            $adapter = new \FP\PerfSuite\ServiceContainerAdapter($container);
            $serviceLoader = new ServiceLoader($adapter);
            $serviceLoader->loadEnabledServices();
        }, 10);
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 250; // Load after all other providers
    }
    
    /**
     * Check if provider should load
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return true; // Always load
    }
}










