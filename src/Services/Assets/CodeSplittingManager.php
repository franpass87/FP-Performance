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
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        // Controlla se il servizio è abilitato
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return;
        }
        
        add_action('wp_enqueue_scripts', [$this, 'splitScripts'], 997);
        add_action('wp_footer', [$this, 'addCodeSplittingScript'], 42);
    }
    
    public function splitScripts()
    {
        // Controlla se il servizio è abilitato
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return;
        }
        
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
    
    /**
     * Restituisce le impostazioni
     * 
     * @return array
     */
    public function settings(): array
    {
        $savedSettings = get_option('fp_ps_code_splitting_manager', []);
        return [
            'enabled' => $savedSettings['enabled'] ?? false,
            'chunk_size' => $savedSettings['chunk_size'] ?? $this->chunk_size,
            'lazy_loading' => $savedSettings['lazy_loading'] ?? $this->lazy_loading,
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
        $currentSettings = get_option('fp_ps_code_splitting_manager', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        $newSettings['enabled'] = (bool) ($newSettings['enabled'] ?? false);
        $newSettings['chunk_size'] = max(50000, (int) ($newSettings['chunk_size'] ?? 100000));
        $newSettings['lazy_loading'] = (bool) ($newSettings['lazy_loading'] ?? true);
        
        $result = update_option('fp_ps_code_splitting_manager', $newSettings, false);
        
        if ($result) {
            $this->chunk_size = $newSettings['chunk_size'];
            $this->lazy_loading = $newSettings['lazy_loading'];
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
}