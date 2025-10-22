<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer;
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
        
        // Note: L'hook admin_post è ora registrato nella classe Menu
        // per garantire che sia disponibile quando necessario
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
        <section class="fp-ps-grid two">
            <!-- Technical SEO Score -->
            <?php 
            $seoScore = (int) $score['total'];
            $seoScoreClass = $seoScore >= 90 ? 'score-excellent' : ($seoScore >= 70 ? 'score-good' : ($seoScore >= 50 ? 'score-warning' : 'score-critical'));
            ?>
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Technical SEO Score', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-score <?php echo esc_attr($seoScoreClass); ?> fp-ps-my-lg">
                    <?php echo esc_html((string) $score['total']); ?><span class="fp-ps-text-lg">/100</span>
                </div>
                <p class="description">
                    <?php esc_html_e('Configuration optimization score', 'fp-performance-suite'); ?>
                </p>
            </div>

            <!-- Health Score -->
            <?php 
            $healthScore = (int) $analysis['score'];
            $healthClass = $healthScore >= 70 ? 'health-excellent' : ($healthScore >= 50 ? 'health-good' : 'health-poor');
            $healthScoreClass = $healthScore >= 90 ? 'score-excellent' : ($healthScore >= 70 ? 'score-good' : ($healthScore >= 50 ? 'score-warning' : 'score-critical'));
            ?>
            <div class="fp-ps-card <?php echo esc_attr($healthClass); ?>">
                <h2><?php esc_html_e('Health Score', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-score <?php echo esc_attr($healthScoreClass); ?> fp-ps-my-lg">
                    <?php echo esc_html($analysis['score']); ?><span class="fp-ps-text-lg">/100</span>
                </div>
                <p class="description fp-ps-text-muted">
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

        </section>

        <!-- Quick Wins: Azioni Immediate Consigliate -->
        <?php 
        $quickWins = [];
        
        // Raccogli le top 3 azioni con massima priorità
        $allIssues = array_merge(
            array_map(function($issue) { $issue['type'] = 'critical'; return $issue; }, $analysis['critical'] ?? []),
            array_map(function($issue) { $issue['type'] = 'warning'; return $issue; }, $analysis['warnings'] ?? []),
            array_map(function($issue) { $issue['type'] = 'recommendation'; return $issue; }, $analysis['recommendations'] ?? [])
        );
        
        // Filtra solo quelli con action_id
        $actionableIssues = array_filter($allIssues, function($issue) {
            return !empty($issue['action_id']);
        });
        
        // Ordina per priorità
        usort($actionableIssues, function($a, $b) {
            return $b['priority'] - $a['priority'];
        });
        
        $quickWins = array_slice($actionableIssues, 0, 3);
        
        if (!empty($quickWins)) : 
        ?>
        <section class="fp-ps-quick-wins">
            <div class="fp-ps-quick-wins-header">
                <div class="fp-ps-quick-wins-icon">⚡</div>
                <div class="fp-ps-quick-wins-content">
                    <h2>
                        <?php esc_html_e('Quick Wins - Azioni Immediate', 'fp-performance-suite'); ?>
                    </h2>
                    <p>
                        <?php esc_html_e('Applica questi miglioramenti con un click per ottenere risultati immediati', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
            
            <div class="fp-ps-quick-wins-grid">
                <?php foreach ($quickWins as $index => $win) : 
                    $iconMap = [
                        'critical' => '🚨',
                        'warning' => '⚠️',
                        'recommendation' => '💡'
                    ];
                    $icon = $iconMap[$win['type']] ?? '⚡';
                ?>
                <div class="fp-ps-quick-win-card">
                    <div class="fp-ps-quick-win-icon"><?php echo $icon; ?></div>
                    <h3 class="fp-ps-quick-win-title">
                        <?php echo esc_html($win['issue']); ?>
                    </h3>
                    <p class="fp-ps-quick-win-description">
                        <?php echo esc_html($win['impact']); ?>
                    </p>
                    <button 
                        type="button" 
                        class="fp-ps-quick-win-button fp-ps-apply-recommendation" 
                        data-action-id="<?php echo esc_attr($win['action_id']); ?>">
                        ✨ <?php esc_html_e('Applica Ora', 'fp-performance-suite'); ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Metriche di Performance in Tempo Reale -->
        <section class="fp-ps-grid three">
            <div class="fp-ps-stat-box">
                <div class="stat-value">
                    <?php echo number_format($stats7days['avg_load_time'] * 1000, 0); ?><span class="fp-ps-text-md">ms</span>
                </div>
                <div class="stat-label">⚡ <?php esc_html_e('Avg Load Time (7d)', 'fp-performance-suite'); ?></div>
                <p class="description fp-ps-mt-sm">
                    <?php printf(
                        esc_html__('Based on %s samples', 'fp-performance-suite'),
                        number_format($stats7days['samples'])
                    ); ?>
                </p>
            </div>
            
            <div class="fp-ps-stat-box">
                <div class="stat-value">
                    <?php echo number_format($stats7days['avg_queries'], 1); ?>
                </div>
                <div class="stat-label">🗄️ <?php esc_html_e('Avg DB Queries (7d)', 'fp-performance-suite'); ?></div>
                <p class="description fp-ps-mt-sm">
                    <?php esc_html_e('Database queries per page', 'fp-performance-suite'); ?>
                </p>
            </div>
            
            <div class="fp-ps-stat-box">
                <div class="stat-value">
                    <?php echo number_format($stats7days['avg_memory'], 1); ?><span class="fp-ps-text-md">MB</span>
                </div>
                <div class="stat-label">💾 <?php esc_html_e('Avg Memory (7d)', 'fp-performance-suite'); ?></div>
                <p class="description fp-ps-mt-sm">
                    <?php esc_html_e('Peak memory usage', 'fp-performance-suite'); ?>
                </p>
            </div>
        </section>

        <!-- Score Breakdown e Ottimizzazioni Attive -->
        <section class="fp-ps-grid two">
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Score Breakdown', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-mb-md">
                    <?php foreach ($score['breakdown_detailed'] as $label => $details) : 
                        $statusIcon = $details['status'] === 'complete' ? '✅' : ($details['status'] === 'partial' ? '⚠️' : '❌');
                        $statusClass = $details['status'] === 'complete' ? 'complete' : ($details['status'] === 'partial' ? 'partial' : 'incomplete');
                    ?>
                        <div class="fp-ps-score-breakdown-item <?php echo esc_attr($statusClass); ?>">
                            <div class="fp-ps-score-breakdown-header">
                                <div class="fp-ps-score-breakdown-label">
                                    <span><?php echo $statusIcon; ?></span>
                                    <strong><?php echo esc_html($label); ?></strong>
                                </div>
                                <span class="fp-ps-score-breakdown-value fp-ps-status-<?php echo esc_attr($details['status']); ?>">
                                    <?php echo esc_html($details['current']); ?>/<?php echo esc_html($details['max']); ?>
                                </span>
                            </div>
                            
                            <!-- Barra di progresso -->
                            <div class="fp-ps-progress-bar">
                                <div class="fp-ps-progress-fill <?php echo esc_attr($details['status']); ?>" 
                                     style="width: <?php echo esc_attr($details['percentage']); ?>%;"></div>
                            </div>
                            
                            <?php if ($details['suggestion']) : ?>
                                <div class="fp-ps-suggestion-box">
                                    <p>
                                        <strong>💡 <?php esc_html_e('Come migliorare:', 'fp-performance-suite'); ?></strong>
                                        <?php echo esc_html($details['suggestion']); ?>
                                    </p>
                                </div>
                            <?php elseif ($details['status'] === 'complete') : ?>
                                <div class="fp-ps-optimized-box">
                                    <p>
                                        <strong>✨ <?php esc_html_e('Ottimizzato!', 'fp-performance-suite'); ?></strong>
                                        <?php esc_html_e('Questa categoria è completamente ottimizzata.', 'fp-performance-suite'); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
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
                <div class="fp-ps-actions fp-ps-mt-lg">
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
            
            <?php 
            $summaryClass = $analysis['score'] >= 70 ? 'excellent' : ($analysis['score'] >= 50 ? 'good' : 'poor');
            ?>
            <div class="fp-ps-analysis-summary <?php echo esc_attr($summaryClass); ?>">
                <p><?php echo esc_html($analysis['summary']); ?></p>
            </div>

            <!-- Critical Issues -->
            <?php if (!empty($analysis['critical'])) : ?>
            <div class="fp-ps-mb-lg">
                <h3 class="fp-ps-issue-section-header critical">
                    <span>🚨</span>
                    <?php printf(esc_html__('Problemi Critici (%d)', 'fp-performance-suite'), count($analysis['critical'])); ?>
                </h3>
                <?php 
                usort($analysis['critical'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach (array_slice($analysis['critical'], 0, 3) as $issue) : 
                ?>
                <div class="fp-ps-issue-box critical">
                    <h4><?php echo esc_html($issue['issue']); ?></h4>
                    <p>
                        <strong><?php esc_html_e('Impatto:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['impact']); ?>
                    </p>
                    <div class="fp-ps-issue-solution">
                        <strong>💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </div>
                    <?php if (!empty($issue['action_id'])) : ?>
                    <div class="fp-ps-issue-actions">
                        <button 
                            type="button" 
                            class="button button-primary fp-ps-apply-recommendation" 
                            data-action-id="<?php echo esc_attr($issue['action_id']); ?>">
                            ✨ <?php esc_html_e('Applica Ora', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Warnings -->
            <?php if (!empty($analysis['warnings'])) : ?>
            <div class="fp-ps-mb-lg">
                <h3 class="fp-ps-issue-section-header warning">
                    <span>⚠️</span>
                    <?php printf(esc_html__('Avvisi (%d)', 'fp-performance-suite'), count($analysis['warnings'])); ?>
                </h3>
                <?php 
                usort($analysis['warnings'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach (array_slice($analysis['warnings'], 0, 3) as $issue) : 
                ?>
                <div class="fp-ps-issue-box warning">
                    <h4><?php echo esc_html($issue['issue']); ?></h4>
                    <p>
                        <strong><?php esc_html_e('Impatto:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['impact']); ?>
                    </p>
                    <div class="fp-ps-issue-solution">
                        <strong>💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </div>
                    <?php if (!empty($issue['action_id'])) : ?>
                    <div class="fp-ps-issue-actions">
                        <button 
                            type="button" 
                            class="button button-primary fp-ps-apply-recommendation" 
                            data-action-id="<?php echo esc_attr($issue['action_id']); ?>">
                            ✨ <?php esc_html_e('Applica Ora', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Recommendations -->
            <?php if (!empty($analysis['recommendations'])) : ?>
            <div class="fp-ps-mb-lg">
                <h3 class="fp-ps-issue-section-header info">
                    <span>💡</span>
                    <?php printf(esc_html__('Raccomandazioni (%d)', 'fp-performance-suite'), count($analysis['recommendations'])); ?>
                </h3>
                <?php 
                usort($analysis['recommendations'], function($a, $b) {
                    return $b['priority'] - $a['priority'];
                });
                foreach (array_slice($analysis['recommendations'], 0, 3) as $issue) : 
                ?>
                <div class="fp-ps-issue-box recommendation">
                    <h4><?php echo esc_html($issue['issue']); ?></h4>
                    <p>
                        <strong><?php esc_html_e('Impatto:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['impact']); ?>
                    </p>
                    <div class="fp-ps-issue-solution">
                        <strong>💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </div>
                    <?php if (!empty($issue['action_id'])) : ?>
                    <div class="fp-ps-issue-actions">
                        <button 
                            type="button" 
                            class="button button-primary fp-ps-apply-recommendation" 
                            data-action-id="<?php echo esc_attr($issue['action_id']); ?>">
                            ✨ <?php esc_html_e('Applica Ora', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (empty($analysis['critical']) && empty($analysis['warnings']) && empty($analysis['recommendations'])) : ?>
            <div class="fp-ps-notice success fp-ps-text-center">
                <div class="fp-ps-notice-content">
                    <div class="fp-ps-score fp-ps-text-xxl">✅</div>
                    <p class="fp-ps-font-medium">
                        <?php esc_html_e('Nessun problema rilevato! Il tuo sito è ottimizzato correttamente.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
            <?php endif; ?>

            <?php 
            $totalIssues = count($analysis['critical']) + count($analysis['warnings']) + count($analysis['recommendations']);
            if ($totalIssues > 9) : 
            ?>
            <div class="fp-ps-text-center fp-ps-mt-lg">
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
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-settings&tab=diagnostics')); ?>">
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
        
        <!-- JavaScript per applicazione suggerimenti -->
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.fp-ps-apply-recommendation').on('click', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var actionId = $button.data('action-id');
                var originalText = $button.html();
                
                // Conferma prima di applicare
                if (!confirm('<?php echo esc_js(__('Sei sicuro di voler applicare questo suggerimento?', 'fp-performance-suite')); ?>')) {
                    return;
                }
                
                // Disabilita bottone e mostra loading
                $button.prop('disabled', true).html('⏳ <?php echo esc_js(__('Applicazione in corso...', 'fp-performance-suite')); ?>');
                
                // Invia richiesta AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'fp_ps_apply_recommendation',
                        action_id: actionId,
                        nonce: '<?php echo wp_create_nonce('fp_ps_apply_recommendation'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $button.html('✅ <?php echo esc_js(__('Applicato!', 'fp-performance-suite')); ?>')
                                   .removeClass('button-primary')
                                   .addClass('button-secondary')
                                   .css('background-color', '#059669')
                                   .css('border-color', '#059669')
                                   .css('color', '#fff');
                            
                            // Mostra messaggio di successo
                            var $issueCard = $button.closest('div[style*="border-left"]');
                            $issueCard.css({
                                'opacity': '0.6',
                                'transition': 'opacity 0.3s'
                            });
                            
                            // Notifica WordPress
                            if (typeof wp !== 'undefined' && wp.a11y && wp.a11y.speak) {
                                wp.a11y.speak('<?php echo esc_js(__('Suggerimento applicato con successo', 'fp-performance-suite')); ?>');
                            }
                            
                            // Ricarica la pagina dopo 2 secondi per mostrare i nuovi risultati
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $button.prop('disabled', false).html(originalText);
                            alert('<?php echo esc_js(__('Errore:', 'fp-performance-suite')); ?> ' + (response.data.message || '<?php echo esc_js(__('Errore sconosciuto', 'fp-performance-suite')); ?>'));
                        }
                    },
                    error: function(xhr, status, error) {
                        $button.prop('disabled', false).html(originalText);
                        alert('<?php echo esc_js(__('Errore di comunicazione con il server:', 'fp-performance-suite')); ?> ' + error);
                    }
                });
            });
        });
        </script>
        
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

        exit;
    }
}
