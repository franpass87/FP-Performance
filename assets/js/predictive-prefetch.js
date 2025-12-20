/**
 * Predictive Prefetching
 * 
 * Pre-carica le pagine basandosi sul comportamento dell'utente
 * Strategie supportate: hover, viewport, aggressive
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

(function() {
    'use strict';

    // Verifica configurazione
    if (typeof window.fpPrefetchConfig === 'undefined') {
        return; // Config non disponibile, esci
    }

    const config = window.fpPrefetchConfig;
    const strategy = config.strategy || 'hover';
    const hoverDelay = config.hoverDelay || 100;
    const limit = config.limit || 5;

    let prefetchedUrls = new Set();
    let prefetchCount = 0;

    /**
     * Prefetch di un URL
     */
    function prefetchUrl(url) {
        // Evita prefetch duplicati
        if (prefetchedUrls.has(url)) {
            return;
        }

        // Rispetta il limite
        if (prefetchCount >= limit) {
            return;
        }

        // Verifica che sia un URL interno
        if (!isInternalUrl(url)) {
            return;
        }

        // Crea link prefetch
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        document.head.appendChild(link);

        prefetchedUrls.add(url);
        prefetchCount++;
    }

    /**
     * Verifica se l'URL è interno
     */
    function isInternalUrl(url) {
        try {
            const urlObj = new URL(url, window.location.origin);
            return urlObj.hostname === window.location.hostname;
        } catch (e) {
            return false;
        }
    }

    /**
     * Strategia Hover
     */
    function initHoverStrategy() {
        let hoverTimeout;

        document.addEventListener('mouseover', function(e) {
            const link = e.target.closest('a[href]');
            if (!link) return;

            const url = link.href;
            if (!isInternalUrl(url)) return;

            // Cancella timeout precedente
            clearTimeout(hoverTimeout);

            // Prefetch dopo delay
            hoverTimeout = setTimeout(() => {
                prefetchUrl(url);
            }, hoverDelay);
        }, { passive: true });
    }

    /**
     * Strategia Viewport
     */
    function initViewportStrategy() {
        if (!window.IntersectionObserver) {
            return; // Browser non supporta IntersectionObserver
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const link = entry.target;
                    if (link.tagName === 'A' && link.href) {
                        prefetchUrl(link.href);
                        observer.unobserve(link);
                    }
                }
            });
        }, {
            rootMargin: '50px' // Prefetch quando il link è a 50px dal viewport
        });

        // Osserva tutti i link nella pagina
        document.querySelectorAll('a[href]').forEach(link => {
            if (isInternalUrl(link.href)) {
                observer.observe(link);
            }
        });
    }

    /**
     * Strategia Aggressive
     */
    function initAggressiveStrategy() {
        // Combina hover e viewport
        initHoverStrategy();
        initViewportStrategy();

        // Prefetch immediato dei primi link del menu
        const menuLinks = document.querySelectorAll('nav a[href], .menu a[href]');
        Array.from(menuLinks).slice(0, limit).forEach(link => {
            if (isInternalUrl(link.href)) {
                prefetchUrl(link.href);
            }
        });
    }

    /**
     * Inizializza in base alla strategia
     */
    function init() {
        switch (strategy) {
            case 'hover':
                initHoverStrategy();
                break;
            case 'viewport':
                initViewportStrategy();
                break;
            case 'aggressive':
                initAggressiveStrategy();
                break;
            default:
                console.warn('FP Performance: Strategia prefetch non riconosciuta:', strategy);
        }
    }

    // Inizializza quando il DOM è pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();




