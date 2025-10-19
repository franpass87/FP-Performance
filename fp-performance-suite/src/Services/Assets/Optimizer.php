<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Services\Assets\Combiners\CssCombiner;
use FP\PerfSuite\Services\Assets\Combiners\DependencyResolver;
use FP\PerfSuite\Services\Assets\Combiners\JsCombiner;
use FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager;
use FP\PerfSuite\Utils\Semaphore;

use function __;
use function array_key_exists;
use function esc_url_raw;
use function filter_var;
use function is_admin;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function preg_split;
use function trim;
use function array_unique;
use function array_values;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * Asset Optimization Orchestrator
 *
 * Coordinates various asset optimization strategies including minification,
 * combination, defer/async loading, and resource hints.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Optimizer
{
    private const OPTION = 'fp_ps_assets';

    private Semaphore $semaphore;
    private HtmlMinifier $htmlMinifier;
    private ScriptOptimizer $scriptOptimizer;
    private WordPressOptimizer $wpOptimizer;
    private ResourceHintsManager $resourceHints;
    private CssCombiner $cssCombiner;
    private JsCombiner $jsCombiner;

    public function __construct(
        Semaphore $semaphore,
        ?HtmlMinifier $htmlMinifier = null,
        ?ScriptOptimizer $scriptOptimizer = null,
        ?WordPressOptimizer $wpOptimizer = null,
        ?ResourceHintsManager $resourceHints = null,
        ?DependencyResolver $dependencyResolver = null
    ) {
        $this->semaphore = $semaphore;
        $this->htmlMinifier = $htmlMinifier ?? new HtmlMinifier();
        $this->scriptOptimizer = $scriptOptimizer ?? new ScriptOptimizer();
        $this->wpOptimizer = $wpOptimizer ?? new WordPressOptimizer();
        $this->resourceHints = $resourceHints ?? new ResourceHintsManager();

        $dependencyResolver = $dependencyResolver ?? new DependencyResolver();
        $this->cssCombiner = new CssCombiner($dependencyResolver);
        $this->jsCombiner = new JsCombiner($dependencyResolver);
    }

    public function register(): void
    {
        $settings = $this->settings();

        if (!is_admin()) {
            // HTML Minification
            if (!empty($settings['minify_html'])) {
                add_action('template_redirect', [$this, 'startBuffer'], 1);
                add_action('shutdown', [$this, 'endBuffer'], PHP_INT_MAX);
            }

            // Script defer/async
            if (!empty($settings['defer_js'])) {
                add_filter('script_loader_tag', [$this, 'filterScriptTag'], 10, 3);
            }

            // CSS async loading
            if (!empty($settings['async_css'])) {
                add_filter('style_loader_tag', [$this, 'filterStyleTag'], 10, 4);
            }

            // Resource hints
            if (!empty($settings['dns_prefetch'])) {
                $this->resourceHints->setDnsPrefetchUrls($settings['dns_prefetch']);
                add_filter('wp_resource_hints', [$this->resourceHints, 'addDnsPrefetch'], 10, 2);
            }

            if (!empty($settings['preload'])) {
                $this->resourceHints->setPreloadUrls($settings['preload']);
                add_filter('wp_resource_hints', [$this->resourceHints, 'addPreloadHints'], 10, 2);
            }

            if (!empty($settings['preconnect'])) {
                $this->resourceHints->setPreconnectUrls($settings['preconnect']);
                add_filter('wp_resource_hints', [$this->resourceHints, 'addPreconnectHints'], 10, 2);
            }

            // Asset combination
            if (!empty($settings['combine_css']) || !empty($settings['combine_js'])) {
                add_action('wp_enqueue_scripts', [$this, 'applyCombination'], PHP_INT_MAX);
            }
        }

        // WordPress optimizations
        if (!empty($settings['remove_emojis'])) {
            $this->wpOptimizer->disableEmojis();
        }

        if (!empty($settings['heartbeat_admin'])) {
            $this->wpOptimizer->registerHeartbeat((int) $settings['heartbeat_admin']);
        }
    }

    /**
     * Get current settings
     *
     * @return array{
     *  minify_html:bool,
     *  defer_js:bool,
     *  async_js:bool,
     *  async_css:bool,
     *  remove_emojis:bool,
     *  dns_prefetch:array<int,string>,
     *  preload:array<int,string>,
     *  preconnect:array<int,mixed>,
     *  heartbeat_admin:int,
     *  combine_css:bool,
     *  combine_js:bool,
     *  critical_css_handles:array<int,string>
     * }
     */
    public function settings(): array
    {
        $defaults = [
            'minify_html' => false,
            'defer_js' => true,
            'async_js' => false,
            'async_css' => false,
            'remove_emojis' => true,
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
        $options = get_option(self::OPTION, []);

        // Sanitize URL lists
        if (isset($options['dns_prefetch'])) {
            $options['dns_prefetch'] = $this->sanitizeUrlList($options['dns_prefetch']);
        }
        if (isset($options['preload'])) {
            $options['preload'] = $this->sanitizeUrlList($options['preload']);
        }
        if (isset($options['preconnect']) && is_string($options['preconnect'])) {
            $options['preconnect'] = $this->sanitizeUrlList($options['preconnect']);
        }
        if (isset($options['critical_css_handles']) && is_string($options['critical_css_handles'])) {
            $options['critical_css_handles'] = $this->sanitizeHandleList($options['critical_css_handles']);
        }
        if (isset($options['critical_assets_list']) && is_string($options['critical_assets_list'])) {
            $options['critical_assets_list'] = $this->sanitizeUrlList($options['critical_assets_list']);
        }

        return wp_parse_args($options, $defaults);
    }

    public function update(array $settings): void
    {
        $current = $this->settings();
        $new = [
            'minify_html' => $this->resolveFlag($settings, 'minify_html', $current['minify_html']),
            'defer_js' => $this->resolveFlag($settings, 'defer_js', $current['defer_js']),
            'async_js' => $this->resolveFlag($settings, 'async_js', $current['async_js']),
            'async_css' => $this->resolveFlag($settings, 'async_css', $current['async_css']),
            'remove_emojis' => $this->resolveFlag($settings, 'remove_emojis', $current['remove_emojis']),
            'dns_prefetch' => $this->sanitizeUrlList($settings['dns_prefetch'] ?? $current['dns_prefetch']),
            'preload' => $this->sanitizeUrlList($settings['preload'] ?? $current['preload']),
            'preconnect' => isset($settings['preconnect']) ? $this->sanitizeUrlList($settings['preconnect']) : $current['preconnect'],
            'heartbeat_admin' => isset($settings['heartbeat_admin']) ? (int) $settings['heartbeat_admin'] : $current['heartbeat_admin'],
            'combine_css' => $this->resolveFlag($settings, 'combine_css', $current['combine_css']),
            'combine_js' => $this->resolveFlag($settings, 'combine_js', $current['combine_js']),
            'critical_css_handles' => isset($settings['critical_css_handles']) ? $this->sanitizeHandleList($settings['critical_css_handles']) : $current['critical_css_handles'],
            'exclude_css' => isset($settings['exclude_css']) ? $settings['exclude_css'] : $current['exclude_css'],
            'exclude_js' => isset($settings['exclude_js']) ? $settings['exclude_js'] : $current['exclude_js'],
            'minify_inline_css' => $this->resolveFlag($settings, 'minify_inline_css', $current['minify_inline_css']),
            'minify_inline_js' => $this->resolveFlag($settings, 'minify_inline_js', $current['minify_inline_js']),
            'remove_comments' => $this->resolveFlag($settings, 'remove_comments', $current['remove_comments']),
            'optimize_google_fonts' => $this->resolveFlag($settings, 'optimize_google_fonts', $current['optimize_google_fonts']),
            'preload_critical_assets' => $this->resolveFlag($settings, 'preload_critical_assets', $current['preload_critical_assets']),
            'critical_assets_list' => isset($settings['critical_assets_list']) ? (is_array($settings['critical_assets_list']) ? $settings['critical_assets_list'] : $this->sanitizeUrlList($settings['critical_assets_list'])) : $current['critical_assets_list'],
        ];
        update_option(self::OPTION, $new);
    }

    public function applyCombination(): void
    {
        if (is_admin()) {
            return;
        }

        $settings = $this->settings();

        if (!empty($settings['combine_css'])) {
            $this->cssCombiner->combine();
        }

        if (!empty($settings['combine_js'])) {
            $this->jsCombiner->combine(false);  // head scripts
            $this->jsCombiner->combine(true);   // footer scripts
        }
    }

    public function startBuffer(): void
    {
        $this->htmlMinifier->startBuffer();
    }

    public function endBuffer(): void
    {
        $this->htmlMinifier->endBuffer();
    }

    /**
     * @deprecated Use HtmlMinifier::minify() directly
     */
    public function minifyHtml(string $html): string
    {
        return $this->htmlMinifier->minify($html);
    }

    public function filterScriptTag(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();
        $defer = !empty($settings['defer_js']);
        $async = !empty($settings['async_js']);

        return $this->scriptOptimizer->filterScriptTag($tag, $handle, $src, $defer, $async);
    }

    /**
     * Filter style tag to make CSS load asynchronously
     *
     * @param string $html Style tag HTML
     * @param string $handle Style handle
     * @param string $href Style URL
     * @param string $media Media attribute
     * @return string Modified style tag
     */
    public function filterStyleTag(string $html, string $handle, string $href, $media): string
    {
        // Skip critical CSS handles (should load synchronously)
        $settings = $this->settings();
        $criticalHandles = $settings['critical_css_handles'] ?? [];
        
        if (in_array($handle, $criticalHandles, true)) {
            return $html;
        }

        // Skip admin CSS
        if (strpos($handle, 'admin') !== false) {
            return $html;
        }

        // Convert to async loading using preload trick
        // Reference: https://web.dev/defer-non-critical-css/
        $html = str_replace(
            "rel='stylesheet'",
            "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
            $html
        );

        // Add noscript fallback
        $noscriptTag = str_replace(
            "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
            "rel='stylesheet'",
            $html
        );
        $html .= "\n<noscript>" . $noscriptTag . "</noscript>";

        return $html;
    }

    /**
     * @deprecated Use ResourceHintsManager directly
     */
    public function dnsPrefetch(array $hints, string $relation): array
    {
        return $this->resourceHints->addDnsPrefetch($hints, $relation);
    }

    /**
     * @deprecated Use ResourceHintsManager directly
     */
    public function preloadResources(array $hints, string $relation): array
    {
        return $this->resourceHints->addPreloadHints($hints, $relation);
    }

    /**
     * @deprecated Use WordPressOptimizer directly
     */
    public function heartbeatSettings(array $settings): array
    {
        $current = $this->settings();
        return $this->wpOptimizer->configureHeartbeat($settings, (int) $current['heartbeat_admin']);
    }

    public function status(): array
    {
        $settings = $this->settings();
        return [
            'minify_html' => !empty($settings['minify_html']),
            'defer_js' => !empty($settings['defer_js']),
            'async_js' => !empty($settings['async_js']),
            'remove_emojis' => !empty($settings['remove_emojis']),
            'heartbeat_admin' => (int) $settings['heartbeat_admin'],
            'combine_css' => !empty($settings['combine_css']),
            'combine_js' => !empty($settings['combine_js']),
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function riskLevels(): array
    {
        return [
            $this->semaphore->describe('remove_emojis', 'green', __('Disables emoji scripts to save requests.', 'fp-performance-suite')),
            $this->semaphore->describe('defer_js', 'amber', __('Defers non-critical JavaScript to speed rendering.', 'fp-performance-suite')),
            $this->semaphore->describe('combine_js', 'red', __('Combining scripts may break complex setups. Proceed with caution.', 'fp-performance-suite')),
        ];
    }

    /**
     * Sanitize URL list
     *
     * @param mixed $value
     * @return array<int, string>
     */
    private function sanitizeUrlList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $urls = [];
        foreach ($value as $entry) {
            if (is_array($entry)) {
                $entry = $entry['href'] ?? $entry['url'] ?? null;
            }

            if (!is_string($entry)) {
                continue;
            }
            $trimmed = trim($entry);
            if ($trimmed === '') {
                continue;
            }
            $sanitized = esc_url_raw($trimmed);
            if ($sanitized === '') {
                continue;
            }
            $urls[] = $sanitized;
        }

        return array_values(array_unique($urls));
    }

    /**
     * Sanitize handle list (comma or newline separated)
     *
     * @param mixed $value
     * @return array<int, string>
     */
    private function sanitizeHandleList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $handles = [];
        foreach ($value as $handle) {
            if (!is_string($handle)) {
                continue;
            }
            $trimmed = trim($handle);
            if ($trimmed === '') {
                continue;
            }
            // Sanitize handle (allow alphanumeric, dash, underscore)
            $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $trimmed);
            if ($sanitized === '') {
                continue;
            }
            $handles[] = $sanitized;
        }

        return array_values(array_unique($handles));
    }

    private function resolveFlag(array $settings, string $key, bool $current): bool
    {
        if (!array_key_exists($key, $settings)) {
            return $current;
        }

        return $this->interpretFlag($settings[$key], $current);
    }

    /**
     * @param mixed $value
     */
    private function interpretFlag($value, bool $fallback): bool
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

    // Getters for modular components (useful for testing and extension)

    public function getHtmlMinifier(): HtmlMinifier
    {
        return $this->htmlMinifier;
    }

    public function getScriptOptimizer(): ScriptOptimizer
    {
        return $this->scriptOptimizer;
    }

    public function getWordPressOptimizer(): WordPressOptimizer
    {
        return $this->wpOptimizer;
    }

    public function getResourceHintsManager(): ResourceHintsManager
    {
        return $this->resourceHints;
    }

    public function getCssCombiner(): CssCombiner
    {
        return $this->cssCombiner;
    }

    public function getJsCombiner(): JsCombiner
    {
        return $this->jsCombiner;
    }
}
