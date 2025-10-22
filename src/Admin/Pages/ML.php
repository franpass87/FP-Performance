<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Admin\Pages\AbstractPage;
use FP\PerfSuite\ServiceContainer;
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

    protected function content(): string
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }

    public function render(): void
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
        ?>
        <div class="wrap">
            <h1><?php echo esc_html($this->getTitle()); ?></h1>
            
            <div class="fp-ps-admin-grid">
                <!-- ML Settings -->
                <div class="fp-ps-admin-card">
                    <h2><?php _e('Machine Learning Settings', 'fp-performance-suite'); ?></h2>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('fp_ps_ml_settings', 'fp_ps_ml_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="enabled"><?php _e('Enable ML Predictions', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?> />
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
                
                <!-- ML Status -->
                <div class="fp-ps-admin-card">
                    <h2><?php _e('ML Status', 'fp-performance-suite'); ?></h2>
                    
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
                
                <!-- Predictions -->
                <div class="fp-ps-admin-card">
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
                
                <!-- Anomalies -->
                <div class="fp-ps-admin-card">
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
                
                <!-- Auto Tuning -->
                <div class="fp-ps-admin-card">
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
                
                <!-- ML Recommendations -->
                <div class="fp-ps-admin-card">
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
        </div>
        
        <style>
        .fp-ps-ml-stats,
        .fp-ps-anomaly-stats,
        .fp-ps-tuning-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .fp-ps-stat-item {
            display: flex;
            flex-direction: column;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .fp-ps-stat-label {
            font-weight: 600;
            color: #666;
            margin-bottom: 0.5rem;
        }
        
        .fp-ps-stat-value {
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .fp-ps-status-enabled {
            color: #46b450;
        }
        
        .fp-ps-status-disabled {
            color: #dc3232;
        }
        
        .fp-ps-critical {
            color: #dc3232;
        }
        
        .fp-ps-high {
            color: #ffb900;
        }
        
        .fp-ps-predictions,
        .fp-ps-anomalies-list,
        .fp-ps-recommendations {
            margin: 1rem 0;
        }
        
        .fp-ps-prediction,
        .fp-ps-anomaly,
        .fp-ps-recommendation {
            padding: 1rem;
            margin: 0.5rem 0;
            border-left: 4px solid #ddd;
            background: #f9f9f9;
        }
        
        .fp-ps-prediction-high,
        .fp-ps-anomaly-critical,
        .fp-ps-recommendation-high {
            border-left-color: #dc3232;
            background: #fef7f7;
        }
        
        .fp-ps-prediction-medium,
        .fp-ps-anomaly-high,
        .fp-ps-recommendation-medium {
            border-left-color: #ffb900;
            background: #fffbf0;
        }
        
        .fp-ps-prediction-low,
        .fp-ps-anomaly-medium,
        .fp-ps-recommendation-low {
            border-left-color: #00a0d2;
            background: #f0f8ff;
        }
        
        .fp-ps-prediction-header,
        .fp-ps-anomaly-header,
        .fp-ps-rec-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .fp-ps-confidence,
        .fp-ps-rec-confidence {
            font-size: 0.9em;
            color: #666;
            background: #e0e0e0;
            padding: 0.2rem 0.5rem;
            border-radius: 3px;
        }
        
        .fp-ps-prediction-action,
        .fp-ps-anomaly-action,
        .fp-ps-rec-action {
            margin: 0.5rem 0;
            font-style: italic;
            color: #666;
        }
        
        .fp-ps-rec-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.8em;
            color: #999;
        }
        
        .fp-ps-tuning-changes {
            margin: 1rem 0;
        }
        
        .fp-ps-tuning-category {
            margin: 1rem 0;
            padding: 1rem;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .fp-ps-tuning-category h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .fp-ps-tuning-change {
            margin: 0.5rem 0;
            padding: 0.5rem;
            background: #fff;
            border-radius: 3px;
        }
        </style>
        <?php
    }

    private function handleFormSubmission(): void
    {
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
    }

    private function getSettings(): array
    {
        return get_option('fp_ps_ml_predictor', [
            'enabled' => true,
            'data_retention_days' => 30,
            'prediction_threshold' => 0.7,
            'anomaly_threshold' => 0.8,
            'pattern_confidence_threshold' => 0.8
        ]);
    }
}