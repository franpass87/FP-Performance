<?php
/**
 * TEST COMPLETO VISIBILITÀ MENU ADMIN FP PERFORMANCE
 * 
 * Questo script verifica che il menu admin sia visibile e funzionante
 */

// Carica WordPress se non è già caricato
if (!function_exists('wp_get_current_user')) {
    require_once('wp-config.php');
    require_once('wp-load.php');
}

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    die('❌ Accesso negato. Solo gli amministratori possono eseguire questo test.');
}

echo '<h1>🔍 Test Visibilità Menu FP Performance</h1>';
echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';

$tests_passed = 0;
$total_tests = 0;

try {
    // Test 1: Verifica che il plugin sia attivo
    echo '<h2>1. 🔌 Test Plugin Attivo</h2>';
    $total_tests++;
    
    if (is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
        echo '✅ Plugin attivo correttamente<br>';
        $tests_passed++;
    } else {
        echo '❌ Plugin non attivo<br>';
    }
    
    // Test 2: Verifica inizializzazione plugin
    echo '<h2>2. 🚀 Test Inizializzazione Plugin</h2>';
    $total_tests++;
    
    if (class_exists('FP\\PerfSuite\\Plugin')) {
        echo '✅ Classe Plugin disponibile<br>';
        
        // Verifica se il plugin è inizializzato
        if (method_exists('FP\\PerfSuite\\Plugin', 'isInitialized')) {
            $is_initialized = FP\PerfSuite\Plugin::isInitialized();
            if ($is_initialized) {
                echo '✅ Plugin inizializzato correttamente<br>';
                $tests_passed++;
            } else {
                echo '❌ Plugin non inizializzato<br>';
            }
        } else {
            echo '⚠️ Metodo isInitialized non disponibile<br>';
        }
    } else {
        echo '❌ Classe Plugin non trovata<br>';
    }
    
    // Test 3: Verifica variabile globale
    echo '<h2>3. 🌐 Test Variabile Globale</h2>';
    $total_tests++;
    
    global $fp_perf_suite_initialized;
    if (isset($fp_perf_suite_initialized) && $fp_perf_suite_initialized) {
        echo '✅ Variabile globale $fp_perf_suite_initialized = true<br>';
        $tests_passed++;
    } else {
        echo '❌ Variabile globale non impostata o falsa<br>';
        echo 'Valore: ' . (isset($fp_perf_suite_initialized) ? var_export($fp_perf_suite_initialized, true) : 'non definita') . '<br>';
    }
    
    // Test 4: Verifica menu registrato
    echo '<h2>4. 📋 Test Menu Registrato</h2>';
    $total_tests++;
    
    global $menu, $submenu;
    
    $menu_found = false;
    $submenu_found = false;
    
    // Cerca il menu principale
    if (isset($menu)) {
        foreach ($menu as $item) {
            if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
                $menu_found = true;
                echo '✅ Menu principale trovato: ' . $item[0] . '<br>';
                break;
            }
        }
    }
    
    // Cerca submenu
    if (isset($submenu['fp-performance'])) {
        $submenu_found = true;
        echo '✅ Submenu trovati: ' . count($submenu['fp-performance']) . ' elementi<br>';
    }
    
    if ($menu_found || $submenu_found) {
        $tests_passed++;
    } else {
        echo '❌ Menu non trovato<br>';
    }
    
    // Test 5: Verifica capabilities
    echo '<h2>5. 🔐 Test Capabilities</h2>';
    $total_tests++;
    
    $current_user = wp_get_current_user();
    $can_manage_options = current_user_can('manage_options');
    $can_edit_posts = current_user_can('edit_posts');
    
    echo 'Utente corrente: ' . $current_user->user_login . '<br>';
    echo 'Ruolo: ' . implode(', ', $current_user->roles) . '<br>';
    echo 'Può gestire opzioni: ' . ($can_manage_options ? 'SÌ' : 'NO') . '<br>';
    echo 'Può modificare post: ' . ($can_edit_posts ? 'SÌ' : 'NO') . '<br>';
    
    if ($can_manage_options) {
        echo '✅ Capabilities corrette<br>';
        $tests_passed++;
    } else {
        echo '❌ Capabilities insufficienti<br>';
    }
    
    // Test 6: Verifica hook admin_menu
    echo '<h2>6. 🎣 Test Hook admin_menu</h2>';
    $total_tests++;
    
    $admin_menu_hooks = $GLOBALS['wp_filter']['admin_menu'] ?? null;
    if ($admin_menu_hooks) {
        echo '✅ Hook admin_menu registrato<br>';
        echo 'Numero di callback: ' . count($admin_menu_hooks->callbacks) . '<br>';
        $tests_passed++;
    } else {
        echo '❌ Hook admin_menu non trovato<br>';
    }
    
    // Test 7: Verifica opzioni plugin
    echo '<h2>7. ⚙️ Test Opzioni Plugin</h2>';
    $total_tests++;
    
    $settings = get_option('fp_ps_settings', []);
    if (is_array($settings)) {
        echo '✅ Opzioni plugin caricate<br>';
        echo 'Allowed role: ' . ($settings['allowed_role'] ?? 'non impostato') . '<br>';
        $tests_passed++;
    } else {
        echo '❌ Opzioni plugin non caricate<br>';
    }
    
    // Test 8: Verifica errori PHP
    echo '<h2>8. 🚨 Test Errori PHP</h2>';
    $total_tests++;
    
    $error_log = ini_get('log_errors') ? ini_get('error_log') : 'Non configurato';
    echo 'Log errori: ' . $error_log . '<br>';
    
    // Controlla se ci sono errori recenti
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
        $tests_passed++;
    } else {
        echo '❌ Errori PHP trovati<br>';
    }
    
    // Test 9: Verifica database
    echo '<h2>9. 🗄️ Test Database</h2>';
    $total_tests++;
    
    global $wpdb;
    if ($wpdb && $wpdb->db_connect()) {
        echo '✅ Connessione database OK<br>';
        
        // Verifica tabelle plugin
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}fp_%'");
        if (!empty($tables)) {
            echo '✅ Tabelle plugin trovate: ' . count($tables) . '<br>';
        } else {
            echo '⚠️ Nessuna tabella plugin trovata<br>';
        }
        
        $tests_passed++;
    } else {
        echo '❌ Problema connessione database<br>';
    }
    
    // Test 10: Verifica file system
    echo '<h2>10. 📁 Test File System</h2>';
    $total_tests++;
    
    $plugin_dir = WP_PLUGIN_DIR . '/fp-performance-suite';
    if (file_exists($plugin_dir)) {
        echo '✅ Directory plugin trovata<br>';
        
        $main_file = $plugin_dir . '/fp-performance-suite.php';
        if (file_exists($main_file)) {
            echo '✅ File principale trovato<br>';
            $tests_passed++;
        } else {
            echo '❌ File principale non trovato<br>';
        }
    } else {
        echo '❌ Directory plugin non trovata<br>';
    }
    
    // Riepilogo finale
    echo '<h2>📊 Riepilogo Test</h2>';
    echo '<div style="background: ' . ($tests_passed == $total_tests ? '#d4edda' : '#f8d7da') . '; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>Test superati: ' . $tests_passed . '/' . $total_tests . '</strong><br>';
    
    if ($tests_passed == $total_tests) {
        echo '🎉 <strong>TUTTI I TEST SUPERATI!</strong><br>';
        echo 'Il menu admin dovrebbe essere visibile.<br>';
        echo '<a href="' . admin_url() . '" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;">Vai al Menu Admin</a>';
    } else {
        echo '⚠️ <strong>ALCUNI TEST FALLITI</strong><br>';
        echo 'Il menu potrebbe non essere visibile.<br>';
    }
    echo '</div>';
    
    // Informazioni aggiuntive
    echo '<h2>ℹ️ Informazioni Aggiuntive</h2>';
    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>Versione WordPress:</strong> ' . get_bloginfo('version') . '<br>';
    echo '<strong>Versione PHP:</strong> ' . PHP_VERSION . '<br>';
    echo '<strong>Memoria disponibile:</strong> ' . ini_get('memory_limit') . '<br>';
    echo '<strong>Limite esecuzione:</strong> ' . ini_get('max_execution_time') . 's<br>';
    echo '<strong>Debug mode:</strong> ' . (WP_DEBUG ? 'Attivo' : 'Disattivo') . '<br>';
    echo '<strong>Plugin attivi:</strong> ' . count(get_option('active_plugins', [])) . '<br>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>❌ Errore durante il test:</strong><br>';
    echo $e->getMessage() . '<br>';
    echo '<strong>File:</strong> ' . $e->getFile() . '<br>';
    echo '<strong>Linea:</strong> ' . $e->getLine() . '<br>';
    echo '</div>';
}

echo '</div>';
?>
