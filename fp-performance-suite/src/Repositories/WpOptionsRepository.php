<?php

namespace FP\PerfSuite\Repositories;

use FP\PerfSuite\Contracts\SettingsRepositoryInterface;
use FP\PerfSuite\Plugin;

/**
 * WordPress Options-based settings repository
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class WpOptionsRepository implements SettingsRepositoryInterface
{
    private string $prefix;

    public function __construct(string $prefix = 'fp_ps_')
    {
        $this->prefix = $prefix;
    }

    /**
     * Get setting value
     */
    public function get(string $key, $default = null)
    {
        $fullKey = $this->prefix . $key;
        
        // Use container's cached settings if available
        if (class_exists('\FP\PerfSuite\Plugin')) {
            try {
                $container = Plugin::container();
                $settings = $container->getCachedSettings($fullKey, ['value' => $default]);
                return $settings['value'] ?? $default;
            } catch (\Throwable $e) {
                // Fallback to direct get_option
            }
        }
        
        return get_option($fullKey, $default);
    }

    /**
     * Set setting value
     */
    public function set(string $key, $value): bool
    {
        $fullKey = $this->prefix . $key;
        $result = update_option($fullKey, $value);
        
        // Invalidate cache
        if (class_exists('\FP\PerfSuite\Plugin')) {
            try {
                $container = Plugin::container();
                $container->invalidateSettingsCache($fullKey);
            } catch (\Throwable $e) {
                // Continue anyway
            }
        }
        
        do_action('fp_ps_setting_updated', $key, $value);
        
        return $result;
    }

    /**
     * Check if setting exists
     */
    public function has(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        return get_option($fullKey, '__NOT_FOUND__') !== '__NOT_FOUND__';
    }

    /**
     * Delete setting
     */
    public function delete(string $key): bool
    {
        $fullKey = $this->prefix . $key;
        $result = delete_option($fullKey);
        
        // Invalidate cache
        if (class_exists('\FP\PerfSuite\Plugin')) {
            try {
                $container = Plugin::container();
                $container->invalidateSettingsCache($fullKey);
            } catch (\Throwable $e) {
                // Continue anyway
            }
        }
        
        do_action('fp_ps_setting_deleted', $key);
        
        return $result;
    }

    /**
     * Get all settings with this prefix
     */
    public function all(): array
    {
        global $wpdb;
        
        $pattern = $wpdb->esc_like($this->prefix) . '%';
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $pattern
            ),
            ARRAY_A
        );
        
        $result = [];
        foreach ($options as $option) {
            $key = str_replace($this->prefix, '', $option['option_name']);
            $result[$key] = maybe_unserialize($option['option_value']);
        }
        
        return $result;
    }

    /**
     * Bulk set settings
     * 
     * @param array $settings Array of key => value pairs
     * @return bool Success
     */
    public function bulkSet(array $settings): bool
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
        
        return true;
    }

    /**
     * Get settings matching pattern
     * 
     * @param string $pattern Wildcard pattern (e.g., 'cache_*')
     * @return array
     */
    public function getByPattern(string $pattern): array
    {
        $all = $this->all();
        $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));
        
        return array_filter($all, function($key) use ($pattern) {
            return preg_match('/^' . $pattern . '$/', $key);
        }, ARRAY_FILTER_USE_KEY);
    }
}
