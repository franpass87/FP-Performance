# Script PowerShell per eliminare pagine ridondanti dal plugin FP Performance Suite
# Data: 21 Ottobre 2025
# Autore: AI Assistant

Write-Host "`n🧹 FP Performance Suite - Cleanup Ridondanze" -ForegroundColor Cyan
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "FASE 1: Eliminazione File Ridondanti al 100%" -ForegroundColor Yellow
Write-Host "================================================"
Write-Host ""
Write-Host "I seguenti file sono COMPLETAMENTE ridondanti (funzionalità migrate):"
Write-Host ""
Write-Host "  1. Compression.php        → Funzionalità in InfrastructureCdn.php"
Write-Host "  2. Tools.php              → Funzionalità in Settings.php"
Write-Host "  3. ScheduledReports.php   → Funzionalità in MonitoringReports.php"
Write-Host "  4. AIConfigAdvanced.php   → Duplicato di AIConfig.php"
Write-Host "  5. _Presets_OLD.php       → File backup obsoleto"
Write-Host ""

$confirm = Read-Host "Vuoi procedere con l'eliminazione dei 5 file ridondanti? (y/n)"

if ($confirm -eq 'y' -or $confirm -eq 'Y') {
    Write-Host ""
    Write-Host "Eliminazione in corso..."
    
    $files = @(
        "src\Admin\Pages\Compression.php",
        "src\Admin\Pages\Tools.php",
        "src\Admin\Pages\ScheduledReports.php",
        "src\Admin\Pages\AIConfigAdvanced.php",
        "src\Admin\Pages\_Presets_OLD.php"
    )
    
    foreach ($file in $files) {
        if (Test-Path $file) {
            Remove-Item $file -Force
            Write-Host "  ✓ Eliminato: $file" -ForegroundColor Green
        } else {
            Write-Host "  ⚠ File non trovato: $file" -ForegroundColor Yellow
        }
    }
    
    Write-Host ""
    Write-Host "✓ Eliminazione completata!" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "⚠ Eliminazione annullata" -ForegroundColor Yellow
    Write-Host ""
}

Write-Host ""
Write-Host "FASE 2: File da Valutare" -ForegroundColor Yellow
Write-Host "=========================="
Write-Host ""
Write-Host "I seguenti file richiedono una decisione:"
Write-Host ""
Write-Host "  • UnusedCSS.php                  → Funzionalità UNICA (130 KiB saving)"
Write-Host "  • LighthouseFontOptimization.php → Funzionalità parzialmente coperta"
Write-Host "  • Presets.php                    → UI per preset (service già esiste)"
Write-Host ""
Write-Host "Consulta REPORT_RIDONDANZE_MENU_AGGIORNATO.md per dettagli"
Write-Host ""

$confirmPresets = Read-Host "Vuoi eliminare anche Presets.php (consigliato)? (y/n)"

if ($confirmPresets -eq 'y' -or $confirmPresets -eq 'Y') {
    if (Test-Path "src\Admin\Pages\Presets.php") {
        Remove-Item "src\Admin\Pages\Presets.php" -Force
        Write-Host "  ✓ Eliminato: Presets.php" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "FASE 3: Git Commit" -ForegroundColor Yellow
Write-Host "==================="
Write-Host ""

$confirmGit = Read-Host "Vuoi creare un commit git con le modifiche? (y/n)"

if ($confirmGit -eq 'y' -or $confirmGit -eq 'Y') {
    git add -A
    git commit -m @"
chore: remove redundant admin pages

- Removed Compression.php (migrated to InfrastructureCdn.php)
- Removed Tools.php (migrated to Settings.php)
- Removed ScheduledReports.php (migrated to MonitoringReports.php)
- Removed AIConfigAdvanced.php (duplicate of AIConfig.php)
- Removed _Presets_OLD.php (obsolete backup)

All functionality preserved in active pages.
See REPORT_RIDONDANZE_MENU_AGGIORNATO.md for details.
"@
    
    Write-Host ""
    Write-Host "✓ Commit creato con successo!" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "⚠ Commit annullato. Ricordati di committare manualmente." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "==============================================" -ForegroundColor Cyan
Write-Host "🎉 Cleanup completato!" -ForegroundColor Green
Write-Host ""
Write-Host "Prossimi passi:"
Write-Host "  1. Rivedi REPORT_RIDONDANZE_MENU_AGGIORNATO.md"
Write-Host "  2. Decidi su UnusedCSS.php (integra o elimina)"
Write-Host "  3. Decidi su LighthouseFontOptimization.php"
Write-Host "  4. Testa il plugin per verificare tutto funzioni"
Write-Host ""

# Pausa finale per leggere il messaggio
Read-Host "Premi ENTER per uscire"

