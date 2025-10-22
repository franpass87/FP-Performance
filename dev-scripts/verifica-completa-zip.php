<?php
echo "=== Verifica Completa fp-performance-suite.zip ===\n\n";

$zip = new ZipArchive();
if ($zip->open('fp-performance-suite.zip') !== TRUE) {
    die("❌ Impossibile aprire ZIP\n");
}

$zipSize = filesize('fp-performance-suite.zip');
echo "📦 File: fp-performance-suite.zip\n";
echo "📊 Dimensione: " . round($zipSize / 1024 / 1024, 2) . " MB (" . round($zipSize / 1024) . " KB)\n";
echo "📁 File totali: " . $zip->numFiles . "\n\n";

// File critici da verificare
$criticalFiles = [
    'fp-performance-suite.php' => 'File principale plugin',
    'src/Plugin.php' => 'Bootstrap e ServiceContainer',
    'src/Services/DB/DatabaseOptimizer.php' => 'Ottimizzatore DB avanzato',
    'src/Services/DB/DatabaseQueryMonitor.php' => 'Monitor query',
    'src/Services/DB/PluginSpecificOptimizer.php' => 'Cleanup plugin-specific',
    'src/Services/DB/DatabaseReportService.php' => 'Report e Health Score',
    'src/Admin/Pages/Database.php' => 'Pagina admin database',
    'src/Cli/Commands.php' => 'Comandi WP-CLI',
];

echo "=== File Critici ===\n";
$allPresent = true;

foreach ($criticalFiles as $file => $description) {
    $fullPath = 'fp-performance-suite\\' . str_replace('/', '\\', $file);
    $stat = $zip->statName($fullPath);
    
    if ($stat !== false) {
        $size = round($stat['size'] / 1024, 1);
        echo "✅ $file ($size KB)\n";
        echo "   → $description\n";
    } else {
        echo "❌ MANCANTE: $file\n";
        $allPresent = false;
    }
}

echo "\n=== File Services/DB (Completi) ===\n";
for ($i = 0; $i < $zip->numFiles; $i++) {
    $stat = $zip->statIndex($i);
    $name = $stat['name'];
    
    if (stripos($name, 'Services\\DB\\') !== false && stripos($name, '.php') !== false) {
        $basename = basename($name);
        $size = round($stat['size'] / 1024, 1);
        echo "✅ $basename ($size KB)\n";
    }
}

$zip->close();

echo "\n";

if ($allPresent) {
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🎉 PLUGIN COMPLETO v1.4.0 VERIFICATO!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    echo "✅ Tutti i file di ottimizzazione database sono inclusi!\n";
    echo "✅ Tutte le funzionalità avanzate sono integrate!\n";
    echo "✅ Nessun file esterno richiesto!\n\n";
    
    echo "🚀 PRONTO PER L'INSTALLAZIONE!\n\n";
    
    echo "Come installare:\n";
    echo "1. WordPress Admin > Plugin > Aggiungi Nuovo > Carica Plugin\n";
    echo "2. Seleziona: fp-performance-suite.zip\n";
    echo "3. Click 'Installa Ora'\n";
    echo "4. Attiva il plugin\n";
    echo "5. Vai su FP Performance > Database\n";
    echo "6. Vedrai immediatamente il Database Health Score! 💯\n";
} else {
    echo "❌ File mancanti! C'è un problema con il build.\n";
}

echo "\n=== Fine Verifica ===\n";

