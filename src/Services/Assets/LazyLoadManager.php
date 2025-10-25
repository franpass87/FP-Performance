<?php

namespace FP\PerfSuite\Services\Assets;

class LazyLoadManager
{
    private $images;
    private $videos;
    private $iframes;
    
    public function __construct($images = true, $videos = true, $iframes = true)
    {
        $this->images = $images;
        $this->videos = $videos;
        $this->iframes = $iframes;
    }
    
    public function init()
    {
        if ($this->images) {
            add_filter('wp_get_attachment_image_attributes', [$this, 'addImageLazyLoading'], 10, 3);
            add_filter('the_content', [$this, 'optimizeContentImages']);
        }
        
        if ($this->videos) {
            add_filter('the_content', [$this, 'optimizeContentVideos']);
        }
        
        if ($this->iframes) {
            add_filter('the_content', [$this, 'optimizeContentIframes']);
        }
        
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_footer', [$this, 'addLazyLoadScript'], 49);
        }
    }
    
    public function addImageLazyLoading($attr, $attachment, $size)
    {
        $attr['loading'] = 'lazy';
        $attr['decoding'] = 'async';
        return $attr;
    }
    
    public function optimizeContentImages($content)
    {
        if (!$this->images) return $content;
        
        $content = preg_replace('/<img([^>]+)src=/', '<img$1loading="lazy" decoding="async" src=', $content);
        
        return $content;
    }
    
    public function optimizeContentVideos($content)
    {
        if (!$this->videos) return $content;
        
        $content = preg_replace('/<video([^>]+)src=/', '<video$1preload="none" src=', $content);
        
        return $content;
    }
    
    public function optimizeContentIframes($content)
    {
        if (!$this->iframes) return $content;
        
        $content = preg_replace_callback('/<iframe([^>]+)src="([^"]+)"([^>]*)>/i', function($matches) {
            $src = $matches[2];
            $attributes = $matches[1] . $matches[3];
            
            return '<iframe' . $attributes . 'data-src="' . $src . '" loading="lazy"></iframe>';
        }, $content);
        
        return $content;
    }
    
    public function addLazyLoadScript()
    {
        if (!$this->images && !$this->videos && !$this->iframes) return;
        
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                if ("IntersectionObserver" in window) {
                    const lazyElements = document.querySelectorAll("img[loading=\"lazy\"], iframe[data-src]");
                    
                    const lazyObserver = new IntersectionObserver((entries, observer) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const element = entry.target;
                                
                                if (element.tagName === "IMG") {
                                    element.src = element.dataset.src || element.src;
                                } else if (element.tagName === "IFRAME") {
                                    element.src = element.dataset.src;
                                }
                                
                                observer.unobserve(element);
                            }
                        });
                    });
                    
                    lazyElements.forEach(element => {
                        lazyObserver.observe(element);
                    });
                }
            });
        </script>';
    }
    
    public function getLazyLoadMetrics()
    {
        return [
            'images_enabled' => $this->images,
            'videos_enabled' => $this->videos,
            'iframes_enabled' => $this->iframes
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