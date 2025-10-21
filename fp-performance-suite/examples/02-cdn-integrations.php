<?php
/**
 * Example: CDN Integration Examples
 * 
 * Various CDN integrations with FP Performance Suite
 */

// Example 1: CloudFlare Full Integration
add_action('plugins_loaded', function() {
    if (!class_exists('\FP\PerfSuite\Plugin')) {
        return;
    }
    
    $container = \FP\PerfSuite\Plugin::container();
    $cdn = $container->get(\FP\PerfSuite\Services\CDN\CdnManager::class);
    
    $cdn->update([
        'enabled' => true,
        'url' => 'https://cdn.yourdomain.com',
        'provider' => 'cloudflare',
        'api_key' => defined('CLOUDFLARE_API_KEY') ? CLOUDFLARE_API_KEY : '',
        'zone_id' => defined('CLOUDFLARE_ZONE_ID') ? CLOUDFLARE_ZONE_ID : '',
        'rewrite_content' => true,
    ]);
});

// Example 2: Auto-purge CDN when cache cleared
add_action('fp_ps_cache_cleared', function() {
    $container = \FP\PerfSuite\Plugin::container();
    $cdn = $container->get(\FP\PerfSuite\Services\CDN\CdnManager::class);
    
    // Purge CDN cache
    $cdn->purgeAll();
    
    \FP\PerfSuite\Utils\Logger::info('CDN cache purged after page cache clear');
});

// Example 3: Purge specific files on update
add_action('fp_ps_webp_converted', function($file) {
    $container = \FP\PerfSuite\Plugin::container();
    $cdn = $container->get(\FP\PerfSuite\Services\CDN\CdnManager::class);
    
    // Purge specific image from CDN
    $cdn->purgeFile($file);
    
    \FP\PerfSuite\Utils\Logger::debug('CDN file purged', ['file' => basename($file)]);
});

// Example 4: Custom CDN Provider
add_action('fp_ps_cdn_purge_all', function($settings) {
    // Handle custom CDN purge
    if ($settings['provider'] === 'custom') {
        wp_remote_post('https://your-custom-cdn.com/purge', [
            'headers' => [
                'Authorization' => 'Bearer ' . $settings['api_key'],
            ],
            'body' => json_encode(['purge' => 'all']),
        ]);
    }
});

// Example 5: Domain Sharding Setup
add_action('plugins_loaded', function() {
    $container = \FP\PerfSuite\Plugin::container();
    $cdn = $container->get(\FP\PerfSuite\Services\CDN\CdnManager::class);
    
    // Multiple CDN domains for parallel loading
    $cdn->update([
        'enabled' => true,
        'url' => 'https://cdn1.yourdomain.com',
        'domains' => [
            'https://cdn1.yourdomain.com',
            'https://cdn2.yourdomain.com',
            'https://cdn3.yourdomain.com',
        ],
    ]);
});

// Example 6: Exclude Certain Files from CDN
add_filter('fp_ps_cdn_url', function($cdnUrl, $originalUrl) {
    // Don't use CDN for login/admin assets
    if (strpos($originalUrl, '/wp-admin/') !== false || 
        strpos($originalUrl, '/wp-login') !== false) {
        return $originalUrl;
    }
    
    return $cdnUrl;
}, 10, 2);
