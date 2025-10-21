# Cleanup Repository Structure - Versione Semplificata

Write-Host "=======================================" -ForegroundColor Cyan
Write-Host "  FP Performance Suite - Cleanup" -ForegroundColor Cyan
Write-Host "=======================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "ATTENZIONE!" -ForegroundColor Yellow
Write-Host ""
Write-Host "Questo script eliminera:" -ForegroundColor Yellow
Write-Host "  - /src/ (vecchia, 149 file)" -ForegroundColor Red
Write-Host "  - /fp-performance-suite.php (root)" -ForegroundColor Red
Write-Host "  - /build/ (rigenerabile)" -ForegroundColor Red
Write-Host ""
Write-Host "Manterra:" -ForegroundColor Green
Write-Host "  - /fp-performance-suite/ (completa, 235 file)" -ForegroundColor Green
Write-Host ""

$confirm = Read-Host "Continuare? (si/no)"
if ($confirm -ne "si") {
    Write-Host "Annullato" -ForegroundColor Red
    exit 0
}

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

Write-Host "Prossimi passi:" -ForegroundColor Cyan
Write-Host "  1. Testa build: .\build-plugin.ps1" -ForegroundColor White
Write-Host "  2. Verifica plugin localmente" -ForegroundColor White
Write-Host "  3. Commit:" -ForegroundColor White
Write-Host "     git add ." -ForegroundColor Gray
Write-Host "     git commit -m 'refactor: cleaned duplicate src/'" -ForegroundColor Gray
Write-Host "  4. Elimina backup se ok:" -ForegroundColor White
Write-Host "     Remove-Item -Path '$backupDir' -Recurse -Force" -ForegroundColor Gray
Write-Host ""
Write-Host "FATTO!" -ForegroundColor Green

