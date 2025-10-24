<?php
/**
 * Disattivazione diretta del plugin via database
 */

echo "🚨 DISATTIVAZIONE DIRETTA PLUGIN FP PERFORMANCE SUITE\n";
echo "==================================================\n\n";

// Configurazione database (modifica questi valori se necessario)
$db_config = [
    'host' => 'localhost',
    'name' => 'wordpress',  // Modifica con il nome del tuo database
    'user' => 'root',       // Modifica con il tuo username
    'pass' => ''            // Modifica con la tua password
];

echo "🔧 Tentativo connessione database...\n";

try {
    $pdo = new PDO("mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8mb4", 
                   $db_config['user'], $db_config['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connesso al database\n";
    
    // 1. Disattiva il plugin
    echo "\n🔧 Disattivazione plugin...\n";
    
    $stmt = $pdo->prepare("SELECT option_value FROM wp_options WHERE option_name = 'active_plugins'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $active_plugins = unserialize($result['option_value']);
        
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
    
    echo "\n🎉 DISATTIVAZIONE COMPLETA TERMINATA!\n";
    echo "=====================================\n\n";
    
    echo "✅ Plugin FP Performance Suite completamente disabilitato\n";
    echo "✅ Opzioni del plugin rimosse dal database\n";
    echo "✅ Cache pulita\n\n";
    
    echo "📋 PROSSIMI PASSI:\n";
    echo "1. Visita il tuo sito WordPress - dovrebbe funzionare normalmente\n";
    echo "2. Controlla tutte le pagine principali\n";
    echo "3. Verifica l'area amministrativa (/wp-admin)\n";
    echo "4. Se tutto funziona, il problema è risolto\n\n";
    
} catch (PDOException $e) {
    echo "❌ Errore database: " . $e->getMessage() . "\n";
    echo "\n🔧 SOLUZIONI ALTERNATIVE:\n";
    echo "1. Accedi al tuo database via phpMyAdmin\n";
    echo "2. Vai nella tabella wp_options\n";
    echo "3. Trova la riga con option_name = 'active_plugins'\n";
    echo "4. Rimuovi 'fp-performance-suite/fp-performance-suite.php' dalla lista\n";
    echo "5. Salva le modifiche\n\n";
    
    echo "OPPURE:\n";
    echo "1. Accedi a wp-admin/plugins.php\n";
    echo "2. Disattiva il plugin FP Performance Suite\n";
    echo "3. Se non riesci ad accedere, rinomina la cartella del plugin\n\n";
}

echo "Script completato.\n";
?>
