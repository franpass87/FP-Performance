<?php

namespace FP\PerfSuite\Services\Monitoring;

use FP\PerfSuite\Utils\Logger;

/**
 * Performance Monitoring Service
 *
 * Tracks and stores performance metrics over time
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PerformanceMonitor
{
    private const OPTION = 'fp_ps_perf_monitor';
    private const MAX_ENTRIES = 1000;

    private static ?self $instance = null;
    private array $currentPageMetrics = [];
    private float $pageLoadStart;

    private function __construct()
    {
        $this->pageLoadStart = defined('WP_START_TIMESTAMP') ? WP_START_TIMESTAMP : microtime(true);
    }

    /**
     * Get singleton instance
     */
    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Register monitoring hooks
     */
    public function register(): void
    {
        if ($this->isEnabled()) {
            add_action('shutdown', [$this, 'recordPageLoad'], PHP_INT_MAX);
            add_action('wp_footer', [$this, 'injectTimingScript'], PHP_INT_MAX);
        }
    }

    /**
     * Check if monitoring is enabled
     */
    public function isEnabled(): bool
    {
        $settings = $this->settings();
        return !empty($settings['enabled']) && !is_admin();
    }

    /**
     * Get monitoring settings
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'sample_rate' => 10, // Percentage of requests to monitor
            'track_queries' => true,
            'track_memory' => true,
            'track_timing' => true,
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update monitoring settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $new = [
            'enabled' => !empty($settings['enabled']),
            'sample_rate' => max(1, min(100, (int)($settings['sample_rate'] ?? $current['sample_rate']))),
            'track_queries' => !empty($settings['track_queries']),
            'track_memory' => !empty($settings['track_memory']),
            'track_timing' => !empty($settings['track_timing']),
        ];

        update_option(self::OPTION, $new);
        Logger::info('Performance monitoring settings updated', $new);
    }

    /**
     * Record page load metrics
     */
    public function recordPageLoad(): void
    {
        $settings = $this->settings();

        // Sample based on sample_rate
        if (rand(1, 100) > $settings['sample_rate']) {
            return;
        }

        global $wpdb;

        $metrics = [
            'url' => $_SERVER['REQUEST_URI'] ?? '/',
            'timestamp' => time(),
            'load_time' => microtime(true) - $this->pageLoadStart,
        ];

        if ($settings['track_queries']) {
            $metrics['db_queries'] = $wpdb->num_queries;
            $metrics['db_time'] = $wpdb->timer_stop();
        }

        if ($settings['track_memory']) {
            $metrics['memory_usage'] = memory_get_usage(true);
            $metrics['memory_peak'] = memory_get_peak_usage(true);
        }

        // Add custom metrics tracked during request
        $metrics = array_merge($metrics, $this->currentPageMetrics);

        $this->storeMetric($metrics);

        Logger::debug('Page load recorded', [
            'load_time' => round($metrics['load_time'] * 1000, 2) . 'ms',
            'queries' => $metrics['db_queries'] ?? 0,
        ]);
    }

    /**
     * Store metric in database
     */
    private function storeMetric(array $metrics): void
    {
        $stored = get_option(self::OPTION . '_data', []);

        if (!is_array($stored)) {
            $stored = [];
        }

        // Add new metric
        $stored[] = $metrics;

        // Keep only last MAX_ENTRIES
        if (count($stored) > self::MAX_ENTRIES) {
            $stored = array_slice($stored, -self::MAX_ENTRIES);
        }

        update_option(self::OPTION . '_data', $stored, false);
    }

    /**
     * Track custom metric for current page
     */
    public function track(string $key, $value): void
    {
        $this->currentPageMetrics[$key] = $value;
    }

    /**
     * Start timing an operation
     */
    public function startTimer(string $name): void
    {
        $this->currentPageMetrics["timer_{$name}_start"] = microtime(true);
    }

    /**
     * Stop timing an operation
     */
    public function stopTimer(string $name): float
    {
        $startKey = "timer_{$name}_start";

        if (!isset($this->currentPageMetrics[$startKey])) {
            return 0.0;
        }

        $duration = microtime(true) - $this->currentPageMetrics[$startKey];
        $this->currentPageMetrics["timer_{$name}"] = $duration;
        unset($this->currentPageMetrics[$startKey]);

        return $duration;
    }

    /**
     * Get performance statistics
     */
    public function getStats(int $days = 7): array
    {
        $data = get_option(self::OPTION . '_data', []);

        if (empty($data)) {
            return [
                'avg_load_time' => 0,
                'avg_queries' => 0,
                'avg_memory' => 0,
                'samples' => 0,
            ];
        }

        // Filter by date range
        $cutoff = time() - ($days * DAY_IN_SECONDS);
        $filtered = array_filter($data, function ($metric) use ($cutoff) {
            return isset($metric['timestamp']) && $metric['timestamp'] >= $cutoff;
        });

        if (empty($filtered)) {
            return [
                'avg_load_time' => 0,
                'avg_queries' => 0,
                'avg_memory' => 0,
                'samples' => 0,
            ];
        }

        $samples = count($filtered);

        $totalLoadTime = array_sum(array_column($filtered, 'load_time'));
        $totalQueries = array_sum(array_filter(array_column($filtered, 'db_queries')));
        $totalMemory = array_sum(array_filter(array_column($filtered, 'memory_peak')));

        return [
            'avg_load_time' => round($totalLoadTime / $samples, 3),
            'avg_queries' => round($totalQueries / $samples, 1),
            'avg_memory' => round($totalMemory / $samples / 1024 / 1024, 2),
            'samples' => $samples,
            'period_days' => $days,
        ];
    }

    /**
     * Get recent metrics
     */
    public function getRecent(int $limit = 50): array
    {
        $data = get_option(self::OPTION . '_data', []);

        if (empty($data) || !is_array($data)) {
            return [];
        }

        return array_slice($data, -$limit);
    }

    /**
     * Clear all stored metrics
     */
    public function clearMetrics(): bool
    {
        delete_option(self::OPTION . '_data');
        Logger::info('Performance metrics cleared');
        return true;
    }

    /**
     * Inject client-side timing script
     */
    public function injectTimingScript(): void
    {
        if (!$this->settings()['track_timing']) {
            return;
        }

        ?>
        <script>
        (function() {
            if (!window.performance || !window.performance.timing) return;
            
            window.addEventListener('load', function() {
                setTimeout(function() {
                    var t = window.performance.timing;
                    var metrics = {
                        dns: t.domainLookupEnd - t.domainLookupStart,
                        tcp: t.connectEnd - t.connectStart,
                        ttfb: t.responseStart - t.requestStart,
                        download: t.responseEnd - t.responseStart,
                        dom: t.domContentLoadedEventEnd - t.domContentLoadedEventStart,
                        load: t.loadEventEnd - t.loadEventStart,
                        total: t.loadEventEnd - t.navigationStart
                    };
                    
                    // Send to server via REST API or beacon
                    if (navigator.sendBeacon && window.fpPerfSuite) {
                        var data = new FormData();
                        data.append('metrics', JSON.stringify(metrics));
                        navigator.sendBeacon(window.fpPerfSuite.restUrl + 'metrics/timing', data);
                    }
                }, 0);
            });
        })();
        </script>
        <?php
    }

    /**
     * Get performance trends
     */
    public function getTrends(int $days = 30): array
    {
        $data = get_option(self::OPTION . '_data', []);

        if (empty($data)) {
            return [];
        }

        $cutoff = time() - ($days * DAY_IN_SECONDS);
        $filtered = array_filter($data, function ($metric) use ($cutoff) {
            return isset($metric['timestamp']) && $metric['timestamp'] >= $cutoff;
        });

        // Group by day
        $byDay = [];
        foreach ($filtered as $metric) {
            $day = date('Y-m-d', $metric['timestamp']);

            if (!isset($byDay[$day])) {
                $byDay[$day] = [
                    'load_times' => [],
                    'queries' => [],
                    'memory' => [],
                ];
            }

            $byDay[$day]['load_times'][] = $metric['load_time'];
            if (isset($metric['db_queries'])) {
                $byDay[$day]['queries'][] = $metric['db_queries'];
            }
            if (isset($metric['memory_peak'])) {
                $byDay[$day]['memory'][] = $metric['memory_peak'];
            }
        }

        // Calculate daily averages
        $trends = [];
        foreach ($byDay as $day => $metrics) {
            $trends[$day] = [
                'date' => $day,
                'avg_load_time' => count($metrics['load_times']) > 0 ? round(array_sum($metrics['load_times']) / count($metrics['load_times']), 3) : 0,
                'avg_queries' => count($metrics['queries']) > 0 ? round(array_sum($metrics['queries']) / count($metrics['queries']), 1) : 0,
                'avg_memory' => count($metrics['memory']) > 0 ? round(array_sum($metrics['memory']) / count($metrics['memory']) / 1024 / 1024, 2) : 0,
                'samples' => count($metrics['load_times']),
            ];
        }

        return array_values($trends);
    }
}
