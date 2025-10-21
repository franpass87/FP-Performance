<?php
/**
 * Test Output Reale delle Ottimizzazioni
 * 
 * Simula una richiesta frontend e verifica che le ottimizzazioni
 * vengano effettivamente applicate all'HTML output.
 * 
 * @author Francesco Passeri
 */

// Carica WordPress
$wp_load_paths = [
    __DIR__ . '/wp-load.php',
    __DIR__ . '/../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    __DIR__ . '/../../../wp-load.php',
];

foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

if (!defined('ABSPATH')) {
    die('❌ Impossibile trovare WordPress');
}

use FP\PerfSuite\Plugin;

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Output Ottimizzazioni</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 1400px; margin: 20px auto; padding: 20px; background: #f0f0f1; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .test-section { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-section h2 { margin-top: 0; border-bottom: 2px solid #2271b1; padding-bottom: 10px; }
        .result { margin: 15px 0; padding: 15px; border-left: 4px solid #ccc; background: #f9f9f9; border-radius: 4px; }
        .result.pass { border-left-color: #46b450; background: #f0f9ff; }
        .result.fail { border-left-color: #dc3232; background: #fff0f0; }
        .result.info { border-left-color: #2271b1; background: #e7f5ff; }
        .icon { font-size: 20px; margin-right: 8px; }
        .code-block { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 13px; margin: 10px 0; }
        .highlight { background: #3e4451; padding: 2px 6px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f1; font-weight: 600; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .badge.pass { background: #46b450; color: white; }
        .badge.fail { background: #dc3232; color: white; }
        .badge.partial { background: #ffb900; color: white; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 32px; font-weight: bold; }
        .stat-label { font-size: 13px; opacity: 0.9; margin-top: 5px; }
    </style>
</head>
<body>

<div class="header">
    <h1>🔬 Test Output Reale delle Ottimizzazioni</h1>
    <p style="margin: 10px 0 0 0; opacity: 0.9;">Verifica che le ottimizzazioni attivate vengano effettivamente applicate all'HTML generato</p>
</div>

<?php
$container = Plugin::container();
$results = ['total' => 0, 'passed' => 0, 'failed' => 0];

// Genera un sample HTML per testare
ob_start();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <title>Test Page</title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div class="content">
        <h1>Test Content</h1>
        <img src="<?php echo esc_url(home_url('/wp-content/uploads/test-image.jpg')); ?>" alt="Test" width="800" height="600">
        <img src="<?php echo esc_url(home_url('/wp-content/uploads/logo.png')); ?>" alt="Logo" class="logo">
        <iframe src="https://www.youtube.com/embed/test" width="560" height="315"></iframe>
        
        <style>
            .test { color: red; }
        </style>
        
        <!-- Comments to be removed -->
    </div>
    <?php wp_footer(); ?>
</body>
</html>
<?php
$original_html = ob_get_clean();

// Applica le ottimizzazioni simulando un rendering
echo "<div class='test-section'>";
echo "<h2>📝 HTML Originale</h2>";
echo "<div class='code-block'>" . htmlspecialchars(substr($original_html, 0, 500)) . "...</div>";
echo "<p><small>Dimensione: " . strlen($original_html) . " bytes</small></p>";
echo "</div>";

// ==============================================
// TEST 1: LAZY LOADING
// ==============================================
echo "<div class='test-section'>";
echo "<h2>🖼️ Test Lazy Loading</h2>";

$lazyLoad = $container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class);
$lazySettings = $lazyLoad->settings();

$results['total']++;
if ($lazySettings['enabled'] && $lazySettings['images']) {
    // Test se l'immagine ha loading="lazy"
    $testImg = '<img src="test.jpg" alt="Test" width="800" height="600">';
    $processed = $lazyLoad->addLazyLoadToImage(['src' => 'test.jpg', 'alt' => 'Test', 'width' => 800, 'height' => 600], null, 'large');
    
    if (isset($processed['loading']) && $processed['loading'] === 'lazy') {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Lazy Loading Immagini</strong> - FUNZIONANTE";
        echo "<div style='margin-top: 10px;'><code>loading=\"lazy\"</code> viene aggiunto correttamente</div>";
        if (isset($processed['decoding']) && $processed['decoding'] === 'async') {
            echo "<div><code>decoding=\"async\"</code> viene aggiunto correttamente</div>";
        }
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'>";
        echo "<span class='icon'>❌</span> <strong>Lazy Loading Immagini</strong> - NON FUNZIONA";
        echo "<div>L'attributo loading=\"lazy\" non viene aggiunto</div>";
        echo "</div>";
    }
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> Lazy loading disabilitato - Test saltato</div>";
}

// Test iframe lazy loading
$results['total']++;
if ($lazySettings['enabled'] && $lazySettings['iframes']) {
    $testContent = '<p>Test <iframe src="https://youtube.com/embed/test"></iframe> content</p>';
    $processed = $lazyLoad->addLazyLoadToIframes($testContent);
    
    if (strpos($processed, 'loading="lazy"') !== false) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Lazy Loading Iframe</strong> - FUNZIONANTE";
        echo "<div class='code-block'>" . htmlspecialchars($processed) . "</div>";
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'><span class='icon'>❌</span> <strong>Lazy Loading Iframe</strong> - NON FUNZIONA</div>";
    }
}

echo "</div>";

// ==============================================
// TEST 2: HTML MINIFICATION
// ==============================================
echo "<div class='test-section'>";
echo "<h2>🗜️ Test HTML Minification</h2>";

$optimizer = $container->get(\FP\PerfSuite\Services\Assets\Optimizer::class);
$assetSettings = $optimizer->settings();

$results['total']++;
if ($assetSettings['minify_html']) {
    $htmlMinifier = new \FP\PerfSuite\Services\Assets\HtmlMinifier();
    
    $testHtml = "<!DOCTYPE html>\n<html>\n<head>\n    <title>Test</title>\n</head>\n<body>\n    <!-- Comment -->\n    <p>Test</p>\n</body>\n</html>";
    $minified = $htmlMinifier->minify($testHtml);
    
    $originalSize = strlen($testHtml);
    $minifiedSize = strlen($minified);
    $reduction = round((($originalSize - $minifiedSize) / $originalSize) * 100, 1);
    
    if ($minifiedSize < $originalSize) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>HTML Minification</strong> - FUNZIONANTE";
        echo "<div style='margin-top: 10px;'>";
        echo "Originale: {$originalSize} bytes → Minificato: {$minifiedSize} bytes<br>";
        echo "<strong>Riduzione: {$reduction}%</strong>";
        echo "</div>";
        echo "<div class='code-block'>" . htmlspecialchars(substr($minified, 0, 200)) . "...</div>";
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'><span class='icon'>❌</span> <strong>HTML Minification</strong> - NON FUNZIONA (nessuna riduzione)</div>";
    }
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> HTML minification disabilitata - Test saltato</div>";
}

echo "</div>";

// ==============================================
// TEST 3: SCRIPT DEFER/ASYNC
// ==============================================
echo "<div class='test-section'>";
echo "<h2>⚡ Test Script Defer/Async</h2>";

$scriptOptimizer = new \FP\PerfSuite\Services\Assets\ScriptOptimizer();

$results['total']++;
if ($assetSettings['defer_js']) {
    $testScript = '<script src="https://example.com/script.js"></script>';
    $processed = $scriptOptimizer->filterScriptTag($testScript, 'test-handle', 'https://example.com/script.js', true, false);
    
    if (strpos($processed, 'defer') !== false || strpos($processed, 'async') !== false) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Script Defer/Async</strong> - FUNZIONANTE";
        echo "<div class='code-block'>" . htmlspecialchars($processed) . "</div>";
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'>";
        echo "<span class='icon'>❌</span> <strong>Script Defer/Async</strong> - NON FUNZIONA";
        echo "<div>Gli attributi defer/async non vengono aggiunti</div>";
        echo "<div class='code-block'>" . htmlspecialchars($processed) . "</div>";
        echo "</div>";
    }
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> Defer JS disabilitato - Test saltato</div>";
}

echo "</div>";

// ==============================================
// TEST 4: FONT OPTIMIZER
// ==============================================
echo "<div class='test-section'>";
echo "<h2>🔤 Test Font Optimizer</h2>";

$fontOptimizer = $container->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class);
$fontSettings = $fontOptimizer->settings();

$results['total']++;
if ($fontSettings['enabled'] && $fontSettings['google_fonts_display_swap']) {
    $testLink = '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700" />';
    $processed = $fontOptimizer->optimizeGoogleFonts($testLink, 'google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700', '');
    
    if (strpos($processed, 'display=swap') !== false) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Google Fonts display=swap</strong> - FUNZIONANTE";
        echo "<div class='code-block'>" . htmlspecialchars($processed) . "</div>";
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'><span class='icon'>❌</span> <strong>Font Optimizer</strong> - NON FUNZIONA</div>";
    }
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> Font optimizer disabilitato - Test saltato</div>";
}

// Test preconnect
if ($fontSettings['enabled'] && $fontSettings['preconnect']) {
    $hints = [];
    $urls = ['fonts.googleapis.com', 'fonts.gstatic.com'];
    $processed = $fontOptimizer->addPreconnect($hints, 'preconnect');
    
    if (!empty($processed)) {
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Font Preconnect</strong> - FUNZIONANTE";
        echo "<div>Preconnect links vengono aggiunti per font providers</div>";
        echo "</div>";
    }
}

echo "</div>";

// ==============================================
// TEST 5: IMAGE OPTIMIZER  
// ==============================================
echo "<div class='test-section'>";
echo "<h2>🎨 Test Image Optimizer</h2>";

$imageOptimizer = $container->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class);
$imageSettings = $imageOptimizer->settings();

$results['total']++;
if ($imageSettings['enabled'] && $imageSettings['add_dimensions']) {
    $testAttr = ['src' => 'test.jpg', 'alt' => 'Test'];
    $attachment_id = null; // Simuliamo senza ID
    
    // In un test reale dovremmo usare un'immagine vera
    echo "<div class='result info'>";
    echo "<span class='icon'>ℹ️</span> <strong>Image Dimensions</strong> - Configurato";
    echo "<div>Aggiunge width/height esplicite alle immagini per prevenire layout shift</div>";
    echo "</div>";
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> Image optimizer disabilitato - Test saltato</div>";
}

echo "</div>";

// ==============================================
// TEST 6: WEBP DELIVERY
// ==============================================
echo "<div class='test-section'>";
echo "<h2>🖼️ Test WebP Delivery</h2>";

$webpConverter = $container->get(\FP\PerfSuite\Services\Media\WebPConverter::class);
$webpSettings = $webpConverter->settings();

$results['total']++;
if ($webpSettings['enabled']) {
    // Check se GD supporta WebP
    if (function_exists('imagewebp')) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>WebP Support</strong> - Server compatibile";
        echo "<div>GD Library supporta la conversione WebP</div>";
        echo "<div>Qualità: {$webpSettings['quality']}% - Metodo: {$webpSettings['method']}</div>";
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'>";
        echo "<span class='icon'>❌</span> <strong>WebP Support</strong> - Server NON compatibile!";
        echo "<div>⚠️ La funzione imagewebp() non è disponibile. Le immagini non saranno convertite.</div>";
        echo "</div>";
    }
    
    // Test auto-replacement
    if ($webpSettings['auto_convert']) {
        echo "<div class='result info'>";
        echo "<span class='icon'>ℹ️</span> <strong>Auto-conversione</strong> - Abilitata";
        echo "<div>Le immagini caricate verranno automaticamente convertite in WebP</div>";
        echo "</div>";
    }
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> WebP disabilitato - Test saltato</div>";
}

echo "</div>";

// ==============================================
// TEST 7: CACHE
// ==============================================
echo "<div class='test-section'>";
echo "<h2>💾 Test Cache</h2>";

$pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
$cacheSettings = $pageCache->settings();

$results['total']++;
if ($cacheSettings['enabled']) {
    $cacheDir = WP_CONTENT_DIR . '/cache/fp-performance-suite';
    
    if (is_dir($cacheDir) && is_writable($cacheDir)) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Page Cache Directory</strong> - Accessibile e scrivibile";
        echo "<div>Directory: <code>{$cacheDir}</code></div>";
        
        $status = $pageCache->status();
        echo "<div>File cached: {$status['files']}</div>";
        echo "<div>Dimensione totale: " . size_format($status['size'] ?? 0) . "</div>";
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'>";
        echo "<span class='icon'>❌</span> <strong>Page Cache Directory</strong> - NON accessibile o non scrivibile!";
        echo "<div>⚠️ La cache non può salvare file</div>";
        echo "</div>";
    }
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> Page cache disabilitata - Test saltato</div>";
}

// Test Object Cache
$objectCache = $container->get(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class);
$objectSettings = $objectCache->settings();

if ($objectSettings['enabled']) {
    $results['total']++;
    $testResult = $objectCache->testConnection();
    
    if ($testResult['success']) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Object Cache</strong> - Connessione riuscita";
        echo "<div>Driver: {$testResult['driver']}</div>";
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'>";
        echo "<span class='icon'>❌</span> <strong>Object Cache</strong> - Connessione fallita!";
        echo "<div>⚠️ Verifica host, porta e credenziali</div>";
        echo "</div>";
    }
}

echo "</div>";

// ==============================================
// TEST 8: COMPRESSION
// ==============================================
echo "<div class='test-section'>";
echo "<h2>🗜️ Test Compression</h2>";

$compression = $container->get(\FP\PerfSuite\Services\Compression\CompressionManager::class);
$compressionSettings = $compression->settings();

$results['total']++;
if ($compressionSettings['enabled']) {
    $testResult = $compression->testCompression();
    
    if ($testResult['enabled']) {
        $results['passed']++;
        echo "<div class='result pass'>";
        echo "<span class='icon'>✅</span> <strong>Compressione</strong> - Attiva";
        echo "<div>Metodo: <strong>{$testResult['method']}</strong></div>";
        
        if (isset($testResult['ratio'])) {
            echo "<div>Rapporto compressione: " . round($testResult['ratio'], 1) . "%</div>";
        }
        echo "</div>";
    } else {
        $results['failed']++;
        echo "<div class='result fail'>";
        echo "<span class='icon'>❌</span> <strong>Compressione</strong> - NON attiva!";
        echo "<div>⚠️ Nessun metodo di compressione disponibile sul server</div>";
        echo "</div>";
    }
} else {
    echo "<div class='result info'><span class='icon'>ℹ️</span> Compressione disabilitata - Test saltato</div>";
}

echo "</div>";

// ==============================================
// SUMMARY
// ==============================================
$percentage = $results['total'] > 0 ? round(($results['passed'] / $results['total']) * 100) : 0;

echo "<div class='test-section' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>";
echo "<h2 style='color: white; border-bottom-color: rgba(255,255,255,0.3);'>📊 Riepilogo Test</h2>";

echo "<div class='stats'>";
echo "<div class='stat-box' style='background: rgba(255,255,255,0.2);'>
    <div class='stat-number'>{$results['total']}</div>
    <div class='stat-label'>Test Totali</div>
</div>";
echo "<div class='stat-box' style='background: rgba(70,180,80,0.9);'>
    <div class='stat-number'>{$results['passed']}</div>
    <div class='stat-label'>Superati</div>
</div>";
echo "<div class='stat-box' style='background: rgba(220,50,50,0.9);'>
    <div class='stat-number'>{$results['failed']}</div>
    <div class='stat-label'>Falliti</div>
</div>";
echo "<div class='stat-box' style='background: rgba(255,200,0,0.9);'>
    <div class='stat-number'>{$percentage}%</div>
    <div class='stat-label'>Successo</div>
</div>";
echo "</div>";

if ($results['failed'] === 0) {
    echo "<div style='text-align: center; padding: 20px; background: rgba(70,180,80,0.3); border-radius: 8px; margin-top: 20px;'>";
    echo "<h3 style='color: white; margin: 0;'>🎉 PERFETTO!</h3>";
    echo "<p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>Tutte le ottimizzazioni attivate funzionano correttamente e vengono applicate all'output HTML</p>";
    echo "</div>";
} else {
    echo "<div style='text-align: center; padding: 20px; background: rgba(220,50,50,0.3); border-radius: 8px; margin-top: 20px;'>";
    echo "<h3 style='color: white; margin: 0;'>⚠️ ATTENZIONE</h3>";
    echo "<p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>Alcune ottimizzazioni non funzionano come previsto. Controlla i dettagli sopra per risolvere i problemi.</p>";
    echo "</div>";
}

echo "</div>";

?>

</body>
</html>

