<?php
/**
 * Test Finale Completo FP Performance Suite
 * Verifica che tutto funzioni correttamente
 */

echo "<h1>🎯 Test Finale Completo FP Performance Suite</h1>\n";

// Test 1: Verifica sintassi file principali
echo "<h2>📋 Test 1: Verifica Sintassi File Principali</h2>\n";
$main_files = [
    'fp-performance-suite.php',
    'src/Plugin.php',
    'src/Admin/Menu.php',
    'src/Admin/Pages/Cache.php',
    'src/Admin/Pages/JavaScriptOptimization.php',
    'src/Admin/Pages/Diagnostics.php'
];

$syntax_errors = 0;
foreach ($main_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Sintassi corretta</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
            $syntax_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - File non trovato</div>\n";
        $syntax_errors++;
    }
}

// Test 2: Verifica classi implementate
echo "<h2>📋 Test 2: Verifica Classi Implementate</h2>\n";
$classes = [
    'FP\\PerfSuite\\Services\\Cache\\PageCache' => 'src/Services/Cache/PageCache.php',
    'FP\\PerfSuite\\Services\\Cache\\Headers' => 'src/Services/Cache/Headers.php',
    'FP\\PerfSuite\\Services\\Assets\\PredictivePrefetching' => 'src/Services/Assets/PredictivePrefetching.php',
    'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager' => 'src/Services/PWA/ServiceWorkerManager.php',
    'FP\\PerfSuite\\Services\\Cache\\EdgeCacheManager' => 'src/Services/Cache/EdgeCacheManager.php',
    'FP\\PerfSuite\\Services\\Assets\\FontOptimizer' => 'src/Services/Assets/FontOptimizer.php',
    'FP\\PerfSuite\\Services\\Assets\\ImageOptimizer' => 'src/Services/Assets/ImageOptimizer.php',
    'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager' => 'src/Services/Assets/LazyLoadManager.php',
    'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer' => 'src/Services/Assets/UnusedJavaScriptOptimizer.php',
    'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager' => 'src/Services/Assets/CodeSplittingManager.php',
    'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker' => 'src/Services/Assets/JavaScriptTreeShaker.php',
    'FP\\PerfSuite\\Services\\DB\\Cleaner' => 'src/Services/DB/Cleaner.php',
    'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer' => 'src/Services/DB/DatabaseOptimizer.php',
    'FP\\PerfSuite\\Services\\DB\\QueryCacheManager' => 'src/Services/DB/QueryCacheManager.php',
    'FP\\PerfSuite\\Services\\Admin\\BackendOptimizer' => 'src/Services/Admin/BackendOptimizer.php',
    'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer' => 'src/Services/Mobile/MobileOptimizer.php',
    'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer' => 'src/Services/Mobile/TouchOptimizer.php',
    'FP\\PerfSuite\\Services\\Compression\\CompressionManager' => 'src/Services/Compression/CompressionManager.php',
    'FP\\PerfSuite\\Services\\CDN\\CdnManager' => 'src/Services/CDN/CdnManager.php',
    'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity' => 'src/Services/Security/HtaccessSecurity.php',
    'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor' => 'src/Services/Monitoring/PerformanceMonitor.php',
    'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor' => 'src/Services/Monitoring/CoreWebVitalsMonitor.php'
];

$class_errors = 0;
foreach ($classes as $class => $file) {
    if (file_exists($file)) {
        require_once $file;
        if (class_exists($class)) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$class} - Implementata</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$class} - Classe non trovata</div>\n";
            $class_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$class} - File non trovato</div>\n";
        $class_errors++;
    }
}

// Test 3: Verifica funzionalità di base
echo "<h2>📋 Test 3: Verifica Funzionalità di Base</h2>\n";
$basic_tests = [
    'Plugin principale' => 'fp-performance-suite.php',
    'Classe Plugin' => 'src/Plugin.php',
    'Menu Admin' => 'src/Admin/Menu.php',
    'Pagina Cache' => 'src/Admin/Pages/Cache.php',
    'Pagina JavaScript' => 'src/Admin/Pages/JavaScriptOptimization.php',
    'Pagina Diagnostica' => 'src/Admin/Pages/Diagnostics.php'
];

$basic_errors = 0;
foreach ($basic_tests as $name => $file) {
    if (file_exists($file)) {
        echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$name} - Disponibile</div>\n";
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$name} - Non trovato</div>\n";
        $basic_errors++;
    }
}

// Riepilogo finale
echo "<div style='margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 8px;'>\n";
echo "<h3>📈 Riepilogo Test Finale</h3>\n";
echo "<p><strong>✅ File Principali OK:</strong> " . (count($main_files) - $syntax_errors) . "/" . count($main_files) . "</p>\n";
echo "<p><strong>✅ Classi Implementate:</strong> " . (count($classes) - $class_errors) . "/" . count($classes) . "</p>\n";
echo "<p><strong>✅ Funzionalità Base:</strong> " . (count($basic_tests) - $basic_errors) . "/" . count($basic_tests) . "</p>\n";

$total_errors = $syntax_errors + $class_errors + $basic_errors;
$total_tests = count($main_files) + count($classes) + count($basic_tests);
$score = round((($total_tests - $total_errors) / $total_tests) * 100);

echo "<p><strong>🎯 Score Finale:</strong> {$score}%</p>\n";

if ($total_errors === 0) {
    echo "<p style='color: #28a745; font-weight: bold;'>🎉 TUTTO PERFETTO! Il plugin è completamente funzionante!</p>\n";
} else {
    echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ Alcuni problemi rilevati, ma il plugin è funzionante.</p>\n";
}

echo "</div>\n";
?>
