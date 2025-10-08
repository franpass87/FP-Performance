# Guida Deployment Asset Modulari

## 📋 Pre-Deployment Checklist

### 1. Verifica Locale

```bash
# Vai nella directory assets
cd fp-performance-suite/assets

# Esegui lo script di verifica
./verify-structure.sh
```

**Risultato atteso**: 44/44 test passati ✅

### 2. Verifica Git

```bash
# Verifica status
git status

# Verifica che tutti i nuovi file siano tracciati
git add assets/css/ assets/js/ assets/legacy/
git add src/Admin/Assets.php
git add *.md assets/*.sh
```

### 3. Test Funzionali (Ambiente WordPress)

#### Installazione Plugin in WordPress Locale

```bash
# Se non hai già un ambiente WordPress locale
# 1. Installa WordPress localmente (Local by Flywheel, XAMPP, Docker, ecc.)
# 2. Copia il plugin nella directory wp-content/plugins/

# Oppure usa WP-CLI
wp plugin activate fp-performance-suite
```

#### Test Dashboard
- [ ] Apri `/wp-admin/admin.php?page=fp-performance-suite`
- [ ] Verifica che la pagina si carichi senza errori
- [ ] Controlla console browser (F12) per errori JavaScript
- [ ] Verifica Network tab per caricamento file

#### Test Stili CSS
- [ ] Verifica che tutti gli stili siano applicati correttamente
- [ ] Controlla header, breadcrumbs, card, badge
- [ ] Verifica tooltip on hover
- [ ] Testa tabelle e log viewer

#### Test Dark Mode
```javascript
// Apri console browser e forza dark mode
document.documentElement.style.colorScheme = 'dark';
```
- [ ] Verifica che i colori cambino
- [ ] Controlla che il testo sia leggibile
- [ ] Verifica contrasto adeguato

#### Test JavaScript
- [ ] Clicca su un pulsante preset → dovrebbe mostrare notifica
- [ ] Verifica log viewer → dovrebbe aggiornarsi ogni 2 secondi
- [ ] Testa bulk action → dovrebbe mostrare progress bar
- [ ] Prova toggle rischioso → dovrebbe chiedere conferma "PROCEDI"

#### Test API Pubblica
```javascript
// Apri console browser
console.log(window.fpPerfSuiteUtils);
// Dovrebbe mostrare: { showNotice, showProgress, removeProgress }

// Testa notifica
window.fpPerfSuiteUtils.showNotice('Test notifica', 'success');
```

### 4. Test Browser

| Browser | Versione Min | Status |
|---------|--------------|--------|
| Chrome | 61+ | ⬜ |
| Firefox | 60+ | ⬜ |
| Safari | 11+ | ⬜ |
| Edge | 79+ | ⬜ |

**ES6 Modules Support**: Tutti i browser moderni supportano ES6 modules

### 5. Verifica Performance

#### DevTools Network Tab
1. Apri DevTools (F12) → Network
2. Ricarica pagina admin
3. Verifica:
   - [ ] `css/admin.css` caricato (1 richiesta)
   - [ ] 16 file CSS importati automaticamente
   - [ ] `js/main.js` caricato con type="module"
   - [ ] Moduli JS caricati automaticamente
   - [ ] Nessun errore 404

#### Performance Metrics
```javascript
// Console browser
performance.getEntriesByType('resource')
  .filter(r => r.name.includes('fp-performance-suite'))
  .forEach(r => console.log(r.name, r.duration + 'ms'));
```

### 6. Verifica PHP

```bash
# Se hai PHP CLI
php -l src/Admin/Assets.php
# Dovrebbe rispondere: No syntax errors detected

# Se hai PHPStan
phpstan analyse src/Admin/Assets.php

# Se hai PHPCS
phpcs src/Admin/Assets.php
```

---

## 🚀 Deployment Steps

### Ambiente Staging

#### 1. Commit Changes

```bash
git add .
git commit -m "feat: modularizzare asset CSS e JavaScript

- Suddiviso admin.css in 17 file modulari
- Suddiviso admin.js in 9 moduli ES6
- Aggiornato Assets.php per supporto ES6 modules
- Aggiunta documentazione completa
- Backup file originali in assets/legacy/

BREAKING CHANGES: Nessuno (100% retrocompatibile)

Test: 44/44 automatici passati"
```

#### 2. Push to Repository

```bash
git push origin main
# oppure
git push origin develop
```

#### 3. Deploy su Staging

```bash
# Metodo 1: SSH + Git
ssh user@staging-server
cd /path/to/wordpress/wp-content/plugins/fp-performance-suite
git pull origin main

# Metodo 2: FTP
# Upload intera directory fp-performance-suite/

# Metodo 3: Deployment automatico (se configurato)
# Il CI/CD dovrebbe gestire automaticamente
```

#### 4. Test su Staging

- [ ] Login su WordPress staging
- [ ] Attiva/Riattiva il plugin
- [ ] Ripeti tutti i test funzionali
- [ ] Verifica che non ci siano errori nei log

#### 5. Monitoring Staging

```bash
# Controlla error log
tail -f /path/to/wordpress/wp-content/debug.log

# Controlla access log
tail -f /var/log/apache2/access.log  # o nginx
```

### Ambiente Produzione

⚠️ **IMPORTANTE**: Esegui solo dopo successo completo in staging!

#### 1. Backup Produzione

```bash
# Backup database
wp db export backup-pre-modular-assets.sql

# Backup files
tar -czf backup-pre-modular-assets.tar.gz wp-content/plugins/fp-performance-suite/
```

#### 2. Maintenance Mode (Opzionale)

```bash
# Attiva maintenance mode
wp maintenance-mode activate

# Oppure crea file .maintenance
echo '<?php $upgrading = time(); ?>' > .maintenance
```

#### 3. Deploy

```bash
# Metodo consigliato: Blue-Green deployment
# 1. Deploy su server nuovo
# 2. Test completo
# 3. Switch traffico
# 4. Rollback veloce se problemi

# Metodo tradizionale:
ssh user@production-server
cd /path/to/wordpress/wp-content/plugins/fp-performance-suite
git pull origin main
```

#### 4. Test Produzione

- [ ] Verifica homepage
- [ ] Login admin
- [ ] Apri pagina plugin
- [ ] Controlla console per errori
- [ ] Verifica funzionalità critiche

#### 5. Disattiva Maintenance Mode

```bash
wp maintenance-mode deactivate
# oppure
rm .maintenance
```

#### 6. Monitoring Post-Deploy

**Prime 15 minuti:**
- Controlla error log in tempo reale
- Monitora performance (New Relic, Datadog, ecc.)
- Verifica metriche utenti (Google Analytics)

**Prime 24 ore:**
- Controlla tasso errori
- Verifica feedback utenti
- Monitora performance server

---

## 🔄 Rollback Plan

### Se Qualcosa Va Storto

#### Rollback Rapido (Opzione 1: Git)

```bash
# Torna al commit precedente
git revert HEAD
git push origin main

# Deploy
ssh user@server
cd /path/to/plugin
git pull origin main
```

#### Rollback con File Legacy (Opzione 2)

```bash
# Ripristina file originali
cd assets/
cp legacy/admin.css.bak admin.css
cp legacy/admin.js.bak admin.js

# Rimuovi directory modulari
rm -rf css/ js/

# Ripristina Assets.php precedente (da Git)
git checkout HEAD~1 -- src/Admin/Assets.php
```

#### Rollback Completo (Opzione 3)

```bash
# Ripristina da backup
cd /path/to/wordpress/wp-content/plugins/
rm -rf fp-performance-suite/
tar -xzf backup-pre-modular-assets.tar.gz
```

---

## 📊 Metriche Post-Deploy

### KPI da Monitorare

1. **Performance**
   - Page Load Time
   - Time to Interactive
   - First Contentful Paint
   - Lighthouse Score

2. **Errori**
   - JavaScript errors (console)
   - PHP errors (error log)
   - 404 errors (access log)
   - User-reported issues

3. **Usabilità**
   - Plugin usage metrics
   - Feature adoption
   - User satisfaction

### Tools Consigliati

- **Performance**: Lighthouse, WebPageTest, GTmetrix
- **Monitoring**: New Relic, Datadog, Sentry
- **Analytics**: Google Analytics, Matomo
- **Error Tracking**: Sentry, Rollbar, Bugsnag

---

## 🐛 Troubleshooting

### Problema: Stili non caricano

**Sintomi**: Pagina senza stili o stili parziali

**Causa**: Path @import errati o file mancanti

**Soluzione**:
```bash
# Verifica presenza file
./assets/verify-structure.sh

# Controlla DevTools Network
# Cerca 404 errors sui file CSS

# Verifica path relativi in admin.css
cat assets/css/admin.css | grep @import
```

### Problema: JavaScript non funziona

**Sintomi**: Console mostra errori, funzionalità non funzionano

**Causa 1**: Browser non supporta ES6 modules (molto raro)
```javascript
// Test supporto ES6
if ('noModule' in document.createElement('script')) {
  console.log('ES6 modules supportati ✅');
} else {
  console.log('ES6 modules NON supportati ❌');
}
```

**Causa 2**: Errore import/export
```bash
# Controlla console browser per dettagli
# Verifica path import in main.js
grep "import" assets/js/main.js
```

**Soluzione**: Usa rollback e verifica file

### Problema: Errori 404 su moduli

**Sintomi**: Network tab mostra 404 su file CSS/JS

**Causa**: Path errati o file mancanti

**Soluzione**:
```bash
# Verifica tutti i file
find assets/ -name "*.css" -o -name "*.js"

# Ri-esegui verifica
./assets/verify-structure.sh

# Se fallisce, ripristina da Git
git checkout -- assets/
```

### Problema: Retrocompatibilità rotta

**Sintomi**: Codice esterno non funziona

**Causa**: API globale non esposta

**Soluzione**:
```javascript
// Verifica in console
console.log(window.fpPerfSuiteUtils);

// Dovrebbe esistere e contenere:
// { showNotice, showProgress, removeProgress }
```

Se manca, verifica `assets/js/main.js` linee 43-47

---

## ✅ Post-Deployment Checklist

### Immediate (Prime ore)
- [ ] Nessun errore in console browser
- [ ] Nessun errore in PHP error log
- [ ] Tutte le pagine caricano correttamente
- [ ] Funzionalità core funzionano
- [ ] Performance accettabile

### Breve termine (1-7 giorni)
- [ ] Nessun report utenti su problemi
- [ ] Metriche performance stabili
- [ ] Tasso errori normale
- [ ] Feedback positivo

### Successo Completo ✅
- Tutti i test passati
- Nessun problema segnalato
- Performance migliorata o stabile
- Codice più manutenibile

---

## 📞 Supporto

### In caso di problemi:

1. **Controlla documentazione**
   - `assets/README.md`
   - `MODULARIZATION_REPORT.md`
   - `assets/VERIFICATION.md`

2. **Esegui diagnostica**
   ```bash
   ./assets/verify-structure.sh
   ```

3. **Controlla log**
   - Browser console (F12)
   - PHP error log
   - Server access log

4. **Rollback se necessario**
   - Usa procedura rollback sopra
   - Investigare offline

5. **Debug**
   - Attiva WP_DEBUG
   - Usa browser DevTools
   - Controlla Network tab

---

**Preparato da**: Francesco Passeri  
**Data**: Ottobre 2025  
**Versione Plugin**: 1.1.0  
**Versione Documento**: 1.0