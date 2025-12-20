<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://francescopasseri.com
 * Description: Modular performance suite for shared hosting with caching, asset tuning, database cleanup, and safe debug tools.
 * Version: 2.0.0
 * Author: Francesco Passeri
 * Author URI: https://francescopasseri.com
 * Text Domain: fp-performance-suite
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/franpass87/FP-Performance
 * Primary Branch: main
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

// Constants
defined('FP_PERF_SUITE_VERSION') || define('FP_PERF_SUITE_VERSION', '2.0.0');
defined('FP_PERF_SUITE_DIR') || define('FP_PERF_SUITE_DIR', __DIR__);
defined('FP_PERF_SUITE_FILE') || define('FP_PERF_SUITE_FILE', __FILE__);

// Autoload
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    require_once $autoload;
}

// Bootstrap
FP\PerfSuite\Kernel\Bootstrapper::bootstrap(__FILE__);













