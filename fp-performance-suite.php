<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://francescopasseri.com
 * Description: Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.
 * Version: 1.7.0
 * Author: Francesco Passeri
 * Author URI: https://francescopasseri.com
 * Text Domain: fp-performance-suite
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/franpass87/FP-Performance
 * Primary Branch: main
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

// Last modified: 2025-11-02 - v1.7.0 Critical Features Release
// Plugin principale FP Performance Suite - OTTIMIZZATO PER SHARED HOSTING
// NEW in v1.7.0: Instant Page, Delay JS, Embed Facades, WooCommerce Optimizations

// Abilita SAVEQUERIES per admin se configurato (attivato dopo che WordPress è pronto)
add_action('plugins_loaded', function() {
    if (!defined('SAVEQUERIES')) {
        $saveQueriesAdminOnly = get_option('fp_ps_savequeries_admin_only', false);
        
        // FIX CRITICO: Verifica che tutte le funzioni esistano prima di chiamarle
        if ($saveQueriesAdminOnly && 
            function_exists('is_user_logged_in') && 
            function_exists('current_user_can') && 
            is_user_logged_in() && 
            current_user_can('manage_options')) {
            define('SAVEQUERIES', true);
        }
    }
}, 1);

/**
 * Verifica se il database WordPress è disponibile
 * 
 * @return bool True se il database è disponibile
 */
function fp_perf_suite_is_db_available(): bool {
    global $wpdb;
    
    // Verifica che $wpdb esista
    if (!isset($wpdb) || !is_object($wpdb)) {
        return false;
    }
    
    // Verifica che la connessione sia attiva
    if (!isset($wpdb->dbh)) {
        return false;
    }
    
    // Per mysqli - Verifica connessione
    if (is_object($wpdb->dbh) && $wpdb->dbh instanceof \mysqli) {
        return true;
    }
    
    // Fallback: tenta una query semplice
    try {
        $result = @$wpdb->query('SELECT 1');
        return $result !== false;
    } catch (\Throwable $e) {
        return false;
    }
}

/**
 * Log sicuro senza dipendenze dal database
 * 
 * @param string $message Messaggio da loggare
 * @param string $level Livello di log
 */
function fp_perf_suite_safe_log(string $message, string $level = 'ERROR'): void {
    $timestamp = gmdate('Y-m-d H:i:s');
    $logMessage = sprintf(
        '[%s] [FP-PerfSuite] [%s] %s',
        $timestamp,
        $level,
        $message
    );
    
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log($logMessage);
    }
}

// NON caricare file di debug che causano white screen
// Questi file sono stati identificati come causa del problema

// Autoload Composer con gestione errori
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    try {
        require_once $autoload;
    } catch (\Throwable $e) {
        fp_perf_suite_safe_log('Errore caricamento autoloader Composer: ' . $e->getMessage());
    }
}

// Autoloader PSR-4 per FP\PerfSuite\ (sempre attivo) - FIXED
spl_autoload_register(static function ($class) {
    // Verifica che la classe appartenga al namespace FP\PerfSuite
    if (strpos($class, 'FP\\PerfSuite\\') !== 0) {
        return;
    }
    
    try {
        // Rimuovi il prefisso namespace base
        $relativePath = substr($class, strlen('FP\\PerfSuite\\'));
        
        // Converti i namespace separator in directory separator
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);
        
        // Costruisci il path completo
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relativePath . '.php';
        
        // Carica il file se esiste
        if (is_readable($path)) {
            require_once $path;
        }
    } catch (\Throwable $e) {
        fp_perf_suite_safe_log('Errore autoloader per classe ' . $class . ': ' . $e->getMessage());
    }
});

defined('FP_PERF_SUITE_VERSION') || define('FP_PERF_SUITE_VERSION', '1.7.0');
defined('FP_PERF_SUITE_DIR') || define('FP_PERF_SUITE_DIR', __DIR__);
defined('FP_PERF_SUITE_FILE') || define('FP_PERF_SUITE_FILE', __FILE__);

// Activation/Deactivation hooks con gestione errori migliorata - SOLO UNA VOLTA
if (!function_exists('fp_perf_suite_activation_handler')) {
    function fp_perf_suite_activation_handler() {
        try {
            // Carica la classe solo quando necessario
            if (!class_exists('FP\\PerfSuite\\Plugin')) {
                $pluginFile = __DIR__ . '/src/Plugin.php';
                if (!file_exists($pluginFile)) {
                    wp_die('Errore critico: File Plugin.php non trovato in ' . esc_html($pluginFile));
                }
                require_once $pluginFile;
            }
            
            FP\PerfSuite\Plugin::onActivate();
        } catch (\Throwable $e) {
            fp_perf_suite_safe_log('Errore attivazione: ' . $e->getMessage());
            wp_die(sprintf(
                '<h1>Errore di Attivazione Plugin</h1><p><strong>Messaggio:</strong> %s</p><p><strong>File:</strong> %s:%d</p>',
                esc_html($e->getMessage()),
                esc_html($e->getFile()),
                $e->getLine()
            ));
        }
    }
    
    function fp_perf_suite_deactivation_handler() {
        try {
            // Carica la classe solo quando necessario
            if (!class_exists('FP\\PerfSuite\\Plugin')) {
                $pluginFile = __DIR__ . '/src/Plugin.php';
                if (file_exists($pluginFile)) {
                    require_once $pluginFile;
                }
            }
            
            if (class_exists('FP\\PerfSuite\\Plugin')) {
                FP\PerfSuite\Plugin::onDeactivate();
            }
        } catch (\Throwable $e) {
            fp_perf_suite_safe_log('Errore disattivazione: ' . $e->getMessage());
        }
    }
    
    register_activation_hook(__FILE__, 'fp_perf_suite_activation_handler');
    register_deactivation_hook(__FILE__, 'fp_perf_suite_deactivation_handler');
}

// Prevenzione caricamento multiplo
if (defined('FP_PERF_SUITE_LOADED')) {
    return;
}
define('FP_PERF_SUITE_LOADED', true);

// Sistema di inizializzazione - FIX RACE CONDITION
if (function_exists('add_action')) {
    // Inizializzazione con protezione contro race conditions
    add_action('init', static function () {
        // Inizializzazione sicura con try-catch
        try {
            fp_perf_suite_initialize_plugin_fixed();
        } catch (\Throwable $e) {
            fp_perf_suite_safe_log('Errore inizializzazione plugin: ' . $e->getMessage());
            
            // Mostra avviso in admin se possibile
            if (is_admin() && function_exists('current_user_can') && current_user_can('manage_options')) {
                add_action('admin_notices', static function () use ($e) {
                    printf(
                        '<div class="notice notice-error"><p><strong>FP Performance Suite:</strong> Errore di inizializzazione: %s</p></div>',
                        esc_html($e->getMessage())
                    );
                });
            }
        }
    }, 1);
}

/**
 * Funzione di inizializzazione del plugin
 * FIX RACE CONDITION: Il controllo di inizializzazione è ora gestito da Plugin::init()
 */
function fp_perf_suite_initialize_plugin_fixed(): void {
    
    // Verifica che il file Plugin.php esista
    $pluginFile = __DIR__ . '/src/Plugin.php';
    
    if (!file_exists($pluginFile)) {
        fp_perf_suite_safe_log('ERRORE CRITICO: File Plugin.php non trovato in ' . $pluginFile, 'ERROR');
        
        add_action('admin_notices', static function () use ($pluginFile) {
            if (current_user_can('manage_options')) {
                printf(
                    '<div class="notice notice-error"><p><strong>FP Performance Suite - Errore Critico:</strong> File Plugin.php non trovato.<br>Percorso cercato: <code>%s</code><br>Reinstalla il plugin completamente.</p></div>',
                    esc_html($pluginFile)
                );
            }
        });
        
        return;
    }
    
    // Carica la classe Plugin con protezione da errori
    try {
        if (!class_exists('FP\\PerfSuite\\Plugin')) {
            require_once $pluginFile;
        }
        
        // Verifica che la classe sia stata caricata correttamente
        if (!class_exists('FP\\PerfSuite\\Plugin')) {
            throw new \RuntimeException('Classe Plugin non trovata dopo require_once. Possibile errore di sintassi nel file.');
        }
        
    } catch (\Throwable $e) {
        fp_perf_suite_safe_log('Errore caricamento Plugin.php: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 'ERROR');
        
        add_action('admin_notices', static function () use ($e) {
            if (current_user_can('manage_options')) {
                printf(
                    '<div class="notice notice-error"><p><strong>FP Performance Suite - Errore di Caricamento:</strong><br>%s<br><small>File: %s:%d</small></p></div>',
                    esc_html($e->getMessage()),
                    esc_html($e->getFile()),
                    $e->getLine()
                );
            }
        });
        
        return;
    }
    
    // Inizializza con protezione massima
    try {
        \FP\PerfSuite\Plugin::init();
        fp_perf_suite_safe_log('Plugin initialized successfully', 'DEBUG');
        
    } catch (\Throwable $e) {
        fp_perf_suite_safe_log('Plugin initialization error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 'ERROR');
        
        // Non bloccare WordPress, continua in modalità sicura
        add_action('admin_notices', static function () use ($e) {
            if (current_user_can('manage_options')) {
                printf(
                    '<div class="notice notice-error"><p><strong>FP Performance Suite:</strong> Errore di inizializzazione: %s<br><small>File: %s:%d</small></p></div>',
                    esc_html($e->getMessage()),
                    esc_html($e->getFile()),
                    $e->getLine()
                );
            }
        });
    }
}

?>
