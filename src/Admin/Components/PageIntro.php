<?php

namespace FP\PerfSuite\Admin\Components;

/**
 * Page Intro Component
 * 
 * Componente riutilizzabile per mostrare l'intro box nelle pagine admin
 * 
 * @package FP\PerfSuite\Admin\Components
 * @author Francesco Passeri
 */
class PageIntro
{
    /**
     * Renderizza l'intro box della pagina
     * 
     * @param string $icon Emoji o icona da mostrare nel titolo
     * @param string $title Titolo della pagina
     * @param string $description Descrizione della pagina
     * @return string HTML dell'intro box
     */
    public static function render(string $icon, string $title, string $description): string
    {
        ob_start();
        ?>
        
        <!-- Page Intro Box -->
        <!-- BUGFIX #14b: Testo bianco inline per massima leggibilità su gradiente viola -->
        <div class="fp-ps-intro-panel" style="color: white;">
            <h2 class="fp-ps-intro-title" style="color: white;">
                <?php if ($icon): ?>
                    <?php echo esc_html($icon); ?> 
                <?php endif; ?>
                <?php echo esc_html($title); ?>
            </h2>
            <p class="fp-ps-intro-description" style="color: white !important;">
                <?php echo esc_html($description); ?>
            </p>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Renderizza l'intro box con HTML personalizzato nella descrizione
     * 
     * @param string $icon Emoji o icona da mostrare nel titolo
     * @param string $title Titolo della pagina
     * @param string $description_html HTML della descrizione (già escapato)
     * @return string HTML dell'intro box
     */
    public static function renderWithHtml(string $icon, string $title, string $description_html): string
    {
        ob_start();
        ?>
        
        <!-- Page Intro Box -->
        <!-- BUGFIX #14b: Testo bianco inline per massima leggibilità su gradiente viola -->
        <div class="fp-ps-intro-panel" style="color: white;">
            <h2 class="fp-ps-intro-title" style="color: white;">
                <?php if ($icon): ?>
                    <?php echo esc_html($icon); ?> 
                <?php endif; ?>
                <?php echo esc_html($title); ?>
            </h2>
            <div class="fp-ps-intro-description" style="color: white !important;">
                <?php echo $description_html; // Già escapato dal chiamante ?>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
}
