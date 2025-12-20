<?php

/**
 * Options Repository Interface
 * 
 * Defines the contract for options management.
 * Provides a unified interface for accessing plugin options with type safety,
 * validation, defaults, and migration support.
 *
 * @package FP\PerfSuite\Core\Options
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Options;

interface OptionsRepositoryInterface
{
    /**
     * Get an option value
     * 
     * @param string $key Option key
     * @param mixed $default Default value if option doesn't exist
     * @return mixed Option value
     */
    public function get(string $key, $default = null);
    
    /**
     * Set an option value
     * 
     * @param string $key Option key
     * @param mixed $value Option value
     * @param bool $autoload Whether to autoload the option
     * @return bool True on success, false on failure
     */
    public function set(string $key, $value, bool $autoload = true): bool;
    
    /**
     * Delete an option
     * 
     * @param string $key Option key
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool;
    
    /**
     * Check if an option exists
     * 
     * @param string $key Option key
     * @return bool True if option exists, false otherwise
     */
    public function has(string $key): bool;
    
    /**
     * Get all options (with optional prefix filter)
     * 
     * @param string|null $prefix Optional prefix to filter options
     * @return array<string, mixed> Array of options
     */
    public function all(?string $prefix = null): array;
    
    /**
     * Migrate options from one version to another
     * 
     * @param string $fromVersion Source version
     * @param string $toVersion Target version
     * @return bool True on success, false on failure
     */
    public function migrate(string $fromVersion, string $toVersion): bool;
    
    /**
     * Clear options cache
     * 
     * @return void
     */
    public function clearCache(): void;
}













