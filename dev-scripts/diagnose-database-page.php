<?php
/**
 * Script di Diagnostica: Database Page Vuota
 * 
 * Questo script identifica perché la pagina Database appare vuota
 */

// Carica WordPress
require_once dirname(__DIR__) . '/wp-content/plugins/fp-performance-suite/fp-performance-suite.php';

// Abilita error reporting completo
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "========================================\n";
echo "DIAGNOSTICA DATABASE PAGE\n";
echo "========================================\n\n";

// 1. Verifica che il plugin sia caricato
echo "1. Verifica Plugin:\n";
if (defined('FP_PERF_SUITE_VERSION')) {
    echo "   ✓ Plugin caricato (v" . FP_PERF_SUITE_VERSION . ")\n";
} else {
    echo "   ✗ Plugin NON caricato\n";
    exit(1);
}

// 2. Verifica ServiceContainer
echo "\n2. Verifica ServiceContainer:\n";
try {
    $container = FP\PerfSuite\Plugin::container();
    if ($container) {
        echo "   ✓ ServiceContainer disponibile\n";
    } else {
        echo "   ✗ ServiceContainer NON disponibile\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ✗ Errore: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Verifica servizi DB
echo "\n3. Verifica Servizi Database:\n";

$services = [
    'Cleaner' => 'FP\\PerfSuite\\Services\\DB\\Cleaner',
    'DatabaseOptimizer' => 'FP\\PerfSuite\\Services\\DB\\DatabaseOptimizer',
    'DatabaseQueryMonitor' => 'FP\\PerfSuite\\Services\\DB\\DatabaseQueryMonitor',
    'PluginSpecificOptimizer' => 'FP\\PerfSuite\\Services\\DB\\PluginSpecificOptimizer',
    'DatabaseReportService' => 'FP\\PerfSuite\\Services\\DB\\DatabaseReportService',
    'ObjectCacheManager' => 'FP\\PerfSuite\\Services\\Cache\\ObjectCacheManager',
];

foreach ($services as $name => $class) {
    // Verifica se la classe esiste
    $classExists = class_exists($class);
    
    // Verifica se è registrato nel container
    $inContainer = false;
    $canGet = false;
    
    if ($classExists) {
        try {
            $inContainer = $container->has($class);
            if ($inContainer) {
                $instance = $container->get($class);
                $canGet = is_object($instance);
            }
        } catch (Exception $e) {
            echo "   ⚠ $name: Classe esiste ma errore nel container: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    if ($classExists && $inContainer && $canGet) {
        echo "   ✓ $name: OK\n";
    } elseif ($classExists && !$inContainer) {
        echo "   ⚠ $name: Classe esiste ma NON registrato nel container\n";
    } elseif (!$classExists) {
        echo "   ✗ $name: Classe NON esiste\n";
    } else {
        echo "   ⚠ $name: Stato sconosciuto\n";
    }
}

// 4. Verifica file Database.php
echo "\n4. Verifica File Database.php:\n";
$dbPageFile = dirname(__DIR__) . '/src/Admin/Pages/Database.php';
if (file_exists($dbPageFile)) {
    echo "   ✓ File esiste: $dbPageFile\n";
    
    // Verifica sintassi
    $output = [];
    $returnVar = 0;
    exec('php -l "' . $dbPageFile . '" 2>&1', $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "   ✓ Sintassi corretta\n";
    } else {
        echo "   ✗ Errore sintassi:\n";
        echo "     " . implode("\n     ", $output) . "\n";
    }
} else {
    echo "   ✗ File NON esiste\n";
}

// 5. Test creazione istanza Database Page
echo "\n5. Test Istanza Database Page:\n";
try {
    $dbPage = new FP\PerfSuite\Admin\Pages\Database($container);
    echo "   ✓ Istanza creata con successo\n";
    
    // Test metodi
    echo "   - Slug: " . $dbPage->slug() . "\n";
    echo "   - Title: " . $dbPage->title() . "\n";
    echo "   - View: " . $dbPage->view() . "\n";
    
    // Verifica view file
    if (file_exists($dbPage->view())) {
        echo "   ✓ View file esiste\n";
    } else {
        echo "   ✗ View file NON esiste: " . $dbPage->view() . "\n";
    }
    
} catch (Error $e) {
    echo "   ✗ Errore creazione istanza:\n";
    echo "     Tipo: " . get_class($e) . "\n";
    echo "     Messaggio: " . $e->getMessage() . "\n";
    echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "     Stack trace:\n";
    foreach ($e->getTrace() as $i => $trace) {
        echo "       #$i " . ($trace['file'] ?? 'unknown') . ":" . ($trace['line'] ?? '?') . " ";
        echo ($trace['class'] ?? '') . ($trace['type'] ?? '') . ($trace['function'] ?? '') . "()\n";
    }
    exit(1);
} catch (Exception $e) {
    echo "   ✗ Errore creazione istanza:\n";
    echo "     Tipo: " . get_class($e) . "\n";
    echo "     Messaggio: " . $e->getMessage() . "\n";
    echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

// 6. Test metodo content() (buffered per catturare output)
echo "\n6. Test Metodo content():\n";
try {
    ob_start();
    
    // Crea reflection per accedere al metodo protected
    $reflection = new ReflectionClass($dbPage);
    $contentMethod = $reflection->getMethod('content');
    $contentMethod->setAccessible(true);
    
    // Esegui il metodo
    $content = $contentMethod->invoke($dbPage);
    
    $bufferOutput = ob_get_clean();
    
    if ($bufferOutput) {
        echo "   ⚠ Output buffer catturato (" . strlen($bufferOutput) . " bytes)\n";
    }
    
    if (empty($content)) {
        echo "   ✗ PROBLEMA: content() restituisce stringa VUOTA\n";
        echo "     Questo è il problema! Il metodo content() non genera output.\n";
    } else {
        echo "   ✓ content() restituisce contenuto (" . strlen($content) . " bytes)\n";
        
        // Controlla se contiene errori visibili
        if (stripos($content, 'error') !== false || stripos($content, 'warning') !== false) {
            echo "   ⚠ Contenuto contiene possibili errori\n";
        }
    }
    
} catch (Error $e) {
    $bufferOutput = ob_get_clean();
    echo "   ✗ Errore FATALE nel metodo content():\n";
    echo "     Tipo: " . get_class($e) . "\n";
    echo "     Messaggio: " . $e->getMessage() . "\n";
    echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n";
    echo "   Stack trace dettagliato:\n";
    foreach ($e->getTrace() as $i => $trace) {
        echo "     #$i ";
        if (isset($trace['file'])) {
            echo $trace['file'] . ":" . ($trace['line'] ?? '?');
        } else {
            echo "[internal function]";
        }
        echo " ";
        if (isset($trace['class'])) {
            echo $trace['class'] . $trace['type'];
        }
        echo $trace['function'] . "()\n";
    }
    
    if ($bufferOutput) {
        echo "\n   Output catturato prima dell'errore:\n";
        echo "   " . str_replace("\n", "\n   ", substr($bufferOutput, 0, 500)) . "\n";
    }
    
    echo "\n========================================\n";
    echo "CAUSA PROBABILE:\n";
    echo "========================================\n";
    echo "Errore PHP fatale nel metodo Database::content().\n";
    echo "Questo causa la pagina bianca.\n\n";
    
    exit(1);
} catch (Exception $e) {
    ob_get_clean();
    echo "   ✗ Errore nel metodo content():\n";
    echo "     " . $e->getMessage() . "\n";
    exit(1);
}

// 7. Verifica opzioni database
echo "\n7. Verifica Opzioni Database:\n";
$dbOptions = get_option('fp_ps_db', []);
echo "   - Schedule: " . ($dbOptions['schedule'] ?? 'non impostato') . "\n";
echo "   - Batch: " . ($dbOptions['batch'] ?? 'non impostato') . "\n";

// 8. Test completo render (simulato)
echo "\n8. Test Render Completo:\n";
try {
    // Simula il processo di render
    $data = $reflection->getMethod('data');
    $data->setAccessible(true);
    $pageData = $data->invoke($dbPage);
    
    echo "   ✓ Metodo data() OK\n";
    echo "   - Title: " . ($pageData['title'] ?? 'N/A') . "\n";
    echo "   - Breadcrumbs: " . (isset($pageData['breadcrumbs']) ? count($pageData['breadcrumbs']) . ' elementi' : 'N/A') . "\n";
    
} catch (Exception $e) {
    echo "   ✗ Errore nel metodo data(): " . $e->getMessage() . "\n";
}

echo "\n========================================\n";
echo "RIEPILOGO DIAGNOSTICA\n";
echo "========================================\n";

if (empty($content)) {
    echo "\n❌ PROBLEMA IDENTIFICATO:\n";
    echo "   La pagina Database è vuota perché il metodo\n";
    echo "   Database::content() restituisce una stringa vuota.\n\n";
    echo "POSSIBILI CAUSE:\n";
    echo "   1. Errore PHP silenzioso che impedisce la generazione del contenuto\n";
    echo "   2. Problema con i servizi richiesti (Cleaner, Optimizer, etc.)\n";
    echo "   3. Errore nel buffering output (ob_start/ob_get_clean)\n\n";
    echo "AZIONE CONSIGLIATA:\n";
    echo "   - Abilita error_log in WordPress\n";
    echo "   - Controlla i log PHP del server\n";
    echo "   - Verifica che tutti i servizi siano disponibili\n";
} else {
    echo "\n✓ DIAGNOSI COMPLETATA:\n";
    echo "   La pagina Database sembra funzionare correttamente\n";
    echo "   nella diagnostica. Il problema potrebbe essere:\n";
    echo "   1. Specifico dell'ambiente WordPress\n";
    echo "   2. Legato ai permessi utente\n";
    echo "   3. Conflitto con altri plugin\n";
}

echo "\n";

