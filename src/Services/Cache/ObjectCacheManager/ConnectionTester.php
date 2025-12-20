<?php

namespace FP\PerfSuite\Services\Cache\ObjectCacheManager;

/**
 * Testa le connessioni ai backend di caching
 * 
 * @package FP\PerfSuite\Services\Cache\ObjectCacheManager
 * @author Francesco Passeri
 */
class ConnectionTester
{
    /**
     * Testa la connessione Redis
     */
    public function testRedisConnection(): array
    {
        try {
            $redis = new \Redis();
            
            $host = defined('WP_REDIS_HOST') ? WP_REDIS_HOST : '127.0.0.1';
            $port = defined('WP_REDIS_PORT') ? WP_REDIS_PORT : 6379;
            $timeout = defined('WP_REDIS_TIMEOUT') ? WP_REDIS_TIMEOUT : 1;
            
            if ($redis->connect($host, $port, $timeout)) {
                // Test autenticazione se configurata
                if (defined('WP_REDIS_PASSWORD') && WP_REDIS_PASSWORD) {
                    $redis->auth(WP_REDIS_PASSWORD);
                }
                
                // Test set/get
                $testKey = 'fp_perfsuite_test';
                $redis->set($testKey, 'test', 10);
                $result = $redis->get($testKey);
                $redis->del($testKey);
                
                return [
                    'status' => 'ok',
                    'host' => $host,
                    'port' => $port,
                    'info' => $redis->info('Server'),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        
        return [
            'status' => 'error',
            'message' => 'Impossibile connettersi a Redis',
        ];
    }

    /**
     * Testa la connessione Memcached
     */
    public function testMemcachedConnection(): array
    {
        try {
            $memcached = new \Memcached();
            
            $host = defined('WP_CACHE_HOST') ? WP_CACHE_HOST : '127.0.0.1';
            $port = defined('WP_CACHE_PORT') ? WP_CACHE_PORT : 11211;
            
            $memcached->addServer($host, $port);
            
            // Test set/get
            $testKey = 'fp_perfsuite_test';
            $memcached->set($testKey, 'test', 10);
            $result = $memcached->get($testKey);
            $memcached->delete($testKey);
            
            if ($result === 'test') {
                return [
                    'status' => 'ok',
                    'host' => $host,
                    'port' => $port,
                    'stats' => $memcached->getStats(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
        
        return [
            'status' => 'error',
            'message' => 'Impossibile connettersi a Memcached',
        ];
    }
}















