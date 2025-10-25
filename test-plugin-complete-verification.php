<?php
/**
 * Test Completo Plugin FP Performance Suite
 * Script per verificare tutte le funzionalità del plugin
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>🔍 Test Completo Plugin FP Performance Suite</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
</style>";

// 1. VERIFICA INSTALLAZIONE PLUGIN
echo "<div class='section'>";
echo "<h2>📦 1. Verifica Installazione Plugin</h2>";

if (class_exists('FP_Performance_Suite')) {
    echo "<div class='test-item success'>✅ Plugin FP_Performance_Suite caricato correttamente</div>";
    
    // Verifica versione
    if (defined('FP_PERFORMANCE_VERSION')) {
        echo "<div class='test-item info'>📋 Versione: " . FP_PERFORMANCE_VERSION . "</div>";
    }
    
    // Verifica istanza singleton
    $instance = FP_Performance_Suite::get_instance();
    if ($instance) {
        echo "<div class='test-item success'>✅ Istanza singleton creata correttamente</div>";
    } else {
        echo "<div class='test-item error'>❌ Errore creazione istanza singleton</div>";
    }
} else {
    echo "<div class='test-item error'>❌ Plugin FP_Performance_Suite NON trovato</div>";
}
echo "</div>";

// 2. VERIFICA CLASSI PRINCIPALI
echo "<div class='section'>";
echo "<h2>🏗️ 2. Verifica Classi Principali</h2>";

$classes_to_check = [
    'FP_Performance_Suite',
    'FP_Cache_Manager',
    'FP_Database_Optimizer', 
    'FP_Asset_Optimizer',
    'FP_Mobile_Optimizer',
    'FP_Performance_Monitor',
    'FP_Admin_Interface',
    'FP_Security_Manager'
];

foreach ($classes_to_check as $class) {
    if (class_exists($class)) {
        echo "<div class='test-item success'>✅ Classe {$class} disponibile</div>";
    } else {
        echo "<div class='test-item error'>❌ Classe {$class} NON trovata</div>";
    }
}
echo "</div>";

// 3. VERIFICA PAGINE ADMIN
echo "<div class='section'>";
echo "<h2>🎛️ 3. Verifica Pagine Admin</h2>";

// Verifica menu admin
if (function_exists('fp_performance_add_admin_menu')) {
    echo "<div class='test-item success'>✅ Funzione menu admin disponibile</div>";
} else {
    echo "<div class='test-item error'>❌ Funzione menu admin NON trovata</div>";
}

// Verifica hook admin
$admin_hooks = [
    'admin_menu',
    'admin_init',
    'admin_enqueue_scripts'
];

foreach ($admin_hooks as $hook) {
    if (has_action($hook, 'fp_performance_admin_init')) {
        echo "<div class='test-item success'>✅ Hook {$hook} registrato</div>";
    } else {
        echo "<div class='test-item warning'>⚠️ Hook {$hook} non registrato</div>";
    }
}
echo "</div>";

// 4. VERIFICA OTTIMIZZAZIONI CACHE
echo "<div class='section'>";
echo "<h2>⚡ 4. Verifica Ottimizzazioni Cache</h2>";

// Verifica cache manager
if (class_exists('FP_Cache_Manager')) {
    $cache_manager = new FP_Cache_Manager();
    echo "<div class='test-item success'>✅ Cache Manager istanziato</div>";
    
    // Test metodi cache
    $cache_methods = ['clear_cache', 'get_cache_stats', 'is_cache_enabled'];
    foreach ($cache_methods as $method) {
        if (method_exists($cache_manager, $method)) {
            echo "<div class='test-item success'>✅ Metodo cache {$method} disponibile</div>";
        } else {
            echo "<div class='test-item error'>❌ Metodo cache {$method} NON trovato</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Cache Manager NON disponibile</div>";
}
echo "</div>";

// 5. VERIFICA OTTIMIZZAZIONI DATABASE
echo "<div class='section'>";
echo "<h2>🗄️ 5. Verifica Ottimizzazioni Database</h2>";

if (class_exists('FP_Database_Optimizer')) {
    $db_optimizer = new FP_Database_Optimizer();
    echo "<div class='test-item success'>✅ Database Optimizer istanziato</div>";
    
    // Test metodi database
    $db_methods = ['optimize_tables', 'get_table_stats', 'clean_revisions'];
    foreach ($db_methods as $method) {
        if (method_exists($db_optimizer, $method)) {
            echo "<div class='test-item success'>✅ Metodo database {$method} disponibile</div>";
        } else {
            echo "<div class='test-item error'>❌ Metodo database {$method} NON trovato</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Database Optimizer NON disponibile</div>";
}
echo "</div>";

// 6. VERIFICA OTTIMIZZAZIONI ASSETS
echo "<div class='section'>";
echo "<h2>📁 6. Verifica Ottimizzazioni Assets</h2>";

if (class_exists('FP_Asset_Optimizer')) {
    $asset_optimizer = new FP_Asset_Optimizer();
    echo "<div class='test-item success'>✅ Asset Optimizer istanziato</div>";
    
    // Test metodi assets
    $asset_methods = ['optimize_css', 'optimize_js', 'minify_html'];
    foreach ($asset_methods as $method) {
        if (method_exists($asset_optimizer, $method)) {
            echo "<div class='test-item success'>✅ Metodo asset {$method} disponibile</div>";
        } else {
            echo "<div class='test-item error'>❌ Metodo asset {$method} NON trovato</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Asset Optimizer NON disponibile</div>";
}
echo "</div>";

// 7. VERIFICA OTTIMIZZAZIONI MOBILE
echo "<div class='section'>";
echo "<h2>📱 7. Verifica Ottimizzazioni Mobile</h2>";

if (class_exists('FP_Mobile_Optimizer')) {
    $mobile_optimizer = new FP_Mobile_Optimizer();
    echo "<div class='test-item success'>✅ Mobile Optimizer istanziato</div>";
    
    // Test metodi mobile
    $mobile_methods = ['optimize_images', 'lazy_load', 'mobile_css'];
    foreach ($mobile_methods as $method) {
        if (method_exists($mobile_optimizer, $method)) {
            echo "<div class='test-item success'>✅ Metodo mobile {$method} disponibile</div>";
        } else {
            echo "<div class='test-item error'>❌ Metodo mobile {$method} NON trovato</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Mobile Optimizer NON disponibile</div>";
}
echo "</div>";

// 8. VERIFICA SISTEMA METRICHE
echo "<div class='section'>";
echo "<h2>📊 8. Verifica Sistema Metriche</h2>";

if (class_exists('FP_Performance_Monitor')) {
    $monitor = new FP_Performance_Monitor();
    echo "<div class='test-item success'>✅ Performance Monitor istanziato</div>";
    
    // Test metodi monitor
    $monitor_methods = ['get_performance_score', 'get_metrics', 'update_metrics'];
    foreach ($monitor_methods as $method) {
        if (method_exists($monitor, $method)) {
            echo "<div class='test-item success'>✅ Metodo monitor {$method} disponibile</div>";
        } else {
            echo "<div class='test-item error'>❌ Metodo monitor {$method} NON trovato</div>";
        }
    }
} else {
    echo "<div class='test-item error'>❌ Performance Monitor NON disponibile</div>";
}
echo "</div>";

// 9. VERIFICA OPZIONI PLUGIN
echo "<div class='section'>";
echo "<h2>⚙️ 9. Verifica Opzioni Plugin</h2>";

$plugin_options = get_option('fp_performance_options', []);
if (!empty($plugin_options)) {
    echo "<div class='test-item success'>✅ Opzioni plugin caricate</div>";
    echo "<div class='test-item info'>📋 Numero opzioni: " . count($plugin_options) . "</div>";
    
    // Mostra opzioni principali
    $main_options = ['cache_enabled', 'database_optimization', 'asset_optimization', 'mobile_optimization'];
    foreach ($main_options as $option) {
        if (isset($plugin_options[$option])) {
            $value = $plugin_options[$option] ? 'Abilitato' : 'Disabilitato';
            echo "<div class='test-item info'>📋 {$option}: {$value}</div>";
        } else {
            echo "<div class='test-item warning'>⚠️ Opzione {$option} non trovata</div>";
        }
    }
} else {
    echo "<div class='test-item warning'>⚠️ Opzioni plugin vuote o non caricate</div>";
}
echo "</div>";

// 10. VERIFICA ERRORI PHP
echo "<div class='section'>";
echo "<h2>🐛 10. Verifica Errori PHP</h2>";

$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $errors = file_get_contents($error_log);
    if (strpos($errors, 'fp-performance') !== false) {
        echo "<div class='test-item error'>❌ Errori trovati nel log per fp-performance</div>";
        echo "<div class='test-item info'>📋 Controlla il file: {$error_log}</div>";
    } else {
        echo "<div class='test-item success'>✅ Nessun errore specifico per fp-performance nel log</div>";
    }
} else {
    echo "<div class='test-item info'>📋 Log errori non disponibile o vuoto</div>";
}
echo "</div>";

// 11. TEST FUNZIONALITÀ SPECIFICHE
echo "<div class='section'>";
echo "<h2>🧪 11. Test Funzionalità Specifiche</h2>";

// Test salvataggio opzioni
if (function_exists('fp_performance_save_options')) {
    echo "<div class='test-item success'>✅ Funzione salvataggio opzioni disponibile</div>";
} else {
    echo "<div class='test-item error'>❌ Funzione salvataggio opzioni NON trovata</div>";
}

// Test AJAX
if (wp_doing_ajax()) {
    echo "<div class='test-item info'>📋 Richiesta AJAX rilevata</div>";
} else {
    echo "<div class='test-item info'>📋 Richiesta normale (non AJAX)</div>";
}

// Test permessi utente
if (current_user_can('manage_options')) {
    echo "<div class='test-item success'>✅ Permessi amministratore verificati</div>";
} else {
    echo "<div class='test-item error'>❌ Permessi amministratore insufficienti</div>";
}
echo "</div>";

// 12. RACCOMANDAZIONI
echo "<div class='section'>";
echo "<h2>💡 12. Raccomandazioni</h2>";

echo "<div class='test-item info'>📋 1. Verifica che tutte le classi siano caricate correttamente</div>";
echo "<div class='test-item info'>📋 2. Controlla che le pagine admin siano accessibili</div>";
echo "<div class='test-item info'>📋 3. Testa le funzionalità di ottimizzazione</div>";
echo "<div class='test-item info'>📋 4. Verifica che non ci siano errori PHP</div>";
echo "<div class='test-item info'>📋 5. Controlla le opzioni del plugin</div>";

echo "</div>";

echo "<h2>✅ Test Completato!</h2>";
echo "<p>Esegui questo script nella directory del plugin o tramite WordPress per verificare tutte le funzionalità.</p>";
?>
