<?php

/**
 * Plugin Facade
 * 
 * Backward compatibility facade for the plugin.
 * All functionality has been moved to PluginKernel and Service Providers.
 *
 * @package FP\PerfSuite
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite;

use FP\PerfSuite\Kernel\PluginKernel;
use FP\PerfSuite\ServiceContainer;

class Plugin
{
    /**
     * Get the service container
     * 
     * @return ServiceContainerAdapter Service container adapter (backward compatible)
     */
    public static function container(): ServiceContainerAdapter
    {
        $kernelContainer = PluginKernel::container();
        
        // Return adapter for backward compatibility
        return new ServiceContainerAdapter($kernelContainer);
    }
    
    /**
     * Check if plugin is initialized
     * 
     * @return bool
     */
    public static function isInitialized(): bool
    {
        return PluginKernel::isBooted();
    }
    
    /**
     * Reset plugin state (for testing/debugging)
     * 
     * @internal
     * @return void
     */
    public static function reset(): void
    {
        PluginKernel::reset();
    }
    
    /**
     * Preboot the plugin (environment setup)
     * 
     * @deprecated Use PluginKernel::preboot() instead
     * @return void
     */
    public static function preboot(): void
    {
        PluginKernel::preboot();
    }
    
    /**
     * Initialize plugin
     * 
     * @deprecated Plugin initialization is now handled by PluginKernel::boot()
     * This method is kept for backward compatibility.
     * @return void
     */
    public static function init(): void
    {
        // Delegate to kernel
        PluginKernel::boot();
    }
    
    /**
     * Handle plugin activation
     * 
     * @return void
     */
    public static function onActivate(): void
    {
        PluginKernel::onActivate();
    }
    
    /**
     * Handle plugin deactivation
     * 
     * @return void
     */
    public static function onDeactivate(): void
    {
        PluginKernel::onDeactivate();
    }
    
    /**
     * Register a service only if not already registered
     * 
     * @deprecated Service registration is now handled by Service Providers
     * @param string $serviceClass Service class name
     * @param callable $registerCallback Registration callback
     * @return bool True if registered, false if already registered
     */
    public static function registerServiceOnce(string $serviceClass, callable $registerCallback): bool
    {
        $container = PluginKernel::container();
        
        // FIX: Se il servizio è già nel container (singleton), chiama comunque register()
        // Questo è importante per BackendOptimizer che deve registrare gli hook anche se già istanziato
        if ($container->has($serviceClass)) {
            try {
                $service = $container->get($serviceClass);
                // Se il servizio ha il metodo register, chiamalo comunque
                if (method_exists($service, 'register')) {
                    $service->register();
                }
            } catch (\Throwable $e) {
                if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                    \FP\PerfSuite\Utils\ErrorHandler::handleSilently($e, "Service re-registration: {$serviceClass}");
                }
            }
            return false;
        }
        
        try {
            $registerCallback();
            return true;
        } catch (\Throwable $e) {
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                \FP\PerfSuite\Utils\ErrorHandler::handleSilently($e, "Service registration: {$serviceClass}");
            }
            return false;
        }
    }
    
    /**
     * Check if a service is registered
     * 
     * @param string $serviceClass Service class name
     * @return bool
     */
    public static function isServiceRegistered(string $serviceClass): bool
    {
        return PluginKernel::container()->has($serviceClass);
    }
    
    /**
     * Check if frontend services should be disabled
     * 
     * @deprecated Use EnvironmentChecker instead
     * @return bool
     */
    public static function shouldDisableFrontendServices(): bool
    {
        return apply_filters('fp_ps_disable_frontend_services', is_admin());
    }
    
    /**
     * Force mobile options initialization
     * 
     * @return bool
     */
    public static function forceMobileOptionsInitialization(): bool
    {
        try {
            $has_mobile_options = get_option('fp_ps_mobile_optimizer') || 
                                 get_option('fp_ps_touch_optimizer') || 
                                 get_option('fp_ps_mobile_cache') || 
                                 get_option('fp_ps_responsive_images');
            
            if (!$has_mobile_options) {
                $container = PluginKernel::container();
                if ($container->has(\FP\PerfSuite\Initialization\DefaultOptionsManager::class)) {
                    $defaultOptionsManager = $container->get(\FP\PerfSuite\Initialization\DefaultOptionsManager::class);
                    $defaultOptionsManager->ensureDefaults();
                }
            }
            
            return true;
        } catch (\Exception $e) {
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                \FP\PerfSuite\Utils\ErrorHandler::handleSilently($e, 'Mobile options initialization');
            }
            return false;
        }
    }
}
