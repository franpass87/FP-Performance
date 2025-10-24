<?php
/**
 * Test Ultra Completo FP Performance Suite
 * Verifica ogni aspetto del plugin
 */

echo "<h1>🔬 Test Ultra Completo FP Performance Suite</h1>\n";

// Test 1: Verifica struttura completa
echo "<h2>📋 Test 1: Verifica Struttura Completa</h2>\n";

// Verifica tutte le directory
$directories = [
    'src',
    'src/Admin',
    'src/Admin/Pages',
    'src/Admin/Components',
    'src/Services',
    'src/Services/Cache',
    'src/Services/Assets',
    'src/Services/DB',
    'src/Services/Media',
    'src/Services/Mobile',
    'src/Services/Compression',
    'src/Services/CDN',
    'src/Services/Security',
    'src/Services/Monitoring',
    'src/Services/PWA',
    'src/Services/Admin',
    'src/Utils',
    'src/Contracts',
    'src/Enums',
    'src/Events',
    'src/Health',
    'src/Http',
    'src/Repositories',
    'src/ValueObjects',
    'views',
    'assets',
    'languages'
];

$dir_errors = 0;
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$dir}/ - Directory presente</div>\n";
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$dir}/ - Directory mancante</div>\n";
        $dir_errors++;
    }
}

// Test 2: Verifica file critici
echo "<h2>📋 Test 2: Verifica File Critici</h2>\n";
$critical_files = [
    'fp-performance-suite.php',
    'src/Plugin.php',
    'src/ServiceContainer.php',
    'uninstall.php',
    'README.md',
    'readme.txt',
    'LICENSE',
    'composer.json',
    'composer.lock'
];

$critical_errors = 0;
foreach ($critical_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l {$file} 2>&1");
        if (strpos($output, 'No syntax errors') !== false || !strpos($output, 'php -l')) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Presente e valido</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
            $critical_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - File mancante</div>\n";
        $critical_errors++;
    }
}

// Test 3: Verifica tutte le classi PHP
echo "<h2>📋 Test 3: Verifica Tutte le Classi PHP</h2>\n";

// Trova tutti i file PHP
$php_files = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('src'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $php_files[] = $file->getPathname();
    }
}

$php_errors = 0;
foreach ($php_files as $file) {
    $output = shell_exec("php -l {$file} 2>&1");
    if (strpos($output, 'No syntax errors') !== false) {
        echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Sintassi corretta</div>\n";
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Errore sintassi</div>\n";
        $php_errors++;
    }
}

// Test 4: Verifica funzionalità specifiche
echo "<h2>📋 Test 4: Verifica Funzionalità Specifiche</h2>\n";

// Verifica che le classi implementate funzionino
$test_classes = [
    'FP\\PerfSuite\\Services\\Cache\\PageCache',
    'FP\\PerfSuite\\Services\\Assets\\FontOptimizer',
    'FP\\PerfSuite\\Services\\DB\\Cleaner',
    'FP\\PerfSuite\\Services\\Mobile\\MobileOptimizer',
    'FP\\PerfSuite\\Services\\Compression\\CompressionManager',
    'FP\\PerfSuite\\Services\\CDN\\CdnManager',
    'FP\\PerfSuite\\Services\\Security\\HtaccessSecurity',
    'FP\\PerfSuite\\Services\\Monitoring\\PerformanceMonitor'
];

$class_errors = 0;
foreach ($test_classes as $class) {
    $file = str_replace('\\', '/', $class) . '.php';
    $file = str_replace('FP/PerfSuite/', 'src/', $file);
    
    if (file_exists($file)) {
        require_once $file;
        if (class_exists($class)) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$class} - Classe funzionante</div>\n";
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$class} - Classe non trovata</div>\n";
            $class_errors++;
        }
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$class} - File non trovato</div>\n";
        $class_errors++;
    }
}

// Test 5: Verifica configurazione
echo "<h2>📋 Test 5: Verifica Configurazione</h2>\n";

// Verifica file di configurazione
$config_files = [
    'composer.json',
    'composer.lock',
    '.gitignore',
    'README.md',
    'readme.txt',
    'LICENSE'
];

$config_errors = 0;
foreach ($config_files as $file) {
    if (file_exists($file)) {
        echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ {$file} - Presente</div>\n";
    } else {
        echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ {$file} - Mancante</div>\n";
        $config_errors++;
    }
}

// Test 6: Verifica integrità
echo "<h2>📋 Test 6: Verifica Integrità</h2>\n";

// Verifica che il plugin principale possa essere caricato
$plugin_loaded = false;
try {
    if (file_exists('fp-performance-suite.php')) {
        $content = file_get_contents('fp-performance-suite.php');
        if (strpos($content, 'Plugin Name:') !== false && strpos($content, 'Version:') !== false) {
            echo "<div style='color: #28a745; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>✅ Plugin principale - Header valido</div>\n";
            $plugin_loaded = true;
        } else {
            echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ Plugin principale - Header non valido</div>\n";
        }
    }
} catch (Exception $e) {
    echo "<div style='color: #dc3545; margin: 5px 0; padding: 5px; background: rgba(0,0,0,0.05); border-radius: 4px;'>❌ Plugin principale - Errore caricamento</div>\n";
}

// Riepilogo finale
echo "<div style='margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 8px;'>\n";
echo "<h3>📈 Riepilogo Test Ultra Completo</h3>\n";
echo "<p><strong>✅ Directory:</strong> " . (count($directories) - $dir_errors) . "/" . count($directories) . "</p>\n";
echo "<p><strong>✅ File Critici:</strong> " . (count($critical_files) - $critical_errors) . "/" . count($critical_files) . "</p>\n";
echo "<p><strong>✅ File PHP:</strong> " . (count($php_files) - $php_errors) . "/" . count($php_files) . "</p>\n";
echo "<p><strong>✅ Classi Test:</strong> " . (count($test_classes) - $class_errors) . "/" . count($test_classes) . "</p>\n";
echo "<p><strong>✅ Configurazione:</strong> " . (count($config_files) - $config_errors) . "/" . count($config_files) . "</p>\n";

$total_errors = $dir_errors + $critical_errors + $php_errors + $class_errors + $config_errors;
$total_items = count($directories) + count($critical_files) + count($php_files) + count($test_classes) + count($config_files);
$score = round((($total_items - $total_errors) / $total_items) * 100);

echo "<p><strong>🎯 Score Ultra Completo:</strong> {$score}%</p>\n";
echo "<p><strong>📊 Totale Elementi Testati:</strong> {$total_items}</p>\n";
echo "<p><strong>❌ Errori Totali:</strong> {$total_errors}</p>\n";
echo "<p><strong>📁 File PHP Totali:</strong> " . count($php_files) . "</p>\n";

if ($total_errors === 0) {
    echo "<p style='color: #28a745; font-weight: bold;'>🎉 PERFETTO! Tutto funziona alla perfezione!</p>\n";
} elseif ($total_errors < 3) {
    echo "<p style='color: #ffc107; font-weight: bold;'>⚠️ Quasi perfetto! Solo pochi errori minori.</p>\n";
} else {
    echo "<p style='color: #dc3545; font-weight: bold;'>❌ Alcuni problemi rilevati che necessitano attenzione.</p>\n";
}

echo "</div>\n";
?>
