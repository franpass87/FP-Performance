<?php
/**
 * Test Fix White Screen - FP Performance Suite
 * 
 * Questo script verifica che il fix per il white screen funzioni correttamente
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Verifica permessi admin
if (!current_user_can('manage_options')) {
    die('Permessi insufficienti');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Fix White Screen - FP Performance Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Fix White Screen - FP Performance Suite</h1>
        
        <?php
        echo '<h2>🔍 Verifica Fix Applicato</h2>';
        
        // 1. Verifica se il plugin è attivo
        $plugin_active = is_plugin_active('fp-performance-suite/fp-performance-suite.php');
        echo '<p><strong>Plugin attivo:</strong> ' . ($plugin_active ? '✅ Sì' : '❌ No') . '</p>';
        
        // 2. Verifica variabile globale
        global $fp_perf_suite_initialized;
        echo '<p><strong>Variabile globale inizializzata:</strong> ' . (isset($fp_perf_suite_initialized) ? '✅ Sì' : '❌ No') . '</p>';
        if (isset($fp_perf_suite_initialized)) {
            echo '<p><strong>Valore variabile:</strong> ' . ($fp_perf_suite_initialized ? '✅ true' : '❌ false') . '</p>';
        }
        
        // 3. Verifica se il fix è stato applicato
        $plugin_file = WP_PLUGIN_DIR . '/fp-performance-suite/fp-performance-suite.php';
        if (file_exists($plugin_file)) {
            $content = file_get_contents($plugin_file);
            $fix_applied = strpos($content, '// Marca come inizializzato IMMEDIATAMENTE per prevenire race conditions') !== false;
            echo '<p><strong>Fix applicato:</strong> ' . ($fix_applied ? '✅ Sì' : '❌ No') . '</p>';
        }
        
        // 4. Verifica errori recenti
        $error_log = ini_get('error_log');
        if ($error_log && file_exists($error_log)) {
            $recent_errors = file_get_contents($error_log);
            $fp_errors = substr_count($recent_errors, '[FP-PerfSuite]');
            echo '<p><strong>Errori FP Performance Suite negli ultimi log:</strong> ' . $fp_errors . '</p>';
        }
        
        // 5. Test di inizializzazione
        echo '<h3>🧪 Test di Inizializzazione</h3>';
        
        if ($plugin_active) {
            echo '<div class="info">';
            echo '<h4>Plugin Attivo - Test in Corso</h4>';
            echo '<p>Il plugin è attivo. Verifica manualmente:</p>';
            echo '<ul>';
            echo '<li>Vai su <strong>wp-admin</strong> e controlla che le pagine non siano vuote</li>';
            echo '<li>Controlla che il menu "FP Performance" sia visibile</li>';
            echo '<li>Verifica che non ci siano errori PHP</li>';
            echo '</ul>';
            echo '</div>';
        } else {
            echo '<div class="warning">';
            echo '<h4>Plugin Disattivo</h4>';
            echo '<p>Il plugin è disattivo. Per testare il fix:</p>';
            echo '<ol>';
            echo '<li>Vai su <strong>wp-admin/plugins.php</strong></li>';
            echo '<li>Attiva "FP Performance Suite"</li>';
            echo '<li>Verifica che le pagine admin funzionino</li>';
            echo '</ol>';
            echo '</div>';
        }
        
        // 6. Informazioni di sistema
        echo '<h3>📊 Informazioni Sistema</h3>';
        echo '<p><strong>Versione WordPress:</strong> ' . get_bloginfo('version') . '</p>';
        echo '<p><strong>Versione PHP:</strong> ' . PHP_VERSION . '</p>';
        echo '<p><strong>Memoria disponibile:</strong> ' . ini_get('memory_limit') . '</p>';
        echo '<p><strong>Debug WordPress:</strong> ' . (defined('WP_DEBUG') && WP_DEBUG ? 'Abilitato' : 'Disabilitato') . '</p>';
        echo '<p><strong>Plugin directory:</strong> ' . WP_PLUGIN_DIR . '</p>';
        
        // 7. Istruzioni per il test
        echo '<h3>📋 Istruzioni per il Test</h3>';
        echo '<div class="code">';
        echo '<h4>Test 1: Verifica Pagine Admin</h4>';
        echo '<ol>';
        echo '<li>Vai su <strong>wp-admin</strong></li>';
        echo '<li>Controlla che tutte le pagine si carichino normalmente</li>';
        echo '<li>Verifica che non ci siano pagine vuote</li>';
        echo '</ol>';
        
        echo '<h4>Test 2: Verifica Plugin FP Performance</h4>';
        echo '<ol>';
        echo '<li>Vai su <strong>wp-admin/plugins.php</strong></li>';
        echo '<li>Attiva "FP Performance Suite" se non è attivo</li>';
        echo '<li>Vai su <strong>FP Performance</strong> nel menu admin</li>';
        echo '<li>Verifica che tutte le pagine del plugin funzionino</li>';
        echo '</ol>';
        
        echo '<h4>Test 3: Verifica Log</h4>';
        echo '<ol>';
        echo '<li>Controlla i log di WordPress</li>';
        echo '<li>Verifica che non ci siano errori di inizializzazione multipla</li>';
        echo '<li>Controlla che i servizi si registrino una sola volta</li>';
        echo '</ol>';
        echo '</div>';
        
        // 8. Risultato atteso
        echo '<h3>✅ Risultato Atteso</h3>';
        echo '<div class="success">';
        echo '<h4>Dopo il Fix, dovresti vedere:</h4>';
        echo '<ul>';
        echo '<li>✅ Pagine admin funzionanti (nessun white screen)</li>';
        echo '<li>✅ Plugin FP Performance operativo</li>';
        echo '<li>✅ Menu "FP Performance" visibile e funzionante</li>';
        echo '<li>✅ Nessun errore di inizializzazione multipla nei log</li>';
        echo '<li>✅ Servizi registrati una sola volta</li>';
        echo '</ul>';
        echo '</div>';
        ?>
        
        <hr>
        <p><small>Test creato per verificare il fix del white screen del plugin FP Performance Suite.</small></p>
    </div>
</body>
</html>
