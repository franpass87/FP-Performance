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

        wp_enqueue_style(
            'fp-performance-suite-admin',
            plugins_url('assets/admin.css', FP_PERF_SUITE_FILE),
            [],
            FP_PERF_SUITE_VERSION
        );

        wp_enqueue_script(
            'fp-performance-suite-admin',
            plugins_url('assets/admin.js', FP_PERF_SUITE_FILE),
            ['wp-i18n'],
            FP_PERF_SUITE_VERSION,
            true
        );

        wp_localize_script('fp-performance-suite-admin', 'fpPerfSuite', [
            'restUrl' => esc_url_raw(get_rest_url(null, 'fp-ps/v1/')),
            'confirmLabel' => __('Type PROCEDI to confirm high-risk actions', 'fp-performance-suite'),
            'cancelledLabel' => __('Action cancelled', 'fp-performance-suite'),
            'messages' => [
                'logsError' => __('Unable to load log data.', 'fp-performance-suite'),
                'presetError' => __('Unable to apply preset.', 'fp-performance-suite'),
            ],
        ]);
    }
}
