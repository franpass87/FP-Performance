<?php
/**
 * PHPUnit Bootstrap for FP Performance Suite
 *
 * @package FP\PerfSuite\Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../../');
}

// Define test constants
if (!defined('FP_PERF_SUITE_VERSION')) {
    define('FP_PERF_SUITE_VERSION', '1.8.0');
}

if (!defined('FP_PERF_SUITE_DIR')) {
    define('FP_PERF_SUITE_DIR', dirname(__DIR__));
}

if (!defined('FP_PERF_SUITE_FILE')) {
    define('FP_PERF_SUITE_FILE', FP_PERF_SUITE_DIR . '/fp-performance-suite.php');
}

// Load Composer autoloader
$autoload = FP_PERF_SUITE_DIR . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

// Load Brain Monkey
if (class_exists('Brain\Monkey\setUp')) {
    Brain\Monkey\setUp();
}

// Mock WordPress constants
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', '/tmp/wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Clean up after tests
register_shutdown_function(function() {
    if (class_exists('Brain\Monkey\tearDown')) {
        Brain\Monkey\tearDown();
    }
});










