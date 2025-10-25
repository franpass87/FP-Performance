<?php

namespace FP\PerfSuite\Services\Mobile;

use FP\PerfSuite\Utils\Logger;

/**
 * Mobile Cache Manager Service
 * 
 * Gestisce cache specifica per dispositivi mobile
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class MobileCacheManager
{
    private const OPTION = 'fp_ps_mobile_cache';
    private const CACHE_GROUP = 'fp_ps_mobile';

    /**
     * Registra gli hook per la gestione cache mobile
     */
    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }

        add_action('template_redirect', [$this, 'applyMobileCaching']);
        add_action('wp_head', [$this, 'addMobileCacheHeaders'], 21);
        
        Logger::debug('Mobile cache manager registered');
    }

    /**
     * Applica strategie di caching specifiche per mobile
     */
    public function applyMobileCaching(): void
    {
        if (!wp_is_mobile()) {
            return;
        }

        $settings = $this->settings();
        
        // Cache headers specifici per mobile
        if ($settings['enable_mobile_cache_headers']) {
            $this->setMobileCacheHeaders();
        }
        
        // Cache per risorse mobile
        if ($settings['enable_resource_caching']) {
            $this->cacheMobileResources();
        }
    }

    /**
     * Aggiunge header di cache specifici per mobile
     */
    public function addMobileCacheHeaders(): void
    {
        if (!wp_is_mobile()) {
            return;
        }

        $this->setMobileCacheHeaders();
    }

    /**
     * Imposta header di cache per mobile
     */
    private function setMobileCacheHeaders(): void
    {
        $settings = $this->settings();
        
        // Cache per HTML mobile (più breve per aggiornamenti frequenti)
        if (!headers_sent()) {
            header('Cache-Control: public, max-age=' . $settings['html_cache_duration']);
            header('Vary: User-Agent, Accept-Encoding');
        }
    }

    /**
     * Cache risorse specifiche per mobile
     */
    private function cacheMobileResources(): void
    {
        $settings = $this->settings();
        
        // Cache per CSS mobile
        if ($settings['cache_mobile_css']) {
            $this->cacheMobileCSS();
        }
        
        // Cache per JS mobile
        if ($settings['cache_mobile_js']) {
            $this->cacheMobileJS();
        }
    }

    /**
     * Cache CSS ottimizzato per mobile
     */
    private function cacheMobileCSS(): void
    {
        $cache_key = 'mobile_css_' . get_the_ID();
        
        $css = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($css === false) {
            $css = $this->generateMobileCSS();
            wp_cache_set($cache_key, $css, self::CACHE_GROUP, HOUR_IN_SECONDS);
        }
    }

    /**
     * Cache JavaScript ottimizzato per mobile
     */
    private function cacheMobileJS(): void
    {
        $cache_key = 'mobile_js_' . get_the_ID();
        
        $js = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($js === false) {
            $js = $this->generateMobileJS();
            wp_cache_set($cache_key, $js, self::CACHE_GROUP, HOUR_IN_SECONDS);
        }
    }

    /**
     * Genera CSS ottimizzato per mobile
     */
    private function generateMobileCSS(): string
    {
        return '
        @media screen and (max-width: 768px) {
            /* Mobile-specific optimizations */
            .mobile-hidden { display: none !important; }
            .mobile-visible { display: block !important; }
            
            /* Optimize for mobile viewport */
            body { font-size: 16px; line-height: 1.5; }
            
            /* Improve touch targets */
            a, button, input, select, textarea {
                min-height: 44px;
                min-width: 44px;
            }
        }
        ';
    }

    /**
     * Genera JavaScript ottimizzato per mobile
     */
    private function generateMobileJS(): string
    {
        return '
        (function() {
            "use strict";
            
            // Mobile-specific optimizations
            if (window.innerWidth <= 768) {
                // Mobile-specific code here
            }
        })();
        ';
    }

    /**
     * Pulisce cache mobile
     */
    public function clearMobileCache(): bool
    {
        return wp_cache_flush_group(self::CACHE_GROUP);
    }

    /**
     * Ottiene statistiche cache mobile
     */
    public function getCacheStats(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'settings' => $this->settings(),
            'cache_group' => self::CACHE_GROUP,
            'last_cleared' => get_option('fp_ps_mobile_cache_last_cleared', 0)
        ];
    }

    /**
     * Ottiene le impostazioni
     */
    private function settings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'enable_mobile_cache_headers' => true,
            'enable_resource_caching' => true,
            'cache_mobile_css' => true,
            'cache_mobile_js' => true,
            'html_cache_duration' => 300, // 5 minuti per mobile
            'css_cache_duration' => 3600, // 1 ora
            'js_cache_duration' => 3600 // 1 ora
        ]);
    }

    /**
     * Controlla se il servizio è abilitato
     */
    private function isEnabled(): bool
    {
        $settings = $this->settings();
        return !empty($settings['enabled']);
    }
}