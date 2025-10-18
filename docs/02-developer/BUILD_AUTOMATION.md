# 🔧 Automazione Build ZIP Plugin

Questo documento descrive il sistema automatico per mantenere sempre aggiornato il file `fp-performance-suite.zip`.

## 🎯 Problema Risolto

In passato, il file ZIP del plugin poteva rimanere obsoleto dopo modifiche al codice, causando:
- ❌ Utenti che scaricano versioni non aggiornate
- ❌ Perdita di tempo nel debug di problemi già risolti
- ❌ Inconsistenza tra codice sorgente e ZIP distribuito

## ✅ Soluzione Implementata

### 1. Script Locale: `update-zip.sh`

Script bash per rigenerare manualmente il ZIP quando necessario.

**Utilizzo:**
```bash
bash update-zip.sh
```

**Quando usarlo:**
- Prima di committare modifiche importanti
- Dopo aver modificato file in `src/`, `assets/`, o `views/`
- Prima di creare una release

**Cosa fa:**
- ✅ Copia solo i file necessari (no test, docs, esempi)
- ✅ Esclude file di sviluppo (composer, phpunit, etc.)
- ✅ Verifica il contenuto del ZIP
- ✅ Mostra statistiche (numero file, dimensione, features)

### 2. GitHub Actions: Aggiornamento Automatico

#### Workflow: `update-zip.yml`
Si attiva automaticamente quando:
- ✅ Push su branch `main`
- ✅ Modifiche a file in `fp-performance-suite/src/**`
- ✅ Modifiche a file in `fp-performance-suite/assets/**`
- ✅ Modifiche a file in `fp-performance-suite/views/**`

**Cosa fa:**
1. Costruisce un ZIP fresco con le ultime modifiche
2. Verifica il contenuto (controlla features PageSpeed, etc.)
3. Committà automaticamente il ZIP aggiornato
4. Usa `[skip ci]` per evitare loop infiniti

**Commit automatico:**
```
chore: Update plugin ZIP to v1.2.0 [skip ci]
```

#### Workflow: `verify-zip.yml`
Si attiva su **Pull Request** per verificare che il ZIP sia aggiornato.

**Cosa fa:**
1. Costruisce un ZIP fresco
2. Confronta con il ZIP committato
3. ❌ Fallisce se il ZIP non è aggiornato
4. ✅ Passa se il ZIP è sincronizzato

**Messaggio di errore:**
```
⚠️  ATTENZIONE: Il file fp-performance-suite.zip non è aggiornato!

📝 Esegui questo comando per aggiornarlo:
   bash update-zip.sh
```

#### Workflow: `build.yml`
Workflow esistente modificato per committare il ZIP dopo ogni build su `main`.

## 📋 Workflow di Sviluppo Consigliato

### Durante lo Sviluppo

1. Fai modifiche ai file del plugin
2. Testa le modifiche
3. **Opzionale:** Esegui `bash update-zip.sh` per testare il build locale

### Prima del Commit

Il workflow di verifica ti avviserà se dimentichi di aggiornare il ZIP:

```bash
# Se hai modificato file del plugin, aggiorna il ZIP
bash update-zip.sh

# Poi committa tutto insieme
git add fp-performance-suite.zip
git commit -m "feat: nuova funzionalità"
```

### Dopo il Merge su Main

GitHub Actions aggiornerà automaticamente il ZIP se necessario.

## 🔍 Verifica Manuale

Per verificare che il ZIP contenga le ultime modifiche:

```bash
# Controlla presenza di una feature specifica
unzip -p fp-performance-suite.zip fp-performance-suite/src/Admin/Pages/Assets.php | grep "PageSpeed"

# Mostra versione nel ZIP
unzip -p fp-performance-suite.zip fp-performance-suite/fp-performance-suite.php | grep "Version:"

# Lista file nel ZIP
unzip -l fp-performance-suite.zip | less
```

## 🚨 Risoluzione Problemi

### Il ZIP non si aggiorna automaticamente

**Verifica:**
1. Controlla che le modifiche siano su `main`
2. Verifica i log di GitHub Actions
3. Controlla che il messaggio di commit non contenga `[skip ci]`

**Soluzione rapida:**
```bash
bash update-zip.sh
git add fp-performance-suite.zip
git commit -m "chore: Update plugin ZIP manually"
```

### Lo script update-zip.sh fallisce

**Controlla:**
- Che tu sia nella directory root del progetto
- Che la directory `fp-performance-suite/` esista
- Che `zip` sia installato (`apt-get install zip` o `brew install zip`)

### Il workflow GitHub Actions fallisce

**Possibili cause:**
- Mancano permessi di scrittura (`permissions: contents: write`)
- Conflitto di merge automatico
- File troppo grande (limite GitHub: 100MB)

**Soluzione:**
Esegui il build manualmente con `bash update-zip.sh` e committà.

## 📊 Statistiche

Ogni build mostra statistiche utili:

```
✅ ZIP aggiornato con successo!
📊 Statistiche:
   - File inclusi: 172
   - PageSpeed features: 5
   - Dimensione: 175K
```

## 🔐 Sicurezza

Lo script esclude automaticamente:
- ❌ File di test
- ❌ Documentazione sviluppatori
- ❌ Esempi e tool
- ❌ File di configurazione (composer, phpunit, phpstan)
- ❌ File di versioning (.git, .gitignore)
- ❌ Dipendenze dev (vendor/ se presente)

Include solo:
- ✅ File PHP del plugin
- ✅ Asset (CSS, JS)
- ✅ File di traduzione (.pot)
- ✅ File essenziali (LICENSE, readme.txt, uninstall.php)

## 📝 Note

- Il commit automatico usa `[skip ci]` per evitare loop infiniti
- Il workflow si attiva solo per modifiche rilevanti (non per .md, test, docs)
- Il ZIP viene sempre committato nella root del progetto
- La versione nel commit proviene dal file `fp-performance-suite.php`

## 🎉 Benefici

✅ **Sempre sincronizzato**: Il ZIP è sempre allineato al codice  
✅ **Nessun intervento manuale**: GitHub Actions fa tutto automaticamente  
✅ **Verifica su PR**: Nessuna PR può essere mergiata con ZIP obsoleto  
✅ **Facile debug**: Script locale per test rapidi  
✅ **Tracciabilità**: Ogni update ha il suo commit dedicato  
✅ **Nessun loop infinito**: `[skip ci]` previene problemi  

---

**Ultima modifica:** 2025-10-11  
**Versione plugin:** 1.2.0
