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
use function is_array;
use function json_decode;
use function json_encode;
use function is_string;
use function sanitize_key;
use function sprintf;
use function trim;
use function wp_json_encode;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

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
        $headerDefaults = $headers->settings();
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_tools_nonce'])) {
            $nonce = wp_unslash($_POST['fp_ps_tools_nonce']);
            if (!is_string($nonce) || !wp_verify_nonce($nonce, 'fp-ps-tools')) {
                $message = __('Security check failed. Please try again.', 'fp-performance-suite');
            } elseif (isset($_POST['import_json'])) {
                $json = wp_unslash($_POST['settings_json'] ?? '');
                $data = json_decode($json, true);
                if (is_array($data)) {
                    $prepared = [];
                    $valid = true;
                    $allowed = [
                        'fp_ps_page_cache',
                        'fp_ps_browser_cache',
                        'fp_ps_assets',
                        'fp_ps_webp',
                        'fp_ps_db',
                    ];
                    foreach ($allowed as $option) {
                        if (!array_key_exists($option, $data)) {
                            continue;
                        }
                        if (!is_array($data[$option])) {
                            $valid = false;
                            break;
                        }
                        switch ($option) {
                            case 'fp_ps_page_cache':
                                $prepared[$option] = [
                                    'enabled' => !empty($data[$option]['enabled']),
                                    'ttl' => isset($data[$option]['ttl']) ? (int) $data[$option]['ttl'] : $pageCache->settings()['ttl'],
                                ];
                                break;
                            case 'fp_ps_browser_cache':
                                $prepared[$option] = $this->normalizeBrowserCacheImport(
                                    $data[$option],
                                    $headerDefaults
                                );
                                break;
                            case 'fp_ps_assets':
                                $assetDefaults = $optimizer->settings();
                                $incoming = $data[$option];
                                $prepared[$option] = [
                                    'minify_html' => !empty($incoming['minify_html']),
                                    'defer_js' => !empty($incoming['defer_js']),
                                    'async_js' => !empty($incoming['async_js']),
                                    'remove_emojis' => !empty($incoming['remove_emojis']),
                                    'dns_prefetch' => $incoming['dns_prefetch'] ?? $assetDefaults['dns_prefetch'],
                                    'preload' => $incoming['preload'] ?? $assetDefaults['preload'],
                                    'heartbeat_admin' => isset($incoming['heartbeat_admin']) ? (int) $incoming['heartbeat_admin'] : $assetDefaults['heartbeat_admin'],
                                    'combine_css' => !empty($incoming['combine_css']),
                                    'combine_js' => !empty($incoming['combine_js']),
                                ];
                                break;
                            case 'fp_ps_webp':
                                $webpDefaults = $webp->settings();
                                $incoming = $data[$option];
                                $quality = isset($incoming['quality']) ? (int) $incoming['quality'] : $webpDefaults['quality'];
                                $quality = max(1, min(100, $quality));
                                $prepared[$option] = [
                                    'enabled' => !empty($incoming['enabled']),
                                    'quality' => $quality,
                                    'keep_original' => array_key_exists('keep_original', $incoming)
                                        ? (bool) $incoming['keep_original']
                                        : $webpDefaults['keep_original'],
                                    'lossy' => array_key_exists('lossy', $incoming)
                                        ? (bool) $incoming['lossy']
                                        : $webpDefaults['lossy'],
                                ];
                                break;
                            case 'fp_ps_db':
                                $prepared[$option] = [
                                    'schedule' => sanitize_key($data[$option]['schedule'] ?? $cleaner->settings()['schedule']),
                                    'batch' => isset($data[$option]['batch']) ? (int) $data[$option]['batch'] : $cleaner->settings()['batch'],
                                ];
                                break;
                        }
                    }

                    if ($valid) {
                        if (isset($prepared['fp_ps_page_cache'])) {
                            $pageCache->update($prepared['fp_ps_page_cache']);
                        }
                        if (isset($prepared['fp_ps_browser_cache'])) {
                            $headers->update($prepared['fp_ps_browser_cache']);
                        }
                        if (isset($prepared['fp_ps_assets'])) {
                            $optimizer->update($prepared['fp_ps_assets']);
                        }
                        if (isset($prepared['fp_ps_webp'])) {
                            $webp->update($prepared['fp_ps_webp']);
                        }
                        if (isset($prepared['fp_ps_db'])) {
                            $cleaner->update($prepared['fp_ps_db']);
                        }
                        $importStatus = __('Settings imported successfully.', 'fp-performance-suite');
                    } else {
                        $importStatus = __('Invalid JSON payload.', 'fp-performance-suite');
                    }
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

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,headers:array{Cache-Control:string},expires_ttl:int,htaccess:string}
     */
    protected function normalizeBrowserCacheImport(array $incoming, array $defaults): array
    {
        $defaultHeaders = [];
        if (isset($defaults['headers']) && is_array($defaults['headers'])) {
            $defaultHeaders = $defaults['headers'];
        }

        $defaultCacheControl = isset($defaultHeaders['Cache-Control']) && is_string($defaultHeaders['Cache-Control'])
            ? trim($defaultHeaders['Cache-Control'])
            : 'public, max-age=31536000';

        $defaultTtl = isset($defaults['expires_ttl']) ? (int) $defaults['expires_ttl'] : 31536000;
        $defaultHtaccess = isset($defaults['htaccess']) && is_string($defaults['htaccess'])
            ? $defaults['htaccess']
            : '';

        $headerValue = $incoming['headers'] ?? [];
        if (is_string($headerValue)) {
            $headerValue = ['Cache-Control' => $headerValue];
        }

        $cacheControl = $defaultCacheControl;
        if (is_array($headerValue) && isset($headerValue['Cache-Control']) && is_string($headerValue['Cache-Control'])) {
            $trimmed = trim($headerValue['Cache-Control']);
            if ($trimmed !== '') {
                $cacheControl = $trimmed;
            }
        }

        $ttl = $defaultTtl;
        if (isset($incoming['expires_ttl'])) {
            $ttl = (int) $incoming['expires_ttl'];
            if ($ttl < 0) {
                $ttl = $defaultTtl;
            }
        }

        $htaccess = $defaultHtaccess;
        if (isset($incoming['htaccess']) && is_string($incoming['htaccess'])) {
            $htaccess = $incoming['htaccess'];
        }

        return [
            'enabled' => !empty($incoming['enabled']),
            'headers' => ['Cache-Control' => $cacheControl],
            'expires_ttl' => $ttl,
            'htaccess' => $htaccess,
        ];
    }
}
