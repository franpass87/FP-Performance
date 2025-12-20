<?php

namespace FP\PerfSuite\Admin\Pages\ML\Tabs;

use function __;
use function esc_html;
use function esc_attr;
use function date;
use function wp_nonce_field;
use function submit_button;

/**
 * Tab Overview per la pagina ML
 * 
 * @package FP\PerfSuite\Admin\Pages\ML\Tabs
 * @author Francesco Passeri
 */
class OverviewTab
{
    private array $mlReport;
    private array $anomalyReport;
    private array $tuningReport;

    public function __construct(array $mlReport, array $anomalyReport, array $tuningReport)
    {
        $this->mlReport = $mlReport;
        $this->anomalyReport = $anomalyReport;
        $this->tuningReport = $tuningReport;
    }

    public function render(): void
    {
        ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;">
            <!-- ML Status Overview -->
            <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2><?php _e('Stato Sistema ML', 'fp-performance-suite'); ?></h2>
                
                <div class="fp-ps-ml-stats">
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Status', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value <?php echo $this->mlReport['enabled'] ? 'fp-ps-status-enabled' : 'fp-ps-status-disabled'; ?>">
                            <?php echo $this->mlReport['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                        </span>
                    </div>
                    
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Data Points', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value"><?php echo esc_html($this->mlReport['data_points']); ?></span>
                    </div>
                    
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Model Accuracy', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value"><?php echo esc_html(round($this->mlReport['model_accuracy'] * 100, 1)); ?>%</span>
                    </div>
                    
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Last Analysis', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value"><?php echo $this->mlReport['last_analysis'] ? esc_html(date('Y-m-d H:i:s', $this->mlReport['last_analysis'])) : __('Never', 'fp-performance-suite'); ?></span>
                    </div>
                </div>
            </div>
            <!-- Anomaly Detection Overview -->
            <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2><?php _e('Rilevamento Anomalie', 'fp-performance-suite'); ?></h2>
                
                <div class="fp-ps-anomaly-stats">
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Anomalies Found', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value"><?php echo esc_html($this->anomalyReport['anomalies_count']); ?></span>
                    </div>
                    
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Critical', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value fp-ps-critical"><?php echo esc_html($this->anomalyReport['critical_anomalies']); ?></span>
                    </div>
                    
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('High Priority', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value fp-ps-high"><?php echo esc_html($this->anomalyReport['high_anomalies']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Auto Tuning Overview -->
            <div class="fp-ps-admin-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2><?php _e('Auto Tuning', 'fp-performance-suite'); ?></h2>
                
                <div class="fp-ps-tuning-stats">
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Status', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value <?php echo $this->tuningReport['enabled'] ? 'fp-ps-status-enabled' : 'fp-ps-status-disabled'; ?>">
                            <?php echo $this->tuningReport['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                        </span>
                    </div>
                    
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Tuning Count', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value"><?php echo esc_html($this->tuningReport['tuning_count']); ?></span>
                    </div>
                    
                    <div class="fp-ps-stat-item">
                        <span class="fp-ps-stat-label"><?php _e('Last Tuning', 'fp-performance-suite'); ?>:</span>
                        <span class="fp-ps-stat-value"><?php echo $this->tuningReport['last_tuning'] ? esc_html(date('Y-m-d H:i:s', $this->tuningReport['last_tuning'])) : __('Never', 'fp-performance-suite'); ?></span>
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
        <?php
    }
}
















