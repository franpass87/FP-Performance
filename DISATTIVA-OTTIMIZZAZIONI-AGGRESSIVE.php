<?php
/**
 * Script per disattivare solo le ottimizzazioni aggressive che causano errori critici
 * 
 * USO: Carica questo file direttamente nel browser
 * URL: https://tuosito.com/wp-content/plugins/FP-Performance/DISATTIVA-OTTIMIZZAZIONI-AGGRESSIVE.php
 * 
 * Questo script disabilita SOLO le ottimizzazioni più aggressive, mantenendo quelle sicure
 */

// Security check
if (!defined('ABSPATH')) {
    require_once('../../../wp-load.php');
}

// Verifica permessi
if (!current_user_can('manage_options')) {
    die('Accesso negato. Devi essere amministratore.');
}

// Disabilita SOLO le ottimizzazioni aggressive
$aggressive_options = [
    // JavaScript - Defer/Async (possono rompere script essenziali)
    'fp_ps_assets' => [
        'defer_js' => false,
        'async_js' => false,
        'unused_optimization' => false,
        'code_splitting' => false,
        'delay_inline_scripts' => false,
        'minify_inline_js' => false,
        'minify_inline_css' => false, // CSS inline minification può causare problemi
    ],
    
    // Third Party Scripts - DISABILITA TUTTO (molto probabile causa errore)
    'fp_ps_third_party_settings' => [],
    
    // HTML Minification (può rompere HTML malformato)
    'fp_ps_html_minify' => ['enabled' => false],
    
    // Critical Path (può causare problemi)
    'fp_ps_critical_path_optimization' => ['enabled' => false],
    
    // Unused CSS/JS (può rimuovere codice necessario)
    'fp_ps_unused_css' => ['enabled' => false],
    'fp_ps_unused_js' => ['enabled' => false],
];

$disabled = [];
$errors = [];

foreach ($aggressive_options as $option_name => $value) {
    try {
        if (is_array($value)) {
            $current = get_option($option_name, []);
            $updated = false;
            foreach ($value as $key => $val) {
                if (isset($current[$key]) && $current[$key] !== $val) {
                    $current[$key] = $val;
                    $updated = true;
                }
            }
            if ($updated) {
                update_option($option_name, $current, false);
                $disabled[] = $option_name;
            }
        } else {
            if (get_option($option_name) !== $value) {
                update_option($option_name, $value, false);
                $disabled[] = $option_name;
            }
        }
    } catch (\Exception $e) {
        $errors[] = "$option_name: " . $e->getMessage();
    }
}

// Pulisci cache
wp_cache_flush();

// Output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>FP Performance - Disattiva Ottimizzazioni Aggressive</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        ul { margin: 10px 0; padding-left: 30px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>⚠️ FP Performance - Disattiva Ottimizzazioni Aggressive</h1>
    
    <?php if (!empty($disabled)): ?>
        <div class="success">
            <h2>✅ Ottimizzazioni Aggressive Disabilitate</h2>
            <p>Le seguenti ottimizzazioni aggressive sono state disabilitate:</p>
            <ul>
                <?php foreach ($disabled as $opt): ?>
                    <li><code><?php echo esc_html($opt); ?></code></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Cache pulita.</strong></p>
        </div>
        
        <div class="info">
            <h2>ℹ️ Ottimizzazioni Mantenute Attive</h2>
            <p>Le seguenti ottimizzazioni SICURE sono rimaste attive:</p>
            <ul>
                <li>✓ Minificazione CSS/JS (se già attiva)</li>
                <li>✓ Cache (Page Cache, Browser Cache)</li>
                <li>✓ Compressione</li>
                <li>✓ Lazy Loading Immagini</li>
                <li>✓ Rimozione Emoji</li>
                <li>✓ Ottimizzazioni Database</li>
            </ul>
        </div>
    <?php else: ?>
        <div class="info">
            <p>Nessuna ottimizzazione aggressiva trovata (già disabilitate o non configurate).</p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <h2>⚠️ Errori</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo esc_html($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="warning">
        <h2>📋 Ottimizzazioni Disabilitate</h2>
        <p>Le seguenti ottimizzazioni sono state disabilitate perché possono causare errori critici:</p>
        <ul>
            <li><strong>Defer/Async JavaScript</strong> - Possono rompere script che dipendono dall'ordine di caricamento</li>
            <li><strong>Unused CSS/JS Optimization</strong> - Possono rimuovere codice necessario</li>
            <li><strong>Code Splitting</strong> - Può causare problemi con dipendenze</li>
            <li><strong>Third Party Script Blocking</strong> - Può bloccare script essenziali</li>
            <li><strong>HTML Minification</strong> - Può rompere HTML malformato</li>
            <li><strong>Critical Path Optimization</strong> - Può causare problemi con CSS critico</li>
        </ul>
    </div>
    
    <div class="info">
        <h2>📋 Prossimi Passi</h2>
        <ol>
            <li><strong>Verifica il frontend</strong> - Il sito dovrebbe ora funzionare correttamente</li>
            <li><strong>Controlla i log</strong> - Vai in <code>wp-content/debug.log</code> per vedere eventuali errori</li>
            <li><strong>Riabilita gradualmente</strong> - Se necessario, attiva le ottimizzazioni una alla volta per identificare il problema</li>
            <li><strong>Rimuovi questo file</strong> - Elimina <code>DISATTIVA-OTTIMIZZAZIONI-AGGRESSIVE.php</code> dopo l'uso</li>
        </ol>
    </div>
    
    <p><a href="<?php echo admin_url('admin.php?page=fp-performance-suite'); ?>">← Torna a FP Performance</a></p>
    <p><a href="<?php echo home_url(); ?>">← Vai al Frontend</a></p>
</body>
</html>

