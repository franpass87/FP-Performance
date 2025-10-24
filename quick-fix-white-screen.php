<?php
/**
 * Fix rapido per white screen - Disattiva plugin via database
 */

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
    die("wp-config.php non trovato. Esegui questo script dalla directory del plugin.");
}

// Connessione diretta al database per disattivare il plugin
try {
    $host = DB_HOST;
    $dbname = DB_NAME;
    $username = DB_USER;
    $password = DB_PASSWORD;
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connesso al database WordPress\n";
    
    // Ottieni i plugin attivi
    $stmt = $pdo->prepare("SELECT option_value FROM wp_options WHERE option_name = 'active_plugins'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        $active_plugins = maybe_unserialize($result['option_value']);
        
        if (is_array($active_plugins)) {
            // Rimuovi FP Performance Suite dalla lista
            $plugin_file = 'fp-performance-suite/fp-performance-suite.php';
            $key = array_search($plugin_file, $active_plugins);
            
            if ($key !== false) {
                unset($active_plugins[$key]);
                $active_plugins = array_values($active_plugins); // Re-index array
                
                // Aggiorna il database
                $new_plugins = serialize($active_plugins);
                $update_stmt = $pdo->prepare("UPDATE wp_options SET option_value = ? WHERE option_name = 'active_plugins'");
                $update_stmt->execute([$new_plugins]);
                
                echo "✅ Plugin FP Performance Suite disattivato con successo!\n";
                echo "Il tuo sito WordPress dovrebbe ora funzionare normalmente.\n";
                echo "Puoi riattivare il plugin da wp-admin/plugins.php quando necessario.\n";
            } else {
                echo "ℹ️ Plugin FP Performance Suite non era attivo\n";
            }
        } else {
            echo "⚠️ Nessun plugin attivo trovato\n";
        }
    } else {
        echo "❌ Impossibile leggere i plugin attivi dal database\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Errore database: " . $e->getMessage() . "\n";
    echo "Prova a disattivare manualmente il plugin da wp-admin/plugins.php\n";
}

echo "\nScript completato.\n";
?>
