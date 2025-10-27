<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs;

use FP\PerfSuite\Services\Assets\CriticalPathOptimizer;
use FP\PerfSuite\Admin\Components\StatusIndicator;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function wp_nonce_field;

class FontsTab
{
    public function render(array $data): string
    {
        $current_tab = $data['current_tab'];
        $fontSettings = $data['fontSettings'];
        $container = $data['container'];

        // Get Critical Path Optimizer status
        $criticalPathOptimizer = new CriticalPathOptimizer();
        $cpStatus = $criticalPathOptimizer->status();
        $cpSettings = $criticalPathOptimizer->getSettings();

        ob_start();
        ?>
        <!-- TAB: Fonts -->
        <div class="fp-ps-tab-content <?php echo $current_tab === 'fonts' ? 'active' : ''; ?>" data-tab="fonts">
        
        <!-- Intro Panel -->
        <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 10px 0; color: white; font-size: 24px;">
                ⚡ <?php esc_html_e('Font Optimization & Critical Path', 'fp-performance-suite'); ?>
            </h2>
            <p style="margin: 0; font-size: 15px; line-height: 1.6; opacity: 0.95; color: white;">
                <?php esc_html_e('Ottimizza il caricamento dei font e risolvi il problema del Maximum critical path latency (6,414ms) causato dai font che bloccano il rendering.', 'fp-performance-suite'); ?>
            </p>
        </div>
        
        <!-- Status Cards -->
        <div class="fp-ps-grid three fp-ps-mb-xl">
            <?php 
            echo StatusIndicator::renderCard(
                $cpStatus['enabled'] ? 'success' : 'inactive',
                __('Critical Path Optimizer', 'fp-performance-suite'),
                $cpStatus['enabled'] ? __('Ottimizzazione attiva', 'fp-performance-suite') : __('Ottimizzazione disattivata', 'fp-performance-suite'),
                $cpStatus['enabled'] ? __('⚙️ Attivo', 'fp-performance-suite') : __('💤 Disattivo', 'fp-performance-suite')
            );
            
            echo StatusIndicator::renderCard(
                'warning',
                __('Font Critici Rilevati', 'fp-performance-suite'),
                __('Font che impattano il critical path', 'fp-performance-suite'),
                '🔤 ' . esc_html($cpStatus['critical_fonts_count'])
            );
            
            echo StatusIndicator::renderCard(
                $cpSettings['optimize_google_fonts'] ? 'success' : 'inactive',
                __('Google Fonts Optimization', 'fp-performance-suite'),
                $cpSettings['optimize_google_fonts'] ? __('Attiva', 'fp-performance-suite') : __('Disattivata', 'fp-performance-suite'),
                $cpSettings['optimize_google_fonts'] ? __('✅ ON', 'fp-performance-suite') : __('❌ OFF', 'fp-performance-suite')
            );
            ?>
        </div>
        
        <!-- Configuration Form -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Critical Path & Font Settings', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="critical_path_fonts" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Critical Path Optimizer', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="critical_path_enabled" value="1" <?php checked($cpSettings['enabled']); ?>>
                                <?php esc_html_e('Activate critical path optimization for fonts', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Optimizes font loading to reduce critical path latency and improve FCP/LCP.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Preload Critical Fonts', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="preload_critical_fonts" value="1" <?php checked($cpSettings['preload_critical_fonts'] ?? false); ?>>
                                <?php esc_html_e('Preload fonts in the critical rendering path', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Uses <link rel="preload"> to load critical fonts earlier.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Optimize Google Fonts', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="optimize_google_fonts" value="1" <?php checked($cpSettings['optimize_google_fonts'] ?? false); ?>>
                                <?php esc_html_e('Optimize Google Fonts loading', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Adds display=swap and preconnect hints for Google Fonts.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Preconnect to Font Providers', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="preconnect_providers" value="1" <?php checked($cpSettings['preconnect_providers'] ?? false); ?>>
                                <?php esc_html_e('Add preconnect hints for font providers', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Establishes early connections to Google Fonts and other font CDNs.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Inject font-display', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="inject_font_display" value="1" <?php checked($cpSettings['inject_font_display'] ?? false); ?>>
                                <?php esc_html_e('Inject font-display: swap in CSS', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Forces swap behavior on all fonts to prevent FOIT (Flash of Invisible Text).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Add Resource Hints', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="add_resource_hints" value="1" <?php checked($cpSettings['add_resource_hints'] ?? false); ?>>
                                <?php esc_html_e('Add dns-prefetch and preconnect hints automatically', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Automatically adds resource hints for detected font providers.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Preload Local Fonts', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="preload_fonts" value="1" <?php checked($fontSettings['preload_fonts'] ?? false); ?>>
                                <?php esc_html_e('Preload locally hosted fonts', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Preloads woff2 fonts hosted on your server.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Font Settings', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </form>
        </section>
        
        </div>
        <!-- Close TAB: Fonts -->
        <?php
        return ob_get_clean();
    }
}
