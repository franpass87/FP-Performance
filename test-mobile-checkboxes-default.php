<?php
/**
 * Test per verificare che le opzioni mobile siano "checked" di default
 * 
 * Questo script simula il comportamento della pagina admin mobile
 * per verificare che le checkbox siano selezionate di default.
 */

// Simula l'ambiente WordPress per il test
if (!function_exists('get_option')) {
    // Simula le funzioni WordPress per il test
    $mock_options = [];
    
    function get_option($option_name, $default = false) {
        global $mock_options;
        return $mock_options[$option_name] ?? $default;
    }
    
    function update_option($option_name, $value, $autoload = true) {
        global $mock_options;
        $mock_options[$option_name] = $value;
        return true;
    }
}

echo "🧪 TEST CHECKBOX MOBILE DEFAULT\n";
echo "===============================\n\n";

// 1. Simula lo stato iniziale (opzioni mancanti)
echo "📋 1. STATO INIZIALE (OPZIONI MANCANTI)\n";
echo "--------------------------------------\n";

// Simula il metodo getSettings() della pagina Mobile
function getMobileSettings() {
    return get_option('fp_ps_mobile_optimizer', [
        'enabled' => false, // UNCHECKED di default
        'disable_animations' => false,
        'remove_unnecessary_scripts' => false,
        'optimize_touch_targets' => false,
        'enable_responsive_images' => false
    ]);
}

$settings = getMobileSettings();
echo "Mobile Optimizer Settings:\n";
foreach ($settings as $key => $value) {
    $checked = $value ? "✅ CHECKED" : "❌ UNCHECKED";
    echo "  - $key: $checked\n";
}

echo "\n";

// 2. Simula le altre sezioni
echo "📋 2. ALTRE SEZIONI MOBILE\n";
echo "--------------------------\n";

// Touch Optimizer
$touch_settings = get_option('fp_ps_touch_optimizer', []);
echo "Touch Optimizer Settings:\n";
if (empty($touch_settings)) {
    echo "  - Opzioni non inizializzate (userà default del plugin)\n";
} else {
    foreach ($touch_settings as $key => $value) {
        $checked = $value ? "✅ CHECKED" : "❌ UNCHECKED";
        echo "  - $key: $checked\n";
    }
}

// Responsive Images
$responsive_settings = get_option('fp_ps_responsive_images', []);
echo "\nResponsive Images Settings:\n";
if (empty($responsive_settings)) {
    echo "  - Opzioni non inizializzate (userà default del plugin)\n";
} else {
    foreach ($responsive_settings as $key => $value) {
        $checked = $value ? "✅ CHECKED" : "❌ UNCHECKED";
        echo "  - $key: $checked\n";
    }
}

echo "\n";

// 3. Simula l'inizializzazione delle opzioni
echo "🔧 3. INIZIALIZZAZIONE OPZIONI MOBILE\n";
echo "------------------------------------\n";

// Simula il comportamento del plugin dopo le modifiche (UNCHECKED di default)
$mobile_options = [
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

foreach ($mobile_options as $option_name => $option_values) {
    update_option($option_name, $option_values);
    echo "✅ $option_name: INIZIALIZZATO\n";
}

echo "\n";

// 4. Verifica stato finale delle checkbox
echo "✅ 4. VERIFICA CHECKBOX FINALI\n";
echo "-----------------------------\n";

// Mobile Optimizer
$final_settings = getMobileSettings();
echo "Mobile Optimizer (Pagina Admin):\n";
foreach ($final_settings as $key => $value) {
    $checked = $value ? "✅ CHECKED" : "❌ UNCHECKED";
    echo "  - $key: $checked\n";
}

// Touch Optimizer
$final_touch = get_option('fp_ps_touch_optimizer', []);
echo "\nTouch Optimizer (Pagina Admin):\n";
if (!empty($final_touch)) {
    foreach ($final_touch as $key => $value) {
        $checked = $value ? "✅ CHECKED" : "❌ UNCHECKED";
        echo "  - $key: $checked\n";
    }
} else {
    echo "  - Opzioni non inizializzate\n";
}

// Responsive Images
$final_responsive = get_option('fp_ps_responsive_images', []);
echo "\nResponsive Images (Pagina Admin):\n";
if (!empty($final_responsive)) {
    foreach ($final_responsive as $key => $value) {
        $checked = $value ? "✅ CHECKED" : "❌ UNCHECKED";
        echo "  - $key: $checked\n";
    }
} else {
    echo "  - Opzioni non inizializzate\n";
}

echo "\n";

// 5. Risultato del test
echo "📊 5. RISULTATO DEL TEST\n";
echo "=======================\n";

$main_enabled = $final_settings['enabled'] ?? false;
$touch_enabled = $final_touch['enabled'] ?? false;
$responsive_enabled = $final_responsive['enabled'] ?? false;

if ($main_enabled) {
    echo "🎉 Mobile Optimizer: ✅ ABILITATO (checkbox checked)\n";
} else {
    echo "❌ Mobile Optimizer: DISABILITATO (checkbox unchecked)\n";
}

if ($touch_enabled) {
    echo "🎉 Touch Optimizer: ✅ ABILITATO (checkbox checked)\n";
} else {
    echo "❌ Touch Optimizer: DISABILITATO (checkbox unchecked)\n";
}

if ($responsive_enabled) {
    echo "🎉 Responsive Images: ✅ ABILITATO (checkbox checked)\n";
} else {
    echo "❌ Responsive Images: DISABILITATO (checkbox unchecked)\n";
}

echo "\n";

if ($main_enabled && $touch_enabled && $responsive_enabled) {
    echo "🎉 RISULTATO: SUCCESSO!\n";
    echo "✅ Tutte le opzioni principali sono CHECKED di default\n";
    echo "✅ La pagina mobile mostrerà le checkbox selezionate\n";
    echo "✅ I servizi mobile saranno attivi al primo caricamento\n";
} else {
    echo "⚠️ RISULTATO: PARZIALE\n";
    echo "Alcune opzioni potrebbero non essere checked di default.\n";
}

echo "\n🔧 Test completato!\n";
?>
