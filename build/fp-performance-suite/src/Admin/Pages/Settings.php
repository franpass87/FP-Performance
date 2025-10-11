<?php

namespace FP\PerfSuite\Admin\Pages;

use function __;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function get_option;
use function sanitize_text_field;
use function selected;
use function checked;
use function update_option;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Settings extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-settings';
    }

    public function title(): string
    {
        return __('Settings', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Configuration', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $options = get_option('fp_ps_settings', [
            'allowed_role' => 'administrator',
            'safety_mode' => true,
        ]);
        $criticalCss = get_option('fp_ps_critical_css', '');
        $message = '';
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_settings_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_settings_nonce']), 'fp-ps-settings')) {
            $options['allowed_role'] = sanitize_text_field($_POST['allowed_role'] ?? 'administrator');
            $options['safety_mode'] = !empty($_POST['safety_mode']);
            update_option('fp_ps_settings', $options);
            update_option('fp_ps_critical_css', wp_unslash($_POST['critical_css'] ?? ''));
            $message = __('Settings saved.', 'fp-performance-suite');
        }
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Access Control', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-settings', 'fp_ps_settings_nonce'); ?>
                <p>
                    <label for="allowed_role"><?php esc_html_e('Minimum role to manage plugin', 'fp-performance-suite'); ?></label>
                    <select name="allowed_role" id="allowed_role">
                        <option value="administrator" <?php selected($options['allowed_role'], 'administrator'); ?>><?php esc_html_e('Administrator', 'fp-performance-suite'); ?></option>
                        <option value="editor" <?php selected($options['allowed_role'], 'editor'); ?>><?php esc_html_e('Editor', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Safety mode', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Sicuro da attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="safety_mode" value="1" <?php checked($options['safety_mode']); ?> />
                </label>
                <p>
                    <label for="critical_css"><?php esc_html_e('Critical CSS placeholder', 'fp-performance-suite'); ?></label>
                    <textarea name="critical_css" id="critical_css" rows="6" class="large-text code" placeholder="<?php esc_attr_e('Paste above-the-fold CSS or snippet reference.', 'fp-performance-suite'); ?>"><?php echo esc_textarea($criticalCss); ?></textarea>
                </p>
                <p><button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'fp-performance-suite'); ?></button></p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
