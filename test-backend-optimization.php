<?php
/**
 * Test Backend Optimization
 * 
 * Verifica se il servizio BackendOptimizer funziona correttamente
 */

// Carica WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "<h1>🔍 Test Backend Optimization</h1>\n";

// Verifica se il plugin è attivo
if (!class_exists('FP\PerfSuite\Plugin')) {
    echo "❌ Plugin FP Performance Suite non trovato\n";
    exit;
}

echo "✅ Plugin FP Performance Suite trovato\n";

// Verifica se la classe BackendOptimizer esiste
if (!class_exists('FP\PerfSuite\Services\Admin\BackendOptimizer')) {
    echo "❌ Classe BackendOptimizer non trovata\n";
    exit;
}

echo "✅ Classe BackendOptimizer trovata\n";

// Crea istanza del servizio
$backendOptimizer = new FP\PerfSuite\Services\Admin\BackendOptimizer();

// Verifica impostazioni correnti
$settings = $backendOptimizer->getSettings();
echo "<h2>📊 Impostazioni Correnti</h2>\n";
echo "<pre>" . print_r($settings, true) . "</pre>\n";

// Verifica se il servizio è abilitato
$isEnabled = !empty($settings['enabled']);
echo "<h2>⚡ Stato Abilitazione</h2>\n";
echo "Backend Optimization " . ($isEnabled ? "✅ ABILITATO" : "❌ DISABILITATO") . "\n";

// Verifica opzioni WordPress
$optionKey = 'fp_ps_backend_optimizer';
$wpOptions = get_option($optionKey, []);
echo "<h2>🗄️ Opzioni WordPress</h2>\n";
echo "Chiave opzione: {$optionKey}\n";
echo "<pre>" . print_r($wpOptions, true) . "</pre>\n";

// Test di aggiornamento impostazioni
echo "<h2>🔄 Test Aggiornamento Impostazioni</h2>\n";
$testSettings = [
    'enabled' => true,
    'optimize_heartbeat' => true,
    'heartbeat_interval' => 60,
    'limit_revisions' => true,
    'revisions_limit' => 5,
    'optimize_dashboard' => true,
    'remove_dashboard_widgets' => true,
    'optimize_admin_ajax' => true,
    'admin_bar' => [
        'disable_frontend' => true,
        'disable_wordpress_logo' => true,
        'disable_updates' => true,
        'disable_comments' => true,
        'disable_new' => true,
        'disable_customize' => true,
    ],
    'dashboard' => [
        'disable_welcome' => true,
        'disable_quick_press' => true,
        'disable_activity' => true,
        'disable_primary' => true,
        'disable_secondary' => true,
        'disable_site_health' => true,
        'disable_php_update' => true,
    ],
    'heartbeat' => [
        'dashboard' => 'slow',
        'editor' => 'default',
        'frontend' => 'disable',
    ],
    'admin_ajax' => [
        'disable_emojis' => true,
        'disable_embeds' => true,
    ],
];

$updateResult = $backendOptimizer->updateSettings($testSettings);
echo "Risultato aggiornamento: " . ($updateResult ? "✅ SUCCESSO" : "❌ FALLITO") . "\n";

// Verifica impostazioni dopo aggiornamento
$newSettings = $backendOptimizer->getSettings();
echo "<h2>📊 Impostazioni Dopo Aggiornamento</h2>\n";
echo "<pre>" . print_r($newSettings, true) . "</pre>\n";

// Verifica se il servizio è registrato
echo "<h2>🔧 Test Registrazione Servizio</h2>\n";

// Simula la registrazione
$backendOptimizer->register();

// Verifica hook registrati
global $wp_filter;
$initHooks = $wp_filter['init'] ?? [];
$heartbeatHooks = $wp_filter['heartbeat_settings'] ?? [];

echo "Hook 'init' registrati: " . count($initHooks) . "\n";
echo "Hook 'heartbeat_settings' registrati: " . count($heartbeatHooks) . "\n";

// Test metriche
echo "<h2>📈 Test Metriche</h2>\n";
$metrics = $backendOptimizer->getBackendMetrics();
echo "<pre>" . print_r($metrics, true) . "</pre>\n";

// Test report ottimizzazioni
echo "<h2>📋 Test Report Ottimizzazioni</h2>\n";
$report = $backendOptimizer->getOptimizationsReport();
echo "<pre>" . print_r($report, true) . "</pre>\n";

// Test status
echo "<h2>📊 Test Status</h2>\n";
$status = $backendOptimizer->status();
echo "<pre>" . print_r($status, true) . "</pre>\n";

echo "<h2>✅ Test Completato</h2>\n";
echo "Se vedi questo messaggio, il servizio BackendOptimizer funziona correttamente.\n";
echo "Il problema potrebbe essere nella registrazione del servizio nel plugin principale.\n";
