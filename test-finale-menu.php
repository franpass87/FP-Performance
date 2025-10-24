<?php
/**
 * TEST FINALE MENU ADMIN
 * 
 * Questo script verifica che tutte le correzioni funzionino
 */

// Carica WordPress
if (!function_exists('wp_get_current_user')) {
    require_once('wp-config.php');
    require_once('wp-load.php');
}

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    die('❌ Accesso negato. Solo gli amministratori possono eseguire questo test.');
}

echo '<h1>🎯 Test Finale Menu FP Performance</h1>';
echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';

$tests_passed = 0;
$total_tests = 0;

try {
    // Test 1: Plugin attivo
    echo '<h2>1. 🔌 Plugin Attivo</h2>';
    $total_tests++;
    if (is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
        echo '✅ Plugin attivo<br>';
        $tests_passed++;
    } else {
        echo '❌ Plugin non attivo<br>';
    }
    
    // Test 2: Classe Plugin
    echo '<h2>2. 🚀 Classe Plugin</h2>';
    $total_tests++;
    if (class_exists('FP\\PerfSuite\\Plugin')) {
        echo '✅ Classe Plugin disponibile<br>';
        $tests_passed++;
    } else {
        echo '❌ Classe Plugin non trovata<br>';
    }
    
    // Test 3: Inizializzazione
    echo '<h2>3. ⚙️ Inizializzazione</h2>';
    $total_tests++;
    global $fp_perf_suite_initialized;
    if (isset($fp_perf_suite_initialized) && $fp_perf_suite_initialized) {
        echo '✅ Plugin inizializzato<br>';
        $tests_passed++;
    } else {
        echo '❌ Plugin non inizializzato<br>';
        echo 'Valore: ' . (isset($fp_perf_suite_initialized) ? var_export($fp_perf_suite_initialized, true) : 'non definita') . '<br>';
    }
    
    // Test 4: Menu registrato
    echo '<h2>4. 📋 Menu Registrato</h2>';
    $total_tests++;
    global $menu;
    $menu_found = false;
    if (isset($menu)) {
        foreach ($menu as $item) {
            if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
                $menu_found = true;
                echo '✅ Menu trovato: ' . $item[0] . '<br>';
                echo 'Capability: ' . $item[1] . '<br>';
                break;
            }
        }
    }
    if ($menu_found) {
        $tests_passed++;
    } else {
        echo '❌ Menu non trovato<br>';
    }
    
    // Test 5: Capabilities
    echo '<h2>5. 🔐 Capabilities</h2>';
    $total_tests++;
    $current_user = wp_get_current_user();
    $can_manage_options = current_user_can('manage_options');
    echo 'Utente: ' . $current_user->user_login . '<br>';
    echo 'Ruolo: ' . implode(', ', $current_user->roles) . '<br>';
    echo 'Può gestire opzioni: ' . ($can_manage_options ? 'SÌ' : 'NO') . '<br>';
    
    if ($can_manage_options) {
        echo '✅ Capabilities corrette<br>';
        $tests_passed++;
    } else {
        echo '❌ Capabilities insufficienti<br>';
    }
    
    // Test 6: Opzioni plugin
    echo '<h2>6. ⚙️ Opzioni Plugin</h2>';
    $total_tests++;
    $settings = get_option('fp_ps_settings', []);
    if (is_array($settings)) {
        echo '✅ Opzioni plugin caricate<br>';
        echo 'Allowed role: ' . ($settings['allowed_role'] ?? 'non impostato') . '<br>';
        $tests_passed++;
    } else {
        echo '❌ Opzioni plugin non caricate<br>';
    }
    
    // Riepilogo finale
    echo '<h2>📊 Riepilogo Finale</h2>';
    echo '<div style="background: ' . ($tests_passed == $total_tests ? '#d4edda' : '#f8d7da') . '; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>Test superati: ' . $tests_passed . '/' . $total_tests . '</strong><br>';
    
    if ($tests_passed == $total_tests) {
        echo '🎉 <strong>TUTTI I TEST SUPERATI!</strong><br>';
        echo 'Il menu FP Performance dovrebbe essere visibile nel menu admin.<br>';
        echo '<a href="' . admin_url() . '" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;">Vai al Menu Admin</a>';
    } else {
        echo '⚠️ <strong>ALCUNI TEST FALLITI</strong><br>';
        echo 'Il menu potrebbe non essere visibile. Controlla i log di errore.';
    }
    echo '</div>';
    
    // Informazioni di debug
    echo '<h2>🔍 Informazioni Debug</h2>';
    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>Versione WordPress:</strong> ' . get_bloginfo('version') . '<br>';
    echo '<strong>Versione PHP:</strong> ' . PHP_VERSION . '<br>';
    echo '<strong>Debug mode:</strong> ' . (WP_DEBUG ? 'Attivo' : 'Disattivo') . '<br>';
    echo '<strong>Plugin attivi:</strong> ' . count(get_option('active_plugins', [])) . '<br>';
    echo '<strong>Memoria:</strong> ' . ini_get('memory_limit') . '<br>';
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
