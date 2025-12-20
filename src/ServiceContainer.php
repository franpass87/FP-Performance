<?php

namespace FP\PerfSuite;

use RuntimeException;

/**
 * Legacy Service Container
 * 
 * @deprecated Use Kernel\Container or ServiceContainerAdapter instead.
 * This class is kept for backward compatibility but will be removed in a future version.
 * 
 * Migration path:
 * - New code: Use Kernel\Container directly
 * - Existing code: Use ServiceContainerAdapter which wraps Kernel\Container
 * - Plugin::container() returns ServiceContainerAdapter for backward compatibility
 */
class ServiceContainer
{
    /** @var array<string, callable|object> */
    private array $bindings = [];

    /** @var array<string, array> Settings cache to reduce database queries */
    private array $settingsCache = [];

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

    /**
     * Get cached settings to reduce database queries
     *
     * @param string $optionName WordPress option name
     * @param array $defaults Default values
     * @return array Parsed settings
     */
    public function getCachedSettings(string $optionName, array $defaults = []): array
    {
        if (!isset($this->settingsCache[$optionName])) {
            $options = get_option($optionName, []);
            $this->settingsCache[$optionName] = wp_parse_args($options, $defaults);
        }

        return $this->settingsCache[$optionName];
    }

    /**
     * Invalidate settings cache after update
     *
     * @param string $optionName WordPress option name to invalidate
     */
    public function invalidateSettingsCache(string $optionName): void
    {
        unset($this->settingsCache[$optionName]);
    }

    /**
     * Clear all settings cache
     */
    public function clearSettingsCache(): void
    {
        $this->settingsCache = [];
    }
}
