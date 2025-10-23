<?php
/**
 * Script di Diagnostica Menu Admin FP Performance Suite
 * 
 * Questo script aiuta a identificare perché il menu admin
 * non appare su alcuni siti.
 */

// Verifica che sia eseguito in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    wp_die('Permessi insufficienti per eseguire questa diagnostica');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Diagnostica Menu Admin FP Performance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #0073aa; }
        .error { border-left-color: #dc3232; background: #fef7f7; }
        .success { border-left-color: #46b450; background: #f7fef7; }
        .warning { border-left-color: #ffb900; background: #fffbf0; }
        .code { background: #f1f1f1; padding: 10px; font-family: monospace; }
        .button { background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; }
    </style>
</head>
<body>

<h1>🔍 Diagnostica Menu Admin FP Performance Suite</h1>

<?php

// 1. VERIFICA PLUGIN ATTIVO
echo '<div class="section">';
echo '<h2>1. 📦 Stato Plugin</h2>';

if (class_exists('FP\\PerfSuite\\Plugin')) {
    echo '<p class="success">✅ Plugin FP Performance Suite è attivo e caricato</p>';
} else {
    echo '<p class="error">❌ Plugin FP Performance Suite NON è attivo o caricato</p>';
}

// Verifica se il plugin è nella lista dei plugin attivi
$active_plugins = get_option('active_plugins', []);
$plugin_found = false;
foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'fp-performance-suite') !== false) {
        $plugin_found = true;
        echo '<p class="success">✅ Plugin trovato in active_plugins: ' . esc_html($plugin) . '</p>';
        break;
    }
}

if (!$plugin_found) {
    echo '<p class="error">❌ Plugin NON trovato nella lista active_plugins</p>';
}

echo '</div>';

// 2. VERIFICA IMPOSTAZIONI PLUGIN
echo '<div class="section">';
echo '<h2>2. ⚙️ Impostazioni Plugin</h2>';

$settings = get_option('fp_ps_settings', []);
echo '<p><strong>Impostazioni attuali:</strong></p>';
echo '<div class="code">';
echo '<pre>' . print_r($settings, true) . '</pre>';
echo '</div>';

// Verifica ruolo configurato
$allowed_role = $settings['allowed_role'] ?? 'administrator';
echo '<p><strong>Ruolo configurato:</strong> ' . esc_html($allowed_role) . '</p>';

echo '</div>';

// 3. VERIFICA CAPABILITIES
echo '<div class="section">';
echo '<h2>3. 🔐 Verifica Capabilities</h2>';

// Simula il calcolo della capability
$capability = 'manage_options';
if (isset($settings['allowed_role'])) {
    switch ($settings['allowed_role']) {
        case 'editor':
            $capability = 'edit_pages';
            break;
        default:
            $capability = 'manage_options';
            break;
    }
}

echo '<p><strong>Capability richiesta:</strong> ' . esc_html($capability) . '</p>';
echo '<p><strong>Utente corrente può accedere:</strong> ' . (current_user_can($capability) ? '✅ SÌ' : '❌ NO') . '</p>';

// Verifica permessi specifici
echo '<p><strong>Permessi utente corrente:</strong></p>';
echo '<ul>';
echo '<li>manage_options: ' . (current_user_can('manage_options') ? '✅' : '❌') . '</li>';
echo '<li>edit_pages: ' . (current_user_can('edit_pages') ? '✅' : '❌') . '</li>';
echo '<li>edit_posts: ' . (current_user_can('edit_posts') ? '✅' : '❌') . '</li>';
echo '</ul>';

echo '</div>';

// 4. VERIFICA RUOLO UTENTE
echo '<div class="section">';
echo '<h2>4. 👤 Ruolo Utente Corrente</h2>';

$current_user = wp_get_current_user();
echo '<p><strong>Utente:</strong> ' . esc_html($current_user->user_login) . '</p>';
echo '<p><strong>Email:</strong> ' . esc_html($current_user->user_email) . '</p>';
echo '<p><strong>Ruoli:</strong> ' . implode(', ', $current_user->roles) . '</p>';

echo '</div>';

// 5. VERIFICA MENU REGISTRATO
echo '<div class="section">';
echo '<h2>5. 📋 Menu Admin Registrato</h2>';

global $menu, $submenu;

// Cerca il menu FP Performance
$fp_menu_found = false;
if (isset($menu)) {
    foreach ($menu as $menu_item) {
        if (isset($menu_item[2]) && $menu_item[2] === 'fp-performance-suite') {
            $fp_menu_found = true;
            echo '<p class="success">✅ Menu FP Performance trovato nel menu admin</p>';
            echo '<p><strong>Dettagli menu:</strong></p>';
            echo '<div class="code">';
            echo '<pre>' . print_r($menu_item, true) . '</pre>';
            echo '</div>';
            break;
        }
    }
}

if (!$fp_menu_found) {
    echo '<p class="error">❌ Menu FP Performance NON trovato nel menu admin</p>';
}

// Verifica submenu
if (isset($submenu['fp-performance-suite'])) {
    echo '<p class="success">✅ Submenu FP Performance trovati</p>';
    echo '<p><strong>Submenu disponibili:</strong></p>';
    echo '<div class="code">';
    echo '<pre>' . print_r($submenu['fp-performance-suite'], true) . '</pre>';
    echo '</div>';
} else {
    echo '<p class="warning">⚠️ Submenu FP Performance non trovati</p>';
}

echo '</div>';

// 6. VERIFICA ERRORI
echo '<div class="section">';
echo '<h2>6. 🐛 Verifica Errori</h2>';

// Verifica errori di attivazione
$activation_error = get_option('fp_perfsuite_activation_error');
if ($activation_error) {
    echo '<p class="error">❌ Errore di attivazione rilevato:</p>';
    echo '<div class="code">' . esc_html($activation_error) . '</div>';
} else {
    echo '<p class="success">✅ Nessun errore di attivazione</p>';
}

// Verifica log errori
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<p class="success">✅ WP_DEBUG è attivo - controlla i log per errori</p>';
} else {
    echo '<p class="warning">⚠️ WP_DEBUG non attivo - attiva per vedere errori dettagliati</p>';
}

echo '</div>';

// 7. SOLUZIONI
echo '<div class="section">';
echo '<h2>7. 🛠️ Soluzioni Proposte</h2>';

echo '<h3>Soluzione 1: Reset Impostazioni</h3>';
echo '<p>Se le impostazioni sono corrotte, resetta con:</p>';
echo '<div class="code">';
echo 'update_option(\'fp_ps_settings\', [\'allowed_role\' => \'administrator\']);';
echo '</div>';

echo '<h3>Soluzione 2: Forza Registrazione Menu</h3>';
echo '<p>Se il menu non si registra, prova a disattivare e riattivare il plugin</p>';

echo '<h3>Soluzione 3: Verifica Conflitti</h3>';
echo '<p>Disattiva temporaneamente altri plugin per verificare conflitti</p>';

echo '<h3>Soluzione 4: Reset Completo</h3>';
echo '<p>Se tutto fallisce, disattiva il plugin, elimina le opzioni e riattiva:</p>';
echo '<div class="code">';
echo 'delete_option(\'fp_ps_settings\');<br>';
echo 'delete_option(\'fp_perfsuite_activation_error\');';
echo '</div>';

echo '</div>';

// 8. AZIONI RAPIDE
echo '<div class="section">';
echo '<h2>8. ⚡ Azioni Rapide</h2>';

echo '<p><a href="' . admin_url('admin.php?page=fp-performance-suite-settings') . '" class="button">🔧 Vai alle Impostazioni</a></p>';

if (current_user_can('manage_options')) {
    echo '<p><a href="' . admin_url('plugins.php') . '" class="button">📦 Gestisci Plugin</a></p>';
}

echo '<p><a href="' . admin_url('admin.php?page=fp-performance-suite') . '" class="button">🚀 Vai a FP Performance</a></p>';

echo '</div>';

?>

<div class="section">
    <h2>📊 Riepilogo Diagnostica</h2>
    <p><strong>Data/Ora:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    <p><strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?></p>
    <p><strong>PHP:</strong> <?php echo PHP_VERSION; ?></p>
    <p><strong>URL Sito:</strong> <?php echo home_url(); ?></p>
</div>

</body>
</html>
