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
        add_action('admin_notices', [$this, 'displayPurgeNotices']);
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

        // Get current page URL for redirect after purge
        $current_page = '';
        if (!empty($_SERVER['REQUEST_URI'])) {
            $current_page = urlencode($_SERVER['REQUEST_URI']);
        }

        $purge_url = admin_url('admin.php?page=fp-performance-suite-cache&action=fp_ps_purge_cache&redirect_to=' . $current_page . '&_wpnonce=' . wp_create_nonce('fp_ps_purge_cache'));

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
            $purge_current_url = admin_url('admin.php?page=fp-performance-suite-cache&action=fp_ps_purge_current&current_url=' . urlencode($current_url) . '&redirect_to=' . $current_page . '&_wpnonce=' . wp_create_nonce('fp_ps_purge_current'));
            
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
                
                // Store success message in transient
                set_transient('fp_ps_purge_notice', [
                    'type' => 'success',
                    'message' => __('Cache purged successfully!', 'fp-performance-suite')
                ], 30);

            } elseif ($action === 'fp_ps_purge_current' && isset($_GET['current_url'])) {
                // Purge specific URL
                $url = esc_url_raw($_GET['current_url']);
                $result = $cache->purgeUrl($url);
                
                if ($result) {
                    Logger::info('Cache purged for URL from admin bar', ['url' => $url]);
                    
                    // Store success message in transient
                    set_transient('fp_ps_purge_notice', [
                        'type' => 'success',
                        'message' => __('Cache purged successfully for:', 'fp-performance-suite') . ' ' . $url
                    ], 30);
                } else {
                    Logger::warning('Failed to purge cache for URL from admin bar', ['url' => $url]);
                    
                    // Store warning message in transient
                    set_transient('fp_ps_purge_notice', [
                        'type' => 'warning',
                        'message' => __('Could not purge cache for the specified URL.', 'fp-performance-suite')
                    ], 30);
                }
            }

            // Fire action for other plugins
            do_action('fp_ps_admin_bar_purge', $action);

            // Redirect back to the original page
            $redirect_to = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : '';
            
            if (!empty($redirect_to)) {
                // If we have a redirect_to parameter, redirect back to that page
                $redirect_url = admin_url($redirect_to);
                wp_redirect($redirect_url);
                exit;
            } elseif ($action === 'fp_ps_purge_current' && isset($_GET['current_url'])) {
                // For current page purge, redirect back to that page
                $redirect_url = esc_url_raw($_GET['current_url']);
                wp_redirect($redirect_url);
                exit;
            }
            // If no redirect specified, stay on current page (no redirect)

        } catch (\Exception $e) {
            Logger::error('Failed to purge cache from admin bar', $e);
            
            // Store error message in transient
            set_transient('fp_ps_purge_notice', [
                'type' => 'error',
                'message' => __('Error purging cache:', 'fp-performance-suite') . ' ' . $e->getMessage()
            ], 30);
            
            // Redirect back even on error
            $redirect_to = isset($_GET['redirect_to']) ? urldecode($_GET['redirect_to']) : '';
            if (!empty($redirect_to)) {
                $redirect_url = admin_url($redirect_to);
                wp_redirect($redirect_url);
                exit;
            }
        }
    }

    /**
     * Display purge notices after redirect
     */
    public function displayPurgeNotices(): void
    {
        $notice = get_transient('fp_ps_purge_notice');
        
        if (!$notice || !is_array($notice)) {
            return;
        }
        
        // Delete transient
        delete_transient('fp_ps_purge_notice');
        
        $type = isset($notice['type']) ? $notice['type'] : 'info';
        $message = isset($notice['message']) ? $notice['message'] : '';
        
        if (empty($message)) {
            return;
        }
        
        echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>';
        echo '<strong>' . esc_html__('FP Performance Suite:', 'fp-performance-suite') . '</strong> ';
        echo esc_html($message);
        echo '</p></div>';
    }
}
