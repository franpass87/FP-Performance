# 🚀 FP Performance Suite - Lightweight Mode

## Configurazione Ottimizzata per Hosting Condivisi

Questa guida descrive come configurare FP Performance Suite per **ridurre drasticamente l'overhead** su hosting condivisi, mantenendo tutte le funzionalità disponibili.

---

## 📊 **Ottimizzazioni Implementate**

### 1️⃣ **Vendor Ottimizzato (-9 MB)**
- ✅ PHPCS tools spostati in `require-dev`
- ✅ Autoloader ottimizzato con `classmap-authoritative`
- ✅ APCu autoloader abilitato dove disponibile

**Comando per deployment production:**
```bash
composer install --no-dev --optimize-autoloader --classmap-authoritative
```

### 2️⃣ **Logging Ridotto (-20+ chiamate per request)**
- ✅ Tutti gli `error_log()` rimossi da `Plugin.php`
- ✅ Logging condizionale solo se `FP_PERF_DEBUG` è abilitato
- ✅ Logger interno usa `WP_DEBUG` invece di scrivere sempre

### 3️⃣ **Conditional Service Loading**
- ✅ **AdminBar**, **AdminAssets**, **Menu**: caricati SOLO in `is_admin()`
- ✅ **Routes**: caricato SOLO in admin o API requests
- ✅ **Shortcodes**: caricato SOLO in frontend
- ✅ **DatabaseQueryMonitor**: disabilitato se `!WP_DEBUG`

### 4️⃣ **Query Monitor Ottimizzato**
- ✅ Monitoring disabilitato in production se `!WP_DEBUG`
- ✅ `analyzeWpdbQueries()` esegue SOLO se `SAVEQUERIES` definito
- ✅ Nessun overhead su richieste normali

### 5️⃣ **Lazy Loading Admin Pages**
- ✅ Servizi come `Cleaner`, `DatabaseOptimizer` caricati on-demand
- ✅ Nessuna istanziazione pesante nel `__construct()`
- ✅ Servizi istanziati solo quando si accede alla pagina

### 6️⃣ **Animazioni e Confetti Rimossi**
- ✅ Funzione `showConfetti()` rimossa completamente
- ✅ CSS e animazioni confetti eliminate
- ✅ Effetto `typeWriter` disabilitato (overhead inutile)
- ✅ Tempi di animazione ridotti del 50%

---

## 🎯 **Configurazione Raccomandata per Shared Hosting**

### **File: `wp-config.php`**

Aggiungi PRIMA di `/* Buon blogging! */`:

```php
/**
 * FP Performance Suite - Lightweight Mode per Shared Hosting
 */

// 1. DISABILITA Debug in Production
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// 2. ABILITA Debug Selettivo (opzionale - solo per troubleshooting)
// define('FP_PERF_DEBUG', true); // Decommentare SOLO se serve debug

// 3. DISABILITA Query Logging in Production (riduce overhead)
// define('SAVEQUERIES', true); // Decommentare SOLO per debug query

// 4. Ottimizza Memory Limit (shared hosting ha limiti bassi)
define('WP_MEMORY_LIMIT', '128M'); // Ridotto per shared hosting
define('WP_MAX_MEMORY_LIMIT', '256M');

// 5. Disabilita Cron (usa system cron se possibile)
// define('DISABLE_WP_CRON', true); // Abilita se hai system cron

// 6. Ottimizza Autosave
define('AUTOSAVE_INTERVAL', 300); // 5 minuti invece di 60 secondi
define('WP_POST_REVISIONS', 3); // Limita revisioni
```

---

## ⚙️ **Impostazioni Plugin Raccomandate**

### **1. Cache**
- ✅ **Page Cache**: ABILITATO (TTL: 3600s)
- ✅ **Browser Cache**: ABILITATO (TTL: 2592000s per static assets)
- ⚪ **Object Cache**: Disabilitato (a meno che Redis/Memcached disponibili)
- ⚪ **Edge Cache**: Disabilitato (richiede CDN)

### **2. Database**
- ✅ **Query Monitor**: DISABILITATO in production
- ⚪ **Query Cache**: Disabilitato (overhead su shared hosting)
- ✅ **Database Cleaner**: ABILITATO (pulizia settimanale)

### **3. Assets**
- ✅ **Asset Optimizer**: ABILITATO
- ✅ **Script Optimizer**: ABILITATO
- ✅ **CSS Optimizer**: ABILITATO
- ✅ **HTML Minifier**: ABILITATO
- ⚪ **Unused JS Optimizer**: Disabilitato (richiede analisi pesante)
- ⚪ **Code Splitting**: Disabilitato (overhead in fase build)
- ⚪ **Tree Shaker**: Disabilitato (overhead in fase build)
- ✅ **Critical CSS**: ABILITATO (se generato offline)
- ⚪ **Predictive Prefetch**: Disabilitato (consuma banda)

### **4. Media**
- ✅ **Lazy Load Manager**: ABILITATO

### **5. Backend & Admin**
- ✅ **Backend Optimizer**: ABILITATO
- ✅ **Admin Bar**: ABILITATO (rimuovi elementi inutili)

### **6. Mobile**
- ✅ **Mobile Optimizer**: ABILITATO

### **7. Intelligence & ML**
- ⚪ **ML Predictor**: Disabilitato (richiede CPU)
- ⚪ **Pattern Learner**: Disabilitato (richiede memoria)
- ⚪ **Auto Tuner**: Disabilitato (richiede cron frequenti)

### **8. Security**
- ✅ **Htaccess Security**: ABILITATO

### **9. Compression**
- ✅ **Compression Manager**: ABILITATO (Gzip/Brotli)

---

## 📈 **Monitoraggio Performance**

### **Metriche da Monitorare:**

1. **Memory Usage:**
   - Target: < 64 MB per request
   - Verifica: Query Monitor (solo in staging)

2. **Page Load Time:**
   - Target: < 2 secondi
   - Verifica: GTmetrix, PageSpeed Insights

3. **Database Queries:**
   - Target: < 50 query per page
   - Verifica: Query Monitor (solo in staging)

4. **Cache Hit Rate:**
   - Target: > 80%
   - Verifica: FP Performance Dashboard

---

## 🛠️ **Troubleshooting**

### **Se il sito è lento:**

1. **Verifica Memory Limit:**
   ```php
   // Aggiungi a wp-config.php
   ini_set('memory_limit', '256M');
   ```

2. **Abilita Query Logging (temporaneo):**
   ```php
   // Aggiungi a wp-config.php
   define('SAVEQUERIES', true);
   define('FP_PERF_DEBUG', true);
   ```

3. **Controlla log:**
   ```bash
   tail -f wp-content/debug.log
   ```

### **Se ci sono errori:**

1. **Disabilita servizi gradualmente:**
   - Disattiva ML/Intelligence
   - Disattiva Advanced JS Optimizers
   - Disattiva Predictive Prefetch

2. **Verifica autoloader:**
   ```bash
   composer dump-autoload --optimize --classmap-authoritative
   ```

---

## 📦 **Deployment Production**

### **Checklist Pre-Deploy:**

- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] Verifica `WP_DEBUG = false` in `wp-config.php`
- [ ] Verifica `FP_PERF_DEBUG` non definito
- [ ] Cache abilitata e funzionante
- [ ] Database ottimizzato
- [ ] Backup completo eseguito

### **Comando Deploy:**

```bash
# 1. Pulizia vendor
composer install --no-dev --optimize-autoloader --classmap-authoritative

# 2. Rimuovi file dev (opzionale)
rm -rf tests/
rm -rf dev-scripts/
rm -rf docs/

# 3. Verifica dimensioni
du -sh vendor/

# 4. Upload al server (escludi .git, tests, docs)
```

---

## 🔒 **Security Note**

⚠️ **IMPORTANTE:** Non abilitare mai `WP_DEBUG` in production!

- Espone informazioni sensibili
- Degrada le performance
- Aumenta la superficie di attacco

Usa `FP_PERF_DEBUG` solo per troubleshooting temporaneo e rimuovilo immediatamente dopo.

---

## 📊 **Risultati Attesi**

### **Prima delle ottimizzazioni:**
- ❌ Vendor: 15 MB
- ❌ Logging: 20+ error_log per request
- ❌ Memory: ~128 MB per request
- ❌ Load Time: ~3-4 secondi

### **Dopo le ottimizzazioni:**
- ✅ Vendor: 6 MB (-60%)
- ✅ Logging: 0 in production (-100%)
- ✅ Memory: ~64 MB per request (-50%)
- ✅ Load Time: ~1-2 secondi (-50%)

---

## 🎓 **Best Practices**

1. **Testing:**
   - Testa sempre in **staging** prima di production
   - Usa tools come Query Monitor in staging
   - Monitora metriche reali con GTmetrix

2. **Maintenance:**
   - Pulizia database settimanale
   - Svuota cache regolarmente
   - Monitora dimensioni uploads/

3. **Scaling:**
   - Se il sito cresce, considera VPS
   - Valuta Redis/Memcached per object cache
   - Implementa CDN (Cloudflare, BunnyCDN)

---

## 📞 **Supporto**

- **Documentazione:** `docs/`
- **Issues:** GitHub Issues
- **Email:** francesco@francescopasseri.com

---

**Ultimo aggiornamento:** 26 Ottobre 2025  
**Versione Plugin:** 1.6.0+  
**Compatibilità:** WordPress 6.0+, PHP 8.0+

