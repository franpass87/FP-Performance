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
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        if (!$this->isEnabled) {
            return;
        }
        
        // Hook per intercettare le query
        add_filter('query', [$this, 'interceptQuery']);
        add_action('shutdown', [$this, 'logStatistics'], PHP_INT_MAX);
        
        // Hook per salvare i log
        add_action('wp_footer', [$this, 'displayAdminBar'], PHP_INT_MAX);
        add_action('admin_footer', [$this, 'displayAdminBar'], PHP_INT_MAX);
    }
    
    /**
     * Intercetta e analizza ogni query
     */
    public function interceptQuery(string $query): string
    {
        if (!$this->isEnabled) {
            return $query;
        }
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $caller = $this->getQueryCaller($backtrace);
        
        $queryHash = md5($query);
        $startTime = microtime(true);
        
        // Registra la query
        $this->queries[$queryHash] = [
            'query' => $query,
            'caller' => $caller,
            'time' => $startTime,
            'count' => isset($this->queries[$queryHash]) ? $this->queries[$queryHash]['count'] + 1 : 1,
        ];
        
        // Registra query duplicate
        if (isset($this->queries[$queryHash]) && $this->queries[$queryHash]['count'] > 1) {
            $this->duplicateCount++;
        }
        
        return $query;
    }
    
    /**
     * Traccia il tempo di esecuzione delle query
     */
    public function trackQueryTime(string $query, float $executionTime): void
    {
        $queryHash = md5($query);
        
        if (isset($this->queries[$queryHash])) {
            $this->queries[$queryHash]['execution_time'] = $executionTime;
            $this->totalQueryTime += $executionTime;
            
            // Query lenta?
            if ($executionTime > self::SLOW_QUERY_THRESHOLD) {
                $this->slowQueries[] = [
                    'query' => $query,
                    'time' => $executionTime,
                    'caller' => $this->queries[$queryHash]['caller'],
                ];
            }
        }
    }
    
    /**
     * Ottiene le statistiche attuali
     */
    public function getStatistics(): array
    {
        global $wpdb;
        
        $totalQueries = $wpdb->num_queries ?? count($this->queries);
        
        return [
            'total_queries' => $totalQueries,
            'slow_queries' => count($this->slowQueries),
            'duplicate_queries' => $this->duplicateCount,
            'total_query_time' => $this->totalQueryTime,
            'average_query_time' => $totalQueries > 0 ? $this->totalQueryTime / $totalQueries : 0,
            'queries' => $this->queries,
            'slow_queries_list' => $this->slowQueries,
            'timestamp' => time(),
        ];
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
        if (!$this->isEnabled) {
            return;
        }
        
        $analysis = $this->analyzeAndRecommend();
        
        // Salva solo se ci sono problemi
        if (!empty($analysis['recommendations'])) {
            update_option(self::OPTION_KEY . '_last_analysis', $analysis, false);
        }
        
        Logger::debug('Query Monitor Statistics', $analysis['statistics']);
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

