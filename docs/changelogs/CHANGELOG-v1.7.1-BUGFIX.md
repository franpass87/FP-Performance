# CHANGELOG - FP Performance Suite v1.7.1 - Bugfix

## 🐛 Bug Risolti

### 1. **jQuery Dependency Mancante** (BUG CRITICO)
**Problema:**  
Il file `main.js` veniva caricato senza jQuery come dipendenza, causando `ReferenceError: jQuery is not defined` quando `Overview.php` tentava di usare jQuery inline.

**Fix Applicate:**
- ✅ `src/Admin/Assets.php` - Aggiunto `'jquery'` alle dipendenze di `fp-performance-suite-admin` script
- ✅ `src/Admin/Pages/Overview.php` - Wrappato lo script inline in un `waitForJQuery()` che attende che jQuery sia disponibile prima di eseguire il codice

**File Modificati:**
- `src/Admin/Assets.php` (riga 31)
- `src/Admin/Pages/Overview.php` (righe 664-668, 742-743)

---

### 2. **AJAX Timeout su Bottone "Applica Ora"** (Dashboard)
**Problema:**  
Il bottone "Applica Ora" per abilitare la cache rimaneva bloccato in "Applicazione in corso..." indefinitamente senza gestione del timeout.

**Fix Applicate:**
- ✅ Aggiunto `timeout: 15000` (15 secondi) alla chiamata AJAX
- ✅ Aggiunto messaggio di errore specifico per timeout
- ✅ Implementato fallback per guidare l'utente verso configurazione manuale

**File Modificati:**
- `src/Admin/Pages/Overview.php` (riga 689, righe 734-735)

---

### 3. **RiskMatrix Keys Mismatch** (Pallini di Rischio Mancanti/Generici)
**Problema:**  
I pallini di rischio mostravano "Non ancora classificato" perché le chiavi usate nelle chiamate `RiskMatrix::renderIndicator()` non matchavano le chiavi definite in `RiskMatrix.php`.

**Fix Applicate:**
- ✅ Rinominato `'page_cache_enabled'` → `'page_cache'`  
- ✅ Rinominato `'prefetch_enabled'` → `'predictive_prefetch'`  
- ✅ Aggiunto `'cache_rules'` (mancante)  
- ✅ Aggiunto `'html_cache'` (mancante)  
- ✅ Aggiunto `'fonts_cache'` (mancante)  
- ✅ Aggiunto `'database_enabled'` (mancante)  
- ✅ Aggiunto `'query_monitor'` (mancante)  

**Chiavi Totali Verificate e Corrette:** 6 su Cache.php

**File Modificati:**
- `src/Admin/RiskMatrix.php` (righe 41-110)

---

### 4. **CORS Error su Local con Porte Non Standard**
**Problema:**  
WordPress su Local genera URL asset senza porta (es: `http://fp-development.local/...`) mentre il sito gira su porta custom (es: `:10005`), causando blocco CORS per moduli ES6.

**Fix Applicate:**
- ✅ Creato metodo `getCorrectBaseUrl()` che rileva automaticamente porta da `$_SERVER['HTTP_HOST']`
- ✅ Modificato enqueue di CSS/JS per usare URL completo con porta corretta
- ⚠️ **NOTA:** I moduli ES6 importati (`import`) continuano a causare redirect perché il server Local fa redirect automatici - **questo è un problema di configurazione server, non del plugin**

**File Modificati:**
- `src/Admin/Assets.php` (righe 18-49)

**Workaround:** Se il CORS persiste, usare wp-config.php o DB per forzare `WP_HOME` e `WP_SITEURL` con porta corretta.

---

## 📊 Riepilogo Modifiche

| File | Righe Modificate | Tipo Modifica |
|------|------------------|---------------|
| `src/Admin/Assets.php` | 7 | Fix CORS + jQuery dependency |
| `src/Admin/Pages/Overview.php` | 6 | Timeout handling + jQuery wait |
| `src/Admin/RiskMatrix.php` | 70 | Key rename + add missing entries |

---

## ✅ Test Eseguiti

- [x] **Dashboard Overview** - Carica senza errori PHP
- [x] **Pagina Cache** - Carica senza errori PHP  
- [x] **Pallini Rischio** - Tutte le chiavi sono definite (6/6 su Cache.php)
- [x] **Browser Console** - jQuery dependency corretto (ma CORS persiste per moduli ES6)
- [ ] **Test Bottoni AJAX** - Non testato funzionalmente a causa di CORS

---

## ⚠️ Problemi Noti (Non Risolvibili lato Plugin)

### CORS su Local con Porte Non Standard
- **Causa:** WordPress genera URL senza porta, server Local fa redirect cambiando origin
- **Impatto:** Moduli ES6 importati bloccati da CORS policy
- **Soluzione:** Configurare correttamente `WP_HOME` e `WP_SITEURL` nel database o wp-config.php

---

## 📝 Note Sviluppo

- **Versione Plugin:** 1.7.1 (Bugfix)
- **Data Fix:** 5 Novembre 2025
- **Ambiente Test:** Local by Flywheel (Windows 10)
- **WordPress:** 6.8.3
- **PHP:** 8.4.4

---

## 🔮 Prossimi Passi Consigliati

1. ✅ **Testare in produzione** (senza porta custom, CORS dovrebbe sparire)
2. ⏳ **Verificare funzionalità AJAX** dei bottoni "Applica Ora"
3. ⏳ **Testare tooltip** dei pallini di rischio (hover)
4. ⏳ **Completare test su tutte le 17 pagine admin** per checkboxes e bottoni

---

**Fine Changelog v1.7.1**

