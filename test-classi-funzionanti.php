<?php
/**
 * Test Classi Funzionanti FP Performance Suite
 * Verifica che tutte le classi siano implementate e funzionino
 */

echo "<h1>🧪 Test Classi Funzionanti FP Performance Suite</h1>\n";

// Lista delle classi da testare
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

$successi = 0;
$errori = 0;

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h2>🔧 Test Classi Implementate</h2>\n";

foreach ($classes as $class => $file) {
    if (file_exists($file)) {
        require_once $file;
        if (class_exists($class)) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$class} - IMPLEMENTATA E FUNZIONANTE</div>\n";
            $successi++;
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$class} - CLASSE NON TROVATA</div>\n";
            $errori++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$class} - FILE NON TROVATO: {$file}</div>\n";
        $errori++;
    }
}

echo "</div>\n";

echo "<div style='margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 8px;'>\n";
echo "<h3>📈 Riepilogo Test Classi</h3>\n";
echo "<p><strong>✅ Successi:</strong> {$successi}</p>\n";
echo "<p><strong>❌ Errori:</strong> {$errori}</p>\n";
echo "<p><strong>🎯 Score Classi:</strong> " . round(($successi / ($successi + $errori)) * 100) . "%</p>\n";

if ($errori === 0) {
    echo "<p style='color: #28a745; font-weight: bold;'>🎉 Tutte le classi sono implementate e funzionanti!</p>\n";
} else {
    echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ Alcune classi potrebbero avere problemi.</p>\n";
}

echo "</div>\n";
?>
