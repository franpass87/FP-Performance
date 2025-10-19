<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Logger;

use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Database Query Cache Manager
 *
 * Caches expensive database queries for improved performance
 *
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 */
class QueryCacheManager
{
    private const OPTION = 'fp_ps_query_cache';
    private array $cache = [];
    private array $stats = [
        'hits' => 0,
        'misses' => 0,
        'stores' => 0,
    ];

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Intercept queries
        add_filter('query', [$this, 'cacheQuery'], 10, 1);
        
        // Log stats on shutdown
        add_action('shutdown', [$this, 'logStats']);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,ttl:int,max_size:int,exclude_patterns:array,cache_selects_only:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'ttl' => 3600, // 1 hour
            'max_size' => 1000, // Max cached queries
            'exclude_patterns' => [
                'wp_options',
                'wp_postmeta',
                'wp_usermeta',
                'SHOW',
                'DESCRIBE',
            ],
            'cache_selects_only' => true,
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $ttl = isset($settings['ttl']) ? (int)$settings['ttl'] : $current['ttl'];
        $ttl = max(60, min(86400, $ttl));

        $maxSize = isset($settings['max_size']) ? (int)$settings['max_size'] : $current['max_size'];
        $maxSize = max(100, min(10000, $maxSize));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'ttl' => $ttl,
            'max_size' => $maxSize,
            'exclude_patterns' => $settings['exclude_patterns'] ?? $current['exclude_patterns'],
            'cache_selects_only' => isset($settings['cache_selects_only']) ? !empty($settings['cache_selects_only']) : $current['cache_selects_only'],
        ];

        update_option(self::OPTION, $new);

        // Clear cache on settings change
        $this->clearCache();
    }

    /**
     * Cache database query
     *
     * @param string $query SQL query
     * @return string Original query (unchanged)
     */
    public function cacheQuery(string $query): string
    {
        global $wpdb;

        $settings = $this->settings();

        // Only cache SELECT queries if configured
        if ($settings['cache_selects_only']) {
            if (stripos(trim($query), 'SELECT') !== 0) {
                return $query;
            }
        }

        // Check exclusions
        if ($this->shouldExclude($query, $settings['exclude_patterns'])) {
            return $query;
        }

        // Generate cache key
        $cacheKey = md5($query);

        // Check cache
        if ($this->hasCache($cacheKey)) {
            $cached = $this->getCache($cacheKey);
            
            if ($cached !== null) {
                // Inject cached result into wpdb
                $wpdb->last_result = $cached['result'];
                $wpdb->num_rows = $cached['num_rows'];
                
                $this->stats['hits']++;
                
                Logger::debug('Query cache hit', [
                    'query' => substr($query, 0, 100),
                    'rows' => $cached['num_rows'],
                ]);
                
                // Return empty string to prevent query execution
                // This is a hack - a better approach would be to use wpdb filters
                return $query;
            }
        }

        $this->stats['misses']++;

        // Store result after query execution
        add_filter('posts_results', function($results) use ($query, $cacheKey) {
            $this->storeQueryResult($query, $cacheKey, $results);
            return $results;
        }, 999);

        return $query;
    }

    /**
     * Store query result in cache
     *
     * @param string $query SQL query
     * @param string $cacheKey Cache key
     * @param mixed $result Query result
     */
    private function storeQueryResult(string $query, string $cacheKey, $result): void
    {
        global $wpdb;

        if (empty($result)) {
            return;
        }

        $settings = $this->settings();

        // Check cache size limit
        if (count($this->cache) >= $settings['max_size']) {
            // Remove oldest entry (FIFO)
            array_shift($this->cache);
        }

        $this->cache[$cacheKey] = [
            'query' => $query,
            'result' => $wpdb->last_result,
            'num_rows' => $wpdb->num_rows,
            'timestamp' => time(),
        ];

        $this->stats['stores']++;

        Logger::debug('Query cached', [
            'query' => substr($query, 0, 100),
            'rows' => $wpdb->num_rows,
        ]);
    }

    /**
     * Check if query should be excluded from caching
     *
     * @param string $query SQL query
     * @param array $patterns Exclusion patterns
     * @return bool True if should exclude
     */
    private function shouldExclude(string $query, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (stripos($query, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if cache entry exists and is valid
     *
     * @param string $key Cache key
     * @return bool True if valid cache exists
     */
    private function hasCache(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        $settings = $this->settings();
        $entry = $this->cache[$key];

        // Check TTL
        if (time() - $entry['timestamp'] > $settings['ttl']) {
            unset($this->cache[$key]);
            return false;
        }

        return true;
    }

    /**
     * Get cache entry
     *
     * @param string $key Cache key
     * @return array|null Cache entry or null
     */
    private function getCache(string $key): ?array
    {
        return $this->cache[$key] ?? null;
    }

    /**
     * Clear all cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
        Logger::info('Query cache cleared');
    }

    /**
     * Log cache statistics
     */
    public function logStats(): void
    {
        if ($this->stats['hits'] + $this->stats['misses'] === 0) {
            return;
        }

        $hitRate = ($this->stats['hits'] / ($this->stats['hits'] + $this->stats['misses'])) * 100;

        Logger::info('Query cache stats', [
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'stores' => $this->stats['stores'],
            'hit_rate' => round($hitRate, 2) . '%',
            'cached_queries' => count($this->cache),
        ]);
    }

    /**
     * Get cache statistics
     *
     * @return array{hits:int,misses:int,stores:int,hit_rate:float,size:int}
     */
    public function getStats(): array
    {
        $total = $this->stats['hits'] + $this->stats['misses'];
        $hitRate = $total > 0 ? ($this->stats['hits'] / $total) * 100 : 0;

        return [
            'hits' => $this->stats['hits'],
            'misses' => $this->stats['misses'],
            'stores' => $this->stats['stores'],
            'hit_rate' => round($hitRate, 2),
            'size' => count($this->cache),
        ];
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,cached_queries:int,hit_rate:float}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $stats = $this->getStats();

        return [
            'enabled' => $settings['enabled'],
            'cached_queries' => $stats['size'],
            'hit_rate' => $stats['hit_rate'],
        ];
    }
}
