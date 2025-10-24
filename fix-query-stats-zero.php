<?php
/**
 * Fix per le statistiche delle query che rimangono sempre a 0
 * 
 * Questo script risolve il problema delle statistiche delle query che rimangono sempre a 0
 * abilitando il monitoraggio delle query e configurando correttamente il sistema.
 */

// Assicurati che WordPress sia caricato
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

echo "<h1>🔧 Fix Statistiche Query a 0</h1>\n";

try {
    echo "<h2>1. Diagnosi del Problema</h2>\n";
    
    // Verifica lo stato attuale
    $dbSettings = get_option('fp_ps_db', []);
    $querySettings = get_option('fp_ps_query_monitor', []);
    
    echo "Servizio database abilitato: " . (!empty($dbSettings['enabled']) ? "✅ Sì" : "❌ No") . "\n";
    echo "Query Monitor abilitato: " . (!empty($querySettings['enabled']) ? "✅ Sì" : "❌ No") . "\n";
    echo "SAVEQUERIES definito: " . (defined('SAVEQUERIES') ? "✅ Sì (" . SAVEQUERIES . ")" : "❌ No") . "\n";
    
    echo "<h2>2. Applicazione Fix</h2>\n";
    
    // Fix 1: Abilita SAVEQUERIES
    if (!defined('SAVEQUERIES')) {
        define('SAVEQUERIES', true);
        echo "✅ SAVEQUERIES abilitato\n";
    } else {
        echo "✅ SAVEQUERIES già abilitato\n";
    }
    
    // Fix 2: Abilita il servizio database
    $dbSettings['enabled'] = true;
    update_option('fp_ps_db', $dbSettings);
    echo "✅ Servizio database abilitato\n";
    
    // Fix 3: Abilita il Query Monitor
    $querySettings['enabled'] = true;
    $querySettings['slow_query_threshold'] = 0.005; // 5ms
    update_option('fp_ps_query_monitor', $querySettings);
    echo "✅ Query Monitor abilitato\n";
    
    // Fix 4: Forza l'abilitazione del logging delle query
    global $wpdb;
    if ($wpdb && !isset($wpdb->queries)) {
        $wpdb->queries = [];
    }
    echo "✅ Logging query forzato\n";
    
    echo "<h2>3. Test del Sistema</h2>\n";
    
    // Test del monitor
    if (class_exists('FP\PerfSuite\Services\DB\DatabaseQueryMonitor')) {
        $monitor = new FP\PerfSuite\Services\DB\DatabaseQueryMonitor();
        $monitor->register();
        echo "✅ Monitor creato e registrato\n";
        
        // Esegui alcune query di test
        $wpdb->get_var("SELECT 1");
        $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} LIMIT 3");
        $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts}");
        echo "✅ Query di test eseguite\n";
        
        // Verifica le statistiche
        $stats = $monitor->getStatistics();
        echo "Query totali: " . ($stats['total_queries'] ?? 0) . "\n";
        echo "Query lente: " . ($stats['slow_queries'] ?? 0) . "\n";
        echo "Query duplicate: " . ($stats['duplicate_queries'] ?? 0) . "\n";
        echo "Tempo totale: " . ($stats['total_query_time'] ?? 0) . "s\n";
        
        // Salva le statistiche
        $monitor->logStatistics();
        echo "✅ Statistiche salvate\n";
        
    } else {
        echo "❌ DatabaseQueryMonitor non disponibile\n";
    }
    
    echo "<h2>4. Verifica Finale</h2>\n";
    
    // Verifica che le statistiche siano state salvate
    $savedStats = get_option('fp_ps_query_monitor_stats', []);
    if (!empty($savedStats)) {
        echo "✅ Statistiche salvate nel database\n";
        echo "Query totali salvate: " . ($savedStats['total_queries'] ?? 0) . "\n";
        echo "Query lente salvate: " . ($savedStats['slow_queries'] ?? 0) . "\n";
        echo "Query duplicate salvate: " . ($savedStats['duplicate_queries'] ?? 0) . "\n";
        echo "Tempo totale salvato: " . ($savedStats['total_query_time'] ?? 0) . "s\n";
    } else {
        echo "❌ Statistiche non salvate nel database\n";
    }
    
    echo "<h2>5. Risultato</h2>\n";
    
    if (($stats['total_queries'] ?? 0) > 0) {
        echo "✅ PROBLEMA RISOLTO! Le statistiche delle query ora funzionano correttamente.\n";
        echo "✅ Le statistiche non rimangono più a 0.\n";
        echo "✅ Il monitoraggio delle query è attivo e funzionante.\n";
    } else {
        echo "❌ Il problema persiste. Le statistiche rimangono a 0.\n";
        echo "❌ Potrebbe essere necessario riavviare il server o ricaricare la pagina.\n";
    }
    
    echo "<h2>6. Informazioni Debug</h2>\n";
    echo "SAVEQUERIES: " . (defined('SAVEQUERIES') ? SAVEQUERIES : 'Non definito') . "\n";
    echo "wpdb->queries: " . (!empty($wpdb->queries) ? count($wpdb->queries) : 'Vuoto') . "\n";
    echo "wpdb->num_queries: " . ($wpdb->num_queries ?? 'N/A') . "\n";
    echo "Servizio database: " . (get_option('fp_ps_db')['enabled'] ?? 'N/A') . "\n";
    echo "Query Monitor: " . (get_option('fp_ps_query_monitor')['enabled'] ?? 'N/A') . "\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Errore durante il fix</h2>\n";
    echo "Errore: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "<h2>7. Fix Completato</h2>\n";
echo "Script completato alle " . date('Y-m-d H:i:s') . "\n";
echo "<p><strong>Nota:</strong> Se le statistiche rimangono ancora a 0, prova a ricaricare la pagina del dashboard o riavviare il server.</p>\n";
?>
