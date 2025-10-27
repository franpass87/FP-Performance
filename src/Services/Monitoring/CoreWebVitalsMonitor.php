<?php

namespace FP\PerfSuite\Services\Monitoring;

class CoreWebVitalsMonitor
{
    private $lcp;
    private $fid;
    private $cls;
    
    public function __construct($lcp = true, $fid = true, $cls = true)
    {
        $this->lcp = $lcp;
        $this->fid = $fid;
        $this->cls = $cls;
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
        if (!wp_verify_nonce($_POST['nonce'], 'fp_save_vitals')) {
            wp_die('Security check failed');
        }
        
        $vitals = json_decode(stripslashes($_POST['vitals']), true);
        
        if ($vitals) {
            update_option('fp_core_web_vitals', $vitals);
        }
        
        wp_die();
    }
    
    public function getCoreWebVitals()
    {
        return get_option('fp_core_web_vitals', []);
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