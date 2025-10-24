<?php

namespace FP\PerfSuite\Services\Admin;

use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Utils\BackendRateLimiter;

/**
 * Backend Optimizer
 * 
 * Ottimizza le performance del backend WordPress
 *
 * @package FP\PerfSuite\Services\Admin
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class BackendOptimizer
{
    private const OPTION_KEY = 'fp_ps_backend_optimizer';

    /**
     * Registra l'ottimizzatore (metodo standard per i servizi)
     */
    public function register(): void
    {
        add_action('init', [$this, 'init']);
    }

    /**
     * Inizializza l'ottimizzatore
     */
    public function init(): void
    {
        if (!is_admin()) {
            return;
        }

        $settings = $this->getSettings();

        // Log per debug
        Logger::debug('Backend Optimizer init called', [
            'enabled' => $settings['enabled'] ?? false,
            'settings' => $settings
        ]);

        if (empty($settings['enabled'])) {
            Logger::debug('Backend Optimizer disabled, skipping initialization');
            return;
        }

        // Ottimizzazioni heartbeat
        if (!empty($settings['optimize_heartbeat'])) {
            add_filter('heartbeat_settings', [$this, 'optimizeHeartbeat']);
        }

        // Limita revisioni
        if (!empty($settings['limit_revisions'])) {
            $this->limitRevisions($settings['revisions_limit']);
        }

        // Disabilita autosave
        if (!empty($settings['disable_autosave'])) {
            $this->disableAutosave();
        }

        // Ottimizza dashboard
        if (!empty($settings['optimize_dashboard'])) {
            $this->optimizeDashboard();
        }

        // Rimuovi widget inutili
        if (!empty($settings['remove_dashboard_widgets'])) {
            add_action('wp_dashboard_setup', [$this, 'removeDashboardWidgets']);
        }

        // Ottimizza admin-ajax
        if (!empty($settings['optimize_admin_ajax'])) {
            $this->optimizeAdminAjax();
        }

        // Disabilita screen options pesanti
        if (!empty($settings['disable_heavy_screen_options'])) {
            $this->disableHeavyScreenOptions();
        }

        // Ottimizzazioni Admin Bar
        if (!empty($settings['admin_bar'])) {
            $this->optimizeAdminBar($settings['admin_bar']);
        }

        Logger::debug('Backend Optimizer initialized');
    }

    /**
     * Forza l'inizializzazione (per debug)
     */
    public function forceInit(): void
    {
        Logger::debug('Backend Optimizer force init called');
        $this->init();
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'optimize_heartbeat' => true,
            'heartbeat_interval' => 60,
            'limit_revisions' => true,
            'revisions_limit' => 5,
            'disable_autosave' => false,
            'optimize_dashboard' => true,
            'remove_dashboard_widgets' => true,
            'optimize_admin_ajax' => true,
            'disable_heavy_screen_options' => false,
        ];

        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        // Use rate limiting to prevent configuration overload
        $result = BackendRateLimiter::executeWithLimit('backend_config', function() use ($settings) {
            $current = $this->getSettings();
            $updated = wp_parse_args($settings, $current);

            $result = update_option(self::OPTION_KEY, $updated);
            
            if ($result) {
                Logger::info('Backend Optimizer settings updated', $updated);
            }

            return $result;
        });
        
        if (!$result) {
            Logger::debug('Backend configuration update rate limited');
        }
        
        return $result;
    }

    /**
     * Ottimizza heartbeat API
     */
    public function optimizeHeartbeat(array $settings): array
    {
        $config = $this->getSettings();
        $interval = max(15, (int) $config['heartbeat_interval']);

        $settings['interval'] = $interval;

        return $settings;
    }

    /**
     * Limita revisioni
     */
    private function limitRevisions(int $limit): void
    {
        if (!defined('WP_POST_REVISIONS')) {
            define('WP_POST_REVISIONS', max(1, $limit));
        }
    }

    /**
     * Disabilita autosave
     */
    private function disableAutosave(): void
    {
        add_action('admin_init', function () {
            wp_deregister_script('autosave');
        });
    }

    /**
     * Ottimizza dashboard
     */
    private function optimizeDashboard(): void
    {
        // Rimuovi benvenuto panel
        remove_action('welcome_panel', 'wp_welcome_panel');

        // Disabilita aggiornamenti automatici nel dashboard
        add_filter('pre_site_transient_update_core', '__return_null');
        add_filter('pre_site_transient_update_plugins', '__return_null');
        add_filter('pre_site_transient_update_themes', '__return_null');
    }

    /**
     * Rimuove widget dashboard inutili
     */
    public function removeDashboardWidgets(): void
    {
        global $wp_meta_boxes;

        // Rimuovi widget pesanti
        $widgetsToRemove = [
            'dashboard_incoming_links',        // Link in entrata
            'dashboard_plugins',               // Plugin popolari
            'dashboard_primary',               // Blog WordPress
            'dashboard_secondary',             // Altre notizie WordPress
            'dashboard_quick_press',           // Bozza veloce
            'dashboard_recent_drafts',         // Bozze recenti
            'dashboard_php_nag',               // Avvisi PHP
            'dashboard_browser_nag',           // Avvisi browser
            'health_check_status',             // Stato salute (pesante)
            'dashboard_site_health',           // Salute sito
        ];

        foreach ($widgetsToRemove as $widget) {
            remove_meta_box($widget, 'dashboard', 'normal');
            remove_meta_box($widget, 'dashboard', 'side');
        }
    }

    /**
     * Ottimizza admin-ajax
     */
    private function optimizeAdminAjax(): void
    {
        // Cache admin-ajax per azioni specifiche
        add_action('admin_init', function () {
            if (defined('DOING_AJAX') && DOING_AJAX) {
                // Disabilita alcuni check pesanti durante AJAX
                remove_action('admin_init', '_maybe_update_core');
                remove_action('admin_init', '_maybe_update_plugins');
                remove_action('admin_init', '_maybe_update_themes');
            }
        });
    }

    /**
     * Disabilita screen options pesanti
     */
    private function disableHeavyScreenOptions(): void
    {
        // Nasconde alcune opzioni schermo pesanti
        add_filter('screen_options_show_screen', function ($show) {
            $screen = get_current_screen();
            
            // Liste pesanti
            if (in_array($screen->id, ['edit-post', 'edit-page', 'users'], true)) {
                return false;
            }

            return $show;
        }, 10, 1);
    }

    /**
     * Disabilita notifiche admin pesanti
     */
    public function disableAdminNotices(): void
    {
        global $wp_filter;

        if (isset($wp_filter['admin_notices'])) {
            unset($wp_filter['admin_notices']);
        }

        if (isset($wp_filter['all_admin_notices'])) {
            unset($wp_filter['all_admin_notices']);
        }
    }

    /**
     * Ottimizza elenco post
     */
    public function optimizePostsList(): void
    {
        add_filter('posts_per_page', function ($per_page) {
            if (is_admin()) {
                return min($per_page, 20); // Limita a 20 post per pagina
            }
            return $per_page;
        });
    }

    /**
     * Disabilita emoji in admin
     */
    public function disableEmojisInAdmin(): void
    {
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
    }

    /**
     * Ottimizza menu admin
     */
    public function optimizeAdminMenu(): void
    {
        add_action('admin_menu', function () {
            // Rimuovi menu poco utilizzati se l'utente non è admin
            if (!current_user_can('administrator')) {
                remove_menu_page('tools.php');
            }
        }, 999);
    }

    /**
     * Riduce complessità query admin
     */
    public function optimizeAdminQueries(): void
    {
        add_filter('posts_clauses', function ($clauses, $query) {
            if (is_admin() && $query->is_main_query()) {
                // Evita query complesse non necessarie
                global $wpdb;
                
                // Semplifica ORDER BY
                if (strpos($clauses['orderby'], 'RAND()') !== false) {
                    $clauses['orderby'] = "{$wpdb->posts}.post_date DESC";
                }
            }

            return $clauses;
        }, 10, 2);
    }

    /**
     * Ottiene metriche performance backend
     */
    public function getBackendMetrics(): array
    {
        $metrics = [
            'heartbeat_interval' => $this->getSettings()['heartbeat_interval'],
            'revisions_limit' => defined('WP_POST_REVISIONS') ? WP_POST_REVISIONS : false,
            'autosave_disabled' => !wp_script_is('autosave', 'registered'),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
        ];

        if (function_exists('get_num_queries')) {
            $metrics['queries'] = get_num_queries();
        }

        return $metrics;
    }

    /**
     * Genera report ottimizzazioni
     */
    public function getOptimizationsReport(): array
    {
        $settings = $this->getSettings();
        $metrics = $this->getBackendMetrics();

        $optimizations = [
            'heartbeat' => [
                'enabled' => !empty($settings['optimize_heartbeat']),
                'interval' => $settings['heartbeat_interval'],
                'status' => $settings['heartbeat_interval'] >= 60 ? 'optimal' : 'can_improve',
            ],
            'revisions' => [
                'enabled' => !empty($settings['limit_revisions']),
                'limit' => $settings['revisions_limit'],
                'status' => $settings['revisions_limit'] <= 5 ? 'optimal' : 'can_improve',
            ],
            'autosave' => [
                'disabled' => !empty($settings['disable_autosave']),
                'status' => !empty($settings['disable_autosave']) ? 'optimal' : 'default',
            ],
            'dashboard' => [
                'optimized' => !empty($settings['optimize_dashboard']),
                'widgets_removed' => !empty($settings['remove_dashboard_widgets']),
                'status' => !empty($settings['optimize_dashboard']) ? 'optimal' : 'default',
            ],
            'admin_bar' => [
                'optimized' => !empty($settings['admin_bar']),
                'frontend_disabled' => !empty($settings['admin_bar']['disable_frontend']),
                'logo_removed' => !empty($settings['admin_bar']['disable_wordpress_logo']),
                'updates_removed' => !empty($settings['admin_bar']['disable_updates']),
                'comments_removed' => !empty($settings['admin_bar']['disable_comments']),
                'new_menu_removed' => !empty($settings['admin_bar']['disable_new']),
                'customize_removed' => !empty($settings['admin_bar']['disable_customize']),
                'status' => !empty($settings['admin_bar']) ? 'optimal' : 'default',
            ],
        ];

        return [
            'settings' => $settings,
            'metrics' => $metrics,
            'optimizations' => $optimizations,
            'score' => $this->calculateOptimizationScore($optimizations),
        ];
    }

    /**
     * Calcola score ottimizzazione
     */
    private function calculateOptimizationScore(array $optimizations): int
    {
        $score = 0;
        $maxScore = 100;

        // Heartbeat (25 punti)
        if ($optimizations['heartbeat']['status'] === 'optimal') {
            $score += 25;
        }

        // Revisioni (15 punti)
        if ($optimizations['revisions']['status'] === 'optimal') {
            $score += 15;
        }

        // Autosave (10 punti)
        if ($optimizations['autosave']['status'] === 'optimal') {
            $score += 10;
        }

        // Dashboard (25 punti)
        if ($optimizations['dashboard']['status'] === 'optimal') {
            $score += 25;
        }

        // Admin Bar (25 punti)
        if ($optimizations['admin_bar']['status'] === 'optimal') {
            $score += 25;
        }

        return min($score, $maxScore);
    }

    /**
     * Ottimizza Admin Bar
     */
    private function optimizeAdminBar(array $adminBarSettings): void
    {
        // Disabilita Admin Bar sul frontend
        if (!empty($adminBarSettings['disable_frontend'])) {
            add_filter('show_admin_bar', '__return_false');
        }

        // Rimuovi logo WordPress
        if (!empty($adminBarSettings['disable_wordpress_logo'])) {
            add_action('admin_bar_menu', [$this, 'removeWordPressLogo'], 11);
        }

        // Rimuovi menu aggiornamenti
        if (!empty($adminBarSettings['disable_updates'])) {
            add_action('admin_bar_menu', [$this, 'removeUpdatesMenu'], 11);
        }

        // Rimuovi menu commenti
        if (!empty($adminBarSettings['disable_comments'])) {
            add_action('admin_bar_menu', [$this, 'removeCommentsMenu'], 11);
        }

        // Rimuovi menu "+ Nuovo"
        if (!empty($adminBarSettings['disable_new'])) {
            add_action('admin_bar_menu', [$this, 'removeNewMenu'], 11);
        }

        // Rimuovi link Personalizza
        if (!empty($adminBarSettings['disable_customize'])) {
            add_action('admin_bar_menu', [$this, 'removeCustomizeLink'], 11);
        }
    }

    /**
     * Rimuove il logo WordPress dalla admin bar
     */
    public function removeWordPressLogo(\WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('wp-logo');
    }

    /**
     * Rimuove il menu aggiornamenti dalla admin bar
     */
    public function removeUpdatesMenu(\WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('updates');
    }

    /**
     * Rimuove il menu commenti dalla admin bar
     */
    public function removeCommentsMenu(\WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('comments');
    }

    /**
     * Rimuove il menu "+ Nuovo" dalla admin bar
     */
    public function removeNewMenu(\WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('new-content');
    }

    /**
     * Rimuove il link Personalizza dalla admin bar
     */
    public function removeCustomizeLink(\WP_Admin_Bar $wp_admin_bar): void
    {
        $wp_admin_bar->remove_node('customize');
    }

    /**
     * Status
     */
    public function status(): array
    {
        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'settings' => $this->getSettings(),
            'metrics' => $this->getBackendMetrics(),
            'report' => $this->getOptimizationsReport(),
        ];
    }
}

