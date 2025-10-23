<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\CDN\CdnManager;

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
use function admin_url;
use function add_query_arg;

/**
 * CDN Page
 * 
 * Gestisce la configurazione del Content Delivery Network:
 * - CDN Integration
 * - URL Rewriting
 * - Provider Configuration
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Cdn extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-cdn';
    }

    public function title(): string
    {
        return __('CDN', 'fp-performance-suite');
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
                __('CDN', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        // Handle form submission
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce'])) {
            $message = $this->handleSave();
        }

        // Check for messages from URL (from admin_post handlers)
        if (isset($_GET['message'])) {
            $message = urldecode($_GET['message']);
        }
        
        // Check for legacy success message from URL
        if (isset($_GET['updated']) && $_GET['updated'] === '1') {
            $message = __('Impostazioni CDN salvate con successo!', 'fp-performance-suite');
        }

        // Check for legacy error message from URL
        if (isset($_GET['error']) && $_GET['error'] === '1') {
            $message = isset($_GET['message']) 
                ? urldecode($_GET['message']) 
                : __('Si è verificato un errore durante il salvataggio.', 'fp-performance-suite');
        }

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
                <strong>🌐 <?php esc_html_e('Content Delivery Network', 'fp-performance-suite'); ?></strong><br>
                <?php esc_html_e('Configura un CDN per distribuire i tuoi asset da server geograficamente più vicini ai tuoi utenti, migliorando drasticamente i tempi di caricamento.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('fp_ps_cdn', '_wpnonce'); ?>
            <input type="hidden" name="action" value="fp_ps_save_cdn">
            
            <!-- CDN Section -->
            <?php echo $this->renderCdnSection(); ?>
            
            <!-- Save Button -->
            <div class="fp-ps-card">
                <div class="fp-ps-actions">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Salva Impostazioni CDN', 'fp-performance-suite'); ?>
                    </button>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e('Salva tutte le modifiche apportate alle impostazioni CDN.', 'fp-performance-suite'); ?>
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
                <tr>
                    <th scope="row">
                        <label for="cdn_included_extensions"><?php esc_html_e('Estensioni File da Servire', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="cdn[included_extensions]" id="cdn_included_extensions" value="<?php echo esc_attr($settings['included_extensions'] ?? 'jpg,jpeg,png,gif,webp,css,js,svg,woff,woff2,ttf,eot'); ?>" class="large-text">
                        <p class="description"><?php esc_html_e('Estensioni dei file da servire tramite CDN (separati da virgola)', 'fp-performance-suite'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="cdn_excluded_paths"><?php esc_html_e('Percorsi Esclusi', 'fp-performance-suite'); ?></label>
                    </th>
                    <td>
                        <textarea name="cdn[excluded_paths]" id="cdn_excluded_paths" rows="3" class="large-text"><?php echo esc_textarea($settings['excluded_paths'] ?? ''); ?></textarea>
                        <p class="description"><?php esc_html_e('Percorsi da escludere dal CDN (uno per riga, es: /wp-admin/, /checkout/)', 'fp-performance-suite'); ?></p>
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
                    <li><?php esc_html_e('Caching automatico degli asset statici', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('⚙️ Configurazione CDN:', 'fp-performance-suite'); ?></p>
                <ol style="margin: 10px 0 0 20px; color: #856404;">
                    <li><?php esc_html_e('Configura il tuo CDN per puntare al tuo sito come origin', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Inserisci l\'URL del CDN nel campo sopra', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Abilita il CDN e testa il funzionamento', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Configura cache headers e purge rules sul tuo provider CDN', 'fp-performance-suite'); ?></li>
                </ol>
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
    public function handleSave(): string
    {
        // Verifica permessi utente
        if (!current_user_can($this->capability())) {
            return __('Permesso negato. Non hai i permessi necessari per salvare queste impostazioni.', 'fp-performance-suite');
        }

        // Verifica nonce di sicurezza
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'fp_ps_cdn')) {
            return __('Errore di sicurezza: nonce non valido o scaduto. Riprova a ricaricare la pagina.', 'fp-performance-suite');
        }

        try {
            // Save CDN settings
            $this->saveCdnSettings();

            return __('CDN settings saved successfully!', 'fp-performance-suite');

        } catch (\Throwable $e) {
            // Log dell'errore
            error_log(sprintf(
                '[FP Performance Suite] Errore durante il salvataggio CDN: %s in %s:%d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            
            return sprintf(
                __('Error saving CDN settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
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
                'included_extensions' => sanitize_text_field($_POST['cdn']['included_extensions'] ?? 'jpg,jpeg,png,gif,webp,css,js,svg,woff,woff2,ttf,eot'),
                'excluded_paths' => sanitize_textarea_field($_POST['cdn']['excluded_paths'] ?? ''),
            ]);
            
            $cdn->update($cdnSettings);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Errore nel salvataggio CDN: ' . $e->getMessage());
            throw new \Exception(__('Errore nel salvataggio delle impostazioni CDN.', 'fp-performance-suite'));
        }
    }
}

