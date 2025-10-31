<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Admin\Pages\AbstractPage;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Mobile\MobileOptimizer;
use FP\PerfSuite\Services\Mobile\TouchOptimizer;
use FP\PerfSuite\Services\Mobile\MobileCacheManager;
use FP\PerfSuite\Services\Mobile\ResponsiveImageManager;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;

/**
 * Mobile Optimization Admin Page
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Mobile extends AbstractPage
{
    protected ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-mobile';
    }

    public function title(): string
    {
        return __('Mobile Optimization', 'fp-performance-suite');
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function content(): string
    {
        ob_start();
        $this->render();
        return ob_get_clean();
    }

    public function render(): void
    {
        $this->handleFormSubmission();
        $settings = $this->getSettings();
        
        // Assicurati che il MobileOptimizer sia disponibile
        try {
            $mobile_optimizer = $this->container->get(MobileOptimizer::class);
            $report = $mobile_optimizer->generateMobileReport();
        } catch (\Exception $e) {
            // Fallback se il servizio non è disponibile
            $report = [
                'enabled' => false,
                'settings' => $settings,
                'issues' => [],
                'issues_count' => 0,
                'critical_issues' => 0,
                'recommendations' => []
            ];
        }
        ?>
        <div class="wrap">
            <!-- INTRO BOX -->
            <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                    📱 <?php esc_html_e('Mobile Optimization', 'fp-performance-suite'); ?>
                </h2>
                <p style="margin: 0; font-size: 16px; line-height: 1.6; opacity: 0.95;">
                    <?php esc_html_e('Ottimizza l\'esperienza mobile del tuo sito: animazioni, touch targets, responsive images e cache dedicata per dispositivi mobili.', 'fp-performance-suite'); ?>
                </p>
            </div>
            
            <?php
            // Mostra legenda rischi
            echo RiskLegend::renderLegend();
            ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 30px; margin-top: 30px;">
                <!-- Settings Form -->
                <div class="fp-ps-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2><?php _e('Mobile Optimization Settings', 'fp-performance-suite'); ?></h2>
                    
                    <form method="post" action="?page=fp-performance-suite-mobile">
                        <?php wp_nonce_field('fp_ps_mobile_settings', 'fp_ps_mobile_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="enabled"><?php _e('Enable Mobile Optimization', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('mobile_cache')); ?>" />
                                    <p class="description"><?php _e('Enable mobile-specific optimizations', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="disable_animations"><?php _e('Disable Animations on Mobile', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="disable_animations" name="disable_animations" value="1" <?php checked($settings['disable_animations']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('mobile_disable_animations')); ?>" />
                                    <p class="description"><?php _e('Disable CSS animations on mobile devices for better performance', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="remove_unnecessary_scripts"><?php _e('Remove Unnecessary Scripts', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="remove_unnecessary_scripts" name="remove_unnecessary_scripts" value="1" <?php checked($settings['remove_unnecessary_scripts']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('mobile_remove_scripts')); ?>" />
                                    <p class="description"><?php _e('Remove non-essential scripts on mobile devices', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="optimize_touch_targets"><?php _e('Optimize Touch Targets', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="optimize_touch_targets" name="optimize_touch_targets" value="1" <?php checked($settings['optimize_touch_targets']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('touch_optimization')); ?>" />
                                    <p class="description"><?php _e('Ensure touch targets are at least 44px for better usability', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="enable_responsive_images"><?php _e('Enable Responsive Images', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="enable_responsive_images" name="enable_responsive_images" value="1" <?php checked($settings['enable_responsive_images']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('responsive_images')); ?>" />
                                    <p class="description"><?php _e('Optimize images for mobile devices', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(__('Save Mobile Settings', 'fp-performance-suite')); ?>
                    </form>
                </div>
                
                <!-- Mobile Report -->
                <div class="fp-ps-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2><?php _e('Mobile Performance Report', 'fp-performance-suite'); ?></h2>
                    
                    <div class="fp-ps-mobile-stats">
                        <div class="fp-ps-stat-item">
                            <span class="fp-ps-stat-label"><?php _e('Status', 'fp-performance-suite'); ?></span>
                            <span class="fp-ps-stat-value <?php echo $report['enabled'] ? 'fp-ps-status-enabled' : 'fp-ps-status-disabled'; ?>">
                                <?php echo $report['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                            </span>
                        </div>
                        
                        <div class="fp-ps-stat-item">
                            <span class="fp-ps-stat-label"><?php _e('Issues Found', 'fp-performance-suite'); ?></span>
                            <span class="fp-ps-stat-value"><?php echo esc_html($report['issues_count']); ?></span>
                        </div>
                        
                        <div class="fp-ps-stat-item">
                            <span class="fp-ps-stat-label"><?php _e('Critical Issues', 'fp-performance-suite'); ?></span>
                            <span class="fp-ps-stat-value fp-ps-critical"><?php echo esc_html($report['critical_issues']); ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($report['issues'])): ?>
                        <h3><?php _e('Detected Issues', 'fp-performance-suite'); ?></h3>
                        <div class="fp-ps-issues-list">
                            <?php foreach ($report['issues'] as $issue): ?>
                                <div class="fp-ps-issue fp-ps-issue-<?php echo esc_attr($issue['severity']); ?>">
                                    <strong><?php echo esc_html($issue['message']); ?></strong>
                                    <p><?php echo esc_html($issue['fix']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($report['recommendations'])): ?>
                        <h3><?php _e('Recommendations', 'fp-performance-suite'); ?></h3>
                        <ul class="fp-ps-recommendations">
                            <?php foreach ($report['recommendations'] as $recommendation): ?>
                                <li><?php echo esc_html($recommendation); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <!-- Touch Optimization Settings -->
                <div class="fp-ps-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2><?php _e('Touch Optimization', 'fp-performance-suite'); ?></h2>
                    
                    <?php
                    $touch_settings = get_option('fp_ps_touch_optimizer', []);
                    ?>
                    
                    <form method="post" action="?page=fp-performance-suite-mobile">
                        <?php wp_nonce_field('fp_ps_touch_settings', 'fp_ps_touch_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="touch_enabled"><?php _e('Enable Touch Optimization', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="touch_enabled" name="touch_enabled" value="1" <?php checked($touch_settings['enabled'] ?? false); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('touch_optimization')); ?>" />
                                    <p class="description"><?php _e('Optimize touch interactions for mobile devices', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="disable_hover_effects"><?php _e('Disable Hover Effects', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="disable_hover_effects" name="disable_hover_effects" value="1" <?php checked($touch_settings['disable_hover_effects'] ?? true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('touch_optimization')); ?>" />
                                    <p class="description"><?php _e('Disable hover effects on mobile devices', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="improve_touch_targets"><?php _e('Improve Touch Targets', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="improve_touch_targets" name="improve_touch_targets" value="1" <?php checked($touch_settings['improve_touch_targets'] ?? true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('touch_optimization')); ?>" />
                                    <p class="description"><?php _e('Ensure touch targets meet accessibility guidelines', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="optimize_scroll"><?php _e('Optimize Scroll Performance', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="optimize_scroll" name="optimize_scroll" value="1" <?php checked($touch_settings['optimize_scroll'] ?? true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('touch_optimization')); ?>" />
                                    <p class="description"><?php _e('Improve scroll performance on mobile devices', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="prevent_zoom"><?php _e('Prevent Double-Tap Zoom', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="prevent_zoom" name="prevent_zoom" value="1" <?php checked($touch_settings['prevent_zoom'] ?? true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('touch_optimization')); ?>" />
                                    <p class="description"><?php _e('Prevent accidental zoom on double-tap', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(__('Save Touch Settings', 'fp-performance-suite')); ?>
                    </form>
                </div>
                
                <!-- Responsive Images Settings -->
                <div class="fp-ps-card" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2><?php _e('Responsive Images', 'fp-performance-suite'); ?></h2>
                    
                    <?php
                    $responsive_settings = get_option('fp_ps_responsive_images', []);
                    ?>
                    
                    <form method="post" action="?page=fp-performance-suite-mobile">
                        <?php wp_nonce_field('fp_ps_responsive_settings', 'fp_ps_responsive_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="responsive_enabled"><?php _e('Enable Responsive Images', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="responsive_enabled" name="responsive_enabled" value="1" <?php checked($responsive_settings['enabled'] ?? false); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('responsive_images')); ?>" />
                                    <p class="description"><?php _e('Enable responsive image optimization for mobile', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="enable_lazy_loading"><?php _e('Enable Lazy Loading', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="enable_lazy_loading" name="enable_lazy_loading" value="1" <?php checked($responsive_settings['enable_lazy_loading'] ?? true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('lazy_load_images')); ?>" />
                                    <p class="description"><?php _e('Load images only when they come into view', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="optimize_srcset"><?php _e('Optimize Srcset', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" id="optimize_srcset" name="optimize_srcset" value="1" <?php checked($responsive_settings['optimize_srcset'] ?? true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('responsive_images')); ?>" />
                                    <p class="description"><?php _e('Optimize srcset attributes for mobile devices', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="max_mobile_width"><?php _e('Max Mobile Width', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="max_mobile_width" name="max_mobile_width" value="<?php echo esc_attr($responsive_settings['max_mobile_width'] ?? 768); ?>" min="320" max="1200" />
                                    <p class="description"><?php _e('Maximum image width for mobile devices (px)', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="max_content_image_width"><?php _e('Max Content Image Width', 'fp-performance-suite'); ?></label>
                                </th>
                                <td>
                                    <input type="text" id="max_content_image_width" name="max_content_image_width" value="<?php echo esc_attr($responsive_settings['max_content_image_width'] ?? '100%'); ?>" />
                                    <p class="description"><?php _e('Maximum width for images in content (CSS value)', 'fp-performance-suite'); ?></p>
                                </td>
                            </tr>
                        </table>
                        
                        <?php submit_button(__('Save Responsive Settings', 'fp-performance-suite')); ?>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    private function handleFormSubmission(): void
    {
        // Handle mobile settings
        if (isset($_POST['fp_ps_mobile_nonce']) && wp_verify_nonce($_POST['fp_ps_mobile_nonce'], 'fp_ps_mobile_settings')) {
            $settings = [
                'enabled' => !empty($_POST['enabled']),
                'disable_animations' => !empty($_POST['disable_animations']),
                'remove_unnecessary_scripts' => !empty($_POST['remove_unnecessary_scripts']),
                'optimize_touch_targets' => !empty($_POST['optimize_touch_targets']),
                'enable_responsive_images' => !empty($_POST['enable_responsive_images'])
            ];
            
            update_option('fp_ps_mobile_optimizer', $settings);
            echo '<div class="notice notice-success"><p>' . __('Mobile settings saved successfully!', 'fp-performance-suite') . '</p></div>';
        }
        
        // Handle touch settings
        if (isset($_POST['fp_ps_touch_nonce']) && wp_verify_nonce($_POST['fp_ps_touch_nonce'], 'fp_ps_touch_settings')) {
            $settings = [
                'enabled' => !empty($_POST['touch_enabled']),
                'disable_hover_effects' => !empty($_POST['disable_hover_effects']),
                'improve_touch_targets' => !empty($_POST['improve_touch_targets']),
                'optimize_scroll' => !empty($_POST['optimize_scroll']),
                'prevent_zoom' => !empty($_POST['prevent_zoom'])
            ];
            
            update_option('fp_ps_touch_optimizer', $settings);
            echo '<div class="notice notice-success"><p>' . __('Touch settings saved successfully!', 'fp-performance-suite') . '</p></div>';
        }
        
        // Handle responsive settings
        if (isset($_POST['fp_ps_responsive_nonce']) && wp_verify_nonce($_POST['fp_ps_responsive_nonce'], 'fp_ps_responsive_settings')) {
            $settings = [
                'enabled' => !empty($_POST['responsive_enabled']),
                'enable_lazy_loading' => !empty($_POST['enable_lazy_loading']),
                'optimize_srcset' => !empty($_POST['optimize_srcset']),
                'max_mobile_width' => intval($_POST['max_mobile_width'] ?? 768),
                'max_content_image_width' => sanitize_text_field($_POST['max_content_image_width'] ?? '100%')
            ];
            
            update_option('fp_ps_responsive_images', $settings);
            echo '<div class="notice notice-success"><p>' . __('Responsive settings saved successfully!', 'fp-performance-suite') . '</p></div>';
        }
    }

    private function getSettings(): array
    {
        return get_option('fp_ps_mobile_optimizer', [
            'enabled' => false,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => false,
            'optimize_touch_targets' => false,
            'enable_responsive_images' => false
        ]);
    }
}