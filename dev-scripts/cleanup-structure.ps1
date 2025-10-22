#!/usr/bin/env pwsh
###############################################################################
# Cleanup Structure - Riorganizzazione Repository
#
# Questo script elimina le copie ridondanti e crea una struttura pulita
###############################################################################

$ErrorActionPreference = "Stop"

Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║  FP Performance Suite - Repository Cleanup                ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host ""

# Conferma dall'utente
Write-Host "⚠️  ATTENZIONE!" -ForegroundColor Yellow
Write-Host ""
Write-Host 'Questo script eliminerà:' -ForegroundColor Yellow
Write-Host '  - /src/ (149 file - versione vecchia e incompleta)' -ForegroundColor Red
Write-Host '  - /fp-performance-suite.php (file di test nella root)' -ForegroundColor Red
Write-Host '  - /build/ (può essere rigenerato)' -ForegroundColor Yellow
Write-Host ""
Write-Host 'Verrà mantenuto:' -ForegroundColor Green
Write-Host '  - /fp-performance-suite/ (235 file - versione completa)' -ForegroundColor Green
Write-Host ""

$confirm = Read-Host 'Vuoi continuare? (si/no)'
if ($confirm -ne "si") {
    Write-Host "❌ Operazione annullata dall'utente" -ForegroundColor Red
    exit 0
}

Write-Host ""
Write-Host "📦 Creazione backup prima della pulizia..." -ForegroundColor Cyan

# Crea backup
$backupDir = "backup-before-cleanup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null

# Backup /src/
if (Test-Path "src") {
    Write-Host "  Backup /src/..." -ForegroundColor Gray
    Copy-Item -Path "src" -Destination "$backupDir/src" -Recurse -Force
}

# Backup fp-performance-suite.php root
if (Test-Path "fp-performance-suite.php") {
    Write-Host "  Backup /fp-performance-suite.php..." -ForegroundColor Gray
    Copy-Item -Path "fp-performance-suite.php" -Destination "$backupDir/" -Force
}

# Backup build
if (Test-Path "build") {
    Write-Host "  Backup /build/..." -ForegroundColor Gray
    Copy-Item -Path "build" -Destination "$backupDir/build" -Recurse -Force
}

Write-Host "✅ Backup creato in: $backupDir" -ForegroundColor Green
Write-Host ""

# Elimina /src/ root
Write-Host "🗑️  Step 1: Eliminazione /src/ (versione vecchia)..." -ForegroundColor Yellow
if (Test-Path "src") {
    Remove-Item -Path "src" -Recurse -Force
    Write-Host "✅ /src/ eliminata" -ForegroundColor Green
} else {
    Write-Host "⚠️  /src/ non esiste" -ForegroundColor Gray
}

# Elimina fp-performance-suite.php root
Write-Host "🗑️  Step 2: Eliminazione /fp-performance-suite.php (file di test)..." -ForegroundColor Yellow
if (Test-Path "fp-performance-suite.php") {
    Remove-Item -Path "fp-performance-suite.php" -Force
    Write-Host "✅ /fp-performance-suite.php eliminato" -ForegroundColor Green
} else {
    Write-Host "⚠️  /fp-performance-suite.php non esiste" -ForegroundColor Gray
}

# Elimina build (può essere rigenerato)
Write-Host "🗑️  Step 3: Eliminazione /build/ (sarà rigenerata)..." -ForegroundColor Yellow
if (Test-Path "build") {
    Remove-Item -Path "build" -Recurse -Force
    Write-Host "✅ /build/ eliminata" -ForegroundColor Green
} else {
    Write-Host "⚠️  /build/ non esiste" -ForegroundColor Gray
}

# Elimina zip se esiste
Write-Host "🗑️  Step 4: Eliminazione fp-performance-suite.zip (sarà rigenerato)..." -ForegroundColor Yellow
if (Test-Path "fp-performance-suite.zip") {
    Remove-Item -Path "fp-performance-suite.zip" -Force
    Write-Host "✅ fp-performance-suite.zip eliminato" -ForegroundColor Green
} else {
    Write-Host "⚠️  fp-performance-suite.zip non esiste" -ForegroundColor Gray
}

Write-Host ""
Write-Host "╔════════════════════════════════════════════════════════════╗" -ForegroundColor Green
Write-Host "║  ✅ PULIZIA COMPLETATA!                                    ║" -ForegroundColor Green
Write-Host "╚════════════════════════════════════════════════════════════╝" -ForegroundColor Green
Write-Host ""

# Mostra nuova struttura
Write-Host "📂 Nuova struttura repository:" -ForegroundColor Cyan
Write-Host ""
Write-Host "FP-Performance/" -ForegroundColor White
Write-Host "├── fp-performance-suite/           ← UNICO SORGENTE" -ForegroundColor Green
Write-Host "│   ├── src/                        ← Codice plugin (235 file)" -ForegroundColor Green
Write-Host "│   ├── assets/                     ← Risorse" -ForegroundColor Green
Write-Host "│   ├── languages/                  ← Traduzioni" -ForegroundColor Green
Write-Host "│   ├── views/                      ← Template" -ForegroundColor Green
Write-Host "│   └── fp-performance-suite.php    ← File principale" -ForegroundColor Green
Write-Host "├── build-plugin.ps1                ← Script di build" -ForegroundColor Cyan
Write-Host "├── docs/                           ← Documentazione" -ForegroundColor Gray
Write-Host "├── tests/                          ← Test" -ForegroundColor Gray
Write-Host "└── $backupDir/         ← Backup (puoi eliminarlo)" -ForegroundColor Yellow
Write-Host ""

Write-Host "📋 Prossimi passi:" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Verifica che tutto funzioni:" -ForegroundColor White
Write-Host "   .\build-plugin.ps1" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Testa il plugin localmente" -ForegroundColor White
Write-Host ""
Write-Host "3. Se tutto ok, committa i cambiamenti:" -ForegroundColor White
Write-Host "   git add ." -ForegroundColor Gray
Write-Host "   git commit -m 'refactor: cleaned repository structure - removed duplicate /src/'" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Puoi eliminare il backup se tutto funziona:" -ForegroundColor White
Write-Host "   Remove-Item -Path '$backupDir' -Recurse -Force" -ForegroundColor Gray
Write-Host ""

Write-Host "✅ Repository pulito e organizzato!" -ForegroundColor Green
Write-Host ""

# Crea file README nella nuova struttura
$readmeContent = @"
# FP Performance Suite - Repository Structure

## 📂 Struttura Corrente

\`\`\`
FP-Performance/
├── fp-performance-suite/           ← PLUGIN COMPLETO (sorgente unico)
│   ├── src/                        ← Codice sorgente (235 file PHP)
│   ├── assets/                     ← CSS, JS, immagini
│   ├── languages/                  ← File di traduzione
│   ├── views/                      ← Template HTML
│   └── fp-performance-suite.php    ← File principale del plugin
├── build-plugin.ps1                ← Script per build e packaging
├── docs/                           ← Documentazione
├── tests/                          ← Test suite
└── README.md                       ← Questo file
\`\`\`

## 🚀 Build del Plugin

\`\`\`powershell
# Windows
.\build-plugin.ps1

# Output: fp-performance-suite.zip (pronto per deploy)
\`\`\`

\`\`\`bash
# Linux/Mac
./update-zip.sh

# Output: fp-performance-suite.zip
\`\`\`

## 📦 Sviluppo

### Modificare il codice

Tutte le modifiche vanno fatte in:
\`\`\`
/fp-performance-suite/src/
\`\`\`

### Build per distribuzione

Dopo ogni modifica:
\`\`\`powershell
.\build-plugin.ps1
\`\`\`

Questo genera \`fp-performance-suite.zip\` nella root.

### Test locale

Il plugin in \`/fp-performance-suite/\` può essere:
- Copiato direttamente in \`/wp-content/plugins/\`
- Oppure installato come zip dopo il build

## 🗂️ Changelog Struttura

### 21 Ottobre 2025 - Pulizia Repository

**Rimosso:**
- ❌ \`/src/\` nella root (versione vecchia con 149 file e bug)
- ❌ \`/fp-performance-suite.php\` nella root (file di test)
- ❌ \`/build/\` (viene rigenerato ad ogni build)

**Mantenuto:**
- ✅ \`/fp-performance-suite/\` come UNICO sorgente (235 file, completo e corretto)

**Motivo:**
- Eliminata confusione tra versioni
- Struttura più pulita e chiara
- Un solo punto di verità per il codice

## 📝 Note

- Il backup della vecchia struttura è in: \`backup-before-cleanup-YYYYMMDD-HHMMSS/\`
- Puoi eliminare il backup dopo aver verificato che tutto funzioni
- \`/build/\` viene ricreato automaticamente da \`build-plugin.ps1\`

"@

Set-Content -Path "REPOSITORY_STRUCTURE.md" -Value $readmeContent -Encoding UTF8
Write-Host "📄 Creato: REPOSITORY_STRUCTURE.md" -ForegroundColor Cyan
Write-Host ""

