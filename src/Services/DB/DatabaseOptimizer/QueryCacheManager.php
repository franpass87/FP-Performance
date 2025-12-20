<?php

namespace FP\PerfSuite\Services\DB\DatabaseOptimizer;

/**
 * Gestisce la query cache
 * 
 * @package FP\PerfSuite\Services\DB\DatabaseOptimizer
 * @author Francesco Passeri
 */
class QueryCacheManager
{
    /**
     * Abilita la query cache
     */
    public function enableQueryCache(): void
    {
        global $wpdb;
        
        // Enable query cache if not already enabled
        $wpdb->query("SET SESSION query_cache_type = ON");
        $wpdb->query("SET SESSION query_cache_size = 268435456"); // 256MB
    }
}















