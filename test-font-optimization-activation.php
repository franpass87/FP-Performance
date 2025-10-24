<?php
/**
 * Test per verificare l'attivazione delle funzionalità di ottimizzazione font
 * 
 * Questo script testa se le funzionalità di ottimizzazione font si attivano
 * correttamente quando vengono salvate le impostazioni.
 */

// Carica WordPress
require_once('../../../wp-load.php');

// Verifica che il plugin sia attivo
if (!class_exists('FP\PerfSuite\Plugin')) {
    die("❌ Plugin FP Performance Suite non trovato!\n");
}

echo "🧪 TEST ATTIVAZIONE OTTIMIZZAZIONE FONT\n";
echo "=====================================\n\n";

// 1. Testa le impostazioni attuali
echo "1. 📊 STATO ATTUALE DELLE IMPOSTAZIONI:\n";
echo "------------------------------------\n";

$criticalPathSettings = get_option('fp_ps_critical_path_optimization', []);
$fontSettings = get_option('fp_ps_font_optimization', []);
$fontOptimizationEnabled = get_option('fp_ps_font_optimization_enabled', false);

echo "Critical Path Settings: " . print_r($criticalPathSettings, true);
echo "Font Settings: " . print_r($fontSettings, true);
echo "Font Optimization Enabled (legacy): " . ($fontOptimizationEnabled ? 'true' : 'false') . "\n\n";

// 2. Simula l'attivazione delle funzionalità
echo "2. 🔧 SIMULAZIONE ATTIVAZIONE FUNZIONALITÀ:\n";
echo "----------------------------------------\n";

// Attiva Critical Path Optimizer
$criticalPathSettings['enabled'] = true;
$criticalPathSettings['preload_critical_fonts'] = true;
$criticalPathSettings['optimize_google_fonts'] = true;
$criticalPathSettings['preconnect_providers'] = true;
$criticalPathSettings['inject_font_display'] = true;
$criticalPathSettings['add_resource_hints'] = true;

$result1 = update_option('fp_ps_critical_path_optimization', $criticalPathSettings);
echo "✅ Critical Path Settings salvate: " . ($result1 ? 'SUCCESS' : 'FAILED') . "\n";

// Attiva Font Optimizer
$fontSettings['enabled'] = true;
$fontSettings['optimize_google_fonts'] = true;
$fontSettings['preload_fonts'] = true;
$fontSettings['preconnect_providers'] = true;

$result2 = update_option('fp_ps_font_optimization', $fontSettings);
echo "✅ Font Settings salvate: " . ($result2 ? 'SUCCESS' : 'FAILED') . "\n\n";

// 3. Verifica se i servizi sono registrati
echo "3. 🔍 VERIFICA REGISTRAZIONE SERVIZI:\n";
echo "----------------------------------\n";

// Controlla se i hook sono registrati
$criticalPathHooks = [
    'wp_head' => has_action('wp_head', 'FP\PerfSuite\Services\Assets\CriticalPathOptimizer'),
    'style_loader_tag' => has_filter('style_loader_tag', 'FP\PerfSuite\Services\Assets\CriticalPathOptimizer')
];

$fontOptimizerHooks = [
    'wp_head' => has_action('wp_head', 'FP\PerfSuite\Services\Assets\FontOptimizer'),
    'style_loader_tag' => has_filter('style_loader_tag', 'FP\PerfSuite\Services\Assets\FontOptimizer')
];

echo "Critical Path Optimizer Hooks:\n";
foreach ($criticalPathHooks as $hook => $priority) {
    echo "  - $hook: " . ($priority ? "✅ Registrato (priorità: $priority)" : "❌ Non registrato") . "\n";
}

echo "\nFont Optimizer Hooks:\n";
foreach ($fontOptimizerHooks as $hook => $priority) {
    echo "  - $hook: " . ($priority ? "✅ Registrato (priorità: $priority)" : "❌ Non registrato") . "\n";
}

// 4. Testa l'istanziazione dei servizi
echo "\n4. 🏗️ TEST ISTANZIAZIONE SERVIZI:\n";
echo "--------------------------------\n";

try {
    $container = \FP\PerfSuite\Plugin::container();
    
    if ($container->has('FP\PerfSuite\Services\Assets\CriticalPathOptimizer')) {
        $criticalPathOptimizer = $container->get('FP\PerfSuite\Services\Assets\CriticalPathOptimizer');
        echo "✅ Critical Path Optimizer: Istanziazione OK\n";
        echo "   - Enabled: " . ($criticalPathOptimizer->isEnabled() ? 'true' : 'false') . "\n";
        echo "   - Settings: " . print_r($criticalPathOptimizer->getSettings(), true);
    } else {
        echo "❌ Critical Path Optimizer: Non disponibile nel container\n";
    }
    
    if ($container->has('FP\PerfSuite\Services\Assets\FontOptimizer')) {
        $fontOptimizer = $container->get('FP\PerfSuite\Services\Assets\FontOptimizer');
        echo "✅ Font Optimizer: Istanziazione OK\n";
        echo "   - Enabled: " . ($fontOptimizer->isEnabled() ? 'true' : 'false') . "\n";
        echo "   - Settings: " . print_r($fontOptimizer->getSettings(), true);
    } else {
        echo "❌ Font Optimizer: Non disponibile nel container\n";
    }
    
} catch (Exception $e) {
    echo "❌ Errore nell'istanziazione: " . $e->getMessage() . "\n";
}

// 5. Testa la registrazione manuale
echo "\n5. 🔄 TEST REGISTRAZIONE MANUALE:\n";
echo "--------------------------------\n";

try {
    $container = \FP\PerfSuite\Plugin::container();
    
    if ($container->has('FP\PerfSuite\Services\Assets\CriticalPathOptimizer')) {
        $criticalPathOptimizer = $container->get('FP\PerfSuite\Services\Assets\CriticalPathOptimizer');
        $criticalPathOptimizer->register();
        echo "✅ Critical Path Optimizer: Registrazione manuale OK\n";
    }
    
    if ($container->has('FP\PerfSuite\Services\Assets\FontOptimizer')) {
        $fontOptimizer = $container->get('FP\PerfSuite\Services\Assets\FontOptimizer');
        $fontOptimizer->register();
        echo "✅ Font Optimizer: Registrazione manuale OK\n";
    }
    
} catch (Exception $e) {
    echo "❌ Errore nella registrazione manuale: " . $e->getMessage() . "\n";
}

// 6. Verifica finale degli hook
echo "\n6. ✅ VERIFICA FINALE HOOK:\n";
echo "-------------------------\n";

$finalCriticalPathHooks = [
    'wp_head' => has_action('wp_head', 'FP\PerfSuite\Services\Assets\CriticalPathOptimizer'),
    'style_loader_tag' => has_filter('style_loader_tag', 'FP\PerfSuite\Services\Assets\CriticalPathOptimizer')
];

$finalFontOptimizerHooks = [
    'wp_head' => has_action('wp_head', 'FP\PerfSuite\Services\Assets\FontOptimizer'),
    'style_loader_tag' => has_filter('style_loader_tag', 'FP\PerfSuite\Services\Assets\FontOptimizer')
];

echo "Critical Path Optimizer Hooks (finale):\n";
foreach ($finalCriticalPathHooks as $hook => $priority) {
    echo "  - $hook: " . ($priority ? "✅ Registrato (priorità: $priority)" : "❌ Non registrato") . "\n";
}

echo "\nFont Optimizer Hooks (finale):\n";
foreach ($finalFontOptimizerHooks as $hook => $priority) {
    echo "  - $hook: " . ($priority ? "✅ Registrato (priorità: $priority)" : "❌ Non registrato") . "\n";
}

// 7. Riepilogo
echo "\n7. 📋 RIEPILOGO:\n";
echo "---------------\n";

$criticalPathActive = !empty($criticalPathSettings['enabled']);
$fontOptimizerActive = !empty($fontSettings['enabled']) || $fontOptimizationEnabled;

echo "Critical Path Optimizer: " . ($criticalPathActive ? "✅ ATTIVO" : "❌ DISATTIVO") . "\n";
echo "Font Optimizer: " . ($fontOptimizerActive ? "✅ ATTIVO" : "❌ DISATTIVO") . "\n";

$criticalPathHooksActive = array_filter($finalCriticalPathHooks);
$fontOptimizerHooksActive = array_filter($finalFontOptimizerHooks);

echo "Critical Path Hooks: " . (count($criticalPathHooksActive) > 0 ? "✅ REGISTRATI" : "❌ NON REGISTRATI") . "\n";
echo "Font Optimizer Hooks: " . (count($fontOptimizerHooksActive) > 0 ? "✅ REGISTRATI" : "❌ NON REGISTRATI") . "\n";

if ($criticalPathActive && count($criticalPathHooksActive) > 0 && $fontOptimizerActive && count($fontOptimizerHooksActive) > 0) {
    echo "\n🎉 TUTTO FUNZIONA CORRETTAMENTE!\n";
    echo "Le funzionalità di ottimizzazione font sono attive e registrate.\n";
} else {
    echo "\n⚠️ PROBLEMI RILEVATI:\n";
    if (!$criticalPathActive) echo "- Critical Path Optimizer non attivo\n";
    if (count($criticalPathHooksActive) === 0) echo "- Critical Path Optimizer hooks non registrati\n";
    if (!$fontOptimizerActive) echo "- Font Optimizer non attivo\n";
    if (count($fontOptimizerHooksActive) === 0) echo "- Font Optimizer hooks non registrati\n";
}

echo "\n🏁 Test completato!\n";
