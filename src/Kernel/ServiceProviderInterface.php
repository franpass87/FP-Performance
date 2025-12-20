<?php

/**
 * Service Provider Interface
 * 
 * Defines the contract for service providers in the plugin architecture.
 * All service providers must implement this interface.
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

use FP\PerfSuite\Kernel\Container;

interface ServiceProviderInterface
{
    /**
     * Register services in the container
     * 
     * This method is called first to bind all services to the container.
     * Services should be registered but not initialized here.
     * 
     * @param Container $container The service container
     */
    public function register(Container $container): void;
    
    /**
     * Boot services after all providers registered
     * 
     * This method is called after all providers have registered their services.
     * Use this to initialize services that depend on other services.
     * 
     * @param Container $container The service container
     */
    public function boot(Container $container): void;
    
    /**
     * Get provider priority (lower = earlier)
     * 
     * Providers are registered and booted in priority order.
     * Lower numbers mean earlier execution.
     * 
     * @return int Priority value (default: 100)
     */
    public function priority(): int;
    
    /**
     * Check if provider should load
     * 
     * Allows conditional loading based on environment, context, or configuration.
     * 
     * @return bool True if provider should load, false otherwise
     */
    public function shouldLoad(): bool;
}













