<?php
/**
 * Database Query Cache Tab View
 * 
 * @var \FP\PerfSuite\Services\DB\QueryCacheManager|null $queryCache
 * @var array $cacheSettings
 * @var array $stats
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="fp-ps-query-cache-tab">
    
    <?php if ($queryCache === null): ?>
        <div class="notice notice-warning">
            <p><?php esc_html_e('Query Cache service is not available. Please ensure the service is properly configured.', 'fp-performance-suite'); ?></p>
        </div>
    <?php else: ?>
    
    <!-- Cache Statistics -->
    <div class="fp-ps-card fp-ps-mb-lg">
        <h3>📊 <?php esc_html_e('Cache Statistics', 'fp-performance-suite'); ?></h3>
        
        <div class="fp-ps-grid four" style="margin-top: 16px;">
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($stats['hits'] ?? 0); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Cache Hits', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($stats['misses'] ?? 0); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Cache Misses', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html(($stats['hits'] ?? 0) + ($stats['misses'] ?? 0) > 0 ? number_format((($stats['hits'] ?? 0) / (($stats['hits'] ?? 0) + ($stats['misses'] ?? 0))) * 100, 1) : 0); ?>%</div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Hit Rate', 'fp-performance-suite'); ?></div>
            </div>
            <div class="fp-ps-stat-box">
                <div class="fp-ps-stat-value"><?php echo esc_html($stats['cached_queries'] ?? 0); ?></div>
                <div class="fp-ps-stat-label"><?php esc_html_e('Cached Queries', 'fp-performance-suite'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Cache Settings -->
    <div class="fp-ps-card fp-ps-mb-lg">
        <h3>⚙️ <?php esc_html_e('Cache Settings', 'fp-performance-suite'); ?></h3>
        
        <form method="post" action="">
            <?php wp_nonce_field('fp_ps_query_cache_settings', 'fp_ps_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cache_enabled"><?php esc_html_e('Enable Query Cache', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label class="fp-ps-toggle">
                            <input type="checkbox" name="cache_enabled" id="cache_enabled" value="1" 
                                <?php checked($cacheSettings['enabled'] ?? false); ?>>
                            <span class="fp-ps-toggle-slider"></span>
                        </label>
                        <p class="description"><?php esc_html_e('Enable caching of frequent database queries.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cache_ttl"><?php esc_html_e('Cache TTL', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cache_ttl" id="cache_ttl" 
                            value="<?php echo esc_attr($cacheSettings['ttl'] ?? 3600); ?>" 
                            min="60" max="86400" class="small-text"> 
                        <?php esc_html_e('seconds', 'fp-performance-suite'); ?>
                        <p class="description"><?php esc_html_e('How long to keep cached queries (default: 3600 = 1 hour).', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="max_cached"><?php esc_html_e('Max Cached Queries', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="max_cached" id="max_cached" 
                            value="<?php echo esc_attr($cacheSettings['max_cached'] ?? 1000); ?>" 
                            min="100" max="10000" class="small-text">
                        <p class="description"><?php esc_html_e('Maximum number of queries to cache.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <p>
                <button type="submit" name="fp_ps_save_cache_settings" class="button button-primary">
                    💾 <?php esc_html_e('Save Settings', 'fp-performance-suite'); ?>
                </button>
                <button type="submit" name="fp_ps_clear_query_cache" class="button">
                    🗑️ <?php esc_html_e('Clear Cache', 'fp-performance-suite'); ?>
                </button>
            </p>
        </form>
    </div>
    
    <!-- Cache Info -->
    <div class="fp-ps-card">
        <h3>ℹ️ <?php esc_html_e('About Query Cache', 'fp-performance-suite'); ?></h3>
        <p class="description">
            <?php esc_html_e('Query caching stores the results of frequent database queries to reduce database load and improve page load times. This is especially useful for sites with high traffic and complex queries.', 'fp-performance-suite'); ?>
        </p>
        <ul style="margin: 16px 0; padding-left: 20px; list-style: disc;">
            <li><?php esc_html_e('Cache is automatically invalidated when relevant data changes', 'fp-performance-suite'); ?></li>
            <li><?php esc_html_e('SELECT queries are cached, INSERT/UPDATE/DELETE are not', 'fp-performance-suite'); ?></li>
            <li><?php esc_html_e('Queries with user-specific data are excluded from caching', 'fp-performance-suite'); ?></li>
        </ul>
    </div>
    
    <?php endif; ?>
    
</div>

