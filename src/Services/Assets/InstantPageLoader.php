<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function esc_html__;
use function esc_js;
use function wp_enqueue_script;
use function wp_add_inline_script;
use function is_admin;
use function wp_doing_ajax;

/**
 * Instant Page Loader
 * 
 * Precarica le pagine quando l'utente passa il mouse sopra i link,
 * rendendo la navigazione istantanea
 * 
 * Ispirato a instant.page e quicklink
 * 
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class InstantPageLoader
{
    private const OPTION = 'fp_ps_instant_page';
    
    /**
     * Impostazioni del servizio
     */
    public function getSettings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'trigger' => 'hover', // hover, viewport, both
            'intensity' => 'medium', // low, medium, high, aggressive
            'delay' => 65, // ms delay su hover prima di prefetch
            'max_requests' => 10, // max prefetch simultanei
            'exclude_keywords' => [], // URL keywords da escludere
            'preload_visible' => true, // Preload links visibili nel viewport
        ]);
    }
    
    /**
     * Aggiorna impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        // BUGFIX: Valida che settings sia un array valido
        if (!is_array($settings) || empty($settings)) {
            return false;
        }
        
        $current = $this->getSettings();
        $new = array_merge($current, $settings);
        
        // Validazione
        if (isset($new['trigger']) && !in_array($new['trigger'], ['hover', 'viewport', 'both'], true)) {
            $new['trigger'] = 'hover';
        }
        
        if (isset($new['intensity']) && !in_array($new['intensity'], ['low', 'medium', 'high', 'aggressive'], true)) {
            $new['intensity'] = 'medium';
        }
        
        if (isset($new['delay'])) {
            $new['delay'] = max(0, min(500, (int) $new['delay']));
        }
        
        if (isset($new['max_requests'])) {
            $new['max_requests'] = max(1, min(50, (int) $new['max_requests']));
        }
        
        return update_option(self::OPTION, $new, false);
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $settings = $this->getSettings();
        
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Non attivare in admin o AJAX
        if (is_admin() || wp_doing_ajax()) {
            return;
        }
        
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts'], 999);
        
        Logger::debug('InstantPageLoader registered', $settings);
    }
    
    /**
     * Enqueue scripts per instant page
     */
    public function enqueueScripts(): void
    {
        $settings = $this->getSettings();
        
        // Inline script per instant page
        $script = $this->generateInstantPageScript($settings);
        
        wp_add_inline_script('jquery', $script, 'after');
        
        Logger::debug('InstantPageLoader scripts enqueued');
    }
    
    /**
     * Genera lo script JavaScript per instant page
     */
    private function generateInstantPageScript(array $settings): string
    {
        $trigger = $settings['trigger'];
        $delay = $settings['delay'];
        $maxRequests = $settings['max_requests'];
        $excludeKeywords = !empty($settings['exclude_keywords']) ? $settings['exclude_keywords'] : [];
        $preloadVisible = !empty($settings['preload_visible']);
        
        // BUGFIX: Converti exclude keywords in JSON con fallback
        $excludeKeywordsJson = wp_json_encode($excludeKeywords);
        if ($excludeKeywordsJson === false) {
            Logger::error('Failed to encode exclude keywords to JSON');
            $excludeKeywordsJson = '[]'; // Fallback sicuro
        }
        
        $script = <<<JAVASCRIPT
(function() {
    'use strict';
    
    // FP Performance Suite - Instant Page Loader
    const FPInstantPage = {
        config: {
            trigger: '{$trigger}',
            delay: {$delay},
            maxRequests: {$maxRequests},
            excludeKeywords: {$excludeKeywordsJson},
            preloadVisible: {$preloadVisible}
        },
        
        prefetchedUrls: new Set(),
        activeRequests: 0,
        hoverTimer: null,
        intersectionObserver: null,
        
        init: function() {
            if (this.config.trigger === 'hover' || this.config.trigger === 'both') {
                this.initHoverPrefetch();
            }
            
            if (this.config.trigger === 'viewport' || this.config.trigger === 'both') {
                this.initViewportPrefetch();
            }
            
            // Touch devices: prefetch on touchstart
            if ('ontouchstart' in window) {
                this.initTouchPrefetch();
            }
        },
        
        initHoverPrefetch: function() {
            const self = this;
            
            // Usa event delegation per performance
            document.addEventListener('mouseover', function(e) {
                const link = e.target.closest('a');
                if (!link) return;
                
                // Delay prima del prefetch
                clearTimeout(self.hoverTimer);
                self.hoverTimer = setTimeout(function() {
                    self.prefetchUrl(link.href);
                }, self.config.delay);
            }, { passive: true });
            
            // Cancella timer se mouse esce dal link
            document.addEventListener('mouseout', function(e) {
                if (e.target.closest('a')) {
                    clearTimeout(self.hoverTimer);
                }
            }, { passive: true });
        },
        
        initViewportPrefetch: function() {
            if (!('IntersectionObserver' in window)) {
                return; // Fallback: browser non supporta
            }
            
            const self = this;
            
            this.intersectionObserver = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const link = entry.target;
                        self.prefetchUrl(link.href);
                        // Stop observing dopo prefetch
                        self.intersectionObserver.unobserve(link);
                    }
                });
            }, {
                rootMargin: '100px' // Prefetch quando link è vicino al viewport
            });
            
            // Osserva tutti i link visibili
            if (this.config.preloadVisible) {
                this.observeLinks();
            }
        },
        
        initTouchPrefetch: function() {
            const self = this;
            
            document.addEventListener('touchstart', function(e) {
                const link = e.target.closest('a');
                if (link) {
                    self.prefetchUrl(link.href);
                }
            }, { passive: true });
        },
        
        observeLinks: function() {
            if (!this.intersectionObserver) return;
            
            const links = document.querySelectorAll('a[href]');
            const self = this;
            
            links.forEach(function(link) {
                if (self.shouldPrefetch(link.href)) {
                    self.intersectionObserver.observe(link);
                }
            });
        },
        
        shouldPrefetch: function(url) {
            if (!url) return false;
            
            // Solo URL dello stesso dominio
            try {
                const urlObj = new URL(url, window.location.href);
                
                // Diverso dominio
                if (urlObj.host !== window.location.host) {
                    return false;
                }
                
                // URL già prefetchato
                if (this.prefetchedUrls.has(url)) {
                    return false;
                }
                
                // Controlla exclude keywords
                const href = urlObj.href.toLowerCase();
                for (let i = 0; i < this.config.excludeKeywords.length; i++) {
                    if (href.indexOf(this.config.excludeKeywords[i].toLowerCase()) !== -1) {
                        return false;
                    }
                }
                
                // Escludi URL con anchor (#)
                if (urlObj.hash && urlObj.pathname === window.location.pathname) {
                    return false;
                }
                
                // Escludi file non-HTML
                const ext = urlObj.pathname.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'xml'].indexOf(ext) !== -1) {
                    return false;
                }
                
                return true;
                
            } catch (e) {
                return false;
            }
        },
        
        prefetchUrl: function(url) {
            if (!this.shouldPrefetch(url)) {
                return;
            }
            
            // Limita richieste simultanee
            if (this.activeRequests >= this.config.maxRequests) {
                return;
            }
            
            // Segna come prefetchato
            this.prefetchedUrls.add(url);
            this.activeRequests++;
            
            const self = this;
            
            // Usa <link rel="prefetch"> per performance ottimale
            const link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = url;
            link.as = 'document';
            
            link.onload = function() {
                self.activeRequests--;
            };
            
            link.onerror = function() {
                self.activeRequests--;
                self.prefetchedUrls.delete(url); // Rimuovi se fallito
            };
            
            document.head.appendChild(link);
        }
    };
    
    // Inizializza quando DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            FPInstantPage.init();
        });
    } else {
        FPInstantPage.init();
    }
    
})();
JAVASCRIPT;
        
        return $script;
    }
    
    /**
     * Status del servizio
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => !empty($settings['enabled']),
            'trigger' => $settings['trigger'] ?? 'hover',
            'intensity' => $settings['intensity'] ?? 'medium',
            'delay' => $settings['delay'] ?? 65,
            'max_requests' => $settings['max_requests'] ?? 10,
            'exclude_count' => count($settings['exclude_keywords'] ?? []),
        ];
    }
}

