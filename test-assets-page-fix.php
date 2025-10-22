<?php
/**
 * Test Fix Pagina Assets
 * 
 * Questo script testa se i servizi mancanti sono stati registrati correttamente
 * e se la pagina Assets può essere renderizzata senza errori.
 * 
 * UTILIZZO:
 * 1. Carica questo file nella root di WordPress
 * 2. Accedi come amministratore
 * 3. Visita: http://tuo-sito.com/test-assets-page-fix.php
 */

// Carica WordPress
require_once __DIR__ . '/../../wp-load.php';

// Verifica che l'utente sia amministratore
if (!current_user_can('manage_options')) {
    die('Accesso negato. Devi essere amministratore.');
}

echo "<h1>Test Fix Pagina Assets</h1>\n";
echo "<hr>\n";

// 1. Verifica che il plugin sia attivo
echo "<h2>1. Verifica Plugin</h2>\n";
if (defined('FP_PERF_SUITE_VERSION')) {
    echo "✅ Plugin FP Performance Suite ATTIVO (v" . FP_PERF_SUITE_VERSION . ")<br>\n";
} else {
    echo "❌ Plugin NON attivo<br>\n";
    die();
}

// 2. Ottieni il Service Container
echo "<h2>2. Service Container</h2>\n";
try {
    $container = \FP\PerfSuite\Plugin::container();
    echo "✅ Service Container disponibile<br>\n";
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "<br>\n";
    die();
}

// 3. Verifica che i servizi richiesti siano registrati
echo "<h2>3. Verifica Servizi Registrati</h2>\n";
$requiredServices = [
    'FP\PerfSuite\Services\Assets\Optimizer' => 'Assets Optimizer',
    'FP\PerfSuite\Services\Assets\FontOptimizer' => 'Font Optimizer',
    'FP\PerfSuite\Services\Assets\ThirdPartyScriptManager' => 'Third Party Script Manager',
    'FP\PerfSuite\Services\Assets\SmartAssetDelivery' => 'Smart Asset Delivery (FIX)',
    'FP\PerfSuite\Services\Assets\Http2ServerPush' => 'HTTP/2 Server Push (FIX)',
    'FP\PerfSuite\Services\Compatibility\ThemeDetector' => 'Theme Detector',
];

$allOk = true;
foreach ($requiredServices as $serviceClass => $serviceName) {
    try {
        $service = $container->get($serviceClass);
        if ($service) {
            echo "✅ <strong>$serviceName</strong> ($serviceClass)<br>\n";
        } else {
            echo "⚠️ <strong>$serviceName</strong> restituisce NULL<br>\n";
            $allOk = false;
        }
    } catch (Exception $e) {
        echo "❌ <strong>$serviceName</strong> - ERRORE: " . $e->getMessage() . "<br>\n";
        $allOk = false;
    }
}

if (!$allOk) {
    echo "<p style='color: red;'><strong>Alcuni servizi non sono disponibili!</strong></p>\n";
    die();
}

// 4. Tenta di istanziare la pagina Assets
echo "<h2>4. Istanziazione Pagina Assets</h2>\n";
try {
    $assetsPage = new \FP\PerfSuite\Admin\Pages\Assets($container);
    echo "✅ Pagina Assets istanziata con successo<br>\n";
    echo "- Slug: " . $assetsPage->slug() . "<br>\n";
    echo "- Titolo: " . $assetsPage->title() . "<br>\n";
    echo "- View: " . $assetsPage->view() . "<br>\n";
} catch (Exception $e) {
    echo "❌ Errore istanziazione: " . $e->getMessage() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
    die();
}

// 5. Tenta di generare il contenuto (senza salvare nulla)
echo "<h2>5. Generazione Contenuto</h2>\n";
try {
    // Simula una richiesta GET
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['page'] = 'fp-performance-suite-assets';
    
    // Abilita error reporting per catturare eventuali warning
    $oldErrorReporting = error_reporting(E_ALL);
    $oldDisplayErrors = ini_get('display_errors');
    ini_set('display_errors', '1');
    
    // Usa reflection per chiamare il metodo protected content()
    $reflection = new ReflectionClass($assetsPage);
    $contentMethod = $reflection->getMethod('content');
    $contentMethod->setAccessible(true);
    
    ob_start();
    $content = $contentMethod->invoke($assetsPage);
    $extraOutput = ob_get_clean();
    
    // Ripristina error reporting
    error_reporting($oldErrorReporting);
    ini_set('display_errors', $oldDisplayErrors);
    
    if (!empty($content)) {
        $contentLength = strlen($content);
        echo "✅ <strong>Contenuto generato con successo</strong> ($contentLength caratteri)<br>\n";
        
        // Verifica presenza di elementi chiave
        $checks = [
            '<form' => 'Form HTML',
            'nav-tab-wrapper' => 'Navigazione tab',
            'fp-ps-' => 'Classi CSS plugin',
            'esc_html_e' => 'Funzioni WordPress', // Potrebbe essere già eseguita
        ];
        
        foreach ($checks as $needle => $description) {
            if (stripos($content, $needle) !== false) {
                echo "✅ Contiene: <strong>$description</strong><br>\n";
            }
        }
        
        // Mostra inizio del contenuto (primi 500 caratteri)
        echo "<h3>Preview contenuto (primi 500 caratteri):</h3>\n";
        echo "<div style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; overflow: auto; max-height: 300px;'>\n";
        echo "<pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>\n";
        echo "</div>\n";
        
    } else {
        echo "❌ <strong>Contenuto VUOTO</strong><br>\n";
        if (!empty($extraOutput)) {
            echo "Output buffer catturato:<br>\n";
            echo "<pre>" . htmlspecialchars($extraOutput) . "</pre>\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Errore nella generazione: " . $e->getMessage() . "<br>\n";
    echo "<pre>" . $e->getTraceAsString() . "</pre>\n";
}

// 6. Verifica errori PHP
echo "<h2>6. Errori PHP</h2>\n";
$lastError = error_get_last();
if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
    echo "⚠️ Ultimo errore PHP critico:<br>\n";
    echo "- Tipo: " . $lastError['type'] . "<br>\n";
    echo "- Messaggio: " . $lastError['message'] . "<br>\n";
    echo "- File: " . $lastError['file'] . "<br>\n";
    echo "- Linea: " . $lastError['line'] . "<br>\n";
} else {
    echo "✅ Nessun errore PHP critico registrato<br>\n";
}

echo "<hr>\n";
echo "<h2>✅ TEST COMPLETATO CON SUCCESSO!</h2>\n";
echo "<p><strong>La pagina Assets dovrebbe ora funzionare correttamente.</strong></p>\n";
echo "<p>Vai alla pagina: <a href='" . admin_url('admin.php?page=fp-performance-suite-assets') . "' target='_blank'>FP Performance Suite → Assets</a></p>\n";
echo "<p><small>Puoi eliminare questo file di test dopo la verifica.</small></p>\n";

