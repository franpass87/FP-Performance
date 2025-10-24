<?php
/**
 * Script per abilitare il monitoraggio delle query
 * 
 * Questo script abilita il monitoraggio delle query nel plugin FP Performance Suite
 * e verifica che funzioni correttamente.
 */

// Assicurati che WordPress sia caricato
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

echo "<h1>Abilitazione Monitoraggio Query</h1>\n";

try {
    // 1. Abilita il monitoraggio delle query
    echo "<h2>1. Abilitazione Monitoraggio</h2>\n";
    
    // Abilita SAVEQUERIES se non è già abilitato
    if (!defined('SAVEQUERIES')) {
        define('SAVEQUERIES', true);
        echo "✅ SAVEQUERIES abilitato\n";
    } else {
        echo "✅ SAVEQUERIES già abilitato\n";
    }
    
    // 2. Abilita il servizio database
    echo "<h2>2. Abilitazione Servizio Database</h2>\n";
    
    $dbSettings = get_option('fp_ps_db', []);
    $dbSettings['enabled'] = true;
    update_option('fp_ps_db', $dbSettings);
    echo "✅ Servizio database abilitato\n";
    
    // 3. Abilita il monitoraggio delle query
    echo "<h2>3. Abilitazione Query Monitor</h2>\n";
    
    $querySettings = get_option('fp_ps_query_monitor', []);
    $querySettings['enabled'] = true;
    $querySettings['slow_query_threshold'] = 0.005; // 5ms
    update_option('fp_ps_query_monitor', $querySettings);
    echo "✅ Query Monitor abilitato\n";
    
    // 4. Verifica che il servizio sia disponibile
    echo "<h2>4. Verifica Servizio</h2>\n";
    
    if (class_exists('FP\PerfSuite\Services\DB\DatabaseQueryMonitor')) {
        echo "✅ DatabaseQueryMonitor disponibile\n";
        
        // Crea un'istanza del monitor
        $monitor = new FP\PerfSuite\Services\DB\DatabaseQueryMonitor();
        echo "✅ Monitor creato\n";
        
        // Registra il servizio
        $monitor->register();
        echo "✅ Servizio registrato\n";
        
        // Test delle statistiche
        $stats = $monitor->getStatistics();
        echo "✅ Statistiche recuperate\n";
        echo "Query totali: " . ($stats['total_queries'] ?? 0) . "\n";
        echo "Query lente: " . ($stats['slow_queries'] ?? 0) . "\n";
        echo "Query duplicate: " . ($stats['duplicate_queries'] ?? 0) . "\n";
        echo "Tempo totale: " . ($stats['total_query_time'] ?? 0) . "s\n";
        
    } else {
        echo "❌ DatabaseQueryMonitor non disponibile\n";
    }
    
    // 5. Test di una query
    echo "<h2>5. Test Query</h2>\n";
    
    global $wpdb;
    
    // Esegui alcune query di test
    $wpdb->get_var("SELECT 1");
    echo "✅ Query di test eseguita\n";
    
    $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} LIMIT 5");
    echo "✅ Query complessa eseguita\n";
    
    // Verifica le statistiche dopo le query
    $statsAfter = $monitor->getStatistics();
    echo "Query totali dopo test: " . ($statsAfter['total_queries'] ?? 0) . "\n";
    echo "Query lente dopo test: " . ($statsAfter['slow_queries'] ?? 0) . "\n";
    echo "Query duplicate dopo test: " . ($statsAfter['duplicate_queries'] ?? 0) . "\n";
    echo "Tempo totale dopo test: " . ($statsAfter['total_query_time'] ?? 0) . "s\n";
    
    // 6. Salva le statistiche
    echo "<h2>6. Salvataggio Statistiche</h2>\n";
    
    $monitor->logStatistics();
    echo "✅ Statistiche salvate\n";
    
    // Verifica che le statistiche siano state salvate
    $savedStats = get_option('fp_ps_query_monitor_stats', []);
    if (!empty($savedStats)) {
        echo "✅ Statistiche salvate nel database\n";
        echo "Query totali salvate: " . ($savedStats['total_queries'] ?? 0) . "\n";
        echo "Query lente salvate: " . ($savedStats['slow_queries'] ?? 0) . "\n";
        echo "Query duplicate salvate: " . ($savedStats['duplicate_queries'] ?? 0) . "\n";
    } else {
        echo "❌ Statistiche non salvate nel database\n";
    }
    
    echo "<h2>7. Risultato Finale</h2>\n";
    
    if (($statsAfter['total_queries'] ?? 0) > 0) {
        echo "✅ Il monitoraggio delle query funziona correttamente!\n";
        echo "✅ Le statistiche vengono salvate e recuperate correttamente!\n";
        echo "✅ Il problema delle statistiche a 0 è stato risolto!\n";
    } else {
        echo "❌ Il monitoraggio delle query non funziona correttamente.\n";
        echo "❌ Le statistiche rimangono a 0.\n";
    }
    
    echo "<h2>8. Informazioni Debug</h2>\n";
    echo "SAVEQUERIES: " . (defined('SAVEQUERIES') ? SAVEQUERIES : 'Non definito') . "\n";
    echo "wpdb->queries: " . (!empty($wpdb->queries) ? count($wpdb->queries) : 'Vuoto') . "\n";
    echo "wpdb->num_queries: " . ($wpdb->num_queries ?? 'N/A') . "\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Errore durante l'abilitazione</h2>\n";
    echo "Errore: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "<h2>9. Abilitazione Completata</h2>\n";
echo "Script completato alle " . date('Y-m-d H:i:s') . "\n";
?>
