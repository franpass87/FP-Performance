<?php

/**
 * Enhanced Service Container
 * 
 * Provides dependency injection with advanced features:
 * - Service tagging
 * - Singleton pattern
 * - Service aliases
 * - Factory pattern support
 * - Lazy loading
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

use RuntimeException;

class Container implements ContainerInterface
{
    /** @var array<string, callable|object> Service bindings */
    private array $bindings = [];
    
    /** @var array<string, bool> Singleton tracking */
    private array $singletons = [];
    
    /** @var array<string, array<string>> Service tags */
    private array $tags = [];
    
    /** @var array<string, string> Service aliases */
    private array $aliases = [];
    
    /** @var array<string, array> Settings cache to reduce database queries */
    private array $settingsCache = [];
    
    /**
     * Bind a service to the container
     * 
     * @param string $id Service identifier (typically class name)
     * @param callable|object|string $concrete Service factory, instance, or class name
     * @param bool $singleton Whether to treat as singleton
     */
    public function bind(string $id, $concrete, bool $singleton = false): void
    {
        $this->bindings[$id] = $concrete;
        
        if ($singleton) {
            $this->singletons[$id] = true;
        }
    }
    
    /**
     * Set a service (alias for bind, for backward compatibility)
     * 
     * @param string $id Service identifier
     * @param callable|object|string $concrete Service factory, instance, or class name
     */
    public function set(string $id, $concrete): void
    {
        $this->bind($id, $concrete, false);
    }
    
    /**
     * Bind a service as singleton
     * 
     * @param string $id Service identifier
     * @param callable|object|string $concrete Service factory, instance, or class name
     */
    public function singleton(string $id, $concrete): void
    {
        $this->bind($id, $concrete, true);
    }
    
    /**
     * Bind a service only if not already bound
     * 
     * @param string $id Service identifier
     * @param callable|object|string $concrete Service factory, instance, or class name
     * @param bool $singleton Whether to treat as singleton
     */
    public function bindIf(string $id, $concrete, bool $singleton = false): void
    {
        if (!$this->has($id)) {
            $this->bind($id, $concrete, $singleton);
        }
    }
    
    /**
     * Get a service from the container
     * 
     * @template T
     * @param class-string<T>|string $id Service identifier
     * @return T|mixed
     * @throws RuntimeException If service not found
     */
    public function get(string $id)
    {
        // Resolve alias if present
        $id = $this->resolveAlias($id);
        
        if (!array_key_exists($id, $this->bindings)) {
            throw new RuntimeException(sprintf('Service "%s" not found in container.', $id));
        }
        
        $service = $this->bindings[$id];
        
        // If it's a singleton and already resolved, return cached instance
        if (isset($this->singletons[$id]) && is_object($service) && !is_callable($service)) {
            return $service;
        }
        
        // Resolve the service
        if (is_callable($service)) {
            $resolved = $service($this);
            
            // Cache singleton instances
            if (isset($this->singletons[$id])) {
                $this->bindings[$id] = $resolved;
            }
            
            return $resolved;
        }
        
        // If it's a string class name, instantiate it
        if (is_string($service) && class_exists($service)) {
            $instance = new $service();
            
            if (isset($this->singletons[$id])) {
                $this->bindings[$id] = $instance;
            }
            
            return $instance;
        }
        
        return $service;
    }
    
    /**
     * Check if a service is bound
     * 
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool
    {
        $id = $this->resolveAlias($id);
        return array_key_exists($id, $this->bindings);
    }
    
    /**
     * Tag multiple services
     * 
     * @param string $tag Tag name
     * @param array<string> $services Array of service IDs to tag
     */
    public function tag(string $tag, array $services): void
    {
        if (!isset($this->tags[$tag])) {
            $this->tags[$tag] = [];
        }
        
        $this->tags[$tag] = array_merge($this->tags[$tag], $services);
    }
    
    /**
     * Get all services with a given tag
     * 
     * @param string $tag Tag name
     * @return array<mixed> Array of resolved services
     */
    public function tagged(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }
        
        $services = [];
        foreach ($this->tags[$tag] as $serviceId) {
            if ($this->has($serviceId)) {
                $services[] = $this->get($serviceId);
            }
        }
        
        return $services;
    }
    
    /**
     * Create an alias for a service
     * 
     * @param string $alias Alias name
     * @param string $target Target service ID
     */
    public function alias(string $alias, string $target): void
    {
        $this->aliases[$alias] = $target;
    }
    
    /**
     * Resolve service alias
     * 
     * @param string $id Service identifier
     * @return string Resolved service ID
     */
    private function resolveAlias(string $id): string
    {
        while (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }
        
        return $id;
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
        if (!isset($this->settingsCache[$optionName])) {
            $options = get_option($optionName, []);
            $this->settingsCache[$optionName] = wp_parse_args($options, $defaults);
        }
        
        return $this->settingsCache[$optionName];
    }
    
    /**
     * Invalidate settings cache after update
     * 
     * @param string $optionName WordPress option name to invalidate
     */
    public function invalidateSettingsCache(string $optionName): void
    {
        unset($this->settingsCache[$optionName]);
    }
    
    /**
     * Clear all settings cache
     */
    public function clearSettingsCache(): void
    {
        $this->settingsCache = [];
    }
    
    /**
     * Make a service (alias for get, for Laravel-like syntax)
     * 
     * @template T
     * @param class-string<T>|string $id Service identifier
     * @return T|mixed
     */
    public function make(string $id)
    {
        return $this->get($id);
    }
}
