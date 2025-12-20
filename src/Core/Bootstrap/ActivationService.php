<?php

/**
 * Activation Service
 * 
 * Handles plugin activation logic.
 * Replaces Plugin::onActivate() and fp_perf_suite_activation_handler()
 *
 * @package FP\PerfSuite\Core\Bootstrap
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Bootstrap;

use FP\PerfSuite\Initialization\SystemChecker;
use FP\PerfSuite\Initialization\DirectoryManager;
use FP\PerfSuite\Initialization\ActivationErrorHandler;
use FP\PerfSuite\Initialization\DefaultOptionsManager;

class ActivationService
{
    /**
     * Handle plugin activation
     * 
     * Note: WordPress activation hooks don't pass parameters, so we use the constant
     */
    public static function handle(): void
    {
        try {
            // Increase memory limit temporarily for activation
            @ini_set('memory_limit', '768M');
            
            // Run system checks
            $systemChecker = new SystemChecker();
            $systemChecker->performChecks();
            
            // Create required directories
            $directoryManager = new DirectoryManager();
            $directoryManager->ensureRequiredDirectories();
            
            // Set default options
            $defaultOptionsManager = new DefaultOptionsManager();
            $defaultOptionsManager->ensureDefaults();
            
            // Save version
            $version = defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : '1.8.0';
            update_option('fp_perfsuite_version', $version, false);
            
            // Clear any previous activation errors
            delete_option('fp_perfsuite_activation_error');
            
            // Trigger activation hook for providers
            do_action('fp_ps_plugin_activated', $version);
            
        } catch (\Throwable $e) {
            // Save error for display in admin
            $errorHandler = new ActivationErrorHandler();
            $errorHandler->saveError($e);
            
            // Log error safely
            BootstrapService::safeLog('Errore attivazione: ' . $e->getMessage());
            
            // Show error to user
            wp_die(
                sprintf(
                    '<h1>Errore di Attivazione Plugin</h1><p><strong>Messaggio:</strong> %s</p><p><strong>File:</strong> %s:%d</p>',
                    esc_html($e->getMessage()),
                    esc_html($e->getFile()),
                    $e->getLine()
                ),
                'Errore di Attivazione',
                ['response' => 500]
            );
        }
    }
}

