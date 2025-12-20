<?php

/**
 * Deactivation Service
 * 
 * Handles plugin deactivation logic.
 * Replaces Plugin::onDeactivate() and fp_perf_suite_deactivation_handler()
 *
 * @package FP\PerfSuite\Core\Bootstrap
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Bootstrap;

use FP\PerfSuite\Services\DB\Cleaner;

class DeactivationService
{
    /**
     * Handle plugin deactivation
     */
    public static function handle(): void
    {
        try {
            // Clear scheduled cron jobs
            wp_clear_scheduled_hook('fp_ps_cleanup');
            wp_clear_scheduled_hook('fp_ps_ml_analyze_patterns');
            wp_clear_scheduled_hook('fp_ps_ml_predict_issues');
            wp_clear_scheduled_hook('fp_ps_auto_tune');
            wp_clear_scheduled_hook('fp_ps_db_auto_report');
            
            // Clear database cleaner cron if exists
            if (class_exists(Cleaner::class)) {
                wp_clear_scheduled_hook(Cleaner::CRON_HOOK);
            }
            
            // Clear scheduled reports cron if exists
            if (class_exists('\FP\PerfSuite\Services\Reports\ScheduledReports')) {
                wp_clear_scheduled_hook(\FP\PerfSuite\Services\Reports\ScheduledReports::CRON_HOOK);
            }
            
            // Trigger deactivation hook for providers
            do_action('fp_ps_plugin_deactivated');
            
            // Log deactivation
            BootstrapService::safeLog('Plugin deactivated - all cron jobs cleared', 'INFO');
            
        } catch (\Throwable $e) {
            // Log error but don't block deactivation
            BootstrapService::safeLog('Errore disattivazione: ' . $e->getMessage());
        }
    }
}










