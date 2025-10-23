<?php
/**
 * 🧪 TEST: Stato Opzioni Mobile - FP Performance Suite
 * 
 * Questo script verifica lo stato delle opzioni mobile e mostra
 * informazioni dettagliate per il debug.
 */

// Verifica che siamo in ambiente WordPress
if (!function_exists('get_option')) {
    die('❌ Questo script deve essere eseguito in ambiente WordPress');
}

echo "🧪 TEST STATO OPZIONI MOBILE - FP Performance Suite\n";
echo "==================================================\n\n";

// 1. Verifica plugin attivo
echo "📋 1. VERIFICA PLUGIN\n";
echo "--------------------\n";

if (class_exists('FP\\PerfSuite\\Plugin')) {
    echo "✅ Plugin FP Performance Suite: ATTIVO\n";
    
    // Verifica se il metodo esiste
    if (method_exists('FP\\PerfSuite\\Plugin', 'forceMobileOptionsInitialization')) {
        echo "✅ Metodo forceMobileOptionsInitialization: DISPONIBILE\n";
    } else {
        echo "❌ Metodo forceMobileOptionsInitialization: MANCANTE\n";
    }
    
    if (method_exists('FP\\PerfSuite\\Plugin', 'ensureDefaultOptionsExist')) {
        echo "✅ Metodo ensureDefaultOptionsExist: DISPONIBILE\n";
    } else {
        echo "❌ Metodo ensureDefaultOptionsExist: MANCANTE\n";
    }
} else {
    echo "❌ Plugin FP Performance Suite: NON ATTIVO\n";
    exit;
}

echo "\n";

// 2. Verifica opzioni mobile
echo "📋 2. VERIFICA OPZIONI MOBILE\n";
echo "-----------------------------\n";

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

// 3. Statistiche
echo "📊 3. STATISTICHE\n";
echo "-----------------\n";
echo "Opzioni totali: " . count($mobile_options) . "\n";
echo "Opzioni esistenti: " . count($existing_options) . "\n";
echo "Opzioni mancanti: " . count($missing_options) . "\n";

echo "\n";

// 4. Test inizializzazione
echo "🔧 4. TEST INIZIALIZZAZIONE\n";
echo "---------------------------\n";

if (!empty($missing_options)) {
    echo "Tentativo di inizializzazione delle opzioni mancanti...\n";
    
    try {
        $result = FP\PerfSuite\Plugin::forceMobileOptionsInitialization();
        
        if ($result) {
            echo "✅ Inizializzazione: SUCCESSO\n";
            
            // Verifica dopo l'inizializzazione
            echo "\nVerifica dopo inizializzazione:\n";
            foreach ($missing_options as $option_name) {
                $option_value = get_option($option_name);
                if ($option_value) {
                    $enabled = $option_value['enabled'] ?? false;
                    echo "✅ $option_name: INIZIALIZZATA (" . ($enabled ? "ABILITATA" : "DISABILITATA") . ")\n";
                } else {
                    echo "❌ $option_name: ANCORA MANCANTE\n";
                }
            }
        } else {
            echo "❌ Inizializzazione: FALLITA\n";
        }
    } catch (Exception $e) {
        echo "❌ Errore durante inizializzazione: " . $e->getMessage() . "\n";
    }
} else {
    echo "✅ Tutte le opzioni sono già presenti, nessuna inizializzazione necessaria.\n";
}

echo "\n";

// 5. Informazioni sistema
echo "💻 5. INFORMAZIONI SISTEMA\n";
echo "--------------------------\n";
echo "WordPress Version: " . get_bloginfo('version') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Plugin Version: " . (defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'N/A') . "\n";
echo "Plugin Directory: " . (defined('FP_PERF_SUITE_DIR') ? FP_PERF_SUITE_DIR : 'N/A') . "\n";

echo "\n";

// 6. Raccomandazioni
echo "💡 6. RACCOMANDAZIONI\n";
echo "---------------------\n";

if (!empty($missing_options)) {
    echo "❌ PROBLEMA RILEVATO: Opzioni mobile mancanti\n";
    echo "🔧 SOLUZIONI:\n";
    echo "1. Disattiva e riattiva il plugin FP Performance Suite\n";
    echo "2. Esegui lo script fix-mobile-page-browser.php nel browser\n";
    echo "3. Contatta il supporto se il problema persiste\n";
} else {
    echo "✅ TUTTO OK: Tutte le opzioni mobile sono presenti\n";
    echo "📱 La pagina Mobile dovrebbe funzionare correttamente\n";
}

echo "\n";
echo "🔧 Test completato.\n";
