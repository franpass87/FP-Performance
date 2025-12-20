<?php

namespace FP\PerfSuite\Utils;

use FP\PerfSuite\Utils\ErrorHandler;

/**
 * Gestisce le protezioni e configurazioni dell'ambiente
 * 
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 */
class EnvironmentGuard
{
    private static bool $registered = false;

    /**
     * Inizializza le protezioni dell'ambiente
     */
    public static function bootstrap(): void
    {
        if (self::$registered) {
            return;
        }

        self::$registered = true;

        if (function_exists('wp_raise_memory_limit')) {
            wp_raise_memory_limit('admin');
            wp_raise_memory_limit('image');
        } else {
            @ini_set('memory_limit', '512M');
        }

        add_filter('admin_memory_limit', [self::class, 'ensureMinimumAdminMemory']);
        add_action('doing_it_wrong_run', [self::class, 'logDoingItWrongContext'], 10, 3);
        add_filter('user_has_cap', [self::class, 'traceInvalidMetaCapChecks'], 10, 3);
    }

    /**
     * Assicura un minimo di memoria per l'admin
     * 
     * @param string $current Memoria corrente
     * @return string Memoria configurata
     */
    public static function ensureMinimumAdminMemory(string $current): string
    {
        if (!function_exists('wp_convert_hr_to_bytes')) {
            return $current;
        }

        $targetBytes = wp_convert_hr_to_bytes('512M');
        $currentBytes = wp_convert_hr_to_bytes($current);

        if ($targetBytes <= 0 || $currentBytes <= 0) {
            return $current;
        }

        return ($currentBytes < $targetBytes) ? '512M' : $current;
    }

    /**
     * Logga il contesto di doing_it_wrong
     * 
     * @param string $function Nome della funzione
     * @param string $message Messaggio
     * @param string $version Versione
     */
    public static function logDoingItWrongContext(string $function, string $message, string $version): void
    {
        if (!function_exists('wp_debug_backtrace_summary')) {
            return;
        }

        $traceFrames = function_exists('debug_backtrace') ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) : [];
        $trace = !empty($traceFrames) ? print_r(array_slice($traceFrames, 0, 10), true) : (string) wp_debug_backtrace_summary(null, 0, false);

        if ($function === 'map_meta_cap') {
            error_log('[FP-PerfSuite] map_meta_cap doing_it_wrong trace: ' . $trace);
            return;
        }

        if ($function === '_load_textdomain_just_in_time') {
            if (class_exists('\FP\PerfSuite\Utils\ErrorHandler')) {
                ErrorHandler::handleSilently(
                    new \RuntimeException('load_textdomain_early: ' . $message),
                    'EnvironmentGuard trace: ' . $trace
                );
            }
        }
    }

    /**
     * Traccia controlli di capability non validi
     * 
     * @param array $userCaps Capabilities dell'utente
     * @param array $requiredCaps Capabilities richieste
     * @param array $args Argomenti
     * @return array Capabilities dell'utente
     */
    public static function traceInvalidMetaCapChecks(array $userCaps, array $requiredCaps, array $args): array
    {
        if (empty($args)) {
            return $userCaps;
        }

        $capability = $args[0];
        $objectId = $args[2] ?? null;

        if (!in_array($capability, ['delete_post', 'edit_post', 'read_post'], true)) {
            return $userCaps;
        }

        if (!empty($objectId)) {
            return $userCaps;
        }

        static $logged = false;

        if ($logged || !function_exists('wp_debug_backtrace_summary')) {
            return $userCaps;
        }

        $logged = true;
        $summary = wp_debug_backtrace_summary(null, 0, false);
        $trace = is_array($summary) ? print_r($summary, true) : (string) $summary;
        if (class_exists('\FP\PerfSuite\Utils\ErrorHandler')) {
            ErrorHandler::handleSilently(
                new \RuntimeException(sprintf('Invalid %s check without object id', $capability)),
                'EnvironmentGuard trace: ' . $trace
            );
        }

        return $userCaps;
    }
}
















