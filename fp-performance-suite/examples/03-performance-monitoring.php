<?php
/**
 * Example: Performance Monitoring & Tracking
 * 
 * Advanced performance monitoring examples
 */

// Example 1: Track Custom Operations
add_action('init', function() {
    if (!class_exists('\FP\PerfSuite\Services\Monitoring\PerformanceMonitor')) {
        return;
    }
    
    $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
    
    // Track custom metric
    $monitor->track('page_type', is_front_page() ? 'home' : 'other');
    
    // Time an operation
    $monitor->startTimer('my_custom_query');
    // ... expensive operation ...
    $duration = $monitor->stopTimer('my_custom_query');
    
    if ($duration > 1.0) {
        \FP\PerfSuite\Utils\Logger::warning("Slow operation detected: {$duration}s");
    }
});

// Example 2: WooCommerce Integration
add_action('woocommerce_init', function() {
    $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
    
    // Track checkout performance
    if (is_checkout()) {
        $monitor->startTimer('checkout_render');
    }
});

add_action('wp_footer', function() {
    if (is_checkout()) {
        $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
        $duration = $monitor->stopTimer('checkout_render');
        
        $monitor->track('checkout_render_time', $duration);
    }
});

// Example 3: Database Query Monitoring
add_action('shutdown', function() {
    global $wpdb;
    
    $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
    
    // Track slow queries
    if ($wpdb->num_queries > 50) {
        $monitor->track('slow_page_queries', $wpdb->num_queries);
        \FP\PerfSuite\Utils\Logger::warning("High query count: {$wpdb->num_queries}");
    }
});

// Example 4: Send Metrics to Google Analytics
add_action('wp_footer', function() {
    if (!is_user_logged_in()) {
        ?>
        <script>
        if (typeof gtag !== 'undefined' && window.performance) {
            window.addEventListener('load', function() {
                setTimeout(function() {
                    var loadTime = window.performance.timing.loadEventEnd - 
                                   window.performance.timing.navigationStart;
                    
                    gtag('event', 'timing_complete', {
                        'name': 'page_load',
                        'value': loadTime,
                        'event_category': 'Performance'
                    });
                }, 0);
            });
        }
        </script>
        <?php
    }
});

// Example 5: Alert on Performance Degradation
add_action('fp_ps_db_cleanup_complete', function($results, $dryRun) {
    if ($dryRun) return;
    
    $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
    $stats = $monitor->getStats(7);
    
    // Alert if avg load time > 2 seconds
    if ($stats['avg_load_time'] > 2.0) {
        wp_mail(
            get_option('admin_email'),
            'Performance Alert: Slow Load Times',
            "Average load time is {$stats['avg_load_time']}s (target: < 2s)\n\n" .
            "Samples: {$stats['samples']}\n" .
            "Avg Queries: {$stats['avg_queries']}\n" .
            "Avg Memory: {$stats['avg_memory']} MB"
        );
    }
}, 10, 2);

// Example 6: Custom Dashboard Widget
add_action('wp_dashboard_setup', function() {
    wp_add_dashboard_widget(
        'fp_performance_widget',
        'FP Performance Metrics',
        function() {
            $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
            $stats = $monitor->getStats(7);
            
            echo '<div class="activity-block">';
            echo '<h3>Last 7 Days</h3>';
            echo '<p><strong>Avg Load Time:</strong> ' . number_format($stats['avg_load_time'] * 1000, 0) . ' ms</p>';
            echo '<p><strong>Avg Queries:</strong> ' . number_format($stats['avg_queries'], 1) . '</p>';
            echo '<p><strong>Samples:</strong> ' . number_format($stats['samples']) . '</p>';
            echo '<a href="' . admin_url('admin.php?page=fp-performance-suite-performance') . '" class="button">View Details</a>';
            echo '</div>';
        }
    );
});
