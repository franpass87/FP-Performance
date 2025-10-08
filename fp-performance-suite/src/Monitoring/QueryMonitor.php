<?php

namespace FP\PerfSuite\Monitoring;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Cache\PageCache;

/**
 * Query Monitor Integration
 *
 * Adds FP Performance Suite metrics to Query Monitor plugin
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class QueryMonitor
{
    private static array $metrics = [];

    /**
     * Register integration if Query Monitor is active
     */
    public static function register(): void
    {
        if (!class_exists('QM_Collectors')) {
            return;
        }

        add_filter('qm/collectors', [self::class, 'addCollector']);
        add_filter('qm/outputter/html', [self::class, 'addOutputter'], 10, 2);
    }

    /**
     * Add FP Performance collector
     */
    public static function addCollector(array $collectors): array
    {
        require_once __DIR__ . '/QueryMonitor/Collector.php';
        $collectors['fp_performance'] = new \FP\PerfSuite\Monitoring\QueryMonitor\Collector();
        return $collectors;
    }

    /**
     * Add FP Performance outputter
     */
    public static function addOutputter(array $output, \QM_Collectors $collectors): array
    {
        require_once __DIR__ . '/QueryMonitor/Output.php';

        if ($collector = \QM_Collectors::get('fp_performance')) {
            $output['fp_performance'] = new \FP\PerfSuite\Monitoring\QueryMonitor\Output($collector);
        }

        return $output;
    }

    /**
     * Track metric
     */
    public static function track(string $key, $value): void
    {
        self::$metrics[$key] = $value;
    }

    /**
     * Start timing
     */
    public static function startTimer(string $name): void
    {
        self::$metrics['timers'][$name]['start'] = microtime(true);
    }

    /**
     * Stop timing
     */
    public static function stopTimer(string $name): void
    {
        if (isset(self::$metrics['timers'][$name]['start'])) {
            self::$metrics['timers'][$name]['end'] = microtime(true);
            self::$metrics['timers'][$name]['duration'] =
                self::$metrics['timers'][$name]['end'] - self::$metrics['timers'][$name]['start'];
        }
    }

    /**
     * Get all metrics
     */
    public static function getMetrics(): array
    {
        return self::$metrics;
    }
}
