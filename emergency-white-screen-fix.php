<?php
/**
 * Script di emergenza per risolvere il white screen di WordPress
 * 
 * Questo script diagnostica e risolve i problemi che causano
 * pagine vuote in tutto WordPress dopo l'ultimo commit.
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    // Se non siamo in WordPress, proviamo a caricarlo
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../wp-load.php',
        '../wp-load.php',
        './wp-load.php',
        dirname(__FILE__) . '/wp-load.php',
        dirname(dirname(__FILE__)) . '/wp-load.php',
        dirname(dirname(dirname(__FILE__))) . '/wp-load.php'
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

// Abilita error reporting per diagnosticare
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

/**
 * Diagnostica completa del sistema
 */
function fp_emergency_diagnose() {
    echo "<h1>🔧 Diagnostica di Emergenza - White Screen Fix</h1>";
    echo "<div style='background: #f0f0f0; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
    
    // 1. Verifica errori PHP
    echo "<h2>1. Verifica Errori PHP</h2>";
    $last_error = error_get_last();
    if ($last_error) {
        echo "<p style='color: red;'><strong>❌ Ultimo errore PHP:</strong> " . esc_html($last_error['message']) . "</p>";
        echo "<p><strong>File:</strong> " . esc_html($last_error['file']) . "</p>";
        echo "<p><strong>Linea:</strong> " . esc_html($last_error['line']) . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Nessun errore PHP rilevato</p>";
    }
    
    // 2. Verifica memoria
    echo "<h2>2. Verifica Memoria</h2>";
    $memory_limit = ini_get('memory_limit');
    $memory_usage = memory_get_usage(true);
    $memory_peak = memory_get_peak_usage(true);
    
    echo "<p><strong>Limite memoria:</strong> " . esc_html($memory_limit) . "</p>";
    echo "<p><strong>Uso attuale:</strong> " . size_format($memory_usage) . "</p>";
    echo "<p><strong>Picco memoria:</strong> " . size_format($memory_peak) . "</p>";
    
    if ($memory_usage > (int)$memory_limit * 0.8 * 1024 * 1024) {
        echo "<p style='color: orange;'>⚠️ Uso memoria elevato</p>";
    } else {
        echo "<p style='color: green;'>✅ Memoria OK</p>";
    }
    
    // 3. Verifica plugin FP Performance Suite
    echo "<h2>3. Verifica Plugin FP Performance Suite</h2>";
    
    $plugin_file = __DIR__ . '/fp-performance-suite.php';
    if (file_exists($plugin_file)) {
        echo "<p style='color: green;'>✅ File plugin principale trovato</p>";
        
        // Verifica sintassi
        $syntax_check = shell_exec('php -l "' . $plugin_file . '" 2>&1');
        if (strpos($syntax_check, 'No syntax errors') !== false) {
            echo "<p style='color: green;'>✅ Sintassi PHP OK</p>";
        } else {
            echo "<p style='color: red;'>❌ Errori di sintassi:</p>";
            echo "<pre>" . esc_html($syntax_check) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ File plugin principale non trovato</p>";
    }
    
    // 4. Verifica classi del plugin
    echo "<h2>4. Verifica Classi Plugin</h2>";
    
    $plugin_class_file = __DIR__ . '/src/Plugin.php';
    if (file_exists($plugin_class_file)) {
        echo "<p style='color: green;'>✅ File Plugin.php trovato</p>";
        
        // Verifica sintassi
        $syntax_check = shell_exec('php -l "' . $plugin_class_file . '" 2>&1');
        if (strpos($syntax_check, 'No syntax errors') !== false) {
            echo "<p style='color: green;'>✅ Sintassi Plugin.php OK</p>";
        } else {
            echo "<p style='color: red;'>❌ Errori di sintassi in Plugin.php:</p>";
            echo "<pre>" . esc_html($syntax_check) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ File Plugin.php non trovato</p>";
    }
    
    // 5. Verifica autoloader
    echo "<h2>5. Verifica Autoloader</h2>";
    
    $autoload_file = __DIR__ . '/vendor/autoload.php';
    if (file_exists($autoload_file)) {
        echo "<p style='color: green;'>✅ Autoloader Composer trovato</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Autoloader Composer non trovato, usando autoloader personalizzato</p>";
    }
    
    // 6. Verifica hook WordPress
    echo "<h2>6. Verifica Hook WordPress</h2>";
    
    if (function_exists('add_action')) {
        echo "<p style='color: green;'>✅ Funzione add_action disponibile</p>";
    } else {
        echo "<p style='color: red;'>❌ Funzione add_action non disponibile</p>";
    }
    
    if (function_exists('add_filter')) {
        echo "<p style='color: green;'>✅ Funzione add_filter disponibile</p>";
    } else {
        echo "<p style='color: red;'>❌ Funzione add_filter non disponibile</p>";
    }
    
    // 7. Verifica database
    echo "<h2>7. Verifica Database</h2>";
    
    global $wpdb;
    if (isset($wpdb) && is_object($wpdb)) {
        echo "<p style='color: green;'>✅ Oggetto wpdb disponibile</p>";
        
        try {
            $result = $wpdb->query('SELECT 1');
            if ($result !== false) {
                echo "<p style='color: green;'>✅ Connessione database OK</p>";
            } else {
                echo "<p style='color: red;'>❌ Problema connessione database</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Errore database: " . esc_html($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Oggetto wpdb non disponibile</p>";
    }
    
    echo "</div>";
}

/**
 * Ripara il plugin disabilitandolo temporaneamente
 */
function fp_emergency_disable_plugin() {
    echo "<h2>🔧 Disabilitazione Temporanea del Plugin</h2>";
    
    // Verifica se il plugin è attivo
    if (is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
        echo "<p style='color: orange;'>⚠️ Plugin attivo, tentativo di disabilitazione...</p>";
        
        // Tenta di disattivare il plugin
        deactivate_plugins('fp-performance-suite/fp-performance-suite.php');
        
        if (!is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
            echo "<p style='color: green;'>✅ Plugin disattivato con successo</p>";
            echo "<p><strong>Ora prova ad accedere al tuo sito WordPress.</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ Impossibile disattivare il plugin automaticamente</p>";
            echo "<p>Disattiva manualmente il plugin dalla pagina Plugins in WordPress admin.</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Plugin già disattivato</p>";
    }
}

/**
 * Ripara il plugin rimuovendo i file problematici
 */
function fp_emergency_clean_plugin() {
    echo "<h2>🧹 Pulizia File Problematici</h2>";
    
    $problematic_files = [
        'debug-initialization-issues.php',
        'fix-register-meta-errors.php',
        'fix-fp-git-updater-deprecated.php'
    ];
    
    foreach ($problematic_files as $file) {
        $file_path = __DIR__ . '/' . $file;
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                echo "<p style='color: green;'>✅ Rimosso: " . esc_html($file) . "</p>";
            } else {
                echo "<p style='color: red;'>❌ Impossibile rimuovere: " . esc_html($file) . "</p>";
            }
        } else {
            echo "<p style='color: gray;'>- File non trovato: " . esc_html($file) . "</p>";
        }
    }
}

/**
 * Ripristina il plugin con una versione pulita
 */
function fp_emergency_restore_plugin() {
    echo "<h2>🔄 Ripristino Plugin</h2>";
    
    // Crea un backup del file principale
    $main_file = __DIR__ . '/fp-performance-suite.php';
    $backup_file = __DIR__ . '/fp-performance-suite.php.backup.' . date('Y-m-d-H-i-s');
    
    if (file_exists($main_file)) {
        if (copy($main_file, $backup_file)) {
            echo "<p style='color: green;'>✅ Backup creato: " . basename($backup_file) . "</p>";
        }
    }
    
    // Crea una versione minimale del plugin
    $minimal_plugin = '<?php
/**
 * Plugin Name: FP Performance Suite (Emergency Mode)
 * Description: Versione di emergenza per risolvere white screen
 * Version: 1.5.1-emergency
 * Author: Francesco Passeri
 */

defined(\'ABSPATH\') || exit;

// Modalità di emergenza - plugin disabilitato
add_action(\'admin_notices\', function() {
    if (current_user_can(\'manage_options\')) {
        echo \'<div class="notice notice-warning"><p><strong>FP Performance Suite:</strong> In modalità di emergenza. Il plugin è stato disabilitato per risolvere problemi di white screen.</p></div>\';
    }
});

// Plugin minimale - nessuna funzionalità attiva
class FP_PerfSuite_Emergency {
    public static function init() {
        // Nessuna inizializzazione per evitare conflitti
    }
}';
    
    if (file_put_contents($main_file, $minimal_plugin)) {
        echo "<p style='color: green;'>✅ Plugin sostituito con versione di emergenza</p>";
    } else {
        echo "<p style='color: red;'>❌ Impossibile sostituire il plugin</p>";
    }
}

// Esegui la diagnostica
fp_emergency_diagnose();

echo "<hr>";

// Mostra opzioni di riparazione
echo "<h1>🛠️ Opzioni di Riparazione</h1>";

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'disable':
            fp_emergency_disable_plugin();
            break;
        case 'clean':
            fp_emergency_clean_plugin();
            break;
        case 'restore':
            fp_emergency_restore_plugin();
            break;
    }
    
    echo "<hr>";
    echo "<p><a href='?' style='background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>← Torna alla Diagnostica</a></p>";
} else {
    echo "<div style='background: #fff3cd; padding: 15px; margin: 20px 0; border: 1px solid #ffeaa7; border-radius: 5px;'>";
    echo "<h3>⚠️ Attenzione</h3>";
    echo "<p>Seleziona un'opzione di riparazione:</p>";
    echo "</div>";
    
    echo "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
    echo "<a href='?action=disable' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>1. Disabilita Plugin</a>";
    echo "<a href='?action=clean' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>2. Pulisci File</a>";
    echo "<a href='?action=restore' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>3. Ripristina Plugin</a>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; margin: 20px 0; border: 1px solid #bee5eb; border-radius: 5px;'>";
    echo "<h3>📋 Istruzioni</h3>";
    echo "<ol>";
    echo "<li><strong>Disabilita Plugin:</strong> Disattiva temporaneamente il plugin per verificare se è la causa del problema</li>";
    echo "<li><strong>Pulisci File:</strong> Rimuove file di debug che potrebbero causare conflitti</li>";
    echo "<li><strong>Ripristina Plugin:</strong> Sostituisce il plugin con una versione minimale di emergenza</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<hr>";
echo "<p><small>Script di emergenza FP Performance Suite - " . date('Y-m-d H:i:s') . "</small></p>";
?>
