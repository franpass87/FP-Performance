<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\DatabaseQueryMonitor;
use FP\PerfSuite\Services\DB\DatabaseOptimizer;
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
        $cleaner = $this->container->get(Cleaner::class);
        $queryMonitor = $this->container->get(DatabaseQueryMonitor::class);
        $optimizer = $this->container->get(DatabaseOptimizer::class);
        $objectCache = $this->container->get(ObjectCacheManager::class);
        
        $message = '';
        $results = [];
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_db_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_db_nonce']), 'fp-ps-db')) {
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
            if (isset($_POST['enable_query_monitor'])) {
                $queryMonitor->updateSettings(['enabled' => !empty($_POST['query_monitor_enabled'])]);
                $message = __('Query Monitor settings updated.', 'fp-performance-suite');
            }
            if (isset($_POST['optimize_all_tables'])) {
                $results = $optimizer->optimizeAllTables();
                $message = sprintf(__('Ottimizzate %d tabelle.', 'fp-performance-suite'), count($results['optimized'] ?? []));
            }
            if (isset($_POST['enable_object_cache'])) {
                $result = $objectCache->install();
                $message = $result['message'];
            }
            if (isset($_POST['disable_object_cache'])) {
                $result = $objectCache->uninstall();
                $message = $result['message'];
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
        // Ottieni dati per le nuove sezioni
        $queryAnalysis = $queryMonitor->getLastAnalysis();
        $dbAnalysis = $optimizer->analyze();
        $cacheInfo = $objectCache->getBackendInfo();
        $cacheStats = $objectCache->getStatistics();
        
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- Query Monitor Section -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Database Query Monitor', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Monitora le query database in tempo reale e identifica colli di bottiglia.', 'fp-performance-suite'); ?></p>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="enable_query_monitor" value="1" />
                
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
            <h2><?php esc_html_e('Object Caching', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('L\'object caching riduce drasticamente il numero di query database memorizzando i risultati in memoria.', 'fp-performance-suite'); ?></p>
            
            <?php if ($cacheInfo['available']) : ?>
                <div style="padding: 15px; background: #e7f7ef; border-left: 4px solid #46b450; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #46b450;">
                        ✓ <?php echo esc_html($cacheInfo['name']); ?> <?php esc_html_e('Disponibile', 'fp-performance-suite'); ?>
                    </h3>
                    <p><?php echo esc_html($cacheInfo['description']); ?></p>
                    
                    <?php if ($cacheInfo['enabled']) : ?>
                        <p><strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong> <span style="color: #46b450;">● <?php esc_html_e('Attivo', 'fp-performance-suite'); ?></span></p>
                        
                        <?php if ($cacheStats['enabled']) : ?>
                            <div class="fp-ps-grid three" style="margin: 20px 0;">
                                <div class="fp-ps-stat-box">
                                    <div class="stat-value"><?php echo esc_html(number_format_i18n($cacheStats['hits'] ?? 0)); ?></div>
                                    <div class="stat-label"><?php esc_html_e('Cache Hits', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-stat-box">
                                    <div class="stat-value"><?php echo esc_html(number_format_i18n($cacheStats['misses'] ?? 0)); ?></div>
                                    <div class="stat-label"><?php esc_html_e('Cache Misses', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-stat-box">
                                    <div class="stat-value"><?php echo esc_html(number_format_i18n($cacheStats['ratio'] ?? 0, 1)); ?>%</div>
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
                            <div class="stat-label"><?php esc_html_e('Overhead Recuperabile', 'fp-performance-suite'); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3><?php esc_html_e('Tabelle', 'fp-performance-suite'); ?></h3>
                    <p><strong><?php esc_html_e('Totale:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(number_format_i18n($dbAnalysis['table_analysis']['total_tables'])); ?></p>
                    <p><strong><?php esc_html_e('Necessitano ottimizzazione:', 'fp-performance-suite'); ?></strong> 
                        <?php 
                        $needsOpt = array_filter($dbAnalysis['table_analysis']['tables'], fn($t) => $t['needs_optimization']);
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
        
        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Scheduler', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="save_db_settings" value="1" />
                <p>
                    <label for="schedule"><?php esc_html_e('Cleanup schedule', 'fp-performance-suite'); ?></label>
                    <select name="schedule" id="schedule">
                        <option value="manual" <?php selected($settings['schedule'], 'manual'); ?>><?php esc_html_e('Manual', 'fp-performance-suite'); ?></option>
                        <option value="weekly" <?php selected($settings['schedule'], 'weekly'); ?>><?php esc_html_e('Weekly', 'fp-performance-suite'); ?></option>
                        <option value="monthly" <?php selected($settings['schedule'], 'monthly'); ?>><?php esc_html_e('Monthly', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                <p>
                    <label for="batch"><?php esc_html_e('Batch size', 'fp-performance-suite'); ?></label>
                    <input type="number" name="batch" id="batch" value="<?php echo esc_attr((string) $settings['batch']); ?>" min="50" max="500" />
                </p>
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
            <form method="post">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="run_cleanup" value="1" />
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
        <?php
        return (string) ob_get_clean();
    }
}
