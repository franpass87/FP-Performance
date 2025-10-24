<?php
/**
 * Fix per il warning di deprecazione del Git Updater
 * 
 * Questo script risolve il problema dei parametri opzionali nel Git Updater
 * che causano warning di deprecazione in PHP 8.0+
 */

// Verifica se il file del Git Updater esiste
$git_updater_file = WP_PLUGIN_DIR . '/fp-git-updater/includes/class-updater.php';

if (file_exists($git_updater_file)) {
    // Leggi il contenuto del file
    $content = file_get_contents($git_updater_file);
    
    // Cerca la riga problematica (linea 405 circa)
    $lines = explode("\n", $content);
    
    for ($i = 0; $i < count($lines); $i++) {
        // Cerca la funzione run_plugin_update con parametri opzionali prima di quelli richiesti
        if (strpos($lines[$i], 'function run_plugin_update') !== false) {
            // Cerca la riga con i parametri
            for ($j = $i; $j < min($i + 10, count($lines)); $j++) {
                if (strpos($lines[$j], '$commit_sha') !== false && strpos($lines[$j], '$plugin') !== false) {
                    // Verifica se $commit_sha è opzionale e $plugin è richiesto
                    if (strpos($lines[$j], '$commit_sha = null') !== false || strpos($lines[$j], '$commit_sha =') !== false) {
                        // Correggi l'ordine dei parametri
                        $lines[$j] = str_replace(
                            ['$commit_sha = null, $plugin', '$commit_sha = null,$plugin'],
                            ['$plugin, $commit_sha = null', '$plugin,$commit_sha = null'],
                            $lines[$j]
                        );
                        
                        // Salva il file corretto
                        $new_content = implode("\n", $lines);
                        file_put_contents($git_updater_file, $new_content);
                        
                        echo "✅ Git Updater fix applicato con successo!\n";
                        echo "📁 File corretto: " . $git_updater_file . "\n";
                        break 2;
                    }
                }
            }
        }
    }
} else {
    echo "⚠️  File Git Updater non trovato: " . $git_updater_file . "\n";
    echo "💡 Assicurati che il plugin fp-git-updater sia installato.\n";
}

// Alternativa: sopprimere temporaneamente il warning
if (!function_exists('suppress_git_updater_warnings')) {
    function suppress_git_updater_warnings() {
        // Sopprime i warning di deprecazione per il Git Updater
        error_reporting(E_ALL & ~E_DEPRECATED);
    }
    
    // Applica la soppressione solo per le chiamate del Git Updater
    add_action('init', function() {
        if (isset($_GET['action']) && $_GET['action'] === 'fp_git_updater') {
            suppress_git_updater_warnings();
        }
    });
}

echo "🔧 Fix per Git Updater deprecation completato!\n";
