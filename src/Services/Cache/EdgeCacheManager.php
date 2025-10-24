<?php

namespace FP\PerfSuite\Services\Cache;

class EdgeCacheManager
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
    
    public function getCacheStatus($url)
    {
        $response = wp_remote_head($url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        
        return [
            'cache_control' => $headers->get('cache-control'),
            'expires' => $headers->get('expires'),
            'age' => $headers->get('age')
        ];
    }
}