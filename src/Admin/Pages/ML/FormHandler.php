<?php

namespace FP\PerfSuite\Admin\Pages\ML;

use FP\PerfSuite\Admin\Form\AbstractFormHandler;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\ML\MLPredictor;
use FP\PerfSuite\Services\ML\AutoTuner;

use function __;
use function update_option;
use function wp_clear_scheduled_hook;
use function time;
use function wp_next_scheduled;
use function date;

/**
 * Gestisce le submission dei form per la pagina ML
 * 
 * @package FP\PerfSuite\Admin\Pages\ML
 * @author Francesco Passeri
 */
class FormHandler extends AbstractFormHandler
{

    /**
     * Gestisce tutte le submission dei form
     * 
     * @return string Messaggio di risultato (vuoto se nessun form processato)
     */
    public function handle(): string
    {
        if (!$this->isPost()) {
            return '';
        }

        $result = '';

        // Handle ML Settings
        if ($this->verifyNonce('fp_ps_ml_nonce', 'fp_ps_ml_settings')) {
            $result = $this->saveMLSettings();
        }
        
        // Handle Auto-Tuner Settings
        if ($this->verifyNonce('fp_ps_auto_tuner_nonce', 'fp_ps_auto_tuner_settings')) {
            if ($this->sanitizeInput('save_auto_tuner_settings', 'bool')) {
                $result = $this->saveAutoTunerSettings();
            } elseif ($this->sanitizeInput('enable_auto_tuning', 'bool')) {
                $result = $this->enableAutoTuning();
            } elseif ($this->sanitizeInput('disable_auto_tuning', 'bool')) {
                $result = $this->disableAutoTuning();
            } elseif ($this->sanitizeInput('run_manual_tuning', 'bool')) {
                $result = $this->runManualTuning();
            }
        }
        
        // Handle Manual Actions
        $action = $this->sanitizeInput('action', 'text');
        if (!empty($action) && $this->verifyNonce('fp_ps_ml_manual_nonce', 'fp_ps_ml_manual_' . $action)) {
            $result = match($action) {
                'manual_analysis' => $this->executeManualAnalysis(),
                'manual_prediction' => $this->executeManualPrediction(),
                'manual_anomaly' => $this->executeManualAnomalyDetection(),
                'check_cron' => $this->checkCronJobs(),
                default => ''
            };
        }

        return $result;
    }

    private function saveMLSettings(): string
    {
        try {
            $settings = [
                'enabled' => $this->sanitizeInput('enabled', 'bool') ?? false,
                'data_retention_days' => $this->sanitizeInput('data_retention_days', 'int') ?? 30,
                'prediction_threshold' => (float)($this->sanitizeInput('prediction_threshold', 'text') ?? 0.7),
                'anomaly_threshold' => (float)($this->sanitizeInput('anomaly_threshold', 'text') ?? 0.8),
                'pattern_confidence_threshold' => (float)($this->sanitizeInput('pattern_confidence_threshold', 'text') ?? 0.8)
            ];
            
            update_option('fp_ps_ml_predictor', $settings);
            return $this->successMessage(__('ML settings saved successfully!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'ML settings save');
        }
    }

    private function saveAutoTunerSettings(): string
    {
        try {
            $settings = [
                'enabled' => $this->sanitizeInput('auto_tuner_enabled', 'bool') ?? false,
                'tuning_frequency' => $this->sanitizeInput('tuning_frequency', 'text') ?? '6hourly',
                'aggressive_mode' => $this->sanitizeInput('aggressive_mode', 'bool') ?? false,
                'auto_apply_changes' => true,
                'tuning_threshold' => 0.1
            ];
            
            update_option('fp_ps_auto_tuner', $settings);
            
            // Se abilitato, registra il servizio
            if ($settings['enabled']) {
                $autoTuner = $this->container->get(AutoTuner::class);
                $autoTuner->register();
            }
            
            return $this->successMessage(__('Auto-Tuner settings saved successfully!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Auto-Tuner settings save');
        }
    }

    private function enableAutoTuning(): string
    {
        try {
            $settings = get_option('fp_ps_auto_tuner', []);
            $settings['enabled'] = true;
            update_option('fp_ps_auto_tuner', $settings);
            
            // Registra il servizio
            $autoTuner = $this->container->get(AutoTuner::class);
            $autoTuner->register();
            
            return $this->successMessage(__('Auto-Tuning enabled successfully!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Enable Auto-Tuning');
        }
    }

    private function disableAutoTuning(): string
    {
        try {
            $settings = get_option('fp_ps_auto_tuner', []);
            $settings['enabled'] = false;
            update_option('fp_ps_auto_tuner', $settings);
            
            // Rimuovi cron job
            wp_clear_scheduled_hook('fp_ps_auto_tune');
            
            return sprintf('<div class="notice notice-warning is-dismissible"><p>%s</p></div>', esc_html(__('Auto-Tuning disabled successfully!', 'fp-performance-suite')));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Disable Auto-Tuning');
        }
    }

    private function runManualTuning(): string
    {
        try {
            $autoTuner = $this->container->get(AutoTuner::class);
            $results = $autoTuner->performAutoTuning();
            
            return $this->successMessage(sprintf(__('Tuning manuale completato! Modificati %d parametri.', 'fp-performance-suite'), count($results['changes'] ?? [])));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Manual tuning');
        }
    }

    private function executeManualAnalysis(): string
    {
        try {
            $predictor = $this->container->get(MLPredictor::class);
            $predictor->analyzePatterns();
            
            // Aggiorna timestamp ultima analisi
            update_option('fp_ps_ml_last_analysis', time());
            
            return $this->successMessage(__('Analisi pattern eseguita con successo!', 'fp-performance-suite'));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Manual analysis');
        }
    }

    private function executeManualPrediction(): string
    {
        try {
            $predictor = $this->container->get(MLPredictor::class);
            $predictions = $predictor->predictIssues();
            
            return $this->successMessage(sprintf(__('Predizioni generate con successo! Trovate %d predizioni.', 'fp-performance-suite'), count($predictions)));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Manual prediction');
        }
    }

    private function executeManualAnomalyDetection(): string
    {
        try {
            $predictor = $this->container->get(MLPredictor::class);
            $anomalies = $predictor->detectAnomalies();
            
            return $this->successMessage(sprintf(__('Rilevamento anomalie completato! Trovate %d anomalie.', 'fp-performance-suite'), count($anomalies)));
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Manual anomaly detection');
        }
    }

    private function checkCronJobs(): string
    {
        try {
            $next_analysis = wp_next_scheduled('fp_ps_ml_analyze_patterns');
            $next_prediction = wp_next_scheduled('fp_ps_ml_predict_issues');
            
            $message = '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html(__('Stato Cron Jobs ML:', 'fp-performance-suite')) . '</strong></p>';
            $message .= '<ul>';
            $message .= '<li>' . esc_html(sprintf(__('Prossima analisi pattern: %s', 'fp-performance-suite'), 
                $next_analysis ? date('Y-m-d H:i:s', $next_analysis) : __('Non programmata', 'fp-performance-suite'))) . '</li>';
            $message .= '<li>' . esc_html(sprintf(__('Prossima predizione: %s', 'fp-performance-suite'), 
                $next_prediction ? date('Y-m-d H:i:s', $next_prediction) : __('Non programmata', 'fp-performance-suite'))) . '</li>';
            $message .= '</ul>';
            
            if (!$next_analysis || !$next_prediction) {
                $message .= '<p><strong>' . esc_html(__('⚠️ Attenzione:', 'fp-performance-suite')) . '</strong> ' . 
                    esc_html(__('Alcuni cron job non sono programmati. Il sistema potrebbe non funzionare correttamente.', 'fp-performance-suite')) . '</p>';
                
                // Prova a riprogrammare i cron job
                $predictor = $this->container->get(MLPredictor::class);
                $predictor->register(); // Questo dovrebbe riprogrammare i cron job
                
                $message .= '<p>' . esc_html(__('Tentativo di riprogrammazione cron job eseguito.', 'fp-performance-suite')) . '</p>';
            }
            
            $message .= '</div>';
            
            return $message;
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Check cron jobs');
        }
    }
}
















