<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Services\Cache\EdgeCache\CloudflareProvider;
use FP\PerfSuite\Services\Cache\EdgeCache\FastlyProvider;
use FP\PerfSuite\Services\Cache\EdgeCache\CloudFrontProvider;
use FP\PerfSuite\Services\Cache\EdgeCache\EdgeCacheProvider;
use FP\PerfSuite\Utils\Logger;

use function add_action;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Edge Cache Manager
 *
 * Manages edge caching integrations with CDN/WAF providers
 *
 * @package FP\PerfSuite\Services\Cache
 * @author Francesco Passeri
 */
class EdgeCacheManager
{
    private const OPTION = 'fp_ps_edge_cache';
    private ?EdgeCacheProvider $provider = null;

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        $this->initializeProvider($settings);

        // Auto-purge on content changes
        if ($settings['auto_purge']) {
            add_action('save_post', [$this, 'purgePostCache'], 10, 2);
            add_action('deleted_post', [$this, 'purgePostCache'], 10, 2);
            add_action('switch_theme', [$this, 'purgeAllCache']);
            add_action('activated_plugin', [$this, 'purgeAllCache']);
        }
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,provider:string,auto_purge:bool,cloudflare:array,fastly:array,cloudfront:array}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'provider' => 'none', // cloudflare, fastly, cloudfront
            'auto_purge' => true,
            'cloudflare' => [
                'api_token' => '',
                'zone_id' => '',
                'email' => '',
            ],
            'fastly' => [
                'api_key' => '',
                'service_id' => '',
            ],
            'cloudfront' => [
                'access_key_id' => '',
                'secret_access_key' => '',
                'distribution_id' => '',
                'region' => 'us-east-1',
            ],
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $new = [
            'enabled' => !empty($settings['enabled']),
            'provider' => $settings['provider'] ?? $current['provider'],
            'auto_purge' => isset($settings['auto_purge']) ? !empty($settings['auto_purge']) : $current['auto_purge'],
            'cloudflare' => isset($settings['cloudflare']) ? array_merge($current['cloudflare'], $settings['cloudflare']) : $current['cloudflare'],
            'fastly' => isset($settings['fastly']) ? array_merge($current['fastly'], $settings['fastly']) : $current['fastly'],
            'cloudfront' => isset($settings['cloudfront']) ? array_merge($current['cloudfront'], $settings['cloudfront']) : $current['cloudfront'],
        ];

        update_option(self::OPTION, $new);

        // Reinitialize provider with new settings
        if ($new['enabled']) {
            $this->initializeProvider($new);
        }
    }

    /**
     * Initialize edge cache provider
     *
     * @param array $settings Settings
     * @return bool True if initialized
     */
    private function initializeProvider(array $settings): bool
    {
        $provider = $settings['provider'];

        try {
            switch ($provider) {
                case 'cloudflare':
                    $config = $settings['cloudflare'];
                    if (empty($config['api_token']) || empty($config['zone_id'])) {
                        Logger::warning('Cloudflare credentials not configured');
                        return false;
                    }
                    $this->provider = new CloudflareProvider(
                        $config['api_token'],
                        $config['zone_id'],
                        $config['email'] ?? ''
                    );
                    break;

                case 'fastly':
                    $config = $settings['fastly'];
                    if (empty($config['api_key']) || empty($config['service_id'])) {
                        Logger::warning('Fastly credentials not configured');
                        return false;
                    }
                    $this->provider = new FastlyProvider(
                        $config['api_key'],
                        $config['service_id']
                    );
                    break;

                case 'cloudfront':
                    $config = $settings['cloudfront'];
                    if (empty($config['access_key_id']) || empty($config['secret_access_key']) || empty($config['distribution_id'])) {
                        Logger::warning('CloudFront credentials not configured');
                        return false;
                    }
                    $this->provider = new CloudFrontProvider(
                        $config['access_key_id'],
                        $config['secret_access_key'],
                        $config['distribution_id'],
                        $config['region'] ?? 'us-east-1'
                    );
                    break;

                default:
                    Logger::warning('No edge cache provider configured');
                    return false;
            }

            Logger::info('Edge cache provider initialized', ['provider' => $provider]);
            return true;
        } catch (\Exception $e) {
            Logger::error('Failed to initialize edge cache provider', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Purge all cache
     *
     * @return bool True if successful
     */
    public function purgeAll(): bool
    {
        if ($this->provider === null) {
            Logger::warning('No edge cache provider available');
            return false;
        }

        return $this->provider->purgeAll();
    }

    /**
     * Purge specific URLs
     *
     * @param array $urls URLs to purge
     * @return bool True if successful
     */
    public function purgeUrls(array $urls): bool
    {
        if ($this->provider === null) {
            return false;
        }

        return $this->provider->purgeUrls($urls);
    }

    /**
     * Purge by tags
     *
     * @param array $tags Cache tags
     * @return bool True if successful
     */
    public function purgeTags(array $tags): bool
    {
        if ($this->provider === null) {
            return false;
        }

        return $this->provider->purgeTags($tags);
    }

    /**
     * Purge post cache on save/delete
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     */
    public function purgePostCache(int $post_id, $post): void
    {
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }

        $urls = $this->getPostUrls($post_id);
        
        if (!empty($urls)) {
            $this->purgeUrls($urls);
            Logger::info('Edge cache purged for post', ['post_id' => $post_id, 'urls' => count($urls)]);
        }
    }

    /**
     * Purge all cache (wrapper for actions)
     */
    public function purgeAllCache(): void
    {
        $this->purgeAll();
    }

    /**
     * Get URLs to purge for a post
     *
     * @param int $post_id Post ID
     * @return array URLs
     */
    private function getPostUrls(int $post_id): array
    {
        $urls = [];

        // Post permalink
        $permalink = get_permalink($post_id);
        if ($permalink) {
            $urls[] = $permalink;
        }

        // Homepage
        $urls[] = home_url('/');

        // Category/tag archives
        $post = get_post($post_id);
        if ($post) {
            $categories = get_the_category($post_id);
            foreach ($categories as $category) {
                $urls[] = get_category_link($category->term_id);
            }

            $tags = get_the_tags($post_id);
            if (is_array($tags)) {
                foreach ($tags as $tag) {
                    $urls[] = get_tag_link($tag->term_id);
                }
            }
        }

        // Allow filtering
        return apply_filters('fp_ps_edge_cache_purge_urls', $urls, $post_id);
    }

    /**
     * Test provider connection
     *
     * @return array{success:bool,message:string,provider:string,info?:array}
     */
    public function testConnection(): array
    {
        if ($this->provider === null) {
            $settings = $this->settings();
            if (!$this->initializeProvider($settings)) {
                return [
                    'success' => false,
                    'message' => 'Provider not configured',
                    'provider' => $settings['provider'],
                ];
            }
        }

        $result = $this->provider->testConnection();
        $result['provider'] = $this->provider->getName();

        return $result;
    }

    /**
     * Get cache statistics
     *
     * @return array Stats
     */
    public function getStats(): array
    {
        if ($this->provider === null) {
            return [];
        }

        return $this->provider->getStats();
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,provider:string,connected:bool}
     */
    public function status(): array
    {
        $settings = $this->settings();

        return [
            'enabled' => $settings['enabled'],
            'provider' => $settings['provider'],
            'connected' => $this->provider !== null,
        ];
    }

    /**
     * Get provider instance (for testing/advanced usage)
     *
     * @return EdgeCacheProvider|null
     */
    public function getProvider(): ?EdgeCacheProvider
    {
        return $this->provider;
    }
}
