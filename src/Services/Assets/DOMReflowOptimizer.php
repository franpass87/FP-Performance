<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

use function add_action;
use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * DOM Reflow Optimizer
 *
 * Prevents forced reflows by optimizing DOM queries and batching updates
 * Implements techniques to reduce layout thrashing and improve performance
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class DOMReflowOptimizer
{
    private const OPTION = 'fp_ps_dom_reflow_optimization';
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /**
     * Costruttore
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Solo nel frontend
        if (!is_admin()) {
            // Add optimization script to footer - PRIORITÀ MEDIA per ottimizzazioni DOM
            add_action('wp_footer', [$this, 'injectOptimizationScript'], 10);
            
            // Add CSS to prevent layout shifts - PRIORITÀ BASSA per ottimizzazioni DOM
            add_action('wp_head', [$this, 'injectPreventiveCSS'], 15);
        }
        
        // Solo nel frontend
        if (!is_admin()) {
            // Optimize script loading - PRIORITÀ BASSA per ottimizzazioni avanzate
            add_filter('script_loader_tag', [$this, 'optimizeScriptLoading'], 20, 3);
        }
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,batch_updates:bool,defer_queries:bool,prevent_layout_shift:bool,optimize_jquery:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'batch_updates' => true,
            'defer_queries' => true,
            'prevent_layout_shift' => true,
            'optimize_jquery' => true,
            'use_request_animation_frame' => true,
            'debounce_resize' => true,
            'debounce_scroll' => true,
            'debounce_timeout' => 16, // ~60fps
        ];

        return wp_parse_args($this->getOption(self::OPTION, []), $defaults);
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
            'batch_updates' => !empty($settings['batch_updates']),
            'defer_queries' => !empty($settings['defer_queries']),
            'prevent_layout_shift' => !empty($settings['prevent_layout_shift']),
            'optimize_jquery' => !empty($settings['optimize_jquery']),
            'use_request_animation_frame' => !empty($settings['use_request_animation_frame']),
            'debounce_resize' => !empty($settings['debounce_resize']),
            'debounce_scroll' => !empty($settings['debounce_scroll']),
            'debounce_timeout' => isset($settings['debounce_timeout']) ? max(1, min(100, (int)$settings['debounce_timeout'])) : $current['debounce_timeout'],
        ];

        $this->setOption(self::OPTION, $new);
        
        // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
        $this->forceInit();
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_action('wp_footer', [$this, 'injectOptimizationScript'], 10);
        remove_action('wp_head', [$this, 'injectPreventiveCSS'], 15);
        remove_filter('script_loader_tag', [$this, 'optimizeScriptLoading'], 20);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Inject optimization script
     */
    public function injectOptimizationScript(): void
    {
        $settings = $this->settings();
        
        if (!$settings['enabled']) {
            return;
        }

        $debounceTimeout = $settings['debounce_timeout'];
        $useRAF = $settings['use_request_animation_frame'] ? 'true' : 'false';
        $batchUpdates = $settings['batch_updates'] ? 'true' : 'false';
        $deferQueries = $settings['defer_queries'] ? 'true' : 'false';
        $optimizeJQuery = $settings['optimize_jquery'] ? 'true' : 'false';
        $debounceResize = $settings['debounce_resize'] ? 'true' : 'false';
        $debounceScroll = $settings['debounce_scroll'] ? 'true' : 'false';

        ?>
        <script>
        (function() {
            'use strict';
            
            // Configuration
            var config = {
                batchUpdates: <?php echo $batchUpdates; ?>,
                deferQueries: <?php echo $deferQueries; ?>,
                optimizeJQuery: <?php echo $optimizeJQuery; ?>,
                useRAF: <?php echo $useRAF; ?>,
                debounceResize: <?php echo $debounceResize; ?>,
                debounceScroll: <?php echo $debounceScroll; ?>,
                debounceTimeout: <?php echo $debounceTimeout; ?>
            };

            // DOM Reflow Optimizer
            var DOMReflowOptimizer = {
                pendingUpdates: [],
                isProcessing: false,
                
                // Batch DOM updates to prevent multiple reflows
                batchUpdate: function(callback) {
                    if (!config.batchUpdates) {
                        callback();
                        return;
                    }
                    
                    this.pendingUpdates.push(callback);
                    
                    if (!this.isProcessing) {
                        this.processUpdates();
                    }
                },
                
                // Process all pending updates in a single frame
                processUpdates: function() {
                    if (this.isProcessing || this.pendingUpdates.length === 0) {
                        return;
                    }
                    
                    this.isProcessing = true;
                    
                    var self = this;
                    var process = function() {
                        var updates = self.pendingUpdates.slice();
                        self.pendingUpdates = [];
                        
                        // Execute all updates
                        updates.forEach(function(update) {
                            try {
                                update();
                            } catch (e) {
                                console.warn('[FP Performance] DOM update error:', e);
                            }
                        });
                        
                        self.isProcessing = false;
                        
                        // Process any new updates that came in
                        if (self.pendingUpdates.length > 0) {
                            if (config.useRAF) {
                                requestAnimationFrame(process);
                            } else {
                                setTimeout(process, 0);
                            }
                        }
                    };
                    
                    if (config.useRAF) {
                        requestAnimationFrame(process);
                    } else {
                        setTimeout(process, 0);
                    }
                },
                
                // Defer expensive DOM queries
                deferQuery: function(callback) {
                    if (!config.deferQueries) {
                        callback();
                        return;
                    }
                    
                    var self = this;
                    if (config.useRAF) {
                        requestAnimationFrame(function() {
                            callback();
                        });
                    } else {
                        setTimeout(function() {
                            callback();
                        }, 0);
                    }
                },
                
                // Debounce resize events
                debounceResize: function(callback) {
                    if (!config.debounceResize) {
                        window.addEventListener('resize', callback);
                        return;
                    }
                    
                    var timeout;
                    window.addEventListener('resize', function() {
                        clearTimeout(timeout);
                        timeout = setTimeout(callback, config.debounceTimeout);
                    });
                },
                
                // Debounce scroll events
                debounceScroll: function(callback) {
                    if (!config.debounceScroll) {
                        window.addEventListener('scroll', callback, { passive: true });
                        return;
                    }
                    
                    var timeout;
                    window.addEventListener('scroll', function() {
                        clearTimeout(timeout);
                        timeout = setTimeout(callback, config.debounceTimeout);
                    }, { passive: true });
                }
            };

            // Optimize jQuery if present
            if (config.optimizeJQuery && typeof jQuery !== 'undefined') {
                var originalJQuery = jQuery;
                
                // Override jQuery methods that cause reflows
                var originalShow = jQuery.fn.show;
                var originalHide = jQuery.fn.hide;
                var originalToggle = jQuery.fn.toggle;
                var originalCss = jQuery.fn.css;
                
                // Batch show/hide operations
                jQuery.fn.show = function() {
                    var self = this;
                    DOMReflowOptimizer.batchUpdate(function() {
                        return originalShow.apply(self, arguments);
                    });
                    return self;
                };
                
                jQuery.fn.hide = function() {
                    var self = this;
                    DOMReflowOptimizer.batchUpdate(function() {
                        return originalHide.apply(self, arguments);
                    });
                    return self;
                };
                
                jQuery.fn.toggle = function() {
                    var self = this;
                    DOMReflowOptimizer.batchUpdate(function() {
                        return originalToggle.apply(self, arguments);
                    });
                    return self;
                };
                
                // Optimize CSS changes
                jQuery.fn.css = function() {
                    var self = this;
                    if (arguments.length > 0) {
                        DOMReflowOptimizer.batchUpdate(function() {
                            return originalCss.apply(self, arguments);
                        });
                    } else {
                        return originalCss.apply(self, arguments);
                    }
                    return self;
                };
                
                // Override jQuery animations to use RAF
                var originalAnimate = jQuery.fn.animate;
                jQuery.fn.animate = function() {
                    var self = this;
                    var args = arguments;
                    
                    DOMReflowOptimizer.batchUpdate(function() {
                        return originalAnimate.apply(self, args);
                    });
                    
                    return self;
                };
            }

            // Optimize common DOM operations
            var originalQuerySelector = document.querySelector;
            var originalQuerySelectorAll = document.querySelectorAll;
            
            if (config.deferQueries) {
                document.querySelector = function() {
                    var self = this;
                    var args = arguments;
                    var result;
                    
                    DOMReflowOptimizer.deferQuery(function() {
                        result = originalQuerySelector.apply(self, args);
                    });
                    
                    return result;
                };
                
                document.querySelectorAll = function() {
                    var self = this;
                    var args = arguments;
                    var result;
                    
                    DOMReflowOptimizer.deferQuery(function() {
                        result = originalQuerySelectorAll.apply(self, args);
                    });
                    
                    return result;
                };
            }

            // Optimize window events
            if (config.debounceResize) {
                DOMReflowOptimizer.debounceResize(function() {
                    // Trigger custom event for other scripts
                    var event = new Event('fp_optimized_resize');
                    window.dispatchEvent(event);
                });
            }
            
            if (config.debounceScroll) {
                DOMReflowOptimizer.debounceScroll(function() {
                    // Trigger custom event for other scripts
                    var event = new Event('fp_optimized_scroll');
                    window.dispatchEvent(event);
                });
            }

            // Expose optimizer globally
            window.FPDOMReflowOptimizer = DOMReflowOptimizer;
            
            // Log initialization
            console.log('[FP Performance] DOM Reflow Optimizer initialized', config);
            
        })();
        </script>
        <?php
    }

    /**
     * Inject preventive CSS to reduce layout shifts
     */
    public function injectPreventiveCSS(): void
    {
        $settings = $this->settings();
        
        if (!$settings['enabled'] || !$settings['prevent_layout_shift']) {
            return;
        }

        ?>
        <style id="fp-prevent-layout-shift">
        /* FP Performance - Prevent Layout Shifts */
        * {
            box-sizing: border-box;
        }
        
        /* Prevent layout shifts from images */
        img {
            max-width: 100%;
            height: auto;
        }
        
        /* Optimize font loading */
        @font-face {
            font-display: swap;
        }
        
        /* Prevent layout shifts from dynamic content */
        .fp-optimized-container {
            contain: layout style;
        }
        
        /* Optimize animations */
        .fp-optimized-animation {
            will-change: transform;
            transform: translateZ(0);
        }
        
        /* Prevent reflow on common elements */
        .fp-no-reflow {
            contain: layout;
        }
        </style>
        <?php
    }

    /**
     * Optimize script loading to reduce reflows
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    public function filterScriptTag(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();
        
        if (!$settings['enabled']) {
            return $tag;
        }

        // Add optimization attributes to non-critical scripts
        if (!$this->isCriticalScript($handle, $src)) {
            // Add defer to reduce blocking
            if (strpos($tag, 'defer') === false && strpos($tag, 'async') === false) {
                $tag = str_replace('<script ', '<script defer ', $tag);
            }
            
            // Add optimization class
            $tag = str_replace('<script ', '<script data-fp-optimized="true" ', $tag);
        }

        return $tag;
    }

    /**
     * Check if script is critical (should not be deferred)
     *
     * @param string $handle Script handle
     * @param string $src Script source
     * @return bool True if critical
     */
    private function isCriticalScript(string $handle, string $src): bool
    {
        $criticalHandles = [
            'jquery', 'jquery-core', 'jquery-migrate',
            'wp-util', 'wp-hooks', 'wp-i18n',
        ];

        $criticalPatterns = [
            '/wp-includes/js/jquery/',
            '/wp-includes/js/wp-',
            '/wp-admin/',
        ];

        if (in_array($handle, $criticalHandles)) {
            return true;
        }

        foreach ($criticalPatterns as $pattern) {
            if (strpos($src, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,optimizations:array}
     */
    public function status(): array
    {
        $settings = $this->settings();
        
        return [
            'enabled' => $settings['enabled'],
            'optimizations' => [
                'batch_updates' => $settings['batch_updates'],
                'defer_queries' => $settings['defer_queries'],
                'prevent_layout_shift' => $settings['prevent_layout_shift'],
                'optimize_jquery' => $settings['optimize_jquery'],
                'use_request_animation_frame' => $settings['use_request_animation_frame'],
                'debounce_resize' => $settings['debounce_resize'],
                'debounce_scroll' => $settings['debounce_scroll'],
            ],
        ];
    }
}
