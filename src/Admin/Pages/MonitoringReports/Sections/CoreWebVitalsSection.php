<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Sections;

use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\Admin\RiskMatrix;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor;

use function esc_attr;
use function esc_html;
use function esc_html_e;
use function checked;
use function number_format;
use function in_array;
use function error_log;

/**
 * Renderizza la sezione Core Web Vitals Monitor
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Sections
 * @author Francesco Passeri
 */
class CoreWebVitalsSection
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Renderizza la sezione
     */
    public function render(): string
    {
        try {
            $cwvMonitor = $this->container->get(CoreWebVitalsMonitor::class);
            $settings = $cwvMonitor->settings();
            $status = $cwvMonitor->status();
            $summary = $cwvMonitor->getSummary(7);
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Error loading CoreWebVitalsMonitor');
            return $this->renderError('Core Web Vitals Monitor', $e->getMessage());
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
            <div class="fp-ps-mb-lg">
                <h4 style="margin-top: 0;"><?php esc_html_e('Metriche Ultimi 7 Giorni', 'fp-performance-suite'); ?></h4>
                <section class="fp-ps-grid three">
                    <?php foreach ($summary as $metricName => $data): 
                        // Determina il rating in base al metricName e p75
                        $rating = 'success';
                        if ($metricName === 'LCP') {
                            $rating = $data['p75'] < 2500 ? 'success' : ($data['p75'] < 4000 ? 'warning' : 'danger');
                        } elseif ($metricName === 'FID') {
                            $rating = $data['p75'] < 100 ? 'success' : ($data['p75'] < 300 ? 'warning' : 'danger');
                        } elseif ($metricName === 'CLS') {
                            $rating = $data['p75'] < 0.1 ? 'success' : ($data['p75'] < 0.25 ? 'warning' : 'danger');
                        } elseif ($metricName === 'FCP') {
                            $rating = $data['p75'] < 1800 ? 'success' : ($data['p75'] < 3000 ? 'warning' : 'danger');
                        } elseif ($metricName === 'TTFB') {
                            $rating = $data['p75'] < 800 ? 'success' : ($data['p75'] < 1800 ? 'warning' : 'danger');
                        }
                    ?>
                    <div class="fp-ps-stat-box">
                        <div class="stat-value <?php echo esc_attr($rating); ?>">
                            <?php 
                            if ($metricName === 'CLS') {
                                echo number_format($data['p75'], 3);
                            } else {
                                echo number_format($data['p75']);
                            }
                            if (in_array($metricName, ['LCP', 'FID', 'FCP', 'TTFB'])) {
                                echo '<span class="fp-ps-text-md">ms</span>';
                            }
                            ?>
                        </div>
                        <div class="stat-label"><?php echo esc_html($metricName); ?></div>
                        <p class="description fp-ps-mt-sm">
                            <?php printf(
                                esc_html__('p75 • %s campioni', 'fp-performance-suite'),
                                number_format($data['count'])
                            ); ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </section>
            </div>
            <?php endif; ?>
            
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong><?php esc_html_e('Abilita Core Web Vitals Monitoring', 'fp-performance-suite'); ?></strong>
                    <?php echo RiskMatrix::renderIndicator('core_web_vitals_monitoring'); ?>
                    <small><?php esc_html_e('Raccoglie metriche reali dai browser degli utenti per avere dati accurati sulle performance percepite.', 'fp-performance-suite'); ?></small>
                </span>
                <input type="checkbox" name="cwv[enabled]" id="cwv_enabled" value="1" <?php checked($settings['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('core_web_vitals_monitoring')); ?>" />
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

    /**
     * Renderizza messaggio di errore
     */
    private function renderError(string $sectionName, string $errorMessage): string
    {
        ob_start();
        ?>
        <div class="notice notice-error">
            <p><strong><?php esc_html_e('Errore:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($sectionName); ?></p>
            <p><?php echo esc_html($errorMessage); ?></p>
        </div>
        <?php
        return ob_get_clean();
    }
}
















