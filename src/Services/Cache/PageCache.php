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
        // SICUREZZA: Validiamo la chiave per prevenire path traversal
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->getCacheFile($key);
        
        // SICUREZZA: Verifichiamo che il file sia nella directory cache
        if (!$this->isValidCacheFile($file)) {
            return false;
        }
        
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }
        
        try {
            $data = file_get_contents($file);
            if ($data === false) {
                return false;
            }
            
            // SICUREZZA: Validiamo i dati prima di unserialize per prevenire object injection
            $cache_data = $this->safeUnserialize($data);
            
            if (!is_array($cache_data) || !isset($cache_data['expires'])) {
                $this->delete($key);
                return false;
            }
            
            if ($cache_data['expires'] < time()) {
                $this->delete($key);
                return false;
            }
            
            return $cache_data['content'];
        } catch (\Exception $e) {
            error_log('FP Performance Suite: Cache read error: ' . $e->getMessage());
            return false;
        }
    }
    
    public function set($key, $content)
    {
        // SICUREZZA: Validiamo la chiave per prevenire path traversal
        if (empty($key) || !is_string($key)) {
            return false;
        }
        
        $file = $this->getCacheFile($key);
        
        // SICUREZZA: Verifichiamo che il file sia nella directory cache
        if (!$this->isValidCacheFile($file)) {
            return false;
        }
        
        $cache_data = [
            'content' => $content,
            'expires' => time() + $this->ttl
        ];
        
        try {
            $result = file_put_contents($file, serialize($cache_data), LOCK_EX);
            return $result !== false;
        } catch (\Exception $e) {
            error_log('FP Performance Suite: Cache write error: ' . $e->getMessage());
            return false;
        }
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
     * SICUREZZA: Verifica che il file sia nella directory cache autorizzata
     */
    private function isValidCacheFile($file): bool
    {
        $realCacheDir = realpath($this->cache_dir);
        $realFile = realpath(dirname($file));
        
        if ($realCacheDir === false || $realFile === false) {
            return false;
        }
        
        return strpos($realFile, $realCacheDir) === 0;
    }
    
    /**
     * SICUREZZA: Unserialize sicuro per prevenire object injection
     */
    private function safeUnserialize($data)
    {
        // Controlliamo che non ci siano oggetti pericolosi
        if (strpos($data, 'O:') !== false) {
            // Se contiene oggetti, rifiutiamo per sicurezza
            error_log('FP Performance Suite: Dangerous object detected in cache data');
            return false;
        }
        
        // Controlliamo che sia un array serializzato
        if (strpos($data, 'a:') !== 0) {
            error_log('FP Performance Suite: Invalid cache data format');
            return false;
        }
        
        try {
            return unserialize($data);
        } catch (\Exception $e) {
            error_log('FP Performance Suite: Unserialize error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica se la page cache è abilitata
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        $settings = get_option('fp_ps_page_cache_settings', []);
        return isset($settings['enabled']) && $settings['enabled'];
    }
    
    /**
     * Restituisce le impostazioni della page cache
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        $settings = get_option('fp_ps_page_cache_settings', []);
        
        return [
            'enabled' => isset($settings['enabled']) && $settings['enabled'],
            'ttl' => $settings['ttl'] ?? $this->ttl,
            'cache_dir' => $this->cache_dir,
            'exclude_urls' => $settings['exclude_urls'] ?? [],
            'exclude_cookies' => $settings['exclude_cookies'] ?? [],
            'exclude_query_strings' => $settings['exclude_query_strings'] ?? [],
        ];
    }
    
    /**
     * Aggiorna le impostazioni della cache
     * 
     * @param array $settings Nuove impostazioni
     * @return bool True se salvato con successo
     */
    public function update(array $settings): bool
    {
        $currentSettings = get_option('fp_ps_page_cache_settings', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['enabled'])) {
            $newSettings['enabled'] = (bool) $newSettings['enabled'];
        }
        
        if (isset($newSettings['ttl'])) {
            $newSettings['ttl'] = max(0, (int) $newSettings['ttl']);
            // Se TTL è 0, disabilita la cache
            if ($newSettings['ttl'] === 0) {
                $newSettings['enabled'] = false;
            }
        }
        
        // Valida array di esclusioni
        if (isset($newSettings['exclude_urls']) && !is_array($newSettings['exclude_urls'])) {
            $newSettings['exclude_urls'] = [];
        }
        if (isset($newSettings['exclude_cookies']) && !is_array($newSettings['exclude_cookies'])) {
            $newSettings['exclude_cookies'] = [];
        }
        if (isset($newSettings['exclude_query_strings']) && !is_array($newSettings['exclude_query_strings'])) {
            $newSettings['exclude_query_strings'] = [];
        }
        
        $result = update_option('fp_ps_page_cache_settings', $newSettings, false);
        
        if ($result && isset($newSettings['ttl'])) {
            $this->ttl = $newSettings['ttl'];
        }
        
        return $result;
    }
    
    /**
     * Restituisce lo stato della cache
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        $enabled = $this->isEnabled();
        
        return [
            'enabled' => $enabled,
            'ttl' => $this->ttl,
            'cache_dir' => $this->cache_dir,
            'dir_exists' => file_exists($this->cache_dir),
            'dir_writable' => is_writable($this->cache_dir),
        ];
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