<?php

namespace FP\PerfSuite;

use RuntimeException;

class ServiceContainer
{
    /** @var array<string, callable|object> */
    private array $bindings = [];

    /**
     * @param string $id
     * @param callable $factory
     */
    public function set(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    /**
     * @template T
     * @param class-string<T>|string $id
     * @return T|mixed
     */
    public function get(string $id)
    {
        if (!array_key_exists($id, $this->bindings)) {
            throw new RuntimeException(sprintf('Service "%s" not found.', $id));
        }

        $service = $this->bindings[$id];
        if (is_callable($service)) {
            $this->bindings[$id] = $service = $service($this);
        }

        return $service;
    }

    public function has(string $id): bool
    {
        return array_key_exists($id, $this->bindings);
    }
}
