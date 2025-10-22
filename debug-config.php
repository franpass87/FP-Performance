<?php
/**
 * Configurazione Debug per FP Performance Suite
 * 
 * Questo file contiene le configurazioni per controllare il logging
 * e ridurre il debug eccessivo in produzione.
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

// Disabilita completamente i log di debug in produzione
// Aggiungi questa riga al wp-config.php per disabilitare tutti i log DEBUG:
// define('FP_PS_DISABLE_DEBUG_LOGS', true);

// Configurazione del livello di logging
// Valori possibili: ERROR, WARNING, INFO, DEBUG
// Aggiungi al wp-config.php per impostare il livello:
// define('FP_PS_LOG_LEVEL', 'ERROR');

/**
 * Configurazioni consigliate per ambiente:
 * 
 * SVILUPPO:
 * - WP_DEBUG = true
 * - FP_PS_DISABLE_DEBUG_LOGS = false (o non definito)
 * - FP_PS_LOG_LEVEL = 'DEBUG'
 * 
 * STAGING:
 * - WP_DEBUG = true
 * - FP_PS_DISABLE_DEBUG_LOGS = false
 * - FP_PS_LOG_LEVEL = 'INFO'
 * 
 * PRODUZIONE:
 * - WP_DEBUG = false
 * - FP_PS_DISABLE_DEBUG_LOGS = true
 * - FP_PS_LOG_LEVEL = 'ERROR'
 */

// Hook per personalizzare il logging
add_action('init', function() {
    // Esempio: Disabilita log di debug solo per utenti non admin
    if (!is_admin() && !current_user_can('manage_options')) {
        if (!defined('FP_PS_DISABLE_DEBUG_LOGS')) {
            define('FP_PS_DISABLE_DEBUG_LOGS', true);
        }
    }
});

// Hook per log personalizzati
add_action('fp_ps_log_debug', function($message, $context) {
    // Esempio: Log personalizzato per debug specifici
    if (strpos($message, 'Cache file count') !== false) {
        // Log solo se ci sono file cache
        if (isset($context['count']) && $context['count'] > 0) {
            error_log("FP-PerfSuite Custom: Cache files found: {$context['count']}");
        }
    }
});

// Hook per monitoraggio performance
add_action('fp_ps_log_error', function($message, $exception, $context) {
    // Esempio: Invia notifiche per errori critici
    if (strpos($message, 'Failed to') !== false) {
        // Qui potresti inviare una notifica email o Slack
        error_log("FP-PerfSuite CRITICAL: {$message}");
    }
});
