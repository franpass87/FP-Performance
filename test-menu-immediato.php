<?php
/**
 * TEST IMMEDIATO MENU ADMIN
 * 
 * Questo script verifica immediatamente se il menu è visibile
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

echo '<h1>🔍 Test Immediato Menu FP Performance</h1>';
echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';

// Test 1: Plugin attivo
echo '<h2>1. Plugin Attivo</h2>';
if (is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
    echo '✅ Plugin attivo<br>';
} else {
    echo '❌ Plugin non attivo<br>';
    echo '</div>';
    exit;
}

// Test 2: Menu registrato
echo '<h2>2. Menu Registrato</h2>';
global $menu;
$menu_found = false;
$menu_items = [];

if (isset($menu)) {
    foreach ($menu as $item) {
        if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
            $menu_found = true;
            $menu_items[] = $item;
            echo '✅ Menu trovato: ' . $item[0] . '<br>';
            echo 'Capability: ' . $item[1] . '<br>';
            echo 'Slug: ' . $item[2] . '<br>';
        }
    }
}

if (!$menu_found) {
    echo '❌ Menu non trovato<br>';
    echo '<strong>Menu disponibili:</strong><br>';
    if (isset($menu)) {
        foreach ($menu as $item) {
            if (isset($item[0]) && !empty($item[0])) {
                echo '- ' . $item[0] . ' (' . $item[2] . ')<br>';
            }
        }
    }
}

// Test 3: Capabilities
echo '<h2>3. Capabilities</h2>';
$current_user = wp_get_current_user();
echo 'Utente: ' . $current_user->user_login . '<br>';
echo 'Ruolo: ' . implode(', ', $current_user->roles) . '<br>';
echo 'Può gestire opzioni: ' . (current_user_can('manage_options') ? 'SÌ' : 'NO') . '<br>';

// Test 4: Opzioni plugin
echo '<h2>4. Opzioni Plugin</h2>';
$settings = get_option('fp_ps_settings', []);
if (is_array($settings)) {
    echo '✅ Opzioni plugin caricate<br>';
    echo 'Allowed role: ' . ($settings['allowed_role'] ?? 'non impostato') . '<br>';
} else {
    echo '❌ Opzioni plugin non caricate<br>';
}

// Test 5: Inizializzazione
echo '<h2>5. Inizializzazione</h2>';
global $fp_perf_suite_initialized;
if (isset($fp_perf_suite_initialized) && $fp_perf_suite_initialized) {
    echo '✅ Plugin inizializzato<br>';
} else {
    echo '❌ Plugin non inizializzato<br>';
    echo 'Valore: ' . (isset($fp_perf_suite_initialized) ? var_export($fp_perf_suite_initialized, true) : 'non definita') . '<br>';
}

// Riepilogo
echo '<h2>📊 Riepilogo</h2>';
if ($menu_found) {
    echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;">';
    echo '🎉 <strong>SUCCESSO!</strong> Il menu è visibile.<br>';
    echo '<a href="' . admin_url() . '" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;">Vai al Menu Admin</a>';
    echo '</div>';
} else {
    echo '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;">';
    echo '⚠️ <strong>PROBLEMA!</strong> Il menu non è visibile.<br>';
    echo 'Prova a ricaricare la pagina o disattivare/riattivare il plugin.';
    echo '</div>';
}

echo '</div>';
?>
