<?php

namespace FP\PerfSuite\Services\Mobile;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\MobileRateLimiter;

/**
 * Responsive Image Manager Service
 * 
 * Gestione avanzata delle immagini responsive per mobile
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ResponsiveImageManager
{
    private const OPTION = 'fp_ps_responsive_images';

    /**
     * Registra gli hook per la gestione immagini responsive
     */
    public function register(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        add_filter('wp_get_attachment_image_attributes', [$this, 'optimizeImageAttributes'], 10, 3);
        add_filter('the_content', [$this, 'optimizeContentImages'], 20);
        add_action('wp_head', [$this, 'addResponsiveImageCSS'], 20);
        
        Logger::debug('Responsive image manager registered');
    }

    /**
     * Ottimizza attributi delle immagini per mobile
     */
    public function optimizeImageAttributes(array $attr, \WP_Post $attachment, $size): array
    {
        if (!$this->isMobile()) {
            return $attr;
        }

        // Rate limiting per responsive images
        if (!MobileRateLimiter::isAllowed('responsive_images')) {
            Logger::debug('Responsive image optimization rate limited');
            return $attr;
        }

        $settings = $this->settings();
        
        // Aggiungi loading lazy per mobile
        if ($settings['enable_lazy_loading']) {
            $attr['loading'] = 'lazy';
        }
        
        // Ottimizza srcset per mobile
        if ($settings['optimize_srcset']) {
            $attr = $this->optimizeSrcset($attr, $attachment, $size);
        }
        
        // Aggiungi dimensioni ottimali per mobile
        if ($settings['add_mobile_dimensions']) {
            $attr = $this->addMobileDimensions($attr, $attachment, $size);
        }
        
        return $attr;
    }

    /**
     * Ottimizza immagini nel contenuto per mobile
     */
    public function optimizeContentImages(string $content): string
    {
        if (!$this->isMobile() || empty($content)) {
            return $content;
        }

        $settings = $this->settings();
        
        // Aggiungi lazy loading alle immagini nel contenuto
        if ($settings['enable_content_lazy_loading']) {
            $content = $this->addLazyLoadingToContent($content);
        }
        
        // Ottimizza dimensioni immagini nel contenuto
        if ($settings['optimize_content_image_sizes']) {
            $content = $this->optimizeContentImageSizes($content);
        }
        
        return $content;
    }

    /**
     * Aggiunge CSS per immagini responsive
     */
    public function addResponsiveImageCSS(): void
    {
        if (!$this->isMobile()) {
            return;
        }

        $css = $this->generateResponsiveImageCSS();
        echo '<style id="fp-ps-responsive-images">' . $css . '</style>';
    }

    /**
     * Ottimizza srcset per mobile
     */
    private function optimizeSrcset(array $attr, \WP_Post $attachment, $size): array
    {
        $settings = $this->settings();
        
        if (isset($attr['srcset'])) {
            $srcset = $attr['srcset'];
            
            // Filtra srcset per mobile (solo dimensioni appropriate)
            $mobile_srcset = $this->filterSrcsetForMobile($srcset);
            
            if (!empty($mobile_srcset)) {
                $attr['srcset'] = $mobile_srcset;
            }
        }
        
        return $attr;
    }

    /**
     * Aggiunge dimensioni ottimali per mobile
     */
    private function addMobileDimensions(array $attr, \WP_Post $attachment, $size): array
    {
        $settings = $this->settings();
        
        // Imposta dimensioni massime per mobile
        if ($settings['max_mobile_width']) {
            $attr['style'] = 'max-width: ' . $settings['max_mobile_width'] . 'px; height: auto;';
        }
        
        return $attr;
    }

    /**
     * Aggiunge lazy loading al contenuto
     */
    private function addLazyLoadingToContent(string $content): string
    {
        // Trova tutte le immagini nel contenuto
        $pattern = '/<img([^>]+)>/i';
        
        $content = preg_replace_callback($pattern, function($matches) {
            $img_tag = $matches[0];
            $attributes = $matches[1];
            
            // Controlla se ha già loading attribute
            if (strpos($attributes, 'loading=') === false) {
                $img_tag = str_replace('<img', '<img loading="lazy"', $img_tag);
            }
            
            return $img_tag;
        }, $content);
        
        return $content;
    }

    /**
     * Ottimizza dimensioni immagini nel contenuto
     */
    private function optimizeContentImageSizes(string $content): string
    {
        $settings = $this->settings();
        
        // Aggiungi CSS per limitare dimensioni immagini nel contenuto
        $max_width = $settings['max_content_image_width'] ?? '100%';
        
        $css = '
        .entry-content img,
        .post-content img,
        .page-content img {
            max-width: ' . $max_width . ';
            height: auto;
        }
        ';
        
        // Inietta CSS se non già presente
        if (strpos($content, 'fp-ps-content-images') === false) {
            $content = '<style id="fp-ps-content-images">' . $css . '</style>' . $content;
        }
        
        return $content;
    }

    /**
     * Filtra srcset per mobile
     */
    private function filterSrcsetForMobile(string $srcset): string
    {
        $settings = $this->settings();
        $max_width = $settings['max_mobile_image_width'] ?? 768;
        
        $srcset_parts = explode(',', $srcset);
        $mobile_srcset = [];
        
        foreach ($srcset_parts as $part) {
            $part = trim($part);
            
            // Estrai larghezza dal srcset
            if (preg_match('/(\d+)w/', $part, $matches)) {
                $width = (int) $matches[1];
                
                // Includi solo dimensioni appropriate per mobile
                if ($width <= $max_width) {
                    $mobile_srcset[] = $part;
                }
            }
        }
        
        return implode(', ', $mobile_srcset);
    }

    /**
     * Genera CSS per immagini responsive
     */
    private function generateResponsiveImageCSS(): string
    {
        $settings = $this->settings();
        
        return '
        @media screen and (max-width: 768px) {
            /* Responsive images */
            img {
                max-width: 100%;
                height: auto;
            }
            
            /* Lazy loading placeholder */
            img[loading="lazy"] {
                background-color: #f0f0f0;
                background-image: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: loading 1.5s infinite;
            }
            
            @keyframes loading {
                0% { background-position: 200% 0; }
                100% { background-position: -200% 0; }
            }
            
            /* Optimize image containers */
            .wp-block-image,
            .gallery-item,
            .attachment {
                overflow: hidden;
            }
        }
        ';
    }

    /**
     * Rileva immagini non responsive
     */
    public function detectNonResponsiveImages(): array
    {
        $issues = [];
        
        // Controlla immagini senza srcset
        $images_without_srcset = $this->findImagesWithoutSrcset();
        if (!empty($images_without_srcset)) {
            $issues[] = [
                'type' => 'missing_srcset',
                'count' => count($images_without_srcset),
                'message' => 'Immagini senza srcset responsive'
            ];
        }
        
        // Controlla immagini troppo grandi per mobile
        $oversized_images = $this->findOversizedImages();
        if (!empty($oversized_images)) {
            $issues[] = [
                'type' => 'oversized_images',
                'count' => count($oversized_images),
                'message' => 'Immagini troppo grandi per mobile'
            ];
        }
        
        return $issues;
    }

    /**
     * Trova immagini senza srcset
     */
    private function findImagesWithoutSrcset(): array
    {
        // Implementazione semplificata
        return [];
    }

    /**
     * Trova immagini troppo grandi
     */
    private function findOversizedImages(): array
    {
        // Implementazione semplificata
        return [];
    }

    /**
     * Rileva se la richiesta è da dispositivo mobile
     */
    private function isMobile(): bool
    {
        return wp_is_mobile();
    }

    /**
     * Ottiene le impostazioni
     */
    private function settings(): array
    {
        return get_option(self::OPTION, [
            'enabled' => false,
            'enable_lazy_loading' => true,
            'optimize_srcset' => true,
            'add_mobile_dimensions' => true,
            'enable_content_lazy_loading' => true,
            'optimize_content_image_sizes' => true,
            'max_mobile_width' => 768,
            'max_mobile_image_width' => 768,
            'max_content_image_width' => '100%'
        ]);
    }

    /**
     * Controlla se il servizio è abilitato
     */
    private function isEnabled(): bool
    {
        $settings = $this->settings();
        return !empty($settings['enabled']);
    }
}