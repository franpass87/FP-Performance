<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function wp_nonce_field;

class JavaScriptTab
{
    public function render(array $data): string
    {
        $current_tab = $data['current_tab'];
        $settings = $data['settings'];
        $excludeJs = $data['excludeJs'] ?? null;

        ob_start();
        ?>
        <!-- TAB: JavaScript -->
        <div class="fp-ps-tab-content <?php echo $current_tab === 'javascript' ? 'active' : ''; ?>" data-tab="javascript">
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('JavaScript Optimization', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="javascript" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Defer JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Posticipa l\'esecuzione degli script JavaScript dopo il caricamento della pagina.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Può causare errori con script che dipendono da jQuery o altri script caricati prima.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Consigliato: Migliora significativamente il First Contentful Paint. Testa animazioni e funzionalità interattive.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="defer_js" value="1" <?php checked($settings['defer_js']); ?> data-risk="amber" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Async JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Carica gli script in modo asincrono senza bloccare il rendering.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Gli script potrebbero eseguirsi in ordine diverso, causando errori di dipendenza.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚠️ Usa con cautela: Non combinare con Defer. Testa approfonditamente.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="async_js" value="1" <?php checked($settings['async_js']); ?> data-risk="amber" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine JS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red">
                            <div class="fp-ps-risk-tooltip red">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">🔴</span>
                                    <?php esc_html_e('Rischio Alto', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Combina tutti i file JavaScript in un unico file.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Molto alto rischio di errori JavaScript, problemi di dipendenze e conflitti tra script.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ Sconsigliato: Può rompere funzionalità critiche. Meglio usare il defer invece della combinazione.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="combine_js" value="1" <?php checked($settings['combine_js']); ?> data-risk="red" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove emojis script', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove lo script WordPress per il supporto emoji legacy. I browser moderni li supportano nativamente.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Riduce le richieste HTTP senza rischi. Attiva sempre.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="remove_emojis" value="1" <?php checked($settings['remove_emojis']); ?> data-risk="green" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify inline JavaScript', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="minify_inline_js" value="1" <?php checked($settings['minify_inline_js'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Minify JavaScript code embedded in HTML <script> tags. Use with caution.', 'fp-performance-suite'); ?>
                </p>
                
                <div style="background: #f0f6fc; border: 2px solid #0969da; border-radius: 6px; padding: 15px; margin: 20px 0;">
                    <h4 style="margin-top: 0; color: #0969da;">⚡ <?php esc_html_e('Smart JavaScript Detection', 'fp-performance-suite'); ?></h4>
                    <p style="font-size: 13px; margin-bottom: 10px;">
                        <?php esc_html_e('Let the AI detect JavaScript files that should not be optimized (deferred/combined) automatically.', 'fp-performance-suite'); ?>
                    </p>
                    <button type="submit" name="auto_detect_exclude_js" class="button button-secondary">
                        🔍 <?php esc_html_e('Auto-Detect JS to Exclude', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <?php if ($excludeJs) : ?>
                    <div style="background: white; border: 2px solid #059669; border-radius: 6px; padding: 15px; margin: 20px 0;">
                        <h4 style="margin-top: 0; color: #059669;">✨ <?php esc_html_e('JavaScript Files to Exclude Detected', 'fp-performance-suite'); ?></h4>
                        
                        <?php if (!empty($excludeJs['plugin_specific'])) : ?>
                            <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                                🔌 <?php esc_html_e('Plugin-Specific JavaScript', 'fp-performance-suite'); ?>
                            </h5>
                            <?php foreach ($excludeJs['plugin_specific'] as $item) : ?>
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
                        
                        <?php if (!empty($excludeJs['core_dependencies'])) : ?>
                            <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                                🔗 <?php esc_html_e('Core Dependencies', 'fp-performance-suite'); ?>
                            </h5>
                            <?php foreach (array_slice($excludeJs['core_dependencies'], 0, 5) as $item) : ?>
                                <div style="background: #e0e7ff; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                    <strong><?php echo esc_html($item['handle']); ?></strong> - 
                                    <em style="color: #4338ca;"><?php echo esc_html($item['reason']); ?></em>
                                    <span style="background: #6366f1; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px;">
                                        <?php echo esc_html(round($item['confidence'] * 100)); ?>% confidence
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <?php if (!empty($excludeJs['inline_dependent'])) : ?>
                            <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                                📝 <?php esc_html_e('Scripts with Inline Code', 'fp-performance-suite'); ?>
                            </h5>
                            <?php foreach (array_slice($excludeJs['inline_dependent'], 0, 5) as $item) : ?>
                                <div style="background: #fee2e2; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                    <strong><?php echo esc_html($item['handle']); ?></strong> - 
                                    <em style="color: #dc2626;"><?php echo esc_html($item['reason']); ?></em>
                                    <span style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 10px; font-size: 10px; margin-left: 5px;">
                                        <?php echo esc_html(round($item['confidence'] * 100)); ?>% confidence
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div style="background: #d1fae5; padding: 10px; margin-top: 15px; border-radius: 4px;">
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #065f46;">
                                💡 <strong><?php esc_html_e('Suggerimento:', 'fp-performance-suite'); ?></strong>
                                <?php esc_html_e('Aggiungi questi script alla lista di esclusione qui sotto per evitare problemi di funzionalità.', 'fp-performance-suite'); ?>
                            </p>
                            <button type="submit" name="apply_js_exclusions" class="button button-primary">
                                ✅ <?php esc_html_e('Applica Suggerimenti Automaticamente', 'fp-performance-suite'); ?>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
                
                <p>
                    <label for="exclude_js"><?php esc_html_e('Exclude JavaScript from optimization', 'fp-performance-suite'); ?></label>
                    <textarea name="exclude_js" id="exclude_js" rows="4" class="large-text code" placeholder="<?php esc_attr_e('One handle or URL per line. Examples:\njquery\njquery-core\n/wp-includes/js/jquery/jquery.js', 'fp-performance-suite'); ?>"><?php echo esc_textarea($settings['exclude_js'] ?? ''); ?></textarea>
                    <span class="description"><?php esc_html_e('JavaScript files to exclude from defer/async/combine. Use script handle or partial URL.', 'fp-performance-suite'); ?></span>
                </p>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save JavaScript Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        </div>
        <!-- Close TAB: JavaScript -->
        <?php
        return ob_get_clean();
    }
}
