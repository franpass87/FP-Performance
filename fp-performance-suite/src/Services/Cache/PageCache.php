<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Contracts\CacheInterface;
use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Logger;

use function get_author_posts_url;
use function get_object_taxonomies;
use function get_option;
use function get_permalink;
use function get_post;
use function get_post_type_archive_link;
use function get_term_link;
use function get_the_terms;
use function headers_list;
use function home_url;
use function is_user_logged_in;
use function sanitize_key;
use function trailingslashit;
use function update_option;
use function wp_cache_get_cookies_values;
use function wp_is_post_revision;
use function wp_mkdir_p;
use function wp_parse_args;
use function wp_parse_url;

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
     * Purge cache for specific URL
     *
     * @param string $url URL to purge
     * @return bool Success status
     */
    public function purgeUrl(string $url): bool
    {
        try {
            $file = $this->urlToCacheFile($url);
            
            if (!$file) {
                Logger::warning('Cannot purge URL - invalid format', ['url' => $url]);
                return false;
            }

            $deleted = false;

            // Delete main cache file
            if (file_exists($file)) {
                @unlink($file);
                $deleted = true;
            }

            // Delete meta file
            if (file_exists($file . '.meta')) {
                @unlink($file . '.meta');
            }

            if ($deleted) {
                Logger::info('Cache purged for URL', ['url' => $url]);
                do_action('fp_ps_cache_purged_url', $url);
            } else {
                Logger::debug('Cache file does not exist for URL', ['url' => $url]);
            }

            // Return true even if file doesn't exist - the result is the same (no cache for this URL)
            return true;
        } catch (\Throwable $e) {
            Logger::error('Failed to purge URL cache', $e);
            return false;
        }
    }

    /**
     * Purge cache for specific post and related URLs
     *
     * @param int $postId Post ID
     * @return int Number of URLs purged
     */
    public function purgePost(int $postId): int
    {
        $post = get_post($postId);
        if (!$post) {
            return 0;
        }

        $urlsToPurge = [];

        // Post permalink
        $permalink = get_permalink($postId);
        if ($permalink) {
            $urlsToPurge[] = $permalink;
        }

        // Home page (if post is shown on front page)
        if ($post->post_type === 'post' || (get_option('show_on_front') === 'page' && (int) get_option('page_on_front') === $postId)) {
            $urlsToPurge[] = home_url('/');
        }

        // Post type archive
        if ($post->post_type !== 'page') {
            $archiveLink = get_post_type_archive_link($post->post_type);
            if ($archiveLink) {
                $urlsToPurge[] = $archiveLink;
            }
        }

        // Author archive
        if ($post->post_author) {
            $authorLink = get_author_posts_url($post->post_author);
            if ($authorLink) {
                $urlsToPurge[] = $authorLink;
            }
        }

        // Category/tag archives
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($postId, $taxonomy);
            if (is_array($terms)) {
                foreach ($terms as $term) {
                    $termLink = get_term_link($term);
                    if (!is_wp_error($termLink)) {
                        $urlsToPurge[] = $termLink;
                    }
                }
            }
        }

        // Purge all URLs
        $purged = 0;
        foreach (array_unique($urlsToPurge) as $url) {
            if ($this->purgeUrl($url)) {
                $purged++;
            }
        }

        if ($purged > 0) {
            Logger::info('Cache purged for post', [
                'post_id' => $postId,
                'urls_purged' => $purged,
            ]);
            do_action('fp_ps_cache_purged_post', $postId, $purged);
        }

        return $purged;
    }

    /**
     * Purge cache files matching pattern
     *
     * @param string $pattern Pattern to match (e.g., "category-*")
     * @return int Number of files purged
     */
    public function purgePattern(string $pattern): int
    {
        try {
            $dir = $this->cacheDir();
            $host = sanitize_key(wp_parse_url(home_url(), PHP_URL_HOST) ?? 'site');
            $hostDir = trailingslashit($dir) . $host;

            if (!is_dir($hostDir)) {
                return 0;
            }

            $files = glob($hostDir . '/' . $pattern . '.html');
            if ($files === false) {
                return 0;
            }

            $purged = 0;
            foreach ($files as $file) {
                @unlink($file);
                @unlink($file . '.meta');
                $purged++;
            }

            if ($purged > 0) {
                Logger::info('Cache purged by pattern', [
                    'pattern' => $pattern,
                    'files_purged' => $purged,
                ]);
                do_action('fp_ps_cache_purged_pattern', $pattern, $purged);
            }

            return $purged;
        } catch (\Throwable $e) {
            Logger::error('Failed to purge cache by pattern', $e);
            return 0;
        }
    }

    /**
     * Convert URL to cache file path
     *
     * @param string $url URL to convert
     * @return string|null Cache file path or null if invalid
     */
    private function urlToCacheFile(string $url): ?string
    {
        $parsed = wp_parse_url($url);
        if (!$parsed || empty($parsed['host'])) {
            return null;
        }

        $host = sanitize_key($parsed['host']);
        $path = trim($parsed['path'] ?? '/', '/');
        $path = $path === '' ? 'index' : str_replace('/', '-', $path);

        $dir = trailingslashit($this->cacheDir()) . $host;
        return $dir . '/' . $path . '.html';
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

        // Use selective purge for better performance
        $purged = $this->purgePost($postId);

        Logger::info('Auto-purge triggered by post update', [
            'post_id' => $postId,
            'post_type' => $post->post_type ?? 'unknown',
            'urls_purged' => $purged,
        ]);

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

        // Purge the post and related pages
        $purged = $this->purgePost($comment->comment_post_ID);

        Logger::info('Auto-purge triggered by comment update', [
            'comment_id' => $commentId,
            'post_id' => $comment->comment_post_ID,
            'urls_purged' => $purged,
        ]);

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
        
        $fileTime = @filemtime($file);
        if ($ttl > 0 && $fileTime !== false && $fileTime + $ttl < time()) {
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

        if (is_user_logged_in() || is_admin() || defined('DONOTCACHEPAGE')) {
            return false;
        }

        // Exclude REST API requests
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return false;
        }

        // Exclude AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return false;
        }

        // Exclude WP-Cron requests
        if (defined('DOING_CRON') && DOING_CRON) {
            return false;
        }

        // Exclude special WordPress pages
        if (function_exists('is_preview') && is_preview()) {
            return false;
        }

        if (function_exists('is_customize_preview') && is_customize_preview()) {
            return false;
        }

        if (function_exists('is_feed') && is_feed()) {
            return false;
        }

        if (function_exists('is_search') && is_search()) {
            return false;
        }

        if (function_exists('is_404') && is_404()) {
            return false;
        }

        // Check URL patterns
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            
            // Exclude REST API endpoints
            $restPrefix = rest_get_url_prefix();
            if (strpos($requestUri, '/' . $restPrefix . '/') !== false || strpos($requestUri, '/' . $restPrefix) !== false) {
                return false;
            }

            // Exclude critical WordPress files
            $excludeFiles = [
                '/xmlrpc.php',
                '/wp-cron.php',
                '/wp-login.php',
                '/wp-signup.php',
                '/wp-trackback.php',
                '/wp-comments-post.php',
                '/wp-sitemap',      // WordPress 5.5+ core sitemaps
                'sitemap.xml',      // SEO plugin sitemaps
                'sitemap_index.xml',
                '/feed/',
                '/rss/',
                '/atom/',
                'robots.txt',
            ];

            // WooCommerce critical pages
            $woocommercePages = [
                '/cart',
                '/checkout',
                '/my-account',
                '/add-to-cart',
                '/remove-from-cart',
                '/wc-ajax',
                '/wc-api',
                '?add-to-cart=',
                '?remove_item=',
            ];

            // Easy Digital Downloads
            $eddPages = [
                '/edd-ajax',
                '/edd-api',
                '/purchase',
                '/downloads',
                '?edd_action=',
            ];

            // MemberPress
            $memberpressPages = [
                '/membership',
                '/register',
                '/mepr',
                '/account',
            ];

            // LearnDash
            $learnDashPages = [
                '/courses/',
                '/lessons/',
                '/topic/',
                '/quiz/',
            ];

            // bbPress
            $bbPressPages = [
                '/forums/',
                '/forum/',
                '/topic/',
                '/reply/',
            ];

            // BuddyPress
            $buddyPressPages = [
                '/members/',
                '/groups/',
                '/activity/',
                '/profile/',
            ];

            // WP Rocket & LMS compatibility
            $otherPages = [
                '/lms/',
                '/course/',
                '/lesson/',
            ];

            // CRITICAL: Password reset, email verification, webhooks
            $securityPages = [
                '?action=lostpassword',
                '?action=rp',
                '?action=resetpass',
                '?verify=',
                '?confirmation=',
                '?activation=',
                '?token=',
                '/verify-email',
                '/two-factor',
                '/2fa',
            ];

            // Payment webhooks
            $webhookPages = [
                '/wc-api/',
                '/paypal-ipn',
                '/stripe-webhook',
                '/payment-callback',
                '/payment-return',
                '/payment-webhook',
            ];

            // WooCommerce Advanced
            $wooAdvanced = [
                '/my-account/subscriptions',
                '/my-account/bookings',
                '/my-account/memberships',
                '/wishlist',
                '/compare',
            ];

            // Multivendor
            $multivendor = [
                '/dashboard',
                '/vendor-dashboard',
                '/wcfm',
            ];

            // Merge all plugin patterns
            $excludePluginPages = array_merge(
                $excludeFiles,
                $woocommercePages,
                $eddPages,
                $memberpressPages,
                $learnDashPages,
                $bbPressPages,
                $buddyPressPages,
                $otherPages,
                $securityPages,
                $webhookPages,
                $wooAdvanced,
                $multivendor
            );

            foreach ($excludePluginPages as $pattern) {
                if (strpos($requestUri, $pattern) !== false) {
                    return false;
                }
            }
        }

        // WooCommerce conditional tags (more reliable)
        if (function_exists('is_cart') && is_cart()) {
            return false;
        }

        if (function_exists('is_checkout') && is_checkout()) {
            return false;
        }

        if (function_exists('is_account_page') && is_account_page()) {
            return false;
        }

        if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
            return false;
        }

        if ($this->hasPrivateCookies()) {
            return false;
        }

        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET';
        if (!in_array(strtoupper($requestMethod), ['GET', 'HEAD'], true)) {
            return false;
        }

        // Smart query parameter check (whitelist tracking params)
        if (!empty($_GET)) {
            // Tracking params are safe to cache
            $safeTrackingParams = [
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
                'fbclid', 'gclid', 'msclkid', 'mc_cid', 'mc_eid',
                '_ga', '_gl', 'ref', 'referrer',
                'fb_action_ids', 'fb_action_types', 'fb_source',
                'igshid', 'share',
            ];

            // Dynamic params must NOT be cached
            $dynamicParams = [
                // Search & Filter
                's', 'search', 'query', 'filter', 'orderby', 'order',
                'paged', 'page', 'offset', 'per_page',
                'min_price', 'max_price', 'rating_filter',
                // E-commerce actions
                'add-to-cart', 'remove_item', 'added-to-cart',
                'add-to-wishlist', 'remove-from-wishlist', 'wishlist_id',
                'add_to_compare',
                // Authentication & Security
                'action', 'verify', 'token', 'confirmation', 'activation',
                'reset', 'rp', 'key', 'otp', 'code',
                // Payment
                'paypal', 'stripe', 'payment_method', 'order_id', 'transaction_id',
                // Subscriptions & Bookings
                'cancel_subscription', 'resubscribe', 'subscription_id',
                'booking_id', 'cancel_booking',
                // Forms
                'form_id', 'form_submit', 'wpcf7', 'gf_page', 'subscribe', 'newsletter',
                // Multivendor
                'dokan_pro', 'wcfm-', 'wc_vendors',
                // LMS
                'quiz_id', 'question', 'attempt', 'cert_id',
                // Localization (user-specific)
                'currency', 'lang', 'language',
                // Redirects
                'redirect_to', 'return_url',
            ];

            $hasDynamicParams = false;
            foreach ($_GET as $key => $value) {
                // Skip safe tracking params
                if (in_array($key, $safeTrackingParams, true)) {
                    continue;
                }

                // Check if it's a dynamic param
                foreach ($dynamicParams as $dynamicParam) {
                    if ($key === $dynamicParam || strpos($key, $dynamicParam) === 0) {
                        $hasDynamicParams = true;
                        break 2;
                    }
                }

                // If unknown param (not safe, not in dynamic list), exclude it to be safe
                $hasDynamicParams = true;
                break;
            }

            if ($hasDynamicParams) {
                return false;
            }
        }

        return true;
    }

    private function cacheFile(): string
    {
        $host = sanitize_key(wp_parse_url(home_url(), PHP_URL_HOST) ?? 'site');
        $requestUri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
        $path = trim(parse_url($requestUri, PHP_URL_PATH) ?? '/', '/');
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
        $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : 'GET';
        return strtoupper($requestMethod) === 'HEAD';
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
