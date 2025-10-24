<?php
/**
 * Script di emergenza per disattivare il plugin FP Performance Suite
 * 
 * Questo script disattiva il plugin in modo sicuro per risolvere
 * problemi di white screen in WordPress.
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../wp-load.php',
        '../wp-load.php',
        './wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('WordPress non trovato. Esegui questo script dalla directory del plugin.');
    }
}

// Verifica permessi
if (!current_user_can('manage_options')) {
    wp_die('Non hai i permessi necessari per eseguire questa operazione.');
}

echo "<h1>🚨 Disattivazione di Emergenza Plugin FP Performance Suite</h1>";

/**
 * Disattiva il plugin
 */
function fp_emergency_deactivate_plugin() {
    echo "<h2>🔧 Disattivazione Plugin</h2>";
    
    $plugin_file = 'fp-performance-suite/fp-performance-suite.php';
    
    // Verifica se il plugin è attivo
    if (is_plugin_active($plugin_file)) {
        echo "<p style='color: orange;'>⚠️ Plugin attivo, procedo con la disattivazione...</p>";
        
        // Disattiva il plugin
        deactivate_plugins($plugin_file);
        
        // Verifica se è stato disattivato
        if (!is_plugin_active($plugin_file)) {
            echo "<p style='color: green;'>✅ Plugin disattivato con successo!</p>";
            echo "<p><strong>Il tuo sito WordPress dovrebbe ora funzionare normalmente.</strong></p>";
            
            // Pulisci cache se possibile
            if (function_exists('wp_cache_flush')) {
                wp_cache_flush();
                echo "<p style='color: green;'>✅ Cache pulita</p>";
            }
            
            return true;
        } else {
            echo "<p style='color: red;'>❌ Impossibile disattivare il plugin automaticamente</p>";
            return false;
        }
    } else {
        echo "<p style='color: green;'>✅ Plugin già disattivato</p>";
        return true;
    }
}

/**
 * Rimuove i file di debug che potrebbero causare problemi
 */
function fp_cleanup_debug_files() {
    echo "<h2>🧹 Pulizia File di Debug</h2>";
    
    $debug_files = [
        'debug-initialization-issues.php',
        'fix-register-meta-errors.php',
        'fix-fp-git-updater-deprecated.php'
    ];
    
    $cleaned = 0;
    
    foreach ($debug_files as $file) {
        $file_path = __DIR__ . '/' . $file;
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                echo "<p style='color: green;'>✅ Rimosso: " . esc_html($file) . "</p>";
                $cleaned++;
            } else {
                echo "<p style='color: red;'>❌ Impossibile rimuovere: " . esc_html($file) . "</p>";
            }
        }
    }
    
    if ($cleaned > 0) {
        echo "<p style='color: green;'>✅ Rimossi $cleaned file di debug</p>";
    } else {
        echo "<p style='color: gray;'>- Nessun file di debug trovato</p>";
    }
}

/**
 * Crea un backup del plugin
 */
function fp_create_plugin_backup() {
    echo "<h2>💾 Backup Plugin</h2>";
    
    $backup_dir = __DIR__ . '/backup-' . date('Y-m-d-H-i-s');
    
    if (!mkdir($backup_dir, 0755, true)) {
        echo "<p style='color: red;'>❌ Impossibile creare directory di backup</p>";
        return false;
    }
    
    // Copia i file principali
    $main_files = [
        'fp-performance-suite.php',
        'src/Plugin.php'
    ];
    
    $backed_up = 0;
    
    foreach ($main_files as $file) {
        $source = __DIR__ . '/' . $file;
        $dest = $backup_dir . '/' . $file;
        
        if (file_exists($source)) {
            $dest_dir = dirname($dest);
            if (!is_dir($dest_dir)) {
                mkdir($dest_dir, 0755, true);
            }
            
            if (copy($source, $dest)) {
                echo "<p style='color: green;'>✅ Backup: " . esc_html($file) . "</p>";
                $backed_up++;
            } else {
                echo "<p style='color: red;'>❌ Errore backup: " . esc_html($file) . "</p>";
            }
        }
    }
    
    if ($backed_up > 0) {
        echo "<p style='color: green;'>✅ Backup creato in: " . basename($backup_dir) . "</p>";
        return true;
    } else {
        echo "<p style='color: red;'>❌ Nessun file copiato nel backup</p>";
        return false;
    }
}

/**
 * Verifica lo stato del sito dopo la disattivazione
 */
function fp_verify_site_status() {
    echo "<h2>🔍 Verifica Stato Sito</h2>";
    
    // Test di base
    $tests = [
        'WordPress caricato' => defined('ABSPATH'),
        'Database connesso' => isset($GLOBALS['wpdb']),
        'Funzioni WordPress' => function_exists('get_option'),
        'Admin disponibile' => is_admin(),
        'Frontend disponibile' => !is_admin()
    ];
    
    $passed = 0;
    $total = count($tests);
    
    foreach ($tests as $test_name => $result) {
        $status = $result ? '✅' : '❌';
        $color = $result ? 'green' : 'red';
        echo "<p style='color: $color;'>$status $test_name</p>";
        if ($result) $passed++;
    }
    
    echo "<hr>";
    echo "<p><strong>Risultato:</strong> $passed/$total test superati</p>";
    
    if ($passed === $total) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Sito WordPress funzionante!</p>";
    } else {
        echo "<p style='color: red; font-weight: bold;'>⚠️ Alcuni problemi rilevati</p>";
    }
}

// Esegui le operazioni
echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px;'>";

// 1. Crea backup
fp_create_plugin_backup();

echo "<hr>";

// 2. Pulisci file di debug
fp_cleanup_debug_files();

echo "<hr>";

// 3. Disattiva plugin
$deactivated = fp_emergency_deactivate_plugin();

echo "<hr>";

// 4. Verifica stato
fp_verify_site_status();

echo "</div>";

// Istruzioni finali
echo "<h2>📋 Prossimi Passi</h2>";
echo "<div style='background: #d1ecf1; padding: 15px; margin: 20px 0; border: 1px solid #bee5eb; border-radius: 5px;'>";

if ($deactivated) {
    echo "<h3>✅ Plugin Disattivato con Successo</h3>";
    echo "<ol>";
    echo "<li><strong>Verifica il tuo sito:</strong> Visita la homepage e le pagine principali per assicurarti che tutto funzioni</li>";
    echo "<li><strong>Accedi all'admin:</strong> Vai su /wp-admin per verificare che l'area amministrativa funzioni</li>";
    echo "<li><strong>Riattiva gradualmente:</strong> Se tutto funziona, puoi riattivare il plugin e testare le funzionalità una alla volta</li>";
    echo "</ol>";
} else {
    echo "<h3>⚠️ Disattivazione Manuale Richiesta</h3>";
    echo "<ol>";
    echo "<li><strong>Vai su Plugins:</strong> Accedi a /wp-admin/plugins.php</li>";
    echo "<li><strong>Disattiva il plugin:</strong> Clicca su 'Disattiva' per FP Performance Suite</li>";
    echo "<li><strong>Verifica il sito:</strong> Controlla che le pagine funzionino normalmente</li>";
    echo "</ol>";
}

echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; margin: 20px 0; border: 1px solid #ffeaa7; border-radius: 5px;'>";
echo "<h3>🔧 Se il Problema Persiste</h3>";
echo "<p>Se dopo la disattivazione del plugin le pagine sono ancora vuote:</p>";
echo "<ul>";
echo "<li>Controlla i log degli errori di WordPress</li>";
echo "<li>Verifica che non ci siano altri plugin in conflitto</li>";
echo "<li>Controlla la configurazione del server web</li>";
echo "<li>Contatta il supporto del tuo hosting</li>";
echo "</ul>";
echo "</div>";

echo "<hr>";
echo "<p><small>Script di emergenza eseguito il " . date('Y-m-d H:i:s') . "</small></p>";
?>
