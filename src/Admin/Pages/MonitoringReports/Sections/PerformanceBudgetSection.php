<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Sections;

use FP\PerfSuite\Admin\RiskMatrix;

use function esc_attr;
use function esc_html_e;
use function checked;
use function get_option;

/**
 * Renderizza la sezione Performance Budget
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Sections
 * @author Francesco Passeri
 */
class PerformanceBudgetSection
{
    /**
     * Renderizza la sezione
     */
    public function render(): string
    {
        $budget = get_option('fp_ps_performance_budget', [
            'enabled' => false,
            'score_threshold' => 80,
            'load_time_threshold' => 3000,
            'fcp_threshold' => 1800,
            'lcp_threshold' => 2500,
            'cls_threshold' => 0.1,
            'alert_email' => get_option('admin_email'),
            'alert_on_exceed' => true,
        ]);

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📊 <?php esc_html_e('Performance Budget', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Imposta soglie di performance e ricevi avvisi quando vengono superate. Aiuta a mantenere il sito veloce nel tempo prevenendo regressioni di performance.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="perf_budget_enabled">
                            <?php esc_html_e('Abilita Performance Budget', 'fp-performance-suite'); ?>
                            <?php echo RiskMatrix::renderIndicator('performance_budget_enabled'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="perf_budget[enabled]" id="perf_budget_enabled" value="1" <?php checked($budget['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('performance_budget_enabled')); ?>">
                            <?php esc_html_e('Monitora e avvisa quando le soglie vengono superate', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Soglie Performance', 'fp-performance-suite'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="score_threshold"><?php esc_html_e('Performance Score Minimo', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[score_threshold]" id="score_threshold" value="<?php echo esc_attr($budget['score_threshold']); ?>" min="0" max="100" class="small-text">
                        <span>/100</span>
                        <p class="description"><?php esc_html_e('Score minimo accettabile. Avviso se scende sotto questa soglia.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="load_time_threshold"><?php esc_html_e('Load Time Massimo (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[load_time_threshold]" id="load_time_threshold" value="<?php echo esc_attr($budget['load_time_threshold']); ?>" min="500" max="10000" step="100" class="small-text">
                        <span>ms</span>
                        <p class="description"><?php esc_html_e('Tempo massimo di caricamento accettabile.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="fcp_threshold"><?php esc_html_e('FCP Threshold (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[fcp_threshold]" id="fcp_threshold" value="<?php echo esc_attr($budget['fcp_threshold']); ?>" min="500" max="5000" step="100" class="small-text">
                        <span>ms</span>
                        <p class="description"><?php esc_html_e('First Contentful Paint - Consigliato: < 1800ms', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lcp_threshold"><?php esc_html_e('LCP Threshold (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[lcp_threshold]" id="lcp_threshold" value="<?php echo esc_attr($budget['lcp_threshold']); ?>" min="1000" max="10000" step="100" class="small-text">
                        <span>ms</span>
                        <p class="description"><?php esc_html_e('Largest Contentful Paint - Consigliato: < 2500ms', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cls_threshold"><?php esc_html_e('CLS Threshold', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[cls_threshold]" id="cls_threshold" value="<?php echo esc_attr($budget['cls_threshold']); ?>" min="0" max="1" step="0.01" class="small-text">
                        <p class="description"><?php esc_html_e('Cumulative Layout Shift - Consigliato: < 0.1', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Avvisi', 'fp-performance-suite'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="alert_on_exceed">
                            <?php esc_html_e('Invia avvisi via email', 'fp-performance-suite'); ?>
                            <?php echo RiskMatrix::renderIndicator('performance_budget_alert_on_exceed'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="perf_budget[alert_on_exceed]" id="alert_on_exceed" value="1" <?php checked($budget['alert_on_exceed']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('performance_budget_alert_on_exceed')); ?>">
                            <?php esc_html_e('Invia email quando le soglie vengono superate', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="alert_email"><?php esc_html_e('Email per avvisi', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="perf_budget[alert_email]" id="alert_email" value="<?php echo esc_attr($budget['alert_email']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('💡 Cos\'è un Performance Budget?', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #856404;">
                    <li><?php esc_html_e('Definisce limiti chiari per le metriche di performance del sito', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Previene degradi delle performance nel tempo (performance regression)', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Avvisa quando nuove modifiche peggiorano le performance', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Best practice per team di sviluppo e siti enterprise', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
















