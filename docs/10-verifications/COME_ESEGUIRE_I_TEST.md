# 🧪 Come Eseguire i Test - Simulazione Amministratore

## FP Performance Suite v1.2.0

Questa guida spiega come eseguire tutti i test di simulazione amministratore per verificare la corretta funzionalità del plugin.

---

## 📋 File Creati per i Test

### 1. Script Principale

**File**: `test-admin-simulation.php`  
**Descrizione**: Script completo che testa tutte le funzionalità del plugin  
**Test Inclusi**: 139 test automatizzati

### 2. Test Specifici

**Directory**: `tests-specifici/`

- `test-cache-module.php` - Test dettagliato modulo Cache (18 test)

### 3. Documentazione

- `GUIDA_AMMINISTRATORE.md` - Guida completa per test manuali
- `REPORT_VERIFICA_FUNZIONALE.md` - Report dettagliato risultati test
- `RIEPILOGO_ESECUTIVO_TEST.md` - Riepilogo esecutivo finale

---

## 🚀 Metodo 1: Test Automatico Completo

### Prerequisiti

- ✅ WordPress installato e funzionante
- ✅ FP Performance Suite v1.2.0 attivato
- ✅ Accesso SSH o WP-CLI (opzionale ma consigliato)
- ✅ PHP 8.0+

### Opzione A: Via SSH/Terminal

```bash
# 1. Naviga nella directory del plugin
cd /path/to/wordpress/wp-content/plugins/FP-Performance/

# 2. Esegui lo script di test principale
php test-admin-simulation.php

# Output atteso:
# ╔════════════════════════════════════════════════════════════════╗
# ║   TEST SIMULAZIONE AMMINISTRATORE - FP PERFORMANCE SUITE       ║
# ╚════════════════════════════════════════════════════════════════╝
# 
# [output test dettagliato]
# 
# ✅ Successi: 135
# ⚠️  Warning: 4
# ❌ Errori: 0
```

### Opzione B: Via Browser

```bash
# 1. Copia lo script nella root di WordPress
cp test-admin-simulation.php /path/to/wordpress/test-performance.php

# 2. Modifica per permettere accesso web (TEMPORANEO!)
# Aggiungi all'inizio del file dopo <?php:
define('ABSPATH', dirname(__FILE__) . '/');
require_once ABSPATH . 'wp-load.php';

# 3. Visita nel browser
https://tuosito.com/test-performance.php

# 4. ⚠️ IMPORTANTE: Elimina il file dopo il test!
rm test-performance.php
```

### Opzione C: Via WP-CLI

```bash
# Esegui come comando WP-CLI
wp eval-file test-admin-simulation.php
```

---

## 🔍 Metodo 2: Test Modulo Specifico (Cache)

### Esecuzione Test Cache

```bash
# Via PHP CLI
php tests-specifici/test-cache-module.php

# Via WP-CLI
wp eval-file tests-specifici/test-cache-module.php
```

### Output Atteso

```
╔══════════════════════════════════════════════╗
║   TEST MODULO CACHE - FP PERFORMANCE SUITE   ║
╚══════════════════════════════════════════════╝

🧪 Test 1: Verifica Classe PageCache
───────────────────────────────────────────
✅ Classe PageCache trovata
✅ Metodo clear() disponibile
✅ Metodo purge() disponibile
[...]

╔══════════════════════════════════════════════╗
║              RIEPILOGO TEST CACHE            ║
╚══════════════════════════════════════════════╝

✅ Passed: 17
❌ Failed: 0
⚠️  Warnings: 1
📊 Total: 18

🎉 TUTTI I TEST SUPERATI!
```

---

## 👨‍💼 Metodo 3: Test Manuale da Amministratore

### Setup

1. **Login WordPress**: Accedi come amministratore
2. **Menu Plugin**: Vai su "FP Performance" nel menu admin
3. **Segui la Guida**: Usa `GUIDA_AMMINISTRATORE.md` come riferimento

### Workflow Completo

#### Fase 1: Dashboard e Overview (5 minuti)

```
1. Apri: FP Performance > Dashboard
2. Verifica: Performance Score visibile
3. Verifica: Quick Actions funzionanti
4. Verifica: Statistiche mostrate
5. Screenshot: Salva per riferimento
```

#### Fase 2: Test Modulo Cache (10 minuti)

```
1. Apri: FP Performance > Cache
2. Abilita: Page Cache
3. Configura: TTL 3600s
4. Salva: Impostazioni
5. Test: Visita homepage 3 volte
6. Verifica: DevTools > Network > Header "X-FP-Cache: HIT"
7. Test: Purge Cache button
8. Verifica: Cache svuotata
```

#### Fase 3: Test Modulo Assets (10 minuti)

```
1. Apri: FP Performance > Assets
2. Abilita: Minify CSS
3. Abilita: Minify JS
4. Abilita: Defer JavaScript
5. Salva: Impostazioni
6. Test: Visita homepage
7. Verifica: View Source > script tag con "defer"
8. Verifica: Nessun errore console
9. Test: Funzionalità sito (menu, form, ecc.)
```

#### Fase 4: Test Modulo WebP (15 minuti)

```
1. Apri: FP Performance > Media > WebP
2. Configura: Qualità 80, Metodo GD
3. Test Singolo:
   - Vai su Media Library
   - Seleziona 1 immagine
   - Actions > Convert to WebP
   - Verifica: File .webp creato
   
4. Test Bulk:
   - Clicca "Bulk Convert"
   - Seleziona: All images
   - Avvia: Conversione
   - Monitora: Progress bar
   - Verifica: Report completamento
   
5. Test Delivery:
   - Visita homepage
   - DevTools > Network
   - Filtra: immagini
   - Verifica: Content-Type: image/webp
```

#### Fase 5: Test Modulo Database (10 minuti)

```
1. Apri: FP Performance > Database
2. Seleziona: Tutte le operazioni cleanup
3. Abilita: Dry Run mode
4. Clicca: "Analyze Database"
5. Verifica: Report mostra preview senza eliminare
6. Rivedi: Report dettagliato
7. Disabilita: Dry Run
8. Clicca: "Run Cleanup"
9. Conferma: Operazione
10. Verifica: Report finale con spazio liberato
```

#### Fase 6: Test Modulo Logs (10 minuti)

```
1. Apri: FP Performance > Logs
2. Abilita: Debug Mode
3. Verifica: Costanti WP_DEBUG in wp-config
4. Genera: Un errore di test
5. Apri: Log Viewer
6. Verifica: Errore visualizzato
7. Test: Filtri (Error, Warning, Info)
8. Test: Ricerca full-text
9. Disabilita: Debug Mode
10. Verifica: Backup wp-config disponibile
```

#### Fase 7: Test Funzionalità Avanzate (15 minuti)

```
1. Critical CSS:
   - Apri: Advanced > Critical CSS
   - Inserisci: CSS critico
   - Salva
   - Verifica: View Source > inline CSS

2. CDN (se disponibile):
   - Apri: Advanced > CDN
   - Configura: Provider
   - Abilita: URL Rewriting
   - Verifica: View Source > URL CDN
   
3. Performance Monitoring:
   - Apri: Advanced > Monitoring
   - Abilita: Performance Monitoring
   - Naviga: Alcune pagine
   - Verifica: Dashboard > Metriche
   
4. Scheduled Reports:
   - Apri: Advanced > Reports
   - Configura: Email reports
   - Test: Send test report
   - Verifica: Email ricevuta
```

#### Fase 8: Test PageSpeed Features (15 minuti)

```
1. Lazy Loading:
   - Apri: PageSpeed > Lazy Loading
   - Abilita: Lazy Load Images
   - Salva
   - Verifica: View Source > loading="lazy"
   
2. Font Optimizer:
   - Apri: PageSpeed > Fonts
   - Abilita: Font Display Swap
   - Configura: Preload fonts
   - Verifica: View Source > preconnect + preload
   
3. Image Optimizer:
   - Apri: PageSpeed > Images
   - Abilita: Add Dimensions
   - Verifica: View Source > width/height attributes
   
4. Async CSS:
   - Apri: PageSpeed > CSS
   - Abilita: Async CSS Loading
   - Configura: Whitelist critical CSS
   - Verifica: View Source > media="print" trick
```

#### Fase 9: Test Performance (30 minuti)

```
1. Test Baseline:
   - Prima di abilitare ottimizzazioni
   - PageSpeed Insights: Test homepage
   - GTmetrix: Test homepage
   - WebPageTest: Test homepage
   - Nota: Tutti i risultati

2. Abilita: Tutte le ottimizzazioni
   - Cache: ✅
   - Assets: ✅
   - WebP: ✅
   - Database: ✅
   - PageSpeed: ✅

3. Test Ottimizzato:
   - PageSpeed Insights: Nuovo test
   - GTmetrix: Nuovo test
   - WebPageTest: Nuovo test
   - Nota: Tutti i risultati
   
4. Confronta: Before vs After
   - PageSpeed Score: +XX punti
   - Load Time: -XX%
   - Page Size: -XX%
   - Requests: -XX
```

### Tempo Totale Test Manuali

```
Dashboard: 5 min
Cache: 10 min
Assets: 10 min
WebP: 15 min
Database: 10 min
Logs: 10 min
Advanced: 15 min
PageSpeed: 15 min
Performance: 30 min
────────────────
TOTALE: 120 min (2 ore)
```

---

## 📊 Interpretazione Risultati

### Test Automatici

#### Successo Completo

```
✅ Successi: 135/139 (97.1%)
⚠️  Warning: 4
❌ Errori: 0

✅ VERDETTO: PASS - Plugin completamente funzionante
```

#### Successo Parziale

```
✅ Successi: 120/139 (86.3%)
⚠️  Warning: 10
❌ Errori: 9

⚠️  VERDETTO: PARTIAL - Verificare warning ed errori
    Azione: Consulta log per dettagli
```

#### Fallimento

```
✅ Successi: 90/139 (64.7%)
⚠️  Warning: 20
❌ Errori: 29

❌ VERDETTO: FAIL - Problemi critici rilevati
    Azione: Revisione configurazione necessaria
```

### Test Performance

#### Miglioramenti Attesi

| Metrica | Target Minimo | Target Ottimale |
|---------|---------------|-----------------|
| PageSpeed Mobile | +15 punti | +30 punti |
| PageSpeed Desktop | +10 punti | +20 punti |
| Load Time | -30% | -60% |
| Page Size | -20% | -50% |
| Requests | -30% | -50% |
| TTFB | -20% | -60% |

#### Interpretazione

```
Eccellente: +30 punti mobile, -60% load time
Ottimo: +20-29 punti mobile, -40-59% load time
Buono: +15-19 punti mobile, -30-39% load time
Sufficiente: +10-14 punti mobile, -20-29% load time
Insufficiente: < +10 punti mobile, < -20% load time
```

---

## 🐛 Troubleshooting

### Problema: "Class not found"

```bash
# Verifica plugin attivo
wp plugin list

# Se non attivo
wp plugin activate fp-performance-suite

# Verifica autoload
composer dump-autoload -d fp-performance-suite/
```

### Problema: "Permission denied" su cache directory

```bash
# Correggi permessi
chmod 755 /path/to/wp-content/cache/
chmod 755 /path/to/wp-content/cache/fp-performance/
```

### Problema: Test non produce output

```bash
# Verifica PHP CLI
php -v

# Esegui con verbose
php -d display_errors=1 test-admin-simulation.php

# Controlla error log
tail -f /path/to/wp-content/debug.log
```

### Problema: .htaccess non scrivibile

```bash
# Correggi permessi
chmod 644 /path/to/wordpress/.htaccess

# Verifica owner
chown www-data:www-data /path/to/wordpress/.htaccess
```

---

## 📝 Checklist Pre-Test

Prima di eseguire i test, verifica:

- [ ] WordPress 6.2+ installato
- [ ] PHP 8.0+ configurato
- [ ] FP Performance Suite v1.2.0 attivato
- [ ] Accesso admin WordPress
- [ ] Backup completo sito effettuato
- [ ] Ambiente di staging (consigliato)
- [ ] DevTools browser disponibili
- [ ] Connessione SSH (per test automatici)
- [ ] PageSpeed Insights accessibile
- [ ] Tempo disponibile: 30-120 minuti

---

## 🎯 Cosa Fare Dopo i Test

### Se Tutti i Test Passano ✅

1. **Documentare Risultati**
   - Salva screenshot
   - Esporta configurazione
   - Nota metriche performance

2. **Deploy in Produzione**
   - Applica stessa configurazione
   - Monitora metriche
   - Configura scheduled tasks

3. **Monitoraggio Continuo**
   - Abilita Performance Monitoring
   - Configura email reports
   - Controlla Site Health periodicamente

### Se Ci Sono Warning ⚠️

1. **Analizzare Warning**
   - Leggi messaggi dettagliati
   - Verifica se bloccanti
   - Consulta documentazione

2. **Risolvere se Necessario**
   - Configurazione hosting
   - Permessi file
   - Estensioni PHP

3. **Re-Test**
   - Esegui nuovamente test
   - Verifica miglioramenti

### Se Ci Sono Errori ❌

1. **Log Dettagliati**
   - Abilita WP_DEBUG
   - Controlla error.log
   - Nota stack trace

2. **Supporto**
   - Consulta FAQ
   - Cerca in GitHub Issues
   - Contatta supporto

3. **Rollback**
   - Disabilita plugin temporaneamente
   - Ripristina backup
   - Indaga causa

---

## 📚 Riferimenti

- **Guida Completa**: `GUIDA_AMMINISTRATORE.md`
- **Report Tecnico**: `REPORT_VERIFICA_FUNZIONALE.md`
- **Riepilogo Esecutivo**: `RIEPILOGO_ESECUTIVO_TEST.md`
- **Script Test**: `test-admin-simulation.php`
- **Documentazione Plugin**: `fp-performance-suite/README.md`

---

## 🆘 Supporto

### Risorse

- 📧 Email: info@francescopasseri.com
- 🌐 Website: https://francescopasseri.com
- 📚 Docs: `/docs/`
- 🐛 Issues: GitHub

### Before Contacting Support

Preparare:
1. Versione WordPress
2. Versione PHP
3. Configurazione hosting
4. Output test completo
5. Log errori
6. Screenshot problema

---

**Buon Testing! 🚀**

Seguendo questa guida, potrai verificare completamente la funzionalità del plugin e assicurarti che tutte le ottimizzazioni siano applicate correttamente.

---

**© 2025 FP Performance Suite**  
**Version 1.2.0**

