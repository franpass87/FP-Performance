<?php

/**
 * WP-CLI Service Provider
 * 
 * Registers WP-CLI commands
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class CliServiceProvider implements ServiceProviderInterface
{
    /**
     * Register CLI services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // CLI Commands are registered via static methods, no container binding needed
    }
    
    /**
     * Boot CLI services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        if (!defined('WP_CLI') || !WP_CLI) {
            return;
        }
        
        if (!class_exists('WP_CLI')) {
            return;
        }
        
        // Register WP-CLI commands
        \WP_CLI::add_command('fp-performance cache', [\FP\PerfSuite\Cli\Commands::class, 'cache'], [
            'shortdesc' => 'Manage page cache',
        ]);
        
        \WP_CLI::add_command('fp-performance db', [\FP\PerfSuite\Cli\Commands::class, 'db'], [
            'shortdesc' => 'Database cleanup operations',
        ]);
        
        \WP_CLI::add_command('fp-performance score', [\FP\PerfSuite\Cli\Commands::class, 'score'], [
            'shortdesc' => 'Calculate performance score',
        ]);
        
        \WP_CLI::add_command('fp-performance info', [\FP\PerfSuite\Cli\Commands::class, 'info'], [
            'shortdesc' => 'Show plugin information',
        ]);
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 50; // As per plan: CliServiceProvider priority 50
    }
    
    /**
     * Check if provider should load
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }
}









