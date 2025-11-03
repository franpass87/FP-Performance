<?php

namespace FP\PerfSuite\Services\CDN;

use FP\PerfSuite\Utils\Logger;

/**
 * CDN Manager Service
 * 
 * Gestisce la distribuzione di asset tramite CDN
 * 
 * SECURITY: API credentials NON devono MAI essere esposte nel frontend
 * 
 * @package FP\PerfSuite\Services\CDN
 */
class CdnManager
{
    private string $provider;
    private string $api_key;
    private string $zone_id;
    private ?string $cdn_url = null;
    
    /**
     * Costruttore
     * 
     * @param string $provider Provider CDN
     * @param string $api_key API key (NEVER expose in frontend!)
     * @param string $zone_id Zone ID (NEVER expose in frontend!)
     */
    public function __construct(string $provider = 'cloudflare', string $api_key = '', string $zone_id = '')
    {
        $this->provider = $provider;
        $this->api_key = $api_key;
        $this->zone_id = $zone_id;
        
        // Carica CDN URL da settings
        $settings = $this->settings();
        $this->cdn_url = $settings['url'] ?? null;
    }
    
    /**
     * Inizializza il servizio
     */
    public function init(): void
    {
        // Solo se CDN è configurato
        if (empty($this->cdn_url)) {
            return;
        }
        
        add_filter('wp_get_attachment_url', [$this, 'cdnUrl'], 10, 2);
        add_filter('wp_get_attachment_image_src', [$this, 'cdnImageSrc'], 10, 4);
        
        // SECURITY FIX: NON esporre API key/zone nel frontend
        // Rimuovo completamente cdnScripts()
    }
    
    /**
     * Converte URL in CDN URL
     * 
     * @param string $url URL originale
     * @param int $post_id Post ID
     * @return string CDN URL o URL originale
     */
    public function cdnUrl(string $url, int $post_id): string
    {
        // Verifica che CDN sia configurato
        if (empty($this->cdn_url)) {
            return $url;
        }
        
        return $this->getCdnUrl($url);
    }
    
    /**
     * Converte image src in CDN URL
     * 
     * @param array|false $image Image data
     * @param int $attachment_id Attachment ID
     * @param string|array $size Image size
     * @param bool $icon Is icon
     * @return array|false CDN image data
     */
    public function cdnImageSrc($image, int $attachment_id, $size, bool $icon)
    {
        if (empty($this->cdn_url)) {
            return $image;
        }
        
        if ($image && is_array($image) && isset($image[0])) {
            $image[0] = $this->getCdnUrl($image[0]);
        }
        
        return $image;
    }
    
    private function getCdnUrl($url)
    {
        if (strpos($url, home_url()) === false) {
            return $url;
        }
        
        $cdn_domain = $this->getCdnDomain();
        if (!$cdn_domain) {
            return $url;
        }
        
        return str_replace(home_url(), $cdn_domain, $url);
    }
    
    /**
     * SECURITY FIX: CDN domain da configurazione, non hardcoded
     * 
     * @return string|false CDN domain o false
     */
    private function getCdnDomain()
    {
        // FIX: Usa CDN URL da settings invece di hardcoded
        if (!empty($this->cdn_url)) {
            return $this->cdn_url;
        }
        
        // Fallback: leggi da settings se non impostato nel constructor
        $settings = $this->settings();
        if (!empty($settings['url'])) {
            return esc_url_raw($settings['url']);
        }
        
        // Nessun CDN configurato
        Logger::debug('CDN domain not configured');
        return false;
    }
    
    /**
     * Purga cache CDN
     * 
     * SECURITY: API operations only from server-side (never frontend)
     * 
     * @param array $urls URL da purgare (vuoto = purge all)
     * @return bool True se successo
     */
    public function purgeCache(array $urls = []): bool
    {
        if (empty($this->api_key) || empty($this->zone_id)) {
            Logger::warning('CDN purge skipped - missing credentials');
            return false;
        }
        
        try {
            switch ($this->provider) {
                case 'cloudflare':
                    return $this->purgeCloudflare($urls);
                case 'fastly':
                    return $this->purgeFastly($urls);
                default:
                    Logger::warning('CDN purge skipped - unsupported provider', [
                        'provider' => $this->provider
                    ]);
                    return false;
            }
        } catch (\Throwable $e) {
            Logger::error('CDN purge exception', $e);
            return false;
        }
    }
    
    /**
     * Purga cache Cloudflare
     * 
     * @param array $urls URL da purgare
     * @return bool True se successo
     */
    private function purgeCloudflare(array $urls): bool
    {
        $endpoint = sprintf(
            'https://api.cloudflare.com/client/v4/zones/%s/purge_cache',
            $this->zone_id
        );
        
        $data = [
            'purge_everything' => empty($urls),
            'files' => $urls
        ];
        
        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode($data),
            'timeout' => 30,
        ]);
        
        if (is_wp_error($response)) {
            Logger::error('Cloudflare purge failed', [
                'error' => $response->get_error_message()
            ]);
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $responseData = json_decode($body, true);
        
        // SECURITY FIX: Valida JSON response
        if (json_last_error() !== JSON_ERROR_NONE) {
            Logger::error('Cloudflare response JSON invalid', [
                'error' => json_last_error_msg()
            ]);
            return false;
        }
        
        $success = isset($responseData['success']) && $responseData['success'];
        
        if (!$success) {
            Logger::error('Cloudflare purge failed', [
                'errors' => $responseData['errors'] ?? 'Unknown error',
                'messages' => $responseData['messages'] ?? []
            ]);
        } else {
            Logger::info('Cloudflare cache purged', [
                'urls_count' => count($urls),
                'purge_all' => empty($urls)
            ]);
        }
        
        return $success;
    }
    
    /**
     * Purga cache Fastly
     * 
     * @param array $urls URL da purgare
     * @return bool True se successo
     */
    private function purgeFastly(array $urls): bool
    {
        $endpoint = sprintf(
            'https://api.fastly.com/service/%s/purge',
            $this->zone_id
        );
        
        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Fastly-Key' => $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => wp_json_encode(['urls' => $urls]),
            'timeout' => 30,
        ]);
        
        if (is_wp_error($response)) {
            Logger::error('Fastly purge failed', [
                'error' => $response->get_error_message()
            ]);
            return false;
        }
        
        $code = wp_remote_retrieve_response_code($response);
        $success = $code === 200;
        
        if (!$success) {
            Logger::error('Fastly purge failed', [
                'status_code' => $code,
                'response' => wp_remote_retrieve_body($response)
            ]);
        } else {
            Logger::info('Fastly cache purged', [
                'urls_count' => count($urls)
            ]);
        }
        
        return $success;
    }
    
    public function getCdnMetrics()
    {
        return [
            'provider' => $this->provider,
            'api_key_configured' => !empty($this->api_key),
            'zone_id_configured' => !empty($this->zone_id)
        ];
    }
    
    /**
     * Restituisce le impostazioni del CDN
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        return [
            'enabled' => $this->enabled ?? false,
            'cdn_url' => $this->cdn_url ?? '',
            'provider' => $this->provider,
            'api_key' => !empty($this->api_key) ? '***' : '', // Nascondi API key
            'zone_id' => $this->zone_id,
        ];
    }
    
    /**
     * Aggiorna le impostazioni del CDN
     * 
     * @param array $settings Array con le impostazioni da aggiornare
     * @return bool True se salvato con successo
     */
    public function update(array $settings): bool
    {
        $currentSettings = get_option('fp_ps_cdn_settings', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['enabled'])) {
            $newSettings['enabled'] = (bool) $newSettings['enabled'];
        }
        
        if (isset($newSettings['cdn_url'])) {
            $newSettings['cdn_url'] = esc_url_raw($newSettings['cdn_url']);
        }
        
        if (isset($newSettings['provider'])) {
            $allowedProviders = ['cloudflare', 'fastly', 'cloudfront', 'bunnycdn', 'custom'];
            if (!in_array($newSettings['provider'], $allowedProviders, true)) {
                $newSettings['provider'] = 'cloudflare';
            }
        }
        
        if (isset($newSettings['api_key'])) {
            $newSettings['api_key'] = sanitize_text_field($newSettings['api_key']);
        }
        
        if (isset($newSettings['zone_id'])) {
            $newSettings['zone_id'] = sanitize_text_field($newSettings['zone_id']);
        }
        
        $result = update_option('fp_ps_cdn_settings', $newSettings, false);
        
        // Aggiorna proprietà interne
        if (isset($newSettings['provider'])) {
            $this->provider = $newSettings['provider'];
        }
        if (isset($newSettings['api_key'])) {
            $this->api_key = $newSettings['api_key'];
        }
        if (isset($newSettings['zone_id'])) {
            $this->zone_id = $newSettings['zone_id'];
        }
        
        return $result;
    }
    
    /**
     * Restituisce lo stato del CDN
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        return [
            'enabled' => $this->enabled && !empty($this->cdn_url),
            'cdn_url' => $this->cdn_url,
            'provider' => $this->provider,
            'api_key_configured' => !empty($this->api_key),
            'zone_id_configured' => !empty($this->zone_id),
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}