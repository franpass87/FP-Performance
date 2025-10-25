<?php

namespace FP\PerfSuite\Services\Assets;

class ImageOptimizer
{
    private $lazy_loading;
    
    public function __construct($lazy_loading = true)
    {
        $this->lazy_loading = $lazy_loading;
    }
    
    public function init()
    {
        add_filter('wp_get_attachment_image_attributes', [$this, 'addLazyLoading'], 10, 3);
        add_filter('the_content', [$this, 'optimizeContentImages']);
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_head', [$this, 'addImageOptimizationScripts'], 26);
        }
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
    
    
    public function getImageMetrics()
    {
        return [
            'lazy_loading_enabled' => $this->lazy_loading
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}