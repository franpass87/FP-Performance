<?php

/**
 * Unused CSS Optimizer Interface
 * 
 * Interface for CSS optimization services that remove unused CSS rules.
 * 
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Services\Assets;

interface UnusedCSSOptimizerInterface
{
    /**
     * Optimize HTML by removing unused CSS
     * 
     * @param string $html HTML content
     * @return string Optimized HTML
     */
    public function optimize(string $html): string;
    
    /**
     * Analyze HTML and CSS files to find unused rules
     * 
     * @param string $html HTML content
     * @return array Analysis result with unused rules
     */
    public function analyze(string $html): array;
    
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
}




