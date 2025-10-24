<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * CSS Optimizer
 * 
 * Optimizes CSS delivery to prevent render blocking and reduce
 * Element render delay issues.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CSSOptimizer
{
    private const OPTION = 'fp_ps_css_optimization';

    /**
     * Register hooks
     */
    public function register(): void
    {
        if (!is_admin() && $this->isEnabled()) {
            // Defer non-critical CSS - PRIORITÀ MEDIA per ottimizzazioni CSS
            add_filter('style_loader_tag', [$this, 'deferNonCriticalCSS'], 15, 4);
            
            // Inline critical CSS - PRIORITÀ MEDIA per CSS base
            add_action('wp_head', [$this, 'inlineCriticalCSS'], 5);
            
            // Optimize CSS loading order - PRIORITÀ MEDIA per CSS base
            add_action('wp_head', [$this, 'optimizeCSSLoading'], 6);
            
            Logger::debug('CSSOptimizer registered');
        }
    }

    /**
     * Defer non-critical CSS to prevent render blocking
     */
    public function deferNonCriticalCSS(string $html, string $handle, string $href, $media): string
    {
        $settings = $this->getSettings();
        
        if (!$settings['defer_non_critical']) {
            return $html;
        }

        // Skip critical CSS
        if ($this->isCriticalCSS($handle, $href)) {
            return $html;
        }

        // Skip if already deferred
        if (strpos($html, 'data-fp-deferred') !== false) {
            return $html;
        }

        // Apply defer loading
        $html = str_replace(
            'rel="stylesheet"',
            'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"',
            $html
        );

        // Add defer attribute
        $html = str_replace('<link', '<link data-fp-deferred="true"', $html);

        // Add noscript fallback
        $html .= '<noscript><link rel="stylesheet" href="' . esc_url($href) . '" media="' . esc_attr($media) . '"></noscript>';

        Logger::debug('CSS deferred', ['handle' => $handle]);

        return $html;
    }

    /**
     * Inline critical CSS for above-the-fold content
     */
    public function inlineCriticalCSS(): void
    {
        $criticalCSS = $this->getCriticalCSS();
        
        if (empty($criticalCSS)) {
            return;
        }

        echo "\n<!-- FP Performance Suite - Critical CSS -->\n";
        echo '<style id="fp-critical-css">' . $criticalCSS . '</style>' . "\n";
        echo "<!-- End Critical CSS -->\n";
    }

    /**
     * Optimize CSS loading order and priority
     */
    public function optimizeCSSLoading(): void
    {
        $settings = $this->getSettings();
        
        if (!$settings['optimize_loading_order']) {
            return;
        }

        echo "\n<!-- FP Performance Suite - CSS Loading Optimization -->\n";
        
        // Add resource hints for CSS files
        $this->addCSSResourceHints();
        
        // Add loading optimization script
        $this->addCSSLoadingScript();
        
        echo "<!-- End CSS Loading Optimization -->\n";
    }

    /**
     * Check if CSS is critical
     */
    private function isCriticalCSS(string $handle, string $href): bool
    {
        $criticalHandles = [
            'theme-style',
            'style',
            'main-style',
            'critical-css',
            'above-the-fold'
        ];

        $criticalPatterns = [
            'style.css',
            'main.css',
            'critical.css',
            'above-the-fold.css'
        ];

        // Check handle
        foreach ($criticalHandles as $criticalHandle) {
            if (strpos($handle, $criticalHandle) !== false) {
                return true;
            }
        }

        // Check URL patterns
        foreach ($criticalPatterns as $pattern) {
            if (strpos($href, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get critical CSS content
     */
    private function getCriticalCSS(): string
    {
        $settings = $this->getSettings();
        $criticalCSS = $settings['critical_css'] ?? '';

        // If no custom critical CSS, generate basic one
        if (empty($criticalCSS)) {
            $criticalCSS = $this->generateBasicCriticalCSS();
        }

        return $criticalCSS;
    }

    /**
     * Generate basic critical CSS for common elements
     */
    private function generateBasicCriticalCSS(): string
    {
        return '
            /* Critical CSS for above-the-fold content */
            * { box-sizing: border-box; }
            body { 
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 0;
            }
            .site-header, header, .header { 
                display: block;
                position: relative;
                z-index: 100;
            }
            .site-main, main, .main { 
                display: block;
            }
            .hero, .banner, .hero-section { 
                display: block;
                position: relative;
            }
            h1, h2, h3, h4, h5, h6 { 
                font-weight: bold;
                line-height: 1.2;
                margin: 0 0 1rem 0;
            }
            p { 
                line-height: 1.6;
                margin: 0 0 1rem 0;
            }
            img { 
                max-width: 100%;
                height: auto;
                display: block;
            }
            .container, .wrapper { 
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 1rem;
            }
            .btn, .button { 
                display: inline-block;
                padding: 10px 20px;
                text-decoration: none;
                border: none;
                cursor: pointer;
            }
            /* Prevent layout shift */
            .lazy { opacity: 0; }
            .lazy.loaded { opacity: 1; transition: opacity 0.3s; }
        ';
    }

    /**
     * Add resource hints for CSS files
     */
    private function addCSSResourceHints(): void
    {
        $hints = [
            '//fonts.googleapis.com',
            '//fonts.gstatic.com',
            '//cdnjs.cloudflare.com',
            '//maxcdn.bootstrapcdn.com'
        ];

        foreach ($hints as $hint) {
            echo '<link rel="dns-prefetch" href="' . esc_url($hint) . '">' . "\n";
        }
    }

    /**
     * Add CSS loading optimization script
     */
    private function addCSSLoadingScript(): void
    {
        ?>
        <script>
        (function() {
            'use strict';
            
            // Optimize CSS loading
            function optimizeCSSLoading() {
                var deferredStyles = document.querySelectorAll('link[data-fp-deferred="true"]');
                
                if (deferredStyles.length === 0) {
                    return;
                }
                
                // Load deferred styles on user interaction
                var events = ['mousemove', 'scroll', 'keydown', 'click', 'touchstart'];
                var loadDeferredStyles = function() {
                    deferredStyles.forEach(function(link) {
                        if (link.rel === 'preload') {
                            link.rel = 'stylesheet';
                        }
                    });
                    
                    // Remove event listeners
                    events.forEach(function(event) {
                        window.removeEventListener(event, loadDeferredStyles);
                    });
                };
                
                // Add event listeners
                events.forEach(function(event) {
                    window.addEventListener(event, loadDeferredStyles, { passive: true, once: true });
                });
                
                // Fallback timeout
                setTimeout(loadDeferredStyles, 3000);
            }
            
            // Run optimization
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', optimizeCSSLoading);
            } else {
                optimizeCSSLoading();
            }
        })();
        </script>
        <?php
    }

    /**
     * Check if optimization is enabled
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return !empty($settings['enabled']);
    }

    /**
     * Get all settings
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'defer_non_critical' => false,
            'inline_critical' => false,
            'optimize_loading_order' => false,
            'critical_css' => '',
        ];

        $settings = get_option(self::OPTION, []);
        return is_array($settings) ? array_merge($defaults, $settings) : $defaults;
    }

    /**
     * Update settings
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = array_merge($current, $settings);

        $result = update_option(self::OPTION, $updated);

        if ($result) {
            Logger::info('CSS optimization settings updated', $updated);
            do_action('fp_ps_css_optimization_updated', $updated);
        }

        return $result;
    }

    /**
     * Get status for admin display
     */
    public function status(): array
    {
        $settings = $this->getSettings();
        
        return [
            'enabled' => $this->isEnabled(),
            'defer_enabled' => !empty($settings['defer_non_critical']),
            'inline_critical' => !empty($settings['inline_critical']),
            'optimize_loading' => !empty($settings['optimize_loading_order']),
            'critical_css_configured' => !empty($settings['critical_css']),
        ];
    }
}
