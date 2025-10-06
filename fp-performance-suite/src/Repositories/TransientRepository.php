<?php

namespace FP\PerfSuite\Repositories;

/**
 * Transient-based temporary storage repository
 * 
 * For caching and temporary data
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class TransientRepository
{
    private string $prefix;
    private int $defaultExpiration;

    public function __construct(string $prefix = 'fp_ps_', int $defaultExpiration = 3600)
    {
        $this->prefix = $prefix;
        $this->defaultExpiration = $defaultExpiration;
    }

    /**
     * Get transient value
     */
    public function get(string $key, $default = null)
    {
        $fullKey = $this->prefix . $key;
        $value = get_transient($fullKey);
        
        return $value === false ? $default : $value;
    }

    /**
     * Set transient value
     * 
     * @param string $key Transient key
     * @param mixed $value Value to store
     * @param int|null $expiration Expiration in seconds (null uses default)
     * @return bool Success
     */
    public function set(string $key, $value, ?int $expiration = null): bool
    {
        $fullKey = $this->prefix . $key;
        $expiration = $expiration ?? $this->defaultExpiration;
        
        return set_transient($fullKey, $value, $expiration);
    }

    /**
     * Check if transient exists and is not expired
     */
    public function has(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        return get_transient($fullKey) !== false;
    }

    /**
     * Delete transient
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        return delete_transient($fullKey);
    }

    /**
     * Remember value with callback
     * 
     * Get from cache or generate and cache result
     * 
     * @param string $key Cache key
     * @param callable $callback Function to generate value if not cached
     * @param int|null $expiration Expiration in seconds
     * @return mixed Cached or generated value
     */
    public function remember(string $key, callable $callback, ?int $expiration = null)
    {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $expiration);
        
        return $value;
    }

    /**
     * Increment numeric value
     * 
     * @param string $key Transient key
     * @param int $amount Amount to increment by
     * @return int New value
     */
    public function increment(string $key, int $amount = 1): int
    {
        $value = (int)$this->get($key, 0);
        $newValue = $value + $amount;
        $this->set($key, $newValue);
        
        return $newValue;
    }

    /**
     * Decrement numeric value
     * 
     * @param string $key Transient key
     * @param int $amount Amount to decrement by
     * @return int New value
     */
    public function decrement(string $key, int $amount = 1): int
    {
        $value = (int)$this->get($key, 0);
        $newValue = max(0, $value - $amount);
        $this->set($key, $newValue);
        
        return $newValue;
    }

    /**
     * Clear all transients with this prefix
     * 
     * @return int Number of transients deleted
     */
    public function clear(): int
    {
        global $wpdb;
        
        $pattern = '_transient_' . $wpdb->esc_like($this->prefix) . '%';
        $count = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            )
        );
        
        // Also delete timeout keys
        $timeoutPattern = '_transient_timeout_' . $wpdb->esc_like($this->prefix) . '%';
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $timeoutPattern
            )
        );
        
        return (int)$count;
    }
}
