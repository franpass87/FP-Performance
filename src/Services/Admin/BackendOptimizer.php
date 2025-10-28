<?php

namespace FP\PerfSuite\Services\Admin;

class BackendOptimizer
{
    private $optimize_heartbeat;
    private $limit_revisions;
    private $optimize_dashboard;
    private $admin_bar;
    
    public function __construct($optimize_heartbeat = true, $limit_revisions = true, $optimize_dashboard = true, $admin_bar = true)
    {
        $this->optimize_heartbeat = $optimize_heartbeat;
        $this->limit_revisions = $limit_revisions;
        $this->optimize_dashboard = $optimize_dashboard;
        $this->admin_bar = $admin_bar;
    }
    
    public function init()
    {
        // Carica le impostazioni salvate
        $settings = $this->getSettings();
        
        if ($this->optimize_heartbeat) {
            add_action('init', [$this, 'optimizeHeartbeat']);
        }
        
        if ($this->limit_revisions) {
            add_action('init', [$this, 'limitRevisions']);
        }
        
        if ($this->optimize_dashboard) {
            add_action('wp_dashboard_setup', [$this, 'optimizeDashboard']);
        }
        
        // Admin Bar: gestione granulare
        if (!empty($settings['admin_bar'])) {
            // Disabilita completamente sul frontend se richiesto
            if (!empty($settings['admin_bar']['disable_frontend'])) {
                add_filter('show_admin_bar', '__return_false');
            }
            
            // Rimuovi elementi specifici dalla admin bar
            add_action('wp_before_admin_bar_render', [$this, 'customizeAdminBar']);
        }
        
        // Dashboard Widgets: gestione granulare
        if (!empty($settings['dashboard_widgets'])) {
            add_action('wp_dashboard_setup', [$this, 'customizeDashboardWidgets'], 999);
        }
    }
    
    public function optimizeHeartbeat()
    {
        // Reduce heartbeat frequency
        add_filter('heartbeat_settings', function($settings) {
            $settings['interval'] = 60; // 60 seconds instead of 15
            return $settings;
        });
        
        // Disable heartbeat on frontend
        if (!is_admin()) {
            wp_deregister_script('heartbeat');
        }
    }
    
    public function limitRevisions()
    {
        // Limit post revisions
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', 3);
        }
    }
    
    public function optimizeDashboard()
    {
        // Remove unnecessary dashboard widgets
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
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
        global $wp_admin_bar;
        
        if (!$wp_admin_bar) {
            return;
        }
        
        $settings = $this->getSettings();
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
        $settings = $this->getSettings();
        $dashboardSettings = $settings['dashboard_widgets'] ?? [];
        
        // Disabilita pannello di benvenuto
        if (!empty($dashboardSettings['disable_welcome'])) {
            remove_action('welcome_panel', 'wp_welcome_panel');
        }
        
        // Disabilita Quick Press
        if (!empty($dashboardSettings['disable_quick_press'])) {
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        }
        
        // Disabilita Eventi e Notizie WordPress
        if (!empty($dashboardSettings['disable_events_news'])) {
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
        }
        
        // Disabilita Widget Attività
        if (!empty($dashboardSettings['disable_activity'])) {
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        }
        
        // Disabilita Bozze Recenti
        if (!empty($dashboardSettings['disable_recent_drafts'])) {
            remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
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
        $savedSettings = get_option('fp_ps_backend_optimizer', []);
        
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
        $currentSettings = get_option('fp_ps_backend_optimizer', []);
        
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
        $result = update_option('fp_ps_backend_optimizer', $newSettings, false);
        
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
        
        return $result;
    }
    
    /**
     * Forza l'inizializzazione del servizio
     */
    public function forceInit(): void
    {
        // Ricarica le impostazioni dal database
        $settings = $this->getSettings();
        
        // Applica le impostazioni se il servizio è abilitato
        if (!empty($settings['enabled'])) {
            $this->init();
        }
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}