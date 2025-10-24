<?php
/**
 * SISTEMA DI DEBUG STRUTTURATO MENU FP PERFORMANCE
 * 
 * Questo script analizza passo per passo cosa sta succedendo
 */

// Carica WordPress
if (!function_exists('wp_get_current_user')) {
    require_once('wp-config.php');
    require_once('wp-load.php');
}

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    die('❌ Accesso negato. Solo gli amministratori possono eseguire questo debug.');
}

echo '<h1>🔍 Debug Sistema Menu FP Performance</h1>';
echo '<div style="font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';

$debug_info = [];
$errors = [];

try {
    // === STEP 1: VERIFICA PLUGIN ===
    echo '<h2>1. 🔌 Stato Plugin</h2>';
    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    
    $plugin_active = is_plugin_active('fp-performance-suite/fp-performance-suite.php');
    $debug_info['plugin_active'] = $plugin_active;
    
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
        foreach ($admin_menu_hooks->callbacks as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (is_array($callback['function']) && 
                    is_object($callback['function'][0]) && 
                    get_class($callback['function'][0]) === 'FP\\PerfSuite\\Admin\\Menu') {
                    $plugin_callbacks[] = $priority;
                }
            }
        }
        
        if (!empty($plugin_callbacks)) {
            echo '✅ Callback plugin trovati alle priorità: ' . implode(', ', $plugin_callbacks) . '<br>';
            $debug_info['plugin_callbacks'] = $plugin_callbacks;
        } else {
            echo '❌ Callback plugin non trovati<br>';
            $errors[] = 'Callback plugin non registrati';
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
?>
