<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs;

use FP\PerfSuite\Services\Assets\UnusedJavaScriptOptimizer;
use FP\PerfSuite\Services\Assets\CodeSplittingManager;
use FP\PerfSuite\Services\Assets\JavaScriptTreeShaker;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;

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
        
        <?php
        // Mostra legenda rischi (senza warning, le opzioni JS sono moderate)
        echo RiskLegend::renderLegend();
        ?>
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('JavaScript Optimization', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="javascript" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Defer JavaScript', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('defer_js'); ?>
                        <small><?php esc_html_e('Posticipa l\'esecuzione degli script dopo il caricamento', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="defer_js" value="1" <?php checked($settings['defer_js']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('defer_js')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Async JavaScript', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('async_js'); ?>
                        <small><?php esc_html_e('Carica script in modo asincrono senza bloccare rendering', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="async_js" value="1" <?php checked($settings['async_js']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('async_js')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine JS files', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('combine_js'); ?>
                        <small><?php esc_html_e('Combina tutti i file JavaScript in un unico file', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="combine_js" value="1" <?php checked($settings['combine_js']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('combine_js')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove emojis script', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('remove_emojis'); ?>
                        <small><?php esc_html_e('Rimuove lo script WordPress per il supporto emoji legacy', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="remove_emojis" value="1" <?php checked($settings['remove_emojis']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('remove_emojis')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify inline JavaScript', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('minify_inline_js'); ?>
                        <small><?php esc_html_e('Minify JavaScript code embedded in HTML <script> tags', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="minify_inline_js" value="1" <?php checked($settings['minify_inline_js'] ?? false); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('minify_inline_js')); ?>" />
                </label>
                
                <?php if (get_option('fp_ps_intelligence_enabled', false)) : ?>
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 6px; padding: 15px; margin: 20px 0;">
                    <h4 style="margin-top: 0; color: white;">🧠 <?php esc_html_e('Intelligent JavaScript Detection', 'fp-performance-suite'); ?></h4>
                    <p style="font-size: 13px; margin-bottom: 15px; color: rgba(255,255,255,0.9);">
                        <?php esc_html_e('Use our AI-powered system for automatic JavaScript detection, performance-based exclusions, and smart optimization.', 'fp-performance-suite'); ?>
                    </p>
                    <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-intelligence'); ?>" class="button button-primary" style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); color: white;">
                        🎯 <?php esc_html_e('Open Intelligence Dashboard', 'fp-performance-suite'); ?>
                    </a>
                </div>
                <?php endif; ?>
                
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
        
        <?php
        // Get advanced optimization settings
        $unusedOptimizer = new UnusedJavaScriptOptimizer();
        $codeSplittingManager = new CodeSplittingManager();
        $treeShaker = new JavaScriptTreeShaker();
        
        $unusedSettings = $unusedOptimizer->settings();
        $codeSplittingSettings = $codeSplittingManager->settings();
        $treeShakingSettings = $treeShaker->settings();
        ?>
        
        <!-- Advanced JavaScript Optimization -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🚀 Ottimizzazione JavaScript Avanzata', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Tecniche avanzate per ridurre il JavaScript non utilizzato e ottimizzare il caricamento degli script per migliorare le prestazioni e i Core Web Vitals.', 'fp-performance-suite'); ?>
            </p>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="advanced_js_optimization" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <!-- Unused JavaScript Optimization -->
                <div class="fp-ps-mb-lg" style="border: 1px solid #e1e5e9; border-radius: 8px; padding: 20px; background: #fafbfc;">
                    <h3 style="margin-top: 0; color: #24292f;">🔍 <?php esc_html_e('Rimozione JavaScript Non Utilizzato', 'fp-performance-suite'); ?></h3>
                    <p class="description" style="font-size: 14px; color: #656d76;">
                        <?php esc_html_e('Identifica e rimuove automaticamente il codice JavaScript che non viene eseguito sulla pagina, riducendo il peso dei bundle e migliorando il First Contentful Paint (FCP).', 'fp-performance-suite'); ?>
                    </p>
                    
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Abilita Ottimizzazione', 'fp-performance-suite'); ?></strong>
                            <?php echo RiskMatrix::renderIndicator('unused_js_enabled'); ?>
                            <small><?php esc_html_e('Attiva la rimozione automatica del codice JavaScript non utilizzato', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" 
                               name="unused_optimization[enabled]" 
                               value="1"
                               <?php checked($unusedSettings['enabled']); ?>
                               data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('unused_js_enabled')); ?>" />
                    </label>
                </div>
                
                <!-- Code Splitting -->
                <div class="fp-ps-mb-lg" style="border: 1px solid #e1e5e9; border-radius: 8px; padding: 20px; background: #fafbfc;">
                    <h3 style="margin-top: 0; color: #24292f;">📦 <?php esc_html_e('Code Splitting', 'fp-performance-suite'); ?></h3>
                    <p class="description" style="font-size: 14px; color: #656d76;">
                        <?php esc_html_e('Divide il codice JavaScript in chunks più piccoli caricati on-demand, riducendo il payload iniziale e migliorando i tempi di caricamento percepiti.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Abilita Code Splitting', 'fp-performance-suite'); ?></strong>
                            <?php echo RiskMatrix::renderIndicator('code_splitting_enabled'); ?>
                            <small><?php esc_html_e('Divide il codice JavaScript in chunks più piccoli per un caricamento più efficiente', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" 
                               name="code_splitting[enabled]" 
                               value="1"
                               <?php checked($codeSplittingSettings['enabled']); ?>
                               data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('code_splitting_enabled')); ?>" />
                    </label>
                </div>
                
                <!-- Tree Shaking -->
                <div class="fp-ps-mb-lg" style="border: 1px solid #e1e5e9; border-radius: 8px; padding: 20px; background: #fafbfc;">
                    <h3 style="margin-top: 0; color: #24292f;">🌳 <?php esc_html_e('Tree Shaking', 'fp-performance-suite'); ?></h3>
                    <p class="description" style="font-size: 14px; color: #656d76;">
                        <?php esc_html_e('Elimina il "dead code" e le funzioni non utilizzate dai bundle JavaScript, mantenendo solo il codice effettivamente eseguito.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Abilita Tree Shaking', 'fp-performance-suite'); ?></strong>
                            <?php echo RiskMatrix::renderIndicator('tree_shaking_enabled'); ?>
                            <small><?php esc_html_e('Rimuove il codice morto e le funzioni non utilizzate dai bundle JavaScript', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" 
                               name="tree_shaking[enabled]" 
                               value="1"
                               <?php checked($treeShakingSettings['enabled']); ?>
                               data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('tree_shaking_enabled')); ?>" />
                    </label>
                </div>
                
                <!-- Stats Overview -->
                <div style="border: 1px solid #e1e5e9; border-radius: 8px; padding: 20px; background: #f6f8fa;">
                    <h3 style="margin-top: 0; color: #24292f;">📊 <?php esc_html_e('Impatto Ottimizzazioni', 'fp-performance-suite'); ?></h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 15px 0;">
                        <div style="text-align: center; padding: 15px; background: white; border-radius: 6px; border: 1px solid #d0d7de;">
                            <div style="font-size: 24px; font-weight: bold; color: <?php echo $unusedSettings['enabled'] ? '#1a7f37' : '#656d76'; ?>;">
                                <?php echo $unusedSettings['enabled'] ? '✓' : '—'; ?>
                            </div>
                            <div style="font-size: 12px; color: #656d76; margin-top: 5px;"><?php esc_html_e('Unused JS', 'fp-performance-suite'); ?></div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: white; border-radius: 6px; border: 1px solid #d0d7de;">
                            <div style="font-size: 24px; font-weight: bold; color: <?php echo $codeSplittingSettings['enabled'] ? '#1a7f37' : '#656d76'; ?>;">
                                <?php echo $codeSplittingSettings['enabled'] ? '✓' : '—'; ?>
                            </div>
                            <div style="font-size: 12px; color: #656d76; margin-top: 5px;"><?php esc_html_e('Code Splitting', 'fp-performance-suite'); ?></div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: white; border-radius: 6px; border: 1px solid #d0d7de;">
                            <div style="font-size: 24px; font-weight: bold; color: <?php echo $treeShakingSettings['enabled'] ? '#1a7f37' : '#656d76'; ?>;">
                                <?php echo $treeShakingSettings['enabled'] ? '✓' : '—'; ?>
                            </div>
                            <div style="font-size: 12px; color: #656d76; margin-top: 5px;"><?php esc_html_e('Tree Shaking', 'fp-performance-suite'); ?></div>
                        </div>
                    </div>
                    
                    <?php 
                    $activeCount = (int)$unusedSettings['enabled'] + (int)$codeSplittingSettings['enabled'] + (int)$treeShakingSettings['enabled'];
                    ?>
                    
                    <?php if ($activeCount === 0) : ?>
                        <div style="background: #fff3cd; border: 1px solid #ffecb5; border-radius: 6px; padding: 15px; margin-top: 15px;">
                            <p style="margin: 0; color: #856404;">
                                <strong>⚠️ <?php esc_html_e('Nessuna ottimizzazione attiva', 'fp-performance-suite'); ?></strong><br>
                                <?php esc_html_e('Abilita almeno un\'ottimizzazione per migliorare le performance JavaScript del tuo sito.', 'fp-performance-suite'); ?>
                            </p>
                        </div>
                    <?php elseif ($activeCount === 3) : ?>
                        <div style="background: #d1e7dd; border: 1px solid #a3cfbb; border-radius: 6px; padding: 15px; margin-top: 15px;">
                            <p style="margin: 0; color: #0f5132;">
                                <strong>✅ <?php esc_html_e('Tutte le ottimizzazioni attive!', 'fp-performance-suite'); ?></strong><br>
                                <?php esc_html_e('Il tuo sito sta utilizzando tutte le tecniche avanzate di ottimizzazione JavaScript disponibili.', 'fp-performance-suite'); ?>
                            </p>
                        </div>
                    <?php else : ?>
                        <div style="background: #cff4fc; border: 1px solid #9eeaf9; border-radius: 6px; padding: 15px; margin-top: 15px;">
                            <p style="margin: 0; color: #055160;">
                                <strong>ℹ️ <?php printf(esc_html__('%d ottimizzazioni attive su 3', 'fp-performance-suite'), $activeCount); ?></strong><br>
                                <?php esc_html_e('Considera di attivare le altre ottimizzazioni per massimizzare le performance.', 'fp-performance-suite'); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Advanced JavaScript Settings', 'fp-performance-suite'); ?>
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
