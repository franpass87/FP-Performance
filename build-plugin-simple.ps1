# Build FP Performance Suite v1.5.0 - Fix WSOD
# Script semplificato senza emoji

$ErrorActionPreference = "Stop"

Write-Host "Build FP Performance Suite v1.5.0 - Fix WSOD" -ForegroundColor Cyan
Write-Host ""

$pluginSlug = "FP-Performance"
$zipFile = "fp-performance-suite.zip"
$buildDir = "build-temp"

# Pulizia
if (Test-Path $buildDir) {
    Remove-Item -Path $buildDir -Recurse -Force
}
if (Test-Path $zipFile) {
    Remove-Item -Path $zipFile -Force
}

# Crea struttura
New-Item -ItemType Directory -Path "$buildDir\$pluginSlug" -Force | Out-Null

$targetDir = "$buildDir\$pluginSlug"

Write-Host "Copiando file..." -ForegroundColor Green

# File principale dalla ROOT (con fix WSOD)
Copy-Item -Path "fp-performance-suite.php" -Destination "$targetDir\" -Force
Write-Host "  [OK] fp-performance-suite.php (ROOT - CON FIX)" -ForegroundColor Green

# Crea directory fp-performance-suite nel target
$targetPluginDir = "$targetDir\fp-performance-suite"
New-Item -ItemType Directory -Path $targetPluginDir -Force | Out-Null

# Copia directory complete
Copy-Item -Path "fp-performance-suite\src" -Destination "$targetPluginDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/src/" -ForegroundColor Green

Copy-Item -Path "fp-performance-suite\assets" -Destination "$targetPluginDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/assets/" -ForegroundColor Green

Copy-Item -Path "fp-performance-suite\languages" -Destination "$targetPluginDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/languages/" -ForegroundColor Green

Copy-Item -Path "fp-performance-suite\views" -Destination "$targetPluginDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/views/" -ForegroundColor Green

# Altri file
Copy-Item -Path "fp-performance-suite\uninstall.php" -Destination "$targetDir\" -Force
Write-Host "  [OK] uninstall.php" -ForegroundColor Green

if (Test-Path "LICENSE") {
    Copy-Item -Path "LICENSE" -Destination "$targetDir\" -Force
}
if (Test-Path "readme.txt") {
    Copy-Item -Path "readme.txt" -Destination "$targetDir\" -Force
}

# Verifica file critici
Write-Host "`nVerifica file critici..." -ForegroundColor Cyan

$criticalOK = $true

$mainFile = "$targetDir\fp-performance-suite.php"
if (Test-Path $mainFile) {
    Write-Host "  [OK] fp-performance-suite.php" -ForegroundColor Green
} else {
    Write-Host "  [ERRORE] fp-performance-suite.php MANCANTE" -ForegroundColor Red
    $criticalOK = $false
}

$pluginFile = "$targetPluginDir\src\Plugin.php"
if (Test-Path $pluginFile) {
    Write-Host "  [OK] fp-performance-suite/src/Plugin.php" -ForegroundColor Green
} else {
    Write-Host "  [ERRORE] Plugin.php MANCANTE" -ForegroundColor Red
    $criticalOK = $false
}

$containerFile = "$targetPluginDir\src\ServiceContainer.php"
if (Test-Path $containerFile) {
    Write-Host "  [OK] fp-performance-suite/src/ServiceContainer.php" -ForegroundColor Green
} else {
    Write-Host "  [ERRORE] ServiceContainer.php MANCANTE" -ForegroundColor Red
    $criticalOK = $false
}

if (-not $criticalOK) {
    Write-Host "`n[ERRORE] File critici mancanti! Build interrotto." -ForegroundColor Red
    Remove-Item -Path $buildDir -Recurse -Force
    exit 1
}

# Crea ZIP
Write-Host "`nCreazione ZIP..." -ForegroundColor Cyan
Compress-Archive -Path "$buildDir\*" -DestinationPath $zipFile -Force

# Pulisci
Remove-Item -Path $buildDir -Recurse -Force

# Statistiche
$zipSize = (Get-Item $zipFile).Length
$zipSizeMB = [math]::Round($zipSize / 1MB, 2)

Write-Host "`n=====================================" -ForegroundColor Green
Write-Host " Build Completato!" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green
Write-Host "File: $zipFile" -ForegroundColor White
Write-Host "Dimensione: $zipSizeMB MB" -ForegroundColor White
Write-Host "Versione: 1.5.0 + FIX WSOD" -ForegroundColor White
Write-Host "=====================================" -ForegroundColor Green

Write-Host "`nFIX WSOD INCLUSO:" -ForegroundColor Yellow
Write-Host "  - Caricamento lazy della classe Plugin"
Write-Host "  - Verifica file esistenti"
Write-Host "  - Try-catch completi"
Write-Host "  - Verifica database"
Write-Host "  - Admin notices invece di WSOD"

Write-Host "`nInstallazione:" -ForegroundColor Cyan
Write-Host "  WordPress: Plugin > Aggiungi nuovo > Carica plugin"
Write-Host "  FTP: Estrai e carica in wp-content/plugins/"

Write-Host "`nFatto!" -ForegroundColor Green

