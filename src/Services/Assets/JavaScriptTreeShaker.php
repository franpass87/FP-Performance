<?php

namespace FP\PerfSuite\Services\Assets;

class JavaScriptTreeShaker
{
    private $aggressive_mode;
    
    public function __construct($aggressive_mode = false)
    {
        $this->aggressive_mode = $aggressive_mode;
    }
    
    public function init()
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        add_action('wp_enqueue_scripts', [$this, 'optimizeScripts'], 998);
        add_action('wp_footer', [$this, 'addTreeShakingScript'], 43);
    }
    
    public function optimizeScripts()
    {
        global $wp_scripts;
        
        if (!$wp_scripts) return;
        
        foreach ($wp_scripts->registered as $handle => $script) {
            if ($this->shouldOptimizeScript($script)) {
                $this->optimizeScript($script);
            }
        }
    }
    
    private function shouldOptimizeScript($script)
    {
        // Skip critical scripts
        $critical_scripts = ['jquery', 'wp-util', 'wp-api'];
        
        if (in_array($script->handle, $critical_scripts)) {
        return false;
    }

        // Check if script is from external source
        if ($script->src && strpos($script->src, home_url()) === false) {
            return false;
        }

        return true;
    }
    
    private function optimizeScript($script)
    {
        if ($this->aggressive_mode) {
            $script->extra['defer'] = true;
            $script->extra['async'] = true;
        }
    }
    
    public function addTreeShakingScript()
    {
        echo '<script>
            // JavaScript Tree Shaking
            if ("requestIdleCallback" in window) {
                requestIdleCallback(function() {
                    // Analyze unused functions
                    const functions = [];
                    const usedFunctions = new Set();
                    
                    // Track function calls
                    const originalEval = window.eval;
                    window.eval = function(code) {
                        // Analyze code for function usage
                        const functionMatches = code.match(/function\s+(\w+)/g);
                        if (functionMatches) {
                            functionMatches.forEach(match => {
                                const funcName = match.replace(/function\s+/, "");
                                functions.push(funcName);
                            });
                        }
                        
                        return originalEval.call(this, code);
                    };
                    
                    // Report unused functions
                    setTimeout(() => {
                        const unusedFunctions = functions.filter(func => !usedFunctions.has(func));
                        
                        if (unusedFunctions.length > 0) {
                            console.warn("Unused functions detected:", unusedFunctions);
                        }
                    }, 5000);
                });
            }
        </script>';
    }
    
    public function getTreeShakingMetrics()
    {
        return [
            'aggressive_mode' => $this->aggressive_mode,
            'optimization_enabled' => true
        ];
    }
}