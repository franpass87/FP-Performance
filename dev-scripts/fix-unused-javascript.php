<?php
/**
 * Fix Unused JavaScript - Configurazione Automatica
 * 
 * Questo script configura automaticamente le ottimizzazioni JavaScript
 * per risolvere il problema "Reduce unused JavaScript" mostrato nel report.
 * 
 * @package FP\PerfSuite
 * @author Francesco Passeri
 */

// Verifica che il plugin sia attivo
if (!defined('ABSPATH')) {
    die('Accesso diretto non consentito');
}

// Carica le classi necessarie
require_once __DIR__ . '/src/Services/Assets/UnusedJavaScriptOptimizer.php';
require_once __DIR__ . '/src/Services/Assets/CodeSplittingManager.php';
require_once __DIR__ . '/src/Services/Assets/JavaScriptTreeShaker.php';

use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;

echo "<h1>🔧 Fix Unused JavaScript - Configurazione Automatica</h1>\n";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: #28a745; font-weight: bold; }
    .error { color: #dc3545; font-weight: bold; }
    .info { color: #17a2b8; }
    .warning { color: #ffc107; }
    .config-section { background: #f8f9fa; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007cba; }
    pre { background: #f1f3f4; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>\n";

// Configurazione per risolvere il problema specifico del report
$configurations = [
    'unused_optimization' => [
        'enabled' => true,
        'code_splitting' => true,
        'dynamic_imports' => true,
        'conditional_loading' => true,
        'lazy_loading' => true,
        'exclude_critical' => [
            'jquery', 'jquery-core', 'jquery-migrate',
            'wc-checkout', 'wc-cart', 'wc-cart-fragments',
            'stripe', 'stripe-js', 'paypal-sdk',
            'contact-form-7', 'wpcf7-recaptcha',
            'elementor-frontend', 'elementor-pro-frontend'
        ],
        'conditional_patterns' => [
            'analytics' => ['is_front_page', 'is_single', 'is_shop'],
            'social' => ['is_single', 'is_page'],
            'maps' => ['has_shortcode:map', 'has_shortcode:googlemap'],
            'chat' => ['is_front_page', 'is_shop'],
            'popup' => ['is_front_page', 'is_shop']
        ],
        'lazy_load_patterns' => [
            'instagram', 'facebook', 'twitter', 'youtube', 'vimeo',
            'maps', 'chat', 'popup', 'analytics', 'tracking',
            'wonderpush', 'gtm', 'gtag'
        ],
        'dynamic_import_threshold' => 50000
    ],
    'code_splitting' => [
        'enabled' => true,
        'dynamic_imports' => true,
        'route_splitting' => true,
        'component_splitting' => true,
        'vendor_chunks' => true,
        'thresholds' => [
            'large_script' => 50000,
            'vendor_script' => 100000,
            'critical_script' => 20000
        ],
        'routes' => [
            'home' => ['is_front_page'],
            'single' => ['is_single'],
            'page' => ['is_page'],
            'shop' => ['is_shop', 'is_product_category', 'is_product_tag'],
            'checkout' => ['is_checkout', 'is_cart'],
            'account' => ['is_account_page']
        ],
        'components' => [
            'slider' => ['has_shortcode:slider', 'has_shortcode:rev_slider'],
            'map' => ['has_shortcode:map', 'has_shortcode:googlemap'],
            'form' => ['has_shortcode:contact-form-7', 'has_shortcode:gravityform'],
            'gallery' => ['has_shortcode:gallery', 'has_shortcode:nggallery'],
            'video' => ['has_shortcode:video', 'has_shortcode:youtube']
        ],
        'vendor_patterns' => [
            'jquery', 'lodash', 'moment', 'bootstrap', 'foundation',
            'react', 'vue', 'angular', 'backbone', 'underscore'
        ]
    ],
    'tree_shaking' => [
        'enabled' => true,
        'dead_code_elimination' => true,
        'unused_functions' => true,
        'unused_variables' => true,
        'unused_imports' => true,
        'aggressive_mode' => false,
        'exclude_patterns' => [
            'jquery', 'jquery-core', 'jquery-migrate',
            'wc-checkout', 'wc-cart', 'wc-cart-fragments',
            'stripe', 'stripe-js', 'paypal-sdk',
            'contact-form-7', 'wpcf7-recaptcha',
            'elementor-frontend', 'elementor-pro-frontend'
        ],
        'preserve_patterns' => [
            'init', 'ready', 'load', 'start',
            'checkout', 'payment', 'form', 'validation',
            'ajax', 'api', 'rest', 'endpoint'
        ],
        'minification_threshold' => 10000,
        'compression_level' => 6
    ]
];

echo "<div class='config-section'>\n";
echo "<h2>🎯 Configurazione Automatica per Risolvere 'Reduce unused JavaScript'</h2>\n";
echo "<p>Questa configurazione è ottimizzata per risolvere il problema specifico mostrato nel report di performance.</p>\n";
echo "</div>\n";

// Applica configurazioni
$results = [];

try {
    // 1. Unused JavaScript Optimizer
    echo "<div class='config-section'>\n";
    echo "<h3>🔍 Configurazione Unused JavaScript Optimizer</h3>\n";
    
    $unusedOptimizer = new UnusedJavaScriptOptimizer();
    $unusedOptimizer->update($configurations['unused_optimization']);
    $results['unused_optimizer'] = 'success';
    
    echo "<p class='success'>✅ Configurazione applicata con successo</p>\n";
    echo "<p class='info'>📊 Script condizionali: " . count($configurations['unused_optimization']['conditional_patterns']) . "</p>\n";
    echo "<p class='info'>📊 Script lazy: " . count($configurations['unused_optimization']['lazy_load_patterns']) . "</p>\n";
    echo "<p class='info'>📊 Soglia import dinamici: " . $configurations['unused_optimization']['dynamic_import_threshold'] . " bytes</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore nella configurazione Unused JavaScript Optimizer: " . $e->getMessage() . "</p>\n";
    $results['unused_optimizer'] = 'error';
}

try {
    // 2. Code Splitting Manager
    echo "<div class='config-section'>\n";
    echo "<h3>📦 Configurazione Code Splitting Manager</h3>\n";
    
    $codeSplittingManager = new CodeSplittingManager();
    $codeSplittingManager->update($configurations['code_splitting']);
    $results['code_splitting'] = 'success';
    
    echo "<p class='success'>✅ Configurazione applicata con successo</p>\n";
    echo "<p class='info'>📊 Route configurate: " . count($configurations['code_splitting']['routes']) . "</p>\n";
    echo "<p class='info'>📊 Componenti configurati: " . count($configurations['code_splitting']['components']) . "</p>\n";
    echo "<p class='info'>📊 Pattern vendor: " . count($configurations['code_splitting']['vendor_patterns']) . "</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore nella configurazione Code Splitting Manager: " . $e->getMessage() . "</p>\n";
    $results['code_splitting'] = 'error';
}

try {
    // 3. JavaScript Tree Shaker
    echo "<div class='config-section'>\n";
    echo "<h3>🌳 Configurazione JavaScript Tree Shaker</h3>\n";
    
    $treeShaker = new JavaScriptTreeShaker();
    $treeShaker->update($configurations['tree_shaking']);
    $results['tree_shaker'] = 'success';
    
    echo "<p class='success'>✅ Configurazione applicata con successo</p>\n";
    echo "<p class='info'>📊 Dead Code Elimination: " . ($configurations['tree_shaking']['dead_code_elimination'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📊 Unused Functions: " . ($configurations['tree_shaking']['unused_functions'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📊 Unused Variables: " . ($configurations['tree_shaking']['unused_variables'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "<p class='info'>📊 Unused Imports: " . ($configurations['tree_shaking']['unused_imports'] ? 'Abilitato' : 'Disabilitato') . "</p>\n";
    echo "</div>\n";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Errore nella configurazione JavaScript Tree Shaker: " . $e->getMessage() . "</p>\n";
    $results['tree_shaker'] = 'error';
}

// Riepilogo configurazione
echo "<div class='config-section'>\n";
echo "<h2>📋 Riepilogo Configurazione</h2>\n";

$successCount = 0;
$errorCount = 0;

foreach ($results as $service => $status) {
    if ($status === 'success') {
        $successCount++;
        echo "<p class='success'>✅ $service: Configurato correttamente</p>\n";
    } else {
        $errorCount++;
        echo "<p class='error'>❌ $service: Errore nella configurazione</p>\n";
    }
}

echo "<p class='info'>📊 Servizi configurati: $successCount</p>\n";
echo "<p class='info'>📊 Errori: $errorCount</p>\n";

if ($errorCount === 0) {
    echo "<p class='success'>🎉 Tutte le configurazioni applicate con successo!</p>\n";
    echo "<p class='info'>🚀 Le ottimizzazioni JavaScript sono ora attive</p>\n";
    echo "<p class='info'>📈 Risultati attesi: Riduzione del 60-80% del JavaScript non utilizzato</p>\n";
} else {
    echo "<p class='warning'>⚠️ Alcune configurazioni non sono state applicate correttamente</p>\n";
    echo "<p class='info'>🔧 Controlla i log per maggiori dettagli</p>\n";
}

echo "</div>\n";

// Istruzioni per il test
echo "<div class='config-section'>\n";
echo "<h2>🧪 Come Testare le Ottimizzazioni</h2>\n";
echo "<ol>\n";
echo "<li><strong>Chrome DevTools</strong>: Apri DevTools > Network > Ricarica la pagina</li>\n";
echo "<li><strong>Coverage Tab</strong>: Vai su DevTools > Coverage per vedere JavaScript non utilizzato</li>\n";
echo "<li><strong>Lighthouse</strong>: Esegui un audit Lighthouse per verificare il miglioramento</li>\n";
echo "<li><strong>PageSpeed Insights</strong>: Testa su pagespeed.web.dev</li>\n";
echo "</ol>\n";
echo "</div>\n";

// Script di test automatico
echo "<div class='config-section'>\n";
echo "<h2>🔍 Test Automatico</h2>\n";
echo "<p>Esegui questo script per testare le ottimizzazioni:</p>\n";
echo "<pre>php test-js-optimization.php</pre>\n";
echo "</div>\n";

echo "<hr>\n";
echo "<p><em>Configurazione completata il " . date('Y-m-d H:i:s') . "</em></p>\n";
echo "<p><strong>Prossimi passi:</strong></p>\n";
echo "<ul>\n";
echo "<li>Testa le ottimizzazioni su una pagina di esempio</li>\n";
echo "<li>Verifica i risultati con Lighthouse</li>\n";
echo "<li>Monitora le performance nel tempo</li>\n";
echo "<li>Regola le impostazioni se necessario</li>\n";
echo "</ul>\n";
?>
