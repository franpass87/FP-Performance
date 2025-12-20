<?php

/**
 * Event Dispatcher Interface
 * 
 * PSR-14 compatible event dispatcher interface
 *
 * @package FP\PerfSuite\Core\Events
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Events;

interface EventDispatcherInterface
{
    /**
     * Dispatch an event
     * 
     * @param object $event Event object
     * @return object The dispatched event
     */
    public function dispatch(object $event): object;
    
    /**
     * Add an event listener
     * 
     * @param string $eventName Event name or class
     * @param callable $listener Event listener
     * @param int $priority Listener priority
     * @return void
     */
    public function addListener(string $eventName, callable $listener, int $priority = 10): void;
    
    /**
     * Remove an event listener
     * 
     * @param string $eventName Event name
     * @param callable $listener Event listener
     * @return void
     */
    public function removeListener(string $eventName, callable $listener): void;
}









