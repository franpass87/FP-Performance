<?php

namespace FP\PerfSuite\Contracts;

/**
 * Interface for cache implementations
 */
interface CacheInterface
{
    /**
     * Check if cache is enabled
     */
    public function isEnabled(): bool;

    /**
     * Get cache settings
     * 
     * @return array
     */
    public function settings(): array;

    /**
     * Update cache settings
     * 
     * @param array $settings
     */
    public function update(array $settings): void;

    /**
     * Clear all cache
     */
    public function clear(): void;

    /**
     * Get cache status
     * 
     * @return array
     */
    public function status(): array;
}
