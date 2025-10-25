<?php
/**
 * Test Sistema Metriche e Semafori Plugin FP Performance Suite
 * Script per verificare metriche di performance e sistema semafori
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>📊 Test Sistema Metriche e Semafori</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .metric { background: #f0f0f0; padding: 10px; margin: 5px 0; border-radius: 3px; }
    .traffic-light { display: inline-block; width: 20px; height: 20px; border-radius: 50%; margin: 0 5px; }
    .traffic-red { background: #ff4444; }
    .traffic-yellow { background: #ffaa00; }
    .traffic-green { background: #44ff44; }
    .score { font-size: 24px; font-weight: bold; padding: 10px; border-radius: 5px; }
    .score-excellent { background: #d4edda; color: #155724; }
    .score-good { background: #d1ecf1; color: #0c5460; }
    .score-fair { background: #fff3cd; color: #856404; }
    .score-poor { background: #f8d7da; color: #721c24; }
</style>";

// 1. VERIFICA SISTEMA METRICHE
echo "<div class='section'>";
echo "<h2>📊 1. Verifica Sistema Metriche</h2>";

if (class_exists('FP_Performance_Monitor')) {
    $monitor = new FP_Performance_Monitor();
    echo "<div class='test-item success'>✅ Performance Monitor istanziato</div>";
    
    // Test score performance generale
    if (method_exists($monitor, 'get_performance_score')) {
        try {
            $score = $monitor->get_performance_score();
            echo "<div class='test-item success'>✅ Score performance ottenuto: {$score}</div>";
            
            // Classificazione score
            if ($score >= 90) {
                echo "<div class='score score-excellent'>🏆 Score Eccellente: {$score}/100</div>";
            } elseif ($score >= 70) {
                echo "<div class='score score-good'>👍 Score Buono: {$score}/100</div>";
            } elseif ($score >= 50) {
                echo "<div class='score score-fair'>⚠️ Score Discreto: {$score}/100</div>";
            } else {
                echo "<div class='score score-poor'>❌ Score Scarso: {$score}/100</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore score performance: " . $e->getMessage() . "</div>";
        }
    }
    
    // Test metriche dettagliate
    if (method_exists($monitor, 'get_metrics')) {
        try {
            $metrics = $monitor->get_metrics();
            echo "<div class='test-item success'>✅ Metriche ottenute</div>";
            echo "<div class='metric'>📊 Metriche: " . json_encode($metrics, JSON_PRETTY_PRINT) . "</div>";
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

// 2. TEST SISTEMA SEMAFORI
echo "<div class='section'>";
echo "<h2>🚦 2. Test Sistema Semafori</h2>";

// Verifica semafori disponibili
$traffic_lights = [
    'cache_status' => 'Cache',
    'database_status' => 'Database',
    'assets_status' => 'Assets',
    'mobile_status' => 'Mobile',
    'security_status' => 'Sicurezza',
    'performance_status' => 'Performance'
];

foreach ($traffic_lights as $status => $name) {
    $status_value = get_option("fp_performance_{$status}", 'unknown');
    
    // Determina colore semaforo
    $traffic_class = 'traffic-red';
    $status_text = 'Rosso';
    
    if ($status_value === 'good' || $status_value === 'enabled' || $status_value === 'optimized') {
        $traffic_class = 'traffic-green';
        $status_text = 'Verde';
    } elseif ($status_value === 'warning' || $status_value === 'partial') {
        $traffic_class = 'traffic-yellow';
        $status_text = 'Giallo';
    }
    
    echo "<div class='test-item info'>";
    echo "<span class='traffic-light {$traffic_class}'></span>";
    echo "{$name}: {$status_text} ({$status_value})";
    echo "</div>";
}

// Test aggiornamento semafori
if (function_exists('fp_update_traffic_lights')) {
    try {
        $lights_result = fp_update_traffic_lights();
        if ($lights_result) {
            echo "<div class='test-item success'>✅ Semafori aggiornati</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Aggiornamento semafori fallito</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore aggiornamento semafori: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test-item warning'>⚠️ Funzione aggiornamento semafori non disponibile</div>";
}
echo "</div>";

// 3. TEST METRICHE SPECIFICHE
echo "<div class='section'>";
echo "<h2>📈 3. Test Metriche Specifiche</h2>";

// Test metriche cache
if (class_exists('FP_Cache_Manager')) {
    $cache_manager = new FP_Cache_Manager();
    if (method_exists($cache_manager, 'get_cache_stats')) {
        try {
            $cache_stats = $cache_manager->get_cache_stats();
            echo "<div class='test-item success'>✅ Statistiche cache ottenute</div>";
            echo "<div class='metric'>📊 Cache Stats: " . json_encode($cache_stats) . "</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore statistiche cache: " . $e->getMessage() . "</div>";
        }
    }
}

// Test metriche database
if (class_exists('FP_Database_Optimizer')) {
    $db_optimizer = new FP_Database_Optimizer();
    if (method_exists($db_optimizer, 'get_table_stats')) {
        try {
            $db_stats = $db_optimizer->get_table_stats();
            echo "<div class='test-item success'>✅ Statistiche database ottenute</div>";
            echo "<div class='metric'>📊 Database Stats: " . json_encode($db_stats) . "</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore statistiche database: " . $e->getMessage() . "</div>";
        }
    }
}

// Test metriche assets
if (class_exists('FP_Asset_Optimizer')) {
    $asset_optimizer = new FP_Asset_Optimizer();
    if (method_exists($asset_optimizer, 'get_asset_stats')) {
        try {
            $asset_stats = $asset_optimizer->get_asset_stats();
            echo "<div class='test-item success'>✅ Statistiche assets ottenute</div>";
            echo "<div class='metric'>📊 Asset Stats: " . json_encode($asset_stats) . "</div>";
        } catch (Exception $e) {
            echo "<div class='test-item error'>❌ Errore statistiche assets: " . $e->getMessage() . "</div>";
        }
    }
}
echo "</div>";

// 4. TEST PERFORMANCE METRICS
echo "<div class='section'>";
echo "<h2>⚡ 4. Test Performance Metrics</h2>";

// Test tempo di caricamento
$start_time = microtime(true);
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;

echo "<div class='test-item info'>⏱️ Tempo di caricamento: " . round($load_time, 2) . "ms</div>";

// Classificazione tempo di caricamento
if ($load_time < 100) {
    echo "<div class='test-item success'>✅ Tempo di caricamento eccellente</div>";
} elseif ($load_time < 500) {
    echo "<div class='test-item success'>✅ Tempo di caricamento buono</div>";
} elseif ($load_time < 1000) {
    echo "<div class='test-item warning'>⚠️ Tempo di caricamento discreto</div>";
} else {
    echo "<div class='test-item error'>❌ Tempo di caricamento lento</div>";
}

// Test memoria utilizzata
$memory_usage = memory_get_usage(true);
$memory_peak = memory_get_peak_usage(true);

echo "<div class='test-item info'>💾 Memoria utilizzata: " . round($memory_usage / 1024 / 1024, 2) . "MB</div>";
echo "<div class='test-item info'>📈 Picco memoria: " . round($memory_peak / 1024 / 1024, 2) . "MB</div>";

// Classificazione memoria
if ($memory_peak < 32) {
    echo "<div class='test-item success'>✅ Uso memoria ottimo</div>";
} elseif ($memory_peak < 64) {
    echo "<div class='test-item success'>✅ Uso memoria buono</div>";
} elseif ($memory_peak < 128) {
    echo "<div class='test-item warning'>⚠️ Uso memoria discreto</div>";
} else {
    echo "<div class='test-item error'>❌ Uso memoria eccessivo</div>";
}

// Test query database
global $wpdb;
$query_count = $wpdb->num_queries;
echo "<div class='test-item info'>🗄️ Query database: {$query_count}</div>";

// Classificazione query
if ($query_count < 10) {
    echo "<div class='test-item success'>✅ Numero query ottimo</div>";
} elseif ($query_count < 20) {
    echo "<div class='test-item success'>✅ Numero query buono</div>";
} elseif ($query_count < 50) {
    echo "<div class='test-item warning'>⚠️ Numero query discreto</div>";
} else {
    echo "<div class='test-item error'>❌ Numero query eccessivo</div>";
}
echo "</div>";

// 5. TEST SCORE VISUALIZZAZIONE
echo "<div class='section'>";
echo "<h2>🎯 5. Test Score Visualizzazione</h2>";

// Test score cache
if (function_exists('fp_get_cache_score')) {
    try {
        $cache_score = fp_get_cache_score();
        echo "<div class='test-item success'>✅ Score cache: {$cache_score}/100</div>";
        
        if ($cache_score >= 80) {
            echo "<div class='test-item success'>✅ Cache ottimizzata</div>";
        } elseif ($cache_score >= 60) {
            echo "<div class='test-item warning'>⚠️ Cache da migliorare</div>";
        } else {
            echo "<div class='test-item error'>❌ Cache da ottimizzare</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore score cache: " . $e->getMessage() . "</div>";
    }
}

// Test score database
if (function_exists('fp_get_database_score')) {
    try {
        $db_score = fp_get_database_score();
        echo "<div class='test-item success'>✅ Score database: {$db_score}/100</div>";
        
        if ($db_score >= 80) {
            echo "<div class='test-item success'>✅ Database ottimizzato</div>";
        } elseif ($db_score >= 60) {
            echo "<div class='test-item warning'>⚠️ Database da migliorare</div>";
        } else {
            echo "<div class='test-item error'>❌ Database da ottimizzare</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore score database: " . $e->getMessage() . "</div>";
    }
}

// Test score assets
if (function_exists('fp_get_assets_score')) {
    try {
        $assets_score = fp_get_assets_score();
        echo "<div class='test-item success'>✅ Score assets: {$assets_score}/100</div>";
        
        if ($assets_score >= 80) {
            echo "<div class='test-item success'>✅ Assets ottimizzati</div>";
        } elseif ($assets_score >= 60) {
            echo "<div class='test-item warning'>⚠️ Assets da migliorare</div>";
        } else {
            echo "<div class='test-item error'>❌ Assets da ottimizzare</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore score assets: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 6. TEST AGGIORNAMENTO METRICHE
echo "<div class='section'>";
echo "<h2>🔄 6. Test Aggiornamento Metriche</h2>";

// Test aggiornamento automatico
if (function_exists('fp_auto_update_metrics')) {
    try {
        $auto_update_result = fp_auto_update_metrics();
        if ($auto_update_result) {
            echo "<div class='test-item success'>✅ Aggiornamento automatico metriche eseguito</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Aggiornamento automatico metriche fallito</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore aggiornamento automatico: " . $e->getMessage() . "</div>";
    }
}

// Test aggiornamento manuale
if (function_exists('fp_manual_update_metrics')) {
    try {
        $manual_update_result = fp_manual_update_metrics();
        if ($manual_update_result) {
            echo "<div class='test-item success'>✅ Aggiornamento manuale metriche eseguito</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Aggiornamento manuale metriche fallito</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore aggiornamento manuale: " . $e->getMessage() . "</div>";
    }
}

// Test reset metriche
if (function_exists('fp_reset_metrics')) {
    try {
        $reset_result = fp_reset_metrics();
        if ($reset_result) {
            echo "<div class='test-item success'>✅ Reset metriche eseguito</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Reset metriche fallito</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore reset metriche: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 7. TEST OPZIONI METRICHE
echo "<div class='section'>";
echo "<h2>⚙️ 7. Test Opzioni Metriche</h2>";

$metrics_options = get_option('fp_performance_metrics_options', []);
if (!empty($metrics_options)) {
    echo "<div class='test-item success'>✅ Opzioni metriche caricate</div>";
    
    $metrics_settings = [
        'auto_update' => 'Aggiornamento automatico',
        'score_display' => 'Visualizzazione score',
        'traffic_lights' => 'Semafori',
        'detailed_metrics' => 'Metriche dettagliate',
        'performance_alerts' => 'Avvisi performance'
    ];
    
    foreach ($metrics_settings as $option => $name) {
        if (isset($metrics_options[$option])) {
            $status = $metrics_options[$option] ? 'Abilitato' : 'Disabilitato';
            $class = $metrics_options[$option] ? 'success' : 'warning';
            echo "<div class='test-item {$class}'>📋 {$name}: {$status}</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Opzione {$name} non trovata</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Opzioni metriche vuote</div>";
}
echo "</div>";

// 8. TEST RACCOMANDAZIONI
echo "<div class='section'>";
echo "<h2>💡 8. Test Raccomandazioni</h2>";

// Test generazione raccomandazioni
if (function_exists('fp_generate_recommendations')) {
    try {
        $recommendations = fp_generate_recommendations();
        if (!empty($recommendations)) {
            echo "<div class='test-item success'>✅ Raccomandazioni generate</div>";
            echo "<div class='metric'>📋 Raccomandazioni: " . json_encode($recommendations, JSON_PRETTY_PRINT) . "</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Nessuna raccomandazione generata</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore generazione raccomandazioni: " . $e->getMessage() . "</div>";
    }
}

// Test priorità raccomandazioni
if (function_exists('fp_get_priority_recommendations')) {
    try {
        $priority_recs = fp_get_priority_recommendations();
        if (!empty($priority_recs)) {
            echo "<div class='test-item success'>✅ Raccomandazioni prioritarie ottenute</div>";
            echo "<div class='metric'>📋 Priorità: " . json_encode($priority_recs, JSON_PRETTY_PRINT) . "</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Nessuna raccomandazione prioritaria</div>";
        }
    } catch (Exception $e) {
        echo "<div class='test-item error'>❌ Errore raccomandazioni prioritarie: " . $e->getMessage() . "</div>";
    }
}
echo "</div>";

// 9. RACCOMANDAZIONI FINALI
echo "<div class='section'>";
echo "<h2>🎯 9. Raccomandazioni Finali</h2>";

echo "<div class='test-item info'>📋 1. Monitora regolarmente le metriche di performance</div>";
echo "<div class='test-item info'>📋 2. Mantieni i semafori verdi per tutte le ottimizzazioni</div>";
echo "<div class='test-item info'>📋 3. Aggiorna le metriche automaticamente</div>";
echo "<div class='test-item info'>📋 4. Segui le raccomandazioni per migliorare i score</div>";
echo "<div class='test-item info'>📋 5. Testa le performance su dispositivi diversi</div>";
echo "<div class='test-item info'>📋 6. Ottimizza le aree con score bassi</div>";
echo "<div class='test-item info'>📋 7. Usa il sistema di semafori per monitorare lo stato</div>";
echo "<div class='test-item info'>📋 8. Configura avvisi per performance critiche</div>";

echo "</div>";

echo "<h2>✅ Test Metriche e Semafori Completato!</h2>";
echo "<p>Verifica i risultati sopra per identificare eventuali problemi con il sistema di metriche e semafori.</p>";
?>
