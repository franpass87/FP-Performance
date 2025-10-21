<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Media\WebPConverter;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html__;
use function esc_html_e;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function number_format_i18n;
use function printf;

class Media extends AbstractPage
{
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
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Media', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $converter = $this->container->get(WebPConverter::class);
        $message = '';
        $bulkResult = null;
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_media_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_media_nonce']), 'fp_ps_media')) {
            if (isset($_POST['save_webp'])) {
                $converter->update([
                    'enabled' => !empty($_POST['webp_enabled']),
                    'quality' => (int) ($_POST['webp_quality'] ?? 82),
                    'keep_original' => !empty($_POST['keep_original']),
                    'lossy' => !empty($_POST['lossy']),
                    'auto_deliver' => !empty($_POST['auto_deliver']),
                ]);
                $message = __('WebP settings saved.', 'fp-performance-suite');
            }
            if (isset($_POST['bulk_convert'])) {
                $limit = (int) ($_POST['bulk_limit'] ?? 20);
                $offset = (int) ($_POST['bulk_offset'] ?? 0);
                $bulkResult = $converter->bulkConvert($limit, $offset);
                if (!empty($bulkResult['queued'])) {
                    $message = __('Bulk conversion queued in the background.', 'fp-performance-suite');
                } else {
                    $message = __('Bulk conversion completed.', 'fp-performance-suite');
                }
            }
        }
        $settings = $converter->settings();
        $status = $converter->status();
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('WebP Conversion', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp_ps_media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="save_webp" value="1" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable WebP on upload', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Converte automaticamente le immagini caricate in formato WebP.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce il peso delle immagini del 30-40% senza perdita di qualità visibile.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Supportato da tutti i browser moderni. Migliora LCP e riduce il consumo di banda.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                        <small><?php esc_html_e('Automatically convert uploaded images to WebP format', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="webp_enabled" value="1" <?php checked($settings['enabled']); ?> data-risk="green" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Auto-deliver WebP images', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Serve automaticamente WebP ai browser compatibili, fallback alle immagini originali.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce il peso totale delle pagine del 30-40%, migliorando i tempi di caricamento.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Altamente consigliato: Compatibilità automatica, nessun downside. Essenziale per performance.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                        <small><?php esc_html_e('Automatically serve WebP to compatible browsers (30-40% smaller)', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="auto_deliver" value="1" <?php checked($settings['auto_deliver']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="webp_quality"><?php esc_html_e('Quality (0-100)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="webp_quality" id="webp_quality" value="<?php echo esc_attr((string) $settings['quality']); ?>" min="10" max="100" />
                </p>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Keep original files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Mantiene sia i file originali che le versioni WebP sul server.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Raddoppia l\'utilizzo dello spazio disco per le immagini.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Consigliato se hai spazio: Utile per compatibilità e come backup. Disattiva solo se lo spazio disco è limitato.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="keep_original" value="1" <?php checked($settings['keep_original']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Use lossy compression', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Usa compressione lossy per file ancora più piccoli (invece di lossless).', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Possibile leggera perdita di qualità visiva, specialmente con immagini ad alta risoluzione.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Consigliato per blog: La perdita è minima e il guadagno in performance è significativo. Sconsigliato per siti fotografici.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="lossy" value="1" <?php checked($settings['lossy']); ?> data-risk="amber" />
                </label>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Save Media Settings', 'fp-performance-suite'); ?></button>
                </p>
            </form>
            <p class="description"><?php printf(esc_html__('Current WebP coverage: %s%%', 'fp-performance-suite'), number_format_i18n($status['coverage'], 2)); ?></p>
        </section>
        
        <!-- Bulk Convert Library - Modernized UI -->
        <section class="fp-ps-card fp-ps-bulk-convert-section">
            <div class="fp-ps-bulk-convert-header">
                <div class="fp-ps-bulk-convert-title-wrapper">
                    <h2 style="margin-bottom: 8px;">
                        <span class="fp-ps-bulk-icon">🔄</span>
                        <?php esc_html_e('Conversione Bulk della Libreria Media', 'fp-performance-suite'); ?>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Operazione Pesante', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Converte tutte le immagini esistenti nella tua libreria media in formato WebP.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Processa le immagini in batch per evitare timeout. Puoi interrompere e riprendere in qualsiasi momento.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </h2>
                    <p class="fp-ps-bulk-convert-description">
                        <?php esc_html_e('Converti automaticamente tutte le immagini esistenti della tua libreria media in formato WebP per ottenere immagini più leggere e prestazioni migliori.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
            
            <!-- Statistics Grid -->
            <div class="fp-ps-bulk-stats-grid">
                <div class="fp-ps-stat-card">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">📚</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Totale Immagini', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value"><?php echo number_format_i18n($status['total_images'] ?? 0); ?></div>
                    </div>
                </div>
                
                <div class="fp-ps-stat-card">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #1f9d55 0%, #0ea372 100%);">✅</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Già Convertite', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value"><?php echo number_format_i18n($status['converted_images'] ?? 0); ?></div>
                    </div>
                </div>
                
                <div class="fp-ps-stat-card">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">⏳</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Da Convertire', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value"><?php echo number_format_i18n(($status['total_images'] ?? 0) - ($status['converted_images'] ?? 0)); ?></div>
                    </div>
                </div>
                
                <div class="fp-ps-stat-card">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">📊</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Copertura WebP', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value"><?php printf('%s%%', number_format_i18n($status['coverage'], 1)); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Bulk Conversion Form -->
            <form method="post" class="fp-ps-bulk-convert-form">
                <?php wp_nonce_field('fp_ps_media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="bulk_convert" value="1" />
                
                <div class="fp-ps-bulk-convert-controls">
                    <div class="fp-ps-bulk-settings">
                        <details class="fp-ps-advanced-settings">
                            <summary class="fp-ps-advanced-toggle">
                                <span class="fp-ps-settings-icon">⚙️</span>
                                <?php esc_html_e('Impostazioni Avanzate', 'fp-performance-suite'); ?>
                                <span class="fp-ps-toggle-arrow">▼</span>
                            </summary>
                            <div class="fp-ps-advanced-content">
                                <div class="fp-ps-form-row">
                                    <div class="fp-ps-form-field">
                                        <label for="bulk_limit">
                                            <strong><?php esc_html_e('Immagini per batch', 'fp-performance-suite'); ?></strong>
                                            <small class="description"><?php esc_html_e('Numero di immagini da processare per volta', 'fp-performance-suite'); ?></small>
                                        </label>
                                        <input type="number" name="bulk_limit" id="bulk_limit" value="20" min="5" max="200" class="fp-ps-number-input" />
                                    </div>
                                    
                                    <div class="fp-ps-form-field">
                                        <label for="bulk_offset">
                                            <strong><?php esc_html_e('Offset (avanzato)', 'fp-performance-suite'); ?></strong>
                                            <small class="description"><?php esc_html_e('Inizia dalla immagine N (lascia 0 per iniziare dall\'inizio)', 'fp-performance-suite'); ?></small>
                                        </label>
                                        <input type="number" name="bulk_offset" id="bulk_offset" value="0" min="0" class="fp-ps-number-input" />
                                    </div>
                                </div>
                            </div>
                        </details>
                    </div>
                    
                    <div class="fp-ps-bulk-action-wrapper">
                        <button type="submit" class="button button-primary button-hero fp-ps-bulk-start-btn" data-risk="amber">
                            <span class="fp-ps-btn-icon">🚀</span>
                            <?php esc_html_e('Avvia Conversione Bulk', 'fp-performance-suite'); ?>
                        </button>
                        <p class="fp-ps-bulk-hint">
                            <span class="fp-ps-hint-icon">💡</span>
                            <?php esc_html_e('La conversione avviene in background. Puoi continuare a lavorare normalmente.', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                </div>
            </form>
            
            <?php if ($bulkResult) : ?>
                <div class="fp-ps-bulk-result">
                    <?php if (!empty($bulkResult['queued'])) : ?>
                        <div class="notice notice-success inline">
                            <p><strong>✅ <?php printf(esc_html__('%d immagini accodate per la conversione in background.', 'fp-performance-suite'), (int) ($bulkResult['total'] ?? 0)); ?></strong></p>
                        </div>
                    <?php else : ?>
                        <div class="notice notice-info inline">
                            <p><strong>📊 <?php printf(esc_html__('%1$d immagini processate su %2$d totali.', 'fp-performance-suite'), (int) $bulkResult['converted'], (int) $bulkResult['total']); ?></strong></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <!-- Info Box -->
            <div class="fp-ps-info-box">
                <div class="fp-ps-info-icon">ℹ️</div>
                <div class="fp-ps-info-content">
                    <strong><?php esc_html_e('Come funziona la conversione bulk?', 'fp-performance-suite'); ?></strong>
                    <ul class="fp-ps-info-list">
                        <li><?php esc_html_e('✓ Converte tutte le immagini JPEG e PNG in formato WebP', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('✓ Processa le immagini in batch per evitare timeout del server', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('✓ Mantiene le immagini originali (se configurato nelle impostazioni)', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('✓ Puoi interrompere e riprendere la conversione in qualsiasi momento', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
            </div>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
