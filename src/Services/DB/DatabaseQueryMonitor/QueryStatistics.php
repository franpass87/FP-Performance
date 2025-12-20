<?php

namespace FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function get_option;
use function update_option;
use function time;
use function count;
use function round;

/**
 * Gestisce le statistiche delle query
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseQueryMonitor
 * @author Francesco Passeri
 */
class QueryStatistics
{
    private const OPTION_KEY = 'fp_ps_query_monitor';
    private QueryTracker $tracker;
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Costruttore
     * 
     * @param QueryTracker $tracker
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct(QueryTracker $tracker, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->tracker = $tracker;
        $this->optionsRepo = $optionsRepo;
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }

    /**
     * Ottiene le statistiche attuali
     */
    public function getStatistics(bool $enabled = true): array
    {
        global $wpdb;
        
        $queries = $this->tracker->getQueries();
        $slowQueries = $this->tracker->getSlowQueries();
        $totalQueryTime = $this->tracker->getTotalQueryTime();
        $duplicateCount = $this->tracker->getDuplicateCount();
        
        // Calcola il numero totale di query eseguite
        $totalQueries = 0;
        foreach ($queries as $queryData) {
            $totalQueries += $queryData['count'];
        }
        
        // Se non abbiamo query registrate, usa il contatore di WordPress
        if ($totalQueries === 0) {
            $totalQueries = $wpdb->num_queries ?? 0;
        }
        
        // Se ancora non abbiamo query, prova a recuperare dalle query salvate
        if ($totalQueries === 0) {
            $savedStats = $this->getOption(self::OPTION_KEY . '_stats', []);
            if (!empty($savedStats)) {
                return [
                    'total_queries' => $savedStats['total_queries'] ?? 0,
                    'slow_queries' => $savedStats['slow_queries'] ?? 0,
                    'duplicate_queries' => $savedStats['duplicate_queries'] ?? 0,
                    'total_query_time' => $savedStats['total_query_time'] ?? 0,
                ];
            }
        }
        
        $stats = [
            'total_queries' => $totalQueries,
            'slow_queries' => count($slowQueries),
            'duplicate_queries' => $duplicateCount,
            'total_query_time' => round($totalQueryTime, 4),
            'average_query_time' => $totalQueries > 0 ? round($totalQueryTime / $totalQueries, 4) : 0,
            'unique_queries' => count($queries),
            'queries' => $queries,
            'slow_queries_list' => $slowQueries,
            'timestamp' => time(),
            'enabled' => $enabled,
        ];
        
        // Salva le statistiche per la persistenza
        $this->saveStatistics($stats);
        
        return $stats;
    }

    /**
     * Salva le statistiche
     */
    public function saveStatistics(array $stats): void
    {
        $this->setOption(self::OPTION_KEY . '_stats', [
            'total_queries' => $stats['total_queries'] ?? 0,
            'slow_queries' => $stats['slow_queries'] ?? 0,
            'duplicate_queries' => $stats['duplicate_queries'] ?? 0,
            'total_query_time' => $stats['total_query_time'] ?? 0,
            'timestamp' => $stats['timestamp'] ?? time(),
        ], false);
    }
}
















