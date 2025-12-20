<?php

namespace FP\PerfSuite\Services\Assets\CSS;

/**
 * Analizza i file CSS per identificare quelli non utilizzati
 * 
 * @package FP\PerfSuite\Services\Assets\CSS
 * @author Francesco Passeri
 */
class CSSFileAnalyzer
{
    /**
     * Ottiene la lista dei file CSS non utilizzati basata su report Lighthouse
     * 
     * @return array Array associativo con handle => configurazione
     */
    public function getUnusedCSSFiles(): array
    {
        return [
            // Dashicons (35.8 KiB savings)
            'dashicons' => [
                'remove' => true,
                'reason' => 'Lighthouse: 35.8 KiB unused CSS',
                'savings' => '35.8 KiB'
            ],
            
            // Theme style.css (35.6 KiB savings)
            'theme-style' => [
                'remove' => false, // Keep but defer
                'reason' => 'Lighthouse: 35.6 KiB unused CSS - deferring instead of removing',
                'savings' => '35.6 KiB deferred'
            ],
            
            // Salient dynamic styles (19.8 KiB savings)
            'salient-dynamic-styles' => [
                'remove' => true,
                'reason' => 'Lighthouse: 19.8 KiB unused CSS',
                'savings' => '19.8 KiB'
            ],
            
            // Instagram plugin (18.1 KiB savings)
            'sb_instagram_styles' => [
                'remove' => true,
                'reason' => 'Lighthouse: 18.1 KiB unused CSS',
                'savings' => '18.1 KiB'
            ],
            
            // Font Awesome legacy (11.0 KiB savings)
            'font-awesome' => [
                'remove' => true,
                'reason' => 'Lighthouse: 11.0 KiB unused CSS',
                'savings' => '11.0 KiB'
            ],
            
            // Material skin (10.0 KiB savings)
            'skin-material' => [
                'remove' => true,
                'reason' => 'Lighthouse: 10.0 KiB unused CSS',
                'savings' => '10.0 KiB'
            ]
        ];
    }

    /**
     * Verifica se un CSS dovrebbe essere deferrato
     * 
     * @param string $handle Handle del CSS
     * @param string $href URL del CSS
     * @return bool True se dovrebbe essere deferrato
     */
    public function shouldDeferCSS(string $handle, string $href): bool
    {
        $deferHandles = [
            'theme-style',
            'main-style',
            'style',
            'wp-block-library',
            'wp-block-library-theme',
            'global-styles'
        ];

        $deferPatterns = [
            'style.css',
            'main.css',
            'theme.css',
            'block-library'
        ];

        // Check handle
        foreach ($deferHandles as $deferHandle) {
            if (strpos($handle, $deferHandle) !== false) {
                return true;
            }
        }

        // Check URL patterns
        foreach ($deferPatterns as $pattern) {
            if (strpos($href, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
















