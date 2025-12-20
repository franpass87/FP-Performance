<?php

/**
 * Cache Interface
 * 
 * Abstraction layer for WordPress transients and object cache.
 * Provides a unified interface for caching operations.
 *
 * @package FP\PerfSuite\Core\Cache
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Cache;

interface CacheInterface
{
    /**
     * Get a cached value
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null);
    
    /**
     * Set a cached value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Expiration time in seconds (0 = no expiration)
     * @return bool True on success, false on failure
     */
    public function set(string $key, $value, int $expiration = 0): bool;
    
    /**
     * Delete a cached value
     * 
     * @param string $key Cache key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool;
    
    /**
     * Flush all cached values
     * 
     * @return bool True on success, false on failure
     */
    public function flush(): bool;
    
    /**
     * Check if a key exists in cache
     * 
     * @param string $key Cache key
     * @return bool True if key exists, false otherwise
     */
    public function has(string $key): bool;
    
    /**
     * Get multiple cached values at once
     * 
     * @param array<string> $keys Array of cache keys
     * @return array<string, mixed> Array of key => value pairs
     */
    public function getMultiple(array $keys): array;
    
    /**
     * Set multiple cached values at once
     * 
     * @param array<string, mixed> $values Array of key => value pairs
     * @param int $expiration Expiration time in seconds (0 = no expiration)
     * @return bool True on success, false on failure
     */
    public function setMultiple(array $values, int $expiration = 0): bool;
    
    /**
     * Delete multiple cached values at once
     * 
     * @param array<string> $keys Array of cache keys
     * @return bool True on success, false on failure
     */
    public function deleteMultiple(array $keys): bool;
}










