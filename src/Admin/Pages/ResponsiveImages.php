<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Admin\Pages\AbstractPage;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;

use function wp_verify_nonce;
use function wp_redirect;
use function admin_url;
use function add_query_arg;
use function esc_html;
use function esc_attr;
use function current_user_can;

/**
 * Responsive Images Configuration Page
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ResponsiveImages extends AbstractPage
{
    private ResponsiveImageOptimizer $optimizer;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->optimizer = new ResponsiveImageOptimizer();
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'fp-performance-suite'));
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSave();
        }

        // Include the view
        include FP_PERF_SUITE_DIR . '/views/admin/responsive-images.php';
    }

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        if (!wp_verify_nonce($_POST['fp_ps_responsive_images_nonce'] ?? '', 'fp_ps_responsive_images')) {
            wp_die(__('Security check failed.', 'fp-performance-suite'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'fp-performance-suite'));
        }

        $settings = [];

        // Handle enabled setting
        $settings['enabled'] = !empty($_POST['enabled']);

        // Handle generate_sizes setting
        $settings['generate_sizes'] = !empty($_POST['generate_sizes']);

        // Handle js_detection setting
        $settings['js_detection'] = !empty($_POST['js_detection']);

        // Handle min_width setting
        $minWidth = (int) ($_POST['min_width'] ?? 300);
        $settings['min_width'] = max(100, min(2000, $minWidth));

        // Handle min_height setting
        $minHeight = (int) ($_POST['min_height'] ?? 300);
        $settings['min_height'] = max(100, min(2000, $minHeight));

        // Handle quality setting
        $quality = (int) ($_POST['quality'] ?? 85);
        $settings['quality'] = max(60, min(100, $quality));

        // Check if reset was requested
        if (!empty($_POST['reset_settings'])) {
            $settings = [
                'enabled' => true,
                'generate_sizes' => true,
                'js_detection' => true,
                'min_width' => 300,
                'min_height' => 300,
                'quality' => 85,
            ];
        }

        // Update settings
        $result = $this->optimizer->updateSettings($settings);

        if ($result) {
            $message = !empty($_POST['reset_settings']) 
                ? __('Settings reset to defaults successfully.', 'fp-performance-suite')
                : __('Settings saved successfully.', 'fp-performance-suite');
            
            $redirectUrl = add_query_arg([
                'page' => 'fp-performance-suite-responsive-images',
                'message' => 'success',
                'message_text' => urlencode($message)
            ], admin_url('admin.php'));
        } else {
            $redirectUrl = add_query_arg([
                'page' => 'fp-performance-suite-responsive-images',
                'message' => 'error',
                'message_text' => urlencode(__('Failed to save settings.', 'fp-performance-suite'))
            ], admin_url('admin.php'));
        }

        wp_redirect($redirectUrl);
        exit;
    }

    /**
     * Get page slug (implementazione metodo astratto)
     */
    public function slug(): string
    {
        return 'fp-performance-suite-responsive-images';
    }

    /**
     * Get page title (implementazione metodo astratto)
     */
    public function title(): string
    {
        return __('Responsive Images', 'fp-performance-suite');
    }

    /**
     * Get view path (implementazione metodo astratto)
     */
    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin/responsive-images.php';
    }

    /**
     * Get page content (implementazione metodo astratto)
     */
    protected function content(): string
    {
        ob_start();
        include $this->view();
        return ob_get_clean();
    }

    /**
     * Backward compatibility - alias per slug()
     */
    public function getSlug(): string
    {
        return $this->slug();
    }

    /**
     * Backward compatibility - alias per title()
     */
    public function getTitle(): string
    {
        return $this->title();
    }
}
