<?php

namespace FP\PerfSuite\Http\Ajax;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\ServiceContainerAdapter;
use FP\PerfSuite\Kernel\Container as KernelContainer;

use function __;
use function add_action;
use function apply_filters;
use function check_ajax_referer;
use function current_user_can;
use function get_option;
use function set_time_limit;
use function update_option;
use function wp_cache_flush;
use function wp_send_json_error;
use function wp_send_json_success;

class SafeOptimizationsAjax
{
    private const ACTION = 'fp_ps_apply_all_safe_optimizations';
    private const NONCE = 'fp_ps_apply_all_safe';

    private ServiceContainer|ServiceContainerAdapter|KernelContainer $container;
    
    /**
     * @param ServiceContainer|ServiceContainerAdapter|KernelContainer $container
     */
    public function __construct(ServiceContainer|ServiceContainerAdapter|KernelContainer $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        add_action('wp_ajax_' . self::ACTION, [$this, 'handle']);
    }

    public function handle(): void
    {
        check_ajax_referer(self::NONCE, 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Non hai i permessi necessari per eseguire questa operazione.', 'fp-performance-suite'),
            ], 403);
        }

        if (!headers_sent()) {
            @set_time_limit(120);
        }

        $settingsMap = $this->getSettingsMap();
        $applied = 0;
        $errors = [];

        foreach ($settingsMap as $setting) {
            try {
                if ($this->applySetting($setting)) {
                    $applied++;
                }
            } catch (\Throwable $exception) {
                $errors[] = [
                    'key' => $setting['key'],
                    'message' => $exception->getMessage(),
                ];
            }
        }

        do_action('fp_ps_safe_optimizations_applied', $applied, $settingsMap, $errors);

        if ($errors) {
            wp_send_json_error([
                'message' => __('Operazione completata con alcuni avvisi.', 'fp-performance-suite'),
                'applied' => $applied,
                'total' => count($settingsMap),
                'errors' => $errors,
            ], 207);
        }

        wp_cache_flush();

        wp_send_json_success([
            'message' => sprintf(
                /* translators: 1: number applied, 2: total */
                __('Applicate %1$d ottimizzazioni sicure su %2$d.', 'fp-performance-suite'),
                $applied,
                count($settingsMap)
            ),
            'applied' => $applied,
            'total' => count($settingsMap),
        ]);
    }

    private function applySetting(array $setting): bool
    {
        $optionName = $setting['option'];
        $value = $setting['value'];
        $path = $setting['path'];

        if ($path === null) {
            $previous = get_option($optionName);
            if ($previous === $value) {
                return false;
            }
            return update_option($optionName, $value, false);
        }

        $current = get_option($optionName, []);
        $updated = $this->setNestedValue($current, $path, $value);

        if (!$updated) {
            return false;
        }

        return update_option($optionName, $current, false);
    }

    private function setNestedValue(array &$array, array $path, $value): bool
    {
        $changed = false;
        $cursor = &$array;

        $lastIndex = count($path) - 1;
        foreach ($path as $index => $segment) {
            if ($index === $lastIndex) {
                if (($cursor[$segment] ?? null) !== $value) {
                    $cursor[$segment] = $value;
                    $changed = true;
                }
                break;
            }

            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }

            $cursor = &$cursor[$segment];
        }

        return $changed;
    }

    /**
     * @return array<int,array{key:string,option:string,path:?array<int,string>,value:mixed}>
     */
    private function getSettingsMap(): array
    {
        $settings = [
            // Cache
            ['key' => 'page_cache', 'option' => 'fp_ps_page_cache_settings', 'path' => ['enabled'], 'value' => true],
            ['key' => 'browser_cache', 'option' => 'fp_ps_browser_cache', 'path' => ['enabled'], 'value' => true],
            ['key' => 'edge_cache', 'option' => 'fp_ps_edge_cache_enabled', 'path' => null, 'value' => true],
            ['key' => 'predictive_prefetch', 'option' => 'fp_ps_predictive_prefetch', 'path' => ['enabled'], 'value' => true],
            ['key' => 'cache_rules', 'option' => 'fp_ps_htaccess_security', 'path' => ['cache_rules', 'enabled'], 'value' => true],
            ['key' => 'fonts_cache', 'option' => 'fp_ps_htaccess_security', 'path' => ['cache_rules', 'fonts_cache'], 'value' => true],

            // Compression
            ['key' => 'compression_toggle', 'option' => 'fp_ps_compression_enabled', 'path' => null, 'value' => true],
            ['key' => 'gzip_enabled', 'option' => 'fp_ps_compression_deflate_enabled', 'path' => null, 'value' => true],
            ['key' => 'brotli_enabled', 'option' => 'fp_ps_compression_brotli_enabled', 'path' => null, 'value' => true],

            // CSS
            ['key' => 'minify_css', 'option' => 'fp_ps_assets', 'path' => ['minify_css'], 'value' => true],
            ['key' => 'minify_inline_css', 'option' => 'fp_ps_assets', 'path' => ['minify_inline_css'], 'value' => true],
            ['key' => 'remove_html_comments', 'option' => 'fp_ps_assets', 'path' => ['remove_comments'], 'value' => true],
            ['key' => 'optimize_google_fonts', 'option' => 'fp_ps_assets', 'path' => ['optimize_google_fonts'], 'value' => true],

            // JavaScript
            ['key' => 'minify_js', 'option' => 'fp_ps_assets', 'path' => ['minify_js'], 'value' => true],
            ['key' => 'remove_emojis', 'option' => 'fp_ps_assets', 'path' => ['remove_emojis'], 'value' => true],

            // Media / Images
            ['key' => 'image_optimizer_enabled', 'option' => 'fp_ps_image_optimizer', 'path' => ['enabled'], 'value' => true],
            ['key' => 'image_lazy_loading', 'option' => 'fp_ps_image_optimizer', 'path' => ['lazy_loading'], 'value' => true],
            ['key' => 'responsive_images', 'option' => 'fp_ps_responsive_images', 'path' => ['enabled'], 'value' => true],
            ['key' => 'responsive_lazy_loading', 'option' => 'fp_ps_responsive_images', 'path' => ['enable_lazy_loading'], 'value' => true],

            // Database
            ['key' => 'database_core', 'option' => 'fp_ps_db', 'path' => ['enabled'], 'value' => true],
            ['key' => 'query_monitor', 'option' => 'fp_ps_db', 'path' => ['query_monitor', 'enabled'], 'value' => true],
            ['key' => 'db_cleanup_revisions', 'option' => 'fp_ps_db', 'path' => ['cleanup_scope'], 'value' => ['revisions', 'autodrafts', 'spam', 'transients']],
            ['key' => 'db_auto_optimize', 'option' => 'fp_ps_db', 'path' => ['optimization', 'auto_optimize'], 'value' => true],
            ['key' => 'db_optimize_on_cleanup', 'option' => 'fp_ps_db', 'path' => ['optimization', 'optimize_on_cleanup'], 'value' => true],

            // Security
            ['key' => 'security_headers', 'option' => 'fp_ps_htaccess_security', 'path' => ['security_headers', 'enabled'], 'value' => true],
            ['key' => 'xmlrpc_disabled', 'option' => 'fp_ps_htaccess_security', 'path' => ['xmlrpc_disabled'], 'value' => true],
            ['key' => 'file_protection', 'option' => 'fp_ps_htaccess_security', 'path' => ['file_protection', 'enabled'], 'value' => true],
            ['key' => 'protect_hidden_files', 'option' => 'fp_ps_htaccess_security', 'path' => ['file_protection', 'protect_hidden_files'], 'value' => true],
            ['key' => 'protect_wp_config', 'option' => 'fp_ps_htaccess_security', 'path' => ['file_protection', 'protect_wp_config'], 'value' => true],
            ['key' => 'protect_htaccess', 'option' => 'fp_ps_htaccess_security', 'path' => ['file_protection', 'protect_htaccess'], 'value' => true],
            ['key' => 'protect_readme', 'option' => 'fp_ps_htaccess_security', 'path' => ['file_protection', 'protect_readme'], 'value' => true],

            // Font Optimization / Critical Path
            ['key' => 'critical_path_enabled', 'option' => 'fp_ps_critical_path_optimization', 'path' => ['enabled'], 'value' => true],
            ['key' => 'critical_font_preload', 'option' => 'fp_ps_critical_path_optimization', 'path' => ['preload_critical_fonts'], 'value' => true],
            ['key' => 'critical_preconnect', 'option' => 'fp_ps_critical_path_optimization', 'path' => ['preconnect_providers'], 'value' => true],
            ['key' => 'critical_font_display', 'option' => 'fp_ps_critical_path_optimization', 'path' => ['inject_font_display'], 'value' => true],
            ['key' => 'critical_resource_hints', 'option' => 'fp_ps_critical_path_optimization', 'path' => ['add_resource_hints'], 'value' => true],
            ['key' => 'critical_font_swap', 'option' => 'fp_ps_critical_path_optimization', 'path' => ['font_display_swap'], 'value' => true],

            // Mobile
            ['key' => 'mobile_optimizer', 'option' => 'fp_ps_mobile_optimizer', 'path' => ['enabled'], 'value' => true],
            ['key' => 'mobile_disable_animations', 'option' => 'fp_ps_mobile_optimizer', 'path' => ['disable_animations'], 'value' => true],
            ['key' => 'touch_optimizer', 'option' => 'fp_ps_touch_optimizer', 'path' => ['enabled'], 'value' => true],
            ['key' => 'touch_targets', 'option' => 'fp_ps_touch_optimizer', 'path' => ['improve_touch_targets'], 'value' => true],
            ['key' => 'touch_disable_hover', 'option' => 'fp_ps_touch_optimizer', 'path' => ['disable_hover_effects'], 'value' => true],
            ['key' => 'mobile_cache', 'option' => 'fp_ps_mobile_cache', 'path' => ['enabled'], 'value' => true],
            ['key' => 'mobile_cache_headers', 'option' => 'fp_ps_mobile_cache', 'path' => ['enable_mobile_cache_headers'], 'value' => true],
            ['key' => 'mobile_resource_cache', 'option' => 'fp_ps_mobile_cache', 'path' => ['enable_resource_caching'], 'value' => true],
        ];

        return apply_filters('fp_ps_safe_optimizations_settings_map', $settings, $this->container);
    }
}
