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
        if ($this->optimize_heartbeat) {
            add_action('init', [$this, 'optimizeHeartbeat']);
        }
        
        if ($this->limit_revisions) {
            add_action('init', [$this, 'limitRevisions']);
        }
        
        if ($this->optimize_dashboard) {
            add_action('wp_dashboard_setup', [$this, 'optimizeDashboard']);
        }
        
        if (!$this->admin_bar) {
            add_action('wp_before_admin_bar_render', [$this, 'removeAdminBar']);
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
     * Ottiene le impostazioni del servizio
     */
    public function getSettings(): array
    {
        return $this->getStatus();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}