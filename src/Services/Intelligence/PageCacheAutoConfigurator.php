<?php

namespace FP\PerfSuite\Services\Intelligence;

use FP\PerfSuite\Utils\Logger;

/**
 * Page Cache Auto Configurator
 * 
 * Sistema intelligente che rileva, consiglia e aggiunge automaticamente:
 * - URL sensibili da escludere dalla cache
 * - URL importanti per il cache warming
 * - Parametri query da escludere
 * - TTL ottimale basato sul tipo di sito
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PageCacheAutoConfigurator
{
    /**
     * Parametri query comuni da escludere
     */
    private const COMMON_QUERY_PARAMS = [
        // Tracking & Analytics
        'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
        'fbclid', 'gclid', 'msclkid', 'mc_cid', 'mc_eid',
        '_ga', '_gl', 'ref', 'referrer',
        
        // Social
        'fb_action_ids', 'fb_action_types', 'fb_source',
        'igshid', 'share',
        
        // E-commerce & Actions
        'add-to-cart', 'remove_item', 'update_cart',
        'added-to-cart', 'removed-from-cart',
        
        // WordPress
        'preview', 'preview_id', 'preview_nonce',
        's', // search query
    ];

    /**
     * Pattern URL sensibili comuni
     */
    private const SENSITIVE_URL_PATTERNS = [
        // E-commerce
        '/cart', '/checkout', '/order', '/payment', '/billing',
        '/add-to-cart', '/remove-from-cart', '/update-cart',
        '/wc-ajax', '/edd-ajax',
        
        // User areas
        '/account', '/profile', '/dashboard', '/admin',
        '/login', '/logout', '/register', '/signup',
        '/my-account', '/user', '/member',
        
        // Forms & Actions
        '/contact', '/submit', '/form', '/action',
        '/wp-admin', '/wp-login', '/wp-json',
        
        // Dynamic content
        '/search', '/filter', '/ajax', '/api',
        '/preview', '/customize',
    ];

    /**
     * SmartExclusionDetector per rilevamento avanzato
     */
    private SmartExclusionDetector $exclusionDetector;

    public function __construct(SmartExclusionDetector $exclusionDetector)
    {
        $this->exclusionDetector = $exclusionDetector;
    }

    /**
     * Analizza il sito e genera suggerimenti completi
     */
    public function analyzeSite(): array
    {
        Logger::info('PageCacheAutoConfigurator: Inizio analisi sito');

        $suggestions = [
            'exclude_urls' => $this->detectUrlsToExclude(),
            'warming_urls' => $this->detectUrlsToWarm(),
            'exclude_query_params' => $this->detectQueryParamsToExclude(),
            'optimal_ttl' => $this->suggestOptimalTTL(),
            'cache_enabled' => $this->shouldEnableCache(),
            'warming_enabled' => $this->shouldEnableWarming(),
            'warming_schedule' => $this->suggestWarmingSchedule(),
        ];

        Logger::info('PageCacheAutoConfigurator: Analisi completata', [
            'exclude_urls_count' => count($suggestions['exclude_urls']),
            'warming_urls_count' => count($suggestions['warming_urls']),
            'query_params_count' => count($suggestions['exclude_query_params']),
        ]);

        // Salva i suggerimenti
        update_option('fp_ps_page_cache_suggestions', [
            'suggestions' => $suggestions,
            'generated_at' => time(),
        ]);

        return $suggestions;
    }

    /**
     * Ottieni suggerimenti salvati o genera nuovi
     */
    public function getSuggestions(bool $forceRefresh = false): array
    {
        if (!$forceRefresh) {
            $saved = get_option('fp_ps_page_cache_suggestions', []);
            
            // Se i suggerimenti sono recenti (< 24h), usali
            if (!empty($saved['suggestions']) && !empty($saved['generated_at'])) {
                if (time() - $saved['generated_at'] < DAY_IN_SECONDS) {
                    return $saved['suggestions'];
                }
            }
        }

        return $this->analyzeSite();
    }

    /**
     * Rileva URL da escludere dalla cache
     */
    private function detectUrlsToExclude(): array
    {
        $exclusions = [];

        // 1. Usa SmartExclusionDetector per rilevamento avanzato
        $detectedUrls = $this->exclusionDetector->detectSensitiveUrls();
        
        foreach ($detectedUrls as $category => $items) {
            foreach ($items as $item) {
                if ($item['confidence'] >= 0.7) {
                    $exclusions[] = [
                        'url' => $item['url'],
                        'reason' => $item['reason'],
                        'confidence' => $item['confidence'],
                        'category' => $category,
                        'source' => 'smart_detector',
                    ];
                }
            }
        }

        // 2. Aggiungi URL standard sempre validi
        foreach (self::SENSITIVE_URL_PATTERNS as $pattern) {
            // Controlla se non è già stato aggiunto
            $exists = false;
            foreach ($exclusions as $existing) {
                if ($existing['url'] === $pattern) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $exclusions[] = [
                    'url' => $pattern,
                    'reason' => $this->getReasonForPattern($pattern),
                    'confidence' => 0.85,
                    'category' => 'standard',
                    'source' => 'builtin',
                ];
            }
        }

        // 3. Rileva da sitemap se disponibile
        $sitemapUrls = $this->detectFromSitemap();
        foreach ($sitemapUrls as $url) {
            $exclusions[] = $url;
        }

        // Rimuovi duplicati
        $exclusions = $this->removeDuplicateUrls($exclusions);

        return $exclusions;
    }

    /**
     * Rileva URL importanti per il cache warming
     */
    private function detectUrlsToWarm(): array
    {
        $warmingUrls = [];

        // 1. Homepage (sempre)
        $warmingUrls[] = [
            'url' => home_url('/'),
            'reason' => __('Homepage - pagina più visitata', 'fp-performance-suite'),
            'priority' => 100,
            'confidence' => 1.0,
        ];

        // 2. Pagine statiche importanti
        $importantPages = $this->getImportantPages();
        foreach ($importantPages as $page) {
            $warmingUrls[] = $page;
        }

        // 3. Pagine più visitate (da analytics se disponibile)
        $popularPages = $this->getPopularPages();
        foreach ($popularPages as $page) {
            $warmingUrls[] = $page;
        }

        // 4. Archive pages principali
        $archivePages = $this->getMainArchivePages();
        foreach ($archivePages as $page) {
            $warmingUrls[] = $page;
        }

        // Ordina per priorità
        usort($warmingUrls, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });

        // Limita a top 10
        return array_slice($warmingUrls, 0, 10);
    }

    /**
     * Rileva parametri query da escludere
     */
    private function detectQueryParamsToExclude(): array
    {
        $params = [];

        // 1. Parametri comuni standard
        foreach (self::COMMON_QUERY_PARAMS as $param) {
            $params[] = [
                'param' => $param,
                'reason' => $this->getReasonForQueryParam($param),
                'confidence' => 0.9,
                'source' => 'builtin',
            ];
        }

        // 2. Rileva da log se disponibili
        $loggedParams = $this->detectQueryParamsFromLogs();
        foreach ($loggedParams as $param) {
            // Aggiungi solo se non esiste già
            $exists = false;
            foreach ($params as $existing) {
                if ($existing['param'] === $param['param']) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $params[] = $param;
            }
        }

        return $params;
    }

    /**
     * Suggerisce TTL ottimale basato sul tipo di sito
     */
    private function suggestOptimalTTL(): array
    {
        $siteType = $this->detectSiteType();

        $ttlSuggestions = [
            'blog' => [
                'ttl' => 86400, // 24 ore
                'reason' => __('I blog hanno contenuti che cambiano raramente', 'fp-performance-suite'),
            ],
            'news' => [
                'ttl' => 3600, // 1 ora
                'reason' => __('Siti di notizie necessitano aggiornamenti frequenti', 'fp-performance-suite'),
            ],
            'ecommerce' => [
                'ttl' => 1800, // 30 minuti
                'reason' => __('E-commerce con prezzi e stock che cambiano', 'fp-performance-suite'),
            ],
            'corporate' => [
                'ttl' => 86400, // 24 ore
                'reason' => __('Siti corporate hanno contenuti statici', 'fp-performance-suite'),
            ],
            'forum' => [
                'ttl' => 300, // 5 minuti
                'reason' => __('Forum con contenuti dinamici generati dagli utenti', 'fp-performance-suite'),
            ],
            'default' => [
                'ttl' => 3600, // 1 ora
                'reason' => __('Valore bilanciato per siti generici', 'fp-performance-suite'),
            ],
        ];

        return $ttlSuggestions[$siteType] ?? $ttlSuggestions['default'];
    }

    /**
     * Determina se la cache dovrebbe essere abilitata
     */
    private function shouldEnableCache(): array
    {
        $serverType = $this->detectServerType();
        
        // Cache molto consigliata per shared hosting
        if ($serverType === 'shared') {
            return [
                'enabled' => true,
                'reason' => __('Hosting condiviso - la cache riduce drasticamente il carico CPU', 'fp-performance-suite'),
                'confidence' => 0.95,
            ];
        }

        // Consigliata per la maggior parte dei casi
        return [
            'enabled' => true,
            'reason' => __('La cache migliora le performance per tutti i visitatori', 'fp-performance-suite'),
            'confidence' => 0.85,
        ];
    }

    /**
     * Determina se il cache warming dovrebbe essere abilitato
     */
    private function shouldEnableWarming(): array
    {
        $trafficLevel = $this->estimateTrafficLevel();

        if ($trafficLevel === 'high') {
            return [
                'enabled' => true,
                'reason' => __('Alto traffico - mantieni le pagine sempre calde', 'fp-performance-suite'),
                'confidence' => 0.9,
            ];
        }

        if ($trafficLevel === 'medium') {
            return [
                'enabled' => true,
                'reason' => __('Traffico medio - migliora esperienza utente', 'fp-performance-suite'),
                'confidence' => 0.75,
            ];
        }

        return [
            'enabled' => false,
            'reason' => __('Traffico basso - il warming potrebbe non essere necessario', 'fp-performance-suite'),
            'confidence' => 0.6,
        ];
    }

    /**
     * Suggerisce la frequenza di cache warming
     */
    private function suggestWarmingSchedule(): array
    {
        $siteType = $this->detectSiteType();

        $schedules = [
            'news' => [
                'schedule' => 'hourly',
                'reason' => __('Contenuti aggiornati frequentemente', 'fp-performance-suite'),
            ],
            'ecommerce' => [
                'schedule' => 'hourly',
                'reason' => __('Prezzi e disponibilità cambiano spesso', 'fp-performance-suite'),
            ],
            'blog' => [
                'schedule' => 'twicedaily',
                'reason' => __('Contenuti aggiornati meno frequentemente', 'fp-performance-suite'),
            ],
            'default' => [
                'schedule' => 'twicedaily',
                'reason' => __('Bilanciamento tra freschezza e carico server', 'fp-performance-suite'),
            ],
        ];

        return $schedules[$siteType] ?? $schedules['default'];
    }

    /**
     * Applica automaticamente i suggerimenti
     */
    public function applyAutoConfiguration(bool $dryRun = false): array
    {
        $suggestions = $this->getSuggestions();
        $results = [
            'applied' => false,
            'changes' => [],
            'errors' => [],
        ];

        if ($dryRun) {
            $results['dry_run'] = true;
            $results['would_apply'] = $suggestions;
            return $results;
        }

        try {
            // Ottieni impostazioni attuali
            $currentSettings = get_option('fp_ps_page_cache', []);

            // Prepara nuove impostazioni
            $newSettings = [
                'enabled' => $suggestions['cache_enabled']['enabled'],
                'ttl' => $suggestions['optimal_ttl']['ttl'],
                'exclude_urls' => $this->formatExcludeUrls($suggestions['exclude_urls']),
                'exclude_query_strings' => $this->formatQueryParams($suggestions['exclude_query_params']),
                'warming_enabled' => $suggestions['warming_enabled']['enabled'],
                'warming_urls' => $this->formatWarmingUrls($suggestions['warming_urls']),
                'warming_schedule' => $suggestions['warming_schedule']['schedule'],
            ];

            // Applica impostazioni
            update_option('fp_ps_page_cache', $newSettings);

            $results['applied'] = true;
            $results['changes'] = [
                'cache_enabled' => $newSettings['enabled'],
                'ttl' => $newSettings['ttl'],
                'exclude_urls_count' => count($suggestions['exclude_urls']),
                'warming_urls_count' => count($suggestions['warming_urls']),
                'query_params_count' => count($suggestions['exclude_query_params']),
            ];

            Logger::info('PageCacheAutoConfigurator: Configurazione applicata con successo', $results['changes']);

            // Salva timestamp applicazione
            update_option('fp_ps_page_cache_auto_applied_at', time());

        } catch (\Throwable $e) {
            $results['errors'][] = $e->getMessage();
            Logger::error('PageCacheAutoConfigurator: Errore applicazione configurazione', $e);
        }

        return $results;
    }

    /**
     * Ottieni pagine importanti dal sito
     */
    private function getImportantPages(): array
    {
        $pages = [];

        // Cerca pagine comuni
        $commonPages = [
            'about' => __('Chi Siamo', 'fp-performance-suite'),
            'contact' => __('Contatti', 'fp-performance-suite'),
            'services' => __('Servizi', 'fp-performance-suite'),
            'products' => __('Prodotti', 'fp-performance-suite'),
        ];

        foreach ($commonPages as $slug => $name) {
            $page = get_page_by_path($slug);
            if ($page && $page->post_status === 'publish') {
                $pages[] = [
                    'url' => get_permalink($page->ID),
                    'reason' => sprintf(__('Pagina importante: %s', 'fp-performance-suite'), $name),
                    'priority' => 80,
                    'confidence' => 0.8,
                ];
            }
        }

        // Aggiungi pagine da menu principale
        $menuPages = $this->getPagesFromMenu();
        foreach ($menuPages as $page) {
            $pages[] = $page;
        }

        return $pages;
    }

    /**
     * Ottieni pagine dal menu principale
     */
    private function getPagesFromMenu(): array
    {
        $pages = [];
        $locations = get_nav_menu_locations();
        
        if (empty($locations)) {
            return $pages;
        }

        // Prendi il primo menu (solitamente primary)
        $menuId = reset($locations);
        $menuItems = wp_get_nav_menu_items($menuId);

        if (!$menuItems) {
            return $pages;
        }

        foreach ($menuItems as $item) {
            if ($item->type === 'post_type' && $item->object === 'page') {
                $pages[] = [
                    'url' => $item->url,
                    'reason' => sprintf(__('Pagina nel menu principale: %s', 'fp-performance-suite'), $item->title),
                    'priority' => 85,
                    'confidence' => 0.85,
                ];
            }
        }

        return array_slice($pages, 0, 5); // Limita a 5
    }

    /**
     * Ottieni pagine più visitate (placeholder - richiederebbe analytics)
     */
    private function getPopularPages(): array
    {
        // TODO: Integrare con Google Analytics o dati interni
        // Per ora, usa i post più recenti come approssimazione
        
        $posts = get_posts([
            'numberposts' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
        ]);

        $pages = [];
        foreach ($posts as $post) {
            $pages[] = [
                'url' => get_permalink($post->ID),
                'reason' => sprintf(__('Post recente: %s', 'fp-performance-suite'), $post->post_title),
                'priority' => 70,
                'confidence' => 0.65,
            ];
        }

        return $pages;
    }

    /**
     * Ottieni pagine archivio principali
     */
    private function getMainArchivePages(): array
    {
        $pages = [];

        // Blog archive
        if (get_option('show_on_front') === 'page') {
            $blogPageId = get_option('page_for_posts');
            if ($blogPageId) {
                $pages[] = [
                    'url' => get_permalink($blogPageId),
                    'reason' => __('Archivio blog', 'fp-performance-suite'),
                    'priority' => 75,
                    'confidence' => 0.85,
                ];
            }
        }

        // Shop page (WooCommerce)
        if (function_exists('wc_get_page_id')) {
            $shopPageId = wc_get_page_id('shop');
            if ($shopPageId > 0) {
                $pages[] = [
                    'url' => get_permalink($shopPageId),
                    'reason' => __('Pagina negozio WooCommerce', 'fp-performance-suite'),
                    'priority' => 90,
                    'confidence' => 0.95,
                ];
            }
        }

        return $pages;
    }

    /**
     * Rileva parametri query dai log
     */
    private function detectQueryParamsFromLogs(): array
    {
        // TODO: Analizzare i log delle richieste per trovare parametri comuni
        // Per ora restituisci array vuoto
        return [];
    }

    /**
     * Rileva URL dalla sitemap
     */
    private function detectFromSitemap(): array
    {
        // TODO: Parse sitemap.xml per trovare URL sensibili
        return [];
    }

    /**
     * Rileva tipo di sito
     */
    private function detectSiteType(): string
    {
        // WooCommerce
        if (class_exists('WooCommerce')) {
            return 'ecommerce';
        }

        // bbPress / BuddyPress
        if (function_exists('is_bbpress') || function_exists('bp_is_active')) {
            return 'forum';
        }

        // Controlla numero di post vs pagine
        $postCount = wp_count_posts('post');
        $pageCount = wp_count_posts('page');

        if ($postCount->publish > $pageCount->publish * 3) {
            return 'blog';
        }

        return 'corporate';
    }

    /**
     * Rileva tipo di server
     */
    private function detectServerType(): string
    {
        // Controlla se è shared hosting (euristica semplice)
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitInt = (int) $memoryLimit;

        if ($memoryLimitInt <= 128) {
            return 'shared';
        }

        return 'vps';
    }

    /**
     * Stima livello di traffico
     */
    private function estimateTrafficLevel(): string
    {
        // TODO: Integrare con analytics o dati interni
        // Per ora usa euristica basata su numero contenuti
        
        $postCount = wp_count_posts('post');
        $pageCount = wp_count_posts('page');
        $totalContent = $postCount->publish + $pageCount->publish;

        if ($totalContent > 100) {
            return 'high';
        } elseif ($totalContent > 20) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Formatta URL da escludere per salvataggio
     */
    private function formatExcludeUrls(array $urls): string
    {
        $formatted = [];
        foreach ($urls as $item) {
            $formatted[] = $item['url'];
        }
        return implode("\n", array_unique($formatted));
    }

    /**
     * Formatta URL per warming per salvataggio
     */
    private function formatWarmingUrls(array $urls): string
    {
        $formatted = [];
        foreach ($urls as $item) {
            $formatted[] = $item['url'];
        }
        return implode("\n", array_unique($formatted));
    }

    /**
     * Formatta parametri query per salvataggio
     */
    private function formatQueryParams(array $params): string
    {
        $formatted = [];
        foreach ($params as $item) {
            $formatted[] = $item['param'];
        }
        return implode(', ', array_unique($formatted));
    }

    /**
     * Rimuovi URL duplicati
     */
    private function removeDuplicateUrls(array $urls): array
    {
        $seen = [];
        $unique = [];

        foreach ($urls as $item) {
            if (!in_array($item['url'], $seen, true)) {
                $seen[] = $item['url'];
                $unique[] = $item;
            }
        }

        return $unique;
    }

    /**
     * Ottieni ragione per pattern URL
     */
    private function getReasonForPattern(string $pattern): string
    {
        $reasons = [
            '/cart' => __('Carrello - contenuto dinamico', 'fp-performance-suite'),
            '/checkout' => __('Checkout - contiene dati sensibili', 'fp-performance-suite'),
            '/account' => __('Area utente - contenuto personalizzato', 'fp-performance-suite'),
            '/login' => __('Login - token CSRF e nonce', 'fp-performance-suite'),
            '/search' => __('Risultati ricerca - contenuto dinamico', 'fp-performance-suite'),
            '/ajax' => __('Endpoint AJAX - sempre dinamico', 'fp-performance-suite'),
        ];

        foreach ($reasons as $key => $reason) {
            if (strpos($pattern, $key) !== false) {
                return $reason;
            }
        }

        return __('URL potenzialmente sensibile', 'fp-performance-suite');
    }

    /**
     * Ottieni ragione per parametro query
     */
    private function getReasonForQueryParam(string $param): string
    {
        if (strpos($param, 'utm_') === 0) {
            return __('Parametro tracking marketing', 'fp-performance-suite');
        }

        if (in_array($param, ['fbclid', 'gclid', 'msclkid'], true)) {
            return __('Parametro tracking social/ads', 'fp-performance-suite');
        }

        if (in_array($param, ['add-to-cart', 'remove_item'], true)) {
            return __('Azione e-commerce', 'fp-performance-suite');
        }

        return __('Parametro che non influenza il contenuto', 'fp-performance-suite');
    }

    /**
     * Ottieni statistiche
     */
    public function getStats(): array
    {
        $suggestions = get_option('fp_ps_page_cache_suggestions', []);
        $appliedAt = get_option('fp_ps_page_cache_auto_applied_at', 0);

        return [
            'has_suggestions' => !empty($suggestions['suggestions']),
            'suggestions_age' => !empty($suggestions['generated_at']) 
                ? time() - $suggestions['generated_at'] 
                : 0,
            'auto_applied' => $appliedAt > 0,
            'applied_at' => $appliedAt,
            'suggestions_count' => [
                'exclude_urls' => count($suggestions['suggestions']['exclude_urls'] ?? []),
                'warming_urls' => count($suggestions['suggestions']['warming_urls'] ?? []),
                'query_params' => count($suggestions['suggestions']['exclude_query_params'] ?? []),
            ],
        ];
    }
}
