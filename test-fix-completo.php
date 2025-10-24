<?php
/**
 * Test Fix Completo - FP Performance Suite
 * 
 * Questo script verifica che tutti i fix per il white screen funzionino correttamente
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
    <title>Test Fix Completo - FP Performance Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f1f1f1; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; font-family: monospace; margin: 10px 0; }
        .status { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .status-ok { background: #d4edda; color: #155724; }
        .status-error { background: #f8d7da; color: #721c24; }
        .status-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Fix Completo - FP Performance Suite</h1>
        
        <?php
        echo '<h2>🔍 Verifica Fix Applicati</h2>';
        
        // 1. Verifica plugin attivo
        $plugin_active = is_plugin_active('fp-performance-suite/fp-performance-suite.php');
        echo '<p><strong>Plugin attivo:</strong> <span class="status ' . ($plugin_active ? 'status-ok' : 'status-error') . '">' . ($plugin_active ? 'SÌ' : 'NO') . '</span></p>';
        
        // 2. Verifica variabile globale
        global $fp_perf_suite_initialized;
        $global_initialized = isset($fp_perf_suite_initialized) && $fp_perf_suite_initialized;
        echo '<p><strong>Variabile globale inizializzata:</strong> <span class="status ' . ($global_initialized ? 'status-ok' : 'status-error') . '">' . ($global_initialized ? 'SÌ' : 'NO') . '</span></p>';
        
        // 3. Verifica fix file principale
        $plugin_file = WP_PLUGIN_DIR . '/fp-performance-suite/fp-performance-suite.php';
        $fix_applied = false;
        if (file_exists($plugin_file)) {
            $content = file_get_contents($plugin_file);
            $fix_applied = strpos($content, '// Marca come inizializzato IMMEDIATAMENTE per prevenire race conditions') !== false;
        }
        echo '<p><strong>Fix file principale:</strong> <span class="status ' . ($fix_applied ? 'status-ok' : 'status-error') . '">' . ($fix_applied ? 'APPLICATO' : 'NON APPLICATO') . '</span></p>';
        
        // 4. Verifica fix Plugin.php
        $plugin_src_file = WP_PLUGIN_DIR . '/fp-performance-suite/src/Plugin.php';
        $fix_plugin_applied = false;
        if (file_exists($plugin_src_file)) {
            $content = file_get_contents($plugin_src_file);
            $fix_plugin_applied = strpos($content, '|| $fp_perf_suite_initialized') !== false;
        }
        echo '<p><strong>Fix Plugin.php:</strong> <span class="status ' . ($fix_plugin_applied ? 'status-ok' : 'status-error') . '">' . ($fix_plugin_applied ? 'APPLICATO' : 'NON APPLICATO') . '</span></p>';
        
        // 5. Verifica fix Menu.php
        $menu_file = WP_PLUGIN_DIR . '/fp-performance-suite/src/Admin/Menu.php';
        $fix_menu_applied = false;
        if (file_exists($menu_file)) {
            $content = file_get_contents($menu_file);
            $fix_menu_applied = strpos($content, 'private static bool $menuRegistered = false;') !== false;
        }
        echo '<p><strong>Fix Menu.php:</strong> <span class="status ' . ($fix_menu_applied ? 'status-ok' : 'status-error') . '">' . ($fix_menu_applied ? 'APPLICATO' : 'NON APPLICATO') . '</span></p>';
        
        // 6. Verifica errori recenti
        $error_log = ini_get('error_log');
        $fp_errors = 0;
        $initialization_errors = 0;
        if ($error_log && file_exists($error_log)) {
            $recent_errors = file_get_contents($error_log);
            $fp_errors = substr_count($recent_errors, '[FP-PerfSuite]');
            $initialization_errors = substr_count($recent_errors, 'Plugin::init() called');
        }
        echo '<p><strong>Errori FP Performance Suite negli ultimi log:</strong> ' . $fp_errors . '</p>';
        echo '<p><strong>Chiamate Plugin::init() negli ultimi log:</strong> ' . $initialization_errors . '</p>';
        
        // 7. Test di funzionamento
        echo '<h3>🧪 Test di Funzionamento</h3>';
        
        if ($plugin_active) {
            echo '<div class="info">';
            echo '<h4>Plugin Attivo - Test in Corso</h4>';
            echo '<p>Il plugin è attivo. Verifica manualmente:</p>';
            echo '<ul>';
            echo '<li>✅ Vai su <strong>wp-admin</strong> e controlla che le pagine non siano vuote</li>';
            echo '<li>✅ Controlla che il menu "FP Performance" sia visibile</li>';
            echo '<li>✅ Verifica che non ci siano errori PHP</li>';
            echo '<li>✅ Controlla che i servizi si registrino una sola volta</li>';
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
        
        // 8. Analisi dei log
        echo '<h3>📊 Analisi dei Log</h3>';
        
        if ($initialization_errors > 1) {
            echo '<div class="error">';
            echo '<h4>❌ Problema: Inizializzazione Multipla</h4>';
            echo '<p>Il plugin si sta ancora inizializzando più volte (' . $initialization_errors . ' volte).</p>';
            echo '<p><strong>Possibili cause:</strong></p>';
            echo '<ul>';
            echo '<li>Fix non applicato correttamente</li>';
            echo '<li>Cache del plugin non pulita</li>';
            echo '<li>Conflitto con altri plugin</li>';
            echo '</ul>';
            echo '</div>';
        } elseif ($initialization_errors == 1) {
            echo '<div class="success">';
            echo '<h4>✅ Inizializzazione Corretta</h4>';
            echo '<p>Il plugin si inizializza una sola volta (corretto).</p>';
            echo '</div>';
        } else {
            echo '<div class="info">';
            echo '<h4>ℹ️ Nessuna Inizializzazione Rilevata</h4>';
            echo '<p>Non sono state rilevate inizializzazioni recenti nei log.</p>';
            echo '</div>';
        }
        
        // 9. Istruzioni per il test completo
        echo '<h3>📋 Test Completo</h3>';
        echo '<div class="code">';
        echo '<h4>Test 1: Verifica Pagine Admin</h4>';
        echo '<ol>';
        echo '<li>Vai su <strong>wp-admin</strong></li>';
        echo '<li>Controlla che tutte le pagine si carichino normalmente</li>';
        echo '<li>Verifica che non ci siano pagine vuote o white screen</li>';
        echo '<li>Naviga tra le diverse sezioni dell\'admin</li>';
        echo '</ol>';
        
        echo '<h4>Test 2: Verifica Plugin FP Performance</h4>';
        echo '<ol>';
        echo '<li>Vai su <strong>wp-admin/plugins.php</strong></li>';
        echo '<li>Attiva "FP Performance Suite" se non è attivo</li>';
        echo '<li>Vai su <strong>FP Performance</strong> nel menu admin</li>';
        echo '<li>Verifica che tutte le pagine del plugin funzionino</li>';
        echo '<li>Controlla che il menu sia visibile e accessibile</li>';
        echo '</ol>';
        
        echo '<h4>Test 3: Verifica Log</h4>';
        echo '<ol>';
        echo '<li>Controlla i log di WordPress</li>';
        echo '<li>Verifica che non ci siano errori di inizializzazione multipla</li>';
        echo '<li>Controlla che i servizi si registrino una sola volta</li>';
        echo '<li>Verifica che il menu si registri una sola volta</li>';
        echo '</ol>';
        echo '</div>';
        
        // 10. Risultato atteso
        echo '<h3>✅ Risultato Atteso</h3>';
        echo '<div class="success">';
        echo '<h4>Dopo tutti i Fix, dovresti vedere:</h4>';
        echo '<ul>';
        echo '<li>✅ <strong>Pagine admin funzionanti</strong> (nessun white screen)</li>';
        echo '<li>✅ <strong>Plugin FP Performance operativo</strong></li>';
        echo '<li>✅ <strong>Menu "FP Performance" visibile e funzionante</strong></li>';
        echo '<li>✅ <strong>Inizializzazione singola</strong> (1 volta sola nei log)</li>';
        echo '<li>✅ <strong>Servizi registrati una sola volta</strong></li>';
        echo '<li>✅ <strong>Menu registrato una sola volta</strong></li>';
        echo '<li>✅ <strong>Nessun errore di inizializzazione multipla</strong></li>';
        echo '</ul>';
        echo '</div>';
        
        // 11. Informazioni di sistema
        echo '<h3>📊 Informazioni Sistema</h3>';
        echo '<p><strong>Versione WordPress:</strong> ' . get_bloginfo('version') . '</p>';
        echo '<p><strong>Versione PHP:</strong> ' . PHP_VERSION . '</p>';
        echo '<p><strong>Memoria disponibile:</strong> ' . ini_get('memory_limit') . '</p>';
        echo '<p><strong>Debug WordPress:</strong> ' . (defined('WP_DEBUG') && WP_DEBUG ? 'Abilitato' : 'Disabilitato') . '</p>';
        echo '<p><strong>Plugin directory:</strong> ' . WP_PLUGIN_DIR . '</p>';
        echo '<p><strong>Timestamp test:</strong> ' . date('Y-m-d H:i:s') . '</p>';
        ?>
        
        <hr>
        <p><small>Test completo creato per verificare tutti i fix del white screen del plugin FP Performance Suite.</small></p>
    </div>
</body>
</html>
