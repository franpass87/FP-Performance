<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\CriticalCss;
use FP\PerfSuite\Services\CDN\CdnManager;
use FP\PerfSuite\Services\Compression\CompressionManager;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor;
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
        // Check for success message
        $success_message = '';
        if (isset($_GET['updated']) && $_GET['updated'] === '1') {
            $success_message = __('Advanced settings saved.', 'fp-performance-suite');
        }

        // Check for error message
        $error_message = '';
        if (isset($_GET['error']) && $_GET['error'] === '1') {
            $error_message = isset($_GET['message']) 
                ? urldecode($_GET['message']) 
                : __('An error occurred while saving settings.', 'fp-performance-suite');
        }

        ob_start();
        ?>
        
        <?php if ($success_message) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($success_message); ?></p></div>
        <?php endif; ?>
        
        <?php if ($error_message) : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error_message); ?></p></div>
        <?php endif; ?>
        
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
            
            <!-- Core Web Vitals Monitor Section -->
            <?php echo $this->renderCoreWebVitalsSection(); ?>
            
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

    private function renderCoreWebVitalsSection(): string
    {
        $cwvMonitor = $this->container->get(CoreWebVitalsMonitor::class);
        $settings = $cwvMonitor->settings();
        $status = $cwvMonitor->status();
        $summary = $cwvMonitor->getSummary(7);

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📊 <?php esc_html_e('Core Web Vitals Monitor', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">v1.2.0</span></h2>
            <p><?php esc_html_e('Monitora in tempo reale i Core Web Vitals (LCP, FID, CLS) degli utenti reali per ottimizzare le performance e il ranking SEO di Google.', 'fp-performance-suite'); ?></p>
            
            <?php if ($status['enabled']): ?>
                <div class="notice notice-success inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong>
                        <?php printf(
                            esc_html__('Attivo - %d metriche raccolte (Sample rate: %.0f%%)', 'fp-performance-suite'),
                            $status['metrics_count'],
                            $settings['sample_rate'] * 100
                        ); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($summary)): ?>
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 15px 0;">
                <h4 style="margin-top: 0;"><?php esc_html_e('Metriche Ultimi 7 Giorni', 'fp-performance-suite'); ?></h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <?php foreach ($summary as $metricName => $data): ?>
                    <div style="background: white; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <div style="font-size: 12px; color: #666; margin-bottom: 5px;"><?php echo esc_html($metricName); ?></div>
                        <div style="font-size: 24px; font-weight: bold; margin-bottom: 5px;">
                            <?php 
                            if ($metricName === 'CLS') {
                                echo number_format($data['p75'], 3);
                            } else {
                                echo number_format($data['p75'], 0);
                                echo '<span style="font-size: 14px;">ms</span>';
                            }
                            ?>
                        </div>
                        <div style="font-size: 11px; color: #666;">
                            <?php printf(
                                esc_html__('%d campioni', 'fp-performance-suite'),
                                $data['count']
                            ); ?>
                        </div>
                        <div style="margin-top: 8px; display: flex; gap: 5px; font-size: 11px;">
                            <span style="color: #00a32a;">✓ <?php echo $data['good']; ?></span>
                            <span style="color: #dba617;">⚠ <?php echo $data['needs_improvement']; ?></span>
                            <span style="color: #d63638;">✗ <?php echo $data['poor']; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cwv_enabled"><?php esc_html_e('Abilita Monitoring', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="cwv[enabled]" id="cwv_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Monitora Core Web Vitals degli utenti reali (RUM)', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Raccoglie metriche reali dai browser degli utenti per avere dati accurati sulle performance percepite.', 'fp-performance-suite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_sample_rate"><?php esc_html_e('Sample Rate', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[sample_rate]" id="cwv_sample_rate" value="<?php echo esc_attr($settings['sample_rate'] * 100); ?>" min="1" max="100" class="small-text">
                        <span>%</span>
                        <p class="description"><?php esc_html_e('Percentuale di utenti da monitorare (100% = tutti gli utenti)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Metriche da Tracciare', 'fp-performance-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="cwv[track_lcp]" value="1" <?php checked($settings['track_lcp']); ?>>
                                <?php esc_html_e('LCP - Largest Contentful Paint', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_fid]" value="1" <?php checked($settings['track_fid']); ?>>
                                <?php esc_html_e('FID - First Input Delay', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_cls]" value="1" <?php checked($settings['track_cls']); ?>>
                                <?php esc_html_e('CLS - Cumulative Layout Shift', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_fcp]" value="1" <?php checked($settings['track_fcp']); ?>>
                                <?php esc_html_e('FCP - First Contentful Paint', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_ttfb]" value="1" <?php checked($settings['track_ttfb']); ?>>
                                <?php esc_html_e('TTFB - Time to First Byte', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="cwv[track_inp]" value="1" <?php checked($settings['track_inp']); ?>>
                                <?php esc_html_e('INP - Interaction to Next Paint (sperimentale)', 'fp-performance-suite'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_retention_days"><?php esc_html_e('Conservazione Dati', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[retention_days]" id="cwv_retention_days" value="<?php echo esc_attr($settings['retention_days']); ?>" min="1" max="365" class="small-text">
                        <span><?php esc_html_e('giorni', 'fp-performance-suite'); ?></span>
                        <p class="description"><?php esc_html_e('Per quanto tempo conservare le metriche raccolte', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_send_analytics"><?php esc_html_e('Integrazione Analytics', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="cwv[send_to_analytics]" id="cwv_send_analytics" value="1" <?php checked($settings['send_to_analytics']); ?>>
                            <?php esc_html_e('Invia metriche a Google Analytics (se disponibile)', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Le metriche verranno inviate come eventi personalizzati a GA4 se configurato', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Soglie di Allerta', 'fp-performance-suite'); ?></h3>
            <p class="description"><?php esc_html_e('Ricevi notifiche via email quando le metriche superano queste soglie (valori "poor"):', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_email"><?php esc_html_e('Email per Allerte', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="cwv[alert_email]" id="cwv_alert_email" value="<?php echo esc_attr($settings['alert_email']); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_lcp"><?php esc_html_e('Soglia LCP (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[alert_threshold_lcp]" id="cwv_alert_lcp" value="<?php echo esc_attr($settings['alert_threshold_lcp']); ?>" min="1000" max="10000" step="100" class="small-text">
                        <span class="description"><?php esc_html_e('Default: 2500ms (good), 4000ms (poor)', 'fp-performance-suite'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_fid"><?php esc_html_e('Soglia FID (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[alert_threshold_fid]" id="cwv_alert_fid" value="<?php echo esc_attr($settings['alert_threshold_fid']); ?>" min="50" max="1000" step="10" class="small-text">
                        <span class="description"><?php esc_html_e('Default: 100ms (good), 300ms (poor)', 'fp-performance-suite'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cwv_alert_cls"><?php esc_html_e('Soglia CLS', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cwv[alert_threshold_cls]" id="cwv_alert_cls" value="<?php echo esc_attr($settings['alert_threshold_cls']); ?>" min="0.05" max="1" step="0.05" class="small-text">
                        <span class="description"><?php esc_html_e('Default: 0.1 (good), 0.25 (poor)', 'fp-performance-suite'); ?></span>
                    </td>
                </tr>
            </table>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Perché è importante:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('I Core Web Vitals sono un fattore di ranking di Google per la SEO', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('I dati RUM (Real User Monitoring) sono più accurati dei test sintetici', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Aiuta a identificare problemi di performance che colpiscono gli utenti reali', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Monitora l\'impatto delle ottimizzazioni in tempo reale', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
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
            $redirect_url = add_query_arg(
                ['error' => '1', 'message' => urlencode(__('Permesso negato. Non hai i permessi necessari per salvare queste impostazioni.', 'fp-performance-suite'))],
                admin_url('admin.php?page=' . $this->slug())
            );
            wp_safe_redirect($redirect_url);
            exit;
        }

        try {
            // Verifica il nonce di sicurezza
            if (!check_admin_referer('fp_ps_advanced', '_wpnonce', false)) {
                throw new \Exception(__('Errore di sicurezza: nonce non valido o scaduto. Riprova a ricaricare la pagina.', 'fp-performance-suite'));
            }
            // Save Critical CSS
            if (isset($_POST['critical_css'])) {
                $criticalCss = new CriticalCss();
                $criticalCss->update(wp_unslash($_POST['critical_css']));
            }

            // Save Compression settings
            // IMPORTANTE: Gestiamo sempre la compressione perché quando una checkbox
            // non è selezionata, non viene inviata nei dati POST
            $compression = $this->container->get(CompressionManager::class);
            $enabled = !empty($_POST['compression']['enabled']);
            
            if ($enabled) {
                $compression->enable();
            } else {
                $compression->disable();
            }

            // Save CDN settings
            $cdn = new CdnManager();
            $currentCdn = $cdn->settings();
            
            // Prepara i settings CDN preservando i campi non presenti nel form
            $cdnSettings = array_merge($currentCdn, wp_unslash($_POST['cdn'] ?? []));
            
            // Gestisci esplicitamente le checkbox (non inviate se non selezionate)
            $cdnSettings['enabled'] = isset($_POST['cdn']['enabled']);
            
            $cdn->update($cdnSettings);

            // Save Monitoring settings
            $monitor = PerformanceMonitor::instance();
            $currentMonitoring = $monitor->settings();
            
            // Prepara i settings Monitoring preservando i campi non presenti nel form
            $monitoringSettings = array_merge($currentMonitoring, wp_unslash($_POST['monitoring'] ?? []));
            
            // Gestisci esplicitamente le checkbox (non inviate se non selezionate)
            $monitoringSettings['enabled'] = isset($_POST['monitoring']['enabled']);
            
            $monitor->update($monitoringSettings);

            // Save Core Web Vitals settings
            $cwvMonitor = $this->container->get(CoreWebVitalsMonitor::class);
            if (isset($_POST['cwv'])) {
                $cwvData = wp_unslash($_POST['cwv']);
                
                // Convert sample rate from percentage to decimal
                if (isset($cwvData['sample_rate'])) {
                    $cwvData['sample_rate'] = (float)$cwvData['sample_rate'] / 100;
                }
                
                // Gestisci le checkbox
                $cwvData['enabled'] = isset($_POST['cwv']['enabled']);
                $cwvData['track_lcp'] = isset($_POST['cwv']['track_lcp']);
                $cwvData['track_fid'] = isset($_POST['cwv']['track_fid']);
                $cwvData['track_cls'] = isset($_POST['cwv']['track_cls']);
                $cwvData['track_fcp'] = isset($_POST['cwv']['track_fcp']);
                $cwvData['track_ttfb'] = isset($_POST['cwv']['track_ttfb']);
                $cwvData['track_inp'] = isset($_POST['cwv']['track_inp']);
                $cwvData['send_to_analytics'] = isset($_POST['cwv']['send_to_analytics']);
                
                $cwvMonitor->update($cwvData);
            }

            // Save Reports settings
            $reports = new ScheduledReports();
            $currentReports = $reports->settings();
            
            // Prepara i settings Reports preservando i campi non presenti nel form
            $reportsSettings = array_merge($currentReports, wp_unslash($_POST['reports'] ?? []));
            
            // Gestisci esplicitamente le checkbox (non inviate se non selezionate)
            $reportsSettings['enabled'] = isset($_POST['reports']['enabled']);
            
            $reports->update($reportsSettings);

            // Redirect con successo
            $redirect_url = add_query_arg('updated', '1', admin_url('admin.php?page=' . $this->slug()));
            wp_safe_redirect($redirect_url);
            exit;

        } catch (\Throwable $e) {
            // Log dell'errore
            error_log('[FP Performance Suite] Errore durante il salvataggio delle impostazioni advanced: ' . $e->getMessage());
            
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
