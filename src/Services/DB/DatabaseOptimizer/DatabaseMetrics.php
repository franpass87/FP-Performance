<?php

namespace FP\PerfSuite\Services\DB\DatabaseOptimizer;

/**
 * Gestisce le metriche del database
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseOptimizer
 * @author Francesco Passeri
 */
class DatabaseMetrics
{
    /**
     * Ottiene le metriche del database
     */
    public function getDatabaseMetrics(): array
    {
        global $wpdb;
        
        $tables = $wpdb->get_results("SHOW TABLE STATUS");
        
        $total_size = 0;
        $total_rows = 0;
        $total_tables = 0;
        
        foreach ($tables as $table) {
            $data_length = $table->Data_length ?? 0;
            $index_length = $table->Index_length ?? 0;
            $rows = $table->Rows ?? 0;
            
            $total_size += ($data_length + $index_length);
            $total_rows += $rows;
            $total_tables++;
        }
        
        return [
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2),
            'total_rows' => $total_rows,
            'total_tables' => $total_tables,
        ];
    }

    /**
     * Ottiene la dimensione totale del database
     */
    public function getDatabaseSize(): float
    {
        global $wpdb;
        
        $tables = $wpdb->get_results("SHOW TABLE STATUS");
        $total_size = 0;
        
        foreach ($tables as $table) {
            $data_length = $table->Data_length ?? 0;
            $index_length = $table->Index_length ?? 0;
            $total_size += ($data_length + $index_length);
        }
        
        return round($total_size / 1024 / 1024, 2); // MB
    }

    /**
     * Ottiene tutte le tabelle
     */
    public function getTables(): array
    {
        global $wpdb;
        
        $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
        $table_list = [];
        
        foreach ($tables as $table) {
            $table_list[] = $table[0];
        }
        
        return $table_list;
    }

    /**
     * Ottiene lo status delle tabelle
     */
    public function getTableStatus(): array
    {
        global $wpdb;
        
        return $wpdb->get_results("SHOW TABLE STATUS");
    }
}















