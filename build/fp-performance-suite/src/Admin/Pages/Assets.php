<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\Optimizer;

use function __;
use function array_filter;
use function array_map;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function explode;
use function implode;
use function trim;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Assets extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-assets';
    }

    public function title(): string
    {
        return __('Assets Optimization', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Assets', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $optimizer = $this->container->get(Optimizer::class);
        $message = '';
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_assets_nonce']), 'fp-ps-assets')) {
            $dnsPrefetch = array_filter(array_map('trim', explode("\n", wp_unslash($_POST['dns_prefetch'] ?? ''))));
            $preload = array_filter(array_map('trim', explode("\n", wp_unslash($_POST['preload'] ?? ''))));
            $optimizer->update([
                'minify_html' => !empty($_POST['minify_html']),
                'defer_js' => !empty($_POST['defer_js']),
                'async_js' => !empty($_POST['async_js']),
                'remove_emojis' => !empty($_POST['remove_emojis']),
                'dns_prefetch' => $dnsPrefetch,
                'preload' => $preload,
                'heartbeat_admin' => (int) ($_POST['heartbeat_admin'] ?? 60),
                'combine_css' => !empty($_POST['combine_css']),
                'combine_js' => !empty($_POST['combine_js']),
            ]);
            $message = __('Asset settings saved.', 'fp-performance-suite');
        }
        $settings = $optimizer->settings();
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Delivery', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify HTML output', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="minify_html" value="1" <?php checked($settings['minify_html']); ?> />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Defer JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="defer_js" value="1" <?php checked($settings['defer_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Async JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber" title="<?php esc_attr_e('Rischio medio - Testare prima di attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="async_js" value="1" <?php checked($settings['async_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine CSS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red" title="<?php esc_attr_e('Rischio alto - Potrebbe causare problemi', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="combine_css" value="1" <?php checked($settings['combine_css']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine JS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red" title="<?php esc_attr_e('Rischio alto - Potrebbe causare problemi', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="combine_js" value="1" <?php checked($settings['combine_js']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove emojis script', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green" title="<?php esc_attr_e('Rischio basso - Sicuro da attivare', 'fp-performance-suite'); ?>"></span>
                    </span>
                    <input type="checkbox" name="remove_emojis" value="1" <?php checked($settings['remove_emojis']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="heartbeat_admin"><?php esc_html_e('Heartbeat interval (seconds)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="heartbeat_admin" id="heartbeat_admin" value="<?php echo esc_attr((string) $settings['heartbeat_admin']); ?>" min="15" step="5" />
                </p>
                <p>
                    <label for="dns_prefetch"><?php esc_html_e('DNS Prefetch domains (one per line)', 'fp-performance-suite'); ?></label>
                    <textarea name="dns_prefetch" id="dns_prefetch" rows="4" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['dns_prefetch'])); ?></textarea>
                </p>
                <p>
                    <label for="preload"><?php esc_html_e('Preload resources (full URLs)', 'fp-performance-suite'); ?></label>
                    <textarea name="preload" id="preload" rows="4" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['preload'])); ?></textarea>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Asset Settings', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
