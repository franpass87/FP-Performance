<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Media\WebPConverter;

use function __;
use function array_key_exists;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function filter_var;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function json_decode;
use function json_encode;
use function is_string;
use function sanitize_key;
use function sprintf;
use function strcasecmp;
use function trim;
use function wp_json_encode;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_INT;

class Tools extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-tools';
    }

    public function title(): string
    {
        return __('Configuration', 'fp-performance-suite');
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
        
        // Handle Settings form submission
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_settings_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_settings_nonce']), 'fp-ps-settings')) {
            $pluginOptions = get_option('fp_ps_settings', []);
            $pluginOptions['allowed_role'] = sanitize_text_field($_POST['allowed_role'] ?? 'administrator');
            $pluginOptions['safety_mode'] = !empty($_POST['safety_mode']);
            update_option('fp_ps_settings', $pluginOptions);
            update_option('fp_ps_critical_css', wp_unslash($_POST['critical_css'] ?? ''));
            $message = __('Settings saved.', 'fp-performance-suite');
        }
        
        // Handle Tools form submission
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
                                $prepared[$option] = $this->normalizePageCacheImport(
                                    $data[$option],
                                    $pageCache->settings()
                                );
                                break;
                            case 'fp_ps_browser_cache':
                                $prepared[$option] = $this->normalizeBrowserCacheImport(
                                    $data[$option],
                                    $headerDefaults
                                );
                                break;
                            case 'fp_ps_assets':
                                $assetDefaults = $optimizer->settings();
                                $prepared[$option] = $this->normalizeAssetSettingsImport($data[$option], $assetDefaults);
                                break;
                            case 'fp_ps_webp':
                                $prepared[$option] = $this->normalizeWebpImport(
                                    $data[$option],
                                    $webp->settings()
                                );
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
        
        // Tab corrente
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'importexport';
        $valid_tabs = ['importexport', 'settings'];
        if (!in_array($current_tab, $valid_tabs, true)) {
            $current_tab = 'importexport';
        }
        
        // Mantieni il tab dopo il POST
        if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['current_tab'])) {
            $current_tab = sanitize_key($_POST['current_tab']);
        }
        
        // Load Settings data
        $pluginOptions = get_option('fp_ps_settings', [
            'allowed_role' => 'administrator',
            'safety_mode' => true,
        ]);
        $criticalCss = get_option('fp_ps_critical_css', '');

        ob_start();
        ?>
        
        <!-- Navigazione Tabs -->
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="?page=fp-performance-suite-tools&tab=importexport" 
               class="nav-tab <?php echo $current_tab === 'importexport' ? 'nav-tab-active' : ''; ?>">
                📥 <?php esc_html_e('Import/Export', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-tools&tab=settings" 
               class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                ⚙️ <?php esc_html_e('Plugin Settings', 'fp-performance-suite'); ?>
            </a>
        </div>

        <!-- Tab Description -->
        <?php if ($current_tab === 'importexport') : ?>
            <div class="fp-ps-tab-description" style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; color: #1e40af;">
                    <strong>📥 Import/Export:</strong> 
                    <?php esc_html_e('Esporta tutte le configurazioni del plugin in formato JSON per backup o migrazione su altri siti.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php elseif ($current_tab === 'settings') : ?>
            <div class="fp-ps-tab-description" style="background: #f3e8ff; border-left: 4px solid #a855f7; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; color: #6b21a8;">
                    <strong>⚙️ Plugin Settings:</strong> 
                    <?php esc_html_e('Configura accesso al plugin, modalità sicurezza e impostazioni globali del sistema.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- TAB: Import/Export -->
        <div class="fp-ps-tab-content" data-tab="importexport" style="display: <?php echo $current_tab === 'importexport' ? 'block' : 'none'; ?>;">
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
        
        <!-- Close TAB: Import/Export, Open TAB: Settings -->
        </div>
        <div class="fp-ps-tab-content" data-tab="settings" style="display: <?php echo $current_tab === 'settings' ? 'block' : 'none'; ?>;">
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Access Control', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-tools&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-settings', 'fp_ps_settings_nonce'); ?>
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                <p>
                    <label for="allowed_role"><?php esc_html_e('Minimum role to manage plugin', 'fp-performance-suite'); ?></label>
                    <select name="allowed_role" id="allowed_role">
                        <option value="administrator" <?php selected($pluginOptions['allowed_role'], 'administrator'); ?>><?php esc_html_e('Administrator', 'fp-performance-suite'); ?></option>
                        <option value="editor" <?php selected($pluginOptions['allowed_role'], 'editor'); ?>><?php esc_html_e('Editor', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Safety mode', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Previene operazioni potenzialmente pericolose e aggiunge ulteriori controlli di sicurezza.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Mantieni sempre attivo per maggiore sicurezza, non ha impatti negativi sulle performance.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="safety_mode" value="1" <?php checked($pluginOptions['safety_mode']); ?> />
                </label>
                <p>
                    <label for="critical_css"><?php esc_html_e('Critical CSS placeholder', 'fp-performance-suite'); ?></label>
                    <textarea name="critical_css" id="critical_css" rows="6" class="large-text code" placeholder="<?php esc_attr_e('Paste above-the-fold CSS or snippet reference.', 'fp-performance-suite'); ?>"><?php echo esc_textarea($criticalCss); ?></textarea>
                </p>
                <p><button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'fp-performance-suite'); ?></button></p>
            </form>
        </section>
        
        <!-- Close TAB: Settings -->
        </div>
        
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

        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $headerValue = $incoming['headers'] ?? [];
        if (is_string($headerValue)) {
            $headerValue = ['Cache-Control' => $headerValue];
        }

        $cacheControl = $defaultCacheControl;
        if (is_array($headerValue)) {
            if (isset($headerValue['Cache-Control']) && is_string($headerValue['Cache-Control'])) {
                $trimmed = trim($headerValue['Cache-Control']);
                if ($trimmed !== '') {
                    $cacheControl = $trimmed;
                }
            } else {
                foreach ($headerValue as $key => $value) {
                    if (!is_string($key) || strcasecmp($key, 'Cache-Control') !== 0) {
                        continue;
                    }

                    if (is_string($value)) {
                        $trimmed = trim($value);
                        if ($trimmed !== '') {
                            $cacheControl = $trimmed;
                            break;
                        }
                    }
                }
            }
        }

        if ($cacheControl === $defaultCacheControl && isset($incoming['cache_control']) && is_string($incoming['cache_control'])) {
            $legacy = trim($incoming['cache_control']);
            if ($legacy !== '') {
                $cacheControl = $legacy;
            }
        }

        $ttl = $defaultTtl;
        if (array_key_exists('expires_ttl', $incoming)) {
            $parsedTtl = $this->parseInteger($incoming['expires_ttl']);
            if ($parsedTtl !== null && $parsedTtl >= 0) {
                $ttl = $parsedTtl;
            }
        }

        $htaccess = $defaultHtaccess;
        if (array_key_exists('htaccess', $incoming) && is_string($incoming['htaccess'])) {
            $htaccess = $incoming['htaccess'];
        }

        return [
            'enabled' => $enabled,
            'headers' => ['Cache-Control' => $cacheControl],
            'expires_ttl' => $ttl,
            'htaccess' => $htaccess,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,ttl:int}
     */
    protected function normalizePageCacheImport(array $incoming, array $defaults): array
    {
        $defaultTtl = isset($defaults['ttl']) ? (int) $defaults['ttl'] : 3600;

        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $ttl = $defaultTtl;
        if (array_key_exists('ttl', $incoming)) {
            $parsedTtl = $this->parseInteger($incoming['ttl']);
            if ($parsedTtl !== null && $parsedTtl >= 0) {
                $ttl = $parsedTtl;
            }
        }

        return [
            'enabled' => $enabled,
            'ttl' => $ttl,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,quality:int,keep_original:bool,lossy:bool}
     */
    protected function normalizeWebpImport(array $incoming, array $defaults): array
    {
        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $defaultQuality = isset($defaults['quality']) ? (int) $defaults['quality'] : 82;
        $quality = $defaultQuality;
        if (array_key_exists('quality', $incoming)) {
            $parsedQuality = $this->parseInteger($incoming['quality']);
            if ($parsedQuality !== null) {
                $quality = max(1, min(100, $parsedQuality));
            }
        }

        $keepOriginal = array_key_exists('keep_original', $defaults)
            ? (bool) $defaults['keep_original']
            : false;
        if (array_key_exists('keep_original', $incoming)) {
            $keepOriginal = $this->interpretBoolean($incoming['keep_original'], $keepOriginal);
        }

        $lossy = array_key_exists('lossy', $defaults)
            ? (bool) $defaults['lossy']
            : false;
        if (array_key_exists('lossy', $incoming)) {
            $lossy = $this->interpretBoolean($incoming['lossy'], $lossy);
        }

        return [
            'enabled' => $enabled,
            'quality' => $quality,
            'keep_original' => $keepOriginal,
            'lossy' => $lossy,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    protected function normalizeAssetSettingsImport(array $incoming, array $defaults): array
    {
        $settings = $defaults;

        foreach (['minify_html', 'defer_js', 'async_js', 'remove_emojis', 'combine_css', 'combine_js'] as $flag) {
            if (array_key_exists($flag, $incoming)) {
                $settings[$flag] = $this->interpretBoolean($incoming[$flag], !empty($defaults[$flag] ?? false));
            }
        }

        foreach (['dns_prefetch', 'preload'] as $listKey) {
            if (array_key_exists($listKey, $incoming)) {
                $settings[$listKey] = $incoming[$listKey];
            }
        }

        if (array_key_exists('heartbeat_admin', $incoming)) {
            $parsedHeartbeat = $this->parseInteger($incoming['heartbeat_admin']);
            if ($parsedHeartbeat !== null && $parsedHeartbeat >= 0) {
                $settings['heartbeat_admin'] = $parsedHeartbeat;
            }
        }

        return $settings;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function resolveBoolean(array $source, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return $this->interpretBoolean($source[$key], $default);
    }

    /**
     * @param mixed $value
     */
    private function parseInteger($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            $filtered = filter_var($trimmed, FILTER_VALIDATE_INT);
            if ($filtered !== false) {
                return (int) $filtered;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function interpretBoolean($value, bool $fallback): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return false;
            }

            $filtered = filter_var($trimmed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtered !== null) {
                return $filtered;
            }
        }

        return $fallback;
    }
}
