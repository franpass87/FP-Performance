<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Reports\ScheduledReports as ReportsService;

/**
 * Scheduled Reports Admin Page
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ScheduledReports extends AbstractPage
{
    private ReportsService $reportsService;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->reportsService = new ReportsService();
    }

    public function slug(): string
    {
        return 'fp-performance-suite-scheduled-reports';
    }

    public function title(): string
    {
        return __('Scheduled Reports', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return 'manage_options';
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
                __('FP Performance', 'fp-performance-suite'),
                __('Scheduled Reports', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        $settings = $this->reportsService->settings();

        ob_start();
        ?>
        <section class="fp-ps-card">
            <h2>📧 <?php esc_html_e('Scheduled Reports', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Configure automatic performance reports via email.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="reports_enabled"><?php esc_html_e('Enable Reports', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="reports[enabled]" id="reports_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Send scheduled performance reports', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="reports_frequency"><?php esc_html_e('Frequency', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <select name="reports[frequency]" id="reports_frequency">
                            <option value="daily" <?php selected($settings['frequency'], 'daily'); ?>><?php esc_html_e('Daily', 'fp-performance-suite'); ?></option>
                            <option value="weekly" <?php selected($settings['frequency'], 'weekly'); ?>><?php esc_html_e('Weekly', 'fp-performance-suite'); ?></option>
                            <option value="monthly" <?php selected($settings['frequency'], 'monthly'); ?>><?php esc_html_e('Monthly', 'fp-performance-suite'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="reports_recipient"><?php esc_html_e('Recipient', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="reports[recipient]" id="reports_recipient" value="<?php echo esc_attr($settings['recipient']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
        </section>
        <?php
        return ob_get_clean();
    }

    public function handleSave(): void
    {
        if (!current_user_can($this->capability())) {
            $redirect_url = add_query_arg(
                ['error' => '1', 'message' => urlencode(__('Permission denied. You do not have the necessary permissions to save these settings.', 'fp-performance-suite'))],
                admin_url('admin.php?page=' . $this->slug())
            );
            wp_safe_redirect($redirect_url);
            exit;
        }

        try {
            // Verifica il nonce di sicurezza
            if (!check_admin_referer('fp_ps_reports', '_wpnonce', false)) {
                throw new \Exception(__('Security error: invalid or expired nonce. Please reload the page and try again.', 'fp-performance-suite'));
            }

            // Save Reports settings
            $currentReports = $this->reportsService->settings();
            
            // Prepara i settings Reports preservando i campi non presenti nel form
            $reportsSettings = array_merge($currentReports, wp_unslash($_POST['reports'] ?? []));
            
            // Gestisci esplicitamente le checkbox (non inviate se non selezionate)
            $reportsSettings['enabled'] = isset($_POST['reports']['enabled']);
            
            $this->reportsService->update($reportsSettings);

            // Redirect con successo
            $redirect_url = add_query_arg('updated', '1', admin_url('admin.php?page=' . $this->slug()));
            wp_safe_redirect($redirect_url);
            exit;

        } catch (\Throwable $e) {
            // Log dell'errore
            error_log('[FP Performance Suite] Error saving scheduled reports settings: ' . $e->getMessage());
            
            // Redirect con errore
            $redirect_url = add_query_arg(
                ['error' => '1', 'message' => urlencode($e->getMessage())],
                admin_url('admin.php?page=' . $this->slug())
            );
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
}
