# 🚀 Nuovi Servizi Performance Implementati v1.3.0

Documentazione completa dei nuovi servizi per il miglioramento delle performance implementati nel plugin **FP Performance Suite**.

## 📋 Indice

1. [Object Cache Manager (Redis/Memcached)](#1-object-cache-manager)
2. [AVIF Image Converter](#2-avif-image-converter)
3. [HTTP/2 Server Push](#3-http2-server-push)
4. [Critical CSS Automation](#4-critical-css-automation)
5. [Edge Cache Integrations](#5-edge-cache-integrations)
6. [Database Query Cache](#6-database-query-cache)
7. [Third-Party Script Manager](#7-third-party-script-manager)
8. [Service Worker Manager (PWA)](#8-service-worker-manager)
9. [Core Web Vitals Monitor](#9-core-web-vitals-monitor)
10. [Predictive Prefetching](#10-predictive-prefetching)
11. [Smart Asset Delivery](#11-smart-asset-delivery)

---

## 1. Object Cache Manager

**File:** `src/Services/Cache/ObjectCacheManager.php`

### Descrizione
Gestisce la cache degli oggetti persistente con supporto per Redis e Memcached, riducendo drasticamente il carico sul database.

### Caratteristiche
- ✅ Supporto Redis
- ✅ Supporto Memcached
- ✅ Auto-detection del driver disponibile
- ✅ Connessione con autenticazione
- ✅ Gestione prefissi per multi-site
- ✅ Drop-in file automatico per WordPress
- ✅ Statistiche cache (hits, misses, size)
- ✅ Test connessione con diagnostica

### Configurazione

```php
// Esempio settings
$settings = [
    'enabled' => true,
    'driver' => 'redis', // 'redis', 'memcached', 'auto'
    'host' => '127.0.0.1',
    'port' => 6379,
    'database' => 0,
    'password' => '',
    'prefix' => 'fp_ps_',
    'timeout' => 1,
];
```

### Come funziona REDIS/MEMCACHED
- **Redis**: Utilizza l'estensione PHP Redis per connettersi a un server Redis
- **Memcached**: Utilizza l'estensione PHP Memcached
- Il servizio installa automaticamente un `object-cache.php` drop-in nella directory `wp-content`

---

## 2. AVIF Image Converter

**File:** `src/Services/Media/AVIFConverter.php`

### Descrizione
Converte automaticamente le immagini in formato AVIF, che offre compressione superiore del 30-50% rispetto a WebP.

### Come funziona AVIF

**AVIF** (AV1 Image File Format) è un formato immagine moderno basato sul codec video AV1. È supportato da:

- **PHP 8.1+** con GD (funzione `imageavif()`)
- **Imagick** con supporto AVIF compilato

### Conversione AVIF in PHP

```php
// Con GD (PHP 8.1+)
$image = imagecreatefromjpeg('photo.jpg');
imageavif($image, 'photo.jpg.avif', 75); // Quality: 0-100 (invertito!)
imagedestroy($image);

// Con Imagick
$imagick = new Imagick('photo.jpg');
$imagick->setImageFormat('avif');
$imagick->setImageCompressionQuality(75);
$imagick->writeImage('photo.jpg.avif');
```

### Caratteristiche
- ✅ Auto-detect GD o Imagick
- ✅ Conversione automatica su upload
- ✅ Supporto thumbnails
- ✅ Delivery automatico ai browser compatibili
- ✅ Fallback a immagini originali
- ✅ Controllo qualità adattivo
- ✅ Strip metadata opzionale

### Vantaggi AVIF
- 📉 **30-50% più piccolo** di WebP
- 🎨 **Migliore qualità** a parità di dimensione
- 🌈 **HDR support**
- 🔍 **Alpha transparency**

---

## 3. HTTP/2 Server Push

**File:** `src/Services/Assets/Http2ServerPush.php`

### Descrizione
Invia proattivamente risorse critiche tramite HTTP/2 Server Push, riducendo il tempo di caricamento iniziale.

### Caratteristiche
- ✅ Push automatico CSS critici
- ✅ Push automatico JavaScript essenziali
- ✅ Push automatico font web
- ✅ Limitazione numero risorse
- ✅ Filtraggio risorse critiche
- ✅ Header `Link: rel=preload`
- ✅ Detection HTTP/2

### Funzionamento

```http
HTTP/2 200
Link: </style.css>; rel=preload; as=style
Link: </script.js>; rel=preload; as=script
Link: </font.woff2>; rel=preload; as=font; crossorigin
```

Il server **invia questi file prima ancora** che il browser li richieda!

---

## 4. Critical CSS Automation

**File:** `src/Services/Assets/CriticalCssAutomation.php`

### Descrizione
Estrae automaticamente il CSS critico per il contenuto above-the-fold e lo inline nel `<head>`.

### Caratteristiche
- ✅ Estrazione automatica CSS critico
- ✅ Supporto multipli viewport (mobile/desktop)
- ✅ Integrazione con CriticalCSS.com API
- ✅ Estrazione interna (senza API esterne)
- ✅ Cache per page type
- ✅ Rigenerazione automatica
- ✅ Minificazione CSS

### Metodi di estrazione
1. **Interno**: Analisi HTML e CSS direttamente in PHP
2. **CriticalCSS.com API**: Servizio esterno (richiede API key)
3. **Personalizzato**: Via filtri WordPress

---

## 5. Edge Cache Integrations

**File:** `src/Services/Cache/EdgeCacheManager.php`

### Descrizione
Integrazione diretta con CDN/WAF provider per gestione cache edge.

### Provider Supportati

#### 5.1 Cloudflare
**File:** `src/Services/Cache/EdgeCache/CloudflareProvider.php`

```php
// Configurazione
$settings = [
    'api_token' => 'your-token',
    'zone_id' => 'your-zone-id',
    'email' => 'optional@email.com',
];
```

**Funzionalità:**
- Purge completo
- Purge per URL (max 30 alla volta)
- Purge per tag
- Development mode
- Statistiche analytics

#### 5.2 Fastly
**File:** `src/Services/Cache/EdgeCache/FastlyProvider.php`

```php
$settings = [
    'api_key' => 'your-api-key',
    'service_id' => 'your-service-id',
];
```

**Funzionalità:**
- Purge completo
- Purge per URL (PURGE method)
- Soft purge per tag
- Statistiche requests/bandwidth

#### 5.3 AWS CloudFront
**File:** `src/Services/Cache/EdgeCache/CloudFrontProvider.php`

```php
$settings = [
    'access_key_id' => 'AWS_KEY',
    'secret_access_key' => 'AWS_SECRET',
    'distribution_id' => 'DISTRIBUTION_ID',
    'region' => 'us-east-1',
];
```

**Funzionalità:**
- Invalidation automatica
- AWS Signature v4
- Max 3000 paths per invalidation

### Auto-Purge
Il servizio purga automaticamente la cache quando:
- Un post viene salvato/pubblicato
- Un post viene cancellato
- Il tema viene cambiato
- Un plugin viene attivato

---

## 6. Database Query Cache

**File:** `src/Services/DB/QueryCacheManager.php`

### Descrizione
Cache persistente per query database costose.

### Caratteristiche
- ✅ Cache automatica SELECT queries
- ✅ Configurazione TTL
- ✅ Esclusioni pattern
- ✅ Limite dimensione cache
- ✅ Statistiche hit/miss rate
- ✅ FIFO eviction policy

### Configurazione

```php
$settings = [
    'enabled' => true,
    'ttl' => 3600, // 1 ora
    'max_size' => 1000, // Max query cachate
    'exclude_patterns' => [
        'wp_options',
        'wp_postmeta',
    ],
    'cache_selects_only' => true,
];
```

---

## 7. Third-Party Script Manager

**File:** `src/Services/Assets/ThirdPartyScriptManager.php`

### Descrizione
Gestisce il caricamento ritardato di script di terze parti (analytics, social, ads).

### Script Supportati (39 totali)

#### Analytics & Tracking
- 🔍 Google Analytics / GTM
- 📊 Google Ads
- 🔥 Hotjar
- 🔍 Microsoft Clarity
- 📊 Segment
- 📈 Mixpanel
- 🎬 FullStory

#### Social Media Pixels
- 📘 Facebook Pixel
- 💼 LinkedIn Insight Tag
- 🐦 Twitter/X Pixel
- 🎵 TikTok Pixel
- 📌 Pinterest Tag
- 👻 Snapchat Pixel

#### Live Chat & Support
- 💬 Intercom
- 🎧 Zendesk
- 💬 Drift
- 💬 Crisp
- 💬 Tidio
- 💬 Tawk.to
- 💬 LiveChat
- 🧡 HubSpot

#### E-commerce & Reviews
- ⭐ Trustpilot
- 📧 Klaviyo
- 📧 Mailchimp
- 📊 ActiveCampaign

#### Payments
- 💳 Stripe
- 💳 PayPal
- 💳 Klarna

#### Testing & Optimization
- 🧪 Optimizely

#### Compliance & Privacy
- 🍪 OneTrust
- 🤖 reCAPTCHA

#### Scheduling & Forms
- 📅 Calendly
- 📋 Typeform

#### Media & Embeds
- 📺 YouTube
- ▶️ Vimeo
- 🎵 SoundCloud
- 🎵 Spotify
- 🗺️ Google Maps

#### Accessibility
- ♿ UserWay

### Strategie di Caricamento
1. **On Interaction**: Carica al primo scroll/click/hover
2. **On Scroll**: Carica al primo scroll
3. **Timeout**: Carica dopo X millisecondi

### Funzionamento

```javascript
// Prima: <script src="analytics.js"></script>
// Dopo:  <script data-fp-delayed="true" 
//                data-fp-delayed-src="analytics.js" 
//                type="text/plain"></script>

// Il loader li attiva al momento giusto!
```

### Benefici
- ⚡ **Riduce JavaScript bloccante** all'avvio
- 📈 **Migliora FCP e LCP** 
- 🎯 **Carica solo se necessario**

---

## 8. Service Worker Manager (PWA)

**File:** `src/Services/PWA/ServiceWorkerManager.php`

### Descrizione
Genera e gestisce Service Worker per funzionalità Progressive Web App.

### Caratteristiche
- ✅ Service Worker auto-generato
- ✅ Manifest.json auto-generato
- ✅ Strategie cache configurabili
- ✅ Offline support
- ✅ Precache assets critici
- ✅ Cache size management
- ✅ Auto-update service worker

### Strategie Cache

```php
$settings = [
    'cache_strategy' => 'network_first', // Opzioni:
    // - cache_first: Cache → Network
    // - network_first: Network → Cache
    // - stale_while_revalidate: Cache + Background update
];
```

### Generazione Service Worker

Il servizio genera automaticamente `/fp-sw.js`:

```javascript
// Precache
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME).then(cache => 
            cache.addAll(PRECACHE_URLS)
        )
    );
});

// Fetch con strategia configurabile
self.addEventListener('fetch', event => {
    event.respondWith(
        handleFetch(event.request)
    );
});
```

---

## 9. Core Web Vitals Monitor

**File:** `src/Services/Monitoring/CoreWebVitalsMonitor.php`

### Descrizione
Monitoraggio Real User Monitoring (RUM) per Core Web Vitals.

### Metriche Tracciate

- **LCP** (Largest Contentful Paint) - Caricamento
- **FID** (First Input Delay) - Interattività  
- **CLS** (Cumulative Layout Shift) - Stabilità visiva
- **FCP** (First Contentful Paint) - Rendering iniziale
- **TTFB** (Time to First Byte) - Risposta server
- **INP** (Interaction to Next Paint) - Responsività

### Caratteristiche
- ✅ Raccolta client-side via PerformanceObserver API
- ✅ Sample rate configurabile
- ✅ Rating automatico (good/needs-improvement/poor)
- ✅ Alert email su threshold superati
- ✅ Statistiche aggregate (p75, p90, median)
- ✅ Retention configurabile
- ✅ Invio a Google Analytics opzionale

### Funzionamento

```javascript
// Client-side
new PerformanceObserver(function(list) {
    var lcp = list.getEntries()[0];
    sendMetric('LCP', lcp.renderTime);
}).observe({ type: 'largest-contentful-paint' });

// Invia al server via sendBeacon
navigator.sendBeacon('/wp-json/fp-ps/v1/metrics/cwv', data);
```

### Soglie Alert

```php
$settings = [
    'alert_threshold_lcp' => 2500,  // ms (poor > 4000)
    'alert_threshold_fid' => 100,   // ms (poor > 300)
    'alert_threshold_cls' => 0.1,   // unitless (poor > 0.25)
];
```

---

## 10. Predictive Prefetching

**File:** `src/Services/Assets/PredictivePrefetching.php`

### Descrizione
Prefetch intelligente delle pagine probabilmente visitate dopo.

### Strategie

#### 10.1 Hover Prefetch
Prefetch quando l'utente passa il mouse su un link.

```javascript
link.addEventListener('mouseover', () => {
    setTimeout(() => {
        prefetch(link.href);
    }, 100); // Delay configurabile
});
```

#### 10.2 Viewport Prefetch
Prefetch dei link visibili nel viewport.

```javascript
new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            prefetch(entry.target.href);
        }
    });
});
```

#### 10.3 Mouse Tracking (Sperimentale)
Predice la direzione del mouse e prefetcha il link verso cui si sta dirigendo.

```javascript
// Calcola velocità mouse
mouseVelocity = (currentPos - lastPos) / deltaTime;

// Trova link nella direzione del movimento
predictedLink = findLinkInDirection(mouseVelocity);
prefetch(predictedLink.href);
```

### Configurazione

```php
$settings = [
    'strategy' => 'hover', // hover, visible, viewport, mouse-tracking
    'delay' => 100, // ms
    'max_prefetch' => 3, // Max simultanei
    'exclude_patterns' => ['/wp-admin/', '.pdf'],
];
```

---

## 11. Smart Asset Delivery

**File:** `src/Services/Assets/SmartAssetDelivery.php`

### Descrizione
Delivery adattivo di asset basato sulla velocità di connessione dell'utente.

### Caratteristiche
- ✅ Detection tipo connessione (2G, 3G, 4G)
- ✅ Save-Data mode support
- ✅ Qualità immagini adattiva
- ✅ Client Hints headers
- ✅ Network Information API
- ✅ Lazy loading automatico su connessioni lente

### Funzionamento

#### Detection Connessione

```javascript
// JavaScript API
navigator.connection.effectiveType // 'slow-2g', '2g', '3g', '4g'
navigator.connection.downlink // Mbps
navigator.connection.saveData // boolean

// Server-side (HTTP Headers)
$_SERVER['HTTP_ECT'] // effective connection type
$_SERVER['HTTP_SAVE_DATA'] // '1' se attivo
```

#### Qualità Adattiva

```php
$settings = [
    'quality_slow' => 50,      // 2G: 50% quality
    'quality_moderate' => 70,  // 3G: 70% quality  
    'quality_fast' => 85,      // 4G: 85% quality
];

// Detection automatica
$quality = $service->getRecommendedQuality(); // Ritorna 50/70/85
```

#### Client Hints

```http
Accept-CH: ECT, Save-Data, Downlink, RTT, Viewport-Width
Accept-CH-Lifetime: 86400
```

Il browser invia questi header nelle richieste successive:

```http
GET /image.jpg
ECT: 4g
Downlink: 10.0
RTT: 50
Save-Data: 0
```

---

## 🎯 Riepilogo Benefici

| Servizio | Impatto Performance | Difficoltà |
|----------|-------------------|------------|
| Object Cache | ⭐⭐⭐⭐⭐ | Media |
| AVIF Converter | ⭐⭐⭐⭐ | Bassa |
| HTTP/2 Push | ⭐⭐⭐⭐ | Media |
| Critical CSS | ⭐⭐⭐⭐⭐ | Alta |
| Edge Cache | ⭐⭐⭐⭐⭐ | Media |
| Query Cache | ⭐⭐⭐ | Bassa |
| Script Manager | ⭐⭐⭐⭐ | Bassa |
| Service Worker | ⭐⭐⭐⭐ | Alta |
| Web Vitals Monitor | ⭐⭐⭐ | Media |
| Prefetching | ⭐⭐⭐ | Media |
| Smart Delivery | ⭐⭐⭐⭐ | Media |

---

## 📚 Utilizzo

### Esempio Completo

```php
// Nel tema o plugin
add_action('init', function() {
    $container = \FP\PerfSuite\Plugin::container();
    
    // Configura AVIF
    $avif = $container->get(\FP\PerfSuite\Services\Media\AVIFConverter::class);
    $avif->update([
        'enabled' => true,
        'quality' => 75,
        'auto_deliver' => true,
    ]);
    
    // Configura Core Web Vitals
    $cwv = $container->get(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class);
    $cwv->update([
        'enabled' => true,
        'sample_rate' => 0.5, // 50% utenti
        'track_lcp' => true,
        'track_fid' => true,
        'track_cls' => true,
    ]);
    
    // Configura Edge Cache (Cloudflare)
    $edge = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
    $edge->update([
        'enabled' => true,
        'provider' => 'cloudflare',
        'cloudflare' => [
            'api_token' => 'YOUR_TOKEN',
            'zone_id' => 'YOUR_ZONE_ID',
        ],
        'auto_purge' => true,
    ]);
});
```

---

## 🔧 Requisiti Tecnici

### PHP
- **Versione minima**: PHP 8.0
- **AVIF Support**: PHP 8.1+ (per GD) o Imagick con AVIF
- **Estensioni opzionali**:
  - `redis` (per Object Cache)
  - `memcached` (per Object Cache)
  - `imagick` (per AVIF/WebP avanzato)

### Server
- **HTTP/2**: Richiesto per Server Push
- **Mod_rewrite**: Raccomandato per cache rules

### Browser Support
- **Service Workers**: Chrome 40+, Firefox 44+, Safari 11.1+
- **AVIF**: Chrome 85+, Firefox 93+, Safari 16.0+
- **Network Information API**: Chrome, Edge (limitato su Safari/Firefox)

---

## 🚀 Prossimi Sviluppi

Servizi pianificati per v1.4.0:

- [ ] **ML-based Image Optimization** - Compressione adattiva via machine learning
- [ ] **GraphQL API** - API GraphQL per metriche
- [ ] **WebAssembly Modules** - Ottimizzazioni WASM
- [ ] **Advanced Lazy Loading** - Lazy loading video/iframe/background
- [ ] **Resource Bundling** - Bundle ottimizzato automatico

---

## 📖 Documentazione API

Ogni servizio espone metodi standard:

```php
interface PerformanceService {
    public function register(): void;
    public function settings(): array;
    public function update(array $settings): void;
    public function status(): array;
}
```

### Hooks & Filters

Tutti i servizi offrono hook WordPress per personalizzazione:

```php
// Esempio: Modificare risorse HTTP/2 Push
add_filter('fp_ps_http2_push_resources', function($resources) {
    $resources[] = [
        'url' => '/custom-font.woff2',
        'as' => 'font',
        'type' => 'font/woff2',
    ];
    return $resources;
});

// Esempio: Escludere URL da prefetch
add_filter('fp_ps_edge_cache_purge_urls', function($urls, $post_id) {
    // Aggiungi URL custom da purgare
    $urls[] = '/custom-archive/';
    return $urls;
}, 10, 2);
```

---

## 📝 Note Finali

Tutti i servizi sono stati implementati seguendo le best practice:

- ✅ **PSR-4 Autoloading**
- ✅ **Dependency Injection**
- ✅ **WordPress Coding Standards**
- ✅ **Logging con Logger utility**
- ✅ **Gestione errori graceful**
- ✅ **Backward compatibility**

---

**Autore**: Francesco Passeri  
**Versione Plugin**: 1.3.0  
**Data**: 2025-10-15  
**Licenza**: GPL v2 or later

---

Per domande o supporto, consultare la documentazione completa o aprire un issue su GitHub.
