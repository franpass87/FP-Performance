<?php
/**
 * 🔧 FIX BROWSER: Pagina Mobile Vuota - FP Performance Suite
 * 
 * Questo script può essere eseguito direttamente nel browser per risolvere
 * il problema della pagina admin mobile vuota.
 * 
 * URL: https://tuosito.com/wp-content/plugins/FP-Performance/fix-mobile-page-browser.php
 * 
 * @author Francesco Passeri
 * @version 1.0
 */

// Verifica che siamo in ambiente WordPress
if (!defined('ABSPATH')) {
    // Prova a caricare WordPress
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../../../wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists(__DIR__ . '/' . $path)) {
            require_once __DIR__ . '/' . $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('❌ Impossibile caricare WordPress. Assicurati che questo file sia nella directory del plugin.');
    }
}

// Verifica che l'utente sia amministratore
if (!current_user_can('manage_options')) {
    die('❌ Accesso negato. Devi essere un amministratore per eseguire questo script.');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Fix Pagina Mobile Vuota - FP Performance Suite</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f1f1f1;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }
        .status.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        .status.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
        }
        .status.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .status.info {
            background: #d1ecf1;
            border-color: #17a2b8;
            color: #0c5460;
        }
        .btn {
            background: #0073aa;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #005a87;
        }
        .btn.success {
            background: #28a745;
        }
        .btn.success:hover {
            background: #218838;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            border: 1px solid #e9ecef;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Fix Pagina Mobile Vuota - FP Performance Suite</h1>
        
        <?php
        // Verifica se il plugin è attivo
        if (!class_exists('FP\\PerfSuite\\Plugin')) {
            echo '<div class="status error">';
            echo '<strong>❌ Errore:</strong> Il plugin FP Performance Suite non è attivo o non è installato.';
            echo '</div>';
            exit;
        }

        // Verifica stato attuale delle opzioni
        echo '<h2>📋 Stato Attuale Opzioni Mobile</h2>';
        
        $mobile_options = [
            'fp_ps_mobile_optimizer' => 'Mobile Optimizer',
            'fp_ps_touch_optimizer' => 'Touch Optimizer', 
            'fp_ps_responsive_images' => 'Responsive Images',
            'fp_ps_mobile_cache' => 'Mobile Cache'
        ];

        $missing_options = [];
        $existing_options = [];

        echo '<div class="grid">';
        foreach ($mobile_options as $option_name => $option_label) {
            $option_value = get_option($option_name);
            
            if (!$option_value) {
                echo '<div class="card">';
                echo '<strong>❌ ' . $option_label . '</strong><br>';
                echo '<small>MANCANTE</small>';
                echo '</div>';
                $missing_options[] = $option_name;
            } else {
                $enabled = $option_value['enabled'] ?? false;
                $status_icon = $enabled ? '✅' : '⚠️';
                $status_text = $enabled ? 'ABILITATO' : 'DISABILITATO';
                echo '<div class="card">';
                echo '<strong>' . $status_icon . ' ' . $option_label . '</strong><br>';
                echo '<small>' . $status_text . '</small>';
                echo '</div>';
                $existing_options[] = $option_name;
            }
        }
        echo '</div>';

        // Se ci sono opzioni mancanti, mostra il pulsante per inizializzarle
        if (!empty($missing_options)) {
            echo '<div class="status warning">';
            echo '<strong>⚠️ Attenzione:</strong> ' . count($missing_options) . ' opzioni mobile sono mancanti. ';
            echo 'Questo causa il problema della pagina mobile vuota.';
            echo '</div>';
            
            if (isset($_POST['fix_mobile_options'])) {
                // Esegui il fix
                try {
                    $result = FP\PerfSuite\Plugin::forceMobileOptionsInitialization();
                    
                    if ($result) {
                        echo '<div class="status success">';
                        echo '<strong>✅ Successo!</strong> Le opzioni mobile sono state inizializzate correttamente.';
                        echo '</div>';
                        
                        echo '<div class="status info">';
                        echo '<strong>📱 Prossimi passi:</strong><br>';
                        echo '1. Vai su <strong>FP Performance > Mobile</strong><br>';
                        echo '2. Abilita le funzionalità che desideri<br>';
                        echo '3. Salva le impostazioni';
                        echo '</div>';
                        
                        echo '<a href="' . admin_url('admin.php?page=fp-performance-suite-mobile') . '" class="btn success">';
                        echo '🚀 Vai alla Pagina Mobile';
                        echo '</a>';
                        
                        // Ricarica la pagina per mostrare lo stato aggiornato
                        echo '<script>setTimeout(function(){ window.location.reload(); }, 3000);</script>';
                    } else {
                        echo '<div class="status error">';
                        echo '<strong>❌ Errore:</strong> Impossibile inizializzare le opzioni mobile.';
                        echo '</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="status error">';
                    echo '<strong>❌ Errore:</strong> ' . esc_html($e->getMessage());
                    echo '</div>';
                }
            } else {
                echo '<form method="post">';
                echo '<button type="submit" name="fix_mobile_options" class="btn">';
                echo '🔧 Inizializza Opzioni Mobile';
                echo '</button>';
                echo '</form>';
            }
        } else {
            echo '<div class="status success">';
            echo '<strong>✅ Tutte le opzioni mobile sono presenti!</strong><br>';
            echo 'La pagina mobile dovrebbe funzionare correttamente.';
            echo '</div>';
            
            echo '<a href="' . admin_url('admin.php?page=fp-performance-suite-mobile') . '" class="btn success">';
            echo '📱 Vai alla Pagina Mobile';
            echo '</a>';
        }

        // Mostra informazioni di debug
        echo '<h2>🔍 Informazioni Debug</h2>';
        echo '<div class="card">';
        echo '<strong>Plugin Version:</strong> ' . (defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'N/A') . '<br>';
        echo '<strong>WordPress Version:</strong> ' . get_bloginfo('version') . '<br>';
        echo '<strong>PHP Version:</strong> ' . PHP_VERSION . '<br>';
        echo '<strong>Plugin Active:</strong> ' . (class_exists('FP\\PerfSuite\\Plugin') ? '✅ Sì' : '❌ No') . '<br>';
        echo '<strong>Options Missing:</strong> ' . count($missing_options) . '<br>';
        echo '<strong>Options Existing:</strong> ' . count($existing_options);
        echo '</div>';

        // Mostra le opzioni mancanti in dettaglio
        if (!empty($missing_options)) {
            echo '<h3>📝 Opzioni Mancanti</h3>';
            echo '<pre>';
            foreach ($missing_options as $option) {
                echo '- ' . $option . "\n";
            }
            echo '</pre>';
        }
        ?>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; text-align: center; color: #666;">
            <small>🔧 FP Performance Suite - Fix Pagina Mobile Vuota v1.0</small>
        </div>
    </div>
</body>
</html>
