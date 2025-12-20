<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://francescopasseri.com
 * Description: Modular performance suite for shared hosting with caching, asset tuning, database cleanup, and safe debug tools.
 * Version: 1.8.0
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
defined('FP_PERF_SUITE_VERSION') || define('FP_PERF_SUITE_VERSION', '1.8.0');
defined('FP_PERF_SUITE_DIR') || define('FP_PERF_SUITE_DIR', __DIR__);
defined('FP_PERF_SUITE_FILE') || define('FP_PERF_SUITE_FILE', __FILE__);

// Autoloaders
$autoload = __DIR__ . '/vendor/autoload.php';
if (is_readable($autoload)) {
    try {
        require_once $autoload;
    } catch (\Throwable $e) {
        if (function_exists('error_log')) {
            error_log('[FP-PerfSuite] Composer autoload failed: ' . $e->getMessage());
        }
    }
}

// PSR-4 autoloader
spl_autoload_register(static function ($class) {
    if (strpos($class, 'FP\\PerfSuite\\') !== 0) {
        return;
    }
    
    try {
        $relativePath = substr($class, strlen('FP\\PerfSuite\\'));
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativePath);
        $path = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relativePath . '.php';
        
        if (is_readable($path)) {
            require_once $path;
        }
    } catch (\Throwable $e) {
        // Silent fail - will be handled by bootstrap
    }
});

// Bootstrap (includes environment guards and global function wrappers)
require_once __DIR__ . '/src/Kernel/Bootstrapper.php';

\FP\PerfSuite\Kernel\Bootstrapper::bootstrap();
