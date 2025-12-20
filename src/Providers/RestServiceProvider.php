<?php

/**
 * REST API Service Provider
 * 
 * Registers REST API routes and controllers
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class RestServiceProvider implements ServiceProviderInterface
{
    /**
     * Register REST services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // REST Routes (legacy, will delegate to controllers)
        $container->singleton(
            \FP\PerfSuite\Http\Routes::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Routes($c);
            }
        );
        
        // REST Controllers
        $container->singleton(
            \FP\PerfSuite\Http\Controllers\CacheController::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Controllers\CacheController($c);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Controllers\LogsController::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Controllers\LogsController($c);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Controllers\PresetController::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Controllers\PresetController($c);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Controllers\DatabaseController::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Controllers\DatabaseController($c);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Controllers\ScoreController::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Controllers\ScoreController($c);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Controllers\DebugController::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Controllers\DebugController($c);
            }
        );
        
        // AJAX Handlers
        $container->singleton(
            \FP\PerfSuite\Http\Ajax\RecommendationsAjax::class,
            function(Container $c) {
                // Convert Kernel\Container to ServiceContainer via adapter
                $serviceContainer = new \FP\PerfSuite\ServiceContainerAdapter($c);
                return new \FP\PerfSuite\Http\Ajax\RecommendationsAjax($serviceContainer);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Ajax\CriticalCssAjax::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Ajax\CriticalCssAjax($c);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Ajax\AIConfigAjax::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Ajax\AIConfigAjax($c);
            }
        );
        
        $container->singleton(
            \FP\PerfSuite\Http\Ajax\SafeOptimizationsAjax::class,
            function(Container $c) {
                return new \FP\PerfSuite\Http\Ajax\SafeOptimizationsAjax($c);
            }
        );
    }
    
    /**
     * Boot REST services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // Only load in admin or REST API requests
        if (!is_admin() && !(function_exists('wp_is_json_request') && wp_is_json_request())) {
            return;
        }
        
        // Boot REST routes
        $container->get(\FP\PerfSuite\Http\Routes::class)->boot();
        
        // Register AJAX handlers during AJAX requests
        if (defined('DOING_AJAX') && DOING_AJAX) {
            add_action('init', function() use ($container) {
                $container->get(\FP\PerfSuite\Http\Ajax\RecommendationsAjax::class)->register();
                $container->get(\FP\PerfSuite\Http\Ajax\CriticalCssAjax::class)->register();
                $container->get(\FP\PerfSuite\Http\Ajax\AIConfigAjax::class)->register();
                $container->get(\FP\PerfSuite\Http\Ajax\SafeOptimizationsAjax::class)->register();
            }, 5);
        }
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 60; // As per plan: RestServiceProvider priority 60
    }
    
    /**
     * Check if provider should load
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return is_admin() || (function_exists('wp_is_json_request') && wp_is_json_request());
    }
}
