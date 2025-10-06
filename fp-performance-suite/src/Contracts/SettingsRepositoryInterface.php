<?php

namespace FP\PerfSuite\Contracts;

/**
 * Interface for settings storage
 */
interface SettingsRepositoryInterface
{
    /**
     * Get a setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Set a setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Value to store
     * @return bool Success
     */
    public function set(string $key, $value): bool;

    /**
     * Check if setting exists
     * 
     * @param string $key Setting key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Delete a setting
     * 
     * @param string $key Setting key
     * @return bool Success
     */
    public function delete(string $key): bool;

    /**
     * Get all settings
     * 
     * @return array
     */
    public function all(): array;
}
