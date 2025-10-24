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
        add_action('wp_enqueue_scripts', [$this, 'cdnScripts']);
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
        
        // Add CDN configuration
        wp_localize_script('jquery', 'fpCdnConfig', [
            'provider' => $this->provider,
            'apiKey' => $this->api_key,
            'zoneId' => $this->zone_id
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
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}