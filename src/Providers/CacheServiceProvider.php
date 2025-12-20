<?php

/**
 * Cache Service Provider
 * 
 * Registers all cache-related services
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * Register cache services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Page Cache
        $container->singleton(
            \FP\PerfSuite\Services\Cache\PageCache::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Cache\PageCache(null, 3600, $optionsRepo);
            }
        );
        
        // Browser Cache
        $container->singleton(
            \FP\PerfSuite\Services\Cache\BrowserCache::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Cache\BrowserCache($optionsRepo);
            }
        );
        
        // Headers Cache
        $container->singleton(
            \FP\PerfSuite\Services\Cache\Headers::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Cache\Headers(3600, $optionsRepo);
            }
        );
        
        // Object Cache Manager
        $container->singleton(
            \FP\PerfSuite\Services\Cache\ObjectCacheManager::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Logging\LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\Cache\ObjectCacheManager($optionsRepo, $logger);
            }
        );
        
        // Edge Cache Manager
        $container->singleton(
            \FP\PerfSuite\Services\Cache\EdgeCacheManager::class,
            fn() => new \FP\PerfSuite\Services\Cache\EdgeCacheManager()
        );
        
        // Edge Cache Providers
        $container->singleton(
            \FP\PerfSuite\Services\Cache\EdgeCache\CloudflareProvider::class,
            function(Container $c) {
                $settings = $c->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class)->settings()['cloudflare'] ?? [];
                return new \FP\PerfSuite\Services\Cache\EdgeCache\CloudflareProvider(
                    $settings['api_token'] ?? '',
                    $settings['zone_id'] ?? '',
                    $settings['email'] ?? ''
                );
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Cache\EdgeCache\CloudFrontProvider::class,
            function(Container $c) {
                $settings = $c->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class)->settings()['cloudfront'] ?? [];
                return new \FP\PerfSuite\Services\Cache\EdgeCache\CloudFrontProvider(
                    $settings['access_key_id'] ?? '',
                    $settings['secret_access_key'] ?? '',
                    $settings['distribution_id'] ?? '',
                    $settings['region'] ?? 'us-east-1'
                );
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Services\Cache\EdgeCache\FastlyProvider::class,
            function(Container $c) {
                $settings = $c->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class)->settings()['fastly'] ?? [];
                return new \FP\PerfSuite\Services\Cache\EdgeCache\FastlyProvider(
                    $settings['api_key'] ?? '',
                    $settings['service_id'] ?? ''
                );
            }
        );
    }
    
    /**
     * Boot cache services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Cache services will be initialized by ServiceLoader based on enabled state
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 40; // As per plan: CacheServiceProvider priority 40
    }
    
    /**
     * Check if provider should load
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return true;
    }
}









