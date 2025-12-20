<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;
use FP\PerfSuite\Services\DB\DatabaseOptimizer\TableOptimizer;
use FP\PerfSuite\Services\DB\DatabaseOptimizer\DatabaseMetrics;
use FP\PerfSuite\Services\DB\DatabaseOptimizer\DatabaseAnalyzer;
use FP\PerfSuite\Services\DB\DatabaseOptimizer\QueryCacheManager;

class DatabaseOptimizer
{
    private $auto_optimize;
    private $query_cache;
    private TableOptimizer $tableOptimizer;
    private DatabaseMetrics $metrics;
    private DatabaseAnalyzer $analyzer;
    private QueryCacheManager $queryCacheManager;
    private ?LoggerInterface $logger = null;
    
    public function __construct($auto_optimize = true, $query_cache = true, ?LoggerInterface $logger = null)
    {
        $this->auto_optimize = $auto_optimize;
        $this->query_cache = $query_cache;
        $this->logger = $logger;
        
        $this->metrics = new DatabaseMetrics();
        $this->analyzer = new DatabaseAnalyzer($this->metrics);
        $this->tableOptimizer = new TableOptimizer();
        $this->queryCacheManager = new QueryCacheManager();
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     * @param \Throwable|null $exception Optional exception
     */
    private function log(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($this->logger !== null) {
            if ($exception !== null && method_exists($this->logger, $level)) {
                $this->logger->$level($message, $context, $exception);
            } else {
                $this->logger->$level($message, $context);
            }
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    public function init()
    {
        if ($this->auto_optimize) {
            add_action('wp_scheduled_delete', [$this, 'optimizeDatabase']);
            add_action('fp_optimize_database', [$this, 'optimizeDatabase']);
        }
        
        if ($this->query_cache) {
            add_action('init', [$this->queryCacheManager, 'enableQueryCache']);
        }
    }
    
    public function optimizeDatabase()
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
            
            $this->log('info', sprintf(
                'Database optimization skipped: rate limit active. Retry in %d minutes (Hosting: %s)',
                $remaining_min,
                $isShared ? 'Shared' : 'VPS'
            ));
            
            return 0;
        }
        
        // Marca inizio ottimizzazione
        set_transient($rate_limit_key, time(), $min_interval);
        
        $this->log('info', 'Starting database optimization (Hosting: ' . ($isShared ? 'Shared' : 'VPS') . ')');
        
        // SICUREZZA: Query preparate per prevenire SQL injection
        $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
        $optimized = 0;
        $start_time = microtime(true);
        
        // Su shared hosting, limita numero tabelle per batch
        $max_tables = $isShared ? 10 : 50;
        $processed = 0;
        
        foreach ($tables as $table) {
            // Rispetta limite batch su shared
            if ($processed >= $max_tables) {
                $this->log('info', "Batch limit reached ({$max_tables} tables), scheduling next batch");
                
                // Schedula prossimo batch tra 5 minuti
                wp_schedule_single_event(time() + (5 * MINUTE_IN_SECONDS), 'fp_optimize_database');
                break;
            }
            
            $table_name = $table[0];
            
            // SICUREZZA: Validiamo il nome della tabella
            if (preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
                try {
                    // NOTA: wpdb->prepare non supporta table names, usiamo escape manuale
                    $result = $wpdb->query("OPTIMIZE TABLE `{$table_name}`");
                    if ($result !== false) {
                        $optimized++;
                    }
                    $processed++;
                } catch (\Throwable $e) {
                    $this->log('error', "Error optimizing table {$table_name}: " . $e->getMessage(), [], $e);
                }
            }
            
            // Su shared hosting, pausa breve tra tabelle per non sovraccaricare
            if ($isShared && $processed % 5 === 0) {
                usleep(100000); // 100ms pause ogni 5 tabelle
            }
        }
        
        $duration = round(microtime(true) - $start_time, 2);
        $this->log('info', "Database optimization completed: {$optimized} tables optimized in {$duration}s");
        
        return $optimized;
    }
    
    /**
     * BUGFIX #16: Implementato metodo optimizeAllTables() che mancava
     * Questo metodo viene chiamato dalla pagina Database.php
     * 
     * @return array Risultati dell'ottimizzazione con lista tabelle ottimizzate ed errori
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
                        $this->log('info', "Table {$table_name} ottimizzata con successo");
                    } else {
                        $results['errors'][$table_name] = $wpdb->last_error ?: 'Errore sconosciuto';
                        $this->log('error', "Errore ottimizzando {$table_name}: " . $wpdb->last_error);
                    }
                } catch (\Throwable $e) {
                    $results['errors'][$table_name] = $e->getMessage();
                    $this->log('error', "Eccezione ottimizzando {$table_name}: " . $e->getMessage(), [], $e);
                }
            }
            
            $results['duration'] = round(microtime(true) - $start_time, 2);
            
            $this->log('info', sprintf(
                'optimizeAllTables completato: %d/%d tabelle ottimizzate in %ss',
                count($results['optimized']),
                $results['total'],
                $results['duration']
            ));
            
        } catch (\Throwable $e) {
            $results['success'] = false;
            $results['errors']['general'] = $e->getMessage();
            $this->log('error', 'optimizeAllTables fallito: ' . $e->getMessage(), [], $e);
        }
        
        return $results;
    }
    
    public function enableQueryCache()
    {
        global $wpdb;
        
        // Enable query cache if not already enabled
        $wpdb->query("SET SESSION query_cache_type = ON");
        $wpdb->query("SET SESSION query_cache_size = 268435456"); // 256MB
    }
    
    public function getDatabaseMetrics()
    {
        global $wpdb;
        
        $tables = $wpdb->get_results("SHOW TABLE STATUS");
        $total_size = 0;
        $total_rows = 0;
        
        if (!empty($tables)) {
            foreach ($tables as $table) {
                $total_size += ($table->Data_length ?? 0) + ($table->Index_length ?? 0);
                $total_rows += $table->Rows ?? 0;
            }
        }
        
        return [
            'total_tables' => count($tables),
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2),
            'total_rows' => $total_rows,
            'auto_optimize' => $this->auto_optimize,
            'query_cache' => $this->query_cache
        ];
    }
    
    /**
     * BUGFIX #16b: Implementato metodo getDatabaseSize() che mancava
     * Usato dalla pagina Database.php per mostrare la dimensione
     */
    public function getDatabaseSize(): float
    {
        $metrics = $this->getDatabaseMetrics();
        return $metrics['total_size_mb'];
    }
    
    /**
     * BUGFIX #16c: Implementato metodo getTables() che mancava
     * Usato dalla pagina Database.php per mostrare info tabelle
     */
    public function getTables(): array
    {
        $analysis = $this->analyze();
        return $analysis['tables'] ?? [];
    }
    
    public function getTableStatus()
    {
        global $wpdb;
        
        return $wpdb->get_results("SHOW TABLE STATUS");
    }
    
    /**
     * Analizza lo stato del database e restituisce raccomandazioni
     * 
     * @return array Analisi completa del database
     */
    public function analyze(): array
    {
        global $wpdb;
        
        $tables_data = [];
        $total_size_bytes = 0;
        $total_overhead_bytes = 0;
        
        // Ottieni informazioni sulle tabelle
        $tables = $this->getTableStatus();
        
        foreach ($tables as $table) {
            $table_name = $table->Name;
            $data_length = $table->Data_length ?? 0;
            $index_length = $table->Index_length ?? 0;
            $data_free = $table->Data_free ?? 0;
            $rows = $table->Rows ?? 0;
            
            $total_size = $data_length + $index_length;
            $overhead = $data_free;
            
            $tables_data[$table_name] = [
                'size' => $total_size,
                'overhead' => $overhead,
                'rows' => $rows,
                'engine' => $table->Engine ?? 'Unknown',
                'needs_optimization' => $overhead > ($total_size * 0.1), // > 10% overhead
            ];
            
            $total_size_bytes += $total_size;
            $total_overhead_bytes += $overhead;
        }
        
        // BUGFIX #16d: Restituisce struttura compatibile con Database.php
        // Database.php si aspetta: database_size.total_mb e table_analysis.total_tables
        $analysis = [
            // Struttura corretta per dimensione database
            'database_size' => [
                'total_bytes' => $total_size_bytes,
                'total_mb' => round($total_size_bytes / 1024 / 1024, 2),
                'total_gb' => round($total_size_bytes / 1024 / 1024 / 1024, 2),
            ],
            // Struttura corretta per analisi tabelle
            'table_analysis' => [
                'total_tables' => count($tables),
                'tables' => $tables_data,
                'total_overhead_bytes' => $total_overhead_bytes,
                'total_overhead_mb' => round($total_overhead_bytes / 1024 / 1024, 2),
            ],
            // Struttura legacy (per compatibilitÃ )
            'tables' => $tables_data,
            'total_size' => $total_size_bytes,
            'overhead' => $total_overhead_bytes,
            'recommendations' => [],
            'issues' => [],
            'optimization_score' => 100,
        ];
        
        // Rileva problemi
        foreach ($tables_data as $table_name => $table_info) {
            $overhead = $table_info['overhead'];
            $size = $table_info['size'];
            $rows = $table_info['rows'];
            $engine = $table_info['engine'];
            
            if ($overhead > ($size * 0.2)) {
                $analysis['issues'][] = [
                    'type' => 'warning',
                    'table' => $table_name,
                    'message' => sprintf('High overhead: %s', size_format($overhead)),
                ];
                $analysis['optimization_score'] -= 10;
            }
            
            if ($rows > 100000 && $engine === 'MyISAM') {
                $analysis['issues'][] = [
                    'type' => 'info',
                    'table' => $table_name,
                    'message' => 'Consider converting to InnoDB for better performance',
                ];
            }
        }
        
        // Genera raccomandazioni
        if ($analysis['overhead'] > ($analysis['total_size'] * 0.1)) {
            $analysis['recommendations'][] = 'Run database optimization to reduce overhead';
        }
        
        if ($analysis['optimization_score'] < 80) {
            $analysis['recommendations'][] = 'Consider regular database maintenance';
        }
        
        // Calcola score finale
        $analysis['optimization_score'] = max(0, $analysis['optimization_score']);
        
        return $analysis;
    }
    
    /**
     * Analizza la frammentazione delle tabelle del database
     * 
     * @return array Dati sulla frammentazione
     */
    public function analyzeFragmentation(): array
    {
        return $this->analyzer->analyzeFragmentation();
    }
    
    // Metodo analyzeFragmentation() originale rimosso - ora gestito da DatabaseAnalyzer
    
    private function analyzeFragmentation_OLD(): array
    {
        global $wpdb;
        
        $fragmentation_data = [
            'total_fragmented_tables' => 0,
            'total_fragmentation_size' => 0,
            'fragmented_tables' => [],
            'needs_optimization' => false,
        ];
        
        $tables = $this->getTableStatus();
        
        foreach ($tables as $table) {
            $table_name = $table->Name;
            $data_free = $table->Data_free ?? 0;
            $data_length = $table->Data_length ?? 0;
            $index_length = $table->Index_length ?? 0;
            $total_size = $data_length + $index_length;
            
            // Considera frammentata se ha piÃ¹ del 10% di overhead
            if ($total_size > 0 && $data_free > ($total_size * 0.1)) {
                $fragmentation_percentage = ($data_free / $total_size) * 100;
                
                $fragmentation_data['fragmented_tables'][] = [
                    'name' => $table_name,
                    'fragmentation_size' => $data_free,
                    'fragmentation_percentage' => round($fragmentation_percentage, 2),
                    'total_size' => $total_size,
                    'engine' => $table->Engine ?? 'Unknown',
                ];
                
                $fragmentation_data['total_fragmented_tables']++;
                $fragmentation_data['total_fragmentation_size'] += $data_free;
            }
        }
        
        $fragmentation_data['needs_optimization'] = $fragmentation_data['total_fragmented_tables'] > 0;
        
        return $fragmentation_data;
    }
    
    /**
     * Analizza indici mancanti o inefficienti
     * 
     * @return array Suggerimenti per indici
     */
    public function analyzeMissingIndexes(): array
    {
        return $this->analyzer->analyzeMissingIndexes();
    }
    
    // Metodo analyzeMissingIndexes() originale rimosso - ora gestito da DatabaseAnalyzer
    
    private function analyzeMissingIndexes_OLD(): array
    {
        global $wpdb;
        
        $index_analysis = [
            'suggestions' => [],
            'total_suggestions' => 0,
            'high_priority' => 0,
        ];
        
        // Ottieni tutte le tabelle
        $tables = $this->getTableStatus();
        
        foreach ($tables as $table) {
            $table_name = $table->Name;
            $rows = $table->Rows ?? 0;
            
            // Suggerisci indici per tabelle grandi senza indici adeguati
            if ($rows > 10000) {
                // Query per ottenere gli indici esistenti
                $indexes = $wpdb->get_results($wpdb->prepare("SHOW INDEX FROM `%s`", $table_name));
                
                // Se ha solo l'indice PRIMARY, potrebbe beneficiare di indici aggiuntivi
                $has_only_primary = count($indexes) === 1 && isset($indexes[0]->Key_name) && $indexes[0]->Key_name === 'PRIMARY';
                
                if ($has_only_primary || count($indexes) === 0) {
                    $priority = $rows > 100000 ? 'high' : 'medium';
                    
                    $index_analysis['suggestions'][] = [
                        'table' => $table_name,
                        'priority' => $priority,
                        'rows' => $rows,
                        'suggestion' => sprintf('Consider adding indexes to improve query performance on %s (%s rows)', $table_name, number_format($rows)),
                    ];
                    
                    $index_analysis['total_suggestions']++;
                    if ($priority === 'high') {
                        $index_analysis['high_priority']++;
                    }
                }
            }
        }
        
        return $index_analysis;
    }
    
    /**
     * Analizza i motori di storage utilizzati
     * 
     * @return array Informazioni sui motori di storage
     */
    public function analyzeStorageEngines(): array
    {
        return $this->analyzer->analyzeStorageEngines();
    }
    
    // Metodo analyzeStorageEngines() originale rimosso - ora gestito da DatabaseAnalyzer
    
    private function analyzeStorageEngines_OLD(): array
    {
        global $wpdb;
        
        $engine_analysis = [
            'myisam_tables' => [],
            'innodb_tables' => [],
            'other_tables' => [],
            'total_myisam' => 0,
            'total_innodb' => 0,
            'recommendation' => '',
        ];
        
        $tables = $this->getTableStatus();
        
        foreach ($tables as $table) {
            $table_name = $table->Name;
            $engine = $table->Engine ?? 'Unknown';
            
            if (strtoupper($engine) === 'MYISAM') {
                $engine_analysis['myisam_tables'][] = [
                    'name' => $table_name,
                    'rows' => $table->Rows ?? 0,
                ];
                $engine_analysis['total_myisam']++;
            } elseif (strtoupper($engine) === 'INNODB') {
                $engine_analysis['innodb_tables'][] = [
                    'name' => $table_name,
                    'rows' => $table->Rows ?? 0,
                ];
                $engine_analysis['total_innodb']++;
            } else {
                $engine_analysis['other_tables'][] = [
                    'name' => $table_name,
                    'engine' => $engine,
                ];
            }
        }
        
        if ($engine_analysis['total_myisam'] > 0) {
            $engine_analysis['recommendation'] = 'Consider converting MyISAM tables to InnoDB for better performance, ACID compliance, and crash recovery.';
        }
        
        return $engine_analysis;
    }
    
    /**
     * Analizza charset e collation delle tabelle
     * 
     * @return array Informazioni su charset
     */
    public function analyzeCharset(): array
    {
        return $this->analyzer->analyzeCharset();
    }
    
    // Metodo analyzeCharset() originale rimosso - ora gestito da DatabaseAnalyzer
    
    private function analyzeCharset_OLD(): array
    {
        global $wpdb;
        
        $charset_analysis = [
            'outdated_tables' => [],
            'utf8mb4_tables' => [],
            'other_tables' => [],
            'needs_upgrade' => false,
        ];
        
        $tables = $this->getTableStatus();
        
        foreach ($tables as $table) {
            $table_name = $table->Name;
            $collation = $table->Collation ?? '';
            
            // Controlla se usa utf8mb4 (raccomandato per WordPress)
            if (strpos($collation, 'utf8mb4') === 0) {
                $charset_analysis['utf8mb4_tables'][] = $table_name;
            } elseif (strpos($collation, 'utf8') === 0) {
                $charset_analysis['outdated_tables'][] = [
                    'name' => $table_name,
                    'current_collation' => $collation,
                    'recommended' => 'utf8mb4_unicode_ci',
                ];
                $charset_analysis['needs_upgrade'] = true;
            } else {
                $charset_analysis['other_tables'][] = [
                    'name' => $table_name,
                    'collation' => $collation,
                ];
            }
        }
        
        return $charset_analysis;
    }
    
    /**
     * Analizza opzioni autoload dettagliate
     * 
     * @return array Analisi dettagliata delle opzioni autoload
     */
    public function analyzeAutoloadDetailed(): array
    {
        return $this->analyzer->analyzeAutoloadDetailed();
    }
    
    // Metodo analyzeAutoloadDetailed() originale rimosso - ora gestito da DatabaseAnalyzer
    
    private function analyzeAutoloadDetailed_OLD(): array
    {
        global $wpdb;
        
        $autoload_analysis = [
            'large_options' => [],
            'total_size_mb' => 0,
            'total_count' => 0,
            'recommendations' => [],
        ];
        
        // Query per ottenere tutte le opzioni autoload
        $results = $wpdb->get_results(
            "SELECT option_name, LENGTH(option_value) as size_bytes 
             FROM {$wpdb->options} 
             WHERE autoload = 'yes' 
             ORDER BY size_bytes DESC 
             LIMIT 50"
        );
        
        $total_size_bytes = 0;
        
        foreach ($results as $row) {
            $size_kb = $row->size_bytes / 1024;
            $total_size_bytes += $row->size_bytes;
            
            // Considera "large" se > 100KB
            if ($size_kb > 100) {
                $autoload_analysis['large_options'][] = [
                    'name' => $row->option_name,
                    'size_kb' => round($size_kb, 2),
                    'size_mb' => round($size_kb / 1024, 2),
                ];
            }
        }
        
        $autoload_analysis['total_size_mb'] = round($total_size_bytes / 1024 / 1024, 2);
        $autoload_analysis['total_count'] = count($results);
        
        if ($autoload_analysis['total_size_mb'] > 1) {
            $autoload_analysis['recommendations'][] = 'Large autoload data detected. Consider disabling autoload for rarely-used options.';
        }
        
        return $autoload_analysis;
    }
    
    /**
     * Ottiene un'analisi completa del database combinando tutti i metodi di analisi
     * 
     * @return array Analisi completa del database
     */
    public function getCompleteAnalysis(): array
    {
        return $this->analyzer->getCompleteAnalysis();
    }
    
    // Metodo getCompleteAnalysis() originale rimosso - ora gestito da DatabaseAnalyzer
    
    private function getCompleteAnalysis_OLD(): array
    {
        return [
            'basic_analysis' => $this->analyze(),
            'fragmentation' => $this->analyzeFragmentation(),
            'missing_indexes' => $this->analyzeMissingIndexes(),
            'storage_engines' => $this->analyzeStorageEngines(),
            'charset' => $this->analyzeCharset(),
            'autoload' => $this->analyzeAutoloadDetailed(),
            'metrics' => $this->getDatabaseMetrics(),
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}