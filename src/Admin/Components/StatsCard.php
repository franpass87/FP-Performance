<?php

namespace FP\PerfSuite\Admin\Components;

use function esc_html;

/**
 * Stats Card Component
 * 
 * Componente riutilizzabile per mostrare card statistiche con gradient
 * Elimina la duplicazione di stili inline per le stats cards
 * 
 * @package FP\PerfSuite\Admin\Components
 * @since 1.7.0
 */
class StatsCard
{
    /**
     * Gradient presets disponibili
     */
    const GRADIENT_PURPLE = 'purple';  // #667eea → #764ba2
    const GRADIENT_PINK = 'pink';      // #f093fb → #f5576c
    const GRADIENT_BLUE = 'blue';      // #4facfe → #00f2fe
    const GRADIENT_GREEN = 'green';    // #43e97b → #38f9d7
    const GRADIENT_ORANGE = 'orange';  // #fa709a → #fee140
    
    /**
     * Mappa dei gradient CSS
     */
    private static array $gradients = [
        self::GRADIENT_PURPLE => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        self::GRADIENT_PINK => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        self::GRADIENT_BLUE => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
        self::GRADIENT_GREEN => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        self::GRADIENT_ORANGE => 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    ];
    
    /**
     * Renderizza una stats card
     * 
     * @param string $icon Emoji o icona
     * @param string $label Etichetta della stat
     * @param string|int $value Valore da mostrare
     * @param string $sublabel Sotto-etichetta opzionale
     * @param string $gradient Preset gradient (default: purple)
     * @return string HTML della card
     */
    public static function render(
        string $icon,
        string $label,
        $value,
        string $sublabel = '',
        string $gradient = self::GRADIENT_PURPLE
    ): string {
        $gradient_css = self::$gradients[$gradient] ?? self::$gradients[self::GRADIENT_PURPLE];
        
        ob_start();
        ?>
        <div class="fp-ps-stats-card" style="background: <?php echo esc_html($gradient_css); ?>; color: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">
                <?php echo esc_html($icon); ?> <?php echo esc_html($label); ?>
            </div>
            <div style="font-size: 36px; font-weight: 700; margin: 10px 0;">
                <?php echo esc_html($value); ?>
            </div>
            <?php if ($sublabel): ?>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 5px;">
                    <?php echo esc_html($sublabel); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizza un gruppo di stats cards in grid
     * 
     * @param array $cards Array di cards config: ['icon', 'label', 'value', 'sublabel', 'gradient']
     * @param int $columns Numero di colonne (default: auto-fit 250px)
     * @return string HTML del grid
     */
    public static function renderGrid(array $cards, int $columns = 0): string
    {
        $grid_style = $columns > 0
            ? "display: grid; grid-template-columns: repeat({$columns}, 1fr); gap: 20px; margin-bottom: 30px;"
            : "display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;";
        
        ob_start();
        ?>
        <div style="<?php echo esc_html($grid_style); ?>">
            <?php foreach ($cards as $card): ?>
                <?php
                echo self::render(
                    $card['icon'] ?? '📊',
                    $card['label'] ?? '',
                    $card['value'] ?? '0',
                    $card['sublabel'] ?? '',
                    $card['gradient'] ?? self::GRADIENT_PURPLE
                );
                ?>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

