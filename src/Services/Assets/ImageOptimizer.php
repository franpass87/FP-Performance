<?php

namespace FP\PerfSuite\Services\Assets;

class ImageOptimizer
{
    private $lazy_loading;
    private $webp_conversion;
    
    public function __construct($lazy_loading = true, $webp_conversion = true)
    {
        $this->lazy_loading = $lazy_loading;
        $this->webp_conversion = $webp_conversion;
    }
    
    public function init()
    {
        add_filter('wp_get_attachment_image_attributes', [$this, 'addLazyLoading'], 10, 3);
        add_filter('the_content', [$this, 'optimizeContentImages']);
        add_action('wp_head', [$this, 'addImageOptimizationScripts']);
    }
    
    public function addLazyLoading($attr, $attachment, $size)
    {
        if ($this->lazy_loading) {
            $attr['loading'] = 'lazy';
            $attr['decoding'] = 'async';
        }
        
        return $attr;
    }
    
    public function optimizeContentImages($content)
    {
        if ($this->lazy_loading) {
            $content = preg_replace('/<img([^>]+)src=/', '<img$1loading="lazy" decoding="async" src=', $content);
        }
        
        if ($this->webp_conversion) {
            $content = $this->convertToWebP($content);
        }
        
        return $content;
    }
    
    public function addImageOptimizationScripts()
    {
        if ($this->lazy_loading) {
            echo '<script>
                if ("IntersectionObserver" in window) {
                    const imageObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const img = entry.target;
                                img.src = img.dataset.src;
                                img.classList.remove("lazy");
                                observer.unobserve(img);
                            }
                        });
                    });
                    
                    document.querySelectorAll("img[data-src]").forEach(img => {
                        imageObserver.observe(img);
                    });
                }
            </script>';
        }
    }
    
    private function convertToWebP($content)
    {
        // Check if browser supports WebP
        $webp_support = $this->checkWebPSupport();
        
        if ($webp_support) {
            $content = preg_replace_callback('/<img([^>]+)src="([^"]+\.(jpg|jpeg|png))"([^>]*)>/i', function($matches) {
                $webp_url = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $matches[2]);
                
                if (file_exists(ABSPATH . $webp_url)) {
                    return '<picture>
                        <source srcset="' . $webp_url . '" type="image/webp">
                        <img' . $matches[1] . 'src="' . $matches[2] . '"' . $matches[4] . '>
                    </picture>';
                }
                
                return $matches[0];
            }, $content);
        }
        
        return $content;
    }
    
    private function checkWebPSupport()
    {
        return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }
    
    public function getImageMetrics()
    {
        return [
            'lazy_loading_enabled' => $this->lazy_loading,
            'webp_conversion_enabled' => $this->webp_conversion,
            'webp_support' => $this->checkWebPSupport()
        ];
    }
}