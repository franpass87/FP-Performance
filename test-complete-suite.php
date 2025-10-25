<?php
/**
 * Test Completo Suite Plugin FP Performance Suite
 * Script principale per eseguire tutti i test del plugin
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>🚀 Test Completo Suite FP Performance</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .summary { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
    .test-link { background: #0073aa; color: white; padding: 8px 12px; text-decoration: none; border-radius: 3px; display: inline-block; margin: 5px; }
    .test-link:hover { background: #005a87; }
</style>";

// 1. INTRODUZIONE
echo "<div class='section'>";
echo "<h2>📋 1. Introduzione</h2>";
echo "<div class='test-item info'>🎯 Questo script esegue un test completo del plugin FP Performance Suite</div>";
echo "<div class='test-item info'>📊 Include test per: installazione, pagine admin, ottimizzazioni, mobile, metriche, errori</div>";
echo "<div class='test-item info'>⏱️ Tempo stimato: 2-5 minuti</div>";
echo "<div class='test-item info'>🔍 Ogni test fornisce risultati dettagliati e raccomandazioni</div>";
echo "</div>";

// 2. TEST INDIVIDUALI
echo "<div class='section'>";
echo "<h2>🧪 2. Test Individuali</h2>";

$test_files = [
    'test-plugin-complete-verification.php' => 'Test Installazione Plugin',
    'test-admin-pages-verification.php' => 'Test Pagine Admin',
    'test-optimization-features.php' => 'Test Funzionalità Ottimizzazione',
    'test-mobile-optimization.php' => 'Test Ottimizzazioni Mobile',
    'test-performance-metrics.php' => 'Test Sistema Metriche',
    'test-error-handling.php' => 'Test Gestione Errori'
];

foreach ($test_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-item success'>✅ {$description} disponibile</div>";
        echo "<div class='test-item info'>🔗 <a href='{$file}' class='test-link' target='_blank'>Esegui {$description}</a></div>";
    } else {
        echo "<div class='test-item error'>❌ {$description} NON disponibile</div>";
    }
}
echo "</div>";

// 3. TEST RAPIDO COMPLETO
echo "<div class='section'>";
echo "<h2>⚡ 3. Test Rapido Completo</h2>";

$test_results = [
    'plugin_loaded' => false,
    'classes_available' => false,
    'admin_pages' => false,
    'optimizations' => false,
    'mobile_features' => false,
    'metrics_system' => false,
    'error_handling' => false
];

// Test caricamento plugin
if (class_exists('FP_Performance_Suite')) {
    $test_results['plugin_loaded'] = true;
    echo "<div class='test-item success'>✅ Plugin caricato correttamente</div>";
} else {
    echo "<div class='test-item error'>❌ Plugin NON caricato</div>";
}

// Test classi principali
$main_classes = ['FP_Cache_Manager', 'FP_Database_Optimizer', 'FP_Asset_Optimizer', 'FP_Mobile_Optimizer', 'FP_Performance_Monitor'];
$classes_loaded = 0;
foreach ($main_classes as $class) {
    if (class_exists($class)) {
        $classes_loaded++;
    }
}
if ($classes_loaded === count($main_classes)) {
    $test_results['classes_available'] = true;
    echo "<div class='test-item success'>✅ Tutte le classi principali disponibili</div>";
} else {
    echo "<div class='test-item warning'>⚠️ Solo {$classes_loaded}/" . count($main_classes) . " classi disponibili</div>";
}

// Test pagine admin
if (function_exists('fp_performance_add_admin_menu')) {
    $test_results['admin_pages'] = true;
    echo "<div class='test-item success'>✅ Pagine admin disponibili</div>";
} else {
    echo "<div class='test-item error'>❌ Pagine admin NON disponibili</div>";
}

// Test ottimizzazioni
$optimization_functions = ['fp_optimize_cache', 'fp_optimize_database', 'fp_optimize_assets'];
$optimizations_available = 0;
foreach ($optimization_functions as $function) {
    if (function_exists($function)) {
        $optimizations_available++;
    }
}
if ($optimizations_available > 0) {
    $test_results['optimizations'] = true;
    echo "<div class='test-item success'>✅ Funzionalità ottimizzazione disponibili</div>";
} else {
    echo "<div class='test-item error'>❌ Funzionalità ottimizzazione NON disponibili</div>";
}

// Test funzionalità mobile
if (class_exists('FP_Mobile_Optimizer')) {
    $test_results['mobile_features'] = true;
    echo "<div class='test-item success'>✅ Funzionalità mobile disponibili</div>";
} else {
    echo "<div class='test-item error'>❌ Funzionalità mobile NON disponibili</div>";
}

// Test sistema metriche
if (class_exists('FP_Performance_Monitor')) {
    $test_results['metrics_system'] = true;
    echo "<div class='test-item success'>✅ Sistema metriche disponibile</div>";
} else {
    echo "<div class='test-item error'>❌ Sistema metriche NON disponibile</div>";
}

// Test gestione errori
$error_functions = ['fp_log_error', 'fp_handle_error', 'fp_recover_error'];
$error_functions_available = 0;
foreach ($error_functions as $function) {
    if (function_exists($function)) {
        $error_functions_available++;
    }
}
if ($error_functions_available > 0) {
    $test_results['error_handling'] = true;
    echo "<div class='test-item success'>✅ Sistema gestione errori disponibile</div>";
} else {
    echo "<div class='test-item error'>❌ Sistema gestione errori NON disponibile</div>";
}
echo "</div>";

// 4. RIEPILOGO RISULTATI
echo "<div class='section'>";
echo "<h2>📊 4. Riepilogo Risultati</h2>";

$total_tests = count($test_results);
$passed_tests = array_sum($test_results);
$success_rate = round(($passed_tests / $total_tests) * 100, 1);

echo "<div class='summary'>";
echo "<h3>📈 Risultati Test Completo</h3>";
echo "<div class='test-item info'>📊 Test superati: {$passed_tests}/{$total_tests}</div>";
echo "<div class='test-item info'>📈 Tasso di successo: {$success_rate}%</div>";

if ($success_rate >= 90) {
    echo "<div class='test-item success'>🏆 Eccellente! Plugin funzionante al {$success_rate}%</div>";
} elseif ($success_rate >= 70) {
    echo "<div class='test-item success'>👍 Buono! Plugin funzionante al {$success_rate}%</div>";
} elseif ($success_rate >= 50) {
    echo "<div class='test-item warning'>⚠️ Discreto! Plugin parzialmente funzionante al {$success_rate}%</div>";
} else {
    echo "<div class='test-item error'>❌ Scarso! Plugin con problemi significativi al {$success_rate}%</div>";
}

echo "</div>";

// Dettaglio risultati
echo "<h4>🔍 Dettaglio Risultati:</h4>";
foreach ($test_results as $test => $result) {
    $test_name = ucwords(str_replace('_', ' ', $test));
    $status = $result ? '✅ Superato' : '❌ Fallito';
    $class = $result ? 'success' : 'error';
    echo "<div class='test-item {$class}'>📋 {$test_name}: {$status}</div>";
}
echo "</div>";

// 5. RACCOMANDAZIONI
echo "<div class='section'>";
echo "<h2>💡 5. Raccomandazioni</h2>";

if ($success_rate >= 90) {
    echo "<div class='test-item success'>🎉 Plugin in ottimo stato! Continua a monitorare le performance.</div>";
    echo "<div class='test-item info'>📋 Raccomandazioni:</div>";
    echo "<div class='test-item info'>• Esegui test regolari per mantenere le performance</div>";
    echo "<div class='test-item info'>• Monitora le metriche di performance</div>";
    echo "<div class='test-item info'>• Aggiorna il plugin quando disponibili nuove versioni</div>";
} elseif ($success_rate >= 70) {
    echo "<div class='test-item warning'>⚠️ Plugin funzionante ma con alcune aree da migliorare.</div>";
    echo "<div class='test-item info'>📋 Raccomandazioni:</div>";
    echo "<div class='test-item info'>• Identifica e risolvi i problemi specifici</div>";
    echo "<div class='test-item info'>• Esegui test individuali per aree specifiche</div>";
    echo "<div class='test-item info'>• Verifica la configurazione del plugin</div>";
} else {
    echo "<div class='test-item error'>❌ Plugin con problemi significativi che richiedono attenzione immediata.</div>";
    echo "<div class='test-item info'>📋 Raccomandazioni:</div>";
    echo "<div class='test-item info'>• Esegui test individuali per identificare problemi specifici</div>";
    echo "<div class='test-item info'>• Verifica l'installazione del plugin</div>";
    echo "<div class='test-item info'>• Controlla i log degli errori</div>";
    echo "<div class='test-item info'>• Considera la reinstallazione del plugin</div>";
}
echo "</div>";

// 6. PROSSIMI PASSI
echo "<div class='section'>";
echo "<h2>🚀 6. Prossimi Passi</h2>";

echo "<div class='test-item info'>📋 1. Esegui test individuali per aree specifiche</div>";
echo "<div class='test-item info'>📋 2. Risolvi eventuali problemi identificati</div>";
echo "<div class='test-item info'>📋 3. Configura le opzioni del plugin</div>";
echo "<div class='test-item info'>📋 4. Testa le funzionalità su dispositivi diversi</div>";
echo "<div class='test-item info'>📋 5. Monitora le performance nel tempo</div>";
echo "<div class='test-item info'>📋 6. Aggiorna il plugin regolarmente</div>";

echo "<h4>🔗 Test Individuali:</h4>";
foreach ($test_files as $file => $description) {
    if (file_exists($file)) {
        echo "<div class='test-item info'>🔗 <a href='{$file}' class='test-link' target='_blank'>{$description}</a></div>";
    }
}
echo "</div>";

// 7. INFORMAZIONI SISTEMA
echo "<div class='section'>";
echo "<h2>💻 7. Informazioni Sistema</h2>";

echo "<div class='test-item info'>📋 WordPress: " . get_bloginfo('version') . "</div>";
echo "<div class='test-item info'>📋 PHP: " . PHP_VERSION . "</div>";
echo "<div class='test-item info'>📋 Server: " . $_SERVER['SERVER_SOFTWARE'] . "</div>";
echo "<div class='test-item info'>📋 Memoria: " . ini_get('memory_limit') . "</div>";
echo "<div class='test-item info'>📋 Tempo massimo: " . ini_get('max_execution_time') . "s</div>";

// Test memoria utilizzata
$memory_usage = memory_get_usage(true);
echo "<div class='test-item info'>📋 Memoria utilizzata: " . round($memory_usage / 1024 / 1024, 2) . "MB</div>";

// Test query database
global $wpdb;
$query_count = $wpdb->num_queries;
echo "<div class='test-item info'>📋 Query database: {$query_count}</div>";
echo "</div>";

echo "<h2>✅ Test Completo Suite Completato!</h2>";
echo "<p>Usa i link sopra per eseguire test specifici e identificare eventuali problemi.</p>";
?>
