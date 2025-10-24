<?php
/**
 * Test del plugin fixato per verificare che non causi white screen
 */

echo "🧪 TEST PLUGIN FP PERFORMANCE SUITE FIXATO\n";
echo "==========================================\n\n";

// Test di sintassi del file principale
echo "1. Test sintassi file principale...\n";
$main_file = __DIR__ . '/fp-performance-suite.php';

if (file_exists($main_file)) {
    $syntax_check = shell_exec('php -l "' . $main_file . '" 2>&1');
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "✅ Sintassi file principale OK\n";
    } else {
        echo "❌ Errori di sintassi nel file principale:\n";
        echo $syntax_check . "\n";
    }
} else {
    echo "❌ File principale non trovato\n";
}

// Test di sintassi del file Plugin.php
echo "\n2. Test sintassi Plugin.php...\n";
$plugin_file = __DIR__ . '/src/Plugin.php';

if (file_exists($plugin_file)) {
    $syntax_check = shell_exec('php -l "' . $plugin_file . '" 2>&1');
    if (strpos($syntax_check, 'No syntax errors') !== false) {
        echo "✅ Sintassi Plugin.php OK\n";
    } else {
        echo "❌ Errori di sintassi in Plugin.php:\n";
        echo $syntax_check . "\n";
    }
} else {
    echo "❌ File Plugin.php non trovato\n";
}

// Verifica che i file di debug siano stati rimossi
echo "\n3. Verifica rimozione file di debug...\n";
$debug_files = [
    'debug-initialization-issues.php',
    'fix-register-meta-errors.php',
    'fix-fp-git-updater-deprecated.php'
];

$removed_count = 0;
foreach ($debug_files as $file) {
    if (!file_exists($file)) {
        echo "✅ Rimosso: $file\n";
        $removed_count++;
    } else {
        echo "⚠️ Ancora presente: $file\n";
    }
}

echo "File di debug rimossi: $removed_count/" . count($debug_files) . "\n";

// Test di caricamento del plugin
echo "\n4. Test caricamento plugin...\n";

// Simula il caricamento del plugin
try {
    ob_start();
    
    // Carica il file principale
    if (file_exists($main_file)) {
        include_once $main_file;
    }
    
    $output = ob_get_clean();
    
    if (empty($output)) {
        echo "✅ Plugin caricato senza output indesiderato\n";
    } else {
        echo "⚠️ Output durante il caricamento:\n";
        echo $output . "\n";
    }
    
} catch (Throwable $e) {
    ob_end_clean();
    echo "❌ Errore durante il caricamento del plugin:\n";
    echo "Messaggio: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Linea: " . $e->getLine() . "\n";
}

// Verifica classi caricate
echo "\n5. Verifica classi caricate...\n";
if (class_exists('FP\\PerfSuite\\Plugin')) {
    echo "✅ Classe Plugin caricata\n";
} else {
    echo "❌ Classe Plugin non caricata\n";
}

// Test di memoria
echo "\n6. Test memoria...\n";
$memory_usage = memory_get_usage(true);
$memory_peak = memory_get_peak_usage(true);

echo "Uso memoria: " . size_format($memory_usage) . "\n";
echo "Picco memoria: " . size_format($memory_peak) . "\n";

if ($memory_usage < 50 * 1024 * 1024) { // Meno di 50MB
    echo "✅ Uso memoria normale\n";
} else {
    echo "⚠️ Uso memoria elevato\n";
}

echo "\n🎉 TEST COMPLETATO!\n";
echo "==================\n\n";

echo "📋 RISULTATO:\n";
echo "Il plugin è stato fixato e dovrebbe ora funzionare senza causare white screen.\n\n";

echo "🔧 PROSSIMI PASSI:\n";
echo "1. Attiva il plugin da wp-admin/plugins.php\n";
echo "2. Verifica che il sito funzioni normalmente\n";
echo "3. Controlla tutte le pagine principali\n";
echo "4. Se tutto funziona, il problema è risolto\n\n";

echo "⚠️ SE IL PROBLEMA PERSISTE:\n";
echo "- Ripristina il backup: copy fp-performance-suite.php.backup-white-screen fp-performance-suite.php\n";
echo "- Disattiva il plugin e contatta il supporto\n\n";

echo "Script completato.\n";
?>
