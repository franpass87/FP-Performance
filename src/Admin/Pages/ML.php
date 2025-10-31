<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Admin\Pages\AbstractPage;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Services\ML\MLPredictor;
use FP\PerfSuite\Services\ML\PatternLearner;
use FP\PerfSuite\Services\ML\AnomalyDetector;
use FP\PerfSuite\Services\ML\AutoTuner;

/**
 * Machine Learning Admin Page
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ML extends AbstractPage
{
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-ml';
    }

    public function title(): string
    {
        return __('Machine Learning', 'fp-performance-suite');
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('AI & ML', 'fp-performance-suite'), __('Machine Learning', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $this->handleFormSubmission();
        $settings = $this->getSettings();
        
        // Get ML services
        $predictor = $this->container->get(MLPredictor::class);
        $pattern_learner = $this->container->get(PatternLearner::class);
        $anomaly_detector = $this->container->get(AnomalyDetector::class);
        $auto_tuner = $this->container->get(AutoTuner::class);
        
        // Get reports
        $ml_report = $predictor->generateMLReport();
        $anomaly_report = $anomaly_detector->generateAnomalyReport();
        $tuning_report = $auto_tuner->generateTuningReport();
        
        // Get current tab
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'overview';
        $valid_tabs = ['overview', 'settings', 'predictions', 'anomalies', 'tuning'];
        if (!in_array($current_tab, $valid_tabs, true)) {
            $current_tab = 'overview';
        }
        
        ob_start();
        ?>
        
        <!-- Pannello Introduttivo -->
        <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                🤖 <?php esc_html_e('Machine Learning Intelligence', 'fp-performance-suite'); ?>
            </h2>
            <p style="margin: 0; font-size: 16px; line-height: 1.6; opacity: 0.95;">
                <?php esc_html_e('L\'intelligenza artificiale che impara dal tuo sito per ottimizzare automaticamente le performance e prevedere problemi futuri.', 'fp-performance-suite'); ?>
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px;">
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">🧠</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px; color: white;"><?php esc_html_e('Predizioni Intelligenti', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5; color: white;">
                        <?php esc_html_e('Analizza pattern di performance e prevede problemi prima che si verifichino', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">🔍</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px; color: white;"><?php esc_html_e('Rilevamento Anomalie', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5; color: white;">
                        <?php esc_html_e('Identifica automaticamente comportamenti anomali e potenziali problemi', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">⚙️</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px; color: white;"><?php esc_html_e('Auto-Tuning', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5; color: white;">
                        <?php esc_html_e('Ottimizza automaticamente le impostazioni basandosi sui dati raccolti', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Alert Informazioni -->
        <div class="notice notice-info inline" style="margin-bottom: 25px; border-left-color: #10b981;">
            <p style="margin: 0.5em 0;">
                <strong>🧠 <?php esc_html_e('Intelligenza Artificiale:', 'fp-performance-suite'); ?></strong> 
                <?php esc_html_e('Il sistema ML impara continuamente dai dati di performance per migliorare la precisione delle predizioni.', 'fp-performance-suite'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-monitoring')); ?>" style="text-decoration: none;">
                    <?php esc_html_e('Visualizza dati di monitoraggio →', 'fp-performance-suite'); ?>
                </a>
            </p>
        </div>
        
        <!-- Navigazione Tabs -->
        <div class="nav-tab-wrapper fp-ps-tab-wrapper">
            <a href="?page=fp-performance-suite-ml&tab=overview" 
               class="nav-tab <?php echo $current_tab === 'overview' ? 'nav-tab-active' : ''; ?>">
                📊 <?php esc_html_e('Overview', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-ml&tab=settings" 
               class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                ⚙️ <?php esc_html_e('Impostazioni', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-ml&tab=predictions" 
               class="nav-tab <?php echo $current_tab === 'predictions' ? 'nav-tab-active' : ''; ?>">
                🔮 <?php esc_html_e('Predizioni', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-ml&tab=anomalies" 
               class="nav-tab <?php echo $current_tab === 'anomalies' ? 'nav-tab-active' : ''; ?>">
                🚨 <?php esc_html_e('Anomalie', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-ml&tab=tuning" 
               class="nav-tab <?php echo $current_tab === 'tuning' ? 'nav-tab-active' : ''; ?>">
                🔧 <?php esc_html_e('Auto-Tuning', 'fp-performance-suite'); ?>
            </a>
        </div>

        <div class="wrap">
            <?php if ($current_tab === 'overview'): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;">
                    <!-- ML Status Overview -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('Stato Sistema ML', 'fp-performance-suite'); ?></h2>
                        
                        <div class="fp-ps-ml-stats">
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Status', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value <?php echo $ml_report['enabled'] ? 'fp-ps-status-enabled' : 'fp-ps-status-disabled'; ?>">
                                    <?php echo $ml_report['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                                </span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Data Points', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo esc_html($ml_report['data_points']); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Model Accuracy', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo esc_html(round($ml_report['model_accuracy'] * 100, 1)); ?>%</span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Last Analysis', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo $ml_report['last_analysis'] ? esc_html(date('Y-m-d H:i:s', $ml_report['last_analysis'])) : __('Never', 'fp-performance-suite'); ?></span>
                            </div>
                        </div>
                    </div>
                    <!-- Anomaly Detection Overview -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('Rilevamento Anomalie', 'fp-performance-suite'); ?></h2>
                        
                        <div class="fp-ps-anomaly-stats">
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Anomalies Found', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo esc_html($anomaly_report['anomalies_count']); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Critical', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value fp-ps-critical"><?php echo esc_html($anomaly_report['critical_anomalies']); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('High Priority', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value fp-ps-high"><?php echo esc_html($anomaly_report['high_anomalies']); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Auto Tuning Overview -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('Auto Tuning', 'fp-performance-suite'); ?></h2>
                        
                        <div class="fp-ps-tuning-stats">
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Status', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value <?php echo $tuning_report['enabled'] ? 'fp-ps-status-enabled' : 'fp-ps-status-disabled'; ?>">
                                    <?php echo $tuning_report['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                                </span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Tuning Count', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo esc_html($tuning_report['tuning_count']); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Last Tuning', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo $tuning_report['last_tuning'] ? esc_html(date('Y-m-d H:i:s', $tuning_report['last_tuning'])) : __('Never', 'fp-performance-suite'); ?></span>
                            </div>
                        </div>
                        
                        <!-- Pulsanti Azione -->
                        <div class="fp-ps-ml-actions">
                            <h3><?php _e('Azioni Manuali', 'fp-performance-suite'); ?></h3>
                            <p class="description"><?php _e('Esegui analisi manuali quando necessario', 'fp-performance-suite'); ?></p>
                            
                            <div class="fp-ps-action-buttons">
                                <form method="post" action="" class="fp-ps-action-form">
                                    <?php wp_nonce_field('fp_ps_ml_manual_analysis', 'fp_ps_ml_manual_nonce'); ?>
                                    <input type="hidden" name="action" value="manual_analysis" />
                                    <?php submit_button(__('Esegui Analisi Pattern', 'fp-performance-suite'), 'secondary', 'manual_analysis', false); ?>
                                </form>
                                
                                <form method="post" action="" class="fp-ps-action-form">
                                    <?php wp_nonce_field('fp_ps_ml_manual_prediction', 'fp_ps_ml_manual_nonce'); ?>
                                    <input type="hidden" name="action" value="manual_prediction" />
                                    <?php submit_button(__('Genera Predizioni', 'fp-performance-suite'), 'secondary', 'manual_prediction', false); ?>
                                </form>
                                
                                <form method="post" action="" class="fp-ps-action-form">
                                    <?php wp_nonce_field('fp_ps_ml_manual_anomaly', 'fp_ps_ml_manual_nonce'); ?>
                                    <input type="hidden" name="action" value="manual_anomaly" />
                                    <?php submit_button(__('Rileva Anomalie', 'fp-performance-suite'), 'secondary', 'manual_anomaly', false); ?>
                                </form>
                                
                                <form method="post" action="" class="fp-ps-action-form">
                                    <?php wp_nonce_field('fp_ps_ml_check_cron', 'fp_ps_ml_manual_nonce'); ?>
                                    <input type="hidden" name="action" value="check_cron" />
                                    <?php submit_button(__('Verifica Cron Jobs', 'fp-performance-suite'), 'secondary', 'check_cron', false); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php elseif ($current_tab === 'settings'): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;">
                    <!-- ML Settings -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('Machine Learning Settings', 'fp-performance-suite'); ?></h2>
                        
                        <form method="post" action="">
                            <?php wp_nonce_field('fp_ps_ml_settings', 'fp_ps_ml_nonce'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="enabled">
                                            <?php _e('Enable ML Predictions', 'fp-performance-suite'); ?>
                                            <?php echo RiskMatrix::renderIndicator('ml_predictor_enabled'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('ml_predictor_enabled')); ?>" />
                                        <p class="description"><?php _e('Enable machine learning predictions and auto-tuning', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="data_retention_days"><?php _e('Data Retention (Days)', 'fp-performance-suite'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="data_retention_days" name="data_retention_days" value="<?php echo esc_attr($settings['data_retention_days']); ?>" min="7" max="365" />
                                        <p class="description"><?php _e('How long to keep performance data for ML analysis', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="prediction_threshold"><?php _e('Prediction Threshold', 'fp-performance-suite'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="prediction_threshold" name="prediction_threshold" value="<?php echo esc_attr($settings['prediction_threshold']); ?>" min="0.1" max="1.0" step="0.1" />
                                        <p class="description"><?php _e('Minimum confidence level for predictions (0.1-1.0)', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="anomaly_threshold"><?php _e('Anomaly Threshold', 'fp-performance-suite'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="anomaly_threshold" name="anomaly_threshold" value="<?php echo esc_attr($settings['anomaly_threshold']); ?>" min="0.1" max="1.0" step="0.1" />
                                        <p class="description"><?php _e('Minimum confidence level for anomaly detection (0.1-1.0)', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="pattern_confidence_threshold"><?php _e('Pattern Confidence Threshold', 'fp-performance-suite'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="pattern_confidence_threshold" name="pattern_confidence_threshold" value="<?php echo esc_attr($settings['pattern_confidence_threshold']); ?>" min="0.1" max="1.0" step="0.1" />
                                        <p class="description"><?php _e('Minimum confidence level for pattern recognition (0.1-1.0)', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(__('Save ML Settings', 'fp-performance-suite')); ?>
                        </form>
                    </div>
                </div>
            <?php elseif ($current_tab === 'predictions'): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;">
                    <!-- ML Predictions -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('ML Predictions', 'fp-performance-suite'); ?></h2>
                        
                        <?php if (!empty($ml_report['predictions'])): ?>
                            <div class="fp-ps-predictions">
                                <?php foreach ($ml_report['predictions'] as $prediction): ?>
                                    <div class="fp-ps-prediction fp-ps-prediction-<?php echo esc_attr($prediction['severity']); ?>">
                                        <div class="fp-ps-prediction-header">
                                            <strong><?php echo esc_html($prediction['message']); ?></strong>
                                            <span class="fp-ps-confidence"><?php echo esc_html(round($prediction['confidence'] * 100, 1)); ?>% confidence</span>
                                        </div>
                                        <p class="fp-ps-prediction-action"><?php echo esc_html($prediction['recommended_action']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p><?php _e('No predictions available. ML needs more data to make predictions.', 'fp-performance-suite'); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- ML Recommendations -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('ML Recommendations', 'fp-performance-suite'); ?></h2>
                        
                        <?php
                        $recommendations = $predictor->getMLRecommendations();
                        if (!empty($recommendations)):
                        ?>
                            <div class="fp-ps-recommendations">
                                <?php foreach (array_slice($recommendations, 0, 10) as $rec): ?>
                                    <div class="fp-ps-recommendation fp-ps-recommendation-<?php echo esc_attr($rec['priority']); ?>">
                                        <div class="fp-ps-rec-header">
                                            <strong><?php echo esc_html($rec['message']); ?></strong>
                                            <span class="fp-ps-rec-confidence"><?php echo esc_html(round($rec['confidence'] * 100, 1)); ?>%</span>
                                        </div>
                                        <p class="fp-ps-rec-action"><?php echo esc_html($rec['action']); ?></p>
                                        <div class="fp-ps-rec-meta">
                                            <span class="fp-ps-rec-type"><?php echo esc_html(ucfirst($rec['type'])); ?></span>
                                            <span class="fp-ps-rec-priority"><?php echo esc_html(ucfirst($rec['priority'])); ?> Priority</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p><?php _e('No recommendations available. ML needs more data to generate recommendations.', 'fp-performance-suite'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($current_tab === 'anomalies'): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;">
                    <!-- Anomaly Detection -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('Anomaly Detection', 'fp-performance-suite'); ?></h2>
                        
                        <div class="fp-ps-anomaly-stats">
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Anomalies Found', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo esc_html($anomaly_report['anomalies_count']); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Critical', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value fp-ps-critical"><?php echo esc_html($anomaly_report['critical_anomalies']); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('High Priority', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value fp-ps-high"><?php echo esc_html($anomaly_report['high_anomalies']); ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($anomaly_report['anomalies'])): ?>
                            <h3><?php _e('Recent Anomalies', 'fp-performance-suite'); ?></h3>
                            <div class="fp-ps-anomalies-list">
                                <?php foreach (array_slice($anomaly_report['anomalies'], 0, 5) as $anomaly): ?>
                                    <div class="fp-ps-anomaly fp-ps-anomaly-<?php echo esc_attr($anomaly['severity']); ?>">
                                        <div class="fp-ps-anomaly-header">
                                            <strong><?php echo esc_html($anomaly['message']); ?></strong>
                                            <span class="fp-ps-confidence"><?php echo esc_html(round($anomaly['confidence'] * 100, 1)); ?>% confidence</span>
                                        </div>
                                        <p class="fp-ps-anomaly-action"><?php echo esc_html($anomaly['recommended_action']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($current_tab === 'tuning'): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;">
                    <!-- Auto Tuning -->
                    <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php _e('Auto Tuning', 'fp-performance-suite'); ?></h2>
                        
                        <div class="fp-ps-tuning-stats">
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Status', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value <?php echo $tuning_report['enabled'] ? 'fp-ps-status-enabled' : 'fp-ps-status-disabled'; ?>">
                                    <?php echo $tuning_report['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                                </span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Tuning Count', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo esc_html($tuning_report['tuning_count']); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Last Tuning', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo $tuning_report['last_tuning'] ? esc_html(date('Y-m-d H:i:s', $tuning_report['last_tuning'])) : __('Never', 'fp-performance-suite'); ?></span>
                            </div>
                            
                            <div class="fp-ps-stat-item">
                                <span class="fp-ps-stat-label"><?php _e('Next Tuning', 'fp-performance-suite'); ?>:</span>
                                <span class="fp-ps-stat-value"><?php echo $tuning_report['next_tuning'] ? esc_html(date('Y-m-d H:i:s', $tuning_report['next_tuning'])) : __('Not scheduled', 'fp-performance-suite'); ?></span>
                            </div>
                        </div>
                        
                        <!-- Controlli Auto-Tuning -->
                        <div class="fp-ps-tuning-controls">
                            <h3><?php _e('Auto-Tuning Controls', 'fp-performance-suite'); ?></h3>
                            
                            <form method="post" action="">
                                <?php wp_nonce_field('fp_ps_auto_tuner_settings', 'fp_ps_auto_tuner_nonce'); ?>
                                
                                <div class="fp-ps-form-group">
                                    <label for="auto_tuner_enabled" class="fp-ps-checkbox-label">
                                        <input type="checkbox" 
                                               id="auto_tuner_enabled" 
                                               name="auto_tuner_enabled" 
                                               value="1" 
                                               <?php checked($tuning_report['enabled'], true); ?>
                                               class="fp-ps-checkbox" />
                                        <?php _e('Enable Auto-Tuning', 'fp-performance-suite'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Automatically optimize performance settings based on machine learning analysis', 'fp-performance-suite'); ?>
                                    </p>
                                </div>
                                
                                <div class="fp-ps-form-group">
                                    <label for="tuning_frequency" class="fp-ps-label">
                                        <?php _e('Tuning Frequency', 'fp-performance-suite'); ?>
                                    </label>
                                    <select id="tuning_frequency" name="tuning_frequency" class="fp-ps-select">
                                        <?php 
                                        $frequencies = [
                                            'hourly' => __('Every Hour', 'fp-performance-suite'),
                                            '6hourly' => __('Every 6 Hours', 'fp-performance-suite'),
                                            'daily' => __('Daily', 'fp-performance-suite'),
                                            'weekly' => __('Weekly', 'fp-performance-suite')
                                        ];
                                        $current_frequency = get_option('fp_ps_auto_tuner')['tuning_frequency'] ?? '6hourly';
                                        foreach ($frequencies as $value => $label): ?>
                                            <option value="<?php echo esc_attr($value); ?>" <?php selected($current_frequency, $value); ?>>
                                                <?php echo esc_html($label); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="fp-ps-form-group">
                                    <label for="aggressive_mode" class="fp-ps-checkbox-label">
                                        <input type="checkbox" 
                                               id="aggressive_mode" 
                                               name="aggressive_mode" 
                                               value="1" 
                                               <?php checked(get_option('fp_ps_auto_tuner')['aggressive_mode'] ?? false, true); ?>
                                               class="fp-ps-checkbox" />
                                        <?php _e('Aggressive Mode', 'fp-performance-suite'); ?>
                                    </label>
                                    <p class="description">
                                        <?php _e('Apply more aggressive optimizations (use with caution)', 'fp-performance-suite'); ?>
                                    </p>
                                </div>
                                
                                <div class="fp-ps-tuning-actions">
                                    <button type="submit" name="save_auto_tuner_settings" class="button button-primary">
                                        <?php _e('Save Settings', 'fp-performance-suite'); ?>
                                    </button>
                                    
                                    <?php if ($tuning_report['enabled']): ?>
                                        <button type="submit" name="run_manual_tuning" class="button button-secondary">
                                            <?php _e('Run Manual Tuning', 'fp-performance-suite'); ?>
                                        </button>
                                        
                                        <button type="submit" name="disable_auto_tuning" class="button fp-ps-button-danger">
                                            <?php _e('Disable Auto-Tuning', 'fp-performance-suite'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" name="enable_auto_tuning" class="button fp-ps-button-success">
                                            <?php _e('Enable Auto-Tuning', 'fp-performance-suite'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        
                        <?php if (!empty($tuning_report['recent_changes'])): ?>
                            <h3><?php _e('Recent Changes', 'fp-performance-suite'); ?></h3>
                            <div class="fp-ps-tuning-changes">
                                <?php foreach ($tuning_report['recent_changes'] as $category => $result): ?>
                                    <?php if ($result['tuned']): ?>
                                        <div class="fp-ps-tuning-category">
                                            <h4><?php echo esc_html(ucfirst($category)); ?></h4>
                                            <?php foreach ($result['changes'] as $change): ?>
                                                <div class="fp-ps-tuning-change">
                                                    <strong><?php echo esc_html($change['setting']); ?>:</strong>
                                                    <?php echo esc_html($change['old_value']); ?> → <?php echo esc_html($change['new_value']); ?>
                                                    <em>(<?php echo esc_html($change['reason']); ?>)</em>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        
        return ob_get_clean();
    }

    private function handleFormSubmission(): void
    {
        // Handle ML Settings
        if (isset($_POST['fp_ps_ml_nonce']) && wp_verify_nonce($_POST['fp_ps_ml_nonce'], 'fp_ps_ml_settings')) {
            $settings = [
                'enabled' => !empty($_POST['enabled']),
                'data_retention_days' => intval($_POST['data_retention_days'] ?? 30),
                'prediction_threshold' => floatval($_POST['prediction_threshold'] ?? 0.7),
                'anomaly_threshold' => floatval($_POST['anomaly_threshold'] ?? 0.8),
                'pattern_confidence_threshold' => floatval($_POST['pattern_confidence_threshold'] ?? 0.8)
            ];
            
            update_option('fp_ps_ml_predictor', $settings);
            echo '<div class="notice notice-success"><p>' . __('ML settings saved successfully!', 'fp-performance-suite') . '</p></div>';
        }
        
        // Handle Auto-Tuner Settings
        if (isset($_POST['fp_ps_auto_tuner_nonce']) && wp_verify_nonce($_POST['fp_ps_auto_tuner_nonce'], 'fp_ps_auto_tuner_settings')) {
            if (isset($_POST['save_auto_tuner_settings'])) {
                $this->saveAutoTunerSettings();
            } elseif (isset($_POST['enable_auto_tuning'])) {
                $this->enableAutoTuning();
            } elseif (isset($_POST['disable_auto_tuning'])) {
                $this->disableAutoTuning();
            } elseif (isset($_POST['run_manual_tuning'])) {
                $this->runManualTuning();
            }
        }
        
        // Handle Manual Actions
        if (isset($_POST['fp_ps_ml_manual_nonce']) && isset($_POST['action'])) {
            $action = sanitize_text_field($_POST['action']);
            
            if ($action === 'manual_analysis' && wp_verify_nonce($_POST['fp_ps_ml_manual_nonce'], 'fp_ps_ml_manual_analysis')) {
                $this->executeManualAnalysis();
            } elseif ($action === 'manual_prediction' && wp_verify_nonce($_POST['fp_ps_ml_manual_nonce'], 'fp_ps_ml_manual_prediction')) {
                $this->executeManualPrediction();
            } elseif ($action === 'manual_anomaly' && wp_verify_nonce($_POST['fp_ps_ml_manual_nonce'], 'fp_ps_ml_manual_anomaly')) {
                $this->executeManualAnomalyDetection();
            } elseif ($action === 'check_cron' && wp_verify_nonce($_POST['fp_ps_ml_manual_nonce'], 'fp_ps_ml_check_cron')) {
                $this->checkCronJobs();
            }
        }
    }
    
    private function executeManualAnalysis(): void
    {
        try {
            $predictor = $this->container->get(MLPredictor::class);
            $predictor->analyzePatterns();
            
            // Aggiorna timestamp ultima analisi
            update_option('fp_ps_ml_last_analysis', time());
            
            echo '<div class="notice notice-success"><p>' . __('Analisi pattern eseguita con successo!', 'fp-performance-suite') . '</p></div>';
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . sprintf(__('Errore durante l\'analisi: %s', 'fp-performance-suite'), $e->getMessage()) . '</p></div>';
        }
    }
    
    private function executeManualPrediction(): void
    {
        try {
            $predictor = $this->container->get(MLPredictor::class);
            $predictions = $predictor->predictIssues();
            
            echo '<div class="notice notice-success"><p>' . sprintf(__('Predizioni generate con successo! Trovate %d predizioni.', 'fp-performance-suite'), count($predictions)) . '</p></div>';
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . sprintf(__('Errore durante la generazione predizioni: %s', 'fp-performance-suite'), $e->getMessage()) . '</p></div>';
        }
    }
    
    private function executeManualAnomalyDetection(): void
    {
        try {
            $predictor = $this->container->get(MLPredictor::class);
            $anomalies = $predictor->detectAnomalies();
            
            echo '<div class="notice notice-success"><p>' . sprintf(__('Rilevamento anomalie completato! Trovate %d anomalie.', 'fp-performance-suite'), count($anomalies)) . '</p></div>';
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . sprintf(__('Errore durante il rilevamento anomalie: %s', 'fp-performance-suite'), $e->getMessage()) . '</p></div>';
        }
    }
    
    private function checkCronJobs(): void
    {
        $next_analysis = wp_next_scheduled('fp_ps_ml_analyze_patterns');
        $next_prediction = wp_next_scheduled('fp_ps_ml_predict_issues');
        
        $message = '<div class="notice notice-info"><p><strong>' . __('Stato Cron Jobs ML:', 'fp-performance-suite') . '</strong></p>';
        $message .= '<ul>';
        $message .= '<li>' . sprintf(__('Prossima analisi pattern: %s', 'fp-performance-suite'), 
            $next_analysis ? date('Y-m-d H:i:s', $next_analysis) : __('Non programmata', 'fp-performance-suite')) . '</li>';
        $message .= '<li>' . sprintf(__('Prossima predizione: %s', 'fp-performance-suite'), 
            $next_prediction ? date('Y-m-d H:i:s', $next_prediction) : __('Non programmata', 'fp-performance-suite')) . '</li>';
        $message .= '</ul>';
        
        if (!$next_analysis || !$next_prediction) {
            $message .= '<p><strong>' . __('⚠️ Attenzione:', 'fp-performance-suite') . '</strong> ' . 
                __('Alcuni cron job non sono programmati. Il sistema potrebbe non funzionare correttamente.', 'fp-performance-suite') . '</p>';
            
            // Prova a riprogrammare i cron job
            $predictor = $this->container->get(MLPredictor::class);
            $predictor->register(); // Questo dovrebbe riprogrammare i cron job
            
            $message .= '<p>' . __('Tentativo di riprogrammazione cron job eseguito.', 'fp-performance-suite') . '</p>';
        }
        
        $message .= '</div>';
        
        echo $message;
    }

    private function getSettings(): array
    {
        return get_option('fp_ps_ml_predictor', [
            'enabled' => false,
            'data_retention_days' => 30,
            'prediction_threshold' => 0.7,
            'anomaly_threshold' => 0.8,
            'pattern_confidence_threshold' => 0.8
        ]);
    }
    
    /**
     * Salva le impostazioni Auto-Tuner
     */
    private function saveAutoTunerSettings(): void
    {
        $settings = [
            'enabled' => !empty($_POST['auto_tuner_enabled']),
            'tuning_frequency' => sanitize_text_field($_POST['tuning_frequency'] ?? '6hourly'),
            'aggressive_mode' => !empty($_POST['aggressive_mode']),
            'auto_apply_changes' => true,
            'tuning_threshold' => 0.1
        ];
        
        update_option('fp_ps_auto_tuner', $settings);
        
        // Se abilitato, registra il servizio
        if ($settings['enabled']) {
            $autoTuner = $this->container->get(\FP\PerfSuite\Services\ML\AutoTuner::class);
            $autoTuner->register();
        }
        
        echo '<div class="notice notice-success"><p>' . __('Auto-Tuner settings saved successfully!', 'fp-performance-suite') . '</p></div>';
    }
    
    /**
     * Abilita Auto-Tuning
     */
    private function enableAutoTuning(): void
    {
        $settings = get_option('fp_ps_auto_tuner', []);
        $settings['enabled'] = true;
        update_option('fp_ps_auto_tuner', $settings);
        
        // Registra il servizio
        $autoTuner = $this->container->get(\FP\PerfSuite\Services\ML\AutoTuner::class);
        $autoTuner->register();
        
        echo '<div class="notice notice-success"><p>' . __('Auto-Tuning enabled successfully!', 'fp-performance-suite') . '</p></div>';
    }
    
    /**
     * Disabilita Auto-Tuning
     */
    private function disableAutoTuning(): void
    {
        $settings = get_option('fp_ps_auto_tuner', []);
        $settings['enabled'] = false;
        update_option('fp_ps_auto_tuner', $settings);
        
        // Rimuovi cron job
        wp_clear_scheduled_hook('fp_ps_auto_tune');
        
        echo '<div class="notice notice-warning"><p>' . __('Auto-Tuning disabled successfully!', 'fp-performance-suite') . '</p></div>';
    }
    
    /**
     * Esegue tuning manuale
     */
    private function runManualTuning(): void
    {
        try {
            $autoTuner = $this->container->get(\FP\PerfSuite\Services\ML\AutoTuner::class);
            $results = $autoTuner->performAutoTuning();
            
            if (!empty($results)) {
                echo '<div class="notice notice-success"><p>' . __('Manual tuning completed successfully!', 'fp-performance-suite') . '</p></div>';
            } else {
                echo '<div class="notice notice-info"><p>' . __('Manual tuning completed with no changes.', 'fp-performance-suite') . '</p></div>';
            }
        } catch (Exception $e) {
            echo '<div class="notice notice-error"><p>' . sprintf(__('Manual tuning failed: %s', 'fp-performance-suite'), $e->getMessage()) . '</p></div>';
        }
    }
}