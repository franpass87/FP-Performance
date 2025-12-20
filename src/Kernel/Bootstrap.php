<?php

/**
 * Bootstrap
 * 
 * Handles plugin bootstrap, environment setup, and early initialization.
 * This class sets up the environment before the kernel boots.
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

class Bootstrap
{
    /** @var bool Whether bootstrap has been initialized */
    private static bool $initialized = false;
    
    /**
     * Bootstrap the plugin environment
     * 
     * Sets up environment guards, error handlers, and action scheduler filters.
     * This is called before PluginKernel::boot().
     */
    public static function bootstrap(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // 1. Environment guards
        self::setupEnvironmentGuards();
        
        // 2. Error handler setup
        self::setupErrorHandlers();
        
        // 3. Action scheduler filters
        self::setupActionSchedulerFilters();
        
        self::$initialized = true;
    }
    
    /**
     * Setup environment guards
     * 
     * @return void
     */
    private static function setupEnvironmentGuards(): void
    {
        if (class_exists('\FP\PerfSuite\Utils\EnvironmentGuard')) {
            \FP\PerfSuite\Utils\EnvironmentGuard::bootstrap();
        }
    }
    
    /**
     * Setup error handlers
     * 
     * @return void
     */
    private static function setupErrorHandlers(): void
    {
        // Enable deprecated trace if needed
        if (!function_exists('fp_perf_suite_enable_deprecated_trace')) {
            return;
        }
        
        fp_perf_suite_enable_deprecated_trace();
        add_action('init', 'fp_perf_suite_enable_deprecated_trace', 0);
    }
    
    /**
     * Setup action scheduler filters
     * 
     * Limits action scheduler batch size and concurrent batches for shared hosting.
     * 
     * @return void
     */
    private static function setupActionSchedulerFilters(): void
    {
        add_filter(
            'action_scheduler_queue_runner_batch_size',
            static function ($size) {
                $size = (int) $size;
                if ($size > 10) {
                    return 10;
                }
                return $size;
            }
        );
        
        add_filter(
            'action_scheduler_queue_runner_concurrent_batches',
            static function ($batches) {
                $batches = (int) $batches;
                if ($batches > 1) {
                    return 1;
                }
                return $batches;
            }
        );
    }
}










