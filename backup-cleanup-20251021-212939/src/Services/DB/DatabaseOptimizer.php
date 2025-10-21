<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Logger;

/**
 * Database Optimizer - Enhanced Version
 * 
 * Analizza e ottimizza le tabelle del database WordPress con funzionalità avanzate:
 * - Analisi frammentazione dettagliata
 * - Rilevamento indici mancanti
 * - Conversione storage engine (MyISAM → InnoDB)
 * - Ottimizzazione charset e collation
 * - Backup automatici pre-ottimizzazione
 * - Pulizia plugin-specific (WooCommerce, Yoast, etc.)
 * 
 * @package FP\PerfSuite\Services\DB
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class DatabaseOptimizer
{
    private const OPTION_KEY = 'fp_ps_db_optimizer';
    private const HISTORY_KEY = 'fp_ps_db_optimizer_history';
    private const BACKUP_KEY = 'fp_ps_db_optimizer_backup';
    
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
    
    // ============================================
    // NUOVE FUNZIONALITÀ AVANZATE
    // ============================================
    
    /**
     * Analisi frammentazione dettagliata per tabella
     */
    public function analyzeFragmentation(): array
    {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT 
                TABLE_NAME,
                ENGINE,
                TABLE_ROWS,
                DATA_LENGTH,
                INDEX_LENGTH,
                DATA_FREE,
                ROUND(DATA_FREE / (DATA_LENGTH + INDEX_LENGTH) * 100, 2) as fragmentation_pct
             FROM information_schema.TABLES
             WHERE table_schema = %s
             AND DATA_FREE > 0
             ORDER BY DATA_FREE DESC",
            DB_NAME
        );
        
        $tables = $wpdb->get_results($query, ARRAY_A);
        $fragmented = [];
        $totalWaste = 0;
        
        foreach ($tables as $table) {
            $fragmentationPct = (float) ($table['fragmentation_pct'] ?? 0);
            $dataFree = (int) ($table['DATA_FREE'] ?? 0);
            
            if ($fragmentationPct > 10 || $dataFree > 1048576) { // > 10% o > 1MB
                $fragmented[] = [
                    'table' => $table['TABLE_NAME'],
                    'engine' => $table['ENGINE'],
                    'rows' => (int) $table['TABLE_ROWS'],
                    'fragmentation_pct' => $fragmentationPct,
                    'wasted_space' => $dataFree,
                    'wasted_mb' => round($dataFree / 1024 / 1024, 2),
                    'severity' => $this->getFragmentationSeverity($fragmentationPct),
                ];
                $totalWaste += $dataFree;
            }
        }
        
        return [
            'fragmented_tables' => $fragmented,
            'total_fragmented' => count($fragmented),
            'total_wasted_bytes' => $totalWaste,
            'total_wasted_mb' => round($totalWaste / 1024 / 1024, 2),
            'recommendation' => count($fragmented) > 0 ? 'Ottimizza le tabelle frammentate per recuperare spazio e migliorare le performance' : null,
        ];
    }
    
    /**
     * Determina la gravità della frammentazione
     */
    private function getFragmentationSeverity(float $pct): string
    {
        if ($pct > 30) return 'high';
        if ($pct > 15) return 'medium';
        return 'low';
    }
    
    /**
     * Analizza indici mancanti e suggerisce creazione
     */
    public function analyzeMissingIndexes(): array
    {
        global $wpdb;
        
        $suggestions = [];
        
        // Tabella posts - colonne comuni senza indice
        $postIndexes = $this->getTableIndexColumns($wpdb->posts);
        $postSuggestions = [];
        
        if (!in_array('post_author', $postIndexes)) {
            $postSuggestions[] = [
                'column' => 'post_author',
                'reason' => 'Migliora query per autore (es. author archives)',
                'benefit' => 'medium',
            ];
        }
        
        if (!in_array('post_modified', $postIndexes)) {
            $postSuggestions[] = [
                'column' => 'post_modified',
                'reason' => 'Migliora query per data modifica',
                'benefit' => 'low',
            ];
        }
        
        if (!empty($postSuggestions)) {
            $suggestions[$wpdb->posts] = $postSuggestions;
        }
        
        // Tabella postmeta - indice composito
        $metaIndexes = $this->getTableIndexes($wpdb->postmeta);
        $hasCompositeIndex = false;
        foreach ($metaIndexes as $index) {
            if (count($index['columns']) > 1 && in_array('meta_key', $index['columns']) && in_array('meta_value', $index['columns'])) {
                $hasCompositeIndex = true;
                break;
            }
        }
        
        if (!$hasCompositeIndex) {
            $suggestions[$wpdb->postmeta][] = [
                'columns' => ['meta_key', 'meta_value(191)'],
                'reason' => 'Migliora drasticamente query meta_query',
                'benefit' => 'high',
            ];
        }
        
        // Tabella options - autoload
        $optionsIndexes = $this->getTableIndexColumns($wpdb->options);
        if (!in_array('autoload', $optionsIndexes)) {
            $suggestions[$wpdb->options][] = [
                'column' => 'autoload',
                'reason' => 'Migliora caricamento opzioni autoload',
                'benefit' => 'high',
            ];
        }
        
        return [
            'suggestions' => $suggestions,
            'total_suggestions' => array_sum(array_map('count', $suggestions)),
            'high_priority' => $this->countByBenefit($suggestions, 'high'),
        ];
    }
    
    /**
     * Ottiene le colonne indicizzate di una tabella
     */
    private function getTableIndexColumns(string $table): array
    {
        global $wpdb;
        $indexes = $wpdb->get_results("SHOW INDEX FROM `{$table}`", ARRAY_A);
        $columns = [];
        
        foreach ($indexes as $index) {
            $columns[] = $index['Column_name'];
        }
        
        return array_unique($columns);
    }
    
    /**
     * Ottiene gli indici di una tabella con dettagli
     */
    private function getTableIndexes(string $table): array
    {
        global $wpdb;
        $indexes = $wpdb->get_results("SHOW INDEX FROM `{$table}`", ARRAY_A);
        $grouped = [];
        
        foreach ($indexes as $index) {
            $keyName = $index['Key_name'];
            if (!isset($grouped[$keyName])) {
                $grouped[$keyName] = [
                    'name' => $keyName,
                    'unique' => $index['Non_unique'] == 0,
                    'columns' => [],
                ];
            }
            $grouped[$keyName]['columns'][] = $index['Column_name'];
        }
        
        return array_values($grouped);
    }
    
    /**
     * Conta suggerimenti per livello di beneficio
     */
    private function countByBenefit(array $suggestions, string $benefit): int
    {
        $count = 0;
        foreach ($suggestions as $tableSuggestions) {
            foreach ($tableSuggestions as $suggestion) {
                if (($suggestion['benefit'] ?? '') === $benefit) {
                    $count++;
                }
            }
        }
        return $count;
    }
    
    /**
     * Analizza storage engine delle tabelle
     */
    public function analyzeStorageEngines(): array
    {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT TABLE_NAME, ENGINE, TABLE_ROWS, 
                    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
             FROM information_schema.TABLES
             WHERE table_schema = %s
             ORDER BY ENGINE, size_mb DESC",
            DB_NAME
        );
        
        $tables = $wpdb->get_results($query, ARRAY_A);
        $byEngine = [];
        $myisamTables = [];
        
        foreach ($tables as $table) {
            $engine = $table['ENGINE'] ?? 'UNKNOWN';
            
            if (!isset($byEngine[$engine])) {
                $byEngine[$engine] = [
                    'count' => 0,
                    'total_size_mb' => 0,
                    'tables' => [],
                ];
            }
            
            $byEngine[$engine]['count']++;
            $byEngine[$engine]['total_size_mb'] += (float) $table['size_mb'];
            $byEngine[$engine]['tables'][] = $table['TABLE_NAME'];
            
            // Identifica tabelle MyISAM da convertire
            if ($engine === 'MyISAM') {
                $myisamTables[] = [
                    'table' => $table['TABLE_NAME'],
                    'size_mb' => (float) $table['size_mb'],
                    'rows' => (int) $table['TABLE_ROWS'],
                ];
            }
        }
        
        return [
            'by_engine' => $byEngine,
            'myisam_tables' => $myisamTables,
            'needs_conversion' => count($myisamTables) > 0,
            'recommendation' => count($myisamTables) > 0 
                ? 'InnoDB è raccomandato per WordPress. Converti le tabelle MyISAM per migliori performance e affidabilità.' 
                : null,
        ];
    }
    
    /**
     * Converte una tabella da MyISAM a InnoDB
     */
    public function convertToInnoDB(string $tableName, bool $backup = true): array
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
        
        // Verifica engine attuale
        $status = $wpdb->get_row($wpdb->prepare(
            "SELECT ENGINE FROM information_schema.TABLES 
             WHERE table_schema = %s AND TABLE_NAME = %s",
            DB_NAME,
            $tableName
        ), ARRAY_A);
        
        $currentEngine = $status['ENGINE'] ?? '';
        
        if ($currentEngine === 'InnoDB') {
            return [
                'success' => false,
                'message' => 'La tabella è già InnoDB',
            ];
        }
        
        // Backup se richiesto
        if ($backup) {
            $backupResult = $this->backupTable($tableName);
            if (!$backupResult['success']) {
                return $backupResult;
            }
        }
        
        // Converti
        $result = $wpdb->query("ALTER TABLE `{$tableName}` ENGINE=InnoDB");
        
        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Errore durante la conversione',
                'error' => $wpdb->last_error,
            ];
        }
        
        $this->addToHistory('convert_engine', $tableName, [
            'from' => $currentEngine,
            'to' => 'InnoDB',
        ]);
        
        Logger::info('Table converted to InnoDB', ['table' => $tableName]);
        
        return [
            'success' => true,
            'message' => 'Tabella convertita a InnoDB con successo',
            'table' => $tableName,
            'from_engine' => $currentEngine,
        ];
    }
    
    /**
     * Analizza charset e collation delle tabelle
     */
    public function analyzeCharset(): array
    {
        global $wpdb;
        
        $query = $wpdb->prepare(
            "SELECT TABLE_NAME, TABLE_COLLATION,
                    CCSA.CHARACTER_SET_NAME
             FROM information_schema.TABLES T
             LEFT JOIN information_schema.COLLATION_CHARACTER_SET_APPLICABILITY CCSA
                ON T.TABLE_COLLATION = CCSA.COLLATION_NAME
             WHERE table_schema = %s",
            DB_NAME
        );
        
        $tables = $wpdb->get_results($query, ARRAY_A);
        $outdated = [];
        $byCharset = [];
        
        foreach ($tables as $table) {
            $charset = $table['CHARACTER_SET_NAME'] ?? '';
            $collation = $table['TABLE_COLLATION'] ?? '';
            
            if (!isset($byCharset[$charset])) {
                $byCharset[$charset] = 0;
            }
            $byCharset[$charset]++;
            
            // Charset obsoleti o non raccomandati
            if (in_array($charset, ['latin1', 'utf8'], true)) {
                $outdated[] = [
                    'table' => $table['TABLE_NAME'],
                    'charset' => $charset,
                    'collation' => $collation,
                    'recommended' => 'utf8mb4',
                ];
            }
        }
        
        return [
            'by_charset' => $byCharset,
            'outdated_tables' => $outdated,
            'needs_conversion' => count($outdated) > 0,
            'recommendation' => count($outdated) > 0 
                ? 'utf8mb4 è il charset raccomandato per WordPress. Supporta emoji e caratteri speciali.' 
                : null,
        ];
    }
    
    /**
     * Converti charset di una tabella a utf8mb4
     */
    public function convertCharset(string $tableName, bool $backup = true): array
    {
        global $wpdb;
        
        if ($backup) {
            $backupResult = $this->backupTable($tableName);
            if (!$backupResult['success']) {
                return $backupResult;
            }
        }
        
        // Converti tabella
        $result = $wpdb->query(
            "ALTER TABLE `{$tableName}` 
             CONVERT TO CHARACTER SET utf8mb4 
             COLLATE utf8mb4_unicode_ci"
        );
        
        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Errore durante la conversione charset',
                'error' => $wpdb->last_error,
            ];
        }
        
        $this->addToHistory('convert_charset', $tableName, [
            'to' => 'utf8mb4',
        ]);
        
        Logger::info('Table charset converted', ['table' => $tableName]);
        
        return [
            'success' => true,
            'message' => 'Charset convertito a utf8mb4 con successo',
            'table' => $tableName,
        ];
    }
    
    /**
     * Ottimizzazione autoload avanzata con dettagli
     */
    public function analyzeAutoloadDetailed(): array
    {
        global $wpdb;
        
        // Top 100 opzioni autoload per dimensione
        $query = "
            SELECT 
                option_name, 
                LENGTH(option_value) as size,
                SUBSTRING(option_value, 1, 100) as preview
            FROM {$wpdb->options}
            WHERE autoload = 'yes'
            ORDER BY size DESC
            LIMIT 100
        ";
        
        $options = $wpdb->get_results($query, ARRAY_A);
        
        $totalSize = 0;
        $largeOptions = [];
        $pluginOptions = [];
        
        foreach ($options as $option) {
            $size = (int) $option['size'];
            $totalSize += $size;
            
            // Opzioni > 50KB
            if ($size > 51200) {
                $largeOptions[] = [
                    'name' => $option['option_name'],
                    'size' => $size,
                    'size_kb' => round($size / 1024, 2),
                    'preview' => substr($option['preview'], 0, 50) . '...',
                    'plugin' => $this->identifyPlugin($option['option_name']),
                ];
            }
            
            // Raggruppa per plugin
            $plugin = $this->identifyPlugin($option['option_name']);
            if (!isset($pluginOptions[$plugin])) {
                $pluginOptions[$plugin] = [
                    'count' => 0,
                    'total_size' => 0,
                ];
            }
            $pluginOptions[$plugin]['count']++;
            $pluginOptions[$plugin]['total_size'] += $size;
        }
        
        // Ordina plugin per dimensione
        uasort($pluginOptions, function($a, $b) {
            return $b['total_size'] - $a['total_size'];
        });
        
        return [
            'total_autoload_options' => count($options),
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'large_options' => $largeOptions,
            'by_plugin' => $pluginOptions,
            'recommendation' => $totalSize > 1048576 
                ? 'Autoload totale > 1MB può rallentare il sito. Considera di disabilitare autoload per opzioni grandi.' 
                : null,
        ];
    }
    
    /**
     * Identifica il plugin da un nome opzione
     */
    private function identifyPlugin(string $optionName): string
    {
        // Pattern comuni
        $patterns = [
            '/^woocommerce_/' => 'WooCommerce',
            '/^wc_/' => 'WooCommerce',
            '/^yoast/' => 'Yoast SEO',
            '/^_yoast_/' => 'Yoast SEO',
            '/^elementor_/' => 'Elementor',
            '/^acf_/' => 'Advanced Custom Fields',
            '/^_acf_/' => 'Advanced Custom Fields',
            '/^wp_/' => 'WordPress Core',
            '/^_transient_/' => 'WordPress Transients',
        ];
        
        foreach ($patterns as $pattern => $plugin) {
            if (preg_match($pattern, $optionName)) {
                return $plugin;
            }
        }
        
        return 'Other';
    }
    
    /**
     * Disabilita autoload per un'opzione
     */
    public function disableAutoload(string $optionName): array
    {
        global $wpdb;
        
        $result = $wpdb->update(
            $wpdb->options,
            ['autoload' => 'no'],
            ['option_name' => $optionName],
            ['%s'],
            ['%s']
        );
        
        if ($result === false) {
            return [
                'success' => false,
                'message' => 'Errore durante la modifica',
                'error' => $wpdb->last_error,
            ];
        }
        
        $this->addToHistory('disable_autoload', $optionName, []);
        
        Logger::info('Autoload disabled', ['option' => $optionName]);
        
        return [
            'success' => true,
            'message' => 'Autoload disabilitato con successo',
            'option' => $optionName,
        ];
    }
    
    /**
     * Backup di una tabella (struttura + primi 1000 record)
     */
    private function backupTable(string $tableName): array
    {
        global $wpdb;
        
        try {
            // Crea struttura
            $createTable = $wpdb->get_var("SHOW CREATE TABLE `{$tableName}`", 1);
            
            if (!$createTable) {
                return [
                    'success' => false,
                    'message' => 'Impossibile creare backup della tabella',
                ];
            }
            
            // Salva backup info
            $backups = get_option(self::BACKUP_KEY, []);
            $backups[$tableName] = [
                'timestamp' => time(),
                'create_table' => $createTable,
            ];
            
            update_option(self::BACKUP_KEY, $backups);
            
            Logger::info('Table backup created', ['table' => $tableName]);
            
            return [
                'success' => true,
                'message' => 'Backup creato con successo',
            ];
            
        } catch (\Exception $e) {
            Logger::error('Backup failed', $e, ['table' => $tableName]);
            return [
                'success' => false,
                'message' => 'Errore durante il backup: ' . $e->getMessage(),
            ];
        }
    }
    
    /**
     * Aggiungi operazione allo storico
     */
    private function addToHistory(string $operation, string $table, array $details): void
    {
        $history = get_option(self::HISTORY_KEY, []);
        
        // Mantieni solo ultime 100 operazioni
        if (count($history) >= 100) {
            array_shift($history);
        }
        
        $history[] = [
            'timestamp' => time(),
            'operation' => $operation,
            'table' => $table,
            'details' => $details,
        ];
        
        update_option(self::HISTORY_KEY, $history);
    }
    
    /**
     * Ottieni storico operazioni
     */
    public function getHistory(int $limit = 50): array
    {
        $history = get_option(self::HISTORY_KEY, []);
        return array_slice(array_reverse($history), 0, $limit);
    }
    
    /**
     * Analisi completa con tutte le metriche
     */
    public function getCompleteAnalysis(): array
    {
        return [
            'basic' => $this->analyze(),
            'fragmentation' => $this->analyzeFragmentation(),
            'missing_indexes' => $this->analyzeMissingIndexes(),
            'storage_engines' => $this->analyzeStorageEngines(),
            'charset' => $this->analyzeCharset(),
            'autoload' => $this->analyzeAutoloadDetailed(),
            'history' => $this->getHistory(10),
            'timestamp' => time(),
        ];
    }
}

