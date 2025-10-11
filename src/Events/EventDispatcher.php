<?php

namespace FP\PerfSuite\Events;

use FP\PerfSuite\Utils\Logger;

/**
 * Event Dispatcher
 *
 * Central event dispatching system for the plugin
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class EventDispatcher
{
    private static ?self $instance = null;
    private array $listeners = [];
    private array $dispatched = [];

    /**
     * Get singleton instance
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Dispatch an event
     *
     * @param Event $event Event to dispatch
     * @return Event The event (possibly modified by listeners)
     */
    public function dispatch(Event $event): Event
    {
        $eventName = $event->name();

        Logger::debug('Event dispatched', [
            'name' => $eventName,
            'data' => $event->getData(),
        ]);

        // Track dispatched events
        $this->dispatched[] = [
            'name' => $eventName,
            'timestamp' => $event->timestamp(),
            'data' => $event->getData(),
        ];

        // Fire WordPress action
        do_action("fp_ps_event_{$eventName}", $event);

        // Call registered listeners
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                if (!$event->shouldPropagate()) {
                    break;
                }

                try {
                    call_user_func($listener, $event);
                } catch (\Throwable $e) {
                    Logger::error("Event listener failed for {$eventName}", $e);
                }
            }
        }

        return $event;
    }

    /**
     * Register event listener
     *
     * @param string $eventName Event name to listen for
     * @param callable $listener Callback function
     * @param int $priority Priority (lower runs first)
     */
    public function listen(string $eventName, callable $listener, int $priority = 10): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][$priority][] = $listener;
        ksort($this->listeners[$eventName]);
    }

    /**
     * Remove event listener
     *
     * @param string $eventName Event name
     * @param callable $listener Callback to remove
     */
    public function remove(string $eventName, callable $listener): void
    {
        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $priority => $listeners) {
            $key = array_search($listener, $listeners, true);
            if ($key !== false) {
                unset($this->listeners[$eventName][$priority][$key]);
            }
        }
    }

    /**
     * Get all dispatched events
     *
     * @return array
     */
    public function getDispatched(): array
    {
        return $this->dispatched;
    }

    /**
     * Clear dispatched events history
     */
    public function clearHistory(): void
    {
        $this->dispatched = [];
    }

    /**
     * Get listeners for an event
     *
     * @param string $eventName Event name
     * @return array
     */
    public function getListeners(string $eventName): array
    {
        $listeners = [];

        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $listeners[] = [
                        'callback' => $callback,
                        'priority' => $priority,
                    ];
                }
            }
        }

        return $listeners;
    }
}
