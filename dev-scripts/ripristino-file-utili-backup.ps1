# Script Ripristino File Utili dal Backup
# Data: 21 Ottobre 2025

Write-Host "RIPRISTINO FILE UTILI DAL BACKUP" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Gray
Write-Host ""

$BackupDir = "backup-cleanup-20251021-212939"
$ErrorCount = 0
$SuccessCount = 0

function Copy-FileWithVerification {
    param(
        [string]$Source,
        [string]$Destination,
        [string]$Description
    )
    
    Write-Host "File: $Description..." -NoNewline
    
    if (Test-Path $Source) {
        $DestDir = Split-Path -Parent $Destination
        if (!(Test-Path $DestDir)) {
            New-Item -ItemType Directory -Path $DestDir -Force | Out-Null
        }
        
        Copy-Item -Path $Source -Destination $Destination -Force
        
        if (Test-Path $Destination) {
            Write-Host " OK" -ForegroundColor Green
            return $true
        } else {
            Write-Host " ERRORE" -ForegroundColor Red
            return $false
        }
    } else {
        Write-Host " NON TROVATO" -ForegroundColor Yellow
        return $false
    }
}

Write-Host "FASE 1: Handler AJAX" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor Gray

if (!(Test-Path "src\Http\Ajax")) {
    New-Item -ItemType Directory -Path "src\Http\Ajax" -Force | Out-Null
    Write-Host "Directory src\Http\Ajax creata" -ForegroundColor Green
}

$ajaxFiles = @(
    @{Source = "$BackupDir\src\Http\Ajax\RecommendationsAjax.php"; Dest = "src\Http\Ajax\RecommendationsAjax.php"; Desc = "RecommendationsAjax.php"},
    @{Source = "$BackupDir\src\Http\Ajax\WebPAjax.php"; Dest = "src\Http\Ajax\WebPAjax.php"; Desc = "WebPAjax.php"},
    @{Source = "$BackupDir\src\Http\Ajax\CriticalCssAjax.php"; Dest = "src\Http\Ajax\CriticalCssAjax.php"; Desc = "CriticalCssAjax.php"},
    @{Source = "$BackupDir\src\Http\Ajax\AIConfigAjax.php"; Dest = "src\Http\Ajax\AIConfigAjax.php"; Desc = "AIConfigAjax.php"}
)

foreach ($file in $ajaxFiles) {
    if (Copy-FileWithVerification -Source $file.Source -Destination $file.Dest -Description $file.Desc) {
        $SuccessCount++
    } else {
        $ErrorCount++
    }
}

Write-Host ""
Write-Host "FASE 2: Componenti UI e Admin" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor Gray

if (!(Test-Path "src\Admin\Components")) {
    New-Item -ItemType Directory -Path "src\Admin\Components" -Force | Out-Null
    Write-Host "Directory src\Admin\Components creata" -ForegroundColor Green
}

$uiFiles = @(
    @{Source = "$BackupDir\src\Admin\Components\StatusIndicator.php"; Dest = "src\Admin\Components\StatusIndicator.php"; Desc = "StatusIndicator.php"},
    @{Source = "$BackupDir\src\Admin\ThemeHints.php"; Dest = "src\Admin\ThemeHints.php"; Desc = "ThemeHints.php"}
)

foreach ($file in $uiFiles) {
    if (Copy-FileWithVerification -Source $file.Source -Destination $file.Dest -Description $file.Desc) {
        $SuccessCount++
    } else {
        $ErrorCount++
    }
}

Write-Host ""
Write-Host "FASE 3: Edge Cache Providers CDN" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor Gray

if (!(Test-Path "src\Services\Cache\EdgeCache")) {
    New-Item -ItemType Directory -Path "src\Services\Cache\EdgeCache" -Force | Out-Null
    Write-Host "Directory src\Services\Cache\EdgeCache creata" -ForegroundColor Green
}

$edgeCacheFiles = @(
    @{Source = "$BackupDir\src\Services\Cache\EdgeCache\EdgeCacheProvider.php"; Dest = "src\Services\Cache\EdgeCache\EdgeCacheProvider.php"; Desc = "EdgeCacheProvider.php"},
    @{Source = "$BackupDir\src\Services\Cache\EdgeCache\CloudflareProvider.php"; Dest = "src\Services\Cache\EdgeCache\CloudflareProvider.php"; Desc = "CloudflareProvider.php"},
    @{Source = "$BackupDir\src\Services\Cache\EdgeCache\CloudFrontProvider.php"; Dest = "src\Services\Cache\EdgeCache\CloudFrontProvider.php"; Desc = "CloudFrontProvider.php"},
    @{Source = "$BackupDir\src\Services\Cache\EdgeCache\FastlyProvider.php"; Dest = "src\Services\Cache\EdgeCache\FastlyProvider.php"; Desc = "FastlyProvider.php"}
)

foreach ($file in $edgeCacheFiles) {
    if (Copy-FileWithVerification -Source $file.Source -Destination $file.Dest -Description $file.Desc) {
        $SuccessCount++
    } else {
        $ErrorCount++
    }
}

Write-Host ""
Write-Host "FASE 4: Ottimizzatori Assets" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor Gray

Write-Host "ATTENZIONE: FontOptimizer.php sara sostituito (734 righe vs 327)" -ForegroundColor Yellow

$optimizerFiles = @(
    @{Source = "$BackupDir\src\Services\Assets\FontOptimizer.php"; Dest = "src\Services\Assets\FontOptimizer.php"; Desc = "FontOptimizer.php (SOSTITUZIONE)"},
    @{Source = "$BackupDir\src\Services\Assets\BatchDOMUpdater.php"; Dest = "src\Services\Assets\BatchDOMUpdater.php"; Desc = "BatchDOMUpdater.php"},
    @{Source = "$BackupDir\src\Services\Assets\CSSOptimizer.php"; Dest = "src\Services\Assets\CSSOptimizer.php"; Desc = "CSSOptimizer.php"},
    @{Source = "$BackupDir\src\Services\Assets\jQueryOptimizer.php"; Dest = "src\Services\Assets\jQueryOptimizer.php"; Desc = "jQueryOptimizer.php"}
)

foreach ($file in $optimizerFiles) {
    if (Copy-FileWithVerification -Source $file.Source -Destination $file.Dest -Description $file.Desc) {
        $SuccessCount++
    } else {
        $ErrorCount++
    }
}

Write-Host ""
Write-Host "FASE 5: Utility" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor Gray

$utilFiles = @(
    @{Source = "$BackupDir\src\Utils\FormValidator.php"; Dest = "src\Utils\FormValidator.php"; Desc = "FormValidator.php"}
)

foreach ($file in $utilFiles) {
    if (Copy-FileWithVerification -Source $file.Source -Destination $file.Dest -Description $file.Desc) {
        $SuccessCount++
    } else {
        $ErrorCount++
    }
}

Write-Host ""
Write-Host "FASE 6: Documentazione" -ForegroundColor Yellow
Write-Host "------------------------------------------------------------" -ForegroundColor Gray

$docFiles = @(
    @{Source = "$BackupDir\src\Services\Intelligence\README.md"; Dest = "src\Services\Intelligence\README.md"; Desc = "Intelligence README.md"}
)

foreach ($file in $docFiles) {
    if (Copy-FileWithVerification -Source $file.Source -Destination $file.Dest -Description $file.Desc) {
        $SuccessCount++
    } else {
        $ErrorCount++
    }
}

Write-Host ""
Write-Host "============================================================" -ForegroundColor Gray
Write-Host "RIEPILOGO RIPRISTINO" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Gray
Write-Host "File copiati con successo: $SuccessCount" -ForegroundColor Green
Write-Host "File con errori: $ErrorCount" -ForegroundColor Red
Write-Host ""

if ($ErrorCount -eq 0) {
    Write-Host "RIPRISTINO COMPLETATO CON SUCCESSO!" -ForegroundColor Green
    Write-Host ""
    Write-Host "PROSSIMI STEP:" -ForegroundColor Yellow
    Write-Host "1. Registra i servizi in src\Plugin.php" -ForegroundColor White
    Write-Host "2. Registra EdgeCache providers nel ServiceContainer" -ForegroundColor White
    Write-Host "3. Testa le funzionalita AJAX" -ForegroundColor White
    Write-Host "4. IMPORTANTE: Testa FontOptimizer sostituito" -ForegroundColor Yellow
    Write-Host "5. Esegui test Lighthouse" -ForegroundColor White
    Write-Host "6. git add . && git commit" -ForegroundColor White
    Write-Host ""
    Write-Host "Vedi report per dettagli completi" -ForegroundColor Cyan
} else {
    Write-Host "RIPRISTINO COMPLETATO CON ERRORI!" -ForegroundColor Yellow
    Write-Host "Verifica manualmente i file non copiati" -ForegroundColor White
}

Write-Host ""
