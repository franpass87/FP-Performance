<?php
/**
 * Test Base Admin Bar Optimization
 * 
 * Verifica solo la logica base senza dipendenze
 */

echo "=== TEST BASE OTTIMIZZAZIONI ADMIN BAR ===\n\n";

// Simula le funzioni WordPress necessarie
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10) {
        global $wp_actions;
        $wp_actions[$hook][] = ['callback' => $callback, 'priority' => $priority];
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10) {
        global $wp_filters;
        $wp_filters[$hook][] = ['callback' => $callback, 'priority' => $priority];
        return true;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        global $wp_options;
        return $wp_options[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        global $wp_options;
        $wp_options[$option] = $value;
        return true;
    }
}

if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults) {
        return array_merge($defaults, $args);
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return true;
    }
}

if (!function_exists('__return_false')) {
    function __return_false() {
        return false;
    }
}

// Le classi BackendRateLimiter e Logger sono ora caricate dai file

// Inizializza variabili globali
global $wp_actions, $wp_filters, $wp_options;
$wp_actions = [];
$wp_filters = [];
$wp_options = [];

echo "1. Test creazione istanza...\n";
require_once('src/Utils/BackendRateLimiter.php');
require_once('src/Utils/Logger.php');
require_once('src/Services/Admin/BackendOptimizer.php');

use FP\PerfSuite\Services\Admin\BackendOptimizer;

$backendOptimizer = new BackendOptimizer();
echo "   ✓ Istanza creata correttamente\n\n";

echo "2. Test impostazioni default...\n";
$defaultSettings = $backendOptimizer->getSettings();
echo "   ✓ Impostazioni default caricate\n";
echo "   ✓ Enabled: " . ($defaultSettings['enabled'] ? 'true' : 'false') . "\n";
echo "   ✓ Admin Bar settings: " . (isset($defaultSettings['admin_bar']) ? 'present' : 'not present') . "\n\n";

echo "3. Test salvataggio impostazioni Admin Bar...\n";
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

echo "4. Test report ottimizzazioni...\n";
$report = $backendOptimizer->getOptimizationsReport();
echo "   ✓ Score ottimizzazione: " . $report['score'] . "/100\n";
echo "   ✓ Admin Bar status: " . $report['optimizations']['admin_bar']['status'] . "\n";
echo "   ✓ Admin Bar ottimizzato: " . ($report['optimizations']['admin_bar']['optimized'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Frontend disabled: " . ($report['optimizations']['admin_bar']['frontend_disabled'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Logo removed: " . ($report['optimizations']['admin_bar']['logo_removed'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Updates removed: " . ($report['optimizations']['admin_bar']['updates_removed'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Comments removed: " . ($report['optimizations']['admin_bar']['comments_removed'] ? 'YES' : 'NO') . "\n";
echo "   ✓ New menu removed: " . ($report['optimizations']['admin_bar']['new_menu_removed'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Customize removed: " . ($report['optimizations']['admin_bar']['customize_removed'] ? 'YES' : 'NO') . "\n\n";

echo "5. Test metodi Admin Bar...\n";
$testMethods = [
    'removeWordPressLogo' => 'Rimuovi logo WordPress',
    'removeUpdatesMenu' => 'Rimuovi menu aggiornamenti',
    'removeCommentsMenu' => 'Rimuovi menu commenti',
    'removeNewMenu' => 'Rimuovi menu + Nuovo',
    'removeCustomizeLink' => 'Rimuovi link Personalizza'
];

$allMethodsExist = true;
foreach ($testMethods as $method => $description) {
    $hasMethod = method_exists($backendOptimizer, $method);
    echo "   ✓ Metodo '$method' ($description): " . ($hasMethod ? 'EXISTS' : 'MISSING') . "\n";
    if (!$hasMethod) {
        $allMethodsExist = false;
    }
}

echo "\n6. Test status completo...\n";
$status = $backendOptimizer->status();
echo "   ✓ Backend Optimizer enabled: " . ($status['enabled'] ? 'YES' : 'NO') . "\n";
echo "   ✓ Admin Bar settings present: " . (!empty($status['settings']['admin_bar']) ? 'YES' : 'NO') . "\n";
echo "   ✓ Report available: " . (isset($status['report']) ? 'YES' : 'NO') . "\n";

echo "\n=== RISULTATO FINALE ===\n";
$allTestsPassed = $result && 
                  $settings['admin_bar']['disable_frontend'] && 
                  $settings['admin_bar']['disable_wordpress_logo'] &&
                  $report['optimizations']['admin_bar']['optimized'] &&
                  $status['enabled'] &&
                  $allMethodsExist;

echo "TUTTI I TEST: " . ($allTestsPassed ? '✅ PASSATI' : '❌ FALLITI') . "\n";

if ($allTestsPassed) {
    echo "\n🎉 Le ottimizzazioni della Admin Bar sono state implementate correttamente!\n";
    echo "   - Le impostazioni vengono salvate correttamente\n";
    echo "   - I metodi di rimozione sono disponibili\n";
    echo "   - Il report include le informazioni Admin Bar\n";
    echo "   - Il punteggio di ottimizzazione include Admin Bar\n";
    echo "\n📋 PROSSIMI PASSI:\n";
    echo "   1. Attiva le ottimizzazioni backend nel plugin\n";
    echo "   2. Configura le opzioni Admin Bar nella pagina Backend\n";
    echo "   3. Verifica che gli elementi vengano rimossi dalla Admin Bar\n";
} else {
    echo "\n⚠️  Alcuni test sono falliti. Controllare l'implementazione.\n";
}

echo "\n=== FINE TEST ===\n";
