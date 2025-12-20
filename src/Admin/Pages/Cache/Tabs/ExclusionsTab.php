<?php

namespace FP\PerfSuite\Admin\Pages\Cache\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\Pages\Exclusions;

use function __;
use function esc_html;
use function preg_replace;
use function sprintf;

/**
 * Render della tab Exclusions per Cache page
 * 
 * @package FP\PerfSuite\Admin\Pages\Cache\Tabs
 * @author Francesco Passeri
 */
class ExclusionsTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render della tab Exclusions
     */
    public function render(): string
    {
        ob_start();
        
        // Include Smart Exclusions content
        try {
            $exclusionsPage = new Exclusions($this->container);
            // Estrae solo il contenuto senza il wrapper della pagina
            $content = $exclusionsPage->getContent();
            
            // Rimuove l'intro box se presente
            $content = preg_replace('/<div class="fp-ps-page-intro".*?<\/div>/s', '', $content);
            
            echo $content;
        } catch (\Throwable $e) {
            ?>
            <div class="notice notice-error">
                <p><?php echo esc_html(sprintf(__('Errore nel caricamento di Smart Exclusions: %s', 'fp-performance-suite'), $e->getMessage())); ?></p>
            </div>
            <?php
        }
        
        return (string) ob_get_clean();
    }
}

