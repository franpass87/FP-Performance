# Proposte di miglioramento per FP Performance Suite

## Priorità di sviluppo

| Priorità | Elementi |
| --- | --- |
| **Alta** | Automatic page-cache purges on content updates<br>Automated page-cache prewarming<br>Selective page-cache purges<br>Render the stored Critical CSS snippet<br>Background WebP backlog worker<br>Automatic WebP delivery<br>Scheduled performance regression alerts |
| **Media** | Asset CDN rewriting support<br>Static cache rewrite rules (.htaccess)<br>WP-CLI support for maintenance tasks<br>Enforce "Safety mode" on high-risk toggles<br>Script optimization exclusion whitelists<br>Preconnect resource hints<br>Database cleanup background queue<br>Extended debug toggler coverage<br>User-defined optimization presets<br>Site Health integration<br>Third-party script defer manager<br>Service worker asset pre-cache<br>Edge cache provider integrations<br>Automatic LQIP placeholders |
| **Bassa** | Database query performance dashboard<br>Real-time performance scoring widget<br>Optimization change audit log |

## Consigli precedenti

### Automatic page-cache purges on content updates
**Priorità:** Alta — evita contenuti obsoleti e riduce interventi manuali dopo ogni aggiornamento.
- Il servizio PageCache gestisce la lettura/scrittura dei buffer ma non registra hook di invalidazione automatici.
- Ogni modifica a contenuti o commenti lascia file obsoleti finché un operatore non usa "Clear Cache".
- Agganciare hook come `save_post`, `deleted_post`, `trashed_post`, `comment_post`, `switch_theme` e `customize_save_after` per eseguire `clear()` quando la cache è abilitata.

### Automated page-cache prewarming
**Priorità:** Alta — riduce i tempi di risposta al primo hit dopo lo svuotamento della cache.
- La cache si popola solo quando un visitatore richiama `startBuffering()`/`saveBuffer()`; non esiste una routine pianificata che preriscaldi i file HTML.
- Aggiungere metodi `schedulePrewarm()`/`unschedulePrewarm()` in `PageCache` per programmare un hook cron dedicato (es. `fp_ps_page_cache_prewarm`).
- Il worker pianificato dovrebbe iterare sugli URL pubblici (via sitemap, `get_posts()` o filtro) e chiamare `wp_remote_get()` con una query string specifica per generare i file.
- Salvare stato e progresso in un'opzione così da mostrare avanzamento e ultimo run nella card Cache dell'admin.

### Selective page-cache purges
**Priorità:** Alta — abilita invalidazioni mirate evitando purge completi dopo piccole modifiche.
- Oggi l'unica operazione disponibile è `clear()` che elimina l'intera cartella, con un solo pulsante "Clear Cache" nell'interfaccia.
- Implementare `PageCache::purgeUrl(string $uri)` per cancellare file HTML e `.meta` associati a un singolo URL considerando host e permalink.
- Esporre una rotta REST `fp-ps/v1/cache/purge` e aggiornare UI/JS per consentire all'admin di indicare l'URL da invalidare.
- Coprire il comportamento con test che assicurino la rimozione selettiva e una gestione robusta degli input.

### Asset CDN rewriting support
**Priorità:** Media — migliora la distribuzione globale ma richiede configurazioni esterne opzionali.
- L'optimizer salva impostazioni per minify/defer/combine ma non gestisce un host CDN.
- Aggiungere un campo `cdn_host` validato nelle impostazioni e nel form Assets.
- Riscrivere gli URL degli asset su frontend tramite i filtri `script_loader_src`, `style_loader_src` e `wp_get_attachment_url`.

### Static cache rewrite rules (.htaccess)
**Priorità:** Media — consente di servire file cacheati senza bootstrap di WordPress, migliorando l'efficienza.
- `PageCache::maybeServeCache()` legge i file durante `template_redirect`, quindi ogni hit passa da PHP.
- Accettare `Htaccess` nel costruttore di `PageCache` e creare un metodo `applyRewriteRules()` che inietti regole mod_rewrite dedicate.
- Attivare/rimuovere automaticamente il blocco `FP-PS-Cache` in `register()`/`update()` in base allo stato dell'opzione, prevedendo anche filtri per Nginx.
- Verificare tramite test che le regole vengano aggiunte quando la cache è abilitata e rimosse alla disattivazione.

### Render the stored Critical CSS snippet
**Priorità:** Alta — utilizza una funzione già disponibile per migliorare il Largest Contentful Paint.
- L'opzione "Critical CSS placeholder" è salvata e valutata dal punteggio ma non viene iniettata nel frontend.
- Hook `wp_head` per stampare una `<style>` con il CSS critico sanificato sulle pagine pubbliche.
- Aggiornare la documentazione/settings per chiarire il nuovo comportamento.

### WP-CLI support for maintenance tasks
**Priorità:** Media — abilita l'automazione ma non è bloccante per installazioni più piccole.
- `Env::isCli()` esiste ma il bootstrap del plugin non registra comandi CLI per cache, cleanup o conversioni WebP.
- Creare un loader CLI che registri comandi `wp fp-perf` per automatizzare purge cache, pulizia DB e conversioni WebP.
- Documentare i comandi e aggiungere test che verificano le invocazioni dei servizi.

### Enforce "Safety mode" on high-risk toggles
**Priorità:** Media — riduce errori umani nelle configurazioni a rischio elevato.
- Il flag `safety_mode` viene salvato ma non condiziona i controlli con rischio "amber/red".
- Localizzare il flag in `assets/admin.js` per disabilitare default e richiedere conferma esplicita.
- Aggiornare i form admin per riflettere lo stato di sicurezza e impedirne l'aggiramento.

### Background WebP backlog worker
**Priorità:** Alta — garantisce la coerenza nella conversione dei media senza interventi manuali ripetuti.
- L'abilitazione WebP agisce solo sui nuovi upload; per la libreria esistente serve un submit manuale.
- Pianificare un hook cron (es. `fp_ps_webp_batch`) che processi lotti di media fino al completamento.
- Mostrare nella pagina Media lo stato del job e un comando "Esegui ora".

### Automatic WebP delivery
**Priorità:** Alta — porta benefici immediati al caricamento immagini sfruttando file già generati.
- `WebPConverter` crea versioni WebP ma non riscrive gli URL o gli `srcset` sul front-end.
- Agganciare filtri come `wp_get_attachment_image_src`, `wp_calculate_image_srcset` e `the_content` per sostituire le estensioni con `.webp` quando disponibili.
- Introdurre controlli di compatibilità (header `Accept`, filtro `fp_ps_webp_delivery_enabled`) e fallback all'originale se il file manca.
- Aggiornare le impostazioni Media con un toggle "Serve WebP automaticamente" e relativi test.

### Script optimization exclusion whitelists
**Priorità:** Media — permette configurazioni granulari senza personalizzazioni di codice.
- `filterScriptTag()` si affida solo al filtro `fp_ps_defer_skip_handles`, assente nell'UI.
- Estendere `Optimizer` con array `defer_exclude`, `async_exclude` e `combine_exclude`, salvati in `fp_ps_assets`.
- Aggiungere textarea dedicate in `src/Admin/Pages/Assets.php` e sincronizzarle con `assets/admin.js` se servono feedback.
- Garantire tramite test che gli handle indicati saltino le ottimizzazioni rispettive.

### Preconnect resource hints
**Priorità:** Media — migliora l'inizializzazione delle connessioni verso asset esterni critici.
- Le impostazioni attuali includono solo DNS-prefetch e preload.
- Ampliare `fp_ps_assets` con una chiave `preconnect` gestita da `settings()`/`update()` e validata con `sanitizeUrlList`.
- Agganciare `wp_resource_hints` per aggiungere i domini `preconnect` quando `$relation` è `preconnect` nel front-end.
- Documentare il campo "Preconnect domains" e coprire la logica con test.

### Database cleanup background queue
**Priorità:** Media — riduce il rischio di timeout durante pulizie estese del database.
- Il pulsante "Execute Cleanup" invoca `Cleaner::cleanup()` nella stessa richiesta POST.
- Introdurre una coda salvata in opzione (es. `fp_ps_db_queue`) con hook cron `fp_ps_db_process_queue` per processare lotti rispettando `batch`.
- Aggiornare la UI Database con un pulsante "Avvia in background", indicatori di progresso e possibilità di forzare il prossimo batch.
- Scrivere test che simulino la creazione della coda e l'avanzamento attraverso l'hook pianificato.

### Extended debug toggler coverage
**Priorità:** Media — offre controllo completo sulle costanti di debug critiche.
- Il servizio attuale gestisce solo `WP_DEBUG` e `WP_DEBUG_LOG`.
- Ampliare `DebugToggler` per includere `WP_DEBUG_DISPLAY`, `SCRIPT_DEBUG` e `SAVEQUERIES`, con backup/ripristino coerente.
- Aggiornare la UI Logs con nuove checkbox e messaggi esplicativi, propagando i valori in `assets/admin.js`.
- Testare la scrittura su `wp-config.php` e il ripristino dai backup generati.

### User-defined optimization presets
**Priorità:** Media — consente di salvare e condividere configurazioni ricorrenti.
- I preset sono attualmente codificati nella classe `PresetManager` senza supporto per preset utente.
- Caricare un'opzione `fp_ps_custom_presets` da fondere con quelli di default e introdurre metodi `createPreset`, `updatePreset`, `deletePreset`.
- Esporre endpoint REST `fp-ps/v1/presets` e aggiornare `src/Admin/Pages/Presets.php` con form/modal per gestire i preset personalizzati.
- Aggiungere test che verifichino serializzazione e fusione corretta dei preset custom.

### Site Health integration
**Priorità:** Media — porta la visibilità dello stato del plugin nel tool core di WordPress.
- Attualmente il plugin non registra test personalizzati in Site Health.
- Creare un servizio (es. `SiteHealth\Status`) che agganci `site_status_tests` con controlli su PageCache, WebP, Scorer e Cleaner.
- Restituire raccomandazioni e link rapidi alle pagine del plugin quando le funzionalità sono inattive o in errore.
- Coprire la registrazione dei test con PHPUnit.

## Nuovi consigli

### Database query performance dashboard
**Priorità:** Bassa — fornisce visibilità avanzata ma non influisce direttamente sulle prestazioni utente immediate.
- Integrare una dashboard che raccolga query lente tramite il logger esistente e le visualizzi con metriche di frequenza e durata.
- Offrire filtri per endpoint, tipo di richiesta e possibilità di esportare i dati per l'analisi.

### Third-party script defer manager
**Priorità:** Media — offre controlli granulari sugli script esterni, con impatto moderato sul tempo di caricamento.
- Aggiungere un'interfaccia per elencare script di terze parti e controllarne il caricamento condizionale, con opzioni per defer, async o caricamento on-interaction.
- Integrare controlli di sicurezza per evitare la disattivazione accidentale di script critici.

### Real-time performance scoring widget
**Priorità:** Bassa — utile per monitoraggio proattivo ma non necessario per il funzionamento quotidiano.
- Esporre un widget nel dashboard admin che esegue test sintetici rapidi (es. Lighthouse API o server-side metrics) e mostra il trend dei punteggi.
- Permettere di programmare report periodici via email o Slack utilizzando i servizi già disponibili.

### Scheduled performance regression alerts
**Priorità:** Alta — previene regressioni ignorate garantendo reazioni rapide ai cali di performance.
- Integrare raccolte periodiche di metriche chiave (Core Web Vitals, tempi di risposta cache) e confrontarle con baseline configurabili.
- Inviare notifiche email/Slack quando il punteggio scende oltre soglie definite o quando i trend negativi persistono.
- Offrire un pannello storico con log degli alert e strumenti per annotare gli interventi eseguiti.

### Service worker asset pre-cache
**Priorità:** Media — migliora percezione di velocità offline e nelle visite successive senza richiedere CDN esterni.
- Generare dinamicamente un manifest di asset critici e registrare un service worker che li pre-carichi e aggiorni quando la cache cambia.
- Fornire esclusioni e limiti dimensione per evitare di saturare storage o interferire con app esistenti.
- Mostrare stato di installazione del service worker e log errori nella pagina Assets.

### Edge cache provider integrations
**Priorità:** Media — riduce complessità nell'uso di piattaforme CDN/WAF popolari.
- Offrire connettori per Cloudflare, Fastly o altri provider per sincronizzare purge e impostazioni direttamente dal plugin.
- Mappare le regole di PageCache con API edge (es. bypass per utenti loggati, TTL dedicati) e riportare lo stato di propagazione.
- Gestire fallback sicuro quando le credenziali o le chiamate API falliscono, con log dettagliati.

### Automatic LQIP placeholders
**Priorità:** Media — migliora l'esperienza visiva iniziale senza interventi manuali sui temi.
- Generare versioni a bassa risoluzione (blurhash/LQIP) durante la conversione media e salvarle come meta per gli attachment.
- Iniettare i placeholder via `wp_get_attachment_image` e blocchi Gutenberg finché l'immagine principale non è caricata.
- Consentire controlli per disattivare la funzione su tipi di contenuto o dimensioni specifiche.

### Optimization change audit log
**Priorità:** Bassa — favorisce governance e tracciabilità in team distribuiti.
- Tracciare modifiche a impostazioni critiche (cache, asset, media) salvando chi ha effettuato il cambiamento, timestamp e valori precedenti.
- Integrare un log consultabile nell'admin con filtri e opzione di export CSV/JSON.
- Prevedere hook/filtro per inviare notifiche o sincronizzare il log con sistemi esterni di auditing.
