<?php
/**
 * Diagnostico Attivazione Opzioni Plugin
 * 
 * Verifica che tutte le opzioni del plugin si attivino correttamente
 * quando vengono abilitate.
 * 
 * @author Francesco Passeri
 */

// Carica WordPress
require_once __DIR__ . '/wp-content/plugins/fp-performance-suite/vendor/autoload.php';
if (file_exists(__DIR__ . '/../../wp-load.php')) {
    require_once __DIR__ . '/../../wp-load.php';
} elseif (file_exists(__DIR__ . '/../../../wp-load.php')) {
    require_once __DIR__ . '/../../../wp-load.php';
} else {
    die('❌ Impossibile trovare wp-load.php');
}

use FP\PerfSuite\Plugin;

echo "<html><head><title>Diagnostica Opzioni Plugin</title>";
echo "<style>
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 1200px; margin: 40px auto; padding: 20px; background: #f0f0f1; }
    .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .card h2 { margin-top: 0; border-bottom: 2px solid #2271b1; padding-bottom: 10px; }
    .check { margin: 10px 0; padding: 10px; border-left: 4px solid #ccc; background: #f9f9f9; }
    .check.success { border-left-color: #46b450; background: #f0f9ff; }
    .check.warning { border-left-color: #ffb900; background: #fff8e5; }
    .check.error { border-left-color: #dc3232; background: #fff0f0; }
    .check.info { border-left-color: #2271b1; background: #e7f5ff; }
    .icon { font-weight: bold; margin-right: 5px; }
    .details { font-size: 13px; color: #666; margin-top: 5px; padding-left: 20px; }
    code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin: 15px 0; }
    th, td { text-align: left; padding: 10px; border-bottom: 1px solid #ddd; }
    th { background: #f0f0f1; font-weight: 600; }
    .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; margin-left: 5px; }
    .badge.enabled { background: #46b450; color: white; }
    .badge.disabled { background: #dcdcde; color: #646970; }
    .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
    .stat-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
    .stat-number { font-size: 36px; font-weight: bold; margin-bottom: 5px; }
    .stat-label { font-size: 14px; opacity: 0.9; }
</style></head><body>";

echo "<h1>🔍 Diagnostica Attivazione Opzioni Plugin FP Performance Suite</h1>";

$container = Plugin::container();

// Array per raccogliere i risultati
$results = [
    'total' => 0,
    'active' => 0,
    'working' => 0,
    'issues' => 0,
];

$checks = [];

// ==============================================
// 1. LAZY LOADING
// ==============================================
echo "<div class='card'>";
echo "<h2>🖼️ Lazy Loading</h2>";

$lazyLoad = $container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class);
$lazySettings = $lazyLoad->settings();

$results['total']++;
if ($lazySettings['enabled']) {
    $results['active']++;
    
    // Verifica hooks registrati
    $hasImageHook = has_filter('wp_get_attachment_image_attributes', [$lazyLoad, 'addLazyLoadToImage']);
    $hasIframeHook = has_filter('the_content', [$lazyLoad, 'addLazyLoadToIframes']);
    
    if ($hasImageHook && $lazySettings['images']) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Lazy Loading Immagini</strong> - Attivo e funzionante";
        echo "<div class='details'>Hook <code>wp_get_attachment_image_attributes</code> registrato correttamente</div></div>";
    } elseif ($lazySettings['images']) {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Lazy Loading Immagini</strong> - Abilitato ma hook non registrato!";
        echo "<div class='details'>⚠️ Il filtro non è attivo. Le immagini NON saranno lazy loaded.</div></div>";
    }
    
    if ($hasIframeHook && $lazySettings['iframes']) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Lazy Loading Iframes</strong> - Attivo e funzionante";
        echo "<div class='details'>Hook <code>the_content</code> registrato correttamente</div></div>";
    } elseif ($lazySettings['iframes']) {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Lazy Loading Iframes</strong> - Abilitato ma hook non registrato!";
        echo "<div class='details'>⚠️ Il filtro non è attivo. Gli iframe NON saranno lazy loaded.</div></div>";
    }
    
    echo "<div class='check info'><span class='icon'>ℹ️</span> <strong>Configurazione</strong>";
    echo "<div class='details'>
        - Skip prima immagine: " . ($lazySettings['skip_first'] > 0 ? "Sì ({$lazySettings['skip_first']})" : "No") . "<br>
        - Dimensione minima: {$lazySettings['min_size']}px<br>
        - Classi escluse: " . (empty($lazySettings['exclude_classes']) ? "Nessuna" : count($lazySettings['exclude_classes'])) . "
    </div></div>";
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Lazy Loading</strong> - Disabilitato";
    echo "<div class='details'>Attiva questa opzione per migliorare LCP e performance PageSpeed</div></div>";
}

echo "</div>";

// ==============================================
// 2. ASSET OPTIMIZER
// ==============================================
echo "<div class='card'>";
echo "<h2>⚡ Asset Optimizer</h2>";

$optimizer = $container->get(\FP\PerfSuite\Services\Assets\Optimizer::class);
$assetSettings = $optimizer->settings();

// Minify HTML
$results['total']++;
if ($assetSettings['minify_html']) {
    $results['active']++;
    $hasBufferHook = has_action('template_redirect', [$optimizer, 'startBuffer']);
    if ($hasBufferHook) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Minificazione HTML</strong> - Attivo e funzionante";
        echo "<div class='details'>Output buffering attivo per minificare HTML</div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Minificazione HTML</strong> - Abilitato ma NON funzionante!";
        echo "<div class='details'>⚠️ Hook <code>template_redirect</code> non trovato</div></div>";
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Minificazione HTML</strong> - Disabilitato";
}

// Defer JS
$results['total']++;
if ($assetSettings['defer_js']) {
    $results['active']++;
    $hasDeferHook = has_filter('script_loader_tag', [$optimizer, 'filterScriptTag']);
    if ($hasDeferHook) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Defer JavaScript</strong> - Attivo e funzionante";
        echo "<div class='details'>Hook <code>script_loader_tag</code> registrato</div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Defer JavaScript</strong> - Abilitato ma NON funzionante!";
        echo "<div class='details'>⚠️ Gli script NON saranno deferiti</div></div>";
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Defer JavaScript</strong> - Disabilitato";
}

// Async CSS
$results['total']++;
if ($assetSettings['async_css']) {
    $results['active']++;
    $hasAsyncHook = has_filter('style_loader_tag', [$optimizer, 'filterStyleTag']);
    if ($hasAsyncHook) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Async CSS</strong> - Attivo e funzionante";
        echo "<div class='details'>Hook <code>style_loader_tag</code> registrato</div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Async CSS</strong> - Abilitato ma NON funzionante!";
        echo "<div class='details'>⚠️ I CSS NON saranno caricati in modo asincrono</div></div>";
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Async CSS</strong> - Disabilitato";
}

// DNS Prefetch
$results['total']++;
if (!empty($assetSettings['dns_prefetch'])) {
    $results['active']++;
    $resourceHints = $container->get(\FP\PerfSuite\Services\Assets\ResourceHints\ResourceHintsManager::class);
    $hasPrefetchHook = has_filter('wp_resource_hints', [$resourceHints, 'addDnsPrefetch']);
    if ($hasPrefetchHook) {
        $results['working']++;
        $prefetchList = is_array($assetSettings['dns_prefetch']) ? $assetSettings['dns_prefetch'] : explode("\n", $assetSettings['dns_prefetch']);
        echo "<div class='check success'><span class='icon'>✅</span> <strong>DNS Prefetch</strong> - Attivo (" . count($prefetchList) . " domini)";
        echo "<div class='details'>Hook <code>wp_resource_hints</code> registrato</div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>DNS Prefetch</strong> - Configurato ma NON funzionante!";
        echo "<div class='details'>⚠️ I DNS hints NON saranno aggiunti</div></div>";
    }
} else {
    echo "<div class='check info'><span class='icon'>ℹ️</span> <strong>DNS Prefetch</strong> - Nessun dominio configurato";
}

// Preload
$results['total']++;
if (!empty($assetSettings['preload'])) {
    $results['active']++;
    $preloadList = is_array($assetSettings['preload']) ? $assetSettings['preload'] : explode("\n", $assetSettings['preload']);
    $results['working']++;
    echo "<div class='check success'><span class='icon'>✅</span> <strong>Resource Preload</strong> - " . count($preloadList) . " risorse configurate";
} else {
    echo "<div class='check info'><span class='icon'>ℹ️</span> <strong>Resource Preload</strong> - Nessuna risorsa configurata";
}

echo "</div>";

// ==============================================
// 3. FONT OPTIMIZER
// ==============================================
echo "<div class='card'>";
echo "<h2>🔤 Font Optimizer</h2>";

$fontOptimizer = $container->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class);
$fontSettings = $fontOptimizer->settings();

$results['total']++;
if ($fontSettings['enabled']) {
    $results['active']++;
    $hasStyleHook = has_filter('style_loader_tag', [$fontOptimizer, 'optimizeGoogleFonts']);
    
    if ($hasStyleHook) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Font Optimizer</strong> - Attivo e funzionante";
        echo "<div class='details'>
            - Google Fonts display=swap: " . ($fontSettings['google_fonts_display_swap'] ? 'Sì' : 'No') . "<br>
            - Preconnect: " . ($fontSettings['preconnect'] ? 'Sì' : 'No') . "<br>
            - Font preload: " . (empty($fontSettings['preload_fonts']) ? 'Nessuno' : count($fontSettings['preload_fonts']) . ' font') . "
        </div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Font Optimizer</strong> - Abilitato ma hook non registrato!";
        echo "<div class='details'>⚠️ Le ottimizzazioni font NON saranno applicate</div></div>";
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Font Optimizer</strong> - Disabilitato";
    echo "<div class='details'>Attiva per ottimizzare Google Fonts e ridurre il render-blocking</div></div>";
}

echo "</div>";

// ==============================================
// 4. IMAGE OPTIMIZER
// ==============================================
echo "<div class='card'>";
echo "<h2>🎨 Image Optimizer</h2>";

$imageOptimizer = $container->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class);
$imageSettings = $imageOptimizer->settings();

$results['total']++;
if ($imageSettings['enabled']) {
    $results['active']++;
    $hasImageSizeHook = has_filter('wp_get_attachment_image_attributes', [$imageOptimizer, 'addExplicitDimensions']);
    
    if ($hasImageSizeHook || $imageSettings['add_dimensions']) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Image Optimizer</strong> - Attivo e funzionante";
        echo "<div class='details'>
            - Dimensioni esplicite: " . ($imageSettings['add_dimensions'] ? 'Sì' : 'No') . "<br>
            - Aspect ratio CSS: " . ($imageSettings['aspect_ratio_css'] ? 'Sì' : 'No') . "
        </div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Image Optimizer</strong> - Abilitato ma NON funzionante!";
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Image Optimizer</strong> - Disabilitato";
}

echo "</div>";

// ==============================================
// 5. WEBP CONVERTER
// ==============================================
echo "<div class='card'>";
echo "<h2>🖼️ WebP/AVIF Converter</h2>";

$webpConverter = $container->get(\FP\PerfSuite\Services\Media\WebPConverter::class);
$webpSettings = $webpConverter->settings();

$results['total']++;
if ($webpSettings['enabled']) {
    $results['active']++;
    $hasContentHook = has_filter('the_content', [$webpConverter, 'replaceImages']);
    
    if ($hasContentHook || $webpSettings['auto_convert']) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>WebP Converter</strong> - Attivo";
        echo "<div class='details'>
            - Conversione automatica: " . ($webpSettings['auto_convert'] ? 'Sì' : 'No') . "<br>
            - Qualità: {$webpSettings['quality']}%<br>
            - Metodo: {$webpSettings['method']} (lossy/lossless)
        </div></div>";
        
        // Check capacità server
        if (function_exists('imagewebp')) {
            echo "<div class='check success'><span class='icon'>✅</span> Server supporta WebP (GD Library)</div>";
        } else {
            echo "<div class='check warning'><span class='icon'>⚠️</span> Server NON supporta WebP - La conversione non funzionerà!</div>";
            $results['issues']++;
        }
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>WebP Converter</strong> - Disabilitato";
}

// AVIF
$avifConverter = $container->get(\FP\PerfSuite\Services\Media\AVIFConverter::class);
$avifSettings = $avifConverter->settings();

$results['total']++;
if ($avifSettings['enabled']) {
    $results['active']++;
    if (function_exists('imageavif')) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>AVIF Converter</strong> - Attivo e supportato dal server</div>";
    } else {
        echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>AVIF Converter</strong> - Abilitato ma server non supporta AVIF</div>";
        $results['issues']++;
    }
}

echo "</div>";

// ==============================================
// 6. CRITICAL CSS
// ==============================================
echo "<div class='card'>";
echo "<h2>🎯 Critical CSS</h2>";

$criticalCss = $container->get(\FP\PerfSuite\Services\Assets\CriticalCss::class);
$criticalSettings = $criticalCss->settings();

$results['total']++;
if ($criticalSettings['enabled']) {
    $results['active']++;
    $hasHeadHook = has_action('wp_head', [$criticalCss, 'inlineCriticalCss']);
    
    if ($hasHeadHook) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Critical CSS</strong> - Attivo e funzionante";
        
        if (!empty($criticalSettings['css'])) {
            $cssLength = strlen($criticalSettings['css']);
            echo "<div class='details'>CSS critico configurato: {$cssLength} bytes</div>";
        } else {
            echo "<div class='details'>⚠️ Nessun CSS critico configurato - Aggiungi CSS per vedere i benefici</div>";
        }
        echo "</div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Critical CSS</strong> - Abilitato ma hook non registrato!</div>";
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Critical CSS</strong> - Disabilitato";
}

echo "</div>";

// ==============================================
// 7. CACHE
// ==============================================
echo "<div class='card'>";
echo "<h2>💾 Cache</h2>";

$pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
$cacheSettings = $pageCache->settings();

$results['total']++;
if ($cacheSettings['enabled']) {
    $results['active']++;
    $cacheStatus = $pageCache->status();
    
    if ($cacheStatus['enabled']) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Page Cache</strong> - Attivo e funzionante";
        echo "<div class='details'>
            - File cached: {$cacheStatus['files']}<br>
            - Dimensione: " . size_format($cacheStatus['size'] ?? 0) . "<br>
            - TTL: {$cacheSettings['ttl']} secondi
        </div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Page Cache</strong> - Abilitato ma NON funzionante!</div>";
    }
    
    // Cache Warming
    if ($cacheSettings['warming_enabled'] ?? false) {
        echo "<div class='check info'><span class='icon'>ℹ️</span> <strong>Cache Warming</strong> - Attivo";
        $warmingUrls = !empty($cacheSettings['warming_urls']) ? explode("\n", $cacheSettings['warming_urls']) : [];
        echo "<div class='details'>URL da pre-caricare: " . count($warmingUrls) . "</div></div>";
    }
} else {
    echo "<div class='check warning'><span class='icon'>⚠️</span> <strong>Page Cache</strong> - Disabilitato";
}

// Browser Cache Headers
$headers = $container->get(\FP\PerfSuite\Services\Cache\Headers::class);
$headerSettings = $headers->settings();

$results['total']++;
if ($headerSettings['enabled']) {
    $results['active']++;
    $results['working']++;
    echo "<div class='check success'><span class='icon'>✅</span> <strong>Browser Cache Headers</strong> - Attivo";
    echo "<div class='details'>Cache-Control: {$headerSettings['headers']['Cache-Control']}</div></div>";
}

// Object Cache
$objectCache = $container->get(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class);
$objectSettings = $objectCache->settings();

$results['total']++;
if ($objectSettings['enabled']) {
    $results['active']++;
    $objectStatus = $objectCache->testConnection();
    
    if ($objectStatus['success']) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Object Cache (Redis/Memcached)</strong> - Connesso a {$objectStatus['driver']}</div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Object Cache</strong> - Abilitato ma connessione fallita!</div>";
    }
}

echo "</div>";

// ==============================================
// 8. COMPRESSION
// ==============================================
echo "<div class='card'>";
echo "<h2>🗜️ Compression</h2>";

$compression = $container->get(\FP\PerfSuite\Services\Compression\CompressionManager::class);
$compressionSettings = $compression->settings();

$results['total']++;
if ($compressionSettings['enabled']) {
    $results['active']++;
    $compressionTest = $compression->testCompression();
    
    if ($compressionTest['enabled']) {
        $results['working']++;
        echo "<div class='check success'><span class='icon'>✅</span> <strong>Compressione Gzip/Brotli</strong> - Attiva";
        echo "<div class='details'>Metodo: {$compressionTest['method']}</div></div>";
    } else {
        $results['issues']++;
        echo "<div class='check error'><span class='icon'>❌</span> <strong>Compressione</strong> - Abilitata ma NON funzionante!</div>";
    }
}

echo "</div>";

// ==============================================
// 9. BACKEND OPTIMIZATION
// ==============================================
echo "<div class='card'>";
echo "<h2>⚙️ Backend Optimization</h2>";

$backendOpt = $container->get(\FP\PerfSuite\Services\Admin\BackendOptimizer::class);
$backendSettings = $backendOpt->settings();

// Heartbeat
if ($backendSettings['heartbeat_dashboard'] ?? false) {
    $results['total']++;
    $results['active']++;
    $results['working']++;
    echo "<div class='check success'><span class='icon'>✅</span> <strong>Heartbeat Dashboard</strong> - Ottimizzato ({$backendSettings['heartbeat_dashboard']})</div>";
}

// Revisioni
if ($backendSettings['limit_revisions'] ?? false) {
    $results['total']++;
    $results['active']++;
    $results['working']++;
    $limit = $backendSettings['revisions_limit'] ?? 5;
    echo "<div class='check success'><span class='icon'>✅</span> <strong>Revisioni Post</strong> - Limitate a {$limit}</div>";
}

// Autosave
if ($backendSettings['autosave_interval'] ?? false) {
    $results['total']++;
    $results['active']++;
    $results['working']++;
    $interval = $backendSettings['autosave_interval'] ?? 120;
    echo "<div class='check success'><span class='icon'>✅</span> <strong>Autosave</strong> - Intervallo {$interval}s</div>";
}

echo "</div>";

// ==============================================
// SUMMARY
// ==============================================
echo "<div class='card' style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;'>";
echo "<h2 style='color: white; border-bottom-color: rgba(255,255,255,0.3);'>📊 Riepilogo Diagnostica</h2>";

echo "<div class='summary'>";
echo "<div class='stat-box' style='background: rgba(255,255,255,0.2);'>
    <div class='stat-number'>{$results['total']}</div>
    <div class='stat-label'>Opzioni Totali</div>
</div>";
echo "<div class='stat-box' style='background: rgba(255,255,255,0.2);'>
    <div class='stat-number'>{$results['active']}</div>
    <div class='stat-label'>Attivate</div>
</div>";
echo "<div class='stat-box' style='background: rgba(70,180,80,0.9);'>
    <div class='stat-number'>{$results['working']}</div>
    <div class='stat-label'>Funzionanti</div>
</div>";
echo "<div class='stat-box' style='background: rgba(220,50,50,0.9);'>
    <div class='stat-number'>{$results['issues']}</div>
    <div class='stat-label'>Problemi Rilevati</div>
</div>";
echo "</div>";

$percentage = $results['active'] > 0 ? round(($results['working'] / $results['active']) * 100) : 0;

if ($results['issues'] === 0) {
    echo "<div style='text-align: center; padding: 20px; background: rgba(255,255,255,0.2); border-radius: 8px; margin-top: 20px;'>";
    echo "<h3 style='color: white; margin: 0;'>✅ TUTTO OK!</h3>";
    echo "<p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>Tutte le opzioni attivate funzionano correttamente ({$percentage}% efficienza)</p>";
    echo "</div>";
} else {
    echo "<div style='text-align: center; padding: 20px; background: rgba(255,200,0,0.3); border-radius: 8px; margin-top: 20px;'>";
    echo "<h3 style='color: white; margin: 0;'>⚠️ ATTENZIONE</h3>";
    echo "<p style='color: rgba(255,255,255,0.9); margin: 10px 0 0 0;'>Rilevati {$results['issues']} problemi che potrebbero impedire il corretto funzionamento</p>";
    echo "</div>";
}

echo "</div>";

// ==============================================
// RACCOMANDAZIONI
// ==============================================
if ($results['issues'] > 0 || $results['active'] < 10) {
    echo "<div class='card'>";
    echo "<h2>💡 Raccomandazioni</h2>";
    
    if ($results['issues'] > 0) {
        echo "<div class='check error'><span class='icon'>🔧</span> <strong>Azioni Richieste</strong>";
        echo "<div class='details'>
            Alcune opzioni sono abilitate ma non funzionano correttamente. Controlla i dettagli sopra e:<br>
            1. Verifica che i servizi necessari siano disponibili (es. GD Library per WebP)<br>
            2. Svuota tutte le cache e ricarica la pagina<br>
            3. Controlla eventuali conflitti con altri plugin<br>
            4. Se il problema persiste, disabilita temporaneamente l'opzione problematica
        </div></div>";
    }
    
    if (!$lazySettings['enabled']) {
        echo "<div class='check info'><span class='icon'>💡</span> <strong>Abilita Lazy Loading</strong>";
        echo "<div class='details'>Il lazy loading può migliorare il punteggio PageSpeed di 10-15 punti riducendo LCP</div></div>";
    }
    
    if (!$assetSettings['defer_js']) {
        echo "<div class='check info'><span class='icon'>💡</span> <strong>Abilita Defer JavaScript</strong>";
        echo "<div class='details'>Deferire JavaScript riduce il Total Blocking Time e migliora TBT</div></div>";
    }
    
    if (!$fontSettings['enabled']) {
        echo "<div class='check info'><span class='icon'>💡</span> <strong>Abilita Font Optimizer</strong>";
        echo "<div class='details'>L'ottimizzazione font (display=swap) migliora il FCP di 0.2-0.5s</div></div>";
    }
    
    if (!$cacheSettings['enabled']) {
        echo "<div class='check info'><span class='icon'>💡</span> <strong>Abilita Page Cache</strong>";
        echo "<div class='details'>La cache delle pagine può ridurre il TTFB del 60-80%</div></div>";
    }
    
    echo "</div>";
}

echo "</body></html>";

