<?php
/**
 * Test specifico per identificare problemi del plugin che causano white screen
 */

// Prevenire accesso diretto
if (!defined('ABSPATH')) {
    $wp_load_paths = [
        '../../../wp-load.php',
        '../../wp-load.php',
        '../wp-load.php',
        './wp-load.php'
    ];
    
    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }
    
    if (!$wp_loaded) {
        die('WordPress non trovato.');
    }
}

// Abilita error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Test Plugin FP Performance Suite</h1>";

/**
 * Test 1: Verifica caricamento del file principale
 */
function test_main_file_loading() {
    echo "<h2>Test 1: Caricamento File Principale</h2>";
    
    $main_file = __DIR__ . '/fp-performance-suite.php';
    
    if (!file_exists($main_file)) {
        echo "<p style='color: red;'>❌ File principale non trovato</p>";
        return false;
    }
    
    echo "<p style='color: green;'>✅ File principale trovato</p>";
    
    // Test di inclusione
    try {
        ob_start();
        $result = include_once $main_file;
        $output = ob_get_clean();
        
        if ($result === false) {
            echo "<p style='color: red;'>❌ Errore nell'inclusione del file</p>";
            return false;
        }
        
        if (!empty($output)) {
            echo "<p style='color: orange;'>⚠️ Output durante il caricamento:</p>";
            echo "<pre>" . esc_html($output) . "</pre>";
        }
        
        echo "<p style='color: green;'>✅ File principale caricato correttamente</p>";
        return true;
        
    } catch (Throwable $e) {
        echo "<p style='color: red;'>❌ Errore fatale durante il caricamento:</p>";
        echo "<p><strong>Messaggio:</strong> " . esc_html($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . esc_html($e->getFile()) . "</p>";
        echo "<p><strong>Linea:</strong> " . $e->getLine() . "</p>";
        return false;
    }
}

/**
 * Test 2: Verifica inizializzazione del plugin
 */
function test_plugin_initialization() {
    echo "<h2>Test 2: Inizializzazione Plugin</h2>";
    
    // Verifica se la classe Plugin esiste
    if (!class_exists('FP\\PerfSuite\\Plugin')) {
        echo "<p style='color: red;'>❌ Classe Plugin non trovata</p>";
        return false;
    }
    
    echo "<p style='color: green;'>✅ Classe Plugin trovata</p>";
    
    // Test di inizializzazione
    try {
        // Verifica se è già inizializzato
        if (method_exists('FP\\PerfSuite\\Plugin', 'isInitialized')) {
            $is_initialized = FP\PerfSuite\Plugin::isInitialized();
            echo "<p><strong>Stato inizializzazione:</strong> " . ($is_initialized ? 'Inizializzato' : 'Non inizializzato') . "</p>";
        }
        
        // Test di inizializzazione (se non già inizializzato)
        if (!FP\PerfSuite\Plugin::isInitialized()) {
            echo "<p>Tentativo di inizializzazione...</p>";
            FP\PerfSuite\Plugin::init();
            echo "<p style='color: green;'>✅ Plugin inizializzato con successo</p>";
        } else {
            echo "<p style='color: green;'>✅ Plugin già inizializzato</p>";
        }
        
        return true;
        
    } catch (Throwable $e) {
        echo "<p style='color: red;'>❌ Errore durante l'inizializzazione:</p>";
        echo "<p><strong>Messaggio:</strong> " . esc_html($e->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . esc_html($e->getFile()) . "</p>";
        echo "<p><strong>Linea:</strong> " . $e->getLine() . "</p>";
        echo "<p><strong>Stack trace:</strong></p>";
        echo "<pre>" . esc_html($e->getTraceAsString()) . "</pre>";
        return false;
    }
}

/**
 * Test 3: Verifica servizi del plugin
 */
function test_plugin_services() {
    echo "<h2>Test 3: Servizi Plugin</h2>";
    
    try {
        if (!class_exists('FP\\PerfSuite\\Plugin')) {
            echo "<p style='color: red;'>❌ Classe Plugin non disponibile</p>";
            return false;
        }
        
        $container = FP\PerfSuite\Plugin::container();
        if (!$container) {
            echo "<p style='color: red;'>❌ Container non disponibile</p>";
            return false;
        }
        
        echo "<p style='color: green;'>✅ Container disponibile</p>";
        
        // Test servizi principali
        $services_to_test = [
            'FP\\PerfSuite\\Admin\\Menu',
            'FP\\PerfSuite\\Admin\\Assets',
            'FP\\PerfSuite\\Http\\Routes'
        ];
        
        foreach ($services_to_test as $service) {
            try {
                if ($container->has($service)) {
                    $service_instance = $container->get($service);
                    echo "<p style='color: green;'>✅ Servizio disponibile: " . esc_html($service) . "</p>";
                } else {
                    echo "<p style='color: orange;'>⚠️ Servizio non registrato: " . esc_html($service) . "</p>";
                }
            } catch (Throwable $e) {
                echo "<p style='color: red;'>❌ Errore servizio " . esc_html($service) . ": " . esc_html($e->getMessage()) . "</p>";
            }
        }
        
        return true;
        
    } catch (Throwable $e) {
        echo "<p style='color: red;'>❌ Errore nel test dei servizi:</p>";
        echo "<p><strong>Messaggio:</strong> " . esc_html($e->getMessage()) . "</p>";
        return false;
    }
}

/**
 * Test 4: Verifica hook WordPress
 */
function test_wordpress_hooks() {
    echo "<h2>Test 4: Hook WordPress</h2>";
    
    // Verifica hook registrati
    global $wp_filter;
    
    $plugin_hooks = [];
    foreach ($wp_filter as $hook => $callbacks) {
        foreach ($callbacks as $priority => $callback_group) {
            foreach ($callback_group as $callback) {
                if (is_array($callback['function']) && 
                    is_object($callback['function'][0]) && 
                    strpos(get_class($callback['function'][0]), 'FP\\PerfSuite') !== false) {
                    $plugin_hooks[] = $hook;
                } elseif (is_string($callback['function']) && 
                         strpos($callback['function'], 'fp_perf_suite') !== false) {
                    $plugin_hooks[] = $hook;
                }
            }
        }
    }
    
    if (!empty($plugin_hooks)) {
        echo "<p style='color: green;'>✅ Hook plugin rilevati:</p>";
        echo "<ul>";
        foreach (array_unique($plugin_hooks) as $hook) {
            echo "<li>" . esc_html($hook) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ Nessun hook plugin rilevato</p>";
    }
    
    return true;
}

/**
 * Test 5: Verifica errori di output
 */
function test_output_errors() {
    echo "<h2>Test 5: Errori di Output</h2>";
    
    // Cattura output buffer
    ob_start();
    
    try {
        // Simula caricamento di una pagina
        do_action('init');
        
        $output = ob_get_clean();
        
        if (!empty($output)) {
            echo "<p style='color: orange;'>⚠️ Output rilevato durante init:</p>";
            echo "<pre>" . esc_html($output) . "</pre>";
        } else {
            echo "<p style='color: green;'>✅ Nessun output indesiderato</p>";
        }
        
        return true;
        
    } catch (Throwable $e) {
        ob_end_clean();
        echo "<p style='color: red;'>❌ Errore durante il test di output:</p>";
        echo "<p><strong>Messaggio:</strong> " . esc_html($e->getMessage()) . "</p>";
        return false;
    }
}

// Esegui tutti i test
echo "<div style='background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px;'>";

$tests = [
    'test_main_file_loading' => 'Caricamento File Principale',
    'test_plugin_initialization' => 'Inizializzazione Plugin',
    'test_plugin_services' => 'Servizi Plugin',
    'test_wordpress_hooks' => 'Hook WordPress',
    'test_output_errors' => 'Errori di Output'
];

$results = [];

foreach ($tests as $test_function => $test_name) {
    echo "<hr>";
    $result = $test_function();
    $results[$test_name] = $result;
}

echo "</div>";

// Riepilogo risultati
echo "<h2>📊 Riepilogo Test</h2>";
echo "<div style='background: #e9ecef; padding: 15px; border-radius: 5px;'>";

$passed = 0;
$total = count($results);

foreach ($results as $test_name => $result) {
    $status = $result ? '✅ PASS' : '❌ FAIL';
    $color = $result ? 'green' : 'red';
    echo "<p style='color: $color;'><strong>$test_name:</strong> $status</p>";
    if ($result) $passed++;
}

echo "<hr>";
echo "<p><strong>Risultato complessivo:</strong> $passed/$total test superati</p>";

if ($passed === $total) {
    echo "<p style='color: green; font-weight: bold;'>🎉 Tutti i test superati! Il plugin dovrebbe funzionare correttamente.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>⚠️ Alcuni test falliti. Il plugin potrebbe causare problemi.</p>";
    echo "<p><strong>Raccomandazione:</strong> Disattiva temporaneamente il plugin e verifica se il problema persiste.</p>";
}

echo "</div>";

echo "<hr>";
echo "<p><small>Test eseguito il " . date('Y-m-d H:i:s') . "</small></p>";
?>
