<?php

/**
 * Hook Registry
 * 
 * Centralized hook registration system for traceability and debugging
 *
 * @package FP\PerfSuite\Core\Hooks
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Hooks;

class HookRegistry implements HookRegistryInterface
{
    /** @var array<string, array> Registered hooks */
    private array $hooks = [];
    
    /** @var array<string, bool> Hook enabled/disabled state */
    private array $hookStates = [];
    
    /** @var bool Whether hook tracing is enabled (for debug mode) */
    private bool $tracingEnabled = false;
    
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
    public function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1, ?string $context = null): bool
    {
        // Check if hook is disabled
        if (!$this->isHookEnabled($hook)) {
            return false;
        }
        
        // Register with WordPress
        $result = add_action($hook, $callback, $priority, $acceptedArgs);
        
        // Track registration
        $hookData = [
            'type' => 'action',
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $acceptedArgs,
            'context' => $context ?? 'unknown',
            'registered' => time(),
        ];
        
        $this->hooks[$hook][] = $hookData;
        
        // Trace hook registration if enabled
        if ($this->tracingEnabled) {
            $this->traceHook('action', $hook, $hookData);
        }
        
        return $result;
    }
    
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
    public function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1, ?string $context = null): bool
    {
        // Check if hook is disabled
        if (!$this->isHookEnabled($hook)) {
            return false;
        }
        
        // Register with WordPress
        $result = add_filter($hook, $callback, $priority, $acceptedArgs);
        
        // Track registration
        $hookData = [
            'type' => 'filter',
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $acceptedArgs,
            'context' => $context ?? 'unknown',
            'registered' => time(),
        ];
        
        $this->hooks[$hook][] = $hookData;
        
        // Trace hook registration if enabled
        if ($this->tracingEnabled) {
            $this->traceHook('filter', $hook, $hookData);
        }
        
        return $result;
    }
    
    /**
     * Remove an action hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @return bool True on success, false on failure
     */
    public function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        $result = remove_action($hook, $callback, $priority);
        
        // Remove from tracking
        if ($result && isset($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $key => $registered) {
                if ($registered['callback'] === $callback && $registered['priority'] === $priority) {
                    unset($this->hooks[$hook][$key]);
                    break;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Remove a filter hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Hook priority
     * @return bool True on success, false on failure
     */
    public function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        $result = remove_filter($hook, $callback, $priority);
        
        // Remove from tracking
        if ($result && isset($this->hooks[$hook])) {
            foreach ($this->hooks[$hook] as $key => $registered) {
                if ($registered['callback'] === $callback && $registered['priority'] === $priority) {
                    unset($this->hooks[$hook][$key]);
                    break;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Get all registered hooks
     * 
     * @param string|null $type Hook type ('action', 'filter', or null for all)
     * @return array Array of registered hooks
     */
    public function getHooks(?string $type = null): array
    {
        if ($type === null) {
            return $this->hooks;
        }
        
        $filtered = [];
        foreach ($this->hooks as $hookName => $registrations) {
            foreach ($registrations as $registration) {
                if ($registration['type'] === $type) {
                    if (!isset($filtered[$hookName])) {
                        $filtered[$hookName] = [];
                    }
                    $filtered[$hookName][] = $registration;
                }
            }
        }
        
        return $filtered;
    }
    
    /**
     * Enable/disable a hook
     * 
     * @param string $hook Hook name
     * @param bool $enabled Whether to enable or disable
     * @return void
     */
    public function setHookEnabled(string $hook, bool $enabled): void
    {
        $this->hookStates[$hook] = $enabled;
    }
    
    /**
     * Check if a hook is enabled
     * 
     * @param string $hook Hook name
     * @return bool
     */
    public function isHookEnabled(string $hook): bool
    {
        return $this->hookStates[$hook] ?? true; // Default to enabled
    }
    
    /**
     * Enable/disable hook tracing (for debug mode)
     * 
     * @param bool $enabled Whether to enable tracing
     * @return void
     */
    public function setTracingEnabled(bool $enabled): void
    {
        $this->tracingEnabled = $enabled;
    }
    
    /**
     * Check if hook tracing is enabled
     * 
     * @return bool
     */
    public function isTracingEnabled(): bool
    {
        return $this->tracingEnabled;
    }
    
    /**
     * Trace hook registration (for debug mode)
     * 
     * @param string $type Hook type ('action' or 'filter')
     * @param string $hook Hook name
     * @param array $hookData Hook registration data
     * @return void
     */
    private function traceHook(string $type, string $hook, array $hookData): void
    {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        $callbackInfo = 'unknown';
        if (is_array($hookData['callback'])) {
            if (is_object($hookData['callback'][0])) {
                $callbackInfo = get_class($hookData['callback'][0]) . '::' . $hookData['callback'][1];
            } else {
                $callbackInfo = $hookData['callback'][0] . '::' . $hookData['callback'][1];
            }
        } elseif (is_string($hookData['callback'])) {
            $callbackInfo = $hookData['callback'];
        } elseif (is_object($hookData['callback']) && ($hookData['callback'] instanceof \Closure)) {
            $callbackInfo = 'Closure';
        }
        
        // Hook trace logging (debug only)
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $traceMessage = sprintf(
                '[FP-PerfSuite Hook Trace] Registered %s: %s | Callback: %s | Priority: %d | Context: %s',
                $type,
                $hook,
                $callbackInfo,
                $hookData['priority'],
                $hookData['context'] ?? ''
            );
            if (class_exists('\FP\PerfSuite\Utils\ErrorHandler')) {
                ErrorHandler::handleSilently(
                    new \RuntimeException($traceMessage),
                    'HookRegistry trace'
                );
            } else {
                error_log($traceMessage);
            }
        }
    }
    
    /**
     * Get hook priority management info
     * 
     * @param string $hook Hook name
     * @return array Array with priority information
     */
    public function getHookPriorities(string $hook): array
    {
        if (!isset($this->hooks[$hook])) {
            return [];
        }
        
        $priorities = [];
        foreach ($this->hooks[$hook] as $registration) {
            $priorities[] = [
                'priority' => $registration['priority'],
                'context' => $registration['context'],
                'registered' => $registration['registered'],
            ];
        }
        
        return $priorities;
    }
}









