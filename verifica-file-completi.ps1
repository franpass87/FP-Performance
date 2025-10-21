# Verifica che tutti i file referenziati in Plugin.php esistano
# Script di controllo completo

$ErrorActionPreference = "Continue"
$baseDir = "fp-performance-suite\src"
$missing = @()
$found = @()

Write-Host "`n=== Verifica File Plugin ===" -ForegroundColor Cyan
Write-Host "Directory base: $baseDir`n" -ForegroundColor White

# Lista completa di tutte le classi referenziate in Plugin.php
$classes = @(
    # Core
    "Plugin.php",
    "ServiceContainer.php",
    
    # Admin
    "Admin\Menu.php",
    "Admin\Assets.php",
    "Admin\AdminBar.php",
    
    # Http
    "Http\Routes.php",
    
    # Services - Cache
    "Services\Cache\PageCache.php",
    "Services\Cache\Headers.php",
    "Services\Cache\ObjectCacheManager.php",
    "Services\Cache\EdgeCacheManager.php",
    
    # Services - Assets
    "Services\Assets\Optimizer.php",
    "Services\Assets\HtmlMinifier.php",
    "Services\Assets\ScriptOptimizer.php",
    "Services\Assets\WordPressOptimizer.php",
    "Services\Assets\CriticalCss.php",
    "Services\Assets\CriticalCssAutomation.php",
    "Services\Assets\LazyLoadManager.php",
    "Services\Assets\FontOptimizer.php",
    "Services\Assets\AutoFontOptimizer.php",
    "Services\Assets\LighthouseFontOptimizer.php",
    "Services\Assets\ImageOptimizer.php",
    "Services\Assets\Http2ServerPush.php",
    "Services\Assets\ThirdPartyScriptManager.php",
    "Services\Assets\ThirdPartyScriptDetector.php",
    "Services\Assets\PredictivePrefetching.php",
    "Services\Assets\SmartAssetDelivery.php",
    "Services\Assets\ThemeAssetConfiguration.php",
    "Services\Assets\ResourceHints\ResourceHintsManager.php",
    "Services\Assets\Combiners\DependencyResolver.php",
    
    # Services - Media
    "Services\Media\WebPConverter.php",
    "Services\Media\AVIFConverter.php",
    "Services\Media\WebP\WebPPathHelper.php",
    "Services\Media\WebP\WebPImageConverter.php",
    "Services\Media\WebP\WebPQueue.php",
    "Services\Media\WebP\WebPAttachmentProcessor.php",
    "Services\Media\WebP\WebPBatchProcessor.php",
    "Services\Media\AVIF\AVIFImageConverter.php",
    "Services\Media\AVIF\AVIFPathHelper.php",
    
    # Services - DB
    "Services\DB\Cleaner.php",
    "Services\DB\DatabaseOptimizer.php",
    "Services\DB\DatabaseQueryMonitor.php",
    "Services\DB\PluginSpecificOptimizer.php",
    "Services\DB\DatabaseReportService.php",
    "Services\DB\QueryCacheManager.php",
    
    # Services - Security
    "Services\Security\HtaccessSecurity.php",
    
    # Services - Compression
    "Services\Compression\CompressionManager.php",
    
    # Services - CDN
    "Services\CDN\CdnManager.php",
    
    # Services - Monitoring
    "Services\Monitoring\PerformanceMonitor.php",
    "Services\Monitoring\PerformanceAnalyzer.php",
    "Services\Monitoring\CoreWebVitalsMonitor.php",
    
    # Services - Reports
    "Services\Reports\ScheduledReports.php",
    
    # Services - Presets
    "Services\Presets\Manager.php",
    
    # Services - Logs
    "Services\Logs\DebugToggler.php",
    "Services\Logs\RealtimeLog.php",
    
    # Services - Admin
    "Services\Admin\BackendOptimizer.php",
    
    # Services - Compatibility
    "Services\Compatibility\ThemeCompatibility.php",
    "Services\Compatibility\ThemeDetector.php",
    "Services\Compatibility\CompatibilityFilters.php",
    "Services\Compatibility\WebPPluginCompatibility.php",
    
    # Services - Intelligence
    "Services\Intelligence\SmartExclusionDetector.php",
    "Services\Intelligence\PageCacheAutoConfigurator.php",
    "Services\Intelligence\CriticalAssetsDetector.php",
    
    # Services - PWA
    "Services\PWA\ServiceWorkerManager.php",
    
    # Utils
    "Utils\Fs.php",
    "Utils\Htaccess.php",
    "Utils\Env.php",
    "Utils\Semaphore.php",
    "Utils\RateLimiter.php",
    "Utils\Logger.php",
    "Utils\InstallationRecovery.php",
    
    # Cli
    "Cli\Commands.php",
    
    # Health
    "Health\HealthCheck.php"
)

Write-Host "Verificando $($classes.Count) file...`n" -ForegroundColor Yellow

foreach ($class in $classes) {
    $filePath = Join-Path $baseDir $class
    
    if (Test-Path $filePath) {
        $size = [math]::Round((Get-Item $filePath).Length / 1KB, 1)
        Write-Host "  [OK] $class ($size KB)" -ForegroundColor Green
        $found += $class
        
        # Verifica sintassi PHP
        $syntaxCheck = php -l $filePath 2>&1
        if ($syntaxCheck -notlike "*No syntax errors*") {
            Write-Host "       [ERRORE SINTASSI] $syntaxCheck" -ForegroundColor Red
        }
    } else {
        Write-Host "  [MANCANTE] $class" -ForegroundColor Red
        $missing += $class
    }
}

Write-Host "`n=== Riepilogo ===" -ForegroundColor Cyan
Write-Host "File trovati: $($found.Count)" -ForegroundColor Green
Write-Host "File mancanti: $($missing.Count)" -ForegroundColor $(if ($missing.Count -gt 0) { "Red" } else { "Green" })

if ($missing.Count -gt 0) {
    Write-Host "`n[CRITICO] File mancanti:" -ForegroundColor Red
    foreach ($m in $missing) {
        Write-Host "  - $m" -ForegroundColor Red
    }
    Write-Host "`n[AZIONE] Il plugin NON funzionerà correttamente!" -ForegroundColor Red
    Write-Host "Reinstalla il plugin tramite Git Updater dopo aver verificato che" -ForegroundColor Yellow
    Write-Host "il file .gitattributes su GitHub NON contenga:" -ForegroundColor Yellow
    Write-Host "  /fp-performance-suite export-ignore" -ForegroundColor Yellow
} else {
    Write-Host "`n[OK] Tutti i file sono presenti!" -ForegroundColor Green
    Write-Host "Il plugin dovrebbe funzionare correttamente." -ForegroundColor Green
}

Write-Host "`nFatto!`n" -ForegroundColor Cyan

