<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs\Components\ScriptDetector;

use function get_transient;
use function parse_url;
use function PHP_URL_HOST;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_js;
use function wp_nonce_field;
use function count;
use function stripos;

/**
 * Renderizza gli script rilevati
 * 
 * @package FP\PerfSuite\Admin\Pages\Assets\Tabs\Components\ScriptDetector
 * @author Francesco Passeri
 */
class ScriptRenderer
{
    private array $thirdPartySettings;

    public function __construct(array $thirdPartySettings)
    {
        $this->thirdPartySettings = $thirdPartySettings;
    }

    /**
     * Renderizza gli script rilevati
     */
    public function render(array $detectedScripts): void
    {
        // Raggruppa per categoria
        $byCategory = [];
        foreach ($detectedScripts as $script) {
            $cat = $script['category'] ?? 'other';
            if (!isset($byCategory[$cat])) {
                $byCategory[$cat] = [];
            }
            $byCategory[$cat][] = $script;
        }
        
        $categoryLabels = [
            'analytics' => ['icon' => '📊', 'label' => 'Analytics', 'color' => '#3b82f6'],
            'marketing' => ['icon' => '🎯', 'label' => 'Marketing', 'color' => '#8b5cf6'],
            'chat' => ['icon' => '💬', 'label' => 'Chat', 'color' => '#10b981'],
            'social' => ['icon' => '👥', 'label' => 'Social', 'color' => '#ec4899'],
            'payment' => ['icon' => '💳', 'label' => 'Pagamenti', 'color' => '#f59e0b'],
            'security' => ['icon' => '🔒', 'label' => 'Sicurezza', 'color' => '#ef4444'],
            'media' => ['icon' => '🎬', 'label' => 'Media', 'color' => '#06b6d4'],
            'fonts' => ['icon' => '🔤', 'label' => 'Font', 'color' => '#6366f1'],
            'cdn' => ['icon' => '🌐', 'label' => 'CDN', 'color' => '#14b8a6'],
            'other' => ['icon' => '📦', 'label' => 'Altro', 'color' => '#64748b'],
        ];
        ?>
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #3b82f6;">
            
            <!-- Statistiche -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0;">
                <h3 style="margin: 0; color: #1e293b; font-size: 18px;">
                    📊 <?php esc_html_e('Script Rilevati', 'fp-performance-suite'); ?>
                </h3>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #3b82f6;">
                            <?php echo count($detectedScripts); ?>
                        </div>
                        <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">
                            Totale Script
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #8b5cf6;">
                            <?php echo count($byCategory); ?>
                        </div>
                        <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">
                            Categorie
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filtri Categoria -->
            <div style="margin-bottom: 20px;">
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php foreach ($byCategory as $cat => $scripts): 
                        $catInfo = $categoryLabels[$cat] ?? $categoryLabels['other'];
                    ?>
                    <span style="background: <?php echo $catInfo['color']; ?>; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                        <span><?php echo $catInfo['icon']; ?></span>
                        <?php echo esc_html($catInfo['label']); ?>
                        <span style="background: rgba(255,255,255,0.3); padding: 2px 6px; border-radius: 10px; font-size: 11px;">
                            <?php echo count($scripts); ?>
                        </span>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <h3 style="margin-top: 0; color: #1e293b;">
                📋 <?php esc_html_e('Lista Dettagliata', 'fp-performance-suite'); ?>
            </h3>
            
            <div style="display: grid; gap: 15px; margin-top: 20px;">
                <?php 
                $currentExclusions = $this->thirdPartySettings['exclusions'] ?? '';
                
                foreach ($detectedScripts as $script): 
                    if (empty($script['src'])) {
                        continue;
                    }
                    
                    $cat = $script['category'] ?? 'other';
                    $catInfo = $categoryLabels[$cat] ?? $categoryLabels['other'];
                    $scriptHost = parse_url($script['src'], PHP_URL_HOST);
                    
                    if (empty($scriptHost) || !is_string($scriptHost)) {
                        continue;
                    }
                    
                    $isExcluded = !empty($currentExclusions) && stripos($currentExclusions, $scriptHost) !== false;
                ?>
                    <div style="background: <?php echo $isExcluded ? '#f0fdf4' : '#f8fafc'; ?>; border: 2px solid <?php echo $isExcluded ? '#16a34a' : '#e2e8f0'; ?>; border-radius: 8px; padding: 15px; transition: all 0.2s;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <!-- Header con nome e badge -->
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
                                    <strong style="font-size: 16px; color: #1e293b;">
                                        <?php echo esc_html($script['name'] ?? 'Unknown Script'); ?>
                                    </strong>
                                    
                                    <!-- Badge Categoria -->
                                    <span style="background: <?php echo esc_attr($catInfo['color'] ?? '#64748b'); ?>; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                        <?php echo ($catInfo['icon'] ?? '📦') . ' ' . esc_html($catInfo['label'] ?? 'Other'); ?>
                                    </span>
                                    
                                    <!-- Stato Gestione -->
                                    <?php if ($isExcluded): ?>
                                        <span style="background: #16a34a; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                            ✓ ESCLUSO DAL DELAY
                                        </span>
                                    <?php elseif (isset($script['managed']) && $script['managed']): ?>
                                        <span style="background: #3b82f6; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                            ⏱️ RITARDATO
                                        </span>
                                    <?php else: ?>
                                        <span style="background: #eab308; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                            ⚠ NON GESTITO
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- URL completo -->
                                <div style="background: white; padding: 8px 12px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #e2e8f0;">
                                    <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">
                                        URL Script:
                                    </div>
                                    <div style="font-size: 12px; color: #475569; font-family: monospace; word-break: break-all;">
                                        <?php echo esc_html($script['src']); ?>
                                    </div>
                                </div>
                                
                                <!-- Host Pattern -->
                                <div style="background: #fffbeb; padding: 8px 12px; border-radius: 6px; border: 1px solid #fde68a;">
                                    <div style="font-size: 11px; color: #92400e; text-transform: uppercase; margin-bottom: 4px;">
                                        Pattern Suggerito:
                                    </div>
                                    <div style="font-size: 13px; color: #78350f; font-family: monospace; font-weight: 600;">
                                        <?php echo esc_html($scriptHost); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bottoni Azione -->
                            <div style="display: flex; flex-direction: column; gap: 8px; margin-left: 20px;">
                                <?php if ($isExcluded): ?>
                                    <form method="post" style="margin: 0;">
                                        <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                                        <input type="hidden" name="detector_action" value="remove_exclusion" />
                                        <input type="hidden" name="script_pattern" value="<?php echo esc_attr($scriptHost); ?>" />
                                        <button type="submit" class="button button-small" style="background: #dc2626; color: white; border: none; white-space: nowrap; padding: 8px 16px; font-size: 13px;" title="Rimuovi dalle esclusioni">
                                            ❌ Rimuovi Esclusione
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" style="margin: 0;">
                                        <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                                        <input type="hidden" name="detector_action" value="add_exclusion" />
                                        <input type="hidden" name="script_pattern" value="<?php echo esc_attr($scriptHost); ?>" />
                                        <button type="submit" class="button button-small" style="background: #16a34a; color: white; border: none; white-space: nowrap; padding: 8px 16px; font-size: 13px;" title="Aggiungi alle esclusioni - NON verrà ritardato">
                                            ➕ Escludi dal Delay
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <button 
                                    onclick="navigator.clipboard.writeText('<?php echo esc_js($scriptHost); ?>'); this.innerHTML='✓ Copiato!'; setTimeout(() => this.innerHTML='📋 Copia Pattern', 1500);"
                                    class="button button-small" 
                                    style="background: #64748b; color: white; border: none; white-space: nowrap; padding: 8px 16px; font-size: 13px;" 
                                    title="Copia il pattern negli appunti"
                                >
                                    📋 Copia Pattern
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top: 20px; padding: 15px; background: #dbeafe; border-left: 4px solid #3b82f6; border-radius: 6px;">
                <p style="margin: 0; color: #1e3a8a; font-size: 14px;">
                    <strong>💡 Suggerimento:</strong>
                    <?php esc_html_e('Clicca "➕ Escludi" per aggiungere uno script singolo, oppure usa "➕➕ Aggiungi Tutti" per escludere tutti gli script trovati in una volta.', 'fp-performance-suite'); ?>
                </p>
            </div>
            
            <div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                    <input type="hidden" name="detector_action" value="add_all_exclusions" />
                    <button type="submit" class="button button-primary" style="background: #16a34a; border-color: #16a34a;">
                        ➕➕ <?php esc_html_e('Aggiungi Tutti alle Esclusioni', 'fp-performance-suite'); ?>
                    </button>
                </form>
                
                <form method="post" style="margin: 0;">
                    <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                    <input type="hidden" name="detector_action" value="clear_detected" />
                    <button type="submit" class="button" onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler cancellare tutti i risultati della scansione?', 'fp-performance-suite'); ?>');">
                        🗑️ <?php esc_html_e('Cancella Risultati', 'fp-performance-suite'); ?>
                    </button>
                </form>
            </div>
        </div>
        <?php
    }
}















