<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Score\Scorer;

/**
 * Performance Metrics Dashboard
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Performance extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-performance';
    }

    public function title(): string
    {
        return __('Performance Metrics', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [
                __('FP Performance', 'fp-performance-suite'),
                __('Metrics', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        $monitor = PerformanceMonitor::instance();
        $stats7days = $monitor->getStats(7);
        $stats30days = $monitor->getStats(30);
        $trends = $monitor->getTrends(14);

        ob_start();
        ?>
        
        <!-- Overview Cards -->
        <section class="fp-ps-grid three">
            <div class="fp-ps-card">
                <h3>⚡ <?php esc_html_e('Avg Load Time (7d)', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-score" style="font-size: 36px;">
                    <?php echo number_format($stats7days['avg_load_time'] * 1000, 0); ?><span style="font-size: 18px;">ms</span>
                </div>
                <p class="description">
                    <?php printf(
                        esc_html__('Based on %s samples', 'fp-performance-suite'),
                        number_format($stats7days['samples'])
                    ); ?>
                </p>
            </div>
            
            <div class="fp-ps-card">
                <h3>🗄️ <?php esc_html_e('Avg DB Queries (7d)', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-score" style="font-size: 36px;">
                    <?php echo number_format($stats7days['avg_queries'], 1); ?>
                </div>
                <p class="description">
                    <?php esc_html_e('Database queries per page', 'fp-performance-suite'); ?>
                </p>
            </div>
            
            <div class="fp-ps-card">
                <h3>💾 <?php esc_html_e('Avg Memory (7d)', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-score" style="font-size: 36px;">
                    <?php echo number_format($stats7days['avg_memory'], 1); ?><span style="font-size: 18px;">MB</span>
                </div>
                <p class="description">
                    <?php esc_html_e('Peak memory usage', 'fp-performance-suite'); ?>
                </p>
            </div>
        </section>

        <!-- 30-day Comparison -->
        <section class="fp-ps-card">
            <h2>📈 <?php esc_html_e('30-Day Comparison', 'fp-performance-suite'); ?></h2>
            <table class="fp-ps-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Metric', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('7 Days', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('30 Days', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Trend', 'fp-performance-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php esc_html_e('Load Time', 'fp-performance-suite'); ?></td>
                        <td><?php echo number_format($stats7days['avg_load_time'] * 1000, 0); ?> ms</td>
                        <td><?php echo number_format($stats30days['avg_load_time'] * 1000, 0); ?> ms</td>
                        <td>
                            <?php
                            $delta = (($stats7days['avg_load_time'] - $stats30days['avg_load_time']) / $stats30days['avg_load_time']) * 100;
                            if ($delta < -5) {
                                echo '<span style="color: #10b981;">↓ ' . number_format(abs($delta), 1) . '%</span>';
                            } elseif ($delta > 5) {
                                echo '<span style="color: #ef4444;">↑ ' . number_format($delta, 1) . '%</span>';
                            } else {
                                echo '<span style="color: #6b7280;">→ Stable</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('DB Queries', 'fp-performance-suite'); ?></td>
                        <td><?php echo number_format($stats7days['avg_queries'], 1); ?></td>
                        <td><?php echo number_format($stats30days['avg_queries'], 1); ?></td>
                        <td>
                            <?php
                            $delta = (($stats7days['avg_queries'] - $stats30days['avg_queries']) / max(1, $stats30days['avg_queries'])) * 100;
                            if ($delta < -5) {
                                echo '<span style="color: #10b981;">↓ ' . number_format(abs($delta), 1) . '%</span>';
                            } elseif ($delta > 5) {
                                echo '<span style="color: #ef4444;">↑ ' . number_format($delta, 1) . '%</span>';
                            } else {
                                echo '<span style="color: #6b7280;">→ Stable</span>';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Trends Chart (Simple Text Version) -->
        <?php if (!empty($trends)) : ?>
        <section class="fp-ps-card">
            <h2>📊 <?php esc_html_e('14-Day Trends', 'fp-performance-suite'); ?></h2>
            <table class="fp-ps-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Date', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Avg Load Time', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Avg Queries', 'fp-performance-suite'); ?></th>
                        <th><?php esc_html_e('Samples', 'fp-performance-suite'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice(array_reverse($trends), 0, 14) as $trend) : ?>
                    <tr>
                        <td><?php echo esc_html($trend['date']); ?></td>
                        <td><?php echo number_format($trend['avg_load_time'] * 1000, 0); ?> ms</td>
                        <td><?php echo number_format($trend['avg_queries'], 1); ?></td>
                        <td><?php echo number_format($trend['samples']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <!-- Actions -->
        <section class="fp-ps-card">
            <h2>⚙️ <?php esc_html_e('Actions', 'fp-performance-suite'); ?></h2>
            <div class="fp-ps-actions">
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=fp_ps_clear_metrics'), 'fp-ps-clear-metrics')); ?>" class="button" onclick="return confirm('<?php esc_attr_e('Clear all performance metrics?', 'fp-performance-suite'); ?>');">
                    <?php esc_html_e('Clear Metrics', 'fp-performance-suite'); ?>
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-advanced')); ?>" class="button button-primary">
                    <?php esc_html_e('Configure Monitoring', 'fp-performance-suite'); ?>
                </a>
            </div>
        </section>
        
        <?php
        return ob_get_clean();
    }
}
