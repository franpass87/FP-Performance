<?php

namespace FP\PerfSuite\Services\Cache\EdgeCache;

use FP\PerfSuite\Utils\Logger;

use function wp_remote_post;
use function wp_remote_get;
use function wp_remote_request;

/**
 * Cloudflare Edge Cache Provider
 *
 * @package FP\PerfSuite\Services\Cache\EdgeCache
 * @author Francesco Passeri
 */
class CloudflareProvider implements EdgeCacheProvider
{
    private string $apiToken;
    private string $zoneId;
    private string $email;
    private const API_BASE = 'https://api.cloudflare.com/client/v4';

    public function __construct(string $apiToken, string $zoneId, string $email = '')
    {
        $this->apiToken = $apiToken;
        $this->zoneId = $zoneId;
        $this->email = $email;
    }

    /**
     * {@inheritdoc}
     */
    public function purgeAll(): bool
    {
        $endpoint = self::API_BASE . '/zones/' . $this->zoneId . '/purge_cache';

        $response = wp_remote_post($endpoint, [
            'headers' => $this->getHeaders(),
            'body' => json_encode(['purge_everything' => true]),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            Logger::error('Cloudflare purge all failed', null, ['error' => $response->get_error_message()]);
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $success = !empty($data['success']);

        if ($success) {
            Logger::info('Cloudflare cache purged completely');
        } else {
            Logger::error('Cloudflare purge failed', null, ['errors' => $data['errors'] ?? []]);
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function purgeUrls(array $urls): bool
    {
        if (empty($urls)) {
            return true;
        }

        // Cloudflare allows max 30 URLs per request
        $chunks = array_chunk($urls, 30);
        $allSuccess = true;

        foreach ($chunks as $chunk) {
            $endpoint = self::API_BASE . '/zones/' . $this->zoneId . '/purge_cache';

            $response = wp_remote_post($endpoint, [
                'headers' => $this->getHeaders(),
                'body' => json_encode(['files' => $chunk]),
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                Logger::error('Cloudflare purge URLs failed', null, ['error' => $response->get_error_message()]);
                $allSuccess = false;
                continue;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (empty($data['success'])) {
                Logger::error('Cloudflare purge URLs failed', null, ['errors' => $data['errors'] ?? []]);
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            Logger::info('Cloudflare URLs purged', ['count' => count($urls)]);
        }

        return $allSuccess;
    }

    /**
     * {@inheritdoc}
     */
    public function purgeTags(array $tags): bool
    {
        if (empty($tags)) {
            return true;
        }

        // Cloudflare allows max 30 tags per request
        $chunks = array_chunk($tags, 30);
        $allSuccess = true;

        foreach ($chunks as $chunk) {
            $endpoint = self::API_BASE . '/zones/' . $this->zoneId . '/purge_cache';

            $response = wp_remote_post($endpoint, [
                'headers' => $this->getHeaders(),
                'body' => json_encode(['tags' => $chunk]),
                'timeout' => 30,
            ]);

            if (is_wp_error($response)) {
                Logger::error('Cloudflare purge tags failed', null, ['error' => $response->get_error_message()]);
                $allSuccess = false;
                continue;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (empty($data['success'])) {
                Logger::error('Cloudflare purge tags failed', null, ['errors' => $data['errors'] ?? []]);
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            Logger::info('Cloudflare tags purged', ['count' => count($tags)]);
        }

        return $allSuccess;
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): array
    {
        $endpoint = self::API_BASE . '/zones/' . $this->zoneId;

        $response = wp_remote_get($endpoint, [
            'headers' => $this->getHeaders(),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($data['success'])) {
            return [
                'success' => false,
                'message' => 'Invalid API credentials or Zone ID',
                'errors' => $data['errors'] ?? [],
            ];
        }

        return [
            'success' => true,
            'message' => 'Connected successfully',
            'info' => [
                'zone_name' => $data['result']['name'] ?? 'Unknown',
                'status' => $data['result']['status'] ?? 'Unknown',
                'plan' => $data['result']['plan']['name'] ?? 'Unknown',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getStats(): array
    {
        $endpoint = self::API_BASE . '/zones/' . $this->zoneId . '/analytics/dashboard';

        $response = wp_remote_get($endpoint, [
            'headers' => $this->getHeaders(),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return [];
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($data['success'])) {
            return [];
        }

        $result = $data['result'] ?? [];

        return [
            'requests' => $result['totals']['requests']['all'] ?? 0,
            'bandwidth' => $result['totals']['bandwidth']['all'] ?? 0,
            'cache_hit_rate' => $result['totals']['requests']['cached'] ?? 0,
            'threats' => $result['totals']['threats']['all'] ?? 0,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Cloudflare';
    }

    /**
     * Get API request headers
     *
     * @return array Headers
     */
    private function getHeaders(): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ];

        // Legacy authentication with email
        if (!empty($this->email)) {
            $headers['X-Auth-Email'] = $this->email;
            $headers['X-Auth-Key'] = $this->apiToken;
            unset($headers['Authorization']);
        }

        return $headers;
    }

    /**
     * Set development mode
     *
     * @param bool $enabled Enable development mode
     * @return bool True if successful
     */
    public function setDevelopmentMode(bool $enabled): bool
    {
        $endpoint = self::API_BASE . '/zones/' . $this->zoneId . '/settings/development_mode';

        $response = wp_remote_request($endpoint, [
            'method' => 'PATCH',
            'headers' => $this->getHeaders(),
            'body' => json_encode(['value' => $enabled ? 'on' : 'off']),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            Logger::error('Cloudflare dev mode toggle failed', null, ['error' => $response->get_error_message()]);
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        
        return !empty($data['success']);
    }
}
