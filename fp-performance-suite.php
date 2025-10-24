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
    
    // Sistema di debug per il menu
    add_action('admin_menu', static function () {
        // Solo per admin e solo se il debug è attivo
        if (!current_user_can('manage_options') || !WP_DEBUG) {
            return;
        }
        
        add_menu_page(
            'Debug FP Performance',
            'Debug FP Performance',
            'manage_options',
            'fp-performance-debug',
            'fp_performance_debug_page',
            'dashicons-admin-tools',
            99
        );
    });
    
    // Forza attivazione plugin se necessario
    add_action('admin_init', static function () {
        if (!is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
            // Prova ad attivare il plugin
            $result = activate_plugin('fp-performance-suite/fp-performance-suite.php');
            if (is_wp_error($result)) {
                error_log('[FP Performance Suite] Errore attivazione: ' . $result->get_error_message());
            }
        }
    });
    
    // Forza registrazione menu principale se non è registrato
    add_action('admin_menu', static function () {
        // Solo se il plugin è inizializzato
        if (!class_exists('FP\\PerfSuite\\Plugin') || !FP\PerfSuite\Plugin::isInitialized()) {
            return;
        }
        
        try {
            $container = FP\PerfSuite\Plugin::container();
            if ($container) {
                $menu_service = $container->get('FP\\PerfSuite\\Admin\\Menu');
                if ($menu_service) {
                    // Forza il boot del menu service
                    $menu_service->boot();
                }
            }
        } catch (Exception $e) {
            error_log('[FP Performance Suite] Errore nel boot del menu: ' . $e->getMessage());
        }
    }, 1); // Priorità alta per essere sicuri che sia registrato
    
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

/**
 * Pagina di debug per il menu
 */
function fp_performance_debug_page(): void {
    echo '<div class="wrap">';
    echo '<h1>🔍 Debug Sistema Menu FP Performance</h1>';
    echo '<div style="font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';

    $debug_info = [];
    $errors = [];

    try {
        // === STEP 1: VERIFICA PLUGIN ===
        echo '<h2>1. 🔌 Stato Plugin</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        // Verifica multiple per essere sicuri
        $plugin_active_1 = is_plugin_active('fp-performance-suite/fp-performance-suite.php');
        $plugin_active_2 = is_plugin_active('fp-performance-suite.php');
        $plugin_active_3 = function_exists('fp_perf_suite_initialize_plugin');
        
        $plugin_active = $plugin_active_1 || $plugin_active_2 || $plugin_active_3;
        $debug_info['plugin_active'] = $plugin_active;
        $debug_info['plugin_active_1'] = $plugin_active_1;
        $debug_info['plugin_active_2'] = $plugin_active_2;
        $debug_info['plugin_active_3'] = $plugin_active_3;
        
        echo 'Plugin attivo (metodo 1): ' . ($plugin_active_1 ? 'SÌ' : 'NO') . '<br>';
        echo 'Plugin attivo (metodo 2): ' . ($plugin_active_2 ? 'SÌ' : 'NO') . '<br>';
        echo 'Plugin attivo (metodo 3): ' . ($plugin_active_3 ? 'SÌ' : 'NO') . '<br>';
        
        if ($plugin_active) {
            echo '✅ Plugin attivo<br>';
        } else {
            echo '❌ Plugin non attivo<br>';
            $errors[] = 'Plugin non attivo';
        }
        
        // Verifica file principale
        $main_file = WP_PLUGIN_DIR . '/fp-performance-suite/fp-performance-suite.php';
        if (file_exists($main_file)) {
            echo '✅ File principale trovato<br>';
            $debug_info['main_file_exists'] = true;
        } else {
            echo '❌ File principale non trovato<br>';
            $errors[] = 'File principale non trovato';
        }
        
        // Verifica plugin attivi
        $active_plugins = get_option('active_plugins', []);
        $fp_plugin_found = false;
        foreach ($active_plugins as $plugin) {
            if (strpos($plugin, 'fp-performance') !== false) {
                $fp_plugin_found = true;
                echo '✅ Plugin trovato in lista attivi: ' . $plugin . '<br>';
                break;
            }
        }
        
        if (!$fp_plugin_found) {
            echo '❌ Plugin non trovato in lista attivi<br>';
            $errors[] = 'Plugin non in lista attivi';
        }
        
        echo '</div>';
        
        // === STEP 2: VERIFICA INIZIALIZZAZIONE ===
        echo '<h2>2. 🚀 Inizializzazione Plugin</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        // Verifica classe Plugin
        if (class_exists('FP\\PerfSuite\\Plugin')) {
            echo '✅ Classe Plugin disponibile<br>';
            $debug_info['plugin_class_exists'] = true;
            
            // Verifica se è inizializzato
            if (method_exists('FP\\PerfSuite\\Plugin', 'isInitialized')) {
                $is_initialized = FP\PerfSuite\Plugin::isInitialized();
                $debug_info['plugin_initialized'] = $is_initialized;
                echo 'Plugin inizializzato: ' . ($is_initialized ? 'SÌ' : 'NO') . '<br>';
            } else {
                echo '⚠️ Metodo isInitialized non disponibile<br>';
            }
        } else {
            echo '❌ Classe Plugin non trovata<br>';
            $errors[] = 'Classe Plugin non trovata';
        }
        
        // Verifica variabile globale
        global $fp_perf_suite_initialized;
        $debug_info['global_initialized'] = isset($fp_perf_suite_initialized) ? $fp_perf_suite_initialized : 'non definita';
        echo 'Variabile globale: ' . (isset($fp_perf_suite_initialized) ? var_export($fp_perf_suite_initialized, true) : 'non definita') . '<br>';
        
        echo '</div>';
        
        // === STEP 3: VERIFICA HOOK ADMIN_MENU ===
        echo '<h2>3. 🎣 Hook admin_menu</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        $admin_menu_hooks = $GLOBALS['wp_filter']['admin_menu'] ?? null;
        if ($admin_menu_hooks) {
            echo '✅ Hook admin_menu registrato<br>';
            $callback_count = count($admin_menu_hooks->callbacks);
            echo 'Numero di callback: ' . $callback_count . '<br>';
            $debug_info['admin_menu_callbacks'] = $callback_count;
            
            // Cerca callback del plugin
            $plugin_callbacks = [];
            $all_callbacks = [];
            foreach ($admin_menu_hooks->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $all_callbacks[] = $priority;
                    if (is_array($callback['function']) && 
                        is_object($callback['function'][0]) && 
                        get_class($callback['function'][0]) === 'FP\\PerfSuite\\Admin\\Menu') {
                        $plugin_callbacks[] = $priority;
                    }
                }
            }
            
            echo 'Tutte le priorità: ' . implode(', ', array_unique($all_callbacks)) . '<br>';
            
            if (!empty($plugin_callbacks)) {
                echo '✅ Callback plugin trovati alle priorità: ' . implode(', ', $plugin_callbacks) . '<br>';
                $debug_info['plugin_callbacks'] = $plugin_callbacks;
            } else {
                echo '❌ Callback plugin non trovati<br>';
                $errors[] = 'Callback plugin non registrati';
                
                // Verifica se il Menu service è registrato nel container
                if (class_exists('FP\\PerfSuite\\Plugin')) {
                    try {
                        $container = FP\PerfSuite\Plugin::container();
                        if ($container) {
                            $menu_service = $container->get('FP\\PerfSuite\\Admin\\Menu');
                            if ($menu_service) {
                                echo '✅ Menu service disponibile nel container<br>';
                                echo '⚠️ Ma non registrato negli hook admin_menu<br>';
                                $errors[] = 'Menu service non registrato negli hook';
                            }
                        }
                    } catch (Exception $e) {
                        echo '❌ Errore nel Menu service: ' . $e->getMessage() . '<br>';
                    }
                }
            }
        } else {
            echo '❌ Hook admin_menu non trovato<br>';
            $errors[] = 'Hook admin_menu non registrato';
        }
        
        echo '</div>';
        
        // === STEP 4: VERIFICA MENU REGISTRATO ===
        echo '<h2>4. 📋 Menu Registrato</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        global $menu;
        $menu_found = false;
        $menu_items = [];
        
        if (isset($menu)) {
            echo 'Menu globale disponibile<br>';
            foreach ($menu as $item) {
                if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
                    $menu_found = true;
                    $menu_items[] = $item;
                    echo '✅ Menu trovato: ' . $item[0] . '<br>';
                    echo 'Capability: ' . $item[1] . '<br>';
                    echo 'Slug: ' . $item[2] . '<br>';
                    echo 'Posizione: ' . $item[4] . '<br>';
                }
            }
            
            if (!$menu_found) {
                echo '❌ Menu plugin non trovato<br>';
                $errors[] = 'Menu plugin non registrato';
                
                // Mostra tutti i menu disponibili
                echo '<strong>Menu disponibili:</strong><br>';
                foreach ($menu as $item) {
                    if (isset($item[0]) && !empty($item[0])) {
                        echo '- ' . $item[0] . ' (' . $item[2] . ')<br>';
                    }
                }
            }
        } else {
            echo '❌ Menu globale non disponibile<br>';
            $errors[] = 'Menu globale non disponibile';
        }
        
        $debug_info['menu_found'] = $menu_found;
        $debug_info['menu_items'] = $menu_items;
        
        // Verifica se il Menu service ha il metodo boot chiamato
        if (class_exists('FP\\PerfSuite\\Plugin')) {
            try {
                $container = FP\PerfSuite\Plugin::container();
                if ($container) {
                    $menu_service = $container->get('FP\\PerfSuite\\Admin\\Menu');
                    if ($menu_service) {
                        echo '✅ Menu service disponibile<br>';
                        
                        // Verifica se il metodo boot è stato chiamato
                        if (method_exists($menu_service, 'boot')) {
                            echo '✅ Metodo boot disponibile<br>';
                            
                            // Prova a chiamare boot manualmente per test
                            try {
                                $menu_service->boot();
                                echo '✅ Metodo boot chiamato con successo<br>';
                            } catch (Exception $e) {
                                echo '❌ Errore nel metodo boot: ' . $e->getMessage() . '<br>';
                                $errors[] = 'Errore nel metodo boot: ' . $e->getMessage();
                            }
                        } else {
                            echo '❌ Metodo boot non disponibile<br>';
                            $errors[] = 'Metodo boot non disponibile';
                        }
                    }
                }
            } catch (Exception $e) {
                echo '❌ Errore nel Menu service: ' . $e->getMessage() . '<br>';
                $errors[] = 'Errore Menu service: ' . $e->getMessage();
            }
        }
        
        echo '</div>';
        
        // === STEP 5: VERIFICA CAPABILITIES ===
        echo '<h2>5. 🔐 Capabilities</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        $current_user = wp_get_current_user();
        $can_manage_options = current_user_can('manage_options');
        $can_edit_posts = current_user_can('edit_posts');
        
        echo 'Utente: ' . $current_user->user_login . '<br>';
        echo 'Ruolo: ' . implode(', ', $current_user->roles) . '<br>';
        echo 'Può gestire opzioni: ' . ($can_manage_options ? 'SÌ' : 'NO') . '<br>';
        echo 'Può modificare post: ' . ($can_edit_posts ? 'SÌ' : 'NO') . '<br>';
        
        $debug_info['user_login'] = $current_user->user_login;
        $debug_info['user_roles'] = $current_user->roles;
        $debug_info['can_manage_options'] = $can_manage_options;
        
        if (!$can_manage_options) {
            $errors[] = 'Utente senza permessi manage_options';
        }
        
        echo '</div>';
        
        // === STEP 6: VERIFICA OPZIONI PLUGIN ===
        echo '<h2>6. ⚙️ Opzioni Plugin</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        $settings = get_option('fp_ps_settings', []);
        if (is_array($settings)) {
            echo '✅ Opzioni plugin caricate<br>';
            echo 'Allowed role: ' . ($settings['allowed_role'] ?? 'non impostato') . '<br>';
            $debug_info['settings'] = $settings;
        } else {
            echo '❌ Opzioni plugin non caricate<br>';
            $errors[] = 'Opzioni plugin non caricate';
        }
        
        echo '</div>';
        
        // === STEP 7: VERIFICA ERRORI PHP ===
        echo '<h2>7. 🚨 Errori PHP</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        $error_log = ini_get('log_errors') ? ini_get('error_log') : 'Non configurato';
        echo 'Log errori: ' . $error_log . '<br>';
        
        // Controlla errori recenti
        $recent_errors = false;
        if (function_exists('error_get_last')) {
            $last_error = error_get_last();
            if ($last_error && strpos($last_error['message'], 'FP Performance') !== false) {
                $recent_errors = true;
                echo '⚠️ Errore recente trovato: ' . $last_error['message'] . '<br>';
            }
        }
        
        if (!$recent_errors) {
            echo '✅ Nessun errore PHP recente<br>';
        } else {
            echo '❌ Errori PHP trovati<br>';
            $errors[] = 'Errori PHP recenti';
        }
        
        echo '</div>';
        
        // === STEP 8: VERIFICA CONTAINER ===
        echo '<h2>8. 🏗️ Service Container</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        
        if (class_exists('FP\\PerfSuite\\Plugin')) {
            try {
                $container = FP\PerfSuite\Plugin::container();
                if ($container) {
                    echo '✅ Container disponibile<br>';
                    $debug_info['container_available'] = true;
                    
                    // Verifica Menu nel container
                    try {
                        $menu_service = $container->get('FP\\PerfSuite\\Admin\\Menu');
                        if ($menu_service) {
                            echo '✅ Menu service disponibile<br>';
                            $debug_info['menu_service_available'] = true;
                        } else {
                            echo '❌ Menu service non disponibile<br>';
                            $errors[] = 'Menu service non disponibile';
                        }
                    } catch (Exception $e) {
                        echo '❌ Errore nel Menu service: ' . $e->getMessage() . '<br>';
                        $errors[] = 'Errore Menu service: ' . $e->getMessage();
                    }
                } else {
                    echo '❌ Container non disponibile<br>';
                    $errors[] = 'Container non disponibile';
                }
            } catch (Exception $e) {
                echo '❌ Errore nel Container: ' . $e->getMessage() . '<br>';
                $errors[] = 'Errore Container: ' . $e->getMessage();
            }
        } else {
            echo '❌ Classe Plugin non disponibile<br>';
            $errors[] = 'Classe Plugin non disponibile';
        }
        
        echo '</div>';
        
        // === RIEPILOGO FINALE ===
        echo '<h2>📊 Riepilogo Debug</h2>';
        
        if (empty($errors)) {
            echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;">';
            echo '🎉 <strong>TUTTO OK!</strong><br>';
            echo 'Il plugin dovrebbe funzionare correttamente. Se il menu non è visibile, potrebbe essere un problema di cache del browser.';
            echo '</div>';
        } else {
            echo '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;">';
            echo '⚠️ <strong>PROBLEMI TROVATI:</strong><br>';
            foreach ($errors as $error) {
                echo '• ' . $error . '<br>';
            }
            echo '</div>';
        }
        
        // === INFORMAZIONI SISTEMA ===
        echo '<h2>ℹ️ Informazioni Sistema</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        echo '<strong>Versione WordPress:</strong> ' . get_bloginfo('version') . '<br>';
        echo '<strong>Versione PHP:</strong> ' . PHP_VERSION . '<br>';
        echo '<strong>Debug mode:</strong> ' . (WP_DEBUG ? 'Attivo' : 'Disattivo') . '<br>';
        echo '<strong>Plugin attivi:</strong> ' . count(get_option('active_plugins', [])) . '<br>';
        echo '<strong>Memoria:</strong> ' . ini_get('memory_limit') . '<br>';
        echo '<strong>Limite esecuzione:</strong> ' . ini_get('max_execution_time') . 's<br>';
        echo '</div>';
        
        // === DEBUG INFO JSON ===
        echo '<h2>🔍 Debug Info (JSON)</h2>';
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        echo '<pre style="background: #fff; padding: 10px; border: 1px solid #ddd; border-radius: 3px; overflow-x: auto;">';
        echo json_encode($debug_info, JSON_PRETTY_PRINT);
        echo '</pre>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        echo '<strong>❌ Errore durante il debug:</strong><br>';
        echo $e->getMessage() . '<br>';
        echo '<strong>File:</strong> ' . $e->getFile() . '<br>';
        echo '<strong>Linea:</strong> ' . $e->getLine() . '<br>';
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
}

