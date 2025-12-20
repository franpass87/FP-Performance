<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Sections;

use FP\PerfSuite\Utils\ErrorHandler;

use FP\PerfSuite\Services\Reports\ScheduledReports;

use function esc_attr;
use function esc_html_e;
use function checked;
use function selected;
use function error_log;

/**
 * Renderizza la sezione Scheduled Reports
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Sections
 * @author Francesco Passeri
 */
class ReportsSection
{
    /**
     * Renderizza la sezione
     */
    public function render(): string
    {
        try {
            $reports = new ScheduledReports();
            $settings = $reports->settings();
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Error loading ScheduledReports');
            return $this->renderError('Scheduled Reports', $e->getMessage());
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
















