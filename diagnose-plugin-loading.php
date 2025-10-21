<?php
/**
 * Script Diagnostico per Plugin Loading
 * 
 * Carica questo file sul server e accedilo via browser per diagnosticare
 * il problema del caricamento della classe Plugin.
 * 
 * URL: https://tuosito.com/wp-content/plugins/FP-Performance/diagnose-plugin-loading.php
 */

// Impedisci esecuzione diretta
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
}

?><!DOCTYPE html>
<html>
<head>
    <title>Diagnosi Plugin Loading - FP Performance Suite</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #0073aa; border-bottom: 2px solid #0073aa; padding-bottom: 10px; }
        h2 { color: #23282d; margin-top: 30px; }
        .success { color: #46b450; font-weight: bold; }
        .error { color: #dc3232; font-weight: bold; }
        .warning { color: #f56e28; font-weight: bold; }
        .info { background: #e8f4f8; padding: 10px; border-left: 4px solid #0073aa; margin: 10px 0; }
        .code { background: #23282d; color: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table td, table th { padding: 10px; border: 1px solid #ddd; text-align: left; }
        table th { background: #f8f9fa; font-weight: bold; }
        .test-result { padding: 5px 10px; border-radius: 3px; display: inline-block; }
        .test-pass { background: #d4edda; color: #155724; }
        .test-fail { background: #f8d7da; color: #721c24; }
        .test-warn { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Diagnosi Plugin Loading - FP Performance Suite</h1>
    
    <?php
    
    $pluginDir = __DIR__;
    $results = [];
    $criticalError = false;
    
    // ============================================
    // TEST 1: Verifica Percorsi Base
    // ============================================
    echo '<h2>📁 Test 1: Verifica Percorsi Base</h2>';
    echo '<table>';
    
    $paths = [
        'Plugin Directory' => $pluginDir,
        'ABSPATH' => ABSPATH,
        'Plugin File' => $pluginDir . '/fp-performance-suite.php',
        'Source Directory' => $pluginDir . '/fp-performance-suite/src',
        'Plugin.php Path' => $pluginDir . '/fp-performance-suite/src/Plugin.php',
        'Autoloader Path' => $pluginDir . '/fp-performance-suite/vendor/autoload.php',
    ];
    
    foreach ($paths as $label => $path) {
        $exists = file_exists($path);
        $readable = $exists && is_readable($path);
        
        echo '<tr>';
        echo '<td><strong>' . esc_html($label) . '</strong></td>';
        echo '<td><code>' . esc_html($path) . '</code></td>';
        echo '<td>';
        if ($exists) {
            if ($readable) {
                echo '<span class="test-result test-pass">✓ Esiste e leggibile</span>';
            } else {
                echo '<span class="test-result test-fail">✗ Esiste ma NON leggibile</span>';
                $criticalError = true;
            }
        } else {
            echo '<span class="test-result test-fail">✗ NON esiste</span>';
            if (strpos($label, 'Plugin.php') !== false) {
                $criticalError = true;
            }
        }
        echo '</td>';
        echo '</tr>';
        
        $results[$label] = [
            'path' => $path,
            'exists' => $exists,
            'readable' => $readable,
        ];
    }
    
    echo '</table>';
    
    // ============================================
    // TEST 2: Verifica Contenuto Directory
    // ============================================
    echo '<h2>📂 Test 2: Contenuto Directory Plugin</h2>';
    
    $mainDirContents = @scandir($pluginDir);
    echo '<div class="info"><strong>File/Directory nella root del plugin:</strong></div>';
    echo '<div class="code">';
    if ($mainDirContents) {
        foreach ($mainDirContents as $item) {
            if ($item === '.' || $item === '..') continue;
            $fullPath = $pluginDir . '/' . $item;
            $type = is_dir($fullPath) ? '[DIR]' : '[FILE]';
            $size = is_file($fullPath) ? ' (' . filesize($fullPath) . ' bytes)' : '';
            echo htmlspecialchars("$type $item$size") . "\n";
        }
    } else {
        echo "❌ Impossibile leggere la directory\n";
        $criticalError = true;
    }
    echo '</div>';
    
    // Verifica contenuto fp-performance-suite/src
    $srcDir = $pluginDir . '/fp-performance-suite/src';
    if (is_dir($srcDir)) {
        echo '<div class="info"><strong>File nella directory src/:</strong></div>';
        echo '<div class="code">';
        $srcContents = @scandir($srcDir);
        if ($srcContents) {
            foreach ($srcContents as $item) {
                if ($item === '.' || $item === '..') continue;
                $fullPath = $srcDir . '/' . $item;
                $type = is_dir($fullPath) ? '[DIR]' : '[FILE]';
                $size = is_file($fullPath) ? ' (' . filesize($fullPath) . ' bytes)' : '';
                echo htmlspecialchars("$type $item$size") . "\n";
            }
        } else {
            echo "❌ Impossibile leggere la directory src/\n";
        }
        echo '</div>';
    }
    
    // ============================================
    // TEST 3: Verifica Autoloader
    // ============================================
    echo '<h2>⚙️ Test 3: Test Autoloader</h2>';
    
    $autoloadPath = $pluginDir . '/fp-performance-suite/vendor/autoload.php';
    $useComposerAutoload = false;
    
    if (file_exists($autoloadPath) && is_readable($autoloadPath)) {
        echo '<div class="test-result test-pass">✓ Composer autoload disponibile</div>';
        $useComposerAutoload = true;
        require_once $autoloadPath;
    } else {
        echo '<div class="test-result test-warn">⚠ Composer autoload non disponibile, uso SPL autoload</div>';
        
        // Registra SPL autoloader
        spl_autoload_register(function ($class) use ($pluginDir) {
            if (strpos($class, 'FP\\PerfSuite\\') !== 0) {
                return;
            }
            
            $path = $pluginDir . '/fp-performance-suite/src/' . str_replace(['FP\\PerfSuite\\', '\\'], ['', '/'], $class) . '.php';
            
            echo '<div class="info">';
            echo '<strong>Autoloader tentativo:</strong><br>';
            echo 'Classe richiesta: <code>' . esc_html($class) . '</code><br>';
            echo 'Path calcolato: <code>' . esc_html($path) . '</code><br>';
            echo 'File esiste: ' . (file_exists($path) ? '<span class="success">✓ Sì</span>' : '<span class="error">✗ No</span>') . '<br>';
            echo 'File leggibile: ' . (is_readable($path) ? '<span class="success">✓ Sì</span>' : '<span class="error">✗ No</span>');
            echo '</div>';
            
            if (file_exists($path)) {
                require_once $path;
                echo '<div class="test-result test-pass">✓ File caricato: ' . basename($path) . '</div>';
            }
        });
    }
    
    // ============================================
    // TEST 4: Verifica Caricamento Classe Plugin
    // ============================================
    echo '<h2>🔌 Test 4: Caricamento Classe Plugin</h2>';
    
    // Tenta di caricare manualmente Plugin.php
    $pluginPhpPath = $pluginDir . '/fp-performance-suite/src/Plugin.php';
    
    if (file_exists($pluginPhpPath)) {
        echo '<div class="info">Tentativo caricamento manuale di Plugin.php...</div>';
        
        // Verifica sintassi prima di includere
        $syntaxCheck = shell_exec('php -l ' . escapeshellarg($pluginPhpPath) . ' 2>&1');
        echo '<div class="code">' . esc_html($syntaxCheck) . '</div>';
        
        if (strpos($syntaxCheck, 'No syntax errors') !== false) {
            echo '<div class="test-result test-pass">✓ Sintassi PHP corretta</div>';
            
            // Prova a includere il file
            try {
                require_once $pluginPhpPath;
                echo '<div class="test-result test-pass">✓ File Plugin.php incluso con successo</div>';
                
                // Verifica se la classe esiste
                if (class_exists('FP\\PerfSuite\\Plugin')) {
                    echo '<div class="test-result test-pass">✓ Classe FP\\PerfSuite\\Plugin trovata!</div>';
                    
                    // Verifica metodi della classe
                    $reflection = new ReflectionClass('FP\\PerfSuite\\Plugin');
                    echo '<div class="info"><strong>Metodi pubblici della classe:</strong></div>';
                    echo '<div class="code">';
                    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                        echo htmlspecialchars($method->getName()) . "\n";
                    }
                    echo '</div>';
                    
                } else {
                    echo '<div class="test-result test-fail">✗ Classe FP\\PerfSuite\\Plugin NON trovata dopo l\'inclusione</div>';
                    $criticalError = true;
                }
                
            } catch (\Throwable $e) {
                echo '<div class="test-result test-fail">✗ Errore durante l\'inclusione: ' . esc_html($e->getMessage()) . '</div>';
                echo '<div class="code">' . esc_html($e->getTraceAsString()) . '</div>';
                $criticalError = true;
            }
            
        } else {
            echo '<div class="test-result test-fail">✗ Errori di sintassi rilevati</div>';
            $criticalError = true;
        }
        
    } else {
        echo '<div class="test-result test-fail">✗ File Plugin.php NON trovato</div>';
        $criticalError = true;
    }
    
    // ============================================
    // TEST 5: Verifica Database WordPress
    // ============================================
    echo '<h2>🗄️ Test 5: Connessione Database WordPress</h2>';
    
    if (file_exists(ABSPATH . 'wp-load.php')) {
        require_once ABSPATH . 'wp-load.php';
        
        global $wpdb;
        
        echo '<table>';
        echo '<tr><td><strong>$wpdb esiste</strong></td><td>' . (isset($wpdb) ? '<span class="success">✓ Sì</span>' : '<span class="error">✗ No</span>') . '</td></tr>';
        
        if (isset($wpdb)) {
            echo '<tr><td><strong>$wpdb->dbh esiste</strong></td><td>' . (isset($wpdb->dbh) ? '<span class="success">✓ Sì</span>' : '<span class="error">✗ No</span>') . '</td></tr>';
            
            if (isset($wpdb->dbh)) {
                $isObject = is_object($wpdb->dbh);
                echo '<tr><td><strong>$wpdb->dbh è un oggetto</strong></td><td>' . ($isObject ? '<span class="success">✓ Sì</span>' : '<span class="error">✗ No</span>') . '</td></tr>';
                
                if ($isObject) {
                    $class = get_class($wpdb->dbh);
                    echo '<tr><td><strong>Tipo connessione</strong></td><td><code>' . esc_html($class) . '</code></td></tr>';
                    
                    // Test ping
                    if (method_exists($wpdb->dbh, 'ping')) {
                        $ping = @$wpdb->dbh->ping();
                        echo '<tr><td><strong>Ping database</strong></td><td>' . ($ping ? '<span class="success">✓ Connessione attiva</span>' : '<span class="error">✗ Connessione persa</span>') . '</td></tr>';
                    }
                    
                    // Test query
                    $testQuery = $wpdb->query('SELECT 1');
                    echo '<tr><td><strong>Test query</strong></td><td>' . ($testQuery !== false ? '<span class="success">✓ Query riuscita</span>' : '<span class="error">✗ Query fallita</span>') . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="2"><span class="error">✗ Database handle è NULL - QUESTO È IL PROBLEMA!</span></td></tr>';
            }
        }
        echo '</table>';
        
    } else {
        echo '<div class="test-result test-warn">⚠ wp-load.php non trovato, impossibile testare il database</div>';
    }
    
    // ============================================
    // TEST 6: Permessi File
    // ============================================
    echo '<h2>🔒 Test 6: Permessi File</h2>';
    
    $filesToCheck = [
        'Plugin.php' => $pluginDir . '/fp-performance-suite/src/Plugin.php',
        'ServiceContainer.php' => $pluginDir . '/fp-performance-suite/src/ServiceContainer.php',
        'fp-performance-suite.php' => $pluginDir . '/fp-performance-suite.php',
    ];
    
    echo '<table>';
    echo '<tr><th>File</th><th>Permessi</th><th>Owner</th><th>Leggibile</th></tr>';
    
    foreach ($filesToCheck as $name => $path) {
        if (file_exists($path)) {
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $owner = function_exists('posix_getpwuid') ? posix_getpwuid(fileowner($path))['name'] : 'N/A';
            $readable = is_readable($path);
            
            echo '<tr>';
            echo '<td><code>' . esc_html($name) . '</code></td>';
            echo '<td><code>' . esc_html($perms) . '</code></td>';
            echo '<td>' . esc_html($owner) . '</td>';
            echo '<td>' . ($readable ? '<span class="success">✓</span>' : '<span class="error">✗</span>') . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    
    // ============================================
    // DIAGNOSI FINALE
    // ============================================
    echo '<h2>🎯 Diagnosi Finale</h2>';
    
    if ($criticalError) {
        echo '<div class="test-result test-fail" style="font-size: 16px; padding: 15px;">';
        echo '✗ ERRORE CRITICO RILEVATO';
        echo '</div>';
        echo '<div class="info">';
        echo '<strong>Possibili cause:</strong><ul>';
        echo '<li>File Plugin.php mancante o non leggibile</li>';
        echo '<li>Errori di sintassi in Plugin.php</li>';
        echo '<li>Permessi file errati</li>';
        echo '<li>Struttura directory incompleta</li>';
        echo '</ul>';
        echo '<strong>Azione consigliata:</strong> Ricarica tutti i file del plugin sul server via FTP.';
        echo '</div>';
    } else {
        echo '<div class="test-result test-pass" style="font-size: 16px; padding: 15px;">';
        echo '✓ Tutti i test passati - La struttura del plugin è corretta';
        echo '</div>';
    }
    
    // Info sistema
    echo '<h2>ℹ️ Informazioni Sistema</h2>';
    echo '<table>';
    echo '<tr><td><strong>PHP Version</strong></td><td>' . PHP_VERSION . '</td></tr>';
    echo '<tr><td><strong>Server Software</strong></td><td>' . ($_SERVER['SERVER_SOFTWARE'] ?? 'N/A') . '</td></tr>';
    echo '<tr><td><strong>Document Root</strong></td><td>' . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '</td></tr>';
    echo '<tr><td><strong>Script Filename</strong></td><td>' . __FILE__ . '</td></tr>';
    echo '</table>';
    
    ?>
    
</div>
</body>
</html>

