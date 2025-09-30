<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use function __;
use function checked;
use function esc_attr;
use function esc_html_e;
use function esc_textarea;
use function printf;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function wp_nonce_field;
use function wp_unslash;

class Cache extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-cache';
    }

    public function title(): string
    {
        return __('Cache Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Cache', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $message = '';

        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_cache_nonce']), 'fp-ps-cache')) {
            if (isset($_POST['fp_ps_page_cache'])) {
                $pageCache->update([
                    'enabled' => !empty($_POST['page_cache_enabled']),
                    'ttl' => (int) ($_POST['page_cache_ttl'] ?? 3600),
                ]);
                $message = __('Page cache settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_browser_cache'])) {
                $headers->update([
                    'enabled' => !empty($_POST['browser_cache_enabled']),
                    'headers' => [
                        'Cache-Control' => sanitize_text_field($_POST['cache_control'] ?? 'public, max-age=31536000'),
                        'Expires' => sanitize_text_field($_POST['expires_header'] ?? ''),
                    ],
                    'htaccess' => sanitize_textarea_field($_POST['htaccess_rules'] ?? ''),
                ]);
                $message = __('Browser cache settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['fp_ps_clear_cache'])) {
                $pageCache->clear();
                $message = __('Page cache cleared.', 'fp-performance-suite');
            }
        }

        $pageSettings = $pageCache->settings();
        $headerSettings = $headers->settings();
        $status = $pageCache->status();

        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Page Cache', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Serve cached HTML for anonymous visitors using filesystem storage.', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_page_cache" value="1" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable page cache', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Recommended for shared hosting with limited CPU.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="page_cache_enabled" value="1" <?php checked($pageSettings['enabled']); ?> data-risk="amber" />
                </label>
                <p>
                    <label for="page_cache_ttl"><?php esc_html_e('Cache lifetime (seconds)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="page_cache_ttl" id="page_cache_ttl" value="<?php echo esc_attr((string) $pageSettings['ttl']); ?>" min="60" step="60" />
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Page Cache', 'fp-performance-suite'); ?></button>
                    <button type="submit" name="fp_ps_clear_cache" value="1" class="button"><?php esc_html_e('Clear Cache', 'fp-performance-suite'); ?></button>
                </p>
                <p class="description"><?php printf(esc_html__('Current cached files: %d', 'fp-performance-suite'), (int) $status['files']); ?></p>
            </form>
        </section>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Browser Cache Headers', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-cache', 'fp_ps_cache_nonce'); ?>
                <input type="hidden" name="fp_ps_browser_cache" value="1" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable headers', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Adds Cache-Control/Expires headers for static files.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="browser_cache_enabled" value="1" <?php checked($headerSettings['enabled']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="cache_control"><?php esc_html_e('Cache-Control', 'fp-performance-suite'); ?></label>
                    <input type="text" name="cache_control" id="cache_control" value="<?php echo esc_attr($headerSettings['headers']['Cache-Control']); ?>" class="regular-text" />
                </p>
                <p>
                    <label for="expires_header"><?php esc_html_e('Expires header', 'fp-performance-suite'); ?></label>
                    <input type="text" name="expires_header" id="expires_header" value="<?php echo esc_attr($headerSettings['headers']['Expires'] ?? ''); ?>" class="regular-text" />
                </p>
                <p>
                    <label for="htaccess_rules"><?php esc_html_e('.htaccess rules', 'fp-performance-suite'); ?></label>
                    <textarea name="htaccess_rules" id="htaccess_rules" rows="6" class="large-text code"><?php echo esc_textarea($headerSettings['htaccess']); ?></textarea>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Headers', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
