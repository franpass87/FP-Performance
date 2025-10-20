<?php

namespace FP\PerfSuite\Services\Cache\EdgeCache;

use FP\PerfSuite\Utils\Logger;

use function wp_remote_post;
use function wp_remote_get;

/**
 * Fastly Edge Cache Provider
 *
 * @package FP\PerfSuite\Services\Cache\EdgeCache
 * @author Francesco Passeri
 */
class FastlyProvider implements EdgeCacheProvider
{
    private string $apiKey;
    private string $serviceId;
    private const API_BASE = 'https://api.fastly.com';

    public function __construct(string $apiKey, string $serviceId)
    {
        $this->apiKey = $apiKey;
        $this->serviceId = $serviceId;
    }

    /**
     * {@inheritdoc}
     */
    public function purgeAll(): bool
    {
        $endpoint = self::API_BASE . '/service/' . $this->serviceId . '/purge_all';

        $response = wp_remote_post($endpoint, [
            'headers' => $this->getHeaders(),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            Logger::error('Fastly purge all failed', null, ['error' => $response->get_error_message()]);
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        $success = isset($data['status']) && $data['status'] === 'ok';

        if ($success) {
            Logger::info('Fastly cache purged completely');
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

        $allSuccess = true;

        foreach ($urls as $url) {
            $response = wp_remote_post($url, [
                'method' => 'PURGE',
                'headers' => $this->getHeaders(),
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                Logger::error('Fastly purge URL failed', null, [
                    'url' => $url,
                    'error' => $response->get_error_message(),
                ]);
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            Logger::info('Fastly URLs purged', ['count' => count($urls)]);
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

        $endpoint = self::API_BASE . '/service/' . $this->serviceId . '/purge';

        $allSuccess = true;

        foreach ($tags as $tag) {
            $response = wp_remote_post($endpoint, [
                'headers' => array_merge($this->getHeaders(), [
                    'Fastly-Soft-Purge' => '1',
                    'Surrogate-Key' => $tag,
                ]),
                'timeout' => 10,
            ]);

            if (is_wp_error($response)) {
                Logger::error('Fastly purge tag failed', null, [
                    'tag' => $tag,
                    'error' => $response->get_error_message(),
                ]);
                $allSuccess = false;
            }
        }

        if ($allSuccess) {
            Logger::info('Fastly tags purged', ['count' => count($tags)]);
        }

        return $allSuccess;
    }

    /**
     * {@inheritdoc}
     */
    public function testConnection(): array
    {
        $endpoint = self::API_BASE . '/service/' . $this->serviceId;

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

        $code = wp_remote_retrieve_response_code($response);

        if ($code !== 200) {
            return [
                'success' => false,
                'message' => 'Invalid API key or Service ID',
            ];
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        return [
            'success' => true,
            'message' => 'Connected successfully',
            'info' => [
                'service_name' => $data['name'] ?? 'Unknown',
                'version' => $data['version'] ?? 'Unknown',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getStats(): array
    {
        $endpoint = self::API_BASE . '/service/' . $this->serviceId . '/stats';

        $response = wp_remote_get($endpoint, [
            'headers' => $this->getHeaders(),
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return [];
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($data)) {
            return [];
        }

        return [
            'requests' => $data['requests'] ?? 0,
            'hits' => $data['hits'] ?? 0,
            'miss' => $data['miss'] ?? 0,
            'bandwidth' => $data['bandwidth'] ?? 0,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Fastly';
    }

    /**
     * Get API request headers
     *
     * @return array Headers
     */
    private function getHeaders(): array
    {
        return [
            'Fastly-Key' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }
}
