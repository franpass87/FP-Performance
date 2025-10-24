<?php
/**
 * Test per verificare che l'overhead del database si aggiorni correttamente dopo l'ottimizzazione
 */

// Simula l'ambiente WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Carica il plugin
require_once __DIR__ . '/fp-performance-suite.php';

use FP\PerfSuite\Services\DB\DatabaseOptimizer;

echo "🔧 Test Database Overhead Fix\n";
echo "============================\n\n";

try {
    // Crea istanza dell'optimizer
    $optimizer = new DatabaseOptimizer();
    
    echo "1. Analisi iniziale del database...\n";
    $initialAnalysis = $optimizer->analyze();
    $initialOverhead = $initialAnalysis['table_analysis']['total_overhead_mb'] ?? 0;
    $initialNeedsOpt = count(array_filter($initialAnalysis['table_analysis']['tables'] ?? [], fn($t) => $t['needs_optimization'] ?? false));
    
    echo "   - Overhead iniziale: {$initialOverhead} MB\n";
    echo "   - Tabelle che necessitano ottimizzazione: {$initialNeedsOpt}\n\n";
    
    if ($initialOverhead > 0) {
        echo "2. Esecuzione ottimizzazione...\n";
        $results = $optimizer->optimizeAllTables();
        $optimizedCount = count($results['optimized'] ?? []);
        echo "   - Tabelle ottimizzate: {$optimizedCount}\n\n";
        
        echo "3. Analisi dopo ottimizzazione...\n";
        $finalAnalysis = $optimizer->analyze();
        $finalOverhead = $finalAnalysis['table_analysis']['total_overhead_mb'] ?? 0;
        $finalNeedsOpt = count(array_filter($finalAnalysis['table_analysis']['tables'] ?? [], fn($t) => $t['needs_optimization'] ?? false));
        
        echo "   - Overhead finale: {$finalOverhead} MB\n";
        echo "   - Tabelle che necessitano ottimizzazione: {$finalNeedsOpt}\n\n";
        
        // Verifica che i valori siano cambiati
        if ($finalOverhead < $initialOverhead) {
            echo "✅ SUCCESS: L'overhead è diminuito da {$initialOverhead} MB a {$finalOverhead} MB\n";
        } elseif ($finalOverhead == 0 && $initialOverhead > 0) {
            echo "✅ SUCCESS: L'overhead è stato completamente eliminato!\n";
        } else {
            echo "⚠️  WARNING: L'overhead non è cambiato significativamente\n";
        }
        
        if ($finalNeedsOpt < $initialNeedsOpt) {
            echo "✅ SUCCESS: Il numero di tabelle che necessitano ottimizzazione è diminuito da {$initialNeedsOpt} a {$finalNeedsOpt}\n";
        } elseif ($finalNeedsOpt == 0 && $initialNeedsOpt > 0) {
            echo "✅ SUCCESS: Tutte le tabelle sono state ottimizzate!\n";
        } else {
            echo "⚠️  WARNING: Il numero di tabelle che necessitano ottimizzazione non è cambiato significativamente\n";
        }
        
    } else {
        echo "ℹ️  INFO: Nessun overhead rilevato, il database è già ottimizzato\n";
    }
    
    echo "\n🎯 Test completato!\n";
    
} catch (Exception $e) {
    echo "❌ ERRORE: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
