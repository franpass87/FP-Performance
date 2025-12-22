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
        
        // Registra gli hook CSS immediatamente nel costruttore
        // Usa una variabile per catturare $this nella closure
        $self = $this;
        
        // FIX: Registra gli hook CSS su hooks più precoci per essere sicuri che vengano registrati in tempo
        // admin_init viene eseguito prima di admin_head, quindi registra qui
        add_action('admin_init', function() use ($self) {
            if (!has_action('admin_head', [$self, 'addAdminBarHideCSS'])) {
                add_action('admin_head', [$self, 'addAdminBarHideCSS'], 999);
            }
            if (!has_action('admin_footer', [$self, 'addAdminBarHideCSSJS'])) {
                add_action('admin_footer', [$self, 'addAdminBarHideCSSJS'], 999);
            }
        }, 1); // Priorità 1 per essere sicuri che venga eseguito molto presto
        
        // Frontend hooks
        add_action('wp_head', [$self, 'addAdminBarHideCSS'], 999);
        add_action('wp_footer', [$self, 'addAdminBarHideCSSJS'], 999);
        
        // Se admin_head è già stato eseguito, inietta direttamente nel footer come fallback
        if (is_admin() && did_action('admin_head')) {
            add_action('admin_footer', [$self, 'addAdminBarHideCSS'], 99999);
        }
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
        // FIX: Registra sempre gli hook (controllo abilitazione fatto nei metodi stessi)
        // Questo permette agli hook di rispondere ai cambiamenti delle impostazioni
        
        // Verifica se gli hook sono già stati eseguiti
        $initDone = did_action('init');
        
        // Heartbeat optimization - registra sempre (controllo abilitazione nel metodo)
        if (!$initDone && !has_action('init', [$this, 'optimizeHeartbeat'])) {
            add_action('init', [$this, 'optimizeHeartbeat'], 999);
            self::$registeredHooks['init_heartbeat'] = true;
        }
        
        // Limit revisions - registra sempre (controllo abilitazione nel metodo)
        if (!$initDone && !has_action('init', [$this, 'limitRevisions'])) {
            add_action('init', [$this, 'limitRevisions'], 999);
            self::$registeredHooks['init_revisions'] = true;
        }
        
        // Dashboard optimization - registra sempre (controllo abilitazione nel metodo)
        if (!has_action('wp_dashboard_setup', [$this, 'optimizeDashboard'])) {
            add_action('wp_dashboard_setup', [$this, 'optimizeDashboard'], 999);
            self::$registeredHooks['wp_dashboard_setup'] = true;
        }
        
        // Admin Bar: registra sempre gli hook (controllo abilitazione nel metodo)
        // Disabilita completamente sul frontend
        if (!has_filter('show_admin_bar', [$this, 'filterShowAdminBar'])) {
            add_filter('show_admin_bar', [$this, 'filterShowAdminBar'], 999);
            self::$registeredHooks['show_admin_bar'] = true;
        }
        
        // Rimuovi hook di WordPress che aggiungono nodi all'admin bar
        // WordPress registra gli hook durante add_menus() che viene chiamato dentro _wp_admin_bar_init
        // add_admin_bar_menus viene eseguito DOPO che WordPress ha registrato gli hook ma PRIMA che admin_bar_menu venga eseguito
        // Quindi possiamo rimuovere gli hook durante add_admin_bar_menus
        // FIX: Registra sempre senza controllo has_action per garantire che sia sempre registrato
        if (!has_action('add_admin_bar_menus', [$this, 'removeAdminBarHooks'])) {
            add_action('add_admin_bar_menus', [$this, 'removeAdminBarHooks'], 999);
            self::$registeredHooks['add_admin_bar_menus'] = true;
        }
        
        // Rimuovi nodi dell'admin bar durante admin_bar_menu con priorità molto alta (9999)
        // WordPress registra i suoi hook con priorità 10-70, quindi priorità 9999 viene eseguita dopo tutti
        // Questo permette di rimuovere i nodi dopo che WordPress li ha aggiunti
        // FIX: Registra sempre senza controllo has_action per garantire che sia sempre registrato
        if (!has_action('admin_bar_menu', [$this, 'removeAdminBarNodesAfterAdd'])) {
            add_action('admin_bar_menu', [$this, 'removeAdminBarNodesAfterAdd'], 9999);
            self::$registeredHooks['admin_bar_menu_remove_nodes'] = true;
        }
        
        // Rimuovi anche con wp_before_admin_bar_render come ulteriore fallback
        // Questo viene eseguito dopo admin_bar_menu ma prima che l'admin bar venga renderizzato
        // FIX: Registra sempre senza controllo has_action per garantire che sia sempre registrato
        if (!has_action('wp_before_admin_bar_render', [$this, 'customizeAdminBar'])) {
            add_action('wp_before_admin_bar_render', [$this, 'customizeAdminBar'], 999);
            self::$registeredHooks['wp_before_admin_bar_render'] = true;
        }
        
        // NOTA: Gli hook CSS sono già registrati nel costruttore per essere sicuri che vengano eseguiti
        // Non è necessario registrarli qui di nuovo
        self::$registeredHooks['admin_head_admin_bar_css'] = true;
        self::$registeredHooks['wp_head_admin_bar_css'] = true;
        self::$registeredHooks['wp_footer_admin_bar_css'] = true;
        self::$registeredHooks['admin_footer_admin_bar_css'] = true;
        
        // Dashboard Widgets: registra sempre (controllo abilitazione nel metodo)
        if (!has_action('wp_dashboard_setup', [$this, 'customizeDashboardWidgets'])) {
            add_action('wp_dashboard_setup', [$this, 'customizeDashboardWidgets'], 999);
            self::$registeredHooks['wp_dashboard_setup'] = true;
        }
        
        // Heartbeat API settings - registra sempre (controllo abilitazione nel metodo)
        if (!$initDone && !has_action('init', [$this, 'applyHeartbeatSettings'])) {
            add_action('init', [$this, 'applyHeartbeatSettings'], 999);
            self::$registeredHooks['init_heartbeat'] = true;
        }
        
        // Admin AJAX optimizations - registra sempre (controllo abilitazione nel metodo)
        if (!$initDone && !has_action('init', [$this, 'applyAdminAjaxSettings'])) {
            add_action('init', [$this, 'applyAdminAjaxSettings'], 999);
            self::$registeredHooks['init_admin_ajax'] = true;
        }
        
        // Se init è già stato eseguito ma ci sono impostazioni heartbeat/admin ajax,
        // registrale per hook futuri (admin_enqueue_scripts, wp_enqueue_scripts)
        if ($initDone) {
            // Heartbeat settings per hook futuri
            if (!empty($settings['heartbeat']) || !empty($settings['optimize_heartbeat'])) {
                $this->registerHeartbeatHooksForFuture($settings);
            }
            
            // Admin AJAX settings per hook futuri
            if (!empty($settings['optimize_admin_ajax']) || !empty($settings['admin_ajax'])) {
                $this->registerAdminAjaxHooksForFuture($settings);
            }
        }
    }
    
    /**
     * Registra hook heartbeat per richieste future
     * Usato quando init è già stato eseguito
     */
    private function registerHeartbeatHooksForFuture(array $settings): void
    {
        $heartbeatSettings = $settings['heartbeat'] ?? [];
        $heartbeatInterval = $settings['heartbeat_interval'] ?? 60;
        
        // Dashboard heartbeat
        if (!empty($heartbeatSettings['dashboard'])) {
            if ($heartbeatSettings['dashboard'] === 'disable') {
                add_action('admin_enqueue_scripts', function() {
                    if (is_admin()) {
                        wp_deregister_script('heartbeat');
                    }
                }, 1);
            } elseif ($heartbeatSettings['dashboard'] === 'slow') {
                add_filter('heartbeat_settings', function($hbSettings) use ($heartbeatInterval) {
                    if (is_admin()) {
                        $hbSettings['interval'] = max(120, $heartbeatInterval);
                    }
                    return $hbSettings;
                }, 999);
            }
        }
        
        // Editor heartbeat
        if (!empty($heartbeatSettings['editor'])) {
            if ($heartbeatSettings['editor'] === 'disable') {
                add_action('admin_enqueue_scripts', function() {
                    $screen = get_current_screen();
                    if ($screen && ($screen->is_block_editor() || $screen->post_type)) {
                        wp_deregister_script('heartbeat');
                    }
                }, 1);
            } elseif ($heartbeatSettings['editor'] === 'slow') {
                add_filter('heartbeat_settings', function($hbSettings) use ($heartbeatInterval) {
                    $screen = get_current_screen();
                    if ($screen && ($screen->is_block_editor() || $screen->post_type)) {
                        $hbSettings['interval'] = max(30, $heartbeatInterval);
                    }
                    return $hbSettings;
                }, 999);
            }
        }
        
        // Frontend heartbeat
        if (!empty($heartbeatSettings['frontend'])) {
            if ($heartbeatSettings['frontend'] === 'disable') {
                add_action('wp_enqueue_scripts', function() {
                    wp_deregister_script('heartbeat');
                }, 1);
            } elseif ($heartbeatSettings['frontend'] === 'slow') {
                add_filter('heartbeat_settings', function($hbSettings) use ($heartbeatInterval) {
                    if (!is_admin()) {
                        $hbSettings['interval'] = max(120, $heartbeatInterval);
                    }
                    return $hbSettings;
                }, 999);
            }
        }
        
        // Applica intervallo personalizzato se specificato
        if (!empty($heartbeatInterval) && $heartbeatInterval !== 60) {
            add_filter('heartbeat_settings', function($hbSettings) use ($heartbeatInterval) {
                $hbSettings['interval'] = max(15, min(300, $heartbeatInterval));
                return $hbSettings;
            }, 999);
        }
    }
    
    /**
     * Registra hook admin ajax per richieste future
     * Usato quando init è già stato eseguito
     */
    private function registerAdminAjaxHooksForFuture(array $settings): void
    {
        $adminAjaxSettings = $settings['admin_ajax'] ?? [];
        
        // Intervallo autosave - può essere applicato per richieste future
        if (!empty($settings['autosave_interval'])) {
            $autosaveInterval = max(30, min(300, (int) $settings['autosave_interval']));
            add_filter('autosave_interval', function() use ($autosaveInterval) {
                return $autosaveInterval;
            }, 999);
        }
        
        // Emoji e embeds sono già stati applicati immediatamente in applyAdminAjaxSettingsImmediate
        // ma li registriamo anche per le richieste future
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
            }, 999);
        }
        
        if (!empty($adminAjaxSettings['disable_embeds'])) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');
            remove_action('rest_api_init', 'wp_oembed_register_route');
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            add_filter('embed_oembed_discover', '__return_false', 999);
            remove_filter('wp_head', 'wp_oembed_add_discovery_links');
            remove_filter('wp_head', 'wp_oembed_add_host_js');
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
     * Filtra show_admin_bar per disabilitare admin bar sul frontend
     * 
     * @param bool $show Se mostrare admin bar
     * @return bool
     */
    public function filterShowAdminBar($show): bool
    {
        $settings = $this->getSettings();
        if (empty($settings['enabled']) || empty($settings['admin_bar']['disable_frontend'])) {
            return $show;
        }
        return false;
    }
    
    /**
     * Rimuove gli hook di WordPress che aggiungono nodi all'admin bar
     * Chiamato su add_admin_bar_menus con priorità 999 per rimuovere gli hook dopo che sono stati registrati ma prima che vengano eseguiti
     */
    public function removeAdminBarHooks(): void
    {
        // Carica le impostazioni direttamente dal database senza cache usando get_option() direttamente
        $savedSettings = get_option('fp_ps_backend_optimizer', []);
        $adminBarSettings = $savedSettings['admin_bar'] ?? [];
        
        // Rimuovi hook che aggiungono logo WordPress (priorità 10)
        if (!empty($adminBarSettings['disable_wordpress_logo']) || !empty($adminBarSettings['disable_wp_logo'])) {
            remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
        }
        
        // Rimuovi hook che aggiungono menu aggiornamenti (priorità 50)
        if (!empty($adminBarSettings['disable_updates']) || !empty($adminBarSettings['disable_updates_menu'])) {
            remove_action('admin_bar_menu', 'wp_admin_bar_updates_menu', 50);
        }
        
        // Rimuovi hook che aggiungono menu commenti (priorità 60)
        if (!empty($adminBarSettings['disable_comments']) || !empty($adminBarSettings['disable_comments_menu'])) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
        
        // Rimuovi hook che aggiungono menu "+ Nuovo" (priorità 70)
        if (!empty($adminBarSettings['disable_new']) || !empty($adminBarSettings['disable_new_menu'])) {
            remove_action('admin_bar_menu', 'wp_admin_bar_new_content_menu', 70);
        }
        
        // Rimuovi hook che aggiungono link Personalizza (priorità 40)
        if (!empty($adminBarSettings['disable_customize'])) {
            remove_action('admin_bar_menu', 'wp_admin_bar_customize_menu', 40);
        }
    }
    
    /**
     * Rimuove i nodi dall'admin bar dopo che sono stati aggiunti
     * Chiamato su admin_bar_menu con priorità 9999 per rimuovere i nodi dopo che tutti gli hook sono stati eseguiti
     * WordPress registra i suoi hook con priorità 10-70, quindi priorità 9999 viene eseguita dopo tutti
     */
    public function removeAdminBarNodesAfterAdd($wp_admin_bar): void
    {
        if (!$wp_admin_bar || !is_a($wp_admin_bar, 'WP_Admin_Bar')) {
            return;
        }
        
        // Carica le impostazioni direttamente dal database senza cache
        $savedSettings = get_option('fp_ps_backend_optimizer', []);
        $adminBarSettings = $savedSettings['admin_bar'] ?? [];
        
        $cssRules = [];
        
        // FORCE REMOVAL - rimuovi sempre se le impostazioni lo richiedono
        // Rimuovi logo WordPress - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_wordpress_logo']) || !empty($adminBarSettings['disable_wp_logo'])) {
            $wp_admin_bar->remove_node('wp-logo');
            $wp_admin_bar->remove_node('about');
            $wp_admin_bar->remove_node('wporg');
            // Forza anche la rimozione degli hook
            remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
            $cssRules[] = '#wp-admin-bar-wp-logo { display: none !important; }';
        }
        
        // Rimuovi menu aggiornamenti - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_updates']) || !empty($adminBarSettings['disable_updates_menu'])) {
            $wp_admin_bar->remove_node('updates');
            // Forza anche la rimozione degli hook
            remove_action('admin_bar_menu', 'wp_admin_bar_updates_menu', 50);
            $cssRules[] = '#wp-admin-bar-updates { display: none !important; }';
        }
        
        // Rimuovi menu commenti - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_comments']) || !empty($adminBarSettings['disable_comments_menu'])) {
            $wp_admin_bar->remove_node('comments');
            $cssRules[] = '#wp-admin-bar-comments { display: none !important; }';
        }
        
        // Rimuovi menu "+ Nuovo" - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_new']) || !empty($adminBarSettings['disable_new_menu'])) {
            $wp_admin_bar->remove_node('new-content');
            $cssRules[] = '#wp-admin-bar-new-content { display: none !important; }';
        }
        
        // Rimuovi link Personalizza
        if (!empty($adminBarSettings['disable_customize'])) {
            $wp_admin_bar->remove_node('customize');
            $cssRules[] = '#wp-admin-bar-customize { display: none !important; }';
        }
        
        // Inietta CSS direttamente qui come fallback immediato
        // Usa output buffering per iniettare CSS nell'head anche se admin_head è già stato eseguito
        if (!empty($cssRules)) {
            $cssContent = implode("\n", $cssRules);
            // Inietta CSS direttamente nell'output corrente se possibile
            if (!did_action('admin_head')) {
                add_action('admin_head', function() use ($cssContent) {
                    echo '<style id="fp-ps-hide-admin-bar-items">' . esc_html($cssContent) . '</style>' . "\n";
                }, 99999);
            }
            // Fallback JavaScript nel footer (sempre disponibile)
            add_action('admin_footer', function() use ($cssContent) {
                echo '<script>!function(){var e=document.getElementById("fp-ps-hide-admin-bar-items");if(!e){e=document.createElement("style");e.id="fp-ps-hide-admin-bar-items";document.head.appendChild(e)}e.textContent=' . json_encode($cssContent) . '}();</script>' . "\n";
            }, 99999);
        }
    }
    
    /**
     * Personalizza l'Admin Bar rimuovendo elementi specifici
     */
    public function customizeAdminBar(): void
    {
        global $wp_admin_bar;
        
        if (!$wp_admin_bar) {
            return;
        }
        
        // Carica le impostazioni direttamente dal database senza cache
        $savedSettings = get_option('fp_ps_backend_optimizer', []);
        $adminBarSettings = $savedSettings['admin_bar'] ?? [];
        
        // Rimuovi logo WordPress - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_wordpress_logo']) || !empty($adminBarSettings['disable_wp_logo'])) {
            $wp_admin_bar->remove_node('wp-logo');
            $wp_admin_bar->remove_node('about');
            $wp_admin_bar->remove_node('wporg');
            // Forza anche la rimozione degli hook
            remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10);
        }
        
        // Rimuovi menu aggiornamenti - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_updates']) || !empty($adminBarSettings['disable_updates_menu'])) {
            $wp_admin_bar->remove_node('updates');
            // Forza anche la rimozione degli hook
            remove_action('admin_bar_menu', 'wp_admin_bar_updates_menu', 50);
        }
        
        // Rimuovi menu commenti - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_comments']) || !empty($adminBarSettings['disable_comments_menu'])) {
            $wp_admin_bar->remove_node('comments');
        }
        
        // Rimuovi menu "+ Nuovo" - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_new']) || !empty($adminBarSettings['disable_new_menu'])) {
            $wp_admin_bar->remove_node('new-content');
        }
        
        // Rimuovi link Personalizza
        if (!empty($adminBarSettings['disable_customize'])) {
            $wp_admin_bar->remove_node('customize');
        }
    }
    
    /**
     * Aggiunge CSS per nascondere elementi dell'admin bar come fallback finale
     */
    public function addAdminBarHideCSS(): void
    {
        // Carica le impostazioni direttamente dal database
        $savedSettings = get_option('fp_ps_backend_optimizer', []);
        $adminBarSettings = $savedSettings['admin_bar'] ?? [];
        
        $cssRules = [];
        
        // Nascondi logo WordPress - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_wordpress_logo']) || !empty($adminBarSettings['disable_wp_logo'])) {
            $cssRules[] = '#wp-admin-bar-wp-logo { display: none !important; }';
        }
        
        // Nascondi menu aggiornamenti - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_updates']) || !empty($adminBarSettings['disable_updates_menu'])) {
            $cssRules[] = '#wp-admin-bar-updates { display: none !important; }';
        }
        
        // Nascondi menu commenti
        if (!empty($adminBarSettings['disable_comments']) || !empty($adminBarSettings['disable_comments_menu'])) {
            $cssRules[] = '#wp-admin-bar-comments { display: none !important; }';
        }
        
        // Nascondi menu "+ Nuovo"
        if (!empty($adminBarSettings['disable_new']) || !empty($adminBarSettings['disable_new_menu'])) {
            $cssRules[] = '#wp-admin-bar-new-content { display: none !important; }';
        }
        
        // Nascondi link Personalizza
        if (!empty($adminBarSettings['disable_customize'])) {
            $cssRules[] = '#wp-admin-bar-customize { display: none !important; }';
        }
        
        if (!empty($cssRules)) {
            $cssContent = implode("\n", $cssRules);
            // Inietta CSS con id specifico per identificarlo
            echo '<style id="fp-ps-hide-admin-bar-items">' . esc_html($cssContent) . '</style>' . "\n";
        }
    }
    
    /**
     * Aggiunge CSS via JavaScript nel footer come fallback finale
     * Questo metodo viene SEMPRE chiamato per assicurarsi che il CSS venga iniettato
     */
    public function addAdminBarHideCSSJS(): void
    {
        // Carica le impostazioni direttamente dal database
        $savedSettings = get_option('fp_ps_backend_optimizer', []);
        $adminBarSettings = $savedSettings['admin_bar'] ?? [];
        
        $cssRules = [];
        
        // Nascondi logo WordPress - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_wordpress_logo']) || !empty($adminBarSettings['disable_wp_logo'])) {
            $cssRules[] = '#wp-admin-bar-wp-logo { display: none !important; }';
        }
        
        // Nascondi menu aggiornamenti - controlla entrambe le chiavi possibili
        if (!empty($adminBarSettings['disable_updates']) || !empty($adminBarSettings['disable_updates_menu'])) {
            $cssRules[] = '#wp-admin-bar-updates { display: none !important; }';
        }
        
        // Nascondi menu commenti
        if (!empty($adminBarSettings['disable_comments']) || !empty($adminBarSettings['disable_comments_menu'])) {
            $cssRules[] = '#wp-admin-bar-comments { display: none !important; }';
        }
        
        // Nascondi menu "+ Nuovo"
        if (!empty($adminBarSettings['disable_new']) || !empty($adminBarSettings['disable_new_menu'])) {
            $cssRules[] = '#wp-admin-bar-new-content { display: none !important; }';
        }
        
        // Nascondi link Personalizza
        if (!empty($adminBarSettings['disable_customize'])) {
            $cssRules[] = '#wp-admin-bar-customize { display: none !important; }';
        }
        
        // Inietta CSS via JavaScript nel footer
        if (!empty($cssRules)) {
            $cssContent = implode(' ', $cssRules);
            // Usa IIFE per evitare conflitti con altri script
            echo '<script>!function(){var e=document.getElementById("fp-ps-hide-admin-bar-items");if(!e){e=document.createElement("style");e.id="fp-ps-hide-admin-bar-items";document.head.appendChild(e)}e.textContent=' . json_encode($cssContent) . '}();</script>' . "\n";
        }
    }
    
    /**
     * Enqueue CSS per nascondere elementi dell'admin bar
     */
    public function enqueueAdminBarHideCSS(): void
    {
        // Carica le impostazioni direttamente dal database
        $savedSettings = get_option('fp_ps_backend_optimizer', []);
        $adminBarSettings = $savedSettings['admin_bar'] ?? [];
        
        $cssRules = [];
        
        // Nascondi logo WordPress
        if (!empty($adminBarSettings['disable_wordpress_logo']) || !empty($adminBarSettings['disable_wp_logo'])) {
            $cssRules[] = '#wp-admin-bar-wp-logo { display: none !important; }';
        }
        
        // Nascondi menu aggiornamenti
        if (!empty($adminBarSettings['disable_updates']) || !empty($adminBarSettings['disable_updates_menu'])) {
            $cssRules[] = '#wp-admin-bar-updates { display: none !important; }';
        }
        
        // Nascondi menu commenti
        if (!empty($adminBarSettings['disable_comments']) || !empty($adminBarSettings['disable_comments_menu'])) {
            $cssRules[] = '#wp-admin-bar-comments { display: none !important; }';
        }
        
        // Nascondi menu "+ Nuovo"
        if (!empty($adminBarSettings['disable_new']) || !empty($adminBarSettings['disable_new_menu'])) {
            $cssRules[] = '#wp-admin-bar-new-content { display: none !important; }';
        }
        
        // Nascondi link Personalizza
        if (!empty($adminBarSettings['disable_customize'])) {
            $cssRules[] = '#wp-admin-bar-customize { display: none !important; }';
        }
        
        if (!empty($cssRules)) {
            $cssContent = implode("\n", $cssRules);
            // Assicura che admin-bar sia caricato
            wp_enqueue_style('admin-bar');
            wp_add_inline_style('admin-bar', $cssContent);
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
        
        // Merge con le nuove impostazioni (merge profondo per array annidati)
        $newSettings = $this->arrayMergeDeep($currentSettings, $settings);
        
        // Validazione e sanitizzazione
        $newSettings['enabled'] = !empty($newSettings['enabled']);
        
        // Valida admin_bar se presente - NON sovrascrivere i valori esistenti, solo assicurarsi che le chiavi esistano
        if (isset($newSettings['admin_bar']) && is_array($newSettings['admin_bar'])) {
            $defaults = [
                'disable_frontend' => false,
                'disable_wordpress_logo' => false,
                'disable_updates' => false,
                'disable_comments' => false,
                'disable_new' => false,
                'disable_customize' => false,
            ];
            // Merge mantenendo i valori esistenti (i valori in $newSettings['admin_bar'] hanno priorità)
            $newSettings['admin_bar'] = array_merge($defaults, $newSettings['admin_bar']);
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
        remove_action('add_admin_bar_menus', [$this, 'removeAdminBarHooks'], 999);
        remove_action('admin_bar_menu', [$this, 'removeAdminBarNodesAfterAdd'], 9999);
        remove_action('wp_before_admin_bar_render', [$this, 'customizeAdminBar'], 999);
        remove_action('admin_head', [$this, 'addAdminBarHideCSS'], 999);
        remove_action('wp_head', [$this, 'addAdminBarHideCSS'], 999);
        remove_action('wp_footer', [$this, 'addAdminBarHideCSSJS'], 999);
        remove_action('admin_footer', [$this, 'addAdminBarHideCSSJS'], 999);
        remove_filter('show_admin_bar', [$this, 'filterShowAdminBar'], 999);
        
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
     * Applica immediatamente le modifiche quando possibile (hook già eseguiti)
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
        
        // Se il servizio non è abilitato, non fare nulla
        if (empty($settings['enabled'])) {
            return;
        }
        
        // Verifica se gli hook sono già stati eseguiti
        $initDone = did_action('init');
        $dashboardSetupDone = did_action('wp_dashboard_setup');
        $adminBarRenderDone = did_action('wp_before_admin_bar_render');
        
        // Applica immediatamente le modifiche se gli hook sono già stati eseguiti
        if ($initDone) {
            // Hook init già eseguiti - applica direttamente le modifiche quando possibile
            
            // Admin AJAX optimizations - possono essere applicate immediatamente
            if (!empty($settings['optimize_admin_ajax']) || !empty($settings['admin_ajax'])) {
                $this->applyAdminAjaxSettingsImmediate($settings);
            }
        }
        
        // Dashboard widgets - applica immediatamente se wp_dashboard_setup è già stato eseguito
        if ($dashboardSetupDone && is_admin()) {
            if (!empty($settings['optimize_dashboard']) || !empty($settings['remove_dashboard_widgets'])) {
                $this->optimizeDashboard();
            }
            if (!empty($settings['dashboard'])) {
                $this->customizeDashboardWidgets();
            }
        }
        
        // Admin Bar - applica immediatamente se wp_before_admin_bar_render è già stato eseguito
        if ($adminBarRenderDone) {
            if (!empty($settings['admin_bar'])) {
                $this->customizeAdminBar();
            }
        }
        
        // Registra gli hook per la prossima richiesta (se non già eseguiti) e per hook futuri
        $this->init();
    }
    
    /**
     * Applica immediatamente le ottimizzazioni Admin AJAX
     * Usato quando forceInit() viene chiamato dopo che init è già stato eseguito
     */
    private function applyAdminAjaxSettingsImmediate(array $settings): void
    {
        $adminAjaxSettings = $settings['admin_ajax'] ?? [];
        
        // Disabilita emoji immediatamente
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
        
        // Disabilita embeds immediatamente
        if (!empty($adminAjaxSettings['disable_embeds'])) {
            remove_action('wp_head', 'wp_oembed_add_discovery_links');
            remove_action('wp_head', 'wp_oembed_add_host_js');
            remove_action('rest_api_init', 'wp_oembed_register_route');
            remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
            add_filter('embed_oembed_discover', '__return_false');
            remove_filter('wp_head', 'wp_oembed_add_discovery_links');
            remove_filter('wp_head', 'wp_oembed_add_host_js');
        }
        
        // Intervallo autosave - può essere applicato immediatamente
        if (!empty($settings['autosave_interval'])) {
            $autosaveInterval = max(30, min(300, (int) $settings['autosave_interval']));
            add_filter('autosave_interval', function() use ($autosaveInterval) {
                return $autosaveInterval;
            });
        }
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
        
        // FIX: Assicurati che gli hook CSS siano sempre registrati
        // Gli hook sono già registrati nel costruttore, ma verifica che siano attivi
        // Se admin_head è già stato eseguito, registra per admin_footer
        if (is_admin() && did_action('admin_head')) {
            // admin_head è già stato eseguito, quindi inietta nel footer
            if (!has_action('admin_footer', [$this, 'addAdminBarHideCSS'])) {
                add_action('admin_footer', [$this, 'addAdminBarHideCSS'], 99999);
            }
        }
    }
    
    /**
     * Merge profondo di array (merge ricorsivo per array annidati)
     * 
     * @param array $array1 Array base
     * @param array $array2 Array da mergere
     * @return array Array mergiato
     */
    private function arrayMergeDeep(array $array1, array $array2): array
    {
        $merged = $array1;
        
        foreach ($array2 as $key => $value) {
            if (isset($merged[$key]) && is_array($merged[$key]) && is_array($value)) {
                // Se entrambi sono array, fai merge ricorsivo
                $merged[$key] = $this->arrayMergeDeep($merged[$key], $value);
            } else {
                // Altrimenti sovrascrivi
                $merged[$key] = $value;
            }
        }
        
        return $merged;
    }
}