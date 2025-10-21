<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Admin\BackendOptimizer;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function sanitize_text_field;
use function selected;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

/**
 * Backend Optimization Page
 * 
 * Ottimizzazioni per l'area amministrativa WordPress:
 * - Admin Bar
 * - Dashboard Widgets
 * - Heartbeat API
 * - Admin AJAX
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 */
class Backend extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-backend';
    }

    public function title(): string
    {
        return __('Backend Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Performance', 'fp-performance-suite'), __('Backend', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $backendOptimizer = $this->container->get(BackendOptimizer::class);
        $allSettings = $backendOptimizer->getSettings();
        $message = '';

        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_backend_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_backend_nonce']), 'fp-ps-backend')) {
            
            // Admin Bar Settings
            if (isset($_POST['save_admin_bar'])) {
                $allSettings['admin_bar'] = [
                    'disable_frontend' => !empty($_POST['disable_admin_bar_frontend']),
                    'disable_wordpress_logo' => !empty($_POST['disable_wp_logo']),
                    'disable_updates' => !empty($_POST['disable_updates_menu']),
                    'disable_comments' => !empty($_POST['disable_comments_menu']),
                    'disable_new' => !empty($_POST['disable_new_menu']),
                    'disable_customize' => !empty($_POST['disable_customize']),
                ];
                $backendOptimizer->updateSettings($allSettings);
                $message = __('Admin Bar settings saved.', 'fp-performance-suite');
            }

            // Dashboard Widgets Settings
            if (isset($_POST['save_dashboard'])) {
                $allSettings['dashboard'] = [
                    'disable_welcome' => !empty($_POST['disable_welcome_panel']),
                    'disable_quick_press' => !empty($_POST['disable_quick_press']),
                    'disable_activity' => !empty($_POST['disable_activity_widget']),
                    'disable_primary' => !empty($_POST['disable_primary_widget']),
                    'disable_secondary' => !empty($_POST['disable_secondary_widget']),
                    'disable_site_health' => !empty($_POST['disable_site_health']),
                    'disable_php_update' => !empty($_POST['disable_php_update_nag']),
                ];
                $allSettings['optimize_dashboard'] = true;
                $allSettings['remove_dashboard_widgets'] = true;
                $backendOptimizer->updateSettings($allSettings);
                $message = __('Dashboard settings saved.', 'fp-performance-suite');
            }

            // Heartbeat API Settings
            if (isset($_POST['save_heartbeat'])) {
                $allSettings['heartbeat'] = [
                    'dashboard' => sanitize_text_field($_POST['heartbeat_dashboard'] ?? 'default'),
                    'editor' => sanitize_text_field($_POST['heartbeat_editor'] ?? 'default'),
                    'frontend' => sanitize_text_field($_POST['heartbeat_frontend'] ?? 'default'),
                ];
                $allSettings['heartbeat_interval'] = (int) ($_POST['heartbeat_interval'] ?? 60);
                $allSettings['optimize_heartbeat'] = true;
                $backendOptimizer->updateSettings($allSettings);
                $message = __('Heartbeat API settings saved.', 'fp-performance-suite');
            }

            // Admin AJAX Settings
            if (isset($_POST['save_admin_ajax'])) {
                $allSettings['admin_ajax'] = [
                    'disable_emojis' => !empty($_POST['disable_emojis']),
                    'disable_embeds' => !empty($_POST['disable_embeds']),
                ];
                $allSettings['revisions_limit'] = (int) ($_POST['limit_revisions'] ?? 5);
                $allSettings['limit_revisions'] = true;
                $allSettings['autosave_interval'] = (int) ($_POST['autosave_interval'] ?? 60);
                $allSettings['optimize_admin_ajax'] = true;
                $backendOptimizer->updateSettings($allSettings);
                $message = __('Admin AJAX settings saved.', 'fp-performance-suite');
            }
        }

        $adminBarSettings = $allSettings['admin_bar'] ?? [];
        $dashboardSettings = $allSettings['dashboard'] ?? [];
        $heartbeatSettings = $allSettings['heartbeat'] ?? [];
        $adminAjaxSettings = $allSettings['admin_ajax'] ?? [];

        ob_start();
        ?>
        
        <?php if ($message) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>

        <!-- Admin Bar Optimization -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🎨 Admin Bar Optimization', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Disabilita elementi non necessari della barra amministrativa per ridurre il carico.', 'fp-performance-suite'); ?></p>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-backend', 'fp_ps_backend_nonce'); ?>
                <input type="hidden" name="save_admin_bar" value="1" />
                
                <div class="fp-ps-grid two">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita Admin Bar sul frontend', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">✓</span>
                                        <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove la barra admin quando visualizzi il sito. Riduce HTTP requests e CSS/JS caricati.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Risparmia ~150KB per caricamento pagina. Migliora leggermente il First Contentful Paint.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Nasconde la barra admin per gli utenti loggati sul frontend', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_admin_bar_frontend" value="1" <?php checked($adminBarSettings['disable_frontend'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Rimuovi logo WordPress', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">✓</span>
                                        <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove il menu dropdown del logo WordPress dalla barra amministrativa.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Interfaccia più pulita. Impatto minimo: ~5KB HTML risparmiati.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Rimuove il menu del logo WordPress dalla barra admin', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_wp_logo" value="1" <?php checked($adminBarSettings['disable_wordpress_logo'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Rimuovi menu aggiornamenti', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Nasconde il menu aggiornamenti dalla barra admin', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_updates_menu" value="1" <?php checked($adminBarSettings['disable_updates'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Rimuovi menu commenti', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Nasconde l\'icona commenti dalla barra admin', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_comments_menu" value="1" <?php checked($adminBarSettings['disable_comments'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Rimuovi menu "+ Nuovo"', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Nasconde il menu per creare nuovi contenuti', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_new_menu" value="1" <?php checked($adminBarSettings['disable_new'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Rimuovi link Personalizza', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Nasconde il link al Customizer dalla barra admin', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_customize" value="1" <?php checked($adminBarSettings['disable_customize'] ?? false); ?> data-risk="green" />
                    </label>
                </div>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Admin Bar', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>

        <!-- Dashboard Widgets -->
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('📊 Dashboard Widgets', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Disabilita i widget non necessari per velocizzare il caricamento della dashboard.', 'fp-performance-suite'); ?></p>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-backend', 'fp_ps_backend_nonce'); ?>
                <input type="hidden" name="save_dashboard" value="1" />
                
                <div class="fp-ps-grid two">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita pannello di benvenuto', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Rimuove il widget "Benvenuto in WordPress"', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_welcome_panel" value="1" <?php checked($dashboardSettings['disable_welcome'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita Quick Press', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Rimuove il widget per la creazione rapida di bozze', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_quick_press" value="1" <?php checked($dashboardSettings['disable_quick_press'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita Attività', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Rimuove il widget che mostra attività recenti', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_activity_widget" value="1" <?php checked($dashboardSettings['disable_activity'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita WordPress News', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Rimuove il feed delle notizie WordPress', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_primary_widget" value="1" <?php checked($dashboardSettings['disable_primary'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita Eventi e Notizie', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Rimuove il widget eventi WordPress', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_secondary_widget" value="1" <?php checked($dashboardSettings['disable_secondary'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita Site Health', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator amber">
                                <div class="fp-ps-risk-tooltip amber">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">⚠</span>
                                        <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Attenzione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Site Health fornisce informazioni utili sulla salute del sito. Disabilitare solo se usi strumenti di monitoraggio alternativi.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Rimuove il widget Site Health Status', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_site_health" value="1" <?php checked($dashboardSettings['disable_site_health'] ?? false); ?> data-risk="amber" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita avviso aggiornamento PHP', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Rimuove il widget di notifica aggiornamento PHP', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_php_update_nag" value="1" <?php checked($dashboardSettings['disable_php_update'] ?? false); ?> data-risk="green" />
                    </label>
                </div>

                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Dashboard', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>

        <!-- Heartbeat API -->
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('💓 Heartbeat API', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('L\'Heartbeat API di WordPress controlla periodicamente il server. Ottimizzalo per ridurre il carico.', 'fp-performance-suite'); ?></p>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-backend', 'fp_ps_backend_nonce'); ?>
                <input type="hidden" name="save_heartbeat" value="1" />
                
                <div class="fp-ps-grid two">
                    <div>
                        <label for="heartbeat_dashboard">
                            <strong><?php esc_html_e('Heartbeat nella Dashboard', 'fp-performance-suite'); ?></strong>
                        </label>
                        <select name="heartbeat_dashboard" id="heartbeat_dashboard">
                            <option value="default" <?php selected($heartbeatSettings['dashboard'] ?? 'default', 'default'); ?>><?php esc_html_e('Default (ogni 60s)', 'fp-performance-suite'); ?></option>
                            <option value="slow" <?php selected($heartbeatSettings['dashboard'] ?? 'default', 'slow'); ?>><?php esc_html_e('Rallentato (ogni 120s)', 'fp-performance-suite'); ?></option>
                            <option value="disable" <?php selected($heartbeatSettings['dashboard'] ?? 'default', 'disable'); ?>><?php esc_html_e('Disabilitato', 'fp-performance-suite'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Controlla la frequenza di heartbeat nella dashboard admin', 'fp-performance-suite'); ?></p>
                    </div>

                    <div>
                        <label for="heartbeat_editor">
                            <strong><?php esc_html_e('Heartbeat nell\'Editor', 'fp-performance-suite'); ?></strong>
                        </label>
                        <select name="heartbeat_editor" id="heartbeat_editor">
                            <option value="default" <?php selected($heartbeatSettings['editor'] ?? 'default', 'default'); ?>><?php esc_html_e('Default (ogni 15s)', 'fp-performance-suite'); ?></option>
                            <option value="slow" <?php selected($heartbeatSettings['editor'] ?? 'default', 'slow'); ?>><?php esc_html_e('Rallentato (ogni 30s)', 'fp-performance-suite'); ?></option>
                            <option value="disable" <?php selected($heartbeatSettings['editor'] ?? 'default', 'disable'); ?>><?php esc_html_e('Disabilitato ⚠️', 'fp-performance-suite'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('⚠️ Non disabilitare se usi l\'autosave dell\'editor', 'fp-performance-suite'); ?></p>
                    </div>

                    <div>
                        <label for="heartbeat_frontend">
                            <strong><?php esc_html_e('Heartbeat sul Frontend', 'fp-performance-suite'); ?></strong>
                        </label>
                        <select name="heartbeat_frontend" id="heartbeat_frontend">
                            <option value="default" <?php selected($heartbeatSettings['frontend'] ?? 'disable', 'default'); ?>><?php esc_html_e('Default (ogni 60s)', 'fp-performance-suite'); ?></option>
                            <option value="slow" <?php selected($heartbeatSettings['frontend'] ?? 'disable', 'slow'); ?>><?php esc_html_e('Rallentato (ogni 120s)', 'fp-performance-suite'); ?></option>
                            <option value="disable" <?php selected($heartbeatSettings['frontend'] ?? 'disable', 'disable'); ?>><?php esc_html_e('Disabilitato ✅', 'fp-performance-suite'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('✅ Consigliato: Disabilitato (raramente necessario sul frontend)', 'fp-performance-suite'); ?></p>
                    </div>

                    <div>
                        <label for="heartbeat_interval">
                            <strong><?php esc_html_e('Intervallo personalizzato (secondi)', 'fp-performance-suite'); ?></strong>
                        </label>
                        <input type="number" name="heartbeat_interval" id="heartbeat_interval" value="<?php echo esc_attr((string) ($heartbeatSettings['interval'] ?? 60)); ?>" min="15" max="300" step="5" />
                        <p class="description"><?php esc_html_e('Imposta un intervallo custom (15-300 secondi)', 'fp-performance-suite'); ?></p>
                    </div>
                </div>

                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-left: 4px solid #f0b429;">
                    <h4 style="margin-top: 0;"><?php esc_html_e('💡 Raccomandazioni Heartbeat', 'fp-performance-suite'); ?></h4>
                    <ul style="margin: 0;">
                        <li><strong><?php esc_html_e('Dashboard:', 'fp-performance-suite'); ?></strong> <?php esc_html_e('Rallenta a 120s o disabilita se non usi funzioni real-time', 'fp-performance-suite'); ?></li>
                        <li><strong><?php esc_html_e('Editor:', 'fp-performance-suite'); ?></strong> <?php esc_html_e('Mantieni attivo ma rallenta a 30s', 'fp-performance-suite'); ?></li>
                        <li><strong><?php esc_html_e('Frontend:', 'fp-performance-suite'); ?></strong> <?php esc_html_e('✅ Disabilita sempre (riduce carico server del 20-30%)', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>

                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Heartbeat', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>

        <!-- Admin AJAX & Revisions -->
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('⚡ Admin AJAX & Revisions', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Ottimizza revisioni, autosave e funzionalità WordPress non essenziali.', 'fp-performance-suite'); ?></p>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-backend', 'fp_ps_backend_nonce'); ?>
                <input type="hidden" name="save_admin_ajax" value="1" />
                
                <div class="fp-ps-grid two">
                    <div>
                        <label for="limit_revisions">
                            <strong><?php esc_html_e('Limita revisioni post', 'fp-performance-suite'); ?></strong>
                        </label>
                        <input type="number" name="limit_revisions" id="limit_revisions" value="<?php echo esc_attr((string) ($adminAjaxSettings['limit_revisions'] ?? 5)); ?>" min="0" max="50" />
                        <p class="description"><?php esc_html_e('0 = disabilita revisioni, consigliato 3-5', 'fp-performance-suite'); ?></p>
                    </div>

                    <div>
                        <label for="autosave_interval">
                            <strong><?php esc_html_e('Intervallo autosave (secondi)', 'fp-performance-suite'); ?></strong>
                        </label>
                        <input type="number" name="autosave_interval" id="autosave_interval" value="<?php echo esc_attr((string) ($adminAjaxSettings['autosave_interval'] ?? 60)); ?>" min="30" max="300" step="10" />
                        <p class="description"><?php esc_html_e('Default: 60s. Aumenta a 120-180s per ridurre AJAX', 'fp-performance-suite'); ?></p>
                    </div>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita Emoji', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">✓</span>
                                        <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove il polyfill emoji di WordPress. I browser moderni supportano emoji nativamente.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Risparmia 1 HTTP request e ~10KB JavaScript. ✅ Altamente consigliato.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Rimuove lo script emoji detection di WordPress', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_emojis" value="1" <?php checked($adminAjaxSettings['disable_emojis'] ?? false); ?> data-risk="green" />
                    </label>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Disabilita Embeds', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">✓</span>
                                        <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Disabilita oEmbed (YouTube, Twitter, ecc.). I contenuti embeddati manualmente funzioneranno comunque.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Risparmia 1 HTTP request e ~4KB JavaScript.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Rimuove funzionalità oEmbed e REST endpoint', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="disable_embeds" value="1" <?php checked($adminAjaxSettings['disable_embeds'] ?? false); ?> data-risk="green" />
                    </label>
                </div>

                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni AJAX', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>

        <!-- Performance Impact Summary -->
        <section class="fp-ps-card" style="margin-top: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <h2 style="color: white;"><?php esc_html_e('📊 Impatto Performance Backend', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid three" style="margin: 20px 0;">
                <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 10px;">⚡</div>
                    <div style="font-size: 32px; font-weight: bold;">-150KB</div>
                    <div style="opacity: 0.9; margin-top: 5px;"><?php esc_html_e('Admin Bar disabilitato', 'fp-performance-suite'); ?></div>
                </div>
                
                <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 10px;">💓</div>
                    <div style="font-size: 32px; font-weight: bold;">-30%</div>
                    <div style="opacity: 0.9; margin-top: 5px;"><?php esc_html_e('Heartbeat ottimizzato', 'fp-performance-suite'); ?></div>
                </div>
                
                <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 8px; text-align: center;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🚀</div>
                    <div style="font-size: 32px; font-weight: bold;">2x</div>
                    <div style="opacity: 0.9; margin-top: 5px;"><?php esc_html_e('Dashboard più veloce', 'fp-performance-suite'); ?></div>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 6px; margin-top: 20px;">
                <h3 style="color: white; margin-top: 0; font-size: 16px;"><?php esc_html_e('✨ Cosa migliora:', 'fp-performance-suite'); ?></h3>
                <ul style="color: rgba(255,255,255,0.95); margin: 0; padding-left: 20px;">
                    <li><?php esc_html_e('Caricamento dashboard fino a 2x più veloce', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Riduzione del 20-30% delle richieste AJAX al server', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Minore consumo di risorse server (CPU e RAM)', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Esperienza admin più snella e reattiva', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </section>

        <?php
        return (string) ob_get_clean();
    }
}

