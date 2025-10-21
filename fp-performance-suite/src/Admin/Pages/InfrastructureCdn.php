<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\CDN\CdnManager;
use FP\PerfSuite\Services\Compression\CompressionManager;

use function __;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url_raw;
use function get_option;
use function update_option;
use function sanitize_text_field;
use function sanitize_email;
use function checked;
use function selected;
use function wp_nonce_field;
use function wp_verify_nonce;
use function wp_unslash;
use function current_user_can;
use function wp_safe_redirect;
use function admin_url;
use function add_query_arg;

/**
 * Infrastructure & CDN Page
 * 
 * Gestisce tutte le ottimizzazioni relative all'infrastruttura:
 * - CDN Integration
 * - Compression (Brotli & Gzip)
 * - Performance Budget
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class InfrastructureCdn extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-infrastructure';
    }

    public function title(): string
    {
        return __('Infrastructure & CDN', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
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
                __('Ottimizzazione', 'fp-performance-suite'),
                __('Infrastruttura & CDN', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        // Check for success message
        $success_message = '';
        if (isset($_GET['updated']) && $_GET['updated'] === '1') {
            $success_message = __('Impostazioni infrastruttura salvate con successo!', 'fp-performance-suite');
        }

        // Check for error message
        $error_message = '';
        if (isset($_GET['error']) && $_GET['error'] === '1') {
            $error_message = isset($_GET['message']) 
                ? urldecode($_GET['message']) 
                : __('Si è verificato un errore durante il salvataggio.', 'fp-performance-suite');
        }

        ob_start();
        ?>
        
        <?php if ($success_message) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($success_message); ?></p></div>
        <?php endif; ?>
        
        <?php if ($error_message) : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error_message); ?></p></div>
        <?php endif; ?>
        
        <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">
            <p style="margin: 0;">
                <strong>🌐 <?php esc_html_e('Ottimizzazioni Infrastruttura', 'fp-performance-suite'); ?></strong><br>
                <?php esc_html_e('Configura CDN, compressione e limiti di performance per ottimizzare la distribuzione dei contenuti e il caricamento del sito.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('fp_ps_infrastructure', '_wpnonce'); ?>
            <input type="hidden" name="action" value="fp_ps_save_infrastructure">
            
            <!-- CDN Section -->
            <?php echo $this->renderCdnSection(); ?>
            
            <!-- Compression Section -->
            <?php echo $this->renderCompressionSection(); ?>
            
            <!-- Performance Budget Section -->
            <?php echo $this->renderPerformanceBudgetSection(); ?>
            
            <!-- Save Button -->
            <div class="fp-ps-card">
                <div class="fp-ps-actions">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Salva Impostazioni Infrastruttura', 'fp-performance-suite'); ?>
                    </button>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e('Salva tutte le modifiche apportate alle impostazioni CDN, compressione e performance budget.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </form>
        
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
            <p><?php esc_html_e('Integra un Content Delivery Network per distribuire i tuoi asset (immagini, CSS, JS) da server geograficamente più vicini ai tuoi utenti, migliorando drasticamente i tempi di caricamento.', 'fp-performance-suite'); ?></p>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="cdn_enabled"><?php esc_html_e('Abilita CDN', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="cdn[enabled]" id="cdn_enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <?php esc_html_e('Abilita rewriting degli URL verso il CDN', 'fp-performance-suite'); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e('Riscrive automaticamente gli URL di assets statici per puntare al tuo CDN.', 'fp-performance-suite'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cdn_url"><?php esc_html_e('CDN URL', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="cdn[url]" id="cdn_url" value="<?php echo esc_attr($settings['url']); ?>" class="regular-text" placeholder="https://cdn.example.com">
                        <p class="description"><?php esc_html_e('Inserisci l\'URL completo del tuo CDN (es: https://cdn.example.com)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cdn_provider"><?php esc_html_e('Provider CDN', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <select name="cdn[provider]" id="cdn_provider">
                            <option value="custom" <?php selected($settings['provider'], 'custom'); ?>><?php esc_html_e('Custom', 'fp-performance-suite'); ?></option>
                            <option value="cloudflare" <?php selected($settings['provider'], 'cloudflare'); ?>>CloudFlare</option>
                            <option value="bunnycdn" <?php selected($settings['provider'], 'bunnycdn'); ?>>BunnyCDN</option>
                            <option value="stackpath" <?php selected($settings['provider'], 'stackpath'); ?>>StackPath</option>
                            <option value="cloudfront" <?php selected($settings['provider'], 'cloudfront'); ?>>Amazon CloudFront</option>
                            <option value="keycdn" <?php selected($settings['provider'], 'keycdn'); ?>>KeyCDN</option>
                        </select>
                        <p class="description"><?php esc_html_e('Seleziona il tuo provider CDN per ottimizzazioni specifiche.', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Benefici CDN:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('Riduzione del 40-70% dei tempi di caricamento per utenti geograficamente distanti', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Riduzione del carico sul server origin', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Maggiore disponibilità e ridondanza', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Protezione da picchi di traffico e DDoS', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
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
            <p><?php esc_html_e('Abilita la compressione Brotli e Gzip per ridurre le dimensioni dei file trasferiti del 60-80%, migliorando significativamente i tempi di caricamento.', 'fp-performance-suite'); ?></p>
            
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
                    <?php esc_html_e('📋 Dettagli Tecnici', 'fp-performance-suite'); ?>
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
            <p><?php esc_html_e('Imposta soglie di performance e ricevi avvisi quando vengono superate. Aiuta a mantenere il sito veloce nel tempo prevenendo regressioni di performance.', 'fp-performance-suite'); ?></p>
            
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

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        // Verifica permessi utente
        if (!current_user_can($this->capability())) {
            $this->redirectWithError(__('Permesso negato. Non hai i permessi necessari per salvare queste impostazioni.', 'fp-performance-suite'));
            return;
        }

        // Verifica nonce di sicurezza
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'fp_ps_infrastructure')) {
            $this->redirectWithError(__('Errore di sicurezza: nonce non valido o scaduto. Riprova a ricaricare la pagina.', 'fp-performance-suite'));
            return;
        }

        try {
            // Save CDN settings
            $this->saveCdnSettings();
            
            // Save Compression settings
            $this->saveCompressionSettings();
            
            // Save Performance Budget settings
            $this->savePerformanceBudgetSettings();

            // Redirect con successo
            $redirect_url = add_query_arg('updated', '1', admin_url('admin.php?page=' . $this->slug()));
            wp_safe_redirect($redirect_url);
            exit;

        } catch (\Throwable $e) {
            // Log dell'errore
            error_log(sprintf(
                '[FP Performance Suite] Errore durante il salvataggio infrastruttura: %s in %s:%d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            
            // Redirect con errore
            $this->redirectWithError($e->getMessage());
        }
    }

    /**
     * Redirect con messaggio di errore
     */
    private function redirectWithError(string $message): void
    {
        $redirect_url = add_query_arg(
            ['error' => '1', 'message' => urlencode($message)],
            admin_url('admin.php?page=' . $this->slug())
        );
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Salva le impostazioni CDN
     */
    private function saveCdnSettings(): void
    {
        try {
            $cdn = new CdnManager();
            $currentCdn = $cdn->settings();
            
            // Merge con i valori correnti per preservare campi non presenti nel form
            $cdnSettings = array_merge($currentCdn, [
                'enabled' => isset($_POST['cdn']['enabled']),
                'url' => sanitize_text_field($_POST['cdn']['url'] ?? ''),
                'provider' => sanitize_text_field($_POST['cdn']['provider'] ?? 'custom'),
            ]);
            
            $cdn->update($cdnSettings);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio CDN: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni CDN.', 'fp-performance-suite'));
        }
    }

    /**
     * Salva le impostazioni di compressione
     */
    private function saveCompressionSettings(): void
    {
        try {
            $compression = $this->container->get(CompressionManager::class);
            $enabled = isset($_POST['compression']['enabled']);
            
            if ($enabled) {
                $compression->enable();
            } else {
                $compression->disable();
            }
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio Compression: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni di compressione.', 'fp-performance-suite'));
        }
    }

    /**
     * Salva le impostazioni del Performance Budget
     */
    private function savePerformanceBudgetSettings(): void
    {
        try {
            $budgetData = [
                'enabled' => isset($_POST['perf_budget']['enabled']),
                'score_threshold' => isset($_POST['perf_budget']['score_threshold']) 
                    ? (int)$_POST['perf_budget']['score_threshold'] 
                    : 80,
                'load_time_threshold' => isset($_POST['perf_budget']['load_time_threshold']) 
                    ? (int)$_POST['perf_budget']['load_time_threshold'] 
                    : 3000,
                'fcp_threshold' => isset($_POST['perf_budget']['fcp_threshold']) 
                    ? (int)$_POST['perf_budget']['fcp_threshold'] 
                    : 1800,
                'lcp_threshold' => isset($_POST['perf_budget']['lcp_threshold']) 
                    ? (int)$_POST['perf_budget']['lcp_threshold'] 
                    : 2500,
                'cls_threshold' => isset($_POST['perf_budget']['cls_threshold']) 
                    ? (float)$_POST['perf_budget']['cls_threshold'] 
                    : 0.1,
                'alert_email' => isset($_POST['perf_budget']['alert_email']) 
                    ? sanitize_email($_POST['perf_budget']['alert_email']) 
                    : get_option('admin_email'),
                'alert_on_exceed' => isset($_POST['perf_budget']['alert_on_exceed']),
            ];
            
            update_option('fp_ps_performance_budget', $budgetData);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio Performance Budget: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni del Performance Budget.', 'fp-performance-suite'));
        }
    }
}

