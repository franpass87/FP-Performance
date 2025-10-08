<?php

namespace FP\PerfSuite\Health;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Utils\Logger;

/**
 * WordPress Site Health integration
 *
 * Adds FP Performance Suite checks to WordPress Site Health
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class HealthCheck
{
    /**
     * Register health checks
     */
    public static function register(): void
    {
        add_filter('site_status_tests', [self::class, 'addTests']);
        add_filter('debug_information', [self::class, 'addDebugInfo']);
    }

    /**
     * Add health check tests
     */
    public static function addTests(array $tests): array
    {
        $tests['direct']['fp_performance_cache'] = [
            'label' => __('FP Performance - Page Cache', 'fp-performance-suite'),
            'test' => [self::class, 'testPageCache'],
        ];

        $tests['direct']['fp_performance_webp'] = [
            'label' => __('FP Performance - WebP Coverage', 'fp-performance-suite'),
            'test' => [self::class, 'testWebPCoverage'],
        ];

        $tests['direct']['fp_performance_database'] = [
            'label' => __('FP Performance - Database Health', 'fp-performance-suite'),
            'test' => [self::class, 'testDatabaseHealth'],
        ];

        $tests['direct']['fp_performance_assets'] = [
            'label' => __('FP Performance - Asset Optimization', 'fp-performance-suite'),
            'test' => [self::class, 'testAssetOptimization'],
        ];

        return $tests;
    }

    /**
     * Test: Page Cache Status
     */
    public static function testPageCache(): array
    {
        try {
            $container = Plugin::container();
            $pageCache = $container->get(PageCache::class);
            $status = $pageCache->status();

            if ($status['enabled']) {
                return [
                    'label' => __('Page cache is active', 'fp-performance-suite'),
                    'status' => 'good',
                    'badge' => [
                        'label' => __('Performance', 'fp-performance-suite'),
                        'color' => 'blue',
                    ],
                    'description' => sprintf(
                        '<p>%s</p>',
                        sprintf(
                            __('Page cache is enabled with %d cached pages. This improves site performance significantly.', 'fp-performance-suite'),
                            $status['files']
                        )
                    ),
                    'actions' => sprintf(
                        '<p><a href="%s">%s</a></p>',
                        admin_url('admin.php?page=fp-performance-suite-cache'),
                        __('Manage cache settings', 'fp-performance-suite')
                    ),
                    'test' => 'fp_performance_cache',
                ];
            }

            return [
                'label' => __('Page cache is not enabled', 'fp-performance-suite'),
                'status' => 'recommended',
                'badge' => [
                    'label' => __('Performance', 'fp-performance-suite'),
                    'color' => 'orange',
                ],
                'description' => sprintf(
                    '<p>%s</p>',
                    __('Enabling page cache can significantly improve your site performance by storing HTML output on disk.', 'fp-performance-suite')
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('admin.php?page=fp-performance-suite-cache'),
                    __('Enable page cache', 'fp-performance-suite')
                ),
                'test' => 'fp_performance_cache',
            ];
        } catch (\Throwable $e) {
            Logger::error('Health check failed for page cache', $e);
            return self::errorResult('Page Cache', $e);
        }
    }

    /**
     * Test: WebP Coverage
     */
    public static function testWebPCoverage(): array
    {
        try {
            $container = Plugin::container();
            $webp = $container->get(WebPConverter::class);
            $status = $webp->status();

            if (!$status['enabled']) {
                return [
                    'label' => __('WebP conversion is not enabled', 'fp-performance-suite'),
                    'status' => 'recommended',
                    'badge' => [
                        'label' => __('Performance', 'fp-performance-suite'),
                        'color' => 'orange',
                    ],
                    'description' => sprintf(
                        '<p>%s</p>',
                        __('WebP images are typically 25-35% smaller than JPEG/PNG while maintaining quality. Enable WebP conversion to improve page load times.', 'fp-performance-suite')
                    ),
                    'actions' => sprintf(
                        '<p><a href="%s">%s</a></p>',
                        admin_url('admin.php?page=fp-performance-suite-media'),
                        __('Enable WebP conversion', 'fp-performance-suite')
                    ),
                    'test' => 'fp_performance_webp',
                ];
            }

            $coverage = $status['coverage'];

            if ($coverage >= 80) {
                return [
                    'label' => __('WebP coverage is excellent', 'fp-performance-suite'),
                    'status' => 'good',
                    'badge' => [
                        'label' => __('Performance', 'fp-performance-suite'),
                        'color' => 'green',
                    ],
                    'description' => sprintf(
                        '<p>%s</p>',
                        sprintf(
                            __('WebP conversion is enabled and %.1f%% of your images are converted. This helps reduce page size and improve load times.', 'fp-performance-suite'),
                            $coverage
                        )
                    ),
                    'actions' => sprintf(
                        '<p><a href="%s">%s</a></p>',
                        admin_url('admin.php?page=fp-performance-suite-media'),
                        __('View WebP settings', 'fp-performance-suite')
                    ),
                    'test' => 'fp_performance_webp',
                ];
            }

            if ($coverage >= 40) {
                $status_level = 'recommended';
                $message = __('Consider running bulk WebP conversion to improve coverage.', 'fp-performance-suite');
            } else {
                $status_level = 'recommended';
                $message = __('Low WebP coverage detected. Run bulk conversion to optimize images.', 'fp-performance-suite');
            }

            return [
                'label' => sprintf(__('WebP coverage: %.1f%%', 'fp-performance-suite'), $coverage),
                'status' => $status_level,
                'badge' => [
                    'label' => __('Performance', 'fp-performance-suite'),
                    'color' => 'orange',
                ],
                'description' => sprintf('<p>%s</p>', $message),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('admin.php?page=fp-performance-suite-media'),
                    __('Run bulk WebP conversion', 'fp-performance-suite')
                ),
                'test' => 'fp_performance_webp',
            ];
        } catch (\Throwable $e) {
            Logger::error('Health check failed for WebP', $e);
            return self::errorResult('WebP Coverage', $e);
        }
    }

    /**
     * Test: Database Health
     */
    public static function testDatabaseHealth(): array
    {
        try {
            $container = Plugin::container();
            $cleaner = $container->get(Cleaner::class);
            $status = $cleaner->status();

            $overhead = $status['overhead_mb'];

            if ($overhead < 5) {
                return [
                    'label' => __('Database is healthy', 'fp-performance-suite'),
                    'status' => 'good',
                    'badge' => [
                        'label' => __('Performance', 'fp-performance-suite'),
                        'color' => 'green',
                    ],
                    'description' => sprintf(
                        '<p>%s</p>',
                        sprintf(
                            __('Database overhead is low (%.2f MB). Your database is well optimized.', 'fp-performance-suite'),
                            $overhead
                        )
                    ),
                    'actions' => sprintf(
                        '<p><a href="%s">%s</a></p>',
                        admin_url('admin.php?page=fp-performance-suite-database'),
                        __('View database settings', 'fp-performance-suite')
                    ),
                    'test' => 'fp_performance_database',
                ];
            }

            if ($overhead < 20) {
                $status_level = 'recommended';
                $color = 'orange';
                $message = __('Moderate database overhead detected. Consider running cleanup to optimize performance.', 'fp-performance-suite');
            } else {
                $status_level = 'recommended';
                $color = 'red';
                $message = __('High database overhead detected. Running cleanup is strongly recommended.', 'fp-performance-suite');
            }

            return [
                'label' => sprintf(__('Database overhead: %.2f MB', 'fp-performance-suite'), $overhead),
                'status' => $status_level,
                'badge' => [
                    'label' => __('Performance', 'fp-performance-suite'),
                    'color' => $color,
                ],
                'description' => sprintf('<p>%s</p>', $message),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('admin.php?page=fp-performance-suite-database'),
                    __('Run database cleanup', 'fp-performance-suite')
                ),
                'test' => 'fp_performance_database',
            ];
        } catch (\Throwable $e) {
            Logger::error('Health check failed for database', $e);
            return self::errorResult('Database Health', $e);
        }
    }

    /**
     * Test: Asset Optimization
     */
    public static function testAssetOptimization(): array
    {
        try {
            $container = Plugin::container();
            $optimizer = $container->get(\FP\PerfSuite\Services\Assets\Optimizer::class);
            $status = $optimizer->status();

            $activeOptimizations = 0;
            $recommendations = [];

            if ($status['defer_js']) {
                $activeOptimizations++;
            } else {
                $recommendations[] = __('Enable JavaScript deferral', 'fp-performance-suite');
            }

            if ($status['minify_html']) {
                $activeOptimizations++;
            } else {
                $recommendations[] = __('Enable HTML minification', 'fp-performance-suite');
            }

            if ($status['remove_emojis']) {
                $activeOptimizations++;
            } else {
                $recommendations[] = __('Remove emoji scripts', 'fp-performance-suite');
            }

            if ($activeOptimizations >= 2) {
                return [
                    'label' => __('Asset optimization is active', 'fp-performance-suite'),
                    'status' => 'good',
                    'badge' => [
                        'label' => __('Performance', 'fp-performance-suite'),
                        'color' => 'green',
                    ],
                    'description' => sprintf(
                        '<p>%s</p>',
                        sprintf(
                            __('%d asset optimizations are enabled, helping reduce page load time.', 'fp-performance-suite'),
                            $activeOptimizations
                        )
                    ),
                    'actions' => sprintf(
                        '<p><a href="%s">%s</a></p>',
                        admin_url('admin.php?page=fp-performance-suite-assets'),
                        __('View asset settings', 'fp-performance-suite')
                    ),
                    'test' => 'fp_performance_assets',
                ];
            }

            return [
                'label' => __('Asset optimization could be improved', 'fp-performance-suite'),
                'status' => 'recommended',
                'badge' => [
                    'label' => __('Performance', 'fp-performance-suite'),
                    'color' => 'orange',
                ],
                'description' => sprintf(
                    '<p>%s<br>%s</p>',
                    __('Additional asset optimizations are available:', 'fp-performance-suite'),
                    '• ' . implode('<br>• ', $recommendations)
                ),
                'actions' => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    admin_url('admin.php?page=fp-performance-suite-assets'),
                    __('Configure asset optimization', 'fp-performance-suite')
                ),
                'test' => 'fp_performance_assets',
            ];
        } catch (\Throwable $e) {
            Logger::error('Health check failed for assets', $e);
            return self::errorResult('Asset Optimization', $e);
        }
    }

    /**
     * Add debug information to Site Health Info tab
     */
    public static function addDebugInfo(array $info): array
    {
        try {
            $container = Plugin::container();

            $pageCache = $container->get(PageCache::class);
            $webp = $container->get(WebPConverter::class);
            $cleaner = $container->get(Cleaner::class);
            $optimizer = $container->get(\FP\PerfSuite\Services\Assets\Optimizer::class);

            $info['fp-performance-suite'] = [
                'label' => __('FP Performance Suite', 'fp-performance-suite'),
                'fields' => [
                    'version' => [
                        'label' => __('Version', 'fp-performance-suite'),
                        'value' => defined('FP_PERF_SUITE_VERSION') ? FP_PERF_SUITE_VERSION : 'Unknown',
                    ],
                    'page_cache_enabled' => [
                        'label' => __('Page Cache', 'fp-performance-suite'),
                        'value' => $pageCache->isEnabled() ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'),
                    ],
                    'page_cache_files' => [
                        'label' => __('Cached Pages', 'fp-performance-suite'),
                        'value' => $pageCache->status()['files'],
                    ],
                    'webp_enabled' => [
                        'label' => __('WebP Conversion', 'fp-performance-suite'),
                        'value' => $webp->status()['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'),
                    ],
                    'webp_coverage' => [
                        'label' => __('WebP Coverage', 'fp-performance-suite'),
                        'value' => round($webp->status()['coverage'], 1) . '%',
                    ],
                    'db_overhead' => [
                        'label' => __('Database Overhead', 'fp-performance-suite'),
                        'value' => round($cleaner->status()['overhead_mb'], 2) . ' MB',
                    ],
                    'defer_js' => [
                        'label' => __('Defer JavaScript', 'fp-performance-suite'),
                        'value' => $optimizer->status()['defer_js'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'),
                    ],
                    'minify_html' => [
                        'label' => __('HTML Minification', 'fp-performance-suite'),
                        'value' => $optimizer->status()['minify_html'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'),
                    ],
                ],
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to add debug info to Site Health', $e);
        }

        return $info;
    }

    /**
     * Create error result
     */
    private static function errorResult(string $testName, \Throwable $e): array
    {
        return [
            'label' => sprintf(__('%s check failed', 'fp-performance-suite'), $testName),
            'status' => 'recommended',
            'badge' => [
                'label' => __('Performance', 'fp-performance-suite'),
                'color' => 'gray',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('Unable to run this health check. Please check the error logs.', 'fp-performance-suite')
            ),
            'test' => 'fp_performance_error',
        ];
    }
}
