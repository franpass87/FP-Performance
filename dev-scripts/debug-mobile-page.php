<?php
/**
 * Debug Script: Pagina Mobile Vuota
 * 
 * Questo script diagnostica e risolve il problema della pagina mobile vuota
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    // Se non siamo in WordPress, simuliamo l'ambiente
    define('ABSPATH', dirname(__FILE__) . '/../../');
    require_once ABSPATH . 'wp-config.php';
}

echo "🔍 DIAGNOSTICA PAGINA MOBILE VUOTA\n";
echo "=====================================\n\n";

// 1. Verifica opzioni mobile esistenti
echo "📋 1. VERIFICA OPZIONI MOBILE ESISTENTI\n";
echo "-------------------------------------\n";

$mobile_options = [
    'fp_ps_mobile_optimizer',
    'fp_ps_touch_optimizer', 
    'fp_ps_responsive_images',
    'fp_ps_mobile_cache'
];

$options_status = [];
foreach ($mobile_options as $option) {
    $value = get_option($option, 'NOT_FOUND');
    $exists = ($value !== 'NOT_FOUND');
    $options_status[$option] = [
        'exists' => $exists,
        'value' => $exists ? $value : null,
        'enabled' => $exists && is_array($value) && !empty($value['enabled'])
    ];
    
    echo sprintf("✅ %s: %s\n", 
        $option, 
        $exists ? 'ESISTE' : 'NON ESISTE'
    );
    
    if ($exists && is_array($value)) {
        echo sprintf("   - enabled: %s\n", !empty($value['enabled']) ? 'true' : 'false');
        echo sprintf("   - keys: %s\n", implode(', ', array_keys($value)));
    }
    echo "\n";
}

// 2. Verifica servizi mobile registrati
echo "🔧 2. VERIFICA SERVIZI MOBILE\n";
echo "-----------------------------\n";

$mobile_services = [
    'FP\PerfSuite\Services\Mobile\MobileOptimizer',
    'FP\PerfSuite\Services\Mobile\TouchOptimizer',
    'FP\PerfSuite\Services\Mobile\MobileCacheManager', 
    'FP\PerfSuite\Services\Mobile\ResponsiveImageManager'
];

foreach ($mobile_services as $service) {
    if (class_exists($service)) {
        echo "✅ $service: CLASSE ESISTE\n";
    } else {
        echo "❌ $service: CLASSE NON TROVATA\n";
    }
}

echo "\n";

// 3. Verifica pagina mobile admin
echo "📱 3. VERIFICA PAGINA MOBILE ADMIN\n";
echo "----------------------------------\n";

if (class_exists('FP\PerfSuite\Admin\Pages\Mobile')) {
    echo "✅ Classe Mobile Admin: ESISTE\n";
    
    // Verifica se la pagina è registrata nel menu
    global $submenu;
    $mobile_page_found = false;
    
    if (isset($submenu['fp-performance-suite'])) {
        foreach ($submenu['fp-performance-suite'] as $item) {
            if (strpos($item[2], 'fp-performance-suite-mobile') !== false) {
                $mobile_page_found = true;
                echo "✅ Pagina Mobile: REGISTRATA NEL MENU\n";
                echo "   - Slug: {$item[2]}\n";
                echo "   - Titolo: {$item[0]}\n";
                break;
            }
        }
    }
    
    if (!$mobile_page_found) {
        echo "❌ Pagina Mobile: NON REGISTRATA NEL MENU\n";
    }
} else {
    echo "❌ Classe Mobile Admin: NON TROVATA\n";
}

echo "\n";

// 4. Diagnosi del problema
echo "🔍 4. DIAGNOSI DEL PROBLEMA\n";
echo "---------------------------\n";

$problems_found = [];
$solutions = [];

// Controlla se le opzioni esistono
$missing_options = [];
foreach ($mobile_options as $option) {
    if (!$options_status[$option]['exists']) {
        $missing_options[] = $option;
    }
}

if (!empty($missing_options)) {
    $problems_found[] = "Opzioni mobile mancanti: " . implode(', ', $missing_options);
    $solutions[] = "Inizializzare le opzioni mancanti con valori di default";
}

// Controlla se i servizi sono abilitati
$disabled_services = [];
foreach ($mobile_options as $option) {
    if ($options_status[$option]['exists'] && !$options_status[$option]['enabled']) {
        $disabled_services[] = $option;
    }
}

if (!empty($disabled_services)) {
    $problems_found[] = "Servizi mobile disabilitati: " . implode(', ', $disabled_services);
    $solutions[] = "Abilitare i servizi mobile nelle impostazioni";
}

// Controlla se la classe Mobile esiste
if (!class_exists('FP\PerfSuite\Admin\Pages\Mobile')) {
    $problems_found[] = "Classe Mobile Admin non trovata";
    $solutions[] = "Verificare che il file src/Admin/Pages/Mobile.php esista";
}

if (empty($problems_found)) {
    echo "✅ NESSUN PROBLEMA RILEVATO\n";
    echo "La pagina mobile dovrebbe funzionare correttamente.\n";
} else {
    echo "❌ PROBLEMI RILEVATI:\n";
    foreach ($problems_found as $i => $problem) {
        echo sprintf("%d. %s\n", $i + 1, $problem);
    }
    
    echo "\n💡 SOLUZIONI SUGGERITE:\n";
    foreach ($solutions as $i => $solution) {
        echo sprintf("%d. %s\n", $i + 1, $solution);
    }
}

echo "\n";

// 5. Fix automatico
echo "🔧 5. APPLICAZIONE FIX AUTOMATICO\n";
echo "---------------------------------\n";

if (!empty($missing_options)) {
    echo "🔨 Inizializzazione opzioni mancanti...\n";
    
    // Mobile Optimizer
    if (!get_option('fp_ps_mobile_optimizer')) {
        update_option('fp_ps_mobile_optimizer', [
            'enabled' => true,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => true,
            'optimize_touch_targets' => true,
            'enable_responsive_images' => true
        ], false);
        echo "✅ fp_ps_mobile_optimizer inizializzata\n";
    }
    
    // Touch Optimizer
    if (!get_option('fp_ps_touch_optimizer')) {
        update_option('fp_ps_touch_optimizer', [
            'enabled' => true,
            'disable_hover_effects' => true,
            'improve_touch_targets' => true,
            'optimize_scroll' => true,
            'prevent_zoom' => true
        ], false);
        echo "✅ fp_ps_touch_optimizer inizializzata\n";
    }
    
    // Responsive Images
    if (!get_option('fp_ps_responsive_images')) {
        update_option('fp_ps_responsive_images', [
            'enabled' => true,
            'enable_lazy_loading' => true,
            'optimize_srcset' => true,
            'max_mobile_width' => 768,
            'max_content_image_width' => '100%'
        ], false);
        echo "✅ fp_ps_responsive_images inizializzata\n";
    }
    
    // Mobile Cache
    if (!get_option('fp_ps_mobile_cache')) {
        update_option('fp_ps_mobile_cache', [
            'enabled' => true,
            'enable_mobile_cache_headers' => true,
            'enable_resource_caching' => true,
            'cache_mobile_css' => true,
            'cache_mobile_js' => true,
            'html_cache_duration' => 300,
            'css_cache_duration' => 3600,
            'js_cache_duration' => 3600
        ], false);
        echo "✅ fp_ps_mobile_cache inizializzata\n";
    }
    
    echo "\n✅ TUTTE LE OPZIONI MOBILE INIZIALIZZATE!\n";
} else {
    echo "✅ Tutte le opzioni mobile sono già presenti\n";
}

// 6. Verifica finale
echo "\n🔍 6. VERIFICA FINALE\n";
echo "--------------------\n";

$final_check = true;
foreach ($mobile_options as $option) {
    $value = get_option($option, 'NOT_FOUND');
    if ($value === 'NOT_FOUND') {
        echo "❌ $option: ANCORA MANCANTE\n";
        $final_check = false;
    } else {
        echo "✅ $option: PRESENTE\n";
    }
}

if ($final_check) {
    echo "\n🎉 SUCCESSO! La pagina mobile dovrebbe ora funzionare correttamente.\n";
    echo "💡 Suggerimento: Vai su 'FP Performance > Mobile' per verificare.\n";
} else {
    echo "\n❌ ERRORE: Alcune opzioni non sono state inizializzate correttamente.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DIAGNOSTICA COMPLETATA\n";
echo str_repeat("=", 50) . "\n";
