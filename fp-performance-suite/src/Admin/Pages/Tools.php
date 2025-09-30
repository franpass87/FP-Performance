<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Media\WebPConverter;
use function __;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function json_decode;
use function json_encode;
use function wp_json_encode;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function is_array;
use function sprintf;
use function update_option;

class Tools extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-tools';
    }

    public function title(): string
    {
        return __('Tools & Export', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Tools', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $webp = $this->container->get(WebPConverter::class);
        $optimizer = $this->container->get(Optimizer::class);
        $cleaner = $this->container->get(Cleaner::class);
        $message = '';
        $importStatus = '';
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_tools_nonce'])) {
            $nonce = wp_unslash($_POST['fp_ps_tools_nonce']);
            if (!is_string($nonce) || !wp_verify_nonce($nonce, 'fp-ps-tools')) {
                $message = __('Security check failed. Please try again.', 'fp-performance-suite');
            } elseif (isset($_POST['import_json'])) {
                $json = wp_unslash($_POST['settings_json'] ?? '');
                $data = json_decode($json, true);
                if (is_array($data)) {
                    $allowed = [
                        'fp_ps_page_cache',
                        'fp_ps_browser_cache',
                        'fp_ps_assets',
                        'fp_ps_webp',
                        'fp_ps_db',
                    ];
                    foreach ($allowed as $option) {
                        if (array_key_exists($option, $data)) {
                            update_option($option, $data[$option]);
                        }
                    }
                    $importStatus = __('Settings imported successfully.', 'fp-performance-suite');
                } else {
                    $importStatus = __('Invalid JSON payload.', 'fp-performance-suite');
                }
            }
        }

        $export = [
            'fp_ps_page_cache' => $pageCache->settings(),
            'fp_ps_browser_cache' => $headers->settings(),
            'fp_ps_assets' => $optimizer->settings(),
            'fp_ps_webp' => $webp->settings(),
            'fp_ps_db' => $cleaner->settings(),
        ];
        $tests = [
            __('Page cache enabled', 'fp-performance-suite') => $pageCache->isEnabled() ? __('Pass', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'),
            __('Browser cache headers', 'fp-performance-suite') => $headers->status()['enabled'] ? __('Pass', 'fp-performance-suite') : __('Missing', 'fp-performance-suite'),
            __('WebP coverage', 'fp-performance-suite') => sprintf('%0.2f%%', $webp->status()['coverage']),
        ];

        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Export Settings', 'fp-performance-suite'); ?></h2>
            <textarea class="large-text code" rows="8" readonly><?php echo esc_textarea(wp_json_encode($export, JSON_PRETTY_PRINT)); ?></textarea>
        </section>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Import Settings', 'fp-performance-suite'); ?></h2>
            <?php if ($importStatus) : ?>
                <div class="notice notice-info"><p><?php echo esc_html($importStatus); ?></p></div>
            <?php endif; ?>
            <form method="post">
                <?php wp_nonce_field('fp-ps-tools', 'fp_ps_tools_nonce'); ?>
                <textarea name="settings_json" rows="6" class="large-text code" placeholder="<?php esc_attr_e('Paste JSON export here', 'fp-performance-suite'); ?>"></textarea>
                <p><button type="submit" name="import_json" value="1" class="button" data-risk="amber"><?php esc_html_e('Import JSON', 'fp-performance-suite'); ?></button></p>
            </form>
        </section>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Diagnostics', 'fp-performance-suite'); ?></h2>
            <ul>
                <?php foreach ($tests as $label => $value) : ?>
                    <li><strong><?php echo esc_html($label); ?>:</strong> <?php echo esc_html($value); ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
