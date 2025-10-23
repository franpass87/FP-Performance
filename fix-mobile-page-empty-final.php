<?php
/**
 * 🔧 FIX DEFINITIVO: Pagina Mobile Vuota - FP Performance Suite
 * 
 * Questo script risolve definitivamente il problema della pagina admin mobile vuota
 * inizializzando tutte le opzioni mobile necessarie.
 * 
 * @author Francesco Passeri
 * @version 1.0
 */

// Verifica che siamo in ambiente WordPress
if (!function_exists('get_option')) {
    die('❌ Questo script deve essere eseguito in ambiente WordPress');
}

echo "🔧 FIX DEFINITIVO PAGINA MOBILE VUOTA - FP Performance Suite\n";
echo "============================================================\n\n";

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
$existing_options = [];

foreach ($mobile_options as $option_name => $option_label) {
    $option_value = get_option($option_name);
    
    if (!$option_value) {
        echo "❌ $option_label: MANCANTE\n";
        $missing_options[] = $option_name;
    } else {
        $enabled = $option_value['enabled'] ?? false;
        echo ($enabled ? "✅" : "⚠️") . " $option_label: " . ($enabled ? "ABILITATO" : "DISABILITATO") . "\n";
        $existing_options[] = $option_name;
    }
}

echo "\n";

// 2. Inizializza le opzioni mancanti
echo "🔧 2. INIZIALIZZAZIONE OPZIONI MOBILE MANCANTI\n";
echo "----------------------------------------------\n";

$default_options = [
    'fp_ps_mobile_optimizer' => [
        'enabled' => false, // UNCHECKED di default per sicurezza
        'disable_animations' => false,
        'remove_unnecessary_scripts' => false,
        'optimize_touch_targets' => false,
        'enable_responsive_images' => false
    ],
    'fp_ps_touch_optimizer' => [
        'enabled' => false, // UNCHECKED di default per sicurezza
        'disable_hover_effects' => true,
        'improve_touch_targets' => true,
        'optimize_scroll' => true,
        'prevent_zoom' => true
    ],
    'fp_ps_responsive_images' => [
        'enabled' => false, // UNCHECKED di default per sicurezza
        'enable_lazy_loading' => true,
        'optimize_srcset' => true,
        'max_mobile_width' => 768,
        'max_content_image_width' => '100%'
    ],
    'fp_ps_mobile_cache' => [
        'enabled' => false, // UNCHECKED di default per sicurezza
        'cache_mobile_separately' => true,
        'mobile_cache_duration' => 3600,
        'exclude_mobile_plugins' => []
    ]
];

$initialized_count = 0;

foreach ($missing_options as $option_name) {
    if (isset($default_options[$option_name])) {
        $result = update_option($option_name, $default_options[$option_name], false);
        if ($result) {
            echo "✅ $option_name: INIZIALIZZATA\n";
            $initialized_count++;
        } else {
            echo "❌ $option_name: ERRORE durante inizializzazione\n";
        }
    }
}

echo "\n";

// 3. Verifica finale
echo "🔍 3. VERIFICA FINALE\n";
echo "--------------------\n";

$all_initialized = true;

foreach ($mobile_options as $option_name => $option_label) {
    $option_value = get_option($option_name);
    
    if (!$option_value) {
        echo "❌ $option_label: ANCORA MANCANTE\n";
        $all_initialized = false;
    } else {
        $enabled = $option_value['enabled'] ?? false;
        echo ($enabled ? "✅" : "⚠️") . " $option_label: " . ($enabled ? "ABILITATO" : "DISABILITATO") . "\n";
    }
}

echo "\n";

// 4. Risultato finale
echo "📊 4. RISULTATO FINALE\n";
echo "----------------------\n";

if ($all_initialized) {
    echo "🎉 SUCCESSO! Tutte le opzioni mobile sono state inizializzate.\n";
    echo "📱 La pagina Mobile dovrebbe ora funzionare correttamente.\n";
    echo "\n";
    echo "🔧 PROSSIMI PASSI:\n";
    echo "1. Vai su FP Performance > Mobile\n";
    echo "2. Abilita le funzionalità che desideri\n";
    echo "3. Salva le impostazioni\n";
} else {
    echo "⚠️ ATTENZIONE: Alcune opzioni potrebbero non essere state inizializzate.\n";
    echo "🔄 Prova a disattivare e riattivare il plugin FP Performance Suite.\n";
}

echo "\n";
echo "📝 OPZIONI INIZIALIZZATE: $initialized_count/" . count($missing_options) . "\n";
echo "🔧 Script completato.\n";
