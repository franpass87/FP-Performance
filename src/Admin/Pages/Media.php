<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;
use FP\PerfSuite\Admin\Components\StatusIndicator;

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
use function current_user_can;
use function wp_die;
use function add_query_arg;
use function admin_url;

class Media extends AbstractPage
{
    private ?ResponsiveImageOptimizer $responsiveOptimizer = null;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Media Optimization', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Determina la tab attiva
        $activeTab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'webp';
        $validTabs = ['webp', 'responsive'];
        if (!in_array($activeTab, $validTabs, true)) {
            $activeTab = 'webp';
        }
        
        // Gestione dei form submissions
        $this->handleFormSubmissions($activeTab);
        
        ob_start();
        
        // Render tabs navigation
        $this->renderTabsNavigation($activeTab);
        
        // Render content based on active tab
        if ($activeTab === 'responsive') {
            echo $this->renderResponsiveImagesTab();
        } else {
            echo $this->renderWebPTab();
        }
        
        return (string) ob_get_clean();
    }

    private function handleFormSubmissions(string $activeTab): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // WebP form submission
        if ($activeTab === 'webp' && isset($_POST['fp_ps_media_nonce'])) {
            if (!wp_verify_nonce(wp_unslash($_POST['fp_ps_media_nonce']), 'fp_ps_media')) {
                return;
            }
            // Il form sarà gestito nel rendering
        }

        // Responsive Images form submission
        if ($activeTab === 'responsive' && isset($_POST['fp_ps_responsive_images_nonce'])) {
            if (!wp_verify_nonce(wp_unslash($_POST['fp_ps_responsive_images_nonce']), 'fp_ps_responsive_images')) {
                wp_die(__('Security check failed.', 'fp-performance-suite'));
            }

            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions.', 'fp-performance-suite'));
            }

            $this->saveResponsiveImagesSettings();
        }

    }

    private function saveResponsiveImagesSettings(): void
    {
        $optimizer = $this->getResponsiveOptimizer();
        $settings = [];

        $settings['enabled'] = !empty($_POST['enabled']);
        $settings['generate_sizes'] = !empty($_POST['generate_sizes']);
        $settings['js_detection'] = !empty($_POST['js_detection']);
        
        $minWidth = (int) ($_POST['min_width'] ?? 300);
        $settings['min_width'] = max(100, min(2000, $minWidth));
        
        $minHeight = (int) ($_POST['min_height'] ?? 300);
        $settings['min_height'] = max(100, min(2000, $minHeight));
        
        $quality = (int) ($_POST['quality'] ?? 85);
        $settings['quality'] = max(60, min(100, $quality));

        if (!empty($_POST['reset_settings'])) {
            $settings = [
                'enabled' => true,
                'generate_sizes' => true,
                'js_detection' => true,
                'min_width' => 300,
                'min_height' => 300,
                'quality' => 85,
            ];
        }

        $optimizer->updateSettings($settings);
    }

    private function getResponsiveOptimizer(): ResponsiveImageOptimizer
    {
        if ($this->responsiveOptimizer === null) {
            $this->responsiveOptimizer = new ResponsiveImageOptimizer();
        }
        return $this->responsiveOptimizer;
    }


    private function renderTabsNavigation(string $activeTab): void
    {
        $baseUrl = admin_url('admin.php?page=fp-performance-suite-media');
        ?>
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="<?php echo esc_url($baseUrl . '&tab=webp'); ?>" 
               class="nav-tab <?php echo $activeTab === 'webp' ? 'nav-tab-active' : ''; ?>">
                🔄 <?php esc_html_e('WebP Conversion', 'fp-performance-suite'); ?>
            </a>
            <a href="<?php echo esc_url($baseUrl . '&tab=responsive'); ?>" 
               class="nav-tab <?php echo $activeTab === 'responsive' ? 'nav-tab-active' : ''; ?>">
                🖼️ <?php esc_html_e('Responsive Images', 'fp-performance-suite'); ?>
            </a>
        </div>
        <?php
    }

    private function renderWebPTab(): string
    {
        ob_start();
        
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
        
        // Controlla se c'è un plugin WebP di terze parti attivo
        $webpWarning = null;
        if (class_exists('FP\PerfSuite\Services\Compatibility\WebPPluginCompatibility')) {
            $compatManager = new \FP\PerfSuite\Services\Compatibility\WebPPluginCompatibility();
            $webpWarning = $compatManager->getWarningMessage();
        }
        
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <?php if ($webpWarning) : ?>
            <div class="notice notice-info fp-ps-webp-plugin-warning" style="position: relative; padding: 12px 16px; border-left: 4px solid #00a0d2; background: #f0f9ff;">
                <div style="display: flex; align-items: start; gap: 12px;">
                    <span style="font-size: 24px;">ℹ️</span>
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">
                            <?php esc_html_e('Plugin WebP Rilevato', 'fp-performance-suite'); ?>
                        </h3>
                        <p style="margin: 0 0 8px 0;">
                            <?php echo wp_kses_post($webpWarning['message']); ?>
                        </p>
                        <?php if (!empty($webpWarning['stats']['sources'])) : ?>
                            <div style="margin: 12px 0; padding: 12px; background: rgba(255,255,255,0.8); border-radius: 6px;">
                                <strong style="display: block; margin-bottom: 8px;">
                                    <?php esc_html_e('Riepilogo Conversioni WebP:', 'fp-performance-suite'); ?>
                                </strong>
                                <ul style="margin: 0; padding-left: 20px;">
                                    <?php foreach ($webpWarning['stats']['sources'] as $slug => $source) : ?>
                                        <li>
                                            <strong><?php echo esc_html($source['name']); ?>:</strong> 
                                            <?php echo esc_html(number_format_i18n($source['count'])); ?> 
                                            <?php esc_html_e('immagini convertite', 'fp-performance-suite'); ?>
                                            <?php if ($source['active']) : ?>
                                                <span style="color: #46b450; font-weight: 600;">● Attivo</span>
                                            <?php else : ?>
                                                <span style="color: #999;">○ Inattivo</span>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($webpWarning['recommendation'])) : ?>
                            <p style="margin: 8px 0 0 0; padding: 8px 12px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;">
                                <strong>💡 <?php esc_html_e('Raccomandazione:', 'fp-performance-suite'); ?></strong><br>
                                <?php echo esc_html($webpWarning['recommendation']); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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

    private function renderResponsiveImagesTab(): string
    {
        ob_start();
        
        $optimizer = $this->getResponsiveOptimizer();
        $status = $optimizer->status();
        $settings = $optimizer->getSettings();
        
        // Mostra messaggio di successo se presente
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fp_ps_responsive_images_nonce'])) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php esc_html_e('Impostazioni salvate con successo!', 'fp-performance-suite'); ?></p>
            </div>
            <?php
        }
        ?>
        
        <!-- Intro Section -->
        <div class="fp-ps-intro-panel">
            <h2>🖼️ <?php esc_html_e('Responsive Images Optimization', 'fp-performance-suite'); ?></h2>
            <p>
                <?php esc_html_e('Ottimizza automaticamente la dimensione delle immagini servite in base alle dimensioni effettive di visualizzazione per ridurre la banda e migliorare LCP.', 'fp-performance-suite'); ?>
            </p>
        </div>

        <!-- Status Overview -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Stato Ottimizzazione', 'fp-performance-suite'); ?></h2>
            <div class="fp-ps-grid three">
                <?php 
                echo StatusIndicator::renderCard(
                    $status['enabled'] ? 'success' : 'inactive',
                    __('Stato Sistema', 'fp-performance-suite'),
                    $status['enabled'] ? __('Ottimizzazione attiva', 'fp-performance-suite') : __('Ottimizzazione disattivata', 'fp-performance-suite'),
                    $status['enabled'] ? __('⚙️ Attivo', 'fp-performance-suite') : __('Disattivo', 'fp-performance-suite')
                );
                
                echo StatusIndicator::renderCard(
                    'info',
                    __('Dimensioni Minime', 'fp-performance-suite'),
                    __('Soglie di attivazione ottimizzazione', 'fp-performance-suite'),
                    '📐 ' . esc_html($status['min_dimensions'])
                );
                
                echo StatusIndicator::renderCard(
                    'success',
                    __('Qualità Immagini', 'fp-performance-suite'),
                    __('Qualità immagini ottimizzate', 'fp-performance-suite'),
                    '📊 ' . esc_html($status['quality']) . '%'
                );
                ?>
            </div>
        </section>

        <!-- Configuration Form -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('⚙️ Configurazione', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Configura il comportamento dell\'ottimizzazione responsive delle immagini.', 'fp-performance-suite'); ?>
            </p>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_responsive_images', 'fp_ps_responsive_images_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php esc_html_e('Abilita Responsive Images', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?>>
                            <p class="description">
                                <?php esc_html_e('Attiva l\'ottimizzazione automatica della dimensione delle immagini.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="generate_sizes"><?php esc_html_e('Genera dimensioni mancanti', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="generate_sizes" name="generate_sizes" value="1" <?php checked($settings['generate_sizes']); ?>>
                            <p class="description">
                                <?php esc_html_e('Crea automaticamente dimensioni ottimizzate al bisogno.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="js_detection"><?php esc_html_e('Rilevamento JavaScript', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="js_detection" name="js_detection" value="1" <?php checked($settings['js_detection']); ?>>
                            <p class="description">
                                <?php esc_html_e('Rileva dimensioni reali tramite JavaScript per maggiore precisione.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <h3><?php esc_html_e('Parametri di Ottimizzazione', 'fp-performance-suite'); ?></h3>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="min_width"><?php esc_html_e('Larghezza minima (px)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="min_width" id="min_width" value="<?php echo esc_attr($settings['min_width']); ?>" min="100" max="2000" step="50" class="regular-text" />
                            <p class="description">
                                <?php esc_html_e('Larghezza minima immagine da considerare per l\'ottimizzazione.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="min_height"><?php esc_html_e('Altezza minima (px)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="min_height" id="min_height" value="<?php echo esc_attr($settings['min_height']); ?>" min="100" max="2000" step="50" class="regular-text" />
                            <p class="description">
                                <?php esc_html_e('Altezza minima immagine da considerare per l\'ottimizzazione.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="quality"><?php esc_html_e('Qualità immagini (%)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <input type="range" name="quality" id="quality" value="<?php echo esc_attr($settings['quality']); ?>" min="60" max="100" step="5" style="flex: 1; max-width: 300px;" />
                                <span id="quality-value" style="min-width: 50px; font-weight: 600; font-size: 18px; color: #2271b1;"><?php echo esc_html($settings['quality']); ?>%</span>
                            </div>
                            <p class="description">
                                <?php esc_html_e('Qualità per le immagini ottimizzate generate (più alto = migliore qualità, file più grandi).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="save_settings" class="button button-primary button-large">
                        💾 <?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?>
                    </button>
                    <button type="submit" name="reset_settings" class="button button-secondary" style="margin-left: 10px;">
                        🔄 <?php esc_html_e('Ripristina Default', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </form>
        </section>

        <!-- Performance Impact -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('📊 Impatto sulle Performance', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('Benefici dell\'ottimizzazione responsive delle immagini.', 'fp-performance-suite'); ?>
            </p>
            
            <div class="fp-ps-grid three" style="margin-top: 20px;">
                <div class="fp-ps-stat-box" style="border-left: 4px solid #667eea;">
                    <div class="stat-label"><?php esc_html_e('Lighthouse Score', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">📊</div>
                    <p class="description">
                        <?php esc_html_e('Migliora l\'audit "Properly size images"', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #f093fb;">
                    <div class="stat-label"><?php esc_html_e('LCP Migliorato', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 40px;">⚡</div>
                    <p class="description">
                        <?php esc_html_e('Riduce Largest Contentful Paint', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div class="fp-ps-stat-box" style="border-left: 4px solid #4facfe;">
                    <div class="stat-label"><?php esc_html_e('Risparmio Banda', 'fp-performance-suite'); ?></div>
                    <div class="stat-value success">40-60%</div>
                    <p class="description">
                        <?php esc_html_e('Riduzione trasferimento dati', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>

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
        
        <?php
        return (string) ob_get_clean();
    }

}
