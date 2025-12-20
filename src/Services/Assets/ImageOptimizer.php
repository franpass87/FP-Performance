<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class ImageOptimizer
{
    private const OPTION_KEY = 'fp_ps_image_optimizer';
    
    private $lazy_loading;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct($lazy_loading = true, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->lazy_loading = $lazy_loading;
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
     * Restituisce lo stato dell'ottimizzazione immagini
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        return [
            'enabled' => $this->lazy_loading,
            'lazy_loading' => $this->lazy_loading,
        ];
    }
    
    /**
     * Restituisce le impostazioni dell'ottimizzazione immagini
     * 
     * @return array Array con tutte le impostazioni
     */
    public function getSettings(): array
    {
        $settings = $this->getOption(self::OPTION_KEY, [
            'enabled' => false,
            'lazy_loading' => true,
            'compression_quality' => 85,
            'max_width' => 2048,
            'max_height' => 2048,
            'strip_metadata' => true,
            'responsive_images' => true,
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