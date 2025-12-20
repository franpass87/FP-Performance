<?php

/**
 * Unused JavaScript Optimizer Interface
 * 
 * Interface for JavaScript optimization services that optimize unused JavaScript loading.
 * 
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Services\Assets;

interface UnusedJavaScriptOptimizerInterface
{
    /**
     * Initialize the optimizer service
     * 
     * @return void
     */
    public function init(): void;
    
    /**
     * Optimize scripts by adding defer/async attributes
     * 
     * @return void
     */
    public function optimizeScripts(): void;
    
    /**
     * Check if the optimizer is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool;
    
    /**
     * Get current settings
     * 
     * @return array Settings array
     */
    public function settings(): array;
    
    /**
     * Get status information
     * 
     * @return array Status array
     */
    public function status(): array;
}




