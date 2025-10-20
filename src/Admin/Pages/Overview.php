<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer;
use FP\PerfSuite\Services\Presets\Manager as PresetManager;
use FP\PerfSuite\Services\Score\Scorer;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Admin\ThemeHints;

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
            'breadcrumbs' => [__('Panoramica', 'fp-performance-suite')],
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
        
        // Performance history data
        $history = $this->getPerformanceHistory();
        
        // Theme-specific hints
        $themeDetector = $this->container->get(ThemeDetector::class);
        $hints = new ThemeHints($themeDetector);
        $salientNotice = $hints->getSalientNotice();

        ob_start();
        ?>
        
        <?php if ($salientNotice && !get_user_meta(get_current_user_id(), 'fp_ps_dismiss_salient_notice', true)): ?>
        <div class="notice notice-info is-dismissible fp-ps-salient-notice" style="border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 20px;">
            <h3 style="margin-top: 0; color: #1e40af;">
                🎨 <?php printf(esc_html__('Configurazione Ottimizzata per %s', 'fp-performance-suite'), esc_html($salientNotice['theme'])); ?>
            </h3>
            <p style="font-size: 14px; margin: 10px 0;">
                <?php echo $salientNotice['message']; ?>
            </p>
            <div style="background: #dbeafe; padding: 12px; border-radius: 4px; margin: 12px 0;">
                <p style="margin: 0 0 8px 0; font-weight: 600; color: #1e40af;">
                    🚀 <?php esc_html_e('Ottimizzazioni raccomandate ad alta priorità:', 'fp-performance-suite'); ?>
                </p>
                <ul style="margin: 5px 0 0 20px; color: #1e3a8a;">
                    <?php foreach ($salientNotice['recommended_services'] as $service): ?>
                        <li><?php echo esc_html($service); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <p style="margin-top: 12px;">
                <strong>💡 <?php esc_html_e('Suggerimento:', 'fp-performance-suite'); ?></strong>
                <?php esc_html_e('Troverai badge e tooltip specifici per il tuo tema nelle sezioni Cache e Assets. I suggerimenti sono personalizzati per', 'fp-performance-suite'); ?> <?php echo esc_html($salientNotice['theme'] . ' + ' . $salientNotice['builder']); ?>.
            </p>
            <button type="button" class="notice-dismiss" data-dismiss="salient-notice"><span class="screen-reader-text"><?php esc_html_e('Dismiss this notice', 'fp-performance-suite'); ?></span></button>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('.fp-ps-salient-notice .notice-dismiss').on('click', function() {
                $.post(ajaxurl, {
                    action: 'fp_ps_dismiss_salient_notice',
                    nonce: '<?php echo wp_create_nonce('fp_ps_dismiss_salient'); ?>'
                });
                $('.fp-ps-salient-notice').fadeOut(300);
            });
        });
        </script>
        <?php endif; ?>
        
        <!-- Header con Score e Metriche Principali -->
        <section class="fp-ps-grid three">
            <!-- Technical SEO Score -->
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Punteggio Ottimizzazione', 'fp-performance-suite'); ?></h2>
                <div class="fp-ps-score" style="font-size: 48px; margin: 20px 0;">
                    <?php echo esc_html((string) $score['total']); ?><span style="font-size: 24px;">/100</span>
                </div>
                <p class="description">
                    <?php esc_html_e('Livello di ottimizzazione della configurazione', 'fp-performance-suite'); ?>
                </p>
            </div>

            <!-- Health Score -->
            <div class="fp-ps-card" style="background: <?php echo $analysis['score'] >= 70 ? '#d1fae5' : ($analysis['score'] >= 50 ? '#fef3c7' : '#fee2e2'); ?>;">
                <h2><?php esc_html_e('Stato di Salute', 'fp-performance-suite'); ?></h2>
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
                <h3>⚡ <?php esc_html_e('Tempo di Caricamento Medio (7g)', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-score" style="font-size: 36px;">
                    <?php echo number_format($stats7days['avg_load_time'] * 1000, 0); ?><span style="font-size: 18px;">ms</span>
                </div>
                <p class="description">
                    <?php printf(
                        esc_html__('Basato su %s campioni', 'fp-performance-suite'),
                        number_format($stats7days['samples'])
                    ); ?>
                </p>
            </div>
            
            <div class="fp-ps-card">
                <h3>🗄️ <?php esc_html_e('Query Database Medie (7g)', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-score" style="font-size: 36px;">
                    <?php echo number_format($stats7days['avg_queries'], 1); ?>
                </div>
                <p class="description">
                    <?php esc_html_e('Query al database per pagina', 'fp-performance-suite'); ?>
                </p>
            </div>
            
            <div class="fp-ps-card">
                <h3>💾 <?php esc_html_e('Memoria Media (7g)', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-score" style="font-size: 36px;">
                    <?php echo number_format($stats7days['avg_memory'], 1); ?><span style="font-size: 18px;">MB</span>
                </div>
                <p class="description">
                    <?php esc_html_e('Utilizzo massimo della memoria', 'fp-performance-suite'); ?>
                </p>
            </div>
        </section>

        <!-- Score Breakdown e Ottimizzazioni Attive -->
        <section class="fp-ps-grid two">
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Dettaglio Punteggio', 'fp-performance-suite'); ?></h2>
                <div style="margin-bottom: 15px;">
                    <?php foreach ($score['breakdown_detailed'] as $label => $details) : 
                        $statusIcon = $details['status'] === 'complete' ? '✅' : ($details['status'] === 'partial' ? '⚠️' : '❌');
                        $statusColor = $details['status'] === 'complete' ? '#10b981' : ($details['status'] === 'partial' ? '#f59e0b' : '#ef4444');
                    ?>
                        <div class="fp-ps-score-breakdown-item" style="border-left: 4px solid <?php echo esc_attr($statusColor); ?>;">
                            <div class="fp-ps-score-breakdown-header">
                                <div class="fp-ps-score-breakdown-label">
                                    <span style="font-size: 18px;"><?php echo $statusIcon; ?></span>
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
                                        <strong style="color: #3b82f6;">💡 <?php esc_html_e('Come migliorare:', 'fp-performance-suite'); ?></strong>
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
                    <?php esc_html_e('Punteggio più alto indica miglior preparazione tecnica per hosting condiviso.', 'fp-performance-suite'); ?>
                </p>
            </div>

            <div class="fp-ps-card">
                <h2><?php esc_html_e('Ottimizzazioni Attive', 'fp-performance-suite'); ?></h2>
                <ul>
                    <?php foreach ($scorer->activeOptimizations() as $opt) : ?>
                        <li>✓ <?php echo esc_html($opt); ?></li>
                    <?php endforeach; ?>
                </ul>
                <div class="fp-ps-actions" style="margin-top: 20px;">
                    <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-cache')); ?>">
                        <?php esc_html_e('Configura Cache', 'fp-performance-suite'); ?>
                    </a>
                    <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-assets')); ?>">
                        <?php esc_html_e('Configura Risorse', 'fp-performance-suite'); ?>
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
                    <p style="margin: 0 0 10px 0; padding: 10px; background: white; border-radius: 4px; color: #374151; font-size: 14px;">
                        <strong style="color: #059669;">💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </p>
                    <?php if (!empty($issue['action_id'])) : ?>
                    <div style="text-align: right;">
                        <button 
                            type="button" 
                            class="button button-primary fp-ps-apply-recommendation" 
                            data-action-id="<?php echo esc_attr($issue['action_id']); ?>"
                            style="font-size: 13px;">
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
                    <p style="margin: 0 0 10px 0; padding: 10px; background: white; border-radius: 4px; color: #374151; font-size: 14px;">
                        <strong style="color: #059669;">💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </p>
                    <?php if (!empty($issue['action_id'])) : ?>
                    <div style="text-align: right;">
                        <button 
                            type="button" 
                            class="button button-primary fp-ps-apply-recommendation" 
                            data-action-id="<?php echo esc_attr($issue['action_id']); ?>"
                            style="font-size: 13px;">
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
                    <p style="margin: 0 0 10px 0; padding: 10px; background: white; border-radius: 4px; color: #374151; font-size: 14px;">
                        <strong style="color: #059669;">💡 <?php esc_html_e('Soluzione:', 'fp-performance-suite'); ?></strong> 
                        <?php echo esc_html($issue['solution']); ?>
                    </p>
                    <?php if (!empty($issue['action_id'])) : ?>
                    <div style="text-align: right;">
                        <button 
                            type="button" 
                            class="button button-primary fp-ps-apply-recommendation" 
                            data-action-id="<?php echo esc_attr($issue['action_id']); ?>"
                            style="font-size: 13px;">
                            ✨ <?php esc_html_e('Applica Ora', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    <?php endif; ?>
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

        <!-- Performance History Dashboard -->
        <section class="fp-ps-card" style="margin-top: 30px;">
            <h2>📊 <?php esc_html_e('Storico Performance', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Traccia le metriche di performance nel tempo per identificare tendenze e regressioni.', 'fp-performance-suite'); ?></p>
            
            <?php if (!empty($history)) : ?>
            <!-- Time Period Selector -->
            <div style="margin: 20px 0;">
                <label for="history-period" style="font-weight: 600; margin-right: 10px;">
                    <?php esc_html_e('Periodo:', 'fp-performance-suite'); ?>
                </label>
                <select id="history-period" style="padding: 5px 10px;">
                    <option value="7"><?php esc_html_e('Ultimi 7 giorni', 'fp-performance-suite'); ?></option>
                    <option value="30" selected><?php esc_html_e('Ultimi 30 giorni', 'fp-performance-suite'); ?></option>
                    <option value="90"><?php esc_html_e('Ultimi 90 giorni', 'fp-performance-suite'); ?></option>
                </select>
            </div>
            
            <!-- Charts Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-top: 30px;">
                <!-- Load Time Trend -->
                <div style="background: #f9f9f9; padding: 20px; border-radius: 4px;">
                    <h3 style="margin-top: 0; font-size: 16px;">⚡ <?php esc_html_e('Andamento Tempo di Caricamento', 'fp-performance-suite'); ?></h3>
                    <div class="fp-ps-chart" style="height: 200px; position: relative;">
                        <?php echo $this->renderMiniChart($history, 'load_time', '#3b82f6'); ?>
                    </div>
                    <div style="margin-top: 10px; font-size: 13px; color: #666;">
                        <strong><?php esc_html_e('Attuale:', 'fp-performance-suite'); ?></strong>
                        <?php echo number_format(end($history)['load_time'] * 1000, 0); ?>ms
                        <?php 
                        $trend = $this->calculateTrend($history, 'load_time');
                        $trendColor = $trend < 0 ? '#059669' : '#dc2626';
                        $trendIcon = $trend < 0 ? '↓' : '↑';
                        ?>
                        <span style="color: <?php echo $trendColor; ?>; font-weight: 600;">
                            <?php echo $trendIcon; ?> <?php echo abs($trend); ?>%
                        </span>
                    </div>
                </div>
                
                <!-- Database Queries Trend -->
                <div style="background: #f9f9f9; padding: 20px; border-radius: 4px;">
                    <h3 style="margin-top: 0; font-size: 16px;">🗄️ <?php esc_html_e('Andamento Query Database', 'fp-performance-suite'); ?></h3>
                    <div class="fp-ps-chart" style="height: 200px; position: relative;">
                        <?php echo $this->renderMiniChart($history, 'db_queries', '#8b5cf6'); ?>
                    </div>
                    <div style="margin-top: 10px; font-size: 13px; color: #666;">
                        <strong><?php esc_html_e('Attuale:', 'fp-performance-suite'); ?></strong>
                        <?php echo number_format(end($history)['db_queries'], 1); ?>
                        <?php 
                        $trend = $this->calculateTrend($history, 'db_queries');
                        $trendColor = $trend < 0 ? '#059669' : '#dc2626';
                        $trendIcon = $trend < 0 ? '↓' : '↑';
                        ?>
                        <span style="color: <?php echo $trendColor; ?>; font-weight: 600;">
                            <?php echo $trendIcon; ?> <?php echo abs($trend); ?>%
                        </span>
                    </div>
                </div>
                
                <!-- Memory Usage Trend -->
                <div style="background: #f9f9f9; padding: 20px; border-radius: 4px;">
                    <h3 style="margin-top: 0; font-size: 16px;">💾 <?php esc_html_e('Andamento Utilizzo Memoria', 'fp-performance-suite'); ?></h3>
                    <div class="fp-ps-chart" style="height: 200px; position: relative;">
                        <?php echo $this->renderMiniChart($history, 'memory', '#ec4899'); ?>
                    </div>
                    <div style="margin-top: 10px; font-size: 13px; color: #666;">
                        <strong><?php esc_html_e('Attuale:', 'fp-performance-suite'); ?></strong>
                        <?php echo number_format(end($history)['memory'] / 1024 / 1024, 1); ?>MB
                        <?php 
                        $trend = $this->calculateTrend($history, 'memory');
                        $trendColor = $trend < 0 ? '#059669' : '#dc2626';
                        $trendIcon = $trend < 0 ? '↓' : '↑';
                        ?>
                        <span style="color: <?php echo $trendColor; ?>; font-weight: 600;">
                            <?php echo $trendIcon; ?> <?php echo abs($trend); ?>%
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Historical Stats Summary -->
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 30px;">
                <h3 style="margin-top: 0; font-size: 14px; font-weight: 600;"><?php esc_html_e('📈 Insights', 'fp-performance-suite'); ?></h3>
                <ul style="margin: 10px 0 0 20px; font-size: 13px;">
                    <?php
                    $insights = $this->generateInsights($history);
                    foreach ($insights as $insight) :
                    ?>
                        <li style="margin-bottom: 5px;">
                            <strong><?php echo esc_html($insight['title']); ?>:</strong>
                            <?php echo esc_html($insight['text']); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php else : ?>
            <div style="padding: 40px; text-align: center; color: #666;">
                <p style="font-size: 16px; margin-bottom: 10px;">📊 <?php esc_html_e('Nessun dato storico disponibile', 'fp-performance-suite'); ?></p>
                <p style="font-size: 14px;"><?php esc_html_e('I dati di performance verranno raccolti automaticamente. Controlla nuovamente tra 24 ore.', 'fp-performance-suite'); ?></p>
            </div>
            <?php endif; ?>
        </section>
        
        <!-- Quick Actions -->
        <section class="fp-ps-card">
            <h2>⚡ <?php esc_html_e('Azioni Rapide', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Esegui ottimizzazioni sicure e diagnostiche.', 'fp-performance-suite'); ?></p>
            <div class="fp-ps-actions">
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-cache')); ?>">
                    <?php esc_html_e('Configura Cache', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-database')); ?>">
                    <?php esc_html_e('Pulizia Database', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-media')); ?>">
                    <?php esc_html_e('Converti in WebP', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-tools')); ?>">
                    <?php esc_html_e('Esegui Test', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-logs')); ?>">
                    <?php esc_html_e('Visualizza Registro', 'fp-performance-suite'); ?>
                </a>
                <a class="button" href="<?php echo esc_url($exportUrl); ?>">
                    <?php esc_html_e('Esporta Report CSV', 'fp-performance-suite'); ?>
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
            wp_die(esc_html__('Non hai i permessi per esportare questo report.', 'fp-performance-suite'));
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
        fputcsv($output, [__('Punteggio Ottimizzazione', 'fp-performance-suite'), $score['total']]);
        fputcsv($output, [__('Stato di Salute', 'fp-performance-suite'), $analysis['score']]);
        fputcsv($output, []);
        
        // Performance Metrics
        fputcsv($output, [__('Metriche Performance (7 giorni)', 'fp-performance-suite')]);
        fputcsv($output, [__('Tempo Medio Caricamento (ms)', 'fp-performance-suite'), number_format($stats7days['avg_load_time'] * 1000, 0)]);
        fputcsv($output, [__('Query DB Medie', 'fp-performance-suite'), number_format($stats7days['avg_queries'], 1)]);
        fputcsv($output, [__('Memoria Media (MB)', 'fp-performance-suite'), number_format($stats7days['avg_memory'], 1)]);
        fputcsv($output, [__('Campioni', 'fp-performance-suite'), $stats7days['samples']]);
        fputcsv($output, []);
        
        // Score Breakdown
        fputcsv($output, [__('Dettaglio Punteggio', 'fp-performance-suite')]);
        fputcsv($output, [__('Categoria', 'fp-performance-suite'), __('Attuale', 'fp-performance-suite'), __('Massimo', 'fp-performance-suite'), __('Stato', 'fp-performance-suite'), __('Suggerimento', 'fp-performance-suite')]);
        foreach ($score['breakdown_detailed'] as $label => $details) {
            fputcsv($output, [
                $label,
                $details['current'],
                $details['max'],
                $details['status'],
                $details['suggestion'] ?? __('Ottimizzato', 'fp-performance-suite')
            ]);
        }
        fputcsv($output, []);

        // Active Optimizations
        fputcsv($output, [__('Ottimizzazioni Attive', 'fp-performance-suite')]);
        foreach ($active as $item) {
            fputcsv($output, [$item]);
        }
        fputcsv($output, []);
        
        // Issues
        if (!empty($analysis['critical'])) {
            fputcsv($output, [__('Problemi Critici', 'fp-performance-suite')]);
            foreach ($analysis['critical'] as $issue) {
                fputcsv($output, [$issue['issue'], $issue['impact']]);
            }
            fputcsv($output, []);
        }
        
        if (!empty($analysis['warnings'])) {
            fputcsv($output, [__('Avvisi', 'fp-performance-suite')]);
            foreach ($analysis['warnings'] as $issue) {
                fputcsv($output, [$issue['issue'], $issue['impact']]);
            }
            fputcsv($output, []);
        }

        exit;
    }
    
    /**
     * Get performance history data
     */
    private function getPerformanceHistory(): array
    {
        global $wpdb;
        
        $table = $wpdb->prefix . 'fp_ps_performance_history';
        
        // Check if table exists  
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return [];
        }
        
        // Get last 30 days of data
        $history = $wpdb->get_results(
            "SELECT DATE(created_at) as date,
                    AVG(load_time) as load_time,
                    AVG(db_queries) as db_queries,
                    AVG(memory_usage) as memory
             FROM {$table}
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            ARRAY_A
        );
        
        return $history ?: [];
    }
    
    /**
     * Render mini chart using ASCII-style bars
     */
    private function renderMiniChart(array $data, string $metric, string $color): string
    {
        if (empty($data)) {
            return '';
        }
        
        $values = array_column($data, $metric);
        $max = max($values);
        $min = min($values);
        $range = $max - $min ?: 1;
        
        $bars = [];
        foreach ($values as $value) {
            $height = (($value - $min) / $range) * 100;
            $bars[] = sprintf(
                '<div style="flex: 1; background: %s; height: %.1f%%; min-height: 2px; border-radius: 2px 2px 0 0; opacity: 0.8;"></div>',
                $color,
                $height
            );
        }
        
        return sprintf(
            '<div style="display: flex; align-items: flex-end; gap: 2px; height: 100%%; padding: 10px 0;">%s</div>',
            implode('', $bars)
        );
    }
    
    /**
     * Calculate trend percentage
     */
    private function calculateTrend(array $data, string $metric): int
    {
        if (count($data) < 2) {
            return 0;
        }
        
        $values = array_column($data, $metric);
        $first = $values[0];
        $last = end($values);
        
        if ($first == 0) {
            return 0;
        }
        
        $trend = (($last - $first) / $first) * 100;
        return (int) round($trend);
    }
    
    /**
     * Generate insights from history
     */
    private function generateInsights(array $history): array
    {
        $insights = [];
        
        if (empty($history)) {
            return $insights;
        }
        
        // Load time insight
        $loadTimeTrend = $this->calculateTrend($history, 'load_time');
        if ($loadTimeTrend < -10) {
            $insights[] = [
                'title' => __('Miglioramento Tempo di Caricamento', 'fp-performance-suite'),
                'text' => sprintf(__('Il tempo di caricamento è migliorato del %d%% negli ultimi 30 giorni', 'fp-performance-suite'), abs($loadTimeTrend)),
            ];
        } elseif ($loadTimeTrend > 10) {
            $insights[] = [
                'title' => __('Peggioramento Tempo di Caricamento', 'fp-performance-suite'),
                'text' => sprintf(__('Il tempo di caricamento è aumentato del %d%% - considera un\'ottimizzazione', 'fp-performance-suite'), $loadTimeTrend),
            ];
        }
        
        // Database queries insight
        $dbTrend = $this->calculateTrend($history, 'db_queries');
        if ($dbTrend > 20) {
            $insights[] = [
                'title' => __('Aumento Query Database', 'fp-performance-suite'),
                'text' => sprintf(__('Le query DB sono aumentate del %d%% - verifica plugin inefficienti', 'fp-performance-suite'), $dbTrend),
            ];
        }
        
        // Memory usage insight
        $memoryTrend = $this->calculateTrend($history, 'memory');
        if ($memoryTrend > 15) {
            $insights[] = [
                'title' => __('Crescita Utilizzo Memoria', 'fp-performance-suite'),
                'text' => sprintf(__('L\'utilizzo memoria è aumentato del %d%% - potrebbe servire aumentare il limite PHP', 'fp-performance-suite'), $memoryTrend),
            ];
        }
        
        // Stability insight
        $loadTimes = array_column($history, 'load_time');
        $stdDev = $this->calculateStdDev($loadTimes);
        $avg = array_sum($loadTimes) / count($loadTimes);
        $cv = ($stdDev / $avg) * 100;
        
        if ($cv < 20) {
            $insights[] = [
                'title' => __('Performance Stabile', 'fp-performance-suite'),
                'text' => __('Le performance sono consistenti con bassa varianza - eccellente stabilità', 'fp-performance-suite'),
            ];
        } elseif ($cv > 50) {
            $insights[] = [
                'title' => __('Performance Instabile', 'fp-performance-suite'),
                'text' => __('Rilevata alta varianza - investiga cache o risorse server', 'fp-performance-suite'),
            ];
        }
        
        return $insights;
    }
    
    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0;
        }
        
        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(function($val) use ($mean) {
            return pow($val - $mean, 2);
        }, $values)) / $count;
        
        return sqrt($variance);
    }
}
