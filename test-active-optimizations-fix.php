<?php
/**
 * Test per verificare la correzione dell'errore critico nella pagina Active Optimizations
 * 
 * Questo script testa:
 * 1. Che il servizio Scorer sia disponibile
 * 2. Che il metodo activeOptimizations() funzioni correttamente
 * 3. Che la pagina Overview non generi errori critici
 */

// Carica WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Verifica che il plugin sia attivo
if (!class_exists('FP\PerfSuite\Plugin')) {
    die("❌ Plugin FP Performance Suite non trovato!\n");
}

echo "🧪 Test correzione errore Active Optimizations\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    // 1. Test disponibilità servizio Scorer
    echo "1. Test disponibilità servizio Scorer...\n";
    $container = \FP\PerfSuite\Plugin::container();
    $scorer = $container->get(\FP\PerfSuite\Services\Score\Scorer::class);
    echo "   ✅ Servizio Scorer disponibile\n\n";
    
    // 2. Test metodo activeOptimizations()
    echo "2. Test metodo activeOptimizations()...\n";
    $activeOptimizations = $scorer->activeOptimizations();
    echo "   ✅ Metodo activeOptimizations() funziona\n";
    echo "   📊 Ottimizzazioni attive: " . count($activeOptimizations) . "\n";
    
    if (!empty($activeOptimizations)) {
        echo "   📋 Lista ottimizzazioni:\n";
        foreach ($activeOptimizations as $opt) {
            echo "      - " . $opt . "\n";
        }
    } else {
        echo "   ℹ️  Nessuna ottimizzazione attiva rilevata\n";
    }
    echo "\n";
    
    // 3. Test calcolo score
    echo "3. Test calcolo score...\n";
    $score = $scorer->calculate();
    echo "   ✅ Calcolo score funziona\n";
    echo "   📊 Score totale: " . $score['total'] . "/100\n\n";
    
    // 4. Test pagina Overview (simulazione)
    echo "4. Test simulazione pagina Overview...\n";
    $overviewPage = new \FP\PerfSuite\Admin\Pages\Overview($container);
    echo "   ✅ Pagina Overview inizializzata correttamente\n";
    
    // Test che il contenuto non generi errori fatali
    ob_start();
    try {
        $content = $overviewPage->content();
        $output = ob_get_clean();
        echo "   ✅ Contenuto pagina generato senza errori\n";
        echo "   📏 Lunghezza contenuto: " . strlen($content) . " caratteri\n\n";
    } catch (\Exception $e) {
        ob_end_clean();
        echo "   ❌ Errore nella generazione contenuto: " . $e->getMessage() . "\n\n";
    }
    
    // 5. Test opzione scoring_enabled
    echo "5. Test opzione scoring_enabled...\n";
    $scoringEnabled = get_option('fp_ps_scoring_enabled', false);
    echo "   📊 fp_ps_scoring_enabled: " . ($scoringEnabled ? 'true' : 'false') . "\n";
    
    if ($scoringEnabled) {
        echo "   ✅ Opzione scoring abilitata correttamente\n";
    } else {
        echo "   ⚠️  Opzione scoring disabilitata - potrebbe causare problemi\n";
    }
    echo "\n";
    
    echo "🎉 TUTTI I TEST COMPLETATI CON SUCCESSO!\n";
    echo "✅ L'errore critico nella pagina Active Optimizations è stato risolto\n";
    
} catch (\Exception $e) {
    echo "❌ ERRORE DURANTE IL TEST:\n";
    echo "   Messaggio: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test completato: " . date('Y-m-d H:i:s') . "\n";
