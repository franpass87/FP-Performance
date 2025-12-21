<?php

namespace FP\PerfSuite\Services\Monitoring;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

use function wp_verify_nonce;
use function wp_unslash;

class CoreWebVitalsMonitor
{
    private const VITALS_KEY = 'fp_core_web_vitals';
    private const SETTINGS_KEY = 'fp_ps_cwv_settings';
    
    private $lcp;
    private $fid;
    private $cls;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct($lcp = true, $fid = true, $cls = true, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->lcp = $lcp;
        $this->fid = $fid;
        $this->cls = $cls;
        $this->optionsRepo = $optionsRepo;
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
    
    public function init()
    {
        // Solo nel frontend
        if (!is_admin()) {
            add_action('wp_footer', [$this, 'addCoreWebVitalsScript'], 48);
        }
        add_action('wp_ajax_fp_save_vitals', [$this, 'saveVitals']);
        add_action('wp_ajax_nopriv_fp_save_vitals', [$this, 'saveVitals']);
    }
    
    public function addCoreWebVitalsScript()
    {
        echo '<script>
            // Core Web Vitals Monitor
            if ("performance" in window) {
                const vitals = {};
                
                // LCP (Largest Contentful Paint)
                if (this.lcp && "PerformanceObserver" in window) {
                    const lcpObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        const lastEntry = entries[entries.length - 1];
                        vitals.lcp = lastEntry.startTime;
                    });
                    lcpObserver.observe({ entryTypes: ["largest-contentful-paint"] });
                }
                
                // FID (First Input Delay)
                if (this.fid && "PerformanceObserver" in window) {
                    const fidObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        entries.forEach(entry => {
                            vitals.fid = entry.processingStart - entry.startTime;
                        });
                    });
                    fidObserver.observe({ entryTypes: ["first-input"] });
                }
                
                // CLS (Cumulative Layout Shift)
                if (this.cls && "PerformanceObserver" in window) {
                    let clsValue = 0;
                    const clsObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        entries.forEach(entry => {
                            if (!entry.hadRecentInput) {
                                clsValue += entry.value;
                            }
                        });
                        vitals.cls = clsValue;
                    });
                    clsObserver.observe({ entryTypes: ["layout-shift"] });
                }
                
                // Report vitals
                setTimeout(() => {
                    if (Object.keys(vitals).length > 0) {
                        fetch("' . admin_url('admin-ajax.php') . '", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded",
                            },
                            body: "action=fp_save_vitals&vitals=" + encodeURIComponent(JSON.stringify(vitals))
                        });
                    }
                }, 5000);
            }
        </script>';
    }
    
    public function saveVitals()
    {
        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce(wp_unslash($_POST['nonce']), 'fp_save_vitals')) {
            wp_die('Security check failed', 'Unauthorized', ['response' => 403]);
        }
        
        // Verifica e sanitizza input
        if (!isset($_POST['vitals']) || !is_string($_POST['vitals'])) {
            wp_die('Invalid data', 'Bad Request', ['response' => 400]);
        }
        
        $vitalsRaw = wp_unslash($_POST['vitals']); // Usa wp_unslash invece di stripslashes
        $vitals = json_decode($vitalsRaw, true);
        
        // Verifica JSON valido
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($vitals)) {
            wp_die('Invalid JSON data', 'Bad Request', ['response' => 400]);
        }
        
        // Sanitizza i valori numerici dei vitals
        $sanitizedVitals = [];
        $allowedKeys = ['lcp', 'fid', 'cls', 'fcp', 'ttfb', 'inp'];
        
        foreach ($allowedKeys as $key) {
            if (isset($vitals[$key])) {
                $sanitizedVitals[$key] = is_numeric($vitals[$key]) ? (float)$vitals[$key] : 0;
            }
        }
        
        if (!empty($sanitizedVitals)) {
            $sanitizedVitals['timestamp'] = time();
            $this->setOption(self::VITALS_KEY, $sanitizedVitals);
        }
        
        wp_die();
    }
    
    public function getCoreWebVitals()
    {
        return $this->getOption(self::VITALS_KEY, []);
    }
    
    public function getCoreWebVitalsMetrics()
    {
        $vitals = $this->getCoreWebVitals();
        
        return [
            'lcp' => $this->lcp,
            'fid' => $this->fid,
            'cls' => $this->cls,
            'current_vitals' => $vitals
        ];
    }
    
    /**
     * Restituisce le impostazioni del Core Web Vitals monitor
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        return [
            'lcp_enabled' => $this->lcp,
            'fid_enabled' => $this->fid,
            'cls_enabled' => $this->cls,
        ];
    }
    
    /**
     * Aggiorna le impostazioni del Core Web Vitals Monitor
     * 
     * @param array $settings Array con le impostazioni da aggiornare
     * @return bool True se salvato con successo
     */
    public function update(array $settings): bool
    {
        $currentSettings = $this->getOption(self::SETTINGS_KEY, []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['lcp_enabled'])) {
            $newSettings['lcp_enabled'] = (bool) $newSettings['lcp_enabled'];
        }
        
        if (isset($newSettings['fid_enabled'])) {
            $newSettings['fid_enabled'] = (bool) $newSettings['fid_enabled'];
        }
        
        if (isset($newSettings['cls_enabled'])) {
            $newSettings['cls_enabled'] = (bool) $newSettings['cls_enabled'];
        }
        
        $result = $this->setOption(self::SETTINGS_KEY, $newSettings);
        
        // Aggiorna proprietà interne
        if (isset($newSettings['lcp_enabled'])) {
            $this->lcp = $newSettings['lcp_enabled'];
        }
        if (isset($newSettings['fid_enabled'])) {
            $this->fid = $newSettings['fid_enabled'];
        }
        if (isset($newSettings['cls_enabled'])) {
            $this->cls = $newSettings['cls_enabled'];
        }
        
        if ($result) {
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
        remove_action('wp_footer', [$this, 'addCoreWebVitalsScript'], 48);
        remove_action('wp_ajax_fp_save_vitals', [$this, 'saveVitals']);
        remove_action('wp_ajax_nopriv_fp_save_vitals', [$this, 'saveVitals']);
        
        // Reinizializza
        $this->init();
    }
    
    /**
     * Restituisce lo stato del Core Web Vitals monitor
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        $vitals = $this->getCoreWebVitals();
        
        return [
            'enabled' => $this->lcp || $this->fid || $this->cls,
            'lcp_enabled' => $this->lcp,
            'fid_enabled' => $this->fid,
            'cls_enabled' => $this->cls,
            'has_data' => !empty($vitals),
            'vitals' => $vitals,
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}