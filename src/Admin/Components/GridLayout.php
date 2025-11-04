<?php

namespace FP\PerfSuite\Admin\Components;

/**
 * Grid Layout Component
 * 
 * Componente riutilizzabile per layout grid responsive
 * Elimina la duplicazione di stili inline per i grid
 * 
 * @package FP\PerfSuite\Admin\Components
 * @since 1.7.0
 */
class GridLayout
{
    /**
     * Preset di grid comuni
     */
    const GRID_TWO = 2;
    const GRID_THREE = 3;
    const GRID_FOUR = 4;
    const GRID_AUTO_FIT = 'auto-fit'; // Auto-fit con minmax
    
    /**
     * Renderizza un grid layout
     * 
     * @param string $content Contenuto HTML del grid
     * @param int|string $columns Numero colonne o 'auto-fit' (default: auto-fit)
     * @param int $min_width Larghezza minima colonne per auto-fit (default: 300px)
     * @param int $gap Gap tra elementi (default: 20px)
     * @param string $extra_classes Classi CSS aggiuntive
     * @return string HTML del grid wrapper
     */
    public static function render(
        string $content,
        $columns = self::GRID_AUTO_FIT,
        int $min_width = 300,
        int $gap = 20,
        string $extra_classes = ''
    ): string {
        if ($columns === self::GRID_AUTO_FIT) {
            $grid_template = "repeat(auto-fit, minmax({$min_width}px, 1fr))";
        } else {
            $grid_template = "repeat({$columns}, 1fr)";
        }
        
        $classes = trim("fp-ps-grid {$extra_classes}");
        
        return sprintf(
            '<div class="%s" style="display: grid; grid-template-columns: %s; gap: %dpx; margin-bottom: 30px;">%s</div>',
            esc_attr($classes),
            esc_attr($grid_template),
            (int) $gap,
            $content // Content già escaped dal chiamante
        );
    }
    
    /**
     * Renderizza un grid item con card styling
     * 
     * @param string $content Contenuto HTML dell'item
     * @param bool $add_padding Aggiungere padding (default: true)
     * @param bool $add_shadow Aggiungere box-shadow (default: true)
     * @return string HTML dell'item
     */
    public static function renderItem(
        string $content,
        bool $add_padding = true,
        bool $add_shadow = true
    ): string {
        $styles = [];
        
        if ($add_padding) {
            $styles[] = 'padding: 20px';
        }
        
        if ($add_shadow) {
            $styles[] = 'box-shadow: 0 2px 4px rgba(0,0,0,0.1)';
        }
        
        $styles[] = 'border-radius: 8px';
        $styles[] = 'background: white';
        
        $style_attr = implode('; ', $styles);
        
        return sprintf(
            '<div class="fp-ps-grid-item" style="%s">%s</div>',
            esc_attr($style_attr),
            $content // Content già escaped dal chiamante
        );
    }
    
    /**
     * Wrapper per grid con 2 colonne
     * 
     * @param string $content Contenuto HTML
     * @param int $gap Gap (default: 20px)
     * @return string HTML
     */
    public static function twoColumns(string $content, int $gap = 20): string
    {
        return self::render($content, self::GRID_TWO, 0, $gap, 'two');
    }
    
    /**
     * Wrapper per grid con 3 colonne
     * 
     * @param string $content Contenuto HTML
     * @param int $gap Gap (default: 20px)
     * @return string HTML
     */
    public static function threeColumns(string $content, int $gap = 20): string
    {
        return self::render($content, self::GRID_THREE, 0, $gap, 'three');
    }
    
    /**
     * Wrapper per grid con 4 colonne
     * 
     * @param string $content Contenuto HTML
     * @param int $gap Gap (default: 20px)
     * @return string HTML
     */
    public static function fourColumns(string $content, int $gap = 20): string
    {
        return self::render($content, self::GRID_FOUR, 0, $gap, 'four');
    }
    
    /**
     * Wrapper per grid auto-fit responsive
     * 
     * @param string $content Contenuto HTML
     * @param int $min_width Larghezza minima (default: 300px)
     * @param int $gap Gap (default: 20px)
     * @return string HTML
     */
    public static function autoFit(string $content, int $min_width = 300, int $gap = 20): string
    {
        return self::render($content, self::GRID_AUTO_FIT, $min_width, $gap, 'auto-fit');
    }
}

