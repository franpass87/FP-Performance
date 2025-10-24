<?php
/**
 * VERIFICA SEMPLICE MENU ADMIN
 * 
 * Questo script verifica che il menu sia visibile senza eseguire test complessi
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

echo '<h1>🔍 Verifica Menu FP Performance</h1>';
echo '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';

// Verifica 1: Plugin attivo
echo '<h2>1. Plugin Attivo</h2>';
if (is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
    echo '✅ Plugin attivo<br>';
} else {
    echo '❌ Plugin non attivo<br>';
}

// Verifica 2: Classe Plugin
echo '<h2>2. Classe Plugin</h2>';
if (class_exists('FP\\PerfSuite\\Plugin')) {
    echo '✅ Classe Plugin disponibile<br>';
} else {
    echo '❌ Classe Plugin non trovata<br>';
}

// Verifica 3: Variabile globale
echo '<h2>3. Inizializzazione</h2>';
global $fp_perf_suite_initialized;
if (isset($fp_perf_suite_initialized) && $fp_perf_suite_initialized) {
    echo '✅ Plugin inizializzato<br>';
} else {
    echo '❌ Plugin non inizializzato<br>';
}

// Verifica 4: Menu registrato
echo '<h2>4. Menu Registrato</h2>';
global $menu;
$menu_found = false;
if (isset($menu)) {
    foreach ($menu as $item) {
        if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
            $menu_found = true;
            echo '✅ Menu trovato: ' . $item[0] . '<br>';
            break;
        }
    }
}
if (!$menu_found) {
    echo '❌ Menu non trovato<br>';
}

// Riepilogo
echo '<h2>📊 Riepilogo</h2>';
if ($menu_found) {
    echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;">';
    echo '🎉 <strong>SUCCESSO!</strong> Il menu dovrebbe essere visibile.<br>';
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
