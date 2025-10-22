<?php

namespace FP\PerfSuite\Services\AI;

use function get_option;
use function wp_count_posts;
use function wp_upload_dir;
use function get_plugins;
use function is_plugin_active;
use function size_format;
use function get_transient;
use function set_transient;

/**
 * AI Analyzer - Analizza il sito e suggerisce configurazioni ottimali
 */
class Analyzer
{
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
     * Genera suggerimenti basati sull'analisi
     */
    public function suggest(array $analysis): array
    {
        $suggestions = [];
        $config = [];

        // Analisi Hosting
        $hosting = $analysis['hosting'];
        if ($hosting['provider'] !== 'unknown') {
            $suggestions[] = [
                'icon' => '🏢',
                'title' => 'Hosting Rilevato: ' . $hosting['name'],
                'description' => 'Ho ottimizzato le configurazioni specifiche per ' . $hosting['name'],
                'impact' => 'high',
            ];
        }

        // Configurazione Page Cache
        $resources = $analysis['resources'];
        $ttl = $this->suggestCacheTTL($resources, $analysis['traffic']);
        $config['page_cache'] = [
            'enabled' => true,
            'ttl' => $ttl,
            'mobile_cache' => true,
        ];
        
        $suggestions[] = [
            'icon' => '🚀',
            'title' => 'Page Cache Ottimizzata',
            'description' => sprintf('Cache impostata a %d minuti basata sul tuo traffico e contenuti', $ttl / 60),
            'impact' => 'high',
        ];

        // Browser Cache
        $config['browser_cache'] = [
            'enabled' => !in_array($hosting['provider'], ['ionos'], true),
        ];

        if (!$config['browser_cache']['enabled']) {
            $suggestions[] = [
                'icon' => '⚠️',
                'title' => 'Browser Cache Disabilitato',
                'description' => $hosting['name'] . ' gestisce già il browser caching',
                'impact' => 'medium',
            ];
        }

        // Asset Optimization
        $hasBuilder = $analysis['plugins']['page_builders'] > 0;
        $hasEcommerce = $analysis['plugins']['ecommerce'];
        
        $config['asset_optimizer'] = [
            'enabled' => true,
            'minify_html' => true,
            'minify_css' => true,
            'minify_js' => !$hasBuilder, // Disabilita se c'è un page builder
            'defer_js' => true,
            'combine_css' => false, // Disabilitato per compatibilità
            'combine_js' => false,
            'remove_query_strings' => true,
            'preload' => [],
        ];

        if ($hasBuilder) {
            $suggestions[] = [
                'icon' => '🎨',
                'title' => 'Page Builder Rilevato',
                'description' => 'Minificazione JS disabilitata per compatibilità con page builders',
                'impact' => 'medium',
            ];
        }

        // WebP Configuration
        $imageCount = $analysis['content']['images'];
        $config['webp'] = [
            'enabled' => $imageCount > 10,
            'quality' => $this->suggestWebPQuality($resources, $hosting['provider']),
            'lossy' => true,
        ];

        if ($imageCount > 10) {
            $suggestions[] = [
                'icon' => '🖼️',
                'title' => 'Conversione WebP Attivata',
                'description' => sprintf('Rilevate %d immagini. WebP ridurrà le dimensioni del 25-35%%', $imageCount),
                'impact' => 'high',
            ];
        }

        // Lazy Load
        $config['lazy_load'] = [
            'enabled' => $imageCount > 20,
            'images' => true,
            'iframes' => true,
            'threshold' => 300,
        ];

        if ($imageCount > 20) {
            $suggestions[] = [
                'icon' => '⚡',
                'title' => 'Lazy Load Attivato',
                'description' => 'Caricamento differito per migliorare i tempi di caricamento iniziali',
                'impact' => 'high',
            ];
        }

        // Database Optimization
        $dbSize = $analysis['database']['size_mb'];
        $config['db'] = [
            'batch' => $this->suggestDBBatch($resources, $hosting['provider']),
            'auto_optimize' => $dbSize > 100,
        ];

        if ($dbSize > 100) {
            $suggestions[] = [
                'icon' => '💾',
                'title' => 'Ottimizzazione Database',
                'description' => sprintf('Database di %.1f MB - consigliata ottimizzazione automatica', $dbSize),
                'impact' => 'medium',
            ];
        }

        // Heartbeat API
        $config['heartbeat'] = $this->suggestHeartbeat($resources, $hasBuilder);
        
        $suggestions[] = [
            'icon' => '💓',
            'title' => 'Heartbeat API Ottimizzato',
            'description' => sprintf('Intervallo impostato a %d secondi per ridurre il carico server', $config['heartbeat']),
            'impact' => 'medium',
        ];

        // Backend Optimization
        $config['backend'] = [
            'limit_post_revisions' => 5,
            'autosave_interval' => 120,
            'disable_emojis' => true,
            'disable_embeds' => !$analysis['content']['has_videos'],
        ];

        // E-commerce specifico
        if ($hasEcommerce) {
            $suggestions[] = [
                'icon' => '🛒',
                'title' => 'E-commerce Rilevato',
                'description' => 'Configurazioni ottimizzate per WooCommerce/Easy Digital Downloads',
                'impact' => 'high',
            ];
            
            // Aggiungi esclusioni per cart/checkout
            $config['exclusions'] = [
                'cart',
                'checkout',
                'my-account',
                'add-to-cart',
            ];
        }

        return [
            'suggestions' => $suggestions,
            'config' => $config,
            'analysis' => $analysis,
            'score' => $this->calculateOptimizationScore($analysis),
        ];
    }

    /**
     * Rileva il provider di hosting
     */
    private function detectHosting(): array
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
    private function analyzeResources(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);
        
        $maxExecutionTime = ini_get('max_execution_time');
        $uploadMaxFilesize = ini_get('upload_max_filesize');
        
        return [
            'memory_limit' => $memoryLimit,
            'memory_limit_bytes' => $memoryLimitBytes,
            'memory_category' => $this->categorizeMemory($memoryLimitBytes),
            'max_execution_time' => $maxExecutionTime,
            'upload_max_filesize' => $uploadMaxFilesize,
            'php_version' => PHP_VERSION,
        ];
    }

    /**
     * Analizza il database
     */
    private function analyzeDatabase(): array
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
            'size_category' => $this->categorizeDBSize($sizeMB),
            'table_count' => $tableCount,
        ];
    }

    /**
     * Stima il traffico del sito
     */
    private function estimateTraffic(): array
    {
        // Usa le statistiche di WordPress se disponibili
        $stats = get_option('fp_ps_traffic_stats', []);
        
        if (empty($stats)) {
            // Stima basata sui post e commenti
            $posts = wp_count_posts();
            $published = $posts->publish ?? 0;
            
            // Stima approssimativa
            $estimated = $published < 10 ? 'low' : ($published < 100 ? 'medium' : 'high');
            
            return [
                'level' => $estimated,
                'posts_count' => $published,
                'estimated' => true,
            ];
        }
        
        return $stats;
    }

    /**
     * Analizza il contenuto del sito
     */
    private function analyzeContent(): array
    {
        global $wpdb;
        
        // Conta immagini nella media library
        $imageCount = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'attachment' 
             AND post_mime_type LIKE 'image/%'"
        );
        
        // Conta video
        $videoCount = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'attachment' 
             AND post_mime_type LIKE 'video/%'"
        );
        
        // Post pubblicati
        $posts = wp_count_posts('post');
        $pages = wp_count_posts('page');
        
        return [
            'images' => (int) $imageCount,
            'videos' => (int) $videoCount,
            'has_videos' => $videoCount > 0,
            'posts' => $posts->publish ?? 0,
            'pages' => $pages->publish ?? 0,
        ];
    }

    /**
     * Analizza i plugin installati
     */
    private function analyzePlugins(): array
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $allPlugins = get_plugins();
        $activePlugins = get_option('active_plugins', []);
        
        $pageBuilders = ['elementor', 'beaver-builder', 'divi', 'wpbakery', 'oxygen', 'bricks'];
        $ecommerce = ['woocommerce', 'easy-digital-downloads', 'wp-ecommerce'];
        $caching = ['wp-rocket', 'w3-total-cache', 'wp-super-cache', 'litespeed-cache'];
        
        $hasBuilder = 0;
        $hasEcommerce = false;
        $hasCaching = false;
        
        foreach ($activePlugins as $plugin) {
            $pluginSlug = dirname($plugin);
            
            foreach ($pageBuilders as $builder) {
                if (stripos($pluginSlug, $builder) !== false) {
                    $hasBuilder++;
                }
            }
            
            foreach ($ecommerce as $shop) {
                if (stripos($pluginSlug, $shop) !== false) {
                    $hasEcommerce = true;
                }
            }
            
            foreach ($caching as $cache) {
                if (stripos($pluginSlug, $cache) !== false) {
                    $hasCaching = true;
                }
            }
        }
        
        return [
            'total' => count($allPlugins),
            'active' => count($activePlugins),
            'page_builders' => $hasBuilder,
            'ecommerce' => $hasEcommerce,
            'caching' => $hasCaching,
        ];
    }

    /**
     * Analizza le informazioni del server
     */
    private function analyzeServer(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'php_sapi' => PHP_SAPI,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'wordpress_version' => get_bloginfo('version'),
            'is_multisite' => is_multisite(),
            'https' => is_ssl(),
        ];
    }

    /**
     * Suggerisce il TTL della cache
     */
    private function suggestCacheTTL(array $resources, array $traffic): int
    {
        $base = 3600; // 1 ora di default
        
        // Riduci per hosting limitati
        if ($resources['memory_category'] === 'low') {
            $base = 1800; // 30 minuti
        }
        
        // Aumenta per alto traffico
        if ($traffic['level'] === 'high') {
            $base = 7200; // 2 ore
        }
        
        // Riduci per basso traffico
        if ($traffic['level'] === 'low') {
            $base = 1800; // 30 minuti
        }
        
        return $base;
    }

    /**
     * Suggerisce la qualità WebP
     */
    private function suggestWebPQuality(array $resources, string $hostingProvider): int
    {
        // Default 80
        $quality = 80;
        
        // Riduci per hosting limitati
        if ($resources['memory_category'] === 'low' || in_array($hostingProvider, ['aruba', 'ionos'], true)) {
            $quality = 75;
        }
        
        // Aumenta per risorse abbondanti
        if ($resources['memory_category'] === 'high') {
            $quality = 85;
        }
        
        return $quality;
    }

    /**
     * Suggerisce il batch size per database
     */
    private function suggestDBBatch(array $resources, string $hostingProvider): int
    {
        if ($resources['memory_category'] === 'low' || in_array($hostingProvider, ['aruba', 'ionos'], true)) {
            return 100;
        }
        
        if ($resources['memory_category'] === 'high') {
            return 300;
        }
        
        return 200;
    }

    /**
     * Suggerisce intervallo heartbeat
     */
    private function suggestHeartbeat(array $resources, bool $hasBuilder): int
    {
        if ($hasBuilder) {
            return 60; // Più frequente per page builders
        }
        
        if ($resources['memory_category'] === 'low') {
            return 120; // Meno frequente per risorse limitate
        }
        
        return 60;
    }

    /**
     * Calcola uno score di ottimizzazione
     */
    private function calculateOptimizationScore(array $analysis): int
    {
        $score = 50; // Base
        
        // +10 se hosting è rilevato
        if ($analysis['hosting']['detected']) {
            $score += 10;
        }
        
        // +15 se risorse sono buone
        if ($analysis['resources']['memory_category'] === 'high') {
            $score += 15;
        } elseif ($analysis['resources']['memory_category'] === 'medium') {
            $score += 10;
        }
        
        // +10 se database è ottimizzato
        if ($analysis['database']['size_category'] === 'small') {
            $score += 10;
        } elseif ($analysis['database']['size_category'] === 'medium') {
            $score += 5;
        }
        
        // +10 se non ci sono troppi plugin
        if ($analysis['plugins']['active'] < 20) {
            $score += 10;
        } elseif ($analysis['plugins']['active'] < 30) {
            $score += 5;
        }
        
        // -10 se c'è già un plugin di caching
        if ($analysis['plugins']['caching']) {
            $score -= 10;
        }
        
        return min(100, max(0, $score));
    }

    /**
     * Helper: Parse memory limit
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
                // fall through
            case 'm':
                $value *= 1024;
                // fall through
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Categorizza memoria
     */
    private function categorizeMemory(int $bytes): string
    {
        $mb = $bytes / 1024 / 1024;
        
        if ($mb < 128) {
            return 'low';
        }
        if ($mb < 256) {
            return 'medium';
        }
        return 'high';
    }

    /**
     * Categorizza dimensione database
     */
    private function categorizeDBSize(float $sizeMB): string
    {
        if ($sizeMB < 50) {
            return 'small';
        }
        if ($sizeMB < 200) {
            return 'medium';
        }
        return 'large';
    }

    /**
     * Pulisce la cache di analisi
     */
    public function clearCache(): void
    {
        delete_transient('fp_ps_ai_analysis');
    }
}

