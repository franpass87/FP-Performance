# Build FP Performance Suite per WordPress
# Struttura corretta per l'installazione manuale

$ErrorActionPreference = "Stop"

Write-Host "Build FP Performance Suite v1.5.0 per WordPress" -ForegroundColor Cyan
Write-Host ""

$pluginSlug = "fp-performance-suite"
$zipFile = "fp-performance-suite-wordpress.zip"
$buildDir = "build-wp-temp"

# Pulizia
if (Test-Path $buildDir) { Remove-Item -Path $buildDir -Recurse -Force }
if (Test-Path $zipFile) { Remove-Item -Path $zipFile -Force }

# Crea struttura corretta per WordPress
# WordPress si aspetta: zip/fp-performance-suite/fp-performance-suite.php
New-Item -ItemType Directory -Path "$buildDir\$pluginSlug" -Force | Out-Null
$targetDir = "$buildDir\$pluginSlug"

Write-Host "Copiando file..." -ForegroundColor Green

# File principale dalla ROOT (con tutti i fix)
Copy-Item -Path "fp-performance-suite.php" -Destination "$targetDir\" -Force
Write-Host "  [OK] fp-performance-suite.php (ROOT - CON TUTTI I FIX)" -ForegroundColor Green

# Crea la sottodirectory fp-performance-suite
$subDir = "$targetDir\fp-performance-suite"
New-Item -ItemType Directory -Path $subDir -Force | Out-Null

# Copia tutte le directory del plugin
Copy-Item -Path "fp-performance-suite\src" -Destination "$subDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/src/ (72 file)" -ForegroundColor Green

Copy-Item -Path "fp-performance-suite\assets" -Destination "$subDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/assets/" -ForegroundColor Green

Copy-Item -Path "fp-performance-suite\languages" -Destination "$subDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/languages/" -ForegroundColor Green

Copy-Item -Path "fp-performance-suite\views" -Destination "$subDir\" -Recurse -Force
Write-Host "  [OK] fp-performance-suite/views/" -ForegroundColor Green

# Altri file
Copy-Item -Path "fp-performance-suite\uninstall.php" -Destination "$targetDir\" -Force
Write-Host "  [OK] uninstall.php" -ForegroundColor Green

if (Test-Path "LICENSE") { Copy-Item -Path "LICENSE" -Destination "$targetDir\" -Force }
if (Test-Path "readme.txt") { Copy-Item -Path "readme.txt" -Destination "$targetDir\" -Force }

# Verifica file critici
Write-Host "`nVerifica file critici..." -ForegroundColor Cyan

$critical = @(
    "$targetDir\fp-performance-suite.php",
    "$subDir\src\Plugin.php",
    "$subDir\src\ServiceContainer.php",
    "$subDir\src\Services\Cache\PageCache.php"
)

$allOK = $true
foreach ($file in $critical) {
    if (Test-Path $file) {
        $name = $file -replace [regex]::Escape($targetDir + "\"), ""
        Write-Host "  [OK] $name" -ForegroundColor Green
    } else {
        $name = $file -replace [regex]::Escape($targetDir + "\"), ""
        Write-Host "  [ERRORE] MANCANTE: $name" -ForegroundColor Red
        $allOK = $false
    }
}

if (-not $allOK) {
    Write-Host "`n[ERRORE] Build fallito - file mancanti!" -ForegroundColor Red
    exit 1
}

Write-Host "`nStruttura finale:" -ForegroundColor Cyan
Write-Host "fp-performance-suite/"
Write-Host "  ├── fp-performance-suite.php       (FILE PRINCIPALE)" -ForegroundColor Yellow
Write-Host "  ├── uninstall.php"
Write-Host "  ├── LICENSE"
Write-Host "  ├── readme.txt"
Write-Host "  └── fp-performance-suite/"
Write-Host "      ├── src/                        (72 file PHP)"
Write-Host "      ├── assets/"
Write-Host "      ├── languages/"
Write-Host "      └── views/"

# Crea ZIP
Write-Host "`nCreazione ZIP..." -ForegroundColor Cyan
Compress-Archive -Path "$buildDir\*" -DestinationPath $zipFile -Force

# Pulisci
Remove-Item -Path $buildDir -Recurse -Force

$zipSize = [math]::Round((Get-Item $zipFile).Length / 1MB, 2)

Write-Host "`n========================================" -ForegroundColor Green
Write-Host " Build Completato!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host "File: $zipFile" -ForegroundColor White
Write-Host "Dimensione: $zipSize MB" -ForegroundColor White
Write-Host "Struttura: CORRETTA per WordPress" -ForegroundColor Yellow
Write-Host "========================================`n" -ForegroundColor Green

Write-Host "Come installare:" -ForegroundColor Cyan
Write-Host "  1. WordPress -> Plugin -> Aggiungi nuovo -> Carica plugin"
Write-Host "  2. Seleziona: $zipFile"
Write-Host "  3. Installa e attiva"
Write-Host ""
Write-Host "Fatto!" -ForegroundColor Green

