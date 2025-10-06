<?php

namespace FP\PerfSuite\Events;

/**
 * Base Event class
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
abstract class Event
{
    protected array $data;
    protected int $timestamp;

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->timestamp = time();
    }

    /**
     * Get event name
     */
    abstract public function name(): string;

    /**
     * Get event data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get specific data value
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Get event timestamp
     */
    public function timestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * Check if event should be propagated
     */
    public function shouldPropagate(): bool
    {
        return true;
    }
}
