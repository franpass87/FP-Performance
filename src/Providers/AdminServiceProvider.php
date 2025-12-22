<?php

/**
 * Admin Service Provider
 * 
 * Registers all admin-related services (menu, pages, assets)
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class AdminServiceProvider implements ServiceProviderInterface
{
    /**
     * Register admin services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Admin Assets
        $container->singleton(
            \FP\PerfSuite\Admin\Assets::class,
            fn() => new \FP\PerfSuite\Admin\Assets()
        );
        
        // Admin Menu
        $container->singleton(
            \FP\PerfSuite\Admin\Menu::class,
            function(Container $c) {
                return new \FP\PerfSuite\Admin\Menu($c);
            }
        );
        
        // Admin Bar
        $container->singleton(
            \FP\PerfSuite\Admin\AdminBar::class,
            function(Container $c) {
                return new \FP\PerfSuite\Admin\AdminBar($c);
            }
        );
        
        // Notice Manager
        $container->singleton(
            \FP\PerfSuite\Admin\NoticeManager::class,
            fn() => new \FP\PerfSuite\Admin\NoticeManager()
        );
        
        // Third Party Notice Hider
        $container->singleton(
            \FP\PerfSuite\Admin\Menu\ThirdPartyNoticeHider::class,
            fn() => new \FP\PerfSuite\Admin\Menu\ThirdPartyNoticeHider()
        );
        
        // Backend Optimizer
        $container->singleton(
            \FP\PerfSuite\Services\Admin\BackendOptimizer::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $backendOptimizer = new \FP\PerfSuite\Services\Admin\BackendOptimizer(true, true, true, true, $optionsRepo);
                // FIX: Registra immediatamente gli hook invece di aspettare init
                // Questo garantisce che gli hook CSS vengano registrati prima di admin_head
                $backendOptimizer->register();
                return $backendOptimizer;
            }
        );
    }
    
    /**
     * Boot admin services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        if (!is_admin()) {
            return;
        }
        
        // FIX: Forza l'istanziazione e la registrazione di BackendOptimizer su plugins_loaded
        // Questo garantisce che gli hook CSS vengano registrati prima di admin_head
        add_action('plugins_loaded', function() use ($container) {
            try {
                if ($container->has(\FP\PerfSuite\Services\Admin\BackendOptimizer::class)) {
                    $backendOptimizer = $container->get(\FP\PerfSuite\Services\Admin\BackendOptimizer::class);
                    if (method_exists($backendOptimizer, 'register')) {
                        $backendOptimizer->register();
                    }
                }
            } catch (\Throwable $e) {
                if (function_exists('error_log')) {
                    error_log('FP-Performance: Errore boot BackendOptimizer - ' . $e->getMessage());
                }
            }
        }, 1); // Priorità 1 per essere eseguito molto presto
        
        // Boot admin menu - con gestione errori sicura
        try {
            if ($container->has(\FP\PerfSuite\Admin\Menu::class)) {
                $container->get(\FP\PerfSuite\Admin\Menu::class)->boot();
            }
        } catch (\Throwable $e) {
            // Log errore ma continua con gli altri servizi
            if (function_exists('error_log')) {
                error_log('FP-Performance: Errore boot Menu - ' . $e->getMessage());
            }
        }
        
        // Boot admin assets - con gestione errori sicura
        try {
            if ($container->has(\FP\PerfSuite\Admin\Assets::class)) {
                $container->get(\FP\PerfSuite\Admin\Assets::class)->boot();
            }
        } catch (\Throwable $e) {
            // Log errore ma continua con gli altri servizi
            if (function_exists('error_log')) {
                error_log('FP-Performance: Errore boot Assets - ' . $e->getMessage());
            }
        }
        
        // Boot admin bar - con gestione errori sicura
        try {
            if ($container->has(\FP\PerfSuite\Admin\AdminBar::class)) {
                $container->get(\FP\PerfSuite\Admin\AdminBar::class)->boot();
                \FP\PerfSuite\Admin\AdminBar::registerActions();
            }
        } catch (\Throwable $e) {
            // Log errore ma continua
            if (function_exists('error_log')) {
                error_log('FP-Performance: Errore boot AdminBar - ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 80; // As per plan: AdminServiceProvider priority 80
    }
    
    /**
     * Check if provider should load
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return is_admin();
    }
}









