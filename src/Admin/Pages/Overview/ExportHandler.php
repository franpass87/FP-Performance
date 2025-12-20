<?php

namespace FP\PerfSuite\Admin\Pages\Overview;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer;
use FP\PerfSuite\Services\Monitoring\SystemMonitor;
use FP\PerfSuite\Services\Score\Scorer;

use function current_user_can;
use function check_admin_referer;
use function wp_die;
use function esc_html__;
use function nocache_headers;
use function header;
use function gmdate;
use function fopen;
use function fputcsv;
use function number_format;
use function __;

/**
 * Gestisce l'export CSV della pagina Overview
 * 
 * @package FP\PerfSuite\Admin\Pages\Overview
 * @author Francesco Passeri
 */
class ExportHandler
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Esegue l'export CSV
     */
    public function export(string $capability): void
    {
        if (!current_user_can($capability)) {
            wp_die(esc_html__('You do not have permission to export this report.', 'fp-performance-suite'));
        }

        check_admin_referer('fp-ps-export');

        try {
            $scorer = $this->container->get(Scorer::class);
            $score = $scorer->calculate();
            $active = $scorer->activeOptimizations();
        } catch (\Throwable $e) {
            $score = ['total' => 0, 'breakdown' => [], 'breakdown_detailed' => [], 'suggestions' => []];
            $active = [];
        }
        
        $monitor = PerformanceMonitor::instance();
        $stats7days = $monitor->getStats(7);
        
        $analyzer = $this->container->get(PerformanceAnalyzer::class);
        $analysis = $analyzer->analyze();
        
        $systemMonitor = SystemMonitor::instance();
        $systemStats = $systemMonitor->getStats(7);

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
        
        // System Metrics
        fputcsv($output, [__('System Metrics', 'fp-performance-suite')]);
        fputcsv($output, [__('Server Memory Usage (%)', 'fp-performance-suite'), number_format($systemStats['memory']['avg_usage_percent'], 1)]);
        fputcsv($output, [__('Server Memory Peak (MB)', 'fp-performance-suite'), number_format($systemStats['memory']['max_peak_mb'], 1)]);
        fputcsv($output, [__('Disk Usage (%)', 'fp-performance-suite'), number_format($systemStats['disk']['usage_percent'], 1)]);
        fputcsv($output, [__('Disk Free (GB)', 'fp-performance-suite'), number_format($systemStats['disk']['free_gb'], 1)]);
        fputcsv($output, [__('System Load (1min)', 'fp-performance-suite'), number_format($systemStats['load']['avg_1min'], 2)]);
        fputcsv($output, [__('Database Size (MB)', 'fp-performance-suite'), number_format($systemStats['database']['size_mb'], 1)]);
        fputcsv($output, [__('PHP Version', 'fp-performance-suite'), $systemStats['system']['php_version']]);
        fputcsv($output, [__('Server Software', 'fp-performance-suite'), $systemStats['system']['server_software']]);
        fputcsv($output, []);
        
        // Score Breakdown
        fputcsv($output, [__('Score Breakdown', 'fp-performance-suite')]);
        fputcsv($output, [__('Category', 'fp-performance-suite'), __('Current', 'fp-performance-suite'), __('Max', 'fp-performance-suite'), __('Status', 'fp-performance-suite'), __('Suggestion', 'fp-performance-suite')]);
        foreach ($score['breakdown_detailed'] as $label => $details) {
            fputcsv($output, [
                $label,
                $details['current'],
                $details['max'],
                $details['status'],
                $details['suggestion'] ?? __('Optimized', 'fp-performance-suite')
            ]);
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
        
        fclose($output);
        exit;
    }
}

