<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Logger;

/**
 * Database Optimizer
 * 
 * Analizza e ottimizza le tabelle del database WordPress
 * 
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class DatabaseOptimizer
{
    private const OPTION_KEY = 'fp_ps_db_optimizer';
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Hook per ottimizzazione automatica
        add_action('fp_ps_db_auto_optimize', [$this, 'runAutoOptimization']);
    }
    
    /**
     * Analizza il database e fornisce raccomandazioni
     */
    public function analyze(): array
    {
        global $wpdb;
        
        $results = [
            'database_size' => $this->getDatabaseSize(),
            'table_analysis' => $this->analyzeTables(),
            'index_analysis' => $this->analyzeIndexes(),
            'query_cache_status' => $this->getQueryCacheStatus(),
            'recommendations' => [],
        ];
        
        // Genera raccomandazioni
        $results['recommendations'] = $this->generateRecommendations($results);
        
        return $results;
    }
    
    /**
     * Ottiene la dimensione del database
     */
    public function getDatabaseSize(): array
    {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT 
                SUM(data_length + index_length) as total_size,
                SUM(data_length) as data_size,
                SUM(index_length) as index_size,
                SUM(data_free) as free_size
             FROM information_schema.TABLES
             WHERE table_schema = %s",
            DB_NAME
        );
        
        $result = $wpdb->get_row($query, ARRAY_A);
        
        return [
            'total' => (int) ($result['total_size'] ?? 0),
            'data' => (int) ($result['data_size'] ?? 0),
            'index' => (int) ($result['index_size'] ?? 0),
            'free' => (int) ($result['free_size'] ?? 0),
            'total_mb' => round(($result['total_size'] ?? 0) / 1024 / 1024, 2),
        ];
    }
    
    /**
     * Analizza le tabelle del database
     */
    public function analyzeTables(): array
    {
        global $wpdb;
        
        $tables = $wpdb->get_results(
            "SHOW TABLE STATUS FROM `" . DB_NAME . "`",
            ARRAY_A
        );
        
        $analysis = [];
        $overhead = 0;
        
        foreach ($tables as $table) {
            $tableName = $table['Name'];
            $engine = $table['Engine'];
            $rows = (int) $table['Rows'];
            $dataLength = (int) $table['Data_length'];
            $indexLength = (int) $table['Index_length'];
            $dataFree = (int) ($table['Data_free'] ?? 0);
            
            $tableInfo = [
                'name' => $tableName,
                'engine' => $engine,
                'rows' => $rows,
                'size' => $dataLength + $indexLength,
                'size_mb' => round(($dataLength + $indexLength) / 1024 / 1024, 2),
                'data_free' => $dataFree,
                'data_free_mb' => round($dataFree / 1024 / 1024, 2),
                'needs_optimization' => false,
            ];
            
            // Tabella necessita ottimizzazione?
            if ($dataFree > 0 && $dataFree > ($dataLength * 0.1)) {
                $tableInfo['needs_optimization'] = true;
                $overhead += $dataFree;
            }
            
            // Tabella molto grande?
            if ($rows > 100000) {
                $tableInfo['warning'] = 'Tabella con molte righe - considera il cleanup periodico';
            }
            
            $analysis[] = $tableInfo;
        }
        
        return [
            'tables' => $analysis,
            'total_tables' => count($tables),
            'total_overhead' => $overhead,
            'total_overhead_mb' => round($overhead / 1024 / 1024, 2),
            'needs_optimization' => $overhead > 0,
        ];
    }
    
    /**
     * Analizza gli indici del database
     */
    public function analyzeIndexes(): array
    {
        global $wpdb;
        
        $tables = $wpdb->get_col("SHOW TABLES FROM `" . DB_NAME . "`");
        $indexAnalysis = [];
        
        foreach ($tables as $table) {
            $indexes = $wpdb->get_results(
                "SHOW INDEX FROM `{$table}`",
                ARRAY_A
            );
            
            $tableIndexes = [];
            foreach ($indexes as $index) {
                $keyName = $index['Key_name'];
                
                if (!isset($tableIndexes[$keyName])) {
                    $tableIndexes[$keyName] = [
                        'name' => $keyName,
                        'unique' => $index['Non_unique'] == 0,
                        'columns' => [],
                        'cardinality' => 0,
                    ];
                }
                
                $tableIndexes[$keyName]['columns'][] = $index['Column_name'];
                $tableIndexes[$keyName]['cardinality'] += (int) $index['Cardinality'];
            }
            
            if (!empty($tableIndexes)) {
                $indexAnalysis[$table] = array_values($tableIndexes);
            }
        }
        
        return [
            'indexes' => $indexAnalysis,
            'total_indexes' => array_sum(array_map('count', $indexAnalysis)),
        ];
    }
    
    /**
     * Ottiene lo stato della query cache
     */
    public function getQueryCacheStatus(): array
    {
        global $wpdb;
        
        $variables = $wpdb->get_results(
            "SHOW VARIABLES LIKE 'query_cache%'",
            ARRAY_A
        );
        
        $status = [];
        foreach ($variables as $var) {
            $status[$var['Variable_name']] = $var['Value'];
        }
        
        return $status;
    }
    
    /**
     * Genera raccomandazioni basate sull'analisi
     */
    private function generateRecommendations(array $analysis): array
    {
        $recommendations = [];
        
        // Database troppo grande?
        if ($analysis['database_size']['total_mb'] > 500) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Database di grandi dimensioni',
                'message' => sprintf(
                    'Il database ha una dimensione di %.2f MB. Considera operazioni di cleanup.',
                    $analysis['database_size']['total_mb']
                ),
                'actions' => [
                    'optimize_tables' => 'Ottimizza le tabelle',
                    'clean_revisions' => 'Pulisci le revisioni dei post',
                    'clean_transients' => 'Pulisci i transient scaduti',
                ],
            ];
        }
        
        // Overhead significativo?
        if ($analysis['table_analysis']['needs_optimization']) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'Tabelle frammentate',
                'message' => sprintf(
                    'Rilevati %.2f MB di overhead. L\'ottimizzazione delle tabelle può liberare spazio.',
                    $analysis['table_analysis']['total_overhead_mb']
                ),
                'actions' => [
                    'optimize_tables' => 'Ottimizza tutte le tabelle',
                ],
            ];
        }
        
        // Tabelle con molte righe?
        $largeTables = array_filter(
            $analysis['table_analysis']['tables'],
            fn($t) => $t['rows'] > 100000
        );
        
        if (!empty($largeTables)) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Tabelle con molte righe',
                'message' => sprintf(
                    '%d tabelle contengono più di 100.000 righe.',
                    count($largeTables)
                ),
                'tables' => array_column($largeTables, 'name'),
                'actions' => [
                    'review_tables' => 'Rivedi le tabelle e considera il cleanup',
                ],
            ];
        }
        
        // Query cache disabilitata?
        $queryCacheType = $analysis['query_cache_status']['query_cache_type'] ?? 'OFF';
        if (strtoupper($queryCacheType) === 'OFF') {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'Query Cache disabilitata',
                'message' => 'La Query Cache di MySQL non è attiva. Considera l\'uso di Object Caching.',
                'actions' => [
                    'enable_object_cache' => 'Attiva Object Caching',
                ],
            ];
        }
        
        return $recommendations;
    }
    
    /**
     * Ottimizza una singola tabella
     */
    public function optimizeTable(string $tableName): array
    {
        global $wpdb;
        
        // Verifica che la tabella esista
        $tables = $wpdb->get_col("SHOW TABLES FROM `" . DB_NAME . "`");
        if (!in_array($tableName, $tables, true)) {
            return [
                'success' => false,
                'message' => 'Tabella non trovata',
            ];
        }
        
        // Usa get_results() invece di query() perché OPTIMIZE TABLE restituisce un result set
        $result = $wpdb->get_results("OPTIMIZE TABLE `{$tableName}`", ARRAY_A);
        
        if ($result === false || !empty($wpdb->last_error)) {
            return [
                'success' => false,
                'message' => 'Errore durante l\'ottimizzazione',
                'error' => $wpdb->last_error,
            ];
        }
        
        // Verifica il risultato dell'ottimizzazione
        $success = true;
        foreach ($result as $row) {
            if (isset($row['Msg_type']) && $row['Msg_type'] === 'error') {
                $success = false;
                break;
            }
        }
        
        if (!$success) {
            return [
                'success' => false,
                'message' => 'Errore durante l\'ottimizzazione della tabella',
                'error' => isset($row['Msg_text']) ? $row['Msg_text'] : 'Unknown error',
            ];
        }
        
        Logger::info('Table optimized', ['table' => $tableName, 'result' => $result]);
        
        return [
            'success' => true,
            'message' => 'Tabella ottimizzata con successo',
            'table' => $tableName,
            'details' => $result,
        ];
    }
    
    /**
     * Ottimizza tutte le tabelle
     */
    public function optimizeAllTables(): array
    {
        global $wpdb;
        
        $tables = $wpdb->get_col("SHOW TABLES FROM `" . DB_NAME . "`");
        $results = [];
        $errors = [];
        
        foreach ($tables as $table) {
            $result = $this->optimizeTable($table);
            
            if ($result['success']) {
                $results[] = $table;
            } else {
                $errors[$table] = $result['message'];
            }
        }
        
        Logger::info('All tables optimized', [
            'optimized' => count($results),
            'errors' => count($errors),
        ]);
        
        return [
            'success' => empty($errors),
            'optimized' => $results,
            'errors' => $errors,
            'total' => count($tables),
        ];
    }
    
    /**
     * Ripara una tabella corrotta
     */
    public function repairTable(string $tableName): array
    {
        global $wpdb;
        
        $result = $wpdb->query("REPAIR TABLE `{$tableName}`");
        
        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Errore durante la riparazione',
                'error' => $wpdb->last_error,
            ];
        }
        
        Logger::info('Table repaired', ['table' => $tableName]);
        
        return [
            'success' => true,
            'message' => 'Tabella riparata con successo',
            'table' => $tableName,
        ];
    }
    
    /**
     * Controlla le tabelle per errori
     */
    public function checkTables(): array
    {
        global $wpdb;
        
        $tables = $wpdb->get_col("SHOW TABLES FROM `" . DB_NAME . "`");
        $corruptedTables = [];
        
        foreach ($tables as $table) {
            $result = $wpdb->get_results("CHECK TABLE `{$table}`", ARRAY_A);
            
            foreach ($result as $row) {
                if (isset($row['Msg_type']) && $row['Msg_type'] === 'error') {
                    $corruptedTables[] = [
                        'table' => $table,
                        'message' => $row['Msg_text'] ?? 'Unknown error',
                    ];
                }
            }
        }
        
        return [
            'checked' => count($tables),
            'corrupted' => $corruptedTables,
            'has_errors' => !empty($corruptedTables),
        ];
    }
    
    /**
     * Ottimizza gli autoload options
     */
    public function optimizeAutoloadOptions(): array
    {
        global $wpdb;
        
        // Trova opzioni autoload grandi
        $query = "
            SELECT option_name, LENGTH(option_value) as size
            FROM {$wpdb->options}
            WHERE autoload = 'yes'
            ORDER BY size DESC
            LIMIT 50
        ";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        $totalSize = 0;
        $largeOptions = [];
        
        foreach ($results as $row) {
            $size = (int) $row['size'];
            $totalSize += $size;
            
            // Opzioni > 100KB
            if ($size > 102400) {
                $largeOptions[] = [
                    'name' => $row['option_name'],
                    'size' => $size,
                    'size_kb' => round($size / 1024, 2),
                ];
            }
        }
        
        return [
            'total_autoload_size' => $totalSize,
            'total_autoload_size_mb' => round($totalSize / 1024 / 1024, 2),
            'large_options' => $largeOptions,
            'recommendation' => $totalSize > 1048576 ? 'Considera di disabilitare autoload per opzioni grandi' : null,
        ];
    }
    
    /**
     * Esegue ottimizzazione automatica
     */
    public function runAutoOptimization(): void
    {
        $settings = $this->getSettings();
        
        if (empty($settings['auto_optimize'])) {
            return;
        }
        
        Logger::info('Running auto optimization');
        
        // Ottimizza tabelle con overhead
        $analysis = $this->analyzeTables();
        
        if ($analysis['needs_optimization']) {
            $this->optimizeAllTables();
        }
        
        Logger::info('Auto optimization completed');
    }
    
    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'auto_optimize' => false,
            'optimize_schedule' => 'weekly',
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
        
        // Schedule/unschedule auto optimization
        if ($updated['auto_optimize']) {
            $this->scheduleAutoOptimization($updated['optimize_schedule']);
        } else {
            $this->unscheduleAutoOptimization();
        }
        
        return update_option(self::OPTION_KEY, $updated);
    }
    
    /**
     * Pianifica ottimizzazione automatica
     */
    private function scheduleAutoOptimization(string $schedule): void
    {
        if (!wp_next_scheduled('fp_ps_db_auto_optimize')) {
            wp_schedule_event(time(), $schedule, 'fp_ps_db_auto_optimize');
        }
    }
    
    /**
     * Rimuovi pianificazione ottimizzazione automatica
     */
    private function unscheduleAutoOptimization(): void
    {
        $timestamp = wp_next_scheduled('fp_ps_db_auto_optimize');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'fp_ps_db_auto_optimize');
        }
    }
}

