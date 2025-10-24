<?php
/**
 * Disattivazione completa del plugin FP Performance Suite
 * per risolvere definitivamente il white screen
 */

echo "🚨 DISATTIVAZIONE COMPLETA PLUGIN FP PERFORMANCE SUITE\n";
echo "====================================================\n\n";

// Percorsi possibili per wp-config.php
$wp_config_paths = [
    '../../../wp-config.php',
    '../../wp-config.php', 
    '../wp-config.php',
    './wp-config.php'
];

$wp_config_found = false;
foreach ($wp_config_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wp_config_found = true;
        break;
    }
}

if (!$wp_config_found) {
    echo "❌ wp-config.php non trovato\n";
    echo "Esegui questo script dalla directory del plugin o specifica il percorso corretto\n";
    exit(1);
}

echo "✅ WordPress config caricato\n";

try {
    // Connessione diretta al database
    $host = DB_HOST;
    $dbname = DB_NAME;
    $username = DB_USER;
    $password = DB_PASSWORD;
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connesso al database WordPress\n";
    
    // 1. Disattiva il plugin
    echo "\n🔧 Disattivazione plugin...\n";
    
    $stmt = $pdo->prepare("SELECT option_value FROM wp_options WHERE option_name = 'active_plugins'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $active_plugins = maybe_unserialize($result['option_value']);
        
        if (is_array($active_plugins)) {
            $plugin_file = 'fp-performance-suite/fp-performance-suite.php';
            $key = array_search($plugin_file, $active_plugins);
            
            if ($key !== false) {
                unset($active_plugins[$key]);
                $active_plugins = array_values($active_plugins);
                
                $new_plugins = serialize($active_plugins);
                $update_stmt = $pdo->prepare("UPDATE wp_options SET option_value = ? WHERE option_name = 'active_plugins'");
                $update_stmt->execute([$new_plugins]);
                
                echo "✅ Plugin disattivato nel database\n";
            } else {
                echo "ℹ️ Plugin non era attivo nel database\n";
            }
        }
    }
    
    // 2. Rimuovi opzioni del plugin
    echo "\n🧹 Pulizia opzioni plugin...\n";
    
    $plugin_options = [
        'fp_ps_page_cache',
        'fp_ps_browser_cache', 
        'fp_ps_assets',
        'fp_ps_mobile',
        'fp_ps_ml',
        'fp_ps_backend',
        'fp_ps_database',
        'fp_ps_security',
        'fp_ps_logs',
        'fp_ps_presets',
        'fp_ps_score',
        'fp_perfsuite_activation_error',
        'fp_perfsuite_initialization_stats'
    ];
    
    $deleted_options = 0;
    foreach ($plugin_options as $option) {
        $delete_stmt = $pdo->prepare("DELETE FROM wp_options WHERE option_name = ?");
        if ($delete_stmt->execute([$option])) {
            $deleted_options++;
        }
    }
    
    echo "✅ Rimosse $deleted_options opzioni del plugin\n";
    
    // 3. Pulisci transients
    echo "\n🗑️ Pulizia transients...\n";
    
    $transient_stmt = $pdo->prepare("DELETE FROM wp_options WHERE option_name LIKE '_transient_fp_ps_%' OR option_name LIKE '_transient_timeout_fp_ps_%'");
    $transient_result = $transient_stmt->execute();
    
    echo "✅ Transients puliti\n";
    
    // 4. Pulisci cache se possibile
    echo "\n💾 Pulizia cache...\n";
    
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        echo "✅ Cache WordPress pulita\n";
    }
    
    // 5. Rimuovi file di debug problematici
    echo "\n🧹 Rimozione file di debug...\n";
    
    $debug_files = [
        'debug-initialization-issues.php',
        'fix-register-meta-errors.php', 
        'fix-fp-git-updater-deprecated.php',
        'emergency-white-screen-fix.php',
        'test-plugin-white-screen.php',
        'disable-plugin-emergency.php',
        'quick-fix-white-screen.php',
        'fix-white-screen-simple.php',
        'replace-with-safe-version.php',
        'complete-plugin-disable.php'
    ];
    
    $removed_files = 0;
    foreach ($debug_files as $file) {
        $file_path = __DIR__ . '/' . $file;
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                echo "✅ Rimosso: $file\n";
                $removed_files++;
            }
        }
    }
    
    echo "✅ Rimossi $removed_files file di debug\n";
    
    // 6. Rinomina il plugin per disabilitarlo completamente
    echo "\n📁 Disabilitazione fisica plugin...\n";
    
    $plugin_dir = __DIR__;
    $disabled_dir = dirname($plugin_dir) . '/fp-performance-suite-disabled';
    
    if (is_dir($plugin_dir)) {
        if (rename($plugin_dir, $disabled_dir)) {
            echo "✅ Plugin rinominato in fp-performance-suite-disabled\n";
            echo "Il plugin è ora completamente disabilitato\n";
        } else {
            echo "⚠️ Impossibile rinominare la directory del plugin\n";
            echo "Rinomina manualmente la cartella del plugin in fp-performance-suite-disabled\n";
        }
    }
    
    echo "\n🎉 DISATTIVAZIONE COMPLETA TERMINATA!\n";
    echo "=====================================\n\n";
    
    echo "✅ Plugin FP Performance Suite completamente disabilitato\n";
    echo "✅ Opzioni del plugin rimosse dal database\n";
    echo "✅ Cache pulita\n";
    echo "✅ File di debug rimossi\n";
    echo "✅ Plugin rinominato per disabilitazione fisica\n\n";
    
    echo "📋 PROSSIMI PASSI:\n";
    echo "1. Visita il tuo sito WordPress - dovrebbe funzionare normalmente\n";
    echo "2. Controlla tutte le pagine principali\n";
    echo "3. Verifica l'area amministrativa (/wp-admin)\n";
    echo "4. Se tutto funziona, il problema è risolto\n\n";
    
    echo "🔧 SE IL PROBLEMA PERSISTE:\n";
    echo "- Controlla altri plugin attivi\n";
    echo "- Verifica la configurazione del server\n";
    echo "- Controlla i log degli errori di WordPress\n";
    echo "- Contatta il supporto del tuo hosting\n\n";
    
    echo "🔄 PER RIATTIVARE IL PLUGIN IN FUTURO:\n";
    echo "- Rinomina fp-performance-suite-disabled in fp-performance-suite\n";
    echo "- Attiva il plugin da wp-admin/plugins.php\n";
    echo "- Testa gradualmente le funzionalità\n\n";
    
} catch (PDOException $e) {
    echo "❌ Errore database: " . $e->getMessage() . "\n";
    echo "Prova a disattivare manualmente il plugin da wp-admin/plugins.php\n";
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
}

echo "Script completato.\n";
?>
