<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor\QueryTracker;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor\QueryAnalyzer;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor\QueryStatistics;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor\QueryRecommendations;

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
    private QueryTracker $tracker;
    private QueryAnalyzer $analyzer;
    private QueryStatistics $statistics;
    private QueryRecommendations $recommendations;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $settings = $this->getSettings();
        $this->isEnabled = !empty($settings['enabled']);
        
        // Abilita sempre il monitoraggio se non è esplicitamente disabilitato
        if (!$this->isEnabled) {
            $this->isEnabled = true; // Forza l'abilitazione per il debug
        }
        
        $slowQueryThreshold = $settings['slow_query_threshold'] ?? self::SLOW_QUERY_THRESHOLD;
        $this->tracker = new QueryTracker($slowQueryThreshold);
        $this->analyzer = new QueryAnalyzer($this->tracker, $slowQueryThreshold);
        $this->statistics = new QueryStatistics($this->tracker, $this->optionsRepo);
        $this->recommendations = new QueryRecommendations();
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
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Disabilita monitoring in production se non WP_DEBUG
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            $this->isEnabled = false;
            return;
        }
        
        $this->isEnabled = true;
        
        // Abilita il logging delle query solo se SAVEQUERIES è definito
        if (defined('SAVEQUERIES') && SAVEQUERIES) {
            $this->enableQueryLogging();
        }
        
        // Hook per intercettare le query usando il metodo corretto di WordPress
        add_filter('log_query_custom_data', [$this, 'interceptQueryData'], 10, 5);
        add_action('wpdb_query', [$this, 'trackQueryExecution'], 10, 2);
        add_action('shutdown', [$this, 'logStatistics'], PHP_INT_MAX);
        
        // Hook per salvare i log - solo nel frontend
        if (!is_admin()) {
            add_action('wp_footer', [$this, 'displayAdminBar'], 52);
        }
        
        // Hook alternativo per intercettare le query - solo nel frontend
        if (!is_admin()) {
            add_action('init', [$this, 'startQueryMonitoring'], 1);
        }
        
        // Hook per intercettare le query in modo più diretto - solo nel frontend
        if (!is_admin()) {
            add_action('wp_loaded', function() {
                $this->analyzer->analyzeWordPressQueries();
            }, 999);
        }
    }
    
    /**
     * Intercetta i dati delle query per il logging
     */
    public function interceptQueryData(array $query_data, string $query, float $query_time, array $query_callstack, float $query_start): array
    {
        if (!$this->isEnabled) {
            return $query_data;
        }
        
        $caller = $this->analyzer->getQueryCaller($query_callstack);
        $this->tracker->trackQuery($query, $query_time, $caller);
        
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
        
        // Forza l'abilitazione di SAVEQUERIES se non è già attivo
        if (!defined('SAVEQUERIES') || !SAVEQUERIES) {
            // Analizza le query già eseguite da $wpdb
            if (isset($wpdb->queries) && is_array($wpdb->queries) && !empty($wpdb->queries)) {
                $this->analyzer->analyzeWpdbQueries();
            }
        } else {
            // Analizza anche $wpdb->queries se disponibile
            if (isset($wpdb->queries) && is_array($wpdb->queries)) {
                $this->analyzer->analyzeWpdbQueries();
            }
        }
        
        return $this->statistics->getStatistics($this->isEnabled);
    }
    
    /**
     * Analizza le query e fornisce raccomandazioni
     */
    public function analyzeAndRecommend(): array
    {
        $stats = $this->getStatistics();
        return $this->recommendations->analyzeAndRecommend($stats);
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
        $this->setOption(self::OPTION_KEY . '_last_analysis', $analysis);
        
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
        
        $this->setOption(self::OPTION_KEY . '_stats', $essentialStats);
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
    
    // Metodo getQueryCaller() rimosso - ora gestito da QueryAnalyzer
    
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
        $this->analyzer->analyzeWpdbQueries();
    }
    
    // Metodo analyzeWpdbQueries() rimosso - ora gestito da QueryAnalyzer
    
    /**
     * Analizza le query di WordPress
     */
    public function analyzeWordPressQueries(): void
    {
        global $wpdb;
        
        // Forza l'abilitazione per il debug
        $this->isEnabled = true;
        
        if (!isset($wpdb->queries) || !is_array($wpdb->queries) || empty($wpdb->queries)) {
            // Se non ci sono query, prova a forzare il logging
            if (!defined('SAVEQUERIES')) {
                define('SAVEQUERIES', true);
            }
            return;
        }
        
        $this->analyzer->analyzeWordPressQueries();
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
        
        $options = $this->getOption(self::OPTION_KEY, []);
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
        
        return $this->setOption(self::OPTION_KEY, $updated);
    }
    
    /**
     * Ottiene l'ultima analisi salvata
     */
    public function getLastAnalysis(): ?array
    {
        $analysis = $this->getOption(self::OPTION_KEY . '_last_analysis', null);
        return is_array($analysis) ? $analysis : null;
    }
}

