<?php

namespace FP\PerfSuite\Admin\Pages\Settings;

use FP\PerfSuite\ServiceContainer;

use function esc_html;
use function esc_attr;
use function round;
use function __;
use function esc_html_e;
use function esc_attr_e;

/**
 * Gestisce il test automatico dei servizi del plugin
 * 
 * @package FP\PerfSuite\Admin\Pages\Settings
 * @author Francesco Passeri
 */
class ServiceTester
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Render Test Functionality Tab
     */
    public function render(): string
    {
        ob_start();
        
        $total_services = 0;
        $active_services = 0;
        $disabled_services = 0;
        $error_services = 0;
        
        ?>
        
        <style>
            .test-results { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
            .test-category { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; }
            .test-item { padding: 8px 0; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
            .test-item:last-child { border-bottom: none; }
            .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
            .badge-success { background: #28a745; color: white; }
            .badge-disabled { background: #6c757d; color: white; }
            .badge-error { background: #dc3545; color: white; }
            .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
            .summary-box { background: white; padding: 20px; border-radius: 8px; text-align: center; }
            .summary-number { font-size: 42px; font-weight: bold; margin: 10px 0; }
        </style>
        
        <section class="fp-ps-card">
            <h2>🧪 <?php esc_html_e('Test Automatico Funzionalità', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Questa pagina verifica automaticamente che tutti i servizi del plugin siano caricati e configurati correttamente.', 'fp-performance-suite'); ?></p>
            
            <?php
            // Definisci i servizi da testare (SOLO SERVIZI ESISTENTI E REGISTRATI)
            $service_categories = [
                '🚀 Cache' => [
                    ['name' => 'Page Cache', 'class' => 'FP\PerfSuite\Services\Cache\PageCache'],
                    ['name' => 'Browser Cache', 'class' => 'FP\PerfSuite\Services\Cache\BrowserCache'],
                    ['name' => 'Headers (Cache Headers)', 'class' => 'FP\PerfSuite\Services\Cache\Headers'],
                    ['name' => 'Object Cache', 'class' => 'FP\PerfSuite\Services\Cache\ObjectCacheManager'],
                    ['name' => 'Edge Cache Manager', 'class' => 'FP\PerfSuite\Services\Cache\EdgeCacheManager'],
                ],
                '📦 Assets' => [
                    ['name' => 'Asset Optimizer', 'class' => 'FP\PerfSuite\Services\Assets\Optimizer'],
                    ['name' => 'Script Optimizer', 'class' => 'FP\PerfSuite\Services\Assets\ScriptOptimizer'],
                    ['name' => 'CSS Optimizer', 'class' => 'FP\PerfSuite\Services\Assets\CSSOptimizer'],
                    ['name' => 'HTML Minifier', 'class' => 'FP\PerfSuite\Services\Assets\HtmlMinifier'],
                    ['name' => 'Unused JS Optimizer', 'class' => 'FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer'],
                    ['name' => 'Code Splitting', 'class' => 'FP\PerfSuite\Services\Assets\CodeSplittingManager'],
                    ['name' => 'Tree Shaker', 'class' => 'FP\PerfSuite\Services\Assets\JavaScriptTreeShaker'],
                    ['name' => 'Critical CSS', 'class' => 'FP\PerfSuite\Services\Assets\CriticalCss'],
                    ['name' => 'Predictive Prefetch', 'class' => 'FP\PerfSuite\Services\Assets\PredictivePrefetching'],
                ],
                '🖼️ Media' => [
                    ['name' => 'Lazy Load Manager', 'class' => 'FP\PerfSuite\Services\Media\LazyLoadManager'],
                ],
                '💾 Database' => [
                    ['name' => 'Database Optimizer', 'class' => 'FP\PerfSuite\Services\DB\DatabaseOptimizer'],
                    ['name' => 'Query Monitor', 'class' => 'FP\PerfSuite\Services\DB\DatabaseQueryMonitor'],
                    ['name' => 'Query Cache', 'class' => 'FP\PerfSuite\Services\DB\QueryCacheManager'],
                    ['name' => 'Database Cleaner', 'class' => 'FP\PerfSuite\Services\DB\Cleaner'],
                ],
                '⚙️ Backend & Admin' => [
                    ['name' => 'Backend Optimizer', 'class' => 'FP\PerfSuite\Services\Admin\BackendOptimizer'],
                    ['name' => 'Admin Bar', 'class' => 'FP\PerfSuite\Admin\AdminBar'],
                ],
                '📱 Mobile' => [
                    ['name' => 'Mobile Optimizer', 'class' => 'FP\PerfSuite\Services\Mobile\MobileOptimizer'],
                ],
                '🌐 CDN' => [
                    ['name' => 'CDN Manager', 'class' => 'FP\PerfSuite\Services\CDN\CdnManager'],
                ],
                '🛡️ Security' => [
                    ['name' => 'Htaccess Security', 'class' => 'FP\PerfSuite\Services\Security\HtaccessSecurity'],
                ],
                '🗜️ Compression' => [
                    ['name' => 'Compression Manager', 'class' => 'FP\PerfSuite\Services\Compression\CompressionManager'],
                ],
                '🧠 Intelligence & ML' => [
                    ['name' => 'ML Predictor', 'class' => 'FP\PerfSuite\Services\ML\MLPredictor'],
                    ['name' => 'Pattern Learner', 'class' => 'FP\PerfSuite\Services\ML\PatternLearner'],
                    ['name' => 'Auto Tuner', 'class' => 'FP\PerfSuite\Services\ML\AutoTuner'],
                    ['name' => 'Smart Exclusion Detector', 'class' => 'FP\PerfSuite\Services\Intelligence\SmartExclusionDetector'],
                    ['name' => 'Intelligence Reporter', 'class' => 'FP\PerfSuite\Services\Intelligence\IntelligenceReporter'],
                ],
                '📊 Monitoring' => [
                    ['name' => 'Performance Analyzer', 'class' => 'FP\PerfSuite\Services\Monitoring\PerformanceAnalyzer'],
                    ['name' => 'System Monitor', 'class' => 'FP\PerfSuite\Services\Monitoring\SystemMonitor'],
                    ['name' => 'Performance Monitor', 'class' => 'FP\PerfSuite\Services\Monitoring\PerformanceMonitor'],
                ],
                '🎯 Score' => [
                    ['name' => 'Scorer', 'class' => 'FP\PerfSuite\Services\Score\Scorer'],
                ],
            ];
            
            // Testa i servizi
            echo '<div class="test-results">';
            
            foreach ($service_categories as $category => $services) {
                echo '<div class="test-category">';
                echo '<h3 style="margin-top: 0;">' . esc_html($category) . '</h3>';
                
                foreach ($services as $service) {
                    $total_services++;
                    $status = '';
                    $badge_class = '';
                    $error_msg = '';
                    
                    try {
                        $instance = $this->container->get($service['class']);
                        
                        // Verifica se ha settings
                        $is_enabled = null;
                        if (method_exists($instance, 'getSettings')) {
                            try {
                                $settings = $instance->getSettings();
                                $is_enabled = !empty($settings['enabled']);
                            } catch (\Throwable $e) {
                                // Servizio caricato ma settings non funzionante
                                $is_enabled = null;
                            }
                        } elseif (method_exists($instance, 'settings')) {
                            try {
                                $settings = $instance->settings();
                                $is_enabled = !empty($settings['enabled']);
                            } catch (\Throwable $e) {
                                // Servizio caricato ma settings non funzionante
                                $is_enabled = null;
                            }
                        }
                        
                        if ($is_enabled === true) {
                            $status = '✅ Attivo';
                            $badge_class = 'badge-success';
                            $active_services++;
                        } elseif ($is_enabled === false) {
                            $status = '⚪ Disabilitato';
                            $badge_class = 'badge-disabled';
                            $disabled_services++;
                        } else {
                            $status = '✅ Caricato';
                            $badge_class = 'badge-success';
                            $active_services++;
                        }
                        
                    } catch (\Throwable $e) {
                        $status = '❌ Non Disponibile';
                        $badge_class = 'badge-error';
                        $error_services++;
                        $error_msg = $e->getMessage();
                    }
                    
                    echo '<div class="test-item">';
                    echo '<span>' . esc_html($service['name']);
                    if ($error_msg) {
                        echo ' <small style="color: #999;" title="' . esc_attr($error_msg) . '">ℹ️</small>';
                    }
                    echo '</span>';
                    echo '<span class="badge ' . $badge_class . '">' . $status . '</span>';
                    echo '</div>';
                }
                
                echo '</div>';
            }
            
            echo '</div>';
            ?>
            
            <!-- Summary -->
            <section class="fp-ps-card">
                <h2>📊 <?php esc_html_e('Riepilogo Test', 'fp-performance-suite'); ?></h2>
                
                <div class="summary-grid">
                    <div class="summary-box">
                        <div style="color: #007bff; font-size: 14px;"><?php esc_html_e('Servizi Totali', 'fp-performance-suite'); ?></div>
                        <div class="summary-number" style="color: #007bff;"><?php echo $total_services; ?></div>
                    </div>
                    <div class="summary-box">
                        <div style="color: #28a745; font-size: 14px;"><?php esc_html_e('Attivi', 'fp-performance-suite'); ?></div>
                        <div class="summary-number" style="color: #28a745;"><?php echo $active_services; ?></div>
                    </div>
                    <div class="summary-box">
                        <div style="color: #6c757d; font-size: 14px;"><?php esc_html_e('Disabilitati', 'fp-performance-suite'); ?></div>
                        <div class="summary-number" style="color: #6c757d;"><?php echo $disabled_services; ?></div>
                    </div>
                    <div class="summary-box">
                        <div style="color: #dc3545; font-size: 14px;"><?php esc_html_e('Errori', 'fp-performance-suite'); ?></div>
                        <div class="summary-number" style="color: #dc3545;"><?php echo $error_services; ?></div>
                    </div>
                </div>
                
                <?php
                $percentage = $total_services > 0 ? round(($active_services / $total_services) * 100, 1) : 0;
                ?>
                
                <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin-top: 20px;">
                    <div style="font-size: 16px; color: #666; margin-bottom: 10px;">
                        <?php esc_html_e('Percentuale Servizi Attivi', 'fp-performance-suite'); ?>
                    </div>
                    <div style="font-size: 48px; font-weight: bold; <?php echo $percentage >= 70 ? 'color: #28a745;' : ($percentage >= 50 ? 'color: #ffc107;' : 'color: #dc3545;'); ?>">
                        <?php echo $percentage; ?>%
                    </div>
                    <?php if ($percentage >= 70): ?>
                        <div style="color: #28a745; margin-top: 10px;">🎉 <?php esc_html_e('Ottimo! Il plugin è configurato bene.', 'fp-performance-suite'); ?></div>
                    <?php elseif ($percentage >= 50): ?>
                        <div style="color: #856404; margin-top: 10px;">⚠️ <?php esc_html_e('Buono. Alcuni servizi sono disabilitati.', 'fp-performance-suite'); ?></div>
                    <?php else: ?>
                        <div style="color: #721c24; margin-top: 10px;">❌ <?php esc_html_e('Attenzione! Molti servizi non sono attivi.', 'fp-performance-suite'); ?></div>
                    <?php endif; ?>
                </div>
            </section>
            
            <div style="background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0; border-radius: 4px;">
                <h3 style="margin-top: 0;">📝 <?php esc_html_e('Legenda', 'fp-performance-suite'); ?></h3>
                <ul style="margin-bottom: 0;">
                    <li><strong class="badge badge-success">✅ Attivo</strong> - <?php esc_html_e('Servizio caricato e abilitato nelle impostazioni', 'fp-performance-suite'); ?></li>
                    <li><strong class="badge badge-disabled">⚪ Disabilitato</strong> - <?php esc_html_e('Servizio presente ma non abilitato (normale)', 'fp-performance-suite'); ?></li>
                    <li><strong class="badge badge-error">❌ Errore</strong> - <?php esc_html_e('Servizio non disponibile o errore di caricamento', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </section>
        
        <?php
        return (string) ob_get_clean();
    }
}
