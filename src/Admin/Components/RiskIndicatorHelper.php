<?php

namespace FP\PerfSuite\Admin\Components;

use FP\PerfSuite\Admin\RiskMatrix;

/**
 * Risk Indicator Helper
 * 
 * Helper functions per semplificare l'aggiunta di indicatori di rischio
 * nei form admin
 * 
 * @package FP\PerfSuite\Admin\Components
 */
class RiskIndicatorHelper
{
    /**
     * Renderizza un toggle con indicatore di rischio integrato
     * 
     * @param string $option_key Chiave opzione nella RiskMatrix
     * @param string $name Nome del campo input
     * @param string $label Label del toggle
     * @param bool $checked Stato checked
     * @param string $description Descrizione opzionale
     * @return string HTML del toggle completo
     */
    public static function renderToggle(
        string $option_key,
        string $name,
        string $label,
        bool $checked,
        string $description = ''
    ): string {
        $riskLevel = RiskMatrix::getRiskLevel($option_key);
        $indicator = RiskMatrix::renderIndicator($option_key);
        
        ob_start();
        ?>
        <label class="fp-ps-toggle">
            <span class="info">
                <strong><?php echo esc_html($label); ?></strong>
                <?php echo $indicator; // Already escaped in RiskMatrix ?>
                <?php if ($description) : ?>
                <small><?php echo esc_html($description); ?></small>
                <?php endif; ?>
            </span>
            <input type="checkbox" 
                   name="<?php echo esc_attr($name); ?>" 
                   value="1" 
                   <?php checked($checked); ?> 
                   data-risk="<?php echo esc_attr($riskLevel); ?>" />
        </label>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizza solo l'indicatore inline
     * 
     * @param string $option_key Chiave opzione nella RiskMatrix
     * @return string HTML dell'indicatore
     */
    public static function renderInline(string $option_key): string
    {
        return RiskMatrix::renderIndicator($option_key);
    }
    
    /**
     * Stampa direttamente un toggle
     * 
     * @param string $option_key Chiave opzione nella RiskMatrix
     * @param string $name Nome del campo input
     * @param string $label Label del toggle
     * @param bool $checked Stato checked
     * @param string $description Descrizione opzionale
     */
    public static function toggle(
        string $option_key,
        string $name,
        string $label,
        bool $checked,
        string $description = ''
    ): void {
        echo self::renderToggle($option_key, $name, $label, $checked, $description);
    }
    
    /**
     * Stampa direttamente un indicatore inline
     * 
     * @param string $option_key Chiave opzione nella RiskMatrix
     */
    public static function inline(string $option_key): void
    {
        echo self::renderInline($option_key);
    }
}

