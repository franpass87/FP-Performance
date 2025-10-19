<?php

namespace FP\PerfSuite\Services\Compatibility;

/**
 * Compatibility Filters
 *
 * Gestisce compatibilità specifica per tema/builder
 * Si concentra su: disabilitazione ottimizzazioni negli editor, cache purge, script di ottimizzazione UX
 * 
 * NOTA: Le configurazioni asset (script, font, immagini) sono ora gestite da
 * \FP\PerfSuite\Services\Assets\ThemeAssetConfiguration
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
     * 
     * NOTA: Esclusioni script e font HTTP/2 sono ora gestiti da ThemeAssetConfiguration
     */
    private function registerSalientFilters(): void
    {
        // Disable parallax on slow connections (UX optimization)
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
     * 
     * NOTA: Esclusioni script sono ora gestite da ThemeAssetConfiguration
     */
    private function registerAvadaFilters(): void
    {
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
     * 
     * NOTA: Esclusioni script sono ora gestite da ThemeAssetConfiguration
     */
    private function registerDiviFilters(): void
    {
        // Nessun filtro specifico necessario al momento
        // Le esclusioni script sono gestite da ThemeAssetConfiguration
    }
    
    /**
     * Register WPBakery-specific filters
     * 
     * NOTA: Esclusioni script sono ora gestite da ThemeAssetConfiguration
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
     * 
     * NOTA: Esclusioni script sono ora gestite da ThemeAssetConfiguration
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
    }
}
