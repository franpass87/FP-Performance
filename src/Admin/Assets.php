<?php

namespace FP\PerfSuite\Admin;

class Assets
{
    public function boot(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    public function enqueue(string $hook): void
    {
        if (strpos($hook, 'fp-performance-suite') === false) {
            return;
        }

        // Enqueue modular CSS (uses @import for sub-modules)
        wp_enqueue_style(
            'fp-performance-suite-admin',
            plugins_url('assets/css/admin.css', FP_PERF_SUITE_FILE),
            [],
            FP_PERF_SUITE_VERSION
        );

        // Enqueue modular JavaScript (ES6 modules)
        wp_enqueue_script(
            'fp-performance-suite-admin',
            plugins_url('assets/js/main.js', FP_PERF_SUITE_FILE),
            ['wp-i18n'],
            FP_PERF_SUITE_VERSION,
            true
        );

        // Add type="module" attribute for ES6 modules
        add_filter('script_loader_tag', [$this, 'addModuleType'], 10, 3);
        
        // Enqueue risk tooltip positioner
        wp_enqueue_script(
            'fp-performance-suite-risk-tooltip',
            plugins_url('assets/js/risk-tooltip-positioner.js', FP_PERF_SUITE_FILE),
            [],
            FP_PERF_SUITE_VERSION,
            true
        );

        // Localize script data for JavaScript modules
        wp_localize_script('fp-performance-suite-admin', 'fpPerfSuite', [
            'restUrl' => esc_url_raw(get_rest_url(null, 'fp-ps/v1/')),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'confirmLabel' => __('Type PROCEDI to confirm high-risk actions', 'fp-performance-suite'),
            'cancelledLabel' => __('Action cancelled', 'fp-performance-suite'),
            'messages' => [
                'logsError' => __('Unable to load log data.', 'fp-performance-suite'),
                'presetError' => __('Unable to apply preset.', 'fp-performance-suite'),
                'presetSuccess' => __('Preset applied successfully!', 'fp-performance-suite'),
            ],
        ]);
    }

    /**
     * Add type="module" to our script tag for ES6 module support
     *
     * @param string $tag    The script tag
     * @param string $handle The script handle
     * @param string $src    The script source URL
     * @return string Modified script tag
     */
    public function addModuleType(string $tag, string $handle, string $src): string
    {
        if ('fp-performance-suite-admin' === $handle) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
    }

}
