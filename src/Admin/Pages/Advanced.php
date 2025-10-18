<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\CriticalCss;
use FP\PerfSuite\Services\CDN\CdnManager;
use FP\PerfSuite\Services\Compression\CompressionManager;
use FP\PerfSuite\Services\Monitoring\PerformanceMonitor;
use FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor;
use FP\PerfSuite\Services\Reports\ScheduledReports;
use FP\PerfSuite\Services\PWA\ServiceWorkerManager;
use FP\PerfSuite\Services\Assets\PredictivePrefetching;

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
            
            <!-- PWA / Service Worker Section -->
            <?php echo $this->renderPWASection(); ?>
            
            <!-- Predictive Prefetching Section -->
            <?php echo $this->renderPrefetchingSection(); ?>
            
            <!-- Performance Budget Section -->
            <?php echo $this->renderPerformanceBudgetSection(); ?>
            
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
        try {
            $criticalCss = new CriticalCss();
            $status = $criticalCss->status();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading CriticalCss: ' . $e->getMessage());
            return $this->renderErrorSection('Critical CSS', $e->getMessage());
        }

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
        try {
            $compression = $this->container->get(CompressionManager::class);
            $status = $compression->status();
            $info = $compression->getInfo();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading CompressionManager: ' . $e->getMessage());
            return $this->renderErrorSection('Compressione', $e->getMessage());
        }

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
        try {
            $cdn = new CdnManager();
            $settings = $cdn->settings();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading CdnManager: ' . $e->getMessage());
            return $this->renderErrorSection('CDN Integration', $e->getMessage());
        }

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
        try {
            $monitor = PerformanceMonitor::instance();
            $settings = $monitor->settings();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading PerformanceMonitor: ' . $e->getMessage());
            return $this->renderErrorSection('Performance Monitoring', $e->getMessage());
        }

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
        try {
            $cwvMonitor = $this->container->get(CoreWebVitalsMonitor::class);
            $settings = $cwvMonitor->settings();
            $status = $cwvMonitor->status();
            $summary = $cwvMonitor->getSummary(7);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading CoreWebVitalsMonitor: ' . $e->getMessage());
            return $this->renderErrorSection('Core Web Vitals Monitor', $e->getMessage());
        }

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
        try {
            $reports = new ScheduledReports();
            $settings = $reports->settings();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading ScheduledReports: ' . $e->getMessage());
            return $this->renderErrorSection('Scheduled Reports', $e->getMessage());
        }

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

    private function renderPWASection(): string
    {
        try {
            $serviceWorker = $this->container->get(ServiceWorkerManager::class);
            $settings = $serviceWorker->settings();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading ServiceWorkerManager: ' . $e->getMessage());
            return $this->renderErrorSection('Progressive Web App (PWA)', $e->getMessage());
        }

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📱 <?php esc_html_e('Progressive Web App (PWA)', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Trasforma il sito in una PWA con Service Worker, cache offline e installabilità.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="pwa_enabled"><?php esc_html_e('Enable PWA', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="pwa[enabled]" id="pwa_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Abilita Service Worker e Web App Manifest', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Rende il sito installabile su home screen e funzionante offline.', 'fp-performance-suite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="pwa_cache_strategy"><?php esc_html_e('Cache Strategy', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <select name="pwa[cache_strategy]" id="pwa_cache_strategy">
                            <option value="network_first" <?php selected($settings['cache_strategy'], 'network_first'); ?>><?php esc_html_e('Network First (consigliato)', 'fp-performance-suite'); ?></option>
                            <option value="cache_first" <?php selected($settings['cache_strategy'], 'cache_first'); ?>><?php esc_html_e('Cache First (veloce)', 'fp-performance-suite'); ?></option>
                            <option value="stale_while_revalidate" <?php selected($settings['cache_strategy'], 'stale_while_revalidate'); ?>><?php esc_html_e('Stale While Revalidate', 'fp-performance-suite'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Strategia di caching del Service Worker', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Cosa cachare', 'fp-performance-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="pwa[cache_assets]" value="1" <?php checked($settings['cache_assets']); ?>>
                                <?php esc_html_e('Assets statici (CSS, JS, immagini)', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="pwa[cache_pages]" value="1" <?php checked($settings['cache_pages']); ?>>
                                <?php esc_html_e('Pagine HTML', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="pwa[cache_api]" value="1" <?php checked($settings['cache_api']); ?>>
                                <?php esc_html_e('Chiamate API (avanzato)', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="pwa[offline_page]" value="1" <?php checked($settings['offline_page']); ?>>
                                <?php esc_html_e('Pagina offline custom', 'fp-performance-suite'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="pwa_update_interval"><?php esc_html_e('Intervallo aggiornamento (secondi)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="pwa[update_interval]" id="pwa_update_interval" value="<?php echo esc_attr($settings['update_interval']); ?>" min="3600" max="604800" class="small-text">
                        <span><?php esc_html_e('secondi', 'fp-performance-suite'); ?></span>
                        <p class="description"><?php esc_html_e('Ogni quanto aggiornare la cache (default: 24h)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="pwa_max_cache_size"><?php esc_html_e('Max dimensione cache (MB)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="pwa[max_cache_size]" id="pwa_max_cache_size" value="<?php echo esc_attr($settings['max_cache_size']); ?>" min="10" max="500" class="small-text">
                        <span>MB</span>
                    </td>
                </tr>
            </table>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('⚠️ Requisiti PWA:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #856404;">
                    <li><?php esc_html_e('HTTPS obbligatorio per Service Worker', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Testa approfonditamente prima di attivare in produzione', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Il Service Worker può cachare dati non aggiornati', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderPrefetchingSection(): string
    {
        try {
            $prefetching = $this->container->get(PredictivePrefetching::class);
            $settings = $prefetching->settings();
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Error loading PredictivePrefetching: ' . $e->getMessage());
            return $this->renderErrorSection('Predictive Prefetching', $e->getMessage());
        }

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>🔮 <?php esc_html_e('Predictive Prefetching', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Prefetch intelligente delle pagine che l\'utente probabilmente visiterà, basato su hover, scroll e viewport.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="prefetch_enabled"><?php esc_html_e('Enable Prefetching', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="prefetch[enabled]" id="prefetch_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Abilita prefetch predittivo dei link', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Precarica le pagine prima del click per navigazione istantanea.', 'fp-performance-suite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="prefetch_strategy"><?php esc_html_e('Strategia', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <select name="prefetch[strategy]" id="prefetch_strategy">
                            <option value="hover" <?php selected($settings['strategy'], 'hover'); ?>><?php esc_html_e('Hover (consigliato)', 'fp-performance-suite'); ?></option>
                            <option value="visible" <?php selected($settings['strategy'], 'visible'); ?>><?php esc_html_e('Visible in viewport', 'fp-performance-suite'); ?></option>
                            <option value="viewport" <?php selected($settings['strategy'], 'viewport'); ?>><?php esc_html_e('Near viewport', 'fp-performance-suite'); ?></option>
                            <option value="mouse-tracking" <?php selected($settings['strategy'], 'mouse-tracking'); ?>><?php esc_html_e('Mouse tracking (aggressivo)', 'fp-performance-suite'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Trigger', 'fp-performance-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="prefetch[prefetch_on_hover]" value="1" <?php checked($settings['prefetch_on_hover']); ?>>
                                <?php esc_html_e('Prefetch su hover link', 'fp-performance-suite'); ?>
                            </label><br>
                            <label>
                                <input type="checkbox" name="prefetch[prefetch_on_visible]" value="1" <?php checked($settings['prefetch_on_visible']); ?>>
                                <?php esc_html_e('Prefetch link visibili', 'fp-performance-suite'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="prefetch_delay"><?php esc_html_e('Delay (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="prefetch[delay]" id="prefetch_delay" value="<?php echo esc_attr($settings['delay']); ?>" min="0" max="2000" step="50" class="small-text">
                        <span>ms</span>
                        <p class="description"><?php esc_html_e('Ritardo prima del prefetch (default: 100ms)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="prefetch_max"><?php esc_html_e('Max prefetch simultanei', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="prefetch[max_prefetch]" id="prefetch_max" value="<?php echo esc_attr($settings['max_prefetch']); ?>" min="1" max="10" class="small-text">
                        <p class="description"><?php esc_html_e('Limite per evitare sovraccarico (consigliato: 3)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Benefici Prefetching:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('Navigazione quasi istantanea tra pagine', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Riduce il tempo di caricamento percepito a ~0ms', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Intelligente: prefetch solo pagine con alta probabilità di click', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Rispetta Save-Data e connessioni lente', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function renderPerformanceBudgetSection(): string
    {
        $budget = get_option('fp_ps_performance_budget', [
            'enabled' => false,
            'score_threshold' => 80,
            'load_time_threshold' => 3000,
            'fcp_threshold' => 1800,
            'lcp_threshold' => 2500,
            'cls_threshold' => 0.1,
            'alert_email' => get_option('admin_email'),
            'alert_on_exceed' => true,
        ]);

        ob_start();
        ?>
        <div class="fp-ps-card">
            <h2>📊 <?php esc_html_e('Performance Budget', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Imposta soglie di performance e ricevi avvisi quando vengono superate. Aiuta a mantenere il sito veloce nel tempo.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="perf_budget_enabled"><?php esc_html_e('Abilita Performance Budget', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="perf_budget[enabled]" id="perf_budget_enabled" value="1" <?php checked($budget['enabled']); ?>>
                            <?php esc_html_e('Monitora e avvisa quando le soglie vengono superate', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Soglie Performance', 'fp-performance-suite'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="score_threshold"><?php esc_html_e('Performance Score Minimo', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[score_threshold]" id="score_threshold" value="<?php echo esc_attr($budget['score_threshold']); ?>" min="0" max="100" class="small-text">
                        <span>/100</span>
                        <p class="description"><?php esc_html_e('Score minimo accettabile. Avviso se scende sotto questa soglia.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="load_time_threshold"><?php esc_html_e('Load Time Massimo (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[load_time_threshold]" id="load_time_threshold" value="<?php echo esc_attr($budget['load_time_threshold']); ?>" min="500" max="10000" step="100" class="small-text">
                        <span>ms</span>
                        <p class="description"><?php esc_html_e('Tempo massimo di caricamento accettabile.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="fcp_threshold"><?php esc_html_e('FCP Threshold (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[fcp_threshold]" id="fcp_threshold" value="<?php echo esc_attr($budget['fcp_threshold']); ?>" min="500" max="5000" step="100" class="small-text">
                        <span>ms</span>
                        <p class="description"><?php esc_html_e('First Contentful Paint - Consigliato: < 1800ms', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lcp_threshold"><?php esc_html_e('LCP Threshold (ms)', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[lcp_threshold]" id="lcp_threshold" value="<?php echo esc_attr($budget['lcp_threshold']); ?>" min="1000" max="10000" step="100" class="small-text">
                        <span>ms</span>
                        <p class="description"><?php esc_html_e('Largest Contentful Paint - Consigliato: < 2500ms', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cls_threshold"><?php esc_html_e('CLS Threshold', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="perf_budget[cls_threshold]" id="cls_threshold" value="<?php echo esc_attr($budget['cls_threshold']); ?>" min="0" max="1" step="0.01" class="small-text">
                        <p class="description"><?php esc_html_e('Cumulative Layout Shift - Consigliato: < 0.1', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php esc_html_e('Avvisi', 'fp-performance-suite'); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="alert_on_exceed"><?php esc_html_e('Invia avvisi via email', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="perf_budget[alert_on_exceed]" id="alert_on_exceed" value="1" <?php checked($budget['alert_on_exceed']); ?>>
                            <?php esc_html_e('Invia email quando le soglie vengono superate', 'fp-performance-suite'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="alert_email"><?php esc_html_e('Email per avvisi', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="perf_budget[alert_email]" id="alert_email" value="<?php echo esc_attr($budget['alert_email']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('💡 Cos\'è un Performance Budget?', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #856404;">
                    <li><?php esc_html_e('Definisce limiti chiari per le metriche di performance del sito', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Previene degradi delle performance nel tempo (performance regression)', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Avvisa quando nuove modifiche peggiorano le performance', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Best practice per team di sviluppo e siti enterprise', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render error section when a service fails to load
     */
    private function renderErrorSection(string $sectionName, string $errorMessage): string
    {
        ob_start();
        ?>
        <div class="fp-ps-card" style="border-left: 4px solid #d63638;">
            <h2>⚠️ <?php echo esc_html($sectionName); ?></h2>
            <div class="notice notice-error inline" style="margin: 0;">
                <p>
                    <strong><?php esc_html_e('Errore:', 'fp-performance-suite'); ?></strong>
                    <?php esc_html_e('Impossibile caricare questa sezione. Controlla i log per maggiori dettagli.', 'fp-performance-suite'); ?>
                </p>
                <details>
                    <summary style="cursor: pointer;"><?php esc_html_e('Dettagli tecnici', 'fp-performance-suite'); ?></summary>
                    <pre style="background: #f0f0f1; padding: 10px; margin-top: 10px; overflow: auto;"><?php echo esc_html($errorMessage); ?></pre>
                </details>
            </div>
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

            // Save PWA / Service Worker settings
            $serviceWorker = $this->container->get(ServiceWorkerManager::class);
            if (isset($_POST['pwa'])) {
                $pwaData = wp_unslash($_POST['pwa']);
                $pwaData['enabled'] = isset($_POST['pwa']['enabled']);
                $pwaData['cache_assets'] = isset($_POST['pwa']['cache_assets']);
                $pwaData['cache_pages'] = isset($_POST['pwa']['cache_pages']);
                $pwaData['cache_api'] = isset($_POST['pwa']['cache_api']);
                $pwaData['offline_page'] = isset($_POST['pwa']['offline_page']);
                $serviceWorker->update($pwaData);
            }

            // Save Predictive Prefetching settings
            $prefetching = $this->container->get(PredictivePrefetching::class);
            if (isset($_POST['prefetch'])) {
                $prefetchData = wp_unslash($_POST['prefetch']);
                $prefetchData['enabled'] = isset($_POST['prefetch']['enabled']);
                $prefetchData['prefetch_on_hover'] = isset($_POST['prefetch']['prefetch_on_hover']);
                $prefetchData['prefetch_on_visible'] = isset($_POST['prefetch']['prefetch_on_visible']);
                $prefetching->update($prefetchData);
            }

            // Save Performance Budget settings
            if (isset($_POST['perf_budget'])) {
                $budgetData = [
                    'enabled' => isset($_POST['perf_budget']['enabled']),
                    'score_threshold' => (int) ($_POST['perf_budget']['score_threshold'] ?? 80),
                    'load_time_threshold' => (int) ($_POST['perf_budget']['load_time_threshold'] ?? 3000),
                    'fcp_threshold' => (int) ($_POST['perf_budget']['fcp_threshold'] ?? 1800),
                    'lcp_threshold' => (int) ($_POST['perf_budget']['lcp_threshold'] ?? 2500),
                    'cls_threshold' => (float) ($_POST['perf_budget']['cls_threshold'] ?? 0.1),
                    'alert_email' => sanitize_email($_POST['perf_budget']['alert_email'] ?? get_option('admin_email')),
                    'alert_on_exceed' => isset($_POST['perf_budget']['alert_on_exceed']),
                ];
                update_option('fp_ps_performance_budget', $budgetData);
            }

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
