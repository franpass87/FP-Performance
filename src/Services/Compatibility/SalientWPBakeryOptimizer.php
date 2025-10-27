<?php

namespace FP\PerfSuite\Services\Compatibility;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\HookManager;

/**
 * Salient + WPBakery Optimizer
 * 
 * Ottimizzazioni specifiche per tema Salient con WPBakery Page Builder
 * 
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 * @since 1.7.0
 */
class SalientWPBakeryOptimizer
{
    private ServiceContainer $container;
    private ThemeDetector $detector;
    private static bool $registered = false;
    private array $config = [];
    
    /**
     * Script critici Salient che NON devono essere ritardati
     */
    private const CRITICAL_SCRIPTS = [
        'jquery',
        'modernizr',
        'touchswipe',
        'salient-',
        'nectar-',
        'wpbakery',
        'vc_',
        'wpb_composer',
    ];
    
    /**
     * Font icons Salient da precaricare
     */
    private const CRITICAL_FONTS = [
        '/css/fonts/icomoon.woff2',
        '/css/fonts/fontello.woff2',
        '/css/fonts/iconsmind.woff2',
    ];
    
    /**
     * Elementi Salient che causano CLS (Layout Shift)
     */
    private const CLS_ELEMENTS = [
        '.nectar-slider-wrap',
        '.nectar-parallax-scene',
        '.portfolio-items',
        '.nectar-fancy-box',
    ];

    public function __construct(ServiceContainer $container, ThemeDetector $detector)
    {
        $this->container = $container;
        $this->detector = $detector;
        
        // Carica configurazione
        $this->loadConfig();
    }

    /**
     * Carica configurazione da opzioni WordPress
     */
    private function loadConfig(): void
    {
        $defaults = [
            'enabled' => true,
            'optimize_scripts' => true,
            'optimize_styles' => true,
            'optimize_fonts' => true,
            'fix_cls' => true,
            'optimize_animations' => true,
            'optimize_parallax' => true,
            'optimize_portfolio' => true,
            'cache_builder_content' => true,
            'preload_critical_assets' => true,
        ];
        
        $this->config = wp_parse_args(
            get_option('fp_ps_salient_wpbakery_config', []),
            $defaults
        );
    }

    /**
     * Registra gli hook di ottimizzazione
     */
    public function register(): void
    {
        // Evita registrazioni multiple
        if (self::$registered) {
            return;
        }
        
        // Verifica che Salient e WPBakery siano attivi
        if (!$this->shouldActivate()) {
            Logger::debug('Salient/WPBakery optimizer: tema o builder non rilevato');
            return;
        }
        
        if (!$this->config['enabled']) {
            Logger::debug('Salient/WPBakery optimizer: disabilitato dalla configurazione');
            return;
        }

        // Ottimizzazioni script
        if ($this->config['optimize_scripts']) {
            HookManager::addActionOnce('wp_enqueue_scripts', [$this, 'optimizeScripts'], 999);
            add_filter('fp_ps_defer_js_exclusions', [$this, 'addScriptExclusions'], 10, 1);
            add_filter('fp_ps_third_party_script_delay', [$this, 'shouldDelayScript'], 10, 2);
        }

        // Ottimizzazioni stili
        if ($this->config['optimize_styles']) {
            HookManager::addActionOnce('wp_enqueue_scripts', [$this, 'optimizeStyles'], 999);
            add_filter('fp_ps_critical_css', [$this, 'addCriticalCSS'], 10, 1);
        }

        // Ottimizzazioni font
        if ($this->config['preload_critical_assets']) {
            HookManager::addActionOnce('wp_head', [$this, 'preloadCriticalAssets'], 5);
        }

        // Fix CLS (Cumulative Layout Shift)
        if ($this->config['fix_cls']) {
            HookManager::addActionOnce('wp_head', [$this, 'addCLSFixes'], 20);
            add_filter('wp_get_attachment_image_attributes', [$this, 'addImageDimensions'], 10, 2);
        }

        // Ottimizzazioni animazioni
        if ($this->config['optimize_animations']) {
            HookManager::addActionOnce('wp_footer', [$this, 'optimizeAnimations'], 100);
        }

        // Ottimizzazioni parallax
        if ($this->config['optimize_parallax']) {
            HookManager::addActionOnce('wp_footer', [$this, 'disableParallaxOnSlow'], 100);
        }

        // Cache contenuto builder
        if ($this->config['cache_builder_content']) {
            add_filter('fp_ps_cache_exclusions', [$this, 'addCacheExclusions'], 10, 1);
            HookManager::addActionOnce('vc_after_save', [$this, 'purgeBuilderCache'], 10);
        }

        // Purge automatico Edge Cache quando si salvano opzioni Salient
        add_action('updated_option', [$this, 'purgeOnSalientUpdate'], 10, 1);

        // Disabilita ottimizzazioni nell'editor WPBakery
        add_filter('fp_ps_should_optimize', [$this, 'shouldOptimize'], 10, 1);

        self::$registered = true;
        
        Logger::info('Salient/WPBakery optimizer registered', [
            'theme' => $this->detector->getThemeName(),
            'config' => $this->config,
        ]);
    }

    /**
     * Verifica se l'ottimizzatore deve essere attivato
     */
    private function shouldActivate(): bool
    {
        return $this->detector->isTheme('salient') && 
               $this->detector->hasPageBuilder('wpbakery');
    }

    /**
     * Verifica se le ottimizzazioni devono essere applicate
     */
    public function shouldOptimize(bool $should): bool
    {
        // Non ottimizzare nell'editor WPBakery
        if (isset($_GET['vc_editable']) || isset($_GET['vc_action'])) {
            return false;
        }
        
        return $should;
    }

    /**
     * Ottimizza script Salient/WPBakery
     */
    public function optimizeScripts(): void
    {
        global $wp_scripts;
        
        if (!is_object($wp_scripts)) {
            return;
        }

        // Sposta script non critici nel footer
        $move_to_footer = [
            'nectar-frontend',
            'vc_grid',
            'vc_accordion',
            'vc_tabs',
        ];

        foreach ($move_to_footer as $handle) {
            if (isset($wp_scripts->registered[$handle])) {
                $wp_scripts->registered[$handle]->extra['group'] = 1; // 1 = footer
            }
        }

        // Rimuovi script duplicati WPBakery se non in edit mode
        if (!$this->isBuilderEditMode()) {
            $remove_scripts = [
                'vc_inline_editor',
                'vc_backend_editor',
            ];

            foreach ($remove_scripts as $handle) {
                wp_dequeue_script($handle);
                wp_deregister_script($handle);
            }
        }

        Logger::debug('Salient/WPBakery scripts optimized');
    }

    /**
     * Ottimizza stili Salient/WPBakery
     */
    public function optimizeStyles(): void
    {
        global $wp_styles;
        
        if (!is_object($wp_styles)) {
            return;
        }

        // Rimuovi stili non utilizzati nell'editor se non in edit mode
        if (!$this->isBuilderEditMode()) {
            $remove_styles = [
                'vc_inline_editor',
                'vc_backend_editor',
            ];

            foreach ($remove_styles as $handle) {
                wp_dequeue_style($handle);
                wp_deregister_style($handle);
            }
        }

        // Aggiungi media query per stili non critici
        $defer_styles = [
            'js_composer_front',
        ];

        foreach ($defer_styles as $handle) {
            if (isset($wp_styles->registered[$handle])) {
                $wp_styles->registered[$handle]->args = 'print';
            }
        }

        Logger::debug('Salient/WPBakery styles optimized');
    }

    /**
     * Aggiunge esclusioni per script critici
     */
    public function addScriptExclusions(array $exclusions): array
    {
        return array_merge($exclusions, self::CRITICAL_SCRIPTS);
    }

    /**
     * Determina se uno script deve essere ritardato
     */
    public function shouldDelayScript(bool $should_delay, string $src): bool
    {
        // Non ritardare script Salient/WPBakery critici
        foreach (self::CRITICAL_SCRIPTS as $pattern) {
            if (stripos($src, $pattern) !== false) {
                return false;
            }
        }
        
        return $should_delay;
    }

    /**
     * Aggiunge CSS critico per Salient
     */
    public function addCriticalCSS(string $css): string
    {
        // CSS critico base per elementi above-the-fold di Salient
        $salient_critical = "
        /* Salient Critical CSS */
        body,html{margin:0;padding:0}
        #header-outer{position:fixed;top:0;left:0;width:100%;z-index:1000}
        .nectar-slider-wrap{position:relative;overflow:hidden}
        .nectar-slider-loading{min-height:500px}
        .portfolio-items{position:relative}
        /* Previeni CLS */
        " . $this->generateCLSPreventionCSS() . "
        ";

        return $css . $salient_critical;
    }

    /**
     * Genera CSS per prevenire CLS
     */
    private function generateCLSPreventionCSS(): string
    {
        $cls_css = '';
        
        foreach (self::CLS_ELEMENTS as $selector) {
            $cls_css .= "{$selector}{min-height:1px;position:relative;}";
        }
        
        return $cls_css;
    }

    /**
     * Precarica asset critici
     */
    public function preloadCriticalAssets(): void
    {
        $theme_url = get_template_directory_uri();
        
        // Precarica font icons critici
        foreach (self::CRITICAL_FONTS as $font) {
            $font_url = $theme_url . $font;
            if (file_exists(get_template_directory() . $font)) {
                echo '<link rel="preload" href="' . esc_url($font_url) . '" as="font" type="font/woff2" crossorigin>' . "\n";
            }
        }

        // Preconnect a domini esterni comuni
        $external_domains = [
            'fonts.googleapis.com',
            'fonts.gstatic.com',
        ];

        foreach ($external_domains as $domain) {
            echo '<link rel="preconnect" href="https://' . esc_attr($domain) . '" crossorigin>' . "\n";
        }

        Logger::debug('Critical assets preloaded for Salient/WPBakery');
    }

    /**
     * Aggiunge fix CSS per CLS (Cumulative Layout Shift)
     */
    public function addCLSFixes(): void
    {
        ?>
        <style id="salient-cls-fixes">
        /* Fix CLS per Salient */
        .nectar-slider-wrap:not(.loaded) {
            min-height: 500px;
            background: #f5f5f5;
        }
        .portfolio-items:empty {
            min-height: 300px;
        }
        .nectar-parallax-scene {
            will-change: auto; /* Riduci layer promotion */
        }
        img[width][height] {
            height: auto; /* Mantieni aspect ratio */
        }
        </style>
        <?php
    }

    /**
     * Aggiunge dimensioni alle immagini per prevenire CLS
     */
    public function addImageDimensions(array $attr, object $attachment): array
    {
        if (empty($attr['width']) || empty($attr['height'])) {
            $meta = wp_get_attachment_metadata($attachment->ID);
            if ($meta && isset($meta['width']) && isset($meta['height'])) {
                $attr['width'] = $meta['width'];
                $attr['height'] = $meta['height'];
            }
        }
        
        return $attr;
    }

    /**
     * Ottimizza animazioni Salient
     */
    public function optimizeAnimations(): void
    {
        ?>
        <script>
        (function() {
            'use strict';
            
            // Ottimizza animazioni Salient per performance
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ready(function($) {
                    // Usa Intersection Observer per animazioni solo quando visibili
                    if ('IntersectionObserver' in window) {
                        var animatedElements = $('.nectar-animate, [data-animate]');
                        
                        var observer = new IntersectionObserver(function(entries) {
                            entries.forEach(function(entry) {
                                if (entry.isIntersecting) {
                                    entry.target.classList.add('in-view');
                                    observer.unobserve(entry.target);
                                }
                            });
                        }, {
                            rootMargin: '50px'
                        });
                        
                        animatedElements.each(function() {
                            observer.observe(this);
                        });
                    }
                    
                    // Riduci animazioni se utente preferisce reduced motion
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        $('body').addClass('no-animations');
                    }
                });
            }
        })();
        </script>
        <?php
    }

    /**
     * Disabilita parallax su connessioni lente
     */
    public function disableParallaxOnSlow(): void
    {
        ?>
        <script>
        (function() {
            'use strict';
            
            // Disabilita parallax su connessioni lente
            if (navigator.connection && typeof jQuery !== 'undefined') {
                var effectiveType = navigator.connection.effectiveType;
                
                if (effectiveType === 'slow-2g' || effectiveType === '2g') {
                    jQuery(document).ready(function($) {
                        // Rimuovi parallax Salient
                        $('.nectar-parallax-scene').removeClass('nectar-parallax-scene');
                        $('[data-parallax]').removeAttr('data-parallax');
                        
                        console.log('FP Performance: Parallax disabled on slow connection');
                    });
                }
            }
        })();
        </script>
        <?php
    }

    /**
     * Aggiunge esclusioni cache per editor e AJAX
     */
    public function addCacheExclusions(array $exclusions): array
    {
        return array_merge($exclusions, [
            '?vc_editable',
            '?vc_action',
            '?salient_ajax',
            '?nectar_',
        ]);
    }

    /**
     * Purge cache quando si salva contenuto WPBakery
     */
    public function purgeBuilderCache(int $post_id): void
    {
        // Purge Edge Cache se disponibile
        try {
            $edge = $this->container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
            
            $urls = [
                get_permalink($post_id),
                home_url('/'),
            ];
            
            $edge->purgeUrls($urls);
            
            Logger::info('Edge cache purged after WPBakery save', [
                'post_id' => $post_id,
                'urls' => $urls,
            ]);
        } catch (\Exception $e) {
            Logger::warning('Failed to purge edge cache', [
                'error' => $e->getMessage(),
            ]);
        }

        // Purge Object Cache per questo post
        wp_cache_delete($post_id, 'posts');
        wp_cache_delete($post_id, 'post_meta');
    }

    /**
     * Purge cache quando si aggiornano opzioni Salient
     */
    public function purgeOnSalientUpdate(string $option_name): void
    {
        // Rileva update opzioni Salient
        if (strpos($option_name, 'salient') === false && strpos($option_name, 'nectar') === false) {
            return;
        }

        try {
            $edge = $this->container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
            $edge->purgeAll();
            
            Logger::info('Edge cache purged after Salient option update', [
                'option' => $option_name,
            ]);
        } catch (\Exception $e) {
            Logger::warning('Failed to purge edge cache on option update', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verifica se siamo in modalità edit del builder
     */
    private function isBuilderEditMode(): bool
    {
        return isset($_GET['vc_editable']) || isset($_GET['vc_action']);
    }

    /**
     * Aggiorna configurazione
     */
    public function updateConfig(array $config): void
    {
        $this->config = wp_parse_args($config, $this->config);
        update_option('fp_ps_salient_wpbakery_config', $this->config);
        
        Logger::info('Salient/WPBakery config updated', $this->config);
    }

    /**
     * Ottiene configurazione corrente
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Ottiene statistiche ottimizzazioni
     */
    public function getStats(): array
    {
        return [
            'theme' => $this->detector->getThemeName(),
            'builder' => 'WPBakery',
            'active' => $this->shouldActivate(),
            'config' => $this->config,
            'critical_scripts' => self::CRITICAL_SCRIPTS,
            'critical_fonts' => self::CRITICAL_FONTS,
        ];
    }
}

