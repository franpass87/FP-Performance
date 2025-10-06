<?php
/**
 * Example: Event System Usage
 * 
 * Using the new Event Dispatcher system
 */

// Example 1: Listen to Events
add_action('plugins_loaded', function() {
    $dispatcher = \FP\PerfSuite\Events\EventDispatcher::instance();
    
    // Listen to cache cleared event
    $dispatcher->listen('cache_cleared', function($event) {
        $filesDeleted = $event->get('files_deleted', 0);
        error_log("Cache cleared: {$filesDeleted} files deleted");
        
        // Warm up important pages
        $urls = [home_url('/'), home_url('/shop/')];
        foreach ($urls as $url) {
            wp_remote_get($url);
        }
    });
    
    // Listen to WebP conversion
    $dispatcher->listen('webp_converted', function($event) {
        $file = $event->get('original_file');
        $reduction = $event->getSizeReduction();
        
        error_log("WebP conversion saved {$reduction}% on {$file}");
    });
});

// Example 2: Dispatch Custom Events
add_action('init', function() {
    $dispatcher = \FP\PerfSuite\Events\EventDispatcher::instance();
    
    // Custom event class
    class MyCustomEvent extends \FP\PerfSuite\Events\Event {
        public function name(): string {
            return 'my_custom_event';
        }
    }
    
    // Dispatch it
    $event = new MyCustomEvent(['data' => 'value']);
    $dispatcher->dispatch($event);
});

// Example 3: Event History Tracking
add_action('shutdown', function() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $dispatcher = \FP\PerfSuite\Events\EventDispatcher::instance();
    $events = $dispatcher->getDispatched();
    
    if (!empty($events)) {
        error_log('Events dispatched this request: ' . count($events));
        foreach ($events as $event) {
            error_log("  - {$event['name']} at {$event['timestamp']}");
        }
    }
});

// Example 4: Conditional Event Listeners
add_action('plugins_loaded', function() {
    $dispatcher = \FP\PerfSuite\Events\EventDispatcher::instance();
    
    // Only on production
    if (wp_get_environment_type() === 'production') {
        $dispatcher->listen('database_cleaned', function($event) {
            if (!$event->isDryRun()) {
                $total = $event->getTotalDeleted();
                // Send to analytics
                if (function_exists('gtag')) {
                    gtag('event', 'database_cleanup', [
                        'event_category' => 'maintenance',
                        'value' => $total,
                    ]);
                }
            }
        });
    }
});

// Example 5: Priority-based Listeners
add_action('plugins_loaded', function() {
    $dispatcher = \FP\PerfSuite\Events\EventDispatcher::instance();
    
    // High priority (runs first)
    $dispatcher->listen('cache_cleared', function($event) {
        error_log('High priority listener: clearing object cache');
        wp_cache_flush();
    }, 5);
    
    // Normal priority
    $dispatcher->listen('cache_cleared', function($event) {
        error_log('Normal priority listener: warming cache');
    }, 10);
    
    // Low priority (runs last)
    $dispatcher->listen('cache_cleared', function($event) {
        error_log('Low priority listener: sending notification');
    }, 20);
});
