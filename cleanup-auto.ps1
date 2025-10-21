# Cleanup Repository Structure - Auto (senza conferma)

Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "  FP Performance Suite - Auto Cleanup" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Operazioni:" -ForegroundColor Yellow
Write-Host "  Eliminera: /src/, /fp-performance-suite.php, /build/" -ForegroundColor Red
Write-Host "  Manterra: /fp-performance-suite/" -ForegroundColor Green
Write-Host ""

Write-Host "Creazione backup..." -ForegroundColor Cyan

$backupDir = "backup-cleanup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

# Backup
if (Test-Path "src") {
    Copy-Item -Path "src" -Destination "$backupDir/src" -Recurse -Force
    Write-Host "  Backup src/" -ForegroundColor Gray
}

if (Test-Path "fp-performance-suite.php") {
    Copy-Item -Path "fp-performance-suite.php" -Destination "$backupDir/" -Force
    Write-Host "  Backup fp-performance-suite.php" -ForegroundColor Gray
}

if (Test-Path "build") {
    Copy-Item -Path "build" -Destination "$backupDir/build" -Recurse -Force
    Write-Host "  Backup build/" -ForegroundColor Gray
}

Write-Host "Backup creato: $backupDir" -ForegroundColor Green
Write-Host ""

# Eliminazione
Write-Host "Eliminazione file..." -ForegroundColor Yellow

if (Test-Path "src") {
    Remove-Item -Path "src" -Recurse -Force
    Write-Host "  Rimosso: /src/" -ForegroundColor Green
}

if (Test-Path "fp-performance-suite.php") {
    Remove-Item -Path "fp-performance-suite.php" -Force
    Write-Host "  Rimosso: /fp-performance-suite.php" -ForegroundColor Green
}

if (Test-Path "build") {
    Remove-Item -Path "build" -Recurse -Force
    Write-Host "  Rimosso: /build/" -ForegroundColor Green
}

if (Test-Path "fp-performance-suite.zip") {
    Remove-Item -Path "fp-performance-suite.zip" -Force
    Write-Host "  Rimosso: /fp-performance-suite.zip" -ForegroundColor Green
}

Write-Host ""
Write-Host "=======================================" -ForegroundColor Green
Write-Host "  PULIZIA COMPLETATA!" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green
Write-Host ""

Write-Host "Nuova struttura:" -ForegroundColor Cyan
Write-Host "  FP-Performance/" -ForegroundColor White
Write-Host "    - fp-performance-suite/  (sorgente unico)" -ForegroundColor Green
Write-Host "    - build-plugin.ps1       (script build)" -ForegroundColor Cyan
Write-Host "    - docs/                  (documentazione)" -ForegroundColor Gray
Write-Host "    - tests/                 (test)" -ForegroundColor Gray
Write-Host "    - $backupDir/" -ForegroundColor Yellow
Write-Host ""

Write-Host "Verifica che tutto funzioni:" -ForegroundColor Cyan
Write-Host "  .\build-plugin.ps1" -ForegroundColor White
Write-Host ""

# Test build automatico
Write-Host "Esecuzione build automatico..." -ForegroundColor Cyan
& ".\build-plugin.ps1"

Write-Host ""
Write-Host "FATTO! Repository pulito." -ForegroundColor Green
Write-Host "Backup in: $backupDir (eliminalo se tutto ok)" -ForegroundColor Yellow

