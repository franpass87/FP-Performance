<?php
/**
 * Script di Fix Automatico Menu Admin FP Performance Suite
 * 
 * Questo script risolve automaticamente i problemi di visualizzazione
 * del menu admin del plugin FP Performance Suite.
 */

// Verifica che sia eseguito in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    wp_die('Permessi insufficienti per eseguire questo fix');
}

// Verifica che il plugin sia attivo
if (!class_exists('FP\\PerfSuite\\Plugin')) {
    wp_die('Plugin FP Performance Suite non è attivo');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Menu Admin FP Performance</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #0073aa; }
        .error { border-left-color: #dc3232; background: #fef7f7; }
        .success { border-left-color: #46b450; background: #f7fef7; }
        .warning { border-left-color: #ffb900; background: #fffbf0; }
        .code { background: #f1f1f1; padding: 10px; font-family: monospace; }
        .button { background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 5px; display: inline-block; }
        .button:hover { background: #005a87; }
    </style>
</head>
<body>

<h1>🛠️ Fix Menu Admin FP Performance Suite</h1>

<?php

$fixes_applied = [];
$errors = [];

// 1. FIX IMPOSTAZIONI PLUGIN
echo '<div class="section">';
echo '<h2>1. ⚙️ Fix Impostazioni Plugin</h2>';

try {
    $current_settings = get_option('fp_ps_settings', []);
    
    // Assicurati che le impostazioni siano un array valido
    if (!is_array($current_settings)) {
        $current_settings = [];
    }
    
    // Imposta il ruolo di default se non esiste o è invalido
    if (!isset($current_settings['allowed_role']) || !in_array($current_settings['allowed_role'], ['administrator', 'editor'])) {
        $current_settings['allowed_role'] = 'administrator';
        update_option('fp_ps_settings', $current_settings);
        $fixes_applied[] = 'Impostazioni plugin resettate a valori di default';
        echo '<p class="success">✅ Impostazioni plugin corrette</p>';
    } else {
        echo '<p class="success">✅ Impostazioni plugin già corrette</p>';
    }
    
} catch (Exception $e) {
    $errors[] = 'Errore nel fix delle impostazioni: ' . $e->getMessage();
    echo '<p class="error">❌ Errore nel fix delle impostazioni</p>';
}

echo '</div>';

// 2. FIX ERRORI DI ATTIVAZIONE
echo '<div class="section">';
echo '<h2>2. 🐛 Fix Errori di Attivazione</h2>';

try {
    $activation_error = get_option('fp_perfsuite_activation_error');
    if ($activation_error) {
        delete_option('fp_perfsuite_activation_error');
        $fixes_applied[] = 'Errore di attivazione rimosso';
        echo '<p class="success">✅ Errore di attivazione rimosso</p>';
    } else {
        echo '<p class="success">✅ Nessun errore di attivazione presente</p>';
    }
} catch (Exception $e) {
    $errors[] = 'Errore nel fix degli errori di attivazione: ' . $e->getMessage();
    echo '<p class="error">❌ Errore nel fix degli errori di attivazione</p>';
}

echo '</div>';

// 3. FIX CAPABILITIES
echo '<div class="section">';
echo '<h2>3. 🔐 Fix Capabilities</h2>';

try {
    // Verifica che l'utente corrente abbia i permessi necessari
    $settings = get_option('fp_ps_settings', ['allowed_role' => 'administrator']);
    $required_capability = ($settings['allowed_role'] === 'editor') ? 'edit_pages' : 'manage_options';
    
    if (current_user_can($required_capability)) {
        echo '<p class="success">✅ Capabilities corrette per l\'utente corrente</p>';
    } else {
        // Se l'utente non ha i permessi, forza il ruolo administrator
        $settings['allowed_role'] = 'administrator';
        update_option('fp_ps_settings', $settings);
        $fixes_applied[] = 'Capabilities forzate a administrator';
        echo '<p class="success">✅ Capabilities corrette forzando ruolo administrator</p>';
    }
} catch (Exception $e) {
    $errors[] = 'Errore nel fix delle capabilities: ' . $e->getMessage();
    echo '<p class="error">❌ Errore nel fix delle capabilities</p>';
}

echo '</div>';

// 4. FORZA RIGENERAZIONE MENU
echo '<div class="section">';
echo '<h2>4. 🔄 Forza Rigenerazione Menu</h2>';

try {
    // Pulisce la cache del menu
    if (function_exists('wp_cache_delete')) {
        wp_cache_delete('admin_menu', 'options');
    }
    
    // Forza la rigenerazione del menu admin
    do_action('admin_menu');
    
    $fixes_applied[] = 'Menu admin rigenerato';
    echo '<p class="success">✅ Menu admin rigenerato</p>';
    
} catch (Exception $e) {
    $errors[] = 'Errore nella rigenerazione del menu: ' . $e->getMessage();
    echo '<p class="error">❌ Errore nella rigenerazione del menu</p>';
}

echo '</div>';

// 5. VERIFICA FINALE
echo '<div class="section">';
echo '<h2>5. ✅ Verifica Finale</h2>';

// Verifica se il menu è ora visibile
global $menu;
$menu_found = false;
if (isset($menu)) {
    foreach ($menu as $menu_item) {
        if (isset($menu_item[2]) && $menu_item[2] === 'fp-performance-suite') {
            $menu_found = true;
            break;
        }
    }
}

if ($menu_found) {
    echo '<p class="success">✅ Menu FP Performance ora visibile!</p>';
} else {
    echo '<p class="warning">⚠️ Menu FP Performance ancora non visibile</p>';
    echo '<p>Prova a:</p>';
    echo '<ul>';
    echo '<li>Disattivare e riattivare il plugin</li>';
    echo '<li>Verificare che non ci siano conflitti con altri plugin</li>';
    echo '<li>Controllare i log di errore di WordPress</li>';
    echo '</ul>';
}

echo '</div>';

// 6. RIEPILOGO
echo '<div class="section">';
echo '<h2>6. 📊 Riepilogo Fix</h2>';

if (!empty($fixes_applied)) {
    echo '<h3>✅ Fix Applicati:</h3>';
    echo '<ul>';
    foreach ($fixes_applied as $fix) {
        echo '<li>' . esc_html($fix) . '</li>';
    }
    echo '</ul>';
}

if (!empty($errors)) {
    echo '<h3>❌ Errori Rilevati:</h3>';
    echo '<ul>';
    foreach ($errors as $error) {
        echo '<li>' . esc_html($error) . '</li>';
    }
    echo '</ul>';
}

if (empty($fixes_applied) && empty($errors)) {
    echo '<p class="success">✅ Nessun fix necessario - tutto sembra funzionare correttamente</p>';
}

echo '</div>';

// 7. AZIONI RAPIDE
echo '<div class="section">';
echo '<h2>7. ⚡ Azioni Rapide</h2>';

echo '<p><a href="' . admin_url('admin.php?page=fp-performance-suite') . '" class="button">🚀 Vai a FP Performance</a></p>';
echo '<p><a href="' . admin_url('admin.php?page=fp-performance-suite-settings') . '" class="button">🔧 Vai alle Impostazioni</a></p>';
echo '<p><a href="' . admin_url('plugins.php') . '" class="button">📦 Gestisci Plugin</a></p>';

echo '</div>';

?>

<div class="section">
    <h2>📊 Informazioni Sistema</h2>
    <p><strong>Data/Ora Fix:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    <p><strong>WordPress:</strong> <?php echo get_bloginfo('version'); ?></p>
    <p><strong>PHP:</strong> <?php echo PHP_VERSION; ?></p>
    <p><strong>URL Sito:</strong> <?php echo home_url(); ?></p>
</div>

</body>
</html>
