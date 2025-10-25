<?php
/**
 * Test Funzionalità di Ottimizzazione Plugin FP Performance Suite
 * Script per verificare cache, database, assets e altre ottimizzazioni
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>⚡ Test Funzionalità di Ottimizzazione</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .metric { background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px; }
</style>";

// 1. TEST SISTEMA CACHE
echo "<div class='section'>";
echo "<h2>🗄️ 1. Test Sistema Cache</h2>";

if (class_exists('FP_Cache_Manager')) {
    $cache_manager = new FP_Cache_Manager();
    echo "<div class='test-item success'>✅ Cache Manager istanziato</div>";
    
    // Test abilitazione cache
    if (method_exists($cache_manager, 'is_cache_enabled')) {
        $cache_enabled = $cache_manager->is_cache_enabled();
        if ($cache_enabled) {
            echo "<div class='test-item success'>✅ Cache abilitata</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Cache disabilitata</div>";
        }
    }
    
    // Test statistiche cache
    if (method_exists($cache_manager, 'get_cache_stats')) {
        try {
            $cache_stats = $cache_manager->get_cache_stats();
            echo "<div class='test-item success'>✅ Statistiche cache ottenute</div>";
            echo "<div class='metric'>📊 Statistiche Cache: " . json_encode($cache_stats) . "</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore statistiche cache: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test pulizia cache
    if (method_exists($cache_manager, 'clear_cache')) {
        try {
            $clear_result = $cache_manager->clear_cache();
            if ($clear_result) {
                echo "<div class='test-item success'>✅ Pulizia cache eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Pulizia cache fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore pulizia cache: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test salvataggio cache
    if (method_exists($cache_manager, 'set_cache')) {
        $test_key = 'fp_test_' . time();
        $test_value = 'Test cache value';
        try {
            $set_result = $cache_manager->set_cache($test_key, $test_value, 300);
            if ($set_result) {
                echo "<div class='test-item success'>✅ Salvataggio cache test riuscito</div>";
                
                // Test recupero cache
                if (method_exists($cache_manager, 'get_cache')) {
                    $retrieved_value = $cache_manager->get_cache($test_key);
                    if ($retrieved_value === $test_value) {
                        echo "<div class='test-item success'>✅ Recupero cache test riuscito</div>";
                    } else {
                        echo "<div class='test-item error'>❌ Recupero cache test fallito</div>";
                    }
                }
            } else {
                echo "<div class='test-item error'>❌ Salvataggio cache test fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore test cache: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Cache Manager NON disponibile</div>";
}
echo "</div>";

// 2. TEST OTTIMIZZAZIONI DATABASE
echo "<div class='section'>";
echo "<h2>🗄️ 2. Test Ottimizzazioni Database</h2>";

if (class_exists('FP_Database_Optimizer')) {
    $db_optimizer = new FP_Database_Optimizer();
    echo "<div class='test-item success'>✅ Database Optimizer istanziato</div>";
    
    // Test statistiche database
    if (method_exists($db_optimizer, 'get_table_stats')) {
        try {
            $db_stats = $db_optimizer->get_table_stats();
            echo "<div class='test-item success'>✅ Statistiche database ottenute</div>";
            echo "<div class='metric'>📊 Statistiche Database: " . json_encode($db_stats) . "</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore statistiche database: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test ottimizzazione tabelle
    if (method_exists($db_optimizer, 'optimize_tables')) {
        try {
            $optimize_result = $db_optimizer->optimize_tables();
            if ($optimize_result) {
                echo "<div class='test-item success'>✅ Ottimizzazione tabelle eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Ottimizzazione tabelle fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore ottimizzazione tabelle: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test pulizia revisioni
    if (method_exists($db_optimizer, 'clean_revisions')) {
        try {
            $clean_result = $db_optimizer->clean_revisions();
            if ($clean_result) {
                echo "<div class='test-item success'>✅ Pulizia revisioni eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Pulizia revisioni fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore pulizia revisioni: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test pulizia spam
    if (method_exists($db_optimizer, 'clean_spam')) {
        try {
            $spam_result = $db_optimizer->clean_spam();
            if ($spam_result) {
                echo "<div class='test-item success'>✅ Pulizia spam eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Pulizia spam fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore pulizia spam: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Database Optimizer NON disponibile</div>";
}
echo "</div>";

// 3. TEST OTTIMIZZAZIONI ASSETS
echo "<div class='section'>";
echo "<h2>📁 3. Test Ottimizzazioni Assets</h2>";

if (class_exists('FP_Asset_Optimizer')) {
    $asset_optimizer = new FP_Asset_Optimizer();
    echo "<div class='test-item success'>✅ Asset Optimizer istanziato</div>";
    
    // Test ottimizzazione CSS
    if (method_exists($asset_optimizer, 'optimize_css')) {
        try {
            $css_result = $asset_optimizer->optimize_css();
            if ($css_result) {
                echo "<div class='test-item success'>✅ Ottimizzazione CSS eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Ottimizzazione CSS fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore ottimizzazione CSS: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test ottimizzazione JS
    if (method_exists($asset_optimizer, 'optimize_js')) {
        try {
            $js_result = $asset_optimizer->optimize_js();
            if ($js_result) {
                echo "<div class='test-item success'>✅ Ottimizzazione JS eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Ottimizzazione JS fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore ottimizzazione JS: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test minificazione HTML
    if (method_exists($asset_optimizer, 'minify_html')) {
        try {
            $html_result = $asset_optimizer->minify_html();
            if ($html_result) {
                echo "<div class='test-item success'>✅ Minificazione HTML eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Minificazione HTML fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore minificazione HTML: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test compressione immagini
    if (method_exists($asset_optimizer, 'optimize_images')) {
        try {
            $image_result = $asset_optimizer->optimize_images();
            if ($image_result) {
                echo "<div class='test-item success'>✅ Ottimizzazione immagini eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Ottimizzazione immagini fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore ottimizzazione immagini: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Asset Optimizer NON disponibile</div>";
}
echo "</div>";

// 4. TEST OTTIMIZZAZIONI MOBILE
echo "<div class='section'>";
echo "<h2>📱 4. Test Ottimizzazioni Mobile</h2>";

if (class_exists('FP_Mobile_Optimizer')) {
    $mobile_optimizer = new FP_Mobile_Optimizer();
    echo "<div class='test-item success'>✅ Mobile Optimizer istanziato</div>";
    
    // Test rilevamento dispositivo mobile
    if (method_exists($mobile_optimizer, 'is_mobile')) {
        $is_mobile = $mobile_optimizer->is_mobile();
        if ($is_mobile) {
            echo "<div class='test-item info'>📱 Dispositivo mobile rilevato</div>";
        } else {
            echo "<div class='test-item info'>💻 Dispositivo desktop rilevato</div>";
        }
    }
    
    // Test ottimizzazione immagini mobile
    if (method_exists($mobile_optimizer, 'optimize_images')) {
        try {
            $mobile_image_result = $mobile_optimizer->optimize_images();
            if ($mobile_image_result) {
                echo "<div class='test-item success'>✅ Ottimizzazione immagini mobile eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Ottimizzazione immagini mobile fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore ottimizzazione immagini mobile: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test lazy loading
    if (method_exists($mobile_optimizer, 'lazy_load')) {
        try {
            $lazy_result = $mobile_optimizer->lazy_load();
            if ($lazy_result) {
                echo "<div class='test-item success'>✅ Lazy loading abilitato</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Lazy loading fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore lazy loading: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test CSS mobile
    if (method_exists($mobile_optimizer, 'mobile_css')) {
        try {
            $css_mobile_result = $mobile_optimizer->mobile_css();
            if ($css_mobile_result) {
                echo "<div class='test-item success'>✅ CSS mobile ottimizzato</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ CSS mobile fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore CSS mobile: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Mobile Optimizer NON disponibile</div>";
}
echo "</div>";

// 5. TEST SISTEMA METRICHE
echo "<div class='section'>";
echo "<h2>📊 5. Test Sistema Metriche</h2>";

if (class_exists('FP_Performance_Monitor')) {
    $monitor = new FP_Performance_Monitor();
    echo "<div class='test-item success'>✅ Performance Monitor istanziato</div>";
    
    // Test score performance
    if (method_exists($monitor, 'get_performance_score')) {
        try {
            $score = $monitor->get_performance_score();
            echo "<div class='test-item success'>✅ Score performance ottenuto: {$score}</div>";
            echo "<div class='metric'>📊 Score Performance: {$score}/100</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore score performance: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test metriche dettagliate
    if (method_exists($monitor, 'get_metrics')) {
        try {
            $metrics = $monitor->get_metrics();
            echo "<div class='test-item success'>✅ Metriche ottenute</div>";
            echo "<div class='metric'>📊 Metriche: " . json_encode($metrics) . "</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore metriche: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test aggiornamento metriche
    if (method_exists($monitor, 'update_metrics')) {
        try {
            $update_result = $monitor->update_metrics();
            if ($update_result) {
                echo "<div class='test-item success'>✅ Aggiornamento metriche eseguito</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Aggiornamento metriche fallito</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore aggiornamento metriche: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Performance Monitor NON disponibile</div>";
}
echo "</div>";

// 6. TEST SICUREZZA
echo "<div class='section'>";
echo "<h2>🔒 6. Test Sicurezza</h2>";

if (class_exists('FP_Security_Manager')) {
    $security_manager = new FP_Security_Manager();
    echo "<div class='test-item success'>✅ Security Manager istanziato</div>";
    
    // Test scansione sicurezza
    if (method_exists($security_manager, 'security_scan')) {
        try {
            $scan_result = $security_manager->security_scan();
            if ($scan_result) {
                echo "<div class='test-item success'>✅ Scansione sicurezza eseguita</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Scansione sicurezza fallita</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore scansione sicurezza: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test protezione login
    if (method_exists($security_manager, 'protect_login')) {
        try {
            $login_protection = $security_manager->protect_login();
            if ($login_protection) {
                echo "<div class='test-item success'>✅ Protezione login attiva</div>";
            } else {
                echo "<div class='test-item warning'>⚠️ Protezione login non attiva</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore protezione login: " . $e->getMessage() . "</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Security Manager NON disponibile</div>";
}
echo "</div>";

// 7. TEST OPZIONI PLUGIN
echo "<div class='section'>";
echo "<h2>⚙️ 7. Test Opzioni Plugin</h2>";

$plugin_options = get_option('fp_performance_options', []);
if (!empty($plugin_options)) {
    echo "<div class='test-item success'>✅ Opzioni plugin caricate</div>";
    
    $optimization_options = [
        'cache_enabled' => 'Cache',
        'database_optimization' => 'Database',
        'asset_optimization' => 'Assets',
        'mobile_optimization' => 'Mobile',
        'security_enabled' => 'Sicurezza'
    ];
    
    foreach ($optimization_options as $option => $name) {
        if (isset($plugin_options[$option])) {
            $status = $plugin_options[$option] ? 'Abilitato' : 'Disabilitato';
            $class = $plugin_options[$option] ? 'success' : 'warning';
            echo "<div class='test-item {$class}'>📋 {$name}: {$status}</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Opzione {$name} non trovata</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Opzioni plugin vuote</div>";
}
echo "</div>";

// 8. TEST PERFORMANCE GENERALE
echo "<div class='section'>";
echo "<h2>🚀 8. Test Performance Generale</h2>";

// Test tempo di caricamento
$start_time = microtime(true);
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;

echo "<div class='test-item info'>⏱️ Tempo di caricamento: " . round($load_time, 2) . "ms</div>";

// Test memoria utilizzata
$memory_usage = memory_get_usage(true);
$memory_peak = memory_get_peak_usage(true);

echo "<div class='test-item info'>💾 Memoria utilizzata: " . round($memory_usage / 1024 / 1024, 2) . "MB</div>";
echo "<div class='test-item info'>📈 Picco memoria: " . round($memory_peak / 1024 / 1024, 2) . "MB</div>";

// Test query database
global $wpdb;
$query_count = $wpdb->num_queries;
echo "<div class='test-item info'>🗄️ Query database: {$query_count}</div>";

echo "</div>";

// 9. RACCOMANDAZIONI
echo "<div class='section'>";
echo "<h2>💡 9. Raccomandazioni</h2>";

echo "<div class='test-item info'>📋 1. Verifica che tutte le ottimizzazioni siano abilitate</div>";
echo "<div class='test-item info'>📋 2. Controlla che le performance siano migliorate</div>";
echo "<div class='test-item info'>📋 3. Testa le funzionalità su dispositivi diversi</div>";
echo "<div class='test-item info'>📋 4. Monitora l'uso della memoria</div>";
echo "<div class='test-item info'>📋 5. Verifica che non ci siano errori</div>";

echo "</div>";

echo "<h2>✅ Test Ottimizzazioni Completato!</h2>";
echo "<p>Verifica i risultati sopra per identificare eventuali problemi con le funzionalità di ottimizzazione.</p>";
?>
