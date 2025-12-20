<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer;
use FP\PerfSuite\Services\Monitoring\SystemMonitor;
use FP\PerfSuite\Services\Score\Scorer;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Admin\Pages\Overview\ExportHandler;

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
use function date_i18n;
use function get_option;
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
        try {
            // Dati dal Scorer (ex-Dashboard) - con gestione errori
            try {
                $scorer = $this->container->get(Scorer::class);
                $score = $scorer->calculate();
            } catch (\Throwable $e) {
                // Fallback se il servizio non è disponibile
                $score = [
                    'total' => 0,
                    'breakdown' => [],
                    'breakdown_detailed' => [],
                    'suggestions' => []
                ];
            }
        
        // Dati dal Performance Monitor (ex-Performance)
        $monitor = PerformanceMonitor::instance();
        $stats7days = $monitor->getStats(7);
        $stats30days = $monitor->getStats(30);
        
        // Analisi dei problemi
        $analyzer = $this->container->get(PerformanceAnalyzer::class);
        $analysis = $analyzer->analyze();
        
        // Metriche del sistema server
        $systemMonitor = SystemMonitor::instance();
        
        // Raccoglie metriche correnti se non ci sono dati recenti
        $systemStats = $systemMonitor->getStats(7);
        if ($systemStats['samples'] === 0) {
            $systemMonitor->collectMetrics();
            $systemStats = $systemMonitor->getStats(7);
        }
        
        // Raccoglie metriche correnti per aggiornare i dati
        $systemMonitor->collectMetrics();

        // Stato cleaner database
        try {
            $cleaner = $this->container->get(Cleaner::class);
            $cleanerStatus = $cleaner->status();
        } catch (\Throwable $e) {
            $cleanerStatus = null;
        }

        $cleanupScheduleLabel = null;
        $cleanupNextRunLabel = null;
        $cleanupLastRunLabel = null;

        if ($cleanerStatus) {
            $schedule = $cleanerStatus['schedule'] ?? 'manual';
            switch ($schedule) {
                case 'weekly':
                    $cleanupScheduleLabel = __('Weekly', 'fp-performance-suite');
                    break;
                case 'monthly':
                    $cleanupScheduleLabel = __('Monthly', 'fp-performance-suite');
                    break;
                case 'daily':
                    $cleanupScheduleLabel = __('Daily', 'fp-performance-suite');
                    break;
                default:
                    $cleanupScheduleLabel = __('Manual', 'fp-performance-suite');
            }

            if (!empty($cleanerStatus['next_run'])) {
                $dateFormat = get_option('date_format');
                $timeFormat = get_option('time_format');
                $cleanupNextRunLabel = date_i18n($dateFormat . ' ' . $timeFormat, (int) $cleanerStatus['next_run']);
            }

            if (!empty($cleanerStatus['last_run'])) {
                $dateFormat = $dateFormat ?? get_option('date_format');
                $timeFormat = $timeFormat ?? get_option('time_format');
                $cleanupLastRunLabel = date_i18n($dateFormat . ' ' . $timeFormat, (int) $cleanerStatus['last_run']);
            }
        }

        $exportUrl = wp_nonce_url(admin_url('admin-post.php?action=fp_ps_export_csv'), 'fp-ps-export');

        ob_start();
        ?>
        
        <!-- Header con Score e Metriche Principali -->
        <section class="fp-ps-grid two fp-ps-mb-xl">
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
        <section class="fp-ps-quick-wins fp-ps-mb-xl">
            <div class="fp-ps-quick-wins-header fp-ps-mb-lg">
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
            
            <!-- FEATURE: One-Click Safe Optimizations Button -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 24px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                    <div style="flex: 1;">
                        <h3 style="color: white; margin: 0 0 8px 0; font-size: 18px;">
                            🚀 <?php esc_html_e('Ottimizzazione One-Click Sicura', 'fp-performance-suite'); ?>
                        </h3>
                        <p style="color: rgba(255, 255, 255, 0.9); margin: 0; font-size: 14px; line-height: 1.5;">
                            <?php esc_html_e('Attiva con un click tutte le 40 ottimizzazioni classificate VERDI (sicure). Zero rischi, massima performance!', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                    <button 
                        type="button" 
                        id="fp-ps-apply-all-safe" 
                        class="button button-primary button-hero"
                        style="background: white !important; color: #667eea !important; border: none !important; padding: 16px 32px !important; font-size: 16px !important; font-weight: 600 !important; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important; white-space: nowrap;">
                        🎯 <?php esc_html_e('Attiva 40 Opzioni Sicure', 'fp-performance-suite'); ?>
                    </button>
                </div>
                <div id="fp-ps-safe-progress" style="display: none; margin-top: 16px; color: white;">
                    <div style="background: rgba(255, 255, 255, 0.2); border-radius: 8px; height: 8px; overflow: hidden; margin-bottom: 8px;">
                        <div id="fp-ps-safe-progress-bar" style="background: white; height: 100%; width: 0%; transition: width 0.3s ease;"></div>
                    </div>
                    <div id="fp-ps-safe-progress-text" style="font-size: 13px; text-align: center;">
                        <?php esc_html_e('Inizializzazione...', 'fp-performance-suite'); ?>
                    </div>
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
        <section class="fp-ps-grid three fp-ps-mb-xl">
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

        <!-- Metriche del Sistema Server -->
        <section class="fp-ps-grid three fp-ps-mb-xl">
            <div class="fp-ps-stat-box">
                <div class="stat-value">
                    <?php echo number_format($systemStats['memory']['avg_usage_mb'], 1); ?><span class="fp-ps-text-md">MB</span>
                </div>
                <div class="stat-label">💾 <?php esc_html_e('Memoria Media', 'fp-performance-suite'); ?></div>
                <p class="description fp-ps-mt-sm">
                    <?php printf(
                        esc_html__('Picco: %s MB', 'fp-performance-suite'),
                        number_format($systemStats['memory']['max_peak_mb'], 1)
                    ); ?>
                </p>
            </div>
            
            <div class="fp-ps-stat-box">
                <div class="stat-value">
                    <?php echo number_format($systemStats['disk']['usage_percent'], 1); ?><span class="fp-ps-text-md">%</span>
                </div>
                <div class="stat-label">💿 <?php esc_html_e('Spazio Disco', 'fp-performance-suite'); ?></div>
                <p class="description fp-ps-mt-sm">
                    <?php printf(
                        esc_html__('%s GB liberi su %s GB', 'fp-performance-suite'),
                        number_format($systemStats['disk']['free_gb'], 1),
                        number_format($systemStats['disk']['total_gb'], 1)
                    ); ?>
                </p>
            </div>
            
            <div class="fp-ps-stat-box">
                <div class="stat-value">
                    <?php echo number_format($systemStats['load']['avg_1min'], 2); ?>
                </div>
                <div class="stat-label">⚡ <?php esc_html_e('Carico Sistema', 'fp-performance-suite'); ?></div>
                <p class="description fp-ps-mt-sm">
                    <?php printf(
                        esc_html__('5min: %s, 15min: %s', 'fp-performance-suite'),
                        number_format($systemStats['load']['avg_5min'], 2),
                        number_format($systemStats['load']['avg_15min'], 2)
                    ); ?>
                </p>
            </div>
        </section>

        <!-- Dettagli Sistema Server -->
        <section class="fp-ps-grid two fp-ps-mb-xl">
            <div class="fp-ps-card">
                <h2>🖥️ <?php esc_html_e('Informazioni Sistema', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-system-info">
                    <div class="fp-ps-info-row">
                        <span class="fp-ps-info-label"><?php esc_html_e('PHP Version:', 'fp-performance-suite'); ?></span>
                        <span class="fp-ps-info-value"><?php echo esc_html($systemStats['system']['php_version']); ?></span>
                    </div>
                    <div class="fp-ps-info-row">
                        <span class="fp-ps-info-label"><?php esc_html_e('Server:', 'fp-performance-suite'); ?></span>
                        <span class="fp-ps-info-value"><?php echo esc_html($systemStats['system']['server_software']); ?></span>
                    </div>
                    <?php if ($systemStats['system']['cpu_cores']) : ?>
                    <div class="fp-ps-info-row">
                        <span class="fp-ps-info-label"><?php esc_html_e('CPU Cores:', 'fp-performance-suite'); ?></span>
                        <span class="fp-ps-info-value"><?php echo esc_html($systemStats['system']['cpu_cores']); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($systemStats['system']['cpu_model']) : ?>
                    <div class="fp-ps-info-row">
                        <span class="fp-ps-info-label"><?php esc_html_e('CPU Model:', 'fp-performance-suite'); ?></span>
                        <span class="fp-ps-info-value"><?php echo esc_html($systemStats['system']['cpu_model']); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="fp-ps-info-row">
                        <span class="fp-ps-info-label"><?php esc_html_e('Database Size:', 'fp-performance-suite'); ?></span>
                        <span class="fp-ps-info-value"><?php echo number_format($systemStats['database']['size_mb'], 1); ?> MB (<?php echo esc_html($systemStats['database']['tables']); ?> tables)</span>
                    </div>
                <?php if ($cleanupScheduleLabel) : ?>
                <div class="fp-ps-info-row">
                    <span class="fp-ps-info-label"><?php esc_html_e('Cleanup Schedule:', 'fp-performance-suite'); ?></span>
                    <span class="fp-ps-info-value">
                        <?php echo esc_html($cleanupScheduleLabel); ?>
                        <?php if ($cleanupNextRunLabel) : ?>
                            — <?php printf(esc_html__('Next run: %s', 'fp-performance-suite'), esc_html($cleanupNextRunLabel)); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if ($cleanupLastRunLabel) : ?>
                <div class="fp-ps-info-row">
                    <span class="fp-ps-info-label"><?php esc_html_e('Last Cleanup:', 'fp-performance-suite'); ?></span>
                    <span class="fp-ps-info-value"><?php echo esc_html($cleanupLastRunLabel); ?></span>
                </div>
                <?php endif; ?>
                </div>
            </div>

            <div class="fp-ps-card">
                <h2>📊 <?php esc_html_e('Utilizzo Risorse', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-resource-usage">
                    <!-- Memoria -->
                    <div class="fp-ps-resource-item">
                        <div class="fp-ps-resource-header">
                            <span class="fp-ps-resource-label"><?php esc_html_e('Memoria PHP', 'fp-performance-suite'); ?></span>
                            <span class="fp-ps-resource-value"><?php echo number_format($systemStats['memory']['avg_usage_percent'], 1); ?>%</span>
                        </div>
                        <div class="fp-ps-progress-bar">
                            <div class="fp-ps-progress-fill" 
                                 style="width: <?php echo esc_attr($systemStats['memory']['avg_usage_percent']); ?>%; 
                                        background-color: <?php echo $systemStats['memory']['avg_usage_percent'] > 80 ? '#ef4444' : ($systemStats['memory']['avg_usage_percent'] > 60 ? '#f59e0b' : '#10b981'); ?>"></div>
                        </div>
                        <div class="fp-ps-resource-details">
                            <?php printf(
                                esc_html__('Media: %s MB | Picco: %s MB', 'fp-performance-suite'),
                                number_format($systemStats['memory']['avg_usage_mb'], 1),
                                number_format($systemStats['memory']['max_usage_mb'], 1)
                            ); ?>
                        </div>
                    </div>

                    <!-- Spazio Disco -->
                    <div class="fp-ps-resource-item">
                        <div class="fp-ps-resource-header">
                            <span class="fp-ps-resource-label"><?php esc_html_e('Spazio Disco', 'fp-performance-suite'); ?></span>
                            <span class="fp-ps-resource-value"><?php echo number_format($systemStats['disk']['usage_percent'], 1); ?>%</span>
                        </div>
                        <div class="fp-ps-progress-bar">
                            <div class="fp-ps-progress-fill" 
                                 style="width: <?php echo esc_attr($systemStats['disk']['usage_percent']); ?>%; 
                                        background-color: <?php echo $systemStats['disk']['usage_percent'] > 90 ? '#ef4444' : ($systemStats['disk']['usage_percent'] > 80 ? '#f59e0b' : '#10b981'); ?>"></div>
                        </div>
                        <div class="fp-ps-resource-details">
                            <?php printf(
                                esc_html__('Usato: %s GB | Libero: %s GB', 'fp-performance-suite'),
                                number_format($systemStats['disk']['used_gb'], 1),
                                number_format($systemStats['disk']['free_gb'], 1)
                            ); ?>
                        </div>
                    </div>

                    <!-- Carico Sistema -->
                    <?php if ($systemStats['load']['avg_1min'] > 0) : ?>
                    <div class="fp-ps-resource-item">
                        <div class="fp-ps-resource-header">
                            <span class="fp-ps-resource-label"><?php esc_html_e('Carico Sistema', 'fp-performance-suite'); ?></span>
                            <span class="fp-ps-resource-value"><?php echo number_format($systemStats['load']['avg_1min'], 2); ?></span>
                        </div>
                        <div class="fp-ps-progress-bar">
                            <div class="fp-ps-progress-fill" 
                                 style="width: <?php echo esc_attr(min(($systemStats['load']['avg_1min'] / 4) * 100, 100)); ?>%; 
                                        background-color: <?php echo $systemStats['load']['avg_1min'] > 3 ? '#ef4444' : ($systemStats['load']['avg_1min'] > 2 ? '#f59e0b' : '#10b981'); ?>"></div>
                        </div>
                        <div class="fp-ps-resource-details">
                            <?php printf(
                                esc_html__('1min: %s | 5min: %s | 15min: %s', 'fp-performance-suite'),
                                number_format($systemStats['load']['avg_1min'], 2),
                                number_format($systemStats['load']['avg_5min'], 2),
                                number_format($systemStats['load']['avg_15min'], 2)
                            ); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Score Breakdown e Ottimizzazioni Attive -->
        <section class="fp-ps-grid two fp-ps-mb-xl">
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
                    <?php 
                    try {
                        $activeOptimizations = $scorer->activeOptimizations();
                        foreach ($activeOptimizations as $opt) : ?>
                            <li>✓ <?php echo esc_html($opt); ?></li>
                        <?php endforeach;
                    } catch (\Throwable $e) {
                        echo '<li>' . esc_html__('Nessuna ottimizzazione attiva rilevata', 'fp-performance-suite') . '</li>';
                    }
                    ?>
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
        <section class="fp-ps-card fp-ps-mb-xl">
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
        <section class="fp-ps-card fp-ps-mb-xl">
            <h2>⚙️ <?php esc_html_e('Quick Actions', 'fp-performance-suite'); ?></h2>
            <p class="fp-ps-mb-lg"><?php esc_html_e('Run safe optimizations and diagnostics.', 'fp-performance-suite'); ?></p>
            <div class="fp-ps-actions">
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-cache')); ?>">
                    <?php esc_html_e('Configure Cache', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-database')); ?>">
                    <?php esc_html_e('Database Cleanup', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-media')); ?>">
                    <?php esc_html_e('Media Optimization', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-monitoring&tab=diagnostics')); ?>">
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
        // BUGFIX: Aspetta che jQuery sia disponibile prima di eseguire
        (function waitForJQuery() {
            if (typeof jQuery === 'undefined') {
                setTimeout(waitForJQuery, 50);
                return;
            }
            jQuery(document).ready(function($) {
            // Assicurati che fpPerfSuite sia disponibile
            if (typeof fpPerfSuite === 'undefined') {
                console.error('fpPerfSuite non è disponibile. Assicurati che gli script siano caricati correttamente.');
                return;
            }
            
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
                    url: fpPerfSuite.ajaxUrl,
                    type: 'POST',
                    timeout: 15000, // BUGFIX: Timeout 15 secondi per evitare blocchi infiniti
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
                        // BUGFIX: Messaggio specifico per timeout
                        if (status === 'timeout') {
                            alert('<?php echo esc_js(__('Timeout: l\'operazione sta richiedendo troppo tempo. Riprova o abilita manualmente dalla pagina Cache.', 'fp-performance-suite')); ?>');
                        } else {
                            alert('<?php echo esc_js(__('Errore di comunicazione con il server:', 'fp-performance-suite')); ?> ' + error);
                        }
                    }
                });
            });
            
            // FEATURE: One-Click Safe Optimizations Handler
            $('#fp-ps-apply-all-safe').on('click', function() {
                if (!confirm('<?php echo esc_js(__('Vuoi attivare TUTTE le 40 ottimizzazioni sicure (GREEN)?\n\nQueste sono opzioni a ZERO RISCHIO verificate dal sistema.\n\nPuoi sempre disattivarle manualmente dopo.\n\nProcedo?', 'fp-performance-suite')); ?>')) {
                    return;
                }
                
                var $btn = $(this);
                var originalText = $btn.html();
                var $progressContainer = $('#fp-ps-safe-progress');
                var $progressBar = $('#fp-ps-safe-progress-bar');
                var $progressText = $('#fp-ps-safe-progress-text');
                
                $btn.prop('disabled', true).html('⏳ <?php echo esc_js(__('Applicazione in corso...', 'fp-performance-suite')); ?>');
                $progressContainer.show();
                
                // Simula progresso
                var progress = 0;
                var progressInterval = setInterval(function() {
                    if (progress < 90) {
                        progress += Math.random() * 10;
                        if (progress > 90) progress = 90;
                        $progressBar.css('width', progress + '%');
                        $progressText.text('<?php echo esc_js(__('Applicazione in corso...', 'fp-performance-suite')); ?> ' + Math.round(progress) + '%');
                    }
                }, 400);
                
                $.ajax({
                    url: fpPerfSuite.ajaxUrl,
                    type: 'POST',
                    timeout: 60000,
                    data: {
                        action: 'fp_ps_apply_all_safe_optimizations',
                        nonce: '<?php echo wp_create_nonce('fp_ps_apply_all_safe'); ?>'
                    },
                    success: function(response) {
                        clearInterval(progressInterval);
                        $progressBar.css('width', '100%');
                        
                        if (response.success) {
                            var applied = response.data.applied || 0;
                            var total = response.data.total || 40;
                            $progressText.html('✅ <?php echo esc_js(__('Completato!', 'fp-performance-suite')); ?> ' + applied + '/' + total + ' <?php echo esc_js(__('opzioni attivate', 'fp-performance-suite')); ?>');
                            $btn.html('✅ <?php echo esc_js(__('Completato!', 'fp-performance-suite')); ?>').css({'background': '#10b981', 'color': 'white', 'border-color': '#10b981'});
                            
                            setTimeout(function() {
                                alert('<?php echo esc_js(__('🎉 Ottimizzazioni applicate con successo!\n\n', 'fp-performance-suite')); ?>' + response.data.message + '\n\n<?php echo esc_js(__('Ricarico la pagina per mostrare i nuovi risultati...', 'fp-performance-suite')); ?>');
                                location.reload();
                            }, 2000);
                        } else {
                            $progressText.text('❌ <?php echo esc_js(__('Errore', 'fp-performance-suite')); ?>');
                            $btn.prop('disabled', false).html(originalText);
                            $progressContainer.hide();
                            alert('<?php echo esc_js(__('Errore:', 'fp-performance-suite')); ?> ' + (response.data || '<?php echo esc_js(__('Errore sconosciuto', 'fp-performance-suite')); ?>'));
                        }
                    },
                    error: function(xhr, status, error) {
                        clearInterval(progressInterval);
                        $progressContainer.hide();
                        $btn.prop('disabled', false).html(originalText);
                        
                        if (status === 'timeout') {
                            alert('<?php echo esc_js(__('Timeout: Operazione troppo lunga. Riprova o applica manualmente.', 'fp-performance-suite')); ?>');
                        } else {
                            alert('<?php echo esc_js(__('Errore di comunicazione:', 'fp-performance-suite')); ?> ' + error);
                        }
                    }
                });
            });
            
            }); // Chiusura jQuery(document).ready
        })(); // Chiusura e invocazione waitForJQuery
        </script>
        
        <?php
        return (string) ob_get_clean();
        
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Overview page error');
            return '<div class="wrap"><div class="notice notice-error"><p><strong>Errore nella pagina Overview:</strong> ' . esc_html($e->getMessage()) . '</p><p><small>File: ' . esc_html($e->getFile()) . ':' . $e->getLine() . '</small></p></div></div>';
        }
    }

    public function exportCsv(): void
    {
        $exportHandler = new ExportHandler($this->container);
        $exportHandler->export($this->capability());
    }
}