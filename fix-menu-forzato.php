<?php
/**
 * FIX FORZATO MENU ADMIN FP PERFORMANCE
 * 
 * Questo script forza la visibilità del menu admin
 * Esegui questo file direttamente nel browser
 */

// Carica WordPress se non è già caricato
if (!function_exists('wp_get_current_user')) {
    require_once('wp-config.php');
    require_once('wp-load.php');
}

// Verifica che l'utente sia admin
if (!current_user_can('manage_options')) {
    die('❌ Accesso negato. Solo gli amministratori possono eseguire questo fix.');
}

echo '<h1>🔧 Fix Forzato Menu FP Performance</h1>';
echo '<div style="font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';

$fixes_applied = [];

try {
    // 1. Forza reset variabile globale
    echo '<h2>1. 🔄 Reset Variabile Globale</h2>';
    
    global $fp_perf_suite_initialized;
    $fp_perf_suite_initialized = false;
    
    // Reset anche la costante se esiste
    if (defined('FP_PERF_SUITE_INITIALIZED')) {
        // Non possiamo undefinire una costante, ma possiamo forzare il reset
        echo '⚠️ Costante FP_PERF_SUITE_INITIALIZED già definita<br>';
    }
    
    $fixes_applied[] = 'Reset variabile globale';
    echo '✅ Variabile globale resettata<br>';
    
    // 2. Forza re-inizializzazione plugin
    echo '<h2>2. 🚀 Re-inizializzazione Plugin</h2>';
    
    if (class_exists('FP\\PerfSuite\\Plugin')) {
        // Reset stato plugin
        if (method_exists('FP\\PerfSuite\\Plugin', 'reset')) {
            FP\PerfSuite\Plugin::reset();
            echo '✅ Stato plugin resettato<br>';
        }
        
        // Forza inizializzazione
        try {
            FP\PerfSuite\Plugin::init();
            echo '✅ Plugin re-inizializzato<br>';
            $fixes_applied[] = 'Plugin re-inizializzato';
        } catch (Exception $e) {
            echo '❌ Errore durante re-inizializzazione: ' . $e->getMessage() . '<br>';
        }
    } else {
        echo '❌ Classe Plugin non trovata<br>';
    }
    
    // 3. Forza registrazione menu manuale
    echo '<h2>3. 📋 Registrazione Menu Manuale</h2>';
    
    // Rimuovi hook esistenti
    remove_all_actions('admin_menu');
    
    // Registra menu manualmente
    add_action('admin_menu', function() {
        // Menu principale
        add_menu_page(
            'FP Performance Suite',
            'FP Performance',
            'manage_options',
            'fp-performance',
            function() {
                echo '<div class="wrap">';
                echo '<h1>FP Performance Suite</h1>';
                echo '<p>Benvenuto nel pannello di controllo FP Performance Suite!</p>';
                echo '<p>Il menu è stato ripristinato con successo.</p>';
                echo '</div>';
            },
            'dashicons-performance',
            30
        );
        
        // Submenu
        add_submenu_page(
            'fp-performance',
            'Dashboard',
            'Dashboard',
            'manage_options',
            'fp-performance',
            function() {
                echo '<div class="wrap">';
                echo '<h1>Dashboard FP Performance</h1>';
                echo '<p>Dashboard principale del plugin.</p>';
                echo '</div>';
            }
        );
        
        add_submenu_page(
            'fp-performance',
            'Impostazioni',
            'Impostazioni',
            'manage_options',
            'fp-performance-settings',
            function() {
                echo '<div class="wrap">';
                echo '<h1>Impostazioni FP Performance</h1>';
                echo '<p>Configurazione del plugin.</p>';
                echo '</div>';
            }
        );
    });
    
    echo '✅ Menu registrato manualmente<br>';
    $fixes_applied[] = 'Menu registrato manualmente';
    
    // 4. Verifica opzioni plugin
    echo '<h2>4. ⚙️ Verifica Opzioni Plugin</h2>';
    
    $settings = get_option('fp_ps_settings', []);
    if (!is_array($settings) || !isset($settings['allowed_role'])) {
        update_option('fp_ps_settings', [
            'allowed_role' => 'administrator',
            'debug_mode' => false,
            'auto_optimize' => false
        ]);
        echo '✅ Opzioni plugin create<br>';
        $fixes_applied[] = 'Opzioni plugin create';
    } else {
        echo '✅ Opzioni plugin esistenti<br>';
    }
    
    // 5. Pulisci cache
    echo '<h2>5. 🧹 Pulizia Cache</h2>';
    
    // Pulisci cache WordPress
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        echo '✅ Cache WordPress pulita<br>';
    }
    
    // Pulisci cache plugin
    $cache_dir = WP_CONTENT_DIR . '/cache/fp-performance-suite';
    if (file_exists($cache_dir)) {
        $files = glob($cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo '✅ Cache plugin pulita<br>';
    }
    
    $fixes_applied[] = 'Cache pulita';
    
    // 6. Verifica finale
    echo '<h2>6. ✅ Verifica Finale</h2>';
    
    global $menu, $submenu;
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
    
    if ($menu_found) {
        echo '🎉 <strong>SUCCESSO! Menu visibile!</strong><br>';
    } else {
        echo '⚠️ Menu non ancora visibile, potrebbe essere necessario ricaricare la pagina<br>';
    }
    
    // Riepilogo
    echo '<h2>📊 Riepilogo Fix Applicati</h2>';
    echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>Fix applicati:</strong><br>';
    foreach ($fixes_applied as $fix) {
        echo '✅ ' . $fix . '<br>';
    }
    echo '</div>';
    
    // Link per testare
    echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>Prossimi passi:</strong><br>';
    echo '1. <a href="' . admin_url() . '" target="_blank">Vai al Menu Admin</a><br>';
    echo '2. Cerca "FP Performance" nel menu laterale<br>';
    echo '3. Se non vedi il menu, ricarica la pagina (F5)<br>';
    echo '4. Se ancora non funziona, esegui il test completo: <a href="test-menu-visibilita.php">Test Menu</a><br>';
    echo '</div>';
    
} catch (Exception $e) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;">';
    echo '<strong>❌ Errore durante il fix:</strong><br>';
    echo $e->getMessage() . '<br>';
    echo '<strong>File:</strong> ' . $e->getFile() . '<br>';
    echo '<strong>Linea:</strong> ' . $e->getLine() . '<br>';
    echo '</div>';
}

echo '</div>';
?>
