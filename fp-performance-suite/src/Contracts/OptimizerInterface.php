<?php

namespace FP\PerfSuite\Contracts;

/**
 * Interface for asset optimization implementations
 */
interface OptimizerInterface
{
    /**
     * Register optimizer hooks
     */
    public function register(): void;

    /**
     * Get optimizer settings
     * 
     * @return array
     */
    public function settings(): array;

    /**
     * Update optimizer settings
     * 
     * @param array $settings
     */
    public function update(array $settings): void;

    /**
     * Get optimizer status
     * 
     * @return array
     */
    public function status(): array;
}
