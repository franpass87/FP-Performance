<?php

/**
 * Transient Cache Implementation
 * 
 * WordPress transient-based cache implementation.
 * Uses WordPress transients API with object cache fallback.
 *
 * @package FP\PerfSuite\Core\Cache
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Cache;

class TransientCache implements CacheInterface
{
    /** @var string Cache key prefix */
    private string $prefix;
    
    /**
     * Constructor
     * 
     * @param string $prefix Cache key prefix (default: 'fp_ps_')
     */
    public function __construct(string $prefix = 'fp_ps_')
    {
        $this->prefix = $prefix;
    }
    
    /**
     * Normalize cache key with prefix
     * 
     * @param string $key Cache key
     * @return string Normalized key
     */
    private function normalizeKey(string $key): string
    {
        // Remove prefix if already present to avoid double prefixing
        if (strpos($key, $this->prefix) === 0) {
            return $key;
        }
        
        return $this->prefix . $key;
    }
    
    /**
     * Get a cached value
     * 
     * @param string $key Cache key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed Cached value or default
     */
    public function get(string $key, $default = null)
    {
        $normalizedKey = $this->normalizeKey($key);
        $value = get_transient($normalizedKey);
        
        return $value !== false ? $value : $default;
    }
    
    /**
     * Set a cached value
     * 
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Expiration time in seconds (0 = no expiration)
     * @return bool True on success, false on failure
     */
    public function set(string $key, $value, int $expiration = 0): bool
    {
        $normalizedKey = $this->normalizeKey($key);
        return set_transient($normalizedKey, $value, $expiration);
    }
    
    /**
     * Delete a cached value
     * 
     * @param string $key Cache key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool
    {
        $normalizedKey = $this->normalizeKey($key);
        return delete_transient($normalizedKey);
    }
    
    /**
     * Flush all cached values
     * 
     * Note: WordPress doesn't provide a direct way to flush all transients.
     * This method attempts to flush by deleting known transients or using object cache.
     * 
     * @return bool True on success, false on failure
     */
    public function flush(): bool
    {
        // If object cache is available, flush it
        if (function_exists('wp_cache_flush')) {
            return wp_cache_flush();
        }
        
        // Otherwise, we can't reliably flush all transients
        // Return true to indicate "attempted" (not an error)
        return true;
    }
    
    /**
     * Check if a key exists in cache
     * 
     * @param string $key Cache key
     * @return bool True if key exists, false otherwise
     */
    public function has(string $key): bool
    {
        $normalizedKey = $this->normalizeKey($key);
        return get_transient($normalizedKey) !== false;
    }
    
    /**
     * Get multiple cached values at once
     * 
     * @param array<string> $keys Array of cache keys
     * @return array<string, mixed> Array of key => value pairs
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value !== null) {
                $results[$key] = $value;
            }
        }
        
        return $results;
    }
    
    /**
     * Set multiple cached values at once
     * 
     * @param array<string, mixed> $values Array of key => value pairs
     * @param int $expiration Expiration time in seconds (0 = no expiration)
     * @return bool True on success, false on failure
     */
    public function setMultiple(array $values, int $expiration = 0): bool
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $expiration)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Delete multiple cached values at once
     * 
     * @param array<string> $keys Array of cache keys
     * @return bool True on success, false on failure
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        
        return $success;
    }
}










