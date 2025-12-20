<?php

/**
 * Options Defaults
 * 
 * Defines default values for all plugin options.
 * Centralizes default option management.
 *
 * @package FP\PerfSuite\Core\Options
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Options;

class OptionsDefaults
{
    /**
     * Get all default option values
     * 
     * @return array<string, mixed> Array of default values keyed by option name
     */
    public static function getAll(): array
    {
        return [
            // Cache defaults
            'fp_ps_page_cache_enabled' => false,
            'fp_ps_page_cache_ttl' => 3600,
            'fp_ps_object_cache_enabled' => false,
            'fp_ps_browser_cache_enabled' => false,
            
            // Asset optimization defaults
            'fp_ps_minify_html' => false,
            'fp_ps_minify_css' => false,
            'fp_ps_minify_js' => false,
            'fp_ps_defer_js' => false,
            'fp_ps_async_js' => false,
            'fp_ps_lazy_load_images' => false,
            
            // Database defaults
            'fp_ps_db_cleanup_enabled' => false,
            'fp_ps_db_cleanup_schedule' => 'weekly',
            
            // Performance monitoring defaults
            'fp_ps_monitoring_enabled' => false,
            
            // ML defaults
            'fp_ps_ml_enabled' => false,
            
            // Mobile defaults
            'fp_ps_mobile_optimizer' => false,
            'fp_ps_touch_optimizer' => false,
            'fp_ps_mobile_cache' => false,
            'fp_ps_responsive_images' => false,
            
            // Logging defaults
            'fp_ps_log_level' => 'ERROR',
            'fp_ps_debug_enabled' => false,
            
            // Version
            'fp_perfsuite_version' => '2.0.0',
        ];
    }
    
    /**
     * Get default value for a specific option
     * 
     * @param string $key Option key
     * @param mixed $fallback Fallback value if not found
     * @return mixed Default value
     */
    public static function get(string $key, $fallback = null)
    {
        $defaults = self::getAll();
        return $defaults[$key] ?? $fallback;
    }
    
    /**
     * Check if an option has a default value defined
     * 
     * @param string $key Option key
     * @return bool True if default exists, false otherwise
     */
    public static function has(string $key): bool
    {
        $defaults = self::getAll();
        return array_key_exists($key, $defaults);
    }
}













