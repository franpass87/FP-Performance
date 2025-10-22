<?php
/**
 * Script per correggere le opzioni del plugin FP Performance Suite
 * 
 * Questo script:
 * 1. Verifica le opzioni mancanti
 * 2. Crea le opzioni di default
 * 3. Abilita i servizi correttamente
 */

// Carica WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== CORREZIONE OPZIONI PLUGIN FP PERFORMANCE SUITE ===\n\n";

// Verifica che il plugin sia attivo
if (!class_exists('FP\\PerfSuite\\Plugin')) {
    die("ERRORE: Plugin FP Performance Suite non trovato o non attivo.\n");
}

echo "1. VERIFICA OPZIONI ESISTENTI:\n";

$requiredOptions = [
    'fp_ps_assets' => [
        'enabled' => true,
        'minify_html' => true,
        'defer_js' => true,
        'async_js' => false,
        'async_css' => false,
        'remove_emojis' => true,
        'dns_prefetch' => [],
        'preload' => [],
        'preconnect' => [],
        'heartbeat_admin' => 60,
        'combine_css' => false,
        'combine_js' => false,
        'critical_css_handles' => [],
        'exclude_css' => '',
        'exclude_js' => '',
        'minify_inline_css' => false,
        'minify_inline_js' => false,
        'remove_comments' => false,
        'optimize_google_fonts' => false,
        'preload_critical_assets' => false,
        'critical_assets_list' => []
    ],
    'fp_ps_webp' => [
        'enabled' => true,
        'quality' => 75,
        'lossy' => true,
        'auto_convert' => true,
        'exclude_small' => true,
        'min_size' => 1024
    ],
    'fp_ps_avif' => [
        'enabled' => false,
        'quality' => 60,
        'lossy' => true
    ],
    'fp_ps_batch_dom_updates' => [
        'enabled' => false,
        'batch_size' => 10,
        'use_raf' => true,
        'optimize_animations' => true,
        'prevent_layout_shift' => true,
        'batch_style_changes' => true,
        'batch_class_changes' => true,
        'batch_content_changes' => true,
        'debounce_timeout' => 16
    ],
    'fp_ps_css_optimization' => [
        'enabled' => false,
        'minify_css' => true,
        'combine_css' => false,
        'critical_css' => false,
        'remove_unused' => false
    ],
    'fp_ps_jquery_optimization' => [
        'enabled' => false,
        'defer_jquery' => true,
        'remove_jquery_migrate' => true,
        'optimize_jquery_ui' => false
    ],
    'fp_ps_third_party_scripts' => [
        'enabled' => false,
        'delay_all' => false,
        'scripts' => []
    ],
    'fp_ps_mobile_optimizer' => [
        'enabled' => false,
        'touch_optimization' => true,
        'viewport_optimization' => true,
        'mobile_specific_assets' => false
    ],
    'fp_ps_touch_optimizer' => [
        'enabled' => false,
        'touch_events' => true,
        'gesture_optimization' => true,
        'scroll_optimization' => true
    ],
    'fp_ps_responsive_images' => [
        'enabled' => false,
        'auto_srcset' => true,
        'webp_support' => true,
        'lazy_loading' => true
    ],
    'fp_ps_mobile_cache' => [
        'enabled' => false,
        'mobile_specific_cache' => true,
        'cache_mobile_assets' => true
    ],
    'fp_ps_ml_predictor' => [
        'enabled' => false,
        'data_retention_days' => 30,
        'prediction_threshold' => 0.7,
        'anomaly_threshold' => 0.8,
        'pattern_confidence_threshold' => 0.8
    ]
];

$fixedOptions = 0;
$totalOptions = count($requiredOptions);

foreach ($requiredOptions as $optionName => $defaultValue) {
    $currentValue = get_option($optionName, 'NOT_SET');
    
    if ($currentValue === 'NOT_SET' || !is_array($currentValue)) {
        echo "   - $optionName: MANCANTE - Creazione...\n";
        $result = update_option($optionName, $defaultValue);
        if ($result) {
            echo "     ✅ Creata con successo\n";
            $fixedOptions++;
        } else {
            echo "     ❌ Errore nella creazione\n";
        }
    } else {
        echo "   - $optionName: ESISTENTE\n";
    }
}

echo "\n2. ABILITAZIONE SERVIZI CORE:\n";

// Abilita servizi essenziali
$coreServices = [
    'fp_ps_assets' => ['enabled' => true],
    'fp_ps_webp' => ['enabled' => true],
    'fp_ps_page_cache' => ['enabled' => true],
    'fp_ps_browser_cache' => ['enabled' => true]
];

foreach ($coreServices as $option => $settings) {
    $current = get_option($option, []);
    $updated = array_merge($current, $settings);
    $result = update_option($option, $updated);
    echo "   - $option: " . ($result ? "ABILITATO" : "ERRORE") . "\n";
}

echo "\n3. VERIFICA REGISTRAZIONE SERVIZI:\n";

// Test se i servizi si registrano correttamente
try {
    $container = \FP\PerfSuite\Plugin::getContainer();
    if ($container) {
        echo "   - Container: DISPONIBILE\n";
        
        // Test Optimizer
        $assetSettings = get_option('fp_ps_assets', []);
        if (!empty($assetSettings['enabled'])) {
            echo "   - Asset Optimizer: ABILITATO\n";
        } else {
            echo "   - Asset Optimizer: DISABILITATO\n";
        }
        
        // Test WebP
        $webpSettings = get_option('fp_ps_webp', []);
        if (!empty($webpSettings['enabled'])) {
            echo "   - WebP Converter: ABILITATO\n";
        } else {
            echo "   - WebP Converter: DISABILITATO\n";
        }
        
    } else {
        echo "   - Container: ERRORE\n";
    }
} catch (Exception $e) {
    echo "   - Errore container: " . $e->getMessage() . "\n";
}

echo "\n4. PULIZIA CACHE:\n";

// Pulisce le cache per forzare il ricaricamento
if (function_exists('wp_cache_flush')) {
    wp_cache_flush();
    echo "   - Cache WordPress: PULITA\n";
}

// Pulisce cache del plugin se disponibile
try {
    $container = \FP\PerfSuite\Plugin::getContainer();
    if ($container) {
        $pageCache = $container->get(\FP\PerfSuite\Services\Cache\PageCache::class);
        if (method_exists($pageCache, 'flush')) {
            $pageCache->flush();
            echo "   - Cache Plugin: PULITA\n";
        }
    }
} catch (Exception $e) {
    echo "   - Errore pulizia cache: " . $e->getMessage() . "\n";
}

echo "\n=== RISULTATI ===\n";
echo "Opzioni corrette: $fixedOptions/$totalOptions\n";
echo "Servizi abilitati: " . count($coreServices) . "\n";

if ($fixedOptions > 0) {
    echo "\n✅ CORREZIONE COMPLETATA!\n";
    echo "Il plugin dovrebbe ora funzionare correttamente.\n";
    echo "Ricarica la pagina admin per vedere le modifiche.\n";
} else {
    echo "\n⚠️  Nessuna correzione necessaria.\n";
    echo "Tutte le opzioni erano già configurate correttamente.\n";
}

echo "\n=== FINE CORREZIONE ===\n";
