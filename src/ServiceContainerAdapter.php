<?php

/**
 * Service Container Adapter
 * 
 * Adapter that wraps Kernel\Container to provide backward compatibility
 * with the old ServiceContainer interface.
 * 
 * This allows existing code to continue using ServiceContainer while
 * internally using the new Kernel\Container.
 *
 * @package FP\PerfSuite
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 * 
 * @deprecated Use Kernel\Container directly. This adapter is for backward compatibility only.
 */

namespace FP\PerfSuite;

use FP\PerfSuite\Kernel\Container as KernelContainer;

class ServiceContainerAdapter extends ServiceContainer
{
    /** @var KernelContainer The kernel container being wrapped */
    private KernelContainer $kernelContainer;
    
    /**
     * Constructor
     * 
     * @param KernelContainer $container Kernel container to wrap
     */
    public function __construct(KernelContainer $container)
    {
        $this->kernelContainer = $container;
    }
    
    /**
     * Set a service binding
     * 
     * @param string $id Service identifier
     * @param callable|object|string $concrete Service factory, instance, or class name
     */
    public function set(string $id, $concrete): void
    {
        $this->kernelContainer->set($id, $concrete);
    }
    
    /**
     * Get a service from the container
     * 
     * @template T
     * @param class-string<T>|string $id Service identifier
     * @return T|mixed
     */
    public function get(string $id)
    {
        return $this->kernelContainer->get($id);
    }
    
    /**
     * Check if a service is bound
     * 
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool
    {
        return $this->kernelContainer->has($id);
    }
    
    /**
     * Get cached settings to reduce database queries
     * 
     * @param string $optionName WordPress option name
     * @param array $defaults Default values
     * @return array Parsed settings
     */
    public function getCachedSettings(string $optionName, array $defaults = []): array
    {
        return $this->kernelContainer->getCachedSettings($optionName, $defaults);
    }
    
    /**
     * Invalidate settings cache after update
     * 
     * @param string $optionName WordPress option name to invalidate
     */
    public function invalidateSettingsCache(string $optionName): void
    {
        $this->kernelContainer->invalidateSettingsCache($optionName);
    }
    
    /**
     * Clear all settings cache
     */
    public function clearSettingsCache(): void
    {
        $this->kernelContainer->clearSettingsCache();
    }
    
    /**
     * Get the underlying kernel container
     * 
     * @internal Use this only for migration purposes
     * @return KernelContainer
     */
    public function getKernelContainer(): KernelContainer
    {
        return $this->kernelContainer;
    }
}







