<?php
/**
 * Script di Verifica File - Miglioramenti Database
 * 
 * Esegui questo script per verificare che tutti i file siano al posto giusto
 * 
 * Uso: php verifica-file.php
 */

echo "=== Verifica File Miglioramenti Database ===\n\n";

$files = [
    'src/Services/DB/DatabaseOptimizer.php',
    'src/Services/DB/PluginSpecificOptimizer.php',
    'src/Services/DB/DatabaseReportService.php',
    'src/Services/DB/DatabaseQueryMonitor.php',
    'src/Plugin.php',
    'src/Admin/Pages/Database.php',
    'src/Cli/Commands.php',
];

$allPresent = true;
$totalSize = 0;

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    
    if (file_exists($path)) {
        $size = filesize($path);
        $sizeKb = round($size / 1024, 2);
        $totalSize += $size;
        
        echo "✅ $file ($sizeKb KB)\n";
    } else {
        echo "❌ $file - FILE MANCANTE!\n";
        $allPresent = false;
    }
}

echo "\n";

if ($allPresent) {
    echo "🎉 Tutti i file sono presenti! Dimensione totale: " . round($totalSize / 1024, 2) . " KB\n";
    echo "\nProssimi passi:\n";
    echo "1. Riattiva il plugin WordPress\n";
    echo "2. Svuota la cache PHP (OPcache)\n";
    echo "3. Vai su FP Performance > Database\n";
    echo "4. Dovresti vedere il nuovo Health Score Dashboard!\n";
} else {
    echo "⚠️  Alcuni file mancano! Esegui il comando:\n";
    echo "   copy fp-performance-suite\\src\\Services\\DB\\*.php src\\Services\\DB\\\n";
    echo "   copy fp-performance-suite\\src\\Plugin.php src\\Plugin.php\n";
    echo "   copy fp-performance-suite\\src\\Admin\\Pages\\Database.php src\\Admin\\Pages\\Database.php\n";
    echo "   copy fp-performance-suite\\src\\Cli\\Commands.php src\\Cli\\Commands.php\n";
}

echo "\n=== Fine Verifica ===\n";

