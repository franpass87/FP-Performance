<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://francescopasseri.com
 * Description: Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.
 * Version: 1.5.1
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

// Plugin principale FP Performance Suite

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
        // ping() è deprecated in PHP 8.4, verifica semplicemente che l'oggetto esista
        // Se dbh è un oggetto mysqli valido, la connessione è disponibile
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

// Autoload
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function ($class) {
        if (strpos($class, 'FP\\PerfSuite\\') !== 0) {
            return;
        }
        $path = __DIR__ . '/src/' . str_replace(['FP\\PerfSuite\\', '\\'], ['', '/'], $class) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    });
}

defined('FP_PERF_SUITE_VERSION') || define('FP_PERF_SUITE_VERSION', '1.5.1');
defined('FP_PERF_SUITE_DIR') || define('FP_PERF_SUITE_DIR', __DIR__);
defined('FP_PERF_SUITE_FILE') || define('FP_PERF_SUITE_FILE', __FILE__);

// NON usare "use" statement globale - carica la classe solo quando serve!

// Activation/Deactivation hooks con caricamento lazy
register_activation_hook(__FILE__, static function () {
    // Carica la classe solo quando necessario
    if (!class_exists('FP\\PerfSuite\\Plugin')) {
        $pluginFile = __DIR__ . '/src/Plugin.php';
        if (!file_exists($pluginFile)) {
            wp_die('Errore critico: File Plugin.php non trovato in ' . esc_html($pluginFile));
        }
        require_once $pluginFile;
    }
    
    try {
        FP\PerfSuite\Plugin::onActivate();
    } catch (\Throwable $e) {
        wp_die(sprintf(
            '<h1>Errore di Attivazione Plugin</h1><p><strong>Messaggio:</strong> %s</p><p><strong>File:</strong> %s:%d</p>',
            esc_html($e->getMessage()),
            esc_html($e->getFile()),
            $e->getLine()
        ));
    }
});

register_deactivation_hook(__FILE__, static function () {
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
});

if (function_exists('add_action')) {
    // Usa una variabile globale per prevenire inizializzazioni multiple
    global $fp_perf_suite_initialized;
    if (!isset($fp_perf_suite_initialized)) {
        $fp_perf_suite_initialized = false;
    }
    
    add_action('plugins_loaded', static function () {
        global $fp_perf_suite_initialized;
        // Prevenire inizializzazioni multiple usando la variabile globale
        if ($fp_perf_suite_initialized) {
            return;
        }
        
        // Verifica che il file Plugin.php esista PRIMA di provare a caricarlo
        $pluginFile = __DIR__ . '/src/Plugin.php';
        
        if (!file_exists($pluginFile)) {
            fp_perf_suite_safe_log(
                'ERRORE CRITICO: File Plugin.php non trovato in ' . $pluginFile,
                'ERROR'
            );
            
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
            fp_perf_suite_safe_log(
                'Errore caricamento Plugin.php: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
                'ERROR'
            );
            
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
        
        // Verifica disponibilità database PRIMA di inizializzare
        if (!fp_perf_suite_is_db_available()) {
            fp_perf_suite_safe_log(
                'Database connection not available. Plugin initialization delayed.',
                'WARNING'
            );
            
            // Riprova dopo che WordPress è completamente caricato
            add_action('wp_loaded', static function () {
                global $fp_perf_suite_initialized;
                // Prevenire inizializzazioni multiple anche qui
                if ($fp_perf_suite_initialized) {
                    return;
                }
                
                if (fp_perf_suite_is_db_available()) {
                    try {
                        \FP\PerfSuite\Plugin::init();
                        // Marca come inizializzato
                        $fp_perf_suite_initialized = true;
                    } catch (\Throwable $e) {
                        fp_perf_suite_safe_log(
                            'Plugin initialization failed: ' . $e->getMessage(),
                            'ERROR'
                        );
                    }
                } else {
                    fp_perf_suite_safe_log(
                        'Database still unavailable after wp_loaded. Plugin running in safe mode.',
                        'ERROR'
                    );
                }
            }, 999);
            
            return;
        }
        
        // Database disponibile, inizializza normalmente
        try {
            \FP\PerfSuite\Plugin::init();
            // Marca come inizializzato usando la variabile globale
            $fp_perf_suite_initialized = true;
        } catch (\Throwable $e) {
            fp_perf_suite_safe_log(
                'Plugin initialization error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
                'ERROR'
            );
            
            // Non bloccare WordPress, continua in modalità sicura
            add_action('admin_notices', static function () use ($e) {
                if (current_user_can('manage_options')) {
                    printf(
                        '<div class="notice notice-error"><p><strong>FP Performance Suite:</strong> Errore di inizializzazione: %s</p></div>',
                        esc_html($e->getMessage())
                    );
                }
            });
        }
    });
}

