<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;
use function wp_enqueue_script;
use function wp_localize_script;
use function is_admin;
use function wp_remote_get;
use function wp_remote_retrieve_body;

/**
 * Code Splitting Manager
 *
 * Implements advanced code splitting techniques for WordPress:
 * - Dynamic imports for large scripts
 * - Route-based splitting
 * - Component-based splitting
 * - Vendor chunk optimization
 * - Critical path optimization
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class CodeSplittingManager
{
    private const OPTION = 'fp_ps_code_splitting';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Don't optimize in admin
        if (is_admin()) {
            return;
        }

        // Add code splitting filters
        add_filter('script_loader_tag', [$this, 'splitScriptTag'], 10, 3);
        add_action('wp_footer', [$this, 'injectCodeSplittingScript'], 1);
        add_action('wp_head', [$this, 'injectPreloadHints'], 1);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,dynamic_imports:bool,route_splitting:bool,component_splitting:bool,vendor_chunks:bool,critical_path:bool,thresholds:array}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'dynamic_imports' => true,
            'route_splitting' => true,
            'component_splitting' => true,
            'vendor_chunks' => true,
            'critical_path' => true,
            'thresholds' => [
                'large_script' => 50000, // 50KB
                'vendor_script' => 100000, // 100KB
                'critical_script' => 20000, // 20KB
            ],
            'routes' => [
                'home' => ['is_front_page'],
                'single' => ['is_single'],
                'page' => ['is_page'],
                'shop' => ['is_shop', 'is_product_category', 'is_product_tag'],
                'checkout' => ['is_checkout', 'is_cart'],
                'account' => ['is_account_page'],
            ],
            'components' => [
                'slider' => ['has_shortcode:slider', 'has_shortcode:rev_slider'],
                'map' => ['has_shortcode:map', 'has_shortcode:googlemap'],
                'form' => ['has_shortcode:contact-form-7', 'has_shortcode:gravityform'],
                'gallery' => ['has_shortcode:gallery', 'has_shortcode:nggallery'],
                'video' => ['has_shortcode:video', 'has_shortcode:youtube'],
            ],
            'vendor_patterns' => [
                'jquery', 'lodash', 'moment', 'bootstrap', 'foundation',
                'react', 'vue', 'angular', 'backbone', 'underscore',
            ],
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $new = [
            'enabled' => !empty($settings['enabled']),
            'dynamic_imports' => !empty($settings['dynamic_imports']),
            'route_splitting' => !empty($settings['route_splitting']),
            'component_splitting' => !empty($settings['component_splitting']),
            'vendor_chunks' => !empty($settings['vendor_chunks']),
            'critical_path' => !empty($settings['critical_path']),
            'thresholds' => $settings['thresholds'] ?? $current['thresholds'],
            'routes' => $settings['routes'] ?? $current['routes'],
            'components' => $settings['components'] ?? $current['components'],
            'vendor_patterns' => $settings['vendor_patterns'] ?? $current['vendor_patterns'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Split script tag for code splitting
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    public function splitScriptTag(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();

        // Apply route-based splitting
        if ($settings['route_splitting']) {
            $tag = $this->applyRouteSplitting($tag, $handle, $src);
        }

        // Apply component-based splitting
        if ($settings['component_splitting']) {
            $tag = $this->applyComponentSplitting($tag, $handle, $src);
        }

        // Apply vendor chunk optimization
        if ($settings['vendor_chunks']) {
            $tag = $this->applyVendorChunks($tag, $handle, $src);
        }

        // Apply dynamic imports
        if ($settings['dynamic_imports']) {
            $tag = $this->applyDynamicImports($tag, $handle, $src);
        }

        return $tag;
    }

    /**
     * Apply route-based splitting
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function applyRouteSplitting(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();
        $currentRoute = $this->getCurrentRoute();

        // Check if script is route-specific
        foreach ($settings['routes'] as $route => $conditions) {
            if ($this->matchesRoute($handle, $src, $route)) {
                if ($currentRoute !== $route) {
                    // Defer non-matching route scripts
                    $tag = $this->deferScript($tag, $handle, $src, 'route');
                }
            }
        }

        return $tag;
    }

    /**
     * Apply component-based splitting
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function applyComponentSplitting(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();

        // Check if script is component-specific
        foreach ($settings['components'] as $component => $conditions) {
            if ($this->matchesComponent($handle, $src, $component)) {
                if (!$this->isComponentNeeded($component)) {
                    // Defer unused component scripts
                    $tag = $this->deferScript($tag, $handle, $src, 'component');
                }
            }
        }

        return $tag;
    }

    /**
     * Apply vendor chunk optimization
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function applyVendorChunks(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();

        // Check if script is vendor library
        foreach ($settings['vendor_patterns'] as $pattern) {
            if (strpos($handle, $pattern) !== false || strpos($src, $pattern) !== false) {
                // Group vendor scripts
                $tag = $this->groupVendorScript($tag, $handle, $src);
                break;
            }
        }

        return $tag;
    }

    /**
     * Apply dynamic imports
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function applyDynamicImports(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();
        $threshold = $settings['thresholds']['large_script'];

        // Check if script is large enough for dynamic import
        if ($this->isScriptLarge($src, $threshold)) {
            $tag = $this->makeDynamicImport($tag, $handle, $src);
        }

        return $tag;
    }

    /**
     * Get current route
     *
     * @return string Current route
     */
    private function getCurrentRoute(): string
    {
        if (is_front_page()) return 'home';
        if (is_single()) return 'single';
        if (is_page()) return 'page';
        if (is_shop() || is_product_category() || is_product_tag()) return 'shop';
        if (is_checkout() || is_cart()) return 'checkout';
        if (is_account_page()) return 'account';
        
        return 'other';
    }

    /**
     * Check if script matches route
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @param string $route Route name
     * @return bool
     */
    private function matchesRoute(string $handle, string $src, string $route): bool
    {
        $routePatterns = [
            'home' => ['home', 'front', 'landing'],
            'single' => ['single', 'post', 'article'],
            'page' => ['page', 'static'],
            'shop' => ['shop', 'product', 'woocommerce', 'cart', 'checkout'],
            'checkout' => ['checkout', 'payment', 'order'],
            'account' => ['account', 'profile', 'user'],
        ];

        if (!isset($routePatterns[$route])) {
            return false;
        }

        foreach ($routePatterns[$route] as $pattern) {
            if (strpos($handle, $pattern) !== false || strpos($src, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if script matches component
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @param string $component Component name
     * @return bool
     */
    private function matchesComponent(string $handle, string $src, string $component): bool
    {
        $componentPatterns = [
            'slider' => ['slider', 'carousel', 'swiper', 'revolution'],
            'map' => ['map', 'googlemap', 'leaflet', 'openstreet'],
            'form' => ['form', 'contact', 'gravity', 'ninja'],
            'gallery' => ['gallery', 'lightbox', 'fancybox', 'photoswipe'],
            'video' => ['video', 'youtube', 'vimeo', 'player'],
        ];

        if (!isset($componentPatterns[$component])) {
            return false;
        }

        foreach ($componentPatterns[$component] as $pattern) {
            if (strpos($handle, $pattern) !== false || strpos($src, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if component is needed
     *
     * @param string $component Component name
     * @return bool
     */
    private function isComponentNeeded(string $component): bool
    {
        $settings = $this->settings();

        if (!isset($settings['components'][$component])) {
            return false;
        }

        $conditions = $settings['components'][$component];

        foreach ($conditions as $condition) {
            if (strpos($condition, 'has_shortcode:') === 0) {
                $shortcode = str_replace('has_shortcode:', '', $condition);
                if (has_shortcode(get_post()->post_content ?? '', $shortcode)) {
                    return true;
                }
            } elseif (strpos($condition, 'is_') === 0) {
                $function = $condition;
                if (function_exists($function) && $function()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if script is large
     *
     * @param string $src Script source
     * @param int $threshold Threshold in bytes
     * @return bool
     */
    private function isScriptLarge(string $src, int $threshold): bool
    {
        $response = wp_remote_head($src);
        if (is_wp_error($response)) {
            return false;
        }

        $contentLength = wp_remote_retrieve_header($response, 'content-length');
        return $contentLength && (int)$contentLength > $threshold;
    }

    /**
     * Defer script
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @param string $reason Defer reason
     * @return string Modified tag
     */
    private function deferScript(string $tag, string $handle, string $src, string $reason): string
    {
        $tag = str_replace('<script ', '<script data-fp-deferred="true" data-fp-defer-reason="' . $reason . '" ', $tag);
        $tag = str_replace(' src=', ' data-fp-deferred-src=', $tag);
        $tag = str_replace('<script', '<script type="text/plain"', $tag);

        Logger::debug('Script deferred', [
            'handle' => $handle,
            'src' => basename($src),
            'reason' => $reason,
        ]);

        return $tag;
    }

    /**
     * Group vendor script
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function groupVendorScript(string $tag, string $handle, string $src): string
    {
        $tag = str_replace('<script ', '<script data-fp-vendor="true" ', $tag);

        Logger::debug('Script grouped as vendor', [
            'handle' => $handle,
            'src' => basename($src),
        ]);

        return $tag;
    }

    /**
     * Make script dynamic import
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function makeDynamicImport(string $tag, string $handle, string $src): string
    {
        $tag = str_replace('<script ', '<script data-fp-dynamic-import="true" ', $tag);
        $tag = str_replace(' src=', ' data-fp-dynamic-src=', $tag);
        $tag = str_replace('<script', '<script type="text/plain"', $tag);

        Logger::debug('Script made dynamic import', [
            'handle' => $handle,
            'src' => basename($src),
        ]);

        return $tag;
    }

    /**
     * Inject code splitting script
     */
    public function injectCodeSplittingScript(): void
    {
        $settings = $this->settings();

        ?>
        <script>
        (function() {
            'use strict';
            
            var settings = <?php echo json_encode($settings); ?>;
            var deferredScripts = document.querySelectorAll('script[data-fp-deferred="true"]');
            var dynamicImports = document.querySelectorAll('script[data-fp-dynamic-import="true"]');
            var vendorScripts = document.querySelectorAll('script[data-fp-vendor="true"]');
            
            // Load deferred scripts
            if (deferredScripts.length > 0) {
                loadDeferredScripts();
            }
            
            // Load dynamic imports
            if (dynamicImports.length > 0) {
                loadDynamicImports();
            }
            
            // Optimize vendor chunks
            if (vendorScripts.length > 0) {
                optimizeVendorChunks();
            }
            
            function loadDeferredScripts() {
                deferredScripts.forEach(function(script) {
                    var src = script.getAttribute('data-fp-deferred-src');
                    var reason = script.getAttribute('data-fp-defer-reason');
                    
                    if (!src) return;
                    
                    // Load based on reason
                    if (reason === 'route') {
                        loadOnRouteMatch(script, src);
                    } else if (reason === 'component') {
                        loadOnComponentNeed(script, src);
                    } else {
                        loadScript(src, script);
                    }
                });
            }
            
            function loadDynamicImports() {
                dynamicImports.forEach(function(script) {
                    var src = script.getAttribute('data-fp-dynamic-src');
                    if (!src) return;
                    
                    // Load when needed
                    loadOnIntersection(script, src);
                });
            }
            
            function optimizeVendorChunks() {
                // Group vendor scripts for better caching
                var vendorGroups = {};
                
                vendorScripts.forEach(function(script) {
                    var src = script.src || script.getAttribute('data-fp-deferred-src');
                    if (!src) return;
                    
                    var vendor = getVendorName(src);
                    if (!vendorGroups[vendor]) {
                        vendorGroups[vendor] = [];
                    }
                    vendorGroups[vendor].push(script);
                });
                
                // Load vendor groups together
                Object.keys(vendorGroups).forEach(function(vendor) {
                    loadVendorGroup(vendorGroups[vendor]);
                });
            }
            
            function loadOnRouteMatch(script, src) {
                var currentRoute = getCurrentRoute();
                var scriptRoute = getScriptRoute(script);
                
                if (currentRoute === scriptRoute) {
                    loadScript(src, script);
                }
            }
            
            function loadOnComponentNeed(script, src) {
                var component = getScriptComponent(script);
                
                if (isComponentNeeded(component)) {
                    loadScript(src, script);
                }
            }
            
            function loadOnIntersection(script, src) {
                if ('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                loadScript(src, script);
                                observer.unobserve(entry.target);
                            }
                        });
                    });
                    
                    observer.observe(document.body);
                } else {
                    // Fallback
                    setTimeout(function() {
                        loadScript(src, script);
                    }, 1000);
                }
            }
            
            function loadVendorGroup(scripts) {
                // Load vendor scripts in sequence for better performance
                var index = 0;
                
                function loadNext() {
                    if (index >= scripts.length) return;
                    
                    var script = scripts[index];
                    var src = script.src || script.getAttribute('data-fp-deferred-src');
                    
                    if (src) {
                        loadScript(src, script, function() {
                            index++;
                            loadNext();
                        });
                    } else {
                        index++;
                        loadNext();
                    }
                }
                
                loadNext();
            }
            
            function getCurrentRoute() {
                if (document.body.classList.contains('home')) return 'home';
                if (document.body.classList.contains('single')) return 'single';
                if (document.body.classList.contains('page')) return 'page';
                if (document.body.classList.contains('shop')) return 'shop';
                if (document.body.classList.contains('checkout')) return 'checkout';
                if (document.body.classList.contains('account')) return 'account';
                return 'other';
            }
            
            function getScriptRoute(script) {
                var handle = script.getAttribute('data-fp-handle') || '';
                var src = script.src || script.getAttribute('data-fp-deferred-src') || '';
                
                if (handle.includes('home') || src.includes('home')) return 'home';
                if (handle.includes('single') || src.includes('single')) return 'single';
                if (handle.includes('page') || src.includes('page')) return 'page';
                if (handle.includes('shop') || src.includes('shop')) return 'shop';
                if (handle.includes('checkout') || src.includes('checkout')) return 'checkout';
                if (handle.includes('account') || src.includes('account')) return 'account';
                
                return 'other';
            }
            
            function getScriptComponent(script) {
                var handle = script.getAttribute('data-fp-handle') || '';
                var src = script.src || script.getAttribute('data-fp-deferred-src') || '';
                
                if (handle.includes('slider') || src.includes('slider')) return 'slider';
                if (handle.includes('map') || src.includes('map')) return 'map';
                if (handle.includes('form') || src.includes('form')) return 'form';
                if (handle.includes('gallery') || src.includes('gallery')) return 'gallery';
                if (handle.includes('video') || src.includes('video')) return 'video';
                
                return 'other';
            }
            
            function isComponentNeeded(component) {
                // Check if component is actually used on the page
                var selectors = {
                    'slider': '[data-slider], .slider, .carousel',
                    'map': '[data-map], .map, .googlemap',
                    'form': '[data-form], .form, .contact-form',
                    'gallery': '[data-gallery], .gallery, .lightbox',
                    'video': '[data-video], .video, .youtube, .vimeo'
                };
                
                if (selectors[component]) {
                    return document.querySelector(selectors[component]) !== null;
                }
                
                return false;
            }
            
            function getVendorName(src) {
                if (src.includes('jquery')) return 'jquery';
                if (src.includes('lodash')) return 'lodash';
                if (src.includes('moment')) return 'moment';
                if (src.includes('bootstrap')) return 'bootstrap';
                if (src.includes('react')) return 'react';
                if (src.includes('vue')) return 'vue';
                if (src.includes('angular')) return 'angular';
                
                return 'other';
            }
            
            function loadScript(src, originalScript, callback) {
                var newScript = document.createElement('script');
                newScript.src = src;
                newScript.type = 'text/javascript';
                
                if (callback) {
                    newScript.onload = callback;
                }
                
                // Copy attributes
                Array.from(originalScript.attributes).forEach(function(attr) {
                    if (!attr.name.startsWith('data-fp-') && attr.name !== 'type') {
                        newScript.setAttribute(attr.name, attr.value);
                    }
                });
                
                // Replace original script
                originalScript.parentNode.replaceChild(newScript, originalScript);
                
                console.log('[FP Performance] Loaded script:', src);
            }
            
        })();
        </script>
        <?php
    }

    /**
     * Inject preload hints
     */
    public function injectPreloadHints(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        ?>
        <link rel="preload" href="data:text/javascript;base64," as="script">
        <meta name="fp-ps-code-splitting" content="enabled">
        <?php
    }

    /**
     * Get optimization status
     *
     * @return array{enabled:bool,deferred_scripts:int,dynamic_imports:int,vendor_chunks:int}
     */
    public function status(): array
    {
        $settings = $this->settings();

        return [
            'enabled' => $settings['enabled'],
            'deferred_scripts' => count($settings['routes']),
            'dynamic_imports' => $settings['dynamic_imports'] ? 1 : 0,
            'vendor_chunks' => count($settings['vendor_patterns']),
        ];
    }
}
