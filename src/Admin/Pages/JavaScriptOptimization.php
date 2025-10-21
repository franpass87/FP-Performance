<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;

use function add_action;
use function wp_verify_nonce;
use function wp_redirect;
use function admin_url;
use function add_query_arg;
use function esc_html;
use function esc_attr;
use function checked;
use function selected;

/**
 * JavaScript Optimization Admin Page
 *
 * Manages advanced JavaScript optimizations including:
 * - Unused JavaScript reduction
 * - Code splitting
 * - Tree shaking
 * - Dynamic imports
 * - Conditional loading
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 */
class JavaScriptOptimization
{
    private UnusedJavaScriptOptimizer $unusedOptimizer;
    private CodeSplittingManager $codeSplittingManager;
    private JavaScriptTreeShaker $treeShaker;

    public function __construct()
    {
        $this->unusedOptimizer = new UnusedJavaScriptOptimizer();
        $this->codeSplittingManager = new CodeSplittingManager();
        $this->treeShaker = new JavaScriptTreeShaker();
    }

    public function register(): void
    {
        add_action('admin_post_fp_ps_save_js_optimization', [$this, 'handleSave']);
    }

    /**
     * Render the page
     */
    public function render(): void
    {
        $unusedSettings = $this->unusedOptimizer->settings();
        $codeSplittingSettings = $this->codeSplittingManager->settings();
        $treeShakingSettings = $this->treeShaker->settings();

        $unusedStatus = $this->unusedOptimizer->status();
        $codeSplittingStatus = $this->codeSplittingManager->status();
        $treeShakingStatus = $this->treeShaker->status();

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('JavaScript Optimization', 'fp-performance-suite'); ?></h1>
            
            <div class="fp-ps-admin-header">
                <div class="fp-ps-header-content">
                    <h2><?php esc_html_e('Advanced JavaScript Optimizations', 'fp-performance-suite'); ?></h2>
                    <p><?php esc_html_e('Optimize JavaScript loading and reduce unused code to improve page performance.', 'fp-performance-suite'); ?></p>
                </div>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('fp_ps_js_optimization', 'fp_ps_js_optimization_nonce'); ?>
                <input type="hidden" name="action" value="fp_ps_save_js_optimization">

                <div class="fp-ps-admin-grid">
                    <!-- Unused JavaScript Optimization -->
                    <div class="fp-ps-admin-card">
                        <div class="fp-ps-card-header">
                            <h3><?php esc_html_e('Unused JavaScript Reduction', 'fp-performance-suite'); ?></h3>
                            <div class="fp-ps-status-badge <?php echo $unusedStatus['enabled'] ? 'enabled' : 'disabled'; ?>">
                                <?php echo $unusedStatus['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                            </div>
                        </div>
                        
                        <div class="fp-ps-card-content">
                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="unused_optimization[enabled]" value="1" <?php checked($unusedSettings['enabled']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Enable Unused JavaScript Optimization', 'fp-performance-suite'); ?></span>
                                </label>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="unused_optimization[code_splitting]" value="1" <?php checked($unusedSettings['code_splitting']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Code Splitting', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Split large JavaScript files into smaller chunks.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="unused_optimization[dynamic_imports]" value="1" <?php checked($unusedSettings['dynamic_imports']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Dynamic Imports', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Load JavaScript modules only when needed.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="unused_optimization[conditional_loading]" value="1" <?php checked($unusedSettings['conditional_loading']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Conditional Loading', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Load scripts only on specific pages or conditions.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="unused_optimization[lazy_loading]" value="1" <?php checked($unusedSettings['lazy_loading']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Lazy Loading', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Load scripts on user interaction or scroll.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label for="dynamic_import_threshold"><?php esc_html_e('Dynamic Import Threshold (bytes)', 'fp-performance-suite'); ?></label>
                                <input type="number" id="dynamic_import_threshold" name="unused_optimization[dynamic_import_threshold]" 
                                       value="<?php echo esc_attr($unusedSettings['dynamic_import_threshold']); ?>" 
                                       min="1000" max="1000000" step="1000">
                                <p class="fp-ps-description"><?php esc_html_e('Minimum file size to trigger dynamic import.', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Code Splitting -->
                    <div class="fp-ps-admin-card">
                        <div class="fp-ps-card-header">
                            <h3><?php esc_html_e('Code Splitting', 'fp-performance-suite'); ?></h3>
                            <div class="fp-ps-status-badge <?php echo $codeSplittingStatus['enabled'] ? 'enabled' : 'disabled'; ?>">
                                <?php echo $codeSplittingStatus['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                            </div>
                        </div>
                        
                        <div class="fp-ps-card-content">
                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="code_splitting[enabled]" value="1" <?php checked($codeSplittingSettings['enabled']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Enable Code Splitting', 'fp-performance-suite'); ?></span>
                                </label>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="code_splitting[dynamic_imports]" value="1" <?php checked($codeSplittingSettings['dynamic_imports']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Dynamic Imports', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Load large scripts dynamically.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="code_splitting[route_splitting]" value="1" <?php checked($codeSplittingSettings['route_splitting']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Route-based Splitting', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Split scripts by page routes (home, single, shop, etc.).', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="code_splitting[component_splitting]" value="1" <?php checked($codeSplittingSettings['component_splitting']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Component-based Splitting', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Split scripts by components (slider, map, form, etc.).', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="code_splitting[vendor_chunks]" value="1" <?php checked($codeSplittingSettings['vendor_chunks']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Vendor Chunks', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Group vendor libraries for better caching.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label for="large_script_threshold"><?php esc_html_e('Large Script Threshold (bytes)', 'fp-performance-suite'); ?></label>
                                <input type="number" id="large_script_threshold" name="code_splitting[thresholds][large_script]" 
                                       value="<?php echo esc_attr($codeSplittingSettings['thresholds']['large_script']); ?>" 
                                       min="1000" max="1000000" step="1000">
                                <p class="fp-ps-description"><?php esc_html_e('Minimum size to trigger code splitting.', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Tree Shaking -->
                    <div class="fp-ps-admin-card">
                        <div class="fp-ps-card-header">
                            <h3><?php esc_html_e('Tree Shaking', 'fp-performance-suite'); ?></h3>
                            <div class="fp-ps-status-badge <?php echo $treeShakingStatus['enabled'] ? 'enabled' : 'disabled'; ?>">
                                <?php echo $treeShakingStatus['enabled'] ? __('Enabled', 'fp-performance-suite') : __('Disabled', 'fp-performance-suite'); ?>
                            </div>
                        </div>
                        
                        <div class="fp-ps-card-content">
                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="tree_shaking[enabled]" value="1" <?php checked($treeShakingSettings['enabled']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Enable Tree Shaking', 'fp-performance-suite'); ?></span>
                                </label>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="tree_shaking[dead_code_elimination]" value="1" <?php checked($treeShakingSettings['dead_code_elimination']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Dead Code Elimination', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Remove unreachable code.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="tree_shaking[unused_functions]" value="1" <?php checked($treeShakingSettings['unused_functions']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Remove Unused Functions', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Remove functions that are never called.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="tree_shaking[unused_variables]" value="1" <?php checked($treeShakingSettings['unused_variables']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Remove Unused Variables', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Remove variables that are never used.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="tree_shaking[unused_imports]" value="1" <?php checked($treeShakingSettings['unused_imports']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Remove Unused Imports', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Remove import statements that are never used.', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label class="fp-ps-switch">
                                    <input type="checkbox" name="tree_shaking[aggressive_mode]" value="1" <?php checked($treeShakingSettings['aggressive_mode']); ?>>
                                    <span class="fp-ps-slider"></span>
                                    <span class="fp-ps-label"><?php esc_html_e('Aggressive Mode', 'fp-performance-suite'); ?></span>
                                </label>
                                <p class="fp-ps-description"><?php esc_html_e('Apply more aggressive tree shaking (may break some functionality).', 'fp-performance-suite'); ?></p>
                            </div>

                            <div class="fp-ps-form-group">
                                <label for="minification_threshold"><?php esc_html_e('Minification Threshold (bytes)', 'fp-performance-suite'); ?></label>
                                <input type="number" id="minification_threshold" name="tree_shaking[minification_threshold]" 
                                       value="<?php echo esc_attr($treeShakingSettings['minification_threshold']); ?>" 
                                       min="1000" max="1000000" step="1000">
                                <p class="fp-ps-description"><?php esc_html_e('Minimum file size to trigger minification.', 'fp-performance-suite'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fp-ps-admin-actions">
                    <button type="submit" class="fp-ps-btn fp-ps-btn-primary">
                        <?php esc_html_e('Save Settings', 'fp-performance-suite'); ?>
                    </button>
                    
                    <button type="button" class="fp-ps-btn fp-ps-btn-secondary" onclick="location.reload()">
                        <?php esc_html_e('Reset', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>

            <!-- Status Overview -->
            <div class="fp-ps-admin-card fp-ps-status-overview">
                <div class="fp-ps-card-header">
                    <h3><?php esc_html_e('Optimization Status', 'fp-performance-suite'); ?></h3>
                </div>
                
                <div class="fp-ps-card-content">
                    <div class="fp-ps-status-grid">
                        <div class="fp-ps-status-item">
                            <h4><?php esc_html_e('Unused JavaScript', 'fp-performance-suite'); ?></h4>
                            <p><?php printf(__('Conditional Scripts: %d', 'fp-performance-suite'), $unusedStatus['conditional_scripts']); ?></p>
                            <p><?php printf(__('Lazy Scripts: %d', 'fp-performance-suite'), $unusedStatus['lazy_scripts']); ?></p>
                            <p><?php printf(__('Dynamic Scripts: %d', 'fp-performance-suite'), $unusedStatus['dynamic_scripts']); ?></p>
                        </div>
                        
                        <div class="fp-ps-status-item">
                            <h4><?php esc_html_e('Code Splitting', 'fp-performance-suite'); ?></h4>
                            <p><?php printf(__('Deferred Scripts: %d', 'fp-performance-suite'), $codeSplittingStatus['deferred_scripts']); ?></p>
                            <p><?php printf(__('Dynamic Imports: %d', 'fp-performance-suite'), $codeSplittingStatus['dynamic_imports']); ?></p>
                            <p><?php printf(__('Vendor Chunks: %d', 'fp-performance-suite'), $codeSplittingStatus['vendor_chunks']); ?></p>
                        </div>
                        
                        <div class="fp-ps-status-item">
                            <h4><?php esc_html_e('Tree Shaking', 'fp-performance-suite'); ?></h4>
                            <p><?php echo $treeShakingStatus['dead_code_elimination'] ? __('Dead Code Elimination: Enabled', 'fp-performance-suite') : __('Dead Code Elimination: Disabled', 'fp-performance-suite'); ?></p>
                            <p><?php echo $treeShakingStatus['unused_functions'] ? __('Unused Functions: Enabled', 'fp-performance-suite') : __('Unused Functions: Disabled', 'fp-performance-suite'); ?></p>
                            <p><?php echo $treeShakingStatus['unused_variables'] ? __('Unused Variables: Enabled', 'fp-performance-suite') : __('Unused Variables: Disabled', 'fp-performance-suite'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        if (!wp_verify_nonce($_POST['fp_ps_js_optimization_nonce'] ?? '', 'fp_ps_js_optimization')) {
            wp_die(__('Security check failed', 'fp-performance-suite'));
        }

        // Save unused optimization settings
        if (isset($_POST['unused_optimization'])) {
            $this->unusedOptimizer->update($_POST['unused_optimization']);
        }

        // Save code splitting settings
        if (isset($_POST['code_splitting'])) {
            $this->codeSplittingManager->update($_POST['code_splitting']);
        }

        // Save tree shaking settings
        if (isset($_POST['tree_shaking'])) {
            $this->treeShaker->update($_POST['tree_shaking']);
        }

        wp_redirect(add_query_arg(['page' => 'fp-ps-js-optimization', 'saved' => '1'], admin_url('admin.php')));
        exit;
    }
}
