<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * External Resource Cache Manager
 * 
 * Gestisce gli header di cache per risorse esterne (JavaScript, CSS, font)
 * per risolvere problemi di TTL inefficienti come mostrato in Lighthouse
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ExternalResourceCacheManager
{
    private const OPTION_KEY = 'fp_ps_external_cache';
    private const DEFAULT_TTL = 31536000; // 1 anno
    private const MIN_TTL = 3600; // 1 ora
    private const MAX_TTL = 31536000 * 2; // 2 anni

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Solo nel frontend
        if (!is_admin()) {
            // Intercetta le richieste per risorse esterne
            add_action('wp_head', [$this, 'addExternalCacheHeaders'], 18);
            add_action('wp_footer', [$this, 'addExternalCacheHeaders'], 30);
        }
        
        // Hook per gestire risorse specifiche - solo nel frontend
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'handleEnqueuedScripts'], 994);
            add_action('wp_enqueue_scripts', [$this, 'handleEnqueuedStyles'], 993);
        }

        Logger::debug('External Resource Cache Manager registered');
    }

    /**
     * Verifica se il servizio è abilitato
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'js_ttl' => self::DEFAULT_TTL,
            'css_ttl' => self::DEFAULT_TTL,
            'font_ttl' => self::DEFAULT_TTL,
            'image_ttl' => self::DEFAULT_TTL,
            'custom_domains' => [],
            'exclude_domains' => [],
            'aggressive_mode' => false,
            'preload_critical' => true,
            'cache_control_headers' => true,
        ];

        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): void
    {
        $current = $this->getSettings();
        
        // Valida TTL
        $ttlFields = ['js_ttl', 'css_ttl', 'font_ttl', 'image_ttl'];
        foreach ($ttlFields as $field) {
            if (isset($settings[$field])) {
                $ttl = max(self::MIN_TTL, min(self::MAX_TTL, (int) $settings[$field]));
                $settings[$field] = $ttl;
            }
        }

        // Merge con impostazioni esistenti
        $newSettings = wp_parse_args($settings, $current);
        
        update_option(self::OPTION_KEY, $newSettings);
        
        Logger::info('External cache settings updated', $newSettings);
    }

    /**
     * Aggiunge header di cache per risorse esterne
     */
    public function addExternalCacheHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        $settings = $this->getSettings();
        
        // Header generali per risorse esterne
        if (!empty($settings['cache_control_headers'])) {
            $this->addGeneralCacheHeaders($settings);
        }
    }

    /**
     * Gestisce script enqueued
     */
    public function handleEnqueuedScripts(): void
    {
        global $wp_scripts;
        
        if (!$wp_scripts instanceof \WP_Scripts) {
            return;
        }

        $settings = $this->getSettings();
        
        foreach ($wp_scripts->queue as $handle) {
            if (!isset($wp_scripts->registered[$handle])) {
                continue;
            }

            $script = $wp_scripts->registered[$handle];
            $src = $script->src;
            
            if ($this->isExternalResource($src) && $this->shouldCacheResource($src, 'js')) {
                $this->addResourceCacheHeaders($src, 'js', $settings['js_ttl']);
            }
        }
    }

    /**
     * Gestisce stili enqueued
     */
    public function handleEnqueuedStyles(): void
    {
        global $wp_styles;
        
        if (!$wp_styles instanceof \WP_Styles) {
            return;
        }

        $settings = $this->getSettings();
        
        foreach ($wp_styles->queue as $handle) {
            if (!isset($wp_styles->registered[$handle])) {
                continue;
            }

            $style = $wp_styles->registered[$handle];
            $src = $style->src;
            
            if ($this->isExternalResource($src) && $this->shouldCacheResource($src, 'css')) {
                $this->addResourceCacheHeaders($src, 'css', $settings['css_ttl']);
            }
        }
    }

    /**
     * Aggiunge header generali di cache
     */
    private function addGeneralCacheHeaders(array $settings): void
    {
        // Header per JavaScript esterni
        if (!headers_sent()) {
            header('X-External-Cache-JS: enabled');
            header('X-External-Cache-TTL: ' . $settings['js_ttl']);
        }
    }

    /**
     * Aggiunge header specifici per una risorsa
     */
    private function addResourceCacheHeaders(string $url, string $type, int $ttl): void
    {
        if (headers_sent()) {
            return;
        }

        $domain = $this->extractDomain($url);
        $cacheControl = "public, max-age={$ttl}, immutable";
        
        // Header specifici per dominio
        header("X-External-{$type}-Cache: {$domain}");
        header("X-External-{$type}-TTL: {$ttl}");
        
        // Se in modalità aggressiva, aggiungi anche preload
        $settings = $this->getSettings();
        if (!empty($settings['aggressive_mode']) && !empty($settings['preload_critical'])) {
            $this->addPreloadHeader($url, $type);
        }
    }

    /**
     * Aggiunge header preload per risorse critiche
     */
    private function addPreloadHeader(string $url, string $type): void
    {
        if (headers_sent()) {
            return;
        }

        $as = $this->getResourceType($type);
        $linkHeader = "<{$url}>; rel=preload; as={$as}";
        
        // Aggiungi crossorigin per risorse esterne
        if ($this->isExternalResource($url)) {
            $linkHeader .= "; crossorigin=anonymous";
        }
        
        header("Link: {$linkHeader}", false);
    }

    /**
     * Verifica se una risorsa è esterna
     */
    public function isExternalResource(string $url): bool
    {
        $homeUrl = home_url();
        $parsedUrl = wp_parse_url($url);
        $parsedHome = wp_parse_url($homeUrl);
        
        if (!$parsedUrl || !$parsedHome) {
            return false;
        }
        
        $urlHost = $parsedUrl['host'] ?? '';
        $homeHost = $parsedHome['host'] ?? '';
        
        return $urlHost !== $homeHost && !empty($urlHost);
    }

    /**
     * Verifica se una risorsa dovrebbe essere cachata
     */
    public function shouldCacheResource(string $url, string $type): bool
    {
        $settings = $this->getSettings();
        $domain = $this->extractDomain($url);
        
        // Controlla domini esclusi
        if (in_array($domain, $settings['exclude_domains'])) {
            return false;
        }
        
        // Se ci sono domini custom, controlla solo quelli
        if (!empty($settings['custom_domains'])) {
            return in_array($domain, $settings['custom_domains']);
        }
        
        // Altrimenti cache tutto (tranne esclusi)
        return true;
    }

    /**
     * Estrae il dominio da un URL
     */
    public function extractDomain(string $url): string
    {
        $parsed = wp_parse_url($url);
        return $parsed['host'] ?? '';
    }

    /**
     * Ottiene il tipo di risorsa per preload
     */
    private function getResourceType(string $type): string
    {
        $types = [
            'js' => 'script',
            'css' => 'style',
            'font' => 'font',
            'image' => 'image',
        ];
        
        return $types[$type] ?? 'script';
    }

    /**
     * Rileva automaticamente risorse esterne dal sito
     */
    public function detectExternalResources(): array
    {
        $resources = [
            'js' => [],
            'css' => [],
            'fonts' => [],
        ];

        // Analizza script
        global $wp_scripts;
        if ($wp_scripts instanceof \WP_Scripts) {
            foreach ($wp_scripts->registered as $handle => $script) {
                if ($this->isExternalResource($script->src)) {
                    $resources['js'][] = [
                        'handle' => $handle,
                        'src' => $script->src,
                        'domain' => $this->extractDomain($script->src),
                    ];
                }
            }
        }

        // Analizza stili
        global $wp_styles;
        if ($wp_styles instanceof \WP_Styles) {
            foreach ($wp_styles->registered as $handle => $style) {
                if ($this->isExternalResource($style->src)) {
                    $resources['css'][] = [
                        'handle' => $handle,
                        'src' => $style->src,
                        'domain' => $this->extractDomain($style->src),
                    ];
                }
            }
        }

        return $resources;
    }

    /**
     * Ottiene statistiche cache
     */
    public function getCacheStats(): array
    {
        $settings = $this->getSettings();
        $resources = $this->detectExternalResources();
        
        $totalResources = count($resources['js']) + count($resources['css']) + count($resources['fonts']);
        $cachedResources = 0;
        
        foreach ($resources as $type => $typeResources) {
            foreach ($typeResources as $resource) {
                if ($this->shouldCacheResource($resource['src'], $type)) {
                    $cachedResources++;
                }
            }
        }
        
        return [
            'total_resources' => $totalResources,
            'cached_resources' => $cachedResources,
            'cache_ratio' => $totalResources > 0 ? round(($cachedResources / $totalResources) * 100, 2) : 0,
            'js_ttl' => $settings['js_ttl'],
            'css_ttl' => $settings['css_ttl'],
            'font_ttl' => $settings['font_ttl'],
        ];
    }

    /**
     * Pulisce la cache
     */
    public function clearCache(): void
    {
        // Per risorse esterne, la cache è gestita dal browser
        // Possiamo solo forzare il refresh delle impostazioni
        do_action('fp_ps_external_cache_cleared');
        
        Logger::info('External cache cleared');
    }
}
