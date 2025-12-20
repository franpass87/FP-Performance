<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports;

use FP\PerfSuite\Admin\Form\AbstractFormHandler;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor;
use FP\PerfSuite\Services\Reports\ScheduledReports;
use FP\PerfSuite\Admin\NoticeManager;

use function current_user_can;
use function get_option;
use function update_option;
use function __;

/**
 * Gestisce le submission dei form per MonitoringReports page
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports
 * @author Francesco Passeri
 */
class FormHandler extends AbstractFormHandler
{

    /**
     * Gestisce tutte le submission dei form
     * 
     * @param string $capability Capability richiesta per l'accesso
     * @return string Messaggio di risultato
     */
    public function handle(string $capability = ''): string
    {
        if (!$this->isPost()) {
            return '';
        }

        // Verifica permessi utente
        if (!empty($capability) && !current_user_can($capability)) {
            return $this->errorMessage(__('Permesso negato. Non hai i permessi necessari per salvare queste impostazioni.', 'fp-performance-suite'));
        }

        // Verifica nonce di sicurezza
        if (!$this->verifyNonce('_wpnonce', 'fp_ps_monitoring')) {
            return $this->errorMessage(__('Errore di sicurezza: nonce non valido o scaduto. Riprova a ricaricare la pagina.', 'fp-performance-suite'));
        }

        try {
            // Save Monitoring settings
            $this->saveMonitoringSettings();
            
            // Save Core Web Vitals settings
            $this->saveCoreWebVitalsSettings();
            
            // Save Reports settings
            $this->saveReportsSettings();
            
            // Save Webhook Integration settings
            $this->saveWebhookSettings();
            
            // Save Performance Budget settings
            $this->savePerformanceBudgetSettings();

            $message = __('Monitoring & Reports settings saved successfully!', 'fp-performance-suite');
            NoticeManager::success($message);
            return $this->successMessage($message);

        } catch (\Throwable $e) {
            return $this->handleError($e, 'MonitoringReports form');
        }
    }

    /**
     * Salva le impostazioni di monitoring
     */
    private function saveMonitoringSettings(): void
    {
        try {
            $monitor = PerformanceMonitor::instance();
            $currentMonitoring = $monitor->settings();
            
            // Merge con i valori correnti
            $monitoringSettings = array_merge($currentMonitoring, [
                'enabled' => $this->sanitizeInput('monitoring', 'array')['enabled'] ?? false,
                'sample_rate' => $this->sanitizeInput('monitoring', 'array')['sample_rate'] 
                    ? (int)$this->sanitizeInput('monitoring', 'array')['sample_rate'] 
                    : ($currentMonitoring['sample_rate'] ?? 10),
            ]);
            
            $monitor->update($monitoringSettings);
        } catch (\Throwable $e) {
            throw new \Exception(__('Errore nel salvataggio delle impostazioni di monitoring.', 'fp-performance-suite'), 0, $e);
        }
    }

    /**
     * Salva le impostazioni Core Web Vitals
     */
    private function saveCoreWebVitalsSettings(): void
    {
        try {
            $cwvMonitor = $this->container->get(CoreWebVitalsMonitor::class);
            $currentSettings = $cwvMonitor->settings();
            
            $cwvInput = $this->sanitizeInput('cwv', 'array') ?? [];
            // Prepara i dati con valori di default
            $cwvData = [
                'enabled' => $cwvInput['enabled'] ?? false,
                'sample_rate' => isset($cwvInput['sample_rate']) 
                    ? (float)$cwvInput['sample_rate'] / 100 
                    : ($currentSettings['sample_rate'] ?? 0.1),
                'track_lcp' => $cwvInput['track_lcp'] ?? false,
                'track_fid' => $cwvInput['track_fid'] ?? false,
                'track_cls' => $cwvInput['track_cls'] ?? false,
                'track_fcp' => $cwvInput['track_fcp'] ?? false,
                'track_ttfb' => $cwvInput['track_ttfb'] ?? false,
                'track_inp' => $cwvInput['track_inp'] ?? false,
                'send_to_analytics' => $cwvInput['send_to_analytics'] ?? false,
                'retention_days' => isset($cwvInput['retention_days']) 
                    ? (int)$cwvInput['retention_days'] 
                    : ($currentSettings['retention_days'] ?? 30),
                'alert_email' => isset($cwvInput['alert_email']) 
                    ? $this->sanitizeInput('cwv', 'array')['alert_email'] 
                    : ($currentSettings['alert_email'] ?? get_option('admin_email')),
                'alert_threshold_lcp' => isset($cwvInput['alert_threshold_lcp']) 
                    ? (int)$cwvInput['alert_threshold_lcp'] 
                    : ($currentSettings['alert_threshold_lcp'] ?? 4000),
                'alert_threshold_fid' => isset($cwvInput['alert_threshold_fid']) 
                    ? (int)$cwvInput['alert_threshold_fid'] 
                    : ($currentSettings['alert_threshold_fid'] ?? 300),
                'alert_threshold_cls' => isset($cwvInput['alert_threshold_cls']) 
                    ? (float)$cwvInput['alert_threshold_cls'] 
                    : ($currentSettings['alert_threshold_cls'] ?? 0.25),
            ];
            
            $cwvMonitor->update($cwvData);
        } catch (\Throwable $e) {
            throw new \Exception(__('Errore nel salvataggio delle impostazioni Core Web Vitals.', 'fp-performance-suite'), 0, $e);
        }
    }

    /**
     * Salva le impostazioni dei report schedulati
     */
    private function saveReportsSettings(): void
    {
        try {
            $reports = new ScheduledReports();
            $currentReports = $reports->settings();
            
            $reportsInput = $this->sanitizeInput('reports', 'array') ?? [];
            // Merge con i valori correnti
            $reportsSettings = array_merge($currentReports, [
                'enabled' => $reportsInput['enabled'] ?? false,
                'frequency' => $reportsInput['frequency'] ?? ($currentReports['frequency'] ?? 'weekly'),
                'recipient' => isset($reportsInput['recipient']) 
                    ? $this->sanitizeInput('reports', 'array')['recipient'] 
                    : ($currentReports['recipient'] ?? get_option('admin_email')),
            ]);
            
            $reports->update($reportsSettings);
        } catch (\Throwable $e) {
            throw new \Exception(__('Errore nel salvataggio delle impostazioni dei report.', 'fp-performance-suite'), 0, $e);
        }
    }

    /**
     * Salva le impostazioni dei Webhook
     */
    private function saveWebhookSettings(): void
    {
        try {
            $webhooksInput = $this->sanitizeInput('webhooks', 'array') ?? [];
            // Sanitizza gli eventi selezionati
            $events = [];
            if (isset($webhooksInput['events']) && is_array($webhooksInput['events'])) {
                $events = array_map('sanitize_text_field', $webhooksInput['events']);
            }
            
            $webhookData = [
                'enabled' => $webhooksInput['enabled'] ?? false,
                'url' => isset($webhooksInput['url']) 
                    ? esc_url_raw($webhooksInput['url']) 
                    : '',
                'secret' => $webhooksInput['secret'] ?? '',
                'events' => $events,
                'retry_failed' => $webhooksInput['retry_failed'] ?? false,
                'max_retries' => isset($webhooksInput['max_retries']) 
                    ? (int)$webhooksInput['max_retries'] 
                    : 3,
            ];
            
            update_option('fp_ps_webhooks', $webhookData);
        } catch (\Throwable $e) {
            throw new \Exception(__('Errore nel salvataggio delle impostazioni dei webhook.', 'fp-performance-suite'), 0, $e);
        }
    }

    /**
     * Salva le impostazioni del Performance Budget
     */
    private function savePerformanceBudgetSettings(): void
    {
        try {
            $budgetInput = $this->sanitizeInput('perf_budget', 'array') ?? [];
            $budgetData = [
                'enabled' => $budgetInput['enabled'] ?? false,
                'score_threshold' => isset($budgetInput['score_threshold']) 
                    ? (int)$budgetInput['score_threshold'] 
                    : 80,
                'load_time_threshold' => isset($budgetInput['load_time_threshold']) 
                    ? (int)$budgetInput['load_time_threshold'] 
                    : 3000,
                'fcp_threshold' => isset($budgetInput['fcp_threshold']) 
                    ? (int)$budgetInput['fcp_threshold'] 
                    : 1800,
                'lcp_threshold' => isset($budgetInput['lcp_threshold']) 
                    ? (int)$budgetInput['lcp_threshold'] 
                    : 2500,
                'cls_threshold' => isset($budgetInput['cls_threshold']) 
                    ? (float)$budgetInput['cls_threshold'] 
                    : 0.1,
                'alert_email' => isset($budgetInput['alert_email']) 
                    ? $this->sanitizeInput('perf_budget', 'array')['alert_email'] 
                    : get_option('admin_email'),
                'alert_on_exceed' => $budgetInput['alert_on_exceed'] ?? false,
            ];
            
            update_option('fp_ps_performance_budget', $budgetData);
        } catch (\Throwable $e) {
            throw new \Exception(__('Errore nel salvataggio delle impostazioni del Performance Budget.', 'fp-performance-suite'), 0, $e);
        }
    }
}
















