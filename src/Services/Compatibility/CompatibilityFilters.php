<?php

namespace FP\PerfSuite\Services\Compatibility;

/**
 * Compatibility Filters
 *
 * Applies theme/builder-specific filters and exclusions
 *
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 */
class CompatibilityFilters
{
    private ThemeDetector $detector;
    
    public function __construct(?ThemeDetector $detector = null)
    {
        $this->detector = $detector ?? new ThemeDetector();
    }
    
    public function register(): void
    {
        // Apply filters based on detected theme
        if ($this->detector->isSalient()) {
            $this->registerSalientFilters();
        } elseif ($this->detector->isAvada()) {
            $this->registerAvadaFilters();
        } elseif ($this->detector->isDivi()) {
            $this->registerDiviFilters();
        }
        
        // Apply builder-specific filters
        $builder = $this->detector->detectPageBuilder();
        if ($builder['slug'] === 'wpbakery') {
            $this->registerWPBakeryFilters();
        } elseif ($builder['slug'] === 'elementor') {
            $this->registerElementorFilters();
        }
    }
    
    /**
     * Register Salient-specific filters
     */
    private function registerSalientFilters(): void
    {
        // Exclude Salient scripts from delay
        add_filter('fp_ps_third_party_script_delay', function($should_delay, $src) {
            $salient_critical = [
                'salient-',
                'nectar-',
                'modernizr',
                'touchswipe',
                'jquery',
            ];
            
            foreach ($salient_critical as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    return false;
                }
            }
            
            return $should_delay;
        }, 10, 2);
        
        // Add Salient fonts to HTTP/2 push
        add_filter('fp_ps_http2_critical_fonts', function($fonts) {
            $theme_uri = get_template_directory_uri();
            
            $salient_fonts = [
                $theme_uri . '/css/fonts/icomoon.woff2',
                $theme_uri . '/css/fonts/fontello.woff2',
                $theme_uri . '/css/fonts/iconsmind.woff2',
            ];
            
            foreach ($salient_fonts as $font_url) {
                $fonts[] = [
                    'url' => $font_url,
                    'as' => 'font',
                    'type' => 'font/woff2',
                ];
            }
            
            return $fonts;
        });
        
        // Disable parallax on slow connections
        add_action('wp_footer', function() {
            ?>
            <script>
            (function() {
                if (!navigator.connection) return;
                var connection = navigator.connection;
                var slowConnections = ['slow-2g', '2g'];
                
                if (slowConnections.includes(connection.effectiveType) || connection.saveData) {
                    if (typeof jQuery !== 'undefined') {
                        jQuery(document).ready(function($) {
                            $('.nectar-parallax-scene').removeClass('nectar-parallax-scene');
                            $('[data-parallax]').removeAttr('data-parallax');
                            $('.nectar-video-wrap').remove();
                            console.log('[FP Performance] Salient heavy features disabled for slow connection');
                        });
                    }
                }
            })();
            </script>
            <?php
        }, 999);
        
        // Force image dimensions to reduce CLS
        add_filter('wp_get_attachment_image_attributes', function($attr, $attachment) {
            if (empty($attr['width']) || empty($attr['height'])) {
                $meta = wp_get_attachment_metadata($attachment->ID);
                if ($meta && isset($meta['width']) && isset($meta['height'])) {
                    $attr['width'] = $meta['width'];
                    $attr['height'] = $meta['height'];
                }
            }
            return $attr;
        }, 10, 2);
        
        // Purge edge cache when Salient options change
        add_action('updated_option', function($option_name) {
            if (strpos($option_name, 'salient') === false && 
                strpos($option_name, 'nectar') === false) {
                return;
            }
            
            try {
                $container = \FP\PerfSuite\Plugin::container();
                $edge = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
                
                if ($edge->status()['enabled']) {
                    $edge->purgeAll();
                }
            } catch (\Exception $e) {
                // Service not available
            }
        });
    }
    
    /**
     * Register Avada-specific filters
     */
    private function registerAvadaFilters(): void
    {
        // Exclude Fusion scripts from delay
        add_filter('fp_ps_third_party_script_delay', function($should_delay, $src) {
            $avada_critical = [
                'fusion-',
                'avada-',
                'jquery',
            ];
            
            foreach ($avada_critical as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    return false;
                }
            }
            
            return $should_delay;
        }, 10, 2);
        
        // Purge cache when Avada options change
        add_action('updated_option', function($option_name) {
            if (strpos($option_name, 'fusion') === false && 
                strpos($option_name, 'avada') === false) {
                return;
            }
            
            try {
                $container = \FP\PerfSuite\Plugin::container();
                $edge = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
                
                if ($edge->status()['enabled']) {
                    $edge->purgeAll();
                }
            } catch (\Exception $e) {
                // Service not available
            }
        });
    }
    
    /**
     * Register Divi-specific filters
     */
    private function registerDiviFilters(): void
    {
        // Exclude Divi scripts from delay
        add_filter('fp_ps_third_party_script_delay', function($should_delay, $src) {
            $divi_critical = [
                'et-',
                'divi-',
                'jquery',
            ];
            
            foreach ($divi_critical as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    return false;
                }
            }
            
            return $should_delay;
        }, 10, 2);
    }
    
    /**
     * Register WPBakery-specific filters
     */
    private function registerWPBakeryFilters(): void
    {
        // Disable optimizations in WPBakery editor
        add_filter('fp_ps_disable_optimizations', function($disable) {
            if (isset($_GET['vc_editable']) || 
                isset($_GET['vc_action']) || 
                isset($_GET['vc_post_id'])) {
                return true;
            }
            return $disable;
        });
        
        // Exclude WPBakery scripts from delay
        add_filter('fp_ps_third_party_script_delay', function($should_delay, $src) {
            $wpbakery_critical = [
                'wpbakery',
                'vc_',
                'js_composer',
            ];
            
            foreach ($wpbakery_critical as $pattern) {
                if (stripos($src, $pattern) !== false) {
                    return false;
                }
            }
            
            return $should_delay;
        }, 10, 2);
        
        // Purge cache after WPBakery save
        add_action('save_post', function($post_id, $post) {
            if ($post->post_status !== 'publish') {
                return;
            }
            
            $vc_active = get_post_meta($post_id, '_wpb_vc_js_status', true);
            if ($vc_active !== 'true') {
                return;
            }
            
            try {
                $container = \FP\PerfSuite\Plugin::container();
                $edge = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
                
                if ($edge->status()['enabled']) {
                    $urls = [
                        get_permalink($post_id),
                        home_url('/'),
                    ];
                    $edge->purgeUrls($urls);
                }
            } catch (\Exception $e) {
                // Service not available
            }
        }, 10, 2);
    }
    
    /**
     * Register Elementor-specific filters
     */
    private function registerElementorFilters(): void
    {
        // Disable optimizations in Elementor editor
        add_filter('fp_ps_disable_optimizations', function($disable) {
            if (isset($_GET['elementor-preview'])) {
                return true;
            }
            return $disable;
        });
        
        // Exclude Elementor scripts from delay
        add_filter('fp_ps_third_party_script_delay', function($should_delay, $src) {
            if (stripos($src, 'elementor') !== false) {
                return false;
            }
            return $should_delay;
        }, 10, 2);
    }
}
