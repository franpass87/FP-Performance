# Build FP Performance Suite v1.5.0 - Con Fix WSOD
# Script aggiornato per includere il file wrapper dalla root

Write-Host "=== Build FP Performance Suite v1.5.0 - FIX WSOD ===" -ForegroundColor Cyan
Write-Host ""

$pluginSlug = "FP-Performance"
$zipFile = "fp-performance-suite.zip"
$buildDir = "build-temp"

# Rimuovi build precedente
if (Test-Path $buildDir) {
    Write-Host "Pulizia build precedente..." -ForegroundColor Yellow
    Remove-Item -Path $buildDir -Recurse -Force
}

# Rimuovi ZIP precedente
if (Test-Path $zipFile) {
    Write-Host "Rimozione ZIP precedente..." -ForegroundColor Yellow
    Remove-Item -Path $zipFile -Force
}

# Crea directory temporanea con nome corretto per WordPress
New-Item -ItemType Directory -Path $buildDir | Out-Null
$targetDir = "$buildDir\$pluginSlug"
New-Item -ItemType Directory -Path $targetDir | Out-Null

Write-Host "Copiando file del plugin...`n" -ForegroundColor Green

# === IMPORTANTE: File principale dalla ROOT (con fix WSOD) ===
Write-Host "📝 Copiando file principale dalla root..." -ForegroundColor Cyan
Copy-Item -Path "fp-performance-suite.php" -Destination "$targetDir\" -Force
Write-Host "✅ fp-performance-suite.php (dalla ROOT - con fix WSOD)" -ForegroundColor Green

# === Copia directory dal plugin source ===
$sourceDir = "fp-performance-suite"

# Crea la directory fp-performance-suite nel target
$targetPluginDir = "$targetDir\fp-performance-suite"
New-Item -ItemType Directory -Path $targetPluginDir | Out-Null

# Copia directory
Copy-Item -Path "$sourceDir\src" -Destination "$targetPluginDir\src" -Recurse -Force
Write-Host "✅ fp-performance-suite/src/" -ForegroundColor Green

Copy-Item -Path "$sourceDir\assets" -Destination "$targetPluginDir\assets" -Recurse -Force
Write-Host "✅ fp-performance-suite/assets/" -ForegroundColor Green

Copy-Item -Path "$sourceDir\languages" -Destination "$targetPluginDir\languages" -Recurse -Force
Write-Host "✅ fp-performance-suite/languages/" -ForegroundColor Green

Copy-Item -Path "$sourceDir\views" -Destination "$targetPluginDir\views" -Recurse -Force
Write-Host "✅ fp-performance-suite/views/" -ForegroundColor Green

# Copia file singoli nella root del plugin
Copy-Item -Path "$sourceDir\uninstall.php" -Destination "$targetDir\" -Force
Write-Host "✅ uninstall.php" -ForegroundColor Green

# Verifica che LICENSE esista, altrimenti usa quello nella cartella fp-performance-suite
if (Test-Path "LICENSE") {
    Copy-Item -Path "LICENSE" -Destination "$targetDir\" -Force
    Write-Host "✅ LICENSE" -ForegroundColor Green
} elseif (Test-Path "$sourceDir\LICENSE") {
    Copy-Item -Path "$sourceDir\LICENSE" -Destination "$targetDir\" -Force
    Write-Host "✅ LICENSE (da fp-performance-suite/)" -ForegroundColor Green
}

# Verifica readme.txt
if (Test-Path "readme.txt") {
    Copy-Item -Path "readme.txt" -Destination "$targetDir\" -Force
    Write-Host "✅ readme.txt" -ForegroundColor Green
} elseif (Test-Path "$sourceDir\readme.txt") {
    Copy-Item -Path "$sourceDir\readme.txt" -Destination "$targetDir\" -Force
    Write-Host "✅ readme.txt (da fp-performance-suite/)" -ForegroundColor Green
}

Write-Host "`n=== Verifica Struttura ===" -ForegroundColor Cyan
Write-Host "Struttura creata:"
Write-Host "build-temp/"
Write-Host "  └── FP-Performance/"
Write-Host "      ├── fp-performance-suite.php     (ROOT - CON FIX WSOD)" -ForegroundColor Yellow
Write-Host "      ├── uninstall.php"
Write-Host "      ├── LICENSE"
Write-Host "      ├── readme.txt"
Write-Host "      └── fp-performance-suite/"
Write-Host "          ├── src/                      (SORGENTE COMPLETO)"
Write-Host "          ├── assets/"
Write-Host "          ├── languages/"
Write-Host "          └── views/"

# Verifica file critici
Write-Host "`n=== Verifica File Critici ===" -ForegroundColor Cyan

$criticalFiles = @(
    "$targetDir\fp-performance-suite.php",
    "$targetPluginDir\src\Plugin.php",
    "$targetPluginDir\src\ServiceContainer.php"
)

$allPresent = $true
foreach ($file in $criticalFiles) {
    if (Test-Path $file) {
        $size = [math]::Round((Get-Item $file).Length / 1KB, 1)
        $name = $file -replace [regex]::Escape($targetDir), ""
        Write-Host "✅ $name ($size KB)" -ForegroundColor Green
    } else {
        $name = $file -replace [regex]::Escape($targetDir), ""
        Write-Host "❌ MANCANTE: $name" -ForegroundColor Red
        $allPresent = $false
    }
}

Write-Host ""

if (-not $allPresent) {
    Write-Host "❌ File critici mancanti! Build interrotto." -ForegroundColor Red
    exit 1
}

# Verifica file ottimizzazione database
Write-Host "=== Verifica File Database Optimization ===" -ForegroundColor Cyan

$dbFiles = @(
    "$targetPluginDir\src\Services\DB\DatabaseOptimizer.php",
    "$targetPluginDir\src\Services\DB\DatabaseQueryMonitor.php",
    "$targetPluginDir\src\Services\DB\PluginSpecificOptimizer.php",
    "$targetPluginDir\src\Services\DB\DatabaseReportService.php"
)

foreach ($file in $dbFiles) {
    if (Test-Path $file) {
        $size = [math]::Round((Get-Item $file).Length / 1KB, 1)
        $name = Split-Path $file -Leaf
        Write-Host "✅ $name ($size KB)" -ForegroundColor Green
    } else {
        $name = Split-Path $file -Leaf
        Write-Host "⚠️  $name (non presente - opzionale)" -ForegroundColor Yellow
    }
}

Write-Host ""

# Crea ZIP
Write-Host "Creazione archivio ZIP..." -ForegroundColor Cyan

Compress-Archive -Path "$buildDir\*" -DestinationPath $zipFile -Force

# Pulisci directory temporanea
Remove-Item -Path $buildDir -Recurse -Force

# Statistiche
$zipSize = (Get-Item $zipFile).Length
$zipSizeMB = [math]::Round($zipSize / 1MB, 2)
$zipSizeKB = [math]::Round($zipSize / 1KB, 0)

Write-Host "`n*** Build Completato con Successo! ***" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "File ZIP: $zipFile" -ForegroundColor White
Write-Host "Dimensione: $zipSizeMB MB ($zipSizeKB KB)" -ForegroundColor White
Write-Host "Versione: 1.5.0 + FIX WSOD" -ForegroundColor White
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "[OK] Plugin pronto per l'installazione!" -ForegroundColor Green
Write-Host "`nFIX WSOD INCLUSO:" -ForegroundColor Yellow
Write-Host "  [OK] Caricamento lazy della classe Plugin"
Write-Host "  [OK] Verifica file esistenti prima del caricamento"
Write-Host "  [OK] Try-catch su tutti i require_once"
Write-Host "  [OK] Verifica database disponibile"
Write-Host "  [OK] Admin notices invece di WSOD"
Write-Host "  [OK] Gestione errori robusta"
Write-Host ""
Write-Host "Come installare:" -ForegroundColor Cyan
Write-Host "  1. Vai su WordPress -> Plugin -> Aggiungi nuovo"
Write-Host "  2. Clicca 'Carica plugin'"
Write-Host "  3. Seleziona: $zipFile"
Write-Host "  4. Clicca 'Installa ora'"
Write-Host "  5. Attiva il plugin"
Write-Host ""
Write-Host "Oppure carica via FTP:"
Write-Host "  1. Estrai il contenuto del ZIP"
Write-Host "  2. Carica la cartella 'FP-Performance' in wp-content/plugins/"
Write-Host "  3. Attiva il plugin da WordPress admin"
Write-Host ""
Write-Host "Fatto!`n" -ForegroundColor Cyan

