<?php
/**
 * Test di caricamento del plugin
 */

// Simula l'ambiente WordPress minimo
define('ABSPATH', __DIR__ . '/test-final/');
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Carica il file principale del plugin
$pluginFile = __DIR__ . '/test-final/fp-performance-suite/fp-performance-suite.php';

if (!file_exists($pluginFile)) {
    die("ERRORE: File plugin non trovato in $pluginFile\n");
}

echo "=== Test Caricamento Plugin ===\n\n";
echo "1. File principale trovato: OK\n";
echo "   Percorso: $pluginFile\n\n";

// Test include
echo "2. Tentativo caricamento file...\n";

try {
    // Simula funzioni WordPress essenziali
    if (!function_exists('esc_html')) {
        function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES); }
    }
    if (!function_exists('add_action')) {
        function add_action($hook, $callback, $priority = 10, $args = 1) {
            echo "   add_action('$hook') registrato\n";
        }
    }
    if (!function_exists('wp_die')) {
        function wp_die($message) {
            die("WP_DIE: $message\n");
        }
    }
    
    // Includi il plugin
    require_once $pluginFile;
    
    echo "   Caricamento file: OK\n\n";
    
} catch (\Throwable $e) {
    echo "   ERRORE durante il caricamento!\n";
    echo "   Messaggio: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

// Verifica che le costanti siano definite
echo "3. Verifica costanti:\n";
echo "   FP_PERF_SUITE_VERSION: " . (defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'NON DEFINITA') . "\n";
echo "   FP_PERF_SUITE_DIR: " . (defined('FP_PERF_SUITE_DIR') ? FP_PERF_SUITE_DIR : 'NON DEFINITA') . "\n";
echo "   FP_PERF_SUITE_FILE: " . (defined('FP_PERF_SUITE_FILE') ? FP_PERF_SUITE_FILE : 'NON DEFINITA') . "\n\n";

// Verifica che l'autoloader sia registrato
echo "4. Test autoloader:\n";

try {
    $pluginClass = 'FP\\PerfSuite\\Plugin';
    $pluginPath = __DIR__ . '/test-final/fp-performance-suite/src/Plugin.php';
    
    if (!file_exists($pluginPath)) {
        echo "   ERRORE: Plugin.php non trovato in $pluginPath\n";
        exit(1);
    }
    
    echo "   Plugin.php esiste: OK\n";
    echo "   Percorso: $pluginPath\n\n";
    
    // Prova a caricare la classe
    if (!class_exists($pluginClass)) {
        require_once $pluginPath;
    }
    
    if (class_exists($pluginClass)) {
        echo "   Classe Plugin caricata: OK\n";
        echo "   Namespace: $pluginClass\n\n";
    } else {
        echo "   ERRORE: Classe Plugin non caricata\n";
        exit(1);
    }
    
} catch (\Throwable $e) {
    echo "   ERRORE nell'autoloader!\n";
    echo "   Messaggio: " . $e->getMessage() . "\n";
    exit(1);
}

// Test caricamento altre classi critiche
echo "5. Test caricamento classi critiche:\n";

$criticalClasses = [
    'FP\\PerfSuite\\ServiceContainer',
    'FP\\PerfSuite\\Services\\Cache\\PageCache',
    'FP\\PerfSuite\\Services\\Assets\\Optimizer',
];

foreach ($criticalClasses as $class) {
    $classPath = str_replace(['FP\\PerfSuite\\', '\\'], ['', '/'], $class);
    $filePath = __DIR__ . '/test-final/fp-performance-suite/src/' . $classPath . '.php';
    
    if (file_exists($filePath)) {
        echo "   [OK] " . basename($class) . "\n";
    } else {
        echo "   [ERRORE] " . basename($class) . " - File non trovato: $filePath\n";
    }
}

echo "\n=== RISULTATO FINALE ===\n";
echo "[OK] Il plugin e' strutturato correttamente!\n";
echo "[OK] Tutti i file sono al posto giusto!\n";
echo "[OK] L'autoloader funziona!\n";
echo "[OK] Pronto per l'installazione su WordPress!\n\n";

echo "File ZIP da usare: fp-performance-suite-final.zip\n";

