<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://francescopasseri.com
 * Description: Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.
 * Version: 1.5.0
 * Author: Francesco Passeri
 * Author URI: https://francescopasseri.com
 * Text Domain: fp-performance-suite
 * Domain Path: /fp-performance-suite/languages
 * GitHub Plugin URI: https://github.com/franpass87/FP-Performance
 * Primary Branch: main
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

// IMPORTANTE: Questo file è un wrapper per Git Updater
// Il codice reale è in /fp-performance-suite/

// Autoload con percorso corretto
$autoload = __DIR__ . '/fp-performance-suite/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function ($class) {
        if (strpos($class, 'FP\\PerfSuite\\') !== 0) {
            return;
        }
        // CORRETTO: Punta a /fp-performance-suite/src/
        $path = __DIR__ . '/fp-performance-suite/src/' . str_replace(['FP\\PerfSuite\\', '\\'], ['', '/'], $class) . '.php';
        if (file_exists($path)) {
            require_once $path;
        }
    });
}

defined('FP_PERF_SUITE_VERSION') || define('FP_PERF_SUITE_VERSION', '1.5.0');
defined('FP_PERF_SUITE_DIR') || define('FP_PERF_SUITE_DIR', __DIR__ . '/fp-performance-suite');
defined('FP_PERF_SUITE_FILE') || define('FP_PERF_SUITE_FILE', __FILE__);

use FP\PerfSuite\Plugin;

register_activation_hook(__FILE__, [Plugin::class, 'onActivate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'onDeactivate']);

if (function_exists('add_action')) {
    add_action('plugins_loaded', static function () {
        Plugin::init();
    });
}

