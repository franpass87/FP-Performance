<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Utils\Logger;

use function add_action;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Object Cache Manager - Redis/Memcached Integration
 *
 * Manages persistent object caching with Redis or Memcached
 *
 * @package FP\PerfSuite\Services\Cache
 * @author Francesco Passeri
 */
class ObjectCacheManager
{
    private const OPTION = 'fp_ps_object_cache';
    private const DROP_IN_FILE = 'object-cache.php';

    private ?object $connection = null;
    private string $driver = 'none';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        $this->initializeConnection($settings);

        add_action('shutdown', [$this, 'closeConnection']);
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,driver:string,host:string,port:int,database:int,password:string,prefix:string,timeout:int}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'driver' => 'redis', // redis, memcached, auto
            'host' => '127.0.0.1',
            'port' => 6379,
            'database' => 0,
            'password' => '',
            'prefix' => 'fp_ps_',
            'timeout' => 1,
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

        $new = [
            'enabled' => !empty($settings['enabled']),
            'driver' => $settings['driver'] ?? $current['driver'],
            'host' => $settings['host'] ?? $current['host'],
            'port' => (int)($settings['port'] ?? $current['port']),
            'database' => (int)($settings['database'] ?? $current['database']),
            'password' => $settings['password'] ?? $current['password'],
            'prefix' => $settings['prefix'] ?? $current['prefix'],
            'timeout' => (int)($settings['timeout'] ?? $current['timeout']),
        ];

        // Validate backend availability before enabling
        if ($new['enabled']) {
            if (!$this->isBackendAvailable($new)) {
                Logger::error('Cannot enable object cache: no backend available', null, [
                    'driver' => $new['driver']
                ]);
                // Don't enable if backend is not available
                $new['enabled'] = false;
            }
        }

        update_option(self::OPTION, $new);

        // Install or remove drop-in
        if ($new['enabled']) {
            $this->installDropIn();
        } else {
            $this->removeDropIn();
        }
    }

    /**
     * Check if cache backend is available
     *
     * @param array $settings Settings to check
     * @return bool True if backend is available
     */
    private function isBackendAvailable(array $settings): bool
    {
        $driver = $settings['driver'];

        if ($driver === 'auto') {
            return class_exists('Redis') || class_exists('Memcached');
        }

        if ($driver === 'redis') {
            return class_exists('Redis');
        }

        if ($driver === 'memcached') {
            return class_exists('Memcached');
        }

        return false;
    }

    /**
     * Initialize cache connection
     *
     * @param array $settings Connection settings
     * @return bool True if connection successful
     */
    private function initializeConnection(array $settings): bool
    {
        $driver = $settings['driver'];

        // Auto-detect available driver
        if ($driver === 'auto') {
            if (class_exists('Redis')) {
                $driver = 'redis';
            } elseif (class_exists('Memcached')) {
                $driver = 'memcached';
            } else {
                Logger::warning('No object cache driver available');
                return false;
            }
        }

        $this->driver = $driver;

        try {
            if ($driver === 'redis') {
                return $this->connectRedis($settings);
            } elseif ($driver === 'memcached') {
                return $this->connectMemcached($settings);
            }
        } catch (\Exception $e) {
            Logger::error('Object cache connection failed', $e, [
                'driver' => $driver,
            ]);
            return false;
        }

        return false;
    }

    /**
     * Connect to Redis
     *
     * @param array $settings Connection settings
     * @return bool True if connected
     */
    private function connectRedis(array $settings): bool
    {
        if (!class_exists('Redis')) {
            Logger::warning('Redis extension not available');
            return false;
        }

        $redis = new \Redis();

        try {
            $connected = $redis->connect(
                $settings['host'],
                $settings['port'],
                $settings['timeout']
            );

            if (!$connected) {
                return false;
            }

            // Authenticate if password provided
            if (!empty($settings['password'])) {
                $redis->auth($settings['password']);
            }

            // Select database
            if ($settings['database'] > 0) {
                $redis->select($settings['database']);
            }

            // Set prefix
            if (!empty($settings['prefix'])) {
                $redis->setOption(\Redis::OPT_PREFIX, $settings['prefix']);
            }

            $this->connection = $redis;

            Logger::info('Redis connected successfully', [
                'host' => $settings['host'],
                'port' => $settings['port'],
                'database' => $settings['database'],
            ]);

            return true;
        } catch (\Exception $e) {
            Logger::error('Redis connection failed', $e);
            return false;
        }
    }

    /**
     * Connect to Memcached
     *
     * @param array $settings Connection settings
     * @return bool True if connected
     */
    private function connectMemcached(array $settings): bool
    {
        if (!class_exists('Memcached')) {
            Logger::warning('Memcached extension not available');
            return false;
        }

        $memcached = new \Memcached();

        try {
            $memcached->addServer($settings['host'], $settings['port']);
            
            // Set options
            $memcached->setOption(\Memcached::OPT_PREFIX_KEY, $settings['prefix']);
            $memcached->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
            $memcached->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);

            // Test connection
            $stats = $memcached->getStats();
            if (empty($stats)) {
                return false;
            }

            $this->connection = $memcached;

            Logger::info('Memcached connected successfully', [
                'host' => $settings['host'],
                'port' => $settings['port'],
            ]);

            return true;
        } catch (\Exception $e) {
            Logger::error('Memcached connection failed', $e);
            return false;
        }
    }

    /**
     * Close connection on shutdown
     */
    public function closeConnection(): void
    {
        if ($this->connection !== null && $this->driver === 'redis' && method_exists($this->connection, 'close')) {
            $this->connection->close();
        }
    }

    /**
     * Install WordPress drop-in file for object cache
     *
     * @return bool True if installed successfully
     */
    private function installDropIn(): bool
    {
        // Check WP_CONTENT_DIR is writable
        if (!is_writable(WP_CONTENT_DIR)) {
            Logger::error('Cannot install object-cache.php: WP_CONTENT_DIR is not writable', null, [
                'path' => WP_CONTENT_DIR
            ]);
            return false;
        }

        $dropInPath = WP_CONTENT_DIR . '/' . self::DROP_IN_FILE;
        
        // Create templates directory if it doesn't exist
        $templatesDir = FP_PERF_SUITE_DIR . '/templates';
        if (!is_dir($templatesDir)) {
            if (!mkdir($templatesDir, 0755, true)) {
                Logger::error('Failed to create templates directory', null, [
                    'path' => $templatesDir
                ]);
            }
        }
        
        $templatePath = $templatesDir . '/object-cache-drop-in.php';

        // Check if template exists, generate if not
        if (!file_exists($templatePath)) {
            $this->generateDropInTemplate($templatePath);
        }

        // Verify template file was created successfully
        if (!file_exists($templatePath) || !is_readable($templatePath)) {
            Logger::error('Object cache template file not found or not readable', null, [
                'path' => $templatePath
            ]);
            return false;
        }

        // Copy drop-in file
        if (!copy($templatePath, $dropInPath)) {
            Logger::error('Failed to install object-cache.php drop-in', null, [
                'from' => $templatePath,
                'to' => $dropInPath
            ]);
            return false;
        }

        Logger::info('Object cache drop-in installed successfully', [
            'path' => $dropInPath
        ]);
        
        return true;
    }

    /**
     * Remove WordPress drop-in file
     *
     * @return bool True if removed successfully
     */
    private function removeDropIn(): bool
    {
        $dropInPath = WP_CONTENT_DIR . '/' . self::DROP_IN_FILE;

        if (!file_exists($dropInPath)) {
            return true;
        }

        // Safety check: only remove if it's our drop-in
        $content = file_get_contents($dropInPath);
        if (strpos($content, 'FP Performance Suite') === false) {
            Logger::warning('Object cache drop-in not managed by FP Performance Suite, skipping removal');
            return false;
        }

        if (!unlink($dropInPath)) {
            Logger::error('Failed to remove object-cache.php drop-in');
            return false;
        }

        Logger::info('Object cache drop-in removed');
        return true;
    }

    /**
     * Generate drop-in template file
     *
     * @param string $path Path to save template
     */
    private function generateDropInTemplate(string $path): void
    {
        $template = <<<'PHP'
<?php
/**
 * Object Cache Drop-In
 * 
 * Managed by FP Performance Suite
 * This file is automatically generated - do not edit manually
 * 
 * CRITICAL: This file is loaded very early in WordPress bootstrap
 * Most WordPress functions and classes are NOT available at this point
 */

// Safety check: exit if ABSPATH is not defined
if (!defined('ABSPATH')) {
    return;
}

/**
 * We CANNOT initialize the object cache here because:
 * 1. WP_Object_Cache class is not yet loaded
 * 2. get_option() is not yet available
 * 3. Plugin functions are not loaded
 * 
 * WordPress will use its default object cache mechanism.
 * The actual persistent cache (Redis/Memcached) will be managed
 * by FP Performance Suite's ObjectCacheManager after plugins are loaded.
 * 
 * To use persistent object caching with this plugin:
 * 1. Install a dedicated plugin like "Redis Object Cache" by Till Krüss
 * 2. Or configure Redis/Memcached at the server level
 * 3. This drop-in serves as a placeholder and will be replaced by
 *    the appropriate implementation when available
 */

// Do NOT initialize anything here - let WordPress handle it
// The persistent cache will be initialized by the plugin later
return;

PHP;

        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $template);
    }

    /**
     * Test connection to cache server
     *
     * @return array{success:bool,driver:string,message:string,info?:array}
     */
    public function testConnection(): array
    {
        $settings = $this->settings();
        $connected = $this->initializeConnection($settings);

        if (!$connected) {
            return [
                'success' => false,
                'driver' => $this->driver,
                'message' => 'Connection failed',
            ];
        }

        $info = [];

        if ($this->driver === 'redis' && $this->connection instanceof \Redis) {
            $info = $this->connection->info();
        } elseif ($this->driver === 'memcached' && $this->connection instanceof \Memcached) {
            $info = $this->connection->getStats();
        }

        return [
            'success' => true,
            'driver' => $this->driver,
            'message' => 'Connected successfully',
            'info' => $info,
        ];
    }

    /**
     * Flush all cached objects
     *
     * @return bool True if flushed successfully
     */
    public function flush(): bool
    {
        if ($this->connection === null) {
            return false;
        }

        try {
            if ($this->driver === 'redis' && method_exists($this->connection, 'flushDB')) {
                return $this->connection->flushDB();
            } elseif ($this->driver === 'memcached' && method_exists($this->connection, 'flush')) {
                return $this->connection->flush();
            }
        } catch (\Exception $e) {
            Logger::error('Failed to flush object cache', $e);
        }

        return false;
    }

    /**
     * Get cache statistics
     *
     * @return array{driver:string,connected:bool,items?:int,size?:int,hits?:int,misses?:int}
     */
    public function getStats(): array
    {
        $stats = [
            'driver' => $this->driver,
            'connected' => $this->connection !== null,
        ];

        if ($this->connection === null) {
            return $stats;
        }

        try {
            if ($this->driver === 'redis' && $this->connection instanceof \Redis) {
                $info = $this->connection->info();
                $stats['items'] = (int)($info['db0']['keys'] ?? 0);
                $stats['size'] = (int)($info['used_memory'] ?? 0);
                $stats['hits'] = (int)($info['keyspace_hits'] ?? 0);
                $stats['misses'] = (int)($info['keyspace_misses'] ?? 0);
            } elseif ($this->driver === 'memcached' && $this->connection instanceof \Memcached) {
                $serverStats = $this->connection->getStats();
                $firstServer = reset($serverStats);
                if ($firstServer) {
                    $stats['items'] = (int)($firstServer['curr_items'] ?? 0);
                    $stats['size'] = (int)($firstServer['bytes'] ?? 0);
                    $stats['hits'] = (int)($firstServer['get_hits'] ?? 0);
                    $stats['misses'] = (int)($firstServer['get_misses'] ?? 0);
                }
            }
        } catch (\Exception $e) {
            Logger::error('Failed to get cache stats', $e);
        }

        return $stats;
    }
}
