<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class JavaScriptTreeShaker
{
    private $aggressive_mode;
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    /**
     * Costruttore
     * 
     * @param bool $aggressive_mode Modalità aggressiva
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct($aggressive_mode = false, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->aggressive_mode = $aggressive_mode;
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
        
        add_action('wp_enqueue_scripts', [$this, 'optimizeScripts'], 998);
        add_action('wp_footer', [$this, 'addTreeShakingScript'], 43);
    }
    
    public function optimizeScripts()
    {
        // Controlla se il servizio è abilitato
        $settings = $this->settings();
        if (!$settings['enabled']) {
            return;
        }
        
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
    
    /**
     * Restituisce le impostazioni
     * 
     * @return array
     */
    public function settings(): array
    {
        $savedSettings = $this->getOption('fp_ps_js_tree_shaker', []);
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
        $currentSettings = $this->getOption('fp_ps_js_tree_shaker', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        $newSettings['enabled'] = (bool) ($newSettings['enabled'] ?? false);
        $newSettings['aggressive_mode'] = (bool) ($newSettings['aggressive_mode'] ?? false);
        
        $result = $this->setOption('fp_ps_js_tree_shaker', $newSettings, false);
        
        if ($result) {
            $this->aggressive_mode = $newSettings['aggressive_mode'];
            
            // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
            $this->forceInit();
        }
        
        return $result;
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_action('wp_enqueue_scripts', [$this, 'optimizeScripts'], 998);
        remove_action('wp_footer', [$this, 'addTreeShakingScript'], 43);
        
        // Ricarica le impostazioni dal database
        $settings = $this->settings();
        $this->aggressive_mode = $settings['aggressive_mode'] ?? false;
        
        // Reinizializza
        $this->init();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}