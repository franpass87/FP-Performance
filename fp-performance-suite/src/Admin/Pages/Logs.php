<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Logs\DebugToggler;
use function __;
use function checked;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function wp_create_nonce;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Logs extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-logs';
    }

    public function title(): string
    {
        return __('Realtime Log Center', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return 'manage_options';
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Monitoring', 'fp-performance-suite'), __('Logs', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $toggler = $this->container->get(DebugToggler::class);
        $status = $toggler->status();
        $message = '';
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_logs_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_logs_nonce']), 'fp-ps-logs')) {
            if (isset($_POST['toggle_debug'])) {
                $enabled = !empty($_POST['wp_debug']);
                $log = !empty($_POST['wp_debug_log']);
                if ($toggler->toggle($enabled, $log)) {
                    $message = __('Debug configuration updated.', 'fp-performance-suite');
                    $status = $toggler->status();
                } else {
                    $message = __('Unable to update wp-config.php. Check file permissions.', 'fp-performance-suite');
                }
            }
            if (isset($_POST['revert_debug'])) {
                if ($toggler->revertLatest()) {
                    $message = __('wp-config.php reverted from backup.', 'fp-performance-suite');
                    $status = $toggler->status();
                } else {
                    $message = __('No backup available to revert.', 'fp-performance-suite');
                }
            }
        }
        $nonce = wp_create_nonce('wp_rest');
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-grid two">
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Debug Toggles', 'fp-performance-suite'); ?></h2>
                <form method="post">
                    <?php wp_nonce_field('fp-ps-logs', 'fp_ps_logs_nonce'); ?>
                    <input type="hidden" name="toggle_debug" value="1" />
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Enable WP_DEBUG', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-badge red"><?php esc_html_e('Red', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="wp_debug" value="1" <?php checked($status['WP_DEBUG']); ?> data-risk="red" />
                    </label>
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Enable debug.log', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-badge amber"><?php esc_html_e('Amber', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="wp_debug_log" value="1" <?php checked($status['WP_DEBUG_LOG']); ?> data-risk="amber" />
                    </label>
                    <p>
                        <button type="submit" class="button button-primary" data-risk="red"><?php esc_html_e('Save Debug Settings', 'fp-performance-suite'); ?></button>
                    </p>
                </form>
                <form method="post" style="margin-top:1em;">
                    <?php wp_nonce_field('fp-ps-logs', 'fp_ps_logs_nonce'); ?>
                    <input type="hidden" name="revert_debug" value="1" />
                    <button type="submit" class="button" data-risk="red"><?php esc_html_e('Revert to Backup', 'fp-performance-suite'); ?></button>
                </form>
                <?php if (!empty($status['log_file'])) : ?>
                    <p><?php esc_html_e('Log file:', 'fp-performance-suite'); ?> <code><?php echo esc_html($status['log_file']); ?></code></p>
                <?php endif; ?>
            </div>
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Realtime Viewer', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Filtered tail of debug.log with live updates every 2 seconds.', 'fp-performance-suite'); ?></p>
                <div class="fp-ps-actions">
                    <label><?php esc_html_e('Level', 'fp-performance-suite'); ?>
                        <select data-fp-log-filter>
                            <option value=""><?php esc_html_e('All', 'fp-performance-suite'); ?></option>
                            <option value="notice"><?php esc_html_e('Notice', 'fp-performance-suite'); ?></option>
                            <option value="warning"><?php esc_html_e('Warning', 'fp-performance-suite'); ?></option>
                            <option value="error"><?php esc_html_e('Error', 'fp-performance-suite'); ?></option>
                        </select>
                    </label>
                    <label><?php esc_html_e('Search', 'fp-performance-suite'); ?>
                        <input type="search" data-fp-log-search placeholder="<?php esc_attr_e('Keyword', 'fp-performance-suite'); ?>" />
                    </label>
                </div>
                <pre class="fp-ps-log-viewer" data-fp-logs data-nonce="<?php echo esc_attr($nonce); ?>" data-lines="200"></pre>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
