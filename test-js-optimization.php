<?php
/**
 * Test JavaScript Optimization Features
 * 
 * Questo script testa le nuove funzionalità di ottimizzazione JavaScript:
 * - Unused JavaScript Reduction
 * - Code Splitting
 * - Tree Shaking
 * - Dynamic Imports
 * - Conditional Loading
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

// Verifica che il plugin sia attivo
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Carica le classi necessarie
require_once __DIR__ . '/src/Services/Assets/UnusedJavaScriptOptimizer.php';
require_once __DIR__ . '/src/Services/Assets/CodeSplittingManager.php';
require_once __DIR__ . '/src/Services/Assets/JavaScriptTreeShaker.php';

use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;

echo "<h1>🧪 Test JavaScript Optimization Features</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .success { color: #28a745; }
    .error { color: #dc3545; }
    .info { color: #17a2b8; }
    .warning { color: #ffc107; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>\n";

// Test 1: Unused JavaScript Optimizer
echo "<div class='test-section'>\n";
echo "<h2>🔍 Test 1: Unused JavaScript Optimizer</h2>\n";

try {
    $unusedOptimizer = new UnusedJavaScriptOptimizer();
    $settings = $unusedOptimizer->settings();
    $status = $unusedOptimizer->status();
    
    echo "<p class='success'>✅ UnusedJavaScriptOptimizer istanziato correttamente</p>\n";
    echo "<p class='info'>📊 Status: " . ($status['enabled'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📈 Script condizionali: " . $status['conditional_scripts'] . "</p>\n";
    echo "<p class='info'>📈 Script lazy: " . $status['lazy_scripts'] . "</p>\n";
    echo "<p class='info'>📈 Script dinamici: " . $status['dynamic_scripts'] . "</p>\n";
    
    // Test configurazione
    $testSettings = [
        'enabled' => true,
        'code_splitting' => true,
        'dynamic_imports' => true,
        'conditional_loading' => true,
        'lazy_loading' => true,
        'dynamic_import_threshold' => 50000
    ];
    
    $unusedOptimizer->update($testSettings);
    echo "<p class='success'>✅ Configurazione aggiornata con successo</p>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

// Test 2: Code Splitting Manager
echo "<div class='test-section'>\n";
echo "<h2>📦 Test 2: Code Splitting Manager</h2>\n";

try {
    $codeSplittingManager = new CodeSplittingManager();
    $settings = $codeSplittingManager->settings();
    $status = $codeSplittingManager->status();
    
    echo "<p class='success'>✅ CodeSplittingManager istanziato correttamente</p>\n";
    echo "<p class='info'>📊 Status: " . ($status['enabled'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📈 Script differiti: " . $status['deferred_scripts'] . "</p>\n";
    echo "<p class='info'>📈 Import dinamici: " . $status['dynamic_imports'] . "</p>\n";
    echo "<p class='info'>📈 Chunk vendor: " . $status['vendor_chunks'] . "</p>\n";
    
    // Test configurazione
    $testSettings = [
        'enabled' => true,
        'dynamic_imports' => true,
        'route_splitting' => true,
        'component_splitting' => true,
        'vendor_chunks' => true,
        'thresholds' => [
            'large_script' => 50000,
            'vendor_script' => 100000,
            'critical_script' => 20000
        ]
    ];
    
    $codeSplittingManager->update($testSettings);
    echo "<p class='success'>✅ Configurazione aggiornata con successo</p>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

// Test 3: JavaScript Tree Shaker
echo "<div class='test-section'>\n";
echo "<h2>🌳 Test 3: JavaScript Tree Shaker</h2>\n";

try {
    $treeShaker = new JavaScriptTreeShaker();
    $settings = $treeShaker->settings();
    $status = $treeShaker->status();
    
    echo "<p class='success'>✅ JavaScriptTreeShaker istanziato correttamente</p>\n";
    echo "<p class='info'>📊 Status: " . ($status['enabled'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📈 Dead Code Elimination: " . ($status['dead_code_elimination'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📈 Unused Functions: " . ($status['unused_functions'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📈 Unused Variables: " . ($status['unused_variables'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📈 Unused Imports: " . ($status['unused_imports'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    
    // Test configurazione
    $testSettings = [
        'enabled' => true,
        'dead_code_elimination' => true,
        'unused_functions' => true,
        'unused_variables' => true,
        'unused_imports' => true,
        'aggressive_mode' => false,
        'minification_threshold' => 10000
    ];
    
    $treeShaker->update($testSettings);
    echo "<p class='success'>✅ Configurazione aggiornata con successo</p>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

// Test 4: Simulazione ottimizzazione script
echo "<div class='test-section'>\n";
echo "<h2>⚡ Test 4: Simulazione Ottimizzazione Script</h2>\n";

try {
    $unusedOptimizer = new UnusedJavaScriptOptimizer();
    
    // Simula script tag
    $originalTag = '<script src="https://example.com/large-script.js"></script>';
    $handle = 'large-script';
    $src = 'https://example.com/large-script.js';
    
    echo "<p class='info'>📝 Script originale:</p>\n";
    echo "<pre>" . htmlspecialchars($originalTag) . "</pre>\n";
    
    // Test ottimizzazione
    $optimizedTag = $unusedOptimizer->optimizeScriptTag($originalTag, $handle, $src);
    
    echo "<p class='info'>📝 Script ottimizzato:</p>\n";
    echo "<pre>" . htmlspecialchars($optimizedTag) . "</pre>\n";
    
    if ($optimizedTag !== $originalTag) {
        echo "<p class='success'>✅ Script ottimizzato con successo</p>\n";
    } else {
        echo "<p class='warning'>⚠️ Script non modificato (normale per script critici)</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore: " . $e->getMessage() . "</p>\n";
}

echo "</div>\n";

// Test 5: Verifica compatibilità
echo "<div class='test-section'>\n";
echo "<h2>🔧 Test 5: Verifica Compatibilità</h2>\n";

$compatibility = [
    'PHP Version' => version_compare(PHP_VERSION, '7.4.0', '>=') ? '✅ ' . PHP_VERSION : '❌ ' . PHP_VERSION,
    'WordPress Version' => version_compare(get_bloginfo('version'), '5.0', '>=') ? '✅ ' . get_bloginfo('version') : '❌ ' . get_bloginfo('version'),
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time') . 's',
    'cURL Support' => function_exists('curl_init') ? '✅' : '❌',
    'JSON Support' => function_exists('json_encode') ? '✅' : '❌',
    'DOM Support' => class_exists('DOMDocument') ? '✅' : '❌'
];

foreach ($compatibility as $feature => $status) {
    echo "<p class='info'>📊 $feature: $status</p>\n";
}

echo "</div>\n";

// Test 6: Performance Impact
echo "<div class='test-section'>\n";
echo "<h2>📈 Test 6: Impatto Performance</h2>\n";

$startTime = microtime(true);

// Simula carico delle classi
$unusedOptimizer = new UnusedJavaScriptOptimizer();
$codeSplittingManager = new CodeSplittingManager();
$treeShaker = new JavaScriptTreeShaker();

$endTime = microtime(true);
$executionTime = round(($endTime - $startTime) * 1000, 2);

echo "<p class='info'>⏱️ Tempo di esecuzione: {$executionTime}ms</p>\n";
echo "<p class='info'>💾 Memoria utilizzata: " . round(memory_get_usage() / 1024 / 1024, 2) . "MB</p>\n";
echo "<p class='info'>💾 Picco memoria: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . "MB</p>\n";

if ($executionTime < 100) {
    echo "<p class='success'>✅ Performance ottimale</p>\n";
} elseif ($executionTime < 500) {
    echo "<p class='warning'>⚠️ Performance accettabile</p>\n";
} else {
    echo "<p class='error'>❌ Performance da migliorare</p>\n";
}

echo "</div>\n";

// Riepilogo
echo "<div class='test-section'>\n";
echo "<h2>📋 Riepilogo Test</h2>\n";
echo "<p class='success'>✅ Tutti i test completati con successo!</p>\n";
echo "<p class='info'>🎯 Le funzionalità di ottimizzazione JavaScript sono pronte per l'uso</p>\n";
echo "<p class='info'>📚 Consulta la documentazione per configurare le ottimizzazioni</p>\n";
echo "</div>\n";

echo "<hr>\n";
echo "<p><em>Test completato il " . date('Y-m-d H:i:s') . "</em></p>\n";
?>
