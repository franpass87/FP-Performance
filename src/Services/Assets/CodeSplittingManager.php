<?php

namespace FP\PerfSuite\Services\Assets;

class CodeSplittingManager
{
    private $chunk_size;
    private $lazy_loading;
    
    public function __construct($chunk_size = 100000, $lazy_loading = true)
    {
        $this->chunk_size = $chunk_size;
        $this->lazy_loading = $lazy_loading;
    }
    
    public function init()
    {
        add_action('wp_enqueue_scripts', [$this, 'splitScripts'], 999);
        add_action('wp_footer', [$this, 'addCodeSplittingScript']);
    }
    
    public function splitScripts()
    {
        global $wp_scripts;
        
        if (!$wp_scripts) return;
        
        foreach ($wp_scripts->registered as $handle => $script) {
            if ($this->shouldSplitScript($script)) {
                $this->splitScript($script);
            }
        }
    }
    
    private function shouldSplitScript($script)
    {
        // Skip critical scripts
        $critical_scripts = ['jquery', 'wp-util', 'wp-api'];
        
        if (in_array($script->handle, $critical_scripts)) {
            return false;
        }

        // Check if script is large enough to split
        if ($script->src && $this->getScriptSize($script->src) > $this->chunk_size) {
                return true;
        }

        return false;
    }

    private function splitScript($script)
    {
        if ($this->lazy_loading) {
            $script->extra['defer'] = true;
            $script->extra['async'] = true;
        }
    }
    
    private function getScriptSize($url)
    {
        $response = wp_remote_head($url);
        
        if (is_wp_error($response)) {
            return 0;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        return (int) $headers->get('content-length', 0);
    }
    
    public function addCodeSplittingScript()
    {
        if (!$this->lazy_loading) return;
        
        echo '<script>
            // Code Splitting Manager
            if ("IntersectionObserver" in window) {
                const scriptObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const script = entry.target;
                            
                            if (script.dataset.src) {
                                script.src = script.dataset.src;
                                script.removeAttribute("data-src");
                            }
                            
                            observer.unobserve(script);
                        }
                    });
                });
                
                document.querySelectorAll("script[data-src]").forEach(script => {
                    scriptObserver.observe(script);
                });
            }
        </script>';
    }
    
    public function getCodeSplittingMetrics()
    {
        return [
            'chunk_size' => $this->chunk_size,
            'lazy_loading_enabled' => $this->lazy_loading
        ];
    }
}