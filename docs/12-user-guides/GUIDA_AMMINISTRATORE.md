# 🎯 Guida Completa per Amministratore - FP Performance Suite

> **Test Simulazione Utente Amministrativo - Verifica Completa Funzionalità Plugin**

## 📋 Indice

1. [Introduzione](#introduzione)
2. [Accesso e Dashboard](#accesso-e-dashboard)
3. [Test Modulo Cache](#test-modulo-cache)
4. [Test Modulo Assets](#test-modulo-assets)
5. [Test Modulo Media/WebP](#test-modulo-mediawebp)
6. [Test Modulo Database](#test-modulo-database)
7. [Test Modulo Logs](#test-modulo-logs)
8. [Test Funzionalità Avanzate](#test-funzionalità-avanzate)
9. [Test Funzionalità PageSpeed](#test-funzionalità-pagespeed)
10. [Scenari d'Uso Reali](#scenari-duso-reali)
11. [Risoluzione Problemi](#risoluzione-problemi)

---

## 🎯 Introduzione

Questa guida simula l'utilizzo completo del plugin **FP Performance Suite v1.2.0** da parte di un amministratore WordPress, testando tutte le funzionalità disponibili e verificando la corretta applicazione delle ottimizzazioni.

### Prerequisiti

- WordPress 6.2+ installato
- PHP 8.0+
- Accesso come amministratore WordPress
- FP Performance Suite v1.2.0 attivato

---

## 🏠 Accesso e Dashboard

### Come Accedere

1. **Login WordPress**: Accedi al pannello amministrativo WordPress
2. **Menu Plugin**: Cerca "FP Performance" nel menu laterale
3. **Dashboard**: Clicca su "Dashboard" per vedere la panoramica

### Test Dashboard

#### ✅ Cosa Verificare

1. **Performance Score**
   - Visualizzazione score complessivo (0-100)
   - Breakdown per categoria (Cache, Assets, Media, Database)
   - Indicatori colorati (verde/giallo/rosso)

2. **Quick Actions**
   - Pulsanti rapidi per:
     - Svuota cache
     - Avvia conversione WebP
     - Cleanup database
     - Visualizza log

3. **Statistiche Rapide**
   - Dimensione cache
   - Immagini WebP convertite
   - Dimensione database
   - Query count

#### 🧪 Test Pratico

```php
// Verifica presenza dashboard
1. Vai su: WordPress Admin > FP Performance > Dashboard
2. Verifica che la pagina si carichi correttamente
3. Controlla che il Performance Score sia visibile
4. Verifica che tutti i widget siano presenti
```

**Risultato Atteso**: Dashboard caricata con score e statistiche visualizzate correttamente.

---

## 💾 Test Modulo Cache

### 1. Page Cache (Filesystem)

#### Configurazione

1. Vai su **FP Performance > Cache**
2. Abilita "Page Cache"
3. Configura opzioni:
   - Cache TTL (es. 3600 secondi)
   - Esclusioni URL
   - Esclusioni per utenti loggati

#### Test Funzionalità

##### Test 1: Creazione Cache

```bash
# Passo 1: Svuota cache esistente
1. Clicca "Purge All Cache"
2. Verifica messaggio successo

# Passo 2: Visita una pagina del sito
3. Apri homepage in incognito
4. Ricarica 2-3 volte

# Passo 3: Verifica file cache
5. Vai su FP Performance > Cache > Stats
6. Verifica "File in Cache" > 0
```

**Risultato Atteso**: File cache creati in `/wp-content/cache/fp-performance/`

##### Test 2: Cache Hit

```bash
# Test velocità
1. Apri DevTools > Network
2. Ricarica homepage
3. Controlla header HTTP: X-FP-Cache: HIT
4. Nota tempo di caricamento
```

**Risultato Atteso**: Header `X-FP-Cache: HIT` presente e tempo ridotto (~50-80% più veloce)

##### Test 3: Cache Invalidation

```bash
# Modifica contenuto
1. Modifica un post o una pagina
2. Pubblica le modifiche
3. Visita la pagina modificata
4. Verifica che le modifiche siano visibili
```

**Risultato Atteso**: Cache automaticamente invalidata, modifiche visibili immediatamente

##### Test 4: Esclusioni

```bash
# Test esclusioni
1. Aggiungi "/carrello" alle esclusioni
2. Salva
3. Visita /carrello
4. Verifica header: X-FP-Cache: BYPASS
```

**Risultato Atteso**: Pagina esclusa non cachata

### 2. Browser Cache Headers

#### Test .htaccess

```bash
1. Vai su Cache > Browser Cache
2. Abilita "Browser Cache Headers"
3. Configura scadenze:
   - Immagini: 1 anno
   - CSS/JS: 1 mese
   - HTML: 1 ora
4. Salva e applica
```

##### Verifica Applicazione

```bash
# Verifica file .htaccess
1. Connettiti via FTP/SSH
2. Apri /.htaccess
3. Cerca sezione "# BEGIN FP Performance Suite"
4. Verifica regole cache presenti
```

**Esempio Regole Attese**:

```apache
# BEGIN FP Performance Suite
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
# END FP Performance Suite
```

##### Test Browser Cache

```bash
1. Apri un'immagine del sito
2. Controlla Response Headers:
   - Cache-Control: max-age=31536000
   - Expires: [data futura]
3. Ricarica pagina
4. Verifica 304 Not Modified
```

**Risultato Atteso**: Header cache presenti, risorse servite dalla cache del browser

### 3. Cache Warmer

```bash
1. Vai su Cache > Advanced
2. Clicca "Warm Cache"
3. Scegli opzioni:
   - Homepage
   - Sitemap XML
   - Custom URLs
4. Avvia processo
```

**Risultato Atteso**: Processo completato, cache pre-generata per le URL selezionate

---

## ⚡ Test Modulo Assets

### 1. Minificazione CSS/JS

#### Test Minify CSS

```bash
1. Vai su Assets > Optimization
2. Abilita "Minify CSS"
3. Salva
4. Ricarica homepage
5. View Source
6. Cerca tag <link rel="stylesheet">
7. Apri URL CSS
```

**Risultato Atteso**: CSS minificato (senza spazi/commenti), riduzione 20-40%

#### Test Minify JS

```bash
1. Abilita "Minify JavaScript"
2. Salva
3. Ricarica homepage
4. Controlla sorgente JS
```

**Risultato Atteso**: JavaScript minificato, riduzione dimensione

#### Test Combina CSS/JS

```bash
1. Abilita "Combine CSS Files"
2. Abilita "Combine JS Files"
3. Salva
4. Ricarica homepage
5. Conta richieste CSS/JS in DevTools
```

**Risultato Atteso**: File combinati in 1-2 file invece di molti, riduzione richieste HTTP

### 2. Defer/Async JavaScript

```bash
1. Vai su Assets > Scripts
2. Abilita "Defer JavaScript"
3. Opzionale: Aggiungi eccezioni (es. jQuery)
4. Salva
5. View Source homepage
```

**Verifica**:

```html
<!-- Prima -->
<script src="script.js"></script>

<!-- Dopo -->
<script src="script.js" defer></script>
```

**Test Funzionalità**:

```bash
1. Naviga il sito
2. Verifica che tutto funzioni
3. Controlla console per errori
4. Testa moduli interattivi (menu, slider, form)
```

**Risultato Atteso**: JavaScript caricato in modo asincrono, nessun errore, sito funzionante

### 3. DNS Prefetch & Preconnect

```bash
1. Vai su Assets > Resource Hints
2. Aggiungi domini esterni:
   - fonts.googleapis.com
   - fonts.gstatic.com
   - analytics.google.com
3. Abilita preconnect per font
4. Salva
```

**Verifica HTML**:

```html
<head>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    <link rel="preconnect" href="//fonts.gstatic.com" crossorigin>
</head>
```

**Risultato Atteso**: Header presenti, connessioni più veloci a risorse esterne

### 4. Heartbeat Control

```bash
1. Vai su Assets > WordPress
2. Configura Heartbeat:
   - Dashboard: Slow (60s)
   - Editor: Default (15s)
   - Frontend: Disable
3. Salva
```

**Test**:

```bash
1. Apri DevTools > Network
2. Resta su una pagina admin
3. Monitora richieste "heartbeat"
4. Verifica frequenza ridotta
```

**Risultato Atteso**: Richieste heartbeat ridotte, minor carico server

### 5. Disabilita Emoji e Embeds

```bash
1. Vai su Assets > WordPress
2. Abilita:
   - ✅ Disable Emojis
   - ✅ Disable Embeds
3. Salva
4. View Source homepage
```

**Verifica**:

```bash
# NON dovrebbero essere presenti:
- wp-emoji-release.min.js
- wp-includes/js/wp-embed.min.js
```

**Risultato Atteso**: Script emoji/embeds rimossi, 2 richieste HTTP in meno

---

## 🖼️ Test Modulo Media/WebP

### 1. Conversione WebP Singola

#### Setup

```bash
1. Vai su Media > WebP Converter
2. Configura:
   - Metodo: GD (o Imagick se disponibile)
   - Qualità: 80
   - Abilita WebP
3. Salva
```

#### Test Conversione

```bash
1. Vai su Media Library
2. Seleziona un'immagine JPEG/PNG
3. Clicca "Convert to WebP" (bulk actions)
4. Attendi completamento
```

**Verifica**:

```bash
1. Aggiorna Media Library
2. Nota presenza file .webp
3. Verifica dimensione ridotta (20-40% più piccolo)
```

**Esempio**:

```
immagine.jpg (100 KB) → immagine.webp (65 KB)
Risparmio: 35%
```

### 2. Conversione Bulk

```bash
1. Vai su Media > WebP > Bulk Convert
2. Clicca "Convert All Images"
3. Monitora progress bar
4. Attendi completamento
```

**Durante il Processo**:

- Progress: 45/200 immagini (22.5%)
- Tempo stimato: 3 minuti
- Errori: 0

**Dopo Completamento**:

```bash
1. Controlla report:
   - Totale: 200 immagini
   - Convertite: 198
   - Fallite: 2
   - Spazio risparmiato: 12.5 MB
```

### 3. Test WebP Delivery

```bash
1. Abilita "WebP Auto-Delivery"
2. Salva
3. Apri homepage in Chrome
4. DevTools > Network
5. Filtra per immagini
6. Verifica formato WebP servito
```

**Verifica Request Headers**:

```
Accept: image/avif,image/webp,image/apng,*/*
```

**Verifica Response**:

```
Content-Type: image/webp
```

**Fallback Test**:

```bash
1. Apri sito in browser vecchio (IE11)
2. Verifica che vengano serviti JPEG/PNG originali
```

**Risultato Atteso**: WebP servito a browser compatibili, fallback automatico per altri

### 4. Report WebP Coverage

```bash
1. Vai su Media > WebP > Report
2. Visualizza statistiche:
   - Coverage: 99%
   - Immagini totali: 200
   - WebP disponibili: 198
   - Spazio risparmiato: 12.5 MB
```

---

## 🗄️ Test Modulo Database

### 1. Database Cleanup - Dry Run

```bash
1. Vai su Database > Cleanup
2. Seleziona operazioni:
   - ✅ Revisioni post
   - ✅ Bozze auto-salvate
   - ✅ Commenti spam/cestino
   - ✅ Transient scaduti
   - ✅ Metadata orfani
3. Seleziona "Dry Run"
4. Clicca "Analyze Database"
```

**Report Dry Run Atteso**:

```
=== DATABASE CLEANUP DRY RUN REPORT ===

Operazioni da Eseguire:
- Revisioni post: 1,245 da eliminare
- Bozze auto: 89 da eliminare
- Commenti spam: 456 da eliminare
- Commenti cestino: 23 da eliminare
- Transient scaduti: 2,341 da eliminare
- Metadata orfani: 67 da eliminare

Spazio totale da liberare: 8.7 MB

⚠️ NOTA: Questo è un test. Nessun dato è stato eliminato.
```

### 2. Database Cleanup - Esecuzione

```bash
1. Rivedi il report
2. Deseleziona operazioni non volute (opzionale)
3. DISABILITA "Dry Run"
4. Clicca "Run Cleanup"
5. Conferma azione
```

**Durante l'Esecuzione**:

```
Cleanup in corso...
✅ Eliminati 1,245 revisioni
✅ Eliminati 89 auto-draft
✅ Eliminati 456 commenti spam
✅ Eliminati 23 commenti cestino
✅ Eliminati 2,341 transient
✅ Eliminati 67 metadata orfani

Cleanup completato!
Spazio liberato: 8.7 MB
```

### 3. Table Optimization

```bash
1. Vai su Database > Optimize
2. Visualizza lista tabelle
3. Controlla overhead
4. Seleziona tabelle con overhead
5. Clicca "Optimize Selected Tables"
```

**Esempio Report**:

```
Tabella             | Overhead | Status
--------------------|----------|----------
wp_posts            | 2.3 MB   | Ottimizzata ✅
wp_postmeta         | 1.8 MB   | Ottimizzata ✅
wp_options          | 512 KB   | Ottimizzata ✅

Totale spazio recuperato: 4.6 MB
```

### 4. Scheduled Cleanup

```bash
1. Vai su Database > Schedule
2. Abilita "Auto Cleanup"
3. Configura:
   - Frequenza: Weekly
   - Giorno: Sunday
   - Ora: 03:00
   - Operazioni: [seleziona tutte]
4. Abilita "Email Report"
5. Salva
```

**Verifica Cron**:

```bash
1. Vai su Tools > Scheduled Events (plugin esterno)
2. Cerca "fp_ps_db_cleanup_cron"
3. Verifica prossima esecuzione
```

**Risultato Atteso**: Cleanup programmato attivo, esecuzione automatica settimanale

---

## 📝 Test Modulo Logs

### 1. Debug Toggle

#### Abilita Debug

```bash
1. Vai su Logs > Debug
2. Clicca "Enable Debug Mode"
3. Seleziona opzioni:
   - ✅ WP_DEBUG = true
   - ✅ WP_DEBUG_LOG = true
   - ⬜ WP_DEBUG_DISPLAY = false (sicurezza)
4. Clicca "Apply Debug Settings"
```

**Verifica**:

```bash
1. Connettiti via FTP
2. Scarica /wp-config.php
3. Verifica righe:
```

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);
```

#### Test Logging

```bash
1. Genera un errore di test:
   - Disattiva temporaneamente un plugin richiesto
   - Visita una pagina
2. Vai su Logs > Viewer
3. Verifica presenza errori nel log
```

#### Disabilita Debug

```bash
1. Vai su Logs > Debug
2. Clicca "Disable Debug Mode"
3. Verifica wp-config.php ripristinato
```

### 2. Backup wp-config

```bash
1. Vai su Logs > Backups
2. Visualizza lista backup wp-config.php
3. Scarica un backup
4. Verifica contenuto
```

**Esempio Lista**:

```
Data                | Dimensione | Azioni
--------------------|------------|--------
2025-10-19 14:30    | 3.2 KB     | [Download] [Restore]
2025-10-18 10:15    | 3.1 KB     | [Download] [Restore]
2025-10-17 08:45    | 3.1 KB     | [Download] [Restore]
```

### 3. Log Viewer in Tempo Reale

```bash
1. Vai su Logs > Real-time Viewer
2. Abilita debug (se non già attivo)
3. In un'altra scheda, genera attività:
   - Modifica un post
   - Svuota cache
   - Converti un'immagine
4. Torna al Log Viewer
5. Clicca "Refresh" o abilita auto-refresh
```

**Filtri Disponibili**:

```bash
1. Livello: [All | Error | Warning | Info | Debug]
2. Contesto: [All | Cache | WebP | Database | Assets]
3. Ricerca: [testo libero]
4. Tail: Ultimi N righe
```

**Esempio Log**:

```
[2025-10-19 15:23:45] INFO: Cache cleared by user admin
[2025-10-19 15:24:12] INFO: WebP conversion started for image-123.jpg
[2025-10-19 15:24:15] SUCCESS: WebP created: image-123.webp (saved 35%)
[2025-10-19 15:25:01] WARNING: High memory usage detected (85%)
```

---

## 🚀 Test Funzionalità Avanzate

### 1. Critical CSS

```bash
1. Vai su Advanced > Critical CSS
2. Inserisci Critical CSS:
   - Metodo 1: Genera automaticamente (se supportato)
   - Metodo 2: Incolla CSS estratto manualmente
3. Salva
```

**Genera Critical CSS**:

```bash
# Opzione A: Tool online
1. Visita https://www.sitelocity.com/critical-path-css-generator
2. Inserisci URL homepage
3. Copia CSS generato
4. Incolla in FP Performance

# Opzione B: DevTools
1. Apri homepage
2. DevTools > Coverage
3. Reload
4. Identifica CSS above-the-fold
5. Copia e incolla
```

**Verifica Applicazione**:

```html
<head>
    <style id="fp-critical-css">
        /* Critical CSS inline */
        .header{...}
        .hero{...}
    </style>
    <link rel="stylesheet" href="style.css" media="print" onload="this.media='all'">
</head>
```

**Risultato Atteso**: CSS critico inline, CSS non critico caricato in modo asincrono

### 2. CDN Integration

#### Setup CloudFlare

```bash
1. Vai su Advanced > CDN
2. Seleziona provider: CloudFlare
3. Inserisci credenziali:
   - API Key: [tua-api-key]
   - Zone ID: [tuo-zone-id]
4. Abilita "Rewrite URLs"
5. Salva
```

#### Test URL Rewriting

```bash
1. View Source homepage
2. Cerca URL asset
```

**Prima**:

```html
<link href="https://tuosito.com/wp-content/themes/theme/style.css">
<img src="https://tuosito.com/wp-content/uploads/image.jpg">
```

**Dopo**:

```html
<link href="https://cdn.cloudflare.com/tuosito.com/wp-content/themes/theme/style.css">
<img src="https://cdn.cloudflare.com/tuosito.com/wp-content/uploads/image.jpg">
```

#### Test CDN Purge

```bash
1. Vai su Advanced > CDN
2. Clicca "Purge CDN Cache"
3. Seleziona:
   - Purge All
   oppure
   - Purge Specific URLs
4. Conferma
```

**Risultato Atteso**: Cache CDN svuotata, contenuti freschi serviti

### 3. Performance Monitoring

```bash
1. Vai su Advanced > Monitoring
2. Abilita "Performance Monitoring"
3. Configura:
   - Sample Rate: 10% (ogni 10 pagine)
   - Track: Page Load, Queries, Memory
4. Salva
```

**Visualizza Metriche**:

```bash
1. Vai su Monitoring > Dashboard
2. Visualizza grafici:
   - Page Load Time (ultimi 7 giorni)
   - Database Queries per page
   - Memory Usage
   - Cache Hit Rate
```

**Esempio Metriche**:

```
Media ultimi 7 giorni:
- Page Load: 1.2s → 0.6s (migliorato del 50%)
- Query/page: 45 → 28 (ridotte del 38%)
- Memory: 35 MB → 28 MB
- Cache Hit: 85%
```

### 4. Scheduled Reports

```bash
1. Vai su Advanced > Reports
2. Abilita "Email Reports"
3. Configura:
   - Email: admin@tuosito.com
   - Frequenza: Weekly
   - Giorno: Monday
   - Ora: 09:00
   - Sezioni: [seleziona tutte]
4. Salva
5. Clicca "Send Test Report"
```

**Email Report Attesa**:

```
Subject: FP Performance Suite - Weekly Report

🎯 Performance Score: 87/100

📊 Metriche Settimana Scorsa:
- Page Load: 0.8s (↓ 15%)
- Cache Hit Rate: 82%
- WebP Coverage: 95%
- Database Size: 45 MB

✅ Successi:
- Convertite 45 nuove immagini WebP
- Liberati 2.3 MB database
- Ridotto tempo caricamento 15%

⚠️ Raccomandazioni:
- Considera abilitare Lazy Loading
- Ottimizza query più lente
```

### 5. WordPress Site Health Integration

```bash
1. Vai su Tools > Site Health
2. Clicca tab "Info"
3. Cerca sezione "FP Performance Suite"
```

**Info Visualizzate**:

```
FP Performance Suite
├── Version: 1.2.0
├── Cache Status: Enabled (85% hit rate)
├── WebP Conversion: 198/200 images (99%)
├── Database Health: Good (4 MB overhead)
└── Performance Score: 87/100
```

```bash
4. Clicca tab "Tests"
5. Cerca test plugin:
   - ✅ Page Cache Active
   - ✅ WebP Conversion High Coverage
   - ⚠️ Database Needs Optimization
   - ✅ Asset Optimization Enabled
```

---

## ⚡ Test Funzionalità PageSpeed (v1.2.0)

### 1. Lazy Loading

```bash
1. Vai su PageSpeed > Lazy Loading
2. Abilita:
   - ✅ Enable Lazy Load
   - ✅ Lazy Load Images
   - ✅ Lazy Load Iframes
3. Configura esclusioni:
   - Logo: .site-logo
   - Hero images: .hero-image
   - Above the fold: .first-screen
4. Salva
```

**Verifica HTML**:

```html
<!-- Immagini sotto la fold -->
<img src="image.jpg" loading="lazy" alt="...">

<!-- Iframe (YouTube, ecc.) -->
<iframe src="..." loading="lazy"></iframe>

<!-- Escluse (logo) -->
<img src="logo.png" class="site-logo" alt="...">
```

**Test Pratico**:

```bash
1. Apri homepage con DevTools
2. Network > Throttling: Slow 3G
3. Scorri pagina lentamente
4. Nota immagini caricate solo quando visibili
```

**Risultato Atteso**: Caricamento iniziale più veloce, immagini caricate on-demand

### 2. Font Optimization

```bash
1. Vai su PageSpeed > Fonts
2. Abilita "Font Display Swap"
3. Aggiungi font da precaricare:
   - /fonts/main-font.woff2
   - https://fonts.gstatic.com/s/roboto/v30/...
4. Abilita "Preconnect to Font Providers"
5. Salva
```

**Verifica HTML**:

```html
<head>
    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Font Display Swap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap">
    
    <!-- Preload Critical Fonts -->
    <link rel="preload" href="/fonts/main-font.woff2" as="font" type="font/woff2" crossorigin>
</head>
```

**Test Visual**:

```bash
1. Apri sito con Network throttling
2. Nota testo visibile immediatamente (fallback font)
3. Font personalizzato caricato senza flash
```

**Risultato Atteso**: Nessun FOIT (Flash of Invisible Text), swap fluido

### 3. Image Optimizer (Dimensions)

```bash
1. Vai su PageSpeed > Images
2. Abilita:
   - ✅ Add Width/Height Attributes
   - ✅ Aspect Ratio CSS
3. Salva
```

**Verifica HTML**:

```html
<!-- Prima -->
<img src="image.jpg" alt="...">

<!-- Dopo -->
<img src="image.jpg" width="800" height="600" alt="..." style="aspect-ratio: 800/600;">
```

**Test CLS (Cumulative Layout Shift)**:

```bash
1. Apri Chrome DevTools
2. Lighthouse > Performance
3. Run audit
4. Controlla metric "CLS"
```

**Risultato Atteso**: CLS ridotto significativamente (< 0.1), layout stabile

### 4. Async CSS Loading

```bash
1. Vai su PageSpeed > CSS
2. Abilita "Async CSS Loading"
3. Configura whitelist (CSS critici da NON rendere async):
   - main-style.css
   - critical.css
4. Salva
```

**Verifica HTML**:

```html
<!-- CSS Critico: caricamento normale -->
<link rel="stylesheet" href="critical.css">

<!-- CSS Non-Critico: caricamento async -->
<link rel="stylesheet" href="secondary.css" media="print" onload="this.media='all'">
<noscript><link rel="stylesheet" href="secondary.css"></noscript>
```

**Test PageSpeed**:

```bash
1. Vai su PageSpeed Insights
2. Testa URL
3. Controlla "Eliminate render-blocking resources"
```

**Risultato Atteso**: Avviso eliminato o ridotto significativamente

### 5. Preconnect Support

```bash
1. Vai su PageSpeed > Preconnect
2. Aggiungi domini:
   - fonts.googleapis.com (crossorigin)
   - fonts.gstatic.com (crossorigin)
   - analytics.google.com
   - youtube.com
3. Salva
```

**Verifica Headers**:

```html
<link rel="preconnect" href="//fonts.googleapis.com">
<link rel="preconnect" href="//fonts.gstatic.com" crossorigin>
<link rel="dns-prefetch" href="//analytics.google.com">
```

**Test Performance**:

```bash
1. DevTools > Network
2. Filtra per domini esterni
3. Nota riduzione tempo TTFB per risorse esterne
```

**Risultato Atteso**: Connessioni stabilite in anticipo, caricamento più veloce

### 6. Test Completo PageSpeed

```bash
1. Esegui test PageSpeed Insights PRIMA delle ottimizzazioni
2. Nota score mobile/desktop
3. Abilita tutte le ottimizzazioni PageSpeed
4. Svuota cache
5. Esegui nuovo test PageSpeed
6. Confronta risultati
```

**Miglioramenti Attesi**:

```
Mobile Score:
- Prima: 65/100
- Dopo: 90/100
- Miglioramento: +25 punti

Desktop Score:
- Prima: 85/100
- Dopo: 98/100
- Miglioramento: +13 punti

Metriche:
- FCP: 2.1s → 1.0s
- LCP: 3.8s → 1.8s
- CLS: 0.25 → 0.05
- TBT: 450ms → 150ms
```

---

## 🎬 Scenari d'Uso Reali

### Scenario 1: Nuovo Sito WordPress

**Obiettivo**: Ottimizzare un sito WordPress appena installato

```bash
# Step 1: Installazione e Attivazione
1. Installa FP Performance Suite
2. Attiva plugin
3. Vai su Dashboard

# Step 2: Configurazione Base
1. Applica Preset "General" o specifico hosting
2. Vai su Cache > Abilita Page Cache
3. Vai su Assets > Abilita minificazione CSS/JS
4. Vai su PageSpeed > Abilita Lazy Loading
5. Vai su PageSpeed > Abilita Font Optimization

# Step 3: Test Iniziale
1. Esegui PageSpeed Insights test
2. Nota score baseline

# Step 4: Ottimizzazioni Graduali
1. Converti immagini esistenti a WebP
2. Abilita browser cache headers
3. Configura Critical CSS
4. Abilita CDN (se disponibile)

# Step 5: Monitoraggio
1. Attiva Performance Monitoring
2. Configura report settimanali
3. Monitora metriche per 1 settimana

# Risultato Atteso
- Score PageSpeed: 85-95
- Page Load: < 2s
- Requests: ridotte del 40%
```

### Scenario 2: Sito Esistente con Problemi Performance

**Problema**: Sito lento (4-5s caricamento), molte immagini, database grande

```bash
# Fase 1: Diagnosi
1. Dashboard > Verifica Performance Score (probabilmente 40-60)
2. Identifica aree critiche

# Fase 2: Quick Wins
1. Database > Run Cleanup (dry-run prima)
2. Media > Bulk Convert to WebP
3. Cache > Abilita Page Cache
4. Assets > Abilita Minify

# Fase 3: Ottimizzazioni Avanzate
1. PageSpeed > Abilita tutte le ottimizzazioni
2. Advanced > Configura Critical CSS
3. Assets > Defer JavaScript
4. Database > Schedule Weekly Cleanup

# Fase 4: Verifica
1. Esegui PageSpeed test
2. Confronta con baseline
3. Monitora metriche

# Risultato Atteso
- Caricamento: 4.5s → 1.5s (67% più veloce)
- Database: ridotto del 30%
- Immagini: risparmio 40-60%
- Score: 45 → 85
```

### Scenario 3: E-commerce WooCommerce

**Specifiche**: Molti prodotti, immagini, alto traffico

```bash
# Configurazione Specifica
1. Cache > Escludi pagine dinamiche:
   - /carrello
   - /checkout
   - /account
   - /?add-to-cart=*

2. WebP > Converti immagini prodotti
   - Focus su immagini grandi
   - Usa qualità 85 per prodotti

3. Assets > Heartbeat Control
   - Frontend: Disable
   - Dashboard: Slow
   - Checkout: Default

4. Database > Cleanup Focus
   - Transient (molto importante per WooCommerce)
   - Sessioni carrello scadute
   - Order notes vecchi

5. CDN > Abilita per asset statici
   - Immagini prodotti
   - CSS/JS

6. PageSpeed > Lazy Loading
   - Immagini prodotti
   - Esclusione: immagine principale prodotto

# Monitoraggio Critico
- Cache hit rate su pagine prodotto
- Tempo caricamento checkout
- Database queries su pagine prodotto

# KPI Target
- Pagina prodotto: < 2s
- Checkout: < 1.5s
- Cache hit: > 80%
- Score mobile: > 75
```

### Scenario 4: Blog con Molto Traffico

**Caratteristiche**: Molti post, commenti, aggiornamenti frequenti

```bash
# Strategia Cache Aggressiva
1. Cache > Page Cache
   - TTL lungo: 7200s (2 ore)
   - Cache warmup dopo pubblicazione
   
2. Browser Cache
   - Immagini: 1 anno
   - CSS/JS: 6 mesi
   
3. Object Cache
   - Abilita Redis/Memcached se disponibile

# Gestione Contenuti
1. Database > Auto Cleanup
   - Weekly schedule
   - Focus su revisioni (molti post)
   - Commenti spam
   
2. WebP > Conversione Immagini
   - Bulk convert post esistenti
   - Auto-convert nuovi upload
   
3. Assets > Ottimizzazioni
   - Minify tutto
   - Defer JS
   - Combine CSS/JS

# Gestione Traffico Alto
1. Rate Limiting
   - Limita conversioni WebP simultanee
   - Limita cleanup database simultanei
   
2. Performance Monitoring
   - Sample rate alto (25%)
   - Alert su metriche critiche
   
3. Scheduled Tasks
   - Cache warm notturno
   - Cleanup fuori orari di punta

# Target
- TTFB: < 200ms (con cache)
- Cache hit: > 90%
- Page load: < 1.5s
```

---

## 🔧 Risoluzione Problemi

### Problema 1: Cache Non Funziona

**Sintomi**:
- Header X-FP-Cache sempre MISS
- Nessun file in directory cache

**Diagnosi**:

```bash
1. Verifica permessi directory
   - /wp-content/cache/ deve essere 755
   - File cache devono essere 644
   
2. Controlla esclusioni
   - URL non escluso per sbaglio
   
3. Verifica conflitti plugin
   - Disattiva altri plugin cache temporaneamente
   
4. Controlla wp-config.php
   - DONOTCACHEPAGE non settato
```

**Soluzione**:

```bash
# Via FTP/SSH
cd /wp-content
mkdir -p cache/fp-performance
chmod 755 cache
chmod 755 cache/fp-performance

# WordPress Admin
1. Vai su Cache > Settings
2. Clicca "Clear Cache"
3. Clicca "Rebuild Cache"
4. Test homepage
```

### Problema 2: Sito Rotto dopo Minificazione

**Sintomi**:
- JavaScript errors in console
- Layout rotto
- Funzionalità non funzionanti

**Diagnosi**:

```bash
1. Apri DevTools > Console
2. Identifica script con errori
3. Nota script specifico
```

**Soluzione**:

```bash
1. Vai su Assets > Minification
2. Aggiungi script problematico alle esclusioni:
   - problematic-script.js
3. Svuota cache
4. Testa di nuovo

# Se persiste
1. Disabilita "Combine JS"
2. Prova solo "Minify JS"
3. Test incrementale
```

### Problema 3: Immagini Non Convertono a WebP

**Sintomi**:
- Errore durante conversione
- Nessun file .webp creato

**Diagnosi**:

```bash
1. Vai su Media > WebP > Diagnostics
2. Controlla:
   - Supporto GD: [Yes/No]
   - Supporto Imagick: [Yes/No]
   - Memoria disponibile: [XXX MB]
```

**Possibili Cause**:

```bash
# Causa 1: GD senza supporto WebP
Soluzione: Contatta hosting per abilitare

# Causa 2: Memoria insufficiente
Soluzione: 
- Riduci qualità WebP (es. 75)
- Elabora in batch più piccoli
- Aumenta memory_limit in wp-config

# Causa 3: Permessi file
Soluzione:
chmod 755 /wp-content/uploads/
chmod 644 /wp-content/uploads/*.jpg
```

### Problema 4: Database Cleanup Troppo Lento

**Sintomi**:
- Processo si blocca
- Timeout

**Soluzione**:

```bash
1. Vai su Database > Cleanup
2. Riduci scope:
   - Seleziona solo 1-2 operazioni per volta
   - Usa filtri per limitare quantità
   
3. Incrementa timeout:
   - Aggiungi in wp-config.php:
   
   set_time_limit(300); // 5 minuti
   
4. Esegui operazioni in orari di basso traffico

5. Usa WP-CLI per grandi cleanup:
   wp fp-performance db cleanup --dry-run
```

### Problema 5: Performance Score Basso

**Score < 70 nonostante ottimizzazioni**

**Checklist**:

```bash
✅ Cache Abilitata?
   - Vai su Cache > Verifica Enabled
   
✅ WebP Convertite?
   - Vai su Media > Verifica Coverage > 80%
   
✅ Minificazione Attiva?
   - Vai su Assets > Verifica Minify CSS/JS
   
✅ Lazy Loading Attivo?
   - Vai su PageSpeed > Verifica Enabled
   
✅ Database Ottimizzato?
   - Vai su Database > Verifica Overhead < 5MB
   
✅ Hosting Performance?
   - Test TTFB server < 500ms
   
✅ Tema Ottimizzato?
   - Considera tema più leggero
   
✅ Plugin Pesanti?
   - Disattiva plugin non essenziali
   - Usa P3 Profiler per identificare
```

---

## 📊 Checklist Verifica Completa

### ✅ Pre-Produzione

```bash
□ Plugin attivato senza errori
□ Tutte le pagine admin accessibili
□ Nessun conflitto con altri plugin
□ Performance Score calcolato
□ Test PageSpeed baseline eseguito
```

### ✅ Configurazione Base

```bash
□ Preset hosting applicato
□ Page cache abilitata e funzionante
□ Browser cache headers configurati
□ Minificazione CSS/JS attiva
□ WebP conversion configurata
```

### ✅ Ottimizzazioni Avanzate

```bash
□ Lazy loading immagini attivo
□ Font optimization configurato
□ Critical CSS implementato (opzionale)
□ CDN configurato (se disponibile)
□ Defer JavaScript attivo
□ Database cleanup schedulato
```

### ✅ Monitoraggio

```bash
□ Performance monitoring attivo
□ Report email configurati
□ Log viewer funzionante
□ Backup wp-config creato
```

### ✅ Test Funzionali

```bash
□ Navigazione sito funzionante
□ Form funzionanti
□ Login/Logout funzionante
□ Checkout funzionante (se e-commerce)
□ Ricerca funzionante
□ Menu responsive funzionante
```

### ✅ Test Performance

```bash
□ PageSpeed score migliorato
□ GTmetrix Grade A/B
□ WebPageTest < 2s Load Time
□ Lighthouse Performance > 85
□ Cache hit rate > 75%
```

### ✅ Documentazione

```bash
□ Configurazioni documentate
□ Baseline metriche salvate
□ Esclusioni documentate
□ Credenziali CDN salvate
□ Schedule backup salvato
```

---

## 📈 Metriche di Successo

### Prima vs Dopo

```bash
=== BASELINE (Prima Ottimizzazioni) ===
PageSpeed Mobile: 55
PageSpeed Desktop: 75
Load Time: 4.2s
Requests: 85
Page Size: 3.2 MB
TTFB: 800ms

=== TARGET (Dopo Ottimizzazioni) ===
PageSpeed Mobile: 85+ ✅
PageSpeed Desktop: 95+ ✅
Load Time: < 2s ✅
Requests: < 40 ✅
Page Size: < 1.5 MB ✅
TTFB: < 300ms ✅
```

---

## 🎯 Conclusioni

Seguendo questa guida, un amministratore WordPress può:

1. ✅ **Testare** tutte le funzionalità del plugin
2. ✅ **Verificare** la corretta applicazione delle ottimizzazioni
3. ✅ **Misurare** i miglioramenti di performance
4. ✅ **Risolvere** problemi comuni
5. ✅ **Ottimizzare** in modo sistematico

### Supporto

Per ulteriore assistenza:
- 📧 Email: info@francescopasseri.com
- 🌐 Web: https://francescopasseri.com
- 📚 Docs: /docs/

---

**Versione Guida**: 1.0  
**Data**: 19 Ottobre 2025  
**Plugin Version**: FP Performance Suite v1.2.0

