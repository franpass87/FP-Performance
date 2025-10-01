<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Semaphore;
use function __;
use function array_key_exists;
use function esc_url_raw;
use function filter_var;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function parse_url;
use function pathinfo;
use function preg_split;
use function strtolower;
use function trim;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const PATHINFO_EXTENSION;

class Optimizer
{
    private const OPTION = 'fp_ps_assets';
    private Semaphore $semaphore;
    private bool $bufferStarted = false;

    public function __construct(Semaphore $semaphore)
    {
        $this->semaphore = $semaphore;
    }

    public function register(): void
    {
        $settings = $this->settings();
        if (!is_admin()) {
            if (!empty($settings['minify_html'])) {
                add_action('template_redirect', [$this, 'startBuffer'], 1);
                add_action('shutdown', [$this, 'endBuffer'], PHP_INT_MAX);
            }
            if (!empty($settings['defer_js'])) {
                add_filter('script_loader_tag', [$this, 'filterScriptTag'], 10, 3);
            }
            if (!empty($settings['dns_prefetch'])) {
                add_filter('wp_resource_hints', [$this, 'dnsPrefetch'], 10, 2);
            }
            if (!empty($settings['preload'])) {
                add_filter('wp_resource_hints', [$this, 'preloadResources'], 10, 2);
            }
        }

        if (!empty($settings['remove_emojis'])) {
            $this->disableEmojis();
        }

        if (!empty($settings['heartbeat_admin'])) {
            add_filter('heartbeat_settings', [$this, 'heartbeatSettings']);
        }
    }

    /**
     * @return array{
     *  minify_html:bool,
     *  defer_js:bool,
     *  async_js:bool,
     *  remove_emojis:bool,
     *  dns_prefetch:array<int,string>,
     *  preload:array<int,string>,
     *  heartbeat_admin:int,
     *  combine_css:bool,
     *  combine_js:bool
     * }
     */
    public function settings(): array
    {
        $defaults = [
            'minify_html' => false,
            'defer_js' => true,
            'async_js' => false,
            'remove_emojis' => true,
            'dns_prefetch' => [],
            'preload' => [],
            'heartbeat_admin' => 60,
            'combine_css' => false,
            'combine_js' => false,
        ];
        $options = get_option(self::OPTION, []);
        $options['dns_prefetch'] = $this->sanitizeUrlList($options['dns_prefetch'] ?? []);
        $options['preload'] = $this->sanitizeUrlList($options['preload'] ?? []);
        return wp_parse_args($options, $defaults);
    }

    public function update(array $settings): void
    {
        $current = $this->settings();
        $new = [
            'minify_html' => $this->resolveFlag($settings, 'minify_html', $current['minify_html']),
            'defer_js' => $this->resolveFlag($settings, 'defer_js', $current['defer_js']),
            'async_js' => $this->resolveFlag($settings, 'async_js', $current['async_js']),
            'remove_emojis' => $this->resolveFlag($settings, 'remove_emojis', $current['remove_emojis']),
            'dns_prefetch' => $this->sanitizeUrlList($settings['dns_prefetch'] ?? $current['dns_prefetch']),
            'preload' => $this->sanitizeUrlList($settings['preload'] ?? $current['preload']),
            'heartbeat_admin' => isset($settings['heartbeat_admin']) ? (int) $settings['heartbeat_admin'] : $current['heartbeat_admin'],
            'combine_css' => $this->resolveFlag($settings, 'combine_css', $current['combine_css']),
            'combine_js' => $this->resolveFlag($settings, 'combine_js', $current['combine_js']),
        ];
        update_option(self::OPTION, $new);
    }

    public function startBuffer(): void
    {
        if ($this->bufferStarted) {
            return;
        }
        ob_start([$this, 'minifyHtml']);
        $this->bufferStarted = true;
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

    public function endBuffer(): void
    {
        if (!$this->bufferStarted) {
            return;
        }
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
        $this->bufferStarted = false;
    }

    public function minifyHtml(string $html): string
    {
        $search = [
            '/\>[\n\r\t ]+/s',
            '/[\n\r\t ]+\</s',
            '/\s{2,}/',
        ];
        $replace = ['>', '<', ' '];
        return preg_replace($search, $replace, $html) ?? $html;
    }

    public function filterScriptTag(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();
        if (is_admin()) {
            return $tag;
        }
        $skip = apply_filters('fp_ps_defer_skip_handles', ['jquery', 'jquery-core', 'jquery-migrate']);
        if (in_array($handle, $skip, true)) {
            return $tag;
        }
        if (!empty($settings['defer_js']) && strpos($tag, ' defer') === false) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }
        if (!empty($settings['async_js']) && strpos($tag, ' async') === false) {
            $tag = str_replace('<script ', '<script async ', $tag);
        }
        return $tag;
    }

    /**
     * @param array<int,string> $hints
     * @param string $relation
     * @return array<int,string>
     */
    public function dnsPrefetch(array $hints, string $relation): array
    {
        if ('dns-prefetch' !== $relation) {
            return $hints;
        }
        $settings = $this->settings();
        return array_unique(array_merge($hints, $settings['dns_prefetch']));
    }

    /**
     * @param array<int,mixed> $hints
     * @param string $relation
     * @return array<int,array<string,mixed>>
     */
    public function preloadResources(array $hints, string $relation): array
    {
        if ('preload' !== $relation) {
            return $hints;
        }

        $settings = $this->settings();

        return $this->mergePreloadHints($hints, $this->formatPreloadHints($settings['preload']));
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function heartbeatSettings(array $settings): array
    {
        $current = $this->settings();
        $settings['interval'] = max(15, (int) $current['heartbeat_admin']);
        return $settings;
    }

    /**
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
     * @param array<int, string> $urls
     * @return array<int, array<string, mixed>>
     */
    private function formatPreloadHints(array $urls): array
    {
        $formatted = [];

        foreach ($urls as $url) {
            if (!is_string($url) || $url === '') {
                continue;
            }

            $formatted[] = [
                'href' => $url,
                'as' => $this->guessPreloadType($url),
            ];
        }

        return $formatted;
    }

    /**
     * @param array<int, mixed> $existing
     * @param array<int, array<string, mixed>> $additional
     * @return array<int, array<string, mixed>>
     */
    private function mergePreloadHints(array $existing, array $additional): array
    {
        $merged = [];
        $seen = [];

        foreach (array_merge($existing, $additional) as $hint) {
            if (is_array($hint) && isset($hint['href'])) {
                $href = (string) $hint['href'];
                if ($href === '') {
                    continue;
                }

                $as = isset($hint['as']) && is_string($hint['as']) && $hint['as'] !== ''
                    ? strtolower($hint['as'])
                    : $this->guessPreloadType($href);

                $key = strtolower($href) . '|' . $as;
                $extras = array_diff_key($hint, ['href' => true, 'as' => true]);
                $entry = ['href' => $href, 'as' => $as] + $extras;
                if (isset($seen[$key])) {
                    $index = $seen[$key];
                    $currentExtras = array_diff_key($merged[$index], ['href' => true, 'as' => true]);
                    if (!empty($extras) && empty($currentExtras)) {
                        $merged[$index] = $entry;
                    }
                    continue;
                }
                $seen[$key] = count($merged);

                $merged[] = $entry;
                continue;
            }

            if (is_string($hint) && $hint !== '') {
                $href = $hint;
                $as = $this->guessPreloadType($href);
                $key = strtolower($href) . '|' . $as;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = count($merged);

                $merged[] = ['href' => $href, 'as' => $as];
            }
        }

        return $merged;
    }

    private function guessPreloadType(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'css':
                return 'style';
            case 'js':
            case 'mjs':
            case 'cjs':
                return 'script';
            case 'woff':
            case 'woff2':
            case 'ttf':
            case 'otf':
                return 'font';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
            case 'avif':
            case 'svg':
                return 'image';
            case 'mp4':
            case 'webm':
            case 'mov':
                return 'video';
            case 'mp3':
            case 'ogg':
            case 'wav':
                return 'audio';
            default:
                return 'fetch';
        }
    }

    private function disableEmojis(): void
    {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
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
}
