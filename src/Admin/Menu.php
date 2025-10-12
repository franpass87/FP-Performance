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
            'advanced' => new Advanced($this->container),
            'settings' => new Settings($this->container),
        ];
    }
}
