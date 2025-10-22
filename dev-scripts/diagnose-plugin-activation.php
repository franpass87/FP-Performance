<?php
/**
 * Script di Diagnostica - FP Performance Suite
 * 
 * Questo script verifica perché le ottimizzazioni non si attivano
 */

// Verifica che siamo in WordPress
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

echo "<h2>🔍 Diagnostica FP Performance Suite</h2>";

// 1. Verifica se il plugin è attivo
$active_plugins = get_option('active_plugins', []);
$plugin_active = false;
foreach ($active_plugins as $plugin) {
    if (strpos($plugin, 'fp-performance-suite') !== false) {
        $plugin_active = true;
        break;
    }
}

echo "<h3>1. Stato Plugin</h3>";
if ($plugin_active) {
    echo "✅ Plugin attivo<br>";
} else {
    echo "❌ Plugin NON attivo<br>";
}

// 2. Verifica errori di attivazione
$activation_error = get_option('fp_perfsuite_activation_error', null);
echo "<h3>2. Errori di Attivazione</h3>";
if ($activation_error) {
    echo "❌ ERRORE TROVATO:<br>";
    echo "<pre>" . print_r($activation_error, true) . "</pre>";
} else {
    echo "✅ Nessun errore di attivazione<br>";
}

// 3. Verifica directory templates
$templates_dir = WP_PLUGIN_DIR . '/fp-performance-suite/templates';
echo "<h3>3. Directory Templates</h3>";
if (is_dir($templates_dir)) {
    echo "✅ Directory templates esiste<br>";
} else {
    echo "❌ Directory templates MANCANTE<br>";
    echo "Creazione directory...<br>";
    if (wp_mkdir_p($templates_dir)) {
        echo "✅ Directory creata con successo<br>";
    } else {
        echo "❌ Impossibile creare directory<br>";
    }
}

// 4. Verifica configurazioni
echo "<h3>4. Configurazioni Attive</h3>";

$cache_config = get_option('fp_ps_cache', []);
echo "Page Cache: " . (isset($cache_config['enabled']) && $cache_config['enabled'] ? '✅ Attivo' : '❌ Disattivo') . "<br>";

$assets_config = get_option('fp_ps_assets', []);
echo "Asset Optimization: " . (isset($assets_config['enabled']) && $assets_config['enabled'] ? '✅ Attivo' : '❌ Disattivo') . "<br>";

$media_config = get_option('fp_ps_media', []);
echo "WebP Conversion: " . (isset($media_config['enabled']) && $media_config['enabled'] ? '✅ Attivo' : '❌ Disattivo') . "<br>";

// 5. Verifica file .htaccess
echo "<h3>5. File .htaccess</h3>";
$htaccess_path = ABSPATH . '.htaccess';
if (file_exists($htaccess_path)) {
    $htaccess_content = file_get_contents($htaccess_path);
    if (strpos($htaccess_content, 'FP Performance Suite') !== false) {
        echo "✅ Regole .htaccess presenti<br>";
    } else {
        echo "❌ Regole .htaccess MANCANTI<br>";
    }
} else {
    echo "❌ File .htaccess non trovato<br>";
}

// 6. Verifica cache
echo "<h3>6. Cache</h3>";
$cache_dir = WP_CONTENT_DIR . '/cache/fp-performance-suite';
if (is_dir($cache_dir)) {
    echo "✅ Directory cache esiste<br>";
    $cache_files = glob($cache_dir . '/*');
    echo "File in cache: " . count($cache_files) . "<br>";
} else {
    echo "❌ Directory cache non trovata<br>";
}

// 7. Test funzionalità
echo "<h3>7. Test Funzionalità</h3>";

// Test Page Cache
if (class_exists('FP\PerfSuite\Services\Cache\PageCache')) {
    echo "✅ Classe PageCache disponibile<br>";
} else {
    echo "❌ Classe PageCache NON disponibile<br>";
}

// Test Asset Optimizer
if (class_exists('FP\PerfSuite\Services\Assets\Optimizer')) {
    echo "✅ Classe Optimizer disponibile<br>";
} else {
    echo "❌ Classe Optimizer NON disponibile<br>";
}

// 8. Raccomandazioni
echo "<h3>8. Raccomandazioni</h3>";

if (!$plugin_active) {
    echo "🔧 <strong>AZIONE RICHIESTA:</strong> Attiva il plugin FP Performance Suite<br>";
}

if ($activation_error) {
    echo "🔧 <strong>AZIONE RICHIESTA:</strong> Risolvi gli errori di attivazione<br>";
    echo "   - Disattiva e riattiva il plugin<br>";
    echo "   - Controlla i log di errore<br>";
}

if (!is_dir($templates_dir)) {
    echo "🔧 <strong>AZIONE RICHIESTA:</strong> Crea la directory templates<br>";
}

if (!isset($cache_config['enabled']) || !$cache_config['enabled']) {
    echo "🔧 <strong>AZIONE RICHIESTA:</strong> Attiva Page Cache<br>";
}

if (!isset($assets_config['enabled']) || !$assets_config['enabled']) {
    echo "🔧 <strong>AZIONE RICHIESTA:</strong> Attiva Asset Optimization<br>";
}

echo "<hr>";
echo "<p><strong>Prossimo passo:</strong> Esegui le azioni raccomandate sopra, poi ricarica questa pagina per verificare.</p>";
?>
