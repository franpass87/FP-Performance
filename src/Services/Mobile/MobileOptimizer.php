<?php

namespace FP\PerfSuite\Services\Mobile;

class MobileOptimizer
{
    private $touch_optimization;
    private $mobile_cache;
    private $responsive_images;
    
    public function __construct($touch_optimization = true, $mobile_cache = true, $responsive_images = true)
    {
        $this->touch_optimization = $touch_optimization;
        $this->mobile_cache = $mobile_cache;
        $this->responsive_images = $responsive_images;
    }
    
    public function init()
    {
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_head', [$this, 'addMobileOptimizations'], 17);
            add_action('wp_footer', [$this, 'addMobileScripts'], 45);
        }
        add_filter('wp_get_attachment_image_attributes', [$this, 'optimizeMobileImages'], 10, 3);
    }
    
    public function addMobileOptimizations()
    {
        // Add viewport meta tag
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        
        // Add mobile-specific CSS
        echo '<style>
            @media (max-width: 768px) {
                .mobile-optimized {
                    font-size: 16px;
                    line-height: 1.5;
                }
                
                .mobile-optimized img {
                    max-width: 100%;
                    height: auto;
                }
                
                .mobile-optimized .lazy-load {
                    opacity: 0;
                    transition: opacity 0.3s;
                }
                
                .mobile-optimized .lazy-load.loaded {
                    opacity: 1;
                }
            }
        </style>';
    }
    
    public function optimizeMobileImages($attr, $attachment, $size)
    {
        if ($this->responsive_images) {
            $attr['sizes'] = '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw';
            $attr['loading'] = 'lazy';
        }
        
        return $attr;
    }
    
    public function addMobileScripts()
    {
        if (!$this->touch_optimization) return;
        
        echo '<script>
            // Mobile Touch Optimization
            if ("ontouchstart" in window) {
                document.addEventListener("touchstart", function(e) {
                    // Add touch feedback
                    e.target.classList.add("touch-active");
                });
                
                document.addEventListener("touchend", function(e) {
                    // Remove touch feedback
                    setTimeout(() => {
                        e.target.classList.remove("touch-active");
                    }, 150);
                });
                
                // Optimize touch scrolling
                document.addEventListener("touchmove", function(e) {
                    if (e.target.closest(".scrollable")) {
                        e.preventDefault();
                    }
                }, { passive: false });
            }
            
            // Mobile-specific optimizations
            if (window.innerWidth <= 768) {
                // Lazy load images on mobile
                const images = document.querySelectorAll("img[data-src]");
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.add("loaded");
                            observer.unobserve(img);
                        }
                    });
                });
                
                images.forEach(img => imageObserver.observe(img));
            }
        </script>';
    }
    
    public function getMobileMetrics()
    {
        return [
            'touch_optimization' => $this->touch_optimization,
            'mobile_cache' => $this->mobile_cache,
            'responsive_images' => $this->responsive_images,
            'is_mobile' => wp_is_mobile()
        ];
    }
    
    /**
     * Genera un report completo delle ottimizzazioni mobile
     * 
     * @return array Report con stato, impostazioni, issues e raccomandazioni
     */
    public function generateMobileReport(): array
    {
        $settings = get_option('fp_ps_mobile_optimizer', [
            'enabled' => false,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => false,
            'optimize_touch_targets' => false,
            'enable_responsive_images' => false
        ]);
        
        $issues = [];
        $recommendations = [];
        $critical_issues = 0;
        
        // Verifica se le ottimizzazioni sono abilitate
        if (!$settings['enabled']) {
            $issues[] = [
                'type' => 'warning',
                'message' => __('Mobile optimization is disabled', 'fp-performance-suite'),
            ];
            $recommendations[] = __('Enable mobile optimization to improve mobile performance', 'fp-performance-suite');
        }
        
        // Verifica responsive images
        if (!$settings['enable_responsive_images']) {
            $issues[] = [
                'type' => 'info',
                'message' => __('Responsive images optimization is disabled', 'fp-performance-suite'),
            ];
        }
        
        // Verifica touch targets
        if (!$settings['optimize_touch_targets']) {
            $issues[] = [
                'type' => 'info',
                'message' => __('Touch targets optimization is disabled', 'fp-performance-suite'),
            ];
        }
        
        return [
            'enabled' => $settings['enabled'],
            'settings' => $settings,
            'issues' => $issues,
            'issues_count' => count($issues),
            'critical_issues' => $critical_issues,
            'recommendations' => $recommendations,
            'is_mobile_device' => wp_is_mobile(),
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