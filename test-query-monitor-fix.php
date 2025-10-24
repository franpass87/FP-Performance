<?php
/**
 * Test per verificare il funzionamento del DatabaseQueryMonitor
 * 
 * Questo script testa se il sistema di monitoraggio delle query funziona correttamente
 * e se le statistiche vengono salvate e recuperate correttamente.
 */

// Assicurati che WordPress sia caricato
if (!defined('ABSPATH')) {
    require_once('../../../wp-config.php');
}

// Carica il plugin
if (!class_exists('FP\PerfSuite\Services\DB\DatabaseQueryMonitor')) {
    require_once('src/Services/DB/DatabaseQueryMonitor.php');
}

use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;

echo "<h1>Test DatabaseQueryMonitor</h1>\n";

try {
    // Crea un'istanza del monitor
    $monitor = new DatabaseQueryMonitor();
    
    echo "<h2>1. Test Inizializzazione</h2>\n";
    echo "Monitor creato: " . (($monitor instanceof DatabaseQueryMonitor) ? "✅ Sì" : "❌ No") . "<br>\n";
    
    // Registra il servizio
    $monitor->register();
    echo "Servizio registrato: ✅ Sì<br>\n";
    
    echo "<h2>2. Test Statistiche Prima</h2>\n";
    $statsBefore = $monitor->getStatistics();
    echo "Query totali: " . ($statsBefore['total_queries'] ?? 0) . "<br>\n";
    echo "Query lente: " . ($statsBefore['slow_queries'] ?? 0) . "<br>\n";
    echo "Query duplicate: " . ($statsBefore['duplicate_queries'] ?? 0) . "<br>\n";
    echo "Tempo totale: " . ($statsBefore['total_query_time'] ?? 0) . "s<br>\n";
    
    echo "<h2>3. Test Esecuzione Query</h2>\n";
    
    // Esegui alcune query di test
    global $wpdb;
    
    // Query semplice
    $wpdb->get_var("SELECT 1");
    echo "Query 1 eseguita<br>\n";
    
    // Query con JOIN
    $wpdb->get_results("SELECT p.ID, p.post_title, u.display_name FROM {$wpdb->posts} p LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID LIMIT 5");
    echo "Query 2 eseguita<br>\n";
    
    // Query con WHERE
    $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish'");
    echo "Query 3 eseguita<br>\n";
    
    // Query duplicata
    $wpdb->get_var("SELECT 1");
    echo "Query duplicata eseguita<br>\n";
    
    echo "<h2>4. Test Statistiche Dopo</h2>\n";
    $statsAfter = $monitor->getStatistics();
    echo "Query totali: " . ($statsAfter['total_queries'] ?? 0) . "<br>\n";
    echo "Query lente: " . ($statsAfter['slow_queries'] ?? 0) . "<br>\n";
    echo "Query duplicate: " . ($statsAfter['duplicate_queries'] ?? 0) . "<br>\n";
    echo "Tempo totale: " . ($statsAfter['total_query_time'] ?? 0) . "s<br>\n";
    
    echo "<h2>5. Test Persistenza</h2>\n";
    
    // Simula il salvataggio delle statistiche
    $monitor->logStatistics();
    echo "Statistiche salvate: ✅ Sì<br>\n";
    
    // Verifica se le statistiche sono state salvate
    $savedStats = get_option('fp_ps_query_monitor_stats', []);
    echo "Statistiche salvate nel database: " . (!empty($savedStats) ? "✅ Sì" : "❌ No") . "<br>\n";
    
    if (!empty($savedStats)) {
        echo "Query totali salvate: " . ($savedStats['total_queries'] ?? 0) . "<br>\n";
        echo "Query lente salvate: " . ($savedStats['slow_queries'] ?? 0) . "<br>\n";
        echo "Query duplicate salvate: " . ($savedStats['duplicate_queries'] ?? 0) . "<br>\n";
    }
    
    echo "<h2>6. Test Recupero Statistiche</h2>\n";
    
    // Crea una nuova istanza per testare il recupero
    $monitor2 = new DatabaseQueryMonitor();
    $statsRecovered = $monitor2->getStatistics();
    
    echo "Query totali recuperate: " . ($statsRecovered['total_queries'] ?? 0) . "<br>\n";
    echo "Query lente recuperate: " . ($statsRecovered['slow_queries'] ?? 0) . "<br>\n";
    echo "Query duplicate recuperate: " . ($statsRecovered['duplicate_queries'] ?? 0) . "<br>\n";
    
    echo "<h2>7. Risultato Finale</h2>\n";
    
    if (($statsAfter['total_queries'] ?? 0) > 0) {
        echo "✅ Il sistema di monitoraggio delle query funziona correttamente!<br>\n";
        echo "✅ Le statistiche vengono salvate e recuperate correttamente!<br>\n";
    } else {
        echo "❌ Il sistema di monitoraggio delle query non funziona correttamente.<br>\n";
        echo "❌ Le statistiche rimangono a 0.<br>\n";
    }
    
    echo "<h2>8. Debug Informazioni</h2>\n";
    echo "SAVEQUERIES definito: " . (defined('SAVEQUERIES') ? "✅ Sì (" . SAVEQUERIES . ")" : "❌ No") . "<br>\n";
    echo "wpdb->queries disponibili: " . (!empty($wpdb->queries) ? "✅ Sì (" . count($wpdb->queries) . ")" : "❌ No") . "<br>\n";
    echo "wpdb->num_queries: " . ($wpdb->num_queries ?? 'N/A') . "<br>\n";
    
} catch (Exception $e) {
    echo "<h2>❌ Errore durante il test</h2>\n";
    echo "Errore: " . $e->getMessage() . "<br>\n";
    echo "Trace: " . $e->getTraceAsString() . "<br>\n";
}

echo "<h2>9. Test Completato</h2>\n";
echo "Test completato alle " . date('Y-m-d H:i:s') . "<br>\n";
?>
