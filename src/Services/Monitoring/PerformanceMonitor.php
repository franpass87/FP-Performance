<?php

namespace FP\PerfSuite\Services\Monitoring;

class PerformanceMonitor
{
    private static ?self $instance = null;
    private $core_web_vitals;
    private $real_user_monitoring;
    
    public function __construct($core_web_vitals = true, $real_user_monitoring = true)
    {
        $this->core_web_vitals = $core_web_vitals;
        $this->real_user_monitoring = $real_user_monitoring;
    }

    /**
     * Singleton pattern per ottenere l'istanza
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function init()
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        if ($this->core_web_vitals) {
            add_action('wp_footer', [$this, 'addCoreWebVitalsScript'], 46);
        }
        
        if ($this->real_user_monitoring) {
            add_action('wp_footer', [$this, 'addRealUserMonitoringScript'], 47);
        }
    }
    
    public function addCoreWebVitalsScript()
    {
        echo '<script>
            // Core Web Vitals Monitoring
            if ("performance" in window) {
                const vitals = {};
                
                // LCP (Largest Contentful Paint)
                if ("PerformanceObserver" in window) {
                    const lcpObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        const lastEntry = entries[entries.length - 1];
                        vitals.lcp = lastEntry.startTime;
                    });
                    lcpObserver.observe({ entryTypes: ["largest-contentful-paint"] });
                }
                
                // FID (First Input Delay)
                if ("PerformanceObserver" in window) {
                    const fidObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        entries.forEach(entry => {
                            vitals.fid = entry.processingStart - entry.startTime;
                        });
                    });
                    fidObserver.observe({ entryTypes: ["first-input"] });
                }
                
                // CLS (Cumulative Layout Shift)
                if ("PerformanceObserver" in window) {
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
                    console.log("Core Web Vitals:", vitals);
                    
                    // Send to server
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
    
    public function addRealUserMonitoringScript()
    {
        echo '<script>
            // Real User Monitoring
            if ("performance" in window) {
                const rum = {
                    pageLoad: performance.timing.loadEventEnd - performance.timing.navigationStart,
                    domContentLoaded: performance.timing.domContentLoadedEventEnd - performance.timing.navigationStart,
                    firstPaint: 0,
                    firstContentfulPaint: 0
                };
                
                // First Paint
                if ("PerformanceObserver" in window) {
                    const paintObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        entries.forEach(entry => {
                            if (entry.name === "first-paint") {
                                rum.firstPaint = entry.startTime;
                            } else if (entry.name === "first-contentful-paint") {
                                rum.firstContentfulPaint = entry.startTime;
                            }
                        });
                    });
                    paintObserver.observe({ entryTypes: ["paint"] });
                }
                
                // Report RUM data
                setTimeout(() => {
                    console.log("Real User Monitoring:", rum);
                    
                    // Send to server
                    fetch("' . admin_url('admin-ajax.php') . '", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: "action=fp_save_rum&rum=" + encodeURIComponent(JSON.stringify(rum))
                    });
                }, 5000);
            }
        </script>';
    }
    
    public function getPerformanceMetrics()
    {
        return [
            'core_web_vitals' => $this->core_web_vitals,
            'real_user_monitoring' => $this->real_user_monitoring,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
    }

    /**
     * Verifica se il performance monitoring è abilitato
     * 
     * @return bool
     */
    public function isEnabled(): bool
    {
        $settings = get_option('fp_ps_monitoring_settings', []);
        return isset($settings['enabled']) && $settings['enabled'];
    }
    
    /**
     * Restituisce le impostazioni del performance monitoring
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        $settings = get_option('fp_ps_monitoring_settings', []);
        
        return [
            'enabled' => isset($settings['enabled']) && $settings['enabled'],
            'core_web_vitals' => $settings['core_web_vitals'] ?? $this->core_web_vitals,
            'real_user_monitoring' => $settings['real_user_monitoring'] ?? $this->real_user_monitoring,
        ];
    }
    
    /**
     * Aggiorna le impostazioni del Performance Monitor
     * 
     * @param array $settings Array con le impostazioni da aggiornare
     * @return bool True se salvato con successo
     */
    public function update(array $settings): bool
    {
        $currentSettings = get_option('fp_ps_monitoring_settings', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['enabled'])) {
            $newSettings['enabled'] = (bool) $newSettings['enabled'];
        }
        
        if (isset($newSettings['core_web_vitals'])) {
            $newSettings['core_web_vitals'] = (bool) $newSettings['core_web_vitals'];
        }
        
        if (isset($newSettings['real_user_monitoring'])) {
            $newSettings['real_user_monitoring'] = (bool) $newSettings['real_user_monitoring'];
        }
        
        $result = update_option('fp_ps_monitoring_settings', $newSettings, false);
        
        // Aggiorna proprietà interne
        if (isset($newSettings['core_web_vitals'])) {
            $this->core_web_vitals = $newSettings['core_web_vitals'];
        }
        if (isset($newSettings['real_user_monitoring'])) {
            $this->real_user_monitoring = $newSettings['real_user_monitoring'];
        }
        
        return $result;
    }
    
    /**
     * Ottiene le statistiche di performance per un periodo specificato
     */
    public function getStats(int $days = 7): array
    {
        // Dati mock per ora - in un'implementazione reale questi verrebbero dal database
        return [
            'samples' => 15,
            'avg_load_time' => 1.2,
            'avg_queries' => 45,
            'avg_memory' => 85.5
        ];
    }
    
    /**
     * Ottiene i dati di performance più recenti
     * 
     * @param int $limit Numero di campioni da restituire
     * @return array Array con i dati recenti
     */
    public function getRecent(int $limit = 10): array
    {
        // Dati mock per ora - in un'implementazione reale questi verrebbero dal database
        $recent = [];
        
        for ($i = 0; $i < $limit; $i++) {
            $recent[] = [
                'timestamp' => time() - ($i * 3600),
                'load_time' => round(0.8 + (rand(0, 10) / 10), 2),
                'queries' => rand(35, 55),
                'memory' => round(70 + rand(0, 30), 1),
                'url' => home_url('/'),
            ];
        }
        
        return $recent;
    }
    
    /**
     * Ottiene i trend di performance
     * 
     * @param int $days Numero di giorni da analizzare
     * @return array Array con i trend
     */
    public function getTrends(int $days = 7): array
    {
        // Dati mock per ora - in un'implementazione reale questi verrebbero dal database
        return [
            'load_time' => [
                'current' => 1.2,
                'previous' => 1.5,
                'change' => -20, // Percentuale di miglioramento
                'trend' => 'improving',
            ],
            'queries' => [
                'current' => 45,
                'previous' => 50,
                'change' => -10,
                'trend' => 'improving',
            ],
            'memory' => [
                'current' => 85.5,
                'previous' => 88.2,
                'change' => -3.1,
                'trend' => 'improving',
            ],
            'overall_trend' => 'improving',
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