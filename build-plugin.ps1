# Build FP Performance Suite v1.5.0 - Piano B - PowerShell Script

Write-Host "=== Build FP Performance Suite v1.5.0 - Piano B ===" -ForegroundColor Cyan
Write-Host ""

$pluginSlug = "fp-performance-suite"
$zipFile = "$pluginSlug.zip"
$buildDir = "build-temp"

# Rimuovi build precedente
if (Test-Path $buildDir) {
    Write-Host "Pulizia build precedente..." -ForegroundColor Yellow
    Remove-Item -Path $buildDir -Recurse -Force
}

# Rimuovi ZIP precedente
if (Test-Path $zipFile) {
    Remove-Item -Path $zipFile -Force
}

# Crea directory temporanea
New-Item -ItemType Directory -Path $buildDir | Out-Null
$targetDir = "$buildDir\$pluginSlug"
New-Item -ItemType Directory -Path $targetDir | Out-Null

Write-Host "Copiando file del plugin...`n" -ForegroundColor Green

# Copia dall'unica sorgente: fp-performance-suite/
$sourceDir = "fp-performance-suite"

# Copia directory
Copy-Item -Path "$sourceDir\src" -Destination "$targetDir\src" -Recurse -Force
Write-Host "✅ src/" -ForegroundColor Green

Copy-Item -Path "$sourceDir\assets" -Destination "$targetDir\assets" -Recurse -Force
Write-Host "✅ assets/" -ForegroundColor Green

Copy-Item -Path "$sourceDir\languages" -Destination "$targetDir\languages" -Recurse -Force
Write-Host "✅ languages/" -ForegroundColor Green

Copy-Item -Path "$sourceDir\views" -Destination "$targetDir\views" -Recurse -Force
Write-Host "✅ views/" -ForegroundColor Green

# Copia file singoli
Copy-Item -Path "$sourceDir\fp-performance-suite.php" -Destination "$targetDir\" -Force
Copy-Item -Path "$sourceDir\uninstall.php" -Destination "$targetDir\" -Force
Copy-Item -Path "LICENSE" -Destination "$targetDir\" -Force
Copy-Item -Path "readme.txt" -Destination "$targetDir\" -Force
Copy-Item -Path "README.md" -Destination "$targetDir\" -Force

Write-Host "✅ File principali copiati`n" -ForegroundColor Green

# Verifica file ottimizzazione database
Write-Host "=== Verifica File Ottimizzazione Database ===" -ForegroundColor Cyan

$dbFiles = @(
    "$targetDir\src\Services\DB\DatabaseOptimizer.php",
    "$targetDir\src\Services\DB\DatabaseQueryMonitor.php",
    "$targetDir\src\Services\DB\PluginSpecificOptimizer.php",
    "$targetDir\src\Services\DB\DatabaseReportService.php"
)

$allPresent = $true
foreach ($file in $dbFiles) {
    if (Test-Path $file) {
        $size = [math]::Round((Get-Item $file).Length / 1KB, 1)
        $name = Split-Path $file -Leaf
        Write-Host "✅ $name ($size KB)" -ForegroundColor Green
    } else {
        $name = Split-Path $file -Leaf
        Write-Host "❌ MANCANTE: $name" -ForegroundColor Red
        $allPresent = $false
    }
}

Write-Host ""

if (-not $allPresent) {
    Write-Host "⚠️  File mancanti! Verifica che esistano in fp-performance-suite/src/Services/DB/" -ForegroundColor Red
    exit 1
}

# Crea ZIP
Write-Host "Creazione ZIP..." -ForegroundColor Cyan

Compress-Archive -Path "$buildDir\*" -DestinationPath $zipFile -Force

# Pulisci directory temporanea
Remove-Item -Path $buildDir -Recurse -Force

# Statistiche
$zipSize = (Get-Item $zipFile).Length
$zipSizeMB = [math]::Round($zipSize / 1MB, 2)

Write-Host "`n🎉 Build Completato!" -ForegroundColor Green
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Cyan
Write-Host "📦 File ZIP: $zipFile" -ForegroundColor White
Write-Host "📊 Dimensione: $zipSizeMB MB" -ForegroundColor White
Write-Host "🏷️  Versione: 1.5.0 - Piano B Complete" -ForegroundColor White
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Cyan

Write-Host "✅ Plugin pronto per l'installazione!" -ForegroundColor Green
Write-Host "`n🚀 Nuove funzionalità v1.5.0 - Piano B:" -ForegroundColor Yellow
Write-Host "  ✅ Menu gerarchico riorganizzato (13 pagine)"
Write-Host "  ✅ 15 tabs per navigazione intuitiva"
Write-Host "  ✅ Nuova pagina Backend Optimization"
Write-Host "  ✅ Settings integrato in Configuration"
Write-Host "  ✅ UX completamente rinnovata"
Write-Host "  ✅ PHP 8.1+ deprecations corretti"
Write-Host "  ✅ Backward compatibility garantita"
Write-Host ""
Write-Host "Installa ora il plugin sul tuo sito!" -ForegroundColor Green
Write-Host "Fatto! 🎊`n" -ForegroundColor Cyan

