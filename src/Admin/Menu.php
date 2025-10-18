<?php

namespace FP\PerfSuite\Admin;

use FP\PerfSuite\Admin\Pages\Advanced;
use FP\PerfSuite\Admin\Pages\Assets;
use FP\PerfSuite\Admin\Pages\Cache;
use FP\PerfSuite\Admin\Pages\Overview;
use FP\PerfSuite\Admin\Pages\Database;
use FP\PerfSuite\Admin\Pages\Logs;
use FP\PerfSuite\Admin\Pages\Media;
use FP\PerfSuite\Admin\Pages\Presets;
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

        $message = sprintf(
            __('FP Performance Suite: Si è verificato un errore durante l\'attivazione del plugin. Errore: %s', 'fp-performance-suite'),
            esc_html($error['message'] ?? 'Errore sconosciuto')
        );

        printf(
            '<div class="notice notice-warning is-dismissible"><p><strong>%s</strong></p><p><a href="#" class="fp-ps-dismiss-activation-error">%s</a></p></div>',
            $message,
            __('Nascondi questo messaggio', 'fp-performance-suite')
        );

        // Script per dismissare il notice
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.fp-ps-dismiss-activation-error').on('click', function(e) {
                e.preventDefault();
                $.post(ajaxurl, {
                    action: 'fp_ps_dismiss_activation_error',
                    nonce: '<?php echo wp_create_nonce('fp_ps_dismiss_error'); ?>'
                }, function() {
                    location.reload();
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

    public function register(): void
    {
        $pages = $this->pages();
        $capability = Capabilities::required();

        add_menu_page(
            __('FP Performance Suite', 'fp-performance-suite'),
            __('FP Performance', 'fp-performance-suite'),
            $capability,
            'fp-performance-suite',
            [$pages['overview'], 'render'],
            'dashicons-performance',
            59
        );

        add_submenu_page('fp-performance-suite', __('Overview', 'fp-performance-suite'), __('Overview', 'fp-performance-suite'), $capability, 'fp-performance-suite', [$pages['overview'], 'render']);
        add_submenu_page('fp-performance-suite', __('Cache', 'fp-performance-suite'), __('Cache', 'fp-performance-suite'), $capability, 'fp-performance-suite-cache', [$pages['cache'], 'render']);
        add_submenu_page('fp-performance-suite', __('Assets', 'fp-performance-suite'), __('Assets', 'fp-performance-suite'), $capability, 'fp-performance-suite-assets', [$pages['assets'], 'render']);
        add_submenu_page('fp-performance-suite', __('Media', 'fp-performance-suite'), __('Media', 'fp-performance-suite'), $capability, 'fp-performance-suite-media', [$pages['media'], 'render']);
        add_submenu_page('fp-performance-suite', __('Database', 'fp-performance-suite'), __('Database', 'fp-performance-suite'), $capability, 'fp-performance-suite-database', [$pages['database'], 'render']);
        add_submenu_page('fp-performance-suite', __('Presets', 'fp-performance-suite'), __('Presets', 'fp-performance-suite'), $capability, 'fp-performance-suite-presets', [$pages['presets'], 'render']);
        add_submenu_page('fp-performance-suite', __('Logs', 'fp-performance-suite'), __('Logs', 'fp-performance-suite'), $capability, 'fp-performance-suite-logs', [$pages['logs'], 'render']);
        add_submenu_page('fp-performance-suite', __('Tools', 'fp-performance-suite'), __('Tools', 'fp-performance-suite'), $capability, 'fp-performance-suite-tools', [$pages['tools'], 'render']);
        add_submenu_page('fp-performance-suite', __('Compatibility', 'fp-performance-suite'), __('🎨 Compatibility', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-compatibility', [$pages['compatibility'], 'render']);
        add_submenu_page('fp-performance-suite', __('Advanced', 'fp-performance-suite'), __('Advanced', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-advanced', [$pages['advanced'], 'render']);
        add_submenu_page('fp-performance-suite', __('Settings', 'fp-performance-suite'), __('Settings', 'fp-performance-suite'), 'manage_options', 'fp-performance-suite-settings', [$pages['settings'], 'render']);
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
            'database' => new Database($this->container),
            'presets' => new Presets($this->container),
            'logs' => new Logs($this->container),
            'tools' => new Tools($this->container),
            'compatibility' => new Compatibility($this->container),
            'advanced' => new Advanced($this->container),
            'settings' => new Settings($this->container),
        ];
    }
}
