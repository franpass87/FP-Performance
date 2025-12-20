<?php

/**
 * Bootstrap Service
 * 
 * Centralized service for bootstrap-related functionality.
 * This service replaces global functions and provides a clean interface
 * for bootstrap operations.
 *
 * @package FP\PerfSuite\Core\Bootstrap
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Bootstrap;

use FP\PerfSuite\Utils\ErrorHandler;

class BootstrapService
{
    /**
     * Enable deprecated trace logging
     * 
     * Replaces fp_perf_suite_enable_deprecated_trace()
     */
    public static function enableDeprecatedTrace(): void
    {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }

        if (!apply_filters('fp_ps_capture_deprecated_trace', false)) {
            return;
        }

        $previousHandler = set_error_handler(static function ($errno, $errstr, $errfile, $errline) use (&$previousHandler) {
            static $handled = [];

            if ($errno === E_DEPRECATED && strpos($errstr, 'str_replace(): Passing null to parameter #3 ($subject)') !== false) {
                $key = md5($errstr . $errfile . $errline);

                if (!isset($handled[$key])) {
                    $handled[$key] = true;

                    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
                    $normalizedTrace = [];

                    foreach ($trace as $frame) {
                        $normalizedTrace[] = [
                            'function' => $frame['function'] ?? '',
                            'class' => $frame['class'] ?? '',
                            'type' => $frame['type'] ?? '',
                            'file' => $frame['file'] ?? '',
                            'line' => $frame['line'] ?? 0,
                        ];
                    }

                    if (class_exists('\FP\PerfSuite\Utils\ErrorHandler')) {
                        ErrorHandler::handleSilently(
                            new \ErrorException($errstr, $errno, 0, $errfile, $errline),
                            'Deprecated trace: ' . wp_json_encode($normalizedTrace)
                        );
                    } else {
                        error_log('[FP-PerfSuite] Deprecated trace captured: ' . $errstr . ' | stack=' . wp_json_encode($normalizedTrace));
                    }
                }
            }

            if ($previousHandler) {
                return $previousHandler($errno, $errstr, $errfile, $errline);
            }

            return false;
        });

        $initialized = true;
    }
    
    /**
     * Check if WordPress database is available
     * 
     * Replaces fp_perf_suite_is_db_available()
     * 
     * @return bool True if database is available
     */
    public static function isDatabaseAvailable(): bool
    {
        global $wpdb;
        
        // Check if $wpdb exists
        if (!isset($wpdb) || !is_object($wpdb)) {
            return false;
        }
        
        // Check if connection is active
        if (!isset($wpdb->dbh)) {
            return false;
        }
        
        // For mysqli - Check connection
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
     * Safe log without database dependencies
     * 
     * Replaces fp_perf_suite_safe_log()
     * This is a fallback logger that works even when database is not available.
     * 
     * @param string $message Message to log
     * @param string $level Log level (ERROR, DEBUG, etc.)
     */
    public static function safeLog(string $message, string $level = 'ERROR'): void
    {
        $timestamp = gmdate('Y-m-d H:i:s');
        $logMessage = sprintf(
            '[%s] [FP-PerfSuite] [%s] %s',
            $timestamp,
            $level,
            $message
        );
        
        if ($level === 'DEBUG') {
            static $recentDebug = [];
            $now = time();
            $key = md5($message);

            if (isset($recentDebug[$key]) && ($now - $recentDebug[$key]) < 300) {
                return;
            }

            if (function_exists('get_transient')) {
                $transientKey = 'fp_ps_safe_log_' . $key;
                if (false !== get_transient($transientKey)) {
                    return;
                }
                set_transient($transientKey, 1, 300);
            }

            $recentDebug[$key] = $now;
        }

        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($logMessage);
        }
    }
}







