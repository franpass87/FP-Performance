<?php

namespace FP\PerfSuite\Admin\Pages\MonitoringReports\Sections;

use FP\PerfSuite\Admin\RiskMatrix;

use function esc_attr;
use function esc_html;
use function esc_html_e;
use function checked;
use function in_array;
use function get_option;
use function home_url;
use function __;

/**
 * Renderizza la sezione Webhook Integration
 * 
 * @package FP\PerfSuite\Admin\Pages\MonitoringReports\Sections
 * @author Francesco Passeri
 */
class WebhookSection
{
    /**
     * Renderizza la sezione
     */
    public function render(): string
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
            'media_optimized' => __('Media Optimization', 'fp-performance-suite'),
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
                        <label for="webhook_enabled">
                            <?php esc_html_e('Abilita Webhooks', 'fp-performance-suite'); ?>
                            <?php echo RiskMatrix::renderIndicator('webhooks_enabled'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="webhooks[enabled]" id="webhook_enabled" value="1" <?php checked($webhooks['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('webhooks_enabled')); ?>">
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
                        <label for="retry_failed">
                            <?php esc_html_e('Riprova Richieste Fallite', 'fp-performance-suite'); ?>
                            <?php echo RiskMatrix::renderIndicator('webhooks_retry_failed'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="webhooks[retry_failed]" id="retry_failed" value="1" <?php checked($webhooks['retry_failed']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('webhooks_retry_failed')); ?>">
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
}
















