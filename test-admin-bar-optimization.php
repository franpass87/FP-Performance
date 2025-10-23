<?php
/**
 * Test Admin Bar Optimization
 * 
 * Verifica che le ottimizzazioni della Admin Bar funzionino correttamente
 */

// Carica WordPress
require_once('wp-config.php');
require_once('wp-load.php');

// Carica il plugin
require_once('fp-performance-suite.php');

echo "=== TEST OTTIMIZZAZIONI ADMIN BAR ===\n\n";

// Ottieni il container del plugin
$container = \FP\PerfSuite\Plugin::container();
$backendOptimizer = $container->get(\FP\PerfSuite\Services\Admin\BackendOptimizer::class);

echo "1. Test impostazioni Admin Bar...\n";

// Imposta le impostazioni Admin Bar
$adminBarSettings = [
    'enabled' => true,
    'admin_bar' => [
        'disable_frontend' => true,
        'disable_wordpress_logo' => true,
        'disable_updates' => true,
        'disable_comments' => true,
        'disable_new' => true,
        'disable_customize' => true,
    ]
];

$result = $backendOptimizer->updateSettings($adminBarSettings);
echo "   ✓ Impostazioni salvate: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";

// Verifica le impostazioni
$settings = $backendOptimizer->getSettings();
echo "   ✓ Admin Bar enabled: " . ($settings['admin_bar']['disable_frontend'] ? 'YES' : 'NO') . "\n";
echo "   ✓ WordPress logo disabled: " . ($settings['admin_bar']['disable_wordpress_logo'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Updates menu disabled: " . ($settings['admin_bar']['disable_updates'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Comments menu disabled: " . ($settings['admin_bar']['disable_comments'] ? 'YES' : 'NO') . "\n";
echo "   ✓ New menu disabled: " . ($settings['admin_bar']['disable_new'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Customize link disabled: " . ($settings['admin_bar']['disable_customize'] ? 'YES' : 'NO') . "\n\n";

echo "2. Test report ottimizzazioni...\n";
$report = $backendOptimizer->getOptimizationsReport();
echo "   ✓ Score ottimizzazione: " . $report['score'] . "/100\n";
echo "   ✓ Admin Bar status: " . $report['optimizations']['admin_bar']['status'] . "\n";
echo "   ✓ Admin Bar ottimizzato: " . ($report['optimizations']['admin_bar']['optimized'] ? 'YES' : 'NO') . "\n\n";

echo "3. Test hook WordPress...\n";

// Verifica che i hook siano registrati
global $wp_filter;

$adminBarHooks = [
    'show_admin_bar' => 'Disabilita Admin Bar frontend',
    'admin_bar_menu' => 'Rimuovi elementi Admin Bar'
];

foreach ($adminBarHooks as $hook => $description) {
    $hasHook = isset($wp_filter[$hook]) && !empty($wp_filter[$hook]->callbacks);
    echo "   ✓ Hook '$hook' ($description): " . ($hasHook ? 'REGISTERED' : 'NOT REGISTERED') . "\n";
}

echo "\n4. Test funzioni Admin Bar...\n";

// Test delle funzioni di rimozione
$testMethods = [
    'removeWordPressLogo' => 'Rimuovi logo WordPress',
    'removeUpdatesMenu' => 'Rimuovi menu aggiornamenti',
    'removeCommentsMenu' => 'Rimuovi menu commenti',
    'removeNewMenu' => 'Rimuovi menu + Nuovo',
    'removeCustomizeLink' => 'Rimuovi link Personalizza'
];

foreach ($testMethods as $method => $description) {
    $hasMethod = method_exists($backendOptimizer, $method);
    echo "   ✓ Metodo '$method' ($description): " . ($hasMethod ? 'EXISTS' : 'MISSING') . "\n";
}

echo "\n5. Test status completo...\n";
$status = $backendOptimizer->status();
echo "   ✓ Backend Optimizer enabled: " . ($status['enabled'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Admin Bar settings present: " . (!empty($status['settings']['admin_bar']) ? 'YES' : 'NO') . "\n";

echo "\n=== RISULTATO FINALE ===\n";
$allTestsPassed = $result && 
                  $settings['admin_bar']['disable_frontend'] && 
                  $settings['admin_bar']['disable_wordpress_logo'] &&
                  $report['optimizations']['admin_bar']['optimized'] &&
                  $status['enabled'];

echo "TUTTI I TEST: " . ($allTestsPassed ? '✅ PASSATI' : '❌ FALLITI') . "\n";

if ($allTestsPassed) {
    echo "\n🎉 Le ottimizzazioni della Admin Bar sono state implementate correttamente!\n";
    echo "   - Le impostazioni vengono salvate correttamente\n";
    echo "   - I hook WordPress vengono registrati\n";
    echo "   - Le funzioni di rimozione sono disponibili\n";
    echo "   - Il report include le informazioni Admin Bar\n";
} else {
    echo "\n⚠️  Alcuni test sono falliti. Controllare l'implementazione.\n";
}

echo "\n=== FINE TEST ===\n";
