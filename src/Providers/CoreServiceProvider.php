<?php

/**
 * Core Service Provider
 * 
 * Registers core services that are essential for the plugin to function.
 * This provider has the highest priority and loads first.
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class CoreServiceProvider implements ServiceProviderInterface
{
    /**
     * Register core services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Register container itself
        $container->singleton(Container::class, fn() => $container);
        
        // Register ContainerInterface alias
        $container->alias(
            \FP\PerfSuite\Kernel\ContainerInterface::class,
            Container::class
        );
        
        // Register existing ServiceContainer for backward compatibility
        $container->singleton(
            \FP\PerfSuite\ServiceContainer::class,
            function($c) {
                // Create adapter/wrapper for old ServiceContainer
                return $c->get(Container::class);
            }
        );
        
        // Register ServiceContainer as alias for backward compatibility
        $container->alias(
            \FP\PerfSuite\ServiceContainer::class,
            Container::class
        );
        
        // Register Options Repository
        $container->singleton(
            \FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class,
            function($c) {
                $validator = $c->has(\FP\PerfSuite\Core\Validation\ValidatorInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Validation\ValidatorInterface::class)
                    : null;
                $sanitizer = $c->has(\FP\PerfSuite\Core\Sanitization\SanitizerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Sanitization\SanitizerInterface::class)
                    : null;
                
                $repo = new \FP\PerfSuite\Core\Options\OptionsRepository('fp_ps_', $validator, $sanitizer);
                
                // Set defaults
                $defaults = \FP\PerfSuite\Core\Options\OptionsDefaults::getAll();
                $repo->setDefaults($defaults);
                
                return $repo;
            }
        );
        
        // Register Logger
        $container->singleton(
            \FP\PerfSuite\Core\Logging\LoggerInterface::class,
            function($c) {
                $options = $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class);
                $handlers = [
                    new \FP\PerfSuite\Core\Logging\FileLogHandler('DEBUG')
                ];
                
                return new \FP\PerfSuite\Core\Logging\Logger($options, $handlers);
            }
        );
        
        // Register Validator
        $container->singleton(
            \FP\PerfSuite\Core\Validation\ValidatorInterface::class,
            fn() => new \FP\PerfSuite\Core\Validation\Validator()
        );
        
        // Register Sanitizer
        $container->singleton(
            \FP\PerfSuite\Core\Sanitization\SanitizerInterface::class,
            fn() => new \FP\PerfSuite\Core\Sanitization\Sanitizer()
        );
        
        // Register Event Dispatcher
        $container->singleton(
            \FP\PerfSuite\Core\Events\EventDispatcherInterface::class,
            fn() => new \FP\PerfSuite\Core\Events\EventDispatcher()
        );
        
        // Register Utility Services (for backward compatibility)
        $container->singleton(
            \FP\PerfSuite\Utils\Fs::class,
            fn() => new \FP\PerfSuite\Utils\Fs()
        );
        
        $container->singleton(
            \FP\PerfSuite\Utils\Htaccess::class,
            function(Container $c) {
                return new \FP\PerfSuite\Utils\Htaccess(
                    $c->get(\FP\PerfSuite\Utils\Fs::class)
                );
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Utils\Env::class,
            fn() => new \FP\PerfSuite\Utils\Env()
        );
        
        $container->singleton(
            \FP\PerfSuite\Utils\Semaphore::class,
            fn() => new \FP\PerfSuite\Utils\Semaphore()
        );
        
        $container->singleton(
            \FP\PerfSuite\Utils\RateLimiter::class,
            fn() => new \FP\PerfSuite\Utils\RateLimiter()
        );
        
        $container->singleton(
            \FP\PerfSuite\Utils\HostingDetector::class,
            fn() => new \FP\PerfSuite\Utils\HostingDetector()
        );
        
        // Register Health Check
        $container->singleton(
            \FP\PerfSuite\Health\HealthCheck::class,
            fn() => new \FP\PerfSuite\Health\HealthCheck()
        );
        
        // Register Hook Registry
        $container->singleton(
            \FP\PerfSuite\Core\Hooks\HookRegistryInterface::class,
            fn() => new \FP\PerfSuite\Core\Hooks\HookRegistry()
        );
        
        // Register HTTP Client
        $container->singleton(
            \FP\PerfSuite\Core\Http\HttpClientInterface::class,
            fn() => new \FP\PerfSuite\Core\Http\HttpClient(30, 3, 1)
        );
        
        // Register Environment Checker
        $container->singleton(
            \FP\PerfSuite\Core\Environment\EnvironmentChecker::class,
            fn() => new \FP\PerfSuite\Core\Environment\EnvironmentChecker()
        );
        
        // Register Capability Checker
        $container->singleton(
            \FP\PerfSuite\Core\Environment\CapabilityChecker::class,
            fn() => new \FP\PerfSuite\Core\Environment\CapabilityChecker()
        );
        
        // Register Database Checker
        $container->singleton(
            \FP\PerfSuite\Core\Environment\DatabaseChecker::class,
            fn() => new \FP\PerfSuite\Core\Environment\DatabaseChecker()
        );
        
        // Register Cache Interface (TransientCache implementation)
        $container->singleton(
            \FP\PerfSuite\Core\Cache\CacheInterface::class,
            fn() => new \FP\PerfSuite\Core\Cache\TransientCache('fp_ps_')
        );
        
        // Register utility services that may be needed by other services
        $container->singleton(
            \FP\PerfSuite\Utils\InstallationRecovery::class,
            fn() => new \FP\PerfSuite\Utils\InstallationRecovery()
        );
        
        // Register Initialization Services
        $container->singleton(
            \FP\PerfSuite\Initialization\DefaultOptionsManager::class,
            fn() => new \FP\PerfSuite\Initialization\DefaultOptionsManager()
        );
        
        $container->singleton(
            \FP\PerfSuite\Initialization\SafeDefaultsManager::class,
            fn() => new \FP\PerfSuite\Initialization\SafeDefaultsManager()
        );
    }
    
    /**
     * Boot core services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Register Site Health checks
        $healthCheck = $container->get(\FP\PerfSuite\Health\HealthCheck::class);
        $healthCheck::register();
    }
    
    /**
     * Get provider priority
     * 
     * Core provider loads first
     * 
     * @return int
     */
    public function priority(): int
    {
        return 100;
    }
    
    /**
     * Check if provider should load
     * 
     * Core provider always loads
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return true;
    }
}




