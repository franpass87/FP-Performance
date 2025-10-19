<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Services\Media\AVIFConverter;
use FP\PerfSuite\Services\Assets\LazyLoadManager;
use FP\PerfSuite\Services\Assets\ImageOptimizer;

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
        $avifConverter = $this->container->get(AVIFConverter::class);
        $lazyLoad = $this->container->get(LazyLoadManager::class);
        $imageOptimizer = $this->container->get(ImageOptimizer::class);
        $message = '';
        $bulkResult = null;
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_media_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_media_nonce']), 'fp-ps-media')) {
            // Determina quale form è stato inviato
            $formType = sanitize_text_field($_POST['form_type'] ?? '');
            
            if ($formType === 'webp') {
                $converter->update([
                    'enabled' => !empty($_POST['webp_enabled']),
                    'quality' => (int) ($_POST['webp_quality'] ?? 82),
                    'keep_original' => !empty($_POST['keep_original']),
                    'lossy' => !empty($_POST['lossy']),
                    'auto_deliver' => !empty($_POST['auto_deliver']),
                ]);
                $message = __('WebP settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'avif') {
                $avifConverter->update([
                    'enabled' => !empty($_POST['avif_enabled']),
                    'quality' => (int) ($_POST['avif_quality'] ?? 75),
                    'keep_original' => !empty($_POST['avif_keep_original']),
                    'auto_deliver' => !empty($_POST['avif_auto_deliver']),
                    'speed' => (int) ($_POST['avif_speed'] ?? 6),
                    'strip_metadata' => !empty($_POST['avif_strip_metadata']),
                ]);
                $message = __('AVIF settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'lazy_load') {
                $lazyLoad->updateSettings([
                    'enabled' => !empty($_POST['lazy_load_enabled']),
                    'images' => !empty($_POST['lazy_load_images']),
                    'iframes' => !empty($_POST['lazy_load_iframes']),
                    'skip_first' => (int) ($_POST['lazy_load_skip_first'] ?? 1),
                    'threshold' => (int) ($_POST['lazy_load_threshold'] ?? 200),
                ]);
                $message = __('Lazy Load settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'image_optimizer') {
                $imageOptimizer->updateSettings([
                    'enabled' => !empty($_POST['image_optimizer_enabled']),
                    'force_dimensions' => !empty($_POST['force_dimensions']),
                    'add_aspect_ratio' => !empty($_POST['add_aspect_ratio']),
                ]);
                $message = __('Image Optimizer settings saved.', 'fp-performance-suite');
            }
            
            // Bulk conversion is now handled via AJAX
            // This fallback is kept for compatibility but should not be used
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
        $avifSettings = $avifConverter->settings();
        $avifStatus = $avifConverter->status();
        $avifInfo = $avifConverter->getInfo();
        $lazyLoadSettings = $lazyLoad->getSettings();
        $imageSettings = $imageOptimizer->getSettings();
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('WebP Conversion', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="form_type" value="webp" />
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
                        <?php esc_html_e('Questa funzione analizza la tua libreria media WordPress e converte tutte le immagini JPEG e PNG in formato WebP. Le immagini WebP sono più leggere del 30-40% senza perdita di qualità visibile, migliorando i tempi di caricamento del tuo sito.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
            
            <!-- Statistics Grid -->
            <?php 
                $totalImages = $status['total_images'] ?? 0;
                $convertedImages = $status['converted_images'] ?? 0;
                $toConvert = $totalImages - $convertedImages;
                $hasImages = $totalImages > 0;
            ?>
            <?php if (!$hasImages) : ?>
            <div class="fp-ps-info-box" style="background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-left-color: #0ea5e9; margin-bottom: 20px;">
                <div class="fp-ps-info-icon">💡</div>
                <div class="fp-ps-info-content">
                    <strong style="color: #0c4a6e;"><?php esc_html_e('Nessuna immagine trovata nella libreria media', 'fp-performance-suite'); ?></strong>
                    <p style="color: #075985; font-size: 13px; margin: 8px 0 0 0;">
                        <?php esc_html_e('Carica alcune immagini nella tua libreria media per iniziare la conversione WebP. Le nuove immagini verranno convertite automaticamente se hai abilitato "Enable WebP on upload" qui sopra.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
            <?php else : ?>
            <div class="fp-ps-bulk-stats-grid">
                <div class="fp-ps-stat-card">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">📚</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Totale Immagini', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value"><?php echo number_format_i18n($totalImages); ?></div>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px;"><?php esc_html_e('Nella libreria media', 'fp-performance-suite'); ?></small>
                    </div>
                </div>
                
                <div class="fp-ps-stat-card">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #1f9d55 0%, #0ea372 100%);">✅</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Già Convertite', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value"><?php echo number_format_i18n($convertedImages); ?></div>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px;"><?php esc_html_e('Hanno già il formato WebP', 'fp-performance-suite'); ?></small>
                    </div>
                </div>
                
                <div class="fp-ps-stat-card" style="<?php echo $toConvert > 0 ? 'border-color: #f59e0b; background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);' : ''; ?>">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">⏳</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Da Convertire', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value" style="<?php echo $toConvert > 0 ? 'color: #ea580c;' : ''; ?>"><?php echo number_format_i18n($toConvert); ?></div>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px;">
                            <?php if ($toConvert > 0) : ?>
                                <?php esc_html_e('Clicca il pulsante sotto per convertirle', 'fp-performance-suite'); ?>
                            <?php else : ?>
                                <?php esc_html_e('Tutte le immagini sono convertite!', 'fp-performance-suite'); ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
                
                <div class="fp-ps-stat-card" style="<?php echo $status['coverage'] >= 80 ? 'border-color: #10b981; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);' : ''; ?>">
                    <div class="fp-ps-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">📊</div>
                    <div class="fp-ps-stat-content">
                        <div class="fp-ps-stat-label"><?php esc_html_e('Copertura WebP', 'fp-performance-suite'); ?></div>
                        <div class="fp-ps-stat-value" style="<?php echo $status['coverage'] >= 80 ? 'color: #059669;' : ''; ?>"><?php printf('%s%%', number_format_i18n($status['coverage'], 1)); ?></div>
                        <small style="color: #64748b; font-size: 11px; margin-top: 4px;"><?php esc_html_e('Percentuale immagini WebP', 'fp-performance-suite'); ?></small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Bulk Conversion Form -->
            <form id="fp-ps-webp-bulk-form" method="post" class="fp-ps-bulk-convert-form">
                <?php wp_nonce_field('fp-ps-media', 'fp_ps_media_nonce'); ?>
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
                        <button type="button" id="fp-ps-webp-bulk-btn" class="button button-primary button-hero fp-ps-bulk-start-btn" data-risk="amber" data-status-nonce="<?php echo esc_attr(wp_create_nonce('fp_ps_webp_status')); ?>" <?php echo !$hasImages || $toConvert === 0 ? 'disabled' : ''; ?>>
                            <span class="fp-ps-btn-icon">🚀</span>
                            <?php 
                                if (!$hasImages) {
                                    esc_html_e('Nessuna immagine da convertire', 'fp-performance-suite');
                                } elseif ($toConvert === 0) {
                                    esc_html_e('Tutte le immagini sono già convertite', 'fp-performance-suite');
                                } else {
                                    printf(esc_html__('Converti %s immagini in WebP', 'fp-performance-suite'), number_format_i18n($toConvert));
                                }
                            ?>
                        </button>
                        <p class="fp-ps-bulk-hint">
                            <span class="fp-ps-hint-icon">💡</span>
                            <?php 
                                if ($hasImages && $toConvert > 0) {
                                    esc_html_e('La conversione avviene in background. Puoi continuare a lavorare normalmente.', 'fp-performance-suite');
                                } elseif ($hasImages && $toConvert === 0) {
                                    esc_html_e('Ottimo! Tutte le tue immagini sono già in formato WebP.', 'fp-performance-suite');
                                } else {
                                    esc_html_e('Carica delle immagini nella libreria media per iniziare.', 'fp-performance-suite');
                                }
                            ?>
                        </p>
                    </div>
                </div>
            </form>
            
            <!-- Progress Display -->
            <div id="fp-ps-webp-bulk-progress" class="fp-ps-bulk-progress-wrapper"></div>
            
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
                        <li><?php esc_html_e('✓ Scansiona la libreria media e trova tutte le immagini JPEG e PNG', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('✓ Converte ogni immagine in formato WebP (più leggero del 30-40%)', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('✓ Processa le immagini in batch per evitare timeout del server', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('✓ Mantiene le immagini originali per compatibilità (se configurato sopra)', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('✓ Puoi interrompere e riprendere la conversione in qualsiasi momento', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
            </div>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('AVIF Conversion', 'fp-performance-suite'); ?> <span class="fp-ps-badge <?php echo $avifInfo['available'] ? 'green' : 'amber'; ?>" style="font-size: 0.7em;"><?php echo $avifInfo['available'] ? esc_html__('Disponibile', 'fp-performance-suite') : esc_html__('Non Disponibile', 'fp-performance-suite'); ?></span></h2>
            <p style="color: #666; margin-bottom: 20px;">
                <?php if ($avifInfo['available']) : ?>
                    <?php printf(esc_html__('AVIF è un formato immagine moderno che offre compressione superiore a WebP (fino al 50%% più piccolo). Metodo: %s', 'fp-performance-suite'), esc_html($avifInfo['method'])); ?>
                <?php else : ?>
                    <?php esc_html_e('AVIF non è disponibile su questo server. È richiesto PHP 8.1+ con estensione GD o Imagick con supporto AVIF.', 'fp-performance-suite'); ?>
                <?php endif; ?>
            </p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="form_type" value="avif" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable AVIF on upload', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Converte automaticamente le immagini caricate in formato AVIF, il formato più efficiente disponibile.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce il peso delle immagini del 50-60% rispetto a JPEG, 30-40% rispetto a WebP.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Altamente consigliato: Supportato da tutti i browser moderni (Chrome 85+, Firefox 93+, Safari 16+).', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                        <small><?php esc_html_e('Automatically convert uploaded images to AVIF format', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="avif_enabled" value="1" <?php checked($avifSettings['enabled']); ?> <?php disabled(!$avifInfo['available']); ?> data-risk="green" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Auto-deliver AVIF images', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Serve automaticamente AVIF ai browser compatibili con fallback automatico.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                        <small><?php esc_html_e('Automatically serve AVIF to compatible browsers (50-60% smaller)', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="avif_auto_deliver" value="1" <?php checked($avifSettings['auto_deliver']); ?> <?php disabled(!$avifInfo['available']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="avif_quality"><?php esc_html_e('Quality (1-100)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="avif_quality" id="avif_quality" value="<?php echo esc_attr((string) $avifSettings['quality']); ?>" min="1" max="100" <?php disabled(!$avifInfo['available']); ?> />
                    <span class="description"><?php esc_html_e('Consigliato: 75 per un buon compromesso qualità/dimensione', 'fp-performance-suite'); ?></span>
                </p>
                <?php if ($avifInfo['method'] === 'imagick') : ?>
                <p>
                    <label for="avif_speed"><?php esc_html_e('Speed (0-10, Imagick only)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="avif_speed" id="avif_speed" value="<?php echo esc_attr((string) $avifSettings['speed']); ?>" min="0" max="10" />
                    <span class="description"><?php esc_html_e('0 = qualità massima (lento), 10 = veloce (qualità inferiore). Consigliato: 6', 'fp-performance-suite'); ?></span>
                </p>
                <?php endif; ?>
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
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Mantiene sia i file originali che le versioni AVIF sul server.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Aumenta l\'utilizzo dello spazio disco.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="avif_keep_original" value="1" <?php checked($avifSettings['keep_original']); ?> <?php disabled(!$avifInfo['available']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Strip metadata (EXIF, etc.)', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove i metadati EXIF per ridurre ulteriormente le dimensioni dei file.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Riduce le dimensioni senza impatto visivo. Disattiva solo se hai bisogno dei metadati.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="avif_strip_metadata" value="1" <?php checked($avifSettings['strip_metadata']); ?> <?php disabled(!$avifInfo['available']); ?> data-risk="green" />
                </label>
                <p>
                    <button type="submit" class="button button-primary" <?php disabled(!$avifInfo['available']); ?>><?php esc_html_e('Salva Impostazioni AVIF', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('Lazy Loading', 'fp-performance-suite'); ?></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Carica immagini e iframe solo quando sono visibili nel viewport dell\'utente', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="form_type" value="lazy_load" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Lazy Loading', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Carica immagini e iframe solo quando sono visibili nel viewport.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Migliora significativamente LCP (-30-50%) e TTI. Riduce il peso iniziale della pagina.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Altamente consigliato: Essenziale per siti con molte immagini. Impatto PageSpeed: +10-15 punti.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="lazy_load_enabled" value="1" <?php checked($lazyLoadSettings['enabled']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Lazy load immagini', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="lazy_load_images" value="1" <?php checked($lazyLoadSettings['images']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Lazy load iframe (YouTube, etc.)', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="lazy_load_iframes" value="1" <?php checked($lazyLoadSettings['iframes']); ?> />
                </label>
                <p style="margin-left: 30px;">
                    <label for="lazy_load_skip_first"><?php esc_html_e('Salta le prime N immagini (hero images)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="lazy_load_skip_first" id="lazy_load_skip_first" value="<?php echo esc_attr((string) $lazyLoadSettings['skip_first']); ?>" min="0" max="5" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Consigliato: 1-2 per evitare di lazy-loadare immagini above-the-fold', 'fp-performance-suite'); ?></span>
                </p>
                <p style="margin-left: 30px;">
                    <label for="lazy_load_threshold"><?php esc_html_e('Distanza dal viewport (px)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="lazy_load_threshold" id="lazy_load_threshold" value="<?php echo esc_attr((string) ($lazyLoadSettings['threshold'] ?? 200)); ?>" min="0" max="1000" step="50" style="width: 120px;" />
                    <span class="description"><?php esc_html_e('Inizia a caricare quando l\'elemento è a questa distanza dal viewport. Consigliato: 200-300px', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Lazy Load', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('Ottimizzazione Immagini', 'fp-performance-suite'); ?></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Previene Cumulative Layout Shift (CLS) aggiungendo dimensioni esplicite alle immagini', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-media', 'fp_ps_media_nonce'); ?>
                <input type="hidden" name="form_type" value="image_optimizer" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita ottimizzazione immagini', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Aggiunge automaticamente dimensioni e aspect-ratio alle immagini.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Previene CLS (Cumulative Layout Shift) e migliora il punteggio Core Web Vitals.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Essenziale per ridurre il CLS. Impatto PageSpeed: +3-5 punti.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="image_optimizer_enabled" value="1" <?php checked($imageSettings['enabled']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Forza dimensioni esplicite (width/height)', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="force_dimensions" value="1" <?php checked($imageSettings['force_dimensions']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Aggiungi CSS aspect-ratio', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="add_aspect_ratio" value="1" <?php checked($imageSettings['add_aspect_ratio']); ?> />
                </label>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Riepilogo ottimizzazioni Media:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('WebP: -30-40% dimensione vs JPEG', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('AVIF: -50-60% dimensione vs JPEG, -30-40% vs WebP', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Lazy Loading: +10-15 punti PageSpeed Mobile', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Image Optimizer: +3-5 punti PageSpeed, riduce CLS', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Image Optimizer', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        <?php
        return (string) ob_get_clean();
    }
}
