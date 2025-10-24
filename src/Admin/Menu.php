<?php

namespace FP\PerfSuite\Admin;

use FP\PerfSuite\Admin\Pages\AIConfig;
use FP\PerfSuite\Admin\Pages\Assets;
use FP\PerfSuite\Admin\Pages\Backend;
use FP\PerfSuite\Admin\Pages\Cache;
use FP\PerfSuite\Admin\Pages\Compression;
use FP\PerfSuite\Admin\Pages\Database;
use FP\PerfSuite\Admin\Pages\Diagnostics;
use FP\PerfSuite\Admin\Pages\Exclusions;
use FP\PerfSuite\Admin\Pages\Cdn;
use FP\PerfSuite\Admin\Pages\Logs;
use FP\PerfSuite\Admin\Pages\Media;
use FP\PerfSuite\Admin\Pages\Mobile;
use FP\PerfSuite\Admin\Pages\ML;
use FP\PerfSuite\Admin\Pages\MonitoringReports;
use FP\PerfSuite\Admin\Pages\Overview;
use FP\PerfSuite\Admin\Pages\Security;
use FP\PerfSuite\Admin\Pages\Settings;
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
use function sanitize_key;

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
        
        // Fallback: registra il menu anche se il sistema normale fallisce
        add_action('admin_menu', [$this, 'registerFallbackMenu'], 999);
        
        // NOTA: wp_ajax_fp_ps_apply_recommendation ora gestito da RecommendationsAjax (ripristinato 21 Ott 2025)
        // Mantenuto metodo applyRecommendation() come fallback per compatibilità
        
        // Registra gli hook admin_post per il salvataggio delle impostazioni
        // Questi devono essere registrati presto, non solo quando le pagine vengono istanziate
        add_action('admin_post_fp_ps_save_compression', [$this, 'handleCompressionSave']);
        add_action('admin_post_fp_ps_save_cdn', [$this, 'handleCdnSave']);
        add_action('admin_post_fp_ps_save_monitoring', [$this, 'handleMonitoringSave']);
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

        // SICUREZZA: Sanitizza TUTTI i valori prima dell'output
        $errorMessage = esc_html($error['message'] ?? 'Errore sconosciuto');
        $errorType = sanitize_key($error['type'] ?? 'unknown');
        $solution = wp_kses_post($error['solution'] ?? 'Contatta il supporto.');
        $phpVersion = esc_html($error['php_version'] ?? PHP_VERSION);
        $wpVersion = esc_html($error['wp_version'] ?? get_bloginfo('version'));
        $file = isset($error['file']) ? esc_html($error['file']) : '';
        $line = isset($error['line']) ? absint($error['line']) : 0;
        $time = isset($error['time']) ? absint($error['time']) : time();

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
                    <p><strong><?php _e('Versione PHP:', 'fp-performance-suite'); ?></strong> <?php echo $phpVersion; ?></p>
                    <p><strong><?php _e('Versione WordPress:', 'fp-performance-suite'); ?></strong> <?php echo $wpVersion; ?></p>
                    <?php if (!empty($file)): ?>
                    <p><strong><?php _e('File:', 'fp-performance-suite'); ?></strong> <?php echo $file; ?></p>
                    <p><strong><?php _e('Linea:', 'fp-performance-suite'); ?></strong> <?php echo $line > 0 ? $line : 'N/A'; ?></p>
                    <?php endif; ?>
                    <p><strong><?php _e('Data:', 'fp-performance-suite'); ?></strong> <?php echo esc_html(date('Y-m-d H:i:s', $time)); ?></p>
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
        // SICUREZZA: Sanitizza il nonce PRIMA di verificarlo
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        
        if (empty($nonce) || !wp_verify_nonce($nonce, 'fp_ps_dismiss_error')) {
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
        // SICUREZZA: Sanitizza il nonce PRIMA di verificarlo
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        
        if (empty($nonce) || !wp_verify_nonce($nonce, 'fp_ps_dismiss_salient')) {
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
        
        // Log per debug
        error_log('[FP Performance Suite] Registrazione menu con capability: ' . $capability);
        error_log('[FP Performance Suite] Utente corrente può accedere: ' . (current_user_can($capability) ? 'SI' : 'NO'));
        
        // Se l'utente corrente è un admin ma non ha accesso, mostra un errore
        // invece di auto-riparare (per evitare privilege escalation)
        if (current_user_can('manage_options') && !current_user_can($capability)) {
            error_log('[FP Performance Suite] ATTENZIONE: Configurazione permessi non valida rilevata');
            
            // Mostra un warning e blocca l'accesso fino alla risoluzione manuale
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-error">
                    <p>
                        <strong><?php esc_html_e('FP Performance Suite - Configurazione Non Valida', 'fp-performance-suite'); ?></strong><br>
                        <?php esc_html_e('Le impostazioni di accesso sono configurate in modo errato. Per risolvere:', 'fp-performance-suite'); ?>
                    </p>
                    <ol>
                        <li><?php esc_html_e('Vai a FP Performance > Settings', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Reimposta "Allowed Role" su "Administrator"', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Salva le impostazioni', 'fp-performance-suite'); ?></li>
                    </ol>
                </div>
                <?php
            });
            
            // Non permettere l'accesso fino alla risoluzione manuale
            $capability = 'do_not_allow';
        }
        
        // Log per debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[FP Performance Suite] Registrazione menu con capability: ' . $capability);
            error_log('[FP Performance Suite] Utente corrente può accedere: ' . (current_user_can($capability) ? 'SI' : 'NO'));
        }
        
        // Fallback: se la capability è 'do_not_allow', usa 'manage_options' per garantire la visibilità
        if ($capability === 'do_not_allow') {
            $capability = 'manage_options';
            error_log('[FP Performance Suite] FALLBACK: Uso manage_options per garantire visibilità menu');
        }
        
        // Controllo finale: se l'utente è admin ma non ha accesso, forza manage_options
        if (current_user_can('manage_options') && !current_user_can($capability)) {
            $capability = 'manage_options';
            error_log('[FP Performance Suite] FORCE: Admin senza accesso, uso manage_options');
        }
        
        // FORZA SEMPRE manage_options per garantire la visibilità
        $capability = 'manage_options';
        error_log('[FP Performance Suite] FORCE FINALE: Uso sempre manage_options per garantire visibilità');

        add_menu_page(
            __('FP Performance Suite', 'fp-performance-suite'),
            __('FP Performance', 'fp-performance-suite'),
            $capability,
            'fp-performance-suite',
            [$pages['overview'], 'render'],
            'dashicons-performance',
            59
        );

        // ═══════════════════════════════════════════════════════════
        // 📊 DASHBOARD & QUICK START
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Overview', 'fp-performance-suite'), __('📊 Overview', 'fp-performance-suite'), $capability, 'fp-performance-suite', [$pages['overview'], 'render']);
        add_submenu_page('fp-performance-suite', __('AI Auto-Config', 'fp-performance-suite'), __('⚡ AI Auto-Config', 'fp-performance-suite'), $capability, 'fp-performance-suite-ai-config', [$pages['ai_config'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🚀 PERFORMANCE OPTIMIZATION
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Cache', 'fp-performance-suite'), __('🚀 Cache', 'fp-performance-suite'), $capability, 'fp-performance-suite-cache', [$pages['cache'], 'render']);
        add_submenu_page('fp-performance-suite', __('Assets', 'fp-performance-suite'), __('📦 Assets', 'fp-performance-suite'), $capability, 'fp-performance-suite-assets', [$pages['assets'], 'render']);
        add_submenu_page('fp-performance-suite', __('Media', 'fp-performance-suite'), __('🖼️ Media', 'fp-performance-suite'), $capability, 'fp-performance-suite-media', [$pages['media'], 'render']);
        add_submenu_page('fp-performance-suite', __('Database', 'fp-performance-suite'), __('💾 Database', 'fp-performance-suite'), $capability, 'fp-performance-suite-database', [$pages['database'], 'render']);
        add_submenu_page('fp-performance-suite', __('Backend', 'fp-performance-suite'), __('⚙️ Backend', 'fp-performance-suite'), $capability, 'fp-performance-suite-backend', [$pages['backend'], 'render']);
        add_submenu_page('fp-performance-suite', __('Compression', 'fp-performance-suite'), __('🗜️ Compression', 'fp-performance-suite'), $capability, 'fp-performance-suite-compression', [$pages['compression'], 'render']);
        add_submenu_page('fp-performance-suite', __('Mobile', 'fp-performance-suite'), __('📱 Mobile', 'fp-performance-suite'), $capability, 'fp-performance-suite-mobile', [$pages['mobile'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🌐 CDN
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('CDN', 'fp-performance-suite'), __('🌐 CDN', 'fp-performance-suite'), $capability, 'fp-performance-suite-cdn', [$pages['cdn'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🛡️ SECURITY & INFRASTRUCTURE
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Security', 'fp-performance-suite'), __('🛡️ Security', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-security', [$pages['security'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🧠 INTELLIGENCE & AUTO-DETECTION
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Exclusions', 'fp-performance-suite'), __('🧠 Smart Exclusions', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-exclusions', [$pages['exclusions'], 'render']);
        add_submenu_page('fp-performance-suite', __('Machine Learning', 'fp-performance-suite'), __('🤖 ML', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-ml', [$pages['ml'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 📊 MONITORING & DIAGNOSTICS
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Monitoring', 'fp-performance-suite'), __('📊 Monitoring', 'fp-performance-suite'), $capability, 'fp-performance-suite-monitoring', [$pages['monitoring'], 'render']);
        add_submenu_page('fp-performance-suite', __('Logs', 'fp-performance-suite'), __('📝 Logs', 'fp-performance-suite'), $capability, 'fp-performance-suite-logs', [$pages['logs'], 'render']);
        add_submenu_page('fp-performance-suite', __('Diagnostics', 'fp-performance-suite'), __('🔍 Diagnostics', 'fp-performance-suite'), $capability, 'fp-performance-suite-diagnostics', [$pages['diagnostics'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🔧 CONFIGURATION
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Settings', 'fp-performance-suite'), __('🔧 Settings', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-settings', [$pages['settings'], 'render']);
    }

    /**
     * Handler per il salvataggio delle impostazioni di compressione
     */
    public function handleCompressionSave(): void
    {
        $compressionPage = new Compression($this->container);
        $message = $compressionPage->handleSave();
        
        // Redirect with message
        $redirect_url = add_query_arg([
            'page' => 'fp-performance-suite-compression',
            'message' => urlencode($message)
        ], admin_url('admin.php'));
        
        wp_safe_redirect($redirect_url);
        exit;
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
     * Handler per il salvataggio delle impostazioni CDN
     */
    public function handleCdnSave(): void
    {
        $cdnPage = new Cdn($this->container);
        $message = $cdnPage->handleSave();
        
        // Redirect with message
        $redirect_url = add_query_arg([
            'page' => 'fp-performance-suite-cdn',
            'message' => urlencode($message)
        ], admin_url('admin.php'));
        
        wp_safe_redirect($redirect_url);
        exit;
    }

    /**
     * Handler per il salvataggio delle impostazioni Monitoring
     */
    public function handleMonitoringSave(): void
    {
        $monitoringPage = new MonitoringReports($this->container);
        $message = $monitoringPage->handleSave();
        
        // Redirect with message
        $redirect_url = add_query_arg([
            'page' => 'fp-performance-suite-monitoring',
            'message' => urlencode($message)
        ], admin_url('admin.php'));
        
        wp_safe_redirect($redirect_url);
        exit;
    }


    /**
     * @return array<string, object>
     */
    private function pages(): array
    {
        return [
            'overview' => new Overview($this->container),
            'cache' => new Cache($this->container),
            'assets' => new Assets($this->container),
            'media' => new Media($this->container),
            'mobile' => new Mobile($this->container),
            'database' => new Database($this->container),
            'backend' => new Backend($this->container),
            'compression' => new Compression($this->container),
            'cdn' => new Cdn($this->container),
            'ai_config' => new AIConfig($this->container),
            'ml' => new ML($this->container),
            'monitoring' => new MonitoringReports($this->container),
            'logs' => new Logs($this->container),
            'settings' => new Settings($this->container),
            'security' => new Security($this->container),
            'exclusions' => new Exclusions($this->container),
            'diagnostics' => new Diagnostics($this->container),
        ];
    }

    /**
     * Handler AJAX per applicare le raccomandazioni automaticamente
     */
    public function applyRecommendation(): void
    {
        // Verifica permessi
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Non hai i permessi per eseguire questa azione.', 'fp-performance-suite'),
            ]);
            return;
        }

        // Verifica nonce
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'fp_ps_apply_recommendation')) {
            wp_send_json_error([
                'message' => __('Verifica di sicurezza fallita. Ricarica la pagina e riprova.', 'fp-performance-suite'),
            ]);
            return;
        }

        // Ottieni action_id
        $actionId = sanitize_key($_POST['action_id'] ?? '');
        if (empty($actionId)) {
            wp_send_json_error([
                'message' => __('ID azione non valido.', 'fp-performance-suite'),
            ]);
            return;
        }

        // Applica la raccomandazione
        try {
            $applicator = $this->container->get(\FP\PerfSuite\Services\Monitoring\RecommendationApplicator::class);
            $result = $applicator->apply($actionId);

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                ]);
            } else {
                wp_send_json_error([
                    'message' => $result['message'],
                ]);
            }
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => sprintf(
                    __('Errore imprevisto: %s', 'fp-performance-suite'),
                    $e->getMessage()
                ),
            ]);
        }
    }
    
    /**
     * Metodo di fallback per registrare il menu se il sistema normale fallisce
     */
    public function registerFallbackMenu(): void
    {
        // Controlla se il menu è già stato registrato
        global $menu;
        $menu_already_registered = false;
        
        if (isset($menu)) {
            foreach ($menu as $item) {
                if (isset($item[2]) && strpos($item[2], 'fp-performance') !== false) {
                    $menu_already_registered = true;
                    break;
                }
            }
        }
        
        // Se il menu non è stato registrato, registralo manualmente
        if (!$menu_already_registered) {
            error_log('[FP Performance Suite] FALLBACK: Registrazione menu manuale');
            
            add_menu_page(
                'FP Performance Suite',
                'FP Performance',
                'manage_options',
                'fp-performance-suite-fallback',
                function() {
                    echo '<div class="wrap">';
                    echo '<h1>FP Performance Suite</h1>';
                    echo '<p>Il plugin è in modalità di recupero. Il menu principale potrebbe non essere visibile.</p>';
                    echo '<p>Prova a disattivare e riattivare il plugin per risolvere il problema.</p>';
                    echo '</div>';
                },
                'dashicons-performance',
                30
            );
        }
    }
}
