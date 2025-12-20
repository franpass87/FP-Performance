<?php

namespace FP\PerfSuite\Admin\Pages;

use function __;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function date_i18n;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Admin\Components\StatsCard;
use FP\PerfSuite\Admin\Pages\IntelligenceDashboard\FormHandler;
use FP\PerfSuite\Admin\Pages\IntelligenceDashboard\DashboardDataCollector;
use FP\PerfSuite\Admin\Pages\IntelligenceDashboard\DashboardFormatter;

class IntelligenceDashboard extends AbstractPage
{
    private FormHandler $formHandler;
    private DashboardDataCollector $dataCollector;
    private DashboardFormatter $formatter;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->formatter = new DashboardFormatter();
        $this->formHandler = new FormHandler();
        $this->dataCollector = new DashboardDataCollector($this->formatter);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-intelligence';
    }

    public function title(): string
    {
        return __('Dashboard Intelligence', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return 'manage_options';
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Intelligenza AI', 'fp-performance-suite'), __('Dashboard', 'fp-performance-suite')],
        ];
    }
    
    /**
     * Metodo pubblico per ottenere il contenuto (usato quando la pagina viene inclusa in altre pagine)
     */
    public function getContent(): string
    {
        return $this->content();
    }

    protected function content(): string
    {
        $message = '';
        $messageType = 'success';
        
        if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && isset($_POST['fp_ps_intelligence_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_intelligence_nonce']), 'fp-ps-intelligence')) {
            
            // BUGFIX: Handle cache refresh (forza ricalcolo dati)
            if (isset($_POST['refresh_cache'])) {
                $message = $this->formHandler->handleCacheRefresh();
                $messageType = 'success';
            }
            
            // Handle auto-optimization
            if (isset($_POST['run_auto_optimization'])) {
                $message = $this->formHandler->runAutoOptimization();
                $messageType = 'success';
            }
            
            // Handle performance-based exclusions
            if (isset($_POST['apply_performance_exclusions'])) {
                $message = $this->formHandler->applyPerformanceExclusions();
                $messageType = 'success';
            }
            
            // Handle cache auto-configuration
            if (isset($_POST['auto_configure_cache'])) {
                $message = $this->formHandler->autoConfigureCache();
                $messageType = 'success';
            }
            
            // Handle asset optimization
            if (isset($_POST['optimize_assets'])) {
                $message = $this->formHandler->optimizeAssets();
                $messageType = 'success';
            }
            
            // Handle CDN sync
            if (isset($_POST['sync_cdn_exclusions'])) {
                $message = $this->formHandler->syncCDNExclusions();
                $messageType = 'success';
            }
            
            // Handle generate report
            if (isset($_POST['generate_report'])) {
                $message = $this->formHandler->generateIntelligenceReport();
                $messageType = 'success';
            }
        }
        
        // Get current data
        $dashboardData = $this->dataCollector->getDashboardData();
        
        ob_start();
        ?>
        
        <style>
            /* Fix per bottoni con testi lunghi nella pagina Intelligence */
            .fp-ps-actions button.button-secondary {
                white-space: normal !important;
                height: auto !important;
                min-height: 36px;
                padding: 8px 16px !important;
                line-height: 1.4 !important;
                font-size: 13px !important;
                word-wrap: break-word;
                text-align: center;
                display: inline-block;
                max-width: 100%;
            }
            
            .fp-ps-actions {
                margin: 15px 0;
            }
        </style>
        
        <?php
        // Intro Box con PageIntro Component
        echo PageIntro::render(
            '🧠',
            __('Intelligence Dashboard', 'fp-performance-suite'),
            __('Dashboard intelligente con auto-detection, esclusioni automatiche basate sulle performance e raccomandazioni smart.', 'fp-performance-suite')
        );
        ?>
        
        <?php if ($message) : ?>
            <div class="notice notice-<?php echo esc_attr($messageType); ?>">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- BUGFIX: Bottone per refresh cache dati -->
        <div style="margin-bottom: 20px; padding: 10px; background: #fff8dc; border-left: 4px solid #ffb900;">
            <p style="margin: 0 0 10px 0;">
                <strong>ℹ️ <?php esc_html_e('Nota:', 'fp-performance-suite'); ?></strong>
                <?php esc_html_e('I dati di questa dashboard sono cachati per 5 minuti per velocizzare il caricamento. Usa il bottone sotto per aggiornarli manualmente.', 'fp-performance-suite'); ?>
            </p>
            <form method="post" style="display: inline;">
                <?php wp_nonce_field('fp-ps-intelligence', 'fp_ps_intelligence_nonce'); ?>
                <button type="submit" name="refresh_cache" class="button button-secondary" style="vertical-align: middle;">
                    🔄 <?php esc_html_e('Aggiorna Dati Ora', 'fp-performance-suite'); ?>
                </button>
                <span style="font-size: 12px; color: #666; margin-left: 10px;">
                    <?php
                    $cache_time = get_option('_transient_timeout_fp_ps_intelligence_dashboard_data', 0);
                    if ($cache_time > time()) {
                        printf(
                            /* translators: %s: time remaining */
                            esc_html__('Cache valida ancora per %s', 'fp-performance-suite'),
                            human_time_diff(time(), $cache_time)
                        );
                    } else {
                        esc_html_e('Cache scaduta - i dati verranno aggiornati', 'fp-performance-suite');
                    }
                    ?>
                </span>
            </form>
        </div>

        <?php
        // Intelligence Overview con StatsCard Component
        echo StatsCard::renderGrid([
            [
                'icon' => '🧠',
                'label' => __('Score Intelligence', 'fp-performance-suite'),
                'value' => $dashboardData['overall_score'] . '%',
                'sublabel' => $this->formatter->getScoreStatus($dashboardData['overall_score']),
                'gradient' => StatsCard::GRADIENT_PURPLE
            ],
            [
                'icon' => '📊',
                'label' => __('Esclusioni Attive', 'fp-performance-suite'),
                'value' => $dashboardData['exclusions_count'],
                'sublabel' => $dashboardData['exclusions_breakdown'],
                'gradient' => StatsCard::GRADIENT_PINK
            ],
            [
                'icon' => '⚡',
                'label' => __('Performance Score', 'fp-performance-suite'),
                'value' => $dashboardData['performance_score'] . '%',
                'sublabel' => $dashboardData['performance_status'],
                'gradient' => StatsCard::GRADIENT_BLUE
            ],
            [
                'icon' => '🎯',
                'label' => __('Raccomandazioni', 'fp-performance-suite'),
                'value' => $dashboardData['recommendations_count'],
                'sublabel' => $dashboardData['action_required'] ? __('Azione richiesta', 'fp-performance-suite') : __('Tutto OK', 'fp-performance-suite'),
                'gradient' => StatsCard::GRADIENT_GREEN
            ]
        ]);
        ?>

        <form method="post">
            <?php wp_nonce_field('fp-ps-intelligence', 'fp_ps_intelligence_nonce'); ?>

            <!-- Auto-Optimization Section -->
            <section class="fp-ps-card">
                <h2>🚀 <?php esc_html_e('Ottimizzazione Automatica', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Esegui ottimizzazioni automatiche basate sull\'intelligenza artificiale per migliorare le performance del tuo sito.', 'fp-performance-suite'); ?></p>
                
                <div class="fp-ps-actions" style="margin: 20px 0;">
                    <button type="submit" name="run_auto_optimization" class="button button-primary button-large">
                        🎯 <?php esc_html_e('Esegui Ottimizzazione Completa', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <div style="background: #f0f6fc; border-left: 4px solid #0969da; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #0969da;">🤖 <?php esc_html_e('Cosa fa l\'ottimizzazione automatica:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555; font-size: 13px;">
                        <li><?php esc_html_e('Rileva e applica esclusioni intelligenti', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Ottimizza configurazioni cache basandosi su performance', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Configura asset optimization automaticamente', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Sincronizza esclusioni con CDN', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
            </section>

            <!-- Individual Optimization Actions -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin: 30px 0;">
                
                <!-- Performance-Based Exclusions -->
                <section class="fp-ps-card">
                    <h3>📈 <?php esc_html_e('Esclusioni Basate su Performance', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Rileva e applica esclusioni basandosi su metriche di performance reali.', 'fp-performance-suite'); ?></p>
                    
                    <div class="fp-ps-actions">
                        <button type="submit" name="apply_performance_exclusions" class="button button-secondary">
                            📊 <?php esc_html_e('Applica Esclusioni Performance', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    
                    <div style="font-size: 12px; color: #666; margin-top: 10px;">
                        <?php esc_html_e('Analizza pagine lente e problematiche per suggerire esclusioni automatiche.', 'fp-performance-suite'); ?>
                    </div>
                </section>

                <!-- Cache Auto-Configuration -->
                <section class="fp-ps-card">
                    <h3>💾 <?php esc_html_e('Auto-Configurazione Cache', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Configura automaticamente le regole di cache basandosi sulle esclusioni rilevate.', 'fp-performance-suite'); ?></p>
                    
                    <div class="fp-ps-actions">
                        <button type="submit" name="auto_configure_cache" class="button button-secondary">
                            ⚙️ <?php esc_html_e('Configura Cache Automaticamente', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    
                    <div style="font-size: 12px; color: #666; margin-top: 10px;">
                        <?php esc_html_e('Ottimizza TTL, compressione e regole di cache per il tuo tipo di sito.', 'fp-performance-suite'); ?>
                    </div>
                </section>

                <!-- Asset Optimization -->
                <section class="fp-ps-card">
                    <h3>🎨 <?php esc_html_e('Ottimizzazione Asset', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Esclude automaticamente script e CSS critici dall\'ottimizzazione.', 'fp-performance-suite'); ?></p>
                    
                    <div class="fp-ps-actions">
                        <button type="submit" name="optimize_assets" class="button button-secondary">
                            🎯 <?php esc_html_e('Ottimizza Asset', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    
                    <div style="font-size: 12px; color: #666; margin-top: 10px;">
                        <?php esc_html_e('Rileva script critici e configura minificazione, defer e tree shaking.', 'fp-performance-suite'); ?>
                    </div>
                </section>

                <!-- CDN Synchronization -->
                <section class="fp-ps-card">
                    <h3>🌐 <?php esc_html_e('Sincronizzazione CDN', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Sincronizza esclusioni con CDN per evitare cache di contenuti sensibili.', 'fp-performance-suite'); ?></p>
                    
                    <div class="fp-ps-actions">
                        <button type="submit" name="sync_cdn_exclusions" class="button button-secondary">
                            🔄 <?php esc_html_e('Sincronizza con CDN', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                    
                    <div style="font-size: 12px; color: #666; margin-top: 10px;">
                        <?php esc_html_e('Rileva provider CDN attivi e applica regole di esclusione.', 'fp-performance-suite'); ?>
                    </div>
                </section>
            </div>

            <!-- Intelligence Report Section -->
            <section class="fp-ps-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div>
                        <h2>📊 <?php esc_html_e('Report Intelligence', 'fp-performance-suite'); ?></h2>
                        <p><?php esc_html_e('Genera report dettagliati sull\'efficacia delle ottimizzazioni e suggerimenti per miglioramenti.', 'fp-performance-suite'); ?></p>
                    </div>
                    <button type="submit" name="generate_report" class="button button-primary">
                        📈 <?php esc_html_e('Genera Report Completo', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <?php if (!empty($dashboardData['recent_recommendations'])) : ?>
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin-top: 20px;">
                        <h3 style="margin-top: 0; color: #495057;">🎯 <?php esc_html_e('Raccomandazioni Recenti', 'fp-performance-suite'); ?></h3>
                        
                        <?php foreach ($dashboardData['recent_recommendations'] as $recommendation) : ?>
                            <div style="background: white; border-left: 4px solid <?php echo esc_attr($this->formatter->getPriorityColor($recommendation['priority'])); ?>; padding: 12px; margin: 10px 0; border-radius: 4px;">
                                <div style="font-weight: 600; color: #495057; margin-bottom: 5px;">
                                    <?php echo esc_html($recommendation['title']); ?>
                                </div>
                                <div style="font-size: 13px; color: #6c757d;">
                                    <?php echo esc_html($recommendation['description']); ?>
                                </div>
                                <div style="font-size: 11px; color: #adb5bd; margin-top: 5px;">
                                    <?php echo esc_html($this->formatter->getPriorityLabel($recommendation['priority'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <!-- System Status -->
            <section class="fp-ps-card">
                <h2>🔧 <?php esc_html_e('Status Sistema', 'fp-performance-suite'); ?></h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px;">
                    
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 5px;">
                            🧠 <?php esc_html_e('Smart Detection', 'fp-performance-suite'); ?>
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #28a745;">
                            <?php echo esc_html($dashboardData['smart_detection_status']); ?>
                        </div>
                    </div>
                    
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 5px;">
                            📊 <?php esc_html_e('Performance Monitor', 'fp-performance-suite'); ?>
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #17a2b8;">
                            <?php echo esc_html($dashboardData['performance_monitor_status']); ?>
                        </div>
                    </div>
                    
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 5px;">
                            💾 <?php esc_html_e('Cache System', 'fp-performance-suite'); ?>
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #ffc107;">
                            <?php echo esc_html($dashboardData['cache_system_status']); ?>
                        </div>
                    </div>
                    
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px;">
                        <div style="font-weight: 600; color: #495057; margin-bottom: 5px;">
                            🌐 <?php esc_html_e('CDN Integration', 'fp-performance-suite'); ?>
                        </div>
                        <div style="font-size: 24px; font-weight: 700; color: #6f42c1;">
                            <?php echo esc_html($dashboardData['cdn_integration_status']); ?>
                        </div>
                    </div>
                </div>
            </section>

        </form>
        
        <?php
        return (string) ob_get_clean();
    }

    // Metodi rimossi - ora gestiti da DashboardDataCollector, DashboardFormatter e FormHandler
    // getDashboardData() -> DashboardDataCollector::getDashboardData()
    // getExclusionsBreakdown() -> DashboardFormatter::getExclusionsBreakdown()
    // getPerformanceStatus() -> DashboardFormatter::getPerformanceStatus()
    // getScoreStatus() -> DashboardFormatter::getScoreStatus()
    // getRecentRecommendations() -> DashboardFormatter::getRecentRecommendations()
    // getSystemStatus() -> DashboardFormatter::getSystemStatus()
    // getPriorityColor() -> DashboardFormatter::getPriorityColor()
    // getPriorityLabel() -> DashboardFormatter::getPriorityLabel()
    // runAutoOptimization() -> FormHandler::runAutoOptimization()
    // applyPerformanceExclusions() -> FormHandler::applyPerformanceExclusions()
    // autoConfigureCache() -> FormHandler::autoConfigureCache()
    // optimizeAssets() -> FormHandler::optimizeAssets()
    // syncCDNExclusions() -> FormHandler::syncCDNExclusions()
    // generateIntelligenceReport() -> FormHandler::generateIntelligenceReport()
}