<?php

namespace FP\PerfSuite\Services\Assets\Optimizer;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Gestisce le impostazioni dell'Asset Optimizer
 * 
 * @package FP\PerfSuite\Services\Assets\Optimizer
 * @author Francesco Passeri
 */
class SettingsManager
{
    private const OPTION = 'fp_ps_assets';
    private DataSanitizer $sanitizer;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Constructor
     * 
     * @param DataSanitizer $sanitizer Data sanitizer
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(DataSanitizer $sanitizer, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->sanitizer = $sanitizer;
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Ottiene le impostazioni correnti
     */
    public function get(): array
    {
        $defaults = [
            'enabled' => false,
            'minify_html' => false,
            'defer_js' => false,
            'async_js' => false,
            'async_css' => false,
            'remove_emojis' => false,
            'dns_prefetch' => [],
            'preload' => [],
            'preconnect' => [],
            'heartbeat_admin' => 60,
            'combine_css' => false,
            'combine_js' => false,
            'critical_css_handles' => [],
            'exclude_css' => '',
            'exclude_js' => '',
            'minify_inline_css' => false,
            'minify_inline_js' => false,
            'remove_comments' => false,
            'optimize_google_fonts' => false,
            'preload_critical_assets' => false,
            'critical_assets_list' => [],
        ];
        $options = $this->getOption(self::OPTION, []);

        // Sanitize URL lists
        if (isset($options['dns_prefetch'])) {
            $options['dns_prefetch'] = $this->sanitizer->sanitizeUrlList($options['dns_prefetch']);
        }
        if (isset($options['preload'])) {
            $options['preload'] = $this->sanitizer->sanitizeUrlList($options['preload']);
        }
        if (isset($options['preconnect']) && is_string($options['preconnect'])) {
            $options['preconnect'] = $this->sanitizer->sanitizeUrlList($options['preconnect']);
        }
        if (isset($options['critical_css_handles']) && is_string($options['critical_css_handles'])) {
            $options['critical_css_handles'] = $this->sanitizer->sanitizeHandleList($options['critical_css_handles']);
        }
        if (isset($options['critical_assets_list']) && is_string($options['critical_assets_list'])) {
            $options['critical_assets_list'] = $this->sanitizer->sanitizeUrlList($options['critical_assets_list']);
        }

        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function update(array $settings): bool
    {
        $current = $this->get();
        $new = [
            'enabled' => $this->sanitizer->resolveFlag($settings, 'enabled', $current['enabled']),
            'minify_html' => $this->sanitizer->resolveFlag($settings, 'minify_html', $current['minify_html']),
            'defer_js' => $this->sanitizer->resolveFlag($settings, 'defer_js', $current['defer_js']),
            'async_js' => $this->sanitizer->resolveFlag($settings, 'async_js', $current['async_js']),
            'async_css' => $this->sanitizer->resolveFlag($settings, 'async_css', $current['async_css']),
            'remove_emojis' => $this->sanitizer->resolveFlag($settings, 'remove_emojis', $current['remove_emojis']),
            'dns_prefetch' => $this->sanitizer->sanitizeUrlList($settings['dns_prefetch'] ?? $current['dns_prefetch']),
            'preload' => $this->sanitizer->sanitizeUrlList($settings['preload'] ?? $current['preload']),
            'preconnect' => isset($settings['preconnect']) ? $this->sanitizer->sanitizeUrlList($settings['preconnect']) : $current['preconnect'],
            'heartbeat_admin' => isset($settings['heartbeat_admin']) ? (int) $settings['heartbeat_admin'] : $current['heartbeat_admin'],
            'combine_css' => $this->sanitizer->resolveFlag($settings, 'combine_css', $current['combine_css']),
            'combine_js' => $this->sanitizer->resolveFlag($settings, 'combine_js', $current['combine_js']),
            'critical_css_handles' => isset($settings['critical_css_handles']) ? $this->sanitizer->sanitizeHandleList($settings['critical_css_handles']) : $current['critical_css_handles'],
            'exclude_css' => isset($settings['exclude_css']) ? $settings['exclude_css'] : $current['exclude_css'],
            'exclude_js' => isset($settings['exclude_js']) ? $settings['exclude_js'] : $current['exclude_js'],
            'minify_inline_css' => $this->sanitizer->resolveFlag($settings, 'minify_inline_css', $current['minify_inline_css']),
            'minify_inline_js' => $this->sanitizer->resolveFlag($settings, 'minify_inline_js', $current['minify_inline_js']),
            'remove_comments' => $this->sanitizer->resolveFlag($settings, 'remove_comments', $current['remove_comments']),
            'optimize_google_fonts' => $this->sanitizer->resolveFlag($settings, 'optimize_google_fonts', $current['optimize_google_fonts']),
            'preload_critical_assets' => $this->sanitizer->resolveFlag($settings, 'preload_critical_assets', $current['preload_critical_assets']),
            'critical_assets_list' => isset($settings['critical_assets_list']) ? (is_array($settings['critical_assets_list']) ? $settings['critical_assets_list'] : $this->sanitizer->sanitizeUrlList($settings['critical_assets_list'])) : $current['critical_assets_list'],
        ];
        return $this->setOption(self::OPTION, $new);
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }
}







