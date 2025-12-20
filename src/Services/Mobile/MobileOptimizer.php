<?php

namespace FP\PerfSuite\Services\Mobile;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

/**
 * Mobile Optimizer Service
 * 
 * Ottimizza il sito per dispositivi mobili con touch optimization,
 * responsive images e mobile-specific CSS/JS
 * 
 * SECURITY: Tutte le opzioni sono validate e l'output è escapato
 * 
 * @package FP\PerfSuite\Services\Mobile
 */
class MobileOptimizer
{
    private bool $touch_optimization;
    private bool $mobile_cache;
    private bool $responsive_images;
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /**
     * Costruttore
     * 
     * @param bool $touch_optimization Abilita ottimizzazioni touch
     * @param bool $mobile_cache Abilita cache mobile
     * @param bool $responsive_images Abilita responsive images
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct(bool $touch_optimization = true, bool $mobile_cache = true, bool $responsive_images = true, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->touch_optimization = $touch_optimization;
        $this->mobile_cache = $mobile_cache;
        $this->responsive_images = $responsive_images;
        $this->optionsRepo = $optionsRepo;
    }
    
    /**
     * Inizializza il servizio
     */
    public function init(): void
    {
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_head', [$this, 'addMobileOptimizations'], 17);
            add_action('wp_footer', [$this, 'addMobileScripts'], 45);
        }
        add_filter('wp_get_attachment_image_attributes', [$this, 'optimizeMobileImages'], 10, 3);
    }
    
    /**
     * Aggiunge ottimizzazioni mobile nel <head>
     * 
     * SECURITY FIX: Output validato e escapato per prevenire XSS
     */
    public function addMobileOptimizations(): void
    {
        // SECURITY: Ottieni e valida settings
        $settings = $this->getValidatedSettings();
        
        // Add viewport meta tag - SAFE: valori hardcoded
        $this->outputViewportMeta($settings);
        
        // Add mobile-specific CSS - SAFE: CSS validato
        $this->outputMobileCSS($settings);
    }
    
    /**
     * Output viewport meta tag (SAFE)
     * 
     * @param array $settings Impostazioni validate
     */
    private function outputViewportMeta(array $settings): void
    {
        // SECURITY: Usa valori validati con default safe
        $initialScale = $settings['initial_scale'] ?? '1.0';
        $maximumScale = $settings['maximum_scale'] ?? '1.0';
        $userScalable = $settings['user_scalable'] ? 'yes' : 'no';
        
        // Valida i valori numerici
        $initialScale = $this->validateScale($initialScale);
        $maximumScale = $this->validateScale($maximumScale);
        
        // Output safe con esc_attr
        printf(
            '<meta name="viewport" content="width=device-width, initial-scale=%s, maximum-scale=%s, user-scalable=%s">',
            esc_attr($initialScale),
            esc_attr($maximumScale),
            esc_attr($userScalable)
        );
    }
    
    /**
     * Output mobile CSS (SAFE)
     * 
     * @param array $settings Impostazioni validate
     */
    private function outputMobileCSS(array $settings): void
    {
        // SECURITY: CSS hardcoded e safe
        // Non usa valori da database per prevenire CSS injection
        $css = $this->getSafeMobileCSS($settings);
        
        if (empty($css)) {
            return;
        }
        
        // Output CSS safe (no user input)
        echo '<style id="fp-mobile-optimizer-css">';
        echo wp_strip_all_tags($css); // SECURITY: Strip eventuali tag
        echo '</style>';
    }
    
    /**
     * Genera CSS mobile safe
     * 
     * @param array $settings Impostazioni validate
     * @return string CSS safe
     */
    private function getSafeMobileCSS(array $settings): string
    {
        // SECURITY: CSS completamente hardcoded, nessun input utente
        $maxWidth = 768; // Hardcoded, non da settings
        
        $css = "@media (max-width: {$maxWidth}px) {";
        $css .= ".mobile-optimized { font-size: 16px; line-height: 1.5; }";
        $css .= ".mobile-optimized img { max-width: 100%; height: auto; }";
        $css .= ".mobile-optimized .lazy-load { opacity: 0; transition: opacity 0.3s; }";
        $css .= ".mobile-optimized .lazy-load.loaded { opacity: 1; }";
        $css .= "}";
        
        return $css;
    }
    
    /**
     * Ottimizza attributi immagini per mobile
     * 
     * @param array $attr Attributi immagine
     * @param \WP_Post $attachment Attachment post
     * @param string|array $size Dimensione immagine
     * @return array Attributi ottimizzati
     */
    public function optimizeMobileImages($attr, $attachment, $size): array
    {
        if ($this->responsive_images && is_array($attr)) {
            // SECURITY: Valori safe hardcoded
            $attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw';
            $attr['loading'] = 'lazy';
        }
        
        return $attr;
    }
    
    /**
     * Aggiunge script mobile nel footer
     * 
     * SECURITY FIX: Usa wp_add_inline_script invece di echo diretto
     */
    public function addMobileScripts(): void
    {
        if (!$this->touch_optimization) {
            return;
        }
        
        // SECURITY FIX: Usa wp_add_inline_script per migliore sicurezza
        // Registra uno script dummy se non esiste già
        if (!wp_script_is('fp-mobile-optimizer', 'registered')) {
            wp_register_script('fp-mobile-optimizer', '', [], '1.0', true);
        }
        
        // Aggiungi script inline in modo sicuro
        wp_enqueue_script('fp-mobile-optimizer');
        wp_add_inline_script('fp-mobile-optimizer', $this->getSafeMobileScript(), 'after');
    }
    
    /**
     * Genera JavaScript mobile safe
     * 
     * @return string JavaScript safe (hardcoded, no user input)
     */
    private function getSafeMobileScript(): string
    {
        // SECURITY: JavaScript completamente hardcoded
        // Nessun valore da database o input utente
        
        $script = '
        (function() {
            "use strict";
            
            // Mobile Touch Optimization
            if ("ontouchstart" in window) {
                document.addEventListener("touchstart", function(e) {
                    if (e.target && e.target.classList) {
                        e.target.classList.add("touch-active");
                    }
                });
                
                document.addEventListener("touchend", function(e) {
                    if (e.target && e.target.classList) {
                        setTimeout(function() {
                            e.target.classList.remove("touch-active");
                        }, 150);
                    }
                });
                
                // Optimize touch scrolling
                document.addEventListener("touchmove", function(e) {
                    if (e.target && e.target.closest && e.target.closest(".scrollable")) {
                        e.preventDefault();
                    }
                }, { passive: false });
            }
            
            // Mobile-specific optimizations
            if (window.innerWidth <= 768) {
                // Lazy load images on mobile
                var images = document.querySelectorAll("img[data-src]");
                
                if ("IntersectionObserver" in window && images.length > 0) {
                    var imageObserver = new IntersectionObserver(function(entries, observer) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                var img = entry.target;
                                if (img.dataset.src) {
                                    img.src = img.dataset.src;
                                    img.classList.add("loaded");
                                    observer.unobserve(img);
                                }
                            }
                        });
                    });
                    
                    images.forEach(function(img) {
                        imageObserver.observe(img);
                    });
                }
            }
        })();
        ';
        
        return $script;
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Ottiene impostazioni validate
     * 
     * SECURITY: Validazione completa di tutte le opzioni
     * 
     * @return array Impostazioni validate
     */
    private function getValidatedSettings(): array
    {
        $settings = $this->getOption('fp_ps_mobile_optimizer', []);
        
        // SECURITY: Valida ogni campo con default safe
        return [
            'enabled' => !empty($settings['enabled']),
            'initial_scale' => $this->validateScale($settings['initial_scale'] ?? '1.0'),
            'maximum_scale' => $this->validateScale($settings['maximum_scale'] ?? '1.0'),
            'user_scalable' => !empty($settings['user_scalable']),
            'disable_animations' => !empty($settings['disable_animations']),
            'remove_unnecessary_scripts' => !empty($settings['remove_unnecessary_scripts']),
            'optimize_touch_targets' => !empty($settings['optimize_touch_targets']),
            'enable_responsive_images' => !empty($settings['enable_responsive_images']),
        ];
    }
    
    /**
     * Valida valore scale per viewport
     * 
     * @param mixed $value Valore da validare
     * @return string Valore validato (1.0 se invalido)
     */
    private function validateScale($value): string
    {
        // SECURITY: Valida che sia numero valido tra 0.1 e 10.0
        $float = filter_var($value, FILTER_VALIDATE_FLOAT);
        
        if ($float === false || $float < 0.1 || $float > 10.0) {
            return '1.0'; // Default safe
        }
        
        // Ritorna con max 1 decimale
        return number_format($float, 1, '.', '');
    }
    
    /**
     * Ottiene metriche mobile
     * 
     * @return array Metriche mobile
     */
    public function getMobileMetrics(): array
    {
        return [
            'touch_optimization' => $this->touch_optimization,
            'mobile_cache' => $this->mobile_cache,
            'responsive_images' => $this->responsive_images,
            'is_mobile' => wp_is_mobile()
        ];
    }
    
    /**
     * Genera un report completo delle ottimizzazioni mobile
     * 
     * @return array Report con stato, impostazioni, issues e raccomandazioni
     */
    public function generateMobileReport(): array
    {
        // SECURITY: Usa getValidatedSettings() invece di get_option diretto
        $settings = $this->getValidatedSettings();
        
        $issues = [];
        $recommendations = [];
        $critical_issues = 0;
        
        // Verifica se le ottimizzazioni sono abilitate
        if (!$settings['enabled']) {
            $issues[] = [
                'type' => 'warning',
                'message' => __('Mobile optimization is disabled', 'fp-performance-suite'),
            ];
            $recommendations[] = __('Enable mobile optimization to improve mobile performance', 'fp-performance-suite');
        }
        
        // Verifica responsive images
        if (!$settings['enable_responsive_images']) {
            $issues[] = [
                'type' => 'info',
                'message' => __('Responsive images optimization is disabled', 'fp-performance-suite'),
            ];
        }
        
        // Verifica touch targets
        if (!$settings['optimize_touch_targets']) {
            $issues[] = [
                'type' => 'info',
                'message' => __('Touch targets optimization is disabled', 'fp-performance-suite'),
            ];
        }
        
        return [
            'enabled' => $settings['enabled'],
            'settings' => $settings,
            'issues' => $issues,
            'issues_count' => count($issues),
            'critical_issues' => $critical_issues,
            'recommendations' => $recommendations,
            'is_mobile_device' => wp_is_mobile(),
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}