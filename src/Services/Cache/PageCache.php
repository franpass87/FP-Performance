<?php

namespace FP\PerfSuite\Services\Cache;

class PageCache
{
    private $cache_dir;
    private $ttl;
    
    public function __construct($cache_dir = null, $ttl = 3600)
    {
        $this->cache_dir = $cache_dir ?: WP_CONTENT_DIR . '/cache/fp-performance/page-cache';
        $this->ttl = $ttl;
        
        if (!file_exists($this->cache_dir)) {
            wp_mkdir_p($this->cache_dir);
        }
    }
    
    public function get($key)
    {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = file_get_contents($file);
        $cache_data = unserialize($data);
        
        if ($cache_data['expires'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return $cache_data['content'];
    }
    
    public function set($key, $content)
    {
        $file = $this->getCacheFile($key);
        $cache_data = [
            'content' => $content,
            'expires' => time() + $this->ttl
        ];
        
        return file_put_contents($file, serialize($cache_data)) !== false;
    }
    
    public function delete($key)
    {
        $file = $this->getCacheFile($key);
        return file_exists($file) ? unlink($file) : true;
    }
    
    public function clear()
    {
        $files = glob($this->cache_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
    
    private function getCacheFile($key)
    {
        return $this->cache_dir . '/' . md5($key) . '.cache';
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // PageCache non ha hook specifici da registrare
        // È utilizzato principalmente per operazioni di cache
    }
}