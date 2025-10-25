<?php
/**
 * Test Gestione Errori e Logging Plugin FP Performance Suite
 * Script per verificare sistema di errori e logging
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>🐛 Test Gestione Errori e Logging</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .log-entry { background: #f8f9fa; padding: 8px; margin: 5px 0; border-left: 4px solid #007cba; font-family: monospace; }
    .error-entry { border-left-color: #dc3545; }
    .warning-entry { border-left-color: #ffc107; }
    .info-entry { border-left-color: #17a2b8; }
</style>";

// 1. VERIFICA SISTEMA LOGGING
echo "<div class='section'>";
echo "<h2>📝 1. Verifica Sistema Logging</h2>";

// Verifica log file
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    echo "<div class='test-item success'>✅ File log trovato: {$log_file}</div>";
    
    // Verifica dimensione log
    $log_size = filesize($log_file);
    echo "<div class='test-item info'>📊 Dimensione log: " . round($log_size / 1024, 2) . "KB</div>";
    
    if ($log_size > 1024 * 1024) { // 1MB
        echo "<div class='test-item warning'>⚠️ Log file molto grande, considera la pulizia</div>";
    }
    
    // Verifica permessi scrittura
    if (is_writable($log_file)) {
        echo "<div class='test-item success'>✅ Log file scrivibile</div>";
    } else {
        echo "<div class='test-item error'>❌ Log file non scrivibile</div>";
    }
} else {
    echo "<div class='test-item warning'>⚠️ File log non trovato</div>";
}

// Verifica debug WordPress
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo "<div class='test-item success'>✅ Debug WordPress abilitato</div>";
} else {
    echo "<div class='test-item warning'>⚠️ Debug WordPress disabilitato</div>";
}

if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    echo "<div class='test-item success'>✅ Log WordPress abilitato</div>";
} else {
    echo "<div class='test-item warning'>⚠️ Log WordPress disabilitato</div>";
}

if (defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
    echo "<div class='test-item warning'>⚠️ Display errori abilitato (disabilita in produzione)</div>";
} else {
    echo "<div class='test-item success'>✅ Display errori disabilitato</div>";
}
echo "</div>";

// 2. TEST LOGGING PLUGIN
echo "<div class='section'>";
echo "<h2>🔍 2. Test Logging Plugin</h2>";

// Test funzioni di logging
$logging_functions = [
    'fp_log_error' => 'Log errori',
    'fp_log_warning' => 'Log warning',
    'fp_log_info' => 'Log informazioni',
    'fp_log_debug' => 'Log debug'
];

foreach ($logging_functions as $function => $description) {
    if (function_exists($function)) {
        echo "<div class='test-item success'>✅ {$description} disponibile</div>";
        
        // Test scrittura log
        try {
            $test_message = "Test {$description} - " . date('Y-m-d H:i:s');
            $log_result = call_user_func($function, $test_message);
            if ($log_result) {
                echo "<div class='test-item success'>✅ Test {$description} riuscito</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Test {$description} fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore test {$description}: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='test-item error'>❌ {$description} NON disponibile</div>";
    }
}
echo "</div>";

// 3. TEST GESTIONE ERRORI
echo "<div class='section'>";
echo "<h2>⚠️ 3. Test Gestione Errori</h2>";

// Test error handler
if (function_exists('fp_error_handler')) {
    echo "<div class='test-item success'>✅ Error handler disponibile</div>";
    
    // Test gestione errori
    try {
        $test_error = fp_error_handler(E_USER_ERROR, 'Test error message', __FILE__, __LINE__);
        echo "<div class='test-item success'>✅ Error handler funzionante</div>";
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore error handler: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item error'>❌ Error handler NON disponibile</div>";
}

// Test exception handler
if (function_exists('fp_exception_handler')) {
    echo "<div class='test-item success'>✅ Exception handler disponibile</div>";
    
    // Test gestione eccezioni
    try {
        $test_exception = new Exception('Test exception message');
        fp_exception_handler($test_exception);
        echo "<div class='test-item success'>✅ Exception handler funzionante</div>";
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore exception handler: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item error'>❌ Exception handler NON disponibile</div>";
}

// Test shutdown handler
if (function_exists('fp_shutdown_handler')) {
    echo "<div class='test-item success'>✅ Shutdown handler disponibile</div>";
} else {
    echo "<div class='test-item error'>❌ Shutdown handler NON disponibile</div>";
}
echo "</div>";

// 4. TEST ERRORI SPECIFICI PLUGIN
echo "<div class='section'>";
echo "<h2>🔧 4. Test Errori Specifici Plugin</h2>";

// Test errori cache
if (function_exists('fp_handle_cache_error')) {
    try {
        $cache_error_result = fp_handle_cache_error('Test cache error');
        if ($cache_error_result) {
            echo "<div class='test-item success'>✅ Gestione errori cache funzionante</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Gestione errori cache fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore gestione cache: " . $e->getMessage() . "</div>";
    }
}

// Test errori database
if (function_exists('fp_handle_database_error')) {
    try {
        $db_error_result = fp_handle_database_error('Test database error');
        if ($db_error_result) {
            echo "<div class='test-item success'>✅ Gestione errori database funzionante</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Gestione errori database fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore gestione database: " . $e->getMessage() . "</div>";
    }
}

// Test errori assets
if (function_exists('fp_handle_asset_error')) {
    try {
        $asset_error_result = fp_handle_asset_error('Test asset error');
        if ($asset_error_result) {
            echo "<div class='test-item success'>✅ Gestione errori assets funzionante</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Gestione errori assets fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore gestione assets: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 5. TEST RECUPERO ERRORI
echo "<div class='section'>";
echo "<h2>🔄 5. Test Recupero Errori</h2>";

// Test recupero errori critici
if (function_exists('fp_recover_critical_errors')) {
    try {
        $recovery_result = fp_recover_critical_errors();
        if ($recovery_result) {
            echo "<div class='test-item success'>✅ Recupero errori critici eseguito</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Recupero errori critici fallito</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore recupero critico: " . $e->getMessage() . "</div>";
    }
}

// Test recupero errori plugin
if (function_exists('fp_recover_plugin_errors')) {
    try {
        $plugin_recovery_result = fp_recover_plugin_errors();
        if ($plugin_recovery_result) {
            echo "<div class='test-item success'>✅ Recupero errori plugin eseguito</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Recupero errori plugin fallito</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore recupero plugin: " . $e->getMessage() . "</div>";
    }
}

// Test reset errori
if (function_exists('fp_reset_errors')) {
    try {
        $reset_result = fp_reset_errors();
        if ($reset_result) {
            echo "<div class='test-item success'>✅ Reset errori eseguito</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Reset errori fallito</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore reset: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 6. TEST ANALISI LOG
echo "<div class='section'>";
echo "<h2>📊 6. Test Analisi Log</h2>";

// Test analisi errori
if (function_exists('fp_analyze_errors')) {
    try {
        $error_analysis = fp_analyze_errors();
        if (!empty($error_analysis)) {
            echo "<div class='test-item success'>✅ Analisi errori eseguita</div>";
            echo "<div class='log-entry'>📊 Analisi: " . json_encode($error_analysis, JSON_PRETTY_PRINT) . "</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Nessun errore da analizzare</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore analisi: " . $e->getMessage() . "</div>";
    }
}

// Test statistiche errori
if (function_exists('fp_get_error_stats')) {
    try {
        $error_stats = fp_get_error_stats();
        if (!empty($error_stats)) {
            echo "<div class='test-item success'>✅ Statistiche errori ottenute</div>";
            echo "<div class='log-entry'>📊 Statistiche: " . json_encode($error_stats, JSON_PRETTY_PRINT) . "</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Nessuna statistica errore disponibile</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore statistiche: " . $e->getMessage() . "</div>";
    }
}

// Test filtri errori
if (function_exists('fp_filter_errors')) {
    try {
        $filtered_errors = fp_filter_errors(['level' => 'error', 'plugin' => 'fp-performance']);
        if (!empty($filtered_errors)) {
            echo "<div class='test-item success'>✅ Filtri errori funzionanti</div>";
            echo "<div class='log-entry'>📊 Errori filtrati: " . count($filtered_errors) . " trovati</div>";
        } else {
            echo "<div class='test-item info'>📋 Nessun errore filtrato trovato</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore filtri: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 7. TEST NOTIFICHE ERRORI
echo "<div class='section'>";
echo "<h2>🔔 7. Test Notifiche Errori</h2>";

// Test notifiche admin
if (function_exists('fp_notify_admin_errors')) {
    try {
        $notification_result = fp_notify_admin_errors();
        if ($notification_result) {
            echo "<div class='test-item success'>✅ Notifiche admin funzionanti</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Notifiche admin fallite</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore notifiche admin: " . $e->getMessage() . "</div>";
    }
}

// Test notifiche email
if (function_exists('fp_notify_email_errors')) {
    try {
        $email_result = fp_notify_email_errors();
        if ($email_result) {
            echo "<div class='test-item success'>✅ Notifiche email funzionanti</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Notifiche email fallite</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore notifiche email: " . $e->getMessage() . "</div>";
    }
}

// Test notifiche critiche
if (function_exists('fp_notify_critical_errors')) {
    try {
        $critical_result = fp_notify_critical_errors();
        if ($critical_result) {
            echo "<div class='test-item success'>✅ Notifiche critiche funzionanti</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Notifiche critiche fallite</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore notifiche critiche: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 8. TEST PULIZIA LOG
echo "<div class='section'>";
echo "<h2>🧹 8. Test Pulizia Log</h2>";

// Test pulizia log vecchi
if (function_exists('fp_clean_old_logs')) {
    try {
        $clean_result = fp_clean_old_logs();
        if ($clean_result) {
            echo "<div class='test-item success'>✅ Pulizia log vecchi eseguita</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Pulizia log vecchi fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore pulizia log: " . $e->getMessage() . "</div>";
    }
}

// Test rotazione log
if (function_exists('fp_rotate_logs')) {
    try {
        $rotate_result = fp_rotate_logs();
        if ($rotate_result) {
            echo "<div class='test-item success'>✅ Rotazione log eseguita</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Rotazione log fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore rotazione log: " . $e->getMessage() . "</div>";
    }
}

// Test compressione log
if (function_exists('fp_compress_logs')) {
    try {
        $compress_result = fp_compress_logs();
        if ($compress_result) {
            echo "<div class='test-item success'>✅ Compressione log eseguita</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Compressione log fallita</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore compressione log: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 9. TEST OPZIONI ERRORI
echo "<div class='section'>";
echo "<h2>⚙️ 9. Test Opzioni Errori</h2>";

$error_options = get_option('fp_performance_error_options', []);
if (!empty($error_options)) {
    echo "<div class='test-item success'>✅ Opzioni errori caricate</div>";
    
    $error_settings = [
        'error_logging' => 'Logging errori',
        'error_notifications' => 'Notifiche errori',
        'error_recovery' => 'Recupero errori',
        'error_analysis' => 'Analisi errori',
        'log_rotation' => 'Rotazione log',
        'log_compression' => 'Compressione log'
    ];
    
    foreach ($error_settings as $option => $name) {
        if (isset($error_options[$option])) {
            $status = $error_options[$option] ? 'Abilitato' : 'Disabilitato';
            $class = $error_options[$option] ? 'success' : 'warning';
            echo "<div class='test-item {$class}'>📋 {$name}: {$status}</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Opzione {$name} non trovata</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Opzioni errori vuote</div>";
}
echo "</div>";

// 10. RACCOMANDAZIONI ERRORI
echo "<div class='section'>";
echo "<h2>💡 10. Raccomandazioni Errori</h2>";

echo "<div class='test-item info'>📋 1. Abilita il logging degli errori</div>";
echo "<div class='test-item info'>📋 2. Configura notifiche per errori critici</div>";
echo "<div class='test-item info'>📋 3. Monitora regolarmente i log</div>";
echo "<div class='test-item info'>📋 4. Implementa recupero automatico degli errori</div>";
echo "<div class='test-item info'>📋 5. Configura rotazione e pulizia dei log</div>";
echo "<div class='test-item info'>📋 6. Analizza gli errori per identificare pattern</div>";
echo "<div class='test-item info'>📋 7. Testa il sistema di gestione errori</div>";
echo "<div class='test-item info'>📋 8. Mantieni backup dei log importanti</div>";

echo "</div>";

echo "<h2>✅ Test Gestione Errori Completato!</h2>";
echo "<p>Verifica i risultati sopra per identificare eventuali problemi con il sistema di gestione errori e logging.</p>";
?>
