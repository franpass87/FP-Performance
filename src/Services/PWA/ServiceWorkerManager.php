<?php

namespace FP\PerfSuite\Services\PWA;

class ServiceWorkerManager
{
    private $cache_strategy;
    private $cache_duration;
    private $offline_fallback;
    
    public function __construct($cache_strategy = 'cache-first', $cache_duration = 86400, $offline_fallback = true)
    {
        $this->cache_strategy = $cache_strategy;
        $this->cache_duration = $cache_duration;
        $this->offline_fallback = $offline_fallback;
    }
    
    public function init()
    {
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueueServiceWorker'], 990);
            add_action('wp_footer', [$this, 'addServiceWorkerScript'], 40);
        }
    }
    
    public function enqueueServiceWorker()
    {
        // SICUREZZA: Verifichiamo che il file esista prima di enqueue
        $script_path = plugin_dir_path(__FILE__) . '../../assets/js/service-worker.js';
        if (file_exists($script_path)) {
            wp_enqueue_script('fp-sw', plugin_dir_url(__FILE__) . '../../assets/js/service-worker.js', [], '1.0.0', true);
        } else {
            error_log('FP Performance Suite: Service worker script not found');
        }
    }
    
    public function addServiceWorkerScript()
    {
        $config = [
            'cacheStrategy' => $this->cache_strategy,
            'cacheDuration' => $this->cache_duration,
            'offlineFallback' => $this->offline_fallback
        ];
        
        echo '<script>window.fpSWConfig = ' . json_encode($config) . ';</script>';
    }
    
    public function generateServiceWorker()
    {
        $sw_content = $this->getServiceWorkerTemplate();
        $sw_content = str_replace('{{CACHE_STRATEGY}}', $this->cache_strategy, $sw_content);
        $sw_content = str_replace('{{CACHE_DURATION}}', $this->cache_duration, $sw_content);
        $sw_content = str_replace('{{OFFLINE_FALLBACK}}', $this->offline_fallback ? 'true' : 'false', $sw_content);
        
        return $sw_content;
    }
    
    private function getServiceWorkerTemplate()
    {
        return "
        const CACHE_NAME = 'fp-performance-v1';
        const CACHE_STRATEGY = '{{CACHE_STRATEGY}}';
        const CACHE_DURATION = {{CACHE_DURATION}};
        const OFFLINE_FALLBACK = {{OFFLINE_FALLBACK}};
        
        self.addEventListener('install', event => {
            event.waitUntil(
                caches.open(CACHE_NAME).then(cache => {
                    return cache.addAll(['/']);
                })
            );
        });
        
        self.addEventListener('fetch', event => {
            if (CACHE_STRATEGY === 'cache-first') {
                event.respondWith(
                    caches.match(event.request).then(response => {
                        return response || fetch(event.request);
                    })
                );
            }
        });
        ";
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}