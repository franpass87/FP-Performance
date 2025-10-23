<?php
/**
 * Test script per verificare il fix del salvataggio nella pagina Assets
 * 
 * Questo script testa che:
 * 1. Non ci siano più redirect problematici
 * 2. I metodi del SmartExclusionDetector esistano
 * 3. Il salvataggio funzioni correttamente
 */

// Simula l'ambiente WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Includi le classi necessarie
require_once 'src/Services/Intelligence/SmartExclusionDetector.php';
require_once 'src/Admin/Pages/Assets/Handlers/PostHandler.php';

use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;
use FP\PerfSuite\Admin\Pages\Assets\Handlers\PostHandler;

echo "🧪 Test Fix Salvataggio Pagina Assets\n";
echo "=====================================\n\n";

// Test 1: Verifica che i metodi esistano
echo "1. ✅ Verifica metodi SmartExclusionDetector...\n";
$detector = new SmartExclusionDetector();

$methods = ['detectExcludeJs', 'detectExcludeCss', 'detectCriticalScripts'];
foreach ($methods as $method) {
    if (method_exists($detector, $method)) {
        echo "   ✅ Metodo '$method' esiste\n";
    } else {
        echo "   ❌ Metodo '$method' NON esiste\n";
    }
}

// Test 2: Verifica che i metodi problematici non esistano
echo "\n2. ✅ Verifica che i metodi problematici non esistano...\n";
$problematicMethods = ['autoDetectExcludeJs', 'autoDetectExcludeCss'];
foreach ($problematicMethods as $method) {
    if (!method_exists($detector, $method)) {
        echo "   ✅ Metodo problematico '$method' NON esiste (corretto)\n";
    } else {
        echo "   ❌ Metodo problematico '$method' esiste ancora (problema)\n";
    }
}

// Test 3: Verifica che non ci siano redirect nel file Assets.php
echo "\n3. ✅ Verifica assenza redirect nel file Assets.php...\n";
$assetsFile = 'src/Admin/Pages/Assets.php';
if (file_exists($assetsFile)) {
    $content = file_get_contents($assetsFile);
    $redirectPatterns = ['wp_safe_redirect', 'wp_redirect', 'exit;'];
    
    foreach ($redirectPatterns as $pattern) {
        if (strpos($content, $pattern) === false) {
            echo "   ✅ Pattern '$pattern' non trovato (corretto)\n";
        } else {
            echo "   ❌ Pattern '$pattern' trovato (problema)\n";
        }
    }
} else {
    echo "   ❌ File Assets.php non trovato\n";
}

// Test 4: Verifica che non ci siano redirect nel file check-final-zip
echo "\n4. ✅ Verifica assenza redirect nel file check-final-zip...\n";
$assetsFileZip = 'check-final-zip/src/Admin/Pages/Assets.php';
if (file_exists($assetsFileZip)) {
    $content = file_get_contents($assetsFileZip);
    $redirectPatterns = ['wp_safe_redirect', 'wp_redirect', 'exit;'];
    
    foreach ($redirectPatterns as $pattern) {
        if (strpos($content, $pattern) === false) {
            echo "   ✅ Pattern '$pattern' non trovato (corretto)\n";
        } else {
            echo "   ❌ Pattern '$pattern' trovato (problema)\n";
        }
    }
} else {
    echo "   ❌ File Assets.php in check-final-zip non trovato\n";
}

// Test 5: Verifica PostHandler
echo "\n5. ✅ Verifica PostHandler...\n";
$postHandlerFile = 'src/Admin/Pages/Assets/Handlers/PostHandler.php';
if (file_exists($postHandlerFile)) {
    $content = file_get_contents($postHandlerFile);
    
    // Verifica che usi i metodi corretti
    if (strpos($content, 'detectExcludeJs()') !== false) {
        echo "   ✅ Usa il metodo corretto 'detectExcludeJs()'\n";
    } else {
        echo "   ❌ Non usa il metodo corretto 'detectExcludeJs()'\n";
    }
    
    if (strpos($content, 'detectExcludeCss()') !== false) {
        echo "   ✅ Usa il metodo corretto 'detectExcludeCss()'\n";
    } else {
        echo "   ❌ Non usa il metodo corretto 'detectExcludeCss()'\n";
    }
    
    // Verifica che non usi i metodi problematici
    if (strpos($content, 'autoDetectExcludeJs()') === false) {
        echo "   ✅ Non usa il metodo problematico 'autoDetectExcludeJs()'\n";
    } else {
        echo "   ❌ Usa ancora il metodo problematico 'autoDetectExcludeJs()'\n";
    }
    
    if (strpos($content, 'autoDetectExcludeCss()') === false) {
        echo "   ✅ Non usa il metodo problematico 'autoDetectExcludeCss()'\n";
    } else {
        echo "   ❌ Usa ancora il metodo problematico 'autoDetectExcludeCss()'\n";
    }
} else {
    echo "   ❌ File PostHandler.php non trovato\n";
}

echo "\n🎯 Risultato del Test:\n";
echo "=====================\n";
echo "Se tutti i test mostrano ✅, il problema del salvataggio dovrebbe essere risolto.\n";
echo "Ora puoi testare manualmente:\n";
echo "1. Vai nel pannello admin di WordPress\n";
echo "2. Clicca su 'FP Performance → 📦 Assets'\n";
echo "3. Prova a salvare qualsiasi form\n";
echo "4. La pagina dovrebbe rimanere visibile e mostrare il messaggio di successo\n\n";

echo "🔧 Se il problema persiste, verifica che stai usando la versione corretta del plugin.\n";
echo "Assicurati di usare i file dalla cartella 'src/' e non da 'check-final-zip/'.\n";
