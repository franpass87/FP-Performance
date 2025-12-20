<?php

namespace FP\PerfSuite\Services\Assets\CSS;

/**
 * Gestisce il defer del caricamento CSS
 * 
 * @package FP\PerfSuite\Services\Assets\CSS
 * @author Francesco Passeri
 */
class CSSDeferManager
{
    /**
     * Applica defer a un tag CSS
     * 
     * @param string $html HTML del tag CSS
     * @param string $href URL del CSS
     * @param string $media Media query
     * @return string HTML modificato
     */
    public function deferCSS(string $html, string $href, string $media): string
    {
        // Convert to preload with onload
        $html = str_replace(
            'rel="stylesheet"',
            'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"',
            $html
        );

        // Add optimization attributes
        $html = str_replace(
            '<link',
            '<link data-fp-optimized="deferred"',
            $html
        );

        // Add noscript fallback
        if (strpos($html, '</head>') !== false) {
            $noscript = '<noscript><link rel="stylesheet" href="' . esc_attr($href) . '" media="' . esc_attr($media) . '"></noscript>';
            $html = str_replace('</head>', $noscript . '</head>', $html);
        }

        return $html;
    }
}
















