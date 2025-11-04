<?php

namespace FP\PerfSuite\Admin\Components;

use function esc_html;

/**
 * Info Box Component
 * 
 * Componente riutilizzabile per info boxes con gradient o colori solidi
 * Elimina la duplicazione di stili inline per i pannelli informativi
 * 
 * @package FP\PerfSuite\Admin\Components
 * @since 1.7.0
 */
class InfoBox
{
    /**
     * Tipi di info box
     */
    const TYPE_INFO = 'info';       // Blu chiaro
    const TYPE_SUCCESS = 'success'; // Verde
    const TYPE_WARNING = 'warning'; // Giallo
    const TYPE_ERROR = 'error';     // Rosso
    const TYPE_GRADIENT = 'gradient'; // Gradient personalizzato
    
    /**
     * Colori per tipo
     */
    private static array $colors = [
        self::TYPE_INFO => ['bg' => '#e0f2fe', 'border' => '#0284c7', 'text' => '#0c4a6e'],
        self::TYPE_SUCCESS => ['bg' => '#dcfce7', 'border' => '#16a34a', 'text' => '#14532d'],
        self::TYPE_WARNING => ['bg' => '#fef3c7', 'border' => '#d97706', 'text' => '#78350f'],
        self::TYPE_ERROR => ['bg' => '#fee2e2', 'border' => '#dc2626', 'text' => '#7f1d1d'],
    ];
    
    /**
     * Renderizza un info box
     * 
     * @param string $title Titolo del box
     * @param string $content Contenuto HTML del box
     * @param string $type Tipo di box (info, success, warning, error, gradient)
     * @param string $gradient_css CSS gradient personalizzato (solo se type=gradient)
     * @return string HTML del box
     */
    public static function render(
        string $title,
        string $content,
        string $type = self::TYPE_INFO,
        string $gradient_css = ''
    ): string {
        if ($type === self::TYPE_GRADIENT && $gradient_css) {
            return self::renderGradient($title, $content, $gradient_css);
        }
        
        $colors = self::$colors[$type] ?? self::$colors[self::TYPE_INFO];
        
        ob_start();
        ?>
        <section class="fp-ps-card fp-ps-info-box" style="background: <?php echo esc_html($colors['bg']); ?>; border: 2px solid <?php echo esc_html($colors['border']); ?>; border-radius: 8px; padding: 25px; margin-bottom: 25px;">
            <h2 style="margin-top: 0; color: <?php echo esc_html($colors['text']); ?>; font-size: 20px;">
                <?php echo esc_html($title); ?>
            </h2>
            <div style="color: <?php echo esc_html($colors['text']); ?>; line-height: 1.6;">
                <?php echo $content; // Content già escaped dal chiamante ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizza un info box con gradient
     * 
     * @param string $title Titolo
     * @param string $content Contenuto HTML
     * @param string $gradient_css CSS gradient
     * @return string HTML del box
     */
    private static function renderGradient(string $title, string $content, string $gradient_css): string
    {
        ob_start();
        ?>
        <section class="fp-ps-card fp-ps-info-box-gradient" style="background: <?php echo esc_html($gradient_css); ?>; border: none; border-radius: 8px; padding: 25px; margin-bottom: 25px; color: white;">
            <h2 style="margin-top: 0; color: white; font-size: 20px;">
                <?php echo esc_html($title); ?>
            </h2>
            <div style="color: white; line-height: 1.6; opacity: 0.95;">
                <?php echo $content; // Content già escaped dal chiamante ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizza un info box con grid di contenuti
     * 
     * @param string $title Titolo del box
     * @param array $items Array di items: ['title' => '', 'content' => '']
     * @param string $type Tipo di box
     * @param int $columns Numero di colonne (default: 2)
     * @return string HTML del box
     */
    public static function renderWithGrid(
        string $title,
        array $items,
        string $type = self::TYPE_INFO,
        int $columns = 2
    ): string {
        $colors = self::$colors[$type] ?? self::$colors[self::TYPE_INFO];
        
        ob_start();
        ?>
        <section class="fp-ps-card fp-ps-info-box" style="background: <?php echo esc_html($colors['bg']); ?>; border: 2px solid <?php echo esc_html($colors['border']); ?>; border-radius: 8px; padding: 25px; margin-bottom: 25px;">
            <h2 style="margin-top: 0; color: <?php echo esc_html($colors['text']); ?>; font-size: 20px;">
                <?php echo esc_html($title); ?>
            </h2>
            
            <div class="fp-ps-grid" style="display: grid; grid-template-columns: repeat(<?php echo esc_html($columns); ?>, 1fr); gap: 20px; margin-top: 20px;">
                <?php foreach ($items as $item): ?>
                    <div style="background: white; padding: 20px; border-radius: 8px;">
                        <h3 style="margin-top: 0; color: <?php echo esc_html($colors['text']); ?>; font-size: 16px;">
                            <?php echo esc_html($item['title'] ?? ''); ?>
                        </h3>
                        <p style="margin: 0; line-height: 1.6; color: <?php echo esc_html($colors['text']); ?>;">
                            <?php echo esc_html($item['content'] ?? ''); ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
}

