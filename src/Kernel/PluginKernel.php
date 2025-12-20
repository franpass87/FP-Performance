<?php

/**
 * Plugin Kernel
 * 
 * Core orchestrator for the plugin architecture.
 * Manages service providers, container lifecycle, and plugin initialization.
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;
use FP\PerfSuite\Utils\ErrorHandler;

class PluginKernel
{
    /** @var Container|null Service container */
    private static ?Container $container = null;
    
    /** @var array<ServiceProviderInterface> Registered service providers */
    private static array $providers = [];
    
    /** @var bool Whether kernel has been booted */
    private static bool $booted = false;
    
    /**
     * Boot the kernel (static method as per plan)
     * 
     * Registers all providers, boots them, and initializes the plugin.
     * This is the main entry point called from the main plugin file.
     */
    public static function boot(): void
    {
        if (self::$booted && self::$container !== null) {
            return;
        }
        
        // Create container
        self::$container = new Container();
        
        // Register core provider first
        $coreProvider = new \FP\PerfSuite\Providers\CoreServiceProvider();
        if ($coreProvider->shouldLoad()) {
            $coreProvider->register(self::$container);
            self::$providers[] = $coreProvider;
        }
        
        // Discover and register all providers
        self::registerProviders();
        
        // Boot providers in priority order
        self::bootProviders();
        
        self::$booted = true;
        
        // Allow providers to register hooks after booting
        do_action('fp_ps_kernel_booted', self::$container);
    }
    
    /**
     * Preboot the kernel (environment setup before full boot)
     * 
     * Sets up environment guards and basic checks.
     */
    public static function preboot(): void
    {
        // Environment guards are handled by Bootstrap
        // This method is kept for backward compatibility
        if (class_exists('\FP\PerfSuite\Utils\EnvironmentGuard')) {
            \FP\PerfSuite\Utils\EnvironmentGuard::bootstrap();
        }
    }
    
    /**
     * Get the service container
     * 
     * @return Container
     */
    public static function container(): Container
    {
        if (self::$container === null) {
            self::boot();
        }
        
        return self::$container;
    }
    
    /**
     * Check if kernel is booted
     * 
     * @return bool
     */
    public static function isBooted(): bool
    {
        return self::$booted;
    }
    
    /**
     * Get kernel instance (for backward compatibility)
     * 
     * @deprecated Use static methods instead
     * @return self
     */
    public static function getInstance(): self
    {
        return new self();
    }
    
    /**
     * Get the service container (instance method for backward compatibility)
     * 
     * @deprecated Use static container() method instead
     * @return Container
     */
    public function getContainer(): Container
    {
        return self::container();
    }
    
    /**
     * Boot the kernel (instance method for backward compatibility)
     * 
     * @deprecated Use static boot() method instead
     */
    public function bootInstance(): void
    {
        self::boot();
    }
    
    /**
     * Register all service providers
     * 
     * This method discovers and registers all providers.
     * Providers are sorted by priority before registration.
     */
    private static function registerProviders(): void
    {
        $providers = [
            new \FP\PerfSuite\Providers\CoreServiceProvider(),
            new \FP\PerfSuite\Providers\AdminServiceProvider(),
            new \FP\PerfSuite\Providers\FrontendServiceProvider(),
            new \FP\PerfSuite\Providers\RestServiceProvider(),
            new \FP\PerfSuite\Providers\CliServiceProvider(),
            new \FP\PerfSuite\Providers\AssetServiceProvider(),
            new \FP\PerfSuite\Providers\CacheServiceProvider(),
            new \FP\PerfSuite\Providers\DatabaseServiceProvider(),
            new \FP\PerfSuite\Providers\IntelligenceServiceProvider(),
            new \FP\PerfSuite\Providers\MLServiceProvider(),
            new \FP\PerfSuite\Providers\MonitoringServiceProvider(),
            new \FP\PerfSuite\Providers\IntegrationServiceProvider(),
        ];
        
        // Filter providers
        $providers = array_filter($providers, fn($p) => $p->shouldLoad());
        
        // Sort by priority (higher priority = earlier)
        usort($providers, fn($a, $b) => $b->priority() <=> $a->priority());
        
        // Register services from each provider
        foreach ($providers as $provider) {
            try {
                $provider->register(self::$container);
                self::$providers[] = $provider;
            } catch (\Throwable $e) {
                ErrorHandler::handleSilently($e, 'Provider registration: ' . get_class($provider));
                
                // Continue with other providers even if one fails
                continue;
            }
        }
    }
    
    /**
     * Boot all service providers
     * 
     * Calls boot() on each provider after all services are registered.
     */
    private static function bootProviders(): void
    {
        foreach (self::$providers as $provider) {
            try {
                $provider->boot(self::$container);
            } catch (\Throwable $e) {
                ErrorHandler::handleSilently($e, 'Provider boot: ' . get_class($provider));
                
                // Continue with other providers even if one fails
                continue;
            }
        }
    }
    
    /**
     * Handle plugin activation
     * 
     * @return void
     */
    public static function onActivate(): void
    {
        // Activation logic is handled by ActivationService
        if (class_exists('\FP\PerfSuite\Core\Bootstrap\ActivationService')) {
            \FP\PerfSuite\Core\Bootstrap\ActivationService::handle();
        } else {
            // Fallback to Plugin::onActivate() for backward compatibility
            if (class_exists('\FP\PerfSuite\Plugin')) {
                \FP\PerfSuite\Plugin::onActivate();
            }
        }
    }
    
    /**
     * Handle plugin deactivation
     * 
     * @return void
     */
    public static function onDeactivate(): void
    {
        // Deactivation logic is handled by DeactivationService
        if (class_exists('\FP\PerfSuite\Core\Bootstrap\DeactivationService')) {
            \FP\PerfSuite\Core\Bootstrap\DeactivationService::handle();
        } else {
            // Fallback to Plugin::onDeactivate() for backward compatibility
            if (class_exists('\FP\PerfSuite\Plugin')) {
                \FP\PerfSuite\Plugin::onDeactivate();
            }
        }
    }
    
    /**
     * Reset kernel (for testing/debugging)
     * 
     * @internal
     */
    public static function reset(): void
    {
        self::$booted = false;
        self::$providers = [];
        self::$container = null;
    }
}
