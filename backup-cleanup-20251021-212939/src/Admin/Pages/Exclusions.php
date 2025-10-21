<?php

namespace FP\PerfSuite\Admin\Pages;

use function __;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function get_option;
use function update_option;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function date_i18n;

use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

class Exclusions extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-exclusions';
    }

    public function title(): string
    {
        return __('Gestisci Esclusioni', 'fp-performance-suite');
    }

    public function capability(): string
    {
        return 'manage_options';
    }

    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Intelligenza AI', 'fp-performance-suite'), __('Esclusioni', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Smart Exclusions Detector
        $smartDetector = new SmartExclusionDetector();
        $autoDetected = null;
        $message = '';
        $messageType = 'success';
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_exclusions_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_exclusions_nonce']), 'fp-ps-exclusions')) {
            
            // Handle auto-detect action
            if (isset($_POST['auto_detect_exclusions'])) {
                $autoDetected = $smartDetector->detectSensitiveUrls();
                $message = __('✓ Auto-detection completata! Revisionare i suggerimenti qui sotto e applicare quelli desiderati.', 'fp-performance-suite');
            }
            
            // Handle auto-apply action
            if (isset($_POST['auto_apply_exclusions'])) {
                $result = $smartDetector->autoApplyExclusions(false);
                
                if ($result['applied'] > 0) {
                    $message = sprintf(
                        __('✓ %d esclusioni applicate con successo!', 'fp-performance-suite'),
                        $result['applied']
                    );
                    if ($result['already_exists'] > 0) {
                        $message .= ' ' . sprintf(
                            __('%d erano già presenti.', 'fp-performance-suite'),
                            $result['already_exists']
                        );
                    }
                    if ($result['skipped'] > 0) {
                        $message .= ' ' . sprintf(
                            __('%d ignorate (confidence bassa).', 'fp-performance-suite'),
                            $result['skipped']
                        );
                    }
                } elseif ($result['already_exists'] > 0) {
                    $message = sprintf(
                        __('ℹ️ Nessuna nuova esclusione da aggiungere. %d esclusioni erano già presenti.', 'fp-performance-suite'),
                        $result['already_exists']
                    );
                    $messageType = 'info';
                } elseif ($result['skipped'] > 0) {
                    $message = sprintf(
                        __('⚠ Nessuna esclusione applicata. %d esclusioni ignorate per confidence troppo bassa (< 80%%).', 'fp-performance-suite'),
                        $result['skipped']
                    );
                    $messageType = 'warning';
                } else {
                    $message = __('ℹ️ Nessuna esclusione rilevata da applicare. Il tuo sito potrebbe già essere completamente protetto o non utilizzare plugin che richiedono esclusioni.', 'fp-performance-suite');
                    $messageType = 'info';
                }
            }
            
            // Handle remove exclusion
            if (isset($_POST['remove_exclusion'])) {
                $exclusionId = sanitize_text_field($_POST['exclusion_id'] ?? '');
                if ($exclusionId) {
                    $smartDetector->removeExclusion($exclusionId);
                    $message = __('✓ Esclusione rimossa con successo.', 'fp-performance-suite');
                }
            }
            
            // Handle add manual exclusion
            if (isset($_POST['add_manual_exclusion'])) {
                $url = sanitize_text_field($_POST['manual_url'] ?? '');
                $reason = sanitize_text_field($_POST['manual_reason'] ?? '');
                
                if ($url) {
                    $added = $smartDetector->addManualExclusion($url, $reason);
                    if ($added) {
                        $message = __('✓ Esclusione manuale aggiunta con successo.', 'fp-performance-suite');
                    } else {
                        $message = __('ℹ️ Questa esclusione è già presente nel sistema.', 'fp-performance-suite');
                        $messageType = 'info';
                    }
                } else {
                    $message = __('⚠ Errore: URL obbligatorio.', 'fp-performance-suite');
                    $messageType = 'error';
                }
            }
            
            // Handle cleanup duplicates
            if (isset($_POST['cleanup_duplicates'])) {
                $stats = $smartDetector->removeDuplicateExclusions();
                if ($stats['duplicates_removed'] > 0) {
                    $message = sprintf(
                        __('✓ Ripulitura completata: %d duplicati rimossi. Totale esclusioni: %d → %d', 'fp-performance-suite'),
                        $stats['duplicates_removed'],
                        $stats['total_before'],
                        $stats['total_after']
                    );
                } else {
                    $message = __('✓ Nessun duplicato trovato. Database già pulito.', 'fp-performance-suite');
                }
            }
        }
        
        // Get current exclusions
        $appliedExclusions = $smartDetector->getAppliedExclusions();
        
        // Count stats
        $totalExclusions = count($appliedExclusions);
        $autoExclusions = count(array_filter($appliedExclusions, fn($e) => $e['type'] === 'automatic'));
        $manualExclusions = count(array_filter($appliedExclusions, fn($e) => $e['type'] === 'manual'));
        
        ob_start();
        ?>
        
        <?php if ($message) : ?>
            <div class="notice notice-<?php echo esc_attr($messageType); ?>">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Stats Overview -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="fp-ps-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px;">
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">📊 Totale Esclusioni</div>
                <div style="font-size: 36px; font-weight: 700;"><?php echo esc_html($totalExclusions); ?></div>
            </div>
            
            <div class="fp-ps-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px;">
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">🤖 Automatiche</div>
                <div style="font-size: 36px; font-weight: 700;"><?php echo esc_html($autoExclusions); ?></div>
            </div>
            
            <div class="fp-ps-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 25px;">
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 5px;">✍️ Manuali</div>
                <div style="font-size: 36px; font-weight: 700;"><?php echo esc_html($manualExclusions); ?></div>
            </div>
        </div>

        <form method="post">
            <?php wp_nonce_field('fp-ps-exclusions', 'fp_ps_exclusions_nonce'); ?>

            <!-- Smart Auto-Exclusions Section -->
            <section class="fp-ps-card">
                <h2>🤖 <?php esc_html_e('Smart Auto-Exclusions', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Sistema intelligente che rileva automaticamente URL sensibili, script critici e pattern da escludere dalle ottimizzazioni.', 'fp-performance-suite'); ?></p>
                
                <div class="fp-ps-actions" style="margin: 20px 0;">
                    <button type="submit" name="auto_detect_exclusions" class="button button-primary button-large">
                        🔍 <?php esc_html_e('Rileva Automaticamente', 'fp-performance-suite'); ?>
                    </button>
                    <button type="submit" name="auto_apply_exclusions" class="button button-secondary button-large" onclick="return confirm('<?php esc_attr_e('Applicare automaticamente tutte le esclusioni con confidence >= 80%?', 'fp-performance-suite'); ?>');">
                        ✨ <?php esc_html_e('Applica Automaticamente (High Confidence)', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <?php if ($autoDetected) : ?>
                    <div style="background: #f0f6fc; border: 2px solid #0969da; border-radius: 6px; padding: 20px; margin: 20px 0;">
                        <h3 style="margin-top: 0; color: #0969da;">
                            📊 <?php esc_html_e('Esclusioni Rilevate Automaticamente', 'fp-performance-suite'); ?>
                        </h3>
                        
                        <?php foreach ($autoDetected as $category => $items) : ?>
                            <?php if (!empty($items)) : ?>
                                <h4 style="margin-top: 20px; font-size: 14px; text-transform: uppercase; color: #666;">
                                    <?php 
                                    $categoryLabels = [
                                        'auto_detected' => __('🎯 Standard Sensitive URLs', 'fp-performance-suite'),
                                        'plugin_based' => __('🔌 Plugin-Based URLs', 'fp-performance-suite'),
                                        'user_behavior' => __('📈 Behavior-Based URLs', 'fp-performance-suite'),
                                    ];
                                    echo esc_html($categoryLabels[$category] ?? $category);
                                    ?>
                                </h4>
                                
                                <div style="display: grid; gap: 10px; margin-top: 10px;">
                                    <?php foreach ($items as $item) : ?>
                                        <?php
                                        $confidence = $item['confidence'] * 100;
                                        $confidenceColor = $confidence >= 90 ? '#059669' : ($confidence >= 70 ? '#d97706' : '#dc2626');
                                        ?>
                                        <div style="background: white; border-left: 4px solid <?php echo $confidenceColor; ?>; padding: 12px; border-radius: 4px;">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <div style="flex: 1;">
                                                    <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-size: 13px;">
                                                        <?php echo esc_html($item['url']); ?>
                                                    </code>
                                                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                                        <?php echo esc_html($item['reason']); ?>
                                                        <?php if (isset($item['plugin'])) : ?>
                                                            <span style="background: #e0e7ff; color: #4f46e5; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 5px;">
                                                                <?php echo esc_html($item['plugin']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div style="text-align: right; margin-left: 20px;">
                                                    <div style="font-size: 18px; font-weight: 600; color: <?php echo $confidenceColor; ?>;">
                                                        <?php echo number_format($confidence, 0); ?>%
                                                    </div>
                                                    <div style="font-size: 10px; color: #666;">confidence</div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-top: 20px; border-radius: 4px;">
                            <p style="margin: 0; font-size: 13px; color: #92400e;">
                                <strong>💡 <?php esc_html_e('Suggerimento:', 'fp-performance-suite'); ?></strong>
                                <?php esc_html_e('Le esclusioni con confidence >= 80% sono generalmente sicure da applicare. Quelle con confidence più bassa richiedono revisione manuale.', 'fp-performance-suite'); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;">🧠 <?php esc_html_e('Come Funziona il Sistema Intelligente:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555; font-size: 13px;">
                        <li><?php esc_html_e('Scansiona automaticamente il sito per URL sensibili (checkout, login, account, etc.)', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Rileva plugin attivi (WooCommerce, EDD, etc.) e applica regole specifiche', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Analizza storico errori per identificare pagine problematiche', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Assegna un punteggio di confidence a ogni esclusione suggerita', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Applica automaticamente solo esclusioni ad alta confidence (>= 80%)', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
            </section>

            <!-- Applied Exclusions Table -->
            <section class="fp-ps-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <div>
                        <h2 style="margin: 0;">📋 <?php esc_html_e('Esclusioni Applicate', 'fp-performance-suite'); ?></h2>
                        <p style="margin: 5px 0 0 0;"><?php esc_html_e('Visualizza e gestisci tutte le esclusioni attualmente attive sul tuo sito.', 'fp-performance-suite'); ?></p>
                    </div>
                    <button 
                        type="submit" 
                        name="cleanup_duplicates" 
                        class="button button-secondary"
                        onclick="return confirm('<?php esc_attr_e('Rimuovere tutti i duplicati? Le esclusioni più recenti verranno mantenute.', 'fp-performance-suite'); ?>');"
                        style="white-space: nowrap;"
                    >
                        🧹 <?php esc_html_e('Ripulisci Duplicati', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <?php if (empty($appliedExclusions)) : ?>
                    <div style="background: #f0f6fc; border: 2px dashed #0969da; border-radius: 6px; padding: 40px; text-align: center; margin-top: 20px;">
                        <div style="font-size: 48px; margin-bottom: 10px;">🔍</div>
                        <p style="font-size: 16px; color: #666; margin: 0;">
                            <?php esc_html_e('Nessuna esclusione applicata al momento.', 'fp-performance-suite'); ?>
                        </p>
                        <p style="font-size: 14px; color: #999; margin: 10px 0 0 0;">
                            <?php esc_html_e('Usa il rilevamento automatico sopra o aggiungi esclusioni manuali qui sotto.', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                <?php else : ?>
                    <div style="overflow-x: auto; margin-top: 20px;">
                        <table class="wp-list-table widefat fixed striped" style="border: 1px solid #ddd;">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">Tipo</th>
                                    <th style="width: 30%;">URL/Pattern</th>
                                    <th>Ragione</th>
                                    <th style="width: 100px; text-align: center;">Confidence</th>
                                    <th style="width: 150px;">Data Applicazione</th>
                                    <th style="width: 100px; text-align: center;">Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appliedExclusions as $exclusion) : ?>
                                    <?php
                                    $confidence = $exclusion['confidence'] * 100;
                                    $confidenceColor = $confidence >= 90 ? '#059669' : ($confidence >= 70 ? '#d97706' : '#6b7280');
                                    $typeIcon = $exclusion['type'] === 'automatic' ? '🤖' : '✍️';
                                    $typeLabel = $exclusion['type'] === 'automatic' ? 'Auto' : 'Manual';
                                    ?>
                                    <tr>
                                        <td style="text-align: center; font-size: 20px;" title="<?php echo esc_attr($typeLabel); ?>">
                                            <?php echo $typeIcon; ?>
                                        </td>
                                        <td>
                                            <code style="background: #f3f4f6; padding: 4px 8px; border-radius: 3px; font-size: 13px;">
                                                <?php echo esc_html($exclusion['url']); ?>
                                            </code>
                                        </td>
                                        <td style="font-size: 13px; color: #666;">
                                            <?php echo esc_html($exclusion['reason']); ?>
                                            <?php if (!empty($exclusion['plugin'])) : ?>
                                                <br>
                                                <span style="background: #e0e7ff; color: #4f46e5; padding: 2px 6px; border-radius: 3px; font-size: 11px;">
                                                    <?php echo esc_html($exclusion['plugin']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <span style="display: inline-block; padding: 4px 10px; border-radius: 4px; font-weight: 600; font-size: 13px; background: <?php echo $confidenceColor; ?>; color: white;">
                                                <?php echo number_format($confidence, 0); ?>%
                                            </span>
                                        </td>
                                        <td style="font-size: 13px; color: #666;">
                                            <?php 
                                            echo esc_html(
                                                date_i18n(
                                                    get_option('date_format') . ' ' . get_option('time_format'),
                                                    $exclusion['applied_at']
                                                )
                                            );
                                            ?>
                                        </td>
                                        <td style="text-align: center;">
                                            <button 
                                                type="submit" 
                                                name="remove_exclusion" 
                                                value="1"
                                                onclick="document.querySelector('input[name=exclusion_id]').value='<?php echo esc_attr($exclusion['id']); ?>'; return confirm('<?php esc_attr_e('Rimuovere questa esclusione?', 'fp-performance-suite'); ?>');"
                                                class="button button-small"
                                                style="color: #d63638;"
                                            >
                                                🗑️ <?php esc_html_e('Rimuovi', 'fp-performance-suite'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <input type="hidden" name="exclusion_id" value="">
            </section>

            <!-- Add Manual Exclusion -->
            <section class="fp-ps-card">
                <h2>➕ <?php esc_html_e('Aggiungi Esclusione Manuale', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Se il sistema automatico non rileva alcune esclusioni necessarie, puoi aggiungerle manualmente qui.', 'fp-performance-suite'); ?></p>
                
                <div style="max-width: 800px; margin-top: 20px;">
                    <div style="margin-bottom: 15px;">
                        <label for="manual_url" style="font-weight: 600; display: block; margin-bottom: 5px;">
                            <?php esc_html_e('URL o Pattern', 'fp-performance-suite'); ?>
                            <span style="color: #d63638;">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="manual_url" 
                            id="manual_url" 
                            class="regular-text" 
                            placeholder="/my-custom-page/"
                            style="width: 100%;"
                        >
                        <p class="description">
                            <?php esc_html_e('Inserisci l\'URL o il pattern da escludere. Supporta wildcards (*). Esempi: /checkout/, /my-account/*, /?s=*', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label for="manual_reason" style="font-weight: 600; display: block; margin-bottom: 5px;">
                            <?php esc_html_e('Motivo dell\'esclusione', 'fp-performance-suite'); ?>
                        </label>
                        <input 
                            type="text" 
                            name="manual_reason" 
                            id="manual_reason" 
                            class="regular-text" 
                            placeholder="<?php esc_attr_e('Es: Pagina con contenuto dinamico personalizzato', 'fp-performance-suite'); ?>"
                            style="width: 100%;"
                        >
                        <p class="description">
                            <?php esc_html_e('Opzionale: Spiega perché questa esclusione è necessaria (utile per riferimento futuro).', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                    
                    <button type="submit" name="add_manual_exclusion" class="button button-primary button-large">
                        ➕ <?php esc_html_e('Aggiungi Esclusione', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </section>

        </form>
        
        <?php
        return (string) ob_get_clean();
    }
}
