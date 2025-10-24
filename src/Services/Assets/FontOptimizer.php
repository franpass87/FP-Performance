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
        add_action('wp_head', [$this, 'addFontOptimizations'], 25);
        add_filter('style_loader_tag', [$this, 'optimizeFontLoading'], 12, 2);
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
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}