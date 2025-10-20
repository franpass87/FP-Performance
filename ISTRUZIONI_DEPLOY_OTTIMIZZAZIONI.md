# 🚀 Istruzioni Deploy Ottimizzazioni

**Data:** 20 Ottobre 2025  
**Tipo:** Guida Deploy e Testing  
**Target:** Produzione

---

## ✅ Pre-Deploy Checklist

### 1. Verifica Linting ✅
```bash
# Tutti i file modificati passano il linting
# Status: ✅ 0 errori
```

### 2. Backup Database e File
```bash
# IMPORTANTE: Fai backup prima del deploy!

# Backup database
wp db export backup-pre-optimizations-$(date +%Y%m%d).sql

# Backup file plugin
cd wp-content/plugins
tar -czf fp-performance-suite-backup-$(date +%Y%m%d).tar.gz fp-performance-suite/
```

### 3. Test in Ambiente Staging

```bash
# Se hai staging, testa lì prima
# Verifica che:
# - Pagine admin carichino correttamente
# - Toggle funzionino
# - Modal si aprano
# - Validazione form funzioni
```

---

## 📦 Deploy in Produzione

### Opzione A: Deploy Completo (Raccomandato)

```bash
# 1. Carica file modificati via FTP/SFTP
# Cartelle principali:
/src/Admin/Menu.php
/src/Admin/Pages/*.php (14 file)
/src/Services/Cache/*.php (2 file)
/src/Utils/FormValidator.php (nuovo)
/assets/js/utils/accessibility.js (nuovo)
/assets/js/components/modal.js (nuovo)
/assets/css/components/modal.css (nuovo)
/assets/js/main.js
/assets/css/admin.css

# 2. Verifica permessi file (se Linux)
chmod 644 src/**/*.php
chmod 644 assets/**/*.{js,css}

# 3. Pulisci cache WordPress
wp cache flush

# 4. Testa in produzione
# - Apri admin > FP Performance
# - Verifica menu tradotto
# - Testa un toggle
# - Salva impostazioni
```

### Opzione B: Deploy Graduale (Sicuro)

**Step 1 - Solo Traduzioni:**
```bash
# Deploy solo file Menu.php e Pages/
# Verifica funzionamento
# Se OK, procedi
```

**Step 2 - Performance:**
```bash
# Deploy Services/Cache/
# Monitora performance admin
# Se OK, procedi
```

**Step 3 - Accessibilità + Modal:**
```bash
# Deploy assets/js e assets/css
# Testa modal e ARIA
# Se OK, deploy completo
```

---

## 🧪 Testing Post-Deploy

### Test 1: Menu e Traduzioni

```bash
✅ Apri wp-admin > FP Performance
✅ Verifica voci menu in italiano:
   - Panoramica
   - Risorse (non Assets)
   - Registro Attività (non Logs)
   - Opzioni Avanzate (🔬, non ⚙️)

✅ Clicca su ogni voce
✅ Verifica breadcrumbs corretti
```

### Test 2: Performance

```bash
✅ Apri Chrome DevTools > Network
✅ Carica pagina Cache
✅ Controlla query DB (dovrebbero essere ridotte)
✅ Nota tempo caricamento

✅ Ricarica pagina
✅ Tempo dovrebbe essere 50-100ms più veloce
```

### Test 3: Modal

```bash
✅ Apri Console JavaScript
✅ Esegui:
   await fpPerfSuiteUtils.confirm('Test');
   
✅ Verifica:
   - Modal si apre con animazione
   - Premendo ESC si chiude
   - Click fuori chiude
   - Focus trap funziona (Tab circola solo nel modal)
```

### Test 4: Accessibilità

```bash
✅ Keyboard navigation:
   - Tab tra gli elementi
   - Spazio per attivare toggle
   - Enter per submit
   - ESC per chiudere modal

✅ Ispeziona toggle in DevTools:
   - Cerca role="switch"
   - Verifica aria-labelledby
   - Verifica aria-describedby
   - aria-checked cambia quando clicchi
```

### Test 5: FormValidator

```bash
✅ Apri pagina Cache
✅ Lascia campo "Durata Cache" vuoto
✅ Inserisci valore < 60
✅ Submit form

✅ Verifica:
   - Messaggio errore in italiano
   - "Il campo Durata Cache deve essere almeno 60"
   - Form non viene salvato
```

---

## 🐛 Troubleshooting

### Problema: JavaScript non carica

```bash
# Verifica console browser per errori
# Comune: sintassi ES6 non supportata

# Soluzione: Verifica browser support
# Chrome 91+, Firefox 89+, Safari 14+, Edge 91+

# Se browser vecchio, considera transpiling con Babel
```

### Problema: Modal non si apre

```bash
# Check 1: CSS caricato?
# DevTools > Network > admin.css > Cerca "modal"

# Check 2: JS caricato?
# Console > typeof window.fpPerfSuiteUtils
# Dovrebbe essere "object"

# Check 3: Import corretto?
# DevTools > Sources > main.js > Cerca import modal
```

### Problema: ARIA non funziona

```bash
# Check: ID duplicati
# Ogni toggle deve avere ID unici
# "page-cache-label" vs "browser-cache-label"

# Check: JavaScript inizializzato
# Console > fpPerfSuiteUtils
# Dovrebbe includere confirm, alert, etc.
```

### Problema: Validazione non funziona

```bash
# Check: FormValidator importato?
# In Cache.php cerca: use FP\PerfSuite\Utils\FormValidator;

# Check: Rules corrette?
# ['min' => 60] non ['min', 60]

# Check: Messaggi localizzati?
# Dovrebbero essere __('...', 'fp-performance-suite')
```

---

## 📊 Monitoraggio Post-Deploy

### Metriche da Monitorare (Prime 48h)

**Performance:**
```bash
# Query Monitor plugin (se installato)
# Confronta query count prima/dopo

# Chrome DevTools > Performance
# Registra caricamento pagina admin
# Target: -50-100ms

# Server logs
# Monitora errori PHP
```

**Accessibilità:**
```bash
# Lighthouse audit
# Chrome DevTools > Lighthouse > Accessibility
# Target: Score 95+

# axe DevTools
# Installa extension
# Scan ogni pagina admin
# Target: 0 violations
```

**JavaScript:**
```bash
# Console errors
# Apri console in ogni pagina
# Verifica 0 errori
# Verifica messaggi debug (se WP_DEBUG=true)
```

---

## 🔧 Rollback (Se Necessario)

### Rollback Rapido

```bash
# Ripristina da backup
cd wp-content/plugins
rm -rf fp-performance-suite
tar -xzf fp-performance-suite-backup-YYYYMMDD.tar.gz

# Oppure via FTP
# Sostituisci con versione backup

# Pulisci cache
wp cache flush
```

### Rollback Parziale

```bash
# Solo file modificati problematici
# Esempio: se modal non funziona

# Rimuovi import da main.js:
# - Commenta riga import modal
# - Ricarica pagina
```

---

## 📝 Note Importanti

### Compatibilità

✅ **WordPress:** 6.0+  
✅ **PHP:** 8.0+  
✅ **Browser:** Chrome 91+, Firefox 89+, Safari 14+, Edge 91+  
✅ **Screen Reader:** NVDA, JAWS, VoiceOver

### Breaking Changes

✅ **NESSUNO** - 100% backward compatible

### Dipendenze

✅ Nessuna dipendenza esterna aggiunta  
✅ Solo codice nativo WordPress  
✅ ES6 modules (supporto browser moderni)

---

## 🎯 Success Criteria

Deploy considerato successo se:

- ✅ Menu in italiano visibile
- ✅ Pagine caricano senza errori
- ✅ Console browser senza errori
- ✅ Modal si aprono con conferme
- ✅ Toggle funzionano normalmente
- ✅ Form salvano correttamente
- ✅ Performance admin migliorate (misurabile)

---

## 📞 Support

### Documentazione Disponibile

1. `SUMMARY_OTTIMIZZAZIONI_COMPLETE.md` - Riepilogo completo
2. `GUIDA_USO_NUOVE_FUNZIONALITA.md` - API e esempi
3. `ANALISI_OTTIMIZZAZIONI_SUGGERITE.md` - Dettagli tecnici

### Debug Mode

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('SCRIPT_DEBUG', true);

// Verifica log in wp-content/debug.log
// Cerca "[FP Performance Suite]"
```

---

## ✅ Post-Deploy Actions

### Dopo Deploy Successo

1. ✅ **Aggiorna changelog** del plugin
2. ✅ **Notifica utenti** (se pubblico)
3. ✅ **Monitora** per 48h
4. ✅ **Backup** nuova versione funzionante
5. ✅ **Pianifica** completamento rimanente (se necessario)

### Se Tutto OK

🎉 **Congratulazioni!** 

Il plugin è ora:
- Più veloce
- Più accessibile  
- Più professionale
- Pronto per crescere

---

**Creato da:** AI Assistant  
**Data:** 20 Ottobre 2025  
**Versione:** 1.0  
**Status:** Ready to Deploy 🚀

