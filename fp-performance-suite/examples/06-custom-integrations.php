<?php
/**
 * Example: Custom Integrations
 * 
 * Real-world integration examples for common scenarios
 */

// Example 1: Redis Object Cache Integration
add_action('fp_perfsuite_container_ready', function($container) {
    // When Redis is available
    if (class_exists('Redis') && defined('WP_REDIS_HOST')) {
        try {
            $redis = new Redis();
            $redis->connect(WP_REDIS_HOST, WP_REDIS_PORT ?? 6379);
            
            // Store Redis instance in container
            $container->set('redis', fn() => $redis);
            
            // Use Redis for rate limiting
            // (Advanced: replace TransientRepository with RedisRepository)
            
        } catch (\Exception $e) {
            \FP\PerfSuite\Utils\Logger::warning('Redis connection failed', $e);
        }
    }
});

// Example 2: New Relic Integration
add_action('shutdown', function() {
    if (!extension_loaded('newrelic')) {
        return;
    }
    
    $container = \FP\PerfSuite\Plugin::container();
    $scorer = $container->get(\FP\PerfSuite\Services\Score\Scorer::class);
    $pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
    
    // Send custom metrics
    $score = $scorer->calculate();
    newrelic_custom_metric('Custom/FP_Performance_Score', $score['total']);
    
    $cacheStatus = $pageCache->status();
    newrelic_custom_metric('Custom/FP_Cached_Pages', $cacheStatus['files']);
    
    // Add custom parameter
    newrelic_add_custom_parameter('fp_cache_enabled', $pageCache->isEnabled() ? 'true' : 'false');
});

// Example 3: Elasticsearch Logging
add_action('fp_ps_log_error', function($message, $exception) {
    if (!defined('ELASTICSEARCH_HOST')) {
        return;
    }
    
    $data = [
        'timestamp' => current_time('c'),
        'level' => 'error',
        'message' => $message,
        'plugin' => 'fp-performance-suite',
        'site' => home_url(),
        'exception' => $exception ? [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ] : null,
    ];
    
    wp_remote_post(ELASTICSEARCH_HOST . '/logs/_doc', [
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($data),
    ]);
}, 10, 2);

// Example 4: Custom Cache Backend (Memcached)
class FP_Memcached_Cache implements \FP\PerfSuite\Contracts\CacheInterface
{
    private $memcached;
    
    public function __construct() {
        $this->memcached = new Memcached();
        $this->memcached->addServer('localhost', 11211);
    }
    
    public function isEnabled(): bool {
        return $this->memcached->getStats() !== false;
    }
    
    public function settings(): array {
        return ['enabled' => $this->isEnabled(), 'backend' => 'memcached'];
    }
    
    public function update(array $settings): void {
        // Implementation
    }
    
    public function clear(): void {
        $this->memcached->flush();
    }
    
    public function status(): array {
        $stats = $this->memcached->getStats();
        return [
            'enabled' => $this->isEnabled(),
            'items' => $stats ? array_sum(array_column($stats, 'curr_items')) : 0,
        ];
    }
}

// Register custom cache backend
add_action('fp_perfsuite_container_ready', function($container) {
    $container->set('memcached_cache', fn() => new FP_Memcached_Cache());
});

// Example 5: Datadog APM Integration
add_action('init', function() {
    if (!function_exists('datadog_trace')) {
        return;
    }
    
    // Trace cache operations
    add_action('fp_ps_cache_cleared', function() {
        datadog_trace('fp_performance.cache.cleared', function() {
            // Cache clear operation traced
        });
    });
});

// Example 6: Prometheus Metrics Export
add_action('rest_api_init', function() {
    register_rest_route('metrics/v1', '/prometheus', [
        'methods' => 'GET',
        'callback' => function() {
            $container = \FP\PerfSuite\Plugin::container();
            $scorer = $container->get(\FP\PerfSuite\Services\Score\Scorer::class);
            $pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
            $webp = $container->get(\FP\PerfSuite\Services\Media\WebPConverter::class);
            
            $score = $scorer->calculate();
            $cacheStatus = $pageCache->status();
            $webpStatus = $webp->status();
            
            $metrics = sprintf(
                "# TYPE fp_performance_score gauge\nfp_performance_score %d\n" .
                "# TYPE fp_cached_pages gauge\nfp_cached_pages %d\n" .
                "# TYPE fp_webp_coverage gauge\nfp_webp_coverage %.2f\n",
                $score['total'],
                $cacheStatus['files'],
                $webpStatus['coverage']
            );
            
            return new WP_REST_Response($metrics, 200, [
                'Content-Type' => 'text/plain; version=0.0.4',
            ]);
        },
        'permission_callback' => '__return_true',
    ]);
});

// Example 7: Webhook on Score Changes
add_action('update_option_fp_ps_page_cache', function($old, $new) {
    // Recalculate score
    $container = \FP\PerfSuite\Plugin::container();
    $scorer = $container->get(\FP\PerfSuite\Services\Score\Scorer::class);
    $newScore = $scorer->calculate();
    
    // If score changed significantly, send webhook
    $oldScore = get_transient('fp_last_score');
    if ($oldScore && abs($newScore['total'] - $oldScore) >= 10) {
        wp_remote_post('https://your-webhook.com/score-changed', [
            'body' => json_encode([
                'old_score' => $oldScore,
                'new_score' => $newScore['total'],
                'delta' => $newScore['total'] - $oldScore,
            ]),
        ]);
    }
    
    set_transient('fp_last_score', $newScore['total'], DAY_IN_SECONDS);
}, 10, 2);
