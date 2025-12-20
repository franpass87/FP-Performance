<?php

/**
 * Capability Checker
 * 
 * Centralized capability checking for the plugin
 *
 * @package FP\PerfSuite\Core\Environment
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Environment;

class CapabilityChecker
{
    /**
     * Check if current user has required capability
     * 
     * @param string|null $capability Capability to check (defaults to manage_options)
     * @return bool
     */
    public function can(string $capability = null): bool
    {
        $capability = $capability ?? 'manage_options';
        return current_user_can($capability);
    }
    
    /**
     * Require capability or die
     * 
     * @param string|null $capability Capability to check
     * @return void
     */
    public function require(string $capability = null): void
    {
        if (!$this->can($capability)) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'fp-performance-suite'));
        }
    }
}









