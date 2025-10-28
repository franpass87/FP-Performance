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
            Logger::info('Installation recovery successful');
        } else {
            Logger::error('Installation recovery failed');
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
        
        if (!is_array($uploadDir) || empty($uploadDir['basedir'])) {
            return false;
        }
        
        $baseDir = $uploadDir['basedir'];

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
        // Ottieni limite corrente
        $currentLimit = ini_get('memory_limit');
        $currentBytes = self::parseMemorySize($currentLimit);
        $targetBytes = 268435456; // 256MB
        
        Logger::info('Attempting memory limit recovery', [
            'current' => $currentLimit,
            'current_bytes' => $currentBytes,
            'target' => '256M',
        ]);
        
        // FIX BUG #23: Tenta di aumentare SOLO se il limite è insufficiente
        if ($currentBytes > 0 && $currentBytes < $targetBytes) {
            if (function_exists('ini_set')) {
                $result = @ini_set('memory_limit', '256M');
                
                if ($result !== false) {
                    $newLimit = ini_get('memory_limit');
                    Logger::info('Memory limit increased successfully', [
                        'from' => $currentLimit,
                        'to' => $newLimit,
                    ]);
                } else {
                    Logger::warning('Failed to increase memory limit via ini_set');
                }
            }
        }

        // NOTA: NON usiamo define('WP_MEMORY_LIMIT') perché:
        // 1. È già caricato da WordPress all'avvio
        // 2. Definirlo ora non ha alcun effetto
        // 3. Può generare warning se già definito

        // Disabilita operazioni memory-intensive
        update_option('fp_ps_disable_batch_operations', true);
        update_option('fp_ps_recovery_mode', 'memory_limit');
        
        // Suggerisci all'utente di modificare wp-config.php
        update_option('fp_ps_recovery_suggestion', 
            __('Aggiungi al wp-config.php: define(\'WP_MEMORY_LIMIT\', \'256M\');', 'fp-performance-suite')
        );
        
        return true;
    }
    
    /**
     * Parse memory size string (es. "128M", "1G")
     */
    private static function parseMemorySize(string $size): int
    {
        $size = trim($size);
        
        if ($size === '-1') {
            return PHP_INT_MAX; // Unlimited
        }
        
        $value = (int) $size;
        $unit = strtoupper(substr($size, -1));
        
        switch ($unit) {
            case 'G':
                $value *= 1024;
                // fall through
            case 'M':
                $value *= 1024;
                // fall through
            case 'K':
                $value *= 1024;
        }
        
        return $value;
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
        // FIX BUG #24: Allineato a PHP 8.0+
        $requiredPhpVersion = '8.0.0';
        $phpVersionOk = version_compare(PHP_VERSION, $requiredPhpVersion, '>=');
        
        $results = [
            'php_version' => [
                'status' => $phpVersionOk,
                'current' => PHP_VERSION,
                'required' => $requiredPhpVersion,
                'message' => $phpVersionOk
                    ? sprintf(__('✅ PHP %s (Richiesto: %s+)', 'fp-performance-suite'), PHP_VERSION, $requiredPhpVersion)
                    : sprintf(__('❌ PHP %s - Necessario %s+', 'fp-performance-suite'), PHP_VERSION, $requiredPhpVersion),
            ],
            'extensions' => [],
            'permissions' => [],
        ];

        // Test estensioni con messaggi dettagliati
        $requiredExtensions = ['json', 'mbstring', 'fileinfo'];
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $results['extensions'][$ext] = [
                'loaded' => $loaded,
                'message' => $loaded 
                    ? sprintf(__('✅ %s caricato', 'fp-performance-suite'), $ext)
                    : sprintf(__('❌ %s mancante', 'fp-performance-suite'), $ext),
            ];
        }

        // Test permessi con dettagli
        $uploadDir = wp_upload_dir();
        $uploadsWritable = is_array($uploadDir) && !empty($uploadDir['basedir']) && is_writable($uploadDir['basedir']);
        
        $results['permissions']['uploads'] = [
            'writable' => $uploadsWritable,
            'path' => (is_array($uploadDir) && isset($uploadDir['basedir'])) ? $uploadDir['basedir'] : 'N/A',
            'message' => $uploadsWritable
                ? __('✅ Directory uploads scrivibile', 'fp-performance-suite')
                : __('❌ Directory uploads non scrivibile', 'fp-performance-suite'),
        ];

        return $results;
    }
}

