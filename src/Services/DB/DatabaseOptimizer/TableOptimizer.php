<?php

namespace FP\PerfSuite\Services\DB\DatabaseOptimizer;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\HostingDetector;
use function get_transient;
use function set_transient;
use function time;
use function HOUR_IN_SECONDS;
use function MINUTE_IN_SECONDS;

/**
 * Ottimizza le tabelle del database
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseOptimizer
 * @author Francesco Passeri
 */
class TableOptimizer
{
    /**
     * Ottimizza il database
     */
    public function optimizeDatabase(): int
    {
        global $wpdb;
        
        // PROTEZIONE SHARED HOSTING: Rate limiting per evitare sovraccarichi
        $rate_limit_key = 'fp_ps_db_optimize_last_run';
        $last_run = get_transient($rate_limit_key);
        
        // Su shared hosting: max 1 volta/ora
        // Su VPS/Dedicated: max 1 volta/15 minuti
        $isShared = HostingDetector::isSharedHosting();
        $min_interval = $isShared ? HOUR_IN_SECONDS : (15 * MINUTE_IN_SECONDS);
        
        if ($last_run && (time() - $last_run) < $min_interval) {
            $remaining = $min_interval - (time() - $last_run);
            $remaining_min = ceil($remaining / 60);
            
            Logger::info(sprintf(
                'Database optimization skipped: rate limit active. Retry in %d minutes (Hosting: %s)',
                $remaining_min,
                $isShared ? 'Shared' : 'VPS/Dedicated'
            ));
            return 0;
        }
        
        $start_time = microtime(true);
        $optimized = 0;
        
        // Ottieni tutte le tabelle
        $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
        
        foreach ($tables as $table) {
            $table_name = $table[0];
            
            // Valida nome tabella per sicurezza
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
                continue;
            }
            
            try {
                $result = $wpdb->query("OPTIMIZE TABLE `{$table_name}`");
                
                if ($result !== false) {
                    $optimized++;
                }
            } catch (\Throwable $e) {
                Logger::error("Error optimizing table {$table_name}: " . $e->getMessage());
            }
        }
        
        // Aggiorna rate limit
        set_transient($rate_limit_key, time(), $min_interval);
        
        $duration = round(microtime(true) - $start_time, 2);
        Logger::info("Database optimization completed: {$optimized} tables optimized in {$duration}s");
        
        return $optimized;
    }

    /**
     * Ottimizza tutte le tabelle
     */
    public function optimizeAllTables(): array
    {
        global $wpdb;
        
        $results = [
            'success' => true,
            'optimized' => [],
            'errors' => [],
            'total' => 0,
            'duration' => 0,
        ];
        
        $start_time = microtime(true);
        
        try {
            // Ottieni tutte le tabelle
            $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
            
            if (empty($tables)) {
                $results['success'] = false;
                $results['errors']['general'] = 'Nessuna tabella trovata nel database';
                return $results;
            }
            
            $results['total'] = count($tables);
            
            // Ottimizza ogni tabella
            foreach ($tables as $table) {
                $table_name = $table[0];
                
                // Valida nome tabella per sicurezza
                if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
                    $results['errors'][$table_name] = 'Nome tabella non valido';
                    continue;
                }
                
                try {
                    // Esegui OPTIMIZE TABLE
                    $result = $wpdb->query("OPTIMIZE TABLE `{$table_name}`");
                    
                    if ($result !== false) {
                        $results['optimized'][] = $table_name;
                        Logger::info("Table {$table_name} ottimizzata con successo");
                    } else {
                        $results['errors'][$table_name] = $wpdb->last_error ?: 'Errore sconosciuto';
                        Logger::error("Errore ottimizzando {$table_name}: " . $wpdb->last_error);
                    }
                } catch (\Throwable $e) {
                    $results['errors'][$table_name] = $e->getMessage();
                    Logger::error("Eccezione ottimizzando {$table_name}: " . $e->getMessage());
                }
            }
            
            $results['duration'] = round(microtime(true) - $start_time, 2);
            
            Logger::info(sprintf(
                'optimizeAllTables completato: %d/%d tabelle ottimizzate in %ss',
                count($results['optimized']),
                $results['total'],
                $results['duration']
            ));
            
        } catch (\Throwable $e) {
            $results['success'] = false;
            $results['errors']['general'] = $e->getMessage();
            Logger::error('optimizeAllTables fallito: ' . $e->getMessage());
        }
        
        return $results;
    }
}















