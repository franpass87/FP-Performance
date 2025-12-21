<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

use function add_action;
use function add_filter;
use function wp_parse_args;

/**
 * jQuery Performance Optimizer
 *
 * Optimizes jQuery usage to prevent forced reflows and improve performance
 * Implements batching, caching, and optimization techniques for jQuery operations
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class jQueryOptimizer
{
    private const OPTION = 'fp_ps_jquery_optimization';
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Solo nel frontend
        if (!is_admin()) {
            // Add jQuery optimization script - PRIORITÀ MEDIA per ottimizzazioni jQuery
            add_action('wp_footer', [$this, 'injectjQueryOptimizer'], 15);
        }
        
        // Solo nel frontend
        if (!is_admin()) {
            // Optimize jQuery loading - PRIORITÀ BASSA per ottimizzazioni jQuery specifiche
            add_filter('script_loader_tag', [$this, 'optimizejQueryLoading'], 25, 3);
        }
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,batch_operations:bool,cache_selectors:bool,optimize_animations:bool,prevent_reflows:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'batch_operations' => true,
            'cache_selectors' => true,
            'optimize_animations' => true,
            'prevent_reflows' => true,
            'use_request_animation_frame' => true,
            'debounce_events' => true,
            'optimize_ready' => true,
            'lazy_loading' => true,
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
            'batch_operations' => !empty($settings['batch_operations']),
            'cache_selectors' => !empty($settings['cache_selectors']),
            'optimize_animations' => !empty($settings['optimize_animations']),
            'prevent_reflows' => !empty($settings['prevent_reflows']),
            'use_request_animation_frame' => !empty($settings['use_request_animation_frame']),
            'debounce_events' => !empty($settings['debounce_events']),
            'optimize_ready' => !empty($settings['optimize_ready']),
            'lazy_loading' => !empty($settings['lazy_loading']),
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
        remove_action('wp_footer', [$this, 'injectjQueryOptimizer'], 15);
        remove_filter('script_loader_tag', [$this, 'optimizejQueryLoading'], 25);
        
        // Reinizializza
        $this->register();
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }

    /**
     * Set option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $value Value to set
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value);
        }
        return update_option($key, $value);
    }

    /**
     * Inject jQuery optimization script
     */
    public function injectjQueryOptimizer(): void
    {
        $settings = $this->settings();
        
        if (!$settings['enabled']) {
            return;
        }

        $batchOps = $settings['batch_operations'] ? 'true' : 'false';
        $cacheSelectors = $settings['cache_selectors'] ? 'true' : 'false';
        $optimizeAnimations = $settings['optimize_animations'] ? 'true' : 'false';
        $preventReflows = $settings['prevent_reflows'] ? 'true' : 'false';
        $useRAF = $settings['use_request_animation_frame'] ? 'true' : 'false';
        $debounceEvents = $settings['debounce_events'] ? 'true' : 'false';
        $optimizeReady = $settings['optimize_ready'] ? 'true' : 'false';
        $lazyLoading = $settings['lazy_loading'] ? 'true' : 'false';

        ?>
        <script>
        (function() {
            'use strict';
            
            // Wait for jQuery to be available
            function waitForjQuery(callback) {
                if (typeof jQuery !== 'undefined') {
                    callback(jQuery);
                } else {
                    setTimeout(function() {
                        waitForjQuery(callback);
                    }, 10);
                }
            }
            
            waitForjQuery(function($) {
                // Configuration
                var config = {
                    batchOperations: <?php echo $batchOps; ?>,
                    cacheSelectors: <?php echo $cacheSelectors; ?>,
                    optimizeAnimations: <?php echo $optimizeAnimations; ?>,
                    preventReflows: <?php echo $preventReflows; ?>,
                    useRAF: <?php echo $useRAF; ?>,
                    debounceEvents: <?php echo $debounceEvents; ?>,
                    optimizeReady: <?php echo $optimizeReady; ?>,
                    lazyLoading: <?php echo $lazyLoading; ?>
                };

                // jQuery Performance Optimizer
                var jQueryOptimizer = {
                    selectorCache: new Map(),
                    pendingOperations: [],
                    isProcessing: false,
                    
                    // Cache frequently used selectors
                    getCachedSelector: function(selector) {
                        if (!config.cacheSelectors) {
                            return $(selector);
                        }
                        
                        if (this.selectorCache.has(selector)) {
                            return this.selectorCache.get(selector);
                        }
                        
                        var elements = $(selector);
                        this.selectorCache.set(selector, elements);
                        return elements;
                    },
                    
                    // Batch DOM operations to prevent multiple reflows
                    batchOperation: function(operation) {
                        if (!config.batchOperations) {
                            operation();
                            return;
                        }
                        
                        this.pendingOperations.push(operation);
                        
                        if (!this.isProcessing) {
                            this.processOperations();
                        }
                    },
                    
                    // Process all pending operations
                    processOperations: function() {
                        if (this.isProcessing || this.pendingOperations.length === 0) {
                            return;
                        }
                        
                        this.isProcessing = true;
                        
                        var self = this;
                        var process = function() {
                            var operations = self.pendingOperations.slice();
                            self.pendingOperations = [];
                            
                            // Execute all operations
                            operations.forEach(function(op) {
                                try {
                                    op();
                                } catch (e) {
                                    console.warn('[FP Performance] jQuery operation error:', e);
                                }
                            });
                            
                            self.isProcessing = false;
                            
                            // Process any new operations
                            if (self.pendingOperations.length > 0) {
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
                    
                    // Optimize jQuery ready function
                    optimizeReady: function(callback) {
                        if (!config.optimizeReady) {
                            $(document).ready(callback);
                            return;
                        }
                        
                        var self = this;
                        $(document).ready(function() {
                            self.batchOperation(callback);
                        });
                    },
                    
                    // Debounce events to prevent excessive calls
                    debounceEvent: function(element, event, callback, delay) {
                        if (!config.debounceEvents) {
                            $(element).on(event, callback);
                            return;
                        }
                        
                        var timeout;
                        $(element).on(event, function() {
                            clearTimeout(timeout);
                            var self = this;
                            var args = arguments;
                            timeout = setTimeout(function() {
                                callback.apply(self, args);
                            }, delay || 16);
                        });
                    }
                };

                // Override jQuery methods to prevent reflows
                if (config.preventReflows) {
                    // Store original methods
                    var originalShow = $.fn.show;
                    var originalHide = $.fn.hide;
                    var originalToggle = $.fn.toggle;
                    var originalCss = $.fn.css;
                    var originalAddClass = $.fn.addClass;
                    var originalRemoveClass = $.fn.removeClass;
                    var originalToggleClass = $.fn.toggleClass;
                    
                    // Override show/hide with batching
                    $.fn.show = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalShow.apply(self, arguments);
                        });
                        return self;
                    };
                    
                    $.fn.hide = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalHide.apply(self, arguments);
                        });
                        return self;
                    };
                    
                    $.fn.toggle = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalToggle.apply(self, arguments);
                        });
                        return self;
                    };
                    
                    // Override CSS with batching
                    $.fn.css = function() {
                        var self = this;
                        if (arguments.length > 0) {
                            jQueryOptimizer.batchOperation(function() {
                                return originalCss.apply(self, arguments);
                            });
                        } else {
                            return originalCss.apply(self, arguments);
                        }
                        return self;
                    };
                    
                    // Override class operations with batching
                    $.fn.addClass = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalAddClass.apply(self, arguments);
                        });
                        return self;
                    };
                    
                    $.fn.removeClass = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalRemoveClass.apply(self, arguments);
                        });
                        return self;
                    };
                    
                    $.fn.toggleClass = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalToggleClass.apply(self, arguments);
                        });
                        return self;
                    };
                }

                // Optimize animations
                if (config.optimizeAnimations) {
                    var originalAnimate = $.fn.animate;
                    
                    $.fn.animate = function() {
                        var self = this;
                        var args = arguments;
                        
                        jQueryOptimizer.batchOperation(function() {
                            return originalAnimate.apply(self, args);
                        });
                        
                        return self;
                    };
                    
                    // Optimize fadeIn/fadeOut
                    var originalFadeIn = $.fn.fadeIn;
                    var originalFadeOut = $.fn.fadeOut;
                    
                    $.fn.fadeIn = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalFadeIn.apply(self, arguments);
                        });
                        return self;
                    };
                    
                    $.fn.fadeOut = function() {
                        var self = this;
                        jQueryOptimizer.batchOperation(function() {
                            return originalFadeOut.apply(self, arguments);
                        });
                        return self;
                    });
                }

                // Optimize jQuery ready
                if (config.optimizeReady) {
                    var originalReady = $.fn.ready;
                    
                    $.fn.ready = function(callback) {
                        jQueryOptimizer.optimizeReady(callback);
                    };
                }

                // Add optimized event handling
                if (config.debounceEvents) {
                    // Debounce resize events
                    jQueryOptimizer.debounceEvent(window, 'resize', function() {
                        $(window).trigger('fp_optimized_resize');
                    }, 16);
                    
                    // Debounce scroll events
                    jQueryOptimizer.debounceEvent(window, 'scroll', function() {
                        $(window).trigger('fp_optimized_scroll');
                    }, 16);
                }

                // Optimize common selectors with caching
                if (config.cacheSelectors) {
                    // Override $ function for common selectors
                    var original$ = window.$;
                    
                    window.$ = function(selector) {
                        if (typeof selector === 'string' && selector.length < 100) {
                            return jQueryOptimizer.getCachedSelector(selector);
                        }
                        return original$(selector);
                    };
                }

                // Expose optimizer globally
                window.FPjQueryOptimizer = jQueryOptimizer;
                
                // Log initialization
                console.log('[FP Performance] jQuery Optimizer initialized', config);
            });
            
        })();
        </script>
        <?php
    }

    /**
     * Optimize jQuery script loading
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

        // Optimize jQuery loading
        if (in_array($handle, ['jquery', 'jquery-core', 'jquery-migrate'])) {
            // Add optimization attributes
            if (strpos($tag, 'data-fp-optimized') === false) {
                $tag = str_replace('<script ', '<script data-fp-optimized="true" ', $tag);
            }
            
            // Ensure proper loading order
            if ($handle === 'jquery-core' && strpos($tag, 'defer') === false) {
                $tag = str_replace('<script ', '<script defer ', $tag);
            }
        }

        return $tag;
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
                'batch_operations' => $settings['batch_operations'],
                'cache_selectors' => $settings['cache_selectors'],
                'optimize_animations' => $settings['optimize_animations'],
                'prevent_reflows' => $settings['prevent_reflows'],
                'use_request_animation_frame' => $settings['use_request_animation_frame'],
                'debounce_events' => $settings['debounce_events'],
                'optimize_ready' => $settings['optimize_ready'],
                'lazy_loading' => $settings['lazy_loading'],
            ],
        ];
    }
}
