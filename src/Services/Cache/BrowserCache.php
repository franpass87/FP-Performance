<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

/**
 * Browser Cache Service
 * 
 * Gestisce gli header di cache per il browser
 * 
 * @package FP\PerfSuite\Services\Cache
 * @author Francesco Passeri
 */
class BrowserCache
{
    private const OPTION_KEY = 'fp_ps_browser_cache';
    
    private bool $enabled = false;
    private int $static_assets_ttl = 31536000; // 1 anno
    private int $html_ttl = 3600; // 1 ora
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $settings = $this->getSettings();
        $this->enabled = $settings['enabled'] ?? false;
        $this->static_assets_ttl = $settings['static_assets_ttl'] ?? 31536000;
        $this->html_ttl = $settings['html_ttl'] ?? 3600;
    }
    
    /**
     * Helper method per ottenere opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = [])
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option($key, $default);
    }
    
    /**
     * Helper method per salvare opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $value Value to save
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set($key, $value);
            return true;
        }
        
        // Fallback to direct option call for backward compatibility
        return update_option($key, $value, false);
    }
    
    /**
     * Inizializza il servizio
     */
    public function init(): void
    {
        if (!$this->enabled) {
            return;
        }
        
        // Aggiungi headers di cache
        add_action('send_headers', [$this, 'addCacheHeaders']);
    }
    
    /**
     * Aggiunge gli header di cache
     */
    public function addCacheHeaders(): void
    {
        if (!$this->enabled || is_admin()) {
            return;
        }
        
        // Determina il tipo di contenuto
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Asset statici
        if (preg_match('/\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf|eot)$/i', $request_uri)) {
            header('Cache-Control: public, max-age=' . $this->static_assets_ttl);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->static_assets_ttl) . ' GMT');
        }
        // HTML
        elseif (preg_match('/\.(html|htm)$/i', $request_uri) || strpos($request_uri, '.') === false) {
            header('Cache-Control: public, max-age=' . $this->html_ttl);
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->html_ttl) . ' GMT');
        }
    }
    
    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $saved = $this->getOption(self::OPTION_KEY, []);
        
        return [
            'enabled' => $saved['enabled'] ?? $this->enabled,
            'static_assets_ttl' => $saved['static_assets_ttl'] ?? $this->static_assets_ttl,
            'html_ttl' => $saved['html_ttl'] ?? $this->html_ttl,
        ];
    }
    
    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getOption(self::OPTION_KEY, []);
        $new = array_merge($current, $settings);
        
        // Validazione
        $new['enabled'] = (bool) ($new['enabled'] ?? false);
        $new['static_assets_ttl'] = max(0, (int) ($new['static_assets_ttl'] ?? 31536000));
        $new['html_ttl'] = max(0, (int) ($new['html_ttl'] ?? 3600));
        
        $result = $this->setOption(self::OPTION_KEY, $new);
        
        if ($result) {
            $this->enabled = $new['enabled'];
            $this->static_assets_ttl = $new['static_assets_ttl'];
            $this->html_ttl = $new['html_ttl'];
            
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
        remove_action('send_headers', [$this, 'addCacheHeaders']);
        
        // Ricarica le impostazioni dal database
        $settings = $this->getSettings();
        $this->enabled = $settings['enabled'] ?? false;
        $this->static_assets_ttl = $settings['static_assets_ttl'] ?? 31536000;
        $this->html_ttl = $settings['html_ttl'] ?? 3600;
        
        // Reinizializza
        $this->init();
    }
    
    /**
     * Restituisce lo stato del servizio
     */
    public function settings(): array
    {
        return $this->getSettings();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}

