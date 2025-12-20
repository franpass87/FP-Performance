<?php

/**
 * Event Dispatcher
 * 
 * PSR-14 compatible event dispatcher for plugin events
 *
 * @package FP\PerfSuite\Core\Events
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Events;

class EventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, array<int, callable[]>> Event listeners keyed by event name and priority */
    private array $listeners = [];
    
    /**
     * Dispatch an event
     * 
     * @param object $event Event object
     * @return object The dispatched event
     */
    public function dispatch(object $event): object
    {
        $eventName = get_class($event);
        
        // Get listeners for this event
        $listeners = $this->getListeners($eventName);
        
        // Call each listener
        foreach ($listeners as $listener) {
            if ($listener($event) === false) {
                // Listener returned false, stop propagation
                break;
            }
        }
        
        return $event;
    }
    
    /**
     * Add an event listener
     * 
     * @param string $eventName Event name or class
     * @param callable $listener Event listener
     * @param int $priority Listener priority (lower = earlier)
     * @return void
     */
    public function addListener(string $eventName, callable $listener, int $priority = 10): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }
        
        if (!isset($this->listeners[$eventName][$priority])) {
            $this->listeners[$eventName][$priority] = [];
        }
        
        $this->listeners[$eventName][$priority][] = $listener;
    }
    
    /**
     * Remove an event listener
     * 
     * @param string $eventName Event name
     * @param callable $listener Event listener
     * @return void
     */
    public function removeListener(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }
        
        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            $key = array_search($listener, $listeners, true);
            if ($key !== false) {
                unset($this->listeners[$eventName][$priority][$key]);
                
                // Reindex array
                $this->listeners[$eventName][$priority] = array_values($this->listeners[$eventName][$priority]);
            }
        }
    }
    
    /**
     * Get all listeners for an event, sorted by priority
     * 
     * @param string $eventName Event name
     * @return callable[] Array of listeners
     */
    private function getListeners(string $eventName): array
    {
        if (!isset($this->listeners[$eventName])) {
            return [];
        }
        
        $allListeners = [];
        
        // Sort by priority (lower = earlier)
        ksort($this->listeners[$eventName]);
        
        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            $allListeners = array_merge($allListeners, $listeners);
        }
        
        return $allListeners;
    }
    
    /**
     * Check if there are listeners for an event
     * 
     * @param string $eventName Event name
     * @return bool
     */
    public function hasListeners(string $eventName): bool
    {
        return isset($this->listeners[$eventName]) && !empty($this->listeners[$eventName]);
    }
}









