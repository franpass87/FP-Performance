<?php
/**
 * Crea Pacchetto Aggiornamento per Server
 * 
 * Questo script crea uno ZIP con solo i file necessari
 * per aggiornare il plugin sul server
 * 
 * Uso: php crea-pacchetto-aggiornamento.php
 */

echo "=== Creazione Pacchetto Aggiornamento ===\n\n";

$zipFile = 'aggiornamento-database-optimization.zip';
$baseDir = __DIR__;

// File da includere nel pacchetto
$files = [
    // Nuovi file
    'src/Services/DB/DatabaseOptimizer.php',
    'src/Services/DB/DatabaseQueryMonitor.php',
    'src/Services/DB/PluginSpecificOptimizer.php',
    'src/Services/DB/DatabaseReportService.php',
    
    // File aggiornati
    'src/Plugin.php',
    'src/Admin/Pages/Database.php',
    'src/Cli/Commands.php',
];

// Crea ZIP
$zip = new ZipArchive();

if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("❌ Impossibile creare il file ZIP\n");
}

$totalSize = 0;
$addedFiles = 0;

foreach ($files as $file) {
    $fullPath = $baseDir . '/' . $file;
    
    if (file_exists($fullPath)) {
        $zip->addFile($fullPath, $file);
        $size = filesize($fullPath);
        $totalSize += $size;
        $addedFiles++;
        echo "✅ Aggiunto: $file (" . round($size / 1024, 1) . " KB)\n";
    } else {
        echo "⚠️  File non trovato: $file\n";
    }
}

// Aggiungi README con istruzioni
$readme = "=== ISTRUZIONI INSTALLAZIONE ===

1. Estrai questo ZIP
2. Carica i file sul server via FTP nella directory:
   /wp-content/plugins/FP-Performance/
   
   Mantieni la struttura delle directory!
   
3. I file andranno in:
   - src/Services/DB/ (4 nuovi file)
   - src/ (Plugin.php)
   - src/Admin/Pages/ (Database.php)
   - src/Cli/ (Commands.php)

4. Dopo il caricamento:
   - Disattiva il plugin
   - Riattiva il plugin
   - Vai su FP Performance > Database
   
5. Dovresti vedere il nuovo Dashboard Health Score!

=== FILE INCLUSI ===

NUOVI FILE (funzionalità avanzate):
✅ DatabaseOptimizer.php - Analisi frammentazione, indici, charset
✅ DatabaseQueryMonitor.php - Monitoraggio query in tempo reale
✅ PluginSpecificOptimizer.php - Cleanup WooCommerce, Elementor, etc.
✅ DatabaseReportService.php - Health Score, Report, Trend

FILE AGGIORNATI:
✅ Plugin.php - Registrazione nuovi servizi
✅ Database.php - Nuova interfaccia admin
✅ Commands.php - Nuovi comandi WP-CLI

=== SUPPORTO ===

Se hai problemi:
1. Verifica permessi file (644)
2. Controlla debug.log
3. Riavvia PHP-FPM se disponibile

Data pacchetto: " . date('Y-m-d H:i:s') . "
";

$zip->addFromString('README.txt', $readme);
echo "✅ Aggiunto: README.txt\n";

$zip->close();

echo "\n🎉 Pacchetto creato con successo!\n";
echo "📦 File: $zipFile\n";
echo "📊 Dimensione: " . round(filesize($zipFile) / 1024, 1) . " KB\n";
echo "📁 File inclusi: $addedFiles\n";
echo "\n📤 Ora puoi:\n";
echo "1. Scaricare il file '$zipFile'\n";
echo "2. Estrarlo sul tuo computer\n";
echo "3. Caricare i file sul server via FTP\n";
echo "4. Mantenere la struttura delle directory!\n";
echo "\n✅ Fatto!\n";

