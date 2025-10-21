<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Compression\CompressionManager;

/**
 * Compression Admin Page
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Compression extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-compression';
    }

    public function title(): string
    {
        return __('Compression', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return 'manage_options';
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [
                __('FP Performance', 'fp-performance-suite'),
                __('Compression', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        // Check for success message
        $success_message = '';
        if (isset($_GET['updated']) && $_GET['updated'] === '1') {
            $success_message = __('Compression settings saved.', 'fp-performance-suite');
        }

        // Check for error message
        $error_message = '';
        if (isset($_GET['error']) && $_GET['error'] === '1') {
            $error_message = __('Error saving compression settings.', 'fp-performance-suite');
        }

        // Get compression service
        $compression = $this->container->get(CompressionManager::class);
        $status = $compression->status();
        $info = $compression->getInfo();

        $html = '<div class="fp-ps-admin-page">';
        
        // Success/Error messages
        if (!empty($success_message)) {
            $html .= '<div class="notice notice-success is-dismissible"><p>' . esc_html($success_message) . '</p></div>';
        }
        
        if (!empty($error_message)) {
            $html .= '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
        }

        $html .= '<div class="fp-ps-page-header">';
        $html .= '<h1>' . esc_html($this->title()) . '</h1>';
        $html .= '<p class="description">' . __('Configure compression settings for optimal performance.', 'fp-performance-suite') . '</p>';
        $html .= '</div>';

        $html .= '<form method="post" action="' . admin_url('admin-post.php') . '" class="fp-ps-form">';
        $html .= wp_nonce_field('fp_ps_save_compression', 'fp_ps_nonce', true, false);
        $html .= '<input type="hidden" name="action" value="fp_ps_save_compression">';

        // Compression Status
        $html .= '<div class="fp-ps-section">';
        $html .= '<h2>' . __('Compression Status', 'fp-performance-suite') . '</h2>';
        $html .= '<div class="fp-ps-status-grid">';
        
        $html .= '<div class="fp-ps-status-item">';
        $html .= '<h3>' . __('Gzip Support', 'fp-performance-suite') . '</h3>';
        $html .= '<div class="fp-ps-status ' . ($status['gzip_supported'] ? 'success' : 'error') . '">';
        $html .= $status['gzip_supported'] ? '✅ ' . __('Supported', 'fp-performance-suite') : '❌ ' . __('Not Supported', 'fp-performance-suite');
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="fp-ps-status-item">';
        $html .= '<h3>' . __('Brotli Support', 'fp-performance-suite') . '</h3>';
        $html .= '<div class="fp-ps-status ' . ($status['brotli_supported'] ? 'success' : 'warning') . '">';
        $html .= $status['brotli_supported'] ? '✅ ' . __('Supported', 'fp-performance-suite') : '⚠️ ' . __('Not Supported', 'fp-performance-suite');
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div class="fp-ps-status-item">';
        $html .= '<h3>' . __('Current Status', 'fp-performance-suite') . '</h3>';
        $html .= '<div class="fp-ps-status ' . ($status['enabled'] ? 'success' : 'info') . '">';
        $html .= $status['enabled'] ? '✅ ' . __('Enabled', 'fp-performance-suite') : 'ℹ️ ' . __('Disabled', 'fp-performance-suite');
        $html .= '</div>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        // Compression Settings
        $html .= '<div class="fp-ps-section">';
        $html .= '<h2>' . __('Compression Settings', 'fp-performance-suite') . '</h2>';
        
        $html .= '<div class="fp-ps-form-group">';
        $html .= '<label class="fp-ps-checkbox-label">';
        $html .= '<input type="checkbox" name="compression[enabled]" value="1" ' . checked($status['enabled'], true, false) . '>';
        $html .= '<span class="fp-ps-checkbox-text">' . __('Enable Compression', 'fp-performance-suite') . '</span>';
        $html .= '</label>';
        $html .= '<p class="description">' . __('Enable Gzip/Brotli compression for better performance.', 'fp-performance-suite') . '</p>';
        $html .= '</div>';

        if ($status['brotli_supported']) {
            $html .= '<div class="fp-ps-form-group">';
            $html .= '<label class="fp-ps-checkbox-label">';
            $html .= '<input type="checkbox" name="compression[brotli_enabled]" value="1" ' . checked($status['brotli_enabled'], true, false) . '>';
            $html .= '<span class="fp-ps-checkbox-text">' . __('Enable Brotli Compression', 'fp-performance-suite') . '</span>';
            $html .= '</label>';
            $html .= '<p class="description">' . __('Brotli provides better compression than Gzip.', 'fp-performance-suite') . '</p>';
            $html .= '</div>';
        }

        $html .= '</div>';

        // Server Information
        if (!empty($info)) {
            $html .= '<div class="fp-ps-section">';
            $html .= '<h2>' . __('Server Information', 'fp-performance-suite') . '</h2>';
            $html .= '<div class="fp-ps-info-grid">';
            
            foreach ($info as $key => $value) {
                $html .= '<div class="fp-ps-info-item">';
                $html .= '<strong>' . esc_html(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ';
                $html .= '<span>' . esc_html($value) . '</span>';
                $html .= '</div>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }

        // Save Button
        $html .= '<div class="fp-ps-form-actions">';
        $html .= '<button type="submit" class="button button-primary button-large">';
        $html .= '<span class="dashicons dashicons-saved" style="vertical-align: middle; margin-right: 5px;"></span>';
        $html .= __('Save Compression Settings', 'fp-performance-suite');
        $html .= '</button>';
        $html .= '</div>';

        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        // Verify nonce
        if (!isset($_POST['fp_ps_nonce']) || !wp_verify_nonce($_POST['fp_ps_nonce'], 'fp_ps_save_compression')) {
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&error=1'));
            exit;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&error=1'));
            exit;
        }

        try {
            // Get compression service
            $compression = $this->container->get(CompressionManager::class);
            
            // Save compression settings
            $enabled = !empty($_POST['compression']['enabled']);
            $brotli_enabled = !empty($_POST['compression']['brotli_enabled']);
            
            // Update settings
            update_option('fp_ps_compression_enabled', $enabled);
            update_option('fp_ps_compression_brotli_enabled', $brotli_enabled);
            
            // Apply settings
            if ($enabled) {
                $compression->enable();
            } else {
                $compression->disable();
            }
            
            // Redirect with success
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&updated=1'));
            exit;
            
        } catch (\Exception $e) {
            error_log('[FP Performance Suite] Compression save error: ' . $e->getMessage());
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&error=1'));
            exit;
        }
    }
}
