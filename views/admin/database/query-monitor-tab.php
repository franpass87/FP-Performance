<?php
/**
 * Database Query Monitor Tab View
 * 
 * @var \FP\PerfSuite\Services\DB\DatabaseQueryMonitor|null $queryMonitor
 * @var array $monitorSettings
 * @var array $statistics
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="fp-ps-query-monitor-tab">
    
    <?php if ($queryMonitor === null): ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e('Query Monitor service is not available. Please ensure the service is properly configured.', 'fp-performance-suite'); ?></p>
        </div>
    <?php else: ?>
    
    <!-- Query Monitor Overview -->
    <div class="fp-ps-card fp-ps-mb-lg">
        <h3>📊 <?php esc_html_e('Query Statistics', 'fp-performance-suite'); ?></h3>
        
        <div class="fp-ps-grid four" style="margin-top: 16px;">
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($statistics['total_queries'] ?? 0); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Total Queries', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html(number_format($statistics['avg_time'] ?? 0, 4)); ?>s</div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Avg Time', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($statistics['slow_queries'] ?? 0); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Slow Queries', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html(number_format($statistics['total_time'] ?? 0, 2)); ?>s</div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Total Time', 'fp-performance-suite'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Monitor Settings -->
    <div class="fp-ps-card fp-ps-mb-lg">
        <h3>⚙️ <?php esc_html_e('Monitor Settings', 'fp-performance-suite'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('fp_ps_query_monitor_settings', 'fp_ps_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="monitor_enabled"><?php esc_html_e('Enable Monitoring', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label class="fp-ps-toggle">
                            <input type="checkbox" name="monitor_enabled" id="monitor_enabled" value="1" 
                                <?php checked($monitorSettings['enabled'] ?? false); ?>>
                            <span class="fp-ps-toggle-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e('Enable database query monitoring.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="slow_query_threshold"><?php esc_html_e('Slow Query Threshold', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="slow_query_threshold" id="slow_query_threshold" 
                            value="<?php echo esc_attr($monitorSettings['slow_threshold'] ?? 0.5); ?>" 
                            step="0.01" min="0.01" max="10" class="small-text"> 
                        <?php esc_html_e('seconds', 'fp-performance-suite'); ?>
                        <p class="description"><?php esc_html_e('Queries taking longer than this will be flagged as slow.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="log_queries"><?php esc_html_e('Log Queries', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label class="fp-ps-toggle">
                            <input type="checkbox" name="log_queries" id="log_queries" value="1" 
                                <?php checked($monitorSettings['log_queries'] ?? false); ?>>
                            <span class="fp-ps-toggle-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e('Log slow queries for analysis.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p>
                <button type="submit" name="fp_ps_save_monitor_settings" class="button button-primary">
                    💾 <?php esc_html_e('Save Settings', 'fp-performance-suite'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <!-- Recent Slow Queries -->
    <?php if (!empty($statistics['recent_slow'])): ?>
    <div class="fp-ps-card">
        <h3>🐢 <?php esc_html_e('Recent Slow Queries', 'fp-performance-suite'); ?></h3>
        
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Query', 'fp-performance-suite'); ?></th>
                    <th width="100"><?php esc_html_e('Time', 'fp-performance-suite'); ?></th>
                    <th width="150"><?php esc_html_e('Date', 'fp-performance-suite'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($statistics['recent_slow'] as $query): ?>
                <tr>
                    <td><code style="font-size: 11px;"><?php echo esc_html(substr($query['sql'], 0, 200)) . (strlen($query['sql']) > 200 ? '...' : ''); ?></code></td>
                    <td><?php echo esc_html(number_format($query['time'], 4)); ?>s</td>
                    <td><?php echo esc_html($query['date']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="fp-ps-card">
        <p class="description"><?php esc_html_e('No slow queries recorded. This is good!', 'fp-performance-suite'); ?></p>
    </div>
    <?php endif; ?>
    
    <?php endif; ?>
    
</div>

