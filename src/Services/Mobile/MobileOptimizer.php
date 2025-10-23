<?php

namespace FP\PerfSuite\Services\Mobile;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\MobileRateLimiter;
use FP\PerfSuite\ServiceContainer;

/**
 * Mobile Optimizer Service
 * 
 * Ottimizzazioni specifiche per dispositivi mobile
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class MobileOptimizer
{
    private const OPTION = 'fp_ps_mobile_optimizer';
    
    private ServiceContainer $container;
    private TouchOptimizer $touchOptimizer;
    private MobileCacheManager $cacheManager;
    private ResponsiveImageManager $responsiveImageManager;

    public function __construct(
        ServiceContainer $container,
        TouchOptimizer $touchOptimizer,
        MobileCacheManager $cacheManager,
        ResponsiveImageManager $responsiveImageManager
    ) {
        $this->container = $container;
        $this->touchOptimizer = $touchOptimizer;
        $this->cacheManager = $cacheManager;
        $this->responsiveImageManager = $responsiveImageManager;
    }

    /**
     * Ottiene le impostazioni del servizio
     */
    public function getSettings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => false,
            'optimize_touch_targets' => false,
            'enable_responsive_images' => false
        ]);
    }

    /**
     * Aggiorna le impostazioni del servizio
     */
    public function updateSettings(array $settings): void
    {
        update_option(self::OPTION, $settings);
    }

    /**
     * Registra gli hook per l'ottimizzazione mobile
     */
    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // Ottimizzazioni CSS per mobile
        add_action('wp_head', [$this, 'addMobileCSS'], 1);
        
        // Ottimizzazioni JavaScript per mobile
        add_action('wp_enqueue_scripts', [$this, 'optimizeMobileScripts'], 20);
        
        // Viewport e meta tag ottimizzati
        add_action('wp_head', [$this, 'addMobileMetaTags'], 1);
        
        // Touch events optimization
        add_action('wp_footer', [$this, 'addTouchOptimizations'], 1);
        
        // Mobile-specific caching
        add_action('template_redirect', [$this, 'applyMobileCaching']);
        
        // Responsive images
        add_filter('wp_get_attachment_image_attributes', [$this, 'optimizeImageAttributes'], 10, 3);
        
        Logger::debug('Mobile optimizer registered');
    }

    /**
     * Aggiunge CSS specifici per mobile
     */
    public function addMobileCSS(): void
    {
        if (!$this->isMobile()) {
            return;
        }

        // Rate limiting per CSS mobile
        if (!MobileRateLimiter::isAllowed('mobile_css')) {
            Logger::debug('Mobile CSS generation rate limited');
            return;
        }

        $css = $this->generateMobileCSS();
        echo '<style id="fp-ps-mobile-optimizer">' . $css . '</style>';
    }

    /**
     * Ottimizza script per mobile
     */
    public function optimizeMobileScripts(): void
    {
        if (!$this->isMobile()) {
            return;
        }

        // Defer non-critical scripts
        add_filter('script_loader_tag', [$this, 'deferNonCriticalScripts'], 10, 2);
        
        // Remove unnecessary scripts on mobile
        add_action('wp_print_scripts', [$this, 'removeUnnecessaryScripts']);
    }

    /**
     * Aggiunge meta tag ottimizzati per mobile
     */
    public function addMobileMetaTags(): void
    {
        if (!$this->isMobile()) {
            return;
        }

        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">' . "\n";
        echo '<meta name="format-detection" content="telephone=no">' . "\n";
        echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
        echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
    }

    /**
     * Aggiunge ottimizzazioni per touch
     */
    public function addTouchOptimizations(): void
    {
        if (!$this->isMobile()) {
            return;
        }

        // Rate limiting per touch optimizations
        if (!MobileRateLimiter::isAllowed('touch_optimizations')) {
            Logger::debug('Touch optimizations rate limited');
            return;
        }

        $js = $this->generateTouchOptimizations();
        echo '<script id="fp-ps-touch-optimizer">' . $js . '</script>';
    }

    /**
     * Applica caching specifico per mobile
     */
    public function applyMobileCaching(): void
    {
        if (!$this->isMobile()) {
            return;
        }

        $this->cacheManager->applyMobileCaching();
    }

    /**
     * Ottimizza attributi delle immagini per mobile
     */
    public function optimizeImageAttributes(array $attr, \WP_Post $attachment, $size): array
    {
        if (!$this->isMobile()) {
            return $attr;
        }

        return $this->responsiveImageManager->optimizeAttributes($attr, $attachment, $size);
    }

    /**
     * Rileva se la richiesta è da dispositivo mobile
     */
    private function isMobile(): bool
    {
        return wp_is_mobile();
    }

    /**
     * Genera CSS ottimizzato per mobile
     */
    private function generateMobileCSS(): string
    {
        $settings = $this->settings();
        
        $css = '
        /* Mobile Optimizations */
        @media screen and (max-width: 768px) {
            /* Improve touch targets */
            a, button, input, select, textarea {
                min-height: 44px;
                min-width: 44px;
            }
            
            /* Optimize text for mobile reading */
            body {
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
                text-size-adjust: 100%;
            }
            
            /* Improve scrolling */
            * {
                -webkit-overflow-scrolling: touch;
            }
            
            /* Optimize images */
            img {
                max-width: 100%;
                height: auto;
            }
        }';

        if ($settings['disable_animations']) {
            $css .= '
            @media screen and (max-width: 768px) {
                *, *::before, *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                }
            }';
        }

        return $css;
    }

    /**
     * Genera ottimizzazioni JavaScript per touch
     */
    private function generateTouchOptimizations(): string
    {
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
        })();
        ';
    }

    /**
     * Differisce script non critici su mobile
     */
    public function deferNonCriticalScripts(string $tag, string $handle): string
    {
        $critical_scripts = [
            'jquery', 'jquery-core', 'wp-util', 'wp-hooks',
            'wp-i18n', 'wp-polyfill'
        ];

        if (in_array($handle, $critical_scripts, true)) {
            return $tag;
        }

        // Aggiungi defer a script non critici
        if (strpos($tag, 'src=') !== false && strpos($tag, 'defer') === false) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }

        return $tag;
    }

    /**
     * Rimuove script non necessari su mobile
     */
    public function removeUnnecessaryScripts(): void
    {
        $settings = $this->settings();
        
        if ($settings['remove_unnecessary_scripts']) {
            // Rimuovi script non necessari per mobile
            $scripts_to_remove = [
                'wp-embed', // WordPress embeds
                'comment-reply', // Comment reply
            ];
            
            foreach ($scripts_to_remove as $script) {
                wp_dequeue_script($script);
            }
        }
    }

    /**
     * Rileva problemi specifici per mobile
     */
    public function detectMobileIssues(): array
    {
        $issues = [];
        
        // Controlla viewport meta tag
        if (!$this->hasViewportMeta()) {
            $issues[] = [
                'type' => 'viewport',
                'severity' => 'high',
                'message' => 'Viewport meta tag mancante o non ottimizzato',
                'fix' => 'Aggiungere viewport meta tag ottimizzato'
            ];
        }
        
        // Controlla touch targets
        $small_targets = $this->detectSmallTouchTargets();
        if (!empty($small_targets)) {
            $issues[] = [
                'type' => 'touch_targets',
                'severity' => 'medium',
                'message' => 'Trovati ' . count($small_targets) . ' elementi con touch target troppo piccoli',
                'fix' => 'Aumentare dimensione touch target a minimo 44px'
            ];
        }
        
        // Controlla immagini non responsive
        $non_responsive_images = $this->detectNonResponsiveImages();
        if (!empty($non_responsive_images)) {
            $issues[] = [
                'type' => 'responsive_images',
                'severity' => 'medium',
                'message' => 'Trovate ' . count($non_responsive_images) . ' immagini non responsive',
                'fix' => 'Implementare immagini responsive'
            ];
        }
        
        return $issues;
    }

    /**
     * Applica fix automatici per mobile
     */
    public function applyMobileFixes(): array
    {
        $applied_fixes = [];
        
        // Fix viewport meta tag
        if (!$this->hasViewportMeta()) {
            add_action('wp_head', [$this, 'addMobileMetaTags'], 1);
            $applied_fixes[] = 'viewport_meta_tag';
        }
        
        // Fix touch targets via CSS
        add_action('wp_head', [$this, 'addMobileCSS'], 1);
        $applied_fixes[] = 'touch_targets_css';
        
        // Fix responsive images
        add_filter('wp_get_attachment_image_attributes', [$this, 'optimizeImageAttributes'], 10, 3);
        $applied_fixes[] = 'responsive_images';
        
        return $applied_fixes;
    }

    /**
     * Genera report mobile
     */
    public function generateMobileReport(): array
    {
        $issues = $this->detectMobileIssues();
        $settings = $this->settings();
        
        return [
            'enabled' => $this->isEnabled(),
            'settings' => $settings,
            'issues' => $issues,
            'issues_count' => count($issues),
            'critical_issues' => count(array_filter($issues, fn($issue) => $issue['severity'] === 'high')),
            'recommendations' => $this->getRecommendations($issues)
        ];
    }

    /**
     * Controlla se esiste viewport meta tag
     */
    private function hasViewportMeta(): bool
    {
        // Verifica se c'è un buffer di output attivo
        if (ob_get_level() > 0) {
            $content = ob_get_contents() ?: '';
            return strpos($content, 'name="viewport"') !== false;
        }
        
        // Se non c'è buffer, controlla se il viewport è già stato aggiunto
        // Questo è un controllo semplificato per evitare errori
        return true; // Assume che il viewport sia presente per evitare falsi positivi
    }

    /**
     * Rileva touch target troppo piccoli
     */
    private function detectSmallTouchTargets(): array
    {
        // Implementazione semplificata - in realtà dovrebbe analizzare il DOM
        return [];
    }

    /**
     * Rileva immagini non responsive
     */
    private function detectNonResponsiveImages(): array
    {
        // Implementazione semplificata - in realtà dovrebbe analizzare il DOM
        return [];
    }

    /**
     * Ottiene raccomandazioni basate sui problemi rilevati
     */
    private function getRecommendations(array $issues): array
    {
        $recommendations = [];
        
        foreach ($issues as $issue) {
            switch ($issue['type']) {
                case 'viewport':
                    $recommendations[] = 'Aggiungere viewport meta tag ottimizzato per mobile';
                    break;
                case 'touch_targets':
                    $recommendations[] = 'Implementare CSS per touch target di almeno 44px';
                    break;
                case 'responsive_images':
                    $recommendations[] = 'Utilizzare immagini responsive con srcset';
                    break;
            }
        }
        
        return array_unique($recommendations);
    }

    /**
     * Ottiene le impostazioni
     */
    private function settings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => true,
            'optimize_touch_targets' => true,
            'enable_responsive_images' => true
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