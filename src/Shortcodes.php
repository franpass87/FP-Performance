<?php

namespace FP\PerfSuite;

/**
 * Shortcodes handler for FP Performance Suite.
 *
 * Registers and handles all plugin shortcodes.
 */
class Shortcodes
{
    /**
     * Register all shortcodes.
     *
     * @return void
     */
    public function boot(): void
    {
        add_shortcode('fp_performance_status', [$this, 'renderStatus']);
    }

    /**
     * Render the status shortcode.
     *
     * Displays plugin status, current time, and version in a single line.
     *
     * @param array<string, mixed> $atts Shortcode attributes (not used).
     * @return string Formatted status output.
     */
    public function renderStatus(array $atts = []): string
    {
        global $wpdb;

        $version = defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'unknown';
        
        // Get current MySQL time safely
        $time = 'unavailable';
        if (isset($wpdb) && is_object($wpdb)) {
            $result = $wpdb->get_var('SELECT NOW()');
            if ($result) {
                $time = sanitize_text_field($result);
            }
        }

        return sprintf(
            'FP-Performance OK — %s — v%s',
            esc_html($time),
            esc_html($version)
        );
    }
}

