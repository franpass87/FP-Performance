<?php
/**
 * Script di Diagnostica per la pagina Assets
 * 
 * Questo script verifica perché la pagina Assets potrebbe apparire vuota
 */

// Carica WordPress
require_once __DIR__ . '/wp-content/plugins/fp-performance-suite/fp-performance-suite.php';

echo "=== DIAGNOSTICA PAGINA ASSETS ===\n\n";

// 1. Verifica che il plugin sia attivo
echo "1. Verifica Plugin:\n";
if (defined('FP_PERF_SUITE_VERSION')) {
    echo "   ✅ Plugin FP Performance Suite ATTIVO (v" . FP_PERF_SUITE_VERSION . ")\n";
} else {
    echo "   ❌ Plugin NON attivo o non definito\n";
    exit(1);
}

// 2. Verifica che la classe Assets esista
echo "\n2. Verifica Classe Assets:\n";
$assetsClass = 'FP\PerfSuite\Admin\Pages\Assets';
if (class_exists($assetsClass)) {
    echo "   ✅ Classe $assetsClass esiste\n";
} else {
    echo "   ❌ Classe $assetsClass NON trovata\n";
    exit(1);
}

// 3. Verifica il Service Container
echo "\n3. Verifica Service Container:\n";
try {
    $container = \FP\PerfSuite\ServiceContainer::getInstance();
    echo "   ✅ Service Container disponibile\n";
    
    // Verifica i servizi richiesti
    $requiredServices = [
        'FP\PerfSuite\Services\Assets\Optimizer',
        'FP\PerfSuite\Services\Assets\FontOptimizer',
        'FP\PerfSuite\Services\Assets\ThirdPartyScriptManager',
        'FP\PerfSuite\Services\Assets\Http2ServerPush',
        'FP\PerfSuite\Services\Assets\SmartAssetDelivery',
        'FP\PerfSuite\Services\Compatibility\ThemeDetector',
    ];
    
    echo "\n   Verifica Servizi:\n";
    $allServicesOk = true;
    foreach ($requiredServices as $service) {
        try {
            $instance = $container->get($service);
            echo "   ✅ $service\n";
        } catch (Exception $e) {
            echo "   ❌ $service - ERRORE: " . $e->getMessage() . "\n";
            $allServicesOk = false;
        }
    }
    
    if (!$allServicesOk) {
        echo "\n   ⚠️  Alcuni servizi non sono disponibili\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Service Container NON disponibile: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Tenta di istanziare la pagina Assets
echo "\n4. Istanziazione Pagina Assets:\n";
try {
    $assetsPage = new \FP\PerfSuite\Admin\Pages\Assets($container);
    echo "   ✅ Pagina Assets istanziata con successo\n";
    
    echo "   - Slug: " . $assetsPage->slug() . "\n";
    echo "   - Titolo: " . $assetsPage->title() . "\n";
    echo "   - View: " . $assetsPage->view() . "\n";
    
} catch (Exception $e) {
    echo "   ❌ Errore istanziazione: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// 5. Verifica che la view esista
echo "\n5. Verifica File View:\n";
$viewFile = FP_PERF_SUITE_DIR . '/views/admin-page.php';
if (file_exists($viewFile)) {
    echo "   ✅ File view esiste: $viewFile\n";
} else {
    echo "   ❌ File view NON trovato: $viewFile\n";
}

// 6. Tenta di generare il contenuto
echo "\n6. Generazione Contenuto:\n";
try {
    // Simula una richiesta GET
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['page'] = 'fp-performance-suite-assets';
    
    ob_start();
    $content = $assetsPage->content();
    $output = ob_get_clean();
    
    if (!empty($content)) {
        $contentLength = strlen($content);
        echo "   ✅ Contenuto generato con successo ($contentLength caratteri)\n";
        
        // Verifica se contiene HTML
        if (strpos($content, '<form') !== false) {
            echo "   ✅ Contiene form HTML\n";
        } else {
            echo "   ⚠️  NON contiene form HTML\n";
        }
        
        if (strpos($content, 'nav-tab-wrapper') !== false) {
            echo "   ✅ Contiene navigazione tab\n";
        } else {
            echo "   ⚠️  NON contiene navigazione tab\n";
        }
        
    } else {
        echo "   ❌ Contenuto VUOTO\n";
        if (!empty($output)) {
            echo "   Output buffer catturato:\n" . substr($output, 0, 500) . "...\n";
        }
    }
    
} catch (Exception $e) {
    echo "   ❌ Errore nella generazione: " . $e->getMessage() . "\n";
    echo "   Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// 7. Verifica errori PHP
echo "\n7. Errori PHP:\n";
$lastError = error_get_last();
if ($lastError) {
    echo "   ⚠️  Ultimo errore PHP:\n";
    echo "      Tipo: " . $lastError['type'] . "\n";
    echo "      Messaggio: " . $lastError['message'] . "\n";
    echo "      File: " . $lastError['file'] . "\n";
    echo "      Linea: " . $lastError['line'] . "\n";
} else {
    echo "   ✅ Nessun errore PHP registrato\n";
}

echo "\n=== FINE DIAGNOSTICA ===\n";
echo "\nSe tutti i test sono ✅, il problema potrebbe essere:\n";
echo "1. Un conflitto con un altro plugin\n";
echo "2. Un problema di permessi utente\n";
echo "3. Un errore JavaScript che nasconde il contenuto\n";
echo "4. Un problema con il tema attivo\n";
echo "\nControlla anche:\n";
echo "- La console JavaScript del browser (F12) per errori\n";
echo "- Il log degli errori PHP di WordPress (wp-content/debug.log)\n";

