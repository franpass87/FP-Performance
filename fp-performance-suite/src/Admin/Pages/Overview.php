<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer;
use FP\PerfSuite\Services\Presets\Manager as PresetManager;
use FP\PerfSuite\Services\Score\Scorer;

use function __;
use function add_action;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_attr_e;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_url;
use function fputcsv;
use function header;
use function nocache_headers;
use function wp_die;
use function wp_nonce_url;

/**
 * Overview Page - Pagina principale integrata
 * 
 * Combina le funzionalità di Dashboard e Performance in un'unica vista
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Overview extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        add_action('admin_post_fp_ps_export_csv', [$this, 'exportCsv']);
    }

    public function slug(): string
    {
        return 'fp-performance-suite';
    }

    public function title(): string
    {
        return __('FP Performance Suite', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Overview', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Dati dal Scorer (ex-Dashboard)
        $scorer = $this->container->get(Scorer::class);
        $score = $scorer->calculate();
        $presetManager = $this->container->get(PresetManager::class);
        $active = $presetManager->getActivePreset();
        $activeLabel = $active ? $presetManager->labelFor($active) : __('Custom', 'fp-performance-suite');
        
        // Dati dal Performance Monitor (ex-Performance)
        $monitor = PerformanceMonitor::instance();
        $stats7days = $monitor->getStats(7);
        $stats30days = $monitor->getStats(30);
        
        // Analisi dei problemi
        $analyzer = $this->container->get(PerformanceAnalyzer::class);
        $analysis = $analyzer->analyze();
        
        $exportUrl = wp_nonce_url(admin_url('admin-post.php?action=fp_ps_export_csv'), 'fp-ps-export');

        ob_start();
        ?>
        
        <!-- Header con Score e Metriche Principali -->
        <section class="fp-ps-grid three">
            <!-- Technical SEO Score -->
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Technical SEO Score', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-score" style="font-size: 48px; margin: 20px 0;">
                    <?php echo esc_html((string) $score['total']); ?><span style="font-size: 24px;">/100</span>
                </div>
                <p class="description">
                    <?php esc_html_e('Configuration optimization score', 'fp-performance-suite'); ?>
                </p>
            </div>

            <!-- Health Score -->
            <div class="fp-ps-card" style="background: <?php echo $analysis['score'] >= 70 ? '#d1fae5' : ($analysis['score'] >= 50 ? '#fef3c7' : '#fee2e2'); ?>;">
                <h2><?php esc_html_e('Health Score', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-score" style="font-size: 48px; margin: 20px 0; color: <?php echo $analysis['score'] >= 70 ? '#059669' : ($analysis['score'] >= 50 ? '#d97706' : '#dc2626'); ?>;">
                    <?php echo esc_html($analysis['score']); ?><span style="font-size: 24px;">/100</span>
                </div>
                <p class="description" style="color: #4b5563;">
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
                </p>
            </div>

            <!-- Preset Attivo -->
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Preset Attivo', 'fp-performance-suite'); ?></h2>
                <div style="font-size: 24px; font-weight: bold; margin: 30px 0; text-align: center;">
                    <?php echo esc_html($activeLabel); ?>
                </div>
                <div class="fp-ps-actions" style="text-align: center;">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-presets')); ?>">
                        <?php esc_html_e('Cambia Preset', 'fp-performance-suite'); ?>
                    </a>
                </div>
            </div>
        </section>

        <!-- Metriche di Performance in Tempo Reale -->
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

        <!-- Score Breakdown e Ottimizzazioni Attive -->
        <section class="fp-ps-grid two">
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Score Breakdown', 'fp-performance-suite'); ?></h2>
                <table class="fp-ps-table" aria-describedby="fp-ps-score-desc">
                    <tbody>
                    <?php foreach ($score['breakdown'] as $label => $value) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($label); ?></th>
                            <td><strong><?php echo esc_html((string) $value); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p id="fp-ps-score-desc" class="description">
                    <?php esc_html_e('Higher score indicates better technical readiness for shared hosting.', 'fp-performance-suite'); ?>
                </p>
            </div>

            <div class="fp-ps-card">
                <h2><?php esc_html_e('Active Optimizations', 'fp-performance-suite'); ?></h2>
                <ul>
                    <?php foreach ($scorer->activeOptimizations() as $opt) : ?>
                        <li>✓ <?php echo esc_html($opt); ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="fp-ps-actions" style="margin-top: 20px;">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-cache')); ?>">
                        <?php esc_html_e('Configure Cache', 'fp-performance-suite'); ?>
                    </a>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-assets')); ?>">
                        <?php esc_html_e('Configure Assets', 'fp-performance-suite'); ?>
                    </a>
                </div>
            </div>
        </section>

        <!-- Analisi Problemi di Performance -->
        <section class="fp-ps-card">
            <h2>🔍 <?php esc_html_e('Analisi Problemi e Raccomandazioni', 'fp-performance-suite'); ?></h2>
            
            <div style="margin-bottom: 20px; padding: 15px; background: <?php echo $analysis['score'] >= 70 ? '#d1fae5' : ($analysis['score'] >= 50 ? '#fef3c7' : '#fee2e2'); ?>; border-radius: 6px;">
                <p style="margin: 0; color: #4b5563; font-size: 15px;"><?php echo esc_html($analysis['summary']); ?></p>
            </div>

            <!-- Critical Issues -->
            <?php if (!empty($analysis['critical'])) : ?>
            <div style="margin-bottom: 25px;">
                <h3 style="color: #dc2626; display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                    <span style="font-size: 24px;">🚨</span>
                    <?php printf(esc_html__('Problemi Critici (%d)', 'fp-performance-suite'), count($analysis['critical'])); ?>
                </h3>
                <?php 
                usort($analysis['critical'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach (array_slice($analysis['critical'], 0, 3) as $issue) : 
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
                usort($analysis['warnings'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach (array_slice($analysis['warnings'], 0, 3) as $issue) : 
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
                usort($analysis['recommendations'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach (array_slice($analysis['recommendations'], 0, 3) as $issue) : 
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

            <?php 
            $totalIssues = count($analysis['critical']) + count($analysis['warnings']) + count($analysis['recommendations']);
            if ($totalIssues > 9) : 
            ?>
            <div style="text-align: center; margin-top: 20px;">
                <p class="description">
                    <?php printf(
                        esc_html__('Visualizzati 9 problemi su %d totali', 'fp-performance-suite'),
                        $totalIssues
                    ); ?>
                </p>
            </div>
            <?php endif; ?>
        </section>

        <!-- Quick Actions -->
        <section class="fp-ps-card">
            <h2>⚙️ <?php esc_html_e('Quick Actions', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Run safe optimizations and diagnostics.', 'fp-performance-suite'); ?></p>
            <div class="fp-ps-actions">
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-cache')); ?>">
                    <?php esc_html_e('Configure Cache', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-database')); ?>">
                    <?php esc_html_e('Database Cleanup', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-media')); ?>">
                    <?php esc_html_e('Bulk WebP Convert', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-tools')); ?>">
                    <?php esc_html_e('Run Tests', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-logs')); ?>">
                    <?php esc_html_e('View Logs', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url($exportUrl); ?>">
                    <?php esc_html_e('Export CSV Summary', 'fp-performance-suite'); ?>
                </a>
            </div>
        </section>
        
        <?php
        return (string) ob_get_clean();
    }

    public function exportCsv(): void
    {
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('You do not have permission to export this report.', 'fp-performance-suite'));
        }

        check_admin_referer('fp-ps-export');

        $scorer = $this->container->get(Scorer::class);
        $score = $scorer->calculate();
        $active = $scorer->activeOptimizations();
        
        $monitor = PerformanceMonitor::instance();
        $stats7days = $monitor->getStats(7);
        
        $analyzer = $this->container->get(PerformanceAnalyzer::class);
        $analysis = $analyzer->analyze();

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="fp-performance-suite-overview-' . gmdate('Ymd-Hi') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // Technical Score
        fputcsv($output, [__('Technical SEO Score', 'fp-performance-suite'), $score['total']]);
        fputcsv($output, [__('Health Score', 'fp-performance-suite'), $analysis['score']]);
        fputcsv($output, []);
        
        // Performance Metrics
        fputcsv($output, [__('Performance Metrics (7 days)', 'fp-performance-suite')]);
        fputcsv($output, [__('Avg Load Time (ms)', 'fp-performance-suite'), number_format($stats7days['avg_load_time'] * 1000, 0)]);
        fputcsv($output, [__('Avg DB Queries', 'fp-performance-suite'), number_format($stats7days['avg_queries'], 1)]);
        fputcsv($output, [__('Avg Memory (MB)', 'fp-performance-suite'), number_format($stats7days['avg_memory'], 1)]);
        fputcsv($output, [__('Samples', 'fp-performance-suite'), $stats7days['samples']]);
        fputcsv($output, []);
        
        // Score Breakdown
        fputcsv($output, [__('Score Breakdown', 'fp-performance-suite')]);
        foreach ($score['breakdown'] as $label => $value) {
            fputcsv($output, [$label, $value]);
        }
        fputcsv($output, []);

        // Active Optimizations
        fputcsv($output, [__('Active Optimizations', 'fp-performance-suite')]);
        foreach ($active as $item) {
            fputcsv($output, [$item]);
        }
        fputcsv($output, []);
        
        // Issues
        if (!empty($analysis['critical'])) {
            fputcsv($output, [__('Critical Issues', 'fp-performance-suite')]);
            foreach ($analysis['critical'] as $issue) {
                fputcsv($output, [$issue['issue'], $issue['impact']]);
            }
            fputcsv($output, []);
        }
        
        if (!empty($analysis['warnings'])) {
            fputcsv($output, [__('Warnings', 'fp-performance-suite')]);
            foreach ($analysis['warnings'] as $issue) {
                fputcsv($output, [$issue['issue'], $issue['impact']]);
            }
            fputcsv($output, []);
        }

        exit;
    }
}
