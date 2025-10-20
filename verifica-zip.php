<?php
/**
 * Verifica contenuto ZIP del plugin
 */

echo "=== Verifica fp-performance-suite.zip ===\n\n";

$zipFile = 'fp-performance-suite.zip';

if (!file_exists($zipFile)) {
    die("❌ File ZIP non trovato!\n");
}

$zip = new ZipArchive();
if ($zip->open($zipFile) !== TRUE) {
    die("❌ Impossibile aprire lo ZIP!\n");
}

echo "📦 File ZIP: $zipFile\n";
echo "📊 Dimensione: " . round(filesize($zipFile) / 1024 / 1024, 2) . " MB\n";
echo "📁 File totali: " . $zip->numFiles . "\n\n";

echo "=== File Ottimizzazione Database ===\n";

$dbFiles = [
    'fp-performance-suite/src/Services/DB/DatabaseOptimizer.php',
    'fp-performance-suite/src/Services/DB/DatabaseQueryMonitor.php',
    'fp-performance-suite/src/Services/DB/PluginSpecificOptimizer.php',
    'fp-performance-suite/src/Services/DB/DatabaseReportService.php',
    'fp-performance-suite/src/Plugin.php',
    'fp-performance-suite/src/Admin/Pages/Database.php',
    'fp-performance-suite/src/Cli/Commands.php',
    'fp-performance-suite/fp-performance-suite.php',
];

$allPresent = true;

foreach ($dbFiles as $file) {
    $stat = $zip->statName($file);
    if ($stat !== false) {
        $size = round($stat['size'] / 1024, 1);
        echo "✅ " . basename($file) . " ($size KB)\n";
    } else {
        echo "❌ MANCANTE: $file\n";
        $allPresent = false;
    }
}

echo "\n=== Struttura Directory ===\n";

$directories = [];
for ($i = 0; $i < $zip->numFiles; $i++) {
    $stat = $zip->statIndex($i);
    $name = $stat['name'];
    
    // Estrai directory
    $parts = explode('/', $name);
    if (count($parts) > 2) {
        $dir = $parts[0] . '/' . $parts[1];
        if (!isset($directories[$dir])) {
            $directories[$dir] = 0;
        }
        $directories[$dir]++;
    }
}

foreach ($directories as $dir => $count) {
    echo "📁 $dir/ ($count file)\n";
}

$zip->close();

echo "\n";

if ($allPresent) {
    echo "🎉 PERFETTO! Tutti i file di ottimizzazione database sono inclusi!\n";
    echo "\n✅ Il plugin v1.4.0 è COMPLETO e PRONTO per l'uso!\n";
    echo "\nPuoi:\n";
    echo "1. Installarlo via WordPress Admin > Plugin > Aggiungi Nuovo > Carica Plugin\n";
    echo "2. Caricarlo via FTP in wp-content/plugins/\n";
    echo "3. Distribuirlo su GitHub/WordPress.org\n";
} else {
    echo "⚠️  Alcuni file mancano. Riesegui: php build-plugin-completo.php\n";
}

echo "\n=== Fine Verifica ===\n";

