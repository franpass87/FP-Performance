<?php

namespace FP\PerfSuite\Admin\Pages\Database;

use FP\PerfSuite\Admin\Form\AbstractFormHandler;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Services\DB\PluginSpecificOptimizer;
use FP\PerfSuite\Services\DB\DatabaseReportService;
use FP\PerfSuite\Services\DB\QueryCacheManager;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Admin\NoticeManager;
use FP\PerfSuite\Utils\ErrorHandler;

/**
 * Gestisce le submission dei form per Database page
 * 
 * @package FP\PerfSuite\Admin\Pages\Database
 * @author Francesco Passeri
 */
class FormHandler extends AbstractFormHandler
{

    /**
     * Gestisce tutte le submission dei form
     * 
     * @param string $activeTab Tab attivo (opzionale, per backward compatibility)
     * @return string Messaggio di risultato
     */
    public function handle(string $activeTab = ''): string
    {
        if (!$this->isPost()) {
            return '';
        }

        // Se activeTab non specificato, cerca di determinarlo dal POST
        if (empty($activeTab)) {
            $activeTab = $this->sanitizeInput('active_tab', 'text') ?? 'operations';
        }

        $message = '';

        try {
            switch ($activeTab) {
                case 'operations':
                    $message = $this->handleOperationsForm();
                    break;
                case 'monitor':
                    $message = $this->handleMonitorForm();
                    break;
                case 'query_cache':
                    $message = $this->handleQueryCacheForm();
                    break;
            }
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Database form handling');
        }

        return $message;
    }

    /**
     * Gestisce form Operations tab
     */
    private function handleOperationsForm(): string
    {
        if (!$this->verifyNonce('fp_ps_db_nonce', 'fp-ps-db')) {
            return '';
        }

        try {
            $cleaner = $this->container->get(Cleaner::class);
            $objectCache = $this->container->get(ObjectCacheManager::class);
            
            $queryMonitor = $this->container->has(DatabaseQueryMonitor::class) 
                ? $this->container->get(DatabaseQueryMonitor::class) 
                : null;
            $optimizer = $this->container->has(DatabaseOptimizer::class) 
                ? $this->container->get(DatabaseOptimizer::class) 
                : null;
            $pluginOptimizer = class_exists('FP\\PerfSuite\\Services\\DB\\PluginSpecificOptimizer') 
                ? new PluginSpecificOptimizer() 
                : null;

            // Main Toggle
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'main_toggle') {
                $dbSettings = get_option('fp_ps_db', []);
                $dbSettings['enabled'] = $this->sanitizeInput('database_enabled', 'bool') ?? false;
                update_option('fp_ps_db', $dbSettings);
                NoticeManager::success(__('Database optimization settings saved successfully!', 'fp-performance-suite'));
                return $this->successMessage(__('Database optimization settings saved successfully!', 'fp-performance-suite'));
            }

            // Save settings
            if (isset($_POST['save_db_settings'])) {
                $cleaner->update([
                    'schedule' => $this->sanitizeInput('schedule', 'text') ?? 'manual',
                    'batch' => $this->sanitizeInput('batch', 'int') ?? 200,
                ]);
                NoticeManager::success(__('Database settings updated.', 'fp-performance-suite'));
                return $this->successMessage(__('Database settings updated.', 'fp-performance-suite'));
            }

            // Run cleanup
            if (isset($_POST['run_cleanup'])) {
                $scope = $this->sanitizeInput('cleanup_scope', 'array') ?? [];
                $dry = $this->sanitizeInput('dry_run', 'bool') ?? false;
                $batch = $this->sanitizeInput('batch', 'int') ?? 200;
                $cleaner->cleanup($scope, $dry, $batch);
                $message = $dry ? __('Dry run completed.', 'fp-performance-suite') : __('Cleanup completed.', 'fp-performance-suite');
                NoticeManager::success($message);
                return $this->successMessage($message);
            }

            // Query Monitor
            if (isset($_POST['enable_query_monitor']) && $queryMonitor) {
                $queryMonitor->updateSettings([
                    'enabled' => $this->sanitizeInput('query_monitor_enabled', 'bool') ?? false
                ]);
                NoticeManager::success(__('Query Monitor settings updated.', 'fp-performance-suite'));
                return $this->successMessage(__('Query Monitor settings updated.', 'fp-performance-suite'));
            }

            // Optimize tables
            if (isset($_POST['optimize_all_tables']) && $optimizer) {
                $results = $optimizer->optimizeAllTables();
                $optimizedCount = count($results['optimized'] ?? []);
                $message = sprintf(__('✅ Ottimizzazione completata! %d tabelle ottimizzate.', 'fp-performance-suite'), $optimizedCount);
                NoticeManager::success($message);
                return $message;
            }

            // Object Cache
            if (isset($_POST['enable_object_cache'])) {
                $settings = $objectCache->getSettings();
                $settings['enabled'] = true;
                $objectCache->updateSettings($settings);
                NoticeManager::success(__('Object Cache abilitato.', 'fp-performance-suite'));
                return __('Object Cache abilitato.', 'fp-performance-suite');
            }

            if (isset($_POST['disable_object_cache'])) {
                $settings = $objectCache->getSettings();
                $settings['enabled'] = false;
                $objectCache->updateSettings($settings);
                NoticeManager::success(__('Object Cache disabilitato.', 'fp-performance-suite'));
                return __('Object Cache disabilitato.', 'fp-performance-suite');
            }

            // Advanced operations
            if (isset($_POST['convert_to_innodb']) && $optimizer) {
                $table = $this->sanitizeInput('table_name', 'text') ?? '';
                $result = $optimizer->convertToInnoDB($table);
                NoticeManager::info($result['message']);
                return $result['message'];
            }

            if (isset($_POST['convert_charset']) && $optimizer) {
                $table = $this->sanitizeInput('table_name', 'text') ?? '';
                $result = $optimizer->convertCharset($table);
                NoticeManager::info($result['message']);
                return $result['message'];
            }

            if (isset($_POST['disable_autoload']) && $optimizer) {
                $option = $this->sanitizeInput('option_name', 'text') ?? '';
                $result = $optimizer->disableAutoload($option);
                NoticeManager::info($result['message']);
                return $result['message'];
            }

            // Plugin-specific cleanup
            if (isset($_POST['cleanup_woocommerce']) && $pluginOptimizer) {
                $tasks = $this->sanitizeInput('wc_tasks', 'array') ?? [];
                $pluginOptimizer->cleanupWooCommerce($tasks);
                NoticeManager::success(__('WooCommerce cleanup completato.', 'fp-performance-suite'));
                return $this->successMessage(__('WooCommerce cleanup completato.', 'fp-performance-suite'));
            }

            if (isset($_POST['cleanup_elementor']) && $pluginOptimizer) {
                $tasks = $this->sanitizeInput('elementor_tasks', 'array') ?? [];
                $pluginOptimizer->cleanupElementor($tasks);
                NoticeManager::success(__('Elementor cleanup completato.', 'fp-performance-suite'));
                return $this->successMessage(__('Elementor cleanup completato.', 'fp-performance-suite'));
            }

        } catch (\Throwable $e) {
            return $this->handleError($e, 'Database operations form');
        }

        return '';
    }

    /**
     * Gestisce form Monitor tab
     */
    private function handleMonitorForm(): string
    {
        if (!$this->verifyNonce('fp_ps_query_monitor_nonce', 'fp_ps_query_monitor')) {
            return '';
        }

        try {
            $queryMonitor = $this->container->get(DatabaseQueryMonitor::class);
            
            $settings = [
                'enabled' => $this->sanitizeInput('enabled', 'bool') ?? false,
                'slow_query_threshold' => (float) ($this->sanitizeInput('slow_query_threshold', 'text') ?? 0.005),
                'log_duplicates' => $this->sanitizeInput('log_duplicates', 'bool') ?? false,
            ];
            
            $queryMonitor->updateSettings($settings);
            NoticeManager::success(__('Configurazione Query Monitor salvata con successo!', 'fp-performance-suite'));
            return $this->successMessage(__('Configurazione Query Monitor salvata con successo!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Query Monitor form');
        }
    }

    /**
     * Gestisce form Query Cache tab
     */
    private function handleQueryCacheForm(): string
    {
        if (!$this->verifyNonce('fp_ps_query_cache_nonce', 'fp_ps_query_cache')) {
            return '';
        }

        try {
            $queryCache = $this->container->get(QueryCacheManager::class);
            
            $settings = [
                'enabled' => $this->sanitizeInput('enabled', 'bool') ?? false,
                'ttl' => $this->sanitizeInput('ttl', 'int') ?? 3600,
                'cache_select_only' => $this->sanitizeInput('cache_select_only', 'bool') ?? false,
                'max_cache_size' => $this->sanitizeInput('max_cache_size', 'int') ?? 1000,
            ];
            
            $queryCache->updateSettings($settings);
            NoticeManager::success(__('Configurazione Query Cache salvata con successo!', 'fp-performance-suite'));
            return $this->successMessage(__('Configurazione Query Cache salvata con successo!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Query Cache form');
        }
    }
}
















