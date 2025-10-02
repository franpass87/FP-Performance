<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://francescopasseri.com
 * Description: Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.
 * Version: 1.0.1
 * Author: Francesco Passeri
 * Author URI: https://francescopasseri.com
 * Text Domain: fp-performance-suite
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function ($class) {
        if (strpos($class, 'FP\\PerfSuite\\') !== 0) {
            return;
        }
        $path = __DIR__ . '/src/' . str_replace('FP\\PerfSuite\\', '', $class) . '.php';
        $path = str_replace('\\', '/', $path);
        if (file_exists($path)) {
            require_once $path;
        }
    });
}

define('FP_PERF_SUITE_VERSION', '1.0.1');
define('FP_PERF_SUITE_DIR', __DIR__);

defined('FP_PERF_SUITE_FILE') || define('FP_PERF_SUITE_FILE', __FILE__);

use FP\PerfSuite\Plugin;

register_activation_hook(__FILE__, [Plugin::class, 'onActivate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'onDeactivate']);

if (function_exists('add_action')) {
    add_action('plugins_loaded', static function () {
        Plugin::init();
    });
}
