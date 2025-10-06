<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\CriticalCss;
use FP\PerfSuite\Services\CDN\CdnManager;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Reports\ScheduledReports;

/**
 * Advanced Features Admin Page
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Advanced extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        
        // Handle form submissions
        add_action('admin_post_fp_ps_save_advanced', [$this, 'handleSave']);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-advanced';
    }

    public function title(): string
    {
        return __('Advanced Features', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return 'manage_options'; // Advanced features require admin
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
                __('Advanced', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        ob_start();
        ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('fp_ps_advanced', '_wpnonce'); ?>
            <input type="hidden" name="action" value="fp_ps_save_advanced">
            
            <!-- Critical CSS Section -->
            <?php echo $this->renderCriticalCssSection(); ?>
            
            <!-- CDN Section -->
            <?php echo $this->renderCdnSection(); ?>
            
            <!-- Performance Monitoring Section -->
            <?php echo $this->renderMonitoringSection(); ?>
            
            <!-- Scheduled Reports Section -->
            <?php echo $this->renderReportsSection(); ?>
            
            <!-- Save Button -->
            <div class="fp-ps-card">
                <div class="fp-ps-actions">
                    <button type="submit" class="button button-primary button-large">
                        <?php esc_html_e('Save Advanced Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </div>
        </form>
        
        <?php
        return ob_get_clean();
    }

    private function renderCriticalCssSection(): string
    {
        $criticalCss = new CriticalCss();
        $status = $criticalCss->status();
        
        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>🎨 <?php esc_html_e('Critical CSS', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Inline critical CSS for above-the-fold content to improve initial render time.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="critical_css"><?php esc_html_e('Critical CSS', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <textarea 
                            name="critical_css" 
                            id="critical_css" 
                            rows="10" 
                            class="large-text code"
                            placeholder="body { margin: 0; } .header { ... }"
                        ><?php echo esc_textarea($criticalCss->get()); ?></textarea>
                        <p class="description">
                            <?php printf(
                                esc_html__('Current size: %s KB / %s KB max', 'fp-performance-suite'),
                                number_format($status['size_kb'], 2),
                                number_format($status['max_size_kb'], 0)
                            ); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderCdnSection(): string
    {
        $cdn = new CdnManager();
        $settings = $cdn->settings();
        
        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>🌐 <?php esc_html_e('CDN Integration', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Rewrite asset URLs to use a Content Delivery Network.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cdn_enabled"><?php esc_html_e('Enable CDN', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="cdn[enabled]" id="cdn_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Enable CDN URL rewriting', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cdn_url"><?php esc_html_e('CDN URL', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="cdn[url]" id="cdn_url" value="<?php echo esc_attr($settings['url']); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Example: https://cdn.example.com', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cdn_provider"><?php esc_html_e('Provider', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <select name="cdn[provider]" id="cdn_provider">
                            <option value="custom" <?php selected($settings['provider'], 'custom'); ?>><?php esc_html_e('Custom', 'fp-performance-suite'); ?></option>
                            <option value="cloudflare" <?php selected($settings['provider'], 'cloudflare'); ?>>CloudFlare</option>
                            <option value="bunnycdn" <?php selected($settings['provider'], 'bunnycdn'); ?>>BunnyCDN</option>
                            <option value="stackpath" <?php selected($settings['provider'], 'stackpath'); ?>>StackPath</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderMonitoringSection(): string
    {
        $monitor = PerformanceMonitor::instance();
        $settings = $monitor->settings();
        
        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📊 <?php esc_html_e('Performance Monitoring', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Track page load times, database queries, and memory usage over time.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="monitoring_enabled"><?php esc_html_e('Enable Monitoring', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="monitoring[enabled]" id="monitoring_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Track performance metrics', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sample_rate"><?php esc_html_e('Sample Rate', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="monitoring[sample_rate]" id="sample_rate" value="<?php echo esc_attr($settings['sample_rate']); ?>" min="1" max="100" class="small-text">
                        <span>%</span>
                        <p class="description"><?php esc_html_e('Percentage of requests to monitor (lower = less overhead)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderReportsSection(): string
    {
        $reports = new ScheduledReports();
        $settings = $reports->settings();
        
        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📧 <?php esc_html_e('Scheduled Reports', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Receive periodic performance reports via email.', 'fp-performance-suite'); ?></p>
            
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
        </div>
        <?php
        return ob_get_clean();
    }

    public function handleSave(): void
    {
        if (!current_user_can($this->capability())) {
            wp_die(esc_html__('Permission denied', 'fp-performance-suite'));
        }

        check_admin_referer('fp_ps_advanced');

        // Save Critical CSS
        if (isset($_POST['critical_css'])) {
            $criticalCss = new CriticalCss();
            $criticalCss->update(wp_unslash($_POST['critical_css']));
        }

        // Save CDN settings
        if (isset($_POST['cdn'])) {
            $cdn = new CdnManager();
            $cdn->update(wp_unslash($_POST['cdn']));
        }

        // Save Monitoring settings
        if (isset($_POST['monitoring'])) {
            $monitor = PerformanceMonitor::instance();
            $monitor->update(wp_unslash($_POST['monitoring']));
        }

        // Save Reports settings
        if (isset($_POST['reports'])) {
            $reports = new ScheduledReports();
            $reports->update(wp_unslash($_POST['reports']));
        }

        wp_safe_redirect(add_query_arg('updated', '1', admin_url('admin.php?page=' . $this->slug())));
        exit;
    }
}
