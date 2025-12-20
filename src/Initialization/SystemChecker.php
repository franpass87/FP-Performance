<?php

namespace FP\PerfSuite\Initialization;

/**
 * Verifica i requisiti di sistema prima dell'attivazione
 * 
 * @package FP\PerfSuite\Initialization
 * @author Francesco Passeri
 */
class SystemChecker
{
    /**
     * Esegue controlli preliminari di sistema
     * 
     * @throws \RuntimeException se i requisiti minimi non sono soddisfatti
     */
    public function performChecks(): void
    {
        $errors = [];

        // Verifica versione PHP minima
        if (version_compare(PHP_VERSION, '7.4.0', '<')) {
            $errors[] = sprintf(
                'PHP 7.4.0 o superiore è richiesto. Versione corrente: %s',
                PHP_VERSION
            );
        }

        // Verifica estensioni PHP richieste
        $requiredExtensions = ['json', 'mbstring', 'fileinfo'];
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = sprintf('Estensione PHP richiesta non trovata: %s', $ext);
            }
        }

        // Verifica permessi di scrittura
        $uploadDir = wp_upload_dir();
        if (is_array($uploadDir) && !empty($uploadDir['basedir']) && !is_writable($uploadDir['basedir'])) {
            $errors[] = sprintf(
                'Directory uploads non scrivibile: %s. Verifica i permessi.',
                $uploadDir['basedir']
            );
        }

        // Verifica disponibilità funzioni WordPress critiche
        $requiredFunctions = ['wp_upload_dir', 'update_option', 'add_action', 'get_option'];
        foreach ($requiredFunctions as $func) {
            if (!function_exists($func)) {
                $errors[] = sprintf('Funzione WordPress richiesta non disponibile: %s', $func);
            }
        }

        // Verifica disponibilità classe WP_Filesystem
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (!empty($errors)) {
            throw new \RuntimeException(
                'Requisiti di sistema non soddisfatti: ' . implode('; ', $errors)
            );
        }
    }
}
















