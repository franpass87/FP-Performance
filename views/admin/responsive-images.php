<?php
/**
 * Responsive Images Configuration Page
 *
 * @package FP\PerfSuite\Views\Admin
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

defined('ABSPATH') || exit;

use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;

$optimizer = new ResponsiveImageOptimizer();
$status = $optimizer->status();
$settings = $optimizer->getSettings();
?>

<div class="wrap">
    <h1><?php esc_html_e('Responsive Images Optimization', 'fp-performance-suite'); ?></h1>
    
    <div class="fp-performance-admin">
        <div class="fp-admin-header">
            <h2><?php esc_html_e('Automatic Image Optimization', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Automatically optimize image delivery by detecting display dimensions and serving appropriately sized images to reduce bandwidth and improve LCP scores.', 'fp-performance-suite'); ?>
            </p>
        </div>

        <div class="fp-admin-grid">
            <!-- Status Overview -->
            <div class="fp-admin-card">
                <h3><?php esc_html_e('Status Overview', 'fp-performance-suite'); ?></h3>
                <div class="fp-status-grid">
                    <div class="fp-status-item">
                        <span class="fp-status-label"><?php esc_html_e('Enabled:', 'fp-performance-suite'); ?></span>
                        <span class="fp-status-value <?php echo $status['enabled'] ? 'fp-status-enabled' : 'fp-status-disabled'; ?>">
                            <?php echo $status['enabled'] ? __('Yes', 'fp-performance-suite') : __('No', 'fp-performance-suite'); ?>
                        </span>
                    </div>
                    <div class="fp-status-item">
                        <span class="fp-status-label"><?php esc_html_e('Generate Sizes:', 'fp-performance-suite'); ?></span>
                        <span class="fp-status-value <?php echo $status['generate_sizes'] ? 'fp-status-enabled' : 'fp-status-disabled'; ?>">
                            <?php echo $status['generate_sizes'] ? __('Yes', 'fp-performance-suite') : __('No', 'fp-performance-suite'); ?>
                        </span>
                    </div>
                    <div class="fp-status-item">
                        <span class="fp-status-label"><?php esc_html_e('JS Detection:', 'fp-performance-suite'); ?></span>
                        <span class="fp-status-value <?php echo $status['js_detection'] ? 'fp-status-enabled' : 'fp-status-disabled'; ?>">
                            <?php echo $status['js_detection'] ? __('Yes', 'fp-performance-suite') : __('No', 'fp-performance-suite'); ?>
                        </span>
                    </div>
                    <div class="fp-status-item">
                        <span class="fp-status-label"><?php esc_html_e('Min Dimensions:', 'fp-performance-suite'); ?></span>
                        <span class="fp-status-value"><?php echo esc_html($status['min_dimensions']); ?></span>
                    </div>
                    <div class="fp-status-item">
                        <span class="fp-status-label"><?php esc_html_e('Quality:', 'fp-performance-suite'); ?></span>
                        <span class="fp-status-value"><?php echo esc_html($status['quality']); ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <div class="fp-admin-card">
                <h3><?php esc_html_e('Configuration', 'fp-performance-suite'); ?></h3>
                <form method="post" action="">
                    <?php wp_nonce_field('fp_ps_responsive_images', 'fp_ps_responsive_images_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="enabled"><?php esc_html_e('Enable Responsive Images', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?> />
                                <p class="description">
                                    <?php esc_html_e('Automatically optimize image delivery based on display dimensions.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="generate_sizes"><?php esc_html_e('Generate Missing Sizes', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="generate_sizes" name="generate_sizes" value="1" <?php checked($settings['generate_sizes']); ?> />
                                <p class="description">
                                    <?php esc_html_e('Automatically generate optimized image sizes when needed.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="js_detection"><?php esc_html_e('JavaScript Dimension Detection', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <input type="checkbox" id="js_detection" name="js_detection" value="1" <?php checked($settings['js_detection']); ?> />
                                <p class="description">
                                    <?php esc_html_e('Use JavaScript to detect actual display dimensions for more accurate optimization.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="min_width"><?php esc_html_e('Minimum Width (px)', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="min_width" name="min_width" value="<?php echo esc_attr($settings['min_width']); ?>" min="100" max="2000" />
                                <p class="description">
                                    <?php esc_html_e('Minimum image width to consider for optimization.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="min_height"><?php esc_html_e('Minimum Height (px)', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <input type="number" id="min_height" name="min_height" value="<?php echo esc_attr($settings['min_height']); ?>" min="100" max="2000" />
                                <p class="description">
                                    <?php esc_html_e('Minimum image height to consider for optimization.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="quality"><?php esc_html_e('Image Quality (%)', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <input type="range" id="quality" name="quality" value="<?php echo esc_attr($settings['quality']); ?>" min="60" max="100" step="5" />
                                <span id="quality-value"><?php echo esc_html($settings['quality']); ?>%</span>
                                <p class="description">
                                    <?php esc_html_e('Quality for generated optimized images (higher = better quality, larger file size).', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="fp-admin-actions">
                        <button type="submit" name="save_settings" class="button button-primary">
                            <?php esc_html_e('Save Settings', 'fp-performance-suite'); ?>
                        </button>
                        <button type="submit" name="reset_settings" class="button button-secondary">
                            <?php esc_html_e('Reset to Defaults', 'fp-performance-suite'); ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Performance Impact -->
            <div class="fp-admin-card">
                <h3><?php esc_html_e('Performance Impact', 'fp-performance-suite'); ?></h3>
                <div class="fp-impact-grid">
                    <div class="fp-impact-item">
                        <div class="fp-impact-icon">📊</div>
                        <div class="fp-impact-content">
                            <h4><?php esc_html_e('Lighthouse Score', 'fp-performance-suite'); ?></h4>
                            <p><?php esc_html_e('Improves "Improve image delivery" audit by serving appropriately sized images.', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                    <div class="fp-impact-item">
                        <div class="fp-impact-icon">⚡</div>
                        <div class="fp-impact-content">
                            <h4><?php esc_html_e('LCP Improvement', 'fp-performance-suite'); ?></h4>
                            <p><?php esc_html_e('Reduces Largest Contentful Paint by serving smaller images when appropriate.', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                    <div class="fp-impact-item">
                        <div class="fp-impact-icon">💾</div>
                        <div class="fp-impact-content">
                            <h4><?php esc_html_e('Bandwidth Savings', 'fp-performance-suite'); ?></h4>
                            <p><?php esc_html_e('Reduces bandwidth usage by serving images sized for their display context.', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- How It Works -->
            <div class="fp-admin-card">
                <h3><?php esc_html_e('How It Works', 'fp-performance-suite'); ?></h3>
                <div class="fp-process-steps">
                    <div class="fp-step">
                        <div class="fp-step-number">1</div>
                        <div class="fp-step-content">
                            <h4><?php esc_html_e('Detection', 'fp-performance-suite'); ?></h4>
                            <p><?php esc_html_e('Detects image display dimensions through CSS analysis and JavaScript measurement.', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                    <div class="fp-step">
                        <div class="fp-step-number">2</div>
                        <div class="fp-step-content">
                            <h4><?php esc_html_e('Analysis', 'fp-performance-suite'); ?></h4>
                            <p><?php esc_html_e('Compares display dimensions with original image size to determine if optimization is needed.', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                    <div class="fp-step">
                        <div class="fp-step-number">3</div>
                        <div class="fp-step-content">
                            <h4><?php esc_html_e('Optimization', 'fp-performance-suite'); ?></h4>
                            <p><?php esc_html_e('Generates or selects the most appropriate image size for the display context.', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                    <div class="fp-step">
                        <div class="fp-step-number">4</div>
                        <div class="fp-step-content">
                            <h4><?php esc_html_e('Delivery', 'fp-performance-suite'); ?></h4>
                            <p><?php esc_html_e('Serves the optimized image, reducing bandwidth and improving performance.', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const qualitySlider = document.getElementById('quality');
    const qualityValue = document.getElementById('quality-value');
    
    if (qualitySlider && qualityValue) {
        qualitySlider.addEventListener('input', function() {
            qualityValue.textContent = this.value + '%';
        });
    }
});
</script>

<style>
.fp-impact-grid {
    display: grid;
    gap: 1rem;
}

.fp-impact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.fp-impact-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.fp-impact-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
}

.fp-impact-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.fp-process-steps {
    display: grid;
    gap: 1.5rem;
}

.fp-step {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.fp-step-number {
    width: 2rem;
    height: 2rem;
    background: #0073aa;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.fp-step-content h4 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
}

.fp-step-content p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}
</style>
