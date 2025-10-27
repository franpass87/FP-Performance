<?php

namespace FP\PerfSuite\Admin\Pages;

/**
 * Status Page - Display plugin operational status.
 *
 * Shows current system status, version, and time.
 */
class Status extends AbstractPage
{
    /**
     * Get page slug.
     *
     * @return string
     */
    public function slug(): string
    {
        return 'fp-performance-status';
    }

    /**
     * Get page title.
     *
     * @return string
     */
    public function title(): string
    {
        return __('FP Performance Status', 'fp-performance-suite');
    }

    /**
     * Get view template path.
     *
     * @return string
     */
    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin/status.php';
    }

    /**
     * Generate page content.
     *
     * @return string
     */
    protected function content(): string
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

        ob_start();
        ?>
        <div class="fp-ps-status-card">
            <h2><?php echo esc_html__('Plugin Status', 'fp-performance-suite'); ?></h2>
            <table class="widefat striped">
                <tbody>
                    <tr>
                        <th><?php echo esc_html__('Status', 'fp-performance-suite'); ?></th>
                        <td><strong style="color: green;">✓ <?php echo esc_html__('OK', 'fp-performance-suite'); ?></strong></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Current Time', 'fp-performance-suite'); ?></th>
                        <td><?php echo esc_html($time); ?></td>
                    </tr>
                    <tr>
                        <th><?php echo esc_html__('Version', 'fp-performance-suite'); ?></th>
                        <td><?php echo esc_html($version); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get page data for the view.
     *
     * @return array<string, mixed>
     */
    protected function data(): array
    {
        return [
            'title' => $this->title(),
        ];
    }
}

