<?php
/**
 * VERIFICA COMPLETA DI TUTTI I SERVIZI DEL PLUGIN
 * 
 * Questo script verifica sistematicamente:
 * 1. Tutti i servizi disponibili
 * 2. Le loro opzioni di controllo
 * 3. La registrazione degli hook
 * 4. La coerenza tra interfaccia admin e codice
 */

// Carica WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== VERIFICA COMPLETA SERVIZI FP PERFORMANCE SUITE ===\n\n";

// Verifica che il plugin sia attivo
if (!class_exists('FP\\PerfSuite\\Plugin')) {
    die("ERRORE: Plugin FP Performance Suite non trovato o non attivo.\n");
}

// Lista completa di tutti i servizi e le loro opzioni di controllo
$allServices = [
    // CORE SERVICES (sempre attivi)
    'PageCache' => [
        'class' => 'FP\\PerfSuite\\Services\\Cache\\PageCache',
        'option' => 'fp_ps_page_cache',
        'required' => true
    ],
    'Headers' => [
        'class' => 'FP\\PerfSuite\\Services\\Cache\\Headers', 
        'option' => 'fp_ps_browser_cache',
        'required' => true
    ],
    
    // ASSET OPTIMIZATION
    'Optimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\Optimizer',
        'option' => 'fp_ps_assets',
        'check_key' => 'enabled'
    ],
    'CSSOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\CSSOptimizer',
        'option' => 'fp_ps_css_optimization',
        'check_key' => 'enabled'
    ],
    'jQueryOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\jQueryOptimizer',
        'option' => 'fp_ps_jquery_optimization',
        'check_key' => 'enabled'
    ],
    'BatchDOMUpdater' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\BatchDOMUpdater',
        'option' => 'fp_ps_batch_dom_updates',
        'check_key' => 'enabled'
    ],
    'ThirdPartyScriptManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\ThirdPartyScriptManager',
        'option' => 'fp_ps_third_party_scripts',
        'check_key' => 'enabled'
    ],
    'FontOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\FontOptimizer',
        'option' => 'fp_ps_font_optimization',
        'check_key' => 'enabled'
    ],
    'LazyLoadManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\LazyLoadManager',
        'option' => 'fp_ps_lazy_load',
        'check_key' => 'enabled'
    ],
    'CriticalCss' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\CriticalCss',
        'option' => 'fp_ps_critical_css',
        'check_key' => 'enabled'
    ],
    'UnusedCSSOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\UnusedCSSOptimizer',
        'option' => 'fp_ps_unused_css',
        'check_key' => 'enabled'
    ],
    'UnusedJavaScriptOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\UnusedJavaScriptOptimizer',
        'option' => 'fp_ps_unused_js',
        'check_key' => 'enabled'
    ],
    'CodeSplittingManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\CodeSplittingManager',
        'option' => 'fp_ps_code_splitting',
        'check_key' => 'enabled'
    ],
    'JavaScriptTreeShaker' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\JavaScriptTreeShaker',
        'option' => 'fp_ps_tree_shaking',
        'check_key' => 'enabled'
    ],
    'RenderBlockingOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\RenderBlockingOptimizer',
        'option' => 'fp_ps_render_blocking',
        'check_key' => 'enabled'
    ],
    'DOMReflowOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\DOMReflowOptimizer',
        'option' => 'fp_ps_dom_reflow',
        'check_key' => 'enabled'
    ],
    'CriticalPathOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\CriticalPathOptimizer',
        'option' => 'fp_ps_critical_path',
        'check_key' => 'enabled'
    ],
    'SmartAssetDelivery' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\SmartAssetDelivery',
        'option' => 'fp_ps_smart_delivery',
        'check_key' => 'enabled'
    ],
    'Http2ServerPush' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\Http2ServerPush',
        'option' => 'fp_ps_http2_push',
        'check_key' => 'enabled'
    ],
    'PredictivePrefetching' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\PredictivePrefetching',
        'option' => 'fp_ps_predictive_prefetch',
        'check_key' => 'enabled'
    ],
    'LighthouseFontOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\LighthouseFontOptimizer',
        'option' => 'fp_ps_lighthouse_fonts',
        'check_key' => 'enabled'
    ],
    'AutoFontOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\AutoFontOptimizer',
        'option' => 'fp_ps_auto_font_optimization',
        'check_key' => 'enabled'
    ],
    
    // MEDIA SERVICES
    'WebPConverter' => [
        'class' => 'FP\\PerfSuite\\Services\\Media\\WebPConverter',
        'option' => 'fp_ps_webp',
        'check_key' => 'enabled'
    ],
    'AVIFConverter' => [
        'class' => 'FP\\PerfSuite\\Services\\Media\\AVIFConverter',
        'option' => 'fp_ps_avif',
        'check_key' => 'enabled'
    ],
    'ImageOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Assets\\ImageOptimizer',
        'option' => 'fp_ps_image_optimization',
        'check_key' => 'enabled'
    ],
    
    // MOBILE SERVICES
    'MobileOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer',
        'option' => 'fp_ps_mobile_optimizer',
        'check_key' => 'enabled'
    ],
    'TouchOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\Mobile\\TouchOptimizer',
        'option' => 'fp_ps_touch_optimizer',
        'check_key' => 'enabled'
    ],
    'ResponsiveImageManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Mobile\\ResponsiveImageManager',
        'option' => 'fp_ps_responsive_images',
        'check_key' => 'enabled'
    ],
    'MobileCacheManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Mobile\\MobileCacheManager',
        'option' => 'fp_ps_mobile_cache',
        'check_key' => 'enabled'
    ],
    
    // DATABASE SERVICES
    'Cleaner' => [
        'class' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
        'option' => 'fp_ps_db',
        'check_key' => 'schedule',
        'check_value' => 'manual'
    ],
    'DatabaseOptimizer' => [
        'class' => 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer',
        'option' => 'fp_ps_db_optimizer',
        'check_key' => 'enabled'
    ],
    'QueryCacheManager' => [
        'class' => 'FP\\PerfSuite\\Services\\DB\\QueryCacheManager',
        'option' => 'fp_ps_query_cache',
        'check_key' => 'enabled'
    ],
    'DatabaseQueryMonitor' => [
        'class' => 'FP\\PerfSuite\\Services\\DB\\DatabaseQueryMonitor',
        'option' => 'fp_ps_query_monitor',
        'check_key' => 'enabled'
    ],
    
    // COMPRESSION SERVICES
    'CompressionManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Compression\\CompressionManager',
        'option' => 'fp_ps_compression',
        'check_key' => 'enabled'
    ],
    
    // MONITORING SERVICES
    'PerformanceMonitor' => [
        'class' => 'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor',
        'option' => 'fp_ps_performance_monitor',
        'check_key' => 'enabled'
    ],
    'CoreWebVitalsMonitor' => [
        'class' => 'FP\\PerfSuite\\Services\\Monitoring\\CoreWebVitalsMonitor',
        'option' => 'fp_ps_core_web_vitals',
        'check_key' => 'enabled'
    ],
    'ScheduledReports' => [
        'class' => 'FP\\PerfSuite\\Services\\Reports\\ScheduledReports',
        'option' => 'fp_ps_scheduled_reports',
        'check_key' => 'enabled'
    ],
    
    // ML SERVICES
    'MLPredictor' => [
        'class' => 'FP\\PerfSuite\\Services\\ML\\MLPredictor',
        'option' => 'fp_ps_ml_predictor',
        'check_key' => 'enabled'
    ],
    'AutoTuner' => [
        'class' => 'FP\\PerfSuite\\Services\\ML\\AutoTuner',
        'option' => 'fp_ps_ml_auto_tuner',
        'check_key' => 'enabled'
    ],
    
    // COMPATIBILITY SERVICES
    'ThemeCompatibility' => [
        'class' => 'FP\\PerfSuite\\Services\\Compatibility\\ThemeCompatibility',
        'option' => null, // Sempre attivo
        'required' => true
    ],
    'CompatibilityFilters' => [
        'class' => 'FP\\PerfSuite\\Services\\Compatibility\\CompatibilityFilters',
        'option' => null, // Sempre attivo
        'required' => true
    ],
    
    // SECURITY SERVICES
    'HtaccessSecurity' => [
        'class' => 'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity',
        'option' => 'fp_ps_security',
        'check_key' => 'enabled'
    ],
    
    // CDN SERVICES
    'CdnManager' => [
        'class' => 'FP\\PerfSuite\\Services\\CDN\\CdnManager',
        'option' => 'fp_ps_cdn',
        'check_key' => 'enabled'
    ],
    
    // CACHE SERVICES
    'ObjectCacheManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Cache\\ObjectCacheManager',
        'option' => 'fp_ps_object_cache',
        'check_key' => 'enabled'
    ],
    'EdgeCacheManager' => [
        'class' => 'FP\\PerfSuite\\Services\\Cache\\EdgeCacheManager',
        'option' => 'fp_ps_edge_cache',
        'check_key' => 'enabled'
    ],
    
    // PWA SERVICES
    'ServiceWorkerManager' => [
        'class' => 'FP\\PerfSuite\\Services\\PWA\\ServiceWorkerManager',
        'option' => 'fp_ps_service_worker',
        'check_key' => 'enabled'
    ]
];

echo "1. VERIFICA DISPONIBILITÀ SERVIZI:\n";
$availableServices = 0;
$totalServices = count($allServices);

foreach ($allServices as $serviceName => $config) {
    $className = $config['class'];
    $isAvailable = class_exists($className);
    $status = $isAvailable ? "✅ DISPONIBILE" : "❌ NON TROVATO";
    echo "   - $serviceName: $status\n";
    
    if ($isAvailable) {
        $availableServices++;
    }
}

echo "\n2. VERIFICA OPZIONI DI CONTROLLO:\n";
$optionIssues = [];

foreach ($allServices as $serviceName => $config) {
    if ($config['option'] === null) {
        echo "   - $serviceName: SEMPRE ATTIVO (opzione: N/A)\n";
        continue;
    }
    
    $optionValue = get_option($config['option'], 'NOT_SET');
    
    if ($optionValue === 'NOT_SET') {
        echo "   - $serviceName: ❌ OPZIONE MANCANTE ($config[option])\n";
        $optionIssues[] = $serviceName;
    } else {
        $isEnabled = false;
        
        if (isset($config['check_key'])) {
            if (is_array($optionValue)) {
                $isEnabled = !empty($optionValue[$config['check_key']]);
            } else {
                $isEnabled = (bool)$optionValue;
            }
        } else {
            $isEnabled = (bool)$optionValue;
        }
        
        $status = $isEnabled ? "✅ ABILITATO" : "⚠️ DISABILITATO";
        echo "   - $serviceName: $status (opzione: $config[option])\n";
    }
}

echo "\n3. VERIFICA REGISTRAZIONE HOOK:\n";
$container = \FP\PerfSuite\Plugin::getContainer();
$registeredServices = 0;

if ($container) {
    echo "   - Container: ✅ DISPONIBILE\n";
    
    foreach ($allServices as $serviceName => $config) {
        if (!class_exists($config['class'])) {
            continue;
        }
        
        try {
            $service = $container->get($config['class']);
            $hasRegisterMethod = method_exists($service, 'register');
            
            if ($hasRegisterMethod) {
                $registeredServices++;
                echo "   - $serviceName: ✅ REGISTRABILE\n";
            } else {
                echo "   - $serviceName: ⚠️ SENZA METODO REGISTER\n";
            }
        } catch (Exception $e) {
            echo "   - $serviceName: ❌ ERRORE - " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "   - Container: ❌ NON DISPONIBILE\n";
}

echo "\n4. VERIFICA COERENZA INTERFACCIA:\n";
$interfaceIssues = [];

// Verifica se le opzioni dell'interfaccia admin corrispondono al codice
$adminPages = [
    'Assets' => ['fp_ps_assets', 'fp_ps_webp', 'fp_ps_avif'],
    'Database' => ['fp_ps_db', 'fp_ps_query_cache'],
    'Compression' => ['fp_ps_compression'],
    'Mobile' => ['fp_ps_mobile_optimizer', 'fp_ps_touch_optimizer'],
    'ML' => ['fp_ps_ml_predictor']
];

foreach ($adminPages as $page => $options) {
    echo "   - Pagina $page:\n";
    foreach ($options as $option) {
        $value = get_option($option, 'NOT_SET');
        if ($value === 'NOT_SET') {
            echo "     ❌ $option: MANCANTE\n";
            $interfaceIssues[] = $option;
        } else {
            echo "     ✅ $option: PRESENTE\n";
        }
    }
}

echo "\n5. RACCOMANDAZIONI:\n";

if (!empty($optionIssues)) {
    echo "   ⚠️ OPZIONI MANCANTI:\n";
    foreach ($optionIssues as $service) {
        echo "     - $service\n";
    }
    echo "   💡 Soluzione: Esegui 'php fix-plugin-options.php'\n";
}

if (!empty($interfaceIssues)) {
    echo "   ⚠️ PROBLEMI INTERFACCIA:\n";
    foreach ($interfaceIssues as $option) {
        echo "     - $option\n";
    }
    echo "   💡 Soluzione: Vai nell'interfaccia admin e salva le impostazioni\n";
}

echo "\n=== RIEPILOGO ===\n";
echo "Servizi disponibili: $availableServices/$totalServices\n";
echo "Servizi registrabili: $registeredServices\n";
echo "Opzioni mancanti: " . count($optionIssues) . "\n";
echo "Problemi interfaccia: " . count($interfaceIssues) . "\n";

if (empty($optionIssues) && empty($interfaceIssues)) {
    echo "\n✅ TUTTO OK! Il plugin dovrebbe funzionare correttamente.\n";
} else {
    echo "\n⚠️ PROBLEMI RILEVATI! Applica le correzioni suggerite.\n";
}

echo "\n=== FINE VERIFICA ===\n";
