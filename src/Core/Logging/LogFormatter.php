<?php

/**
 * Log Formatter
 * 
 * Formats log messages with context and timestamps
 *
 * @package FP\PerfSuite\Core\Logging
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Logging;

class LogFormatter
{
    /** @var string Log prefix */
    private const PREFIX = '[FP-PerfSuite]';
    
    /**
     * Format a log message
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     * @return string Formatted log message
     */
    public static function format(string $level, string $message, array $context = []): string
    {
        $timestamp = gmdate('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        
        $formatted = sprintf(
            '[%s] %s [%s] %s',
            $timestamp,
            self::PREFIX,
            $levelUpper,
            $message
        );
        
        // Add context if present
        if (!empty($context)) {
            $contextStr = wp_json_encode($context);
            if ($contextStr !== false) {
                $formatted .= ' ' . $contextStr;
            }
        }
        
        return $formatted;
    }
    
    /**
     * Format exception in log message
     * 
     * @param \Throwable $exception Exception to format
     * @return string Formatted exception message
     */
    public static function formatException(\Throwable $exception): string
    {
        $message = sprintf(
            '[%s:%d] %s',
            basename($exception->getFile()),
            $exception->getLine(),
            $exception->getMessage()
        );
        
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            $message .= "\nStack trace:\n" . $exception->getTraceAsString();
        }
        
        return $message;
    }
}









