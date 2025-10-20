# Build FP Performance Suite v1.4.0 - PowerShell Script

Write-Host "=== Build FP Performance Suite v1.4.0 ===" -ForegroundColor Cyan
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

# Copia directory
Copy-Item -Path "src" -Destination "$targetDir\src" -Recurse -Force
Write-Host "✅ src/" -ForegroundColor Green

Copy-Item -Path "assets" -Destination "$targetDir\assets" -Recurse -Force
Write-Host "✅ assets/" -ForegroundColor Green

Copy-Item -Path "languages" -Destination "$targetDir\languages" -Recurse -Force
Write-Host "✅ languages/" -ForegroundColor Green

Copy-Item -Path "views" -Destination "$targetDir\views" -Recurse -Force
Write-Host "✅ views/" -ForegroundColor Green

# Copia file singoli
Copy-Item -Path "fp-performance-suite.php" -Destination "$targetDir\" -Force
Copy-Item -Path "uninstall.php" -Destination "$targetDir\" -Force
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
    Write-Host "⚠️  File mancanti! Verifica che esistano in src/Services/DB/" -ForegroundColor Red
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
Write-Host "🏷️  Versione: 1.4.0" -ForegroundColor White
Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━`n" -ForegroundColor Cyan

Write-Host "✅ Plugin pronto per l'installazione!" -ForegroundColor Green
Write-Host "`n🚀 Nuove funzionalità v1.4.0:" -ForegroundColor Yellow
Write-Host "  ✅ Database Health Score (0-100%)"
Write-Host "  ✅ Analisi frammentazione avanzata"
Write-Host "  ✅ Plugin-specific cleanup (WooCommerce, Elementor, Yoast)"
Write-Host "  ✅ Report & Trend con export JSON/CSV"
Write-Host "  ✅ 5 nuovi comandi WP-CLI"
Write-Host "  ✅ Interfaccia admin completamente rinnovata"
Write-Host ""
Write-Host "Installa ora il plugin sul tuo sito!" -ForegroundColor Green
Write-Host "Fatto! 🎊`n" -ForegroundColor Cyan

