# Changelog - Correzioni Bug e Sicurezza

## [Non rilasciato] - 2025-10-09

### 🔒 Sicurezza

#### CRITICA
- **SQL Injection** - Corretta vulnerabilità SQL injection nelle query `OPTIMIZE TABLE` in `Cleaner.php`
  - Aggiunta sanitizzazione con whitelist per nomi delle tabelle
  - Prevenuto potenziale accesso non autorizzato al database
  - File: `src/Services/DB/Cleaner.php` (linee 327-331)

#### ALTA  
- **Accesso insicuro a variabili superglobali** - Sanitizzati tutti gli accessi a `$_SERVER`
  - `$_SERVER['REQUEST_METHOD']` ora sanitizzato con `sanitize_text_field()` e `wp_unslash()`
  - `$_SERVER['REQUEST_URI']` ora sanitizzato con `sanitize_text_field()` e `wp_unslash()`
  - `$_SERVER['SERVER_SOFTWARE']` ora sanitizzato con `sanitize_text_field()` e `wp_unslash()`
  - File modificati:
    - `src/Services/Cache/PageCache.php` (linee 609, 623, 677)
    - `src/Services/Cache/Headers.php` (linea 48)
    - `src/Utils/Env.php` (linea 19)

### 🐛 Bug Corretti

#### Bug Logici
- **is_main_query() in contesto inappropriato** - Rimossa chiamata a `is_main_query()` in `PageCache::isCacheableRequest()`
  - La funzione `is_main_query()` non ha senso fuori dal loop di WordPress
  - File: `src/Services/Cache/PageCache.php` (linea 601)

- **Precedenza operatori errata** - Corretta condizione logica per invalidazione cache homepage
  - Aggiunta parentesi per specificare correttamente precedenza tra `||` e `&&`
  - File: `src/Services/Cache/PageCache.php` (linea 200)

### 📈 Miglioramenti

#### Qualità del Codice
- Migliorata sicurezza generale del codice
- Eliminati potenziali vettori di injection attacks
- Codice più robusto e conforme alle WordPress Coding Standards

#### Documentazione
- Aggiunto commento esplicativo per sanitizzazione tabelle SQL
- Aggiunto commento per gestione sicura di `$_SERVER`

### 🔧 Modifiche Tecniche

#### File Modificati
```
src/Services/Cache/PageCache.php    | 10 +++++-----
src/Services/DB/Cleaner.php         |  5 ++++-
src/Utils/Env.php                   |  2 +-
src/Services/Cache/Headers.php      |  2 +-
```

**Totale:** 4 files changed, 15 insertions(+), 8 deletions(-)

### ⚠️ Breaking Changes
Nessuna modifica breaking. Tutte le correzioni mantengono la retrocompatibilità.

### 🎯 Testing Raccomandato

Dopo aver applicato queste correzioni, si raccomanda di testare:

1. **Funzionalità Cache**
   - [ ] Verifica che la cache venga creata correttamente
   - [ ] Test purge cache per URL specifici
   - [ ] Test purge cache per post
   - [ ] Verifica invalidazione cache su aggiornamento contenuti

2. **Ottimizzazione Database**
   - [ ] Esegui cleanup database in modalità dry-run
   - [ ] Verifica ottimizzazione tabelle
   - [ ] Test cleanup con vari scope

3. **Asset Optimization**
   - [ ] Test defer/async JavaScript
   - [ ] Verifica minificazione HTML
   - [ ] Test combinazione CSS/JS

4. **WebP Conversion**
   - [ ] Test conversione singola immagine
   - [ ] Test conversione bulk
   - [ ] Verifica deliver automatico WebP

### 📚 Riferimenti

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [OWASP SQL Injection Prevention](https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html)
- [WordPress Data Validation](https://developer.wordpress.org/apis/security/data-validation/)

### 👥 Contributors

- Francesco Passeri (@franpass87)
- AI Assistant (Analisi e correzioni automatizzate)

---

## Note Importanti

⚠️ **Queste correzioni includono fix di sicurezza critici. Si raccomanda vivamente di aggiornare alla versione contenente questi fix.**

### Installazione

```bash
# Backup del database prima dell'aggiornamento
wp db export backup.sql

# Aggiorna il plugin
cd wp-content/plugins/fp-performance-suite
git pull origin main

# Verifica che tutto funzioni correttamente
wp fp-performance info
```

### Rollback (se necessario)

```bash
# Ripristina versione precedente
git checkout [tag-versione-precedente]

# Oppure ripristina da backup
wp db import backup.sql
```

### Supporto

Per problemi o domande relative a queste correzioni:
- 🐛 [Segnala un bug](https://github.com/franpass87/FP-Performance/issues)
- 💬 [Discussioni](https://github.com/franpass87/FP-Performance/discussions)
- 📧 Email: info@francescopasseri.com