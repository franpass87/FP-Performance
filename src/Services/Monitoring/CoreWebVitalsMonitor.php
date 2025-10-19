<?php

namespace FP\PerfSuite\Services\Monitoring;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Core Web Vitals Monitor
 *
 * Real User Monitoring (RUM) for Core Web Vitals metrics
 *
 * @package FP\PerfSuite\Services\Monitoring
 * @author Francesco Passeri
 */
class CoreWebVitalsMonitor
{
    private const OPTION = 'fp_ps_cwv_monitor';
    private const METRICS_OPTION = 'fp_ps_cwv_metrics';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // Inject monitoring script
        add_action('wp_footer', [$this, 'injectMonitoringScript'], 999);
        
        // Register REST endpoint for metrics collection
        add_action('rest_api_init', [$this, 'registerRestRoute']);
        
        // Schedule cleanup of old metrics
        if (!wp_next_scheduled('fp_ps_cwv_cleanup')) {
            wp_schedule_event(time(), 'daily', 'fp_ps_cwv_cleanup');
        }
        
        add_action('fp_ps_cwv_cleanup', [$this, 'cleanupOldMetrics']);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,sample_rate:float,track_lcp:bool,track_fid:bool,track_cls:bool,track_fcp:bool,track_ttfb:bool,retention_days:int,send_to_analytics:bool}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'sample_rate' => 1.0, // 100% of users
            'track_lcp' => true,   // Largest Contentful Paint
            'track_fid' => true,   // First Input Delay
            'track_cls' => true,   // Cumulative Layout Shift
            'track_fcp' => true,   // First Contentful Paint
            'track_ttfb' => true,  // Time to First Byte
            'track_inp' => true,   // Interaction to Next Paint
            'retention_days' => 30,
            'send_to_analytics' => false,
            'alert_threshold_lcp' => 2500,  // ms
            'alert_threshold_fid' => 100,   // ms
            'alert_threshold_cls' => 0.1,
            'alert_email' => get_option('admin_email'),
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $sampleRate = isset($settings['sample_rate']) ? (float)$settings['sample_rate'] : $current['sample_rate'];
        $sampleRate = max(0.0, min(1.0, $sampleRate));

        $retentionDays = isset($settings['retention_days']) ? (int)$settings['retention_days'] : $current['retention_days'];
        $retentionDays = max(1, min(365, $retentionDays));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'sample_rate' => $sampleRate,
            'track_lcp' => isset($settings['track_lcp']) ? !empty($settings['track_lcp']) : $current['track_lcp'],
            'track_fid' => isset($settings['track_fid']) ? !empty($settings['track_fid']) : $current['track_fid'],
            'track_cls' => isset($settings['track_cls']) ? !empty($settings['track_cls']) : $current['track_cls'],
            'track_fcp' => isset($settings['track_fcp']) ? !empty($settings['track_fcp']) : $current['track_fcp'],
            'track_ttfb' => isset($settings['track_ttfb']) ? !empty($settings['track_ttfb']) : $current['track_ttfb'],
            'track_inp' => isset($settings['track_inp']) ? !empty($settings['track_inp']) : $current['track_inp'],
            'retention_days' => $retentionDays,
            'send_to_analytics' => isset($settings['send_to_analytics']) ? !empty($settings['send_to_analytics']) : $current['send_to_analytics'],
            'alert_threshold_lcp' => isset($settings['alert_threshold_lcp']) ? (int)$settings['alert_threshold_lcp'] : $current['alert_threshold_lcp'],
            'alert_threshold_fid' => isset($settings['alert_threshold_fid']) ? (int)$settings['alert_threshold_fid'] : $current['alert_threshold_fid'],
            'alert_threshold_cls' => isset($settings['alert_threshold_cls']) ? (float)$settings['alert_threshold_cls'] : $current['alert_threshold_cls'],
            'alert_email' => $settings['alert_email'] ?? $current['alert_email'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Inject monitoring script in frontend
     */
    public function injectMonitoringScript(): void
    {
        $settings = $this->settings();
        $sampleRate = $settings['sample_rate'];
        $endpoint = rest_url('fp-ps/v1/metrics/cwv');
        $nonce = wp_create_nonce('wp_rest');

        ?>
        <script>
        (function() {
            'use strict';
            
            // Sample rate check
            if (Math.random() > <?php echo $sampleRate; ?>) {
                return;
            }
            
            // Metrics collection
            var metrics = {
                url: window.location.href,
                timestamp: Date.now(),
                userAgent: navigator.userAgent,
                connectionType: navigator.connection ? navigator.connection.effectiveType : 'unknown',
            };
            
            // Web Vitals library (inline simplified version)
            function sendMetric(name, value, rating) {
                metrics[name] = {
                    value: value,
                    rating: rating
                };
                
                console.log('[FP CWV]', name, value, rating);
                
                // Send to server
                sendToServer(name, value, rating);
                
                <?php if ($settings['send_to_analytics']): ?>
                // Send to Google Analytics if available
                if (typeof gtag !== 'undefined') {
                    gtag('event', name, {
                        event_category: 'Web Vitals',
                        value: Math.round(value),
                        event_label: rating,
                        non_interaction: true,
                    });
                }
                <?php endif; ?>
            }
            
            function sendToServer(name, value, rating) {
                var data = {
                    metric: name,
                    value: value,
                    rating: rating,
                    url: window.location.href,
                    timestamp: Date.now(),
                };
                
                // Use sendBeacon for reliability
                if (navigator.sendBeacon) {
                    var blob = new Blob([JSON.stringify(data)], { type: 'application/json' });
                    navigator.sendBeacon('<?php echo esc_url($endpoint); ?>', blob);
                } else {
                    // Fallback to fetch
                    fetch('<?php echo esc_url($endpoint); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': '<?php echo $nonce; ?>'
                        },
                        body: JSON.stringify(data),
                        keepalive: true
                    }).catch(function(err) {
                        console.error('[FP CWV] Failed to send metric:', err);
                    });
                }
            }
            
            function getRating(name, value) {
                var thresholds = {
                    'LCP': [2500, 4000],
                    'FID': [100, 300],
                    'CLS': [0.1, 0.25],
                    'FCP': [1800, 3000],
                    'TTFB': [800, 1800],
                    'INP': [200, 500]
                };
                
                var threshold = thresholds[name];
                if (!threshold) return 'unknown';
                
                if (value <= threshold[0]) return 'good';
                if (value <= threshold[1]) return 'needs-improvement';
                return 'poor';
            }
            
            <?php if ($settings['track_lcp']): ?>
            // Largest Contentful Paint
            new PerformanceObserver(function(list) {
                var entries = list.getEntries();
                var lastEntry = entries[entries.length - 1];
                var value = lastEntry.renderTime || lastEntry.loadTime;
                sendMetric('LCP', value, getRating('LCP', value));
            }).observe({ type: 'largest-contentful-paint', buffered: true });
            <?php endif; ?>
            
            <?php if ($settings['track_fid']): ?>
            // First Input Delay
            new PerformanceObserver(function(list) {
                var entries = list.getEntries();
                entries.forEach(function(entry) {
                    if (entry.name === 'first-input') {
                        var value = entry.processingStart - entry.startTime;
                        sendMetric('FID', value, getRating('FID', value));
                    }
                });
            }).observe({ type: 'first-input', buffered: true });
            <?php endif; ?>
            
            <?php if ($settings['track_cls']): ?>
            // Cumulative Layout Shift
            var clsValue = 0;
            new PerformanceObserver(function(list) {
                var entries = list.getEntries();
                entries.forEach(function(entry) {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                    }
                });
            }).observe({ type: 'layout-shift', buffered: true });
            
            // Send CLS on page hide
            addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') {
                    sendMetric('CLS', clsValue, getRating('CLS', clsValue));
                }
            });
            <?php endif; ?>
            
            <?php if ($settings['track_fcp']): ?>
            // First Contentful Paint
            new PerformanceObserver(function(list) {
                var entries = list.getEntries();
                entries.forEach(function(entry) {
                    if (entry.name === 'first-contentful-paint') {
                        sendMetric('FCP', entry.startTime, getRating('FCP', entry.startTime));
                    }
                });
            }).observe({ type: 'paint', buffered: true });
            <?php endif; ?>
            
            <?php if ($settings['track_ttfb']): ?>
            // Time to First Byte
            new PerformanceObserver(function(list) {
                var entries = list.getEntries();
                entries.forEach(function(entry) {
                    if (entry.responseStart > 0) {
                        var value = entry.responseStart;
                        sendMetric('TTFB', value, getRating('TTFB', value));
                    }
                });
            }).observe({ type: 'navigation', buffered: true });
            <?php endif; ?>
            
            <?php if ($settings['track_inp']): ?>
            // Interaction to Next Paint (experimental)
            if ('PerformanceEventTiming' in window) {
                new PerformanceObserver(function(list) {
                    var entries = list.getEntries();
                    var maxDuration = 0;
                    entries.forEach(function(entry) {
                        if (entry.duration > maxDuration) {
                            maxDuration = entry.duration;
                        }
                    });
                    if (maxDuration > 0) {
                        sendMetric('INP', maxDuration, getRating('INP', maxDuration));
                    }
                }).observe({ type: 'event', buffered: true, durationThreshold: 16 });
            }
            <?php endif; ?>
            
        })();
        </script>
        <?php
    }

    /**
     * Register REST API route for metrics collection
     */
    public function registerRestRoute(): void
    {
        register_rest_route('fp-ps/v1', '/metrics/cwv', [
            'methods' => 'POST',
            'callback' => [$this, 'collectMetric'],
            'permission_callback' => '__return_true', // Public endpoint
        ]);
    }

    /**
     * Collect metric from frontend
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response
     */
    public function collectMetric($request): \WP_REST_Response
    {
        $data = $request->get_json_params();

        if (empty($data['metric']) || !isset($data['value'])) {
            return new \WP_REST_Response(['error' => 'Invalid data'], 400);
        }

        $metric = sanitize_text_field($data['metric']);
        $value = (float)$data['value'];
        $rating = sanitize_text_field($data['rating'] ?? 'unknown');
        $url = esc_url_raw($data['url'] ?? '');
        $timestamp = (int)($data['timestamp'] ?? time());

        // Store metric
        $this->storeMetric($metric, $value, $rating, $url, $timestamp);

        // Check for alerts
        $this->checkAlerts($metric, $value, $rating);

        return new \WP_REST_Response(['success' => true], 200);
    }

    /**
     * Store metric in database
     *
     * @param string $metric Metric name
     * @param float $value Metric value
     * @param string $rating Rating (good, needs-improvement, poor)
     * @param string $url Page URL
     * @param int $timestamp Timestamp
     */
    private function storeMetric(string $metric, float $value, string $rating, string $url, int $timestamp): void
    {
        $metrics = get_option(self::METRICS_OPTION, []);

        $metrics[] = [
            'metric' => $metric,
            'value' => $value,
            'rating' => $rating,
            'url' => $url,
            'timestamp' => $timestamp,
        ];

        // Keep only recent metrics (limit array size)
        if (count($metrics) > 10000) {
            $metrics = array_slice($metrics, -10000);
        }

        update_option(self::METRICS_OPTION, $metrics, false);
    }

    /**
     * Check if metric triggers alert
     *
     * @param string $metric Metric name
     * @param float $value Metric value
     * @param string $rating Rating
     */
    private function checkAlerts(string $metric, float $value, string $rating): void
    {
        if ($rating !== 'poor') {
            return;
        }

        $settings = $this->settings();

        // Check thresholds
        $thresholdKey = 'alert_threshold_' . strtolower($metric);
        if (!isset($settings[$thresholdKey])) {
            return;
        }

        $threshold = $settings[$thresholdKey];
        if ($value <= $threshold) {
            return;
        }

        // Send alert (throttled to avoid spam)
        $lastAlert = get_transient('fp_ps_cwv_alert_' . $metric);
        if ($lastAlert) {
            return;
        }

        $this->sendAlert($metric, $value, $rating);

        // Throttle alerts (1 per metric per hour)
        set_transient('fp_ps_cwv_alert_' . $metric, time(), HOUR_IN_SECONDS);
    }

    /**
     * Send alert email
     *
     * @param string $metric Metric name
     * @param float $value Metric value
     * @param string $rating Rating
     */
    private function sendAlert(string $metric, float $value, string $rating): void
    {
        $settings = $this->settings();
        $email = $settings['alert_email'];

        if (empty($email)) {
            return;
        }

        $subject = sprintf('[%s] Core Web Vitals Alert: %s', get_bloginfo('name'), $metric);
        
        $message = sprintf(
            "A Core Web Vitals metric has exceeded the threshold:\n\n" .
            "Metric: %s\n" .
            "Value: %.2f\n" .
            "Rating: %s\n" .
            "Threshold: %.2f\n" .
            "Time: %s\n\n" .
            "Please review your site performance.",
            $metric,
            $value,
            $rating,
            $settings['alert_threshold_' . strtolower($metric)],
            current_time('mysql')
        );

        wp_mail($email, $subject, $message);

        Logger::warning('Core Web Vitals alert sent', [
            'metric' => $metric,
            'value' => $value,
            'rating' => $rating,
        ]);
    }

    /**
     * Cleanup old metrics
     */
    public function cleanupOldMetrics(): void
    {
        $settings = $this->settings();
        $retentionDays = $settings['retention_days'];
        $cutoff = time() - ($retentionDays * DAY_IN_SECONDS);

        $metrics = get_option(self::METRICS_OPTION, []);
        
        $metrics = array_filter($metrics, function($metric) use ($cutoff) {
            return $metric['timestamp'] > $cutoff;
        });

        update_option(self::METRICS_OPTION, array_values($metrics), false);

        Logger::info('Core Web Vitals metrics cleaned up', [
            'retention_days' => $retentionDays,
            'remaining' => count($metrics),
        ]);
    }

    /**
     * Get metrics summary
     *
     * @param int $days Number of days to analyze
     * @return array Summary statistics
     */
    public function getSummary(int $days = 7): array
    {
        $metrics = get_option(self::METRICS_OPTION, []);
        $cutoff = time() - ($days * DAY_IN_SECONDS);

        $filtered = array_filter($metrics, function($metric) use ($cutoff) {
            return $metric['timestamp'] > $cutoff;
        });

        $summary = [];

        foreach (['LCP', 'FID', 'CLS', 'FCP', 'TTFB', 'INP'] as $metricName) {
            $values = array_filter($filtered, function($m) use ($metricName) {
                return $m['metric'] === $metricName;
            });

            if (empty($values)) {
                continue;
            }

            $metricValues = array_column($values, 'value');
            $ratings = array_count_values(array_column($values, 'rating'));

            $summary[$metricName] = [
                'count' => count($values),
                'avg' => array_sum($metricValues) / count($metricValues),
                'median' => $this->median($metricValues),
                'p75' => $this->percentile($metricValues, 75),
                'p90' => $this->percentile($metricValues, 90),
                'good' => $ratings['good'] ?? 0,
                'needs_improvement' => $ratings['needs-improvement'] ?? 0,
                'poor' => $ratings['poor'] ?? 0,
            ];
        }

        return $summary;
    }

    /**
     * Calculate median
     *
     * @param array $values Values
     * @return float Median
     */
    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    /**
     * Calculate percentile
     *
     * @param array $values Values
     * @param int $percentile Percentile (0-100)
     * @return float Percentile value
     */
    private function percentile(array $values, int $percentile): float
    {
        sort($values);
        $index = ($percentile / 100) * (count($values) - 1);
        $lower = floor($index);
        $upper = ceil($index);

        if ($lower === $upper) {
            return $values[$lower];
        }

        $weight = $index - $lower;
        return $values[$lower] * (1 - $weight) + $values[$upper] * $weight;
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,metrics_count:int,sample_rate:float}
     */
    public function status(): array
    {
        $settings = $this->settings();
        $metrics = get_option(self::METRICS_OPTION, []);

        return [
            'enabled' => $settings['enabled'],
            'metrics_count' => count($metrics),
            'sample_rate' => $settings['sample_rate'],
        ];
    }
}
