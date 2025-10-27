<?php

namespace FP\PerfSuite\Services\Assets;

class PredictivePrefetching
{
    private $strategy;
    private $hover_delay;
    private $limit;
    
    public function __construct($strategy = 'hover', $hover_delay = 100, $limit = 5)
    {
        $this->strategy = $strategy;
        $this->hover_delay = $hover_delay;
        $this->limit = $limit;
    }
    
    public function init()
    {
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts'], 989);
            add_action('wp_footer', [$this, 'addPrefetchScript'], 41);
        }
    }
    
    public function enqueueScripts()
    {
        // SICUREZZA: Verifichiamo che il file esista prima di enqueue
        $script_path = plugin_dir_path(__FILE__) . '../../assets/js/predictive-prefetch.js';
        if (file_exists($script_path)) {
            wp_enqueue_script('fp-prefetch', plugin_dir_url(__FILE__) . '../../assets/js/predictive-prefetch.js', [], '1.0.0', true);
        } else {
            error_log('FP Performance Suite: Predictive prefetch script not found');
        }
    }
    
    public function addPrefetchScript()
    {
        $config = [
            'strategy' => $this->strategy,
            'hoverDelay' => $this->hover_delay,
            'limit' => $this->limit
        ];
        
        echo '<script>window.fpPrefetchConfig = ' . json_encode($config) . ';</script>';
    }
    
    public function getPrefetchLinks()
    {
        $links = [];
        $count = 0;
        
        if ($this->strategy === 'hover') {
            $links = $this->getHoverLinks();
        } elseif ($this->strategy === 'viewport') {
            $links = $this->getViewportLinks();
        }
        
        return array_slice($links, 0, $this->limit);
    }
    
    private function getHoverLinks()
    {
        $links = [];
        $menu_items = wp_get_nav_menu_items('primary');
        
        if ($menu_items) {
            foreach ($menu_items as $item) {
                if ($count >= $this->limit) break;
                $links[] = $item->url;
                $count++;
            }
        }
        
        return $links;
    }
    
    private function getViewportLinks()
    {
        $links = [];
        $posts = get_posts(['numberposts' => $this->limit]);
        
        foreach ($posts as $post) {
            $links[] = get_permalink($post->ID);
        }
        
        return $links;
    }
    
    /**
     * Restituisce le impostazioni del predictive prefetching
     * 
     * @return array Array con le impostazioni
     */
    public function getSettings(): array
    {
        // Recupera le impostazioni salvate nel database
        $savedSettings = get_option('fp_ps_predictive_prefetch', []);
        
        return [
            'enabled' => $savedSettings['enabled'] ?? false,
            'strategy' => $savedSettings['strategy'] ?? $this->strategy,
            'hover_delay' => $savedSettings['hover_delay'] ?? $this->hover_delay,
            'prefetch_limit' => $savedSettings['prefetch_limit'] ?? $this->limit,
            'ignore_patterns' => $savedSettings['ignore_patterns'] ?? [],
        ];
    }
    
    /**
     * Aggiorna le impostazioni del predictive prefetching
     * 
     * @param array $settings Array con le nuove impostazioni
     * @return bool True se l'aggiornamento è riuscito
     */
    public function updateSettings(array $settings): bool
    {
        // Recupera le impostazioni esistenti
        $currentSettings = get_option('fp_ps_predictive_prefetch', []);
        
        // Merge con le nuove impostazioni
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione e sanitizzazione
        $newSettings['enabled'] = !empty($newSettings['enabled']);
        $newSettings['strategy'] = in_array($newSettings['strategy'] ?? '', ['hover', 'viewport', 'aggressive']) 
            ? $newSettings['strategy'] 
            : 'hover';
        $newSettings['hover_delay'] = max(0, min(2000, (int) ($newSettings['hover_delay'] ?? 100)));
        $newSettings['prefetch_limit'] = max(1, min(20, (int) ($newSettings['prefetch_limit'] ?? 5)));
        $newSettings['ignore_patterns'] = is_array($newSettings['ignore_patterns'] ?? []) 
            ? $newSettings['ignore_patterns'] 
            : [];
        
        // Salva nel database
        $result = update_option('fp_ps_predictive_prefetch', $newSettings, false);
        
        // Aggiorna anche le proprietà della classe
        $this->strategy = $newSettings['strategy'];
        $this->hover_delay = $newSettings['hover_delay'];
        $this->limit = $newSettings['prefetch_limit'];
        
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