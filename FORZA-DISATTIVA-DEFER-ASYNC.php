<?php
/**
 * Script di emergenza - Forza disattivazione Defer/Async JS
 * 
 * USO: Carica questo file direttamente nel browser
 * URL: https://tuosito.com/wp-content/plugins/FP-Performance/FORZA-DISATTIVA-DEFER-ASYNC.php
 * 
 * ATTENZIONE: Questo script forza la disattivazione di defer_js e async_js
 */

// Security check
if (!defined('ABSPATH')) {
    // Se chiamato direttamente, carica WordPress
    // Prova diversi path possibili
    $current_dir = __DIR__;
    $wp_load_paths = [
        $current_dir . '/../../../wp-load.php', // wp-content/plugins/FP-Performance -> wp-load.php
        $current_dir . '/../../../../wp-load.php', // percorso alternativo
        dirname(dirname(dirname($current_dir))) . '/wp-load.php',
    ];
    
    $loaded = false;
    foreach ($wp_load_paths as $path) {
        $real_path = realpath($path);
        if ($real_path && file_exists($real_path)) {
            require_once($real_path);
            $loaded = true;
            break;
        }
    }
    
    if (!$loaded && !defined('ABSPATH')) {
        die('Impossibile trovare wp-load.php. Path tentati: ' . implode(', ', $wp_load_paths));
    }
}

// Verifica permessi
if (!current_user_can('manage_options')) {
    die('Accesso negato. Devi essere amministratore.');
}

// Forza disattivazione defer_js e async_js
$assets = get_option('fp_ps_assets', []);

$updated = false;
if (!isset($assets['defer_js']) || $assets['defer_js'] !== false) {
    $assets['defer_js'] = false;
    $updated = true;
}

if (!isset($assets['async_js']) || $assets['async_js'] !== false) {
    $assets['async_js'] = false;
    $updated = true;
}

if ($updated) {
    update_option('fp_ps_assets', $assets, false);
    wp_cache_flush();
    $message = '✅ Defer JS e Async JS disattivati con successo!';
} else {
    $message = 'ℹ️ Defer JS e Async JS erano già disattivati.';
}

// Output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>FP Performance - Forza Disattivazione Defer/Async</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f8f9fa; border: 1px solid #dee2e6; padding: 10px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔧 FP Performance - Forza Disattivazione Defer/Async JS</h1>
    
    <div class="<?php echo $updated ? 'success' : 'info'; ?>">
        <h2><?php echo $updated ? '✅ Completato' : 'ℹ️ Informazione'; ?></h2>
        <p><?php echo esc_html($message); ?></p>
    </div>
    
    <div class="code">
        <strong>Valori attuali:</strong><br>
        defer_js: <?php echo isset($assets['defer_js']) && $assets['defer_js'] ? 'true' : 'false'; ?><br>
        async_js: <?php echo isset($assets['async_js']) && $assets['async_js'] ? 'true' : 'false'; ?>
    </div>
    
    <div class="info">
        <h2>📋 Prossimi Passi</h2>
        <ol>
            <li><strong>Verifica l'Overview</strong> - Ricarica la pagina Overview per vedere lo stato aggiornato</li>
            <li><strong>Pulisci la cache</strong> - Se necessario, pulisci la cache del browser (Ctrl+F5)</li>
            <li><strong>Rimuovi questo file</strong> - Elimina <code>FORZA-DISATTIVA-DEFER-ASYNC.php</code> dopo l'uso</li>
        </ol>
    </div>
    
    <p><a href="<?php echo admin_url('admin.php?page=fp-performance-suite'); ?>">← Torna a FP Performance</a></p>
</body>
</html>

