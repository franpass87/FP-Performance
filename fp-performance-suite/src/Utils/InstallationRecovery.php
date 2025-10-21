<?php

namespace FP\PerfSuite\Utils;

/**
 * Installation Recovery
 * 
 * Sistema di recupero per errori di installazione
 *
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class InstallationRecovery
{
    private const RECOVERY_LOG = 'fp_recovery_log';

    /**
     * Tenta il recupero automatico
     */
    public static function attemptRecovery(array $errorDetails): bool
    {
        self::logRecoveryAttempt($errorDetails);

        $recovered = false;

        // Recupero basato sul tipo di errore
        switch ($errorDetails['type'] ?? 'unknown') {
            case 'php_version':
                $recovered = self::recoverPhpVersion();
                break;

            case 'php_extension':
                $recovered = self::recoverPhpExtensions($errorDetails);
                break;

            case 'permissions':
                $recovered = self::recoverPermissions($errorDetails);
                break;

            case 'missing_class':
                $recovered = self::recoverMissingClass($errorDetails);
                break;

            case 'memory_limit':
                $recovered = self::recoverMemoryLimit();
                break;

            default:
                $recovered = self::recoverGeneric($errorDetails);
        }

        if ($recovered) {
            Logger::info('Installation recovery successful', $errorDetails);
        } else {
            Logger::error('Installation recovery failed', $errorDetails);
        }

        return $recovered;
    }

    /**
     * Recupero errore versione PHP
     */
    private static function recoverPhpVersion(): bool
    {
        // Non possiamo cambiare versione PHP, ma possiamo disabilitare funzionalità avanzate
        update_option('fp_ps_safe_mode', true);
        update_option('fp_ps_recovery_mode', 'php_version');
        
        return true;
    }

    /**
     * Recupero estensioni PHP
     */
    private static function recoverPhpExtensions(array $errorDetails): bool
    {
        // Disabilita funzionalità che richiedono estensioni specifiche
        $message = $errorDetails['message'] ?? '';

        if (strpos($message, 'json') !== false) {
            update_option('fp_ps_disable_json_features', true);
        }

        if (strpos($message, 'mbstring') !== false) {
            update_option('fp_ps_disable_mbstring_features', true);
        }

        update_option('fp_ps_safe_mode', true);
        
        return true;
    }

    /**
     * Recupero permessi
     */
    private static function recoverPermissions(array $errorDetails): bool
    {
        $uploadDir = wp_upload_dir();
        $baseDir = $uploadDir['basedir'];

        if (empty($baseDir)) {
            return false;
        }

        // Tenta di creare directory con permessi corretti
        $dirs = [
            $baseDir . '/fp-performance-suite',
            $baseDir . '/fp-performance-suite/cache',
            $baseDir . '/fp-performance-suite/logs',
        ];

        $success = true;
        foreach ($dirs as $dir) {
            if (!file_exists($dir)) {
                $result = wp_mkdir_p($dir);
                if ($result) {
                    @chmod($dir, 0755);
                } else {
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Recupero classe mancante
     */
    private static function recoverMissingClass(array $errorDetails): bool
    {
        $message = $errorDetails['message'] ?? '';

        // Estrai nome classe
        if (preg_match("/Class ['\"]([^'\"]+)['\"]/", $message, $matches)) {
            $className = $matches[1];
            
            // Disabilita funzionalità correlata
            update_option('fp_ps_disabled_classes', get_option('fp_ps_disabled_classes', []) + [$className]);
        }

        update_option('fp_ps_safe_mode', true);
        
        return true;
    }

    /**
     * Recupero limite memoria
     */
    private static function recoverMemoryLimit(): bool
    {
        // Tenta di aumentare limite memoria
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '256M');
        }

        if (!defined('WP_MEMORY_LIMIT')) {
            define('WP_MEMORY_LIMIT', '256M');
        }

        // Disabilita operazioni pesanti
        update_option('fp_ps_disable_batch_operations', true);
        
        return true;
    }

    /**
     * Recupero generico
     */
    private static function recoverGeneric(array $errorDetails): bool
    {
        // Abilita modalità sicura generica
        update_option('fp_ps_safe_mode', true);
        update_option('fp_ps_recovery_mode', 'generic');
        
        // Disabilita funzionalità avanzate
        update_option('fp_ps_disable_advanced_features', true);
        
        return true;
    }

    /**
     * Logga tentativo di recupero
     */
    private static function logRecoveryAttempt(array $errorDetails): void
    {
        $log = get_option(self::RECOVERY_LOG, []);

        $log[] = [
            'timestamp' => time(),
            'error' => $errorDetails,
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
        ];

        // Mantieni solo ultimi 10
        if (count($log) > 10) {
            $log = array_slice($log, -10);
        }

        update_option(self::RECOVERY_LOG, $log, false);
    }

    /**
     * Ottiene log recovery
     */
    public static function getRecoveryLog(): array
    {
        return get_option(self::RECOVERY_LOG, []);
    }

    /**
     * Pulisce log recovery
     */
    public static function clearRecoveryLog(): bool
    {
        return delete_option(self::RECOVERY_LOG);
    }

    /**
     * Verifica se in safe mode
     */
    public static function isInSafeMode(): bool
    {
        return (bool) get_option('fp_ps_safe_mode', false);
    }

    /**
     * Disabilita safe mode
     */
    public static function disableSafeMode(): bool
    {
        delete_option('fp_ps_safe_mode');
        delete_option('fp_ps_recovery_mode');
        delete_option('fp_ps_disabled_classes');
        delete_option('fp_ps_disable_advanced_features');
        delete_option('fp_ps_disable_batch_operations');
        delete_option('fp_ps_disable_json_features');
        delete_option('fp_ps_disable_mbstring_features');
        
        return true;
    }

    /**
     * Test configurazione
     */
    public static function testConfiguration(): array
    {
        $results = [
            'php_version' => [
                'status' => version_compare(PHP_VERSION, '7.4.0', '>='),
                'message' => PHP_VERSION,
            ],
            'extensions' => [],
            'permissions' => [],
        ];

        // Test estensioni
        $requiredExtensions = ['json', 'mbstring', 'fileinfo'];
        foreach ($requiredExtensions as $ext) {
            $results['extensions'][$ext] = extension_loaded($ext);
        }

        // Test permessi
        $uploadDir = wp_upload_dir();
        $results['permissions']['uploads_writable'] = is_writable($uploadDir['basedir']);

        return $results;
    }
}

