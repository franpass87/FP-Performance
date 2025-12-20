<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class LazyLoadManager
{
    private const OPTION_KEY = 'fp_ps_lazy_load';
    
    private $images;
    private $videos;
    private $iframes;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct($images = true, $videos = true, $iframes = true, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->images = $images;
        $this->videos = $videos;
        $this->iframes = $iframes;
        $this->optionsRepo = $optionsRepo;
    }
    
    /**
     * Helper method per ottenere opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = [])
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option($key, $default);
    }
    
    public function init()
    {
        if ($this->images) {
            add_filter('wp_get_attachment_image_attributes', [$this, 'addImageLazyLoading'], 10, 3);
            add_filter('the_content', [$this, 'optimizeContentImages'], 999); // PrioritÃ  alta per eseguire dopo
            
            // BUGFIX #12d: Output buffering globale per catturare TUTTE le immagini (anche quelle del tema)
            // Non solo quelle in the_content
            if (!is_admin()) {
                add_action('template_redirect', [$this, 'startOutputBuffer'], 1);
            }
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
    
    /**
     * BUGFIX #12d: Avvia output buffering per catturare tutto l'HTML finale
     */
    public function startOutputBuffer()
    {
        ob_start([$this, 'processFullPageHtml']);
    }
    
    /**
     * BUGFIX #12d: Processa l'HTML completo della pagina e aggiungi lazy loading
     */
    public function processFullPageHtml($html)
    {
        if (empty($html)) {
            return $html;
        }
        
        // Aggiungi loading="lazy" a TUTTE le immagini che non lo hanno giÃ 
        $html = preg_replace_callback('/<img([^>]*)>/i', function($matches) {
            $img_tag = $matches[0];
            
            // Se giÃ  ha loading= , non modificare
            if (stripos($img_tag, 'loading=') !== false) {
                return $img_tag;
            }
            
            // Aggiungi loading="lazy" decoding="async"
            $img_tag = str_replace('<img', '<img loading="lazy" decoding="async"', $img_tag);
            
            return $img_tag;
        }, $html);
        
        return $html;
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
        
        // BUGFIX #12c: Regex migliorato per aggiungere loading="lazy" solo se non giÃ  presente
        // Evita di aggiungere l'attributo duplicato
        $content = preg_replace_callback('/<img([^>]*)>/i', function($matches) {
            $img_tag = $matches[0];
            
            // Se giÃ  ha loading="lazy", non modificare
            if (stripos($img_tag, 'loading=') !== false) {
                return $img_tag;
            }
            
            // Aggiungi loading="lazy" e decoding="async" prima del >
            $img_tag = str_replace('<img', '<img loading="lazy" decoding="async"', $img_tag);
            
            return $img_tag;
        }, $content);
        
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
            // BUGFIX #12e: Lazy loading client-side per immagini dinamiche (emoji, etc)
            (function() {
                function applyLazyToAllImages() {
                    const allImages = document.querySelectorAll("img:not([loading])");
                    let count = 0;
                    
                    allImages.forEach(function(img) {
                        img.setAttribute("loading", "lazy");
                        img.setAttribute("decoding", "async");
                        count++;
                    });
                    
                    if (count > 0) {
                        console.log("FP Performance: Lazy loading applicato a " + count + " immagini");
                    }
                }
                
                // Applica subito al DOMContentLoaded
                document.addEventListener("DOMContentLoaded", applyLazyToAllImages);
                
                // Ri-applica dopo 500ms per catturare emoji e altre risorse dinamiche
                document.addEventListener("DOMContentLoaded", function() {
                    setTimeout(applyLazyToAllImages, 500);
                    setTimeout(applyLazyToAllImages, 2000);
                });
                
                // Intersection Observer per gestione avanzata
                if ("IntersectionObserver" in window) {
                    document.addEventListener("DOMContentLoaded", function() {
                        const lazyElements = document.querySelectorAll("img[loading=\'lazy\'], iframe[data-src]");
                        
                        const lazyObserver = new IntersectionObserver(function(entries, observer) {
                            entries.forEach(function(entry) {
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
                        
                        lazyElements.forEach(function(element) {
                            lazyObserver.observe(element);
                        });
                    });
                }
            })();
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
     * Restituisce lo stato del lazy loading
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        return [
            'enabled' => $this->images || $this->videos || $this->iframes,
            'images' => $this->images,
            'videos' => $this->videos,
            'iframes' => $this->iframes,
        ];
    }
    
    /**
     * Restituisce le impostazioni del lazy loading
     * 
     * @return array Array con tutte le impostazioni
     */
    public function getSettings(): array
    {
        $settings = $this->getOption(self::OPTION_KEY, [
            'enabled' => false,
            'lazy_load_images' => true,
            'lazy_load_videos' => true,
            'lazy_load_iframes' => true,
            'threshold' => 300,
            'placeholder_type' => 'blur',
            'exclude_first_images' => 2,
        ]);
        
        return $settings;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}