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
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_footer', [$this, 'addPrefetchScript']);
    }
    
    public function enqueueScripts()
    {
        wp_enqueue_script('fp-prefetch', plugin_dir_url(__FILE__) . '../../assets/js/predictive-prefetch.js', [], '1.0.0', true);
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
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}