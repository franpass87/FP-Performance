<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Utils\Logger;

/**
 * Edge Cache Manager
 * 
 * Gestisce l'integrazione con provider di edge caching (Cloudflare, Fastly, ecc.)
 *
 * @package FP\PerfSuite\Services\Cache
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class EdgeCacheManager
{
    private const OPTION_KEY = 'fp_ps_edge_cache';

    private array $providers = [
        'cloudflare' => 'Cloudflare',
        'fastly' => 'Fastly',
        'cloudfront' => 'AWS CloudFront',
        'stackpath' => 'StackPath',
        'bunnycdn' => 'BunnyCDN',
    ];

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Hook per purge automatica
        add_action('fp_ps_cache_cleared', [$this, 'purgeAll']);
        add_action('save_post', [$this, 'purgePost'], 10, 2);
        add_action('deleted_post', [$this, 'purgePost'], 10, 2);
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'provider' => 'cloudflare',
            'cloudflare_zone_id' => '',
            'cloudflare_api_token' => '',
            'fastly_service_id' => '',
            'fastly_api_key' => '',
            'cloudfront_distribution_id' => '',
            'aws_access_key' => '',
            'aws_secret_key' => '',
            'auto_purge_on_update' => true,
        ];

        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);

        $result = update_option(self::OPTION_KEY, $updated);
        
        if ($result) {
            Logger::info('Edge Cache settings updated', ['provider' => $updated['provider']]);
        }

        return $result;
    }

    /**
     * Verifica se il servizio è abilitato
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }

    /**
     * Ottiene i provider disponibili
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Pulisce tutta la cache edge
     */
    public function purgeAll(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $settings = $this->getSettings();
        $provider = $settings['provider'];

        try {
            switch ($provider) {
                case 'cloudflare':
                    return $this->purgeCloudflare();
                
                case 'fastly':
                    return $this->purgeFastly();
                
                case 'cloudfront':
                    return $this->purgeCloudFront();
                
                default:
                    Logger::warning('Edge cache provider not supported', ['provider' => $provider]);
                    return false;
            }
        } catch (\Exception $e) {
            Logger::error('Edge cache purge failed', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Pulisce la cache per un post specifico
     */
    public function purgePost(int $post_id, ?\WP_Post $post = null): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $settings = $this->getSettings();
        if (empty($settings['auto_purge_on_update'])) {
            return false;
        }

        $post = $post ?: get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            return false;
        }

        $url = get_permalink($post_id);
        if (!$url) {
            return false;
        }

        return $this->purgeUrls([$url]);
    }

    /**
     * Pulisce specifici URL dalla cache edge
     */
    public function purgeUrls(array $urls): bool
    {
        if (!$this->isEnabled() || empty($urls)) {
            return false;
        }

        $settings = $this->getSettings();
        $provider = $settings['provider'];

        try {
            switch ($provider) {
                case 'cloudflare':
                    return $this->purgeCloudflareUrls($urls);
                
                case 'fastly':
                    return $this->purgeFastlyUrls($urls);
                
                case 'cloudfront':
                    return $this->purgeCloudFrontPaths($urls);
                
                default:
                    return false;
            }
        } catch (\Exception $e) {
            Logger::error('Edge cache URL purge failed', [
                'provider' => $provider,
                'urls' => $urls,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Pulisce cache Cloudflare
     */
    private function purgeCloudflare(): bool
    {
        $settings = $this->getSettings();
        $zoneId = $settings['cloudflare_zone_id'];
        $apiToken = $settings['cloudflare_api_token'];

        if (empty($zoneId) || empty($apiToken)) {
            Logger::error('Cloudflare credentials missing');
            return false;
        }

        $response = wp_remote_post(
            "https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode(['purge_everything' => true]),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            Logger::error('Cloudflare purge failed', ['error' => $response->get_error_message()]);
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data['success'])) {
            Logger::info('Cloudflare cache purged successfully');
            return true;
        }

        Logger::error('Cloudflare purge failed', ['response' => $data]);
        return false;
    }

    /**
     * Pulisce specifici URL da Cloudflare
     */
    private function purgeCloudflareUrls(array $urls): bool
    {
        $settings = $this->getSettings();
        $zoneId = $settings['cloudflare_zone_id'];
        $apiToken = $settings['cloudflare_api_token'];

        if (empty($zoneId) || empty($apiToken)) {
            return false;
        }

        $response = wp_remote_post(
            "https://api.cloudflare.com/client/v4/zones/{$zoneId}/purge_cache",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode(['files' => $urls]),
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return !empty($data['success']);
    }

    /**
     * Pulisce cache Fastly
     */
    private function purgeFastly(): bool
    {
        $settings = $this->getSettings();
        $serviceId = $settings['fastly_service_id'];
        $apiKey = $settings['fastly_api_key'];

        if (empty($serviceId) || empty($apiKey)) {
            Logger::error('Fastly credentials missing');
            return false;
        }

        $response = wp_remote_post(
            "https://api.fastly.com/service/{$serviceId}/purge_all",
            [
                'headers' => [
                    'Fastly-Key' => $apiKey,
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            Logger::error('Fastly purge failed', ['error' => $response->get_error_message()]);
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 200) {
            Logger::info('Fastly cache purged successfully');
            return true;
        }

        return false;
    }

    /**
     * Pulisce specifici URL da Fastly
     */
    private function purgeFastlyUrls(array $urls): bool
    {
        $settings = $this->getSettings();
        $apiKey = $settings['fastly_api_key'];

        if (empty($apiKey)) {
            return false;
        }

        $success = true;
        foreach ($urls as $url) {
            $response = wp_remote_request(
                $url,
                [
                    'method' => 'PURGE',
                    'headers' => [
                        'Fastly-Key' => $apiKey,
                    ],
                    'timeout' => 10,
                ]
            );

            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Pulisce cache CloudFront
     */
    private function purgeCloudFront(): bool
    {
        $settings = $this->getSettings();
        $distributionId = $settings['cloudfront_distribution_id'];

        if (empty($distributionId)) {
            Logger::error('CloudFront distribution ID missing');
            return false;
        }

        // CloudFront richiede AWS SDK, qui forniamo solo la base
        Logger::warning('CloudFront purge requires AWS SDK implementation');
        
        // Per implementazione completa servirebbero le AWS SDK
        // Per ora logghiamo e restituiamo false
        return false;
    }

    /**
     * Pulisce specifici path da CloudFront
     */
    private function purgeCloudFrontPaths(array $urls): bool
    {
        // CloudFront invalidation richiede AWS SDK
        Logger::warning('CloudFront path purge requires AWS SDK implementation');
        return false;
    }

    /**
     * Testa la connessione al provider
     */
    public function testConnection(): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => __('Edge cache non abilitato', 'fp-performance-suite'),
            ];
        }

        $settings = $this->getSettings();
        $provider = $settings['provider'];

        try {
            switch ($provider) {
                case 'cloudflare':
                    return $this->testCloudflareConnection();
                
                case 'fastly':
                    return $this->testFastlyConnection();
                
                default:
                    return [
                        'success' => false,
                        'message' => sprintf(__('Provider %s non supportato per test', 'fp-performance-suite'), $provider),
                    ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Testa connessione Cloudflare
     */
    private function testCloudflareConnection(): array
    {
        $settings = $this->getSettings();
        $zoneId = $settings['cloudflare_zone_id'];
        $apiToken = $settings['cloudflare_api_token'];

        if (empty($zoneId) || empty($apiToken)) {
            return [
                'success' => false,
                'message' => __('Credenziali Cloudflare mancanti', 'fp-performance-suite'),
            ];
        }

        $response = wp_remote_get(
            "https://api.cloudflare.com/client/v4/zones/{$zoneId}",
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
                'timeout' => 10,
            ]
        );

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!empty($data['success'])) {
            return [
                'success' => true,
                'message' => __('Connessione Cloudflare OK', 'fp-performance-suite'),
                'zone_name' => $data['result']['name'] ?? '',
            ];
        }

        return [
            'success' => false,
            'message' => $data['errors'][0]['message'] ?? __('Errore sconosciuto', 'fp-performance-suite'),
        ];
    }

    /**
     * Testa connessione Fastly
     */
    private function testFastlyConnection(): array
    {
        $settings = $this->getSettings();
        $serviceId = $settings['fastly_service_id'];
        $apiKey = $settings['fastly_api_key'];

        if (empty($serviceId) || empty($apiKey)) {
            return [
                'success' => false,
                'message' => __('Credenziali Fastly mancanti', 'fp-performance-suite'),
            ];
        }

        $response = wp_remote_get(
            "https://api.fastly.com/service/{$serviceId}",
            [
                'headers' => [
                    'Fastly-Key' => $apiKey,
                ],
                'timeout' => 10,
            ]
        );

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message(),
            ];
        }

        $code = wp_remote_retrieve_response_code($response);
        
        if ($code === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            return [
                'success' => true,
                'message' => __('Connessione Fastly OK', 'fp-performance-suite'),
                'service_name' => $data['name'] ?? '',
            ];
        }

        return [
            'success' => false,
            'message' => sprintf(__('Errore HTTP %d', 'fp-performance-suite'), $code),
        ];
    }
}

