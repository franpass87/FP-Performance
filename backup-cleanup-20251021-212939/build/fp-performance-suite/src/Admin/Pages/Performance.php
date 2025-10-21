<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer;
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
        
        // Get performance analysis
        $analyzer = $this->container->get(PerformanceAnalyzer::class);
        $analysis = $analyzer->analyze();

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
                            if ($stats30days['avg_load_time'] > 0) {
                                $delta = (($stats7days['avg_load_time'] - $stats30days['avg_load_time']) / $stats30days['avg_load_time']) * 100;
                                if ($delta < -5) {
                                    echo '<span style="color: #10b981;">↓ ' . number_format(abs($delta), 1) . '%</span>';
                                } elseif ($delta > 5) {
                                    echo '<span style="color: #ef4444;">↑ ' . number_format($delta, 1) . '%</span>';
                                } else {
                                    echo '<span style="color: #6b7280;">→ Stable</span>';
                                }
                            } else {
                                echo '<span style="color: #6b7280;">—</span>';
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

        <!-- Performance Analysis Section -->
        <section class="fp-ps-card">
            <h2>🔍 <?php esc_html_e('Analisi Problemi di Performance', 'fp-performance-suite'); ?></h2>
            
            <!-- Health Score -->
            <div style="margin-bottom: 20px; padding: 15px; background: <?php echo $analysis['score'] >= 70 ? '#d1fae5' : ($analysis['score'] >= 50 ? '#fef3c7' : '#fee2e2'); ?>; border-radius: 6px;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div style="font-size: 48px; font-weight: bold; color: <?php echo $analysis['score'] >= 70 ? '#059669' : ($analysis['score'] >= 50 ? '#d97706' : '#dc2626'); ?>;">
                        <?php echo esc_html($analysis['score']); ?><span style="font-size: 24px;">/100</span>
                    </div>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 8px 0; color: #1f2937;">
                            <?php 
                            if ($analysis['score'] >= 90) {
                                esc_html_e('Salute Eccellente', 'fp-performance-suite');
                            } elseif ($analysis['score'] >= 70) {
                                esc_html_e('Buona Salute', 'fp-performance-suite');
                            } elseif ($analysis['score'] >= 50) {
                                esc_html_e('Necessita Attenzione', 'fp-performance-suite');
                            } else {
                                esc_html_e('Problemi Critici', 'fp-performance-suite');
                            }
                            ?>
                        </h3>
                        <p style="margin: 0; color: #4b5563;"><?php echo esc_html($analysis['summary']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Critical Issues -->
            <?php if (!empty($analysis['critical'])) : ?>
            <div style="margin-bottom: 25px;">
                <h3 style="color: #dc2626; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                    <span style="font-size: 24px;">🚨</span>
                    <?php printf(esc_html__('Problemi Critici (%d)', 'fp-performance-suite'), count($analysis['critical'])); ?>
                </h3>
                <?php 
                // Sort by priority
                usort($analysis['critical'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach ($analysis['critical'] as $issue) : 
                ?>
                <div style="background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin-bottom: 12px; border-radius: 4px;">
                    <h4 style="margin: 0 0 8px 0; color: #991b1b; font-size: 15px;">
                        <?php echo esc_html($issue['issue']); ?>
                    </h4>
                    <p style="margin: 0 0 10px 0; color: #7f1d1d; font-size: 14px;">
                        <strong><?php esc_html_e('Impatto:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['impact']); ?>
                    </p>
                    <p style="margin: 0; padding: 10px; background: white; border-radius: 4px; color: #374151; font-size: 14px;">
                        <strong style="color: #059669;">💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Warnings -->
            <?php if (!empty($analysis['warnings'])) : ?>
            <div style="margin-bottom: 25px;">
                <h3 style="color: #d97706; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                    <span style="font-size: 24px;">⚠️</span>
                    <?php printf(esc_html__('Avvisi (%d)', 'fp-performance-suite'), count($analysis['warnings'])); ?>
                </h3>
                <?php 
                // Sort by priority
                usort($analysis['warnings'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach ($analysis['warnings'] as $issue) : 
                ?>
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 12px; border-radius: 4px;">
                    <h4 style="margin: 0 0 8px 0; color: #92400e; font-size: 15px;">
                        <?php echo esc_html($issue['issue']); ?>
                    </h4>
                    <p style="margin: 0 0 10px 0; color: #78350f; font-size: 14px;">
                        <strong><?php esc_html_e('Impatto:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['impact']); ?>
                    </p>
                    <p style="margin: 0; padding: 10px; background: white; border-radius: 4px; color: #374151; font-size: 14px;">
                        <strong style="color: #059669;">💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Recommendations -->
            <?php if (!empty($analysis['recommendations'])) : ?>
            <div style="margin-bottom: 25px;">
                <h3 style="color: #2563eb; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                    <span style="font-size: 24px;">💡</span>
                    <?php printf(esc_html__('Raccomandazioni (%d)', 'fp-performance-suite'), count($analysis['recommendations'])); ?>
                </h3>
                <?php 
                // Sort by priority
                usort($analysis['recommendations'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach ($analysis['recommendations'] as $issue) : 
                ?>
                <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 12px; border-radius: 4px;">
                    <h4 style="margin: 0 0 8px 0; color: #1e40af; font-size: 15px;">
                        <?php echo esc_html($issue['issue']); ?>
                    </h4>
                    <p style="margin: 0 0 10px 0; color: #1e3a8a; font-size: 14px;">
                        <strong><?php esc_html_e('Impatto:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['impact']); ?>
                    </p>
                    <p style="margin: 0; padding: 10px; background: white; border-radius: 4px; color: #374151; font-size: 14px;">
                        <strong style="color: #059669;">💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($analysis['critical']) && empty($analysis['warnings']) && empty($analysis['recommendations'])) : ?>
            <div style="padding: 20px; text-align: center; color: #059669; background: #d1fae5; border-radius: 6px;">
                <span style="font-size: 48px;">✅</span>
                <p style="margin: 10px 0 0 0; font-size: 16px; font-weight: 500;">
                    <?php esc_html_e('Nessun problema rilevato! Il tuo sito è ottimizzato correttamente.', 'fp-performance-suite'); ?>
                </p>
            </div>
            <?php endif; ?>
        </section>

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
