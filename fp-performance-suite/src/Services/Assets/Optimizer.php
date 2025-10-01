<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Semaphore;
use function __;
use function array_column;
use function array_key_exists;
use function array_flip;
use function esc_url_raw;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function filesize;
use function filter_var;
use function function_exists;
use function home_url;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_readable;
use function is_string;
use function ltrim;
use function md5;
use function md5_file;
use function count;
use function parse_url;
use function pathinfo;
use function preg_split;
use function rtrim;
use function array_unique;
use function array_values;
use function strpos;
use function strlen;
use function substr;
use function strtok;
use function sprintf;
use function strtolower;
use function trim;
use function wp_mkdir_p;
use function wp_parse_url;
use function wp_upload_dir;
use function trailingslashit;
use const PHP_URL_SCHEME;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const PATHINFO_EXTENSION;
use const LOCK_EX;

class Optimizer
{
    private const OPTION = 'fp_ps_assets';
    private const COMBINED_STYLE_HANDLE = 'fp-ps-combined-styles';
    private const COMBINED_SCRIPT_HANDLE = 'fp-ps-combined-scripts';
    private const COMBINED_FOOTER_SCRIPT_HANDLE = 'fp-ps-combined-scripts-footer';

    private Semaphore $semaphore;
    private bool $bufferStarted = false;
    private bool $stylesCombined = false;
    /** @var array{head:bool,footer:bool} */
    private array $scriptsCombined = ['head' => false, 'footer' => false];

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
            if (!empty($settings['combine_css']) || !empty($settings['combine_js'])) {
                add_action('wp_enqueue_scripts', [$this, 'applyCombination'], PHP_INT_MAX);
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

    public function applyCombination(): void
    {
        if (is_admin()) {
            return;
        }

        $settings = $this->settings();

        if (!empty($settings['combine_css'])) {
            $this->combineStyles();
        }

        if (!empty($settings['combine_js'])) {
            $this->combineScripts(false);
            $this->combineScripts(true);
        }
    }

    private function combineStyles(): void
    {
        if ($this->stylesCombined) {
            return;
        }

        if (!function_exists('wp_register_style') || !function_exists('wp_enqueue_style') || !function_exists('wp_dequeue_style')) {
            return;
        }

        global $wp_styles;

        if (!($wp_styles instanceof \WP_Styles)) {
            return;
        }

        $result = $this->combineDependencyGroup($wp_styles, 'css', null);

        if (null === $result) {
            return;
        }

        wp_register_style(self::COMBINED_STYLE_HANDLE, $result['url'], [], null);
        wp_enqueue_style(self::COMBINED_STYLE_HANDLE);

        $this->replaceDependencies($wp_styles, $result['handles'], self::COMBINED_STYLE_HANDLE);

        foreach ($result['handles'] as $handle) {
            wp_dequeue_style($handle);
        }

        $this->stylesCombined = true;
    }

    private function combineScripts(bool $footer): void
    {
        $key = $footer ? 'footer' : 'head';

        if ($this->scriptsCombined[$key]) {
            return;
        }

        if (!function_exists('wp_register_script') || !function_exists('wp_enqueue_script') || !function_exists('wp_dequeue_script')) {
            return;
        }

        global $wp_scripts;

        if (!($wp_scripts instanceof \WP_Scripts)) {
            return;
        }

        $result = $this->combineDependencyGroup($wp_scripts, 'js', $footer ? 1 : 0);

        if (null === $result) {
            return;
        }

        $handle = $footer ? self::COMBINED_FOOTER_SCRIPT_HANDLE : self::COMBINED_SCRIPT_HANDLE;
        wp_register_script($handle, $result['url'], [], null, $footer);
        wp_enqueue_script($handle);

        $this->replaceDependencies($wp_scripts, $result['handles'], $handle);

        foreach ($result['handles'] as $handleToRemove) {
            wp_dequeue_script($handleToRemove);
        }

        $this->scriptsCombined[$key] = true;
    }

    /**
     * @param \WP_Dependencies $collection
     * @return array{handles:array<int,string>,url:string}|null
     */
    private function combineDependencyGroup($collection, string $type, ?int $group): ?array
    {
        if (!is_object($collection) || empty($collection->queue) || empty($collection->registered)) {
            return null;
        }

        $queue = is_array($collection->queue) ? $collection->queue : (array) $collection->queue;
        if (empty($queue)) {
            return null;
        }

        $positions = [];
        foreach (array_values($queue) as $index => $handle) {
            if (!is_string($handle) || '' === $handle) {
                continue;
            }
            if (!isset($positions[$handle])) {
                $positions[$handle] = $index;
            }
        }

        $candidates = [];
        foreach ($queue as $handle) {
            if (!is_string($handle) || '' === $handle) {
                continue;
            }
            if (!isset($collection->registered[$handle])) {
                continue;
            }

            $item = $collection->registered[$handle];

            if (!is_object($item)) {
                continue;
            }

            if (!$this->matchesGroup($item, $type, $group)) {
                continue;
            }

            if (!$this->isDependencyCombinable($item)) {
                continue;
            }

            $source = $this->resolveDependencySource($collection, $item);

            if (null === $source) {
                continue;
            }

            $depsProperty = $item->deps ?? [];
            $deps = is_array($depsProperty) ? $depsProperty : (array) $depsProperty;
            $deps = array_values(array_filter($deps, static function ($dep) {
                return is_string($dep) && '' !== trim($dep);
            }));

            $candidates[$handle] = [
                'handle' => $handle,
                'path' => $source['path'],
                'url' => $source['url'],
                'deps' => $deps,
            ];
        }

        if (count($candidates) < 2) {
            return null;
        }

        $queueLookup = [];
        foreach ($queue as $queuedHandle) {
            if (is_string($queuedHandle) && '' !== $queuedHandle) {
                $queueLookup[$queuedHandle] = true;
            }
        }

        do {
            $changed = false;
            foreach ($candidates as $handle => $data) {
                foreach ($data['deps'] as $dependency) {
                    if (!isset($queueLookup[$dependency])) {
                        continue;
                    }
                    if (!isset($candidates[$dependency])) {
                        unset($candidates[$handle]);
                        $changed = true;
                        continue 3;
                    }
                }
            }
        } while ($changed);

        if (count($candidates) < 2) {
            return null;
        }

        $indegree = [];
        $edges = [];
        foreach ($candidates as $handle => $data) {
            $indegree[$handle] = 0;
        }

        foreach ($candidates as $handle => $data) {
            foreach ($data['deps'] as $dependency) {
                if (!isset($candidates[$dependency])) {
                    continue;
                }
                $edges[$dependency][] = $handle;
                $indegree[$handle]++;
            }
        }

        $available = [];
        foreach ($indegree as $handle => $count) {
            if (0 === $count) {
                $available[] = $handle;
            }
        }

        if (empty($available)) {
            return null;
        }

        $sortByPosition = static function (string $a, string $b) use ($positions): int {
            $posA = $positions[$a] ?? PHP_INT_MAX;
            $posB = $positions[$b] ?? PHP_INT_MAX;
            return $posA <=> $posB;
        };

        usort($available, $sortByPosition);

        $ordered = [];
        while (!empty($available)) {
            $current = array_shift($available);
            $ordered[] = $current;

            foreach ($edges[$current] ?? [] as $next) {
                $indegree[$next]--;
                if (0 === $indegree[$next]) {
                    $available[] = $next;
                }
            }

            if (!empty($available)) {
                usort($available, $sortByPosition);
            }
        }

        if (count($ordered) !== count($candidates)) {
            return null;
        }

        $files = [];
        foreach ($ordered as $handle) {
            $files[] = [
                'handle' => $handle,
                'path' => $candidates[$handle]['path'],
                'url' => $candidates[$handle]['url'],
            ];
        }

        if (count($files) < 2) {
            return null;
        }

        return $this->writeCombinedAsset($files, $type);
    }

    private function matchesGroup(object $item, string $type, ?int $group): bool
    {
        if ('js' !== $type) {
            return true;
        }

        $itemGroup = 0;

        if (isset($item->extra) && is_array($item->extra) && isset($item->extra['group'])) {
            $itemGroup = (int) $item->extra['group'];
        }

        if (null === $group) {
            return 0 === $itemGroup;
        }

        return $itemGroup === $group;
    }

    private function isDependencyCombinable(object $item): bool
    {
        if (!isset($item->src) || !is_string($item->src) || '' === trim($item->src)) {
            return false;
        }

        $extra = [];
        if (isset($item->extra) && is_array($item->extra)) {
            $extra = $item->extra;
        }

        if (!empty($extra['conditional']) || !empty($extra['data']) || !empty($extra['before']) || !empty($extra['after'])) {
            return false;
        }

        return true;
    }

    /**
     * @param \WP_Dependencies $collection
     * @return array{path:string,url:string}|null
     */
    private function resolveDependencySource($collection, object $item): ?array
    {
        $src = is_string($item->src ?? null) ? trim($item->src) : '';

        if ('' === $src) {
            return null;
        }

        $url = $src;

        if (0 === strpos($url, '//')) {
            $scheme = wp_parse_url(home_url(), PHP_URL_SCHEME) ?: 'https';
            $url = $scheme . ':' . $url;
        } elseif (false === strpos($url, '://')) {
            $base = is_string($collection->base_url ?? null) ? $collection->base_url : '';
            if ('' !== $base) {
                $url = rtrim($base, '/') . '/' . ltrim($url, '/');
            } else {
                $url = home_url('/' . ltrim($url, '/'));
            }
        }

        $sanitized = strtok($url, '?') ?: $url;
        $parsed = wp_parse_url($sanitized);

        if (!is_array($parsed) || empty($parsed['path'])) {
            return null;
        }

        $home = wp_parse_url(home_url());
        if (!empty($parsed['host']) && !empty($home['host'])) {
            if (strtolower($parsed['host']) !== strtolower((string) $home['host'])) {
                return null;
            }
        }

        $path = $parsed['path'];

        $home = wp_parse_url(home_url());
        if (is_array($home) && !empty($home['path'])) {
            $homePath = rtrim((string) $home['path'], '/');
            if ('' !== $homePath) {
                if ($path === $homePath) {
                    $path = '';
                } elseif (0 === strpos($path, $homePath . '/')) {
                    $path = substr($path, strlen($homePath));
                }
            }
        }

        if ('' === $path) {
            return null;
        }

        $path = ABSPATH . ltrim($path, '/');

        if (!file_exists($path) || !is_readable($path)) {
            return null;
        }

        return [
            'path' => $path,
            'url' => $sanitized,
        ];
    }

    /**
     * @param array<int,array{handle:string,path:string,url:string}> $files
     * @return array{handles:array<int,string>,url:string}|null
     */
    private function writeCombinedAsset(array $files, string $type): ?array
    {
        if (!function_exists('wp_upload_dir') || !function_exists('wp_mkdir_p')) {
            return null;
        }

        $uploads = wp_upload_dir();

        if (!is_array($uploads) || empty($uploads['basedir']) || empty($uploads['baseurl'])) {
            return null;
        }

        $targetDir = trailingslashit($uploads['basedir']) . 'fp-performance-suite';

        if (!wp_mkdir_p($targetDir)) {
            return null;
        }

        $hashParts = [];
        $contents = '';

        foreach ($files as $file) {
            $mtime = @filemtime($file['path']);
            $size = @filesize($file['path']);
            $hashParts[] = $file['url'] . '|' . ($mtime ?: 0) . '|' . ($size ?: 0);
            $asset = file_get_contents($file['path']);

            if (false === $asset) {
                return null;
            }

            $contents .= '/* ' . $file['handle'] . " */\n" . $asset . "\n";
        }

        $hash = md5(implode('|', $hashParts));
        $extension = 'css' === $type ? 'css' : 'js';
        $filename = sprintf('combined-%s-%s.%s', $type, $hash, $extension);
        $fullPath = trailingslashit($targetDir) . $filename;

        $handles = array_column($files, 'handle');
        $url = trailingslashit($uploads['baseurl']) . 'fp-performance-suite/' . $filename;
        $contentsHash = md5($contents);

        if (file_exists($fullPath)) {
            $existingHash = md5_file($fullPath);

            if (is_string($existingHash) && $existingHash === $contentsHash) {
                return [
                    'handles' => $handles,
                    'url' => $url,
                ];
            }
        }

        $bytesWritten = file_put_contents($fullPath, $contents, LOCK_EX);

        if (false === $bytesWritten) {
            return null;
        }

        return [
            'handles' => $handles,
            'url' => $url,
        ];
    }

    /**
     * @param \WP_Dependencies $collection
     * @param array<int, string> $replacedHandles
     */
    private function replaceDependencies($collection, array $replacedHandles, string $replacement): void
    {
        if (!is_object($collection) || empty($collection->registered) || empty($replacedHandles)) {
            return;
        }

        $lookup = array_flip($replacedHandles);

        foreach ($collection->registered as $handle => $item) {
            if (!is_object($item) || isset($lookup[$handle])) {
                continue;
            }

            $depsProperty = $item->deps ?? [];
            $deps = is_array($depsProperty) ? $depsProperty : (array) $depsProperty;
            $updated = false;

            foreach ($deps as &$dependency) {
                if (isset($lookup[$dependency]) && $dependency !== $replacement) {
                    $dependency = $replacement;
                    $updated = true;
                }
            }

            unset($dependency);

            if ($updated) {
                $item->deps = array_values(array_unique($deps));
            }
        }
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
}
