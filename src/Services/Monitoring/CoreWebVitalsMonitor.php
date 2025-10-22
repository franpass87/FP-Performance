<?php

namespace FP\PerfSuite\Services\Monitoring;

use FP\PerfSuite\Utils\Logger;

/**
 * Core Web Vitals Monitor
 * 
 * Monitora Core Web Vitals (LCP, FID, CLS)
 *
 * @package FP\PerfSuite\Services\Monitoring
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CoreWebVitalsMonitor
{
    private const OPTION_KEY = 'fp_ps_web_vitals';
    private const MAX_SAMPLES = 1000;

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $settings = $this->getSettings();

        if (empty($settings['enabled'])) {
            return;
        }

        // Inietta script monitoring
        add_action('wp_footer', [$this, 'injectMonitoringScript'], 999);

        // Endpoint per raccogliere metriche
        add_action('rest_api_init', [$this, 'registerRestRoute']);

        Logger::debug('Core Web Vitals Monitor registered');
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'sample_rate' => 100, // Percentuale di utenti da monitorare (0-100)
            'track_lcp' => true,
            'track_fid' => true,
            'track_cls' => true,
            'track_fcp' => true,
            'track_ttfb' => true,
            'track_inp' => false, // Sperimentale
            'send_to_analytics' => false,
            'retention_days' => 30,
            'alert_email' => get_option('admin_email'),
            'alert_threshold_lcp' => 4000, // ms
            'alert_threshold_fid' => 300,  // ms
            'alert_threshold_cls' => 0.25, // score
        ];

        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Alias di getSettings() per compatibilità
     */
    public function settings(): array
    {
        return $this->getSettings();
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);

        $result = update_option(self::OPTION_KEY, $updated);
        
        if ($result) {
            Logger::info('Core Web Vitals Monitor settings updated', $updated);
        }

        return $result;
    }

    /**
     * Alias di updateSettings() per compatibilità
     */
    public function update(array $settings): bool
    {
        return $this->updateSettings($settings);
    }

    /**
     * Inietta script di monitoring
     */
    public function injectMonitoringScript(): void
    {
        if (is_admin()) {
            return;
        }

        $settings = $this->getSettings();
        $sampleRate = $settings['sample_rate'];
        $apiUrl = rest_url('fp-performance/v1/web-vitals');
        ?>
        <script>
        (function() {
            // Sample rate check
            if (Math.random() * 100 > <?php echo esc_js($sampleRate); ?>) {
                return;
            }

            // Web Vitals library inline (semplificata)
            const vitalsQueue = [];
            const trackMetric = (metric) => {
                vitalsQueue.push(metric);
                
                // Send to server
                if (navigator.sendBeacon) {
                    const data = JSON.stringify(metric);
                    navigator.sendBeacon('<?php echo esc_js($apiUrl); ?>', data);
                }
            };

            // Largest Contentful Paint (LCP)
            <?php if ($settings['track_lcp']): ?>
            if ('PerformanceObserver' in window) {
                try {
                    const lcpObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        const lastEntry = entries[entries.length - 1];
                        
                        trackMetric({
                            name: 'LCP',
                            value: lastEntry.renderTime || lastEntry.loadTime,
                            rating: lastEntry.renderTime < 2500 ? 'good' : (lastEntry.renderTime < 4000 ? 'needs-improvement' : 'poor'),
                            url: location.pathname,
                            timestamp: Date.now()
                        });
                    });
                    
                    lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
                } catch (e) {
                    console.error('LCP observer error:', e);
                }
            }
            <?php endif; ?>

            // First Input Delay (FID)
            <?php if ($settings['track_fid']): ?>
            if ('PerformanceObserver' in window) {
                try {
                    const fidObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        entries.forEach((entry) => {
                            const fid = entry.processingStart - entry.startTime;
                            
                            trackMetric({
                                name: 'FID',
                                value: fid,
                                rating: fid < 100 ? 'good' : (fid < 300 ? 'needs-improvement' : 'poor'),
                                url: location.pathname,
                                timestamp: Date.now()
                            });
                        });
                    });
                    
                    fidObserver.observe({ entryTypes: ['first-input'] });
                } catch (e) {
                    console.error('FID observer error:', e);
                }
            }
            <?php endif; ?>

            // Cumulative Layout Shift (CLS)
            <?php if ($settings['track_cls']): ?>
            if ('PerformanceObserver' in window) {
                try {
                    let clsValue = 0;
                    const clsObserver = new PerformanceObserver((list) => {
                        for (const entry of list.getEntries()) {
                            if (!entry.hadRecentInput) {
                                clsValue += entry.value;
                            }
                        }
                    });
                    
                    clsObserver.observe({ entryTypes: ['layout-shift'] });
                    
                    // Send CLS on page unload
                    window.addEventListener('visibilitychange', () => {
                        if (document.visibilityState === 'hidden') {
                            trackMetric({
                                name: 'CLS',
                                value: clsValue,
                                rating: clsValue < 0.1 ? 'good' : (clsValue < 0.25 ? 'needs-improvement' : 'poor'),
                                url: location.pathname,
                                timestamp: Date.now()
                            });
                        }
                    });
                } catch (e) {
                    console.error('CLS observer error:', e);
                }
            }
            <?php endif; ?>

            // First Contentful Paint (FCP)
            <?php if ($settings['track_fcp']): ?>
            if ('PerformanceObserver' in window) {
                try {
                    const fcpObserver = new PerformanceObserver((list) => {
                        const entries = list.getEntries();
                        entries.forEach((entry) => {
                            trackMetric({
                                name: 'FCP',
                                value: entry.startTime,
                                rating: entry.startTime < 1800 ? 'good' : (entry.startTime < 3000 ? 'needs-improvement' : 'poor'),
                                url: location.pathname,
                                timestamp: Date.now()
                            });
                        });
                    });
                    
                    fcpObserver.observe({ entryTypes: ['paint'] });
                } catch (e) {
                    console.error('FCP observer error:', e);
                }
            }
            <?php endif; ?>

            // Time to First Byte (TTFB)
            <?php if ($settings['track_ttfb']): ?>
            window.addEventListener('load', () => {
                if (window.performance && window.performance.timing) {
                    const ttfb = window.performance.timing.responseStart - window.performance.timing.requestStart;
                    
                    trackMetric({
                        name: 'TTFB',
                        value: ttfb,
                        rating: ttfb < 800 ? 'good' : (ttfb < 1800 ? 'needs-improvement' : 'poor'),
                        url: location.pathname,
                        timestamp: Date.now()
                    });
                }
            });
            <?php endif; ?>
        })();
        </script>
        <?php
    }

    /**
     * Registra endpoint REST API
     */
    public function registerRestRoute(): void
    {
        register_rest_route('fp-performance/v1', '/web-vitals', [
            'methods' => 'POST',
            'callback' => [$this, 'receiveMetric'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Riceve metrica dal client
     */
    public function receiveMetric(\WP_REST_Request $request): array
    {
        $metric = $request->get_json_params();

        if (empty($metric['name']) || !isset($metric['value'])) {
            return ['success' => false, 'error' => 'Invalid metric data'];
        }

        $this->storeMetric($metric);

        return ['success' => true];
    }

    /**
     * Salva metrica
     */
    private function storeMetric(array $metric): void
    {
        $data = get_option(self::OPTION_KEY . '_data', []);

        if (!is_array($data)) {
            $data = [];
        }

        $data[] = [
            'name' => sanitize_text_field($metric['name']),
            'value' => floatval($metric['value']),
            'rating' => sanitize_text_field($metric['rating'] ?? ''),
            'url' => sanitize_text_field($metric['url'] ?? ''),
            'timestamp' => time(),
        ];

        // Limita dimensione
        if (count($data) > self::MAX_SAMPLES) {
            $data = array_slice($data, -self::MAX_SAMPLES);
        }

        update_option(self::OPTION_KEY . '_data', $data, false);
    }

    /**
     * Ottiene metriche
     */
    public function getMetrics(int $days = 7): array
    {
        $data = get_option(self::OPTION_KEY . '_data', []);

        if (empty($data)) {
            return [];
        }

        $cutoff = time() - ($days * DAY_IN_SECONDS);
        
        return array_filter($data, function ($metric) use ($cutoff) {
            return $metric['timestamp'] >= $cutoff;
        });
    }

    /**
     * Ottiene statistiche aggregate
     */
    public function getStats(int $days = 7): array
    {
        $metrics = $this->getMetrics($days);

        if (empty($metrics)) {
            return [
                'LCP' => ['p75' => 0, 'avg' => 0, 'count' => 0],
                'FID' => ['p75' => 0, 'avg' => 0, 'count' => 0],
                'CLS' => ['p75' => 0, 'avg' => 0, 'count' => 0],
                'FCP' => ['p75' => 0, 'avg' => 0, 'count' => 0],
                'TTFB' => ['p75' => 0, 'avg' => 0, 'count' => 0],
            ];
        }

        $byMetric = [];
        foreach ($metrics as $metric) {
            $name = $metric['name'];
            if (!isset($byMetric[$name])) {
                $byMetric[$name] = [];
            }
            $byMetric[$name][] = $metric['value'];
        }

        $stats = [];
        foreach ($byMetric as $name => $values) {
            sort($values);
            $count = count($values);
            $p75Index = (int) ceil($count * 0.75) - 1;

            $stats[$name] = [
                'p75' => $values[$p75Index] ?? 0,
                'avg' => array_sum($values) / $count,
                'min' => min($values),
                'max' => max($values),
                'count' => $count,
            ];
        }

        return $stats;
    }

    /**
     * Ottiene rating generale
     */
    public function getOverallRating(int $days = 7): array
    {
        $stats = $this->getStats($days);

        $ratings = [];

        // LCP
        if (!empty($stats['LCP'])) {
            $lcp = $stats['LCP']['p75'];
            $ratings['LCP'] = $lcp < 2500 ? 'good' : ($lcp < 4000 ? 'needs-improvement' : 'poor');
        }

        // FID
        if (!empty($stats['FID'])) {
            $fid = $stats['FID']['p75'];
            $ratings['FID'] = $fid < 100 ? 'good' : ($fid < 300 ? 'needs-improvement' : 'poor');
        }

        // CLS
        if (!empty($stats['CLS'])) {
            $cls = $stats['CLS']['p75'];
            $ratings['CLS'] = $cls < 0.1 ? 'good' : ($cls < 0.25 ? 'needs-improvement' : 'poor');
        }

        $goodCount = count(array_filter($ratings, fn($r) => $r === 'good'));
        $totalCount = count($ratings);

        return [
            'ratings' => $ratings,
            'score' => $totalCount > 0 ? round(($goodCount / $totalCount) * 100) : 0,
        ];
    }

    /**
     * Pulisce metriche
     */
    public function clearMetrics(): bool
    {
        return delete_option(self::OPTION_KEY . '_data');
    }

    /**
     * Status
     */
    public function status(): array
    {
        $stats = $this->getStats(7);
        $rating = $this->getOverallRating(7);
        $metrics = $this->getMetrics(7);

        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'settings' => $this->getSettings(),
            'stats' => $stats,
            'rating' => $rating,
            'metrics_count' => count($metrics),
        ];
    }

    /**
     * Ottiene un riepilogo delle metriche per il periodo specificato
     */
    public function getSummary(int $days = 7): array
    {
        $stats = $this->getStats($days);
        
        if (empty($stats)) {
            return [];
        }

        // Ritorna solo le metriche principali con formattazione user-friendly
        $summary = [];
        
        foreach (['LCP', 'FID', 'CLS', 'FCP', 'TTFB'] as $metricName) {
            if (isset($stats[$metricName]) && $stats[$metricName]['count'] > 0) {
                $summary[$metricName] = $stats[$metricName];
            }
        }

        return $summary;
    }
}

