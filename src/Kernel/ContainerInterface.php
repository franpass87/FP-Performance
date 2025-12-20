<?php

/**
 * Container Interface
 * 
 * Defines the contract for the dependency injection container.
 * This interface allows for different container implementations.
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

interface ContainerInterface
{
    /**
     * Bind a service to the container
     * 
     * @param string $id Service identifier (typically class name)
     * @param callable|object|string $concrete Service factory, instance, or class name
     * @param bool $singleton Whether to treat as singleton
     * @return void
     */
    public function bind(string $id, $concrete, bool $singleton = false): void;
    
    /**
     * Set a service (alias for bind, for backward compatibility)
     * 
     * @param string $id Service identifier
     * @param callable|object|string $concrete Service factory, instance, or class name
     * @return void
     */
    public function set(string $id, $concrete): void;
    
    /**
     * Bind a service as singleton
     * 
     * @param string $id Service identifier
     * @param callable|object|string $concrete Service factory, instance, or class name
     * @return void
     */
    public function singleton(string $id, $concrete): void;
    
    /**
     * Get a service from the container
     * 
     * @template T
     * @param class-string<T>|string $id Service identifier
     * @return T|mixed
     * @throws \RuntimeException If service not found
     */
    public function get(string $id);
    
    /**
     * Check if a service is bound
     * 
     * @param string $id Service identifier
     * @return bool
     */
    public function has(string $id): bool;
    
    /**
     * Create an alias for a service
     * 
     * @param string $alias Alias name
     * @param string $target Target service ID
     * @return void
     */
    public function alias(string $alias, string $target): void;
    
    /**
     * Tag multiple services
     * 
     * @param string $tag Tag name
     * @param array<string> $services Array of service IDs to tag
     * @return void
     */
    public function tag(string $tag, array $services): void;
    
    /**
     * Get all services with a given tag
     * 
     * @param string $tag Tag name
     * @return array<mixed> Array of resolved services
     */
    public function tagged(string $tag): array;
}










