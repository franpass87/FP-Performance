<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor;
use FP\PerfSuite\Services\Reports\ScheduledReports;

use function __;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url_raw;
use function get_option;
use function update_option;
use function sanitize_text_field;
use function sanitize_email;
use function checked;
use function selected;
use function wp_nonce_field;
use function wp_verify_nonce;
use function wp_unslash;
use function current_user_can;
use function wp_safe_redirect;
use function admin_url;
use function add_query_arg;
use function is_array;
use function in_array;
use function array_map;

/**
 * Monitoring & Reports Page
 * 
 * Gestisce tutto il monitoraggio e reporting delle performance:
 * - Performance Monitoring
 * - Core Web Vitals Monitor
 * - Scheduled Reports
 * - Webhook Integration
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class MonitoringReports extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-monitoring';
    }

    public function title(): string
    {
        return __('Monitoring & Reports', 'fp-performance-suite');
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
            'breadcrumbs' => [
                __('Monitoraggio', 'fp-performance-suite'),
                __('Monitoring & Reports', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        // Check for success message
        $success_message = '';
        if (isset($_GET['updated']) && $_GET['updated'] === '1') {
            $success_message = __('Impostazioni monitoraggio salvate con successo!', 'fp-performance-suite');
        }

        // Check for error message
        $error_message = '';
        if (isset($_GET['error']) && $_GET['error'] === '1') {
            $error_message = isset($_GET['message']) 
                ? urldecode($_GET['message']) 
                : __('Si è verificato un errore durante il salvataggio.', 'fp-performance-suite');
        }

        ob_start();
        ?>
        
        <?php if ($success_message) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($success_message); ?></p></div>
        <?php endif; ?>
        
        <?php if ($error_message) : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error_message); ?></p></div>
        <?php endif; ?>
        
        <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">
            <p style="margin: 0;">
                <strong>📊 <?php esc_html_e('Monitoraggio e Report', 'fp-performance-suite'); ?></strong><br>
                <?php esc_html_e('Configura il monitoraggio delle performance, Core Web Vitals, report schedulati e integrazioni webhook per tenere sotto controllo la velocità del tuo sito.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('fp_ps_monitoring', '_wpnonce'); ?>
            <input type="hidden" name="action" value="fp_ps_save_monitoring">
            
            <!-- Performance Monitoring Section -->
            <?php echo $this->renderMonitoringSection(); ?>
            
            <!-- Core Web Vitals Monitor Section -->
            <?php echo $this->renderCoreWebVitalsSection(); ?>
            
            <!-- Scheduled Reports Section -->
            <?php echo $this->renderReportsSection(); ?>
            
            <!-- Webhook Integration Section -->
            <?php echo $this->renderWebhookSection(); ?>
            
            <!-- Save Button -->
            <div class="fp-ps-card">
                <div class="fp-ps-actions">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Salva Impostazioni Monitoraggio', 'fp-performance-suite'); ?>
                    </button>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e('Salva tutte le modifiche apportate alle impostazioni di monitoraggio, Core Web Vitals, report e webhook.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </form>
        
        <?php
        return ob_get_clean();
    }

    private function renderMonitoringSection(): string
    {
        try {
            $monitor = PerformanceMonitor::instance();
            $settings = $monitor->settings();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading PerformanceMonitor: ' . $e->getMessage());
            return $this->renderErrorSection('Performance Monitoring', $e->getMessage());
        }

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📊 <?php esc_html_e('Performance Monitoring', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Traccia i tempi di caricamento delle pagine, le query database e l\'uso della memoria nel tempo per identificare bottleneck e ottimizzazioni.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="monitoring_enabled"><?php esc_html_e('Abilita Monitoring', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="monitoring[enabled]" id="monitoring_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Traccia le metriche di performance', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Raccoglie dati su tempi di caricamento, query DB e memoria utilizzata.', 'fp-performance-suite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sample_rate"><?php esc_html_e('Sample Rate', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="monitoring[sample_rate]" id="sample_rate" value="<?php echo esc_attr($settings['sample_rate']); ?>" min="1" max="100" class="small-text">
                        <span>%</span>
                        <p class="description"><?php esc_html_e('Percentuale di richieste da monitorare (valore più basso = meno overhead)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Dati Raccolti:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('Tempo di generazione pagina (TTFB)', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Numero e tempo di esecuzione query database', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Utilizzo memoria PHP', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('URL più lente e problematiche', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderCoreWebVitalsSection(): string
    {
        try {
            $cwvMonitor = $this->container->get(CoreWebVitalsMonitor::class);
            $settings = $cwvMonitor->settings();
            $status = $cwvMonitor->status();
            $summary = $cwvMonitor->getSummary(7);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading CoreWebVitalsMonitor: ' . $e->getMessage());
            return $this->renderErrorSection('Core Web Vitals Monitor', $e->getMessage());
        }

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📊 <?php esc_html_e('Core Web Vitals Monitor', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">Real User Monitoring</span></h2>
            <p><?php esc_html_e('Monitora in tempo reale i Core Web Vitals (LCP, FID, CLS) degli utenti reali per ottimizzare le performance e il ranking SEO di Google.', 'fp-performance-suite'); ?></p>
            
            <?php if ($status['enabled']): ?>
                <div class="notice notice-success inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong>
                        <?php printf(
                            esc_html__('Attivo - %d metriche raccolte (Sample rate: %.0f%%)', 'fp-performance-suite'),
                            $status['metrics_count'],
                            $settings['sample_rate'] * 100
                        ); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($summary)): ?>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <h4 style="margin-top: 0;"><?php esc_html_e('Metriche Ultimi 7 Giorni', 'fp-performance-suite'); ?></h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <?php foreach ($summary as $metricName => $data): ?>
                    <div style="background: white; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <div style="font-size: 12px; color: #666; margin-bottom: 5px;"><?php echo esc_html($metricName); ?></div>
                        <div style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">
                            <?php 
                            if ($metricName === 'CLS') {
                                echo number_format($data['p75'], 3);
                            } else {
                                echo number_format($data['p75']);
                            }
                            ?>
                        </div>
                        <div style="font-size: 11px; color: #999;">
                            <?php printf(
                                esc_html__('p75 • %d campioni', 'fp-performance-suite'),
                                $data['count']
                            ); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong><?php esc_html_e('Abilita Core Web Vitals Monitoring', 'fp-performance-suite'); ?></strong>
                    <span class="fp-ps-risk-indicator green">
                        <div class="fp-ps-risk-tooltip green">
                            <div class="fp-ps-risk-tooltip-title">
                                <span class="icon">✓</span>
                                <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Monitora in tempo reale LCP, FID, CLS e altre metriche degli utenti reali (RUM).', 'fp-performance-suite'); ?></div>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Dati reali sulle performance percepite, identificazione problemi specifici, ottimizzazione basata su dati reali.', 'fp-performance-suite'); ?></div>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Altamente consigliato: Essenziale per monitorare le performance reali e il ranking Google. Impact minimo sulle performance.', 'fp-performance-suite'); ?></div>
                            </div>
                        </div>
                    </span>
                    <small><?php esc_html_e('Raccoglie metriche reali dai browser degli utenti per avere dati accurati sulle performance percepite.', 'fp-performance-suite'); ?></small>
                </span>
                <input type="checkbox" name="cwv[enabled]" id="cwv_enabled" value="1" <?php checked($settings['enabled']); ?> />
            </label>
            
            <table class="form-table" style="margin-top: 20px;">
                <tr>
                    <th scope="row">
                        <label for="cwv_sample_rate"><?php esc_html_e('Sample Rate', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[sample_rate]" id="cwv_sample_rate" value="<?php echo esc_attr($settings['sample_rate'] * 100); ?>" min="1" max="100" class="small-text">
                        <span>%</span>
                        <p class="description"><?php esc_html_e('Percentuale di utenti da monitorare (100% = tutti gli utenti)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Metriche da Tracciare', 'fp-performance-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="cwv[track_lcp]" value="1" <?php checked($settings['track_lcp']); ?>>
                                <?php esc_html_e('LCP - Largest Contentful Paint', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_fid]" value="1" <?php checked($settings['track_fid']); ?>>
                                <?php esc_html_e('FID - First Input Delay', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_cls]" value="1" <?php checked($settings['track_cls']); ?>>
                                <?php esc_html_e('CLS - Cumulative Layout Shift', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_fcp]" value="1" <?php checked($settings['track_fcp']); ?>>
                                <?php esc_html_e('FCP - First Contentful Paint', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_ttfb]" value="1" <?php checked($settings['track_ttfb']); ?>>
                                <?php esc_html_e('TTFB - Time to First Byte', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_inp]" value="1" <?php checked($settings['track_inp']); ?>>
                                <?php esc_html_e('INP - Interaction to Next Paint (sperimentale)', 'fp-performance-suite'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_retention_days"><?php esc_html_e('Conservazione Dati', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[retention_days]" id="cwv_retention_days" value="<?php echo esc_attr($settings['retention_days']); ?>" min="1" max="365" class="small-text">
                        <span><?php esc_html_e('giorni', 'fp-performance-suite'); ?></span>
                        <p class="description"><?php esc_html_e('Per quanto tempo conservare le metriche raccolte', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_send_analytics"><?php esc_html_e('Integrazione Analytics', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="cwv[send_to_analytics]" id="cwv_send_analytics" value="1" <?php checked($settings['send_to_analytics']); ?>>
                            <?php esc_html_e('Invia metriche a Google Analytics (se disponibile)', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Le metriche verranno inviate come eventi personalizzati a GA4 se configurato', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Soglie di Allerta', 'fp-performance-suite'); ?></h3>
            <p class="description"><?php esc_html_e('Ricevi notifiche via email quando le metriche superano queste soglie (valori "poor"):', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_email"><?php esc_html_e('Email per Allerte', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="cwv[alert_email]" id="cwv_alert_email" value="<?php echo esc_attr($settings['alert_email']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_lcp"><?php esc_html_e('Soglia LCP (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[alert_threshold_lcp]" id="cwv_alert_lcp" value="<?php echo esc_attr($settings['alert_threshold_lcp']); ?>" min="1000" max="10000" step="100" class="small-text">
                        <span class="description"><?php esc_html_e('Default: 2500ms (good), 4000ms (poor)', 'fp-performance-suite'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_fid"><?php esc_html_e('Soglia FID (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[alert_threshold_fid]" id="cwv_alert_fid" value="<?php echo esc_attr($settings['alert_threshold_fid']); ?>" min="50" max="1000" step="10" class="small-text">
                        <span class="description"><?php esc_html_e('Default: 100ms (good), 300ms (poor)', 'fp-performance-suite'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_cls"><?php esc_html_e('Soglia CLS', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[alert_threshold_cls]" id="cwv_alert_cls" value="<?php echo esc_attr($settings['alert_threshold_cls']); ?>" min="0.05" max="1" step="0.05" class="small-text">
                        <span class="description"><?php esc_html_e('Default: 0.1 (good), 0.25 (poor)', 'fp-performance-suite'); ?></span>
                    </td>
                </tr>
            </table>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Perché è importante:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('I Core Web Vitals sono un fattore di ranking di Google per la SEO', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('I dati RUM (Real User Monitoring) sono più accurati dei test sintetici', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Aiuta a identificare problemi di performance che colpiscono gli utenti reali', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Monitora l\'impatto delle ottimizzazioni in tempo reale', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderReportsSection(): string
    {
        try {
            $reports = new ScheduledReports();
            $settings = $reports->settings();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading ScheduledReports: ' . $e->getMessage());
            return $this->renderErrorSection('Scheduled Reports', $e->getMessage());
        }

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📧 <?php esc_html_e('Report Schedulati', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Ricevi periodicamente report completi sulle performance via email per tenere sotto controllo lo stato del tuo sito senza doverti collegare all\'admin.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="reports_enabled"><?php esc_html_e('Abilita Report', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="reports[enabled]" id="reports_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Invia report di performance schedulati', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="reports_frequency"><?php esc_html_e('Frequenza', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <select name="reports[frequency]" id="reports_frequency">
                            <option value="daily" <?php selected($settings['frequency'], 'daily'); ?>><?php esc_html_e('Giornaliero', 'fp-performance-suite'); ?></option>
                            <option value="weekly" <?php selected($settings['frequency'], 'weekly'); ?>><?php esc_html_e('Settimanale', 'fp-performance-suite'); ?></option>
                            <option value="monthly" <?php selected($settings['frequency'], 'monthly'); ?>><?php esc_html_e('Mensile', 'fp-performance-suite'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Frequenza di invio dei report via email', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="reports_recipient"><?php esc_html_e('Destinatario', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="reports[recipient]" id="reports_recipient" value="<?php echo esc_attr($settings['recipient']); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Indirizzo email dove ricevere i report', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('📊 Contenuto Report:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('Performance Score e trend', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Core Web Vitals (LCP, FID, CLS)', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Statistiche cache e ottimizzazioni attive', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Avvisi e problemi rilevati', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Raccomandazioni per migliorare', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderWebhookSection(): string
    {
        $webhooks = get_option('fp_ps_webhooks', [
            'enabled' => false,
            'url' => '',
            'secret' => '',
            'events' => [],
            'retry_failed' => true,
            'max_retries' => 3,
        ]);

        $availableEvents = [
            'cache_cleared' => __('Cache Pulita', 'fp-performance-suite'),
            'db_cleaned' => __('Database Pulito', 'fp-performance-suite'),
            'webp_converted' => __('Conversione WebP', 'fp-performance-suite'),
            'preset_applied' => __('Preset Applicato', 'fp-performance-suite'),
            'budget_exceeded' => __('Performance Budget Superato', 'fp-performance-suite'),
            'optimization_error' => __('Errore Ottimizzazione', 'fp-performance-suite'),
        ];

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>🔗 <?php esc_html_e('Integrazione Webhook', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Invia notifiche in tempo reale a servizi esterni quando si verificano eventi specifici. Perfetto per dashboard di monitoring, Slack, Discord o integrazioni personalizzate.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="webhook_enabled"><?php esc_html_e('Abilita Webhooks', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="webhooks[enabled]" id="webhook_enabled" value="1" <?php checked($webhooks['enabled']); ?>>
                            <?php esc_html_e('Invia notifiche webhook per gli eventi selezionati', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="webhook_url"><?php esc_html_e('URL Webhook', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="webhooks[url]" id="webhook_url" value="<?php echo esc_attr($webhooks['url']); ?>" class="large-text" placeholder="https://hooks.example.com/webhook">
                        <p class="description"><?php esc_html_e('URL completo dove verranno inviate le richieste POST', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="webhook_secret"><?php esc_html_e('Chiave Segreta (Opzionale)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="webhooks[secret]" id="webhook_secret" value="<?php echo esc_attr($webhooks['secret']); ?>" class="large-text" placeholder="optional-secret-key">
                        <p class="description"><?php esc_html_e('Verrà inviata come header X-FP-Signature per la verifica', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Eventi da Monitorare', 'fp-performance-suite'); ?></th>
                    <td>
                        <fieldset>
                            <?php foreach ($availableEvents as $event => $label) : ?>
                                <label style="display: block; margin-bottom: 8px;">
                                    <input type="checkbox" name="webhooks[events][]" value="<?php echo esc_attr($event); ?>" <?php checked(in_array($event, $webhooks['events'], true)); ?>>
                                    <?php echo esc_html($label); ?>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php esc_html_e('Seleziona quali eventi devono triggerare notifiche webhook', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="retry_failed"><?php esc_html_e('Riprova Richieste Fallite', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="webhooks[retry_failed]" id="retry_failed" value="1" <?php checked($webhooks['retry_failed']); ?>>
                            <?php esc_html_e('Riprova automaticamente le richieste webhook fallite', 'fp-performance-suite'); ?>
                        </label>
                        <br>
                        <label style="margin-top: 10px; display: inline-block;">
                            <?php esc_html_e('Max tentativi:', 'fp-performance-suite'); ?>
                            <input type="number" name="webhooks[max_retries]" value="<?php echo esc_attr($webhooks['max_retries']); ?>" min="1" max="10" style="width: 60px;">
                        </label>
                    </td>
                </tr>
            </table>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0 0 10px 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('📡 Formato Payload Webhook:', 'fp-performance-suite'); ?></p>
                <pre style="background: #fff; padding: 10px; border-radius: 3px; overflow-x: auto; font-size: 12px;"><code>{
  "event": "cache_cleared",
  "timestamp": "2024-01-15T10:30:00Z",
  "site_url": "<?php echo esc_html(home_url()); ?>",
  "data": {
    "files_deleted": 1234,
    "size_freed": "45.6 MB"
  }
}</code></pre>
            </div>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('💡 Integrazioni Popolari:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #856404;">
                    <li><strong>Slack:</strong> <?php esc_html_e('Usa l\'app Incoming Webhooks', 'fp-performance-suite'); ?></li>
                    <li><strong>Discord:</strong> <?php esc_html_e('Crea webhook nelle impostazioni del canale', 'fp-performance-suite'); ?></li>
                    <li><strong>Zapier:</strong> <?php esc_html_e('Trigger Zaps da webhooks', 'fp-performance-suite'); ?></li>
                    <li><strong>Dashboard Custom:</strong> <?php esc_html_e('Crea monitoring in tempo reale', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render error section when a service fails to load
     */
    private function renderErrorSection(string $sectionName, string $errorMessage): string
    {
        ob_start();
        ?>
        <div class="fp-ps-card" style="border-left: 4px solid #d63638;">
            <h2>⚠️ <?php echo esc_html($sectionName); ?></h2>
            <div class="notice notice-error inline" style="margin: 0;">
                <p>
                    <strong><?php esc_html_e('Errore:', 'fp-performance-suite'); ?></strong>
                    <?php esc_html_e('Impossibile caricare questa sezione. Controlla i log per maggiori dettagli.', 'fp-performance-suite'); ?>
                </p>
                <details>
                    <summary style="cursor: pointer;"><?php esc_html_e('Dettagli tecnici', 'fp-performance-suite'); ?></summary>
                    <pre style="background: #f0f0f1; padding: 10px; margin-top: 10px; overflow: auto;"><?php echo esc_html($errorMessage); ?></pre>
                </details>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        // Verifica permessi utente
        if (!current_user_can($this->capability())) {
            $this->redirectWithError(__('Permesso negato. Non hai i permessi necessari per salvare queste impostazioni.', 'fp-performance-suite'));
            return;
        }

        // Verifica nonce di sicurezza
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'fp_ps_monitoring')) {
            $this->redirectWithError(__('Errore di sicurezza: nonce non valido o scaduto. Riprova a ricaricare la pagina.', 'fp-performance-suite'));
            return;
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

            // Redirect con successo
            $redirect_url = add_query_arg('updated', '1', admin_url('admin.php?page=' . $this->slug()));
            wp_safe_redirect($redirect_url);
            exit;

        } catch (\Throwable $e) {
            // Log dell'errore
            error_log(sprintf(
                '[FP Performance Suite] Errore durante il salvataggio monitoring: %s in %s:%d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            
            // Redirect con errore
            $this->redirectWithError($e->getMessage());
        }
    }

    /**
     * Redirect con messaggio di errore
     */
    private function redirectWithError(string $message): void
    {
        $redirect_url = add_query_arg(
            ['error' => '1', 'message' => urlencode($message)],
            admin_url('admin.php?page=' . $this->slug())
        );
        wp_safe_redirect($redirect_url);
        exit;
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
                'enabled' => isset($_POST['monitoring']['enabled']),
                'sample_rate' => isset($_POST['monitoring']['sample_rate']) 
                    ? (int)$_POST['monitoring']['sample_rate'] 
                    : ($currentMonitoring['sample_rate'] ?? 10),
            ]);
            
            $monitor->update($monitoringSettings);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio Monitoring: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni di monitoring.', 'fp-performance-suite'));
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
            
            // Prepara i dati con valori di default
            $cwvData = [
                'enabled' => isset($_POST['cwv']['enabled']),
                'sample_rate' => isset($_POST['cwv']['sample_rate']) 
                    ? (float)$_POST['cwv']['sample_rate'] / 100 
                    : ($currentSettings['sample_rate'] ?? 0.1),
                'track_lcp' => isset($_POST['cwv']['track_lcp']),
                'track_fid' => isset($_POST['cwv']['track_fid']),
                'track_cls' => isset($_POST['cwv']['track_cls']),
                'track_fcp' => isset($_POST['cwv']['track_fcp']),
                'track_ttfb' => isset($_POST['cwv']['track_ttfb']),
                'track_inp' => isset($_POST['cwv']['track_inp']),
                'send_to_analytics' => isset($_POST['cwv']['send_to_analytics']),
                'retention_days' => isset($_POST['cwv']['retention_days']) 
                    ? (int)$_POST['cwv']['retention_days'] 
                    : ($currentSettings['retention_days'] ?? 30),
                'alert_email' => isset($_POST['cwv']['alert_email']) 
                    ? sanitize_email($_POST['cwv']['alert_email']) 
                    : ($currentSettings['alert_email'] ?? get_option('admin_email')),
                'alert_threshold_lcp' => isset($_POST['cwv']['alert_threshold_lcp']) 
                    ? (int)$_POST['cwv']['alert_threshold_lcp'] 
                    : ($currentSettings['alert_threshold_lcp'] ?? 4000),
                'alert_threshold_fid' => isset($_POST['cwv']['alert_threshold_fid']) 
                    ? (int)$_POST['cwv']['alert_threshold_fid'] 
                    : ($currentSettings['alert_threshold_fid'] ?? 300),
                'alert_threshold_cls' => isset($_POST['cwv']['alert_threshold_cls']) 
                    ? (float)$_POST['cwv']['alert_threshold_cls'] 
                    : ($currentSettings['alert_threshold_cls'] ?? 0.25),
            ];
            
            $cwvMonitor->update($cwvData);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio Core Web Vitals: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni Core Web Vitals.', 'fp-performance-suite'));
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
            
            // Merge con i valori correnti
            $reportsSettings = array_merge($currentReports, [
                'enabled' => isset($_POST['reports']['enabled']),
                'frequency' => sanitize_text_field($_POST['reports']['frequency'] ?? ($currentReports['frequency'] ?? 'weekly')),
                'recipient' => isset($_POST['reports']['recipient']) 
                    ? sanitize_email($_POST['reports']['recipient']) 
                    : ($currentReports['recipient'] ?? get_option('admin_email')),
            ]);
            
            $reports->update($reportsSettings);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio Reports: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni dei report.', 'fp-performance-suite'));
        }
    }

    /**
     * Salva le impostazioni dei Webhook
     */
    private function saveWebhookSettings(): void
    {
        try {
            // Sanitizza gli eventi selezionati
            $events = [];
            if (isset($_POST['webhooks']['events']) && is_array($_POST['webhooks']['events'])) {
                $events = array_map('sanitize_text_field', $_POST['webhooks']['events']);
            }
            
            $webhookData = [
                'enabled' => isset($_POST['webhooks']['enabled']),
                'url' => isset($_POST['webhooks']['url']) 
                    ? esc_url_raw($_POST['webhooks']['url']) 
                    : '',
                'secret' => isset($_POST['webhooks']['secret']) 
                    ? sanitize_text_field($_POST['webhooks']['secret']) 
                    : '',
                'events' => $events,
                'retry_failed' => isset($_POST['webhooks']['retry_failed']),
                'max_retries' => isset($_POST['webhooks']['max_retries']) 
                    ? (int)$_POST['webhooks']['max_retries'] 
                    : 3,
            ];
            
            update_option('fp_ps_webhooks', $webhookData);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio Webhook: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni dei webhook.', 'fp-performance-suite'));
        }
    }
}

