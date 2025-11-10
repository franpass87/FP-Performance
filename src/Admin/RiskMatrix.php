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
        
        // BUGFIX: Rinominata da 'page_cache_enabled' a 'page_cache' per matchare Cache.php riga 400
        'page_cache' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita la cache delle pagine statiche per velocizzare il caricamento.',
            'risks' => '✅ Nessun rischio significativo',
            'why_fails' => 'Funziona con la maggior parte dei siti. Raramente causa problemi.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Escludi contenuti dinamici se necessario.'
        ],
        
        // BUGFIX: Rinominata da 'prefetch_enabled' a 'predictive_prefetch' per matchare Cache.php riga 483
        'predictive_prefetch' => [
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
        
        // BUGFIX: Aggiunta chiave mancante usata in Cache.php riga 582
        'cache_rules' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Applica regole .htaccess ottimizzate per il caching dei file statici.',
            'risks' => '✅ Sicuro - Regole standard',
            'why_fails' => 'Funziona su tutti i server Apache. Non funziona su Nginx.',
            'advice' => '✅ CONSIGLIATO: Attiva se usi Apache. Verifica tipo server prima.'
        ],
        
        // BUGFIX: Aggiunta chiave mancante usata in Cache.php riga 594
        'html_cache' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Abilita cache per file HTML.',
            'risks' => '❌ Contenuti dinamici non si aggiornano\n❌ Form potrebbero non funzionare\n❌ Contenuti personalizzati spariscono',
            'why_fails' => 'L\'HTML contiene spesso contenuti dinamici (utente loggato, carrello, ecc.).',
            'advice' => '❌ SCONSIGLIATO: Meglio usare Page Cache invece. Cache HTML diretto è troppo aggressivo.'
        ],
        
        // BUGFIX: Aggiunta chiave mancante usata in Cache.php riga 603
        'fonts_cache' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Cache per font (woff2, woff, ttf, otf) con durata 1 anno.',
            'risks' => '✅ Sicurissimo',
            'why_fails' => 'I font cambiano raramente. Cache lungo termine è standard.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Riduce richieste HTTP per font.'
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
        
        // BUGFIX #26: Rimosso duplicato (definizione corretta in sezione CSS OPTIMIZATION più sotto)
        
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
        
        // BUGFIX: Aggiunta chiave mancante usata in Database.php riga 402
        'database_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita ottimizzazioni database (cleanup, optimize, query monitor).',
            'risks' => '✅ Sicuro - Solo attiva il sistema',
            'why_fails' => 'Non modifica nulla finché non attivi operazioni specifiche.',
            'advice' => '✅ CONSIGLIATO: Attiva per accedere alle funzionalità database.'
        ],
        
        // BUGFIX: Aggiunta chiave mancante usata in Database.php riga 467
        'query_monitor' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Monitora le query database per identificare quelle lente.',
            'risks' => '✅ Solo lettura - Nessun impatto',
            'why_fails' => 'Raccoglie statistiche senza modificare il database.',
            'advice' => '✅ CONSIGLIATO: Utile per ottimizzare performance database.'
        ],
        
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
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Compressione moderna 20-30% più efficiente di Gzip.',
            'risks' => '✅ Sicuro - Fallback automatico a GZIP se non supportato\n✅ Supportato da >95% browser moderni',
            'why_fails' => 'Funziona su tutti i server moderni. Fallback graceful a GZIP se non disponibile.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Migliora compression senza rischi.'
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
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Disabilita animazioni CSS su mobile per migliorare performance e battery.',
            'risks' => '✅ Sicuro - Migliora performance fino al 40% su mobile lento\n⚠️ Minimo impatto estetico (no transizioni smooth)',
            'why_fails' => 'Funziona perfettamente. Impatto solo estetico, non funzionale.',
            'advice' => '✅ CONSIGLIATO: Attiva su mobile. Dispositivi lenti e battery life ne beneficiano enormemente.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // ⚙️ BACKEND
        // ═══════════════════════════════════════════════════════════
        
        // BUGFIX #26: Rimosso duplicato (definizione in sezione MAIN TOGGLES più sotto)
        
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
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Rimuove automaticamente codice JavaScript non utilizzato dai bundle.',
            'risks' => '⚠️ Può rimuovere codice caricato dinamicamente\n⚠️ Problemi con import() dinamici\n⚠️ Side effects potrebbero essere rimossi',
            'why_fails' => 'L\'analisi statica non vede codice caricato via import(), eval() o altre tecniche dinamiche.',
            'advice' => '⚠️ TESTA: Richiede test approfondito. Può rompere plugin che usano import dinamici.'
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
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Converte immagini in formato WebP (30-50% più leggere).',
            'risks' => '✅ Sicuro - Supporto >97% browser (2025)\n✅ Fallback automatico a JPEG/PNG per browser vecchi',
            'why_fails' => 'Funziona su tutti i browser moderni. Fallback automatico per IE e Safari <14.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre. Riduce peso immagini drasticamente senza perdita qualità visibile.'
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
        
        // BUGFIX #26: Rimosso duplicato (definizione corretta in sezione SECURITY più sotto)
        
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
        
        // BUGFIX #20: Corretto da AMBER a RED - HTTP/2 Push è deprecato e rimosso dai browser moderni
        'http2_push' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'HTTP/2 Server Push - DEPRECATO e non più supportato.',
            'risks' => '❌ DEPRECATO: Rimosso da Chrome 106+ (2022) e Firefox 132+ (2024)\n❌ NON funziona più sui browser moderni\n❌ Può PEGGIORARE performance invece di migliorarle\n⚠️ Spreca banda pushando asset già in cache',
            'why_fails' => 'Tecnologia deprecata e rimossa dai browser moderni. Inefficiente e controproducente.',
            'advice' => '❌ NON USARE: Usa preload hints o HTTP 103 Early Hints invece. HTTP/2 Push è morto.'
        ],
        
        // BUGFIX #20: Corretto da AMBER a RED
        'http2_push_enabled' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Attiva HTTP/2 Server Push - SCONSIGLIATO.',
            'risks' => '❌ DEPRECATO: Chrome 106+ e Firefox 132+ NON supportano più HTTP/2 Push\n❌ NON funziona sui browser moderni (95%+ utenti)\n❌ Può peggiorare performance invece di migliorarle\n❌ Spreca banda e CPU del server',
            'why_fails' => 'Browser moderni hanno RIMOSSO il supporto. Tecnologia morta dal 2022.',
            'advice' => '❌ NON ATTIVARE: Usa <link rel="preload"> o HTTP 103 Early Hints. HTTP/2 Push è obsoleto.'
        ],
        
        // BUGFIX #20: Corretto da AMBER a RED
        'http2_push_css' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Push dei file CSS - NON funziona più.',
            'risks' => '❌ HTTP/2 Push rimosso da Chrome e Firefox\n❌ NON funziona sui browser moderni\n⚠️ CSS già in cache viene scaricato comunque (spreco)',
            'why_fails' => 'Browser moderni hanno rimosso il supporto HTTP/2 Push.',
            'advice' => '❌ NON USARE: Usa <link rel="preload" as="style"> invece. Funziona ovunque.'
        ],
        
        // BUGFIX #20: Corretto da AMBER a RED
        'http2_push_js' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Push dei file JavaScript - DEPRECATO.',
            'risks' => '❌ HTTP/2 Push non supportato da Chrome/Firefox moderni\n❌ NON funziona per 95%+ utenti\n⚠️ JS già in cache viene scaricato comunque',
            'why_fails' => 'Tecnologia rimossa dai browser. Preload è l\'alternativa corretta.',
            'advice' => '❌ NON USARE: Usa <link rel="modulepreload"> o defer/async. HTTP/2 Push è morto.'
        ],
        
        // BUGFIX #20: Corretto da GREEN a RED - anche font push non funziona più
        'http2_push_fonts' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Push dei font - DEPRECATO come tutto HTTP/2 Push.',
            'risks' => '❌ HTTP/2 Push rimosso da Chrome 106+ e Firefox 132+\n❌ NON funziona sui browser moderni\n⚠️ Font già in cache vengono scaricati comunque',
            'why_fails' => 'HTTP/2 Push è stato completamente rimosso dai browser moderni.',
            'advice' => '❌ NON USARE: Usa <link rel="preload" as="font" crossorigin> invece. Funziona perfettamente.'
        ],
        
        // BUGFIX #20: Corretto da AMBER a RED
        'http2_push_images' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Push di immagini - NON supportato più.',
            'risks' => '❌ HTTP/2 Push rimosso dai browser moderni\n❌ NON funziona su Chrome 106+ e Firefox 132+\n⚠️ Immagini pesanti rallentano tutto\n⚠️ Spreca banda enorme',
            'why_fails' => 'Browser non supportano più HTTP/2 Push. Preload è meglio.',
            'advice' => '❌ NON USARE: Usa <link rel="preload" as="image"> o fetchpriority="high" invece.'
        ],
        
        // BUGFIX #26: Corretto da GREEN a RED - HTTP/2 Push è deprecato anche se "critical only"
        'http2_critical_only' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Push solo risorse critiche - MA HTTP/2 Push è DEPRECATO.',
            'risks' => '❌ HTTP/2 Push rimosso da Chrome 106+ e Firefox 132+\n❌ NON funziona anche se limitato a "critical only"\n❌ Spreca CPU e banda del server\n❌ NON supportato da 95%+ browser moderni',
            'why_fails' => 'HTTP/2 Push completamente rimosso dai browser. Anche limitato a "critical" non funziona.',
            'advice' => '❌ NON USARE: Usa <link rel="preload"> invece. HTTP/2 Push è morto, anche "critical only".'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🎯 SMART ASSET DELIVERY
        // ═══════════════════════════════════════════════════════════
        
        'smart_delivery_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita consegna intelligente degli asset basata su connessione.',
            'risks' => '✅ Ottimizza automaticamente qualità in base alla rete',
            'why_fails' => 'Migliora UX su connessioni lente senza rischi.',
            'advice' => '✅ CONSIGLIATO: Perfetto per siti con utenti mobile.'
        ],
        
        'smart_detect_connection' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rileva automaticamente velocità di connessione.',
            'risks' => '✅ Sicuro - Usa API browser standard',
            'why_fails' => 'Network Information API supportata da browser moderni.',
            'advice' => '✅ CONSIGLIATO: Adatta asset a velocità reale utente.'
        ],
        
        'smart_save_data_mode' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Rispetta modalità "risparmio dati" del browser.',
            'risks' => '✅ Migliora esperienza utenti con dati limitati',
            'why_fails' => 'Header Save-Data è uno standard.',
            'advice' => '✅ CONSIGLIATO: Rispetta scelte utente, riduce consumi.'
        ],
        
        'smart_adaptive_images' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Adatta qualità immagini in base a connessione.',
            'risks' => '✅ Immagini leggere su 3G, alta qualità su WiFi\n⚠️ Leggerissima riduzione qualità su connessioni lente',
            'why_fails' => 'Trade-off qualità/velocità controllato.',
            'advice' => '✅ CONSIGLIATO: Migliora caricamento su mobile.'
        ],
        
        'smart_adaptive_videos' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Adatta qualità video in base a connessione.',
            'risks' => '✅ Previene buffering su connessioni lente\n⚠️ Qualità ridotta su 3G (intenzionale)',
            'why_fails' => 'Streaming adattivo è lo standard.',
            'advice' => '✅ CONSIGLIATO: Essenziale per video su mobile.'
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
        
        // ═══════════════════════════════════════════════════════════
        // 🔒 SECURITY & .HTACCESS
        // ═══════════════════════════════════════════════════════════
        
        'security_htaccess_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita ottimizzazioni di sicurezza via .htaccess.',
            'risks' => '✅ Sicuro - Solo attiva il sistema',
            'why_fails' => 'Non modifica nulla finché non attivi opzioni specifiche.',
            'advice' => '✅ CONSIGLIATO: Attiva per accedere alle ottimizzazioni.'
        ],
        
        'canonical_redirect_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Unifica HTTP/HTTPS e WWW/non-WWW.',
            'risks' => '✅ Previene redirect multipli\n⚠️ Assicurati di configurare dominio corretto',
            'why_fails' => 'Migliora SEO eliminando contenuti duplicati.',
            'advice' => '✅ CONSIGLIATO: Ottimo per SEO, verifica dominio corretto.'
        ],
        
        // BUGFIX #26: Corretto da GREEN a AMBER - Richiede SSL come prerequisito (come hsts_enabled)
        'force_https' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Forza HTTPS su tutto il sito.',
            'risks' => '⚠️ RICHIEDE certificato SSL valido e funzionante\n⚠️ Sito INACCESSIBILE se SSL non configurato\n⚠️ Loop di redirect se SSL mal configurato\n⚠️ Verifica che https:// funzioni PRIMA di attivare',
            'why_fails' => 'Redirect HTTP → HTTPS fallisce senza SSL configurato correttamente sul server.',
            'advice' => '⚠️ VERIFICA SSL PRIMA: Assicurati che https://tuosito.it funzioni perfettamente, poi attiva. Essenziale per sicurezza ma richiede setup SSL.'
        ],
        
        'force_www' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Forza WWW davanti al dominio.',
            'risks' => '✅ Unifica versioni del sito\n⚠️ Scegli una sola versione (WWW o non-WWW)',
            'why_fails' => 'Migliora SEO unificando sotto un dominio.',
            'advice' => '✅ OK: Scegli una versione e mantienila sempre.'
        ],
        
        'cors_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita CORS per font e SVG da CDN.',
            'risks' => '✅ Necessario per font da CDN\n✅ Previene errori CORS',
            'why_fails' => 'Header CORS standard per risorse cross-origin.',
            'advice' => '✅ CONSIGLIATO: Attiva se usi CDN per font/SVG.'
        ],
        
        'security_headers_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita Security Headers (HSTS, X-Frame-Options, ecc.).',
            'risks' => '✅ Migliora sicurezza\n✅ Aumenta punteggio security scanner',
            'why_fails' => 'Header standard raccomandati da OWASP.',
            'advice' => '✅ CONSIGLIATO: Protegge da XSS, clickjacking, MIME sniffing.'
        ],
        
        'hsts_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'HTTP Strict Transport Security - Forza HTTPS via browser.',
            'risks' => '⚠️ PERMANENTE per durata max-age\n⚠️ Richiede certificato SSL sempre valido\n⚠️ Se SSL scade, sito inaccessibile',
            'why_fails' => 'HSTS è permanente nel browser finché non scade.',
            'advice' => '⚠️ OK: Ma assicurati che SSL sia sempre valido e auto-rinnovato.'
        ],
        
        'hsts_subdomains' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Applica HSTS anche ai sottodomini.',
            'risks' => '⚠️ TUTTI i sottodomini richiedono HTTPS\n⚠️ Se un sottodominio non ha SSL, sarà inaccessibile',
            'why_fails' => 'Estende HSTS a sottodomini che potrebbero non avere SSL.',
            'advice' => '⚠️ Attento: Attiva solo se TUTTI i sottodomini hanno SSL valido.'
        ],
        
        'hsts_preload' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Aggiunge dominio alla lista preload HSTS dei browser.',
            'risks' => '❌ PERMANENTE NEL BROWSER\n❌ Rimozione richiede mesi\n❌ Se SSL si rompe, sito inaccessibile a TUTTI',
            'why_fails' => 'Preload è una scelta quasi irrevocabile.',
            'advice' => '❌ PERICOLOSO: Usa solo se sei assolutamente sicuro del tuo setup SSL.'
        ],
        
        'x_content_type_options' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Previene MIME-type sniffing.',
            'risks' => '✅ Nessun rischio\n✅ Migliora sicurezza',
            'why_fails' => 'Header standard di sicurezza.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre per sicurezza.'
        ],
        
        'file_protection_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Protegge file sensibili da accesso diretto.',
            'risks' => '✅ Blocca accesso a .env, .git, wp-config.php',
            'why_fails' => 'Previene furto di credenziali e informazioni sensibili.',
            'advice' => '✅ CONSIGLIATO: Essenziale per sicurezza.'
        ],
        
        'protect_hidden_files' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Blocca accesso a file nascosti (.env, .git, ecc.).',
            'risks' => '✅ Previene esposizione file sensibili',
            'why_fails' => 'File nascosti contengono spesso credenziali.',
            'advice' => '✅ CONSIGLIATO: Protezione essenziale.'
        ],
        
        'protect_wp_config' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Blocca accesso diretto a wp-config.php.',
            'risks' => '✅ Protegge credenziali database',
            'why_fails' => 'wp-config.php contiene credenziali sensibili.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre.'
        ],
        
        'xmlrpc_disabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Disabilita XML-RPC per prevenire attacchi brute-force e DDoS.',
            'risks' => '✅ Sicurissimo - Elimina vettore attacco comune\n⚠️ Solo Jetpack e app WordPress (pre-2016) ne hanno bisogno',
            'why_fails' => 'Funziona perfettamente. Solo Jetpack e vecchie app mobile WordPress richiedono XML-RPC.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre a meno che non usi Jetpack. Previene migliaia di attacchi brute force.'
        ],
        
        'hotlink_protection_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Previene uso immagini da altri siti.',
            'risks' => '✅ Risparmia banda\n⚠️ Permetti Google Images',
            'why_fails' => 'Blocca riferimenti da domini non autorizzati.',
            'advice' => '✅ CONSIGLIATO: Risparmia banda se hai sito con molte immagini.'
        ],
        
        'hotlink_allow_google' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Permette a Google di indicizzare immagini.',
            'risks' => '✅ Mantiene SEO immagini',
            'why_fails' => 'Google deve accedere alle immagini per indicizzarle.',
            'advice' => '✅ CONSIGLIATO: Attiva sempre se usi hotlink protection.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 📦 UNUSED CSS
        // ═══════════════════════════════════════════════════════════
        
        'unusedcss_enabled' => [
            'risk' => self::RISK_AMBER,
            'title' => 'Rischio Medio',
            'description' => 'Abilita sistema di rimozione CSS non utilizzato.',
            'risks' => '⚠️ Richiede configurazione attenta\n⚠️ Può rompere layout se mal configurato',
            'why_fails' => 'L\'analisi CSS non usato è complessa.',
            'advice' => '⚠️ Avanzato: Testa molto bene prima di attivare.'
        ],
        
        'unusedcss_remove_unused' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio MOLTO Alto',
            'description' => 'Rimuove completamente CSS identificato come non usato.',
            'risks' => '❌ LOGO SCOMPARE\n❌ MENU ROTTO\n❌ FOOTER DISTRUTTO\n❌ Layout mobile rotto\n❌ Stati hover scompaiono',
            'why_fails' => 'Lighthouse analizza solo homepage. CSS per menu, hover, mobile viene rimosso.',
            'advice' => '❌ SCONSIGLIATO: Troppo aggressivo. Usa defer invece.'
        ],
        
        'unusedcss_defer_non_critical' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Ritarda caricamento CSS non critico.',
            'risks' => '❌ FOUC pesante (flash contenuto senza stile)\n❌ LOGO appare dopo secondi\n❌ MENU "salta"\n❌ Esperienza utente pessima',
            'why_fails' => 'Classifica erroneamente header/menu come "non critici".',
            'advice' => '❌ SCONSIGLIATO: Meglio Critical CSS inline.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🎨 CSS OPTIMIZATION
        // ═══════════════════════════════════════════════════════════
        
        'combine_css' => [
            'risk' => self::RISK_RED,
            'title' => 'Rischio Alto',
            'description' => 'Combina tutti i CSS in un unico file.',
            'risks' => '❌ Layout completamente rotto\n❌ Media queries non funzionano\n❌ CSS specificity rotta\n❌ Ordine caricamento errato',
            'why_fails' => 'Combinare CSS cambia ordine e contesto di caricamento.',
            'advice' => '❌ SCONSIGLIATO: HTTP/2 rende questo inutile e pericoloso.'
        ],
        
        // ═══════════════════════════════════════════════════════════
        // 🎛️ MAIN TOGGLES
        // ═══════════════════════════════════════════════════════════
        
        'assets_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita ottimizzazioni Asset (CSS, JS, Font).',
            'risks' => '✅ Sicuro - Solo attiva il sistema',
            'why_fails' => 'Non modifica nulla finché non attivi opzioni specifiche.',
            'advice' => '✅ CONSIGLIATO: Attiva per accedere alle ottimizzazioni asset.'
        ],
        
        'backend_enabled' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Abilita ottimizzazioni Backend WordPress.',
            'risks' => '✅ Sicuro - Solo attiva il sistema',
            'why_fails' => 'Non modifica nulla finché non attivi opzioni specifiche.',
            'advice' => '✅ CONSIGLIATO: Attiva per accedere alle ottimizzazioni backend.'
        ],
        
        'disable_admin_bar_frontend' => [
            'risk' => self::RISK_GREEN,
            'title' => 'Rischio Basso',
            'description' => 'Nascondi barra admin sul frontend per utenti loggati.',
            'risks' => '✅ Sicuro\n⚠️ Solo nasconde visivamente, non rimuove funzionalità',
            'why_fails' => 'Utenti admin potrebbero preferire la barra.',
            'advice' => '✅ OK: Migliora UX rimuovendo elemento inutile dal frontend.'
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
