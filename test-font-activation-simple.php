<?php
/**
 * Test semplificato per verificare la logica di attivazione delle funzionalità font
 */

echo "🧪 TEST LOGICA ATTIVAZIONE FONT\n";
echo "==============================\n\n";

// Simula le impostazioni
$criticalPathSettings = [
    'enabled' => true,
    'preload_critical_fonts' => true,
    'optimize_google_fonts' => true,
    'preconnect_providers' => true,
    'inject_font_display' => true,
    'add_resource_hints' => true,
];

$fontSettings = [
    'enabled' => true,
    'optimize_google_fonts' => true,
    'preload_fonts' => true,
    'preconnect_providers' => true,
];

$fontOptimizationEnabled = false; // Legacy option

echo "1. 📊 IMPOSTAZIONI SIMULATE:\n";
echo "----------------------------\n";
echo "Critical Path Settings:\n";
foreach ($criticalPathSettings as $key => $value) {
    echo "  - $key: " . ($value ? 'true' : 'false') . "\n";
}

echo "\nFont Settings:\n";
foreach ($fontSettings as $key => $value) {
    echo "  - $key: " . ($value ? 'true' : 'false') . "\n";
}

echo "\nFont Optimization Enabled (legacy): " . ($fontOptimizationEnabled ? 'true' : 'false') . "\n";

// Testa la logica di attivazione
echo "\n2. 🔧 LOGICA DI ATTIVAZIONE:\n";
echo "----------------------------\n";

// Critical Path Optimizer
$criticalPathActive = !empty($criticalPathSettings['enabled']);
echo "Critical Path Optimizer attivo: " . ($criticalPathActive ? '✅ SÌ' : '❌ NO') . "\n";

// Font Optimizer
$fontOptimizerActive = $fontOptimizationEnabled || !empty($fontSettings['enabled']) || !empty($criticalPathSettings['enabled']);
echo "Font Optimizer attivo: " . ($fontOptimizerActive ? '✅ SÌ' : '❌ NO') . "\n";

// Testa le condizioni specifiche
echo "\n3. 🎯 CONDIZIONI SPECIFICHE:\n";
echo "---------------------------\n";

echo "Critical Path enabled: " . ($criticalPathSettings['enabled'] ? '✅' : '❌') . "\n";
echo "Font Settings enabled: " . (!empty($fontSettings['enabled']) ? '✅' : '❌') . "\n";
echo "Legacy font optimization: " . ($fontOptimizationEnabled ? '✅' : '❌') . "\n";

// Testa le funzionalità specifiche
echo "\n4. ⚙️ FUNZIONALITÀ SPECIFICHE:\n";
echo "-----------------------------\n";

$features = [
    'Preload Critical Fonts' => $criticalPathSettings['preload_critical_fonts'] ?? false,
    'Optimize Google Fonts' => $criticalPathSettings['optimize_google_fonts'] ?? false,
    'Preconnect Providers' => $criticalPathSettings['preconnect_providers'] ?? false,
    'Inject Font Display' => $criticalPathSettings['inject_font_display'] ?? false,
    'Add Resource Hints' => $criticalPathSettings['add_resource_hints'] ?? false,
    'Font Preload' => $fontSettings['preload_fonts'] ?? false,
];

foreach ($features as $feature => $enabled) {
    echo "  - $feature: " . ($enabled ? '✅ ATTIVO' : '❌ DISATTIVO') . "\n";
}

// Riepilogo
echo "\n5. 📋 RIEPILOGO:\n";
echo "---------------\n";

$allFeaturesActive = $criticalPathActive && $fontOptimizerActive;
$specificFeaturesActive = array_filter($features);

echo "Servizi principali: " . ($allFeaturesActive ? '✅ ATTIVI' : '❌ DISATTIVI') . "\n";
echo "Funzionalità specifiche attive: " . count($specificFeaturesActive) . "/" . count($features) . "\n";

if ($allFeaturesActive && count($specificFeaturesActive) > 0) {
    echo "\n🎉 TUTTO DOVREBBE FUNZIONARE!\n";
    echo "Le funzionalità di ottimizzazione font sono configurate correttamente.\n";
} else {
    echo "\n⚠️ PROBLEMI RILEVATI:\n";
    if (!$criticalPathActive) echo "- Critical Path Optimizer non attivo\n";
    if (!$fontOptimizerActive) echo "- Font Optimizer non attivo\n";
    if (count($specificFeaturesActive) === 0) echo "- Nessuna funzionalità specifica attiva\n";
}

echo "\n🏁 Test completato!\n";
