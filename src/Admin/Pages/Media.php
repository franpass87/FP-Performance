<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;
use FP\PerfSuite\Admin\Components\StatusIndicator;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function wp_safe_redirect;
use function absint;
use function number_format_i18n;
use function printf;
use function current_user_can;
use function wp_die;
use function add_query_arg;
use function admin_url;

class Media extends AbstractPage
{
    private ?ResponsiveImageOptimizer $responsiveOptimizer = null;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-media';
    }

    public function title(): string
    {
        return __('Media Optimization', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return $this->requiredCapability();
    }

    public function view(): string
    {
        return '';
    }

    protected function content(): string
    {
        return '';
    }

    public function render(): void
    {
        if (!current_user_can($this->capability())) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'fp-performance-suite'));
        }

        // Gestione form submission
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['fp_ps_media_nonce'])) {
            $this->handleFormSubmission();
        }

        $this->renderPage();
    }

    private function handleFormSubmission(): void
    {
        if (!wp_verify_nonce(wp_unslash($_POST['fp_ps_media_nonce'] ?? ''), 'fp_ps_media_settings')) {
            wp_die(__('Security check failed.', 'fp-performance-suite'));
        }

        // Gestione impostazioni responsive images
        $responsiveSettings = [
            'enabled' => !empty($_POST['responsive_enabled']),
            'enable_lazy_loading' => !empty($_POST['responsive_lazy_loading']),
            'optimize_srcset' => !empty($_POST['responsive_srcset']),
            'max_mobile_width' => absint($_POST['responsive_mobile_width'] ?? 768),
            'max_content_image_width' => sanitize_text_field($_POST['responsive_content_width'] ?? '100%'),
        ];

        update_option('fp_ps_responsive_images', $responsiveSettings);

        // Redirect per evitare resubmission
        wp_safe_redirect(add_query_arg(['updated' => '1'], admin_url('admin.php?page=' . $this->slug())));
        exit;
    }

    private function renderPage(): void
    {
        $responsiveSettings = get_option('fp_ps_responsive_images', []);
        $responsiveEnabled = $responsiveSettings['enabled'] ?? false;
        $lazyLoadingEnabled = $responsiveSettings['enable_lazy_loading'] ?? false;
        $srcsetOptimization = $responsiveSettings['optimize_srcset'] ?? false;
        $maxMobileWidth = $responsiveSettings['max_mobile_width'] ?? 768;
        $maxContentWidth = $responsiveSettings['max_content_image_width'] ?? '100%';

        ?>
        <div class="wrap">
            <?php
            // Intro Box con PageIntro Component
            echo PageIntro::render(
                '🖼️',
                __('Media Optimization', 'fp-performance-suite'),
                __('Ottimizza immagini e media: compressione, lazy loading e responsive images per ridurre il peso delle pagine.', 'fp-performance-suite')
            );
            ?>
            
            <?php
            // Mostra legenda rischi
            echo RiskLegend::renderLegend();
            ?>
            
            <?php if (isset($_GET['updated'])): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Settings saved successfully.', 'fp-performance-suite'); ?></p>
                </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px; margin-top: 30px;">
                <div>
                    <div class="fp-ps-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h2><?php esc_html_e('Responsive Images', 'fp-performance-suite'); ?></h2>
                        <p><?php esc_html_e('Optimize images for different screen sizes and devices.', 'fp-performance-suite'); ?></p>
                        
                        <form method="post" action="">
                            <?php wp_nonce_field('fp_ps_media_settings', 'fp_ps_media_nonce'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="responsive_enabled">
                                            <?php esc_html_e('Enable Responsive Images', 'fp-performance-suite'); ?>
                                            <?php echo RiskMatrix::renderIndicator('responsive_images'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="responsive_enabled" name="responsive_enabled" value="1" <?php checked($responsiveEnabled); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('responsive_images')); ?>" />
                                        <p class="description"><?php esc_html_e('Automatically serve optimized images based on device capabilities.', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="responsive_lazy_loading">
                                            <?php esc_html_e('Lazy Loading', 'fp-performance-suite'); ?>
                                            <?php echo RiskMatrix::renderIndicator('lazy_load_images'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="responsive_lazy_loading" name="responsive_lazy_loading" value="1" <?php checked($lazyLoadingEnabled); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('lazy_load_images')); ?>" />
                                        <p class="description"><?php esc_html_e('Load images only when they are about to enter the viewport.', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="responsive_srcset">
                                            <?php esc_html_e('Optimize Srcset', 'fp-performance-suite'); ?>
                                            <?php echo RiskMatrix::renderIndicator('responsive_images'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="checkbox" id="responsive_srcset" name="responsive_srcset" value="1" <?php checked($srcsetOptimization); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('responsive_images')); ?>" />
                                        <p class="description"><?php esc_html_e('Generate optimized srcset attributes for responsive images.', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="responsive_mobile_width"><?php esc_html_e('Max Mobile Width', 'fp-performance-suite'); ?></label>
                                    </th>
                                    <td>
                                        <input type="number" id="responsive_mobile_width" name="responsive_mobile_width" value="<?php echo esc_attr($maxMobileWidth); ?>" min="320" max="1024" />
                                        <p class="description"><?php esc_html_e('Maximum width for mobile devices (in pixels).', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="responsive_content_width"><?php esc_html_e('Max Content Image Width', 'fp-performance-suite'); ?></label>
                                    </th>
                                    <td>
                                        <input type="text" id="responsive_content_width" name="responsive_content_width" value="<?php echo esc_attr($maxContentWidth); ?>" />
                                        <p class="description"><?php esc_html_e('Maximum width for content images (e.g., 100%, 800px).', 'fp-performance-suite'); ?></p>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php submit_button(__('Save Settings', 'fp-performance-suite')); ?>
                        </form>
                    </div>
                </div>
                
                <div>
                    <div class="fp-ps-card" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h3><?php esc_html_e('Media Optimization Status', 'fp-performance-suite'); ?></h3>
                        <div class="fp-ps-status-list">
                            <div class="fp-ps-status-item">
                                <span class="fp-ps-status-label"><?php esc_html_e('Responsive Images:', 'fp-performance-suite'); ?></span>
                                <span class="fp-ps-status-value">
                                    <?php if ($responsiveEnabled): ?>
                                        <span class="fp-ps-status-enabled"><?php esc_html_e('Enabled', 'fp-performance-suite'); ?></span>
                                    <?php else: ?>
                                        <span class="fp-ps-status-disabled"><?php esc_html_e('Disabled', 'fp-performance-suite'); ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <div class="fp-ps-status-item">
                                <span class="fp-ps-status-label"><?php esc_html_e('Lazy Loading:', 'fp-performance-suite'); ?></span>
                                <span class="fp-ps-status-value">
                                    <?php if ($lazyLoadingEnabled): ?>
                                        <span class="fp-ps-status-enabled"><?php esc_html_e('Enabled', 'fp-performance-suite'); ?></span>
                                    <?php else: ?>
                                        <span class="fp-ps-status-disabled"><?php esc_html_e('Disabled', 'fp-performance-suite'); ?></span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .fp-ps-card h2 {
            margin-top: 0;
            color: #23282d;
        }
        
        .fp-ps-card h3 {
            margin-top: 0;
            color: #23282d;
        }
        
        .fp-ps-status-list {
            margin-top: 15px;
        }
        
        .fp-ps-status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f1;
        }
        
        .fp-ps-status-item:last-child {
            border-bottom: none;
        }
        
        .fp-ps-status-label {
            font-weight: 500;
        }
        
        .fp-ps-status-enabled {
            color: #00a32a;
            font-weight: 500;
        }
        
        .fp-ps-status-disabled {
            color: #d63638;
            font-weight: 500;
        }
        
        @media (max-width: 1024px) {
            .wrap > div[style*="grid-template-columns"] {
                grid-template-columns: 1fr !important;
            }
        }
        </style>
        <?php
    }
}