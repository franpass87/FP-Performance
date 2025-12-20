<?php

/**
 * Hook Registry Interface
 * 
 * Defines contract for centralized hook registration
 *
 * @package FP\PerfSuite\Core\Hooks
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Hooks;

interface HookRegistryInterface
{
    /**
     * Register an action hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @param int $acceptedArgs Number of accepted arguments
     * @param string|null $context Context identifier (for debugging)
     * @return bool True on success, false on failure
     */
    public function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1, ?string $context = null): bool;
    
    /**
     * Register a filter hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @param int $acceptedArgs Number of accepted arguments
     * @param string|null $context Context identifier (for debugging)
     * @return bool True on success, false on failure
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1, ?string $context = null): bool;
    
    /**
     * Remove an action hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @return bool True on success, false on failure
     */
    public function removeAction(string $hook, callable $callback, int $priority = 10): bool;
    
    /**
     * Remove a filter hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @return bool True on success, false on failure
     */
    public function removeFilter(string $hook, callable $callback, int $priority = 10): bool;
    
    /**
     * Get all registered hooks
     * 
     * @param string|null $type Hook type ('action', 'filter', or null for all)
     * @return array Array of registered hooks
     */
    public function getHooks(?string $type = null): array;
    
    /**
     * Enable/disable a hook
     * 
     * @param string $hook Hook name
     * @param bool $enabled Whether to enable or disable
     * @return void
     */
    public function setHookEnabled(string $hook, bool $enabled): void;
    
    /**
     * Check if a hook is enabled
     * 
     * @param string $hook Hook name
     * @return bool
     */
    public function isHookEnabled(string $hook): bool;
}









