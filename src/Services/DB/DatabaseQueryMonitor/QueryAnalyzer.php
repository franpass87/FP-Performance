<?php

namespace FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

/**
 * Analizza le query database
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseQueryMonitor
 * @author Francesco Passeri
 */
class QueryAnalyzer
{
    private QueryTracker $tracker;
    private float $slowQueryThreshold;

    public function __construct(QueryTracker $tracker, float $slowQueryThreshold = 0.005)
    {
        $this->tracker = $tracker;
        $this->slowQueryThreshold = $slowQueryThreshold;
    }

    /**
     * Analizza le query di $wpdb
     */
    public function analyzeWpdbQueries(): void
    {
        global $wpdb;
        
        if (!isset($wpdb->queries) || !is_array($wpdb->queries)) {
            return;
        }
        
        foreach ($wpdb->queries as $query_data) {
            if (!is_array($query_data) || count($query_data) < 3) {
                continue;
            }
            
            $query = $query_data[0];
            $query_time = $query_data[1];
            $callstack = $query_data[2] ?? [];
            
            $caller = $this->getQueryCaller($callstack);
            $this->tracker->trackQuery($query, $query_time, $caller);
        }
    }

    /**
     * Analizza le query di WordPress
     */
    public function analyzeWordPressQueries(): void
    {
        global $wpdb;
        
        if (!isset($wpdb->queries) || !is_array($wpdb->queries)) {
            return;
        }
        
        foreach ($wpdb->queries as $query_data) {
            if (!is_array($query_data) || count($query_data) < 3) {
                continue;
            }
            
            $query = $query_data[0];
            $query_time = $query_data[1];
            $callstack = $query_data[2] ?? [];
            
            $caller = $this->getQueryCaller($callstack);
            $this->tracker->trackQuery($query, $query_time, $caller);
        }
    }

    /**
     * Ottiene il chiamante della query dal backtrace
     */
    private function getQueryCaller(array $backtrace): string
    {
        if (empty($backtrace)) {
            return 'Unknown';
        }
        
        // Cerca il primo file che non sia wp-db.php o wp-includes
        foreach ($backtrace as $trace) {
            if (!isset($trace['file'])) {
                continue;
            }
            
            $file = $trace['file'];
            if (strpos($file, 'wp-db.php') === false && strpos($file, 'wp-includes') === false) {
                $function = $trace['function'] ?? 'unknown';
                $line = $trace['line'] ?? 0;
                return basename($file) . ':' . $function . ':' . $line;
            }
        }
        
        // Se non trovato, usa il primo elemento
        $first = $backtrace[0] ?? [];
        $function = $first['function'] ?? 'unknown';
        $file = $first['file'] ?? 'unknown';
        $line = $first['line'] ?? 0;
        
        return basename($file) . ':' . $function . ':' . $line;
    }
}
















