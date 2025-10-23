<?php

namespace FP\PerfSuite\Services\Mobile;

use FP\PerfSuite\Utils\Logger;

/**
 * Touch Optimizer Service
 * 
 * Ottimizzazioni specifiche per touch events e interazioni mobile
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class TouchOptimizer
{
    private const OPTION = 'fp_ps_touch_optimizer';

    /**
     * Registra gli hook per l'ottimizzazione touch
     */
    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        add_action('wp_footer', [$this, 'injectTouchOptimizations'], 1);
        add_action('wp_head', [$this, 'addTouchCSS'], 1);
        
        Logger::debug('Touch optimizer registered');
    }

    /**
     * Inietta ottimizzazioni JavaScript per touch
     */
    public function injectTouchOptimizations(): void
    {
        if (!wp_is_mobile()) {
            return;
        }

        $js = $this->generateTouchJS();
        echo '<script id="fp-ps-touch-optimizer">' . $js . '</script>';
    }

    /**
     * Aggiunge CSS per ottimizzazioni touch
     */
    public function addTouchCSS(): void
    {
        if (!wp_is_mobile()) {
            return;
        }

        $css = $this->generateTouchCSS();
        echo '<style id="fp-ps-touch-css">' . $css . '</style>';
    }

    /**
     * Genera JavaScript per ottimizzazioni touch
     */
    private function generateTouchJS(): string
    {
        $settings = $this->settings();
        
        return '
        (function() {
            "use strict";
            
            // Prevent zoom on double tap
            let lastTouchEnd = 0;
            document.addEventListener("touchend", function (event) {
                const now = (new Date()).getTime();
                if (now - lastTouchEnd <= 300) {
                    event.preventDefault();
                }
                lastTouchEnd = now;
            }, false);
            
            // Improve touch responsiveness
            document.addEventListener("touchstart", function() {}, true);
            document.addEventListener("touchmove", function() {}, true);
            document.addEventListener("touchend", function() {}, true);
            
            // Optimize scroll performance
            let ticking = false;
            function updateScroll() {
                // Scroll optimizations here
                ticking = false;
            }
            
            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateScroll);
                    ticking = true;
                }
            }
            
            window.addEventListener("scroll", requestTick, { passive: true });
            
            // Touch feedback for interactive elements
            const interactiveElements = document.querySelectorAll("a, button, input, select, textarea, [role=button]");
            interactiveElements.forEach(function(element) {
                element.addEventListener("touchstart", function() {
                    this.classList.add("touch-active");
                });
                
                element.addEventListener("touchend", function() {
                    setTimeout(() => {
                        this.classList.remove("touch-active");
                    }, 150);
                });
            });
            
            // Prevent 300ms delay on click
            let clickTimeout;
            document.addEventListener("touchend", function(event) {
                clearTimeout(clickTimeout);
                clickTimeout = setTimeout(function() {
                    // Handle click
                }, 0);
            });
        })();
        ';
    }

    /**
     * Genera CSS per ottimizzazioni touch
     */
    private function generateTouchCSS(): string
    {
        $settings = $this->settings();
        
        $css = '
        /* Touch Optimizations */
        @media screen and (max-width: 768px) {
            /* Improve touch targets */
            a, button, input, select, textarea, [role=button] {
                min-height: 44px;
                min-width: 44px;
                touch-action: manipulation;
            }
            
            /* Touch feedback */
            .touch-active {
                opacity: 0.7;
                transform: scale(0.98);
                transition: all 0.1s ease;
            }
            
            /* Improve scrolling */
            * {
                -webkit-overflow-scrolling: touch;
            }
            
            /* Prevent text selection on touch */
            a, button, [role=button] {
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: none;
            }
            
            /* Optimize tap highlights */
            a, button, input, select, textarea {
                -webkit-tap-highlight-color: rgba(0, 0, 0, 0.1);
            }
        }';

        if ($settings['disable_hover_effects']) {
            $css .= '
            @media screen and (max-width: 768px) {
                *:hover {
                    transform: none !important;
                    box-shadow: none !important;
                }
            }';
        }

        return $css;
    }

    /**
     * Ottiene le impostazioni
     */
    private function settings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'disable_hover_effects' => true,
            'improve_touch_targets' => true,
            'optimize_scroll' => true,
            'prevent_zoom' => true
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