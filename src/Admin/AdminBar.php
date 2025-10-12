<?php

namespace FP\PerfSuite\Admin;

use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Capabilities;
use FP\PerfSuite\Utils\Logger;

use function add_action;
use function wp_verify_nonce;
use function wp_create_nonce;
use function admin_url;
use function wp_redirect;
use function __;

/**
 * Admin Bar integration for quick cache purge
 */
class AdminBar
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    public function boot(): void
    {
        add_action('admin_bar_menu', [$this, 'addPurgeButton'], 100);
        add_action('admin_init', [$this, 'handlePurgeRequest']);
    }

    /**
     * Add purge cache button to admin bar
     */
    public function addPurgeButton(\WP_Admin_Bar $wp_admin_bar): void
    {
        // Check if user has required capability
        if (!current_user_can(Capabilities::required())) {
            return;
        }

        $purge_url = admin_url('admin.php?page=fp-performance-suite-cache&action=fp_ps_purge_cache&_wpnonce=' . wp_create_nonce('fp_ps_purge_cache'));

        // Add main menu node
        $wp_admin_bar->add_node([
            'id' => 'fp-performance-purge',
            'title' => '<span class="ab-icon dashicons dashicons-performance"></span><span class="ab-label">' . __('Purge Cache', 'fp-performance-suite') . '</span>',
            'href' => $purge_url,
            'meta' => [
                'title' => __('Purge all cache', 'fp-performance-suite'),
            ],
        ]);

        // Add submenu for quick actions
        $wp_admin_bar->add_node([
            'parent' => 'fp-performance-purge',
            'id' => 'fp-performance-purge-all',
            'title' => __('Purge All Cache', 'fp-performance-suite'),
            'href' => $purge_url,
        ]);

        // Add purge current page option (only on frontend)
        if (!is_admin() && !empty($_SERVER['REQUEST_URI'])) {
            $current_url = home_url($_SERVER['REQUEST_URI']);
            $purge_current_url = admin_url('admin.php?page=fp-performance-suite-cache&action=fp_ps_purge_current&current_url=' . urlencode($current_url) . '&_wpnonce=' . wp_create_nonce('fp_ps_purge_current'));
            
            $wp_admin_bar->add_node([
                'parent' => 'fp-performance-purge',
                'id' => 'fp-performance-purge-current',
                'title' => __('Purge Current Page', 'fp-performance-suite'),
                'href' => $purge_current_url,
            ]);
        }

        // Add link to cache settings
        $wp_admin_bar->add_node([
            'parent' => 'fp-performance-purge',
            'id' => 'fp-performance-cache-settings',
            'title' => __('Cache Settings', 'fp-performance-suite'),
            'href' => admin_url('admin.php?page=fp-performance-suite-cache'),
        ]);
    }

    /**
     * Handle purge cache requests from admin bar
     */
    public function handlePurgeRequest(): void
    {
        // Check if it's a purge request
        if (!isset($_GET['action']) || !in_array($_GET['action'], ['fp_ps_purge_cache', 'fp_ps_purge_current'], true)) {
            return;
        }

        // Verify nonce
        $action = sanitize_text_field($_GET['action']);
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], $action)) {
            wp_die(__('Security check failed', 'fp-performance-suite'));
        }

        // Check capability
        if (!current_user_can(Capabilities::required())) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'fp-performance-suite'));
        }

        $cache = $this->container->get(PageCache::class);

        try {
            if ($action === 'fp_ps_purge_cache') {
                // Purge all cache
                $cache->clear();
                
                Logger::info('Cache purged from admin bar');
                
                add_action('admin_notices', function () {
                    echo '<div class="notice notice-success is-dismissible"><p>';
                    echo '<strong>' . esc_html__('FP Performance Suite:', 'fp-performance-suite') . '</strong> ';
                    echo esc_html__('Cache purged successfully!', 'fp-performance-suite');
                    echo '</p></div>';
                });

            } elseif ($action === 'fp_ps_purge_current' && isset($_GET['current_url'])) {
                // Purge specific URL
                $url = esc_url_raw($_GET['current_url']);
                $result = $cache->purgeUrl($url);
                
                if ($result) {
                    Logger::info('Cache purged for URL from admin bar', ['url' => $url]);
                    
                    add_action('admin_notices', function () use ($url) {
                        echo '<div class="notice notice-success is-dismissible"><p>';
                        echo '<strong>' . esc_html__('FP Performance Suite:', 'fp-performance-suite') . '</strong> ';
                        echo esc_html__('Cache purged successfully for:', 'fp-performance-suite') . ' ';
                        echo '<code>' . esc_html($url) . '</code>';
                        echo '</p></div>';
                    });
                } else {
                    Logger::warning('Failed to purge cache for URL from admin bar', ['url' => $url]);
                    
                    add_action('admin_notices', function () {
                        echo '<div class="notice notice-warning is-dismissible"><p>';
                        echo '<strong>' . esc_html__('FP Performance Suite:', 'fp-performance-suite') . '</strong> ';
                        echo esc_html__('Could not purge cache for the specified URL.', 'fp-performance-suite');
                        echo '</p></div>';
                    });
                }
            }

            // Fire action for other plugins
            do_action('fp_ps_admin_bar_purge', $action);

        } catch (\Exception $e) {
            Logger::error('Failed to purge cache from admin bar', $e);
            
            add_action('admin_notices', function () use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>';
                echo '<strong>' . esc_html__('FP Performance Suite:', 'fp-performance-suite') . '</strong> ';
                echo esc_html__('Error purging cache:', 'fp-performance-suite') . ' ';
                echo esc_html($e->getMessage());
                echo '</p></div>';
            });
        }
    }
}
