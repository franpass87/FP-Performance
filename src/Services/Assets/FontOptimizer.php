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
     * Aggiorna le impostazioni dell'ottimizzazione font
     * 
     * @param array $settings Array con le nuove impostazioni
     * @return bool True se salvato con successo, false altrimenti
     */
    public function updateSettings(array $settings): bool
    {
        $currentSettings = get_option('fp_ps_font_optimizer', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['enabled'])) {
            $newSettings['enabled'] = (bool) $newSettings['enabled'];
        }
        
        if (isset($newSettings['preload_critical'])) {
            $newSettings['preload_critical'] = (bool) $newSettings['preload_critical'];
        }
        
        if (isset($newSettings['display_swap'])) {
            $newSettings['display_swap'] = (bool) $newSettings['display_swap'];
        }
        
        if (isset($newSettings['optimize_google_fonts'])) {
            $newSettings['optimize_google_fonts'] = (bool) $newSettings['optimize_google_fonts'];
        }
        
        if (isset($newSettings['self_host_google_fonts'])) {
            $newSettings['self_host_google_fonts'] = (bool) $newSettings['self_host_google_fonts'];
        }
        
        if (isset($newSettings['subset_fonts'])) {
            $newSettings['subset_fonts'] = (bool) $newSettings['subset_fonts'];
        }
        
        if (isset($newSettings['remove_unused_fonts'])) {
            $newSettings['remove_unused_fonts'] = (bool) $newSettings['remove_unused_fonts'];
        }
        
        // Sanitizza critical_fonts se presente
        if (isset($newSettings['critical_fonts'])) {
            $newSettings['critical_fonts'] = sanitize_textarea_field($newSettings['critical_fonts']);
        }
        
        // Sanitizza subset_critical se presente
        if (isset($newSettings['subset_critical'])) {
            $newSettings['subset_critical'] = (bool) $newSettings['subset_critical'];
        }
        
        $result = update_option('fp_ps_font_optimizer', $newSettings, false);
        
        // Aggiorna proprietà interne se presente
        if (isset($newSettings['preload_critical'])) {
            $this->preload_critical = $newSettings['preload_critical'];
        }
        if (isset($newSettings['display_swap'])) {
            $this->display_swap = $newSettings['display_swap'];
        }
        
        return $result;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}