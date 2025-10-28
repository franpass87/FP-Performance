<?php

namespace FP\PerfSuite\Admin;

/**
 * Risk Matrix - Sistema Centrale di Classificazione Rischi
 * 
 * Definisce il livello di rischio per TUTTE le opzioni del plugin
 * con descrizioni, rischi concreti, e consigli specifici
 * 
 * @package FP\PerfSuite\Admin
 */
class RiskMatrix
{
    /**
     * Livelli di rischio disponibili
     */
    const RISK_GREEN = 'green';  // Sicuro
    const RISK_AMBER = 'amber';  // Medio - Testa prima
    const RISK_RED = 'red';      // Alto - Sconsigliato
    
    /**
     * Matrice completa dei rischi per opzione
     * 
     * Formato:
     * 'option_key' => [
     *     'risk' => 'green|amber|red',
     *     'title' => 'Titolo per tooltip',
     *     'description' => 'Cosa fa',
     *     'risks' => 'Rischi concreti',
     *     'why_fails' => 'Perché può fallire',
     *     'advice' => 'Consiglio'
     * ]
     */
    private static $matrix = [
        
        // ═══════════════════════════════════════════════════════════
        // 📦 CACHE
        // ═══════════════════════════════════════════════════════════
        
        'page_cache_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita la cache delle pagine statiche per velocizzare il caricamento.',
            'risks' => '✅ Nessun rischio significativo',
            'why_fails' => 'Funziona con la maggior parte dei siti. Raramente causa problemi.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Escludi contenuti dinamici se necessario.'
        ],
        
        'prefetch_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Precarica risorse quando l\'utente passa il mouse su un link.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Migliora navigazione senza effetti negativi.',
            'advice' => '✅ CONSIGLIATO: Velocizza navigazione tra pagine.'
        ],
        
        'edge_cache_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita cache edge (Cloudflare, CloudFront).',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Richiede credenziali API corrette.',
            'advice' => '✅ CONSIGLIATO: Ottimo per siti globali.'
        ],
        
        'browser_cache_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Imposta header di cache per browser.',
            'risks' => '✅ Nessun rischio',
            'why_fails' => 'Standard web supportato da tutti i browser.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📦 ASSETS - CSS
        // ═══════════════════════════════════════════════════════════
        
        'minify_css' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Riduce il peso dei file CSS rimuovendo spazi e commenti.',
            'risks' => '✅ Raramente causa problemi',
            'why_fails' => 'Può fallire solo con CSS mal formattato.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre.'
        ],
        
        'async_css' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Carica i CSS in modo asincrono per non bloccare il rendering.',
            'risks' => '⚠️ FLASH di contenuto non stilizzato (FOUC)\n⚠️ Elementi che "saltano" durante il caricamento',
            'why_fails' => 'La pagina viene renderizzata PRIMA che arrivino gli stili, causando un "salto" visivo.',
            'advice' => '⚠️ Testa accuratamente: Usa Critical CSS inline per mitigare FOUC.'
        ],
        
        'combine_css' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Combina più file CSS in uno solo.',
            'risks' => '⚠️ Ordine di caricamento errato\n⚠️ Specificità CSS può cambiare\n⚠️ Alcuni stili potrebbero non applicarsi',
            'why_fails' => 'L\'ordine dei CSS è importante. Combinarli può alterare le priorità.',
            'advice' => '⚠️ Testa su tutte le pagine: Verifica menu, footer, pagine speciali.'
        ],
        
        'remove_unused_css' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Rimuove completamente i file CSS che Lighthouse considera "inutilizzati".',
            'risks' => '❌ LOGO SCOMPARE\n❌ MENU NON FUNZIONA\n❌ FOOTER ROTTO\n❌ Pulsanti senza stile\n❌ Layout completamente distrutto',
            'why_fails' => 'Lighthouse analizza solo la homepage. Il CSS per menu, hover, mobile, altre pagine viene considerato "inutilizzato" e rimosso.',
            'advice' => '❌ SCONSIGLIATO: NON attivare a meno che tu non abbia configurato TUTTE le esclusioni per header, footer, menu e layout base.'
        ],
        
        'defer_non_critical_css' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Ritarda il caricamento del CSS considerato "non critico".',
            'risks' => '⚠️ FLASH di contenuto non stilizzato (FOUC)\n❌ LOGO appare dopo 1-2 secondi\n❌ MENU "salta" durante il caricamento\n❌ Footer si materializza dopo\n⚠️ Esperienza utente MOLTO negativa',
            'why_fails' => 'Il sistema classifica ERRONEAMENTE come "non critici" gli stili di header, menu, footer.',
            'advice' => '❌ SCONSIGLIATO: Il guadagno in LCP non vale la pessima esperienza utente.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📦 ASSETS - JAVASCRIPT
        // ═══════════════════════════════════════════════════════════
        
        'minify_js' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Riduce il peso dei file JavaScript.',
            'risks' => '✅ Raramente causa problemi',
            'why_fails' => 'Può fallire solo con JS mal formattato o commenti con codice.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre.'
        ],
        
        'defer_js' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Posticipa l\'esecuzione degli script dopo il caricamento della pagina.',
            'risks' => '⚠️ Errori con script che dipendono da jQuery\n⚠️ Menu dropdown non funzionano\n⚠️ Slider/carousel non si caricano\n⚠️ Form non validano',
            'why_fails' => 'Alcuni script si aspettano che altri siano già caricati (dipendenze).',
            'advice' => '⚠️ Testa accuratamente: Esclude jQuery, jQuery-migrate, e script con dipendenze.'
        ],
        
        'async_js' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Carica gli script in modo asincrono senza bloccare il rendering.',
            'risks' => '⚠️ Gli script potrebbero eseguirsi in ordine diverso\n⚠️ Errori di "undefined" per variabili/funzioni\n⚠️ Conflitti tra script',
            'why_fails' => 'L\'ordine di esecuzione non è garantito con async.',
            'advice' => '⚠️ Usa con cautela: Non combinare con Defer. Testa approfonditamente.'
        ],
        
        'combine_js' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Combina tutti i file JavaScript in un unico file.',
            'risks' => '❌ ERRORI JavaScript diffusi\n❌ MENU completamente rotto\n❌ Form non funzionano\n❌ Slider/carousel spariscono\n❌ Console piena di errori',
            'why_fails' => 'Combinar JS è molto più rischioso del CSS. Ordine, scope, dipendenze tutto può rompersi.',
            'advice' => '❌ SCONSIGLIATO: Meglio usare defer. Combinarli causa troppi problemi.'
        ],
        
        'remove_emojis' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove lo script WordPress per il supporto emoji legacy.',
            'risks' => '✅ Nessun rischio',
            'why_fails' => 'I browser moderni supportano gli emoji nativamente.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Riduce richieste HTTP senza rischi.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 💾 DATABASE
        // ═══════════════════════════════════════════════════════════
        
        'db_cleanup_revisions' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove le vecchie revisioni dei post.',
            'risks' => '✅ Sicuro - Rimuove solo vecchie revisioni',
            'why_fails' => 'Non tocca contenuti pubblicati.',
            'advice' => '✅ CONSIGLIATO: Mantieni ultime 5 revisioni per sicurezza.'
        ],
        
        'db_cleanup_autodrafts' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove le bozze automatiche di WordPress.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Rimuove solo bozze auto-salvate, non contenuti reali.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre.'
        ],
        
        'db_cleanup_trashed' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Elimina definitivamente post e pagine nel cestino.',
            'risks' => '⚠️ ELIMINAZIONE PERMANENTE\n⚠️ Non recuperabile',
            'why_fails' => 'Elimina contenuti che potresti voler recuperare.',
            'advice' => '⚠️ Usa con cautela: Controlla il cestino prima di pulire.'
        ],
        
        'db_optimize_tables' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Ottimizza le tabelle del database per ridurre frammentazione.',
            'risks' => '⚠️ Può causare timeout su database grandi\n⚠️ Blocca temporaneamente le tabelle',
            'why_fails' => 'Su shared hosting con limiti stretti, può causare timeout.',
            'advice' => '⚠️ Esegui in orari di basso traffico: Preferibilmente di notte.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🗜️ COMPRESSION
        // ═══════════════════════════════════════════════════════════
        
        'gzip_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Compressione standard supportata da tutti i browser e server.',
            'risks' => '✅ Nessun rischio',
            'why_fails' => 'Standard web universale.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Riduce peso del 60-80%.'
        ],
        
        'brotli_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Compressione moderna più efficiente di Gzip.',
            'risks' => '⚠️ Non supportato da server vecchi\n⚠️ Richiede più CPU',
            'why_fails' => 'Server shared hosting potrebbero non supportarlo.',
            'advice' => '⚠️ Verifica supporto server: Controlla con hosting provider.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📱 MOBILE
        // ═══════════════════════════════════════════════════════════
        
        'mobile_cache' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Cache separata per dispositivi mobili.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Migliora performance mobile senza rischi.',
            'advice' => '✅ CONSIGLIATO: Attiva se hai traffico mobile significativo.'
        ],
        
        'mobile_disable_animations' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Disabilita animazioni CSS su mobile.',
            'risks' => '⚠️ Esperienza utente meno "fluida"\n⚠️ Alcuni elementi potrebbero sembrare "rigidi"',
            'why_fails' => 'Le animazioni sono parte del design.',
            'advice' => '⚠️ Dipende dal sito: OK per siti content-heavy, NO per portfolio/creative.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // ⚙️ BACKEND
        // ═══════════════════════════════════════════════════════════
        
        'disable_admin_bar_frontend' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Nasconde la barra admin nel frontend.',
            'risks' => '✅ Nessun rischio',
            'why_fails' => 'Cambia solo visualizzazione, non funzionalità.',
            'advice' => '✅ OK: Preferenza personale.'
        ],
        
        'disable_heartbeat' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Disabilita l\'API Heartbeat di WordPress.',
            'risks' => '⚠️ Post lock non funziona (più utenti possono modificare stesso post)\n⚠️ Notifiche real-time disabilitate\n⚠️ Autosave ritardato',
            'why_fails' => 'Heartbeat serve per funzionalità real-time.',
            'advice' => '⚠️ Riduci frequenza invece di disabilitare: 60 secondi è un buon compromesso.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🛡️ SECURITY
        // ═══════════════════════════════════════════════════════════
        
        'security_headers' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Aggiunge header di sicurezza (HSTS, X-Frame-Options, ecc.).',
            'risks' => '✅ Generalmente sicuro',
            'why_fails' => 'Standard di sicurezza moderni.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre per maggiore sicurezza.'
        ],
        
        'disable_file_edit' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Disabilita l\'editor di file/plugin nell\'admin.',
            'risks' => '✅ Nessun rischio - Anzi, migliora sicurezza',
            'why_fails' => 'Previene modifiche accidentali o dannose.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre per sicurezza.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📦 ASSETS - CSS AVANZATO
        // ═══════════════════════════════════════════════════════════
        
        'minify_inline_css' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Minifica il CSS inline nei tag <style>.',
            'risks' => '✅ Raramente causa problemi',
            'why_fails' => 'Può fallire solo con CSS mal formattato.',
            'advice' => '✅ CONSIGLIATO: Attiva per ridurre peso HTML.'
        ],
        
        'remove_comments' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove commenti HTML dal codice.',
            'risks' => '✅ Sicuro - Rimuove solo commenti',
            'why_fails' => 'I commenti non sono necessari in produzione.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Riduce peso HTML.'
        ],
        
        'optimize_google_fonts' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Ottimizza il caricamento dei Google Fonts.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Usa display=swap e preconnect, standard consolidati.',
            'advice' => '✅ CONSIGLIATO: Migliora font rendering senza rischi.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📦 ASSETS - JAVASCRIPT AVANZATO
        // ═══════════════════════════════════════════════════════════
        
        'minify_inline_js' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Minifica JavaScript inline nei tag <script>.',
            'risks' => '⚠️ Può rompere codice con commenti speciali\n⚠️ Può causare errori di parsing',
            'why_fails' => 'Il JS inline spesso contiene codice dinamico o commenti importanti.',
            'advice' => '⚠️ Testa accuratamente: Verifica console browser per errori.'
        ],
        
        'unused_js_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Rimuove JavaScript non utilizzato identificato automaticamente.',
            'risks' => '⚠️ Può rompere funzionalità dinamiche\n⚠️ Form potrebbero non funzionare\n⚠️ Eventi onclick/hover possono fallire',
            'why_fails' => 'Il codice "non utilizzato" potrebbe servire per interazioni utente non ancora avvenute.',
            'advice' => '⚠️ Testa accuratamente: Usa le esclusioni per proteggere script critici.'
        ],
        
        'code_splitting_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Divide il codice JavaScript in chunks separati.',
            'risks' => '⚠️ Aumenta richieste HTTP\n⚠️ Può causare ritardi nel caricamento di funzionalità',
            'why_fails' => 'I chunk potrebbero non caricarsi in tempo per l\'interazione utente.',
            'advice' => '⚠️ Utile per siti complessi: Bilancia riduzione bundle vs richieste HTTP.'
        ],
        
        'tree_shaking_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove funzioni non utilizzate dai bundle JavaScript.',
            'risks' => '✅ Generalmente sicuro',
            'why_fails' => 'Rimuove solo codice oggettivamente non raggiungibile.',
            'advice' => '✅ CONSIGLIATO: Efficace e sicuro per ridurre bundle.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🖼️ MEDIA & IMAGES
        // ═══════════════════════════════════════════════════════════
        
        'lazy_load_images' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Carica immagini solo quando entrano nel viewport.',
            'risks' => '✅ Sicuro - Standard moderno',
            'why_fails' => 'Supportato nativamente dai browser moderni.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Migliora LCP significativamente.'
        ],
        
        'lazy_load_iframes' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Carica iframe solo quando visibili.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Standard HTML5 loading="lazy".',
            'advice' => '✅ CONSIGLIATO: Ottimo per video/mappe embedded.'
        ],
        
        'webp_conversion' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Converte immagini in formato WebP.',
            'risks' => '⚠️ Browser vecchi non supportano WebP\n⚠️ Richiede processing server',
            'why_fails' => 'Safari vecchi (<14) non supportano WebP.',
            'advice' => '⚠️ OK su siti moderni: Include fallback per browser vecchi.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 💾 DATABASE - OPZIONI AVANZATE
        // ═══════════════════════════════════════════════════════════
        
        'db_cleanup_spam' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove commenti spam.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Rimuove solo spam già identificato.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre.'
        ],
        
        'db_cleanup_transients' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove transient scaduti.',
            'risks' => '✅ Sicuro - Solo transient scaduti',
            'why_fails' => 'WordPress ricrea transient se necessari.',
            'advice' => '✅ CONSIGLIATO: Pulisce DB senza rischi.'
        ],
        
        'db_cleanup_orphaned_meta' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Rimuove metadata orfani nel database.',
            'risks' => '⚠️ Potrebbe rimuovere dati di plugin disinstallati\n⚠️ Non sempre recuperabile',
            'why_fails' => 'Difficile determinare cosa è veramente "orfano".',
            'advice' => '⚠️ Fai backup prima: Esegui solo dopo aver verificato.'
        ],
        
        'db_auto_optimize' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Ottimizza automaticamente le tabelle del database.',
            'risks' => '⚠️ Può causare timeout su DB grandi\n⚠️ Blocca temporaneamente le tabelle',
            'why_fails' => 'L\'ottimizzazione richiede lock esclusivo sulle tabelle.',
            'advice' => '⚠️ Esegui di notte: Programma in orari di basso traffico.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // ⚙️ BACKEND - ADMIN OPTIMIZATION
        // ═══════════════════════════════════════════════════════════
        
        'disable_dashboard_widgets' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove widget dalla dashboard admin.',
            'risks' => '✅ Nessun rischio',
            'why_fails' => 'Solo preferenza visiva.',
            'advice' => '✅ OK: Velocizza dashboard senza problemi.'
        ],
        
        'disable_update_checks' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Disabilita controlli automatici degli aggiornamenti.',
            'risks' => '❌ SICUREZZA COMPROMESSA\n❌ Non vedi aggiornamenti critici\n❌ Vulnerabilità non patchate',
            'why_fails' => 'Gli aggiornamenti includono fix di sicurezza critici.',
            'advice' => '❌ SCONSIGLIATO: Controlla aggiornamenti manualmente almeno settimanalmente.'
        ],
        
        'limit_post_revisions' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Limita il numero di revisioni salvate.',
            'risks' => '✅ Sicuro - Mantiene ultime N revisioni',
            'why_fails' => 'Mantiene comunque storico recente.',
            'advice' => '✅ CONSIGLIATO: 5-10 revisioni sono sufficienti.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🌐 CDN & EXTERNAL
        // ═══════════════════════════════════════════════════════════
        
        'cdn_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita CDN per asset statici.',
            'risks' => '✅ Sicuro se configurato correttamente',
            'why_fails' => 'Serve configurazione corretta dell\'URL CDN.',
            'advice' => '✅ CONSIGLIATO: Verifica che l\'URL CDN sia corretto.'
        ],
        
        'edge_cache_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita cache edge (Cloudflare, CloudFront).',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Richiede credenziali API corrette.',
            'advice' => '✅ CONSIGLIATO: Ottimo per siti globali.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🔧 HTACCESS & SERVER
        // ═══════════════════════════════════════════════════════════
        
        'htaccess_caching' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Aggiunge regole di cache al file .htaccess.',
            'risks' => '⚠️ .htaccess malformato può causare errore 500\n⚠️ Su Nginx non funziona',
            'why_fails' => 'Solo per server Apache. Nginx usa configurazione diversa.',
            'advice' => '⚠️ Fai backup .htaccess prima: Verifica tipo server (Apache/Nginx).'
        ],
        
        'htaccess_compression' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Aggiunge regole compressione a .htaccess.',
            'risks' => '⚠️ Errore 500 se mal configurato\n⚠️ Non funziona su Nginx',
            'why_fails' => 'Sintassi .htaccess delicata, errori causano sito down.',
            'advice' => '⚠️ Fai backup prima: Testa su staging se possibile.'
        ],
        
        'force_https' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Forza HTTPS per tutte le richieste.',
            'risks' => '⚠️ Sito inaccessibile se certificato SSL non configurato\n⚠️ Loop di redirect se mal configurato',
            'why_fails' => 'Richiede SSL/TLS configurato correttamente sul server.',
            'advice' => '⚠️ Verifica SSL prima: Assicurati che https:// funzioni prima di forzarlo.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🖼️ FONT OPTIMIZATION
        // ═══════════════════════════════════════════════════════════
        
        'font_preload' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Precarica i font critici.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Migliora font rendering senza effetti negativi.',
            'advice' => '✅ CONSIGLIATO: Precarica solo 1-2 font principali.'
        ],
        
        'preload_critical_fonts' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Precarica i font nel critical rendering path.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Usa <link rel="preload"> standard HTML5.',
            'advice' => '✅ CONSIGLIATO: Migliora FCP e LCP.'
        ],
        
        'preconnect_providers' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Aggiunge preconnect hints per font provider (Google Fonts, ecc.).',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Stabilisce connessioni anticipate ai CDN font.',
            'advice' => '✅ CONSIGLIATO: Riduce latenza caricamento font esterni.'
        ],
        
        'inject_font_display' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Inietta font-display: swap nel CSS.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Previene FOIT (Flash of Invisible Text) mostrando subito testo con font di sistema.',
            'advice' => '✅ CONSIGLIATO: Migliora esperienza utente durante caricamento font.'
        ],
        
        'add_resource_hints' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Aggiunge automaticamente dns-prefetch e preconnect hints.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Resource hints standard che velocizzano connessioni.',
            'advice' => '✅ CONSIGLIATO: Ottimizza caricamento risorse esterne.'
        ],
        
        'critical_path_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita l\'ottimizzazione del critical path per i font.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Applica best practice consolidate per caricamento font.',
            'advice' => '✅ CONSIGLIATO: Riduce drasticamente critical path latency.'
        ],
        
        'font_display_swap' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Usa font-display: swap per evitare testo invisibile.',
            'risks' => '✅ Sicuro - Standard moderno',
            'why_fails' => 'Mostra testo subito con font di sistema, poi passa al custom font.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre per evitare FOIT.'
        ],
        
        'self_host_google_fonts' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Scarica e host Google Fonts localmente.',
            'risks' => '⚠️ Richiede spazio disco\n⚠️ Font potrebbero non aggiornarsi\n⚠️ Privacy OK ma gestione complessa',
            'why_fails' => 'Richiede download iniziale e gestione aggiornamenti.',
            'advice' => '⚠️ Buono per privacy: Considera se la privacy è prioritaria.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📱 MOBILE - OPZIONI AVANZATE
        // ═══════════════════════════════════════════════════════════
        
        'mobile_remove_scripts' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Rimuove script non necessari su mobile.',
            'risks' => '❌ Funzionalità mobile rotte\n❌ Form non funzionano\n❌ Menu hamburger non si apre',
            'why_fails' => 'Difficile determinare quali script sono veramente "non necessari".',
            'advice' => '❌ SCONSIGLIATO: Troppo aggressivo. Meglio defer/async.'
        ],
        
        'touch_optimization' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Ottimizza touch targets e interazioni touch.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Migliora UX mobile senza effetti negativi.',
            'advice' => '✅ CONSIGLIATO: Attiva se hai traffico mobile significativo.'
        ],
        
        'responsive_images' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Serve immagini ottimizzate per dimensione device.',
            'risks' => '✅ Sicuro - Usa srcset nativo HTML5',
            'why_fails' => 'Standard HTML5, supporto universale.',
            'advice' => '✅ CONSIGLIATO: Riduce peso pagina su mobile.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🤖 MACHINE LEARNING & AI
        // ═══════════════════════════════════════════════════════════
        
        'ml_predictor_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Abilita predizioni Machine Learning per ottimizzazioni.',
            'risks' => '⚠️ Richiede MOLTA CPU e RAM\n⚠️ Può causare timeout su shared hosting\n⚠️ Database può crescere',
            'why_fails' => 'Il ML richiede risorse che shared hosting non garantisce.',
            'advice' => '⚠️ Solo VPS/Dedicated: Disabilita su shared hosting.'
        ],
        
        'auto_tuner_enabled' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Permette al sistema di modificare automaticamente le impostazioni.',
            'risks' => '❌ MODIFICHE AUTOMATICHE non supervisionate\n❌ Potrebbe attivare opzioni aggressive\n❌ Difficile capire cosa è cambiato',
            'why_fails' => 'L\'AI potrebbe fare scelte sbagliate per il tuo sito specifico.',
            'advice' => '❌ SCONSIGLIATO: Meglio applicare manualmente i suggerimenti.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🔐 SECURITY - OPZIONI AVANZATE
        // ═══════════════════════════════════════════════════════════
        
        'disable_xmlrpc' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Disabilita XML-RPC (usato per attacchi brute force).',
            'risks' => '✅ Sicuro se non usi Jetpack o app mobile WordPress',
            'why_fails' => 'Jetpack e app WordPress mobile richiedono XML-RPC.',
            'advice' => '✅ CONSIGLIATO: Disabilita se non usi Jetpack/app mobile.'
        ],
        
        'disable_rest_api' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Disabilita completamente REST API WordPress.',
            'risks' => '❌ GUTENBERG NON FUNZIONA\n❌ Plugin moderni rotti\n❌ App/integrazioni falliscono',
            'why_fails' => 'Gutenberg e molti plugin moderni usano REST API.',
            'advice' => '❌ SCONSIGLIATO: Limita accesso invece di disabilitare tutto.'
        ],
        
        'hsts_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Abilita HTTP Strict Transport Security.',
            'risks' => '⚠️ Browser ricorderanno HTTPS per sempre (max-age)\n⚠️ Se rimuovi SSL, sito inaccessibile fino a scadenza',
            'why_fails' => 'HSTS dice al browser "usa SEMPRE https per questo sito".',
            'advice' => '⚠️ Solo con SSL stabile: Assicurati che SSL sia permanente.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🎨 THEME & COMPATIBILITY
        // ═══════════════════════════════════════════════════════════
        
        'salient_optimizer' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Ottimizzazioni specifiche per tema Salient.',
            'risks' => '✅ Sicuro - Testato con Salient',
            'why_fails' => 'Ottimizzazioni conservative studiate per Salient.',
            'advice' => '✅ CONSIGLIATO: Attiva se usi Salient + WPBakery.'
        ],
        
        'wpbakery_optimizer' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Ottimizza caricamento WPBakery/Visual Composer.',
            'risks' => '⚠️ Editor potrebbe rallentare\n⚠️ Alcuni elementi potrebbero non renderizzare subito',
            'why_fails' => 'WPBakery carica molti asset, ottimizzarli può causare ritardi.',
            'advice' => '⚠️ Testa in editor: Verifica che tutte le funzionalità editor funzionino.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📊 MONITORING & REPORTS
        // ═══════════════════════════════════════════════════════════
        
        'performance_monitoring' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Monitora metriche di performance.',
            'risks' => '✅ Solo lettura - Nessun impatto sul sito',
            'why_fails' => 'Raccoglie solo dati, non modifica nulla.',
            'advice' => '✅ CONSIGLIATO: Utile per identificare problemi.'
        ],
        
        'scheduled_reports' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Invia report automatici via email.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Solo notifiche, non modifica il sito.',
            'advice' => '✅ OK: Utile per monitoraggio proattivo.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🔄 PWA & SERVICE WORKER
        // ═══════════════════════════════════════════════════════════
        
        'pwa_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Abilita Progressive Web App con Service Worker.',
            'risks' => '⚠️ Cache aggressiva può servire contenuto vecchio\n⚠️ Aggiornamenti sito potrebbero non apparire\n⚠️ Difficile fare debug',
            'why_fails' => 'Service Worker cache in modo aggressivo, difficile svuotare.',
            'advice' => '⚠️ Usa con cautela: Richiede strategia cache ben pensata.'
        ],
        
        'offline_mode' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Permette navigazione offline con contenuto cached.',
            'risks' => '⚠️ Utenti vedono contenuto vecchio offline\n⚠️ Form non funzionano offline',
            'why_fails' => 'Offline = nessuna connessione al server.',
            'advice' => '⚠️ OK per blog/contenuti: Non usare per e-commerce/form critici.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📡 HTTP/2 & ADVANCED
        // ═══════════════════════════════════════════════════════════
        
        'http2_push' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Usa HTTP/2 Server Push per asset critici.',
            'risks' => '⚠️ Può peggiorare performance se mal configurato\n⚠️ Push di asset già in cache spreca banda',
            'why_fails' => 'HTTP/2 push è complicato, facile pushare troppo.',
            'advice' => '⚠️ Avanzato: Richiede comprensione profonda di HTTP/2.'
        ],
        
        'preconnect' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Preconnect a domini esterni (fonts, CDN).',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Stabilisce connessioni in anticipo, nessun effetto negativo.',
            'advice' => '✅ CONSIGLIATO: Ottimizza caricamento risorse esterne.'
        ],
        
        'dns_prefetch' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'DNS prefetch per domini esterni.',
            'risks' => '✅ Sicuro',
            'why_fails' => 'Risolve DNS in anticipo, nessun rischio.',
            'advice' => '✅ CONSIGLIATO: Velocizza risorse di terze parti.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📊 THIRD-PARTY SCRIPTS
        // ═══════════════════════════════════════════════════════════
        
        'delay_third_party' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Ritarda caricamento script di terze parti (GA, FB Pixel, ecc.).',
            'risks' => '⚠️ Analytics potrebbero perdere prime pageview\n⚠️ Pixel conversione potrebbero non tracciare\n⚠️ Chat widget ritardati',
            'why_fails' => 'Alcuni tracking richiedono caricamento immediato.',
            'advice' => '⚠️ OK per la maggior parte: Ma verifica tracking funzioni.'
        ],
        
        'delay_all_scripts' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Ritarda TUTTI gli script fino a interazione utente.',
            'risks' => '❌ NESSUNO SCRIPT FUNZIONA all\'inizio\n❌ Menu dropdown rotti\n❌ Slider fermi\n❌ Form non validano',
            'why_fails' => 'Ritardare TUTTO è troppo aggressivo.',
            'advice' => '❌ SCONSIGLIATO: Usa esclusioni selettive invece.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📊 THIRD-PARTY SCRIPTS - GENERAL
        // ═══════════════════════════════════════════════════════════
        
        'third_party_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita gestione script di terze parti.',
            'risks' => '✅ Sicuro - Solo attiva il sistema di gestione',
            'why_fails' => 'Non modifica script, solo abilita controlli.',
            'advice' => '✅ CONSIGLIATO: Attiva per controllare script esterni.'
        ],
        
        'third_party_auto_detect' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rileva automaticamente script di terze parti.',
            'risks' => '✅ Sicuro - Solo analisi',
            'why_fails' => 'Non modifica nulla, solo identifica script.',
            'advice' => '✅ CONSIGLIATO: Utile per capire cosa carica il sito.'
        ],
        
        'third_party_delay_loading' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Ritarda caricamento script di terze parti fino a interazione.',
            'risks' => '⚠️ Analytics potrebbero perdere prime pageview\n⚠️ Chat widget ritardati\n⚠️ Pixel conversione potrebbero non tracciare subito',
            'why_fails' => 'Alcuni tracking richiedono caricamento immediato per funzionare.',
            'advice' => '⚠️ OK per la maggior parte: Ma verifica che tracking critici funzionino.'
        ],
        
        'third_party_exclude_critical' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Esclude script critici dal ritardo.',
            'risks' => '✅ Sicuro - Protegge funzionalità critiche',
            'why_fails' => 'Permette esclusioni per script importanti.',
            'advice' => '✅ CONSIGLIATO: Usa sempre con delay loading.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🧹 CLEANUP & MAINTENANCE
        // ═══════════════════════════════════════════════════════════
        
        'cleanup_comments' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Elimina commenti approvati più vecchi di X giorni.',
            'risks' => '⚠️ ELIMINAZIONE PERMANENTE commenti reali\n⚠️ Non recuperabile',
            'why_fails' => 'I commenti sono contenuto utente.',
            'advice' => '⚠️ Backup prima: Usa solo se vuoi eliminare commenti vecchi.'
        ],
        
        'cleanup_unapproved' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rimuove commenti in attesa di moderazione.',
            'risks' => '✅ Sicuro se gestisci commenti regolarmente',
            'why_fails' => 'Rimuove solo commenti non ancora approvati.',
            'advice' => '✅ OK: Ma controlla prima che non ci siano commenti legittimi da approvare.'
        ],
        
    ];
    
    /**
     * Ottiene le informazioni di rischio per un'opzione
     * 
     * @param string $option_key Chiave dell'opzione
     * @return array|null Info rischio o null se non trovata
     */
    public static function getRisk(string $option_key): ?array
    {
        return self::$matrix[$option_key] ?? null;
    }
    
    /**
     * Ottiene il livello di rischio per un'opzione
     * 
     * @param string $option_key Chiave dell'opzione
     * @return string green|amber|red
     */
    public static function getRiskLevel(string $option_key): string
    {
        $risk = self::getRisk($option_key);
        return $risk['risk'] ?? self::RISK_AMBER; // Default: medio se non trovato
    }
    
    /**
     * Renderizza l'indicatore di rischio per un'opzione
     * 
     * @param string $option_key Chiave dell'opzione
     * @param bool $echo Se true, stampa direttamente
     * @return string HTML dell'indicatore
     */
    public static function renderIndicator(string $option_key, bool $echo = false): string
    {
        $risk = self::getRisk($option_key);
        
        if (!$risk) {
            // Opzione non in matrice, usa default medio
            $risk = [
                'risk' => self::RISK_AMBER,
                'title' => 'Rischio Medio',
                'description' => 'Testa questa opzione prima di usarla in produzione.',
                'risks' => 'Potrebbe causare problemi su alcuni siti.',
                'why_fails' => 'Non ancora classificato nella matrice di rischio.',
                'advice' => 'Testa su staging prima di attivare.'
            ];
        }
        
        $level = $risk['risk'];
        $icon = $level === self::RISK_GREEN ? '✓' : ($level === self::RISK_RED ? '🔴' : '⚠');
        
        ob_start();
        ?>
        <span class="fp-ps-risk-indicator <?php echo esc_attr($level); ?>">
            <div class="fp-ps-risk-tooltip <?php echo esc_attr($level); ?>">
                <div class="fp-ps-risk-tooltip-title">
                    <span class="icon"><?php echo $icon; ?></span>
                    <?php echo esc_html($risk['title']); ?>
                </div>
                <?php if (!empty($risk['description'])) : ?>
                <div class="fp-ps-risk-tooltip-section">
                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                    <div class="fp-ps-risk-tooltip-text"><?php echo esc_html($risk['description']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($risk['risks'])) : ?>
                <div class="fp-ps-risk-tooltip-section">
                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi Concreti', 'fp-performance-suite'); ?></div>
                    <div class="fp-ps-risk-tooltip-text"><?php echo esc_html($risk['risks']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($risk['why_fails'])) : ?>
                <div class="fp-ps-risk-tooltip-section">
                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Perché Fallisce', 'fp-performance-suite'); ?></div>
                    <div class="fp-ps-risk-tooltip-text"><?php echo esc_html($risk['why_fails']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($risk['advice'])) : ?>
                <div class="fp-ps-risk-tooltip-section">
                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                    <div class="fp-ps-risk-tooltip-text"><?php echo esc_html($risk['advice']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </span>
        <?php
        
        $html = ob_get_clean();
        
        if ($echo) {
            echo $html;
        }
        
        return $html;
    }
    
    /**
     * Ottiene tutte le opzioni per livello di rischio
     * 
     * @param string $level green|amber|red
     * @return array Array di chiavi opzioni
     */
    public static function getOptionsByRisk(string $level): array
    {
        $options = [];
        foreach (self::$matrix as $key => $data) {
            if ($data['risk'] === $level) {
                $options[] = $key;
            }
        }
        return $options;
    }
    
    /**
     * Conta opzioni per livello di rischio
     * 
     * @return array ['green' => N, 'amber' => N, 'red' => N]
     */
    public static function countByRisk(): array
    {
        $counts = [
            self::RISK_GREEN => 0,
            self::RISK_AMBER => 0,
            self::RISK_RED => 0,
        ];
        
        foreach (self::$matrix as $data) {
            $counts[$data['risk']]++;
        }
        
        return $counts;
    }
}

