<?php

namespace FP\PerfSuite\Admin;

use FP\PerfSuite\Admin\Pages\Advanced;
use FP\PerfSuite\Admin\Pages\AIConfig;
use FP\PerfSuite\Admin\Pages\Assets;
use FP\PerfSuite\Admin\Pages\Backend;
use FP\PerfSuite\Admin\Pages\Cache;
use FP\PerfSuite\Admin\Pages\Database;
use FP\PerfSuite\Admin\Pages\Diagnostics;
use FP\PerfSuite\Admin\Pages\Exclusions;
use FP\PerfSuite\Admin\Pages\Logs;
use FP\PerfSuite\Admin\Pages\Media;
use FP\PerfSuite\Admin\Pages\Overview;
use FP\PerfSuite\Admin\Pages\Security;
use FP\PerfSuite\Admin\Pages\Settings;
use FP\PerfSuite\Admin\Pages\Tools;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Capabilities;

use function add_action;
use function add_menu_page;
use function add_submenu_page;
use function __;
use function get_option;
use function delete_option;
use function current_user_can;
use function wp_verify_nonce;
use function wp_create_nonce;
use function wp_send_json_error;
use function wp_send_json_success;
use function esc_html;

class Menu
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    public function boot(): void
    {
        add_action('admin_menu', [$this, 'register']);
        add_action('admin_notices', [$this, 'showActivationErrors']);
        add_action('wp_ajax_fp_ps_dismiss_activation_error', [$this, 'dismissActivationError']);
        add_action('wp_ajax_fp_ps_dismiss_salient_notice', [$this, 'dismissSalientNotice']);
        
        // Registra gli hook admin_post per il salvataggio delle impostazioni
        // Questi devono essere registrati presto, non solo quando le pagine vengono istanziate
        add_action('admin_post_fp_ps_save_advanced', [$this, 'handleAdvancedSave']);
        add_action('admin_post_fp_ps_export_csv', [$this, 'handleOverviewExportCsv']);
    }

    /**
     * Mostra eventuali errori di attivazione nell'area admin
     */
    public function showActivationErrors(): void
    {
        $error = get_option('fp_perfsuite_activation_error');
        
        if (!is_array($error) || empty($error)) {
            return;
        }

        // Mostra il notice solo agli amministratori
        if (!current_user_can('manage_options')) {
            return;
        }

        $errorMessage = esc_html($error['message'] ?? 'Errore sconosciuto');
        $errorType = $error['type'] ?? 'unknown';
        $solution = $error['solution'] ?? 'Contatta il supporto.';
        $phpVersion = $error['php_version'] ?? PHP_VERSION;
        $wpVersion = $error['wp_version'] ?? get_bloginfo('version');

        // Determina l'icona e il colore in base al tipo di errore
        $noticeClass = 'notice-error';
        $icon = '❌';
        
        if (in_array($errorType, ['php_version', 'php_extension'], true)) {
            $icon = '⚠️';
        } elseif ($errorType === 'permissions') {
            $icon = '🔒';
            $noticeClass = 'notice-warning';
        }

        ?>
        <div class="notice <?php echo esc_attr($noticeClass); ?> is-dismissible fp-ps-activation-error" style="border-left-width: 4px; padding: 12px;">
            <h3 style="margin-top: 0;">
                <?php echo $icon; ?> 
                <?php _e('FP Performance Suite: Errore Critico all\'Installazione', 'fp-performance-suite'); ?>
            </h3>
            
            <p style="font-size: 14px; margin: 10px 0;">
                <strong><?php _e('Errore:', 'fp-performance-suite'); ?></strong> 
                <?php echo $errorMessage; ?>
            </p>

            <?php if (!empty($solution)): ?>
            <div style="background: #fff; border-left: 3px solid #00a0d2; padding: 10px; margin: 10px 0;">
                <p style="margin: 0;">
                    <strong>💡 <?php _e('Soluzione:', 'fp-performance-suite'); ?></strong><br>
                    <?php echo esc_html($solution); ?>
                </p>
            </div>
            <?php endif; ?>

            <details style="margin-top: 10px;">
                <summary style="cursor: pointer; color: #2271b1;">
                    <?php _e('Dettagli tecnici (clicca per espandere)', 'fp-performance-suite'); ?>
                </summary>
                <div style="background: #f0f0f1; padding: 10px; margin-top: 10px; font-family: monospace; font-size: 12px;">
                    <p><strong><?php _e('Versione PHP:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($phpVersion); ?></p>
                    <p><strong><?php _e('Versione WordPress:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($wpVersion); ?></p>
                    <?php if (!empty($error['file'])): ?>
                    <p><strong><?php _e('File:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($error['file']); ?></p>
                    <p><strong><?php _e('Linea:', 'fp-performance-suite'); ?></strong> <?php echo esc_html($error['line'] ?? 'N/A'); ?></p>
                    <?php endif; ?>
                    <p><strong><?php _e('Data:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(date('Y-m-d H:i:s', $error['time'] ?? time())); ?></p>
                </div>
            </details>

            <?php if (!empty($error['recovery_attempted'])): ?>
            <div style="background: <?php echo $error['recovery_successful'] ? '#d4edda' : '#f8d7da'; ?>; border-left: 3px solid <?php echo $error['recovery_successful'] ? '#28a745' : '#dc3545'; ?>; padding: 10px; margin: 10px 0;">
                <p style="margin: 0;">
                    <?php if ($error['recovery_successful']): ?>
                        <strong>✅ <?php _e('Recupero Automatico:', 'fp-performance-suite'); ?></strong><br>
                        <?php _e('È stato tentato un recupero automatico con successo. Prova a disattivare e riattivare il plugin per verificare.', 'fp-performance-suite'); ?>
                    <?php else: ?>
                        <strong>❌ <?php _e('Recupero Automatico:', 'fp-performance-suite'); ?></strong><br>
                        <?php _e('Il tentativo di recupero automatico non ha avuto successo. Segui la soluzione suggerita sopra.', 'fp-performance-suite'); ?>
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <p style="margin-top: 15px;">
                <a href="#" class="button button-primary fp-ps-dismiss-activation-error">
                    <?php _e('Ho risolto il problema - Nascondi questo messaggio', 'fp-performance-suite'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-diagnostics'); ?>" class="button button-secondary" style="margin-left: 10px;">
                    <?php _e('Esegui Diagnostica', 'fp-performance-suite'); ?>
                </a>
                <a href="https://francescopasseri.com/support" class="button" target="_blank" style="margin-left: 10px;">
                    <?php _e('Contatta il Supporto', 'fp-performance-suite'); ?>
                </a>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.fp-ps-dismiss-activation-error').on('click', function(e) {
                e.preventDefault();
                $.post(ajaxurl, {
                    action: 'fp_ps_dismiss_activation_error',
                    nonce: '<?php echo wp_create_nonce('fp_ps_dismiss_error'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('.fp-ps-activation-error').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('<?php _e('Errore durante la dismissione del messaggio.', 'fp-performance-suite'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Dismissione dell'errore di attivazione via AJAX
     */
    public function dismissActivationError(): void
    {
        // Verifica il nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fp_ps_dismiss_error')) {
            wp_send_json_error(['message' => 'Nonce non valido']);
            return;
        }

        // Verifica i permessi
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
            return;
        }

        // Rimuovi l'opzione
        delete_option('fp_perfsuite_activation_error');
        
        wp_send_json_success(['message' => 'Errore dismisso con successo']);
    }
    
    /**
     * Dismissione del notice Salient via AJAX
     */
    public function dismissSalientNotice(): void
    {
        // Verifica il nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fp_ps_dismiss_salient')) {
            wp_send_json_error(['message' => 'Nonce non valido']);
            return;
        }

        // Verifica i permessi
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permessi insufficienti']);
            return;
        }

        // Salva la preferenza per l'utente corrente
        update_user_meta(get_current_user_id(), 'fp_ps_dismiss_salient_notice', true);
        
        wp_send_json_success(['message' => 'Notice dismisso con successo']);
    }

    public function register(): void
    {
        $pages = $this->pages();
        
        // Ottieni la capability richiesta con fallback sicuro
        $capability = Capabilities::required();
        
        // Validazione: assicurati che la capability sia valida
        if (empty($capability) || !is_string($capability)) {
            $capability = 'manage_options';
            error_log('[FP Performance Suite] ATTENZIONE: Capability non valida, uso manage_options come fallback');
        }
        
        // Sistema di auto-riparazione: se l'utente corrente è un admin ma non ha accesso,
        // ripristina automaticamente le impostazioni predefinite
        if (current_user_can('manage_options') && !current_user_can($capability)) {
            error_log('[FP Performance Suite] EMERGENZA: Admin bloccato! Ripristino impostazioni predefinite...');
            
            // Ripristina le impostazioni
            $current_settings = get_option('fp_ps_settings', []);
            $current_settings['allowed_role'] = 'administrator';
            update_option('fp_ps_settings', $current_settings);
            
            // Aggiorna la capability
            $capability = 'manage_options';
            
            // Notifica l'admin
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <strong><?php esc_html_e('FP Performance Suite - Auto-riparazione eseguita', 'fp-performance-suite'); ?></strong><br>
                        <?php esc_html_e('È stato rilevato un problema con i permessi di accesso. Le impostazioni sono state automaticamente ripristinate ai valori predefiniti.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <?php
            });
        }
        
        // Log per debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[FP Performance Suite] Registrazione menu con capability: ' . $capability);
            error_log('[FP Performance Suite] Utente corrente può accedere: ' . (current_user_can($capability) ? 'SI' : 'NO'));
        }

        add_menu_page(
            __('FP Performance Suite', 'fp-performance-suite'),
            __('FP Performance', 'fp-performance-suite'),
            $capability,
            'fp-performance-suite',
            [$pages['overview'], 'render'],
            'dashicons-performance',
            59
        );

        // === SEZIONE PRINCIPALE ===
        add_submenu_page('fp-performance-suite', __('Panoramica', 'fp-performance-suite'), __('📊 Panoramica', 'fp-performance-suite'), $capability, 'fp-performance-suite', [$pages['overview'], 'render']);
        add_submenu_page('fp-performance-suite', __('AI Config', 'fp-performance-suite'), __('🤖 AI Config', 'fp-performance-suite'), $capability, 'fp-performance-suite-ai-config', [$pages['ai_config'], 'render']);
        
        // === OTTIMIZZAZIONE ===
        add_submenu_page('fp-performance-suite', __('Cache', 'fp-performance-suite'), __('🚀 Cache', 'fp-performance-suite'), $capability, 'fp-performance-suite-cache', [$pages['cache'], 'render']);
        add_submenu_page('fp-performance-suite', __('Risorse', 'fp-performance-suite'), __('📦 Risorse', 'fp-performance-suite'), $capability, 'fp-performance-suite-assets', [$pages['assets'], 'render']);
        add_submenu_page('fp-performance-suite', __('Media', 'fp-performance-suite'), __('🖼️ Media', 'fp-performance-suite'), $capability, 'fp-performance-suite-media', [$pages['media'], 'render']);
        add_submenu_page('fp-performance-suite', __('Database', 'fp-performance-suite'), __('💾 Database', 'fp-performance-suite'), $capability, 'fp-performance-suite-database', [$pages['database'], 'render']);
        add_submenu_page('fp-performance-suite', __('Backend', 'fp-performance-suite'), __('⚙️ Backend', 'fp-performance-suite'), $capability, 'fp-performance-suite-backend', [$pages['backend'], 'render']);
        
        // === STRUMENTI ===
        add_submenu_page('fp-performance-suite', __('Strumenti', 'fp-performance-suite'), __('🔧 Strumenti', 'fp-performance-suite'), $capability, 'fp-performance-suite-tools', [$pages['tools'], 'render']);
        add_submenu_page('fp-performance-suite', __('Sicurezza', 'fp-performance-suite'), __('🛡️ Sicurezza', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-security', [$pages['security'], 'render']);
        
        // === INTELLIGENCE ===
        add_submenu_page('fp-performance-suite', __('Esclusioni', 'fp-performance-suite'), __('🧠 Esclusioni', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-exclusions', [$pages['exclusions'], 'render']);
        
        // === MONITORAGGIO ===
        add_submenu_page('fp-performance-suite', __('Registro Attività', 'fp-performance-suite'), __('📝 Registro Attività', 'fp-performance-suite'), $capability, 'fp-performance-suite-logs', [$pages['logs'], 'render']);
        add_submenu_page('fp-performance-suite', __('Diagnostica', 'fp-performance-suite'), __('🔍 Diagnostica', 'fp-performance-suite'), $capability, 'fp-performance-suite-diagnostics', [$pages['diagnostics'], 'render']);
        
        // === CONFIGURAZIONE ===
        add_submenu_page('fp-performance-suite', __('Opzioni Avanzate', 'fp-performance-suite'), __('🔬 Opzioni Avanzate', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-advanced', [$pages['advanced'], 'render']);
        add_submenu_page('fp-performance-suite', __('Impostazioni', 'fp-performance-suite'), __('⚙️ Impostazioni', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-settings', [$pages['settings'], 'render']);
    }

    /**
     * Handler per il salvataggio delle impostazioni avanzate
     */
    public function handleAdvancedSave(): void
    {
        $advancedPage = new Advanced($this->container);
        $advancedPage->handleSave();
    }

    /**
     * Handler per l'esportazione CSV dalla pagina Overview
     */
    public function handleOverviewExportCsv(): void
    {
        $overviewPage = new Overview($this->container);
        $overviewPage->exportCsv();
    }

    /**
     * @return array<string, object>
     */
    private function pages(): array
    {
        return [
            'overview' => new Overview($this->container),
            'ai_config' => new AIConfig($this->container),
            'cache' => new Cache($this->container),
            'assets' => new Assets($this->container),
            'media' => new Media($this->container),
            'database' => new Database($this->container),
            'backend' => new Backend($this->container),
            'logs' => new Logs($this->container),
            'tools' => new Tools($this->container),
            'security' => new Security($this->container),
            'exclusions' => new Exclusions($this->container),
            'advanced' => new Advanced($this->container),
            'settings' => new Settings($this->container),
            'diagnostics' => new Diagnostics($this->container),
        ];
    }
}
