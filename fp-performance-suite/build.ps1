# Build script per Windows PowerShell
# Uso: .\build.ps1

$ErrorActionPreference = "Stop"

$PLUGIN_SLUG = "fp-performance-suite"
$PROJECT_ROOT = $PSScriptRoot
$BUILD_DIR = Join-Path $PROJECT_ROOT "build"
$STAGING_DIR = Join-Path $BUILD_DIR $PLUGIN_SLUG
$TIMESTAMP = Get-Date -Format "yyyyMMddHHmm"
$ZIP_NAME = "$PLUGIN_SLUG-$TIMESTAMP.zip"

Write-Host "=== Build FP Performance Suite ===" -ForegroundColor Cyan
Write-Host "Project Root: $PROJECT_ROOT"
Write-Host "Build Dir: $BUILD_DIR"
Write-Host ""

# Pulisci e crea directory di staging
Write-Host "Preparazione directory..." -ForegroundColor Yellow
if (Test-Path $STAGING_DIR) {
    Remove-Item $STAGING_DIR -Recurse -Force
}
New-Item -ItemType Directory -Path $STAGING_DIR -Force | Out-Null

# Lista file/directory da escludere
$excludes = @(
    ".git",
    ".github",
    "tests",
    "docs",
    "node_modules",
    ".idea",
    ".vscode",
    "build",
    "examples",
    "tools",
    "bin",
    "wp-content",
    "wp-admin",
    "wp-includes",
    "vendor"
)

$excludePatterns = @(
    "*.md",
    ".gitattributes",
    ".gitignore",
    "phpunit.xml.dist",
    "phpcs.xml.dist",
    "phpstan.neon.dist",
    "composer.lock",
    ".phpunit.result.cache",
    "build.sh",
    "build.ps1",
    "verify-zip-structure.sh",
    "README-BUILD.md",
    "README-ZIP-WORDPRESS.md",
    ".DS_Store",
    "Thumbs.db"
)

Write-Host "Copia file..." -ForegroundColor Yellow

# Funzione per verificare se un path deve essere escluso
function Should-Exclude {
    param($path)
    
    $relativePath = $path.Replace($PROJECT_ROOT, "").TrimStart("\", "/")
    
    # Controlla directory escluse
    foreach ($exclude in $excludes) {
        if ($relativePath -like "$exclude*" -or $relativePath -like "*\$exclude\*") {
            return $true
        }
    }
    
    # Controlla pattern esclusi
    $fileName = Split-Path $path -Leaf
    foreach ($pattern in $excludePatterns) {
        if ($fileName -like $pattern) {
            return $true
        }
    }
    
    return $false
}

# Copia tutti i file necessari
Get-ChildItem -Path $PROJECT_ROOT -Recurse | ForEach-Object {
    if (Should-Exclude $_.FullName) {
        return
    }
    
    $relativePath = $_.FullName.Replace($PROJECT_ROOT, "")
    $destPath = Join-Path $STAGING_DIR $relativePath
    
    if ($_.PSIsContainer) {
        if (-not (Test-Path $destPath)) {
            New-Item -ItemType Directory -Path $destPath -Force | Out-Null
        }
    } else {
        $destDir = Split-Path $destPath -Parent
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        Copy-Item $_.FullName -Destination $destPath -Force
    }
}

Write-Host "File copiati con successo!" -ForegroundColor Green

# Crea ZIP
Write-Host "Creazione archivio ZIP..." -ForegroundColor Yellow
$zipPath = Join-Path $BUILD_DIR $ZIP_NAME

if (Test-Path $zipPath) {
    Remove-Item $zipPath -Force
}

# Usa Compress-Archive (PowerShell 5+)
try {
    Compress-Archive -Path $STAGING_DIR -DestinationPath $zipPath -CompressionLevel Optimal
    Write-Host "ZIP creato con successo!" -ForegroundColor Green
} catch {
    Write-Host "Errore durante la creazione del ZIP: $_" -ForegroundColor Red
    exit 1
}

# Ottieni versione dal file principale
$mainFile = Join-Path $PROJECT_ROOT "fp-performance-suite.php"
$content = Get-Content $mainFile -Raw
if ($content -match "Version:\s*(.+)") {
    $version = $matches[1].Trim()
} else {
    $version = "unknown"
}

Write-Host ""
Write-Host "=== Build Completato ===" -ForegroundColor Green
Write-Host "Versione: $version" -ForegroundColor Cyan
Write-Host "ZIP: $zipPath" -ForegroundColor Cyan
Write-Host ""
Write-Host "Puoi caricare questo ZIP su WordPress via:" -ForegroundColor Yellow
Write-Host "  Plugin -> Aggiungi Nuovo -> Carica Plugin" -ForegroundColor White

