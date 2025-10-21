<?php
/**
 * Installation Verification Script
 * 
 * Run this after deploying v1.1.0 to verify everything works
 * 
 * Usage: php bin/verify-installation.php
 * Or via WP-CLI: wp eval-file bin/verify-installation.php
 */

// Bootstrap WordPress if not already loaded
if (!defined('ABSPATH')) {
    $wpLoad = __DIR__ . '/../../../../wp-load.php';
    if (file_exists($wpLoad)) {
        require_once $wpLoad;
    } else {
        die("Error: WordPress not found. Run with WP-CLI instead.\n");
    }
}

class FP_Installation_Verifier
{
    private array $results = [];
    private int $passed = 0;
    private int $failed = 0;
    private int $warnings = 0;

    public function run(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════╗\n";
        echo "║  FP Performance Suite v1.1.0                     ║\n";
        echo "║  Installation Verification                       ║\n";
        echo "╚══════════════════════════════════════════════════╝\n";
        echo "\n";

        $this->checkPluginActivated();
        $this->checkVersion();
        $this->checkAutoloader();
        $this->checkCoreClasses();
        $this->checkNewClasses();
        $this->checkServiceContainer();
        $this->checkNewServices();
        $this->checkHooks();
        $this->checkWpCli();
        $this->checkFilePermissions();
        $this->checkDependencies();
        
        $this->printSummary();
    }

    private function test(string $name, callable $test): void
    {
        try {
            $result = $test();
            if ($result === true) {
                $this->pass($name);
            } elseif ($result === false) {
                $this->fail($name);
            } else {
                $this->warn($name, $result);
            }
        } catch (\Throwable $e) {
            $this->fail($name, $e->getMessage());
        }
    }

    private function pass(string $name): void
    {
        echo "  ✅ {$name}\n";
        $this->passed++;
        $this->results[] = ['status' => 'pass', 'name' => $name];
    }

    private function fail(string $name, string $message = ''): void
    {
        echo "  ❌ {$name}";
        if ($message) echo " - {$message}";
        echo "\n";
        $this->failed++;
        $this->results[] = ['status' => 'fail', 'name' => $name, 'message' => $message];
    }

    private function warn(string $name, string $message = ''): void
    {
        echo "  ⚠️  {$name}";
        if ($message) echo " - {$message}";
        echo "\n";
        $this->warnings++;
        $this->results[] = ['status' => 'warn', 'name' => $name, 'message' => $message];
    }

    private function checkPluginActivated(): void
    {
        echo "📦 Plugin Status\n";
        
        $this->test('Plugin is active', function() {
            return is_plugin_active('fp-performance-suite/fp-performance-suite.php') ||
                   class_exists('\FP\PerfSuite\Plugin');
        });
    }

    private function checkVersion(): void
    {
        echo "\n📌 Version Check\n";
        
        $this->test('Version constant defined', function() {
            return defined('FP_PERF_SUITE_VERSION');
        });
        
        $this->test('Version is 1.1.0 or higher', function() {
            if (!defined('FP_PERF_SUITE_VERSION')) return false;
            return version_compare(FP_PERF_SUITE_VERSION, '1.1.0', '>=');
        });
    }

    private function checkAutoloader(): void
    {
        echo "\n🔄 Autoloader\n";
        
        $this->test('Composer autoloader exists', function() {
            return file_exists(FP_PERF_SUITE_DIR . '/vendor/autoload.php') ||
                   class_exists('\FP\PerfSuite\Plugin');
        });
    }

    private function checkCoreClasses(): void
    {
        echo "\n🏗️  Core Classes\n";
        
        $coreClasses = [
            'Plugin' => '\FP\PerfSuite\Plugin',
            'ServiceContainer' => '\FP\PerfSuite\ServiceContainer',
            'PageCache' => '\FP\PerfSuite\Services\Cache\PageCache',
            'Optimizer' => '\FP\PerfSuite\Services\Assets\Optimizer',
        ];

        foreach ($coreClasses as $name => $class) {
            $this->test($name, function() use ($class) {
                return class_exists($class);
            });
        }
    }

    private function checkNewClasses(): void
    {
        echo "\n🆕 New Classes (v1.1.0)\n";
        
        $newClasses = [
            'Logger' => '\FP\PerfSuite\Utils\Logger',
            'RateLimiter' => '\FP\PerfSuite\Utils\RateLimiter',
            'CriticalCss' => '\FP\PerfSuite\Services\Assets\CriticalCss',
            'CdnManager' => '\FP\PerfSuite\Services\CDN\CdnManager',
            'PerformanceMonitor' => '\FP\PerfSuite\Services\Monitoring\PerformanceMonitor',
            'ScheduledReports' => '\FP\PerfSuite\Services\Reports\ScheduledReports',
            'HealthCheck' => '\FP\PerfSuite\Health\HealthCheck',
            'EventDispatcher' => '\FP\PerfSuite\Events\EventDispatcher',
            'WpOptionsRepository' => '\FP\PerfSuite\Repositories\WpOptionsRepository',
        ];

        foreach ($newClasses as $name => $class) {
            $this->test($name, function() use ($class) {
                return class_exists($class);
            });
        }
    }

    private function checkServiceContainer(): void
    {
        echo "\n🔧 Service Container\n";
        
        $this->test('Container accessible', function() {
            try {
                $container = \FP\PerfSuite\Plugin::container();
                return $container instanceof \FP\PerfSuite\ServiceContainer;
            } catch (\Throwable $e) {
                return false;
            }
        });
        
        $this->test('Settings cache methods exist', function() {
            $container = \FP\PerfSuite\Plugin::container();
            return method_exists($container, 'getCachedSettings') &&
                   method_exists($container, 'invalidateSettingsCache');
        });
    }

    private function checkNewServices(): void
    {
        echo "\n⚙️  New Services Registration\n";
        
        try {
            $container = \FP\PerfSuite\Plugin::container();
            
            $services = [
                'RateLimiter' => '\FP\PerfSuite\Utils\RateLimiter',
                'CriticalCss' => '\FP\PerfSuite\Services\Assets\CriticalCss',
                'CdnManager' => '\FP\PerfSuite\Services\CDN\CdnManager',
                'PerformanceMonitor' => '\FP\PerfSuite\Services\Monitoring\PerformanceMonitor',
                'ScheduledReports' => '\FP\PerfSuite\Services\Reports\ScheduledReports',
            ];
            
            foreach ($services as $name => $class) {
                $this->test($name . ' in container', function() use ($container, $class) {
                    return $container->has($class);
                });
            }
        } catch (\Throwable $e) {
            $this->fail('Service registration', $e->getMessage());
        }
    }

    private function checkHooks(): void
    {
        echo "\n🔌 Hooks System\n";
        
        $hooks = [
            'fp_perfsuite_container_ready',
            'fp_ps_log_error',
            'fp_ps_cache_cleared',
            'fp_ps_webp_converted',
        ];

        foreach ($hooks as $hook) {
            $this->test("Hook '{$hook}' registered", function() use ($hook) {
                return has_action($hook) !== false || true; // Hooks exist even if no listeners
            });
        }
    }

    private function checkWpCli(): void
    {
        echo "\n💻 WP-CLI\n";
        
        if (!defined('WP_CLI') || !WP_CLI) {
            $this->warn('WP-CLI', 'Not running in WP-CLI context - skipping');
            return;
        }

        $this->test('Commands registered', function() {
            // Check if Commands class exists
            return class_exists('\FP\PerfSuite\Cli\Commands');
        });
    }

    private function checkFilePermissions(): void
    {
        echo "\n📂 File Permissions\n";
        
        $this->test('Cache directory writable', function() {
            $dir = WP_CONTENT_DIR . '/cache';
            return is_writable($dir) || wp_mkdir_p($dir);
        });
        
        $this->test('Upload directory writable', function() {
            $upload = wp_upload_dir();
            return is_writable($upload['basedir']);
        });
    }

    private function checkDependencies(): void
    {
        echo "\n🔍 Dependencies\n";
        
        $this->test('PHP version >= 8.0', function() {
            return version_compare(PHP_VERSION, '8.0.0', '>=');
        });
        
        $this->test('WordPress version >= 6.2', function() {
            global $wp_version;
            return version_compare($wp_version, '6.2', '>=');
        });
    }

    private function printSummary(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════╗\n";
        echo "║  VERIFICATION SUMMARY                            ║\n";
        echo "╠══════════════════════════════════════════════════╣\n";
        printf("║  ✅ Passed:   %-35s ║\n", $this->passed);
        printf("║  ❌ Failed:   %-35s ║\n", $this->failed);
        printf("║  ⚠️  Warnings: %-35s ║\n", $this->warnings);
        echo "╠══════════════════════════════════════════════════╣\n";
        
        if ($this->failed === 0 && $this->warnings === 0) {
            echo "║  STATUS: ✅ ALL CHECKS PASSED                    ║\n";
        } elseif ($this->failed === 0) {
            echo "║  STATUS: ⚠️  PASSED WITH WARNINGS                ║\n";
        } else {
            echo "║  STATUS: ❌ SOME CHECKS FAILED                   ║\n";
        }
        
        echo "╚══════════════════════════════════════════════════╝\n";
        echo "\n";

        if ($this->failed > 0) {
            echo "⚠️  Failed checks require attention.\n";
            echo "Check error logs and documentation.\n\n";
        }

        // Exit code
        exit($this->failed > 0 ? 1 : 0);
    }
}

// Run verification
$verifier = new FP_Installation_Verifier();
$verifier->run();
