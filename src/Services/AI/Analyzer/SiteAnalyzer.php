<?php

namespace FP\PerfSuite\Services\AI\Analyzer;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function get_option;
use function wp_count_posts;
use function wp_upload_dir;
use function get_plugins;
use function is_plugin_active;
use function get_transient;
use function set_transient;
use const HOUR_IN_SECONDS;

/**
 * Analizza il sito WordPress
 * 
 * @package FP\PerfSuite\Services\AI\Analyzer
 * @author Francesco Passeri
 */
class SiteAnalyzer
{
    private AnalysisCalculator $calculator;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Constructor
     * 
     * @param AnalysisCalculator $calculator Analysis calculator
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(AnalysisCalculator $calculator, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->calculator = $calculator;
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Analizza il sito e restituisce i dati di analisi
     */
    public function analyze(): array
    {
        // Check cache
        $cached = get_transient('fp_ps_ai_analysis');
        if ($cached !== false) {
            return $cached;
        }

        $analysis = [
            'hosting' => $this->detectHosting(),
            'resources' => $this->analyzeResources(),
            'database' => $this->analyzeDatabase(),
            'traffic' => $this->estimateTraffic(),
            'content' => $this->analyzeContent(),
            'plugins' => $this->analyzePlugins(),
            'server' => $this->analyzeServer(),
        ];

        // Cache per 1 ora
        set_transient('fp_ps_ai_analysis', $analysis, HOUR_IN_SECONDS);

        return $analysis;
    }

    /**
     * Rileva il provider di hosting
     */
    public function detectHosting(): array
    {
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        $serverName = $_SERVER['SERVER_NAME'] ?? '';
        $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
        
        $providers = [
            'aruba' => [
                'patterns' => ['aruba', 'technorail'],
                'name' => 'Aruba',
            ],
            'siteground' => [
                'patterns' => ['siteground', 'sgvps'],
                'name' => 'SiteGround',
            ],
            'kinsta' => [
                'patterns' => ['kinsta'],
                'name' => 'Kinsta',
            ],
            'wpengine' => [
                'patterns' => ['wpengine'],
                'name' => 'WP Engine',
            ],
            'flywheel' => [
                'patterns' => ['flywheel', 'getflywheel'],
                'name' => 'Flywheel',
            ],
            'godaddy' => [
                'patterns' => ['godaddy', 'secureserver'],
                'name' => 'GoDaddy',
            ],
            'bluehost' => [
                'patterns' => ['bluehost'],
                'name' => 'Bluehost',
            ],
            'hostgator' => [
                'patterns' => ['hostgator'],
                'name' => 'HostGator',
            ],
            'dreamhost' => [
                'patterns' => ['dreamhost'],
                'name' => 'DreamHost',
            ],
            'ionos' => [
                'patterns' => ['ionos', '1und1', '1and1'],
                'name' => 'IONOS',
            ],
            'ovh' => [
                'patterns' => ['ovh'],
                'name' => 'OVH',
            ],
            'cloudways' => [
                'patterns' => ['cloudways'],
                'name' => 'Cloudways',
            ],
        ];

        foreach ($providers as $key => $provider) {
            foreach ($provider['patterns'] as $pattern) {
                if (stripos($serverSoftware, $pattern) !== false || 
                    stripos($serverName, $pattern) !== false ||
                    stripos($serverAddr, $pattern) !== false) {
                    return [
                        'provider' => $key,
                        'name' => $provider['name'],
                        'detected' => true,
                    ];
                }
            }
        }

        return [
            'provider' => 'unknown',
            'name' => 'Hosting Generico',
            'detected' => false,
        ];
    }

    /**
     * Analizza le risorse del server
     */
    public function analyzeResources(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->calculator->parseMemoryLimit($memoryLimit);
        
        $maxExecutionTime = ini_get('max_execution_time');
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        
        return [
            'memory_limit' => $memoryLimit,
            'memory_limit_bytes' => $memoryLimitBytes,
            'memory_category' => $this->calculator->categorizeMemory($memoryLimitBytes),
            'max_execution_time' => $maxExecutionTime,
            'upload_max_filesize' => $uploadMaxFilesize,
            'php_version' => PHP_VERSION,
        ];
    }

    /**
     * Analizza il database
     */
    public function analyzeDatabase(): array
    {
        global $wpdb;
        
        // Dimensione database
        $result = $wpdb->get_row(
            "SELECT SUM(data_length + index_length) / 1024 / 1024 AS size 
             FROM information_schema.TABLES 
             WHERE table_schema = '{$wpdb->dbname}'"
        );
        
        $sizeMB = $result ? round($result->size, 2) : 0;
        
        // Conta tabelle
        $tables = $wpdb->get_results("SHOW TABLES");
        $tableCount = count($tables);
        
        return [
            'size_mb' => $sizeMB,
            'size_category' => $this->calculator->categorizeDBSize($sizeMB),
            'table_count' => $tableCount,
        ];
    }

    /**
     * Stima il traffico del sito
     */
    public function estimateTraffic(): array
    {
        // Usa le statistiche di WordPress se disponibili
        $stats = $this->getOption('fp_ps_traffic_stats', []);
        
        if (empty($stats)) {
            // Stima basata sui post e commenti
            $posts = wp_count_posts();
            $published = $posts->publish ?? 0;
            
            // Stima approssimativa
            $estimated = $published < 10 ? 'low' : ($published < 100 ? 'medium' : 'high');
            
            return [
                'level' => $estimated,
                'estimated' => true,
            ];
        }
        
        return [
            'level' => $stats['level'] ?? 'medium',
            'estimated' => false,
            'visits_per_month' => $stats['visits_per_month'] ?? 0,
        ];
    }

    /**
     * Analizza il contenuto
     */
    public function analyzeContent(): array
    {
        $posts = wp_count_posts();
        $pages = wp_count_posts('page');
        
        // Conta immagini
        $uploadDir = wp_upload_dir();
        $imageCount = 0;
        
        if (isset($uploadDir['basedir'])) {
            $files = glob($uploadDir['basedir'] . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            $imageCount = $files ? count($files) : 0;
        }
        
        return [
            'posts' => $posts->publish ?? 0,
            'pages' => $pages->publish ?? 0,
            'images' => $imageCount,
            'has_videos' => false, // Placeholder
        ];
    }

    /**
     * Analizza i plugin
     */
    public function analyzePlugins(): array
    {
        $allPlugins = get_plugins();
        $activePlugins = [];
        $pageBuilders = 0;
        $hasEcommerce = false;
        $hasCaching = false;
        
        foreach ($allPlugins as $pluginFile => $pluginData) {
            if (is_plugin_active($pluginFile)) {
                $activePlugins[] = $pluginFile;
                
                // Rileva page builders
                if (stripos($pluginFile, 'elementor') !== false ||
                    stripos($pluginFile, 'beaver') !== false ||
                    stripos($pluginFile, 'divi') !== false ||
                    stripos($pluginFile, 'visual-composer') !== false) {
                    $pageBuilders++;
                }
                
                // Rileva e-commerce
                if (stripos($pluginFile, 'woocommerce') !== false ||
                    stripos($pluginFile, 'edd') !== false ||
                    stripos($pluginFile, 'easy-digital-downloads') !== false) {
                    $hasEcommerce = true;
                }
                
                // Rileva caching
                if (stripos($pluginFile, 'w3-total-cache') !== false ||
                    stripos($pluginFile, 'wp-super-cache') !== false ||
                    stripos($pluginFile, 'wp-rocket') !== false) {
                    $hasCaching = true;
                }
            }
        }
        
        return [
            'active' => count($activePlugins),
            'total' => count($allPlugins),
            'page_builders' => $pageBuilders,
            'ecommerce' => $hasEcommerce,
            'caching' => $hasCaching,
        ];
    }

    /**
     * Analizza il server
     */
    public function analyzeServer(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
        ];
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








