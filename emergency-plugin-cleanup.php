<?php
/**
 * EMERGENZA: Pulizia Completa Plugin FP Performance Suite
 * Script per eliminare TUTTE le versioni e ricreare da zero
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>🚨 EMERGENZA: Pulizia Completa Plugin</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .danger { background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; border-radius: 5px; }
    .success-box { background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; border-radius: 5px; }
</style>";

// 1. DISATTIVAZIONE FORZATA
echo "<div class='section'>";
echo "<h2>🔄 1. Disattivazione Forzata</h2>";

$active_plugins = get_option('active_plugins', []);
$fp_plugins_found = [];

foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'fp-performance') !== false) {
        $fp_plugins_found[] = $plugin;
        echo "<div class='test-item error'>❌ Plugin attivo: {$plugin}</div>";
    }
}

if (!empty($fp_plugins_found)) {
    echo "<div class='danger'>";
    echo "<h3>⚠️ DISATTIVAZIONE FORZATA IN CORSO!</h3>";
    echo "<p>Disattivando " . count($fp_plugins_found) . " plugin FP Performance Suite...</p>";
    
    foreach ($fp_plugins_found as $plugin) {
        try {
            deactivate_plugins($plugin);
            echo "<div class='test-item success'>✅ Plugin disattivato: {$plugin}</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore disattivazione {$plugin}: " . $e->getMessage() . "</div>";
        }
    }
    echo "</div>";
} else {
    echo "<div class='test-item success'>✅ Nessun plugin FP Performance Suite attivo</div>";
}
echo "</div>";

// 2. ELIMINAZIONE OPZIONI
echo "<div class='section'>";
echo "<h2>🗑️ 2. Eliminazione Opzioni</h2>";

$fp_options = [
    'fp_performance_options',
    'fp_performance_cache_options',
    'fp_performance_database_options',
    'fp_performance_asset_options',
    'fp_performance_mobile_options',
    'fp_performance_security_options',
    'fp_performance_metrics_options',
    'fp_performance_error_options',
    'fp_performance_version',
    'fp_performance_activation_time',
    'fp_performance_last_update',
    'fp_performance_menu_added',
    'fp_performance_active',
    'fp_perf_suite_initialized'
];

$deleted_options = 0;
foreach ($fp_options as $option) {
    if (get_option($option) !== false) {
        try {
            delete_option($option);
            echo "<div class='test-item success'>✅ Opzione eliminata: {$option}</div>";
            $deleted_options++;
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore eliminazione {$option}: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='test-item info'>📋 Opzione non trovata: {$option}</div>";
    }
}

echo "<div class='test-item info'>📊 Opzioni eliminate: {$deleted_options}</div>";
echo "</div>";

// 3. ELIMINAZIONE TRANSIENT
echo "<div class='section'>";
echo "<h2>⏰ 3. Eliminazione Transient</h2>";

global $wpdb;
$transient_query = "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_fp_%' OR option_name LIKE '_transient_timeout_fp_%' OR option_name LIKE 'fp_%'";
$transient_result = $wpdb->query($transient_query);

if ($transient_result !== false) {
    echo "<div class='test-item success'>✅ Transient eliminati: {$transient_result}</div>";
} else {
    echo "<div class='test-item warning'>⚠️ Nessun transient da eliminare</div>";
}
echo "</div>";

// 4. ELIMINAZIONE CRON JOBS
echo "<div class='section'>";
echo "<h2>⏰ 4. Eliminazione Cron Jobs</h2>";

$fp_cron_jobs = [
    'fp_performance_cleanup',
    'fp_performance_optimize',
    'fp_performance_update_metrics',
    'fp_performance_clear_cache',
    'fp_perf_suite_cleanup',
    'fp_perf_suite_optimize'
];

$cron_removed = 0;
foreach ($fp_cron_jobs as $cron_job) {
    if (wp_next_scheduled($cron_job)) {
        wp_clear_scheduled_hook($cron_job);
        echo "<div class='test-item success'>✅ Cron job rimosso: {$cron_job}</div>";
        $cron_removed++;
    }
}

echo "<div class='test-item info'>📊 Cron jobs rimossi: {$cron_removed}</div>";
echo "</div>";

// 5. ELIMINAZIONE FILE CACHE
echo "<div class='section'>";
echo "<h2>📁 5. Eliminazione File Cache</h2>";

$cache_directories = [
    WP_CONTENT_DIR . '/cache/fp-performance/',
    WP_CONTENT_DIR . '/uploads/fp-performance/',
    WP_CONTENT_DIR . '/fp-performance-cache/',
    WP_CONTENT_DIR . '/cache/fp-perf-suite/',
    WP_CONTENT_DIR . '/uploads/fp-perf-suite/'
];

$files_deleted = 0;
foreach ($cache_directories as $dir) {
    if (is_dir($dir)) {
        try {
            $files = glob($dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                    $files_deleted++;
                }
            }
            rmdir($dir);
            echo "<div class='test-item success'>✅ Directory eliminata: {$dir}</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore eliminazione {$dir}: " . $e->getMessage() . "</div>";
        }
    }
}

echo "<div class='test-item info'>📊 File eliminati: {$files_deleted}</div>";
echo "</div>";

// 6. ELIMINAZIONE LOG
echo "<div class='section'>";
echo "<h2>📝 6. Eliminazione Log</h2>";

$log_files = [
    WP_CONTENT_DIR . '/fp-performance.log',
    WP_CONTENT_DIR . '/fp-performance-error.log',
    WP_CONTENT_DIR . '/fp-performance-debug.log',
    WP_CONTENT_DIR . '/fp-perf-suite.log',
    WP_CONTENT_DIR . '/fp-perf-suite-error.log',
    WP_CONTENT_DIR . '/fp-perf-suite-debug.log'
];

$logs_deleted = 0;
foreach ($log_files as $log_file) {
    if (file_exists($log_file)) {
        try {
            unlink($log_file);
            echo "<div class='test-item success'>✅ Log eliminato: {$log_file}</div>";
            $logs_deleted++;
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore eliminazione {$log_file}: " . $e->getMessage() . "</div>";
        }
    }
}

echo "<div class='test-item info'>📊 Log eliminati: {$logs_deleted}</div>";
echo "</div>";

// 7. VERIFICA PULIZIA
echo "<div class='section'>";
echo "<h2>✅ 7. Verifica Pulizia</h2>";

// Verifica plugin attivi
$active_plugins_after = get_option('active_plugins', []);
$fp_plugins_after = [];
foreach ($active_plugins_after as $plugin) {
    if (strpos($plugin, 'fp-performance') !== false) {
        $fp_plugins_after[] = $plugin;
    }
}

if (empty($fp_plugins_after)) {
    echo "<div class='test-item success'>✅ Nessun plugin FP Performance Suite attivo</div>";
} else {
    echo "<div class='test-item error'>❌ Plugin FP Performance Suite ancora attivi: " . implode(', ', $fp_plugins_after) . "</div>";
}

// Verifica opzioni
$options_remaining = 0;
foreach ($fp_options as $option) {
    if (get_option($option) !== false) {
        $options_remaining++;
    }
}

if ($options_remaining === 0) {
    echo "<div class='test-item success'>✅ Nessuna opzione FP Performance Suite rimasta</div>";
} else {
    echo "<div class='test-item warning'>⚠️ {$options_remaining} opzioni FP Performance Suite ancora presenti</div>";
}

// Verifica classi
$fp_classes = ['FP_Performance_Suite', 'FP_Cache_Manager', 'FP_Database_Optimizer', 'FP\\PerfSuite\\Plugin'];
$classes_remaining = 0;
foreach ($fp_classes as $class) {
    if (class_exists($class)) {
        $classes_remaining++;
    }
}

if ($classes_remaining === 0) {
    echo "<div class='test-item success'>✅ Nessuna classe FP Performance Suite caricata</div>";
} else {
    echo "<div class='test-item warning'>⚠️ {$classes_remaining} classi FP Performance Suite ancora caricate</div>";
}
echo "</div>";

// 8. RACCOMANDAZIONI FINALI
echo "<div class='section'>";
echo "<h2>💡 8. Raccomandazioni Finali</h2>";

echo "<div class='success-box'>";
echo "<h3>🎉 Pulizia Completa Eseguita!</h3>";
echo "<p>Ora devi:</p>";
echo "<ol>";
echo "<li><strong>Elimina MANUALMENTE tutte le cartelle del plugin</strong></li>";
echo "<li><strong>Vai su Plugin → Tutti i plugin</strong></li>";
echo "<li><strong>Elimina TUTTE le versioni di FP Performance Suite</strong></li>";
echo "<li><strong>Riavvia WordPress</strong></li>";
echo "<li><strong>Installa UNA SOLA versione pulita</strong></li>";
echo "</ol>";
echo "</div>";

echo "<div class='test-item info'>📋 Dopo la pulizia manuale:</div>";
echo "<div class='test-item info'>• Installa SOLO una versione del plugin</div>";
echo "<div class='test-item info'>• Testa che funzioni correttamente</div>";
echo "<div class='test-item info'>• Non installare versioni multiple</div>";
echo "<div class='test-item info'>• Monitora che non si creino duplicati</div>";

echo "</div>";

echo "<h2>✅ Pulizia Emergenza Completata!</h2>";
echo "<p>Ora devi eliminare MANUALMENTE tutte le cartelle del plugin e reinstallare UNA SOLA versione.</p>";
?>
