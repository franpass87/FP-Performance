<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Contracts\CacheInterface;
use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;

use function headers_list;
use function is_user_logged_in;
use function wp_cache_get_cookies_values;
use function wp_mkdir_p;

class PageCache implements CacheInterface
{
    private const OPTION = 'fp_ps_page_cache';
    private const DEFAULT_TTL = 3600;
    private Fs $fs;
    private Env $env;
    private bool $started = false;
    private int $bufferLevel = 0;

    public function __construct(Fs $fs, Env $env)
    {
        $this->fs = $fs;
        $this->env = $env;
    }

    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        add_action('template_redirect', [$this, 'maybeServeCache'], 0);
        add_action('template_redirect', [$this, 'startBuffering'], PHP_INT_MAX);
        add_action('shutdown', [$this, 'saveBuffer'], PHP_INT_MAX);

        // Register automatic cache purge hooks
        $this->registerPurgeHooks();
    }

    public function isEnabled(): bool
    {
        $settings = $this->settings();
        return !empty($settings['enabled']);
    }

    /**
     * @return array{enabled:bool,ttl:int}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'ttl' => self::DEFAULT_TTL,
        ];
        $options = wp_parse_args(get_option(self::OPTION, []), $defaults);
        $ttl = isset($options['ttl']) ? (int) $options['ttl'] : $defaults['ttl'];
        if ($ttl > 0 && $ttl < 60) {
            $ttl = 60;
        }
        $enabled = !empty($options['enabled']) && $ttl > 0;

        $normalizedTtl = $ttl > 0 ? $ttl : 0;

        return [
            'enabled' => $enabled,
            'ttl' => $normalizedTtl,
        ];
    }

    public function update(array $settings): void
    {
        $current = $this->settings();

        $ttl = array_key_exists('ttl', $settings)
            ? max(0, (int) $settings['ttl'])
            : $current['ttl'];

        $enabledFlag = array_key_exists('enabled', $settings)
            ? !empty($settings['enabled'])
            : $current['enabled'];

        if ($enabledFlag && $ttl <= 0) {
            $ttl = $current['ttl'] > 0 ? $current['ttl'] : self::DEFAULT_TTL;
        }

        if ($ttl > 0 && $ttl < 60) {
            $ttl = 60;
        }

        $enabled = $enabledFlag && $ttl > 0;

        update_option(self::OPTION, [
            'enabled' => $enabled,
            'ttl' => $ttl > 0 ? $ttl : 0,
        ]);
    }

    public function cacheDir(): string
    {
        $dir = WP_CONTENT_DIR . '/cache/fp-performance-suite';
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    public function clear(): void
    {
        try {
            $this->fs->delete($this->cacheDir());
            Logger::info('Page cache cleared successfully');
            do_action('fp_ps_cache_cleared');
        } catch (\Throwable $e) {
            Logger::error('Failed to clear page cache', $e);
        }
    }

    /**
     * Register hooks for automatic cache purging on content updates
     */
    private function registerPurgeHooks(): void
    {
        // Post updates
        add_action('save_post', [$this, 'onContentUpdate'], 10, 3);
        add_action('deleted_post', [$this, 'onContentUpdate']);
        add_action('trashed_post', [$this, 'onContentUpdate']);
        add_action('wp_trash_post', [$this, 'onContentUpdate']);

        // Comment updates
        add_action('comment_post', [$this, 'onCommentUpdate'], 10, 2);
        add_action('edit_comment', [$this, 'onCommentUpdate']);
        add_action('deleted_comment', [$this, 'onCommentUpdate']);
        add_action('trashed_comment', [$this, 'onCommentUpdate']);
        add_action('spam_comment', [$this, 'onCommentUpdate']);
        add_action('unspam_comment', [$this, 'onCommentUpdate']);

        // Theme/customizer changes
        add_action('switch_theme', [$this, 'onThemeChange']);
        add_action('customize_save_after', [$this, 'onThemeChange']);

        // Widget updates
        add_action('update_option_sidebars_widgets', [$this, 'onWidgetUpdate']);

        // Menu updates
        add_action('wp_update_nav_menu', [$this, 'onMenuUpdate']);

        // Allow disabling auto-purge via filter
        if (apply_filters('fp_ps_enable_auto_purge', true)) {
            Logger::debug('Auto-purge hooks registered');
        }
    }

    /**
     * Handle content updates (posts, pages, etc.)
     *
     * @param int $postId Post ID
     */
    public function onContentUpdate($postId): void
    {
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (wp_is_post_revision($postId)) {
            return;
        }

        // Only purge for published posts
        $post = get_post($postId);
        if (!$post || $post->post_status !== 'publish') {
            return;
        }

        Logger::info('Auto-purge triggered by post update', [
            'post_id' => $postId,
            'post_type' => $post->post_type ?? 'unknown',
        ]);

        $this->clear();
        do_action('fp_ps_cache_auto_purged', 'post_update', $postId);
    }

    /**
     * Handle comment updates
     *
     * @param int $commentId Comment ID
     */
    public function onCommentUpdate($commentId): void
    {
        $comment = get_comment($commentId);
        if (!$comment) {
            return;
        }

        // Only purge for approved comments on published posts
        if ($comment->comment_approved !== '1') {
            return;
        }

        Logger::info('Auto-purge triggered by comment update', [
            'comment_id' => $commentId,
            'post_id' => $comment->comment_post_ID,
        ]);

        $this->clear();
        do_action('fp_ps_cache_auto_purged', 'comment_update', $commentId);
    }

    /**
     * Handle theme changes
     */
    public function onThemeChange(): void
    {
        Logger::info('Auto-purge triggered by theme change');
        $this->clear();
        do_action('fp_ps_cache_auto_purged', 'theme_change', null);
    }

    /**
     * Handle widget updates
     */
    public function onWidgetUpdate(): void
    {
        Logger::info('Auto-purge triggered by widget update');
        $this->clear();
        do_action('fp_ps_cache_auto_purged', 'widget_update', null);
    }

    /**
     * Handle menu updates
     */
    public function onMenuUpdate(): void
    {
        Logger::info('Auto-purge triggered by menu update');
        $this->clear();
        do_action('fp_ps_cache_auto_purged', 'menu_update', null);
    }

    public function maybeServeCache(): void
    {
        if (!$this->isCacheableRequest()) {
            return;
        }

        $file = $this->cacheFile();
        if (!file_exists($file)) {
            return;
        }

        if ($this->hasPrivateCookies()) {
            return;
        }

        $meta = @json_decode((string) @file_get_contents($file . '.meta'), true);
        $ttl = isset($meta['ttl']) ? (int) $meta['ttl'] : self::DEFAULT_TTL;
        if ($ttl <= 0) {
            @unlink($file);
            @unlink($file . '.meta');
            return;
        }
        if ($ttl > 0 && filemtime($file) + $ttl < time()) {
            @unlink($file);
            @unlink($file . '.meta');
            return;
        }

        header('X-FP-Page-Cache: HIT');
        if ($this->isHeadRequest()) {
            $size = @filesize($file);
            if (!headers_sent() && $size !== false) {
                header('Content-Length: ' . $size);
            }
            exit;
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            return;
        }

        echo $contents;
        exit;
    }

    public function startBuffering(): void
    {
        if ($this->isHeadRequest()) {
            return;
        }
        if (!$this->isCacheableRequest()) {
            return;
        }
        if ($this->hasPrivateCookies()) {
            return;
        }

        if ($this->started) {
            return;
        }

        $started = ob_start([$this, 'maybeFilterOutput']);
        if (!$started) {
            $started = ob_start();
        }

        if ($started) {
            $this->started = true;
            $this->bufferLevel = ob_get_level();
        }
    }

    public function maybeFilterOutput(string $buffer): string
    {
        return $buffer;
    }

    public function saveBuffer(): void
    {
        if ($this->isHeadRequest()) {
            $this->finishBuffering();
            return;
        }
        if (!$this->isCacheableRequest()) {
            $this->finishBuffering();
            return;
        }
        if ($this->hasPrivateCookies()) {
            $this->finishBuffering();
            return;
        }
        if (headers_sent()) {
            $this->finishBuffering();
            return;
        }

        if (function_exists('http_response_code') && http_response_code() !== 200) {
            $this->finishBuffering();
            return;
        }
        if (
            (function_exists('is_404') && is_404()) || (function_exists('is_search') && is_search()) ||
            (function_exists('is_feed') && is_feed()) || (function_exists('is_preview') && is_preview())
        ) {
            $this->finishBuffering();
            return;
        }

        if (function_exists('headers_list')) {
            foreach (headers_list() as $header) {
                if (stripos($header, 'set-cookie:') === 0) {
                    $this->finishBuffering();
                    return;
                }
            }
        }

        $buffer = ob_get_contents();
        if ($buffer === false || $buffer === '') {
            $this->finishBuffering();
            return;
        }

        $settings = $this->settings();
        if (empty($settings['enabled']) || $settings['ttl'] <= 0) {
            $this->finishBuffering();
            return;
        }

        $file = $this->cacheFile();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        try {
            $this->fs->putContents($file, $buffer);
            $this->fs->putContents($file . '.meta', wp_json_encode([
                'ttl' => $settings['ttl'],
                'time' => time(),
            ]));
            Logger::debug('Page cache file saved', ['file' => basename($file)]);
        } catch (\Throwable $e) {
            Logger::error('Failed to save page cache file', $e);
        }
        $this->finishBuffering();
    }

    private function isCacheableRequest(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!is_main_query() || is_user_logged_in() || is_admin() || defined('DONOTCACHEPAGE')) {
            return false;
        }

        if ($this->hasPrivateCookies()) {
            return false;
        }

        if (!in_array(strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'), ['GET', 'HEAD'], true)) {
            return false;
        }

        if (!empty($_GET)) {
            return false;
        }

        return true;
    }

    private function cacheFile(): string
    {
        $host = sanitize_key(wp_parse_url(home_url(), PHP_URL_HOST) ?? 'site');
        $path = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/');
        $path = $path === '' ? 'index' : str_replace('/', '-', $path);
        $dir = trailingslashit($this->cacheDir()) . $host;
        wp_mkdir_p($dir);
        return $dir . '/' . $path . '.html';
    }

    public function status(): array
    {
        $dir = $this->cacheDir();
        $count = 0;
        if (is_dir($dir)) {
            try {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
                );
                foreach ($iterator as $fileInfo) {
                    /** @var \SplFileInfo $fileInfo */
                    if ($fileInfo->isFile() && strtolower($fileInfo->getExtension()) === 'html') {
                        $count++;
                    }
                }
            } catch (\Throwable $e) {
                Logger::error('Unable to read cache directory', $e);
                $count = 0;
            }
        }
        return [
            'enabled' => $this->isEnabled(),
            'files' => $count,
        ];
    }

    private function finishBuffering(): void
    {
        if (!$this->started) {
            return;
        }

        $this->started = false;

        if ($this->bufferLevel > 0) {
            while (ob_get_level() >= $this->bufferLevel && ob_get_level() > 0) {
                ob_end_flush();
            }
        } elseif (ob_get_level() > 0) {
            ob_end_flush();
        }

        $this->bufferLevel = 0;
    }

    private function isHeadRequest(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD';
    }

    private function hasPrivateCookies(): bool
    {
        if (function_exists('wp_cache_get_cookies_values') && wp_cache_get_cookies_values() !== '') {
            return true;
        }

        foreach ($_COOKIE as $name => $value) {
            if (is_string($name) && strpos($name, 'comment_author_') === 0) {
                return true;
            }
        }

        return false;
    }
}
