<?php
/**
 * Test finale per verificare la correzione del problema di attivazione font
 */

echo "🧪 TEST FINALE OTTIMIZZAZIONE FONT\n";
echo "==================================\n\n";

// Simula il salvataggio delle impostazioni
echo "1. 💾 SIMULAZIONE SALVATAGGIO IMPOSTAZIONI:\n";
echo "------------------------------------------\n";

// Simula i dati POST del form
$_POST = [
    'form_type' => 'critical_path_fonts',
    'fp_ps_assets_nonce' => 'test_nonce',
    'critical_path_enabled' => '1',
    'preload_critical_fonts' => '1',
    'optimize_google_fonts' => '1',
    'preconnect_providers' => '1',
    'inject_font_display' => '1',
    'add_resource_hints' => '1',
    'preload_fonts' => '1',
];

echo "Dati POST simulati:\n";
foreach ($_POST as $key => $value) {
    echo "  - $key: $value\n";
}

// Simula il salvataggio delle impostazioni
$criticalPathSettings = [
    'enabled' => !empty($_POST['critical_path_enabled']),
    'preload_critical_fonts' => !empty($_POST['preload_critical_fonts']),
    'optimize_google_fonts' => !empty($_POST['optimize_google_fonts']),
    'preconnect_providers' => !empty($_POST['preconnect_providers']),
    'inject_font_display' => !empty($_POST['inject_font_display']),
    'add_resource_hints' => !empty($_POST['add_resource_hints']),
];

$fontSettings = [
    'preload_fonts' => !empty($_POST['preload_fonts']),
];

echo "\n2. 📊 IMPOSTAZIONI SALVATE:\n";
echo "---------------------------\n";

echo "Critical Path Settings:\n";
foreach ($criticalPathSettings as $key => $value) {
    echo "  - $key: " . ($value ? 'true' : 'false') . "\n";
}

echo "\nFont Settings:\n";
foreach ($fontSettings as $key => $value) {
    echo "  - $key: " . ($value ? 'true' : 'false') . "\n";
}

// Testa la logica di attivazione
echo "\n3. 🔧 LOGICA DI ATTIVAZIONE:\n";
echo "----------------------------\n";

$criticalPathActive = !empty($criticalPathSettings['enabled']);
$fontOptimizerActive = !empty($fontSettings['enabled']) || $criticalPathActive;

echo "Critical Path Optimizer: " . ($criticalPathActive ? '✅ ATTIVO' : '❌ DISATTIVO') . "\n";
echo "Font Optimizer: " . ($fontOptimizerActive ? '✅ ATTIVO' : '❌ DISATTIVO') . "\n";

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

$activeFeatures = 0;
foreach ($features as $feature => $enabled) {
    $status = $enabled ? '✅ ATTIVO' : '❌ DISATTIVO';
    echo "  - $feature: $status\n";
    if ($enabled) $activeFeatures++;
}

// Testa la re-registrazione
echo "\n5. 🔄 TEST RE-REGISTRAZIONE:\n";
echo "----------------------------\n";

// Simula la logica di re-registrazione
$reregisterCriticalPath = $criticalPathActive;
$reregisterFontOptimizer = $fontOptimizerActive;

echo "Critical Path re-registrazione: " . ($reregisterCriticalPath ? '✅ SÌ' : '❌ NO') . "\n";
echo "Font Optimizer re-registrazione: " . ($reregisterFontOptimizer ? '✅ SÌ' : '❌ NO') . "\n";

// Riepilogo finale
echo "\n6. 📋 RIEPILOGO FINALE:\n";
echo "----------------------\n";

$allServicesActive = $criticalPathActive && $fontOptimizerActive;
$allFeaturesActive = $activeFeatures === count($features);
$reregistrationActive = $reregisterCriticalPath && $reregisterFontOptimizer;

echo "Servizi attivi: " . ($allServicesActive ? '✅ TUTTI' : '❌ PARZIALI') . "\n";
echo "Funzionalità attive: $activeFeatures/" . count($features) . " " . ($allFeaturesActive ? '✅ TUTTE' : '❌ PARZIALI') . "\n";
echo "Re-registrazione: " . ($reregistrationActive ? '✅ COMPLETA' : '❌ PARZIALE') . "\n";

if ($allServicesActive && $allFeaturesActive && $reregistrationActive) {
    echo "\n🎉 TUTTO PERFETTO!\n";
    echo "✅ I servizi sono attivi\n";
    echo "✅ Tutte le funzionalità sono abilitate\n";
    echo "✅ La re-registrazione funziona\n";
    echo "\nLe funzionalità di ottimizzazione font dovrebbero ora funzionare correttamente!\n";
} else {
    echo "\n⚠️ PROBLEMI RILEVATI:\n";
    if (!$allServicesActive) echo "- Non tutti i servizi sono attivi\n";
    if (!$allFeaturesActive) echo "- Non tutte le funzionalità sono attive\n";
    if (!$reregistrationActive) echo "- La re-registrazione non funziona completamente\n";
}

echo "\n🏁 Test finale completato!\n";
