<?php
/**
 * Script per inizializzare le opzioni mobile e risolvere la pagina vuota
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

// Carica WordPress
require_once('../../../wp-config.php');

echo "🔧 Inizializzazione Opzioni Mobile FP Performance Suite\n";
echo "=======================================================\n\n";

// Verifica che il plugin sia attivo
if (!class_exists('FP\\PerfSuite\\Plugin')) {
    die("❌ Plugin FP Performance Suite non trovato o non attivo\n");
}

// Opzioni di default per i servizi mobile
$mobileOptions = [
    'fp_ps_mobile_optimizer' => [
        'enabled' => false,
        'disable_animations' => false,
        'remove_unnecessary_scripts' => false,
        'optimize_touch_targets' => false,
        'enable_responsive_images' => false
    ],
    'fp_ps_touch_optimizer' => [
        'enabled' => false,
        'disable_hover_effects' => true,
        'improve_touch_targets' => true,
        'optimize_scroll' => true,
        'prevent_zoom' => true
    ],
    'fp_ps_responsive_images' => [
        'enabled' => false,
        'enable_lazy_loading' => true,
        'optimize_srcset' => true,
        'max_mobile_width' => 768,
        'max_content_image_width' => '100%'
    ],
    'fp_ps_mobile_cache' => [
        'enabled' => false,
        'cache_mobile_separately' => false,
        'mobile_cache_duration' => 3600
    ]
];

echo "📊 Verifica opzioni esistenti:\n";

$initialized = 0;
foreach ($mobileOptions as $optionName => $defaultValues) {
    $existing = get_option($optionName, []);
    
    if (empty($existing)) {
        echo "   ❌ $optionName: Non esistente\n";
        
        // Inizializza con valori di default
        $result = update_option($optionName, $defaultValues);
        if ($result) {
            echo "   ✅ $optionName: Inizializzato\n";
            $initialized++;
        } else {
            echo "   ❌ $optionName: Errore nell'inizializzazione\n";
        }
    } else {
        echo "   ✅ $optionName: Esistente\n";
        $initialized++;
    }
}

echo "\n📈 Risultato:\n";
echo "   - Opzioni inizializzate: $initialized/" . count($mobileOptions) . "\n";

if ($initialized === count($mobileOptions)) {
    echo "\n✅ Tutte le opzioni mobile sono ora disponibili!\n";
    echo "📱 La pagina Mobile dovrebbe ora funzionare correttamente.\n";
    echo "🌐 Vai su: FP Performance > Mobile per verificare\n";
} else {
    echo "\n⚠️ Alcune opzioni potrebbero non essere state inizializzate correttamente.\n";
    echo "🔄 Prova a disattivare e riattivare il plugin.\n";
}

echo "\n🎯 Inizializzazione completata!\n";
?>
