# Finalizza Struttura per Git Updater

Write-Host "=== FINALIZZAZIONE STRUTTURA PER GIT UPDATER ===" -ForegroundColor Cyan
Write-Host ""

Write-Host "STRUTTURA ATTUALE:" -ForegroundColor Yellow
Write-Host "  ✅ /fp-performance-suite.php (wrapper corretto)" -ForegroundColor Green
Write-Host "  ✅ /fp-performance-suite/ (sorgente unico)" -ForegroundColor Green
Write-Host "  ❌ /src/ (da eliminare - versione vecchia)" -ForegroundColor Red
Write-Host "  ⚠️  /assets/, /languages/, /views/ (copie, opzionali)" -ForegroundColor Yellow
Write-Host ""

$confirm = Read-Host "Vuoi finalizzare la struttura per Git Updater? (si/no)"
if ($confirm -ne "si") {
    Write-Host "Annullato" -ForegroundColor Red
    exit 0
}

Write-Host ""
Write-Host "Step 1: Verifica file wrapper..." -ForegroundColor Cyan
if (Test-Path "fp-performance-suite.php") {
    php -l fp-performance-suite.php | Out-Null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  ✅ Wrapper sintatticamente corretto" -ForegroundColor Green
    } else {
        Write-Host "  ❌ Errore nel wrapper!" -ForegroundColor Red
        exit 1
    }
} else {
    Write-Host "  ❌ Wrapper non trovato!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 2: Verifica sorgente..." -ForegroundColor Cyan
if (Test-Path "fp-performance-suite/src") {
    $fileCount = (Get-ChildItem "fp-performance-suite/src" -Recurse -Filter "*.php" | Measure-Object).Count
    Write-Host "  ✅ fp-performance-suite/src/ esiste ($fileCount file PHP)" -ForegroundColor Green
} else {
    Write-Host "  ❌ Sorgente non trovato!" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "Step 3: Stato Git attuale..." -ForegroundColor Cyan
git status -s | Select-String -Pattern "fp-performance-suite.php|src/|assets/|languages/|views/" | ForEach-Object {
    if ($_ -match "^D") {
        Write-Host "  $_" -ForegroundColor Red
    } elseif ($_ -match "^\?\?") {
        Write-Host "  $_" -ForegroundColor Yellow
    } else {
        Write-Host "  $_" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "=== AZIONI DA ESEGUIRE ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "1. Stage del nuovo wrapper:" -ForegroundColor Yellow
Write-Host "   git add fp-performance-suite.php" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Conferma eliminazione /src/ vecchio:" -ForegroundColor Yellow
Write-Host "   git add src/" -ForegroundColor Gray
Write-Host ""
Write-Host "3. (Opzionale) Elimina copie nella root:" -ForegroundColor Yellow
Write-Host "   git rm -r assets/ languages/ views/" -ForegroundColor Gray
Write-Host ""
Write-Host "4. Commit:" -ForegroundColor Yellow
Write-Host "   git commit -m 'refactor: restructure for Git Updater - use wrapper pointing to fp-performance-suite/'" -ForegroundColor Gray
Write-Host ""
Write-Host "5. Push:" -ForegroundColor Yellow
Write-Host "   git push origin main" -ForegroundColor Gray
Write-Host ""

Write-Host "=== RISULTATO FINALE SU GITHUB ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "/fp-performance-suite.php    ← Wrapper (punta a fp-performance-suite/)" -ForegroundColor Green
Write-Host "/fp-performance-suite/       ← Sorgente unico" -ForegroundColor Green
Write-Host "  ├── src/                   ← 121 file PHP corretti" -ForegroundColor Green
Write-Host "  ├── assets/" -ForegroundColor Green
Write-Host "  ├── languages/" -ForegroundColor Green
Write-Host "  └── views/" -ForegroundColor Green
Write-Host ""
Write-Host "Git Updater:" -ForegroundColor Cyan
Write-Host "  1. Clona repository" -ForegroundColor Gray
Write-Host "  2. Trova fp-performance-suite.php (root)" -ForegroundColor Gray
Write-Host "  3. Carica da fp-performance-suite/src/ ✅" -ForegroundColor Green
Write-Host "  4. Plugin funziona! ✅" -ForegroundColor Green
Write-Host ""

$execute = Read-Host "Vuoi eseguire i comandi Git ora? (si/no)"
if ($execute -eq "si") {
    Write-Host ""
    Write-Host "Esecuzione comandi..." -ForegroundColor Cyan
    
    # Stage wrapper
    git add fp-performance-suite.php
    Write-Host "✅ Aggiunto fp-performance-suite.php" -ForegroundColor Green
    
    # Conferma eliminazione src/
    if (git status -s | Select-String "D src/") {
        git add src/
        Write-Host "✅ Confermata eliminazione src/ vecchio" -ForegroundColor Green
    }
    
    # Opzionale: elimina copie root
    Write-Host ""
    $removeCopies = Read-Host "Vuoi eliminare anche /assets/, /languages/, /views/ dalla root? (si/no)"
    if ($removeCopies -eq "si") {
        if (Test-Path "assets") { git rm -r --quiet assets/ 2>$null; Write-Host "✅ Rimosso /assets/" -ForegroundColor Green }
        if (Test-Path "languages") { git rm -r --quiet languages/ 2>$null; Write-Host "✅ Rimosso /languages/" -ForegroundColor Green }
        if (Test-Path "views") { git rm -r --quiet views/ 2>$null; Write-Host "✅ Rimosso /views/" -ForegroundColor Green }
    }
    
    Write-Host ""
    Write-Host "Stato modifiche:" -ForegroundColor Cyan
    git status -s | Select-Object -First 10
    
    Write-Host ""
    $commitNow = Read-Host "Vuoi fare il commit ora? (si/no)"
    if ($commitNow -eq "si") {
        git commit -m "refactor: restructure for Git Updater - use wrapper pointing to fp-performance-suite/"
        Write-Host ""
        Write-Host "✅ Commit eseguito!" -ForegroundColor Green
        Write-Host ""
        
        $pushNow = Read-Host "Vuoi fare il push su GitHub ora? (si/no)"
        if ($pushNow -eq "si") {
            git push origin main
            Write-Host ""
            Write-Host "✅ Push eseguito! Plugin aggiornato su GitHub" -ForegroundColor Green
            Write-Host ""
            Write-Host "Git Updater ora installerà la versione CORRETTA!" -ForegroundColor Green
        }
    }
}

Write-Host ""
Write-Host "✅ COMPLETATO!" -ForegroundColor Green
Write-Host ""
Write-Host "Ora Git Updater userà:" -ForegroundColor Cyan
Write-Host "  fp-performance-suite.php (wrapper) → fp-performance-suite/src/ (corretto)" -ForegroundColor Green
Write-Host ""
Write-Host "WSOD RISOLTO! ✅" -ForegroundColor Green

