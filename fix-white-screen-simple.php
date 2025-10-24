<?php
/**
 * Fix semplice per white screen - Disattiva plugin
 */

echo "🔧 Fix White Screen - FP Performance Suite\n";
echo "==========================================\n\n";

// Verifica se siamo in WordPress
if (!defined('ABSPATH')) {
    echo "❌ WordPress non caricato. Questo script deve essere eseguito in WordPress.\n";
    echo "Istruzioni:\n";
    echo "1. Accedi al tuo sito WordPress\n";
    echo "2. Vai su /wp-admin/plugins.php\n";
    echo "3. Disattiva il plugin 'FP Performance Suite'\n";
    echo "4. Verifica che le pagine funzionino normalmente\n\n";
    echo "Se non riesci ad accedere all'admin:\n";
    echo "1. Accedi via FTP al tuo sito\n";
    echo "2. Vai in /wp-content/plugins/\n";
    echo "3. Rinomina la cartella 'fp-performance-suite' in 'fp-performance-suite-disabled'\n";
    echo "4. Questo disattiverà il plugin\n\n";
    exit;
}

// Se siamo in WordPress, procedi con la disattivazione
echo "✅ WordPress caricato correttamente\n";

// Verifica se il plugin è attivo
$plugin_file = 'fp-performance-suite/fp-performance-suite.php';

if (is_plugin_active($plugin_file)) {
    echo "⚠️ Plugin FP Performance Suite è attivo\n";
    echo "Disattivazione in corso...\n";
    
    // Disattiva il plugin
    deactivate_plugins($plugin_file);
    
    // Verifica se è stato disattivato
    if (!is_plugin_active($plugin_file)) {
        echo "✅ Plugin disattivato con successo!\n";
        echo "Il tuo sito WordPress dovrebbe ora funzionare normalmente.\n";
        
        // Pulisci cache se possibile
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            echo "✅ Cache pulita\n";
        }
        
    } else {
        echo "❌ Impossibile disattivare il plugin automaticamente\n";
        echo "Disattiva manualmente da wp-admin/plugins.php\n";
    }
} else {
    echo "ℹ️ Plugin FP Performance Suite non è attivo\n";
}

echo "\n📋 Prossimi passi:\n";
echo "1. Visita la homepage del tuo sito per verificare che funzioni\n";
echo "2. Controlla le pagine principali\n";
echo "3. Se tutto funziona, puoi riattivare il plugin gradualmente\n";
echo "4. Testa le funzionalità una alla volta\n\n";

echo "🔧 Se il problema persiste:\n";
echo "- Controlla i log degli errori di WordPress\n";
echo "- Verifica altri plugin in conflitto\n";
echo "- Contatta il supporto del tuo hosting\n\n";

echo "Script completato.\n";
?>
