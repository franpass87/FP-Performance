<?php
/**
 * Plugin Name: FP Performance Suite (Safe Mode)
 * Plugin URI: https://francescopasseri.com
 * Description: Versione sicura per risolvere problemi di white screen. Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.
 * Version: 1.5.1-safe
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

// Plugin principale FP Performance Suite - Modalità Sicura

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
        '[%s] [FP-PerfSuite-Safe] [%s] %s',
        $timestamp,
        $level,
        $message
    );
    
    if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
        error_log($logMessage);
    }
}

// NON caricare file di debug che potrebbero causare problemi
// Commentati per sicurezza
/*
$fixFile = __DIR__ . '/fix-register-meta-errors.php';
if (file_exists($fixFile)) {
    require_once $fixFile;
}

if (defined('WP_DEBUG') && WP_DEBUG) {
    $debugFile = __DIR__ . '/debug-initialization-issues.php';
    if (file_exists($debugFile)) {
        require_once $debugFile;
    }
}

$gitUpdaterFix = __DIR__ . '/fix-fp-git-updater-deprecated.php';
if (file_exists($gitUpdaterFix)) {
    require_once $gitUpdaterFix;
}
*/

// Autoload con gestione errori migliorata
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    try {
        require_once $autoload;
    } catch (\Throwable $e) {
        fp_perf_suite_safe_log('Errore caricamento autoloader Composer: ' . $e->getMessage());
    }
} else {
    // Autoloader personalizzato con gestione errori
    spl_autoload_register(static function ($class) {
        if (strpos($class, 'FP\\PerfSuite\\') !== 0) {
            return;
        }
        
        try {
            $path = __DIR__ . '/src/' . str_replace(['FP\\PerfSuite\\', '\\'], ['', '/'], $class) . '.php';
            if (file_exists($path)) {
                require_once $path;
            }
        } catch (\Throwable $e) {
            fp_perf_suite_safe_log('Errore autoloader per classe ' . $class . ': ' . $e->getMessage());
        }
    });
}

defined('FP_PERF_SUITE_VERSION') || define('FP_PERF_SUITE_VERSION', '1.5.1-safe');
defined('FP_PERF_SUITE_DIR') || define('FP_PERF_SUITE_DIR', __DIR__);
defined('FP_PERF_SUITE_FILE') || define('FP_PERF_SUITE_FILE', __FILE__);

// Activation/Deactivation hooks con gestione errori migliorata
register_activation_hook(__FILE__, static function () {
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
});

register_deactivation_hook(__FILE__, static function () {
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
});

// Sistema di inizializzazione ultra-sicuro
if (function_exists('add_action')) {
    global $fp_perf_suite_initialized;
    if (!isset($fp_perf_suite_initialized)) {
        $fp_perf_suite_initialized = false;
    }
    
    // Inizializzazione con protezione massima
    add_action('init', static function () {
        global $fp_perf_suite_initialized;
        
        // Prevenire inizializzazioni multiple
        if ($fp_perf_suite_initialized) {
            return;
        }
        
        // Inizializzazione sicura con try-catch
        try {
            fp_perf_suite_initialize_plugin_safe();
        } catch (\Throwable $e) {
            fp_perf_suite_safe_log('Errore inizializzazione plugin: ' . $e->getMessage());
            
            // Mostra avviso in admin se possibile
            if (is_admin() && current_user_can('manage_options')) {
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
 * Funzione di inizializzazione sicura del plugin
 */
function fp_perf_suite_initialize_plugin_safe(): void {
    global $fp_perf_suite_initialized;
    
    // Prevenire inizializzazioni multiple
    if ($fp_perf_suite_initialized) {
        fp_perf_suite_safe_log('Plugin initialization prevented - already initialized', 'DEBUG');
        return;
    }
    
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
        
        // Verifica se è davvero inizializzato
        if (FP\PerfSuite\Plugin::isInitialized()) {
            $fp_perf_suite_initialized = true;
        }
        
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

// Aggiungi avviso di modalità sicura
add_action('admin_notices', static function () {
    if (current_user_can('manage_options')) {
        echo '<div class="notice notice-info"><p><strong>FP Performance Suite:</strong> In esecuzione in modalità sicura per prevenire problemi di white screen.</p></div>';
    }
});
?>
