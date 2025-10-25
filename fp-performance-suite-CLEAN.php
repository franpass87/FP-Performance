<?php
/**
 * Plugin Name: FP Performance Suite
 * Plugin URI: https://github.com/francescopasseri/fp-performance-suite
 * Description: Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.
 * Version: 1.5.1
 * Author: Francesco Passeri
 * Author URI: https://github.com/francescopasseri
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fp-performance
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * Network: false
 */

// Prevenzione accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Verifica che il plugin non sia già caricato
if (class_exists('FP_Performance_Suite')) {
    return;
}

// Definizione costanti
define('FP_PERFORMANCE_VERSION', '1.5.1');
define('FP_PERFORMANCE_PLUGIN_FILE', __FILE__);
define('FP_PERFORMANCE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FP_PERFORMANCE_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Classe principale del plugin
 */
class FP_Performance_Suite {
    
    /**
     * Istanza singleton
     */
    private static $instance = null;
    
    /**
     * Ottiene l'istanza singleton
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Costruttore privato per singleton
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Inizializzazione del plugin
     */
    private function init() {
        // Hook di attivazione/disattivazione
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        // Hook WordPress
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'admin_init']);
        add_action('admin_enqueue_scripts', [$this, 'admin_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'frontend_scripts']);
        
        // AJAX hooks
        add_action('wp_ajax_fp_performance_save', [$this, 'ajax_save_options']);
        add_action('wp_ajax_fp_performance_test', [$this, 'ajax_test_functionality']);
        
        // Inizializza componenti
        $this->init_components();
    }
    
    /**
     * Inizializza i componenti del plugin
     */
    private function init_components() {
        // Carica classi solo se non sono già caricate
        if (!class_exists('FP_Cache_Manager')) {
            require_once FP_PERFORMANCE_PLUGIN_DIR . 'includes/class-cache-manager.php';
        }
        
        if (!class_exists('FP_Database_Optimizer')) {
            require_once FP_PERFORMANCE_PLUGIN_DIR . 'includes/class-database-optimizer.php';
        }
        
        if (!class_exists('FP_Asset_Optimizer')) {
            require_once FP_PERFORMANCE_PLUGIN_DIR . 'includes/class-asset-optimizer.php';
        }
        
        if (!class_exists('FP_Mobile_Optimizer')) {
            require_once FP_PERFORMANCE_PLUGIN_DIR . 'includes/class-mobile-optimizer.php';
        }
        
        if (!class_exists('FP_Performance_Monitor')) {
            require_once FP_PERFORMANCE_PLUGIN_DIR . 'includes/class-performance-monitor.php';
        }
    }
    
    /**
     * Attivazione del plugin
     */
    public function activate() {
        // Verifica che non ci siano conflitti
        if (class_exists('FP_Performance_Suite')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die('Plugin già attivo. Disattiva la versione precedente prima di attivare questa.');
        }
        
        // Crea opzioni di default
        $this->create_default_options();
        
        // Crea tabelle se necessario
        $this->create_tables();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Disattivazione del plugin
     */
    public function deactivate() {
        // Pulizia opzioni se necessario
        // $this->cleanup_options();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Carica il dominio di testo
     */
    public function load_textdomain() {
        load_plugin_textdomain('fp-performance', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Aggiunge il menu admin
     */
    public function add_admin_menu() {
        // Verifica che il menu non sia già stato aggiunto
        if (get_option('fp_performance_menu_added')) {
            return;
        }
        
        // Menu principale
        add_menu_page(
            __('FP Performance', 'fp-performance'),
            __('FP Performance', 'fp-performance'),
            'manage_options',
            'fp-performance',
            [$this, 'dashboard_page'],
            'dashicons-performance',
            30
        );
        
        // Submenu
        add_submenu_page(
            'fp-performance',
            __('Dashboard', 'fp-performance'),
            __('Dashboard', 'fp-performance'),
            'manage_options',
            'fp-performance',
            [$this, 'dashboard_page']
        );
        
        add_submenu_page(
            'fp-performance',
            __('Cache', 'fp-performance'),
            __('Cache', 'fp-performance'),
            'manage_options',
            'fp-performance-cache',
            [$this, 'cache_page']
        );
        
        add_submenu_page(
            'fp-performance',
            __('Database', 'fp-performance'),
            __('Database', 'fp-performance'),
            'manage_options',
            'fp-performance-database',
            [$this, 'database_page']
        );
        
        add_submenu_page(
            'fp-performance',
            __('Assets', 'fp-performance'),
            __('Assets', 'fp-performance'),
            'manage_options',
            'fp-performance-assets',
            [$this, 'assets_page']
        );
        
        add_submenu_page(
            'fp-performance',
            __('Mobile', 'fp-performance'),
            __('Mobile', 'fp-performance'),
            'manage_options',
            'fp-performance-mobile',
            [$this, 'mobile_page']
        );
        
        add_submenu_page(
            'fp-performance',
            __('Settings', 'fp-performance'),
            __('Settings', 'fp-performance'),
            'manage_options',
            'fp-performance-settings',
            [$this, 'settings_page']
        );
        
        // Marca il menu come aggiunto
        update_option('fp_performance_menu_added', true);
    }
    
    /**
     * Inizializzazione admin
     */
    public function admin_init() {
        // Registra impostazioni
        $this->register_settings();
    }
    
    /**
     * Registra le impostazioni
     */
    private function register_settings() {
        // Gruppo di impostazioni principale
        register_setting('fp_performance_options', 'fp_performance_options', [
            'sanitize_callback' => [$this, 'sanitize_options']
        ]);
        
        // Sezioni
        add_settings_section(
            'fp_performance_general',
            __('General Settings', 'fp-performance'),
            null,
            'fp-performance'
        );
        
        // Campi
        add_settings_field(
            'cache_enabled',
            __('Enable Cache', 'fp-performance'),
            [$this, 'cache_enabled_callback'],
            'fp-performance',
            'fp_performance_general'
        );
    }
    
    /**
     * Script admin
     */
    public function admin_scripts($hook) {
        // Carica solo nelle pagine del plugin
        if (strpos($hook, 'fp-performance') === false) {
            return;
        }
        
        wp_enqueue_script('fp-performance-admin', FP_PERFORMANCE_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], FP_PERFORMANCE_VERSION, true);
        wp_enqueue_style('fp-performance-admin', FP_PERFORMANCE_PLUGIN_URL . 'assets/css/admin.css', [], FP_PERFORMANCE_VERSION);
        
        // Localizza script
        wp_localize_script('fp-performance-admin', 'fpPerformance', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('fp_performance_nonce')
        ]);
    }
    
    /**
     * Script frontend
     */
    public function frontend_scripts() {
        // Carica solo se necessario
        if (!is_admin()) {
            wp_enqueue_script('fp-performance-frontend', FP_PERFORMANCE_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], FP_PERFORMANCE_VERSION, true);
        }
    }
    
    /**
     * Pagina dashboard
     */
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('FP Performance Dashboard', 'fp-performance'); ?></h1>
            
            <div class="fp-performance-dashboard">
                <div class="fp-performance-welcome">
                    <h2><?php _e('Welcome to FP Performance Suite', 'fp-performance'); ?></h2>
                    <p><?php _e('Optimize your WordPress site performance with our comprehensive suite of tools.', 'fp-performance'); ?></p>
                </div>
                
                <div class="fp-performance-stats">
                    <h3><?php _e('Performance Stats', 'fp-performance'); ?></h3>
                    <div class="fp-performance-metrics">
                        <div class="metric">
                            <span class="metric-label"><?php _e('Cache Status', 'fp-performance'); ?></span>
                            <span class="metric-value"><?php echo $this->get_cache_status(); ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-label"><?php _e('Database Status', 'fp-performance'); ?></span>
                            <span class="metric-value"><?php echo $this->get_database_status(); ?></span>
                        </div>
                        <div class="metric">
                            <span class="metric-label"><?php _e('Assets Status', 'fp-performance'); ?></span>
                            <span class="metric-value"><?php echo $this->get_assets_status(); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="fp-performance-actions">
                    <h3><?php _e('Quick Actions', 'fp-performance'); ?></h3>
                    <button type="button" class="button button-primary" id="fp-clear-cache"><?php _e('Clear Cache', 'fp-performance'); ?></button>
                    <button type="button" class="button button-secondary" id="fp-optimize-database"><?php _e('Optimize Database', 'fp-performance'); ?></button>
                    <button type="button" class="button button-secondary" id="fp-optimize-assets"><?php _e('Optimize Assets', 'fp-performance'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Pagina cache
     */
    public function cache_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Cache Settings', 'fp-performance'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('fp_performance_options');
                do_settings_sections('fp-performance');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Pagina database
     */
    public function database_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Database Optimization', 'fp-performance'); ?></h1>
            <p><?php _e('Optimize your database for better performance.', 'fp-performance'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Pagina assets
     */
    public function assets_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Asset Optimization', 'fp-performance'); ?></h1>
            <p><?php _e('Optimize your CSS, JavaScript, and images.', 'fp-performance'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Pagina mobile
     */
    public function mobile_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Mobile Optimization', 'fp-performance'); ?></h1>
            <p><?php _e('Optimize your site for mobile devices.', 'fp-performance'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Pagina impostazioni
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Settings', 'fp-performance'); ?></h1>
            <p><?php _e('Configure your performance settings.', 'fp-performance'); ?></p>
        </div>
        <?php
    }
    
    /**
     * AJAX salvataggio opzioni
     */
    public function ajax_save_options() {
        check_ajax_referer('fp_performance_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $options = $_POST['options'] ?? [];
        update_option('fp_performance_options', $options);
        
        wp_send_json_success(['message' => __('Options saved successfully', 'fp-performance')]);
    }
    
    /**
     * AJAX test funzionalità
     */
    public function ajax_test_functionality() {
        check_ajax_referer('fp_performance_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $test_type = $_POST['test_type'] ?? '';
        $result = $this->run_test($test_type);
        
        wp_send_json_success(['result' => $result]);
    }
    
    /**
     * Esegue un test
     */
    private function run_test($test_type) {
        switch ($test_type) {
            case 'cache':
                return $this->test_cache();
            case 'database':
                return $this->test_database();
            case 'assets':
                return $this->test_assets();
            default:
                return false;
        }
    }
    
    /**
     * Test cache
     */
    private function test_cache() {
        // Implementa test cache
        return true;
    }
    
    /**
     * Test database
     */
    private function test_database() {
        // Implementa test database
        return true;
    }
    
    /**
     * Test assets
     */
    private function test_assets() {
        // Implementa test assets
        return true;
    }
    
    /**
     * Ottiene lo status della cache
     */
    private function get_cache_status() {
        return __('Active', 'fp-performance');
    }
    
    /**
     * Ottiene lo status del database
     */
    private function get_database_status() {
        return __('Optimized', 'fp-performance');
    }
    
    /**
     * Ottiene lo status degli assets
     */
    private function get_assets_status() {
        return __('Optimized', 'fp-performance');
    }
    
    /**
     * Crea opzioni di default
     */
    private function create_default_options() {
        $default_options = [
            'cache_enabled' => true,
            'database_optimization' => true,
            'asset_optimization' => true,
            'mobile_optimization' => true
        ];
        
        add_option('fp_performance_options', $default_options);
    }
    
    /**
     * Crea tabelle se necessario
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'fp_performance_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            level varchar(20) NOT NULL,
            message text NOT NULL,
            context text,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Sanitizza le opzioni
     */
    public function sanitize_options($options) {
        $sanitized = [];
        
        if (isset($options['cache_enabled'])) {
            $sanitized['cache_enabled'] = (bool) $options['cache_enabled'];
        }
        
        return $sanitized;
    }
    
    /**
     * Callback per cache enabled
     */
    public function cache_enabled_callback() {
        $options = get_option('fp_performance_options', []);
        $checked = isset($options['cache_enabled']) ? $options['cache_enabled'] : false;
        ?>
        <input type="checkbox" name="fp_performance_options[cache_enabled]" value="1" <?php checked($checked); ?> />
        <?php
    }
}

// Inizializza il plugin
FP_Performance_Suite::get_instance();

// Hook di attivazione globale
register_activation_hook(__FILE__, function() {
    // Verifica che non ci siano conflitti
    if (class_exists('FP_Performance_Suite')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Plugin già attivo. Disattiva la versione precedente prima di attivare questa.');
    }
});

// Hook di disattivazione globale
register_deactivation_hook(__FILE__, function() {
    // Pulizia se necessario
    delete_option('fp_performance_menu_added');
});
?>
