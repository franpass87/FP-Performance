<?php

namespace FP\PerfSuite\Services\Monitoring;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;

/**
 * System Monitor Service
 *
 * Raccoglie metriche del sistema server: carico, memoria, CPU, spazio disco
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class SystemMonitor
{
    private const OPTION = 'fp_ps_system_monitor';
    private const MAX_ENTRIES = 100;

    private static ?self $instance = null;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    private function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
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

    /**
     * Get singleton instance
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional)
     */
    public static function instance(?OptionsRepositoryInterface $optionsRepo = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($optionsRepo);
        }

        return self::$instance;
    }

    /**
     * Raccoglie metriche del sistema
     */
    public function collectMetrics(): array
    {
        $metrics = [
            'timestamp' => time(),
            'memory_usage' => $this->getMemoryUsage(),
            'memory_limit' => $this->getMemoryLimit(),
            'disk_usage' => $this->getDiskUsage(),
            'load_average' => $this->getLoadAverage(),
            'php_version' => PHP_VERSION,
            'server_software' => $this->getServerSoftware(),
            'uptime' => $this->getUptime(),
            'cpu_info' => $this->getCpuInfo(),
            'database_size' => $this->getDatabaseSize(),
            'wp_memory_limit' => $this->getWpMemoryLimit(),
            'max_execution_time' => ini_get('max_execution_time'),
            'max_input_vars' => ini_get('max_input_vars'),
            'post_max_size' => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        ];

        $this->storeMetrics($metrics);
        
        return $metrics;
    }

    /**
     * Ottiene utilizzo memoria corrente
     */
    private function getMemoryUsage(): array
    {
        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = $this->getMemoryLimit();
        
        return [
            'current' => $current,
            'current_mb' => round($current / 1024 / 1024, 2),
            'peak' => $peak,
            'peak_mb' => round($peak / 1024 / 1024, 2),
            'limit' => $limit,
            'limit_mb' => round($limit / 1024 / 1024, 2),
            'usage_percent' => $limit > 0 ? round(($current / $limit) * 100, 2) : 0,
            'peak_percent' => $limit > 0 ? round(($peak / $limit) * 100, 2) : 0,
        ];
    }

    /**
     * Ottiene limite memoria PHP
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return 0; // Unlimited
        }
        
        $value = (int) $limit;
        $unit = strtoupper(substr($limit, -1));
        
        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return $value;
        }
    }

    /**
     * Ottiene utilizzo spazio disco
     */
    private function getDiskUsage(): array
    {
        $total = disk_total_space(ABSPATH);
        $free = disk_free_space(ABSPATH);
        $used = $total - $free;
        
        return [
            'total' => $total,
            'total_gb' => round($total / 1024 / 1024 / 1024, 2),
            'free' => $free,
            'free_gb' => round($free / 1024 / 1024 / 1024, 2),
            'used' => $used,
            'used_gb' => round($used / 1024 / 1024 / 1024, 2),
            'usage_percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Ottiene carico medio del sistema
     */
    private function getLoadAverage(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => $load[0] ?? 0,
                '5min' => $load[1] ?? 0,
                '15min' => $load[2] ?? 0,
            ];
        }
        
        // Fallback per sistemi Windows o senza sys_getloadavg
        return [
            '1min' => 0,
            '5min' => 0,
            '15min' => 0,
        ];
    }

    /**
     * Ottiene informazioni server
     */
    private function getServerSoftware(): string
    {
        return $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
    }

    /**
     * Ottiene uptime del sistema (se disponibile)
     * SICUREZZA: Rimossi shell_exec pericolosi
     */
    private function getUptime(): ?int
    {
        // SICUREZZA: Non usiamo più shell_exec per evitare vulnerabilità
        // Fallback sicuro usando solo funzioni PHP native
        if (function_exists('sys_getloadavg')) {
            // Usiamo il timestamp corrente come fallback sicuro
            return time();
        }
        
        return null;
    }

    /**
     * Ottiene informazioni CPU
     * SICUREZZA: Rimossi shell_exec pericolosi
     */
    private function getCpuInfo(): array
    {
        $info = [
            'cores' => null,
            'model' => null,
            'frequency' => null,
        ];
        
        // SICUREZZA: Non usiamo più shell_exec per evitare vulnerabilità
        // Usiamo solo funzioni PHP native sicure
        if (function_exists('sys_getloadavg')) {
            // Stimiamo il numero di core basandoci sul load average
            $load = sys_getloadavg();
            if ($load && $load[0] > 0) {
                $info['cores'] = max(1, (int) ceil($load[0]));
            }
        }
        
        // Fallback sicuro per il modello CPU
        $info['model'] = 'Unknown (Security Mode)';
        
        return $info;
    }

    /**
     * Ottiene dimensione database WordPress
     */
    private function getDatabaseSize(): array
    {
        global $wpdb;
        
        $size = 0;
        $tables = 0;
        
        try {
            $result = $wpdb->get_results("
                SELECT 
                    table_name,
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
            ");
            
            if ($result) {
                $tables = count($result);
                foreach ($result as $table) {
                    $size += $table->size_mb;
                }
            }
        } catch (Exception $e) {
            Logger::debug('Error getting database size: ' . $e->getMessage());
        }
        
        return [
            'size_mb' => round($size, 2),
            'tables' => $tables,
        ];
    }

    /**
     * Ottiene limite memoria WordPress
     */
    private function getWpMemoryLimit(): int
    {
        $limit = defined('WP_MEMORY_LIMIT') ? WP_MEMORY_LIMIT : ini_get('memory_limit');
        
        if ($limit === '-1') {
            return 0;
        }
        
        $value = (int) $limit;
        $unit = strtoupper(substr($limit, -1));
        
        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return $value;
        }
    }

    /**
     * Salva metriche nel database
     */
    private function storeMetrics(array $metrics): void
    {
        $stored = $this->getOption(self::OPTION . '_data', []);
        
        if (!is_array($stored)) {
            $stored = [];
        }
        
        $stored[] = $metrics;
        
        // Mantieni solo gli ultimi MAX_ENTRIES
        if (count($stored) > self::MAX_ENTRIES) {
            $stored = array_slice($stored, -self::MAX_ENTRIES);
        }
        
        $this->setOption(self::OPTION . '_data', $stored);
    }

    /**
     * Ottiene statistiche aggregate
     */
    public function getStats(int $days = 7): array
    {
        $data = $this->getOption(self::OPTION . '_data', []);
        
        if (empty($data)) {
            return $this->getDefaultStats();
        }
        
        $cutoff = time() - ($days * DAY_IN_SECONDS);
        $recentData = array_filter($data, function($metric) use ($cutoff) {
            return $metric['timestamp'] >= $cutoff;
        });
        
        if (empty($recentData)) {
            return $this->getDefaultStats();
        }
        
        return $this->calculateStats($recentData);
    }

    /**
     * Calcola statistiche dai dati
     */
    private function calculateStats(array $data): array
    {
        if (empty($data)) {
            return $this->getDefaultStats();
        }

        // Reindicizza per garantire accesso affidabile all'ultimo elemento
        $normalizedData = array_values($data);
        $latest = end($normalizedData) ?: [];

        $memoryUsage = array_column($normalizedData, 'memory_usage');
        $diskUsage = array_column($normalizedData, 'disk_usage');
        $loadAverage = array_column($normalizedData, 'load_average');
        
        $memoryUsageCount = count($memoryUsage);
        $loadAverageCount = count($loadAverage);
        
        return [
            'samples' => count($normalizedData),
            'memory' => [
                'avg_usage_mb' => $memoryUsageCount > 0 ? round(array_sum(array_column($memoryUsage, 'current_mb')) / $memoryUsageCount, 2) : 0,
                'max_usage_mb' => $memoryUsageCount > 0 ? max(array_column($memoryUsage, 'current_mb')) : 0,
                'avg_peak_mb' => $memoryUsageCount > 0 ? round(array_sum(array_column($memoryUsage, 'peak_mb')) / $memoryUsageCount, 2) : 0,
                'max_peak_mb' => $memoryUsageCount > 0 ? max(array_column($memoryUsage, 'peak_mb')) : 0,
                'avg_usage_percent' => $memoryUsageCount > 0 ? round(array_sum(array_column($memoryUsage, 'usage_percent')) / $memoryUsageCount, 2) : 0,
                'max_usage_percent' => $memoryUsageCount > 0 ? max(array_column($memoryUsage, 'usage_percent')) : 0,
            ],
            // BUGFIX #25: Usa l'ULTIMO elemento (più recente) invece del PRIMO (più vecchio)
            'disk' => [
                'total_gb' => !empty($diskUsage) ? end($diskUsage)['total_gb'] : 0,
                'free_gb' => !empty($diskUsage) ? end($diskUsage)['free_gb'] : 0,
                'used_gb' => !empty($diskUsage) ? end($diskUsage)['used_gb'] : 0,
                'usage_percent' => !empty($diskUsage) ? end($diskUsage)['usage_percent'] : 0,
            ],
            'load' => [
                'avg_1min' => $loadAverageCount > 0 ? round(array_sum(array_column($loadAverage, '1min')) / $loadAverageCount, 2) : 0,
                'avg_5min' => $loadAverageCount > 0 ? round(array_sum(array_column($loadAverage, '5min')) / $loadAverageCount, 2) : 0,
                'avg_15min' => $loadAverageCount > 0 ? round(array_sum(array_column($loadAverage, '15min')) / $loadAverageCount, 2) : 0,
                'max_1min' => $loadAverageCount > 0 ? max(array_column($loadAverage, '1min')) : 0,
                'max_5min' => $loadAverageCount > 0 ? max(array_column($loadAverage, '5min')) : 0,
                'max_15min' => $loadAverageCount > 0 ? max(array_column($loadAverage, '15min')) : 0,
            ],
            'database' => [
                'size_mb' => $latest['database_size']['size_mb'] ?? 0,
                'tables' => $latest['database_size']['tables'] ?? 0,
            ],
            'system' => [
                'php_version' => $latest['php_version'] ?? PHP_VERSION,
                'server_software' => $latest['server_software'] ?? 'Unknown',
                'uptime' => $latest['uptime'] ?? null,
                'cpu_cores' => $latest['cpu_info']['cores'] ?? null,
                'cpu_model' => $latest['cpu_info']['model'] ?? null,
            ],
        ];
    }

    /**
     * Statistiche di default quando non ci sono dati
     */
    private function getDefaultStats(): array
    {
        return [
            'samples' => 0,
            'memory' => [
                'avg_usage_mb' => 0,
                'max_usage_mb' => 0,
                'avg_peak_mb' => 0,
                'max_peak_mb' => 0,
                'avg_usage_percent' => 0,
                'max_usage_percent' => 0,
            ],
            'disk' => [
                'total_gb' => 0,
                'free_gb' => 0,
                'used_gb' => 0,
                'usage_percent' => 0,
            ],
            'load' => [
                'avg_1min' => 0,
                'avg_5min' => 0,
                'avg_15min' => 0,
                'max_1min' => 0,
                'max_5min' => 0,
                'max_15min' => 0,
            ],
            'database' => [
                'size_mb' => 0,
                'tables' => 0,
            ],
            'system' => [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'uptime' => null,
                'cpu_cores' => null,
                'cpu_model' => null,
            ],
        ];
    }

    /**
     * Pulisce dati vecchi
     */
    public function cleanup(int $days = 30): int
    {
        $data = $this->getOption(self::OPTION . '_data', []);
        
        if (empty($data)) {
            return 0;
        }
        
        $cutoff = time() - ($days * DAY_IN_SECONDS);
        $filtered = array_filter($data, function($metric) use ($cutoff) {
            return $metric['timestamp'] >= $cutoff;
        });
        
        $removed = count($data) - count($filtered);
        
        if ($removed > 0) {
            $this->setOption(self::OPTION . '_data', array_values($filtered));
        }
        
        return $removed;
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // SystemMonitor non ha hook specifici da registrare
        // È utilizzato principalmente per monitoraggio on-demand
    }
}