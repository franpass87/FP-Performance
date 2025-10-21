<?php
/**
 * Example: Custom Logging Integration
 * 
 * Send FP Performance Suite logs to external monitoring services
 * Add this to your theme's functions.php or a custom plugin
 */

// Example 1: Sentry Integration
add_action('fp_ps_log_error', function($message, $exception) {
    if (!$exception || !function_exists('sentry_capture_exception')) {
        return;
    }
    
    sentry_capture_exception($exception, [
        'tags' => [
            'plugin' => 'fp-performance-suite',
            'version' => FP_PERF_SUITE_VERSION,
        ],
        'extra' => [
            'message' => $message,
        ],
    ]);
}, 10, 2);

// Example 2: Slack Notifications
add_action('fp_ps_log_error', function($message, $exception) {
    $webhookUrl = 'https://hooks.slack.com/services/YOUR/WEBHOOK/URL';
    
    wp_remote_post($webhookUrl, [
        'body' => json_encode([
            'text' => "🚨 *FP Performance Error*\n" . $message,
            'channel' => '#performance-alerts',
            'username' => get_bloginfo('name'),
            'icon_emoji' => ':warning:',
        ]),
        'headers' => ['Content-Type' => 'application/json'],
    ]);
}, 10, 2);

// Example 3: Custom Log File
add_action('fp_ps_log_error', function($message, $exception) {
    $logFile = WP_CONTENT_DIR . '/fp-performance-errors.log';
    $timestamp = current_time('Y-m-d H:i:s');
    $logEntry = sprintf(
        "[%s] %s - %s\n",
        $timestamp,
        $message,
        $exception ? $exception->getMessage() : 'No exception'
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}, 10, 2);

// Example 4: Email Critical Errors Only
add_action('fp_ps_log_error', function($message, $exception) {
    // Only email truly critical errors
    $criticalKeywords = ['fatal', 'critical', 'security', 'corruption'];
    $messageLower = strtolower($message);
    
    foreach ($criticalKeywords as $keyword) {
        if (strpos($messageLower, $keyword) !== false) {
            wp_mail(
                get_option('admin_email'),
                '[CRITICAL] FP Performance Alert',
                "Critical Error Detected:\n\n{$message}\n\n" .
                ($exception ? "Exception: " . $exception->getMessage() : '')
            );
            break;
        }
    }
}, 10, 2);
