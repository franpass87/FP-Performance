<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Sections;

use FP\PerfSuite\Utils\ErrorHandler;

use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;

use function esc_attr;
use function esc_html_e;
use function checked;
use function error_log;

/**
 * Renderizza la sezione Performance Monitoring
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Sections
 * @author Francesco Passeri
 */
class MonitoringSection
{
    /**
     * Renderizza la sezione
     */
    public function render(): string
    {
        try {
            $monitor = PerformanceMonitor::instance();
            $settings = $monitor->settings();
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Error loading PerformanceMonitor');
            return $this->renderError('Performance Monitoring', $e->getMessage());
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
















