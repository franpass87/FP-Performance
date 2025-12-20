<?php

namespace FP\PerfSuite\Services\DB\DatabaseOptimizer;

use function size_format;
use function number_format;

/**
 * Analizza il database
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseOptimizer
 * @author Francesco Passeri
 */
class DatabaseAnalyzer
{
    private DatabaseMetrics $metrics;

    public function __construct(DatabaseMetrics $metrics)
    {
        $this->metrics = $metrics;
    }

    /**
     * Analizza il database
     */
    public function analyze(): array
    {
        global $wpdb;
        
        $tables_data = [];
        $total_size_bytes = 0;
        $total_overhead_bytes = 0;
        
        // Ottieni informazioni sulle tabelle
        $tables = $this->metrics->getTableStatus();
        
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
        $analysis = [
            'database_size' => [
                'total_bytes' => $total_size_bytes,
                'total_mb' => round($total_size_bytes / 1024 / 1024, 2),
                'total_gb' => round($total_size_bytes / 1024 / 1024 / 1024, 2),
            ],
            'table_analysis' => [
                'total_tables' => count($tables),
                'tables' => $tables_data,
                'total_overhead_bytes' => $total_overhead_bytes,
                'total_overhead_mb' => round($total_overhead_bytes / 1024 / 1024, 2),
            ],
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
     * Analizza la frammentazione
     */
    public function analyzeFragmentation(): array
    {
        $fragmentation_data = [
            'total_fragmented_tables' => 0,
            'total_fragmentation_size' => 0,
            'fragmented_tables' => [],
            'needs_optimization' => false,
        ];
        
        $tables = $this->metrics->getTableStatus();
        
        foreach ($tables as $table) {
            $table_name = $table->Name;
            $data_free = $table->Data_free ?? 0;
            $data_length = $table->Data_length ?? 0;
            $index_length = $table->Index_length ?? 0;
            $total_size = $data_length + $index_length;
            
            // Considera frammentata se ha più del 10% di overhead
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
     * Analizza indici mancanti
     */
    public function analyzeMissingIndexes(): array
    {
        global $wpdb;
        
        $index_analysis = [
            'suggestions' => [],
            'total_suggestions' => 0,
            'high_priority' => 0,
        ];
        
        // Ottieni tutte le tabelle
        $tables = $this->metrics->getTableStatus();
        
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
     * Analizza i motori di storage
     */
    public function analyzeStorageEngines(): array
    {
        $engine_analysis = [
            'engines' => [],
            'recommendations' => [],
        ];
        
        $tables = $this->metrics->getTableStatus();
        
        foreach ($tables as $table) {
            $engine = $table->Engine ?? 'Unknown';
            
            if (!isset($engine_analysis['engines'][$engine])) {
                $engine_analysis['engines'][$engine] = 0;
            }
            
            $engine_analysis['engines'][$engine]++;
        }
        
        // Raccomandazioni
        if (isset($engine_analysis['engines']['MyISAM']) && $engine_analysis['engines']['MyISAM'] > 0) {
            $engine_analysis['recommendations'][] = 'Consider converting MyISAM tables to InnoDB for better performance and data integrity';
        }
        
        return $engine_analysis;
    }

    /**
     * Analizza il charset
     */
    public function analyzeCharset(): array
    {
        global $wpdb;
        
        $charset_analysis = [
            'charsets' => [],
            'collations' => [],
            'recommendations' => [],
        ];
        
        $tables = $this->metrics->getTableStatus();
        
        foreach ($tables as $table) {
            $charset = $table->Collation ?? '';
            $collation = $table->Collation ?? '';
            
            if ($charset) {
                $charset_name = explode('_', $charset)[0];
                if (!isset($charset_analysis['charsets'][$charset_name])) {
                    $charset_analysis['charsets'][$charset_name] = 0;
                }
                $charset_analysis['charsets'][$charset_name]++;
            }
            
            if ($collation) {
                if (!isset($charset_analysis['collations'][$collation])) {
                    $charset_analysis['collations'][$collation] = 0;
                }
                $charset_analysis['collations'][$collation]++;
            }
        }
        
        // Raccomandazioni
        if (!isset($charset_analysis['charsets']['utf8mb4']) || $charset_analysis['charsets']['utf8mb4'] < count($tables)) {
            $engine_analysis['recommendations'][] = 'Consider using utf8mb4 charset for better Unicode support';
        }
        
        return $charset_analysis;
    }

    /**
     * Analizza autoload dettagliato
     */
    public function analyzeAutoloadDetailed(): array
    {
        global $wpdb;
        
        $autoload_analysis = [
            'autoload_size' => 0,
            'no_autoload_size' => 0,
            'autoload_count' => 0,
            'no_autoload_count' => 0,
            'largest_autoload' => [],
        ];
        
        // Query per ottenere dimensioni autoload
        $autoload_data = $wpdb->get_results("
            SELECT 
                SUM(CASE WHEN autoload = 'yes' THEN LENGTH(option_value) ELSE 0 END) as autoload_size,
                SUM(CASE WHEN autoload = 'no' THEN LENGTH(option_value) ELSE 0 END) as no_autoload_size,
                COUNT(CASE WHEN autoload = 'yes' THEN 1 END) as autoload_count,
                COUNT(CASE WHEN autoload = 'no' THEN 1 END) as no_autoload_count
            FROM {$wpdb->options}
        ");
        
        if (!empty($autoload_data)) {
            $autoload_analysis['autoload_size'] = $autoload_data[0]->autoload_size ?? 0;
            $autoload_analysis['no_autoload_size'] = $autoload_data[0]->no_autoload_size ?? 0;
            $autoload_analysis['autoload_count'] = $autoload_data[0]->autoload_count ?? 0;
            $autoload_analysis['no_autoload_count'] = $autoload_data[0]->no_autoload_count ?? 0;
        }
        
        // Ottieni le opzioni autoload più grandi
        $largest = $wpdb->get_results("
            SELECT option_name, LENGTH(option_value) as size
            FROM {$wpdb->options}
            WHERE autoload = 'yes'
            ORDER BY size DESC
            LIMIT 10
        ");
        
        foreach ($largest as $option) {
            $autoload_analysis['largest_autoload'][] = [
                'name' => $option->option_name,
                'size' => (int) $option->size,
                'size_mb' => round((int) $option->size / 1024 / 1024, 2),
            ];
        }
        
        return $autoload_analysis;
    }

    /**
     * Ottiene analisi completa
     */
    public function getCompleteAnalysis(): array
    {
        return [
            'general' => $this->analyze(),
            'fragmentation' => $this->analyzeFragmentation(),
            'missing_indexes' => $this->analyzeMissingIndexes(),
            'storage_engines' => $this->analyzeStorageEngines(),
            'charset' => $this->analyzeCharset(),
            'autoload' => $this->analyzeAutoloadDetailed(),
        ];
    }
}















