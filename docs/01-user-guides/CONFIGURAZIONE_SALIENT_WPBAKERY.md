# 🎨 Configurazione Ottimale per Salient + WPBakery

Guida specifica per utilizzare FP Performance Suite v1.3.0 con tema Salient e WPBakery Page Builder.

---

## ⚠️ Considerazioni Importanti

**Salient** è un tema ricco di funzionalità ma potenzialmente pesante:
- Molti script JavaScript (sliders, animazioni, parallax)
- CSS personalizzati per ogni elemento
- Font icons (Font Awesome, Linearicons, etc.)
- Google Fonts multipli
- Shortcode complessi

**WPBakery** aggiunge:
- Script del builder frontend/backend
- CSS inline per ogni elemento
- Rendering shortcode complesso
- Asset dinamici per ogni componente

---

## 🎯 Configurazione Raccomandata per Servizio

### 1. ❌ Object Cache (Redis/Memcached)

**Raccomandazione:** ✅ **ATTIVA - Alta priorità**

```php
// Configurazione ottimale
$settings = [
    'enabled' => true,
    'driver' => 'redis', // Se disponibile sul server
    'host' => '127.0.0.1',
    'port' => 6379,
    'database' => 0,
    'prefix' => 'salient_',
    'timeout' => 2,
];
```

**Perché è importante:**
- Salient fa molte query per opzioni tema
- WPBakery carica template e shortcode dal database
- Riduzione carico database del 70-80%

**Attenzione:**
- Verificare che il server supporti Redis/Memcached
- In shared hosting potrebbe non essere disponibile

---

### 2. 📸 AVIF Converter

**Raccomandazione:** ⚠️ **ATTIVA CON CAUTELA**

```php
$settings = [
    'enabled' => true,
    'quality' => 75, // Qualità media
    'auto_deliver' => false, // IMPORTANTE: Disattivare inizialmente
    'keep_original' => true,
    'strip_metadata' => false,
];
```

**Perché con cautela:**
- Salient usa immagini in molti modi (backgrounds, sliders, lightbox)
- WPBakery genera srcset dinamici
- AVIF potrebbe rompersi con alcune funzioni JavaScript

**Test prima di attivare auto_deliver:**
1. Converti alcune immagini
2. Testa slider Salient (Nectar Slider)
3. Testa lightbox/portfolio
4. Testa background parallax
5. Solo dopo, attiva `auto_deliver => true`

**Script esclusioni per Salient:**
```php
// Escludi immagini in specifici contesti
add_filter('fp_ps_avif_delivery_supported', function($supported) {
    // Disabilita AVIF per admin/builder
    if (is_admin() || isset($_GET['vc_editable'])) {
        return false;
    }
    return $supported;
});
```

---

### 3. 🚀 HTTP/2 Server Push

**Raccomandazione:** ✅ **ATTIVA - Configurazione specifica**

```php
$settings = [
    'enabled' => true,
    'push_css' => true,
    'push_js' => false, // IMPORTANTE per Salient
    'push_fonts' => true,
    'max_resources' => 5, // Limitato
    'critical_only' => true,
];
```

**Perché push_js = false:**
- Salient usa jQuery pesantemente
- Script dipendono l'uno dall'altro
- Push JavaScript può causare problemi di ordine

**Font da pushare:**
```php
add_filter('fp_ps_http2_critical_fonts', function($fonts) {
    // Salient usa questi font
    $salientFonts = [
        '/wp-content/themes/salient/css/fonts/icomoon.woff2',
        '/wp-content/themes/salient/css/fonts/fontello.woff2',
    ];
    
    return array_merge($fonts, array_map(function($font) {
        return [
            'url' => home_url($font),
            'as' => 'font',
            'type' => 'font/woff2',
        ];
    }, $salientFonts));
});
```

---

### 4. 🎨 Critical CSS Automation

**Raccomandazione:** ⚠️ **ATTIVA MA CON ATTENZIONE**

```php
$settings = [
    'enabled' => true,
    'method' => 'internal', // Non usare API esterne
    'auto_regenerate' => false, // Manuale è meglio
    'pages' => ['home'], // Solo homepage inizialmente
];
```

**Problema con Salient + WPBakery:**
- CSS inline generato dinamicamente
- Ogni pagina ha CSS diverso
- Critical CSS potrebbe non includere elementi builder

**Soluzione manuale:**
```php
// Estrai CSS critico manualmente per homepage
add_action('wp_head', function() {
    if (is_front_page()) {
        ?>
        <style id="salient-critical-css">
        /* CSS critico manuale per elementi Salient above-fold */
        body, html { margin:0; padding:0; }
        #header-outer { /* Stili header */ }
        .nectar-slider-wrap { /* Stili slider homepage */ }
        /* ... */
        </style>
        <?php
    }
}, 1);
```

---

### 5. 🌐 Edge Cache (Cloudflare/Fastly/CloudFront)

**Raccomandazione:** ✅ **ATTIVA - Molto utile**

```php
$settings = [
    'enabled' => true,
    'provider' => 'cloudflare', // O altro provider
    'auto_purge' => true,
    'cloudflare' => [
        'api_token' => 'YOUR_TOKEN',
        'zone_id' => 'YOUR_ZONE_ID',
    ],
];
```

**Purge aggiuntivi per Salient:**
```php
// Purge quando cambiano opzioni Salient
add_action('updated_option', function($option_name) {
    if (strpos($option_name, 'salient') !== false) {
        $edge = \FP\PerfSuite\Plugin::container()
            ->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
        $edge->purgeAll();
    }
});

// Purge quando si salva pagina con WPBakery
add_action('vc_after_save', function($post_id) {
    $edge = \FP\PerfSuite\Plugin::container()
        ->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
    
    $urls = [
        get_permalink($post_id),
        home_url('/'),
    ];
    
    $edge->purgeUrls($urls);
});
```

---

### 6. 🗄️ Database Query Cache

**Raccomandazione:** ⚠️ **DISATTIVA INIZIALMENTE**

```php
$settings = [
    'enabled' => false, // Testare prima
];
```

**Perché disattivare:**
- Salient fa query complesse per portfolio/blog
- WPBakery carica template dinamicamente
- Potrebbero esserci conflitti con cache query

**Se vuoi testare:**
```php
$settings = [
    'enabled' => true,
    'ttl' => 1800, // 30 minuti
    'exclude_patterns' => [
        'salient_', // Escludi opzioni Salient
        'vc_', // Escludi WPBakery
        'wpb_', // Escludi WPBakery
        'nectar_', // Escludi componenti Nectar (Salient)
    ],
];
```

---

### 7. 📊 Third-Party Script Manager

**Raccomandazione:** ✅ **ATTIVA - Molto importante!**

```php
$settings = [
    'enabled' => true,
    'delay_all' => false, // NON ritardare tutto
    'load_on' => 'interaction',
    'delay_timeout' => 3000,
    'scripts' => [
        'google_analytics' => ['enabled' => true, 'delay' => true],
        'facebook_pixel' => ['enabled' => true, 'delay' => true],
        'google_ads' => ['enabled' => true, 'delay' => true],
    ],
];
```

**Script Salient da NON ritardare:**
```php
add_filter('fp_ps_third_party_script_delay', function($should_delay, $src) {
    // NON ritardare script Salient critici
    $salient_critical = [
        'modernizr',
        'touchswipe',
        'jquery', // Fondamentale
        'salient-',
        'nectar-',
        'wpbakery',
        'vc_',
    ];
    
    foreach ($salient_critical as $pattern) {
        if (strpos($src, $pattern) !== false) {
            return false; // Non ritardare
        }
    }
    
    return $should_delay;
}, 10, 2);
```

**Script esterni da ritardare:**
- Google Analytics ✅
- Facebook Pixel ✅
- Google Maps (se non in above-fold) ✅
- Google Fonts (Salient ne usa tanti!) ⚠️

---

### 8. 💼 Service Worker (PWA)

**Raccomandazione:** ❌ **DISATTIVA**

```php
$settings = [
    'enabled' => false,
];
```

**Perché disattivare:**
- Salient ha asset dinamici (CSS inline, JS inline)
- WPBakery genera contenuto al volo
- Service Worker potrebbe cachare versioni vecchie
- Problemi con editor frontend WPBakery

**Se proprio vuoi usarlo:**
- Testa molto attentamente
- Escludi editor WPBakery
- Cache strategy = 'network_first'

---

### 9. 📈 Core Web Vitals Monitor

**Raccomandazione:** ✅ **ATTIVA - Fondamentale!**

```php
$settings = [
    'enabled' => true,
    'sample_rate' => 0.5, // 50% utenti
    'track_lcp' => true,
    'track_fid' => true,
    'track_cls' => true, // IMPORTANTE per Salient
    'track_inp' => true,
    'alert_threshold_lcp' => 2500,
    'alert_threshold_cls' => 0.1, // Strict per Salient
];
```

**Perché è fondamentale:**
- Salient tende ad avere CLS alto (animazioni, sliders)
- LCP può essere lento (immagini grandi)
- Monitorare per ottimizzare

**Problemi comuni Salient rilevati:**
- **CLS alto**: Slider/animazioni che shiftano layout
- **LCP lento**: Hero image/slider non ottimizzato
- **FID alto**: Troppi script bloccanti

---

### 10. 🔮 Predictive Prefetching

**Raccomandazione:** ⚠️ **ATTIVA CON LIMITAZIONI**

```php
$settings = [
    'enabled' => true,
    'strategy' => 'hover', // Hover è più sicuro
    'delay' => 200,
    'max_prefetch' => 2, // Massimo 2 per evitare overload
    'exclude_patterns' => [
        '/wp-admin/',
        '/cart/', // WooCommerce se presente
        '/checkout/',
        '?vc_editable', // Editor WPBakery
    ],
];
```

**Esclusioni specifiche Salient:**
```php
add_filter('fp_ps_prefetch_exclude', function($patterns) {
    // Aggiungi pattern Salient
    $salient_exclude = [
        '/portfolio/', // Portfolio può essere pesante
        '?nectar_', // Link interni Salient
    ];
    
    return array_merge($patterns, $salient_exclude);
});
```

---

### 11. 📡 Smart Asset Delivery

**Raccomandazione:** ✅ **ATTIVA**

```php
$settings = [
    'enabled' => true,
    'detect_connection' => true,
    'save_data_mode' => true,
    'adaptive_images' => true,
    'adaptive_videos' => true,
    'quality_slow' => 50, // 2G
    'quality_moderate' => 65, // 3G
    'quality_fast' => 85, // 4G
];
```

**Integrazione con Salient:**
```php
// Disabilita parallax su connessioni lente
add_action('wp_footer', function() {
    ?>
    <script>
    if (navigator.connection && 
        (navigator.connection.effectiveType === 'slow-2g' || 
         navigator.connection.effectiveType === '2g')) {
        // Disabilita parallax Salient
        jQuery(document).ready(function($) {
            $('.nectar-parallax-scene').removeClass('nectar-parallax-scene');
            $('[data-parallax]').removeAttr('data-parallax');
        });
    }
    </script>
    <?php
});
```

---

## 🎯 Configurazione Completa Raccomandata

### Setup Iniziale Conservativo

```php
add_action('init', function() {
    if (!function_exists('FP\PerfSuite\Plugin::container')) {
        return;
    }
    
    $container = \FP\PerfSuite\Plugin::container();
    
    // 1. ATTIVA: Object Cache (se disponibile)
    if (class_exists('Redis') || class_exists('Memcached')) {
        $objectCache = $container->get(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class);
        $objectCache->update([
            'enabled' => true,
            'driver' => 'auto',
            'prefix' => 'salient_fps_',
        ]);
    }
    
    // 2. ATTIVA: Edge Cache (se hai Cloudflare)
    $edgeCache = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
    $edgeCache->update([
        'enabled' => true,
        'provider' => 'cloudflare',
        'auto_purge' => true,
        'cloudflare' => [
            'api_token' => get_option('fps_cloudflare_token'),
            'zone_id' => get_option('fps_cloudflare_zone'),
        ],
    ]);
    
    // 3. ATTIVA: Core Web Vitals Monitor
    $cwv = $container->get(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class);
    $cwv->update([
        'enabled' => true,
        'sample_rate' => 0.5,
        'track_lcp' => true,
        'track_fid' => true,
        'track_cls' => true,
        'alert_threshold_cls' => 0.1,
    ]);
    
    // 4. ATTIVA: Third-Party Script Manager
    $scripts = $container->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class);
    $scripts->update([
        'enabled' => true,
        'delay_all' => false,
        'load_on' => 'interaction',
        'scripts' => [
            'google_analytics' => ['enabled' => true, 'delay' => true],
            'facebook_pixel' => ['enabled' => true, 'delay' => true],
        ],
    ]);
    
    // 5. ATTIVA: Smart Asset Delivery
    $smart = $container->get(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class);
    $smart->update([
        'enabled' => true,
        'save_data_mode' => true,
        'adaptive_images' => true,
    ]);
    
    // 6. ATTIVA: HTTP/2 Server Push (se server supporta HTTP/2)
    $http2 = $container->get(\FP\PerfSuite\Services\Assets\Http2ServerPush::class);
    $http2->update([
        'enabled' => true,
        'push_css' => true,
        'push_js' => false, // IMPORTANTE
        'push_fonts' => true,
        'max_resources' => 5,
    ]);
});
```

### Setup Aggressivo (Solo dopo testing)

```php
// Dopo aver testato il setup conservativo, puoi aggiungere:

// 7. AVIF (con auto-delivery)
$avif = $container->get(\FP\PerfSuite\Services\Media\AVIFConverter::class);
$avif->update([
    'enabled' => true,
    'quality' => 75,
    'auto_deliver' => true, // Solo dopo test
]);

// 8. Predictive Prefetching
$prefetch = $container->get(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class);
$prefetch->update([
    'enabled' => true,
    'strategy' => 'hover',
    'max_prefetch' => 2,
]);
```

---

## ⚠️ Problemi Comuni e Soluzioni

### Problema 1: Slider Salient non funziona

**Causa:** Script ritardati o AVIF non compatibile

**Soluzione:**
```php
// Escludi script slider da delay
add_filter('fp_ps_third_party_script_delay', function($delay, $src) {
    if (strpos($src, 'nectar-slider') !== false) {
        return false;
    }
    return $delay;
}, 10, 2);
```

### Problema 2: WPBakery editor frontend lento

**Causa:** Service Worker o cache aggressive

**Soluzione:**
```php
// Disabilita ottimizzazioni in editor mode
add_filter('fp_ps_disable_optimizations', function($disable) {
    if (isset($_GET['vc_editable']) || isset($_GET['vc_action'])) {
        return true;
    }
    return $disable;
});
```

### Problema 3: Layout shift (CLS alto)

**Causa:** Immagini senza dimensioni, slider, animazioni

**Soluzione:**
```php
// Forza dimensioni su immagini Salient
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment) {
    if (empty($attr['width']) || empty($attr['height'])) {
        $meta = wp_get_attachment_metadata($attachment->ID);
        if ($meta) {
            $attr['width'] = $meta['width'];
            $attr['height'] = $meta['height'];
        }
    }
    return $attr;
}, 10, 2);
```

### Problema 4: Font Icons non caricano

**Causa:** HTTP/2 push sbagliato o AVIF su icone

**Soluzione:**
```php
// Escludi font icons da AVIF
add_filter('fp_ps_avif_exclude_types', function($types) {
    $types[] = 'font';
    return $types;
});

// Assicurati che font icons siano precaricati
add_action('wp_head', function() {
    ?>
    <link rel="preload" href="<?php echo get_template_directory_uri(); ?>/css/fonts/icomoon.woff2" as="font" type="font/woff2" crossorigin>
    <?php
}, 5);
```

---

## 📊 Risultati Attesi

Con configurazione ottimale per Salient + WPBakery:

| Metrica | Prima | Dopo | Miglioramento |
|---------|-------|------|---------------|
| **TTFB** | 800ms | 200ms | -75% ⬇️ |
| **LCP** | 4.5s | 2.2s | -51% ⬇️ |
| **FID** | 150ms | 80ms | -47% ⬇️ |
| **CLS** | 0.25 | 0.08 | -68% ⬇️ |
| **Page Size** | 2.5MB | 1.2MB | -52% ⬇️ |
| **Requests** | 85 | 45 | -47% ⬇️ |

---

## 🧪 Piano di Test

### Fase 1: Setup Base (Settimana 1)
- ✅ Object Cache
- ✅ Edge Cache  
- ✅ Core Web Vitals Monitor
- ✅ Smart Asset Delivery

### Fase 2: Ottimizzazioni Script (Settimana 2)
- ✅ Third-Party Script Manager
- ✅ HTTP/2 Server Push
- ⚠️ Test approfonditi su tutte le pagine

### Fase 3: Ottimizzazioni Avanzate (Settimana 3)
- ⚠️ AVIF (senza auto-delivery)
- ⚠️ Predictive Prefetching
- ⚠️ Test completo funzionalità Salient

### Fase 4: Fine Tuning (Settimana 4)
- ⚠️ AVIF auto-delivery (se test OK)
- ⚠️ Critical CSS (solo homepage)
- ✅ Analisi metriche e aggiustamenti

---

## 🎯 Checklist Pre-Deploy

Prima di attivare in produzione:

- [ ] Backup completo sito
- [ ] Test su staging con clone esatto
- [ ] Test tutti i template Salient
- [ ] Test editor WPBakery frontend
- [ ] Test slider/portfolio/blog
- [ ] Test form contatti
- [ ] Test WooCommerce (se presente)
- [ ] Test responsive mobile
- [ ] Verifica metriche Core Web Vitals
- [ ] Test su browser diversi
- [ ] Test velocità connessione lenta

---

## 📝 Note Finali

**Tema Salient** è potente ma pesante. Con questa configurazione puoi ottenere:
- ✅ 50-70% miglioramento performance
- ✅ Core Web Vitals nella "zona verde"
- ✅ Esperienza utente migliore
- ✅ SEO boost

**Ricorda:**
- Testa sempre in staging prima
- Attiva servizi gradualmente
- Monitora metriche continuamente
- Fai backup regolari

---

**Autore:** FP Performance Suite  
**Versione:** 1.3.0  
**Tema:** Salient  
**Page Builder:** WPBakery  
**Data:** 2025-10-15
