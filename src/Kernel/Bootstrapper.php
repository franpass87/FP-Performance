<?php

/**
 * Plugin Bootstrapper
 * 
 * Handles plugin bootstrap, activation, deactivation, and environment checks.
 * This is the entry point that orchestrates the entire plugin lifecycle.
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

use FP\PerfSuite\Core\Bootstrap\BootstrapService;
use FP\PerfSuite\Core\Environment\EnvironmentChecker;
use FP\PerfSuite\Kernel\ActivationHandler;
use FP\PerfSuite\Kernel\DeactivationHandler;
use FP\PerfSuite\Utils\ErrorHandler;

class Bootstrapper
{
    /** @var bool Whether bootstrapper has been initialized */
    private static bool $initialized = false;
    
    /**
     * Bootstrap the plugin
     * 
     * This is the main entry point called from the main plugin file.
     */
    public static function bootstrap(): void
    {
        if (self::$initialized) {
            return;
        }
        
        // 1. Initialize environment guards early (before WordPress is fully loaded)
        if (class_exists('\FP\PerfSuite\Utils\EnvironmentGuard')) {
            \FP\PerfSuite\Utils\EnvironmentGuard::bootstrap();
        }

        // 2. Environment checks
        // Use container if available, otherwise create new instance (for early bootstrap)
        $checker = null;
        try {
            if (class_exists('\FP\PerfSuite\Kernel\PluginKernel')) {
                $container = \FP\PerfSuite\Kernel\PluginKernel::container();
                if ($container->has(\FP\PerfSuite\Core\Environment\EnvironmentChecker::class)) {
                    $checker = $container->get(\FP\PerfSuite\Core\Environment\EnvironmentChecker::class);
                }
            }
        } catch (\Throwable $e) {
            // Container not ready yet, fallback to new instance
        }
        
        if ($checker === null) {
            $checker = new EnvironmentChecker();
        }
        
        if (!$checker->check()) {
            $errors = $checker->getErrors();
            if (!empty($errors)) {
                add_action('admin_notices', function() use ($errors) {
                    foreach ($errors as $error) {
                        printf(
                            '<div class="notice notice-error"><p><strong>FP Performance Suite:</strong> %s</p></div>',
                            esc_html($error)
                        );
                    }
                });
            }
            return; // Fail gracefully
        }

        // 3. Register activation/deactivation hooks
        register_activation_hook(
            FP_PERF_SUITE_FILE,
            [ActivationHandler::class, 'handle']
        );
        register_deactivation_hook(
            FP_PERF_SUITE_FILE,
            [DeactivationHandler::class, 'handle']
        );

        // 4. Enable deprecated trace if needed
        BootstrapService::enableDeprecatedTrace();

        // 5. Boot kernel on init (when WordPress is fully loaded)
        add_action('init', function() {
            try {
                // Allow custom provider registration via hook before boot
                do_action('fp_ps_before_kernel_boot');
                
                // Boot kernel (will auto-discover and register providers)
                PluginKernel::boot();
                
                // Allow custom actions after kernel boot
                do_action('fp_ps_after_kernel_boot', PluginKernel::container());
            } catch (\Throwable $e) {
                self::handleBootstrapError($e);
            }
        }, 1);

        // Action scheduler optimizations for shared hosting
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

        // Enable SAVEQUERIES for admin if configured
        add_action('plugins_loaded', function() {
            if (!defined('SAVEQUERIES')) {
                $saveQueriesAdminOnly = get_option('fp_ps_savequeries_admin_only', false);
                
                if ($saveQueriesAdminOnly && 
                    function_exists('is_user_logged_in') && 
                    function_exists('current_user_can') && 
                    is_user_logged_in() && 
                    current_user_can('manage_options')) {
                    define('SAVEQUERIES', true);
                }
            }
        }, 1);

        // 6. Register deprecated global function wrappers for backward compatibility
        self::registerGlobalFunctionWrappers();

        self::$initialized = true;
    }
    
    /**
     * Register deprecated global function wrappers
     * 
     * These wrappers maintain backward compatibility with old code that uses
     * global functions. They will be removed in a future version.
     */
    private static function registerGlobalFunctionWrappers(): void
    {
        if (!function_exists('fp_perf_suite_enable_deprecated_trace')) {
            /**
             * @deprecated Use BootstrapService::enableDeprecatedTrace() instead
             */
            function fp_perf_suite_enable_deprecated_trace(): void
            {
                \FP\PerfSuite\Core\Bootstrap\BootstrapService::enableDeprecatedTrace();
            }
        }

        if (!function_exists('fp_perf_suite_is_db_available')) {
            /**
             * @deprecated Use BootstrapService::isDatabaseAvailable() instead
             */
            function fp_perf_suite_is_db_available(): bool
            {
                return \FP\PerfSuite\Core\Bootstrap\BootstrapService::isDatabaseAvailable();
            }
        }

        if (!function_exists('fp_perf_suite_safe_log')) {
            /**
             * @deprecated Use BootstrapService::safeLog() or LoggerInterface instead
             */
            function fp_perf_suite_safe_log(string $message, string $level = 'ERROR'): void
            {
                \FP\PerfSuite\Core\Bootstrap\BootstrapService::safeLog($message, $level);
            }
        }
    }
    
    /**
     * Handle bootstrap errors
     * 
     * @param \Throwable $e Exception that occurred during bootstrap
     */
    private static function handleBootstrapError(\Throwable $e): void
    {
        // Use centralized error handler
        ErrorHandler::handle($e, 'Plugin bootstrap', true);
    }
}
