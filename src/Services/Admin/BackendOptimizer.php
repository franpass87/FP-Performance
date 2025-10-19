<?php

namespace FP\PerfSuite\Services\Admin;

use function add_action;
use function add_filter;
use function array_merge;
use function define;
use function defined;
use function get_current_user_id;
use function get_option;
use function is_admin;
use function max;
use function min;
use function remove_all_actions;
use function remove_meta_box;
use function settings_errors;
use function update_option;
use function wp_dequeue_script;
use function wp_dequeue_style;
use function wp_deregister_script;
use function wp_deregister_style;

/**
 * Backend Performance Optimizer
 *
 * Ottimizza le performance dell'area amministrativa di WordPress
 * riducendo script non necessari, controllando heartbeat, revisioni, ecc.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class BackendOptimizer
{
    /**
     * Opzioni correnti
     *
     * @var array<string, mixed>
     */
    private array $options;

    /**
     * Inizializza il servizio
     */
    public function __construct()
    {
        $this->options = get_option('fp_perf_backend_optimizer', $this->getDefaultOptions());
    }

    /**
     * Ottiene le opzioni predefinite
     *
     * @return array<string, mixed>
     */
    private function getDefaultOptions(): array
    {
        return [
            'heartbeat_enabled' => false,
            'heartbeat_location_dashboard' => 'default', // default, slow, disable
            'heartbeat_location_frontend' => 'disable',
            'heartbeat_location_editor' => 'default',
            'heartbeat_interval' => 60,
            'limit_post_revisions' => false,
            'post_revisions_limit' => 5,
            'autosave_interval' => 120,
            'disable_admin_scripts' => false,
            'admin_scripts_blacklist' => [],
            'disable_dashboard_widgets' => false,
            'dashboard_widgets_blacklist' => [],
            'disable_admin_notices' => false,
            'optimize_admin_ajax' => false,
            'limit_admin_items' => false,
            'items_per_page' => 20,
        ];
    }

    /**
     * Inizializza tutti gli hook
     */
    public function init(): void
    {
        if (!is_admin()) {
            return;
        }

        // Heartbeat Control
        if ($this->options['heartbeat_enabled']) {
            add_action('init', [$this, 'controlHeartbeat']);
            add_filter('heartbeat_settings', [$this, 'modifyHeartbeatSettings']);
        }

        // Post Revisions
        if ($this->options['limit_post_revisions']) {
            $this->limitPostRevisions();
        }

        // Autosave
        $this->configureAutosave();

        // Admin Scripts
        if ($this->options['disable_admin_scripts']) {
            add_action('admin_enqueue_scripts', [$this, 'removeUnnecessaryAdminScripts'], 999);
        }

        // Dashboard Widgets
        if ($this->options['disable_dashboard_widgets']) {
            add_action('wp_dashboard_setup', [$this, 'removeDashboardWidgets'], 999);
        }

        // Admin Notices
        if ($this->options['disable_admin_notices']) {
            add_action('admin_head', [$this, 'hideAdminNotices'], 1);
        }

        // Admin AJAX optimization
        if ($this->options['optimize_admin_ajax']) {
            add_filter('admin_memory_limit', [$this, 'optimizeAdminMemory']);
        }

        // Limit items per page
        if ($this->options['limit_admin_items']) {
            add_filter('edit_posts_per_page', [$this, 'limitItemsPerPage']);
            add_filter('edit_pages_per_page', [$this, 'limitItemsPerPage']);
            add_filter('edit_comments_per_page', [$this, 'limitItemsPerPage']);
        }
    }

    /**
     * Controlla il WordPress Heartbeat API
     */
    public function controlHeartbeat(): void
    {
        global $pagenow;

        $location = 'dashboard';
        
        if ('post.php' === $pagenow || 'post-new.php' === $pagenow) {
            $location = 'editor';
        } elseif (!is_admin()) {
            $location = 'frontend';
        }

        $setting_key = 'heartbeat_location_' . $location;
        $setting = $this->options[$setting_key] ?? 'default';

        if ('disable' === $setting) {
            wp_deregister_script('heartbeat');
        } elseif ('slow' === $setting) {
            // Il rallentamento viene gestito dal filtro heartbeat_settings
        }
    }

    /**
     * Modifica le impostazioni del heartbeat
     *
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    public function modifyHeartbeatSettings(array $settings): array
    {
        global $pagenow;

        $location = 'dashboard';
        
        if ('post.php' === $pagenow || 'post-new.php' === $pagenow) {
            $location = 'editor';
        } elseif (!is_admin()) {
            $location = 'frontend';
        }

        $setting_key = 'heartbeat_location_' . $location;
        $setting = $this->options[$setting_key] ?? 'default';

        if ('slow' === $setting) {
            $settings['interval'] = max(60, (int) $this->options['heartbeat_interval']);
        }

        return $settings;
    }

    /**
     * Limita le revisioni dei post
     */
    private function limitPostRevisions(): void
    {
        $limit = max(1, (int) $this->options['post_revisions_limit']);
        
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', $limit);
        }
    }

    /**
     * Configura l'intervallo di autosalvataggio
     */
    private function configureAutosave(): void
    {
        $interval = max(60, (int) $this->options['autosave_interval']);
        
        if (!defined('AUTOSAVE_INTERVAL')) {
            define('AUTOSAVE_INTERVAL', $interval);
        }
    }

    /**
     * Rimuove script admin non necessari
     */
    public function removeUnnecessaryAdminScripts(): void
    {
        $blacklist = $this->getDefaultAdminScriptsBlacklist();
        
        foreach ($blacklist as $handle) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
            wp_dequeue_style($handle);
            wp_deregister_style($handle);
        }
    }

    /**
     * Lista predefinita di script admin da rimuovere
     *
     * @return array<string>
     */
    private function getDefaultAdminScriptsBlacklist(): array
    {
        global $pagenow;

        $blacklist = [];

        // Rimuovi jQuery UI non necessari se non siamo nell'editor
        if ('post.php' !== $pagenow && 'post-new.php' !== $pagenow) {
            $blacklist = array_merge($blacklist, [
                // 'jquery-ui-core',
                // 'jquery-ui-widget',
                // 'jquery-ui-mouse',
            ]);
        }

        // Aggiungi eventuali script personalizzati dalla blacklist
        if (!empty($this->options['admin_scripts_blacklist'])) {
            $blacklist = array_merge($blacklist, $this->options['admin_scripts_blacklist']);
        }

        return $blacklist;
    }

    /**
     * Rimuove widget dashboard non necessari
     */
    public function removeDashboardWidgets(): void
    {
        global $wp_meta_boxes;

        $widgets_to_remove = $this->getDefaultDashboardWidgetsBlacklist();

        foreach ($widgets_to_remove as $widget_id => $context) {
            remove_meta_box($widget_id, 'dashboard', $context);
        }
    }

    /**
     * Lista predefinita di widget dashboard da rimuovere
     *
     * @return array<string, string>
     */
    private function getDefaultDashboardWidgetsBlacklist(): array
    {
        $widgets = [
            // WordPress Core
            'dashboard_primary' => 'side',              // WordPress News
            'dashboard_secondary' => 'side',            // WordPress Events
            'dashboard_quick_press' => 'side',          // Quick Draft
            'dashboard_recent_drafts' => 'side',        // Recent Drafts
            
            // Yoast SEO
            'wpseo-dashboard-overview' => 'normal',
            'yoast_db_widget' => 'normal',
            
            // WooCommerce
            'woocommerce_dashboard_status' => 'normal',
            'woocommerce_dashboard_recent_reviews' => 'normal',
            
            // Jetpack
            'jetpack_summary_widget' => 'normal',
            
            // Google Analytics
            'ga_dashboard_widget' => 'normal',
        ];

        // Aggiungi widget personalizzati dalla blacklist
        if (!empty($this->options['dashboard_widgets_blacklist'])) {
            foreach ($this->options['dashboard_widgets_blacklist'] as $widget) {
                if (!isset($widgets[$widget])) {
                    $widgets[$widget] = 'normal';
                }
            }
        }

        return $widgets;
    }

    /**
     * Nasconde le notifiche admin
     */
    public function hideAdminNotices(): void
    {
        // Rimuovi notifiche di plugin di terze parti (non le notifiche critiche di WordPress)
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        
        // Mantieni le notifiche di errore critiche
        add_action('admin_notices', function () {
            settings_errors();
        });
    }

    /**
     * Ottimizza il limite di memoria admin
     *
     * @param string $limit
     * @return string
     */
    public function optimizeAdminMemory(string $limit): string
    {
        // Aumenta leggermente il limite per operazioni admin pesanti
        return '256M';
    }

    /**
     * Limita il numero di elementi per pagina nell'admin
     *
     * @param int $limit
     * @return int
     */
    public function limitItemsPerPage(int $limit): int
    {
        return max(10, min(50, (int) $this->options['items_per_page']));
    }

    /**
     * Aggiorna le opzioni
     *
     * @param array<string, mixed> $new_options
     * @return bool
     */
    public function updateOptions(array $new_options): bool
    {
        $this->options = array_merge($this->options, $new_options);
        return update_option('fp_perf_backend_optimizer', $this->options);
    }

    /**
     * Ottiene le opzioni correnti
     *
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Ottiene statistiche sulle ottimizzazioni applicate
     *
     * @return array<string, mixed>
     */
    public function getStats(): array
    {
        $stats = [
            'heartbeat_status' => $this->options['heartbeat_enabled'] ? 'active' : 'inactive',
            'post_revisions_limit' => $this->options['limit_post_revisions'] ? $this->options['post_revisions_limit'] : 'unlimited',
            'autosave_interval' => $this->options['autosave_interval'] . 's',
            'optimizations_active' => 0,
        ];

        // Conta ottimizzazioni attive
        $optimizations = [
            'heartbeat_enabled',
            'limit_post_revisions',
            'disable_admin_scripts',
            'disable_dashboard_widgets',
            'disable_admin_notices',
            'optimize_admin_ajax',
            'limit_admin_items',
        ];

        foreach ($optimizations as $opt) {
            if (!empty($this->options[$opt])) {
                $stats['optimizations_active']++;
            }
        }

        return $stats;
    }

    /**
     * Reimposta alle opzioni predefinite
     *
     * @return bool
     */
    public function resetToDefaults(): bool
    {
        $this->options = $this->getDefaultOptions();
        return update_option('fp_perf_backend_optimizer', $this->options);
    }

    /**
     * Verifica se il backend optimizer è attivo
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $stats = $this->getStats();
        return $stats['optimizations_active'] > 0;
    }

    /**
     * Ottiene suggerimenti per ottimizzazioni
     *
     * @return array<string>
     */
    public function getRecommendations(): array
    {
        $recommendations = [];

        if (!$this->options['heartbeat_enabled']) {
            $recommendations[] = '💡 Attiva il controllo Heartbeat per ridurre le richieste AJAX del 20-30%';
        }

        if (!$this->options['limit_post_revisions']) {
            $recommendations[] = '💡 Limita le revisioni dei post per ridurre la dimensione del database';
        }

        if ((int) $this->options['autosave_interval'] < 120) {
            $recommendations[] = '💡 Aumenta l\'intervallo di autosalvataggio a 120 secondi per ridurre le richieste';
        }

        if (!$this->options['disable_dashboard_widgets']) {
            $recommendations[] = '💡 Rimuovi i widget inutili dalla dashboard per velocizzare il caricamento';
        }

        if ($this->options['items_per_page'] > 30) {
            $recommendations[] = '💡 Riduci il numero di elementi per pagina a 20-30 per migliorare le performance';
        }

        return $recommendations;
    }
}

