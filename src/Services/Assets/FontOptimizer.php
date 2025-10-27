<?php

namespace FP\PerfSuite\Services\Assets;

class FontOptimizer
{
    private $preload_critical;
    private $display_swap;
    
    public function __construct($preload_critical = true, $display_swap = true)
    {
        $this->preload_critical = $preload_critical;
        $this->display_swap = $display_swap;
    }
    
    public function init()
    {
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_head', [$this, 'addFontOptimizations'], 25);
            add_filter('style_loader_tag', [$this, 'optimizeFontLoading'], 12, 2);
        }
    }
    
    public function addFontOptimizations()
    {
        if ($this->preload_critical) {
            $this->addPreloadLinks();
        }
        
        if ($this->display_swap) {
            $this->addDisplaySwap();
        }
    }
    
    private function addPreloadLinks()
    {
        $critical_fonts = $this->getCriticalFonts();
        
        foreach ($critical_fonts as $font) {
            echo '<link rel="preload" href="' . esc_url($font['url']) . '" as="font" type="font/' . $font['type'] . '" crossorigin>';
        }
    }
    
    private function addDisplaySwap()
    {
        echo '<style>
            @font-face {
                font-display: swap;
            }
        </style>';
    }
    
    public function optimizeFontLoading($tag, $handle)
    {
        if (strpos($handle, 'font') !== false) {
            $tag = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $tag);
        }
        
        return $tag;
    }
    
    private function getCriticalFonts()
    {
        $fonts = [];
        
        // Get theme fonts
        $theme_fonts = get_theme_support('fonts');
        if ($theme_fonts) {
            foreach ($theme_fonts[0] as $font) {
                $fonts[] = [
                    'url' => $font['url'],
                    'type' => $font['type'] ?? 'woff2'
                ];
            }
        }
        
        return $fonts;
    }
    
    public function getFontMetrics()
    {
        return [
            'total_fonts' => count($this->getCriticalFonts()),
            'preload_enabled' => $this->preload_critical,
            'display_swap_enabled' => $this->display_swap
        ];
    }
    
    /**
     * Restituisce lo stato dell'ottimizzazione font
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        return [
            'enabled' => $this->preload_critical || $this->display_swap,
            'preload_critical' => $this->preload_critical,
            'display_swap' => $this->display_swap,
        ];
    }
    
    /**
     * Restituisce le impostazioni dell'ottimizzazione font
     * 
     * @return array Array con tutte le impostazioni
     */
    public function getSettings(): array
    {
        $settings = get_option('fp_ps_font_optimizer', [
            'enabled' => false,
            'preload_critical' => true,
            'display_swap' => true,
            'optimize_google_fonts' => true,
            'self_host_google_fonts' => false,
            'subset_fonts' => true,
            'remove_unused_fonts' => false,
        ]);
        
        return $settings;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}