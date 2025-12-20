<?php

/**
 * Error Handler Utility
 * 
 * Centralized error handling for consistent error management across the plugin.
 * Provides logging, notification, and debugging capabilities.
 * 
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Utils;

class ErrorHandler
{
    /**
     * Handle error with full logging and optional notification
     * 
     * @param \Throwable $e Exception or error
     * @param string $context Context where error occurred
     * @param bool $notifyAdmin Whether to notify admin (for critical errors)
     * @return void
     */
    public static function handle(\Throwable $e, string $context = '', bool $notifyAdmin = false): void
    {
        // Log error
        if (class_exists(Logger::class)) {
            Logger::error("Error in {$context}", [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        } else {
            error_log(sprintf(
                '[FP-PerfSuite] Error in %s: %s in %s:%d',
                $context,
                $e->getMessage(),
                basename($e->getFile()),
                $e->getLine()
            ));
        }
        
        // Debug mode: detailed logging
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf(
                '[FP-PerfSuite] Stack trace for %s: %s',
                $context,
                $e->getTraceAsString()
            ));
        }
        
        // Notify admin for critical errors
        if ($notifyAdmin && is_admin() && function_exists('current_user_can') && current_user_can('manage_options')) {
            self::notifyAdmin($e, $context);
        }
    }
    
    /**
     * Handle error silently (log only, no output)
     * 
     * @param \Throwable $e Exception or error
     * @param string $context Context where error occurred
     * @return void
     */
    public static function handleSilently(\Throwable $e, string $context = ''): void
    {
        // Only log, no output
        if (class_exists(Logger::class)) {
            Logger::debug("Silent error in {$context}", [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        } else {
            error_log(sprintf(
                '[FP-PerfSuite] Silent error in %s: %s',
                $context,
                $e->getMessage()
            ));
        }
    }
    
    /**
     * Handle error and return user-friendly message
     * 
     * @param \Throwable $e Exception or error
     * @param string $context Context where error occurred
     * @param string|null $userMessage Custom user message (null = default)
     * @return string User-friendly error message
     */
    public static function handleWithMessage(\Throwable $e, string $context = '', ?string $userMessage = null): string
    {
        self::handle($e, $context);
        
        if ($userMessage !== null) {
            return $userMessage;
        }
        
        // Default user-friendly message
        if (defined('WP_DEBUG') && WP_DEBUG) {
            return sprintf(
                __('Errore in %s: %s', 'fp-performance-suite'),
                $context,
                esc_html($e->getMessage())
            );
        }
        
        return sprintf(
            __('Si è verificato un errore in %s. Controlla i log per dettagli.', 'fp-performance-suite'),
            $context
        );
    }
    
    /**
     * Notify admin about critical error
     * 
     * @param \Throwable $e Exception
     * @param string $context Context
     * @return void
     */
    private static function notifyAdmin(\Throwable $e, string $context): void
    {
        if (!is_admin()) {
            return;
        }
        
        add_action('admin_notices', function() use ($e, $context) {
            printf(
                '<div class="notice notice-error"><p><strong>FP Performance Suite:</strong> %s<br><small>%s:%d</small></p></div>',
                esc_html(sprintf(__('Errore critico in %s', 'fp-performance-suite'), $context)),
                esc_html(basename($e->getFile())),
                $e->getLine()
            );
        });
    }
    
    /**
     * Check if error should be logged based on severity
     * 
     * @param \Throwable $e Exception
     * @return bool True if should log
     */
    public static function shouldLog(\Throwable $e): bool
    {
        // Always log exceptions
        if ($e instanceof \Exception) {
            return true;
        }
        
        // Log errors only in debug mode
        if ($e instanceof \Error) {
            return defined('WP_DEBUG') && WP_DEBUG;
        }
        
        return true;
    }
}




