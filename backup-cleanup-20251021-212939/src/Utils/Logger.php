<?php

namespace FP\PerfSuite\Utils;

/**
 * Centralized logging utility for FP Performance Suite.
 *
 * Provides consistent logging with context and severity levels.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Logger
{
    private const PREFIX = '[FP-PerfSuite]';
    private const OPTION_LOG_LEVEL = 'fp_ps_log_level';

    /**
     * Log levels
     */
    public const ERROR = 'ERROR';
    public const WARNING = 'WARNING';
    public const INFO = 'INFO';
    public const DEBUG = 'DEBUG';

    /**
     * Log an error message
     */
    public static function error(string $message, ?\Throwable $e = null, array $context = []): void
    {
        $contextStr = '';
        
        // Handle exception context
        if ($e) {
            $contextStr = sprintf(
                ' [%s:%d] %s',
                basename($e->getFile()),
                $e->getLine(),
                $e->getMessage()
            );

            if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                $contextStr .= "\nStack trace:\n" . $e->getTraceAsString();
            }
        }
        
        // Handle additional context array
        if (!empty($context)) {
            $contextStr .= ' ' . wp_json_encode($context);
        }

        self::write(self::ERROR, $message . $contextStr);

        // Hook per monitoraggio esterno
        do_action('fp_ps_log_error', $message, $e, $context);
    }

    /**
     * Log a warning message
     */
    public static function warning(string $message): void
    {
        self::write(self::WARNING, $message);
        do_action('fp_ps_log_warning', $message);
    }

    /**
     * Log an informational message
     */
    public static function info(string $message): void
    {
        if (self::shouldLog(self::INFO)) {
            self::write(self::INFO, $message);
            do_action('fp_ps_log_info', $message);
        }
    }

    /**
     * Log a debug message with optional context
     */
    public static function debug(string $message, array $context = []): void
    {
        if (self::shouldLog(self::DEBUG)) {
            $contextStr = !empty($context) ? ' ' . wp_json_encode($context) : '';
            self::write(self::DEBUG, $message . $contextStr);
            do_action('fp_ps_log_debug', $message, $context);
        }
    }

    /**
     * Write log entry
     */
    private static function write(string $level, string $message): void
    {
        $timestamp = gmdate('Y-m-d H:i:s');
        $formattedMessage = sprintf(
            '%s %s [%s] %s',
            $timestamp,
            self::PREFIX,
            $level,
            $message
        );

        error_log($formattedMessage);
    }

    /**
     * Check if we should log at this level
     */
    private static function shouldLog(string $level): bool
    {
        if ($level === self::ERROR || $level === self::WARNING) {
            return true;
        }

        if (defined('WP_DEBUG') && WP_DEBUG) {
            return true;
        }

        $configuredLevel = get_option(self::OPTION_LOG_LEVEL, 'ERROR');
        $levels = [self::ERROR => 1, self::WARNING => 2, self::INFO => 3, self::DEBUG => 4];

        $currentLevel = $levels[$configuredLevel] ?? 1;
        $requestedLevel = $levels[$level] ?? 4;

        return $requestedLevel <= $currentLevel;
    }

    /**
     * Set the minimum log level
     */
    public static function setLevel(string $level): void
    {
        update_option(self::OPTION_LOG_LEVEL, $level);
    }
}

