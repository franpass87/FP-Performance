<?php

/**
 * Installation Recovery Utility
 * 
 * Fornisce strumenti per il recupero automatico da errori di installazione comuni
 *
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 */

namespace FP\PerfSuite\Utils;

class InstallationRecovery
{
    /**
     * Tenta il recupero automatico da un errore di installazione
     * 
     * @param array $error Dettagli dell'errore
     * @return bool True se il recupero ha avuto successo
     */
    public static function attemptRecovery(array $error): bool
    {
        $errorType = $error['type'] ?? 'unknown';
        
        $recoveryMethods = [
            'permissions' => [self::class, 'fixPermissions'],
            'missing_directories' => [self::class, 'createDirectories'],
            'missing_class' => [self::class, 'verifyFiles'],
        ];

        if (isset($recoveryMethods[$errorType]) && is_callable($recoveryMethods[$errorType])) {
            try {
                return call_user_func($recoveryMethods[$errorType]);
            } catch (\Throwable $e) {
                Logger::error('Recovery attempt failed', $e);
                return false;
            }
        }

        return false;
    }

    /**
     * Tenta di correggere i permessi delle directory
     * 
     * @return bool True se i permessi sono stati corretti
     */
    private static function fixPermissions(): bool
    {
        $uploadDir = wp_upload_dir();
        $baseDir = $uploadDir['basedir'];
        
        if (empty($baseDir) || !file_exists($baseDir)) {
            return false;
        }

        $directories = [
            $baseDir . '/fp-performance-suite',
            $baseDir . '/fp-performance-suite/cache',
            $baseDir . '/fp-performance-suite/logs',
        ];

        $success = true;
        foreach ($directories as $dir) {
            if (!file_exists($dir)) {
                if (!wp_mkdir_p($dir)) {
                    $success = false;
                    continue;
                }
            }

            // Tenta di rendere la directory scrivibile
            if (!is_writable($dir)) {
                // Prova con chmod 755
                if (!@chmod($dir, 0755)) {
                    $success = false;
                }
            }

            // Aggiungi protezione .htaccess
            $htaccessFile = $dir . '/.htaccess';
            if (!file_exists($htaccessFile)) {
                @file_put_contents($htaccessFile, "Order deny,allow\nDeny from all\n");
            }

            // Aggiungi index.php per sicurezza
            $indexFile = $dir . '/index.php';
            if (!file_exists($indexFile)) {
                @file_put_contents($indexFile, "<?php\n// Silence is golden\n");
            }
        }

        if ($success) {
            Logger::info('Permissions fixed successfully');
        }

        return $success;
    }

    /**
     * Crea le directory necessarie
     * 
     * @return bool True se le directory sono state create
     */
    private static function createDirectories(): bool
    {
        return self::fixPermissions(); // Stesso processo
    }

    /**
     * Verifica che tutti i file essenziali del plugin siano presenti
     * 
     * @return bool True se tutti i file sono presenti
     */
    private static function verifyFiles(): bool
    {
        $requiredFiles = [
            FP_PERF_SUITE_DIR . '/fp-performance-suite.php',
            FP_PERF_SUITE_DIR . '/src/Plugin.php',
            FP_PERF_SUITE_DIR . '/src/ServiceContainer.php',
            FP_PERF_SUITE_DIR . '/src/Utils/Logger.php',
        ];

        $missingFiles = [];
        foreach ($requiredFiles as $file) {
            if (!file_exists($file)) {
                $missingFiles[] = basename($file);
            }
        }

        if (!empty($missingFiles)) {
            Logger::error('Missing critical files', ['files' => $missingFiles]);
            return false;
        }

        return true;
    }

    /**
     * Esegue un controllo diagnostico completo del sistema
     * 
     * @return array Report diagnostico
     */
    public static function runDiagnostics(): array
    {
        $report = [
            'timestamp' => current_time('mysql'),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'unknown',
            'checks' => [],
        ];

        // Check 1: Versione PHP
        $report['checks']['php_version'] = [
            'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'pass' : 'fail',
            'current' => PHP_VERSION,
            'required' => '7.4.0',
        ];

        // Check 2: Estensioni PHP
        $requiredExtensions = ['json', 'mbstring', 'fileinfo'];
        $loadedExtensions = [];
        foreach ($requiredExtensions as $ext) {
            $loadedExtensions[$ext] = extension_loaded($ext);
        }
        $report['checks']['php_extensions'] = [
            'status' => !in_array(false, $loadedExtensions, true) ? 'pass' : 'fail',
            'extensions' => $loadedExtensions,
        ];

        // Check 3: Permessi directory
        $uploadDir = wp_upload_dir();
        $baseDir = $uploadDir['basedir'];
        $report['checks']['directory_permissions'] = [
            'status' => (!empty($baseDir) && is_writable($baseDir)) ? 'pass' : 'fail',
            'upload_dir' => $baseDir,
            'writable' => is_writable($baseDir),
        ];

        // Check 4: Memoria disponibile
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = self::convertToBytes($memoryLimit);
        $report['checks']['memory_limit'] = [
            'status' => ($memoryLimitBytes >= 128 * 1024 * 1024) ? 'pass' : 'warning',
            'current' => $memoryLimit,
            'recommended' => '128M',
        ];

        // Check 5: File essenziali
        $report['checks']['essential_files'] = [
            'status' => self::verifyFiles() ? 'pass' : 'fail',
        ];

        // Check 6: Database
        global $wpdb;
        $report['checks']['database'] = [
            'status' => !empty($wpdb) && $wpdb->check_connection() ? 'pass' : 'fail',
            'prefix' => $wpdb->prefix,
        ];

        return $report;
    }

    /**
     * Converte una stringa di memoria in bytes
     * 
     * @param string $value Valore di memoria (es. '128M')
     * @return int Bytes
     */
    private static function convertToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

        switch ($unit) {
            case 'g':
                $value *= 1024;
                // fall through
            case 'm':
                $value *= 1024;
                // fall through
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Genera un report HTML dei diagnostici
     * 
     * @param array $diagnostics Report diagnostico
     * @return string HTML del report
     */
    public static function generateDiagnosticReport(array $diagnostics): string
    {
        $html = '<div style="font-family: monospace; font-size: 12px; background: #f0f0f1; padding: 15px; border-radius: 4px;">';
        $html .= '<h4 style="margin-top: 0;">📊 Report Diagnostico</h4>';
        
        $html .= '<p><strong>Timestamp:</strong> ' . esc_html($diagnostics['timestamp']) . '</p>';
        $html .= '<p><strong>PHP:</strong> ' . esc_html($diagnostics['php_version']) . '</p>';
        $html .= '<p><strong>WordPress:</strong> ' . esc_html($diagnostics['wp_version']) . '</p>';
        $html .= '<p><strong>Plugin:</strong> ' . esc_html($diagnostics['plugin_version']) . '</p>';
        
        $html .= '<h5>Controlli di Sistema:</h5>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        
        foreach ($diagnostics['checks'] as $checkName => $checkData) {
            $status = $checkData['status'] ?? 'unknown';
            $statusIcon = $status === 'pass' ? '✅' : ($status === 'warning' ? '⚠️' : '❌');
            
            $html .= '<tr style="border-bottom: 1px solid #ddd;">';
            $html .= '<td style="padding: 8px;">' . $statusIcon . ' ' . esc_html(ucwords(str_replace('_', ' ', $checkName))) . '</td>';
            $html .= '<td style="padding: 8px;">' . esc_html($status) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $html .= '</div>';
        
        return $html;
    }
}
