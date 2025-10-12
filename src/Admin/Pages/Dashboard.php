<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Presets\Manager as PresetManager;
use FP\PerfSuite\Services\Score\Scorer;

use function __;
use function add_action;
use function admin_url;
use function check_admin_referer;
use function current_user_can;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function esc_url;
use function fputcsv;
use function header;
use function nocache_headers;
use function wp_die;
use function wp_nonce_url;

class Dashboard extends AbstractPage
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
            'breadcrumbs' => [__('Dashboard', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $scorer = $this->container->get(Scorer::class);
        $score = $scorer->calculate();
        $presetManager = $this->container->get(PresetManager::class);
        $active = $presetManager->getActivePreset();
        $activeLabel = $active ? $presetManager->labelFor($active) : __('Custom', 'fp-performance-suite');
        $exportUrl = wp_nonce_url(admin_url('admin-post.php?action=fp_ps_export_csv'), 'fp-ps-export');

        ob_start();
        ?>
        <section class="fp-ps-grid two">
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Technical SEO Performance Score', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-score" aria-live="polite"><?php echo esc_html((string) $score['total']); ?></div>
                <p><?php esc_html_e('Score is calculated from caching, assets, images, database, and logging health.', 'fp-performance-suite'); ?></p>
                <table class="fp-ps-table" aria-describedby="fp-ps-score-desc">
                    <tbody>
                    <?php foreach ($score['breakdown'] as $label => $value) : ?>
                        <tr>
                            <th scope="row"><?php echo esc_html($label); ?></th>
                            <td><?php echo esc_html((string) $value); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <p id="fp-ps-score-desc" class="description"><?php esc_html_e('Higher score indicates better technical readiness for shared hosting.', 'fp-performance-suite'); ?></p>
            </div>
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Active Optimizations', 'fp-performance-suite'); ?></h2>
                <ul>
                    <?php foreach ($scorer->activeOptimizations() as $opt) : ?>
                        <li><?php echo esc_html($opt); ?></li>
                    <?php endforeach; ?>
                </ul>
                <p><strong><?php esc_html_e('Preset:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($activeLabel); ?></p>
                <div class="fp-ps-actions">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-cache')); ?>"><?php esc_html_e('Configure Cache', 'fp-performance-suite'); ?></a>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-logs')); ?>"><?php esc_html_e('View Logs', 'fp-performance-suite'); ?></a>
                </div>
            </div>
        </section>
        <section class="fp-ps-grid two">
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Quick Actions', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Run safe optimizations and diagnostics.', 'fp-performance-suite'); ?></p>
                <div class="fp-ps-actions">
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-database')); ?>"><?php esc_html_e('Database Cleanup', 'fp-performance-suite'); ?></a>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-media')); ?>"><?php esc_html_e('Bulk WebP Convert', 'fp-performance-suite'); ?></a>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-tools')); ?>"><?php esc_html_e('Run Tests', 'fp-performance-suite'); ?></a>
                    <a class="button" href="<?php echo esc_url($exportUrl); ?>"><?php esc_html_e('Export CSV Summary', 'fp-performance-suite'); ?></a>
                </div>
            </div>
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Issues & Suggestions', 'fp-performance-suite'); ?></h2>
                <ul>
                    <?php foreach ($score['suggestions'] as $suggestion) : ?>
                        <li><?php echo esc_html($suggestion); ?></li>
                    <?php endforeach; ?>
                </ul>
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

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="fp-performance-suite-score-' . gmdate('Ymd-Hi') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, [__('Metric', 'fp-performance-suite'), __('Value', 'fp-performance-suite')]);
        fputcsv($output, [__('Total Score', 'fp-performance-suite'), $score['total']]);
        foreach ($score['breakdown'] as $label => $value) {
            fputcsv($output, [$label, $value]);
        }

        fputcsv($output, []);
        fputcsv($output, [__('Active Optimizations', 'fp-performance-suite')]);
        foreach ($active as $item) {
            fputcsv($output, [$item]);
        }

        fputcsv($output, []);
        fputcsv($output, [__('Suggestions', 'fp-performance-suite')]);
        foreach ($score['suggestions'] as $suggestion) {
            fputcsv($output, [$suggestion]);
        }

        exit;
    }
}
