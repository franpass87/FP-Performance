<?php

namespace FP\PerfSuite\Services\ML\MLPredictor;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

use function time;
use function memory_get_peak_usage;
use function wp_is_mobile;
use function is_admin;
use function get_template;
use function get_bloginfo;
use function PHP_VERSION;

/**
 * Raccoglie dati di performance per l'analisi ML
 * 
 * @package FP\PerfSuite\Services\ML\MLPredictor
 * @author Francesco Passeri
 */
class DataCollector
{
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
    }
    /**
     * Raccoglie dati di performance
     */
    public function gatherPerformanceData(): array
    {
        $data = [
            'timestamp' => time(),
            'url' => $this->getCurrentUrl(),
            'load_time' => $this->getLoadTime(),
            'memory_usage' => memory_get_peak_usage(true),
            'db_queries' => $this->getDbQueryCount(),
            'cache_hits' => $this->getCacheHits(),
            'cache_misses' => $this->getCacheMisses(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'is_mobile' => wp_is_mobile(),
            'is_admin' => is_admin(),
            'active_plugins' => $this->getActivePluginsCount(),
            'theme' => get_template(),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'server_load' => $this->getServerLoad(),
            'error_count' => $this->getErrorCount()
        ];
        
        return $data;
    }

    private function getCurrentUrl(): string
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            return home_url($_SERVER['REQUEST_URI']);
        }
        return home_url();
    }

    private function getLoadTime(): float
    {
        if (defined('FP_PERF_START_TIME')) {
            return microtime(true) - FP_PERF_START_TIME;
        }
        return 0.0;
    }

    private function getDbQueryCount(): int
    {
        global $wpdb;
        return $wpdb->num_queries ?? 0;
    }

    private function getCacheHits(): int
    {
        return (int) $this->getOption('fp_ps_cache_hits', 0);
    }

    private function getCacheMisses(): int
    {
        return (int) $this->getOption('fp_ps_cache_misses', 0);
    }

    private function getActivePluginsCount(): int
    {
        return count(get_option('active_plugins', []));
    }

    private function getServerLoad(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return $load[0] ?? 0.0;
        }
        return 0.0;
    }

    private function getErrorCount(): int
    {
        return (int) $this->getOption('fp_ps_error_count', 0);
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
}
















