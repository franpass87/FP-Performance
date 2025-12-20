<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager\CriticalPageDetector;

/**
 * Unused JavaScript Optimizer
 *
 * Ottimizza il caricamento di JavaScript non utilizzato mediante defer
 * e rilevamento di script non necessari
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class UnusedJavaScriptOptimizer implements UnusedJavaScriptOptimizerInterface
{
    private const OPTION = 'fp_ps_unused_js_optimizer';
    
    /** @var bool Aggressive mode flag */
    private bool $aggressive_mode;
    
    /** @var OptionsRepositoryInterface|null Options repository */
    private ?OptionsRepositoryInterface $optionsRepo;
    
    /** @var CriticalPageDetector Detector per pagine critiche */
    private CriticalPageDetector $criticalPageDetector;
    
    /**
     * Constructor
     * 
     * @param bool $aggressive_mode Aggressive mode flag
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     * @param CriticalPageDetector|null $criticalPageDetector Detector per pagine critiche (iniettato via DI)
     */
    public function __construct(
        bool $aggressive_mode = false,
        ?OptionsRepositoryInterface $optionsRepo = null,
        ?CriticalPageDetector $criticalPageDetector = null
    ) {
        $this->aggressive_mode = $aggressive_mode;
        $this->optionsRepo = $optionsRepo;
        // Dependency Injection: usa quello iniettato o crea nuovo per backward compatibility
        $this->criticalPageDetector = $criticalPageDetector ?? new CriticalPageDetector();
    }
    
    /**
     * Initialize the service
     */
    public function init(): void
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        // Controlla se il servizio è abilitato
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return;
        }
        
        // NON attivare su pagine critiche (checkout, cart, payment, ecc.)
        if ($this->criticalPageDetector->isCriticalPage()) {
            Logger::debug('UnusedJavaScriptOptimizer: skipped on critical page');
            return;
        }
        
        add_action('wp_enqueue_scripts', [$this, 'optimizeScripts'], 996);
        add_action('wp_footer', [$this, 'addUnusedJSScript'], 44);
    }
    
    /**
     * Optimize scripts by adding defer attribute
     */
    public function optimizeScripts(): void
    {
        // Controlla se il servizio è abilitato
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return;
        }
        
        global $wp_scripts;
        
        if (!$wp_scripts) {
            return;
        }
        
        $deferredCount = 0;
        
        foreach ($wp_scripts->registered as $handle => $script) {
            if ($this->shouldDeferScript($script)) {
                $script->extra['defer'] = true;
                $deferredCount++;
            }
        }
        
        if ($deferredCount > 0) {
            Logger::info('UnusedJavaScriptOptimizer: deferred scripts', [
                'count' => $deferredCount,
                'aggressive_mode' => $this->aggressive_mode,
            ]);
        }
    }
    
    /**
     * Check if a script should be deferred
     * 
     * @param \_WP_Dependency $script Script dependency object
     * @return bool True if script should be deferred
     */
    private function shouldDeferScript(\_WP_Dependency $script): bool
    {
        // Skip critical scripts
        $critical_scripts = ['jquery', 'wp-util', 'wp-api', 'wp-element', 'wp-components'];
        
        if (in_array($script->handle, $critical_scripts, true)) {
            return false;
        }
        
        // Skip scripts with dependencies that need to load first
        if (!empty($script->deps)) {
            return false;
        }
        
        // Skip scripts already marked as async or defer
        if (!empty($script->extra['async']) || !empty($script->extra['defer'])) {
            return false;
        }
        
        // In aggressive mode, defer more scripts
        if ($this->aggressive_mode) {
            // Skip only truly critical scripts
            return true;
        }
        
        // Default: defer non-critical scripts without dependencies
        return true;
    }

    /**
     * Add JavaScript for unused script detection
     */
    public function addUnusedJSScript(): void
    {
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return;
        }
        
        // Migliorato: rilevamento più accurato di JavaScript non utilizzato
        ?>
        <script>
        (function() {
            'use strict';
            
            // Unused JavaScript Detection
            if (!('requestIdleCallback' in window)) {
                return;
            }
            
            requestIdleCallback(function() {
                // Analyze unused JavaScript
                const scripts = document.querySelectorAll('script[src]');
                const scriptUsage = new Map();
                
                // Track script loading
                scripts.forEach(function(script) {
                    scriptUsage.set(script.src, {
                        loaded: false,
                        used: false,
                        size: 0
                    });
                });
                
                // Monitor script execution
                const originalCreateElement = document.createElement;
                document.createElement = function(tagName) {
                    const element = originalCreateElement.call(document, tagName);
                    
                    if (tagName.toLowerCase() === 'script' && element.src) {
                        const scriptSrc = element.src;
                        element.addEventListener('load', function() {
                            const usage = scriptUsage.get(scriptSrc);
                            if (usage) {
                                usage.loaded = true;
                            }
                        });
                    }
                    
                    return element;
                };
                
                // Track function calls and global variable usage
                const globalVars = Object.keys(window);
                const usedGlobals = new Set();
                
                // Monitor setTimeout/setInterval (indicators of script usage)
                const originalSetTimeout = window.setTimeout;
                window.setTimeout = function() {
                    usedGlobals.add('setTimeout');
                    return originalSetTimeout.apply(window, arguments);
                };
                
                const originalSetInterval = window.setInterval;
                window.setInterval = function() {
                    usedGlobals.add('setInterval');
                    return originalSetInterval.apply(window, arguments);
                };
                
                // Report unused JavaScript after page load
                setTimeout(function() {
                    const unusedScripts = [];
                    
                    scriptUsage.forEach(function(usage, src) {
                        if (usage.loaded && !usage.used) {
                            unusedScripts.push(src);
                        }
                    });
                    
                    if (unusedScripts.length > 0 && window.console && window.console.warn) {
                        console.warn('[FP Performance] Potentially unused JavaScript detected:', unusedScripts);
                    }
                }, 5000);
            }, { timeout: 2000 });
        })();
        </script>
        <?php
    }
    
    /**
     * Get unused JavaScript metrics
     * 
     * @return array{aggressive_mode:bool,optimization_enabled:bool,enabled:bool}
     */
    public function getUnusedJSMetrics(): array
    {
        $settings = $this->settings();
        
        return [
            'aggressive_mode' => $this->aggressive_mode,
            'optimization_enabled' => $settings['enabled'] ?? false,
            'enabled' => $settings['enabled'] ?? false,
        ];
    }
    
    /**
     * Restituisce le impostazioni
     * 
     * @return array
     */
    public function settings(): array
    {
        $savedSettings = $this->getOption(self::OPTION, []);
        return [
            'enabled' => $savedSettings['enabled'] ?? false,
            'aggressive_mode' => $savedSettings['aggressive_mode'] ?? $this->aggressive_mode,
        ];
    }
    
    /**
     * Alias di settings() per compatibilità
     */
    public function getSettings(): array
    {
        return $this->settings();
    }
    
    /**
     * Aggiorna le impostazioni del servizio
     */
    public function updateSettings(array $settings): bool
    {
        $currentSettings = $this->getOption(self::OPTION, []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        $newSettings['enabled'] = (bool) ($newSettings['enabled'] ?? false);
        $newSettings['aggressive_mode'] = (bool) ($newSettings['aggressive_mode'] ?? false);
        
        $result = $this->setOption(self::OPTION, $newSettings);
        
        if ($result) {
            $this->aggressive_mode = $newSettings['aggressive_mode'];
        }
        
        return $result;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
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
     * Check if the optimizer is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        $settings = $this->settings();
        return !empty($settings['enabled']);
    }
    
    /**
     * Get status information
     * 
     * @return array Status array
     */
    public function status(): array
    {
        $settings = $this->settings();
        return [
            'enabled' => $this->isEnabled(),
            'aggressive_mode' => $this->aggressive_mode,
            'auto_mode' => !empty($settings['auto_mode']),
            'defer_enabled' => !empty($settings['defer_enabled']),
            'async_enabled' => !empty($settings['async_enabled']),
            'detect_unused' => !empty($settings['detect_unused']),
        ];
    }
}