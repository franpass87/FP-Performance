<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Fs;

class PageCache
{
    private const OPTION = 'fp_ps_page_cache';
    private Fs $fs;
    private Env $env;
    private bool $started = false;

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
    }

    public function isEnabled(): bool
    {
        $settings = get_option(self::OPTION, ['enabled' => false]);
        return !empty($settings['enabled']);
    }

    /**
     * @return array{enabled:bool,ttl:int}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'ttl' => 3600,
        ];
        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    public function update(array $settings): void
    {
        update_option(self::OPTION, [
            'enabled' => !empty($settings['enabled']),
            'ttl' => isset($settings['ttl']) ? (int) $settings['ttl'] : 3600,
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
        $this->fs->delete($this->cacheDir());
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

        $meta = @json_decode((string) @file_get_contents($file . '.meta'), true);
        $ttl = isset($meta['ttl']) ? (int) $meta['ttl'] : 3600;
        if ($ttl > 0 && filemtime($file) + $ttl < time()) {
            unlink($file);
            @unlink($file . '.meta');
            return;
        }

        $contents = file_get_contents($file);
        if ($contents === false) {
            return;
        }

        header('X-FP-Page-Cache: HIT');
        echo $contents;
        exit;
    }

    public function startBuffering(): void
    {
        if (!$this->isCacheableRequest()) {
            return;
        }

        if ($this->started) {
            return;
        }

        if (!ob_start([$this, 'maybeFilterOutput'])) {
            ob_start();
        }
        $this->started = true;
    }

    public function maybeFilterOutput(string $buffer): string
    {
        return $buffer;
    }

    public function saveBuffer(): void
    {
        if (!$this->isCacheableRequest()) {
            return;
        }
        if (headers_sent()) {
            return;
        }

        $buffer = ob_get_contents();
        if ($buffer === false || $buffer === '') {
            return;
        }

        $file = $this->cacheFile();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            wp_mkdir_p($dir);
        }

        $this->fs->putContents($file, $buffer);
        $this->fs->putContents($file . '.meta', wp_json_encode([
            'ttl' => $this->settings()['ttl'],
            'time' => time(),
        ]));
        $this->started = false;
        if (ob_get_level() > 0) {
            ob_end_flush();
        }
    }

    private function isCacheableRequest(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if (!is_main_query() || is_user_logged_in() || is_admin() || defined('DONOTCACHEPAGE')) {
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
        $files = glob($dir . '/*.html');
        $count = is_array($files) ? count($files) : 0;
        return [
            'enabled' => $this->isEnabled(),
            'files' => $count,
        ];
    }
}
