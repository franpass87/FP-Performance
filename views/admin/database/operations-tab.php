<?php
/**
 * Database Operations Tab View
 * 
 * @var \FP\PerfSuite\Services\DB\Cleaner $cleaner
 * @var \FP\PerfSuite\Services\DB\DatabaseQueryMonitor|null $queryMonitor
 * @var \FP\PerfSuite\Services\DB\DatabaseOptimizer|null $optimizer
 * @var \FP\PerfSuite\Services\Cache\ObjectCacheManager $objectCache
 * @var \FP\PerfSuite\Services\DB\PluginSpecificOptimizer|null $pluginOptimizer
 * @var \FP\PerfSuite\Services\DB\DatabaseReportService|null $reportService
 * @var string $message
 * @var array $results
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get cleaner status safely
$cleanerStatus = [];
try {
    $cleanerStatus = $cleaner->status();
} catch (\Throwable $e) {
    $cleanerStatus = ['error' => $e->getMessage()];
}

// Get database stats
global $wpdb;
$tableStatus = $wpdb->get_results("SHOW TABLE STATUS LIKE '{$wpdb->prefix}%'");
$totalDataSize = 0;
$totalIndexSize = 0;
$totalOverhead = 0;
$tableCount = 0;

foreach ($tableStatus as $table) {
    $totalDataSize += $table->Data_length ?? 0;
    $totalIndexSize += $table->Index_length ?? 0;
    $totalOverhead += $table->Data_free ?? 0;
    $tableCount++;
}

// Format sizes
$formatSize = function($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
};
?>

<div class="fp-ps-operations-tab">
    
    <?php if (!empty($message)): ?>
        <div class="notice notice-success">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($cleanerStatus['error'])): ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($cleanerStatus['error']); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Database Overview -->
    <div class="fp-ps-card fp-ps-mb-lg">
        <h3>📊 <?php esc_html_e('Database Overview', 'fp-performance-suite'); ?></h3>
        
        <div class="fp-ps-grid four" style="margin-top: 16px;">
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($tableCount); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Tables', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($formatSize($totalDataSize)); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Data Size', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($formatSize($totalIndexSize)); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Index Size', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box <?php echo $totalOverhead > 1048576 ? 'fp-ps-warning' : ''; ?>">
                <div class="fp-ps-stat-value"><?php echo esc_html($formatSize($totalOverhead)); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Overhead', 'fp-performance-suite'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Cleanup Operations -->
    <div class="fp-ps-card fp-ps-mb-lg">
        <h3>🧹 <?php esc_html_e('Cleanup Operations', 'fp-performance-suite'); ?></h3>
        <p class="description">
            <?php esc_html_e('Remove unnecessary data to optimize database performance.', 'fp-performance-suite'); ?>
        </p>
        
        <form method="post" action="" class="fp-ps-cleanup-form">
            <?php wp_nonce_field('fp_ps_database_cleanup', 'fp_ps_nonce'); ?>
            
            <div class="fp-ps-checkbox-grid" style="margin: 16px 0;">
                <label class="fp-ps-checkbox-item">
                    <input type="checkbox" name="cleanup[]" value="revisions" checked>
                    <span><?php esc_html_e('Post Revisions', 'fp-performance-suite'); ?></span>
                </label>
                <label class="fp-ps-checkbox-item">
                    <input type="checkbox" name="cleanup[]" value="auto_drafts" checked>
                    <span><?php esc_html_e('Auto Drafts', 'fp-performance-suite'); ?></span>
                </label>
                <label class="fp-ps-checkbox-item">
                    <input type="checkbox" name="cleanup[]" value="trashed_posts" checked>
                    <span><?php esc_html_e('Trashed Posts', 'fp-performance-suite'); ?></span>
                </label>
                <label class="fp-ps-checkbox-item">
                    <input type="checkbox" name="cleanup[]" value="spam_comments" checked>
                    <span><?php esc_html_e('Spam Comments', 'fp-performance-suite'); ?></span>
                </label>
                <label class="fp-ps-checkbox-item">
                    <input type="checkbox" name="cleanup[]" value="trashed_comments" checked>
                    <span><?php esc_html_e('Trashed Comments', 'fp-performance-suite'); ?></span>
                </label>
                <label class="fp-ps-checkbox-item">
                    <input type="checkbox" name="cleanup[]" value="transients">
                    <span><?php esc_html_e('Expired Transients', 'fp-performance-suite'); ?></span>
                </label>
                <label class="fp-ps-checkbox-item">
                    <input type="checkbox" name="cleanup[]" value="orphan_meta">
                    <span><?php esc_html_e('Orphaned Meta', 'fp-performance-suite'); ?></span>
                </label>
            </div>
            
            <button type="submit" name="fp_ps_cleanup" class="button button-primary">
                🧹 <?php esc_html_e('Run Cleanup', 'fp-performance-suite'); ?>
            </button>
        </form>
    </div>
    
    <!-- Table Optimization -->
    <div class="fp-ps-card fp-ps-mb-lg">
        <h3>⚡ <?php esc_html_e('Table Optimization', 'fp-performance-suite'); ?></h3>
        <p class="description">
            <?php esc_html_e('Optimize tables to reclaim space and improve query performance.', 'fp-performance-suite'); ?>
        </p>
        
        <?php if ($totalOverhead > 0): ?>
            <div class="notice notice-warning inline" style="margin: 16px 0;">
                <p>
                    <strong><?php esc_html_e('Optimization Recommended:', 'fp-performance-suite'); ?></strong>
                    <?php printf(
                        /* translators: %s: overhead size */
                        esc_html__('You can recover %s of overhead space by optimizing your tables.', 'fp-performance-suite'),
                        esc_html($formatSize($totalOverhead))
                    ); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <form method="post" action="">
            <?php wp_nonce_field('fp_ps_optimize_tables', 'fp_ps_nonce'); ?>
            <button type="submit" name="fp_ps_optimize" class="button button-primary">
                ⚡ <?php esc_html_e('Optimize Tables', 'fp-performance-suite'); ?>
            </button>
        </form>
    </div>
    
    <!-- Scheduled Cleanup -->
    <div class="fp-ps-card">
        <h3>📅 <?php esc_html_e('Scheduled Cleanup', 'fp-performance-suite'); ?></h3>
        <p class="description">
            <?php esc_html_e('Configure automatic database cleanup schedule.', 'fp-performance-suite'); ?>
        </p>
        
        <form method="post" action="">
            <?php wp_nonce_field('fp_ps_schedule_cleanup', 'fp_ps_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cleanup_schedule"><?php esc_html_e('Schedule', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <select name="cleanup_schedule" id="cleanup_schedule">
                            <option value="manual" <?php selected($cleanerStatus['schedule'] ?? 'manual', 'manual'); ?>>
                                <?php esc_html_e('Manual Only', 'fp-performance-suite'); ?>
                            </option>
                            <option value="daily" <?php selected($cleanerStatus['schedule'] ?? '', 'daily'); ?>>
                                <?php esc_html_e('Daily', 'fp-performance-suite'); ?>
                            </option>
                            <option value="weekly" <?php selected($cleanerStatus['schedule'] ?? '', 'weekly'); ?>>
                                <?php esc_html_e('Weekly', 'fp-performance-suite'); ?>
                            </option>
                            <option value="monthly" <?php selected($cleanerStatus['schedule'] ?? '', 'monthly'); ?>>
                                <?php esc_html_e('Monthly', 'fp-performance-suite'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
                <?php if (!empty($cleanerStatus['next_run'])): ?>
                <tr>
                    <th scope="row"><?php esc_html_e('Next Run', 'fp-performance-suite'); ?></th>
                    <td>
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $cleanerStatus['next_run'])); ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($cleanerStatus['last_run'])): ?>
                <tr>
                    <th scope="row"><?php esc_html_e('Last Run', 'fp-performance-suite'); ?></th>
                    <td>
                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $cleanerStatus['last_run'])); ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
            
            <button type="submit" name="fp_ps_save_schedule" class="button button-primary">
                💾 <?php esc_html_e('Save Schedule', 'fp-performance-suite'); ?>
            </button>
        </form>
    </div>
    
</div>

