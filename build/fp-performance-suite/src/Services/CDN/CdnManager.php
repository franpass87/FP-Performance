<?php

namespace FP\PerfSuite\Services\CDN;

use FP\PerfSuite\Utils\Logger;

/**
 * CDN Integration Manager
 *
 * Rewrites asset URLs to use CDN and provides purge functionality
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CdnManager
{
    private const OPTION = 'fp_ps_cdn';

    /**
     * Register CDN hooks
     */
    public function register(): void
    {
        $settings = $this->settings();

        if (empty($settings['enabled'])) {
            return;
        }

        if (!is_admin()) {
            add_filter('wp_get_attachment_url', [$this, 'rewriteUrl'], 10, 2);
            add_filter('wp_calculate_image_srcset', [$this, 'rewriteSrcset'], 10, 5);
            add_filter('script_loader_src', [$this, 'rewriteUrl'], 10, 2);
            add_filter('style_loader_src', [$this, 'rewriteUrl'], 10, 2);

            if (!empty($settings['rewrite_content'])) {
                add_filter('the_content', [$this, 'rewriteContentUrls'], 999);
            }
        }

        // Purge hooks
        add_action('fp_ps_cache_cleared', [$this, 'purgeAll']);
        add_action('fp_ps_webp_converted', [$this, 'purgeFile']);
    }

    /**
     * Get CDN settings
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'url' => '',
            'domains' => [], // Multiple CDN domains for domain sharding
            'excluded_extensions' => ['php'],
            'rewrite_content' => true,
            'provider' => 'custom', // custom, cloudflare, bunnycdn, stackpath
            'api_key' => '',
            'zone_id' => '',
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update CDN settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $new = [
            'enabled' => !empty($settings['enabled']),
            'url' => esc_url_raw($settings['url'] ?? $current['url']),
            'domains' => $this->sanitizeDomains($settings['domains'] ?? $current['domains']),
            'excluded_extensions' => $this->sanitizeExtensions($settings['excluded_extensions'] ?? $current['excluded_extensions']),
            'rewrite_content' => !empty($settings['rewrite_content']),
            'provider' => sanitize_key($settings['provider'] ?? $current['provider']),
            'api_key' => sanitize_text_field($settings['api_key'] ?? $current['api_key']),
            'zone_id' => sanitize_text_field($settings['zone_id'] ?? $current['zone_id']),
        ];

        update_option(self::OPTION, $new);

        Logger::info('CDN settings updated', [
            'enabled' => $new['enabled'],
            'provider' => $new['provider'],
        ]);

        do_action('fp_ps_cdn_settings_updated', $new);
    }

    /**
     * Rewrite URL to CDN
     */
    public function rewriteUrl(string $url, $id = null): string
    {
        $settings = $this->settings();

        if (empty($settings['enabled']) || empty($settings['url'])) {
            return $url;
        }

        // Skip if already CDN URL
        if (strpos($url, $settings['url']) !== false) {
            return $url;
        }

        // Check excluded extensions
        $extension = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        if (in_array($extension, $settings['excluded_extensions'], true)) {
            return $url;
        }

        // Only rewrite local URLs
        $siteUrl = site_url();
        if (strpos($url, $siteUrl) !== 0) {
            return $url;
        }

        // Get CDN domain (support domain sharding)
        $cdnUrl = $this->selectCdnDomain($url, $settings);

        // Replace site URL with CDN URL
        $rewritten = str_replace($siteUrl, rtrim($cdnUrl, '/'), $url);

        Logger::debug('URL rewritten to CDN', [
            'original' => $url,
            'cdn' => $rewritten,
        ]);

        return apply_filters('fp_ps_cdn_url', $rewritten, $url);
    }

    /**
     * Rewrite srcset URLs to CDN
     */
    public function rewriteSrcset(array $sources, $size_array, $image_src, $image_meta, $attachment_id): array
    {
        foreach ($sources as $width => $source) {
            $sources[$width]['url'] = $this->rewriteUrl($source['url'], $attachment_id);
        }

        return $sources;
    }

    /**
     * Rewrite URLs in post content
     */
    public function rewriteContentUrls(string $content): string
    {
        $settings = $this->settings();

        if (empty($settings['enabled']) || empty($settings['url'])) {
            return $content;
        }

        $siteUrl = site_url();
        $uploadDir = wp_upload_dir();
        
        if (empty($uploadDir['baseurl'])) {
            return $content;
        }
        
        $uploadsUrl = $uploadDir['baseurl'];

        // Rewrite upload URLs in content
        $content = str_replace($uploadsUrl, rtrim($settings['url'], '/'), $content);

        return $content;
    }

    /**
     * Select CDN domain (supports domain sharding)
     */
    private function selectCdnDomain(string $url, array $settings): string
    {
        // If no domain sharding, use main URL
        if (empty($settings['domains']) || !is_array($settings['domains']) || count($settings['domains']) === 0) {
            return $settings['url'];
        }

        // Use hash of URL to consistently select same domain for same resource
        $hash = crc32($url);
        $index = abs($hash) % count($settings['domains']);

        return $settings['domains'][$index];
    }

    /**
     * Purge all CDN cache
     */
    public function purgeAll(): bool
    {
        $settings = $this->settings();

        if (empty($settings['enabled']) || empty($settings['api_key'])) {
            return false;
        }

        $result = false;

        switch ($settings['provider']) {
            case 'cloudflare':
                $result = $this->purgeCloudflare($settings);
                break;
            case 'bunnycdn':
                $result = $this->purgeBunnyCdn($settings);
                break;
            default:
                // Custom provider - fire action for custom handling
                do_action('fp_ps_cdn_purge_all', $settings);
                $result = true;
                break;
        }

        if ($result) {
            Logger::info('CDN cache purged', ['provider' => $settings['provider']]);
        } else {
            Logger::error('CDN cache purge failed', null);
        }

        return $result;
    }

    /**
     * Purge specific file from CDN
     */
    public function purgeFile(string $file): bool
    {
        $settings = $this->settings();

        if (empty($settings['enabled'])) {
            return false;
        }

        $url = wp_get_attachment_url(attachment_url_to_postid($file));

        if (!$url) {
            return false;
        }

        do_action('fp_ps_cdn_purge_file', $url, $settings);

        Logger::debug('CDN file purge requested', ['url' => $url]);

        return true;
    }

    /**
     * Purge CloudFlare cache
     */
    private function purgeCloudflare(array $settings): bool
    {
        if (empty($settings['zone_id'])) {
            return false;
        }

        $response = wp_remote_post(
            "https://api.cloudflare.com/client/v4/zones/{$settings['zone_id']}/purge_cache",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $settings['api_key'],
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode(['purge_everything' => true]),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            Logger::error('CloudFlare purge failed', $response);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        return isset($body['success']) && $body['success'];
    }

    /**
     * Purge BunnyCDN cache
     */
    private function purgeBunnyCdn(array $settings): bool
    {
        if (empty($settings['zone_id'])) {
            return false;
        }

        $response = wp_remote_post(
            "https://api.bunny.net/pullzone/{$settings['zone_id']}/purgeCache",
            [
                'headers' => [
                    'AccessKey' => $settings['api_key'],
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            Logger::error('BunnyCDN purge failed', $response);
            return false;
        }

        return wp_remote_retrieve_response_code($response) === 200;
    }

    /**
     * Test CDN connection
     */
    public function testConnection(): array
    {
        $settings = $this->settings();

        if (empty($settings['url'])) {
            return [
                'success' => false,
                'error' => __('CDN URL not configured', 'fp-performance-suite'),
            ];
        }

        // Test by fetching a small asset
        $testUrl = rtrim($settings['url'], '/') . '/favicon.ico';
        $response = wp_remote_head($testUrl, ['timeout' => 10]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'error' => $response->get_error_message(),
            ];
        }

        $code = wp_remote_retrieve_response_code($response);

        if ($code >= 200 && $code < 400) {
            return [
                'success' => true,
                'message' => __('CDN connection successful', 'fp-performance-suite'),
                'response_code' => $code,
            ];
        }

        return [
            'success' => false,
            'error' => sprintf(__('CDN returned status code: %d', 'fp-performance-suite'), $code),
        ];
    }

    /**
     * Get CDN status
     */
    public function status(): array
    {
        $settings = $this->settings();

        return [
            'enabled' => $settings['enabled'],
            'provider' => $settings['provider'],
            'url' => $settings['url'],
            'domains_count' => count($settings['domains']),
            'has_credentials' => !empty($settings['api_key']),
        ];
    }

    /**
     * Sanitize domains array
     */
    private function sanitizeDomains($domains): array
    {
        if (!is_array($domains)) {
            return [];
        }

        return array_values(array_filter(array_map('esc_url_raw', $domains)));
    }

    /**
     * Sanitize extensions array
     */
    private function sanitizeExtensions($extensions): array
    {
        if (!is_array($extensions)) {
            return ['php'];
        }

        return array_values(array_filter(array_map('sanitize_key', $extensions)));
    }
}
