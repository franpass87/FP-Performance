<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Batch DOM Updater
 *
 * Implements batching techniques to reduce DOM reflows and improve performance
 * Groups DOM operations to minimize layout recalculations
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class BatchDOMUpdater
{
    private const OPTION = 'fp_ps_batch_dom_updates';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Solo nel frontend
        if (!is_admin()) {
            // Add batch DOM update script
            add_action('wp_footer', [$this, 'injectBatchUpdater'], 25);
            
            // Add CSS to prevent layout shifts - PRIORITÀ BASSA per batch DOM
            add_action('wp_head', [$this, 'injectBatchCSS'], 16);
        }
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,batch_size:int,use_raf:bool,optimize_animations:bool,prevent_layout_shift:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'batch_size' => 10,
            'use_raf' => true,
            'optimize_animations' => true,
            'prevent_layout_shift' => true,
            'batch_style_changes' => true,
            'batch_class_changes' => true,
            'batch_content_changes' => true,
            'debounce_timeout' => 16,
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
            'batch_size' => isset($settings['batch_size']) ? max(1, min(50, (int)$settings['batch_size'])) : $current['batch_size'],
            'use_raf' => !empty($settings['use_raf']),
            'optimize_animations' => !empty($settings['optimize_animations']),
            'prevent_layout_shift' => !empty($settings['prevent_layout_shift']),
            'batch_style_changes' => !empty($settings['batch_style_changes']),
            'batch_class_changes' => !empty($settings['batch_class_changes']),
            'batch_content_changes' => !empty($settings['batch_content_changes']),
            'debounce_timeout' => isset($settings['debounce_timeout']) ? max(1, min(100, (int)$settings['debounce_timeout'])) : $current['debounce_timeout'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Inject batch DOM updater script
     */
    public function injectBatchUpdater(): void
    {
        $settings = $this->settings();
        
        if (!$settings['enabled']) {
            return;
        }

        $batchSize = $settings['batch_size'];
        $useRAF = $settings['use_raf'] ? 'true' : 'false';
        $optimizeAnimations = $settings['optimize_animations'] ? 'true' : 'false';
        $batchStyleChanges = $settings['batch_style_changes'] ? 'true' : 'false';
        $batchClassChanges = $settings['batch_class_changes'] ? 'true' : 'false';
        $batchContentChanges = $settings['batch_content_changes'] ? 'true' : 'false';
        $debounceTimeout = $settings['debounce_timeout'];

        ?>
        <script>
        (function() {
            'use strict';
            
            // SICUREZZA: Configuration con escape per prevenire XSS
            var config = {
                batchSize: <?php echo (int) $batchSize; ?>,
                useRAF: <?php echo $useRAF ? 'true' : 'false'; ?>,
                optimizeAnimations: <?php echo $optimizeAnimations ? 'true' : 'false'; ?>,
                batchStyleChanges: <?php echo $batchStyleChanges ? 'true' : 'false'; ?>,
                batchClassChanges: <?php echo $batchClassChanges ? 'true' : 'false'; ?>,
                batchContentChanges: <?php echo $batchContentChanges ? 'true' : 'false'; ?>,
                debounceTimeout: <?php echo (int) $debounceTimeout; ?>
            };

            // Batch DOM Updater
            var BatchDOMUpdater = {
                pendingUpdates: [],
                isProcessing: false,
                updateQueue: {
                    styles: [],
                    classes: [],
                    content: [],
                    attributes: []
                },
                
                // Add update to batch
                addUpdate: function(type, element, property, value) {
                    if (!this.shouldBatch(type)) {
                        this.executeImmediate(type, element, property, value);
                        return;
                    }
                    
                    this.updateQueue[type].push({
                        element: element,
                        property: property,
                        value: value,
                        timestamp: Date.now()
                    });
                    
                    this.scheduleBatch();
                },
                
                // Check if operation should be batched
                shouldBatch: function(type) {
                    switch (type) {
                        case 'styles':
                            return config.batchStyleChanges;
                        case 'classes':
                            return config.batchClassChanges;
                        case 'content':
                            return config.batchContentChanges;
                        default:
                            return true;
                    }
                },
                
                // Execute immediate update (non-batched)
                executeImmediate: function(type, element, property, value) {
                    try {
                        switch (type) {
                            case 'styles':
                                element.style[property] = value;
                                break;
                            case 'classes':
                                if (property === 'add') {
                                    element.classList.add(value);
                                } else if (property === 'remove') {
                                    element.classList.remove(value);
                                } else if (property === 'toggle') {
                                    element.classList.toggle(value);
                                }
                                break;
                            case 'content':
                                element.innerHTML = value;
                                break;
                            case 'attributes':
                                element.setAttribute(property, value);
                                break;
                        }
                    } catch (e) {
                        console.warn('[FP Performance] DOM update error:', e);
                    }
                },
                
                // Schedule batch processing
                scheduleBatch: function() {
                    if (this.isProcessing) {
                        return;
                    }
                    
                    var self = this;
                    
                    if (config.useRAF) {
                        requestAnimationFrame(function() {
                            self.processBatch();
                        });
                    } else {
                        setTimeout(function() {
                            self.processBatch();
                        }, config.debounceTimeout);
                    }
                },
                
                // Process all batched updates
                processBatch: function() {
                    if (this.isProcessing) {
                        return;
                    }
                    
                    this.isProcessing = true;
                    
                    // Process style changes
                    this.processStyleChanges();
                    
                    // Process class changes
                    this.processClassChanges();
                    
                    // Process content changes
                    this.processContentChanges();
                    
                    // Process attribute changes
                    this.processAttributeChanges();
                    
                    this.isProcessing = false;
                    
                    // Check if more updates came in during processing
                    if (this.hasPendingUpdates()) {
                        this.scheduleBatch();
                    }
                },
                
                // Process style changes
                processStyleChanges: function() {
                    if (this.updateQueue.styles.length === 0) {
                        return;
                    }
                    
                    var updates = this.updateQueue.styles.slice();
                    this.updateQueue.styles = [];
                    
                    // Group by element for efficiency
                    var elementUpdates = {};
                    updates.forEach(function(update) {
                        var element = update.element;
                        if (!elementUpdates[element]) {
                            elementUpdates[element] = {};
                        }
                        elementUpdates[element][update.property] = update.value;
                    });
                    
                    // Apply all style changes to each element
                    Object.keys(elementUpdates).forEach(function(elementKey) {
                        var element = elementKey;
                        var styles = elementUpdates[element];
                        
                        Object.keys(styles).forEach(function(property) {
                            try {
                                element.style[property] = styles[property];
                            } catch (e) {
                                console.warn('[FP Performance] Style update error:', e);
                            }
                        });
                    });
                },
                
                // Process class changes
                processClassChanges: function() {
                    if (this.updateQueue.classes.length === 0) {
                        return;
                    }
                    
                    var updates = this.updateQueue.classes.slice();
                    this.updateQueue.classes = [];
                    
                    // Group by element and operation
                    var elementUpdates = {};
                    updates.forEach(function(update) {
                        var element = update.element;
                        if (!elementUpdates[element]) {
                            elementUpdates[element] = { add: [], remove: [], toggle: [] };
                        }
                        elementUpdates[element][update.property].push(update.value);
                    });
                    
                    // Apply all class changes to each element
                    Object.keys(elementUpdates).forEach(function(elementKey) {
                        var element = elementKey;
                        var classes = elementUpdates[element];
                        
                        // Add classes
                        classes.add.forEach(function(className) {
                            try {
                                element.classList.add(className);
                            } catch (e) {
                                console.warn('[FP Performance] Class add error:', e);
                            }
                        });
                        
                        // Remove classes
                        classes.remove.forEach(function(className) {
                            try {
                                element.classList.remove(className);
                            } catch (e) {
                                console.warn('[FP Performance] Class remove error:', e);
                            }
                        });
                        
                        // Toggle classes
                        classes.toggle.forEach(function(className) {
                            try {
                                element.classList.toggle(className);
                            } catch (e) {
                                console.warn('[FP Performance] Class toggle error:', e);
                            }
                        });
                    });
                },
                
                // Process content changes
                processContentChanges: function() {
                    if (this.updateQueue.content.length === 0) {
                        return;
                    }
                    
                    var updates = this.updateQueue.content.slice();
                    this.updateQueue.content = [];
                    
                    updates.forEach(function(update) {
                        try {
                            update.element.innerHTML = update.value;
                        } catch (e) {
                            console.warn('[FP Performance] Content update error:', e);
                        }
                    });
                },
                
                // Process attribute changes
                processAttributeChanges: function() {
                    if (this.updateQueue.attributes.length === 0) {
                        return;
                    }
                    
                    var updates = this.updateQueue.attributes.slice();
                    this.updateQueue.attributes = [];
                    
                    updates.forEach(function(update) {
                        try {
                            update.element.setAttribute(update.property, update.value);
                        } catch (e) {
                            console.warn('[FP Performance] Attribute update error:', e);
                        }
                    });
                },
                
                // Check if there are pending updates
                hasPendingUpdates: function() {
                    return this.updateQueue.styles.length > 0 ||
                           this.updateQueue.classes.length > 0 ||
                           this.updateQueue.content.length > 0 ||
                           this.updateQueue.attributes.length > 0;
                },
                
                // Optimize animations
                optimizeAnimation: function(element, animation) {
                    if (!config.optimizeAnimations) {
                        return animation;
                    }
                    
                    // Add optimization classes
                    element.classList.add('fp-optimized-animation');
                    
                    // Use transform for better performance
                    if (animation.property === 'left' || animation.property === 'top') {
                        // Convert to transform
                        var transform = 'translate(' + animation.value + ')';
                        element.style.transform = transform;
                        element.style[animation.property] = 'auto';
                    }
                    
                    return animation;
                }
            };

            // Override common DOM methods for batching
            if (config.batchStyleChanges) {
                var originalStyleSet = Object.getOwnPropertyDescriptor(Element.prototype, 'style').set;
                
                Object.defineProperty(Element.prototype, 'style', {
                    set: function(value) {
                        if (typeof value === 'object') {
                            // Batch multiple style changes
                            Object.keys(value).forEach(function(property) {
                                BatchDOMUpdater.addUpdate('styles', this, property, value[property]);
                            }.bind(this));
                        } else {
                            originalStyleSet.call(this, value);
                        }
                    },
                    get: function() {
                        return this._style || (this._style = {});
                    }
                });
            }

            // Override classList methods for batching
            if (config.batchClassChanges) {
                var originalAdd = Element.prototype.classList.add;
                var originalRemove = Element.prototype.classList.remove;
                var originalToggle = Element.prototype.classList.toggle;
                
                Element.prototype.classList.add = function() {
                    var self = this;
                    var classes = Array.from(arguments);
                    classes.forEach(function(className) {
                        BatchDOMUpdater.addUpdate('classes', self, 'add', className);
                    });
                };
                
                Element.prototype.classList.remove = function() {
                    var self = this;
                    var classes = Array.from(arguments);
                    classes.forEach(function(className) {
                        BatchDOMUpdater.addUpdate('classes', self, 'remove', className);
                    });
                };
                
                Element.prototype.classList.toggle = function(className) {
                    BatchDOMUpdater.addUpdate('classes', this, 'toggle', className);
                };
            }

            // Expose updater globally
            window.FPBatchDOMUpdater = BatchDOMUpdater;
            
            // Log initialization
            console.log('[FP Performance] Batch DOM Updater initialized', config);
            
        })();
        </script>
        <?php
    }

    /**
     * Inject batch CSS to prevent layout shifts
     */
    public function injectBatchCSS(): void
    {
        $settings = $this->settings();
        
        if (!$settings['enabled'] || !$settings['prevent_layout_shift']) {
            return;
        }

        ?>
        <style id="fp-batch-dom-css">
        /* FP Performance - Batch DOM Updates */
        .fp-optimized-animation {
            will-change: transform;
            transform: translateZ(0);
            backface-visibility: hidden;
            perspective: 1000px;
        }
        
        .fp-batch-container {
            contain: layout style;
        }
        
        .fp-no-reflow {
            contain: layout;
        }
        
        /* Optimize common elements */
        img, video, iframe {
            max-width: 100%;
            height: auto;
        }
        
        /* Prevent layout shifts */
        .fp-stable-layout {
            min-height: 1px;
            overflow: hidden;
        }
        </style>
        <?php
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
                'batch_size' => $settings['batch_size'],
                'use_raf' => $settings['use_raf'],
                'optimize_animations' => $settings['optimize_animations'],
                'prevent_layout_shift' => $settings['prevent_layout_shift'],
                'batch_style_changes' => $settings['batch_style_changes'],
                'batch_class_changes' => $settings['batch_class_changes'],
                'batch_content_changes' => $settings['batch_content_changes'],
            ],
        ];
    }
}
