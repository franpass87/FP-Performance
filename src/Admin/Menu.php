<?php

namespace FP\PerfSuite\Admin;

use FP\PerfSuite\Utils\ErrorHandler;

// Definisci le costanti WordPress per i cookie se non già definite
if (!defined('LOGGED_IN_COOKIE')) {
    if (defined('COOKIEHASH')) {
        define('LOGGED_IN_COOKIE', 'wordpress_logged_in_' . COOKIEHASH);
    }
}
if (!defined('AUTH_COOKIE')) {
    if (defined('COOKIEHASH')) {
        define('AUTH_COOKIE', 'wordpress_' . COOKIEHASH);
    }
}
if (!defined('SECURE_AUTH_COOKIE')) {
    if (defined('COOKIEHASH')) {
        define('SECURE_AUTH_COOKIE', 'wordpress_sec_' . COOKIEHASH);
    }
}
use FP\PerfSuite\Admin\Pages\AIConfig;
use FP\PerfSuite\Admin\Pages\Assets;
use FP\PerfSuite\Admin\Pages\Backend;
use FP\PerfSuite\Admin\Pages\Cache;
use FP\PerfSuite\Admin\Pages\Compression;
use FP\PerfSuite\Admin\Pages\Database;
use FP\PerfSuite\Admin\Pages\Diagnostics;
use FP\PerfSuite\Admin\Pages\Exclusions;
use FP\PerfSuite\Admin\Pages\IntelligenceDashboard;
use FP\PerfSuite\Admin\Pages\Cdn;
use FP\PerfSuite\Admin\Pages\JavaScriptOptimization;
use FP\PerfSuite\Admin\Pages\Logs;
use FP\PerfSuite\Admin\Pages\Media;
use FP\PerfSuite\Admin\Pages\Mobile;
use FP\PerfSuite\Admin\Pages\ML;
use FP\PerfSuite\Admin\Pages\MonitoringReports;
use FP\PerfSuite\Admin\Pages\Overview;
use FP\PerfSuite\Admin\Pages\Security;
use FP\PerfSuite\Admin\Pages\Settings;
use FP\PerfSuite\Admin\Pages\Status;
use FP\PerfSuite\Admin\Pages\ThemeOptimization;
use FP\PerfSuite\Admin\Menu\NoticeManager;
use FP\PerfSuite\Admin\Menu\FormHandlers;
use FP\PerfSuite\Admin\Menu\RecommendationHandler;
use FP\PerfSuite\Admin\Menu\ThirdPartyNoticeHider;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\ServiceContainerAdapter;
use FP\PerfSuite\Kernel\Container as KernelContainer;
use FP\PerfSuite\Kernel\ContainerInterface;
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
use function get_current_screen;
use function remove_all_actions;

class Menu
{
    /** @var ServiceContainer|ContainerInterface */
    private $container;
    private static bool $menuRegistered = false;
    private NoticeManager $noticeManager;
    private ?FormHandlers $formHandlers = null;
    private ?RecommendationHandler $recommendationHandler = null;
    private ThirdPartyNoticeHider $noticeHider;

    /**
     * @param ServiceContainer|ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        
        // Inizializza le dipendenze usando dependency injection
        // NoticeManager è registrato in AdminServiceProvider
        if ($container->has(NoticeManager::class)) {
            $this->noticeManager = $container->get(NoticeManager::class);
        } else {
            // Fallback per backward compatibility
            $this->noticeManager = new NoticeManager();
        }
        
        // ThirdPartyNoticeHider - usa container se disponibile
        if ($container->has(ThirdPartyNoticeHider::class)) {
            $this->noticeHider = $container->get(ThirdPartyNoticeHider::class);
        } else {
            // Fallback per backward compatibility
            $this->noticeHider = new ThirdPartyNoticeHider();
        }
        
        // FormHandlers e RecommendationHandler richiedono ServiceContainer,
        // li inizializziamo lazily solo se necessario
        
        // FEATURE: Nascondi notice di altri plugin sulle pagine FP Performance
        // Usa priorità 999 per eseguire DOPO che gli altri plugin registrano i loro notice
        add_action('admin_head', [$this, 'hideOtherPluginsNotices'], 999);
    }
    
    /**
     * Nascondi admin notices di altri plugin sulle pagine FP Performance
     * Per evitare clutter e confusione nell'interfaccia
     */
    public function hideOtherPluginsNotices(): void
    {
        // Verifica se siamo su una pagina FP Performance controllando il parametro GET
        if (!isset($_GET['page']) || strpos($_GET['page'], 'fp-performance-suite') !== 0) {
            return;
        }
        
        // Nascondi i notice con CSS inline (più affidabile di remove_all_actions)
        echo '<style>
            /* Nascondi TUTTI i notice WordPress di altri plugin sulle pagine FP Performance */
            /* Notice di FP Privacy */
            .notice.fp-privacy-detector-alert,
            /* Notice di FP Publisher */
            .notice:not([class*="fp-perf"]):not([class*="fp-performance"]),
            .updated:not([class*="fp-perf"]):not([class*="fp-performance"]),
            .error:not([class*="fp-perf"]):not([class*="fp-performance"]) {
                display: none !important;
            }
            
            /* Mostra solo i notice di FP Performance (se ci sono) */
            .notice.fp-performance-notice,
            .notice.fp-perf-notice {
                display: block !important;
            }
            
            /* BUGFIX #14b: Testo bianco su box viola intro per leggibilità */
            .fp-ps-intro-panel p,
            .fp-ps-intro-panel strong,
            .fp-ps-intro-panel span,
            .fp-ps-intro-panel div {
                color: white !important;
            }
        </style>
        <script>
            // BUGFIX #14b: Forza testo bianco via JavaScript per bypassare OPCache
            document.addEventListener("DOMContentLoaded", function() {
                const introPanels = document.querySelectorAll(".fp-ps-intro-panel");
                introPanels.forEach(panel => {
                    const allText = panel.querySelectorAll("p, strong, span, div, h2, h3");
                    allText.forEach(el => el.style.color = "white");
                });
            });
        </script>';
    }

    public function boot(): void
    {
        add_action('admin_menu', [$this, 'register']);
        add_action('admin_notices', [$this->noticeManager, 'showActivationErrors']);
        add_action('wp_ajax_fp_ps_dismiss_activation_error', [$this->noticeManager, 'dismissActivationError']);
        add_action('wp_ajax_fp_ps_dismiss_salient_notice', [$this->noticeManager, 'dismissSalientNotice']);
        
        // Nascondi i notice di altri plugin nelle pagine del plugin
        add_action('admin_enqueue_scripts', [$this->noticeHider, 'hideThirdPartyNotices']);
        
        // NOTA: wp_ajax_fp_ps_apply_recommendation ora gestito da RecommendationsAjax (ripristinato 21 Ott 2025)
        // Mantenuto metodo applyRecommendation() come fallback per compatibilità
        
        // Registra gli hook admin_post per il salvataggio delle impostazioni
        // Questi devono essere registrati presto, non solo quando le pagine vengono istanziate
        // Usa i metodi wrapper di questa classe per evitare dipendenze dirette su ServiceContainer
        add_action('admin_post_fp_ps_save_compression', [$this, 'handleCompressionSave']);
        add_action('admin_post_fp_ps_save_cdn', [$this, 'handleCdnSave']);
        add_action('admin_post_fp_ps_save_monitoring', [$this, 'handleMonitoringSave']);
        add_action('admin_post_fp_ps_export_csv', [$this, 'handleOverviewExportCsv']);
    }
    

    /**
     * Mostra eventuali errori di attivazione nell'area admin
     * @deprecated Usa NoticeManager::showActivationErrors()
     */
    public function showActivationErrors(): void
    {
        $this->noticeManager->showActivationErrors();
    }
    
    private function showActivationErrors_OLD(): void
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

    public function dismissActivationError(): void
    {
        $this->noticeManager->dismissActivationError();
    }
    
    public function dismissSalientNotice(): void
    {
        $this->noticeManager->dismissSalientNotice();
    }
    
    // Metodi dismissActivationError() e dismissSalientNotice() rimossi - ora gestiti da NoticeManager

    public function register(): void
    {
        // Prevenire doppia registrazione del menu - SOLO se già registrato in questa sessione
        if (self::$menuRegistered) {
            if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
                error_log('FP-Performance: Menu già registrato, skip');
            }
            return;
        }
        self::$menuRegistered = true;
        
        $pages = $this->pages();
        
        // Ottieni la capability richiesta con fallback sicuro
        $capability = Capabilities::required();
        
        // DEBUG: Log capability
        if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
            error_log('FP-Performance: Capability richiesta=' . $capability . ', current_user_can=' . (current_user_can($capability) ? 'SI' : 'NO'));
        }
        
        // Validazione: assicurati che la capability sia valida
        if (empty($capability) || !is_string($capability)) {
            $capability = 'manage_options';
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                ErrorHandler::handleSilently(
                    new \RuntimeException('Capability non valida, uso manage_options come fallback'),
                    'Menu registration'
                );
            }
        }
        
        // Log per debug
        if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
            ErrorHandler::handleSilently(
                new \RuntimeException('Menu registration debug: capability=' . $capability . ', can_access=' . (current_user_can($capability) ? 'SI' : 'NO')),
                'Menu registration'
            );
        }
        
        // Se l'utente corrente è un admin ma non ha accesso, mostra un errore
        // invece di auto-riparare (per evitare privilege escalation)
        if (current_user_can('manage_options') && !current_user_can($capability)) {
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                ErrorHandler::handleSilently(
                    new \RuntimeException('Configurazione permessi non valida rilevata'),
                    'Menu registration'
                );
            }
            
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
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                ErrorHandler::handleSilently(
                    new \RuntimeException('Menu registration debug: capability=' . $capability . ', can_access=' . (current_user_can($capability) ? 'SI' : 'NO')),
                    'Menu registration'
                );
            }
        }
        
        // Fallback: se la capability è 'do_not_allow', usa 'manage_options' per garantire la visibilità
        if ($capability === 'do_not_allow') {
            $capability = 'manage_options';
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                ErrorHandler::handleSilently(
                    new \RuntimeException('FALLBACK: Uso manage_options per garantire visibilità menu'),
                    'Menu registration'
                );
            }
        }
        
        // Controllo finale: se l'utente è admin ma non ha accesso, forza manage_options
        if (current_user_can('manage_options') && !current_user_can($capability)) {
            $capability = 'manage_options';
            if (defined('FP_PERF_DEBUG') && FP_PERF_DEBUG) {
                ErrorHandler::handleSilently(
                    new \RuntimeException('FORCE: Admin senza accesso, uso manage_options'),
                    'Menu registration'
                );
            }
        }
        

        $menu_result = add_menu_page(
            __('FP Performance Suite', 'fp-performance-suite'),
            __('FP Performance', 'fp-performance-suite'),
            $capability,
            'fp-performance-suite',
            [$pages['overview'], 'render'],
            'dashicons-performance',
            59
        );
        
        // DEBUG: Log risultato registrazione
        if (defined('WP_DEBUG') && WP_DEBUG && function_exists('error_log')) {
            error_log('FP-Performance: Menu registrato, hook=' . ($menu_result ?: 'NULL'));
        }

        // ═══════════════════════════════════════════════════════════
        // 🏠 DASHBOARD & QUICK START
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Overview', 'fp-performance-suite'), __('🏠 Overview', 'fp-performance-suite'), $capability, 'fp-performance-suite', [$pages['overview'], 'render']);
        add_submenu_page('fp-performance-suite', __('AI Config', 'fp-performance-suite'), __('🤖 AI Config', 'fp-performance-suite'), $capability, 'fp-performance-suite-ai-config', [$pages['ai_config'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🚀 OPTIMIZATION
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Cache', 'fp-performance-suite'), __('🚀 Cache', 'fp-performance-suite'), $capability, 'fp-performance-suite-cache', [$pages['cache'], 'render']);
        add_submenu_page('fp-performance-suite', __('Assets', 'fp-performance-suite'), __('📦 Assets', 'fp-performance-suite'), $capability, 'fp-performance-suite-assets', [$pages['assets'], 'render']);
        add_submenu_page('fp-performance-suite', __('Compression', 'fp-performance-suite'), __('🗜️ Compression', 'fp-performance-suite'), $capability, 'fp-performance-suite-compression', [$pages['compression'], 'render']);
        add_submenu_page('fp-performance-suite', __('Media', 'fp-performance-suite'), __('🖼️ Media', 'fp-performance-suite'), $capability, 'fp-performance-suite-media', [$pages['media'], 'render']);
        add_submenu_page('fp-performance-suite', __('Mobile', 'fp-performance-suite'), __('📱 Mobile', 'fp-performance-suite'), $capability, 'fp-performance-suite-mobile', [$pages['mobile'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🏗️ INFRASTRUCTURE
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Database', 'fp-performance-suite'), __('💾 Database', 'fp-performance-suite'), $capability, 'fp-performance-suite-database', [$pages['database'], 'render']);
        add_submenu_page('fp-performance-suite', __('CDN', 'fp-performance-suite'), __('🌐 CDN', 'fp-performance-suite'), $capability, 'fp-performance-suite-cdn', [$pages['cdn'], 'render']);
        add_submenu_page('fp-performance-suite', __('Backend', 'fp-performance-suite'), __('🎛️ Backend', 'fp-performance-suite'), $capability, 'fp-performance-suite-backend', [$pages['backend'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🎨 ADVANCED
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Theme', 'fp-performance-suite'), __('🎨 Theme', 'fp-performance-suite'), $capability, 'fp-performance-suite-theme-optimization', [$pages['theme_optimization'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🧠 INTELLIGENCE
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Machine Learning', 'fp-performance-suite'), __('🤖 Machine Learning', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-ml', [$pages['ml'], 'render']);
        
        // BUGFIX #15: Intelligence ripristinata come pagina standalone (troppo pesante come tab)
        add_submenu_page('fp-performance-suite', __('Intelligence', 'fp-performance-suite'), __('🧠 Intelligence', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-intelligence', [$pages['intelligence'], 'render']);
        
        // NOTA: Exclusions è disponibile come TAB dentro la pagina Cache (più logico)
        
        // ═══════════════════════════════════════════════════════════
        // 📈 MONITORING & SECURITY
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Monitoring', 'fp-performance-suite'), __('📈 Monitoring', 'fp-performance-suite'), $capability, 'fp-performance-suite-monitoring', [$pages['monitoring'], 'render']);
        add_submenu_page('fp-performance-suite', __('Security', 'fp-performance-suite'), __('🛡️ Security', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-security', [$pages['security'], 'render']);
        
        // ═══════════════════════════════════════════════════════════
        // 🔧 SETTINGS
        // ═══════════════════════════════════════════════════════════
        add_submenu_page('fp-performance-suite', __('Settings', 'fp-performance-suite'), __('🔧 Settings', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-settings', [$pages['settings'], 'render']);
        
        // Add Status page under WordPress Settings menu
        add_submenu_page(
            'options-general.php',
            __('FP Performance', 'fp-performance-suite'),
            __('FP Performance', 'fp-performance-suite'),
            'manage_options',
            'fp-performance-status',
            [$pages['status'], 'render']
        );
    }

    /**
     * Ottiene FormHandlers inizializzandolo lazy se necessario
     */
    private function getFormHandlers(): ?FormHandlers
    {
        if ($this->formHandlers === null && $this->container instanceof ServiceContainer) {
            $this->formHandlers = new FormHandlers($this->container);
        }
        return $this->formHandlers;
    }
    
    public function handleCompressionSave(): void
    {
        $handlers = $this->getFormHandlers();
        if ($handlers) {
            $handlers->handleCompressionSave();
        }
    }

    public function handleOverviewExportCsv(): void
    {
        $handlers = $this->getFormHandlers();
        if ($handlers) {
            $handlers->handleOverviewExportCsv();
        }
    }

    public function handleCdnSave(): void
    {
        $handlers = $this->getFormHandlers();
        if ($handlers) {
            $handlers->handleCdnSave();
        }
    }

    public function handleMonitoringSave(): void
    {
        $handlers = $this->getFormHandlers();
        if ($handlers) {
            $handlers->handleMonitoringSave();
        }
    }
    
    // Metodi handleCompressionSave(), handleOverviewExportCsv(), handleCdnSave(), handleMonitoringSave() rimossi - ora gestiti da FormHandlers


    /**
     * Ottiene un ServiceContainer compatibile dal container corrente
     */
    private function getServiceContainer(): ServiceContainer
    {
        // Se è già un ServiceContainer, usalo direttamente
        if ($this->container instanceof ServiceContainer) {
            return $this->container;
        }
        
        // Se è un KernelContainer, wrappalo con ServiceContainerAdapter
        if ($this->container instanceof KernelContainer) {
            // ServiceContainerAdapter estende effettivamente le funzionalità necessarie
            // ma le pagine si aspettano ServiceContainer, quindi creiamo un adapter
            // che sarà accettato dai costruttori delle pagine
            return new ServiceContainerAdapter($this->container);
        }
        
        // Fallback: crea un ServiceContainer vuoto
        return new ServiceContainer();
    }
    
    /**
     * @return array<string, object>
     */
    private function pages(): array
    {
        $serviceContainer = $this->getServiceContainer();
        
        return [
            'overview' => new Overview($serviceContainer),
            'cache' => new Cache($serviceContainer),
            'assets' => new Assets($serviceContainer),
            'js_optimization' => new JavaScriptOptimization($serviceContainer),
            'media' => new Media($serviceContainer),
            'mobile' => new Mobile($serviceContainer),
            'database' => new Database($serviceContainer),
            'backend' => new Backend($serviceContainer),
            'compression' => new Compression($serviceContainer),
            'cdn' => new Cdn($serviceContainer),
            'ai_config' => new AIConfig($serviceContainer),
            'ml' => new ML($serviceContainer),
            'monitoring' => new MonitoringReports($serviceContainer),
            'logs' => new Logs($serviceContainer),
            'settings' => new Settings($serviceContainer),
            'security' => new Security($serviceContainer),
            'intelligence' => new IntelligenceDashboard($serviceContainer),
            'exclusions' => new Exclusions($serviceContainer),
            'diagnostics' => new Diagnostics($serviceContainer),
            'status' => new Status($serviceContainer),
            'theme_optimization' => new ThemeOptimization($serviceContainer),
        ];
    }

    /**
     * Ottiene RecommendationHandler inizializzandolo lazy se necessario
     */
    private function getRecommendationHandler(): ?RecommendationHandler
    {
        if ($this->recommendationHandler === null && $this->container instanceof ServiceContainer) {
            $this->recommendationHandler = new RecommendationHandler($this->container);
        }
        return $this->recommendationHandler;
    }
    
    public function applyRecommendation(): void
    {
        $handler = $this->getRecommendationHandler();
        if ($handler) {
            $handler->applyRecommendation();
        }
    }
    
    public function hideThirdPartyNotices(): void
    {
        $this->noticeHider->hideThirdPartyNotices();
    }
}
