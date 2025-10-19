<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Services\Compatibility\ThemeDetector;

/**
 * Theme Asset Configuration
 *
 * Gestisce configurazioni specifiche per asset in base a tema e page builder
 * Centralizza le regole di esclusione script, font HTTP/2 push, e ottimizzazioni immagini
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class ThemeAssetConfiguration
{
    private ThemeDetector $detector;
    
    public function __construct(?ThemeDetector $detector = null)
    {
        $this->detector = $detector ?? new ThemeDetector();
    }
    
    /**
     * Registra tutti i filtri per asset specifici del tema
     */
    public function register(): void
    {
        // Registra esclusioni script basate su tema
        add_filter('fp_ps_third_party_script_delay', [$this, 'filterScriptDelay'], 10, 2);
        
        // Registra font critici per HTTP/2 push
        add_filter('fp_ps_http2_critical_fonts', [$this, 'addCriticalFonts']);
        
        // Registra handle critici per HTTP/2 push
        add_filter('fp_ps_http2_critical_handles', [$this, 'addCriticalHandles'], 10, 2);
    }
    
    /**
     * Filtra se uno script deve essere ritardato
     *
     * @param bool $should_delay Se lo script dovrebbe essere ritardato
     * @param string $src URL dello script
     * @return bool True se deve essere ritardato, false altrimenti
     */
    public function filterScriptDelay(bool $should_delay, string $src): bool
    {
        // Se già deciso di non ritardare, rispetta la decisione
        if (!$should_delay) {
            return false;
        }
        
        // Ottieni pattern di esclusione per il tema corrente
        $excludePatterns = $this->getScriptExclusionPatterns();
        
        // Controlla se lo script matcha un pattern di esclusione
        foreach ($excludePatterns as $pattern) {
            if (stripos($src, $pattern) !== false) {
                return false; // Non ritardare questo script
            }
        }
        
        return $should_delay;
    }
    
    /**
     * Ottieni pattern di esclusione per script in base al tema
     *
     * @return array Lista di pattern da escludere dal delay
     */
    public function getScriptExclusionPatterns(): array
    {
        $patterns = [];
        
        // Salient theme
        if ($this->detector->isSalient()) {
            $patterns = array_merge($patterns, [
                'salient-',
                'nectar-',
                'modernizr',
                'touchswipe',
            ]);
        }
        
        // Avada theme
        if ($this->detector->isAvada()) {
            $patterns = array_merge($patterns, [
                'fusion-',
                'avada-',
            ]);
        }
        
        // Divi theme
        if ($this->detector->isDivi()) {
            $patterns = array_merge($patterns, [
                'et-',
                'divi-',
            ]);
        }
        
        // Page builder specifici
        $builder = $this->detector->detectPageBuilder();
        
        if ($builder['slug'] === 'wpbakery') {
            $patterns = array_merge($patterns, [
                'wpbakery',
                'vc_',
                'js_composer',
            ]);
        }
        
        if ($builder['slug'] === 'elementor') {
            $patterns = array_merge($patterns, [
                'elementor',
            ]);
        }
        
        // jQuery sempre critico per temi/builder dinamici
        $patterns[] = 'jquery';
        
        // Permetti filtro personalizzato
        return apply_filters('fp_ps_theme_script_exclusions', $patterns, $this->detector);
    }
    
    /**
     * Aggiunge font critici per HTTP/2 push basati sul tema
     *
     * @param array $fonts Lista esistente di font
     * @return array Lista aggiornata di font
     */
    public function addCriticalFonts(array $fonts): array
    {
        $theme_uri = get_template_directory_uri();
        
        // Salient theme fonts
        if ($this->detector->isSalient()) {
            $salient_fonts = [
                $theme_uri . '/css/fonts/icomoon.woff2',
                $theme_uri . '/css/fonts/fontello.woff2',
                $theme_uri . '/css/fonts/iconsmind.woff2',
            ];
            
            foreach ($salient_fonts as $font_url) {
                $fonts[] = [
                    'url' => $font_url,
                    'as' => 'font',
                    'type' => 'font/woff2',
                ];
            }
        }
        
        // Avada theme fonts
        if ($this->detector->isAvada()) {
            $avada_fonts = [
                $theme_uri . '/assets/fonts/icomoon.woff2',
                $theme_uri . '/assets/fonts/fontawesome-webfont.woff2',
            ];
            
            foreach ($avada_fonts as $font_url) {
                $fonts[] = [
                    'url' => $font_url,
                    'as' => 'font',
                    'type' => 'font/woff2',
                ];
            }
        }
        
        // Divi theme fonts
        if ($this->detector->isDivi()) {
            $divi_fonts = [
                $theme_uri . '/core/admin/fonts/modules.woff2',
                $theme_uri . '/includes/builder/styles/fonts/ETmodules.woff2',
            ];
            
            foreach ($divi_fonts as $font_url) {
                $fonts[] = [
                    'url' => $font_url,
                    'as' => 'font',
                    'type' => 'font/woff2',
                ];
            }
        }
        
        return $fonts;
    }
    
    /**
     * Aggiunge handle critici per HTTP/2 push basati sul tema
     *
     * @param array $handles Lista esistente di handle critici
     * @param string $type Tipo di risorsa ('script' o 'style')
     * @return array Lista aggiornata di handle
     */
    public function addCriticalHandles(array $handles, string $type): array
    {
        // Salient theme
        if ($this->detector->isSalient()) {
            if ($type === 'style') {
                $handles = array_merge($handles, [
                    'salient-main-styles',
                    'nectar-main-styles',
                    'font-awesome',
                ]);
            } elseif ($type === 'script') {
                $handles = array_merge($handles, [
                    'salient-init',
                    'nectar-frontend',
                ]);
            }
        }
        
        // Avada theme
        if ($this->detector->isAvada()) {
            if ($type === 'style') {
                $handles = array_merge($handles, [
                    'avada-stylesheet',
                    'fusion-dynamic-css',
                ]);
            } elseif ($type === 'script') {
                $handles = array_merge($handles, [
                    'fusion-main',
                    'avada-header',
                ]);
            }
        }
        
        // Divi theme
        if ($this->detector->isDivi()) {
            if ($type === 'style') {
                $handles = array_merge($handles, [
                    'divi-style',
                    'et-builder-modules-style',
                ]);
            } elseif ($type === 'script') {
                $handles = array_merge($handles, [
                    'divi-custom-script',
                    'et-builder-modules-script',
                ]);
            }
        }
        
        // Page builders
        $builder = $this->detector->detectPageBuilder();
        
        if ($builder['slug'] === 'wpbakery') {
            if ($type === 'style') {
                $handles = array_merge($handles, ['js_composer_front']);
            } elseif ($type === 'script') {
                $handles = array_merge($handles, ['wpb_composer_front_js']);
            }
        }
        
        if ($builder['slug'] === 'elementor') {
            if ($type === 'style') {
                $handles = array_merge($handles, ['elementor-frontend', 'elementor-post']);
            } elseif ($type === 'script') {
                $handles = array_merge($handles, ['elementor-frontend', 'elementor-webpack-runtime']);
            }
        }
        
        return $handles;
    }
    
    /**
     * Ottieni configurazione ottimale immagini per il tema corrente
     *
     * @return array Configurazione per ImageOptimizer
     */
    public function getImageOptimizationConfig(): array
    {
        $config = [
            'force_dimensions' => true,
            'add_aspect_ratio' => true,
        ];
        
        // Salient ha problemi con CLS nelle animazioni
        if ($this->detector->isSalient()) {
            $config['force_dimensions'] = true;
            $config['add_aspect_ratio'] = true;
            $config['priority'] = 'high';
        }
        
        // Divi Builder ha molte animazioni
        if ($this->detector->isDivi()) {
            $config['force_dimensions'] = true;
            $config['add_aspect_ratio'] = true;
        }
        
        return apply_filters('fp_ps_theme_image_config', $config, $this->detector);
    }
    
    /**
     * Verifica se AVIF è sicuro per il tema corrente
     *
     * @return array Info su compatibilità AVIF
     */
    public function getAVIFCompatibility(): array
    {
        $compatibility = [
            'safe' => true,
            'warnings' => [],
            'test_required' => false,
        ];
        
        // Salient: testare slider e lightbox
        if ($this->detector->isSalient()) {
            $compatibility['safe'] = false;
            $compatibility['test_required'] = true;
            $compatibility['warnings'][] = 'Testare Nectar Slider e Portfolio Lightbox';
        }
        
        // Avada: testare Fusion Slider
        if ($this->detector->isAvada()) {
            $compatibility['safe'] = false;
            $compatibility['test_required'] = true;
            $compatibility['warnings'][] = 'Testare Fusion Slider e Lightbox';
        }
        
        // Divi: generalmente OK
        if ($this->detector->isDivi()) {
            $compatibility['safe'] = true;
            $compatibility['warnings'][] = 'Monitorare gallerie Divi';
        }
        
        return $compatibility;
    }
    
    /**
     * Ottieni raccomandazioni complete per asset del tema
     *
     * @return array Raccomandazioni complete
     */
    public function getRecommendations(): array
    {
        return [
            'script_exclusions' => $this->getScriptExclusionPatterns(),
            'critical_fonts' => $this->addCriticalFonts([]),
            'image_optimization' => $this->getImageOptimizationConfig(),
            'avif_compatibility' => $this->getAVIFCompatibility(),
        ];
    }
}
