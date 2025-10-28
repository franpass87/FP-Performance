<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Logs\DebugToggler;

use function __;
use function checked;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function wp_create_nonce;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Logs extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-logs';
    }

    public function title(): string
    {
        return __('Realtime Log Center', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Monitoring', 'fp-performance-suite'), __('Logs', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $toggler = $this->container->get(DebugToggler::class);
        $status = $toggler->status();
        $message = '';
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_logs_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_logs_nonce']), 'fp-ps-logs')) {
            if (isset($_POST['toggle_debug'])) {
                $settings = [
                    'WP_DEBUG' => !empty($_POST['wp_debug']),
                    'WP_DEBUG_LOG' => !empty($_POST['wp_debug_log']),
                    'WP_DEBUG_DISPLAY' => !empty($_POST['wp_debug_display']),
                    'SCRIPT_DEBUG' => !empty($_POST['script_debug']),
                    'SAVEQUERIES' => !empty($_POST['savequeries']),
                ];
                if ($toggler->updateSettings($settings)) {
                    $message = __('Debug configuration updated.', 'fp-performance-suite');
                    $status = $toggler->status();
                } else {
                    $message = __('Unable to update wp-config.php. Check file permissions.', 'fp-performance-suite');
                }
            }
            if (isset($_POST['revert_debug'])) {
                if ($toggler->revertLatest()) {
                    $message = __('wp-config.php reverted from backup.', 'fp-performance-suite');
                    $status = $toggler->status();
                } else {
                    $message = __('No backup available to revert.', 'fp-performance-suite');
                }
            }
        }
        $nonce = wp_create_nonce('wp_rest');
        ob_start();
        ?>
        
        <!-- INTRO BOX -->
        <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                📝 <?php esc_html_e('Logs & Debug', 'fp-performance-suite'); ?>
            </h2>
            <p style="margin: 0; font-size: 16px; line-height: 1.6; opacity: 0.95;">
                <?php esc_html_e('Gestisci i log di debug WordPress, visualizza errori PHP in tempo reale e attiva/disattiva WP_DEBUG in sicurezza.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <?php if ($message) : ?>
            <div class="notice notice-info"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-grid two">
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Debug Toggles', 'fp-performance-suite'); ?></h2>
                <form method="post">
                    <?php wp_nonce_field('fp-ps-logs', 'fp_ps_logs_nonce'); ?>
                    <input type="hidden" name="toggle_debug" value="1" />
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Enable WP_DEBUG', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator red">
                                <div class="fp-ps-risk-tooltip red">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">🔴</span>
                                        <?php esc_html_e('Rischio Alto', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Attiva la modalità debug di WordPress, mostrando errori, warning e notice.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Può esporre informazioni sensibili e rallentare significativamente il sito in produzione.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚠️ Pericoloso in produzione: Usa solo in staging/sviluppo. Disattiva immediatamente dopo il debug.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Master switch for WordPress debugging', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="wp_debug" value="1" <?php checked($status['WP_DEBUG']); ?> data-risk="red" />
                    </label>
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Enable WP_DEBUG_LOG', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator amber">
                                <div class="fp-ps-risk-tooltip amber">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">⚠</span>
                                        <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Salva tutti gli errori nel file debug.log senza mostrarli agli utenti.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Il file di log può crescere rapidamente e occupare spazio disco. Leggero impatto sulle performance.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Accettabile in produzione: Meglio di WP_DEBUG_DISPLAY. Monitora la dimensione del log e puliscilo regolarmente.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Save errors to debug.log file', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="wp_debug_log" value="1" <?php checked($status['WP_DEBUG_LOG']); ?> data-risk="amber" />
                    </label>
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Enable WP_DEBUG_DISPLAY', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator amber">
                                <div class="fp-ps-risk-tooltip amber">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">⚠</span>
                                        <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Mostra gli errori direttamente nel browser agli utenti.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Espone informazioni sensibili del server agli utenti. Rovina l\'esperienza utente.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ Sconsigliato in produzione: Mai in produzione! Usa solo in sviluppo locale. Usa WP_DEBUG_LOG invece.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Display errors in browser (not recommended for production)', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="wp_debug_display" value="1" <?php checked($status['WP_DEBUG_DISPLAY']); ?> data-risk="amber" />
                    </label>
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Enable SCRIPT_DEBUG', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">✓</span>
                                        <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Carica le versioni non-minified di CSS e JS di WordPress per facilitare il debug.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Leggero rallentamento del caricamento per file più grandi. Nessun rischio di sicurezza.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ OK per debug temporaneo: Utile per debugging di problemi JS/CSS. Disattiva quando non serve.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Use non-minified versions of core CSS and JS', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="script_debug" value="1" <?php checked($status['SCRIPT_DEBUG']); ?> data-risk="green" />
                    </label>
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Enable SAVEQUERIES', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator amber">
                                <div class="fp-ps-risk-tooltip amber">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">⚠</span>
                                        <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Salva tutte le query SQL per analisi delle performance del database.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Impatto significativo sulla memoria e performance. Può causare out-of-memory su siti grandi.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚠️ Solo per debug specifico: Attiva solo per brevi periodi quando devi ottimizzare query. Mai lasciare attivo permanentemente.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                            <small><?php esc_html_e('Save database queries for analysis (impacts performance)', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="savequeries" value="1" <?php checked($status['SAVEQUERIES']); ?> data-risk="amber" />
                    </label>
                    <p>
                        <button type="submit" class="button button-primary" data-risk="red"><?php esc_html_e('Save Debug Settings', 'fp-performance-suite'); ?></button>
                    </p>
                </form>
                <form method="post" style="margin-top:1em;">
                    <?php wp_nonce_field('fp-ps-logs', 'fp_ps_logs_nonce'); ?>
                    <input type="hidden" name="revert_debug" value="1" />
                    <button type="submit" class="button" data-risk="red"><?php esc_html_e('Revert to Backup', 'fp-performance-suite'); ?></button>
                </form>
                <?php if (!empty($status['log_file'])) : ?>
                    <p><?php esc_html_e('Log file:', 'fp-performance-suite'); ?> <code><?php echo esc_html($status['log_file']); ?></code></p>
                <?php endif; ?>
            </div>
            <div class="fp-ps-card">
                <h2><?php esc_html_e('Realtime Viewer', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Filtered tail of debug.log with live updates every 2 seconds.', 'fp-performance-suite'); ?></p>
                <div class="fp-ps-actions">
                    <label><?php esc_html_e('Level', 'fp-performance-suite'); ?>
                        <select data-fp-log-filter>
                            <option value=""><?php esc_html_e('All', 'fp-performance-suite'); ?></option>
                            <option value="notice"><?php esc_html_e('Notice', 'fp-performance-suite'); ?></option>
                            <option value="warning"><?php esc_html_e('Warning', 'fp-performance-suite'); ?></option>
                            <option value="error"><?php esc_html_e('Error', 'fp-performance-suite'); ?></option>
                        </select>
                    </label>
                    <label><?php esc_html_e('Search', 'fp-performance-suite'); ?>
                        <input type="search" data-fp-log-search placeholder="<?php esc_attr_e('Keyword', 'fp-performance-suite'); ?>" />
                    </label>
                </div>
                <pre class="fp-ps-log-viewer" data-fp-logs data-nonce="<?php echo esc_attr($nonce); ?>" data-lines="200"></pre>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
