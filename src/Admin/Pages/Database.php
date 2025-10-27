<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
use FP\PerfSuite\Services\DB\PluginSpecificOptimizer;
use FP\PerfSuite\Services\DB\DatabaseReportService;
use FP\PerfSuite\Services\DB\QueryCacheManager;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;

use function __;
use function array_map;
use function date_i18n;
use function get_option;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function number_format_i18n;
use function printf;
use function sprintf;
use function sanitize_text_field;
use function selected;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Database extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-database';
    }

    public function title(): string
    {
        return __('Database Optimization', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Database', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Wrapper di sicurezza generale per prevenire pagina vuota
        try {
            // Determina la tab attiva
            $activeTab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'operations';
            $validTabs = ['operations', 'monitor', 'query_cache'];
            if (!in_array($activeTab, $validTabs, true)) {
                $activeTab = 'operations';
            }
            
            // Gestione dei form submissions
            $this->handleFormSubmissions($activeTab);
            
            ob_start();
            
            // Render tabs navigation
            $this->renderTabsNavigation($activeTab);
            
            // Render content based on active tab
            switch ($activeTab) {
                case 'monitor':
                    echo $this->renderQueryMonitorTab();
                    break;
                case 'query_cache':
                    echo $this->renderQueryCacheTab();
                    break;
                default:
                    echo $this->renderOperationsTab();
                    break;
            }
            
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            // Log dell'errore se WP_DEBUG_LOG è abilitato
            if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
                error_log('FP Performance Suite - Database Page Error: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
            }
            
            return $this->renderError(
                'Errore durante il rendering della pagina: ' . $e->getMessage() . 
                ' (File: ' . basename($e->getFile()) . ':' . $e->getLine() . ')'
            );
        }
    }
    
    /**
     * Renderizza il contenuto effettivo della pagina
     */
    private function renderContent(): string
    {
        // Error handling robusto per prevenire pagina vuota
        try {
            $cleaner = $this->container->get(Cleaner::class);
        } catch (\Exception $e) {
            return $this->renderError('Cleaner service non disponibile: ' . $e->getMessage());
        }
        
        // Servizi avanzati - verifica disponibilità (v1.4.0)
        $queryMonitor = $this->container->has(DatabaseQueryMonitor::class) 
            ? $this->container->get(DatabaseQueryMonitor::class) 
            : null;
        $optimizer = $this->container->has(DatabaseOptimizer::class) 
            ? $this->container->get(DatabaseOptimizer::class) 
            : null;
        
        try {
            $objectCache = $this->container->get(ObjectCacheManager::class);
        } catch (\Exception $e) {
            return $this->renderError('ObjectCacheManager service non disponibile: ' . $e->getMessage());
        }
        
        // Crea istanze dirette se le classi esistono
        $pluginOptimizer = class_exists('FP\\PerfSuite\\Services\\DB\\PluginSpecificOptimizer') 
            ? new PluginSpecificOptimizer() 
            : null;
        $reportService = class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseReportService') 
            ? new DatabaseReportService() 
            : null;
        
        $message = '';
        $results = [];
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_db_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_db_nonce']), 'fp-ps-db')) {
            
            // Main Toggle for Database Optimization
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'main_toggle') {
                $dbSettings['enabled'] = !empty($_POST['database_enabled']);
                update_option('fp_ps_db', $dbSettings);
                $message = __('Database optimization settings saved successfully!', 'fp-performance-suite');
            }
            if (isset($_POST['save_db_settings'])) {
                $cleaner->update([
                    'schedule' => sanitize_text_field($_POST['schedule'] ?? 'manual'),
                    'batch' => (int) ($_POST['batch'] ?? 200),
                ]);
                $message = __('Database settings updated.', 'fp-performance-suite');
            }
            if (isset($_POST['run_cleanup'])) {
                $scope = array_map('sanitize_text_field', (array) ($_POST['cleanup_scope'] ?? []));
                $dry = !empty($_POST['dry_run']);
                $results = $cleaner->cleanup($scope, $dry, (int) ($_POST['batch'] ?? 200));
                $message = $dry ? __('Dry run completed.', 'fp-performance-suite') : __('Cleanup completed.', 'fp-performance-suite');
            }
            if (isset($_POST['enable_query_monitor']) && $queryMonitor) {
                $queryMonitor->updateSettings(['enabled' => !empty($_POST['query_monitor_enabled'])]);
                $message = __('Query Monitor settings updated.', 'fp-performance-suite');
            }
            if (isset($_POST['optimize_all_tables']) && $optimizer) {
                $results = $optimizer->optimizeAllTables();
                $optimizedCount = count($results['optimized'] ?? []);
                $message = sprintf(__('✅ Ottimizzazione completata! %d tabelle ottimizzate.', 'fp-performance-suite'), $optimizedCount);
            }
            if (isset($_POST['enable_object_cache'])) {
                $settings = $objectCache->getSettings();
                $settings['enabled'] = true;
                $objectCache->updateSettings($settings);
                $message = __('Object Cache abilitato.', 'fp-performance-suite');
            }
            if (isset($_POST['disable_object_cache'])) {
                $settings = $objectCache->getSettings();
                $settings['enabled'] = false;
                $objectCache->updateSettings($settings);
                $message = __('Object Cache disabilitato.', 'fp-performance-suite');
            }
            // Nuove azioni - verifica disponibilità servizi
            if (isset($_POST['convert_to_innodb']) && $optimizer) {
                $table = sanitize_text_field($_POST['table_name'] ?? '');
                $result = $optimizer->convertToInnoDB($table);
                $message = $result['message'];
            }
            if (isset($_POST['convert_charset']) && $optimizer) {
                $table = sanitize_text_field($_POST['table_name'] ?? '');
                $result = $optimizer->convertCharset($table);
                $message = $result['message'];
            }
            if (isset($_POST['disable_autoload']) && $optimizer) {
                $option = sanitize_text_field($_POST['option_name'] ?? '');
                $result = $optimizer->disableAutoload($option);
                $message = $result['message'];
            }
            if (isset($_POST['cleanup_woocommerce']) && $pluginOptimizer) {
                $tasks = array_map('sanitize_text_field', (array) ($_POST['wc_tasks'] ?? []));
                $results = $pluginOptimizer->cleanupWooCommerce($tasks);
                $message = __('WooCommerce cleanup completato.', 'fp-performance-suite');
            }
            if (isset($_POST['cleanup_elementor']) && $pluginOptimizer) {
                $tasks = array_map('sanitize_text_field', (array) ($_POST['elementor_tasks'] ?? []));
                $results = $pluginOptimizer->cleanupElementor($tasks);
                $message = __('Elementor cleanup completato.', 'fp-performance-suite');
            }
            if (isset($_POST['create_snapshot']) && $reportService) {
                $label = sanitize_text_field($_POST['snapshot_label'] ?? '');
                $reportService->createSnapshot($label);
                $message = __('Snapshot creato con successo.', 'fp-performance-suite');
            }
            if (isset($_POST['export_report']) && $reportService) {
                $format = sanitize_text_field($_POST['report_format'] ?? 'json');
                $this->downloadReport($reportService, $format);
                return ''; // Download triggered
            }
        }
        $settings = $cleaner->settings();
        $status = $cleaner->status();
        $overhead = $status['overhead_mb'];
        $lastRun = empty($status['last_run'])
            ? __('Never', 'fp-performance-suite')
            : date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $status['last_run']);
        $tasks = [
            'revisions' => __('Post revisions', 'fp-performance-suite'),
            'auto_drafts' => __('Auto drafts', 'fp-performance-suite'),
            'trash_posts' => __('Trashed posts', 'fp-performance-suite'),
            'spam_comments' => __('Spam/trashed comments', 'fp-performance-suite'),
            'expired_transients' => __('Expired transients', 'fp-performance-suite'),
            'orphan_postmeta' => __('Orphan post meta', 'fp-performance-suite'),
            'orphan_termmeta' => __('Orphan term meta', 'fp-performance-suite'),
            'orphan_usermeta' => __('Orphan user meta', 'fp-performance-suite'),
            'optimize_tables' => __('Optimize tables', 'fp-performance-suite'),
        ];
        // Ottieni dati per le sezioni
        $queryAnalysis = $queryMonitor ? $queryMonitor->getLastAnalysis() : null;
        // Ricalcola sempre i dati del database per assicurarsi che siano aggiornati
        $dbAnalysis = $optimizer ? $optimizer->analyze() : ['database_size' => ['total_mb' => 0], 'table_analysis' => ['total_tables' => 0, 'total_overhead_mb' => 0, 'tables' => []], 'recommendations' => []];
        
        // Object Cache - usa metodi esistenti
        $cacheSettings = $objectCache->getSettings();
        $cacheStats = $objectCache->getStatistics();
        $cacheEnabled = $cacheSettings['enabled'] ?? false;
        // Il driver viene dal backend disponibile, non dalle settings
        $cacheDriver = $objectCache->getAvailableBackend() ?? 'none';
        
        // Crea struttura compatibile per la vista
        $cacheInfo = [
            'available' => $this->isObjectCacheAvailable(),
            'enabled' => $cacheEnabled,
            'name' => ucfirst($cacheDriver),
            'description' => $this->getDriverDescription($cacheDriver),
        ];
        
        // Nuovi dati avanzati - solo se i servizi sono disponibili
        $fragmentation = $optimizer ? $optimizer->analyzeFragmentation() : ['fragmented_tables' => [], 'total_wasted_mb' => 0];
        $missingIndexes = $optimizer ? $optimizer->analyzeMissingIndexes() : ['suggestions' => [], 'total_suggestions' => 0, 'high_priority' => 0];
        $storageEngines = $optimizer ? $optimizer->analyzeStorageEngines() : ['myisam_tables' => []];
        $charset = $optimizer ? $optimizer->analyzeCharset() : ['outdated_tables' => []];
        $autoloadDetailed = $optimizer ? $optimizer->analyzeAutoloadDetailed() : ['large_options' => [], 'total_size_mb' => 0];
        $pluginOpportunities = $pluginOptimizer ? $pluginOptimizer->analyzeInstalledPlugins() : ['opportunities' => []];
        $healthScore = $reportService ? $reportService->getHealthScore() : ['score' => 0, 'grade' => 'N/A', 'status' => 'unknown', 'issues' => [], 'recommendations' => []];
        $trends = $reportService ? $reportService->analyzeTrends() : ['status' => 'insufficient_data'];
        
        // Tab corrente
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'operations';
        $valid_tabs = ['operations', 'analysis', 'reports'];
        if (!in_array($current_tab, $valid_tabs, true)) {
            $current_tab = 'operations';
        }
        
        // Mantieni il tab dopo il POST
        if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['current_tab'])) {
            $current_tab = sanitize_key($_POST['current_tab']);
        }

        ob_start();
        ?>
        
        <!-- Pannello Introduttivo -->
        <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                💾 Ottimizzazione Database
            </h2>
            <p style="font-size: 18px; line-height: 1.6; margin-bottom: 25px; opacity: 0.95;">
                <?php esc_html_e('Il database è il cuore del tuo WordPress. Queste operazioni lo mantengono veloce, leggero e ottimizzato.', 'fp-performance-suite'); ?>
            </p>
            
            <div class="fp-ps-grid three" style="gap: 20px;">
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">🧹</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Pulizia', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Rimuove dati obsoleti come revisioni, bozze automatiche, spam e transient scaduti', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">⚡</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Ottimizzazione', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Riorganizza tabelle, converte a InnoDB, aggiunge indici e recupera spazio sprecato', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">📊</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Monitoraggio', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Analizza query lente, identifica problemi e fornisce raccomandazioni proattive', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Alert Sicurezza -->
        <div class="notice notice-info inline" style="margin-bottom: 25px; border-left-color: #10b981;">
            <p style="margin: 0.5em 0;">
                <strong>🛡️ <?php esc_html_e('Sicurezza Garantita:', 'fp-performance-suite'); ?></strong> 
                <?php esc_html_e('Viene creato automaticamente un backup completo prima di ogni operazione critica.', 'fp-performance-suite'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-logs')); ?>" style="text-decoration: none;">
                    <?php esc_html_e('Visualizza log operazioni →', 'fp-performance-suite'); ?>
                </a>
            </p>
        </div>
        
        <!-- Navigazione Tabs -->
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="?page=fp-performance-suite-database&tab=operations" 
               class="nav-tab <?php echo $current_tab === 'operations' ? 'nav-tab-active' : ''; ?>">
                🔧 <?php esc_html_e('Operations & Cleanup', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-database&tab=analysis" 
               class="nav-tab <?php echo $current_tab === 'analysis' ? 'nav-tab-active' : ''; ?>">
                📊 <?php esc_html_e('Advanced Analysis', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-database&tab=reports" 
               class="nav-tab <?php echo $current_tab === 'reports' ? 'nav-tab-active' : ''; ?>">
                📈 <?php esc_html_e('Reports & Plugins', 'fp-performance-suite'); ?>
            </a>
        </div>

        <!-- Tab Description -->
        <?php if ($current_tab === 'operations') : ?>
            <div class="fp-ps-tab-description success">
                <p>
                    <strong>🔧 Operations:</strong> 
                    <?php esc_html_e('Esegui pulizia database, configura scheduler automatico e monitora le query per identificare colli di bottiglia.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php elseif ($current_tab === 'analysis') : ?>
            <div class="fp-ps-tab-description info">
                <p>
                    <strong>📊 Analysis:</strong> 
                    <?php esc_html_e('Analisi approfondita del database: Health Score, frammentazione, indici mancanti, storage engines e object caching.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php elseif ($current_tab === 'reports') : ?>
            <div class="fp-ps-tab-description info">
                <p>
                    <strong>📈 Reports & Plugins:</strong> 
                    <?php esc_html_e('Report e trend di crescita database, pulizie specifiche per WooCommerce, Elementor e altri plugin.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php endif; ?>

        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>

        <!-- Main Toggle for Database Optimization -->
        <div class="fp-ps-card" style="margin-bottom: 20px; background: #f8f9fa; border: 2px solid #e9ecef;">
            <h2 style="margin-top: 0; color: #495057;">⚡ <?php esc_html_e('Database Optimization Control', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-database">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="form_type" value="main_toggle" />
                
                <label class="fp-ps-toggle" style="display: flex; align-items: flex-start; gap: 10px; font-size: 16px; margin-bottom: 15px;">
                    <input type="checkbox" name="database_enabled" value="1" <?php checked(!empty($dbSettings['enabled'])); ?> style="transform: scale(1.2); margin-top: 2px; flex-shrink: 0;" />
                    <span class="info" style="text-align: left; flex: 1;">
                        <strong style="display: block;"><?php esc_html_e('Enable Database Optimization', 'fp-performance-suite'); ?></strong>
                        <small style="color: #6c757d; display: block; margin-top: 4px;">
                            <?php esc_html_e('Master switch to enable/disable all database optimization features. When disabled, no database optimization will be applied.', 'fp-performance-suite'); ?>
                        </small>
                    </span>
                </label>
                
                <div style="background: #e3f2fd; border-left: 4px solid #2196f3; padding: 10px; margin: 10px 0;">
                    <p style="margin: 0; font-size: 14px; color: #1565c0;">
                        <strong>ℹ️ <?php esc_html_e('Note:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('This is the main control for database optimization. Individual features in the sections below will only work when this is enabled.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <button type="submit" class="button button-primary" style="margin-top: 10px;">
                    <?php esc_html_e('Save Settings', 'fp-performance-suite'); ?>
                </button>
            </form>
        </div>
        
        <!-- TAB: Operations & Cleanup -->
        <div class="fp-ps-tab-content" data-tab="operations" style="display: <?php echo $current_tab === 'operations' ? 'block' : 'none'; ?>;">
        
        <?php if (!$optimizer || !$reportService || !$pluginOptimizer) : ?>
            <div class="notice notice-warning">
                <h3><?php esc_html_e('⚠️ Funzionalità Avanzate Non Disponibili', 'fp-performance-suite'); ?></h3>
                <p><?php esc_html_e('Alcune funzionalità avanzate di ottimizzazione database non sono disponibili. Per abilitarle, carica questi file sul server:', 'fp-performance-suite'); ?></p>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <?php if (!$optimizer) : ?>
                        <li><code>src/Services/DB/DatabaseOptimizer.php</code></li>
                    <?php endif; ?>
                    <?php if (!$reportService) : ?>
                        <li><code>src/Services/DB/DatabaseReportService.php</code></li>
                    <?php endif; ?>
                    <?php if (!$pluginOptimizer) : ?>
                        <li><code>src/Services/DB/PluginSpecificOptimizer.php</code></li>
                    <?php endif; ?>
                    <?php if (!$queryMonitor) : ?>
                        <li><code>src/Services/DB/DatabaseQueryMonitor.php</code></li>
                    <?php endif; ?>
                </ul>
                <p><?php esc_html_e('Dopo aver caricato i file, riattiva il plugin per vedere le nuove funzionalità.', 'fp-performance-suite'); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Query Monitor Section -->
        <section class="fp-ps-card">
            <h2>
                <?php esc_html_e('Database Query Monitor', 'fp-performance-suite'); ?>
                <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 8px; font-size: 18px;" title="<?php esc_attr_e('Query Monitor: strumento che registra tutte le query SQL eseguite dal sito per identificare query lente o duplicate che rallentano il caricamento.', 'fp-performance-suite'); ?>">ℹ️</span>
            </h2>
            <p class="description"><?php esc_html_e('Monitora le query database in tempo reale e identifica colli di bottiglia.', 'fp-performance-suite'); ?></p>
            
            <?php if ($queryMonitor) : ?>
            <form method="post" action="?page=fp-performance-suite-database&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="enable_query_monitor" value="1" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <?php $querySettings = $queryMonitor->getSettings(); ?>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Query Monitor', 'fp-performance-suite'); ?></strong>
                        <small><?php esc_html_e('Traccia le query database e fornisce statistiche dettagliate', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="query_monitor_enabled" value="1" <?php checked($querySettings['enabled']); ?> />
                </label>
                
                <p>
                    <button type="submit" class="button button-secondary"><?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            <?php else : ?>
                <div class="notice notice-warning inline">
                    <p><?php esc_html_e('Il servizio Query Monitor non è disponibile. Assicurati che il file DatabaseQueryMonitor.php sia presente.', 'fp-performance-suite'); ?></p>
                </div>
            <?php endif; ?>
            
            <?php if ($queryAnalysis && !empty($queryAnalysis['statistics'])) : ?>
                <div style="margin-top: 20px;">
                    <h3><?php esc_html_e('Statistiche Ultime Query', 'fp-performance-suite'); ?></h3>
                    <div class="fp-ps-grid three">
                        <div class="fp-ps-stat-box">
                            <div class="stat-value"><?php echo esc_html(number_format_i18n($queryAnalysis['statistics']['total_queries'])); ?></div>
                            <div class="stat-label"><?php esc_html_e('Totale Query', 'fp-performance-suite'); ?></div>
                        </div>
                        <div class="fp-ps-stat-box">
                            <div class="stat-value <?php echo $queryAnalysis['statistics']['slow_queries'] > 0 ? 'warning' : ''; ?>">
                                <?php echo esc_html(number_format_i18n($queryAnalysis['statistics']['slow_queries'])); ?>
                            </div>
                            <div class="stat-label"><?php esc_html_e('Query Lente (>5ms)', 'fp-performance-suite'); ?></div>
                        </div>
                        <div class="fp-ps-stat-box">
                            <div class="stat-value <?php echo $queryAnalysis['statistics']['duplicate_queries'] > 10 ? 'warning' : ''; ?>">
                                <?php echo esc_html(number_format_i18n($queryAnalysis['statistics']['duplicate_queries'])); ?>
                            </div>
                            <div class="stat-label"><?php esc_html_e('Query Duplicate', 'fp-performance-suite'); ?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($queryAnalysis['recommendations'])) : ?>
                        <div style="margin-top: 20px;">
                            <h4><?php esc_html_e('Raccomandazioni', 'fp-performance-suite'); ?></h4>
                            <?php foreach ($queryAnalysis['recommendations'] as $rec) : ?>
                                <div class="notice notice-<?php echo $rec['type']; ?>" style="margin: 10px 0;">
                                    <h4 style="margin-top: 10px;"><?php echo esc_html($rec['title']); ?></h4>
                                    <p><?php echo esc_html($rec['message']); ?></p>
                                    <?php if (!empty($rec['suggestions'])) : ?>
                                        <ul>
                                            <?php foreach ($rec['suggestions'] as $suggestion) : ?>
                                                <li><?php echo esc_html($suggestion); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Object Cache Section -->
        <section class="fp-ps-card">
            <h2>
                <?php esc_html_e('Object Caching', 'fp-performance-suite'); ?>
                <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 8px; font-size: 18px;" title="<?php esc_attr_e('Object Cache: sistema che memorizza in RAM (Redis/Memcached) i risultati delle query database per evitare di rieseguirle. Riduce il carico DB del 50-80%.', 'fp-performance-suite'); ?>">ℹ️</span>
            </h2>
            <p class="description"><?php esc_html_e('L\'object caching riduce drasticamente il numero di query database memorizzando i risultati in memoria.', 'fp-performance-suite'); ?></p>
            
            <?php if ($cacheInfo['available']) : ?>
                <div style="padding: 15px; background: #e7f7ef; border-left: 4px solid #46b450; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #46b450;">
                        ✓ <?php echo esc_html($cacheInfo['name']); ?> <?php esc_html_e('Disponibile', 'fp-performance-suite'); ?>
                    </h3>
                    <p><?php echo esc_html($cacheInfo['description']); ?></p>
                    
                    <?php if ($cacheInfo['enabled']) : ?>
                        <p><strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong> <span style="color: #46b450;">● <?php esc_html_e('Attivo', 'fp-performance-suite'); ?></span></p>
                        
                        <?php 
                        $hits = $cacheStats['hits'] ?? 0;
                        $misses = $cacheStats['misses'] ?? 0;
                        $total = $hits + $misses;
                        $ratio = $total > 0 ? round(($hits / $total) * 100, 1) : 0;
                        ?>
                        
                        <?php if ($hits > 0 || $misses > 0) : ?>
                            <div class="fp-ps-grid three" style="margin: 20px 0;">
                                <div class="fp-ps-stat-box">
                                    <div class="stat-value"><?php echo esc_html(number_format_i18n($hits)); ?></div>
                                    <div class="stat-label"><?php esc_html_e('Cache Hits', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-stat-box">
                                    <div class="stat-value"><?php echo esc_html(number_format_i18n($misses)); ?></div>
                                    <div class="stat-label"><?php esc_html_e('Cache Misses', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-stat-box">
                                    <div class="stat-value"><?php echo esc_html(number_format_i18n($ratio, 1)); ?>%</div>
                                    <div class="stat-label"><?php esc_html_e('Hit Ratio', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" style="margin-top: 15px;">
                            <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                            <input type="hidden" name="disable_object_cache" value="1" />
                            <button type="submit" class="button button-secondary"><?php esc_html_e('Disattiva Object Cache', 'fp-performance-suite'); ?></button>
                        </form>
                    <?php else : ?>
                        <p><strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong> <span style="color: #f56e28;">● <?php esc_html_e('Non attivo', 'fp-performance-suite'); ?></span></p>
                        
                        <form method="post" style="margin-top: 15px;">
                            <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                            <input type="hidden" name="enable_object_cache" value="1" />
                            <button type="submit" class="button button-primary"><?php esc_html_e('Attiva Object Cache', 'fp-performance-suite'); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div style="padding: 15px; background: #fff3cd; border-left: 4px solid #f0b429; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #826200;">
                        ⚠ <?php esc_html_e('Object Cache Non Disponibile', 'fp-performance-suite'); ?>
                    </h3>
                    <p><?php esc_html_e('Nessun backend di object caching (Redis, Memcached, APCu) è disponibile sul tuo server.', 'fp-performance-suite'); ?></p>
                    <p><?php esc_html_e('Contatta il tuo hosting provider per abilitare Redis o Memcached per migliorare drasticamente le performance.', 'fp-performance-suite'); ?></p>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Close TAB: Operations, Open TAB: Analysis -->
        </div>
        <div class="fp-ps-tab-content" data-tab="analysis" style="display: <?php echo $current_tab === 'analysis' ? 'block' : 'none'; ?>;">
        
        <!-- Database Optimizer Section -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Database Optimizer', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid two" style="margin: 20px 0;">
                <div>
                    <h3><?php esc_html_e('Dimensione Database', 'fp-performance-suite'); ?></h3>
                    <div class="fp-ps-stat-box">
                        <div class="stat-value"><?php echo esc_html(number_format_i18n($dbAnalysis['database_size']['total_mb'], 2)); ?> MB</div>
                        <div class="stat-label"><?php esc_html_e('Dimensione Totale', 'fp-performance-suite'); ?></div>
                    </div>
                    <?php if ($dbAnalysis['table_analysis']['total_overhead_mb'] > 0) : ?>
                        <div class="fp-ps-stat-box" style="margin-top: 10px;">
                            <div class="stat-value warning"><?php echo esc_html(number_format_i18n($dbAnalysis['table_analysis']['total_overhead_mb'], 2)); ?> MB</div>
                            <div class="stat-label">
                                <?php esc_html_e('Overhead Recuperabile', 'fp-performance-suite'); ?>
                                <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 5px; font-size: 14px;" title="<?php esc_attr_e('Overhead = spazio sprecato nel database che può essere recuperato ottimizzando le tabelle. Overhead >100MB indica necessità di ottimizzazione.', 'fp-performance-suite'); ?>">ℹ️</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3><?php esc_html_e('Tabelle', 'fp-performance-suite'); ?></h3>
                    <p><strong><?php esc_html_e('Totale:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($dbAnalysis['table_analysis']['total_tables'] ?? 0)); ?></p>
                    <p><strong><?php esc_html_e('Necessitano ottimizzazione:', 'fp-performance-suite'); ?></strong> 
                        <?php 
                        $tables = $dbAnalysis['table_analysis']['tables'] ?? [];
                        $needsOpt = is_array($tables) ? array_filter($tables, fn($t) => $t['needs_optimization'] ?? false) : [];
                        echo esc_html(number_format_i18n(count($needsOpt))); 
                        ?>
                    </p>
                </div>
            </div>
            
            <?php if (!empty($dbAnalysis['recommendations'])) : ?>
                <div style="margin-top: 20px;">
                    <h3><?php esc_html_e('Raccomandazioni', 'fp-performance-suite'); ?></h3>
                    <?php foreach ($dbAnalysis['recommendations'] as $rec) : ?>
                        <div class="notice notice-<?php echo $rec['type']; ?>" style="margin: 10px 0;">
                            <h4 style="margin-top: 10px;"><?php echo esc_html($rec['title']); ?></h4>
                            <p><?php echo esc_html($rec['message']); ?></p>
                            <?php if (!empty($rec['actions'])) : ?>
                                <div style="margin-top: 10px;">
                                    <?php foreach ($rec['actions'] as $action => $label) : ?>
                                        <button class="button button-secondary" style="margin-right: 10px;">
                                            <?php echo esc_html($label); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="optimize_all_tables" value="1" />
                <button type="submit" class="button button-primary" data-risk="amber">
                    <?php esc_html_e('Ottimizza Tutte le Tabelle', 'fp-performance-suite'); ?>
                </button>
                <p class="description"><?php esc_html_e('Questa operazione può richiedere alcuni minuti.', 'fp-performance-suite'); ?></p>
            </form>
        </section>
        
        <!-- Health Score Dashboard -->
        <section class="fp-ps-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 style="color: white;"><?php esc_html_e('💯 Database Health Score', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid three" style="margin: 20px 0;">
                <div class="fp-ps-stat-box" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                    <div class="stat-value" style="font-size: 48px; color: white;"><?php echo esc_html($healthScore['score']); ?>%</div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);"><?php esc_html_e('Punteggio', 'fp-performance-suite'); ?></div>
                </div>
                <div class="fp-ps-stat-box" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                    <div class="stat-value" style="font-size: 48px; color: white;"><?php echo esc_html($healthScore['grade']); ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);"><?php esc_html_e('Voto', 'fp-performance-suite'); ?></div>
                </div>
                <div class="fp-ps-stat-box" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                    <div class="stat-value" style="font-size: 24px; color: white;"><?php echo esc_html(ucfirst($healthScore['status'])); ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.9);"><?php esc_html_e('Stato', 'fp-performance-suite'); ?></div>
                </div>
            </div>
            
            <?php if (!empty($healthScore['issues'])) : ?>
                <div style="margin-top: 20px; background: rgba(255,255,255,0.15); padding: 15px; border-radius: 4px;">
                    <h3 style="color: white; margin-top: 0;"><?php esc_html_e('Problemi Rilevati:', 'fp-performance-suite'); ?></h3>
                    <ul style="color: rgba(255,255,255,0.95); margin: 0;">
                        <?php foreach ($healthScore['recommendations'] as $rec) : ?>
                            <li><?php echo esc_html($rec); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Advanced Analysis Tabs -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🔬 Analisi Avanzate', 'fp-performance-suite'); ?></h2>
            
            <!-- Fragmentation Analysis -->
            <?php if (!empty($fragmentation['fragmented_tables'])) : ?>
                <div style="margin: 20px 0;">
                    <h3><?php esc_html_e('📊 Frammentazione Tabelle', 'fp-performance-suite'); ?></h3>
                    <p><strong><?php esc_html_e('Spazio recuperabile:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($fragmentation['total_wasted_mb'], 2)); ?> MB</p>
                    
                    <table class="fp-ps-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Tabella', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Frammentazione %', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Spazio Sprecato', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Gravità', 'fp-performance-suite'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($fragmentation['fragmented_tables'], 0, 10) as $table) : ?>
                                <tr>
                                    <td><code><?php echo esc_html($table['table']); ?></code></td>
                                    <td><?php echo esc_html(number_format_i18n($table['fragmentation_pct'], 2)); ?>%</td>
                                    <td><?php echo esc_html(number_format_i18n($table['wasted_mb'], 2)); ?> MB</td>
                                    <td>
                                        <?php if ($table['severity'] === 'high') : ?>
                                            <span style="color: #dc3232;">🔴 <?php esc_html_e('Alta', 'fp-performance-suite'); ?></span>
                                        <?php elseif ($table['severity'] === 'medium') : ?>
                                            <span style="color: #f0b429;">🟡 <?php esc_html_e('Media', 'fp-performance-suite'); ?></span>
                                        <?php else : ?>
                                            <span style="color: #46b450;">🟢 <?php esc_html_e('Bassa', 'fp-performance-suite'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Storage Engine Analysis -->
            <?php if (!empty($storageEngines['myisam_tables'])) : ?>
                <div style="margin: 20px 0;">
                    <h3><?php esc_html_e('⚙️ Storage Engine', 'fp-performance-suite'); ?></h3>
                    <div class="notice notice-warning" style="margin: 10px 0;">
                        <p><strong><?php echo esc_html(count($storageEngines['myisam_tables'])); ?> tabelle MyISAM rilevate.</strong></p>
                        <p><?php esc_html_e('InnoDB è raccomandato per WordPress. Offre migliori performance, affidabilità e supporto transazioni.', 'fp-performance-suite'); ?></p>
                    </div>
                    
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; font-weight: bold;"><?php esc_html_e('Mostra tabelle MyISAM', 'fp-performance-suite'); ?></summary>
                        <ul style="margin-top: 10px;">
                            <?php foreach ($storageEngines['myisam_tables'] as $table) : ?>
                                <li>
                                    <code><?php echo esc_html($table['table']); ?></code> 
                                    (<?php echo esc_html(number_format_i18n($table['size_mb'], 2)); ?> MB)
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                                        <input type="hidden" name="convert_to_innodb" value="1" />
                                        <input type="hidden" name="table_name" value="<?php echo esc_attr($table['table']); ?>" />
                                        <button type="submit" class="button button-small"><?php esc_html_e('Converti a InnoDB', 'fp-performance-suite'); ?></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                </div>
            <?php endif; ?>
            
            <!-- Charset Analysis -->
            <?php if (!empty($charset['outdated_tables'])) : ?>
                <div style="margin: 20px 0;">
                    <h3><?php esc_html_e('🔤 Charset Obsoleti', 'fp-performance-suite'); ?></h3>
                    <div class="notice notice-info" style="margin: 10px 0;">
                        <p><strong><?php echo esc_html(count($charset['outdated_tables'])); ?> tabelle con charset obsoleto.</strong></p>
                        <p><?php esc_html_e('utf8mb4 è il charset raccomandato. Supporta emoji e tutti i caratteri Unicode.', 'fp-performance-suite'); ?></p>
                    </div>
                    
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; font-weight: bold;"><?php esc_html_e('Mostra tabelle da convertire', 'fp-performance-suite'); ?></summary>
                        <ul style="margin-top: 10px;">
                            <?php foreach (array_slice($charset['outdated_tables'], 0, 20) as $table) : ?>
                                <li>
                                    <code><?php echo esc_html($table['table']); ?></code> 
                                    (<?php echo esc_html($table['charset']); ?>)
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                                        <input type="hidden" name="convert_charset" value="1" />
                                        <input type="hidden" name="table_name" value="<?php echo esc_attr($table['table']); ?>" />
                                        <button type="submit" class="button button-small"><?php esc_html_e('Converti a utf8mb4', 'fp-performance-suite'); ?></button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                </div>
            <?php endif; ?>
            
            <!-- Autoload Optimization -->
            <?php if (!empty($autoloadDetailed['large_options'])) : ?>
                <div style="margin: 20px 0;">
                    <h3><?php esc_html_e('⚡ Opzioni Autoload Grandi', 'fp-performance-suite'); ?></h3>
                    <p><strong><?php esc_html_e('Dimensione totale autoload:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($autoloadDetailed['total_size_mb'], 2)); ?> MB</p>
                    
                    <?php if ($autoloadDetailed['total_size_mb'] > 1) : ?>
                        <div class="notice notice-warning" style="margin: 10px 0;">
                            <p><?php esc_html_e('Autoload > 1MB può rallentare significativamente ogni richiesta. Disabilita autoload per opzioni grandi.', 'fp-performance-suite'); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <table class="fp-ps-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Opzione', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Plugin', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Dimensione', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Azione', 'fp-performance-suite'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($autoloadDetailed['large_options'], 0, 10) as $option) : ?>
                                <tr>
                                    <td><code style="font-size: 11px;"><?php echo esc_html(substr($option['name'], 0, 40)); ?>...</code></td>
                                    <td><?php echo esc_html($option['plugin']); ?></td>
                                    <td><?php echo esc_html(number_format_i18n($option['size_kb'], 2)); ?> KB</td>
                                    <td>
                                        <form method="post" style="display: inline;">
                                            <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                                            <input type="hidden" name="disable_autoload" value="1" />
                                            <input type="hidden" name="option_name" value="<?php echo esc_attr($option['name']); ?>" />
                                            <button type="submit" class="button button-small" data-risk="amber"><?php esc_html_e('Disabilita Autoload', 'fp-performance-suite'); ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Missing Indexes -->
            <?php if (!empty($missingIndexes['suggestions'])) : ?>
                <div style="margin: 20px 0;">
                    <h3><?php esc_html_e('🔍 Indici Mancanti', 'fp-performance-suite'); ?></h3>
                    <p><?php echo esc_html($missingIndexes['total_suggestions']); ?> <?php esc_html_e('suggerimenti trovati', 'fp-performance-suite'); ?> 
                        (<?php echo esc_html($missingIndexes['high_priority']); ?> <?php esc_html_e('alta priorità', 'fp-performance-suite'); ?>)</p>
                    
                    <div class="notice notice-info" style="margin: 10px 0;">
                        <p><?php esc_html_e('Gli indici mancanti possono rallentare significativamente le query. Considera di crearli manualmente.', 'fp-performance-suite'); ?></p>
                    </div>
                    
                    <details style="margin-top: 10px;">
                        <summary style="cursor: pointer; font-weight: bold;"><?php esc_html_e('Mostra suggerimenti', 'fp-performance-suite'); ?></summary>
                        <?php foreach ($missingIndexes['suggestions'] as $table => $suggestions) : ?>
                            <div style="margin: 10px 0; padding: 10px; background: #f0f0f1;">
                                <strong><?php echo esc_html($table); ?></strong>
                                <ul>
                                    <?php foreach ($suggestions as $suggestion) : ?>
                                        <li>
                                            <?php if (isset($suggestion['column'])) : ?>
                                                <code><?php echo esc_html($suggestion['column']); ?></code>
                                            <?php else : ?>
                                                <code><?php echo esc_html(implode(', ', $suggestion['columns'])); ?></code>
                                            <?php endif; ?>
                                            - <?php echo esc_html($suggestion['reason']); ?>
                                            <span style="color: <?php echo $suggestion['benefit'] === 'high' ? '#dc3232' : '#f0b429'; ?>;">
                                                (<?php echo esc_html(ucfirst($suggestion['benefit'])); ?> benefit)
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    </details>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Close TAB: Analysis, Open TAB: Reports -->
        </div>
        <div class="fp-ps-tab-content" data-tab="reports" style="display: <?php echo $current_tab === 'reports' ? 'block' : 'none'; ?>;">
        
        <!-- Plugin-Specific Cleanup -->
        <?php if (!empty($pluginOpportunities['opportunities'])) : ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🔌 Ottimizzazioni Plugin-Specific', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Opportunità di pulizia specifiche per i plugin installati.', 'fp-performance-suite'); ?></p>
            
            <div style="margin: 20px 0; padding: 15px; background: #e7f7ef; border-left: 4px solid #46b450;">
                <h3 style="margin-top: 0; color: #1d8f3d;"><?php esc_html_e('💾 Risparmio Potenziale', 'fp-performance-suite'); ?></h3>
                <p style="font-size: 18px; margin: 0;"><strong><?php echo esc_html(number_format_i18n($pluginOpportunities['total_potential_savings_mb'], 2)); ?> MB</strong> possono essere recuperati</p>
                <p style="margin: 5px 0 0 0;"><?php echo esc_html(number_format_i18n($pluginOpportunities['total_items_to_clean'])); ?> elementi da pulire</p>
            </div>
            
            <?php foreach ($pluginOpportunities['opportunities'] as $pluginKey => $data) : ?>
                <div style="margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="margin-top: 0;"><?php echo esc_html($data['plugin_name']); ?></h3>
                    
                    <div class="fp-ps-grid two">
                        <div>
                            <p><strong><?php esc_html_e('Elementi:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($data['total_items'])); ?></p>
                        </div>
                        <div>
                            <p><strong><?php esc_html_e('Risparmio:', 'fp-performance-suite'); ?></strong> ~<?php echo esc_html(number_format_i18n($data['potential_savings_mb'], 2)); ?> MB</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($data['recommendations'])) : ?>
                        <div style="margin-top: 10px;">
                            <?php foreach ($data['recommendations'] as $rec) : ?>
                                <div class="notice notice-<?php echo $rec['type']; ?>" style="margin: 5px 0; padding: 10px;">
                                    <p style="margin: 0;"><?php echo esc_html($rec['message']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($pluginKey === 'woocommerce' && !empty($data['analysis'])) : ?>
                        <form method="post" style="margin-top: 10px;">
                            <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                            <input type="hidden" name="cleanup_woocommerce" value="1" />
                            <label><input type="checkbox" name="wc_tasks[]" value="sessions" /> <?php esc_html_e('Pulisci sessioni scadute', 'fp-performance-suite'); ?> (<?php echo esc_html(number_format_i18n($data['analysis']['sessions'])); ?>)</label><br />
                            <label><input type="checkbox" name="wc_tasks[]" value="old_orders" /> <?php esc_html_e('Elimina ordini vecchi (>30gg)', 'fp-performance-suite'); ?> (<?php echo esc_html(number_format_i18n($data['analysis']['pending_orders'] + $data['analysis']['failed_orders'])); ?>)</label><br />
                            <label><input type="checkbox" name="wc_tasks[]" value="action_scheduler" /> <?php esc_html_e('Pulisci log Action Scheduler', 'fp-performance-suite'); ?></label><br />
                            <button type="submit" class="button button-secondary" style="margin-top: 10px;"><?php esc_html_e('Esegui Pulizia WooCommerce', 'fp-performance-suite'); ?></button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if ($pluginKey === 'elementor' && !empty($data['analysis'])) : ?>
                        <form method="post" style="margin-top: 10px;">
                            <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                            <input type="hidden" name="cleanup_elementor" value="1" />
                            <label><input type="checkbox" name="elementor_tasks[]" value="revisions" /> <?php esc_html_e('Elimina revisioni Elementor', 'fp-performance-suite'); ?> (<?php echo esc_html(number_format_i18n($data['analysis']['revisions'])); ?>)</label><br />
                            <label><input type="checkbox" name="elementor_tasks[]" value="css_cache" /> <?php esc_html_e('Rigenera cache CSS', 'fp-performance-suite'); ?></label><br />
                            <button type="submit" class="button button-secondary" style="margin-top: 10px;"><?php esc_html_e('Esegui Pulizia Elementor', 'fp-performance-suite'); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
        
        <!-- Reports & Trends -->
        <?php if (isset($trends['size'])) : ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('📊 Report & Trend', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid two" style="margin: 20px 0;">
                <div>
                    <h3><?php esc_html_e('Crescita Database', 'fp-performance-suite'); ?></h3>
                    <p><strong><?php esc_html_e('Per giorno:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($trends['size']['growth_per_day_mb'], 2)); ?> MB/giorno</p>
                    <p><strong><?php esc_html_e('Proiezione 30 giorni:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($trends['projection_30_days'], 2)); ?> MB</p>
                    <p><strong><?php esc_html_e('Proiezione 90 giorni:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($trends['projection_90_days'], 2)); ?> MB</p>
                </div>
                <div>
                    <h3><?php esc_html_e('Periodo Analizzato', 'fp-performance-suite'); ?></h3>
                    <p><strong><?php esc_html_e('Dal:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($trends['period']['from']); ?></p>
                    <p><strong><?php esc_html_e('Al:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($trends['period']['to']); ?></p>
                    <p><strong><?php esc_html_e('Giorni:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($trends['period']['days'], 1)); ?></p>
                </div>
            </div>
            
            <div style="margin-top: 20px;">
                <h3><?php esc_html_e('Export Report', 'fp-performance-suite'); ?></h3>
                <form method="post" style="display: inline-block; margin-right: 10px;">
                    <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                    <input type="hidden" name="export_report" value="1" />
                    <input type="hidden" name="report_format" value="json" />
                    <button type="submit" class="button button-secondary"><?php esc_html_e('📥 Export JSON', 'fp-performance-suite'); ?></button>
                </form>
                <form method="post" style="display: inline-block;">
                    <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                    <input type="hidden" name="export_report" value="1" />
                    <input type="hidden" name="report_format" value="csv" />
                    <button type="submit" class="button button-secondary"><?php esc_html_e('📥 Export CSV', 'fp-performance-suite'); ?></button>
                </form>
            </div>
            
            <div style="margin-top: 20px;">
                <h3><?php esc_html_e('Crea Snapshot', 'fp-performance-suite'); ?></h3>
                <form method="post" style="display: flex; gap: 10px; align-items: flex-end;">
                    <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                    <div>
                        <label for="snapshot_label"><?php esc_html_e('Etichetta (opzionale):', 'fp-performance-suite'); ?></label>
                        <input type="text" name="snapshot_label" id="snapshot_label" class="regular-text" placeholder="<?php esc_attr_e('es. Prima dell\'ottimizzazione', 'fp-performance-suite'); ?>" />
                    </div>
                    <input type="hidden" name="create_snapshot" value="1" />
                    <button type="submit" class="button button-secondary"><?php esc_html_e('📸 Crea Snapshot', 'fp-performance-suite'); ?></button>
                </form>
                <p class="description"><?php esc_html_e('Gli snapshot ti permettono di monitorare l\'evoluzione del database nel tempo.', 'fp-performance-suite'); ?></p>
            </div>
        </section>
        <?php endif; ?>
        
        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Scheduler', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-database&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="save_db_settings" value="1" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                <p>
                    <label for="schedule"><?php esc_html_e('Cleanup schedule', 'fp-performance-suite'); ?></label>
                    <select name="schedule" id="schedule">
                        <option value="manual" <?php selected($settings['schedule'], 'manual'); ?>><?php esc_html_e('Manual', 'fp-performance-suite'); ?></option>
                        <option value="weekly" <?php selected($settings['schedule'], 'weekly'); ?>><?php esc_html_e('Weekly', 'fp-performance-suite'); ?></option>
                        <option value="monthly" <?php selected($settings['schedule'], 'monthly'); ?>><?php esc_html_e('Monthly', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                <p>
                    <label for="batch">
                        <?php esc_html_e('Batch size', 'fp-performance-suite'); ?>
                        <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 5px;" title="<?php esc_attr_e('Numero di elementi processati per volta durante la pulizia. Valori più bassi = meno carico ma operazioni più lente. Valori più alti = più veloce ma maggior uso memoria.', 'fp-performance-suite'); ?>">ℹ️</span>
                    </label>
                    <input type="number" name="batch" id="batch" value="<?php echo esc_attr((string) $settings['batch']); ?>" min="50" max="500" placeholder="200" class="regular-text" />
                </p>
                <div class="fp-ps-input-help" style="margin-top: 8px; margin-bottom: 15px;">
                    <p style="background: #dbeafe; border-left: 3px solid #3b82f6; padding: 10px; margin: 10px 0; border-radius: 4px;">
                        💡 <strong><?php esc_html_e('Consigliato: 200 elementi', 'fp-performance-suite'); ?></strong>
                        <br><small style="color: #64748b;">
                            <?php esc_html_e('Hosting Condiviso: 100-200 | VPS: 200-300 | Dedicato: 300-500', 'fp-performance-suite'); ?>
                        </small>
                    </p>
                </div>
                <p>
                    <button type="submit" class="button button-secondary"><?php esc_html_e('Save Scheduler', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #f0f0f1; border-left: 4px solid #2271b1;">
                <h3 style="margin-top: 0;"><?php esc_html_e('Operazioni automatiche dello scheduler:', 'fp-performance-suite'); ?></h3>
                <ul style="margin: 10px 0;">
                    <li><strong>✓</strong> <?php esc_html_e('Post revisions', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Auto drafts', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Trashed posts', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Spam/trashed comments', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Expired transients', 'fp-performance-suite'); ?></li>
                </ul>
                <p style="margin-bottom: 0;"><strong><?php esc_html_e('Escluse dallo scheduler (solo manuale):', 'fp-performance-suite'); ?></strong> <?php esc_html_e('Optimize tables, Orphan meta (post/term/user)', 'fp-performance-suite'); ?></p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #646970;"><em><?php esc_html_e('Le operazioni ad alto rischio come "Optimize tables" devono essere eseguite manualmente durante periodi di basso traffico.', 'fp-performance-suite'); ?></em></p>
            </div>
            
            <p class="description" style="margin-top: 15px;"><?php printf(esc_html__('Current overhead: %s MB', 'fp-performance-suite'), number_format_i18n($overhead, 2)); ?></p>
            <p class="description"><?php printf(esc_html__('Last automated cleanup: %s', 'fp-performance-suite'), esc_html($lastRun)); ?></p>
        </section>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Cleanup Tools', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-database&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="run_cleanup" value="1" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                <p><?php esc_html_e('Select components to clean. Red actions require PROCEDI confirmation.', 'fp-performance-suite'); ?></p>
                <div class="fp-ps-grid two">
                    <?php foreach ($tasks as $key => $label) : ?>
                        <label class="fp-ps-toggle">
                            <span class="info">
                                <strong><?php echo esc_html($label); ?></strong>
                            </span>
                            <input type="checkbox" name="cleanup_scope[]" value="<?php echo esc_attr($key); ?>" data-risk="<?php echo $key === 'optimize_tables' ? 'red' : 'amber'; ?>" />
                        </label>
                    <?php endforeach; ?>
                </div>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Dry run', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="dry_run" value="1" checked data-risk="green" />
                </label>
                <p>
                    <button type="submit" class="button button-primary" data-risk="red"><?php esc_html_e('Execute Cleanup', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            <?php if (!empty($results)) : ?>
                <table class="fp-ps-table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Task', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Found', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Deleted', 'fp-performance-suite'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $task => $data) : ?>
                        <tr>
                            <td><?php echo esc_html($tasks[$task] ?? $task); ?></td>
                            <td>
                                <?php
                                $found = $data['found'] ?? 0;
                                if (!empty($data['site_found'])) {
                                    $found .= sprintf(' (+%d site)', (int) $data['site_found']);
                                }
                                echo esc_html((string) $found);
                                ?>
                            </td>
                            <td>
                                <?php
                                if (isset($data['tables']) && is_array($data['tables'])) {
                                    $tableList = implode(', ', $data['tables']);
                                    echo esc_html($tableList !== '' ? $tableList : '-');
                                } else {
                                    $deleted = $data['deleted'] ?? '-';
                                    if (!empty($data['site_deleted'])) {
                                        $deleted .= sprintf(' (+%d site)', (int) $data['site_deleted']);
                                    }
                                    echo esc_html((string) $deleted);
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
        
        <!-- Close TAB: Reports -->
        </div>
        
        <?php
        return (string) ob_get_clean();
    }
    
    /**
     * Download report file
     */
    private function downloadReport(DatabaseReportService $reportService, string $format): void
    {
        $report = $reportService->generateCompleteReport();
        
        if ($format === 'json') {
            $content = $reportService->exportJSON($report);
            $filename = 'fp-performance-db-report-' . date('Y-m-d') . '.json';
            $contentType = 'application/json';
        } else {
            $content = $reportService->exportCSV($report);
            $filename = 'fp-performance-db-report-' . date('Y-m-d') . '.csv';
            $contentType = 'text/csv';
        }
        
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }
    
    /**
     * Verifica se object cache è disponibile
     */
    private function isObjectCacheAvailable(): bool
    {
        // Verifica Redis
        if (class_exists('Redis')) {
            return true;
        }
        
        // Verifica Memcached
        if (class_exists('Memcached')) {
            return true;
        }
        
        // Verifica APCu
        if (function_exists('apcu_fetch')) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Ottieni descrizione del driver
     */
    private function getDriverDescription(string $driver): string
    {
        $descriptions = [
            'redis' => __('Redis è il backend più performante. Ideale per siti ad alto traffico.', 'fp-performance-suite'),
            'memcached' => __('Memcached è veloce e affidabile. Ottimo per siti di medie dimensioni.', 'fp-performance-suite'),
            'apcu' => __('APCu è un cache in-memory PHP. Buono per hosting condivisi.', 'fp-performance-suite'),
            'none' => __('Nessun backend di object caching configurato.', 'fp-performance-suite'),
        ];
        
        return $descriptions[$driver] ?? $descriptions['none'];
    }
    
    /**
     * Renderizza messaggio di errore invece della pagina vuota
     */
    private function renderError(string $message): string
    {
        ob_start();
        ?>
        <div class="notice notice-error" style="margin: 20px 0; padding: 20px;">
            <h2><?php esc_html_e('Errore Caricamento Pagina Database', 'fp-performance-suite'); ?></h2>
            <p><strong><?php esc_html_e('Si è verificato un errore:', 'fp-performance-suite'); ?></strong></p>
            <p><code><?php echo esc_html($message); ?></code></p>
            <hr>
            <h3><?php esc_html_e('Possibili Soluzioni:', 'fp-performance-suite'); ?></h3>
            <ol>
                <li><?php esc_html_e('Disattiva e riattiva il plugin', 'fp-performance-suite'); ?></li>
                <li><?php esc_html_e('Verifica che tutti i file del plugin siano presenti', 'fp-performance-suite'); ?></li>
                <li><?php esc_html_e('Controlla il log degli errori PHP', 'fp-performance-suite'); ?></li>
                <li><?php esc_html_e('Contatta il supporto se il problema persiste', 'fp-performance-suite'); ?></li>
            </ol>
            
            <h3><?php esc_html_e('Informazioni per il Debug:', 'fp-performance-suite'); ?></h3>
            <ul style="font-family: monospace; font-size: 12px; background: #f5f5f5; padding: 15px;">
                <li><strong>PHP Version:</strong> <?php echo esc_html(PHP_VERSION); ?></li>
                <li><strong>WordPress Version:</strong> <?php echo esc_html(get_bloginfo('version')); ?></li>
                <li><strong>Plugin Version:</strong> <?php echo esc_html(FP_PERF_SUITE_VERSION); ?></li>
                <li><strong>Error:</strong> <?php echo esc_html($message); ?></li>
            </ul>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function handleFormSubmissions(string $activeTab): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Query Monitor form submission
        if ($activeTab === 'monitor' && isset($_POST['fp_ps_query_monitor_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_query_monitor_nonce']), 'fp_ps_query_monitor')) {
            try {
                $queryMonitor = $this->container->get(DatabaseQueryMonitor::class);
                
                $settings = [
                    'enabled' => !empty($_POST['enabled']),
                    'slow_query_threshold' => (float) ($_POST['slow_query_threshold'] ?? 0.005),
                    'log_duplicates' => !empty($_POST['log_duplicates']),
                ];
                
                $result = $queryMonitor->updateSettings($settings);
                
                if ($result) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('Configurazione Query Monitor salvata con successo!', 'fp-performance-suite') . 
                             '</p></div>';
                    });
                }
            } catch (\Exception $e) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . 
                         esc_html__('Errore nel salvare la configurazione Query Monitor: ', 'fp-performance-suite') . 
                         esc_html($e->getMessage()) . '</p></div>';
                });
            }
        }

        // Query Cache form submission
        if ($activeTab === 'query_cache' && isset($_POST['fp_ps_query_cache_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_query_cache_nonce']), 'fp_ps_query_cache')) {
            try {
                $queryCache = $this->container->get(QueryCacheManager::class);
                
                $settings = [
                    'enabled' => !empty($_POST['enabled']),
                    'ttl' => (int) ($_POST['ttl'] ?? 3600),
                    'cache_select_only' => !empty($_POST['cache_select_only']),
                    'max_cache_size' => (int) ($_POST['max_cache_size'] ?? 1000),
                ];
                
                $result = $queryCache->updateSettings($settings);
                
                if ($result) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('Configurazione Query Cache salvata con successo!', 'fp-performance-suite') . 
                             '</p></div>';
                    });
                }
            } catch (\Exception $e) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . 
                         esc_html__('Errore nel salvare la configurazione Query Cache: ', 'fp-performance-suite') . 
                         esc_html($e->getMessage()) . '</p></div>';
                });
            }
        }

        // Reset Cache Stats form submission
        if (isset($_POST['action']) && $_POST['action'] === 'reset_cache_stats' && 
            isset($_POST['fp_ps_reset_cache_stats_nonce']) && 
            wp_verify_nonce(wp_unslash($_POST['fp_ps_reset_cache_stats_nonce']), 'fp_ps_reset_cache_stats')) {
            try {
                $queryCache = $this->container->get(QueryCacheManager::class);
                $result = $queryCache->resetStats();
                
                if ($result) {
                    add_action('admin_notices', function() {
                        echo '<div class="notice notice-success is-dismissible"><p>' . 
                             esc_html__('Statistiche Query Cache resettate con successo!', 'fp-performance-suite') . 
                             '</p></div>';
                    });
                }
            } catch (\Exception $e) {
                add_action('admin_notices', function() use ($e) {
                    echo '<div class="notice notice-error is-dismissible"><p>' . 
                         esc_html__('Errore nel resettare le statistiche: ', 'fp-performance-suite') . 
                         esc_html($e->getMessage()) . '</p></div>';
                });
            }
        }
        
        // Enable SAVEQUERIES for admin form submission
        if (isset($_POST['action']) && $_POST['action'] === 'enable_savequeries_admin' && 
            isset($_POST['fp_ps_enable_savequeries_nonce']) && 
            wp_verify_nonce(wp_unslash($_POST['fp_ps_enable_savequeries_nonce']), 'fp_ps_enable_savequeries')) {
            
            // Salva l'impostazione per abilitare SAVEQUERIES solo per admin
            $result = update_option('fp_ps_savequeries_admin_only', true, false);
            
            if ($result) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . 
                         esc_html__('✅ Query logging abilitato per gli amministratori! Ricarica la pagina per vedere le statistiche.', 'fp-performance-suite') . 
                         '</p></div>';
                });
            }
        }
    }

    private function renderTabsNavigation(string $activeTab): void
    {
        $baseUrl = admin_url('admin.php?page=fp-performance-suite-database');
        ?>
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="<?php echo esc_url($baseUrl . '&tab=operations'); ?>" 
               class="nav-tab <?php echo $activeTab === 'operations' ? 'nav-tab-active' : ''; ?>">
                🔧 <?php esc_html_e('Operations', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=monitor'); ?>" 
               class="nav-tab <?php echo $activeTab === 'monitor' ? 'nav-tab-active' : ''; ?>">
                📊 <?php esc_html_e('Query Monitor', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=query_cache'); ?>" 
               class="nav-tab <?php echo $activeTab === 'query_cache' ? 'nav-tab-active' : ''; ?>">
                ⚡ <?php esc_html_e('Query Cache', 'fp-performance-suite'); ?>
            </a>
        </div>
        <?php
    }

    private function renderOperationsTab(): string
    {
        // Error handling robusto per prevenire pagina vuota
        try {
            $cleaner = $this->container->get(Cleaner::class);
        } catch (\Exception $e) {
            return $this->renderError('Cleaner service non disponibile: ' . $e->getMessage());
        }
        
        // Servizi avanzati - verifica disponibilità (v1.4.0)
        $queryMonitor = $this->container->has(DatabaseQueryMonitor::class) 
            ? $this->container->get(DatabaseQueryMonitor::class) 
            : null;
        $optimizer = $this->container->has(DatabaseOptimizer::class) 
            ? $this->container->get(DatabaseOptimizer::class) 
            : null;
        
        try {
            $objectCache = $this->container->get(ObjectCacheManager::class);
        } catch (\Exception $e) {
            return $this->renderError('ObjectCacheManager service non disponibile: ' . $e->getMessage());
        }
        
        // Crea istanze dirette se le classi esistono
        $pluginOptimizer = class_exists('FP\\PerfSuite\\Services\\DB\\PluginSpecificOptimizer') 
            ? new PluginSpecificOptimizer() 
            : null;
        $reportService = class_exists('FP\\PerfSuite\\Services\\DB\\DatabaseReportService') 
            ? new DatabaseReportService() 
            : null;
        
        $message = '';
        $results = [];
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_db_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_db_nonce']), 'fp-ps-db')) {
            
            // Main Toggle for Database Optimization
            if (isset($_POST['form_type']) && $_POST['form_type'] === 'main_toggle') {
                $dbSettings['enabled'] = !empty($_POST['database_enabled']);
                update_option('fp_ps_db', $dbSettings);
                $message = __('Database optimization settings saved successfully!', 'fp-performance-suite');
            }
            if (isset($_POST['save_db_settings'])) {
                $cleaner->update([
                    'schedule' => sanitize_text_field($_POST['schedule'] ?? 'manual'),
                    'batch' => (int) ($_POST['batch'] ?? 200),
                ]);
                $message = __('Database settings updated.', 'fp-performance-suite');
            }
            if (isset($_POST['run_cleanup'])) {
                $scope = array_map('sanitize_text_field', (array) ($_POST['cleanup_scope'] ?? []));
                $dry = !empty($_POST['dry_run']);
                $results = $cleaner->cleanup($scope, $dry, (int) ($_POST['batch'] ?? 200));
                $message = $dry ? __('Dry run completed.', 'fp-performance-suite') : __('Cleanup completed.', 'fp-performance-suite');
            }
            if (isset($_POST['enable_query_monitor']) && $queryMonitor) {
                $queryMonitor->updateSettings(['enabled' => !empty($_POST['query_monitor_enabled'])]);
                $message = __('Query Monitor settings updated.', 'fp-performance-suite');
            }
            if (isset($_POST['optimize_all_tables']) && $optimizer) {
                $results = $optimizer->optimizeAllTables();
                $optimizedCount = count($results['optimized'] ?? []);
                $message = sprintf(__('✅ Ottimizzazione completata! %d tabelle ottimizzate.', 'fp-performance-suite'), $optimizedCount);
            }
            if (isset($_POST['enable_object_cache'])) {
                $settings = $objectCache->getSettings();
                $settings['enabled'] = true;
                $objectCache->updateSettings($settings);
                $message = __('Object Cache abilitato.', 'fp-performance-suite');
            }
            if (isset($_POST['disable_object_cache'])) {
                $settings = $objectCache->getSettings();
                $settings['enabled'] = false;
                $objectCache->updateSettings($settings);
                $message = __('Object Cache disabilitato.', 'fp-performance-suite');
            }
            // Nuove azioni - verifica disponibilità servizi
            if (isset($_POST['convert_to_innodb']) && $optimizer) {
                $table = sanitize_text_field($_POST['table_name'] ?? '');
                $result = $optimizer->convertToInnoDB($table);
                $message = $result['message'];
            }
            if (isset($_POST['convert_charset']) && $optimizer) {
                $table = sanitize_text_field($_POST['table_name'] ?? '');
                $result = $optimizer->convertCharset($table);
                $message = $result['message'];
            }
            if (isset($_POST['disable_autoload']) && $optimizer) {
                $option = sanitize_text_field($_POST['option_name'] ?? '');
                $result = $optimizer->disableAutoload($option);
                $message = $result['message'];
            }
            if (isset($_POST['cleanup_woocommerce']) && $pluginOptimizer) {
                $tasks = array_map('sanitize_text_field', (array) ($_POST['wc_tasks'] ?? []));
                $results = $pluginOptimizer->cleanupWooCommerce($tasks);
                $message = __('WooCommerce cleanup completato.', 'fp-performance-suite');
            }
            if (isset($_POST['cleanup_elementor']) && $pluginOptimizer) {
                $tasks = array_map('sanitize_text_field', (array) ($_POST['elementor_tasks'] ?? []));
                $results = $pluginOptimizer->cleanupElementor($tasks);
                $message = __('Elementor cleanup completato.', 'fp-performance-suite');
            }
            if (isset($_POST['create_snapshot']) && $reportService) {
                $label = sanitize_text_field($_POST['snapshot_label'] ?? '');
                $reportService->createSnapshot($label);
                $message = __('Snapshot creato con successo.', 'fp-performance-suite');
            }
            if (isset($_POST['export_report']) && $reportService) {
                $format = sanitize_text_field($_POST['report_format'] ?? 'json');
                $this->downloadReport($reportService, $format);
                return ''; // Download triggered
            }
        }
        
        $settings = $cleaner->settings();
        $status = $cleaner->status();
        $overhead = $status['overhead_mb'];
        $lastRun = empty($status['last_run'])
            ? __('Never', 'fp-performance-suite')
            : date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $status['last_run']);
        $tasks = [
            'revisions' => __('Post revisions', 'fp-performance-suite'),
            'auto_drafts' => __('Auto drafts', 'fp-performance-suite'),
            'trash_posts' => __('Trashed posts', 'fp-performance-suite'),
            'spam_comments' => __('Spam/trashed comments', 'fp-performance-suite'),
            'expired_transients' => __('Expired transients', 'fp-performance-suite'),
            'orphan_postmeta' => __('Orphan post meta', 'fp-performance-suite'),
            'orphan_termmeta' => __('Orphan term meta', 'fp-performance-suite'),
            'orphan_usermeta' => __('Orphan user meta', 'fp-performance-suite'),
            'optimize_tables' => __('Optimize tables', 'fp-performance-suite'),
        ];
        
        // Ottieni dati per le sezioni
        $queryAnalysis = $queryMonitor ? $queryMonitor->getLastAnalysis() : null;
        // Ricalcola sempre i dati del database per assicurarsi che siano aggiornati
        $dbAnalysis = $optimizer ? $optimizer->analyze() : ['database_size' => ['total_mb' => 0], 'table_analysis' => ['total_tables' => 0, 'total_overhead_mb' => 0, 'tables' => []], 'recommendations' => []];
        
        // Object Cache - usa metodi esistenti
        $cacheSettings = $objectCache->getSettings();
        $cacheStats = $objectCache->getStatistics();
        $cacheEnabled = $cacheSettings['enabled'] ?? false;
        // Il driver viene dal backend disponibile, non dalle settings
        $cacheDriver = $objectCache->getAvailableBackend() ?? 'none';
        
        // Crea struttura compatibile per la vista
        $cacheInfo = [
            'available' => $this->isObjectCacheAvailable(),
            'enabled' => $cacheEnabled,
            'name' => ucfirst($cacheDriver),
            'description' => $this->getDriverDescription($cacheDriver),
        ];
        
        // Nuovi dati avanzati - solo se i servizi sono disponibili
        $fragmentation = $optimizer ? $optimizer->analyzeFragmentation() : ['fragmented_tables' => [], 'total_wasted_mb' => 0];
        $missingIndexes = $optimizer ? $optimizer->analyzeMissingIndexes() : ['suggestions' => [], 'total_suggestions' => 0, 'high_priority' => 0];
        $storageEngines = $optimizer ? $optimizer->analyzeStorageEngines() : ['myisam_tables' => []];
        $charset = $optimizer ? $optimizer->analyzeCharset() : ['outdated_tables' => []];
        $autoloadDetailed = $optimizer ? $optimizer->analyzeAutoloadDetailed() : ['large_options' => [], 'total_size_mb' => 0];
        $pluginOpportunities = $pluginOptimizer ? $pluginOptimizer->analyzeInstalledPlugins() : ['opportunities' => []];
        $healthScore = $reportService ? $reportService->getHealthScore() : ['score' => 0, 'grade' => 'N/A', 'status' => 'unknown', 'issues' => [], 'recommendations' => []];
        $trends = $reportService ? $reportService->analyzeTrends() : ['status' => 'insufficient_data'];
        
        ob_start();
        
        // Pannello Introduttivo
        ?>
        <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                💾 Ottimizzazione Database
            </h2>
            <p style="font-size: 18px; line-height: 1.6; margin-bottom: 25px; opacity: 0.95;">
                <?php esc_html_e('Il database è il cuore del tuo WordPress. Queste operazioni lo mantengono veloce, leggero e ottimizzato.', 'fp-performance-suite'); ?>
            </p>
            
            <div class="fp-ps-grid three" style="gap: 20px;">
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">🧹</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Pulizia', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Rimuove dati obsoleti come revisioni, bozze automatiche, spam e transient scaduti', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">⚡</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Ottimizzazione', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Riorganizza tabelle, converte a InnoDB, aggiunge indici e recupera spazio sprecato', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">📊</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Monitoraggio', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Analizza query lente, identifica problemi e fornisce raccomandazioni proattive', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Alert Sicurezza -->
        <div class="notice notice-info inline" style="margin-bottom: 25px; border-left-color: #10b981;">
            <p style="margin: 0.5em 0;">
                <strong>🛡️ <?php esc_html_e('Sicurezza Garantita:', 'fp-performance-suite'); ?></strong> 
                <?php esc_html_e('Viene creato automaticamente un backup completo prima di ogni operazione critica.', 'fp-performance-suite'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-logs')); ?>" style="text-decoration: none;">
                    <?php esc_html_e('Visualizza log operazioni →', 'fp-performance-suite'); ?>
                </a>
            </p>
        </div>

        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <?php if (!$optimizer || !$reportService || !$pluginOptimizer) : ?>
            <div class="notice notice-warning">
                <h3><?php esc_html_e('⚠️ Funzionalità Avanzate Non Disponibili', 'fp-performance-suite'); ?></h3>
                <p><?php esc_html_e('Alcune funzionalità avanzate di ottimizzazione database non sono disponibili. Per abilitarle, carica questi file sul server:', 'fp-performance-suite'); ?></p>
                <ul style="list-style-type: disc; margin-left: 20px;">
                    <?php if (!$optimizer) : ?>
                        <li><code>src/Services/DB/DatabaseOptimizer.php</code></li>
                    <?php endif; ?>
                    <?php if (!$reportService) : ?>
                        <li><code>src/Services/DB/DatabaseReportService.php</code></li>
                    <?php endif; ?>
                    <?php if (!$pluginOptimizer) : ?>
                        <li><code>src/Services/DB/PluginSpecificOptimizer.php</code></li>
                    <?php endif; ?>
                    <?php if (!$queryMonitor) : ?>
                        <li><code>src/Services/DB/DatabaseQueryMonitor.php</code></li>
                    <?php endif; ?>
                </ul>
                <p><?php esc_html_e('Dopo aver caricato i file, riattiva il plugin per vedere le nuove funzionalità.', 'fp-performance-suite'); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Cleanup Tools Section -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Cleanup Tools', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-database&tab=operations">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="run_cleanup" value="1" />
                <input type="hidden" name="current_tab" value="operations" />
                <p><?php esc_html_e('Select components to clean. Red actions require PROCEDI confirmation.', 'fp-performance-suite'); ?></p>
                <div class="fp-ps-grid two">
                    <?php foreach ($tasks as $key => $label) : ?>
                        <label class="fp-ps-toggle">
                            <span class="info">
                                <strong><?php echo esc_html($label); ?></strong>
                            </span>
                            <input type="checkbox" name="cleanup_scope[]" value="<?php echo esc_attr($key); ?>" data-risk="<?php echo $key === 'optimize_tables' ? 'red' : 'amber'; ?>" />
                        </label>
                    <?php endforeach; ?>
                </div>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Dry run', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="dry_run" value="1" checked data-risk="green" />
                </label>
                <p>
                    <button type="submit" class="button button-primary" data-risk="red"><?php esc_html_e('Execute Cleanup', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            <?php if (!empty($results)) : ?>
                <table class="fp-ps-table">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Task', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Found', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Deleted', 'fp-performance-suite'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $task => $data) : ?>
                        <tr>
                            <td><?php echo esc_html($tasks[$task] ?? $task); ?></td>
                            <td>
                                <?php
                                $found = $data['found'] ?? 0;
                                if (!empty($data['site_found'])) {
                                    $found .= sprintf(' (+%d site)', (int) $data['site_found']);
                                }
                                echo esc_html((string) $found);
                                ?>
                            </td>
                            <td>
                                <?php
                                if (isset($data['tables']) && is_array($data['tables'])) {
                                    $tableList = implode(', ', $data['tables']);
                                    echo esc_html($tableList !== '' ? $tableList : '-');
                                } else {
                                    $deleted = $data['deleted'] ?? '-';
                                    if (!empty($data['site_deleted'])) {
                                        $deleted .= sprintf(' (+%d site)', (int) $data['site_deleted']);
                                    }
                                    echo esc_html((string) $deleted);
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
        
        <!-- Scheduler Section -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Scheduler', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-database&tab=operations">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="save_db_settings" value="1" />
                <input type="hidden" name="current_tab" value="operations" />
                <p>
                    <label for="schedule"><?php esc_html_e('Cleanup schedule', 'fp-performance-suite'); ?></label>
                    <select name="schedule" id="schedule">
                        <option value="manual" <?php selected($settings['schedule'], 'manual'); ?>><?php esc_html_e('Manual', 'fp-performance-suite'); ?></option>
                        <option value="weekly" <?php selected($settings['schedule'], 'weekly'); ?>><?php esc_html_e('Weekly', 'fp-performance-suite'); ?></option>
                        <option value="monthly" <?php selected($settings['schedule'], 'monthly'); ?>><?php esc_html_e('Monthly', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                <p>
                    <label for="batch">
                        <?php esc_html_e('Batch size', 'fp-performance-suite'); ?>
                        <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 5px;" title="<?php esc_attr_e('Numero di elementi processati per volta durante la pulizia. Valori più bassi = meno carico ma operazioni più lente. Valori più alti = più veloce ma maggior uso memoria.', 'fp-performance-suite'); ?>">ℹ️</span>
                    </label>
                    <input type="number" name="batch" id="batch" value="<?php echo esc_attr((string) $settings['batch']); ?>" min="50" max="500" placeholder="200" class="regular-text" />
                </p>
                <div class="fp-ps-input-help" style="margin-top: 8px; margin-bottom: 15px;">
                    <p style="background: #dbeafe; border-left: 3px solid #3b82f6; padding: 10px; margin: 10px 0; border-radius: 4px;">
                        💡 <strong><?php esc_html_e('Consigliato: 200 elementi', 'fp-performance-suite'); ?></strong>
                        <br><small style="color: #64748b;">
                            <?php esc_html_e('Hosting Condiviso: 100-200 | VPS: 200-300 | Dedicato: 300-500', 'fp-performance-suite'); ?>
                        </small>
                    </p>
                </div>
                <p>
                    <button type="submit" class="button button-secondary"><?php esc_html_e('Save Scheduler', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #f0f0f1; border-left: 4px solid #2271b1;">
                <h3 style="margin-top: 0;"><?php esc_html_e('Operazioni automatiche dello scheduler:', 'fp-performance-suite'); ?></h3>
                <ul style="margin: 10px 0;">
                    <li><strong>✓</strong> <?php esc_html_e('Post revisions', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Auto drafts', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Trashed posts', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Spam/trashed comments', 'fp-performance-suite'); ?></li>
                    <li><strong>✓</strong> <?php esc_html_e('Expired transients', 'fp-performance-suite'); ?></li>
                </ul>
                <p style="margin-bottom: 0;"><strong><?php esc_html_e('Escluse dallo scheduler (solo manuale):', 'fp-performance-suite'); ?></strong> <?php esc_html_e('Optimize tables, Orphan meta (post/term/user)', 'fp-performance-suite'); ?></p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #646970;"><em><?php esc_html_e('Le operazioni ad alto rischio come "Optimize tables" devono essere eseguite manualmente durante periodi di basso traffico.', 'fp-performance-suite'); ?></em></p>
            </div>
            
            <p class="description" style="margin-top: 15px;"><?php printf(esc_html__('Current overhead: %s MB', 'fp-performance-suite'), number_format_i18n($overhead, 2)); ?></p>
            <p class="description"><?php printf(esc_html__('Last automated cleanup: %s', 'fp-performance-suite'), esc_html($lastRun)); ?></p>
        </section>
        
        <!-- Database Optimizer Section -->
        <?php if ($optimizer) : ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Database Optimizer', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid two" style="margin: 20px 0;">
                <div>
                    <h3><?php esc_html_e('Dimensione Database', 'fp-performance-suite'); ?></h3>
                    <div class="fp-ps-stat-box">
                        <div class="stat-value"><?php echo esc_html(number_format_i18n($dbAnalysis['database_size']['total_mb'], 2)); ?> MB</div>
                        <div class="stat-label"><?php esc_html_e('Dimensione Totale', 'fp-performance-suite'); ?></div>
                    </div>
                    <?php if ($dbAnalysis['table_analysis']['total_overhead_mb'] > 0) : ?>
                        <div class="fp-ps-stat-box" style="margin-top: 10px;">
                            <div class="stat-value warning"><?php echo esc_html(number_format_i18n($dbAnalysis['table_analysis']['total_overhead_mb'], 2)); ?> MB</div>
                            <div class="stat-label">
                                <?php esc_html_e('Overhead Recuperabile', 'fp-performance-suite'); ?>
                                <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 5px; font-size: 14px;" title="<?php esc_attr_e('Overhead = spazio sprecato nel database che può essere recuperato ottimizzando le tabelle. Overhead >100MB indica necessità di ottimizzazione.', 'fp-performance-suite'); ?>">ℹ️</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3><?php esc_html_e('Tabelle', 'fp-performance-suite'); ?></h3>
                    <p><strong><?php esc_html_e('Totale:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($dbAnalysis['table_analysis']['total_tables'] ?? 0)); ?></p>
                    <p><strong><?php esc_html_e('Necessitano ottimizzazione:', 'fp-performance-suite'); ?></strong> 
                        <?php 
                        $tables = $dbAnalysis['table_analysis']['tables'] ?? [];
                        $needsOpt = is_array($tables) ? array_filter($tables, fn($t) => $t['needs_optimization'] ?? false) : [];
                        echo esc_html(number_format_i18n(count($needsOpt))); 
                        ?>
                    </p>
                </div>
            </div>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="optimize_all_tables" value="1" />
                <button type="submit" class="button button-primary" data-risk="amber">
                    <?php esc_html_e('Ottimizza Tutte le Tabelle', 'fp-performance-suite'); ?>
                </button>
                <p class="description"><?php esc_html_e('Questa operazione può richiedere alcuni minuti.', 'fp-performance-suite'); ?></p>
            </form>
        </section>
        <?php endif; ?>
        
        <!-- Object Cache Section -->
        <section class="fp-ps-card">
            <h2>
                <?php esc_html_e('Object Caching', 'fp-performance-suite'); ?>
                <span class="fp-ps-help-icon" style="cursor: help; color: #3b82f6; margin-left: 8px; font-size: 18px;" title="<?php esc_attr_e('Object Cache: sistema che memorizza in RAM (Redis/Memcached) i risultati delle query database per evitare di rieseguirle. Riduce il carico DB del 50-80%.', 'fp-performance-suite'); ?>">ℹ️</span>
            </h2>
            <p class="description"><?php esc_html_e('L\'object caching riduce drasticamente il numero di query database memorizzando i risultati in memoria.', 'fp-performance-suite'); ?></p>
            
            <?php if ($cacheInfo['available']) : ?>
                <div style="padding: 15px; background: #e7f7ef; border-left: 4px solid #46b450; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #46b450;">
                        ✓ <?php echo esc_html($cacheInfo['name']); ?> <?php esc_html_e('Disponibile', 'fp-performance-suite'); ?>
                    </h3>
                    <p><?php echo esc_html($cacheInfo['description']); ?></p>
                    
                    <?php if ($cacheInfo['enabled']) : ?>
                        <p><strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong> <span style="color: #46b450;">● <?php esc_html_e('Attivo', 'fp-performance-suite'); ?></span></p>
                        
                        <form method="post" style="margin-top: 15px;">
                            <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                            <input type="hidden" name="disable_object_cache" value="1" />
                            <button type="submit" class="button button-secondary"><?php esc_html_e('Disattiva Object Cache', 'fp-performance-suite'); ?></button>
                        </form>
                    <?php else : ?>
                        <p><strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong> <span style="color: #f56e28;">● <?php esc_html_e('Non attivo', 'fp-performance-suite'); ?></span></p>
                        
                        <form method="post" style="margin-top: 15px;">
                            <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                            <input type="hidden" name="enable_object_cache" value="1" />
                            <button type="submit" class="button button-primary"><?php esc_html_e('Attiva Object Cache', 'fp-performance-suite'); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <div style="padding: 15px; background: #fff3cd; border-left: 4px solid #f0b429; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #826200;">
                        ⚠ <?php esc_html_e('Object Cache Non Disponibile', 'fp-performance-suite'); ?>
                    </h3>
                    <p><?php esc_html_e('Nessun backend di object caching (Redis, Memcached, APCu) è disponibile sul tuo server.', 'fp-performance-suite'); ?></p>
                    <p><?php esc_html_e('Contatta il tuo hosting provider per abilitare Redis o Memcached per migliorare drasticamente le performance.', 'fp-performance-suite'); ?></p>
                </div>
            <?php endif; ?>
        </section>
        
        <?php
        return (string) ob_get_clean();
    }

    private function renderQueryMonitorTab(): string
    {
        ob_start();
        
        try {
            $queryMonitor = $this->container->get(DatabaseQueryMonitor::class);
            $monitorSettings = $queryMonitor->getSettings();
            $statistics = $queryMonitor->getStatistics();
        } catch (\Exception $e) {
            $queryMonitor = null;
            $monitorSettings = [];
            $statistics = [];
        }
        
        ?>
        
        <!-- Query Monitor Configuration -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('📊 Query Monitor', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Monitora le query database in tempo reale e identifica performance issues.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($queryMonitor): ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_query_monitor', 'fp_ps_query_monitor_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Abilita Query Monitor', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" 
                                       <?php checked(!empty($monitorSettings['enabled'])); ?>>
                                <?php esc_html_e('Abilita monitoraggio query', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Intercetta e analizza tutte le query database.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Soglia Query Lente', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="number" name="slow_query_threshold" value="<?php echo esc_attr($monitorSettings['slow_query_threshold'] ?? 0.005); ?>" 
                                   min="0" max="1" step="0.001" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Soglia in secondi per considerare una query lenta (default: 0.005 = 5ms).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Log Query Duplicate', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="log_duplicates" value="1" 
                                       <?php checked(!empty($monitorSettings['log_duplicates'])); ?>>
                                <?php esc_html_e('Traccia query duplicate', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Identifica query identiche eseguite multiple volte.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Salva Configurazione Query Monitor', 'fp-performance-suite')); ?>
            </form>
            
            <?php else: ?>
            
            <div class="notice notice-warning">
                <p><?php esc_html_e('DatabaseQueryMonitor non disponibile. Verifica che il servizio sia registrato correttamente.', 'fp-performance-suite'); ?></p>
            </div>
            
            <?php endif; ?>
        </section>

        <!-- Query Statistics -->
        <?php if ($queryMonitor): ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('📈 Statistiche Query', 'fp-performance-suite'); ?></h2>
            
            <?php 
            $hasStatistics = !empty($statistics) && ($statistics['total_queries'] ?? 0) > 0;
            $savequeriesDefined = defined('SAVEQUERIES') && SAVEQUERIES;
            $saveQueriesAdminOnly = get_option('fp_ps_savequeries_admin_only', false);
            ?>
            
            <?php if (!$savequeriesDefined && !$saveQueriesAdminOnly): ?>
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 10px 0; font-weight: 600; color: #856404;">
                    ⚠️ <?php esc_html_e('Query logging non abilitato', 'fp-performance-suite'); ?>
                </p>
                <p style="margin: 0 0 10px 0; color: #856404;">
                    <?php esc_html_e('Il Query Monitor funziona meglio con SAVEQUERIES abilitato. Puoi:', 'fp-performance-suite'); ?>
                </p>
                <ul style="margin: 10px 0 10px 20px; color: #856404;">
                    <li style="margin-bottom: 8px;">
                        <strong><?php esc_html_e('Opzione 1:', 'fp-performance-suite'); ?></strong> 
                        <?php esc_html_e('Attiva automaticamente solo per gli amministratori (consigliato)', 'fp-performance-suite'); ?>
                        <form method="post" style="display: inline-block; margin-left: 10px;">
                            <?php wp_nonce_field('fp_ps_enable_savequeries', 'fp_ps_enable_savequeries_nonce'); ?>
                            <input type="hidden" name="action" value="enable_savequeries_admin">
                            <button type="submit" class="button button-primary button-small">
                                <?php esc_html_e('Abilita per Admin', 'fp-performance-suite'); ?>
                            </button>
                        </form>
                    </li>
                    <li>
                        <strong><?php esc_html_e('Opzione 2:', 'fp-performance-suite'); ?></strong> 
                        <?php esc_html_e('Aggiungi manualmente al file wp-config.php (prima di "Buon blogging!"):', 'fp-performance-suite'); ?>
                        <code style="display: block; background: #f8f9fa; padding: 10px; border-radius: 4px; color: #d63384; font-family: monospace; margin-top: 5px;">
                            define('SAVEQUERIES', true);
                        </code>
                    </li>
                </ul>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #856404;">
                    ⚡ <?php esc_html_e('Nota: L\'opzione 1 attiva il logging solo quando un amministratore è loggato, minimizzando l\'impatto sulle performance.', 'fp-performance-suite'); ?>
                </p>
            </div>
            <?php elseif ($saveQueriesAdminOnly && !$savequeriesDefined): ?>
            <div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 10px 0; font-weight: 600; color: #0c5460;">
                    ✅ <?php esc_html_e('Query logging abilitato per gli amministratori', 'fp-performance-suite'); ?>
                </p>
                <p style="margin: 0; color: #0c5460; font-size: 14px;">
                    <?php esc_html_e('Il logging delle query è attivo. Le statistiche verranno raccolte durante la navigazione.', 'fp-performance-suite'); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <div class="fp-ps-grid four" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Query Totali', 'fp-performance-suite'); ?></div>
                    <div class="stat-value"><?php echo esc_html($statistics['total_queries'] ?? 0); ?></div>
                    <p class="description">
                        <?php esc_html_e('Query eseguite in questa sessione', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('Query Lente', 'fp-performance-suite'); ?></div>
                    <div class="stat-value"><?php echo esc_html($statistics['slow_queries'] ?? 0); ?></div>
                    <p class="description">
                        <?php esc_html_e('Query che superano la soglia', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Query Duplicate', 'fp-performance-suite'); ?></div>
                    <div class="stat-value"><?php echo esc_html($statistics['duplicate_queries'] ?? 0); ?></div>
                    <p class="description">
                        <?php esc_html_e('Query identiche ripetute', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #43e97b;">
                    <div class="stat-label"><?php esc_html_e('Tempo Totale', 'fp-performance-suite'); ?></div>
                    <div class="stat-value"><?php echo esc_html(round($statistics['total_query_time'] ?? 0, 3)); ?>s</div>
                    <p class="description">
                        <?php esc_html_e('Tempo totale di esecuzione query', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Query Monitor Benefits -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🎯 Benefici Query Monitor', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid three" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Identificazione Problemi', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">🔍</div>
                    <p class="description">
                        <?php esc_html_e('Trova query lente e problematiche', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('Ottimizzazione Database', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">⚡</div>
                    <p class="description">
                        <?php esc_html_e('Migliora le performance del database', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Monitoraggio Real-time', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">📊</div>
                    <p class="description">
                        <?php esc_html_e('Statistiche in tempo reale', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>
        
        <?php
        return (string) ob_get_clean();
    }

    private function renderQueryCacheTab(): string
    {
        ob_start();
        
        try {
            $queryCache = $this->container->get(QueryCacheManager::class);
            $cacheSettings = $queryCache->getSettings();
            $cacheStats = $queryCache->getStats();
        } catch (\Exception $e) {
            $queryCache = null;
            $cacheSettings = [];
            $cacheStats = [];
        }
        
        ?>
        
        <!-- Query Cache Configuration -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('⚡ Query Cache', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Cache i risultati delle query database per migliorare le performance.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($queryCache): ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_query_cache', 'fp_ps_query_cache_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Abilita Query Cache', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" 
                                       <?php checked(!empty($cacheSettings['enabled'])); ?>>
                                <?php esc_html_e('Abilita cache query database', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Cache i risultati delle query per ridurre il carico sul database.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('TTL Cache', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="number" name="ttl" value="<?php echo esc_attr($cacheSettings['ttl'] ?? 3600); ?>" 
                                   min="0" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Durata cache in secondi (default: 3600 = 1 ora).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Cache Solo SELECT', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="cache_select_only" value="1" 
                                       <?php checked(!empty($cacheSettings['cache_select_only'])); ?>>
                                <?php esc_html_e('Cache solo query SELECT', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Cache solo le query di lettura (SELECT) per sicurezza.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Dimensione Massima Cache', 'fp-performance-suite'); ?></th>
                        <td>
                            <input type="number" name="max_cache_size" value="<?php echo esc_attr($cacheSettings['max_cache_size'] ?? 1000); ?>" 
                                   min="0" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Numero massimo di query cachate (default: 1000).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(__('Salva Configurazione Query Cache', 'fp-performance-suite')); ?>
            </form>
            
            <?php else: ?>
            
            <div class="notice notice-warning">
                <p><?php esc_html_e('QueryCacheManager non disponibile. Verifica che il servizio sia registrato correttamente.', 'fp-performance-suite'); ?></p>
            </div>
            
            <?php endif; ?>
        </section>

        <!-- Query Cache Statistics -->
        <?php if ($queryCache): ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('📊 Statistiche Query Cache', 'fp-performance-suite'); ?></h2>
            
            <?php 
            $hasData = !empty($cacheStats) && (($cacheStats['hits'] ?? 0) + ($cacheStats['misses'] ?? 0)) > 0;
            $cacheEnabled = !empty($cacheSettings['enabled']);
            ?>
            
            <?php if (!$hasData): ?>
            <div style="background: #e7f3ff; border-left: 4px solid #2196f3; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <p style="margin: 0 0 10px 0; font-weight: 600; color: #0d47a1;">
                    ℹ️ <?php esc_html_e('Nessun dato disponibile', 'fp-performance-suite'); ?>
                </p>
                <p style="margin: 0; color: #1565c0; font-size: 14px;">
                    <?php if (!$cacheEnabled): ?>
                        <?php esc_html_e('La Query Cache non è attualmente abilitata. Attivala per iniziare a tracciare le statistiche.', 'fp-performance-suite'); ?>
                    <?php else: ?>
                        <?php esc_html_e('Le statistiche verranno raccolte automaticamente durante la navigazione del sito. Visita alcune pagine del frontend per vedere i dati.', 'fp-performance-suite'); ?>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 20px;">
                <form method="post" action="" style="display: inline-block;">
                    <?php wp_nonce_field('fp_ps_reset_cache_stats', 'fp_ps_reset_cache_stats_nonce'); ?>
                    <input type="hidden" name="action" value="reset_cache_stats">
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler resettare le statistiche della cache?', 'fp-performance-suite'); ?>')">
                        <?php esc_html_e('🔄 Resetta Statistiche', 'fp-performance-suite'); ?>
                    </button>
                </form>
                <span style="margin-left: 15px; color: #666; font-size: 13px;">
                    <?php esc_html_e('Le statistiche vengono aggiornate automaticamente durante l\'attività del sito.', 'fp-performance-suite'); ?>
                </span>
            </div>
            
            <div class="fp-ps-grid three" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Cache Hits', 'fp-performance-suite'); ?></div>
                    <div class="stat-value"><?php echo esc_html($cacheStats['hits'] ?? 0); ?></div>
                    <p class="description">
                        <?php esc_html_e('Query servite dalla cache', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('Cache Misses', 'fp-performance-suite'); ?></div>
                    <div class="stat-value"><?php echo esc_html($cacheStats['misses'] ?? 0); ?></div>
                    <p class="description">
                        <?php esc_html_e('Query non trovate in cache', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Hit Rate', 'fp-performance-suite'); ?></div>
                    <div class="stat-value"><?php echo esc_html($cacheStats['hit_rate'] ?? 0); ?>%</div>
                    <p class="description">
                        <?php esc_html_e('Percentuale di successo cache', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Query Cache Benefits -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🚀 Benefici Query Cache', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid three" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Performance Database', 'fp-performance-suite'); ?></div>
                    <div class="stat-value success">50-90%</div>
                    <p class="description">
                        <?php esc_html_e('Riduzione carico sul database', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('Tempi di Risposta', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">⚡</div>
                    <p class="description">
                        <?php esc_html_e('Query cachate servite istantaneamente', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Scalabilità', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">📈</div>
                    <p class="description">
                        <?php esc_html_e('Gestisce traffico elevato senza sovraccaricare DB', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>
        
        <?php
        return (string) ob_get_clean();
    }
}
