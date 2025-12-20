<?php

/**
 * Database Checker
 * 
 * Utility class for checking database availability and connection status.
 * Replaces the global function fp_perf_suite_is_db_available().
 *
 * @package FP\PerfSuite\Core\Environment
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Environment;

class DatabaseChecker
{
    /**
     * Check if the database is available
     * 
     * @return bool True if the database is available, false otherwise
     */
    public function isAvailable(): bool
    {
        global $wpdb;
        
        // Verify that $wpdb exists
        if (!isset($wpdb) || !is_object($wpdb)) {
            return false;
        }
        
        // Verify that the connection is active
        if (!isset($wpdb->dbh)) {
            return false;
        }
        
        // For mysqli - verify connection
        if (is_object($wpdb->dbh) && $wpdb->dbh instanceof \mysqli) {
            return true;
        }
        
        // Fallback: attempt a simple query
        try {
            $result = @$wpdb->query('SELECT 1');
            return $result !== false;
        } catch (\Throwable $e) {
            return false;
        }
    }
    
    /**
     * Check if database is available and throw exception if not
     * 
     * @throws \RuntimeException If database is not available
     * @return void
     */
    public function requireAvailable(): void
    {
        if (!$this->isAvailable()) {
            throw new \RuntimeException('Database is not available');
        }
    }
}










