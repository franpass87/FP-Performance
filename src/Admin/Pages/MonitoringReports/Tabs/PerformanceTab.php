<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs;

use FP\PerfSuite\Admin\Pages\MonitoringReports\Sections\MonitoringSection;
use FP\PerfSuite\Admin\Pages\MonitoringReports\Sections\CoreWebVitalsSection;
use FP\PerfSuite\Admin\Pages\MonitoringReports\Sections\ReportsSection;
use FP\PerfSuite\Admin\Pages\MonitoringReports\Sections\WebhookSection;
use FP\PerfSuite\Admin\Pages\MonitoringReports\Sections\PerformanceBudgetSection;
use FP\PerfSuite\ServiceContainer;

use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url;
use function admin_url;
use function wp_nonce_field;
use function strpos;

/**
 * Renderizza la tab Performance
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Tabs
 * @author Francesco Passeri
 */
class PerformanceTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Renderizza la tab
     */
    public function render(string $message = ''): string
    {
        ob_start();
        ?>
        
        <?php if ($message) : ?>
            <?php 
            $is_error = strpos($message, 'Error') === 0 || strpos($message, 'Errore') === 0;
            $notice_class = $is_error ? 'notice-error' : 'notice-success';
            ?>
            <div class="notice <?php echo esc_attr($notice_class); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
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
            <?php 
            $monitoringSection = new MonitoringSection();
            echo $monitoringSection->render(); 
            ?>
            
            <!-- Core Web Vitals Monitor Section -->
            <?php 
            $cwvSection = new CoreWebVitalsSection($this->container);
            echo $cwvSection->render(); 
            ?>
            
            <!-- Scheduled Reports Section -->
            <?php 
            $reportsSection = new ReportsSection();
            echo $reportsSection->render(); 
            ?>
            
            <!-- Webhook Integration Section -->
            <?php 
            $webhookSection = new WebhookSection();
            echo $webhookSection->render(); 
            ?>
            
            <!-- Performance Budget Section -->
            <?php 
            $budgetSection = new PerformanceBudgetSection();
            echo $budgetSection->render(); 
            ?>
            
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
}
















