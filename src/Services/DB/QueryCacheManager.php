<?php

namespace FP\PerfSuite\Services\DB;

class QueryCacheManager
{
    private $ttl;
    private $cache_enabled;
    
    public function __construct($ttl = 3600, $cache_enabled = true)
    {
        $this->ttl = $ttl;
        $this->cache_enabled = $cache_enabled;
    }
    
    public function init()
    {
        if ($this->cache_enabled) {
            add_filter('posts_pre_query', [$this, 'cacheQuery'], 10, 2);
            // Solo nel frontend
            if (!is_admin()) {
                add_action('wp_footer', [$this, 'addQueryCacheScript'], 50);
            }
        }
    }
    
    public function cacheQuery($posts, $query)
    {
        if (!$this->cache_enabled) {
            return $posts;
        }
        
        $cache_key = $this->getCacheKey($query);
        $cached_posts = wp_cache_get($cache_key, 'fp_query_cache');
        
        if ($cached_posts !== false) {
            return $cached_posts;
        }
        
        // Store in cache
        wp_cache_set($cache_key, $posts, 'fp_query_cache', $this->ttl);
        
        return $posts;
    }
    
    private function getCacheKey($query)
    {
        $query_vars = $query->query_vars;
        unset($query_vars['cache_results']);
        
        return 'fp_query_' . md5(serialize($query_vars));
    }
    
    public function addQueryCacheScript()
    {
        if (!$this->cache_enabled) return;
        
        echo '<script>
            // Query Cache Manager
            if ("performance" in window) {
                const queryMetrics = {
                    cache_hits: 0,
                    cache_misses: 0,
                    total_queries: 0
                };
                
                // Track query performance
                const originalFetch = window.fetch;
                window.fetch = function(...args) {
                    const start = performance.now();
                    
                    return originalFetch.apply(this, args).then(response => {
                        const end = performance.now();
                        queryMetrics.total_queries++;
                        
                        if (response.headers.get("X-Cache-Status") === "HIT") {
                            queryMetrics.cache_hits++;
                } else {
                            queryMetrics.cache_misses++;
                        }
                        
                        return response;
                    });
                };
                
                // Report metrics
                setTimeout(() => {
                    console.log("Query Cache Metrics:", queryMetrics);
                }, 5000);
            }
        </script>';
    }
    
    public function getQueryCacheMetrics()
    {
        return [
            'ttl' => $this->ttl,
            'cache_enabled' => $this->cache_enabled,
            'cache_hits' => wp_cache_get('fp_cache_hits', 'fp_query_cache') ?: 0,
            'cache_misses' => wp_cache_get('fp_cache_misses', 'fp_query_cache') ?: 0
        ];
    }
    
    public function clearQueryCache()
    {
        wp_cache_flush_group('fp_query_cache');
        return true;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}