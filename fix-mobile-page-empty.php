<?php
/**
 * Fix per pagina mobile vuota - FP Performance Suite
 * 
 * Questo script risolve il problema della pagina admin mobile vuota
 * inizializzando le opzioni mobile con valori UNCHECKED di default.
 */

// Verifica che siamo in ambiente WordPress
if (!function_exists('get_option')) {
    die('❌ Questo script deve essere eseguito in ambiente WordPress');
}

echo "🔧 FIX PAGINA MOBILE VUOTA - FP Performance Suite\n";
echo "================================================\n\n";

// 1. Verifica stato attuale delle opzioni
echo "📋 1. VERIFICA STATO ATTUALE OPZIONI MOBILE\n";
echo "-------------------------------------------\n";

$mobile_options = [
    'fp_ps_mobile_optimizer' => 'Mobile Optimizer',
    'fp_ps_touch_optimizer' => 'Touch Optimizer', 
    'fp_ps_responsive_images' => 'Responsive Images',
    'fp_ps_mobile_cache' => 'Mobile Cache'
];

$missing_options = [];

foreach ($mobile_options as $option_name => $option_label) {
    $option_value = get_option($option_name);
    
    if (!$option_value) {
        echo "❌ $option_label: MANCANTE\n";
        $missing_options[] = $option_name;
    } else {
        $enabled = $option_value['enabled'] ?? false;
        echo ($enabled ? "✅" : "❌") . " $option_label: " . ($enabled ? "ABILITATO" : "DISABILITATO") . "\n";
    }
}

echo "\n";

// 2. Usa il metodo del plugin per inizializzare le opzioni
echo "🔧 2. INIZIALIZZAZIONE OPZIONI MOBILE\n";
echo "------------------------------------\n";

// Prova a usare il metodo del plugin se disponibile
if (class_exists('FP\\PerfSuite\\Plugin') && method_exists('FP\\PerfSuite\\Plugin', 'forceMobileOptionsInitialization')) {
    $result = FP\PerfSuite\Plugin::forceMobileOptionsInitialization();
    echo ($result ? "✅" : "❌") . " Plugin method: " . ($result ? "SUCCESSO" : "ERRORE") . "\n";
} else {
    // Fallback: inizializza manualmente con valori UNCHECKED
    echo "⚠️ Metodo plugin non disponibile, uso fallback manuale\n";
    
    $default_options = [
        'fp_ps_mobile_optimizer' => [
            'enabled' => false, // UNCHECKED di default
            'disable_animations' => false,
            'remove_unnecessary_scripts' => false,
            'optimize_touch_targets' => false,
            'enable_responsive_images' => false
        ],
        'fp_ps_touch_optimizer' => [
            'enabled' => false, // UNCHECKED di default
            'disable_hover_effects' => false,
            'improve_touch_targets' => false,
            'optimize_scroll' => false,
            'prevent_zoom' => false
        ],
        'fp_ps_responsive_images' => [
            'enabled' => false, // UNCHECKED di default
            'enable_lazy_loading' => false,
            'optimize_srcset' => false,
            'max_mobile_width' => 768,
            'max_content_image_width' => '100%'
        ],
        'fp_ps_mobile_cache' => [
            'enabled' => false, // UNCHECKED di default
            'enable_mobile_cache_headers' => false,
            'enable_resource_caching' => false,
            'cache_mobile_css' => false,
            'cache_mobile_js' => false,
            'html_cache_duration' => 300,
            'css_cache_duration' => 3600,
            'js_cache_duration' => 3600
        ]
    ];
    
    foreach ($missing_options as $option_name) {
        if (isset($default_options[$option_name])) {
            $result = update_option($option_name, $default_options[$option_name], false);
            $label = $mobile_options[$option_name];
            echo ($result ? "✅" : "❌") . " $label: " . ($result ? "INIZIALIZZATO" : "ERRORE") . "\n";
        }
    }
}

echo "\n";

// 3. Verifica finale
echo "✅ 3. VERIFICA FINALE\n";
echo "--------------------\n";

$all_initialized = true;
foreach ($mobile_options as $option_name => $option_label) {
    $option_value = get_option($option_name);
    $exists = $option_value !== false;
    $enabled = $option_value && ($option_value['enabled'] ?? false);
    
    echo ($exists ? "✅" : "❌") . " $option_label: " . ($exists ? "INIZIALIZZATO" : "MANCANTE") . " (" . ($enabled ? "ABILITATO" : "DISABILITATO") . ")\n";
    
    if (!$exists) {
        $all_initialized = false;
    }
}

echo "\n";

// 4. Risultato
if ($all_initialized) {
    echo "🎉 RISULTATO: SUCCESSO!\n";
    echo "======================\n";
    echo "✅ Tutte le opzioni mobile sono state inizializzate\n";
    echo "✅ La pagina FP Performance > Mobile dovrebbe ora essere visibile\n";
    echo "✅ Le opzioni sono UNCHECKED di default (sicuro)\n";
    echo "✅ L'utente può abilitare le funzionalità manualmente\n\n";
    
    echo "📱 PROSSIMI PASSI:\n";
    echo "1. Vai su WordPress Admin > FP Performance > Mobile\n";
    echo "2. Verifica che la pagina mostri tutte le sezioni\n";
    echo "3. Abilita le funzionalità mobile che desideri utilizzare\n";
    echo "4. Configura le impostazioni secondo le tue esigenze\n";
} else {
    echo "⚠️ RISULTATO: PARZIALE\n";
    echo "=====================\n";
    echo "Alcune opzioni potrebbero non essere state inizializzate correttamente.\n";
    echo "Prova a disattivare e riattivare il plugin FP Performance Suite.\n";
}

echo "\n🔧 Script completato!\n";
?>