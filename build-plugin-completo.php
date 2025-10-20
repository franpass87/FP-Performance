<?php
/**
 * Build Plugin Completo v1.4.0
 * 
 * Crea lo ZIP del plugin completo con tutte le funzionalità integrate
 * incluse le nuove ottimizzazioni database avanzate
 */

echo "=== Build FP Performance Suite v1.4.0 ===\n\n";

$pluginSlug = 'fp-performance-suite';
$version = '1.4.0';
$zipFile = "{$pluginSlug}.zip";
$buildDir = 'build-temp';

// Pulisci directory temporanea se esiste
if (is_dir($buildDir)) {
    echo "Pulizia directory build precedente...\n";
    shell_exec("rm -rf {$buildDir}");
}

// Crea directory temporanea
mkdir($buildDir);
$targetDir = "{$buildDir}/{$pluginSlug}";
mkdir($targetDir);

echo "Copiando file del plugin...\n\n";

// File e directory da includere
$includes = [
    'src/',
    'assets/',
    'languages/',
    'views/',
    'fp-performance-suite.php',
    'uninstall.php',
    'LICENSE',
    'readme.txt',
    'README.md',
];

// File e directory da escludere
$excludes = [
    '.git',
    '.gitignore',
    'node_modules',
    'vendor',
    'tests',
    'build',
    'build-temp',
    '*.zip',
    'composer.json',
    'composer.lock',
    'phpcs.xml',
    'phpunit.xml',
    '.DS_Store',
];

$copied = 0;
$totalSize = 0;

foreach ($includes as $item) {
    $source = __DIR__ . '/' . $item;
    $dest = $targetDir . '/' . $item;
    
    if (file_exists($source)) {
        if (is_dir($source)) {
            // Copia directory ricorsivamente
            echo "📁 Copiando directory: $item\n";
            copyDirectory($source, $dest);
        } else {
            // Copia file singolo
            echo "📄 Copiando file: $item\n";
            copy($source, $dest);
            $totalSize += filesize($source);
            $copied++;
        }
    } else {
        echo "⚠️  Non trovato: $item\n";
    }
}

// Verifica che i nuovi file di ottimizzazione database siano inclusi
echo "\n=== Verifica File Ottimizzazione Database ===\n";
$dbOptFiles = [
    'src/Services/DB/DatabaseOptimizer.php',
    'src/Services/DB/DatabaseQueryMonitor.php',
    'src/Services/DB/PluginSpecificOptimizer.php',
    'src/Services/DB/DatabaseReportService.php',
];

foreach ($dbOptFiles as $file) {
    $fullPath = $targetDir . '/' . $file;
    if (file_exists($fullPath)) {
        $size = round(filesize($fullPath) / 1024, 1);
        echo "✅ $file ($size KB)\n";
    } else {
        echo "❌ MANCANTE: $file\n";
    }
}

echo "\n=== Creazione ZIP ===\n";

// Rimuovi ZIP precedente se esiste
if (file_exists($zipFile)) {
    unlink($zipFile);
}

// Crea ZIP
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("❌ Impossibile creare il file ZIP\n");
}

// Aggiungi tutti i file al ZIP
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($targetDir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$fileCount = 0;
foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($buildDir) + 1);
        
        $zip->addFile($filePath, $relativePath);
        $fileCount++;
    }
}

$zip->close();

// Pulisci directory temporanea
shell_exec("rm -rf {$buildDir}");

// Statistiche finali
$zipSize = filesize($zipFile);
$zipSizeMB = round($zipSize / 1024 / 1024, 2);

echo "\n🎉 Build Completato!\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "📦 File ZIP: $zipFile\n";
echo "📊 Dimensione: $zipSizeMB MB\n";
echo "📁 File inclusi: $fileCount\n";
echo "🏷️  Versione: $version\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "✅ Plugin pronto per:\n";
echo "  • Upload su WordPress.org\n";
echo "  • Installazione manuale\n";
echo "  • Distribuzione GitHub\n";
echo "  • Deployment su server di produzione\n\n";

echo "🚀 Nuove funzionalità v1.4.0:\n";
echo "  ✅ Database Health Score (0-100%)\n";
echo "  ✅ Analisi frammentazione avanzata\n";
echo "  ✅ Rilevamento indici mancanti\n";
echo "  ✅ Conversione MyISAM → InnoDB\n";
echo "  ✅ Ottimizzazione charset (utf8mb4)\n";
echo "  ✅ Analisi autoload dettagliata\n";
echo "  ✅ Plugin-specific cleanup (WooCommerce, Elementor, Yoast)\n";
echo "  ✅ Report & Trend con export JSON/CSV\n";
echo "  ✅ 5 nuovi comandi WP-CLI\n";
echo "  ✅ Interfaccia admin rinnovata\n\n";

echo "Fatto! 🎊\n";

// Funzione helper per copiare directory ricorsivamente
function copyDirectory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $dir = opendir($source);
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $srcFile = $source . '/' . $file;
            $destFile = $dest . '/' . $file;
            
            if (is_dir($srcFile)) {
                copyDirectory($srcFile, $destFile);
            } else {
                copy($srcFile, $destFile);
            }
        }
    }
    closedir($dir);
}

