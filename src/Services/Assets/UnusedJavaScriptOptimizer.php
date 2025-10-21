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

/**
 * Unused JavaScript Optimizer
 *
 * Implements advanced techniques to reduce unused JavaScript:
 * - Code splitting for WordPress
 * - Dynamic imports
 * - Conditional loading
 * - Tree shaking simulation
 * - Lazy loading for non-critical scripts
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class UnusedJavaScriptOptimizer
{
    private const OPTION = 'fp_ps_unused_js_optimization';

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

        // Add optimization filters
        add_filter('script_loader_tag', [$this, 'optimizeScriptTag'], 10, 3);
        add_filter('wp_enqueue_scripts', [$this, 'implementCodeSplitting'], 1);
        add_action('wp_footer', [$this, 'injectOptimizationScript'], 1);
        add_action('wp_head', [$this, 'injectResourceHints'], 1);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,code_splitting:bool,dynamic_imports:bool,conditional_loading:bool,tree_shaking:bool,lazy_loading:bool,exclude_critical:array}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'code_splitting' => true,
            'dynamic_imports' => true,
            'conditional_loading' => true,
            'tree_shaking' => true,
            'lazy_loading' => true,
            'exclude_critical' => [
                'jquery', 'jquery-core', 'jquery-migrate',
                'wc-checkout', 'wc-cart', 'wc-cart-fragments',
                'stripe', 'stripe-js', 'paypal-sdk',
                'contact-form-7', 'wpcf7-recaptcha',
                'elementor-frontend', 'elementor-pro-frontend',
            ],
            'conditional_patterns' => [
                'analytics' => ['is_front_page', 'is_single', 'is_shop'],
                'social' => ['is_single', 'is_page'],
                'maps' => ['has_shortcode:map', 'has_shortcode:googlemap'],
                'chat' => ['is_front_page', 'is_shop'],
                'popup' => ['is_front_page', 'is_shop'],
            ],
            'lazy_load_patterns' => [
                'instagram', 'facebook', 'twitter', 'youtube', 'vimeo',
                'maps', 'chat', 'popup', 'analytics', 'tracking',
            ],
            'dynamic_import_threshold' => 50000, // 50KB
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
            'code_splitting' => !empty($settings['code_splitting']),
            'dynamic_imports' => !empty($settings['dynamic_imports']),
            'conditional_loading' => !empty($settings['conditional_loading']),
            'tree_shaking' => !empty($settings['tree_shaking']),
            'lazy_loading' => !empty($settings['lazy_loading']),
            'exclude_critical' => $settings['exclude_critical'] ?? $current['exclude_critical'],
            'conditional_patterns' => $settings['conditional_patterns'] ?? $current['conditional_patterns'],
            'lazy_load_patterns' => $settings['lazy_load_patterns'] ?? $current['lazy_load_patterns'],
            'dynamic_import_threshold' => (int)($settings['dynamic_import_threshold'] ?? $current['dynamic_import_threshold']),
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Implement code splitting for WordPress
     */
    public function implementCodeSplitting(): void
    {
        $settings = $this->settings();

        if (!$settings['code_splitting']) {
            return;
        }

        // Enqueue code splitting script
        wp_enqueue_script(
            'fp-ps-code-splitting',
            plugin_dir_url(__FILE__) . '../../assets/js/code-splitting.js',
            [],
            '1.0.0',
            true
        );

        // Localize script with settings
        wp_localize_script('fp-ps-code-splitting', 'fpPsCodeSplitting', [
            'settings' => $settings,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fp_ps_code_splitting'),
        ]);
    }

    /**
     * Optimize script tag for unused JS reduction
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    public function optimizeScriptTag(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();

        // Skip critical scripts
        if (in_array($handle, $settings['exclude_critical'], true)) {
            return $tag;
        }

        // Apply conditional loading
        if ($settings['conditional_loading'] && $this->shouldLoadConditionally($handle, $src)) {
            $tag = $this->makeConditional($tag, $handle, $src);
        }

        // Apply lazy loading
        if ($settings['lazy_loading'] && $this->shouldLazyLoad($handle, $src)) {
            $tag = $this->makeLazy($tag, $handle, $src);
        }

        // Apply dynamic imports for large scripts
        if ($settings['dynamic_imports'] && $this->shouldDynamicImport($handle, $src)) {
            $tag = $this->makeDynamicImport($tag, $handle, $src);
        }

        // Add tree shaking attributes
        if ($settings['tree_shaking']) {
            $tag = $this->addTreeShakingAttributes($tag, $handle, $src);
        }

        return $tag;
    }

    /**
     * Check if script should load conditionally
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @return bool
     */
    private function shouldLoadConditionally(string $handle, string $src): bool
    {
        $settings = $this->settings();

        foreach ($settings['conditional_patterns'] as $pattern => $conditions) {
            if (strpos($handle, $pattern) !== false || strpos($src, $pattern) !== false) {
                return $this->checkConditions($conditions);
            }
        }

        return false;
    }

    /**
     * Check if conditions are met
     *
     * @param array $conditions Conditions to check
     * @return bool
     */
    private function checkConditions(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (strpos($condition, 'is_') === 0) {
                $function = $condition;
                if (function_exists($function) && $function()) {
                    return true;
                }
            } elseif (strpos($condition, 'has_shortcode:') === 0) {
                $shortcode = str_replace('has_shortcode:', '', $condition);
                if (has_shortcode(get_post()->post_content ?? '', $shortcode)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if script should lazy load
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @return bool
     */
    private function shouldLazyLoad(string $handle, string $src): bool
    {
        $settings = $this->settings();

        foreach ($settings['lazy_load_patterns'] as $pattern) {
            if (strpos($handle, $pattern) !== false || strpos($src, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if script should use dynamic imports
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @return bool
     */
    private function shouldDynamicImport(string $handle, string $src): bool
    {
        $settings = $this->settings();

        // Check file size (simplified check)
        $response = wp_remote_head($src);
        if (is_wp_error($response)) {
            return false;
        }

        $contentLength = wp_remote_retrieve_header($response, 'content-length');
        if ($contentLength && (int)$contentLength > $settings['dynamic_import_threshold']) {
            return true;
        }

        return false;
    }

    /**
     * Make script conditional
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function makeConditional(string $tag, string $handle, string $src): string
    {
        // Add conditional loading attributes
        $tag = str_replace('<script ', '<script data-fp-conditional="true" ', $tag);
        $tag = str_replace(' src=', ' data-fp-conditional-src=', $tag);
        $tag = str_replace('<script', '<script type="text/plain"', $tag);

        Logger::debug('Script made conditional', [
            'handle' => $handle,
            'src' => basename($src),
        ]);

        return $tag;
    }

    /**
     * Make script lazy
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function makeLazy(string $tag, string $handle, string $src): string
    {
        // Add lazy loading attributes
        $tag = str_replace('<script ', '<script data-fp-lazy="true" ', $tag);
        $tag = str_replace(' src=', ' data-fp-lazy-src=', $tag);
        $tag = str_replace('<script', '<script type="text/plain"', $tag);

        Logger::debug('Script made lazy', [
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
        // Add dynamic import attributes
        $tag = str_replace('<script ', '<script data-fp-dynamic="true" ', $tag);
        $tag = str_replace(' src=', ' data-fp-dynamic-src=', $tag);
        $tag = str_replace('<script', '<script type="text/plain"', $tag);

        Logger::debug('Script made dynamic import', [
            'handle' => $handle,
            'src' => basename($src),
        ]);

        return $tag;
    }

    /**
     * Add tree shaking attributes
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    private function addTreeShakingAttributes(string $tag, string $handle, string $src): string
    {
        // Add tree shaking attributes
        if (strpos($tag, 'data-fp-tree-shaking') === false) {
            $tag = str_replace('<script ', '<script data-fp-tree-shaking="true" ', $tag);
        }

        return $tag;
    }

    /**
     * Inject optimization script
     */
    public function injectOptimizationScript(): void
    {
        $settings = $this->settings();

        ?>
        <script>
        (function() {
            'use strict';
            
            var settings = <?php echo json_encode($settings); ?>;
            var conditionalScripts = document.querySelectorAll('script[data-fp-conditional="true"]');
            var lazyScripts = document.querySelectorAll('script[data-fp-lazy="true"]');
            var dynamicScripts = document.querySelectorAll('script[data-fp-dynamic="true"]');
            
            // Load conditional scripts
            if (conditionalScripts.length > 0) {
                loadConditionalScripts();
            }
            
            // Load lazy scripts
            if (lazyScripts.length > 0) {
                loadLazyScripts();
            }
            
            // Load dynamic scripts
            if (dynamicScripts.length > 0) {
                loadDynamicScripts();
            }
            
            function loadConditionalScripts() {
                conditionalScripts.forEach(function(script) {
                    var src = script.getAttribute('data-fp-conditional-src');
                    if (!src) return;
                    
                    // Check if conditions are met
                    if (checkConditionalRequirements(script)) {
                        loadScript(src, script);
                    }
                });
            }
            
            function loadLazyScripts() {
                // Load on user interaction
                var events = ['mousemove', 'scroll', 'keydown', 'click', 'touchstart'];
                var loaded = false;
                
                function loadLazy() {
                    if (loaded) return;
                    loaded = true;
                    
                    lazyScripts.forEach(function(script) {
                        var src = script.getAttribute('data-fp-lazy-src');
                        if (src) {
                            loadScript(src, script);
                        }
                    });
                    
                    // Remove event listeners
                    events.forEach(function(event) {
                        window.removeEventListener(event, loadLazy);
                    });
                }
                
                events.forEach(function(event) {
                    window.addEventListener(event, loadLazy, { passive: true, once: true });
                });
                
                // Fallback timeout
                setTimeout(loadLazy, 5000);
            }
            
            function loadDynamicScripts() {
                // Load when needed
                dynamicScripts.forEach(function(script) {
                    var src = script.getAttribute('data-fp-dynamic-src');
                    if (!src) return;
                    
                    // Load on intersection observer
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
                });
            }
            
            function checkConditionalRequirements(script) {
                // Implement conditional logic based on script requirements
                var requirements = script.getAttribute('data-fp-requirements');
                if (!requirements) return true;
                
                try {
                    var reqs = JSON.parse(requirements);
                    
                    // Check page type
                    if (reqs.pageType) {
                        var currentPage = getCurrentPageType();
                        if (reqs.pageType.indexOf(currentPage) === -1) {
                            return false;
                        }
                    }
                    
                    // Check shortcodes
                    if (reqs.shortcodes) {
                        for (var i = 0; i < reqs.shortcodes.length; i++) {
                            if (!document.querySelector('[data-shortcode="' + reqs.shortcodes[i] + '"]')) {
                                return false;
                            }
                        }
                    }
                    
                    return true;
                } catch (e) {
                    return true;
                }
            }
            
            function getCurrentPageType() {
                if (document.body.classList.contains('home')) return 'home';
                if (document.body.classList.contains('single')) return 'single';
                if (document.body.classList.contains('page')) return 'page';
                if (document.body.classList.contains('shop')) return 'shop';
                return 'other';
            }
            
            function loadScript(src, originalScript) {
                var newScript = document.createElement('script');
                newScript.src = src;
                newScript.type = 'text/javascript';
                
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
     * Inject resource hints for optimization
     */
    public function injectResourceHints(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        ?>
        <link rel="preload" href="data:text/javascript;base64," as="script">
        <meta name="fp-ps-js-optimization" content="enabled">
        <?php
    }

    /**
     * Get optimization status
     *
     * @return array{enabled:bool,conditional_scripts:int,lazy_scripts:int,dynamic_scripts:int}
     */
    public function status(): array
    {
        $settings = $this->settings();

        return [
            'enabled' => $settings['enabled'],
            'conditional_scripts' => count($settings['conditional_patterns']),
            'lazy_scripts' => count($settings['lazy_load_patterns']),
            'dynamic_scripts' => $settings['dynamic_imports'] ? 1 : 0,
        ];
    }
}
