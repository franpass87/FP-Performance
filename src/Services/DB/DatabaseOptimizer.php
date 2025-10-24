<?php

namespace FP\PerfSuite\Services\DB;

class DatabaseOptimizer
{
    private $auto_optimize;
    private $query_cache;
    
    public function __construct($auto_optimize = true, $query_cache = true)
    {
        $this->auto_optimize = $auto_optimize;
        $this->query_cache = $query_cache;
    }
    
    public function init()
    {
        if ($this->auto_optimize) {
            add_action('wp_scheduled_delete', [$this, 'optimizeDatabase']);
            add_action('fp_optimize_database', [$this, 'optimizeDatabase']);
        }
        
        if ($this->query_cache) {
            add_action('init', [$this, 'enableQueryCache']);
        }
    }
    
    public function optimizeDatabase()
    {
        global $wpdb;
        
        // SICUREZZA: Query preparate per prevenire SQL injection
        $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
        $optimized = 0;
        
        foreach ($tables as $table) {
            $table_name = $table[0];
            
            // SICUREZZA: Validiamo il nome della tabella
            if (preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
                $result = $wpdb->query($wpdb->prepare("OPTIMIZE TABLE `%s`", $table_name));
                if ($result !== false) {
                    $optimized++;
                }
            }
        }
        
        return $optimized;
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
        
        foreach ($tables as $table) {
            $total_size += $table->Data_length + $table->Index_length;
            $total_rows += $table->Rows;
        }
        
        return [
            'total_tables' => count($tables),
            'total_size' => $total_size,
            'total_rows' => $total_rows,
            'auto_optimize' => $this->auto_optimize,
            'query_cache' => $this->query_cache
        ];
    }
    
    public function getTableStatus()
    {
        global $wpdb;
        
        return $wpdb->get_results("SHOW TABLE STATUS");
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}