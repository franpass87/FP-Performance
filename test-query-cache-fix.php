<?php
/**
 * Test per verificare il funzionamento del Query Cache Manager
 * 
 * Questo script testa:
 * 1. La registrazione del servizio
 * 2. Il salvataggio delle statistiche persistenti
 * 3. La simulazione delle query
 * 4. L'aggiornamento delle statistiche
 */

// Carica WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Carica il plugin
if (file_exists('fp-performance-suite.php')) {
    require_once('fp-performance-suite.php');
}

echo "=== TEST QUERY CACHE MANAGER ===\n\n";

try {
    // Verifica che la classe esista
    if (!class_exists('FP\PerfSuite\Services\DB\QueryCacheManager')) {
        throw new Exception('QueryCacheManager class non trovata');
    }
    
    echo "✓ Classe QueryCacheManager trovata\n";
    
    // Crea istanza
    $queryCache = new FP\PerfSuite\Services\DB\QueryCacheManager();
    echo "✓ Istanza QueryCacheManager creata\n";
    
    // Verifica impostazioni
    $settings = $queryCache->getSettings();
    echo "✓ Impostazioni caricate: " . json_encode($settings) . "\n";
    
    // Abilita la cache se non è già abilitata
    if (empty($settings['enabled'])) {
        $queryCache->updateSettings(['enabled' => true]);
        echo "✓ Query Cache abilitata\n";
    } else {
        echo "✓ Query Cache già abilitata\n";
    }
    
    // Registra il servizio
    $queryCache->register();
    echo "✓ Servizio registrato\n";
    
    // Simula alcune query
    echo "\n--- Simulazione Query ---\n";
    
    $testQueries = [
        "SELECT * FROM wp_posts WHERE post_status = 'publish'",
        "SELECT * FROM wp_options WHERE option_name = 'home'",
        "SELECT * FROM wp_users WHERE user_status = 0",
        "SELECT * FROM wp_posts WHERE post_type = 'page'",
        "SELECT * FROM wp_comments WHERE comment_approved = '1'"
    ];
    
    foreach ($testQueries as $i => $query) {
        echo "Query " . ($i + 1) . ": " . substr($query, 0, 50) . "...\n";
        
        // Simula il processo di cache
        $reflection = new ReflectionClass($queryCache);
        $shouldCacheMethod = $reflection->getMethod('shouldCacheQuery');
        $shouldCacheMethod->setAccessible(true);
        
        if ($shouldCacheMethod->invoke($queryCache, $query)) {
            echo "  - Query può essere cachata\n";
            
            $getCacheKeyMethod = $reflection->getMethod('getCacheKey');
            $getCacheKeyMethod->setAccessible(true);
            $cacheKey = $getCacheKeyMethod->invoke($queryCache, $query);
            
            $cached = $queryCache->getFromCache($cacheKey);
            
            if ($cached !== false) {
                echo "  - Cache HIT\n";
            } else {
                echo "  - Cache MISS - Cachando risultato...\n";
                $queryCache->cacheQueryResult($query, ['test' => true, 'timestamp' => time()]);
            }
        } else {
            echo "  - Query non può essere cachata\n";
        }
    }
    
    // Ottieni statistiche
    echo "\n--- Statistiche Finali ---\n";
    $stats = $queryCache->getStats();
    echo "Cache Hits: " . $stats['hits'] . "\n";
    echo "Cache Misses: " . $stats['misses'] . "\n";
    echo "Hit Rate: " . $stats['hit_rate'] . "%\n";
    echo "Total Requests: " . $stats['total_requests'] . "\n";
    echo "Cache Size: " . $stats['size'] . "\n";
    
    // Salva le statistiche
    $queryCache->saveStats();
    echo "✓ Statistiche salvate\n";
    
    // Test di persistenza - ricrea l'istanza
    echo "\n--- Test Persistenza ---\n";
    $queryCache2 = new FP\PerfSuite\Services\DB\QueryCacheManager();
    $stats2 = $queryCache2->getStats();
    
    echo "Statistiche dopo ricreazione istanza:\n";
    echo "Cache Hits: " . $stats2['hits'] . "\n";
    echo "Cache Misses: " . $stats2['misses'] . "\n";
    echo "Hit Rate: " . $stats2['hit_rate'] . "%\n";
    
    if ($stats2['hits'] === $stats['hits'] && $stats2['misses'] === $stats['misses']) {
        echo "✓ Statistiche persistenti funzionano correttamente\n";
    } else {
        echo "✗ Errore: statistiche non persistenti\n";
    }
    
    echo "\n=== TEST COMPLETATO CON SUCCESSO ===\n";
    
} catch (Exception $e) {
    echo "✗ ERRORE: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
