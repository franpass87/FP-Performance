<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Logger;

/**
 * Database Query Monitor
 * 
 * Monitora le query database in tempo reale e fornisce statistiche dettagliate
 * 
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class DatabaseQueryMonitor
{
    private const OPTION_KEY = 'fp_ps_query_monitor';
    private const SLOW_QUERY_THRESHOLD = 0.005; // 5ms
    
    private bool $isEnabled = false;
    private array $queries = [];
    private array $slowQueries = [];
    private float $totalQueryTime = 0;
    private int $duplicateCount = 0;
    
    public function __construct()
    {
        $settings = $this->getSettings();
        $this->isEnabled = !empty($settings['enabled']);
        
        // Abilita sempre il monitoraggio se non è esplicitamente disabilitato
        if (!$this->isEnabled) {
            $this->isEnabled = true; // Forza l'abilitazione per il debug
        }
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Abilita sempre il monitoraggio per il debug
        $this->isEnabled = true;
        
        // Abilita il logging delle query
        $this->enableQueryLogging();
        
        // Hook per intercettare le query usando il metodo corretto di WordPress
        add_filter('log_query_custom_data', [$this, 'interceptQueryData'], 10, 5);
        add_action('wpdb_query', [$this, 'trackQueryExecution'], 10, 2);
        add_action('shutdown', [$this, 'logStatistics'], PHP_INT_MAX);
        
        // Hook per salvare i log
        add_action('wp_footer', [$this, 'displayAdminBar'], 52);
        add_action('admin_footer', [$this, 'displayAdminBar'], PHP_INT_MAX);
        
        // Hook alternativo per intercettare le query
        add_action('init', [$this, 'startQueryMonitoring'], 1);
        
        // Hook per intercettare le query in modo più diretto
        add_action('wp_loaded', [$this, 'analyzeWordPressQueries'], 999);
    }
    
    /**
     * Intercetta i dati delle query per il logging
     */
    public function interceptQueryData(array $query_data, string $query, float $query_time, array $query_callstack, float $query_start): array
    {
        if (!$this->isEnabled) {
            return $query_data;
        }
        
        $queryHash = md5($query);
        $caller = $this->getQueryCaller($query_callstack);
        
        // Registra la query
        if (!isset($this->queries[$queryHash])) {
            $this->queries[$queryHash] = [
                'query' => $query,
                'caller' => $caller,
                'count' => 0,
                'total_time' => 0,
                'max_time' => 0,
                'min_time' => PHP_FLOAT_MAX,
            ];
        }
        
        // Aggiorna le statistiche
        $this->queries[$queryHash]['count']++;
        $this->queries[$queryHash]['total_time'] += $query_time;
        $this->queries[$queryHash]['max_time'] = max($this->queries[$queryHash]['max_time'], $query_time);
        $this->queries[$queryHash]['min_time'] = min($this->queries[$queryHash]['min_time'], $query_time);
        
        // Aggiorna il tempo totale
        $this->totalQueryTime += $query_time;
        
        // Query lenta?
        $settings = $this->getSettings();
        $threshold = $settings['slow_query_threshold'] ?? self::SLOW_QUERY_THRESHOLD;
        if ($query_time > $threshold) {
            $this->slowQueries[] = [
                'query' => $query,
                'time' => $query_time,
                'caller' => $caller,
                'timestamp' => time(),
            ];
        }
        
        // Query duplicate?
        if ($this->queries[$queryHash]['count'] > 1) {
            $this->duplicateCount++;
        }
        
        return $query_data;
    }
    
    /**
     * Traccia l'esecuzione delle query
     */
    public function trackQueryExecution(string $query, float $query_time): void
    {
        if (!$this->isEnabled) {
            return;
        }
        
        // Questo metodo viene chiamato dopo l'esecuzione di ogni query
        // Le statistiche sono già state aggiornate in interceptQueryData
    }
    
    /**
     * Traccia il tempo di esecuzione delle query (metodo legacy mantenuto per compatibilità)
     */
    public function trackQueryTime(string $query, float $executionTime): void
    {
        // Questo metodo è ora gestito da interceptQueryData
        // Mantenuto per compatibilità con codice esistente
    }
    
    /**
     * Ottiene le statistiche attuali
     */
    public function getStatistics(): array
    {
        global $wpdb;
        
        // Calcola il numero totale di query eseguite
        $totalQueries = 0;
        foreach ($this->queries as $queryData) {
            $totalQueries += $queryData['count'];
        }
        
        // Se non abbiamo query registrate, usa il contatore di WordPress
        if ($totalQueries === 0) {
            $totalQueries = $wpdb->num_queries ?? 0;
        }
        
        // Se ancora non abbiamo query, prova a recuperare dalle query salvate
        if ($totalQueries === 0) {
            $savedStats = get_option(self::OPTION_KEY . '_stats', []);
            if (!empty($savedStats)) {
                $totalQueries = $savedStats['total_queries'] ?? 0;
                $this->slowQueries = $savedStats['slow_queries'] ?? [];
                $this->duplicateCount = $savedStats['duplicate_queries'] ?? 0;
                $this->totalQueryTime = $savedStats['total_query_time'] ?? 0;
            }
        }
        
        $stats = [
            'total_queries' => $totalQueries,
            'slow_queries' => count($this->slowQueries),
            'duplicate_queries' => $this->duplicateCount,
            'total_query_time' => round($this->totalQueryTime, 4),
            'average_query_time' => $totalQueries > 0 ? round($this->totalQueryTime / $totalQueries, 4) : 0,
            'unique_queries' => count($this->queries),
            'queries' => $this->queries,
            'slow_queries_list' => $this->slowQueries,
            'timestamp' => time(),
            'enabled' => $this->isEnabled,
        ];
        
        // Salva le statistiche per la persistenza
        $this->saveStatistics($stats);
        
        return $stats;
    }
    
    /**
     * Analizza le query e fornisce raccomandazioni
     */
    public function analyzeAndRecommend(): array
    {
        $stats = $this->getStatistics();
        $recommendations = [];
        
        // Troppe query?
        if ($stats['total_queries'] > 100) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Numero elevato di query',
                'message' => sprintf(
                    'Rilevate %d query database. Questo può rallentare il caricamento della pagina.',
                    $stats['total_queries']
                ),
                'suggestions' => [
                    'Attiva Object Caching (Redis, Memcached o APCu)',
                    'Disabilita plugin non necessari',
                    'Usa un plugin di query monitoring come Query Monitor per identificare i plugin problematici',
                    'Considera l\'uso di lazy loading per i dati non essenziali',
                ],
                'priority' => $stats['total_queries'] > 150 ? 'high' : 'medium',
            ];
        }
        
        // Query lente?
        if ($stats['slow_queries'] > 0) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Query lente rilevate',
                'message' => sprintf(
                    'Rilevate %d query lente (>5ms). Potrebbero necessitare di ottimizzazione.',
                    $stats['slow_queries']
                ),
                'suggestions' => [
                    'Aggiungi indici alle tabelle del database',
                    'Ottimizza le query complesse con JOIN',
                    'Usa il query result caching',
                    'Considera l\'uso di transient per dati che cambiano raramente',
                ],
                'priority' => 'high',
            ];
        }
        
        // Query duplicate?
        if ($stats['duplicate_queries'] > 10) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Query duplicate rilevate',
                'message' => sprintf(
                    'Rilevate %d query duplicate. Indicano un problema di caching.',
                    $stats['duplicate_queries']
                ),
                'suggestions' => [
                    'Attiva Object Caching per ridurre le query ripetute',
                    'Verifica che i transient WordPress siano utilizzati correttamente',
                    'Identifica i plugin che eseguono query duplicate',
                ],
                'priority' => 'medium',
            ];
        }
        
        // Tempo totale query elevato?
        if ($stats['total_query_time'] > 0.5) {
            $recommendations[] = [
                'type' => 'critical',
                'title' => 'Tempo totale query elevato',
                'message' => sprintf(
                    'Il tempo totale delle query è %.2fs. Questo rallenta significativamente il sito.',
                    $stats['total_query_time']
                ),
                'suggestions' => [
                    'Ottimizza immediatamente il database',
                    'Attiva Object Caching',
                    'Considera l\'upgrade dell\'hosting a un server con database più performante',
                    'Usa un CDN per ridurre il carico sul database',
                ],
                'priority' => 'critical',
            ];
        }
        
        // Tutto OK
        if (empty($recommendations)) {
            $recommendations[] = [
                'type' => 'success',
                'title' => 'Performance database ottimale',
                'message' => 'Le query database sono ben ottimizzate.',
                'suggestions' => [],
                'priority' => 'info',
            ];
        }
        
        return [
            'statistics' => $stats,
            'recommendations' => $recommendations,
        ];
    }
    
    /**
     * Salva le statistiche nel database
     */
    public function logStatistics(): void
    {
        // Forza l'abilitazione per il debug
        $this->isEnabled = true;
        
        $analysis = $this->analyzeAndRecommend();
        
        // Salva sempre le statistiche per il debug
        update_option(self::OPTION_KEY . '_last_analysis', $analysis, false);
        
        // Salva le statistiche correnti
        $this->saveStatistics($analysis['statistics']);
        
        if (class_exists('FP\PerfSuite\Utils\Logger')) {
            Logger::debug('Query Monitor Statistics', $analysis['statistics']);
        }
    }
    
    /**
     * Salva le statistiche per la persistenza
     */
    private function saveStatistics(array $stats): void
    {
        // Salva le statistiche essenziali
        $essentialStats = [
            'total_queries' => $stats['total_queries'] ?? 0,
            'slow_queries' => $stats['slow_queries'] ?? 0,
            'duplicate_queries' => $stats['duplicate_queries'] ?? 0,
            'total_query_time' => $stats['total_query_time'] ?? 0,
            'timestamp' => time(),
        ];
        
        update_option(self::OPTION_KEY . '_stats', $essentialStats, false);
    }
    
    /**
     * Mostra le statistiche nella admin bar
     */
    public function displayAdminBar(): void
    {
        if (!$this->isEnabled || !current_user_can('manage_options')) {
            return;
        }
        
        global $wpdb;
        $stats = $this->getStatistics();
        
        echo '<div id="fp-query-monitor-stats" style="display: none;" data-stats="' . esc_attr(wp_json_encode($stats)) . '"></div>';
    }
    
    /**
     * Ottiene il caller di una query dal backtrace
     */
    private function getQueryCaller(array $backtrace): string
    {
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && !str_contains($trace['file'], 'wp-includes/wp-db.php')) {
                $file = str_replace(ABSPATH, '', $trace['file']);
                $line = $trace['line'] ?? '?';
                return sprintf('%s:%s', $file, $line);
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Abilita il logging delle query in WordPress
     */
    public function enableQueryLogging(): void
    {
        // Abilita sempre il logging per il debug
        $this->isEnabled = true;
        
        // Abilita il logging delle query in WordPress
        if (!defined('SAVEQUERIES')) {
            define('SAVEQUERIES', true);
        }
        
        // Abilita il logging personalizzato
        add_filter('log_query_custom_data', [$this, 'interceptQueryData'], 10, 5);
        
        // Forza l'abilitazione del logging delle query
        global $wpdb;
        if ($wpdb) {
            $wpdb->queries = [];
        }
    }
    
    /**
     * Avvia il monitoraggio delle query
     */
    public function startQueryMonitoring(): void
    {
        // Forza l'abilitazione per il debug
        $this->isEnabled = true;
        
        // Intercetta le query usando un approccio più diretto
        add_action('wp_loaded', [$this, 'analyzeWordPressQueries'], 999);
        
        // Hook per intercettare le query in tempo reale
        add_action('wp_loaded', [$this, 'forceQueryLogging'], 1);
    }
    
    /**
     * Forza il logging delle query
     */
    public function forceQueryLogging(): void
    {
        global $wpdb;
        
        // Forza l'abilitazione del logging
        if (!defined('SAVEQUERIES')) {
            define('SAVEQUERIES', true);
        }
        
        // Inizializza l'array delle query se non esiste
        if (!isset($wpdb->queries)) {
            $wpdb->queries = [];
        }
        
        // Hook per intercettare ogni query
        add_action('wp_loaded', [$this, 'trackAllQueries'], 1);
    }
    
    /**
     * Traccia tutte le query eseguite
     */
    public function trackAllQueries(): void
    {
        global $wpdb;
        
        if (empty($wpdb->queries)) {
            return;
        }
        
        $settings = $this->getSettings();
        $threshold = $settings['slow_query_threshold'] ?? self::SLOW_QUERY_THRESHOLD;
        
        foreach ($wpdb->queries as $query_data) {
            if (!is_array($query_data) || count($query_data) < 3) {
                continue;
            }
            
            $query = $query_data[0];
            $query_time = $query_data[1];
            $callstack = $query_data[2] ?? [];
            
            $queryHash = md5($query);
            $caller = $this->getQueryCaller($callstack);
            
            // Registra la query
            if (!isset($this->queries[$queryHash])) {
                $this->queries[$queryHash] = [
                    'query' => $query,
                    'caller' => $caller,
                    'count' => 0,
                    'total_time' => 0,
                    'max_time' => 0,
                    'min_time' => PHP_FLOAT_MAX,
                ];
            }
            
            // Aggiorna le statistiche
            $this->queries[$queryHash]['count']++;
            $this->queries[$queryHash]['total_time'] += $query_time;
            $this->queries[$queryHash]['max_time'] = max($this->queries[$queryHash]['max_time'], $query_time);
            $this->queries[$queryHash]['min_time'] = min($this->queries[$queryHash]['min_time'], $query_time);
            
            // Aggiorna il tempo totale
            $this->totalQueryTime += $query_time;
            
            // Query lenta?
            if ($query_time > $threshold) {
                $this->slowQueries[] = [
                    'query' => $query,
                    'time' => $query_time,
                    'caller' => $caller,
                    'timestamp' => time(),
                ];
            }
            
            // Query duplicate?
            if ($this->queries[$queryHash]['count'] > 1) {
                $this->duplicateCount++;
            }
        }
    }
    
    /**
     * Analizza le query di WordPress
     */
    public function analyzeWordPressQueries(): void
    {
        global $wpdb;
        
        // Forza l'abilitazione per il debug
        $this->isEnabled = true;
        
        if (!$wpdb->queries) {
            // Se non ci sono query, prova a forzare il logging
            if (!defined('SAVEQUERIES')) {
                define('SAVEQUERIES', true);
            }
            return;
        }
        
        $settings = $this->getSettings();
        $threshold = $settings['slow_query_threshold'] ?? self::SLOW_QUERY_THRESHOLD;
        
        foreach ($wpdb->queries as $query_data) {
            if (!is_array($query_data) || count($query_data) < 3) {
                continue;
            }
            
            $query = $query_data[0];
            $query_time = $query_data[1];
            $callstack = $query_data[2] ?? [];
            
            $queryHash = md5($query);
            $caller = $this->getQueryCaller($callstack);
            
            // Registra la query
            if (!isset($this->queries[$queryHash])) {
                $this->queries[$queryHash] = [
                    'query' => $query,
                    'caller' => $caller,
                    'count' => 0,
                    'total_time' => 0,
                    'max_time' => 0,
                    'min_time' => PHP_FLOAT_MAX,
                ];
            }
            
            // Aggiorna le statistiche
            $this->queries[$queryHash]['count']++;
            $this->queries[$queryHash]['total_time'] += $query_time;
            $this->queries[$queryHash]['max_time'] = max($this->queries[$queryHash]['max_time'], $query_time);
            $this->queries[$queryHash]['min_time'] = min($this->queries[$queryHash]['min_time'], $query_time);
            
            // Aggiorna il tempo totale
            $this->totalQueryTime += $query_time;
            
            // Query lenta?
            if ($query_time > $threshold) {
                $this->slowQueries[] = [
                    'query' => $query,
                    'time' => $query_time,
                    'caller' => $caller,
                    'timestamp' => time(),
                ];
            }
            
            // Query duplicate?
            if ($this->queries[$queryHash]['count'] > 1) {
                $this->duplicateCount++;
            }
        }
    }
    
    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'log_slow_queries' => true,
            'slow_query_threshold' => self::SLOW_QUERY_THRESHOLD,
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
        
        $this->isEnabled = !empty($updated['enabled']);
        
        return update_option(self::OPTION_KEY, $updated);
    }
    
    /**
     * Ottiene l'ultima analisi salvata
     */
    public function getLastAnalysis(): ?array
    {
        $analysis = get_option(self::OPTION_KEY . '_last_analysis');
        return is_array($analysis) ? $analysis : null;
    }
}

