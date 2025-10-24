<?php
/**
 * 🧪 TEST RAPIDO STRUMENTI DI DEBUG
 * 
 * Questo script testa rapidamente tutti gli strumenti di debug
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo '<div class="wrap">';
echo '<h1>🧪 Test Rapido Strumenti di Debug</h1>';

// Test 1: Verifica file di debug
echo '<h2>📋 Test 1: Verifica File di Debug</h2>';
$debug_files = [
    'debug-plugin-complete.php',
    'test-plugin-features.php',
    'debug-quick.php',
    'install-debug-tools.php',
    'integrate-debug-tools.php'
];

foreach ($debug_files as $file) {
    $file_path = plugin_dir_path(__FILE__) . $file;
    if (file_exists($file_path)) {
        echo '<p style="color: green;">✅ File ' . $file . ' presente</p>';
    } else {
        echo '<p style="color: red;">❌ File ' . $file . ' mancante</p>';
    }
}

// Test 2: Verifica classi di debug
echo '<h2>📋 Test 2: Verifica Classi di Debug</h2>';
$debug_classes = [
    'FP_Performance_Debug_Tool',
    'FP_Performance_Feature_Tester',
    'FP_Performance_Quick_Debug'
];

foreach ($debug_classes as $class) {
    if (class_exists($class)) {
        echo '<p style="color: green;">✅ Classe ' . $class . ' disponibile</p>';
    } else {
        echo '<p style="color: red;">❌ Classe ' . $class . ' non disponibile</p>';
    }
}

// Test 3: Verifica funzioni helper
echo '<h2>📋 Test 3: Verifica Funzioni Helper</h2>';
$helper_functions = [
    'fp_ps_debug_log',
    'fp_ps_debug_test',
    'fp_ps_is_debug_active'
];

foreach ($helper_functions as $function) {
    if (function_exists($function)) {
        echo '<p style="color: green;">✅ Funzione ' . $function . ' disponibile</p>';
    } else {
        echo '<p style="color: red;">❌ Funzione ' . $function . ' non disponibile</p>';
    }
}

// Test 4: Verifica menu di debug
echo '<h2>📋 Test 4: Verifica Menu di Debug</h2>';
$debug_menu_pages = [
    'fp-performance-debug',
    'fp-performance-test',
    'fp-performance-install-debug'
];

foreach ($debug_menu_pages as $page) {
    $menu_url = menu_page_url($page, false);
    if ($menu_url) {
        echo '<p style="color: green;">✅ Menu ' . $page . ' disponibile</p>';
    } else {
        echo '<p style="color: red;">❌ Menu ' . $page . ' non disponibile</p>';
    }
}

// Test 5: Verifica debug mode
echo '<h2>📋 Test 5: Verifica Debug Mode</h2>';
if (defined('WP_DEBUG') && WP_DEBUG) {
    echo '<p style="color: green;">✅ Debug mode attivo</p>';
} else {
    echo '<p style="color: orange;">⚠️ Debug mode non attivo</p>';
}

if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
    echo '<p style="color: green;">✅ Debug log attivo</p>';
} else {
    echo '<p style="color: orange;">⚠️ Debug log non attivo</p>';
}

// Test 6: Verifica plugin principale
echo '<h2>📋 Test 6: Verifica Plugin Principale</h2>';
if (is_plugin_active('fp-performance-suite/fp-performance-suite.php')) {
    echo '<p style="color: green;">✅ Plugin principale attivo</p>';
} else {
    echo '<p style="color: red;">❌ Plugin principale non attivo</p>';
}

// Test 7: Verifica classi principali del plugin
echo '<h2>📋 Test 7: Verifica Classi Principali del Plugin</h2>';
$main_classes = [
    'FP\PerfSuite\Plugin',
    'FP\PerfSuite\ServiceContainer',
    'FP\PerfSuite\Admin\Pages\Assets',
    'FP\PerfSuite\Admin\Pages\Database',
    'FP\PerfSuite\Admin\Pages\Mobile',
    'FP\PerfSuite\Admin\Pages\Backend',
    'FP\PerfSuite\Admin\Pages\ML'
];

foreach ($main_classes as $class) {
    if (class_exists($class)) {
        echo '<p style="color: green;">✅ Classe ' . $class . ' disponibile</p>';
    } else {
        echo '<p style="color: red;">❌ Classe ' . $class . ' non disponibile</p>';
    }
}

// Test 8: Verifica servizi del plugin
echo '<h2>📋 Test 8: Verifica Servizi del Plugin</h2>';
$services = [
    'FP\PerfSuite\Services\Assets\Optimizer',
    'FP\PerfSuite\Services\Assets\FontOptimizer',
    'FP\PerfSuite\Services\Assets\ThirdPartyScriptManager',
    'FP\PerfSuite\Services\Database\Optimizer',
    'FP\PerfSuite\Services\Mobile\Optimizer',
    'FP\PerfSuite\Services\Backend\Optimizer',
    'FP\PerfSuite\Services\ML\Optimizer'
];

foreach ($services as $service) {
    if (class_exists($service)) {
        echo '<p style="color: green;">✅ Servizio ' . $service . ' disponibile</p>';
    } else {
        echo '<p style="color: orange;">⚠️ Servizio ' . $service . ' non disponibile</p>';
    }
}

// Test 9: Verifica form handlers
echo '<h2>📋 Test 9: Verifica Form Handlers</h2>';
$form_handlers = [
    'FP\PerfSuite\Admin\Pages\Assets\Handlers\PostHandler',
    'FP\PerfSuite\Admin\Pages\Database\Handlers\PostHandler',
    'FP\PerfSuite\Admin\Pages\Mobile\Handlers\PostHandler',
    'FP\PerfSuite\Admin\Pages\Backend\Handlers\PostHandler',
    'FP\PerfSuite\Admin\Pages\ML\Handlers\PostHandler'
];

foreach ($form_handlers as $handler) {
    if (class_exists($handler)) {
        echo '<p style="color: green;">✅ Handler ' . $handler . ' disponibile</p>';
    } else {
        echo '<p style="color: orange;">⚠️ Handler ' . $handler . ' non disponibile</p>';
    }
}

// Test 10: Verifica database
echo '<h2>📋 Test 10: Verifica Database</h2>';
global $wpdb;
if ($wpdb->db_connect()) {
    echo '<p style="color: green;">✅ Connessione database funzionante</p>';
} else {
    echo '<p style="color: red;">❌ Problema connessione database</p>';
}

// Test 11: Verifica settings
echo '<h2>📋 Test 11: Verifica Settings</h2>';
$test_option = 'fp_ps_debug_test_' . time();
$test_value = ['test' => 'value', 'timestamp' => time()];

$saved = update_option($test_option, $test_value);
if ($saved) {
    $retrieved = get_option($test_option);
    if ($retrieved === $test_value) {
        echo '<p style="color: green;">✅ Settings funzionanti</p>';
    } else {
        echo '<p style="color: red;">❌ Problema recupero settings</p>';
    }
    delete_option($test_option);
} else {
    echo '<p style="color: red;">❌ Problema salvataggio settings</p>';
}

// Test 12: Verifica transients
echo '<h2>📋 Test 12: Verifica Transients</h2>';
$transient_key = 'fp_ps_debug_transient_' . time();
$transient_value = ['test' => 'transient', 'timestamp' => time()];

$saved = set_transient($transient_key, $transient_value, 60);
if ($saved) {
    $retrieved = get_transient($transient_key);
    if ($retrieved === $transient_value) {
        echo '<p style="color: green;">✅ Transients funzionanti</p>';
    } else {
        echo '<p style="color: red;">❌ Problema recupero transients</p>';
    }
    delete_transient($transient_key);
} else {
    echo '<p style="color: red;">❌ Problema salvataggio transients</p>';
}

// Test 13: Verifica nonce
echo '<h2>📋 Test 13: Verifica Nonce</h2>';
$nonce = wp_create_nonce('fp-ps-assets');
if ($nonce) {
    echo '<p style="color: green;">✅ Nonce generati correttamente</p>';
} else {
    echo '<p style="color: red;">❌ Problema generazione nonce</p>';
}

// Test 14: Verifica permessi
echo '<h2>📋 Test 14: Verifica Permessi</h2>';
if (current_user_can('manage_options')) {
    echo '<p style="color: green;">✅ Permessi amministratore disponibili</p>';
} else {
    echo '<p style="color: red;">❌ Permessi amministratore non disponibili</p>';
}

// Test 15: Verifica JavaScript
echo '<h2>📋 Test 15: Verifica JavaScript</h2>';
echo '<p>JavaScript test:</p>';
echo '<script>';
echo 'document.addEventListener("DOMContentLoaded", function() {';
echo '    console.log("✅ JavaScript funzionante");';
echo '    document.getElementById("js-test-result").innerHTML = "✅ JavaScript funzionante";';
echo '});';
echo '</script>';
echo '<p id="js-test-result" style="color: orange;">⚠️ Test JavaScript in corso...</p>';

// Riepilogo
echo '<h2>📊 Riepilogo Test</h2>';
echo '<div style="background: #f0f8ff; border: 1px solid #0066cc; padding: 15px; border-radius: 5px;">';
echo '<p><strong>Test completati:</strong> 15</p>';
echo '<p><strong>Strumenti di debug disponibili:</strong></p>';
echo '<ul>';
echo '<li>🔧 Debug Tool Completo</li>';
echo '<li>🧪 Test Funzionalità</li>';
echo '<li>🚀 Debug Rapido</li>';
echo '<li>🔧 Installer Debug Tools</li>';
echo '<li>🔧 Integrazione Debug Tools</li>';
echo '</ul>';
echo '<p><strong>Come usare:</strong></p>';
echo '<ul>';
echo '<li>Aggiungi <code>?debug=1</code> a qualsiasi URL del plugin per debug rapido</li>';
echo '<li>Vai su <strong>FP Performance Suite → Debug</strong> per test completi</li>';
echo '<li>Vai su <strong>FP Performance Suite → Test</strong> per test specifici</li>';
echo '</ul>';
echo '</div>';

echo '</div>';
