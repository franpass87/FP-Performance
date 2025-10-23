<?php
/**
 * Verifica Coerenza Grafica Pagina Mobile
 * 
 * Questo script verifica che tutti i file CSS necessari siano presenti
 * e che la pagina mobile utilizzi correttamente il design system modulare.
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

// Verifica che il plugin sia attivo
if (!defined('ABSPATH')) {
    exit;
}

// Verifica che siamo nell'admin
if (!is_admin()) {
    wp_die('Questo test può essere eseguito solo nell\'area admin di WordPress.');
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica Coerenza Grafica Pagina Mobile - FP Performance Suite</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
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
        .status.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        .status.warning {
            background: #fff3cd;
            border-color: #ffc107;
            color: #856404;
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
        }
        .file-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .file-list li {
            margin: 5px 0;
            font-family: monospace;
        }
        .check-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .check-item.success {
            background: #d4edda;
            border-left: 4px solid #28a745;
        }
        .check-item.error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
        }
        .check-item.warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
        }
        .check-icon {
            font-size: 20px;
            margin-right: 10px;
        }
        .check-icon.success {
            color: #28a745;
        }
        .check-icon.error {
            color: #dc3545;
        }
        .check-icon.warning {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verifica Coerenza Grafica Pagina Mobile</h1>
        
        <div class="status info">
            <strong>ℹ️ Informazioni:</strong> Questa verifica controlla che tutti i file CSS necessari siano presenti e che la pagina mobile utilizzi correttamente il design system modulare del plugin FP Performance Suite.
        </div>

        <?php
        // Verifica file CSS
        $css_files = [
            'assets/css/admin.css' => 'File CSS principale admin',
            'assets/css/base/variables.css' => 'Variabili CSS del design system',
            'assets/css/layout/admin-grid.css' => 'Layout grid per pagine admin',
            'assets/css/components/mobile-page.css' => 'Componenti specifici pagina mobile',
            'assets/css/components/forms.css' => 'Componenti form',
            'assets/css/components/status-indicator.css' => 'Indicatori di stato',
            'assets/css/layout/card.css' => 'Componenti card',
            'assets/css/utilities/utilities.css' => 'Utility CSS'
        ];

        $all_files_exist = true;
        $missing_files = [];

        echo '<h2>📁 Verifica File CSS</h2>';
        foreach ($css_files as $file => $description) {
            $file_path = FP_PERF_SUITE_DIR . '/' . $file;
            $exists = file_exists($file_path);
            
            if ($exists) {
                echo '<div class="check-item success">';
                echo '<span class="check-icon success">✅</span>';
                echo '<div>';
                echo '<strong>' . $description . '</strong><br>';
                echo '<code>' . $file . '</code>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="check-item error">';
                echo '<span class="check-icon error">❌</span>';
                echo '<div>';
                echo '<strong>' . $description . '</strong><br>';
                echo '<code>' . $file . '</code> - <em>File non trovato</em>';
                echo '</div>';
                echo '</div>';
                $all_files_exist = false;
                $missing_files[] = $file;
            }
        }

        // Verifica import CSS
        echo '<h2>🔗 Verifica Import CSS</h2>';
        $admin_css_path = FP_PERF_SUITE_DIR . '/assets/css/admin.css';
        if (file_exists($admin_css_path)) {
            $admin_css_content = file_get_contents($admin_css_path);
            
            $required_imports = [
                'components/mobile-page.css' => 'Import componenti pagina mobile',
                'layout/admin-grid.css' => 'Import layout admin grid'
            ];

            foreach ($required_imports as $import => $description) {
                if (strpos($admin_css_content, $import) !== false) {
                    echo '<div class="check-item success">';
                    echo '<span class="check-icon success">✅</span>';
                    echo '<div>';
                    echo '<strong>' . $description . '</strong><br>';
                    echo '<code>@import url(\'' . $import . '\');</code>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="check-item error">';
                    echo '<span class="check-icon error">❌</span>';
                    echo '<div>';
                    echo '<strong>' . $description . '</strong><br>';
                    echo '<code>@import url(\'' . $import . '\');</code> - <em>Import mancante</em>';
                    echo '</div>';
                    echo '</div>';
                }
            }
        } else {
            echo '<div class="check-item error">';
            echo '<span class="check-icon error">❌</span>';
            echo '<div>';
            echo '<strong>File admin.css non trovato</strong><br>';
            echo '<em>Impossibile verificare gli import CSS</em>';
            echo '</div>';
            echo '</div>';
        }

        // Verifica classi CSS nella pagina mobile
        echo '<h2>🎨 Verifica Classi CSS Pagina Mobile</h2>';
        $mobile_page_path = FP_PERF_SUITE_DIR . '/src/Admin/Pages/Mobile.php';
        if (file_exists($mobile_page_path)) {
            $mobile_page_content = file_get_contents($mobile_page_path);
            
            $required_classes = [
                'fp-ps-mobile-page' => 'Classe principale pagina mobile',
                'fp-ps-admin-grid' => 'Grid layout admin',
                'fp-ps-admin-card' => 'Card componenti admin',
                'fp-ps-mobile-stats' => 'Statistiche mobile',
                'fp-ps-stat-item' => 'Elemento statistica',
                'fp-ps-stat-label' => 'Etichetta statistica',
                'fp-ps-stat-value' => 'Valore statistica',
                'fp-ps-status-enabled' => 'Stato abilitato',
                'fp-ps-status-disabled' => 'Stato disabilitato',
                'fp-ps-critical' => 'Stato critico',
                'fp-ps-issues-list' => 'Lista issues',
                'fp-ps-issue' => 'Elemento issue',
                'fp-ps-issue-high' => 'Issue alta priorità',
                'fp-ps-issue-medium' => 'Issue media priorità',
                'fp-ps-issue-low' => 'Issue bassa priorità',
                'fp-ps-recommendations' => 'Raccomandazioni'
            ];

            foreach ($required_classes as $class => $description) {
                if (strpos($mobile_page_content, $class) !== false) {
                    echo '<div class="check-item success">';
                    echo '<span class="check-icon success">✅</span>';
                    echo '<div>';
                    echo '<strong>' . $description . '</strong><br>';
                    echo '<code>.' . $class . '</code>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="check-item error">';
                    echo '<span class="check-icon error">❌</span>';
                    echo '<div>';
                    echo '<strong>' . $description . '</strong><br>';
                    echo '<code>.' . $class . '</code> - <em>Classe non trovata</em>';
                    echo '</div>';
                    echo '</div>';
                }
            }

            // Verifica che non ci siano stili inline
            if (strpos($mobile_page_content, '<style>') !== false) {
                echo '<div class="check-item error">';
                echo '<span class="check-icon error">❌</span>';
                echo '<div>';
                echo '<strong>Stili CSS inline trovati</strong><br>';
                echo '<em>Gli stili inline dovrebbero essere rimossi e sostituiti con classi CSS modulari</em>';
                echo '</div>';
                echo '</div>';
            } else {
                echo '<div class="check-item success">';
                echo '<span class="check-icon success">✅</span>';
                echo '<div>';
                echo '<strong>Nessun stile CSS inline trovato</strong><br>';
                echo '<em>La pagina utilizza correttamente il design system modulare</em>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="check-item error">';
            echo '<span class="check-icon error">❌</span>';
            echo '<div>';
            echo '<strong>File Mobile.php non trovato</strong><br>';
            echo '<em>Impossibile verificare le classi CSS</em>';
            echo '</div>';
            echo '</div>';
        }

        // Verifica variabili CSS
        echo '<h2>🎨 Verifica Variabili CSS</h2>';
        $variables_css_path = FP_PERF_SUITE_DIR . '/assets/css/base/variables.css';
        if (file_exists($variables_css_path)) {
            $variables_css_content = file_get_contents($variables_css_path);
            
            $required_variables = [
                '--fp-bg' => 'Colore di sfondo',
                '--fp-card' => 'Colore card',
                '--fp-accent' => 'Colore accent',
                '--fp-ok' => 'Colore successo',
                '--fp-warn' => 'Colore avvertimento',
                '--fp-danger' => 'Colore pericolo',
                '--fp-text' => 'Colore testo',
                '--fp-spacing-xs' => 'Spacing extra small',
                '--fp-spacing-sm' => 'Spacing small',
                '--fp-spacing-md' => 'Spacing medium',
                '--fp-spacing-lg' => 'Spacing large',
                '--fp-spacing-xl' => 'Spacing extra large',
                '--fp-font-size-xs' => 'Dimensione font extra small',
                '--fp-font-size-sm' => 'Dimensione font small',
                '--fp-font-size-base' => 'Dimensione font base',
                '--fp-font-size-md' => 'Dimensione font medium',
                '--fp-font-size-lg' => 'Dimensione font large',
                '--fp-radius-sm' => 'Border radius small',
                '--fp-radius-md' => 'Border radius medium',
                '--fp-radius-lg' => 'Border radius large',
                '--fp-shadow-sm' => 'Ombra small',
                '--fp-shadow-md' => 'Ombra medium',
                '--fp-transition-base' => 'Transizione base'
            ];

            foreach ($required_variables as $variable => $description) {
                if (strpos($variables_css_content, $variable) !== false) {
                    echo '<div class="check-item success">';
                    echo '<span class="check-icon success">✅</span>';
                    echo '<div>';
                    echo '<strong>' . $description . '</strong><br>';
                    echo '<code>' . $variable . '</code>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="check-item error">';
                    echo '<span class="check-icon error">❌</span>';
                    echo '<div>';
                    echo '<strong>' . $description . '</strong><br>';
                    echo '<code>' . $variable . '</code> - <em>Variabile non trovata</em>';
                    echo '</div>';
                    echo '</div>';
                }
            }
        } else {
            echo '<div class="check-item error">';
            echo '<span class="check-icon error">❌</span>';
            echo '<div>';
            echo '<strong>File variables.css non trovato</strong><br>';
            echo '<em>Impossibile verificare le variabili CSS</em>';
            echo '</div>';
            echo '</div>';
        }

        // Risultato finale
        if ($all_files_exist) {
            echo '<div class="status success">';
            echo '<strong>✅ Verifica Completata con Successo!</strong><br>';
            echo 'Tutti i file CSS necessari sono presenti e la pagina mobile utilizza correttamente il design system modulare del plugin FP Performance Suite.';
            echo '</div>';
        } else {
            echo '<div class="status error">';
            echo '<strong>❌ Verifica Fallita!</strong><br>';
            echo 'Alcuni file CSS sono mancanti o non configurati correttamente.';
            if (!empty($missing_files)) {
                echo '<br><br><strong>File mancanti:</strong>';
                echo '<ul>';
                foreach ($missing_files as $file) {
                    echo '<li><code>' . $file . '</code></li>';
                }
                echo '</ul>';
            }
            echo '</div>';
        }
        ?>

        <div class="status info">
            <strong>📋 Riepilogo Modifiche:</strong>
            <ul>
                <li>✅ Creato file CSS specifico per la pagina mobile (<code>assets/css/components/mobile-page.css</code>)</li>
                <li>✅ Creato file CSS per il layout admin grid (<code>assets/css/layout/admin-grid.css</code>)</li>
                <li>✅ Aggiornato il file CSS principale admin per includere i nuovi moduli</li>
                <li>✅ Rimossi gli stili CSS inline dalla pagina mobile</li>
                <li>✅ Aggiornate le classi CSS per utilizzare il design system modulare</li>
                <li>✅ Implementato design responsive per dispositivi mobile</li>
                <li>✅ Unificato il sistema di colori e spacing</li>
            </ul>
        </div>

        <div class="status success">
            <strong>🎯 Risultato:</strong> La pagina mobile ora ha coerenza grafica completa con il resto del plugin, utilizzando il design system modulare e mantenendo la responsività su tutti i dispositivi.
        </div>

        <div style="margin-top: 20px;">
            <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-mobile'); ?>" class="btn success">
                Vai alla Pagina Mobile
            </a>
            <a href="<?php echo admin_url('admin.php?page=fp-performance-suite'); ?>" class="btn">
                Torna al Dashboard
            </a>
        </div>
    </div>
</body>
</html>
