<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs;

use function esc_html_e;
use function esc_url;
use function admin_url;

/**
 * Renderizza la tab Logs
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs
 * @author Francesco Passeri
 */
class LogsTab
{
    /**
     * Renderizza la tab
     */
    public function render(): string
    {
        ob_start();
        ?>
        <div class="fp-ps-tab-description" style="background: #fef9c3; border-left: 4px solid #eab308; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <p style="margin: 0; color: #713f12;">
                <strong>📝 Logs & Debug:</strong> 
                <?php esc_html_e('Visualizza e gestisci i log di debug WordPress per troubleshooting e analisi errori.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <div class="fp-ps-card">
            <h3>📝 <?php esc_html_e('Gestione Logs', 'fp-performance-suite'); ?></h3>
            <p><?php esc_html_e('Accedi alle funzionalità complete di gestione logs dalla pagina dedicata:', 'fp-performance-suite'); ?></p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                <div style="background: #f3f4f6; padding: 20px; border-radius: 8px;">
                    <h4 style="margin-top: 0;">🔍 <?php esc_html_e('View Logs', 'fp-performance-suite'); ?></h4>
                    <p><?php esc_html_e('Visualizza errori PHP in tempo reale, filtra per tipo (error, warning, notice), cerca nel debug.log.', 'fp-performance-suite'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-logs')); ?>" class="button button-primary">
                        <?php esc_html_e('Apri Logs Viewer', 'fp-performance-suite'); ?>
                    </a>
                </div>
                
                <div style="background: #fef3c7; padding: 20px; border-radius: 8px;">
                    <h4 style="margin-top: 0;">⚙️ <?php esc_html_e('Debug Settings', 'fp-performance-suite'); ?></h4>
                    <p><?php esc_html_e('Attiva/disattiva WP_DEBUG, WP_DEBUG_LOG, SCRIPT_DEBUG, SAVEQUERIES in sicurezza.', 'fp-performance-suite'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-monitoring&tab=logs')); ?>" class="button">
                        <?php esc_html_e('Debug Toggles', 'fp-performance-suite'); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
}
















