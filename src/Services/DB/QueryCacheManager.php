<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class QueryCacheManager
{
    private const OPTION_KEY = 'fp_ps_query_cache_settings';
    private const STATS_KEY = 'fp_ps_query_cache_stats';
    
    private $ttl;
    private $cache_enabled;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct($ttl = 3600, $cache_enabled = true, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        
        // Carica le impostazioni salvate
        $savedSettings = $this->getOption(self::OPTION_KEY, []);
        
        $this->ttl = $savedSettings['ttl'] ?? $ttl;
        $this->cache_enabled = $savedSettings['enabled'] ?? $cache_enabled;
    }
    
    /**
     * Helper method per ottenere opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = [])
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option($key, $default);
    }
    
    /**
     * Helper method per salvare opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $value Value to save
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set($key, $value);
            return true;
        }
        
        // Fallback to direct option call for backward compatibility
        return update_option($key, $value, false);
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
            // Cache HIT - incrementa il contatore
            $this->incrementHits();
            return $cached_posts;
        }
        
        // Cache MISS - incrementa il contatore
        $this->incrementMisses();
        
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
     * Restituisce le impostazioni della query cache
     * 
     * @return array Array con le impostazioni
     */
    /**
     * Ottiene le impostazioni della query cache
     */
    public function getSettings(): array
    {
        $savedSettings = $this->getOption(self::OPTION_KEY, []);
        
        return [
            'enabled' => $savedSettings['enabled'] ?? $this->cache_enabled,
            'ttl' => $savedSettings['ttl'] ?? $this->ttl,
            'cache_select_only' => $savedSettings['cache_select_only'] ?? true,
            'max_cache_size' => $savedSettings['max_cache_size'] ?? 1000,
        ];
    }
    
    /**
     * Aggiorna le impostazioni della query cache
     */
    public function updateSettings(array $settings): bool
    {
        $currentSettings = $this->getOption(self::OPTION_KEY, []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['enabled'])) {
            $newSettings['enabled'] = (bool) $newSettings['enabled'];
        }
        
        if (isset($newSettings['ttl'])) {
            $newSettings['ttl'] = max(60, (int) $newSettings['ttl']);
        }
        
        $result = $this->setOption(self::OPTION_KEY, $newSettings);
        
        if ($result) {
            $this->cache_enabled = $newSettings['enabled'] ?? $this->cache_enabled;
            $this->ttl = $newSettings['ttl'] ?? $this->ttl;
        }
        
        return $result;
    }
    
    /**
     * Restituisce le statistiche della query cache
     * 
     * @return array Array con le statistiche
     */
    /**
     * Incrementa il contatore di cache hits
     */
    private function incrementHits(): void
    {
        $stats = $this->getOption(self::STATS_KEY, ['hits' => 0, 'misses' => 0]);
        $stats['hits'] = ($stats['hits'] ?? 0) + 1;
        $this->setOption(self::STATS_KEY, $stats);
    }
    
    /**
     * Incrementa il contatore di cache misses
     */
    private function incrementMisses(): void
    {
        $stats = $this->getOption(self::STATS_KEY, ['hits' => 0, 'misses' => 0]);
        $stats['misses'] = ($stats['misses'] ?? 0) + 1;
        $this->setOption(self::STATS_KEY, $stats);
    }
    
    /**
     * Ottiene le statistiche della cache
     */
    public function getStats(): array
    {
        $stats = $this->getOption(self::STATS_KEY, ['hits' => 0, 'misses' => 0]);
        $hits = (int) ($stats['hits'] ?? 0);
        $misses = (int) ($stats['misses'] ?? 0);
        $total = $hits + $misses;
        
        $hitRate = $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        
        return [
            'hits' => $hits,
            'misses' => $misses,
            'hit_rate' => $hitRate,
            'total' => $total,
        ];
    }
    
    /**
     * Resetta le statistiche della cache
     */
    public function resetStats(): bool
    {
        return $this->setOption(self::STATS_KEY, ['hits' => 0, 'misses' => 0]);
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}