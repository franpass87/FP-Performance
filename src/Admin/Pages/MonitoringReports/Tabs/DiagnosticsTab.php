<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs;

use function esc_html_e;
use function esc_url;
use function admin_url;

/**
 * Renderizza la tab Diagnostics
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs
 * @author Francesco Passeri
 */
class DiagnosticsTab
{
    /**
     * Renderizza la tab
     */
    public function render(): string
    {
        ob_start();
        ?>
        <div class="fp-ps-tab-description" style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
            <p style="margin: 0; color: #065f46;">
                <strong>🔧 System Diagnostics:</strong> 
                <?php esc_html_e('Diagnostica completa sistema: verifica servizi, configurazione server, compatibilità e troubleshooting.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <div class="fp-ps-card">
            <h3>🔧 <?php esc_html_e('Strumenti di Diagnostica', 'fp-performance-suite'); ?></h3>
            <p><?php esc_html_e('Accedi alla pagina completa di diagnostica per analisi approfondite del sistema:', 'fp-performance-suite'); ?></p>
            
            <div style="margin: 20px 0;">
                <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-diagnostics')); ?>" class="button button-primary button-large">
                    🔧 <?php esc_html_e('Apri System Diagnostics', 'fp-performance-suite'); ?>
                </a>
            </div>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('📊 Funzionalità disponibili:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('Verifica stato servizi plugin', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Diagnostica completa sistema', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Informazioni server e PHP', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Gestione .htaccess', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Strumenti di recupero', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        
        <?php
        return ob_get_clean();
    }
}
















