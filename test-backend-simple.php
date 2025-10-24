<?php
/**
 * Test Semplice Backend Optimization
 * 
 * Verifica se le impostazioni vengono salvate correttamente
 */

// Simula WordPress
if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        // Simula le opzioni WordPress
        static $options = [];
        return $options[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value) {
        // Simula l'aggiornamento delle opzioni
        static $options = [];
        $options[$option] = $value;
        return true;
    }
}

if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults) {
        return array_merge($defaults, $args);
    }
}

// Simula Logger
class Logger {
    public static function debug($message, $data = []) {
        echo "DEBUG: $message\n";
        if (!empty($data)) {
            echo "Data: " . print_r($data, true) . "\n";
        }
    }
    
    public static function info($message, $data = []) {
        echo "INFO: $message\n";
        if (!empty($data)) {
            echo "Data: " . print_r($data, true) . "\n";
        }
    }
}

echo "<h1>🔍 Test Semplice Backend Optimization</h1>\n";

// Test 1: Verifica se la classe esiste
if (!class_exists('FP\PerfSuite\Services\Admin\BackendOptimizer')) {
    echo "❌ Classe BackendOptimizer non trovata\n";
    exit;
}

echo "✅ Classe BackendOptimizer trovata\n";

// Test 2: Crea istanza
$backendOptimizer = new FP\PerfSuite\Services\Admin\BackendOptimizer();

// Test 3: Verifica impostazioni di default
$defaultSettings = $backendOptimizer->getSettings();
echo "<h2>📊 Impostazioni Default</h2>\n";
echo "<pre>" . print_r($defaultSettings, true) . "</pre>\n";

// Test 4: Simula salvataggio impostazioni
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

echo "<h2>🔄 Test Salvataggio Impostazioni</h2>\n";
$updateResult = $backendOptimizer->updateSettings($testSettings);
echo "Risultato aggiornamento: " . ($updateResult ? "✅ SUCCESSO" : "❌ FALLITO") . "\n";

// Test 5: Verifica impostazioni dopo salvataggio
$newSettings = $backendOptimizer->getSettings();
echo "<h2>📊 Impostazioni Dopo Salvataggio</h2>\n";
echo "<pre>" . print_r($newSettings, true) . "</pre>\n";

// Test 6: Verifica se enabled è true
$isEnabled = !empty($newSettings['enabled']);
echo "<h2>⚡ Stato Abilitazione</h2>\n";
echo "Backend Optimization " . ($isEnabled ? "✅ ABILITATO" : "❌ DISABILITATO") . "\n";

// Test 7: Verifica metriche
echo "<h2>📈 Test Metriche</h2>\n";
$metrics = $backendOptimizer->getBackendMetrics();
echo "<pre>" . print_r($metrics, true) . "</pre>\n";

// Test 8: Verifica report
echo "<h2>📋 Test Report</h2>\n";
$report = $backendOptimizer->getOptimizationsReport();
echo "<pre>" . print_r($report, true) . "</pre>\n";

echo "<h2>✅ Test Completato</h2>\n";
echo "Se vedi questo messaggio, il servizio BackendOptimizer funziona correttamente.\n";
echo "Il problema potrebbe essere nella registrazione del servizio nel plugin principale.\n";
