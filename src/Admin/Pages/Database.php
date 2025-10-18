<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\DB\QueryCacheManager;

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
        $queryCache = $this->container->get(QueryCacheManager::class);
        $message = '';
        $results = [];
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_db_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_db_nonce']), 'fp-ps-db')) {
            if (isset($_POST['save_db_settings'])) {
                $cleaner->update([
                    'schedule' => sanitize_text_field($_POST['schedule'] ?? 'manual'),
                    'batch' => (int) ($_POST['batch'] ?? 200),
                    'auto_optimize' => !empty($_POST['auto_optimize']),
                    'optimize_schedule' => sanitize_text_field($_POST['optimize_schedule'] ?? 'weekly'),
                    'auto_backup' => !empty($_POST['auto_backup']),
                    'notify_on_cleanup' => !empty($_POST['notify_on_cleanup']),
                    'cleanup_email' => sanitize_email($_POST['cleanup_email'] ?? get_option('admin_email')),
                    'max_revisions' => (int) ($_POST['max_revisions'] ?? 5),
                ]);
                $message = __('Database settings updated.', 'fp-performance-suite');
            }
            if (isset($_POST['run_cleanup'])) {
                $scope = array_map('sanitize_text_field', (array) ($_POST['cleanup_scope'] ?? []));
                $dry = !empty($_POST['dry_run']);
                $results = $cleaner->cleanup($scope, $dry, (int) ($_POST['batch'] ?? 200));
                $message = $dry ? __('Dry run completed.', 'fp-performance-suite') : __('Cleanup completed.', 'fp-performance-suite');
            }
            if (isset($_POST['save_query_cache'])) {
                $queryCache->update([
                    'enabled' => !empty($_POST['query_cache_enabled']),
                    'ttl' => (int) ($_POST['query_cache_ttl'] ?? 3600),
                    'max_size' => (int) ($_POST['query_cache_max_size'] ?? 1000),
                    'cache_selects_only' => !empty($_POST['query_cache_selects_only']),
                ]);
                $message = __('Query cache settings saved.', 'fp-performance-suite');
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
        ob_start();
        ?>
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
                
                <h3 style="margin-top: 30px;"><?php esc_html_e('Advanced Scheduling Options', 'fp-performance-suite'); ?></h3>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Auto-optimize tables', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="auto_optimize" value="1" <?php checked($settings['auto_optimize'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Automatically optimize database tables during scheduled maintenance. WARNING: This should only be enabled during low-traffic periods.', 'fp-performance-suite'); ?>
                </p>
                
                <p style="margin-left: 30px;">
                    <label for="optimize_schedule"><?php esc_html_e('Table optimization frequency', 'fp-performance-suite'); ?></label>
                    <select name="optimize_schedule" id="optimize_schedule">
                        <option value="weekly" <?php selected($settings['optimize_schedule'] ?? 'weekly', 'weekly'); ?>><?php esc_html_e('Weekly', 'fp-performance-suite'); ?></option>
                        <option value="biweekly" <?php selected($settings['optimize_schedule'] ?? 'weekly', 'biweekly'); ?>><?php esc_html_e('Every 2 weeks', 'fp-performance-suite'); ?></option>
                        <option value="monthly" <?php selected($settings['optimize_schedule'] ?? 'weekly', 'monthly'); ?>><?php esc_html_e('Monthly', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Auto-backup before cleanup', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="auto_backup" value="1" <?php checked($settings['auto_backup'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Create automatic database backup before running cleanup operations.', 'fp-performance-suite'); ?>
                </p>
                
                <h3 style="margin-top: 30px;"><?php esc_html_e('Notifications', 'fp-performance-suite'); ?></h3>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Email notifications on cleanup', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="notify_on_cleanup" value="1" <?php checked($settings['notify_on_cleanup'] ?? false); ?> />
                </label>
                
                <p style="margin-left: 30px;">
                    <label for="cleanup_email"><?php esc_html_e('Notification email', 'fp-performance-suite'); ?></label>
                    <input type="email" name="cleanup_email" id="cleanup_email" value="<?php echo esc_attr($settings['cleanup_email'] ?? get_option('admin_email')); ?>" class="regular-text">
                </p>
                
                <h3 style="margin-top: 30px;"><?php esc_html_e('Cleanup Limits', 'fp-performance-suite'); ?></h3>
                
                <p>
                    <label for="max_revisions"><?php esc_html_e('Keep last N revisions per post', 'fp-performance-suite'); ?></label>
                    <input type="number" name="max_revisions" id="max_revisions" value="<?php echo esc_attr((string) ($settings['max_revisions'] ?? 5)); ?>" min="1" max="50" class="small-text">
                    <span class="description"><?php esc_html_e('Number of most recent revisions to keep (older will be deleted)', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <button type="submit" class="button button-primary button-large"><?php esc_html_e('Save All Database Settings', 'fp-performance-suite'); ?></button>
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
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>🚀 <?php esc_html_e('Query Cache', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Cache delle query database pesanti per ridurre il carico sul database e migliorare i tempi di risposta.', 'fp-performance-suite'); ?></p>
            
            <?php 
            $queryCacheSettings = $queryCache->settings();
            $queryCacheStats = $queryCache->getStats();
            ?>
            
            <?php if ($queryCacheSettings['enabled']): ?>
                <div class="notice notice-success inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong>
                        <?php printf(
                            esc_html__('Attivo - %d queries cached, Hit rate: %.1f%%', 'fp-performance-suite'),
                            $queryCacheStats['size'],
                            $queryCacheStats['hit_rate']
                        ); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-db', 'fp_ps_db_nonce'); ?>
                <input type="hidden" name="save_query_cache" value="1" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Query Cache', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Memorizza i risultati delle query SELECT per evitare esecuzioni ripetute.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduzione del 40-60% delle query database, tempi di risposta più veloci per query complesse.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Dati potrebbero non essere aggiornati immediatamente. Usa TTL basso per dati che cambiano frequentemente.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="query_cache_enabled" value="1" <?php checked($queryCacheSettings['enabled']); ?> />
                </label>
                
                <p>
                    <label for="query_cache_ttl"><?php esc_html_e('Cache TTL (secondi)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="query_cache_ttl" id="query_cache_ttl" value="<?php echo esc_attr((string) $queryCacheSettings['ttl']); ?>" min="60" max="86400" class="small-text" />
                    <span class="description"><?php esc_html_e('Durata cache per query (60-86400 secondi)', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="query_cache_max_size"><?php esc_html_e('Max queries cached', 'fp-performance-suite'); ?></label>
                    <input type="number" name="query_cache_max_size" id="query_cache_max_size" value="<?php echo esc_attr((string) $queryCacheSettings['max_size']); ?>" min="100" max="10000" class="small-text" />
                    <span class="description"><?php esc_html_e('Numero massimo di query da mantenere in cache', 'fp-performance-suite'); ?></span>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Cache solo SELECT queries', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Raccomandato: cache solo query di lettura, non INSERT/UPDATE/DELETE', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="query_cache_selects_only" value="1" <?php checked($queryCacheSettings['cache_selects_only']); ?> />
                </label>
                
                <?php if (!empty($queryCacheStats) && $queryCacheSettings['enabled']): ?>
                <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                    <h4 style="margin-top: 0;"><?php esc_html_e('Statistiche Cache', 'fp-performance-suite'); ?></h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px;">
                        <div>
                            <div style="font-size: 12px; color: #666;"><?php esc_html_e('Cache Hits', 'fp-performance-suite'); ?></div>
                            <div style="font-size: 24px; font-weight: bold; color: #00a32a;"><?php echo number_format($queryCacheStats['hits']); ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #666;"><?php esc_html_e('Cache Misses', 'fp-performance-suite'); ?></div>
                            <div style="font-size: 24px; font-weight: bold; color: #d63638;"><?php echo number_format($queryCacheStats['misses']); ?></div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #666;"><?php esc_html_e('Hit Rate', 'fp-performance-suite'); ?></div>
                            <div style="font-size: 24px; font-weight: bold;"><?php echo number_format($queryCacheStats['hit_rate'], 1); ?>%</div>
                        </div>
                        <div>
                            <div style="font-size: 12px; color: #666;"><?php esc_html_e('Queries Cached', 'fp-performance-suite'); ?></div>
                            <div style="font-size: 24px; font-weight: bold;"><?php echo number_format($queryCacheStats['size']); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin: 15px 0;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Quando usare Query Cache:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Siti con query complesse e pesanti', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Dati che non cambiano frequentemente', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Complementare all\'Object Cache per massime performance', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Monitoring: verifica l\'hit rate, deve essere >70% per essere efficace', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Query Cache', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
