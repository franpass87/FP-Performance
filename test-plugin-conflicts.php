<?php
/**
 * Test Conflitti Plugin FP Performance Suite
 * Script per identificare e risolvere conflitti tra versioni multiple
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h1>⚠️ Test Conflitti Plugin FP Performance Suite</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { color: blue; }
    .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
    .test-item { margin: 10px 0; padding: 5px; }
    .conflict { background: #fff3cd; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px; }
    .solution { background: #d1ecf1; padding: 10px; border: 1px solid #bee5eb; border-radius: 5px; }
</style>";

// 1. VERIFICA PLUGIN ATTIVI
echo "<div class='section'>";
echo "<h2>🔍 1. Verifica Plugin Attivi</h2>";

$active_plugins = get_option('active_plugins', []);
$fp_plugins_found = [];

foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'fp-performance') !== false) {
        $fp_plugins_found[] = $plugin;
        echo "<div class='test-item error'>❌ Plugin attivo: {$plugin}</div>";
    }
}

if (count($fp_plugins_found) > 1) {
    echo "<div class='conflict'>";
    echo "<h3>⚠️ CONFLITTO RILEVATO!</h3>";
    echo "<p>Hai " . count($fp_plugins_found) . " versioni di FP Performance Suite attive contemporaneamente!</p>";
    echo "<p>Questo causa:</p>";
    echo "<ul>";
    echo "<li>❌ Pagine vuote</li>";
    echo "<li>❌ Errori di inizializzazione</li>";
    echo "<li>❌ Conflitti tra classi</li>";
    echo "<li>❌ Funzionalità duplicate</li>";
    echo "</ul>";
    echo "</div>";
} elseif (count($fp_plugins_found) === 1) {
    echo "<div class='test-item success'>✅ Solo una versione attiva</div>";
} else {
    echo "<div class='test-item warning'>⚠️ Nessuna versione attiva</div>";
}
echo "</div>";

// 2. VERIFICA PLUGIN INSTALLATI
echo "<div class='section'>";
echo "<h2>📦 2. Verifica Plugin Installati</h2>";

$all_plugins = get_plugins();
$fp_installed_plugins = [];

foreach ($all_plugins as $plugin_file => $plugin_data) {
    if (strpos($plugin_file, 'fp-performance') !== false || 
        strpos($plugin_data['Name'], 'FP Performance') !== false) {
        $fp_installed_plugins[] = [
            'file' => $plugin_file,
            'name' => $plugin_data['Name'],
            'version' => $plugin_data['Version'],
            'active' => is_plugin_active($plugin_file)
        ];
    }
}

if (count($fp_installed_plugins) > 1) {
    echo "<div class='conflict'>";
    echo "<h3>⚠️ MULTIPLE VERSIONI INSTALLATE!</h3>";
    echo "<p>Trovate " . count($fp_installed_plugins) . " versioni installate:</p>";
    
    foreach ($fp_installed_plugins as $plugin) {
        $status = $plugin['active'] ? '🟢 ATTIVO' : '🔴 INATTIVO';
        echo "<div class='test-item info'>📋 {$plugin['name']} v{$plugin['version']} - {$status}</div>";
        echo "<div class='test-item info'>📁 File: {$plugin['file']}</div>";
    }
    echo "</div>";
} else {
    echo "<div class='test-item success'>✅ Solo una versione installata</div>";
}
echo "</div>";

// 3. VERIFICA CONFLITTI CLASSI
echo "<div class='section'>";
echo "<h2>🏗️ 3. Verifica Conflitti Classi</h2>";

$fp_classes = [
    'FP_Performance_Suite',
    'FP_Cache_Manager',
    'FP_Database_Optimizer',
    'FP_Asset_Optimizer',
    'FP_Mobile_Optimizer',
    'FP_Performance_Monitor'
];

$conflicts_found = [];

foreach ($fp_classes as $class) {
    if (class_exists($class)) {
        echo "<div class='test-item success'>✅ Classe {$class} disponibile</div>";
        
        // Verifica se ci sono istanze multiple
        $reflection = new ReflectionClass($class);
        if ($reflection->hasMethod('get_instance')) {
            try {
                $instance1 = $class::get_instance();
                $instance2 = $class::get_instance();
                if ($instance1 !== $instance2) {
                    $conflicts_found[] = $class;
                    echo "<div class='test-item error'>❌ Conflitto istanze multiple per {$class}</div>";
                }
            } catch (Exception $e) {
                echo "<div class='test-item error'>❌ Errore verifica istanze {$class}: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        echo "<div class='test-item warning'>⚠️ Classe {$class} NON disponibile</div>";
    }
}

if (!empty($conflicts_found)) {
    echo "<div class='conflict'>";
    echo "<h3>⚠️ CONFLITTI CLASSI RILEVATI!</h3>";
    echo "<p>Classi con conflitti: " . implode(', ', $conflicts_found) . "</p>";
    echo "</div>";
}
echo "</div>";

// 4. VERIFICA HOOK DUPLICATI
echo "<div class='section'>";
echo "<h2>🔗 4. Verifica Hook Duplicati</h2>";

$fp_hooks = [
    'admin_menu',
    'admin_init',
    'wp_enqueue_scripts',
    'wp_ajax_fp_performance_save'
];

foreach ($fp_hooks as $hook) {
    $hook_functions = $GLOBALS['wp_filter'][$hook] ?? [];
    $fp_hook_count = 0;
    
    foreach ($hook_functions as $priority => $functions) {
        foreach ($functions as $function) {
            if (is_array($function['function']) && 
                is_object($function['function'][0]) && 
                strpos(get_class($function['function'][0]), 'FP_') === 0) {
                $fp_hook_count++;
            } elseif (is_string($function['function']) && 
                      strpos($function['function'], 'fp_') === 0) {
                $fp_hook_count++;
            }
        }
    }
    
    if ($fp_hook_count > 1) {
        echo "<div class='test-item error'>❌ Hook {$hook} registrato {$fp_hook_count} volte</div>";
    } elseif ($fp_hook_count === 1) {
        echo "<div class='test-item success'>✅ Hook {$hook} registrato correttamente</div>";
    } else {
        echo "<div class='test-item warning'>⚠️ Hook {$hook} non registrato</div>";
    }
}
echo "</div>";

// 5. VERIFICA OPZIONI DUPLICATE
echo "<div class='section'>";
echo "<h2>⚙️ 5. Verifica Opzioni Duplicate</h2>";

$fp_options = [
    'fp_performance_options',
    'fp_performance_cache_options',
    'fp_performance_database_options',
    'fp_performance_asset_options',
    'fp_performance_mobile_options'
];

foreach ($fp_options as $option) {
    $option_value = get_option($option);
    if ($option_value !== false) {
        echo "<div class='test-item success'>✅ Opzione {$option} presente</div>";
        
        // Verifica se l'opzione è un array (normale) o stringa (possibile duplicato)
        if (is_string($option_value) && strpos($option_value, 'serialize') !== false) {
            echo "<div class='test-item error'>❌ Opzione {$option} potrebbe essere corrotta</div>";
        }
    } else {
        echo "<div class='test-item warning'>⚠️ Opzione {$option} non trovata</div>";
    }
}
echo "</div>";

// 6. SOLUZIONI RACCOMANDATE
echo "<div class='section'>";
echo "<h2>💡 6. Soluzioni Raccomandate</h2>";

if (count($fp_plugins_found) > 1) {
    echo "<div class='solution'>";
    echo "<h3>🔧 SOLUZIONE IMMEDIATA</h3>";
    echo "<ol>";
    echo "<li><strong>Disattiva TUTTI i plugin FP Performance Suite</strong></li>";
    echo "<li><strong>Elimina le versioni duplicate</strong> (mantieni solo 1)</li>";
    echo "<li><strong>Attiva SOLO la versione Safe Mode</strong></li>";
    echo "<li><strong>Testa le funzionalità</strong></li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='solution'>";
echo "<h3>📋 PROCEDURA DETTAGLIATA</h3>";
echo "<ol>";
echo "<li><strong>Vai su Plugin → Tutti i plugin</strong></li>";
echo "<li><strong>Disattiva tutte le versioni di FP Performance Suite</strong></li>";
echo "<li><strong>Elimina 2 delle 3 versioni</strong> (mantieni solo Safe Mode)</li>";
echo "<li><strong>Attiva solo la versione Safe Mode</strong></li>";
echo "<li><strong>Esegui test-complete-suite.php</strong> per verificare</li>";
echo "<li><strong>Se funziona, elimina anche Safe Mode e reinstalla una versione pulita</strong></li>";
echo "</ol>";
echo "</div>";

echo "<div class='solution'>";
echo "<h3>🚨 PREVENZIONE FUTURA</h3>";
echo "<ul>";
echo "<li>✅ Installa sempre UNA sola versione del plugin</li>";
echo "<li>✅ Disattiva versioni precedenti prima di installare nuove</li>";
echo "<li>✅ Usa sempre la versione più recente</li>";
echo "<li>✅ Testa sempre dopo l'installazione</li>";
echo "</ul>";
echo "</div>";
echo "</div>";

// 7. TEST POST-RISOLUZIONE
echo "<div class='section'>";
echo "<h2>🧪 7. Test Post-Risoluzione</h2>";

echo "<div class='test-item info'>📋 Dopo aver risolto i conflitti, esegui:</div>";
echo "<div class='test-item info'>🔗 <a href='test-complete-suite.php' target='_blank'>Test Completo Suite</a></div>";
echo "<div class='test-item info'>🔗 <a href='test-admin-pages-verification.php' target='_blank'>Test Pagine Admin</a></div>";
echo "<div class='test-item info'>🔗 <a href='test-optimization-features.php' target='_blank'>Test Ottimizzazioni</a></div>";

echo "<div class='test-item info'>📋 Verifica che:</div>";
echo "<div class='test-item info'>• Solo una versione sia attiva</div>";
echo "<div class='test-item info'>• Le pagine admin si carichino correttamente</div>";
echo "<div class='test-item info'>• Non ci siano errori nei log</div>";
echo "<div class='test-item info'>• Le funzionalità funzionino come previsto</div>";
echo "</div>";

echo "<h2>✅ Test Conflitti Completato!</h2>";
echo "<p>Segui le soluzioni raccomandate per risolvere i conflitti tra versioni multiple.</p>";
?>
