<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\CriticalCss;
use FP\PerfSuite\Services\CDN\CdnManager;
use FP\PerfSuite\Services\Compression\CompressionManager;
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
            
            <!-- Compression Section -->
            <?php echo $this->renderCompressionSection(); ?>
            
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

    private function renderCompressionSection(): string
    {
        $compression = $this->container->get(CompressionManager::class);
        $status = $compression->status();
        $info = $compression->getInfo();

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>🗜️ <?php esc_html_e('Compressione Brotli & Gzip', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Abilita la compressione Brotli e Gzip per ridurre le dimensioni dei file trasferiti del 60-80%.', 'fp-performance-suite'); ?></p>
            
            <!-- Status Overview -->
            <div style="background: #f0f0f1; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <h4 style="margin-top: 0;"><?php esc_html_e('Stato Attuale', 'fp-performance-suite'); ?></h4>
                <ul style="margin: 0;">
                    <li>
                        <?php if ($status['active']): ?>
                            <span style="color: #00a32a;">✓</span> <?php esc_html_e('Compressione attiva', 'fp-performance-suite'); ?>
                        <?php else: ?>
                            <span style="color: #d63638;">✗</span> <?php esc_html_e('Compressione non attiva', 'fp-performance-suite'); ?>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($status['brotli_supported']): ?>
                            <span style="color: #00a32a;">✓</span> <?php esc_html_e('Brotli supportato', 'fp-performance-suite'); ?>
                        <?php else: ?>
                            <span style="color: #dba617;">⚠</span> <?php esc_html_e('Brotli non disponibile', 'fp-performance-suite'); ?>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($status['gzip_supported']): ?>
                            <span style="color: #00a32a;">✓</span> <?php esc_html_e('Gzip supportato', 'fp-performance-suite'); ?>
                        <?php else: ?>
                            <span style="color: #d63638;">✗</span> <?php esc_html_e('Gzip non disponibile', 'fp-performance-suite'); ?>
                        <?php endif; ?>
                    </li>
                    <li>
                        <?php if ($status['htaccess_supported']): ?>
                            <span style="color: #00a32a;">✓</span> <?php esc_html_e('.htaccess modificabile', 'fp-performance-suite'); ?>
                        <?php else: ?>
                            <span style="color: #dba617;">⚠</span> <?php esc_html_e('.htaccess non modificabile', 'fp-performance-suite'); ?>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>

            <?php if (!$status['gzip_supported'] && !$status['brotli_supported']): ?>
                <div class="notice notice-warning inline">
                    <p>
                        <strong><?php esc_html_e('Attenzione:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('Il tuo server non sembra supportare né Gzip né Brotli. Contatta il tuo hosting provider per abilitare mod_deflate o mod_brotli.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!$status['htaccess_supported']): ?>
                <div class="notice notice-info inline">
                    <p>
                        <strong><?php esc_html_e('Info:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('Il file .htaccess non può essere modificato automaticamente. Dovrai configurare la compressione manualmente nel tuo server.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="compression_enabled"><?php esc_html_e('Abilita Compressione', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="compression[enabled]" id="compression_enabled" value="1" <?php checked($status['enabled']); ?>>
                            <?php esc_html_e('Abilita compressione Brotli e Gzip', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Questo ridurrà le dimensioni di HTML, CSS, JavaScript e altri file di testo del 60-80%, migliorando drasticamente i tempi di caricamento.', 'fp-performance-suite'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <!-- Technical Details (Collapsible) -->
            <details style="margin-top: 20px;">
                <summary style="cursor: pointer; font-weight: 600; padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <?php esc_html_e('Dettagli Tecnici', 'fp-performance-suite'); ?>
                </summary>
                <div style="padding: 15px; background: #fafafa; border: 1px solid #e0e0e0; border-top: none; border-radius: 0 0 4px 4px;">
                    <h4><?php esc_html_e('Moduli Apache Rilevati:', 'fp-performance-suite'); ?></h4>
                    <ul>
                        <?php if (!empty($info['modules'])): ?>
                            <?php foreach ($info['modules'] as $module => $available): ?>
                                <li>
                                    <code><?php echo esc_html($module); ?></code>: 
                                    <?php if ($available): ?>
                                        <span style="color: #00a32a;">✓ <?php esc_html_e('Disponibile', 'fp-performance-suite'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #d63638;">✗ <?php esc_html_e('Non disponibile', 'fp-performance-suite'); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><?php esc_html_e('Informazioni sui moduli non disponibili', 'fp-performance-suite'); ?></li>
                        <?php endif; ?>
                    </ul>
                    
                    <h4><?php esc_html_e('Impostazioni PHP:', 'fp-performance-suite'); ?></h4>
                    <ul>
                        <li>
                            <code>zlib.output_compression</code>: 
                            <strong><?php echo esc_html($info['php_settings']['zlib.output_compression'] ?: 'Off'); ?></strong>
                        </li>
                        <li>
                            <code>gzencode()</code>: 
                            <?php if ($info['php_settings']['gzencode_available']): ?>
                                <span style="color: #00a32a;">✓ <?php esc_html_e('Disponibile', 'fp-performance-suite'); ?></span>
                            <?php else: ?>
                                <span style="color: #d63638;">✗ <?php esc_html_e('Non disponibile', 'fp-performance-suite'); ?></span>
                            <?php endif; ?>
                        </li>
                    </ul>

                    <?php if ($status['has_rules']): ?>
                        <h4><?php esc_html_e('Regole .htaccess:', 'fp-performance-suite'); ?></h4>
                        <p style="color: #00a32a;">
                            ✓ <?php esc_html_e('Le regole di compressione sono presenti nel file .htaccess', 'fp-performance-suite'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </details>
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

        // Save Compression settings
        if (isset($_POST['compression'])) {
            $compression = $this->container->get(CompressionManager::class);
            $enabled = !empty($_POST['compression']['enabled']);
            
            if ($enabled) {
                $compression->enable();
            } else {
                $compression->disable();
            }
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
