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

// Carica il fix per gli errori comuni di WordPress
$fixFile = __DIR__ . '/fix-register-meta-errors.php';
if (file_exists($fixFile)) {
    require_once $fixFile;
}

// Carica il debug per i problemi di inizializzazione (solo se WP_DEBUG è attivo)
if (defined('WP_DEBUG') && WP_DEBUG) {
    $debugFile = __DIR__ . '/debug-initialization-issues.php';
    if (file_exists($debugFile)) {
        require_once $debugFile;
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
    // Sistema di inizializzazione semplificato e robusto
    global $fp_perf_suite_initialized;
    if (!isset($fp_perf_suite_initialized)) {
        $fp_perf_suite_initialized = false;
    }
    
    // Inizializza sempre su init per garantire che il menu sia registrato
    add_action('init', static function () {
        global $fp_perf_suite_initialized;
        
        // Prevenire inizializzazioni multiple
        if ($fp_perf_suite_initialized) {
            return;
        }
        
        $fp_perf_suite_initialized = true;
        fp_perf_suite_initialize_plugin();
    }, 1);
    
    // FORZA SEMPRE la registrazione del menu
    add_action('admin_menu', static function () {
        // Controlla se il menu è già stato registrato
        global $menu;
        $menu_already_registered = false;
        
        if (isset($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
                    $menu_already_registered = true;
                    break;
                }
            }
        }
        
        // Se il menu non è stato registrato, registralo SEMPRE
        if (!$menu_already_registered) {
            error_log('[FP Performance Suite] FORZA: Registrazione menu manuale');
            
            add_menu_page(
                'FP Performance Suite',
                'FP Performance',
                'manage_options',
                'fp-performance-suite',
                function() {
                    // Prova a caricare il contenuto del plugin
                    if (class_exists('FP\\PerfSuite\\Plugin')) {
                        try {
                            $container = FP\PerfSuite\Plugin::container();
                            $menu = $container->get('FP\\PerfSuite\\Admin\\Menu');
                            
                            // Crea una pagina overview semplice
                            echo '<div class="wrap">';
                            echo '<h1>FP Performance Suite</h1>';
                            echo '<div class="notice notice-info">';
                            echo '<p><strong>Plugin attivo e funzionante!</strong></p>';
                            echo '<p>Il menu è stato registrato con successo. Ora puoi accedere a tutte le funzionalità del plugin.</p>';
                            echo '</div>';
                            
                            // Aggiungi link alle sezioni principali
                            echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">';
                            
                            echo '<div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px;">';
                            echo '<h3>🚀 Cache</h3>';
                            echo '<p>Gestisci la cache del sito per migliorare le performance.</p>';
                            echo '<a href="' . admin_url('admin.php?page=fp-performance-suite-cache') . '" class="button button-primary">Configura Cache</a>';
                            echo '</div>';
                            
                            echo '<div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px;">';
                            echo '<h3>📦 Assets</h3>';
                            echo '<p>Ottimizza CSS, JavaScript e altri asset.</p>';
                            echo '<a href="' . admin_url('admin.php?page=fp-performance-suite-assets') . '" class="button button-primary">Configura Assets</a>';
                            echo '</div>';
                            
                            echo '<div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px;">';
                            echo '<h3>💾 Database</h3>';
                            echo '<p>Pulisci e ottimizza il database.</p>';
                            echo '<a href="' . admin_url('admin.php?page=fp-performance-suite-database') . '" class="button button-primary">Configura Database</a>';
                            echo '</div>';
                            
                            echo '<div style="border: 1px solid #ddd; padding: 20px; border-radius: 5px;">';
                            echo '<h3>⚙️ Impostazioni</h3>';
                            echo '<p>Configura le impostazioni generali del plugin.</p>';
                            echo '<a href="' . admin_url('admin.php?page=fp-performance-suite-settings') . '" class="button button-primary">Configura Impostazioni</a>';
                            echo '</div>';
                            
                            echo '</div>';
                            echo '</div>';
                            
                        } catch (Exception $e) {
                            echo '<div class="wrap">';
                            echo '<h1>FP Performance Suite</h1>';
                            echo '<div class="notice notice-error">';
                            echo '<p><strong>Errore nel caricamento del contenuto:</strong> ' . esc_html($e->getMessage()) . '</p>';
                            echo '<p>Prova a disattivare e riattivare il plugin.</p>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="wrap">';
                        echo '<h1>FP Performance Suite</h1>';
                        echo '<div class="notice notice-warning">';
                        echo '<p>Il plugin non è completamente inizializzato. Prova a disattivare e riattivare il plugin.</p>';
                        echo '</div>';
                        echo '</div>';
                    }
                },
                'dashicons-performance',
                30
            );
        }
    }, 999);
    
    // ULTERIORE FALLBACK: registra il menu anche se tutto il resto fallisce
    add_action('admin_menu', static function () {
        // Controlla se il menu è già stato registrato
        global $menu;
        $menu_already_registered = false;
        
        if (isset($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
                    $menu_already_registered = true;
                    break;
                }
            }
        }
        
        // Se il menu non è stato registrato, registralo con un nome diverso
        if (!$menu_already_registered) {
            error_log('[FP Performance Suite] ULTERIORE FALLBACK: Registrazione menu di emergenza');
            
            add_menu_page(
                'FP Performance Suite',
                'FP Performance',
                'manage_options',
                'fp-performance-suite-emergency',
                function() {
                    echo '<div class="wrap">';
                    echo '<h1>FP Performance Suite - Modalità Emergenza</h1>';
                    echo '<p>Il plugin è in modalità di emergenza. Il menu principale potrebbe non essere visibile.</p>';
                    echo '<p>Prova a disattivare e riattivare il plugin per risolvere il problema.</p>';
                    echo '</div>';
                },
                'dashicons-performance',
                31
            );
        }
    }, 9999);
}

/**
 * Funzione di inizializzazione del plugin
 */
function fp_perf_suite_initialize_plugin(): void {
    global $fp_perf_suite_initialized;
    
    // Prevenire inizializzazioni multiple
    if ($fp_perf_suite_initialized) {
        fp_perf_suite_safe_log('Plugin initialization prevented - already initialized', 'DEBUG');
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
    
    // Inizializza sempre - il plugin gestirà internamente i problemi
    try {
        \FP\PerfSuite\Plugin::init();
        // Marca come inizializzato
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
}

