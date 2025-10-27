<?php

namespace FP\PerfSuite\Services\Cache;

class Headers
{
    private $ttl;
    
    public function __construct($ttl = 3600)
    {
        $this->ttl = $ttl;
    }
    
    public function setCacheHeaders($ttl = null)
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        $ttl = $ttl ?: $this->ttl;
        
        if (!headers_sent()) {
            header('Cache-Control: public, max-age=' . $ttl);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $ttl) . ' GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
        }
    }
    
    public function setNoCacheHeaders()
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        if (!headers_sent()) {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
    }
    
    public function setETag($content)
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        if (!headers_sent()) {
            $etag = md5($content);
            header('ETag: "' . $etag . '"');
        }
    }
    
    public function checkETag($etag)
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === '"' . $etag . '"') {
            http_response_code(304);
            exit;
        }
    }
    
    /**
     * Restituisce le impostazioni degli headers di cache
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        $settings = get_option('fp_ps_cache_headers_settings', []);
        
        return [
            'enabled' => isset($settings['enabled']) && $settings['enabled'],
            'ttl' => $settings['ttl'] ?? $this->ttl,
            'expires_ttl' => $settings['expires_ttl'] ?? 31536000, // 1 anno di default
            'headers' => $settings['headers'] ?? [
                'Cache-Control' => 'public, max-age=31536000',
            ],
            'htaccess' => $settings['htaccess'] ?? '',
        ];
    }
    
    /**
     * Restituisce lo stato degli headers di cache
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        $settings = get_option('fp_ps_cache_headers_settings', []);
        $enabled = isset($settings['enabled']) && $settings['enabled'];
        
        return [
            'enabled' => $enabled,
            'ttl' => $this->ttl,
        ];
    }
    
    /**
     * Aggiorna le impostazioni degli headers di cache
     * 
     * @param array $settings Array con le nuove impostazioni
     * @return bool True se salvato con successo, false altrimenti
     */
    public function update(array $settings): bool
    {
        $currentSettings = get_option('fp_ps_cache_headers_settings', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['enabled'])) {
            $newSettings['enabled'] = (bool) $newSettings['enabled'];
        }
        
        if (isset($newSettings['expires_ttl'])) {
            $newSettings['expires_ttl'] = max(0, (int) $newSettings['expires_ttl']);
        }
        
        // Valida headers se presenti
        if (isset($newSettings['headers']) && !is_array($newSettings['headers'])) {
            $newSettings['headers'] = [];
        }
        
        // Sanitizza htaccess rules se presenti
        if (isset($newSettings['htaccess'])) {
            $newSettings['htaccess'] = wp_kses_post(wp_unslash($newSettings['htaccess']));
        }
        
        $result = update_option('fp_ps_cache_headers_settings', $newSettings, false);
        
        // Aggiorna TTL interno se presente
        if (isset($newSettings['expires_ttl'])) {
            $this->ttl = $newSettings['expires_ttl'];
        }
        
        return $result;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Headers non ha hook specifici da registrare
        // È utilizzato principalmente per gestire gli header HTTP
    }
}