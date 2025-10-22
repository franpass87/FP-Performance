<?php
/**
 * Fix Attivazione Opzioni Plugin
 * 
 * Corregge problemi comuni che impediscono alle opzioni
 * di attivarsi correttamente.
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
    <title>Fix Opzioni Plugin</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 1000px; margin: 40px auto; padding: 20px; background: #f0f0f1; }
        .header { background: linear-gradient(135deg, #dc3232 0%, #a00 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card h2 { margin-top: 0; border-bottom: 2px solid #dc3232; padding-bottom: 10px; }
        .fix { margin: 15px 0; padding: 15px; border-left: 4px solid #ccc; background: #f9f9f9; border-radius: 4px; }
        .fix.success { border-left-color: #46b450; background: #f0f9ff; }
        .fix.warning { border-left-color: #ffb900; background: #fff8e5; }
        .fix.error { border-left-color: #dc3232; background: #fff0f0; }
        .icon { font-weight: bold; margin-right: 5px; font-size: 18px; }
        .btn { display: inline-block; padding: 10px 20px; background: #2271b1; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; border: none; cursor: pointer; }
        .btn:hover { background: #135e96; }
        .btn.danger { background: #dc3232; }
        .btn.danger:hover { background: #a00; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>

<div class="header">
    <h1>🔧 Fix Attivazione Opzioni Plugin</h1>
    <p style="margin: 10px 0 0 0; opacity: 0.9;">Diagnosi e riparazione automatica problemi di attivazione</p>
</div>

<?php
$container = Plugin::container();
$fixes = [];

// ==============================================
// FIX 1: Verifica e ripara permessi directory cache
// ==============================================
echo "<div class='card'>";
echo "<h2>💾 Fix Directory Cache</h2>";

$cacheDir = WP_CONTENT_DIR . '/cache/fp-performance-suite';

if (!is_dir($cacheDir)) {
    if (wp_mkdir_p($cacheDir)) {
        echo "<div class='fix success'><span class='icon'>✅</span> Directory cache creata: <code>{$cacheDir}</code></div>";
        $fixes[] = 'cache_dir_created';
    } else {
        echo "<div class='fix error'><span class='icon'>❌</span> Impossibile creare directory cache. Verifica i permessi di <code>" . WP_CONTENT_DIR . "</code></div>";
    }
} else {
    if (is_writable($cacheDir)) {
        echo "<div class='fix success'><span class='icon'>✅</span> Directory cache OK e scrivibile</div>";
    } else {
        // Prova a rendere scrivibile
        if (@chmod($cacheDir, 0755)) {
            echo "<div class='fix success'><span class='icon'>✅</span> Permessi directory cache corretti</div>";
            $fixes[] = 'cache_dir_permissions';
        } else {
            echo "<div class='fix error'><span class='icon'>❌</span> Directory cache non scrivibile e impossibile correggere. Esegui manualmente: <code>chmod 755 {$cacheDir}</code></div>";
        }
    }
}

// Verifica sottodirectory
$subDirs = ['page', 'object', 'webp'];
foreach ($subDirs as $subDir) {
    $path = $cacheDir . '/' . $subDir;
    if (!is_dir($path)) {
        if (wp_mkdir_p($path)) {
            echo "<div class='fix success'><span class='icon'>✅</span> Sottodirectory <code>{$subDir}</code> creata</div>";
            $fixes[] = "cache_subdir_{$subDir}";
        }
    }
}

echo "</div>";

// ==============================================
// FIX 2: Verifica e ripara servizi mancanti
// ==============================================
echo "<div class='card'>";
echo "<h2>🔌 Fix Servizi Mancanti</h2>";

$requiredServices = [
    [\FP\PerfSuite\Services\Assets\LazyLoadManager::class, 'Lazy Load Manager'],
    [\FP\PerfSuite\Services\Assets\FontOptimizer::class, 'Font Optimizer'],
    [\FP\PerfSuite\Services\Assets\ImageOptimizer::class, 'Image Optimizer'],
    [\FP\PerfSuite\Services\Assets\Optimizer::class, 'Asset Optimizer'],
    [\FP\PerfSuite\Services\Cache\PageCache::class, 'Page Cache'],
    [\FP\PerfSuite\Services\Media\WebPConverter::class, 'WebP Converter'],
];

$allServicesOk = true;
foreach ($requiredServices as [$class, $name]) {
    try {
        $service = $container->get($class);
        echo "<div class='fix success'><span class='icon'>✅</span> <strong>{$name}</strong> - Servizio caricato</div>";
    } catch (\Throwable $e) {
        $allServicesOk = false;
        echo "<div class='fix error'><span class='icon'>❌</span> <strong>{$name}</strong> - Errore: " . esc_html($e->getMessage()) . "</div>";
    }
}

if ($allServicesOk) {
    echo "<div class='fix success'><span class='icon'>🎉</span> Tutti i servizi richiesti sono disponibili</div>";
}

echo "</div>";

// ==============================================
// FIX 3: Re-register hooks
// ==============================================
echo "<div class='card'>";
echo "<h2>🔗 Fix Registrazione Hooks</h2>";

if (isset($_GET['reregister']) && $_GET['reregister'] === '1') {
    // Forza re-registrazione di tutti i servizi
    try {
        // Core services
        $container->get(\FP\PerfSuite\Services\Cache\PageCache::class)->register();
        $container->get(\FP\PerfSuite\Services\Cache\Headers::class)->register();
        $container->get(\FP\PerfSuite\Services\Assets\Optimizer::class)->register();
        $container->get(\FP\PerfSuite\Services\Media\WebPConverter::class)->register();
        
        // PageSpeed optimization services
        $container->get(\FP\PerfSuite\Services\Assets\LazyLoadManager::class)->register();
        $container->get(\FP\PerfSuite\Services\Assets\FontOptimizer::class)->register();
        $container->get(\FP\PerfSuite\Services\Assets\ImageOptimizer::class)->register();
        
        echo "<div class='fix success'><span class='icon'>✅</span> Hooks re-registrati con successo</div>";
        $fixes[] = 'hooks_reregistered';
    } catch (\Throwable $e) {
        echo "<div class='fix error'><span class='icon'>❌</span> Errore durante re-registrazione: " . esc_html($e->getMessage()) . "</div>";
    }
} else {
    echo "<div class='fix warning'><span class='icon'>⚠️</span> Clicca qui per forzare la re-registrazione degli hooks:</div>";
    echo "<a href='?reregister=1' class='btn'>🔄 Re-registra Hooks</a>";
}

echo "</div>";

// ==============================================
// FIX 4: Verifica estensioni PHP
// ==============================================
echo "<div class='card'>";
echo "<h2>🧩 Fix Estensioni PHP</h2>";

$extensions = [
    'gd' => 'GD Library (per WebP)',
    'imagick' => 'ImageMagick (opzionale per WebP)',
    'zlib' => 'Zlib (per compressione)',
    'json' => 'JSON',
];

foreach ($extensions as $ext => $name) {
    if (extension_loaded($ext)) {
        echo "<div class='fix success'><span class='icon'>✅</span> <strong>{$name}</strong> - Disponibile</div>";
    } else {
        if ($ext === 'imagick') {
            echo "<div class='fix warning'><span class='icon'>⚠️</span> <strong>{$name}</strong> - Non disponibile (opzionale)</div>";
        } else {
            echo "<div class='fix error'><span class='icon'>❌</span> <strong>{$name}</strong> - NON disponibile! Alcune funzionalità non funzioneranno.</div>";
        }
    }
}

// Verifica supporto WebP
if (function_exists('imagewebp')) {
    echo "<div class='fix success'><span class='icon'>✅</span> <strong>Supporto WebP</strong> - Attivo</div>";
} else {
    echo "<div class='fix error'><span class='icon'>❌</span> <strong>Supporto WebP</strong> - Non disponibile! Aggiorna GD Library.</div>";
}

// Verifica supporto AVIF
if (function_exists('imageavif')) {
    echo "<div class='fix success'><span class='icon'>✅</span> <strong>Supporto AVIF</strong> - Attivo (PHP 8.1+)</div>";
} else {
    echo "<div class='fix warning'><span class='icon'>⚠️</span> <strong>Supporto AVIF</strong> - Non disponibile (richiede PHP 8.1+)</div>";
}

echo "</div>";

// ==============================================
// FIX 5: Clear cache e transients
// ==============================================
echo "<div class='card'>";
echo "<h2>🗑️ Fix Cache & Transients</h2>";

if (isset($_GET['clear_cache']) && $_GET['clear_cache'] === '1') {
    // Clear page cache
    $pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
    $pageCache->clear();
    echo "<div class='fix success'><span class='icon'>✅</span> Page cache svuotata</div>";
    
    // Clear object cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        echo "<div class='fix success'><span class='icon'>✅</span> Object cache svuotata</div>";
    }
    
    // Clear transients
    global $wpdb;
    $deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
    echo "<div class='fix success'><span class='icon'>✅</span> {$deleted} transients eliminati</div>";
    
    // Clear rewrite rules
    flush_rewrite_rules();
    echo "<div class='fix success'><span class='icon'>✅</span> Rewrite rules resettate</div>";
    
    $fixes[] = 'cache_cleared';
} else {
    echo "<div class='fix warning'><span class='icon'>⚠️</span> Svuota tutte le cache per assicurarti che le modifiche vengano applicate:</div>";
    echo "<a href='?clear_cache=1' class='btn danger'>🗑️ Svuota Tutte le Cache</a>";
}

echo "</div>";

// ==============================================
// FIX 6: Verifica conflitti plugin
// ==============================================
echo "<div class='card'>";
echo "<h2>⚠️ Fix Conflitti Plugin</h2>";

$conflictPlugins = [
    'wp-super-cache/wp-cache.php' => 'WP Super Cache',
    'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
    'wp-fastest-cache/wpFastestCache.php' => 'WP Fastest Cache',
    'autoptimize/autoptimize.php' => 'Autoptimize',
    'wp-optimize/wp-optimize.php' => 'WP-Optimize',
    'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
];

$conflicts = [];
foreach ($conflictPlugins as $plugin => $name) {
    if (is_plugin_active($plugin)) {
        $conflicts[] = $name;
        echo "<div class='fix warning'><span class='icon'>⚠️</span> <strong>{$name}</strong> - Attivo (potenziale conflitto)</div>";
    }
}

if (empty($conflicts)) {
    echo "<div class='fix success'><span class='icon'>✅</span> Nessun plugin conflittuale rilevato</div>";
} else {
    echo "<div class='fix warning'><span class='icon'>💡</span> <strong>Raccomandazione:</strong> Disabilita gli altri plugin di cache per evitare conflitti</div>";
}

echo "</div>";

// ==============================================
// FIX 7: Reset impostazioni problematiche
// ==============================================
echo "<div class='card'>";
echo "<h2>🔄 Fix Reset Impostazioni</h2>";

if (isset($_GET['reset_settings']) && $_GET['reset_settings'] === '1') {
    // Reset alle impostazioni safe
    $safeDefaults = [
        'fp_ps_lazy_load' => [
            'enabled' => true,
            'images' => true,
            'iframes' => true,
            'skip_first' => 1,
            'min_size' => 100,
            'exclude_classes' => ['logo', 'no-lazy'],
        ],
        'fp_ps_assets' => [
            'minify_html' => true,
            'defer_js' => true,
            'async_css' => false, // Disabilitato di default per sicurezza
            'remove_emojis' => true,
            'remove_query_strings' => true,
        ],
        'fp_ps_font_optimization' => [
            'enabled' => true,
            'optimize_google_fonts' => true,
            'preconnect_providers' => true,
        ],
    ];
    
    foreach ($safeDefaults as $option => $value) {
        update_option($option, $value);
    }
    
    echo "<div class='fix success'><span class='icon'>✅</span> Impostazioni resettate ai valori sicuri predefiniti</div>";
    $fixes[] = 'settings_reset';
} else {
    echo "<div class='fix warning'><span class='icon'>⚠️</span> Se riscontri problemi, puoi resettare tutte le impostazioni ai valori sicuri predefiniti:</div>";
    echo "<a href='?reset_settings=1' class='btn danger' onclick='return confirm(\"Sei sicuro? Questo resetterà tutte le impostazioni.\")'>🔄 Reset Impostazioni</a>";
}

echo "</div>";

// ==============================================
// SUMMARY
// ==============================================
echo "<div class='card' style='background: linear-gradient(135deg, #46b450 0%, #2d8939 100%); color: white;'>";
echo "<h2 style='color: white; border-bottom-color: rgba(255,255,255,0.3);'>✅ Riepilogo Fix</h2>";

if (empty($fixes)) {
    echo "<p>Nessuna correzione applicata automaticamente. Usa i pulsanti sopra per applicare le correzioni necessarie.</p>";
} else {
    echo "<p><strong>" . count($fixes) . " correzioni applicate:</strong></p>";
    echo "<ul style='margin: 10px 0; padding-left: 20px;'>";
    foreach ($fixes as $fix) {
        echo "<li>" . esc_html(str_replace('_', ' ', ucfirst($fix))) . "</li>";
    }
    echo "</ul>";
    echo "<p style='margin-top: 20px;'><strong>Prossimi passi:</strong></p>";
    echo "<ol style='margin: 10px 0; padding-left: 20px;'>";
    echo "<li>Visita una pagina del tuo sito per verificare che le ottimizzazioni vengano applicate</li>";
    echo "<li>Esegui <code>diagnose-options-activation.php</code> per verificare lo stato</li>";
    echo "<li>Testa con PageSpeed Insights per vedere i miglioramenti</li>";
    echo "</ol>";
}

echo "<div style='text-align: center; margin-top: 20px;'>";
echo "<a href='diagnose-options-activation.php' class='btn' style='background: white; color: #2271b1;'>🔍 Esegui Diagnostica</a>";
echo "<a href='test-actual-output.php' class='btn' style='background: white; color: #2271b1; margin-left: 10px;'>🔬 Test Output</a>";
echo "</div>";

echo "</div>";

?>

</body>
</html>

