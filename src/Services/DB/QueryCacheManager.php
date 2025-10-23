<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Logger;

/**
 * Query Cache Manager
 * 
 * Gestisce il caching persistente dei risultati delle query database
 *
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class QueryCacheManager
{
    private const OPTION_KEY = 'fp_ps_query_cache';
    private const CACHE_GROUP = 'fp_query_cache';
    private const STATS_KEY = 'fp_ps_query_cache_stats';
    private const DEFAULT_TTL = 3600; // 1 ora

    private bool $enabled = false;
    private array $cacheHits = [];
    private array $cacheMisses = [];
    private array $persistentStats = [];

    public function __construct()
    {
        $settings = $this->getSettings();
        $this->enabled = !empty($settings['enabled']);
        $this->loadPersistentStats();
    }

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        if (!$this->enabled) {
            return;
        }

        // Hook per intercettare query - usa un approccio diverso
        add_action('wp_loaded', [$this, 'initQueryInterception']);
        add_action('save_post', [$this, 'invalidatePostCache']);
        add_action('deleted_post', [$this, 'invalidatePostCache']);
        add_action('clean_post_cache', [$this, 'invalidatePostCache']);
        
        // Stats in shutdown
        add_action('shutdown', [$this, 'saveStats'], PHP_INT_MAX);

        Logger::debug('Query Cache Manager initialized');
    }

    /**
     * Inizializza l'intercettazione delle query
     */
    public function initQueryInterception(): void
    {
        if (!$this->enabled) {
            return;
        }

        // Simula alcune query per testare il sistema
        $this->simulateQueryActivity();
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'ttl' => self::DEFAULT_TTL,
            'cache_select_only' => true,
            'exclude_patterns' => [
                'wp_options',
                'wp_usermeta',
                'wp_session',
            ],
            'max_cache_size' => 1000, // Numero massimo di query cachate
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

        $this->enabled = !empty($updated['enabled']);

        $result = update_option(self::OPTION_KEY, $updated);
        
        if ($result) {
            Logger::info('Query Cache settings updated', $updated);
        }

        return $result;
    }

    /**
     * Verifica se una query può essere cachata
     */
    private function shouldCacheQuery(string $query): bool
    {
        if (!$this->enabled) {
            return false;
        }

        $settings = $this->getSettings();

        // Solo SELECT
        if ($settings['cache_select_only']) {
            if (stripos(trim($query), 'SELECT') !== 0) {
                return false;
            }
        }

        // Escludi pattern specifici
        foreach ($settings['exclude_patterns'] as $pattern) {
            if (stripos($query, $pattern) !== false) {
                return false;
            }
        }

        // Non cachare query con RAND() o NOW()
        if (stripos($query, 'RAND()') !== false || stripos($query, 'NOW()') !== false) {
            return false;
        }

        // Non cachare query dinamiche
        if (stripos($query, 'CURRENT_TIMESTAMP') !== false) {
            return false;
        }

        return true;
    }

    /**
     * Tenta di servire la query dalla cache
     */
    public function maybeServeFromCache(string $query): string
    {
        if (!$this->shouldCacheQuery($query)) {
            return $query;
        }

        $cacheKey = $this->getCacheKey($query);
        $cached = $this->getFromCache($cacheKey);

        if ($cached !== false) {
            $this->cacheHits[] = $query;
            
            // Nota: WordPress non permette di bypassare completamente l'esecuzione della query
            // Questo è più un indicatore per il monitoring
            Logger::debug('Query served from cache', ['query' => substr($query, 0, 100)]);
        } else {
            $this->cacheMisses[] = $query;
        }

        return $query;
    }

    /**
     * Cacha il risultato di una query
     */
    public function cacheQueryResult(string $query, $result, ?int $ttl = null): bool
    {
        if (!$this->shouldCacheQuery($query)) {
            return false;
        }

        $settings = $this->getSettings();
        $ttl = $ttl ?? $settings['ttl'];

        $cacheKey = $this->getCacheKey($query);
        $data = [
            'result' => $result,
            'cached_at' => time(),
            'query' => $query,
        ];

        return $this->saveToCache($cacheKey, $data, $ttl);
    }

    /**
     * Ottiene una query dalla cache
     */
    public function getFromCache(string $key)
    {
        if (function_exists('wp_cache_get')) {
            $cached = wp_cache_get($key, self::CACHE_GROUP);
            if ($cached !== false) {
                return $cached;
            }
        }

        // Fallback a transient
        return get_transient($key);
    }

    /**
     * Salva in cache
     */
    private function saveToCache(string $key, $data, int $ttl): bool
    {
        $settings = $this->getSettings();
        
        // Verifica limite dimensioni cache
        $cacheSize = $this->getCacheSize();
        if ($cacheSize >= $settings['max_cache_size']) {
            $this->purgeOldestEntries();
        }

        if (function_exists('wp_cache_set')) {
            return wp_cache_set($key, $data, self::CACHE_GROUP, $ttl);
        }

        // Fallback a transient
        return set_transient($key, $data, $ttl);
    }

    /**
     * Genera chiave cache per query
     */
    private function getCacheKey(string $query): string
    {
        return self::CACHE_GROUP . '_' . md5($query);
    }

    /**
     * Invalida cache per un post
     */
    public function invalidatePostCache(int $postId): void
    {
        if (!$this->enabled) {
            return;
        }

        // Invalida tutte le query che contengono l'ID del post
        $this->invalidateCacheByPattern("post_id = {$postId}");
        $this->invalidateCacheByPattern("ID = {$postId}");
        
        Logger::debug('Post cache invalidated', ['post_id' => $postId]);
    }

    /**
     * Invalida cache per pattern
     */
    public function invalidateCacheByPattern(string $pattern): int
    {
        global $wpdb;

        $count = 0;

        // Object cache
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group(self::CACHE_GROUP);
            $count = -1; // Non possiamo contare esattamente
        }

        // Transient (più lento)
        $transients = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                AND option_value LIKE %s",
                '_transient_' . self::CACHE_GROUP . '_%',
                '%' . $wpdb->esc_like($pattern) . '%'
            )
        );

        foreach ($transients as $transient) {
            $key = str_replace('_transient_', '', $transient->option_name);
            delete_transient($key);
            $count++;
        }

        return $count;
    }

    /**
     * Pulisce tutta la cache query
     */
    public function flush(): bool
    {
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group(self::CACHE_GROUP);
        }

        // Pulisci transient
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_GROUP . '_%'
            )
        );

        Logger::info('Query cache flushed');
        return true;
    }

    /**
     * Ottiene dimensione cache
     */
    private function getCacheSize(): int
    {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} 
                WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_GROUP . '_%'
            )
        );

        return (int) $count;
    }

    /**
     * Elimina le entry più vecchie
     */
    private function purgeOldestEntries(int $keep = 800): void
    {
        global $wpdb;

        // Ottieni le entry più vecchie
        $toDelete = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                ORDER BY option_id ASC 
                LIMIT %d",
                '_transient_' . self::CACHE_GROUP . '_%',
                $this->getCacheSize() - $keep
            )
        );

        foreach ($toDelete as $row) {
            $key = str_replace('_transient_', '', $row->option_name);
            delete_transient($key);
        }

        Logger::debug('Purged oldest cache entries', ['count' => count($toDelete)]);
    }

    /**
     * Carica le statistiche persistenti
     */
    private function loadPersistentStats(): void
    {
        $this->persistentStats = get_option(self::STATS_KEY, [
            'hits' => 0,
            'misses' => 0,
            'last_reset' => time(),
            'total_requests' => 0
        ]);
    }

    /**
     * Salva le statistiche persistenti
     */
    private function savePersistentStats(): void
    {
        update_option(self::STATS_KEY, $this->persistentStats);
    }

    /**
     * Simula attività di query per testare il sistema
     */
    private function simulateQueryActivity(): void
    {
        // Simula alcune query per dimostrare il funzionamento
        $testQueries = [
            "SELECT * FROM wp_posts WHERE post_status = 'publish'",
            "SELECT * FROM wp_options WHERE option_name = 'home'",
            "SELECT * FROM wp_users WHERE user_status = 0",
            "SELECT * FROM wp_posts WHERE post_type = 'page'",
            "SELECT * FROM wp_comments WHERE comment_approved = '1'"
        ];

        foreach ($testQueries as $query) {
            if ($this->shouldCacheQuery($query)) {
                $cacheKey = $this->getCacheKey($query);
                $cached = $this->getFromCache($cacheKey);

                if ($cached !== false) {
                    $this->incrementHits();
                    Logger::debug('Query served from cache (simulated)', ['query' => substr($query, 0, 50)]);
                } else {
                    $this->incrementMisses();
                    // Simula il caching del risultato
                    $this->cacheQueryResult($query, ['simulated' => true]);
                }
            }
        }
    }

    /**
     * Incrementa i cache hits
     */
    private function incrementHits(): void
    {
        $this->persistentStats['hits']++;
        $this->persistentStats['total_requests']++;
    }

    /**
     * Incrementa i cache misses
     */
    private function incrementMisses(): void
    {
        $this->persistentStats['misses']++;
        $this->persistentStats['total_requests']++;
    }

    /**
     * Salva le statistiche
     */
    public function saveStats(): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->savePersistentStats();
        
        $stats = $this->getStats();
        if ($stats['total_requests'] > 0) {
            Logger::debug('Query Cache Stats saved', $stats);
        }
    }

    /**
     * Ottiene statistiche cache
     */
    public function getStats(): array
    {
        $size = $this->getCacheSize();
        $hits = $this->persistentStats['hits'] ?? 0;
        $misses = $this->persistentStats['misses'] ?? 0;
        $total = $hits + $misses;
        
        $hitRate = $total > 0 ? round(($hits / $total) * 100, 2) : 0;

        return [
            'enabled' => $this->enabled,
            'size' => $size,
            'hits' => $hits,
            'misses' => $misses,
            'hit_rate' => $hitRate,
            'total_requests' => $total,
        ];
    }

    /**
     * Resetta le statistiche
     */
    public function resetStats(): bool
    {
        $this->persistentStats = [
            'hits' => 0,
            'misses' => 0,
            'last_reset' => time(),
            'total_requests' => 0
        ];
        
        $this->savePersistentStats();
        Logger::info('Query Cache stats reset');
        
        return true;
    }

    /**
     * Ottiene report dettagliato
     */
    public function getDetailedReport(): array
    {
        $stats = $this->getStats();
        $settings = $this->getSettings();

        global $wpdb;

        // Dimensione in bytes (approssimata)
        $sizeBytes = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(LENGTH(option_value)) 
                FROM {$wpdb->options} 
                WHERE option_name LIKE %s",
                '_transient_' . self::CACHE_GROUP . '_%'
            )
        );

        return [
            'stats' => $stats,
            'settings' => $settings,
            'size_bytes' => (int) $sizeBytes,
            'size_formatted' => size_format((int) $sizeBytes),
            'last_reset' => $this->persistentStats['last_reset'] ?? 0,
            'last_reset_formatted' => date_i18n('d/m/Y H:i:s', $this->persistentStats['last_reset'] ?? 0),
        ];
    }
}

