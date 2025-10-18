<?php

namespace FP\PerfSuite\Admin;

class Assets
{
    public function boot(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_head', [$this, 'injectDarkModeScript'], 1);
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

    /**
     * Inject dark mode script early to prevent flash of unstyled content (FOUC)
     * This applies the saved dark mode preference before the page renders
     *
     * @return void
     */
    public function injectDarkModeScript(): void
    {
        // Only inject on our admin pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'fp-performance-suite') === false) {
            return;
        }

        ?>
        <script>
        (function() {
            // Apply dark mode preference immediately to prevent FOUC
            var preference = localStorage.getItem('fp_ps_dark_mode') || 'auto';
            var body = document.body;
            
            // Check if body is available (it should be in admin_head, but let's be safe)
            if (!body) {
                return;
            }
            
            if (preference === 'dark') {
                body.classList.add('fp-dark-mode');
            } else if (preference === 'light') {
                body.classList.add('fp-light-mode');
            }
            // 'auto' mode is handled by CSS media queries
        })();
        </script>
        <?php
    }
}
