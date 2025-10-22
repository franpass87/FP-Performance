<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs;

use FP\PerfSuite\Services\Assets\UnusedCSSOptimizer;
use FP\PerfSuite\Services\Assets\CriticalCss;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function wp_nonce_field;

class CssTab
{
    public function render(array $data): string
    {
        $current_tab = $data['current_tab'];
        $settings = $data['settings'];
        $excludeCss = $data['excludeCss'] ?? null;

        ob_start();
        ?>
        <!-- TAB: CSS -->
        <div class="fp-ps-tab-content <?php echo $current_tab === 'css' ? 'active' : ''; ?>" data-tab="css">
        
        <!-- SECTION 1: Base CSS Optimization -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('CSS Base Optimization', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="css" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine CSS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red">
                            <div class="fp-ps-risk-tooltip red">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">🔴</span>
                                    <?php esc_html_e('Rischio Alto', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Combina tutti i file CSS in un unico file per ridurre le richieste HTTP.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Alto rischio di rottura del layout, problemi con media queries e specificity CSS.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ Sconsigliato: Usa HTTP/2 invece, che gestisce meglio file multipli. Attiva solo se assolutamente necessario e testa molto bene.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="combine_css" value="1" <?php checked($settings['combine_css']); ?> data-risk="red" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify inline CSS', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="minify_inline_css" value="1" <?php checked($settings['minify_inline_css'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Minify CSS code embedded directly in HTML <style> tags. Reduces HTML size.', 'fp-performance-suite'); ?>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove CSS/JS comments', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="remove_comments" value="1" <?php checked($settings['remove_comments'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Strip all comments from CSS and JavaScript files during optimization.', 'fp-performance-suite'); ?>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Optimize Google Fonts loading', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="optimize_google_fonts_assets" value="1" <?php checked($settings['optimize_google_fonts'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Add display=swap and preconnect hints for Google Fonts. Improves FCP score.', 'fp-performance-suite'); ?>
                </p>
                
                <div style="background: #f0f6fc; border: 2px solid #0969da; border-radius: 6px; padding: 15px; margin: 20px 0;">
                    <h4 style="margin-top: 0; color: #0969da;">🎨 <?php esc_html_e('Smart CSS Detection', 'fp-performance-suite'); ?></h4>
                    <p style="font-size: 13px; margin-bottom: 10px;">
                        <?php esc_html_e('Let the AI detect CSS files that should not be optimized (combined/minified) automatically.', 'fp-performance-suite'); ?>
                    </p>
                    <button type="submit" name="auto_detect_exclude_css" class="button button-secondary">
                        🔍 <?php esc_html_e('Auto-Detect CSS to Exclude', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <?php if ($excludeCss) : ?>
                    <div style="background: white; border: 2px solid #059669; border-radius: 6px; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #059669;">✨ <?php esc_html_e('CSS Files to Exclude Detected', 'fp-performance-suite'); ?></h4>
                        
                        <?php if (!empty($excludeCss['plugin_specific'])) : ?>
                            <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                                🔌 <?php esc_html_e('Plugin-Specific CSS', 'fp-performance-suite'); ?>
                            </h5>
                            <?php foreach ($excludeCss['plugin_specific'] as $item) : ?>
                                <div style="background: #fef3c7; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                    <strong><?php echo esc_html($item['handle']); ?></strong><br>
                                    <?php if (!empty($item['src'])) : ?>
                                        <small style="color: #555;"><?php echo esc_html($item['src']); ?></small><br>
                                    <?php endif; ?>
                                    <em style="color: #b45309;"><?php echo esc_html($item['reason']); ?></em>
                                    <span style="background: #f59e0b; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px;">
                                        <?php echo esc_html(round($item['confidence'] * 100)); ?>% confidence
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($excludeCss['critical_files'])) : ?>
                            <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                                🎯 <?php esc_html_e('Critical Theme Files', 'fp-performance-suite'); ?>
                            </h5>
                            <?php foreach ($excludeCss['critical_files'] as $item) : ?>
                                <div style="background: #e0f2fe; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                    <strong><?php echo esc_html($item['handle']); ?></strong><br>
                                    <?php if (!empty($item['src'])) : ?>
                                        <small style="color: #555;"><?php echo esc_html($item['src']); ?></small><br>
                                    <?php endif; ?>
                                    <em style="color: #0369a1;"><?php echo esc_html($item['reason']); ?></em>
                                    <span style="background: #0ea5e9; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px;">
                                        <?php echo esc_html(round($item['confidence'] * 100)); ?>% confidence
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($excludeCss['admin_styles'])) : ?>
                            <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                                ⚙️ <?php esc_html_e('Admin Styles', 'fp-performance-suite'); ?>
                            </h5>
                            <?php foreach (array_slice($excludeCss['admin_styles'], 0, 5) as $item) : ?>
                                <div style="background: #f3e8ff; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                    <strong><?php echo esc_html($item['handle']); ?></strong> - 
                                    <em style="color: #7c3aed;"><?php echo esc_html($item['reason']); ?></em>
                                    <span style="background: #8b5cf6; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px;">
                                        <?php echo esc_html(round($item['confidence'] * 100)); ?>% confidence
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div style="background: #d1fae5; padding: 10px; margin-top: 15px; border-radius: 4px;">
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #065f46;">
                                💡 <strong><?php esc_html_e('Suggerimento:', 'fp-performance-suite'); ?></strong>
                                <?php esc_html_e('Aggiungi questi CSS alla lista di esclusione qui sotto per evitare problemi di layout.', 'fp-performance-suite'); ?>
                            </p>
                            <button type="submit" name="apply_css_exclusions" class="button button-primary">
                                ✅ <?php esc_html_e('Applica Suggerimenti Automaticamente', 'fp-performance-suite'); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <p>
                    <label for="exclude_css"><?php esc_html_e('Exclude CSS from optimization', 'fp-performance-suite'); ?></label>
                    <textarea name="exclude_css" id="exclude_css" rows="4" class="large-text code" placeholder="<?php esc_attr_e('One handle or URL per line. Examples:\nstyle-handle\n/wp-content/themes/mytheme/custom.css', 'fp-performance-suite'); ?>"><?php echo esc_textarea($settings['exclude_css'] ?? ''); ?></textarea>
                    <span class="description"><?php esc_html_e('CSS files to exclude from minification and combine. Use script handle or partial URL.', 'fp-performance-suite'); ?></span>
                </p>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save CSS Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <!-- SECTION 2: Unused CSS -->
        <section class="fp-ps-card" style="margin-top: 30px;">
            <h2><?php esc_html_e('🧹 Unused CSS Optimization', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Rimuovi o differisci il CSS non utilizzato identificato da Lighthouse per migliorare LCP e FCP.', 'fp-performance-suite'); ?>
            </p>
            
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="unusedcss" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <?php
                $unusedCssOptimizer = new UnusedCSSOptimizer();
                $unusedCssSettings = $unusedCssOptimizer->getSettings();
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable Unused CSS Optimization', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="unusedcss_enabled" value="1" <?php checked($unusedCssSettings['enabled']); ?>>
                                <?php esc_html_e('Activate unused CSS removal', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Removes or defers unused CSS to improve performance.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Remove Unused CSS', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="unusedcss_remove_unused_css" value="1" <?php checked($unusedCssSettings['remove_unused_css']); ?>>
                                <?php esc_html_e('Remove unused CSS files completely', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Removes CSS files identified as unused by Lighthouse.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Defer Non-Critical CSS', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="unusedcss_defer_non_critical" value="1" <?php checked($unusedCssSettings['defer_non_critical']); ?>>
                                <?php esc_html_e('Defer non-critical CSS loading', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Loads non-critical CSS after page load to improve LCP.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Unused CSS Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <!-- SECTION 3: Critical CSS -->
        <section class="fp-ps-card" style="margin-top: 30px;">
            <h2><?php esc_html_e('🚀 Critical CSS', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Inline critical CSS to eliminate render-blocking and improve FCP by 20-30%.', 'fp-performance-suite'); ?>
            </p>
            
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="criticalcss" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <?php
                $criticalCssService = new CriticalCss();
                $criticalCssContent = $criticalCssService->get();
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Critical CSS Code', 'fp-performance-suite'); ?></th>
                        <td>
                            <textarea name="critical_css" rows="15" class="large-text code" placeholder="<?php esc_attr_e('/* Paste your critical CSS here */\nbody { margin: 0; }\n.header { ... }', 'fp-performance-suite'); ?>"><?php echo esc_textarea($criticalCssContent); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('Paste above-the-fold critical CSS. This will be inlined in <head> for instant rendering.', 'fp-performance-suite'); ?>
                            </p>
                            <p class="description">
                                <strong><?php esc_html_e('💡 How to generate:', 'fp-performance-suite'); ?></strong><br>
                                1. Use tools like <a href="https://www.sitelocity.com/critical-path-css-generator" target="_blank">Critical Path CSS Generator</a><br>
                                2. Or use <a href="https://github.com/addyosmani/critical" target="_blank">Critical (npm package)</a><br>
                                3. Extract only above-the-fold styles (header, hero, visible content)
                            </p>
                        </td>
                    </tr>
                </table>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Critical CSS', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        </div>
        <!-- Close TAB: CSS -->
        <?php
        return ob_get_clean();
    }
}
