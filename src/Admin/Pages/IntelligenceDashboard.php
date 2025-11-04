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
use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;
use FP\PerfSuite\Services\Intelligence\PerformanceBasedExclusionDetector;
use FP\PerfSuite\Services\Intelligence\CacheAutoConfigurator;
use FP\PerfSuite\Services\Intelligence\IntelligenceReporter;
use FP\PerfSuite\Services\Intelligence\AssetOptimizationIntegrator;
use FP\PerfSuite\Services\Intelligence\CDNExclusionSync;

class IntelligenceDashboard extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
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
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_intelligence_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_intelligence_nonce']), 'fp-ps-intelligence')) {
            
            // Handle auto-optimization
            if (isset($_POST['run_auto_optimization'])) {
                $message = $this->runAutoOptimization();
                $messageType = 'success';
            }
            
            // Handle performance-based exclusions
            if (isset($_POST['apply_performance_exclusions'])) {
                $message = $this->applyPerformanceExclusions();
                $messageType = 'success';
            }
            
            // Handle cache auto-configuration
            if (isset($_POST['auto_configure_cache'])) {
                $message = $this->autoConfigureCache();
                $messageType = 'success';
            }
            
            // Handle asset optimization
            if (isset($_POST['optimize_assets'])) {
                $message = $this->optimizeAssets();
                $messageType = 'success';
            }
            
            // Handle CDN sync
            if (isset($_POST['sync_cdn_exclusions'])) {
                $message = $this->syncCDNExclusions();
                $messageType = 'success';
            }
            
            // Handle generate report
            if (isset($_POST['generate_report'])) {
                $message = $this->generateIntelligenceReport();
                $messageType = 'success';
            }
        }
        
        // Get current data
        $dashboardData = $this->getDashboardData();
        
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

        <?php
        // Intelligence Overview con StatsCard Component
        echo StatsCard::renderGrid([
            [
                'icon' => '🧠',
                'label' => __('Score Intelligence', 'fp-performance-suite'),
                'value' => $dashboardData['overall_score'] . '%',
                'sublabel' => $this->getScoreStatus($dashboardData['overall_score']),
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
                            <div style="background: white; border-left: 4px solid <?php echo esc_attr($this->getPriorityColor($recommendation['priority'])); ?>; padding: 12px; margin: 10px 0; border-radius: 4px;">
                                <div style="font-weight: 600; color: #495057; margin-bottom: 5px;">
                                    <?php echo esc_html($recommendation['title']); ?>
                                </div>
                                <div style="font-size: 13px; color: #6c757d;">
                                    <?php echo esc_html($recommendation['description']); ?>
                                </div>
                                <div style="font-size: 11px; color: #adb5bd; margin-top: 5px;">
                                    <?php echo esc_html($this->getPriorityLabel($recommendation['priority'])); ?>
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

    /**
     * Ottieni dati per la dashboard
     */
    private function getDashboardData(): array
    {
        $reporter = new IntelligenceReporter();
        $dashboardReport = $reporter->generateDashboardReport();
        
        $smartDetector = new SmartExclusionDetector();
        $appliedExclusions = $smartDetector->getAppliedExclusions();
        
        return [
            'overall_score' => $dashboardReport['overall_score'],
            'exclusions_count' => $dashboardReport['exclusions_count'],
            'exclusions_breakdown' => $this->getExclusionsBreakdown($appliedExclusions),
            'performance_score' => $dashboardReport['performance_score'],
            'performance_status' => $this->getPerformanceStatus($dashboardReport['performance_score']),
            'recommendations_count' => $dashboardReport['recommendations_count'],
            'action_required' => $dashboardReport['action_required'],
            'recent_recommendations' => $this->getRecentRecommendations(),
            'smart_detection_status' => $this->getSystemStatus('smart_detection'),
            'performance_monitor_status' => $this->getSystemStatus('performance_monitor'),
            'cache_system_status' => $this->getSystemStatus('cache_system'),
            'cdn_integration_status' => $this->getSystemStatus('cdn_integration'),
        ];
    }

    /**
     * Ottieni breakdown delle esclusioni
     */
    private function getExclusionsBreakdown(array $exclusions): string
    {
        $automatic = count(array_filter($exclusions, fn($e) => $e['type'] === 'automatic'));
        $manual = count(array_filter($exclusions, fn($e) => $e['type'] === 'manual'));
        
        return sprintf('%d auto, %d manuali', $automatic, $manual);
    }

    /**
     * Ottieni status delle performance
     */
    private function getPerformanceStatus(int $score): string
    {
        if ($score >= 80) return 'Eccellente';
        if ($score >= 60) return 'Buono';
        if ($score >= 40) return 'Migliorabile';
        return 'Critico';
    }

    /**
     * Ottieni status del score
     */
    private function getScoreStatus(int $score): string
    {
        if ($score >= 80) return 'Eccellente';
        if ($score >= 60) return 'Buono';
        if ($score >= 40) return 'Migliorabile';
        return 'Critico';
    }

    /**
     * Ottieni raccomandazioni recenti
     */
    private function getRecentRecommendations(): array
    {
        $reporter = new IntelligenceReporter();
        $fullReport = $reporter->generateComprehensiveReport(7);
        
        return array_slice($fullReport['recommendations'] ?? [], 0, 3);
    }

    /**
     * Ottieni status del sistema
     */
    private function getSystemStatus(string $system): string
    {
        switch ($system) {
            case 'smart_detection':
                return get_option('fp_ps_intelligence_enabled', false) ? 'Attivo' : 'Inattivo';
            case 'performance_monitor':
                $monitor = \FP\PerfSuite\Services\Monitoring\PerformanceMonitor::instance();
                return $monitor->isEnabled() ? 'Attivo' : 'Inattivo';
            case 'cache_system':
                $cacheSettings = get_option('fp_ps_page_cache', []);
                return !empty($cacheSettings['enabled']) ? 'Attivo' : 'Inattivo';
            case 'cdn_integration':
                $cdnSync = new CDNExclusionSync();
                $validation = $cdnSync->validateCDNConfiguration();
                return $validation['providers_detected'] > 0 ? 'Rilevato' : 'Non rilevato';
            default:
                return 'Sconosciuto';
        }
    }

    /**
     * Ottieni colore per priorità
     */
    private function getPriorityColor(string $priority): string
    {
        switch ($priority) {
            case 'high': return '#dc3545';
            case 'medium': return '#ffc107';
            case 'low': return '#28a745';
            default: return '#6c757d';
        }
    }

    /**
     * Ottieni label per priorità
     */
    private function getPriorityLabel(string $priority): string
    {
        switch ($priority) {
            case 'high': return 'Priorità Alta';
            case 'medium': return 'Priorità Media';
            case 'low': return 'Priorità Bassa';
            default: return 'Priorità Sconosciuta';
        }
    }

    /**
     * Esegui ottimizzazione automatica
     */
    private function runAutoOptimization(): string
    {
        $results = [];
        
        // 1. Performance-based exclusions
        $performanceDetector = new PerformanceBasedExclusionDetector();
        $performanceResults = $performanceDetector->autoApplyPerformanceExclusions(false);
        $results[] = sprintf('%d esclusioni performance applicate', $performanceResults['applied']);
        
        // 2. Cache auto-configuration
        $cacheConfigurator = new CacheAutoConfigurator();
        $cacheResults = $cacheConfigurator->autoConfigureCacheRules();
        $results[] = sprintf('%d ottimizzazioni cache applicate', $cacheResults['optimizations_applied']);
        
        // 3. Asset optimization
        $assetIntegrator = new AssetOptimizationIntegrator();
        $assetResults = $assetIntegrator->applySmartAssetExclusions();
        $results[] = sprintf('%d esclusioni asset applicate', $assetResults['js_exclusions_applied'] + $assetResults['css_exclusions_applied']);
        
        // 4. CDN sync
        $cdnSync = new CDNExclusionSync();
        $cdnResults = $cdnSync->syncExclusionsWithCDN();
        $results[] = sprintf('%d provider CDN sincronizzati', $cdnResults['cdn_providers_synced']);
        
        return '✅ Ottimizzazione completata: ' . implode(', ', $results);
    }

    /**
     * Applica esclusioni basate su performance
     */
    private function applyPerformanceExclusions(): string
    {
        $performanceDetector = new PerformanceBasedExclusionDetector();
        $results = $performanceDetector->autoApplyPerformanceExclusions(false);
        
        return sprintf('✅ %d esclusioni performance applicate con successo!', $results['applied']);
    }

    /**
     * Auto-configura cache
     */
    private function autoConfigureCache(): string
    {
        $cacheConfigurator = new CacheAutoConfigurator();
        $results = $cacheConfigurator->autoConfigureCacheRules();
        
        return sprintf('✅ %d ottimizzazioni cache applicate con successo!', $results['optimizations_applied']);
    }

    /**
     * Ottimizza asset
     */
    private function optimizeAssets(): string
    {
        $assetIntegrator = new AssetOptimizationIntegrator();
        $results = $assetIntegrator->applySmartAssetExclusions();
        
        return sprintf('✅ %d esclusioni asset applicate con successo!', $results['js_exclusions_applied'] + $results['css_exclusions_applied']);
    }

    /**
     * Sincronizza esclusioni CDN
     */
    private function syncCDNExclusions(): string
    {
        $cdnSync = new CDNExclusionSync();
        $results = $cdnSync->syncExclusionsWithCDN();
        
        return sprintf('✅ %d provider CDN sincronizzati con successo!', $results['cdn_providers_synced']);
    }

    /**
     * Genera report intelligence
     */
    private function generateIntelligenceReport(): string
    {
        $reporter = new IntelligenceReporter();
        $report = $reporter->generateComprehensiveReport(30);
        
        // Salva report per visualizzazione
        update_option('fp_ps_intelligence_last_report', $report);
        
        return sprintf('✅ Report intelligence generato con score %d%%!', $report['summary']['overall_score']);
    }
}
