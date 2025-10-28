<?php

namespace FP\PerfSuite\Services\CDN;

class CdnManager
{
    private $provider;
    private $api_key;
    private $zone_id;
    
    public function __construct($provider = 'cloudflare', $api_key = '', $zone_id = '')
    {
        $this->provider = $provider;
        $this->api_key = $api_key;
        $this->zone_id = $zone_id;
    }
    
    public function init()
    {
        add_filter('wp_get_attachment_url', [$this, 'cdnUrl'], 10, 2);
        add_filter('wp_get_attachment_image_src', [$this, 'cdnImageSrc'], 10, 4);
        add_action('wp_enqueue_scripts', [$this, 'cdnScripts'], 991);
    }
    
    public function cdnUrl($url, $post_id)
    {
        if (empty($this->api_key) || empty($this->zone_id)) {
            return $url;
        }
        
        return $this->getCdnUrl($url);
    }
    
    public function cdnImageSrc($image, $attachment_id, $size, $icon)
    {
        if (empty($this->api_key) || empty($this->zone_id)) {
            return $image;
        }
        
        if ($image && isset($image[0])) {
            $image[0] = $this->getCdnUrl($image[0]);
        }
        
        return $image;
    }
    
    public function cdnScripts()
    {
        if (empty($this->api_key) || empty($this->zone_id)) {
            return;
        }
        
        // SICUREZZA: Add CDN configuration con sanitizzazione
        wp_localize_script('jquery', 'fpCdnConfig', [
            'provider' => sanitize_text_field($this->provider),
            'apiKey' => sanitize_text_field($this->api_key),
            'zoneId' => sanitize_text_field($this->zone_id)
        ]);
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
    
    private function getCdnDomain()
    {
        switch ($this->provider) {
            case 'cloudflare':
                return 'https://cdn.example.com';
            case 'fastly':
                return 'https://fastly.example.com';
            case 'aws':
                return 'https://s3.amazonaws.com/example-bucket';
            default:
                return false;
        }
    }
    
    public function purgeCache($urls = [])
    {
        if (empty($this->api_key) || empty($this->zone_id)) {
            return false;
        }
        
        switch ($this->provider) {
            case 'cloudflare':
                return $this->purgeCloudflare($urls);
            case 'fastly':
                return $this->purgeFastly($urls);
            default:
                return false;
        }
    }
    
    private function purgeCloudflare($urls)
    {
        $endpoint = "https://api.cloudflare.com/client/v4/zones/{$this->zone_id}/purge_cache";
        
        $data = [
            'purge_everything' => empty($urls),
            'files' => $urls
        ];
        
        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode($data)
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return isset($data['success']) && $data['success'];
    }
    
    private function purgeFastly($urls)
    {
        $endpoint = "https://api.fastly.com/service/{$this->zone_id}/purge";
        
        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Fastly-Key' => $this->api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode(['urls' => $urls])
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        return wp_remote_retrieve_response_code($response) === 200;
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