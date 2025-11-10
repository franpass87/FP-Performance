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
     * Cache per evitare log ripetitivi
     */
    private static array $loggedMessages = [];
    private static int $lastCleanup = 0;

    /**
     * Log levels
     */
    public const ERROR = 'ERROR';
    public const WARNING = 'WARNING';
    public const INFO = 'INFO';
    public const DEBUG = 'DEBUG';

    /**
     * Log an error message
     * 
     * @param string $message Il messaggio di errore
     * @param \Throwable|array|null $secondParam Può essere un'eccezione o un array di contesto
     * @param array $context Array di contesto aggiuntivo (usato solo se $secondParam è Throwable)
     */
    public static function error(string $message, $secondParam = null, array $context = []): void
    {
        $e = null;
        $actualContext = [];
        
        // Gestisci il caso in cui il secondo parametro sia un array invece di Throwable
        if (is_array($secondParam)) {
            $actualContext = $secondParam;
        } elseif ($secondParam instanceof \Throwable) {
            $e = $secondParam;
            $actualContext = $context;
        }
        
        $contextStr = '';
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
        
        if (!empty($actualContext)) {
            $encoded = wp_json_encode($actualContext);
            if ($encoded === false) {
                $contextStr .= ' [JSON encoding error: ' . json_last_error_msg() . ']';
            } else {
                $contextStr .= ' ' . $encoded;
            }
        }

        self::write(self::ERROR, $message . $contextStr);

        // Hook per monitoraggio esterno
        do_action('fp_ps_log_error', $message, $e, $actualContext);
    }

    /**
     * Log a warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        $contextStr = '';
        if (!empty($context)) {
            $encoded = wp_json_encode($context);
            $contextStr = ($encoded === false) 
                ? ' [JSON encoding error: ' . json_last_error_msg() . ']'
                : ' ' . $encoded;
        }
        self::write(self::WARNING, $message . $contextStr);
        do_action('fp_ps_log_warning', $message, $context);
    }

    /**
     * Log an informational message
     */
    public static function info(string $message, array $context = []): void
    {
        if (self::shouldLog(self::INFO)) {
            $contextStr = '';
            if (!empty($context)) {
                $encoded = wp_json_encode($context);
                $contextStr = ($encoded === false) 
                    ? ' [JSON encoding error: ' . json_last_error_msg() . ']'
                    : ' ' . $encoded;
            }
            self::write(self::INFO, $message . $contextStr);
            do_action('fp_ps_log_info', $message, $context);
        }
    }

    /**
     * Log a debug message with optional context
     */
    public static function debug(string $message, array $context = []): void
    {
        if (self::shouldLog(self::DEBUG)) {
            // Evita log ripetitivi per messaggi di inizializzazione
            if (self::isRepetitiveMessage($message)) {
                return;
            }
            
            $contextStr = '';
            if (!empty($context)) {
                $encoded = wp_json_encode($context);
                $contextStr = ($encoded === false) 
                    ? ' [JSON encoding error: ' . json_last_error_msg() . ']'
                    : ' ' . $encoded;
            }
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

        // Controllo per disabilitare completamente i log di debug in produzione
        if ($level === self::DEBUG && defined('FP_PS_DISABLE_DEBUG_LOGS') && FP_PS_DISABLE_DEBUG_LOGS) {
            return false;
        }

        $configuredLevel = strtoupper((string) get_option(self::OPTION_LOG_LEVEL, self::ERROR));
        $levels = [self::ERROR => 1, self::WARNING => 2, self::INFO => 3, self::DEBUG => 4];

        if (!isset($levels[$configuredLevel])) {
            $configuredLevel = self::ERROR;
        }

        $currentLevel = $levels[$configuredLevel];
        $requestedLevel = $levels[$level] ?? 4;

        if ($requestedLevel > $currentLevel) {
            return false;
        }

        if ($level === self::DEBUG) {
            if (defined('FP_PS_FORCE_DEBUG_LOGS') && FP_PS_FORCE_DEBUG_LOGS) {
                return true;
            }

            if (defined('WP_DEBUG') && WP_DEBUG) {
                return true;
            }

            return $configuredLevel === self::DEBUG;
        }

        return true;
    }

    /**
     * Set the minimum log level
     */
    public static function setLevel(string $level): void
    {
        update_option(self::OPTION_LOG_LEVEL, strtoupper($level));
    }
    
    /**
     * Verifica se un messaggio è ripetitivo e dovrebbe essere filtrato
     */
    private static function isRepetitiveMessage(string $message): bool
    {
        // Pulisci la cache ogni 5 minuti
        $now = time();
        if ($now - self::$lastCleanup > 300) {
            self::$loggedMessages = [];
            self::$lastCleanup = $now;
        }
        
        // Messaggi di inizializzazione che si ripetono spesso
        $repetitivePatterns = [
            'Theme compatibility initialized',
            'Compatibility filters registered', 
            'Predictive Prefetching registered',
            'Cache file count refreshed',
            'Auto-purge hooks registered',
            'Output buffering started for page cache',
            '=== PLUGIN INIT DEBUG ===',
            'Frontend services ENABLED',
            'Frontend services disabled in admin',
            'Plugin initialized successfully',
            'Responsive image manager registered',
            'Performance Analyzer registered',
            'Recommendation Applicator registered',
            'ResponsiveImageOptimizer registered',
            'CriticalPathOptimizer registered',
            'Theme Detector registered',
            'Regole sicurezza .htaccess',
        ];
        
        foreach ($repetitivePatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                $key = md5($message);
                
                // Se già loggato negli ultimi 5 minuti, salta
                if (isset(self::$loggedMessages[$key])) {
                    return true;
                }

                $transientKey = 'fp_ps_log_' . $key;
                if (function_exists('get_transient') && false !== get_transient($transientKey)) {
                    return true;
                }
                
                // Marca come loggato
                self::$loggedMessages[$key] = $now;
                if (function_exists('set_transient')) {
                    set_transient($transientKey, 1, 300);
                }
                return false;
            }
        }
        
        return false;
    }
}

