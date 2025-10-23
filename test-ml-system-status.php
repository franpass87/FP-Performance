<?php
/**
 * Test per verificare lo stato del sistema ML
 */

// Simula l'ambiente WordPress
define('WP_USE_THEMES', false);
require_once('wp-config.php');

echo "=== VERIFICA STATO SISTEMA ML ===\n\n";

// 1. Verifica se il sistema ML è abilitato
$ml_settings = get_option('fp_ps_ml_predictor', []);
$is_enabled = !empty($ml_settings['enabled']);

echo "1. STATO SISTEMA ML:\n";
echo "   Abilitato: " . ($is_enabled ? "SÌ" : "NO") . "\n";
echo "   Impostazioni: " . json_encode($ml_settings, JSON_PRETTY_PRINT) . "\n\n";

// 2. Verifica dati raccolti
$ml_data = get_option('fp_ps_ml_data', []);
$data_count = count($ml_data);

echo "2. DATI RACCOLTI:\n";
echo "   Numero di data points: " . $data_count . "\n";
if ($data_count > 0) {
    $last_data = end($ml_data);
    echo "   Ultimo dato: " . date('Y-m-d H:i:s', $last_data['timestamp']) . "\n";
    echo "   URL ultimo dato: " . $last_data['url'] . "\n";
} else {
    echo "   Nessun dato raccolto\n";
}
echo "\n";

// 3. Verifica cron jobs
echo "3. CRON JOBS:\n";
$next_analysis = wp_next_scheduled('fp_ps_ml_analyze_patterns');
$next_prediction = wp_next_scheduled('fp_ps_ml_predict_issues');

echo "   Prossima analisi pattern: " . ($next_analysis ? date('Y-m-d H:i:s', $next_analysis) : "Non programmata") . "\n";
echo "   Prossima predizione: " . ($next_prediction ? date('Y-m-d H:i:s', $next_prediction) : "Non programmata") . "\n\n";

// 4. Verifica ultima analisi
$last_analysis = get_option('fp_ps_ml_last_analysis', 0);
echo "4. ULTIMA ANALISI:\n";
echo "   Timestamp: " . ($last_analysis ? date('Y-m-d H:i:s', $last_analysis) : "Mai eseguita") . "\n\n";

// 5. Verifica pattern appresi
$patterns = get_option('fp_ps_ml_patterns', []);
echo "5. PATTERN APPRESI:\n";
echo "   Numero pattern: " . count($patterns) . "\n\n";

// 6. Verifica predizioni
$predictions = get_option('fp_ps_ml_predictions', []);
echo "6. PREDIZIONI:\n";
echo "   Numero predizioni: " . count($predictions) . "\n\n";

// 7. Verifica anomalie
$anomalies = get_option('fp_ps_anomalies', []);
echo "7. ANOMALIE:\n";
echo "   Numero anomalie: " . count($anomalies) . "\n\n";

// 8. Test raccolta dati manuale
echo "8. TEST RACCOLTA DATI:\n";
if ($is_enabled) {
    echo "   Sistema abilitato - raccolta dati dovrebbe essere attiva\n";
    
    // Simula raccolta dati
    $test_data = [
        'timestamp' => time(),
        'url' => home_url(),
        'load_time' => 0.5,
        'memory_usage' => memory_get_peak_usage(true),
        'db_queries' => 10,
        'cache_hits' => 5,
        'cache_misses' => 2,
        'user_agent' => 'Test Agent',
        'is_mobile' => false,
        'is_admin' => true,
        'active_plugins' => count(get_option('active_plugins', [])),
        'theme' => get_template(),
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version'),
        'server_load' => 0.1,
        'error_count' => 0
    ];
    
    echo "   Dati di test generati: " . json_encode($test_data, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "   Sistema disabilitato - raccolta dati non attiva\n";
}

echo "\n=== FINE VERIFICA ===\n";
