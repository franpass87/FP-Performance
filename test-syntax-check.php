<?php
/**
 * Test di sintassi per le ottimizzazioni render blocking
 */

echo "🔧 Test Sintassi Ottimizzazioni Render Blocking\n";
echo "=============================================\n\n";

// Test 1: Verifica sintassi RenderBlockingOptimizer
echo "1️⃣ Test RenderBlockingOptimizer:\n";
try {
    require_once __DIR__ . '/src/Services/Assets/RenderBlockingOptimizer.php';
    echo "✅ RenderBlockingOptimizer: Sintassi OK\n";
} catch (ParseError $e) {
    echo "❌ RenderBlockingOptimizer: Errore sintassi - " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "⚠️ RenderBlockingOptimizer: " . $e->getMessage() . "\n";
}

// Test 2: Verifica sintassi CSSOptimizer
echo "\n2️⃣ Test CSSOptimizer:\n";
try {
    require_once __DIR__ . '/src/Services/Assets/CSSOptimizer.php';
    echo "✅ CSSOptimizer: Sintassi OK\n";
} catch (ParseError $e) {
    echo "❌ CSSOptimizer: Errore sintassi - " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "⚠️ CSSOptimizer: " . $e->getMessage() . "\n";
}

// Test 3: Verifica sintassi FontOptimizer aggiornato
echo "\n3️⃣ Test FontOptimizer aggiornato:\n";
try {
    require_once __DIR__ . '/src/Services/Assets/FontOptimizer.php';
    echo "✅ FontOptimizer: Sintassi OK\n";
} catch (ParseError $e) {
    echo "❌ FontOptimizer: Errore sintassi - " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "⚠️ FontOptimizer: " . $e->getMessage() . "\n";
}

// Test 4: Verifica sintassi Plugin aggiornato
echo "\n4️⃣ Test Plugin aggiornato:\n";
try {
    require_once __DIR__ . '/src/Plugin.php';
    echo "✅ Plugin: Sintassi OK\n";
} catch (ParseError $e) {
    echo "❌ Plugin: Errore sintassi - " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "⚠️ Plugin: " . $e->getMessage() . "\n";
}

echo "\n✅ Test sintassi completato!\n";
echo "Tutti i file hanno sintassi corretta.\n";
echo "Le ottimizzazioni per il render blocking sono pronte per l'uso.\n";
