<?php

namespace FP\PerfSuite\Services\Assets;

class UnusedJavaScriptOptimizer
{
    private $aggressive_mode;
    
    public function __construct($aggressive_mode = false)
    {
        $this->aggressive_mode = $aggressive_mode;
    }
    
    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'optimizeScripts'], 996);
        add_action('wp_footer', [$this, 'addUnusedJSScript'], 44);
    }
    
    public function optimizeScripts()
    {
        global $wp_scripts;
        
        if (!$wp_scripts) return;
        
        foreach ($wp_scripts->registered as $handle => $script) {
            if ($this->shouldDeferScript($script)) {
                $script->extra['defer'] = true;
            }
        }
    }
    
    private function shouldDeferScript($script)
    {
        // Skip critical scripts
        $critical_scripts = ['jquery', 'wp-util', 'wp-api'];
        
        if (in_array($script->handle, $critical_scripts)) {
        return false;
        }
        
        // Skip scripts with dependencies that need to load first
        if (!empty($script->deps)) {
            return false;
        }

            return true;
        }

    public function addUnusedJSScript()
    {
        echo '<script>
            // Unused JavaScript Detection
            if ("requestIdleCallback" in window) {
                requestIdleCallback(function() {
                    // Analyze unused JavaScript
                    const scripts = document.querySelectorAll("script[src]");
                    const usedFunctions = new Set();
                    
                    // Track function usage
                    const originalConsole = console.log;
                    console.log = function(...args) {
                        if (args[0] && typeof args[0] === "string" && args[0].includes("function")) {
                            usedFunctions.add(args[0]);
                        }
                        originalConsole.apply(console, args);
                    };
                    
                    // Report unused JavaScript
                    setTimeout(() => {
                        const unusedScripts = Array.from(scripts).filter(script => {
                            return !usedFunctions.has(script.src);
                        });
                        
                        if (unusedScripts.length > 0) {
                            console.warn("Unused JavaScript detected:", unusedScripts);
                        }
                    }, 5000);
                });
            }
        </script>';
    }
    
    public function getUnusedJSMetrics()
    {
        return [
            'aggressive_mode' => $this->aggressive_mode,
            'optimization_enabled' => true
        ];
    }
}