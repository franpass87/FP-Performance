<?php

namespace FP\PerfSuite\Services\Admin;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;

class BackendOptimizer
{
    private $optimize_heartbeat;
    private $limit_revisions;
    private $optimize_dashboard;
    private $admin_bar;
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    // FIX: Traccia gli hook registrati per poterli rimuovere
    private static array $registeredHooks = [];
    
    /**
     * Costruttore
     * 
     * @param bool $optimize_heartbeat Abilita ottimizzazione heartbeat
     * @param bool $limit_revisions Limita revisioni
     * @param bool $optimize_dashboard Ottimizza dashboard
     * @param bool $admin_bar Gestione admin bar
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     */
    public function __construct($optimize_heartbeat = true, $limit_revisions = true, $optimize_dashboard = true, $admin_bar = true, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optimize_heartbeat = $optimize_heartbeat;
        $this->limit_revisions = $limit_revisions;
        $this->optimize_dashboard = $optimize_dashboard;
        $this->admin_bar = $admin_bar;
        $this->optionsRepo = $optionsRepo;
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }
    
    public function init()
    {
        // FIX: Carica le impostazioni salvate e verifica se il servizio è abilitato
        $settings = $this->getSettings();
        
        // FIX CRITICO: Se il servizio non è abilitato, non registrare nessun hook
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Heartbeat optimization
        if (!empty($settings['optimize_heartbeat'])) {
            if (!has_action('init', [$this, 'optimizeHeartbeat'])) {
                add_action('init', [$this, 'optimizeHeartbeat']);
                self::$registeredHooks['init_heartbeat'] = true;
            }
        }
        
        // Limit revisions
        if (!empty($settings['limit_revisions'])) {
            if (!has_action('init', [$this, 'limitRevisions'])) {
                add_action('init', [$this, 'limitRevisions']);
                self::$registeredHooks['init_revisions'] = true;
            }
        }
        
        // Dashboard optimization
        if (!empty($settings['optimize_dashboard']) || !empty($settings['remove_dashboard_widgets'])) {
            if (!has_action('wp_dashboard_setup', [$this, 'optimizeDashboard'])) {
                add_action('wp_dashboard_setup', [$this, 'optimizeDashboard']);
                self::$registeredHooks['wp_dashboard_setup'] = true;
            }
        }
        
        // Admin Bar: gestione granulare
        if (!empty($settings['admin_bar'])) {
            // Disabilita completamente sul frontend se richiesto
            if (!empty($settings['admin_bar']['disable_frontend'])) {
                if (!has_filter('show_admin_bar', '__return_false')) {
                    add_filter('show_admin_bar', '__return_false');
                    self::$registeredHooks['show_admin_bar'] = true;
                }
            }
            
            // Rimuovi elementi specifici dalla admin bar
            if (!has_action('wp_before_admin_bar_render', [$this, 'customizeAdminBar'])) {
                add_action('wp_before_admin_bar_render', [$this, 'customizeAdminBar']);
                self::$registeredHooks['wp_before_admin_bar_render'] = true;
            }
        }
        
        // Dashboard Widgets: gestione granulare
        if (!empty($settings['dashboard'])) {
            if (!has_action('wp_dashboard_setup', [$this, 'customizeDashboardWidgets'])) {
                add_action('wp_dashboard_setup', [$this, 'customizeDashboardWidgets'], 999);
                self::$registeredHooks['wp_dashboard_setup'] = true;
            }
        }
        
        // Heartbeat API settings
        if (!empty($settings['optimize_heartbeat']) || !empty($settings['heartbeat'])) {
            if (!has_action('init', [$this, 'applyHeartbeatSettings'])) {
                add_action('init', [$this, 'applyHeartbeatSettings']);
                self::$registeredHooks['init_heartbeat'] = true;
            }
        }
        
        // Admin AJAX optimizations
        if (!empty($settings['optimize_admin_ajax']) || !empty($settings['admin_ajax'])) {
            if (!has_action('init', [$this, 'applyAdminAjaxSettings'])) {
                add_action('init', [$this, 'applyAdminAjaxSettings']);
                self::$registeredHooks['init_admin_ajax'] = true;
            }
        }
    }
    
    public function optimizeHeartbeat()
    {
        // FIX: Verifica che il servizio sia abilitato
        $settings = $this->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Reduce heartbeat frequency
        // FIX: Usa tracciamento statico per evitare duplicati con closure
        static $heartbeatFilterRegistered = false;
        if (!$heartbeatFilterRegistered) {
            add_filter('heartbeat_settings', function($settings) {
                $settings['interval'] = 60; // 60 seconds instead of 15
                return $settings;
            });
            $heartbeatFilterRegistered = true;
            self::$registeredHooks['heartbeat_settings'] = true;
        }
        
        // Disable heartbeat on frontend
        if (!is_admin()) {
            wp_deregister_script('heartbeat');
        }
    }
    
    /**
     * Applica le impostazioni Heartbeat API
     */
    public function applyHeartbeatSettings(): void
    {
        // FIX: Verifica che il servizio sia abilitato
        $settings = $this->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }
        
        $heartbeatSettings = $settings['heartbeat'] ?? [];
        $heartbeatInterval = $settings['heartbeat_interval'] ?? 60;
        
        // Gestisci heartbeat per dashboard
        if (!empty($heartbeatSettings['dashboard'])) {
            if ($heartbeatSettings['dashboard'] === 'disable') {
                add_action('admin_enqueue_scripts', function() {
                    if (is_admin()) {
                        wp_deregister_script('heartbeat');
                    }
                }, 1);
            } elseif ($heartbeatSettings['dashboard'] === 'slow') {
                add_filter('heartbeat_settings', function($settings) use ($heartbeatInterval) {
                    if (is_admin()) {
                        $settings['interval'] = max(120, $heartbeatInterval);
                    }
                    return $settings;
                });
            }
        }
        
        // Gestisci heartbeat per editor
        if (!empty($heartbeatSettings['editor'])) {
            if ($heartbeatSettings['editor'] === 'disable') {
                add_action('admin_enqueue_scripts', function() {
                    $screen = get_current_screen();
                    if ($screen && ($screen->is_block_editor() || $screen->post_type)) {
                        wp_deregister_script('heartbeat');
                    }
                }, 1);
            } elseif ($heartbeatSettings['editor'] === 'slow') {
                add_filter('heartbeat_settings', function($settings) use ($heartbeatInterval) {
                    $screen = get_current_screen();
                    if ($screen && ($screen->is_block_editor() || $screen->post_type)) {
                        $settings['interval'] = max(30, $heartbeatInterval);
                    }
                    return $settings;
                });
            }
        }
        
        // Gestisci heartbeat per frontend
        if (!empty($heartbeatSettings['frontend'])) {
            if ($heartbeatSettings['frontend'] === 'disable') {
                add_action('wp_enqueue_scripts', function() {
                    wp_deregister_script('heartbeat');
                }, 1);
            } elseif ($heartbeatSettings['frontend'] === 'slow') {
                add_filter('heartbeat_settings', function($settings) use ($heartbeatInterval) {
                    if (!is_admin()) {
                        $settings['interval'] = max(120, $heartbeatInterval);
                    }
                    return $settings;
                });
            }
        }
        
        // Applica intervallo personalizzato se specificato
        if (!empty($heartbeatInterval) && $heartbeatInterval !== 60) {
            add_filter('heartbeat_settings', function($settings) use ($heartbeatInterval) {
                $settings['interval'] = max(15, min(300, $heartbeatInterval));
                return $settings;
            });
        }
    }
    
    /**
     * Applica le ottimizzazioni Admin AJAX
     */
    public function applyAdminAjaxSettings(): void
    {
        // FIX: Verifica che il servizio sia abilitato
        $settings = $this->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }
        
        $adminAjaxSettings = $settings['admin_ajax'] ?? [];
        
        // Limita revisioni
        if (!empty($settings['limit_revisions'])) {
            $revisionsLimit = $settings['revisions_limit'] ?? 5;
            if (!defined('WP_POST_REVISIONS')) {
                define('WP_POST_REVISIONS', max(0, min(50, (int) $revisionsLimit)));
            }
        }
        
        // Intervallo autosave
        if (!empty($settings['autosave_interval'])) {
            $autosaveInterval = max(30, min(300, (int) $settings['autosave_interval']));
            add_filter('autosave_interval', function() use ($autosaveInterval) {
                return $autosaveInterval;
            });
        }
        
        // Disabilita emoji
        if (!empty($adminAjaxSettings['disable_emojis'])) {
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter('tiny_mce_plugins', function($plugins) {
                return is_array($plugins) ? array_diff($plugins, ['wpemoji']) : $plugins;
            });
        }
        
        // Disabilita embeds
        if (!empty($adminAjaxSettings['disable_embeds'])) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');
            remove_action('rest_api_init', 'wp_oembed_register_route');
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            add_filter('embed_oembed_discover', '__return_false');
            remove_filter('wp_head', 'wp_oembed_add_discovery_links');
            remove_filter('wp_head', 'wp_oembed_add_host_js');
        }
    }
    
    public function limitRevisions()
    {
        // FIX: Verifica che il servizio sia abilitato
        $settings = $this->getSettings();
        if (empty($settings['enabled']) || empty($settings['limit_revisions'])) {
            return;
        }
        
        // Limit post revisions
        $revisionsLimit = $settings['revisions_limit'] ?? 5;
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', max(0, min(50, (int) $revisionsLimit)));
        }
    }
    
    public function optimizeDashboard()
    {
        // FIX: Verifica che il servizio sia abilitato
        $settings = $this->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Remove unnecessary dashboard widgets (solo se optimize_dashboard è attivo)
        if (!empty($settings['optimize_dashboard']) || !empty($settings['remove_dashboard_widgets'])) {
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
            remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
            remove_meta_box('dashboard_secondary', 'dashboard', 'side');
            remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
            remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
            remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        }
    }
    
    public function removeAdminBar()
    {
        show_admin_bar(false);
    }
    
    /**
     * Personalizza l'Admin Bar rimuovendo elementi specifici
     */
    public function customizeAdminBar(): void
    {
        // FIX: Verifica che il servizio sia abilitato
        $settings = $this->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }
        
        global $wp_admin_bar;
        
        if (!$wp_admin_bar) {
            return;
        }
        
        $adminBarSettings = $settings['admin_bar'] ?? [];
        
        // Rimuovi menu aggiornamenti
        if (!empty($adminBarSettings['disable_updates']) || !empty($adminBarSettings['disable_updates_menu'])) {
            $wp_admin_bar->remove_node('updates');
        }
        
        // Rimuovi menu "+ Nuovo"
        if (!empty($adminBarSettings['disable_new']) || !empty($adminBarSettings['disable_new_menu'])) {
            $wp_admin_bar->remove_node('new-content');
        }
        
        // Rimuovi logo WordPress
        if (!empty($adminBarSettings['disable_wordpress_logo']) || !empty($adminBarSettings['disable_wp_logo'])) {
            $wp_admin_bar->remove_node('wp-logo');
        }
        
        // Rimuovi menu commenti
        if (!empty($adminBarSettings['disable_comments']) || !empty($adminBarSettings['disable_comments_menu'])) {
            $wp_admin_bar->remove_node('comments');
        }
        
        // Rimuovi link Personalizza
        if (!empty($adminBarSettings['disable_customize'])) {
            $wp_admin_bar->remove_node('customize');
        }
    }
    
    /**
     * Personalizza i Dashboard Widgets rimuovendo elementi specifici
     */
    public function customizeDashboardWidgets(): void
    {
        // FIX: Verifica che il servizio sia abilitato
        $settings = $this->getSettings();
        if (empty($settings['enabled'])) {
            return;
        }
        
        $dashboardSettings = $settings['dashboard'] ?? [];
        
        // Disabilita pannello di benvenuto
        if (!empty($dashboardSettings['disable_welcome'])) {
            remove_action('welcome_panel', 'wp_welcome_panel');
        }
        
        // Disabilita Quick Press
        if (!empty($dashboardSettings['disable_quick_press'])) {
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        }
        
        // Disabilita Widget Attività
        if (!empty($dashboardSettings['disable_activity'])) {
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        }
        
        // Disabilita WordPress News (Primary)
        if (!empty($dashboardSettings['disable_primary'])) {
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
        }
        
        // Disabilita Eventi e Notizie (Secondary)
        if (!empty($dashboardSettings['disable_secondary'])) {
            remove_meta_box('dashboard_secondary', 'dashboard', 'side');
        }
        
        // Disabilita Site Health
        if (!empty($dashboardSettings['disable_site_health'])) {
            remove_meta_box('dashboard_site_health', 'dashboard', 'normal');
        }
        
        // Disabilita avviso aggiornamento PHP
        if (!empty($dashboardSettings['disable_php_update'])) {
            remove_action('admin_notices', 'wp_php_update_notice');
        }
    }
    
    public function getBackendMetrics()
    {
        return [
            'optimize_heartbeat' => $this->optimize_heartbeat,
            'limit_revisions' => $this->limit_revisions,
            'optimize_dashboard' => $this->optimize_dashboard,
            'admin_bar' => $this->admin_bar
        ];
    }
    
    /**
     * Ottiene lo status del servizio
     */
    public function getStatus(): array
    {
        return [
            'optimize_heartbeat' => $this->optimize_heartbeat,
            'limit_revisions' => $this->limit_revisions,
            'optimize_dashboard' => $this->optimize_dashboard,
            'admin_bar' => $this->admin_bar
        ];
    }

    /**
     * Ottiene le impostazioni del servizio
     */
    public function getSettings(): array
    {
        // Recupera le impostazioni dal database
        $savedSettings = $this->getOption('fp_ps_backend_optimizer', []);
        
        if (empty($savedSettings)) {
            // Fallback alle proprietà della classe
            return $this->getStatus();
        }
        
        return $savedSettings;
    }
    
    /**
     * Aggiorna le impostazioni del servizio
     * 
     * @param array $settings Nuove impostazioni
     * @return bool True se l'aggiornamento è riuscito
     */
    public function updateSettings(array $settings): bool
    {
        // Recupera le impostazioni esistenti
        $currentSettings = $this->getOption('fp_ps_backend_optimizer', []);
        
        // Merge con le nuove impostazioni
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione e sanitizzazione
        $newSettings['enabled'] = !empty($newSettings['enabled']);
        
        // Valida admin_bar se presente
        if (isset($newSettings['admin_bar']) && is_array($newSettings['admin_bar'])) {
            $newSettings['admin_bar'] = array_merge([
                'disable_frontend' => false,
                'disable_wordpress_logo' => false,
                'disable_updates' => false,
                'disable_comments' => false,
                'disable_new' => false,
                'disable_customize' => false,
            ], $newSettings['admin_bar']);
        }
        
        // Valida dashboard se presente
        if (isset($newSettings['dashboard']) && is_array($newSettings['dashboard'])) {
            $newSettings['dashboard'] = array_merge([
                'disable_welcome' => false,
                'disable_quick_press' => false,
                'disable_activity' => false,
                'disable_primary' => false,
                'disable_secondary' => false,
                'disable_site_health' => false,
                'disable_php_update' => false,
            ], $newSettings['dashboard']);
        }
        
        // Valida heartbeat se presente
        if (isset($newSettings['heartbeat']) && is_array($newSettings['heartbeat'])) {
            $newSettings['heartbeat'] = array_merge([
                'dashboard' => 'default',
                'editor' => 'default',
                'frontend' => 'default',
            ], $newSettings['heartbeat']);
        }
        
        // Valida heartbeat_interval
        if (isset($newSettings['heartbeat_interval'])) {
            $newSettings['heartbeat_interval'] = max(15, min(300, (int) $newSettings['heartbeat_interval']));
        }
        
        // Valida admin_ajax se presente
        if (isset($newSettings['admin_ajax']) && is_array($newSettings['admin_ajax'])) {
            $newSettings['admin_ajax'] = array_merge([
                'disable_heartbeat' => false,
                'disable_autosave' => false,
                'disable_post_lock' => false,
            ], $newSettings['admin_ajax']);
        }
        
        // Salva nel database
        $result = $this->setOption('fp_ps_backend_optimizer', $newSettings, false);
        
        // Aggiorna le proprietà della classe se necessario
        if (isset($newSettings['optimize_heartbeat'])) {
            $this->optimize_heartbeat = $newSettings['optimize_heartbeat'];
        }
        if (isset($newSettings['limit_revisions'])) {
            $this->limit_revisions = $newSettings['limit_revisions'];
        }
        if (isset($newSettings['optimize_dashboard'])) {
            $this->optimize_dashboard = $newSettings['optimize_dashboard'];
        }
        if (isset($newSettings['admin_bar'])) {
            $this->admin_bar = $newSettings['admin_bar'];
        }
        
        // Nota: Le modifiche all'Admin Bar saranno attive dalla prossima pagina caricata
        
        if ($result) {
            // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
            $this->forceInit();
        }
        
        return $result;
    }
    
    /**
     * Rimuove tutti gli hook registrati
     * FIX: Rimuove gli hook esistenti prima di ri-registrarli
     */
    private function removeRegisteredHooks(): void
    {
        // FIX: Rimuovi hook init per heartbeat (metodi specifici)
        remove_action('init', [$this, 'optimizeHeartbeat']);
        remove_action('init', [$this, 'applyHeartbeatSettings']);
        
        // FIX: Rimuovi hook init per revisions
        remove_action('init', [$this, 'limitRevisions']);
        
        // FIX: Rimuovi hook dashboard
        remove_action('wp_dashboard_setup', [$this, 'optimizeDashboard']);
        remove_action('wp_dashboard_setup', [$this, 'customizeDashboardWidgets'], 999);
        
        // FIX: Rimuovi hook admin bar
        remove_action('wp_before_admin_bar_render', [$this, 'customizeAdminBar']);
        
        // FIX: Rimuovi hook init per admin ajax
        remove_action('init', [$this, 'applyAdminAjaxSettings']);
        
        // FIX: Rimuovi filtri heartbeat_settings (potrebbero essere closure, quindi rimuovi tutti)
        // Nota: Questo rimuove anche filtri di altri plugin, ma è necessario per evitare conflitti
        remove_all_filters('heartbeat_settings');
        
        // FIX: Rimuovi filtri show_admin_bar
        remove_all_filters('show_admin_bar');
        
        // FIX: Rimuovi filtri autosave_interval
        remove_all_filters('autosave_interval');
        
        // FIX: Rimuovi hook admin_enqueue_scripts con priority 1 (usati per heartbeat)
        remove_all_actions('admin_enqueue_scripts', 1);
        
        // FIX: Rimuovi hook wp_enqueue_scripts con priority 1 (usati per heartbeat frontend)
        remove_all_actions('wp_enqueue_scripts', 1);
        
        // FIX: Rimuovi hook welcome_panel
        remove_action('welcome_panel', 'wp_welcome_panel');
        
        // FIX: Rimuovi hook admin_notices per PHP update
        remove_action('admin_notices', 'wp_php_update_notice');
        
        // FIX: Rimuovi hook emoji (se registrati)
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        
        // FIX: Rimuovi hook embeds (se registrati)
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        remove_action('rest_api_init', 'wp_oembed_register_route');
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        remove_filter('embed_oembed_discover', '__return_false');
        
        // Reset array tracciamento
        self::$registeredHooks = [];
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // FIX: Rimuovi tutti gli hook esistenti prima di ri-registrarli
        $this->removeRegisteredHooks();
        
        // Ricarica le impostazioni dal database
        $settings = $this->getSettings();
        
        // FIX: Aggiorna le proprietà della classe con le nuove impostazioni
        $this->optimize_heartbeat = !empty($settings['optimize_heartbeat']);
        $this->limit_revisions = !empty($settings['limit_revisions']);
        $this->optimize_dashboard = !empty($settings['optimize_dashboard']) || !empty($settings['remove_dashboard_widgets']);
        $this->admin_bar = !empty($settings['admin_bar']);
        
        // Applica le impostazioni se il servizio è abilitato
        // Nota: init() ora controlla internamente se enabled è true
        $this->init();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}