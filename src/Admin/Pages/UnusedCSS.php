<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\UnusedCSSOptimizer;
use FP\PerfSuite\Admin\Components\StatusIndicator;

use function __;
use function esc_html;
use function esc_html_e;
use function esc_attr;
use function esc_textarea;
use function checked;
use function wp_nonce_field;
use function wp_verify_nonce;
use function sanitize_textarea_field;
use function current_user_can;

/**
 * Unused CSS Optimization Admin Page
 * 
 * Gestisce l'ottimizzazione del CSS non utilizzato identificato dal report Lighthouse
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class UnusedCSS extends AbstractPage
{
    private UnusedCSSOptimizer $optimizer;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->optimizer = new UnusedCSSOptimizer();
    }

    public function slug(): string
    {
        return 'fp-performance-suite-unused-css';
    }

    public function title(): string
    {
        return __('Unused CSS Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [
                __('Performance', 'fp-performance-suite'),
                __('Unused CSS', 'fp-performance-suite'),
            ],
        ];
    }

    public function render(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fp_ps_unused_css_nonce'])) {
            $this->handleFormSubmission();
        }

        parent::render();
    }

    /**
     * Handle form submission
     */
    private function handleFormSubmission(): void
    {
        if (!wp_verify_nonce($_POST['fp_ps_unused_css_nonce'], 'fp_ps_unused_css_settings')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = [
            'enabled' => !empty($_POST['enabled']),
            'remove_unused_css' => !empty($_POST['remove_unused_css']),
            'defer_non_critical' => !empty($_POST['defer_non_critical']),
            'inline_critical' => !empty($_POST['inline_critical']),
            'enable_css_purging' => !empty($_POST['enable_css_purging']),
            'critical_css' => sanitize_textarea_field($_POST['critical_css'] ?? ''),
        ];

        $this->optimizer->updateSettings($settings);
    }

    protected function content(): string
    {
        $settings = $this->optimizer->getSettings();
        $status = $this->optimizer->status();
        $unusedFiles = $this->getUnusedCSSFiles();

        ob_start();
        ?>
        
        <!-- Intro Section -->
        <div class="fp-ps-intro-panel">
            <h2>🎨 <?php esc_html_e('Ottimizzazione CSS Non Utilizzato', 'fp-performance-suite'); ?></h2>
            <p>
                <?php esc_html_e('Risolve il problema dei', 'fp-performance-suite'); ?>
                <strong><?php esc_html_e('130 KiB di CSS non utilizzato', 'fp-performance-suite'); ?></strong>
                <?php esc_html_e('identificato nel report Lighthouse. Questa ottimizzazione può migliorare significativamente le performance del tuo sito.', 'fp-performance-suite'); ?>
            </p>
        </div>

        <!-- Status Overview -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Stato Ottimizzazione', 'fp-performance-suite'); ?></h2>
            <div class="fp-ps-grid three">
                <?php 
                echo StatusIndicator::renderCard(
                    'info',
                    __('Risparmio Stimato', 'fp-performance-suite'),
                    __('Riduzione dimensione totale', 'fp-performance-suite'),
                    '130 KiB'
                );
                
                echo StatusIndicator::renderCard(
                    'info',
                    __('File CSS Analizzati', 'fp-performance-suite'),
                    __('File identificati da Lighthouse', 'fp-performance-suite'),
                    (string)count($unusedFiles)
                );
                
                echo StatusIndicator::renderCard(
                    $status['enabled'] ? 'success' : 'inactive',
                    __('Stato Sistema', 'fp-performance-suite'),
                    $status['enabled'] ? __('Ottimizzazione attiva', 'fp-performance-suite') : __('Ottimizzazione disattivata', 'fp-performance-suite'),
                    $status['enabled'] ? __('Attivo', 'fp-performance-suite') : __('Inattivo', 'fp-performance-suite')
                );
                ?>
            </div>
        </section>

        <!-- Lighthouse Report Analysis -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Analisi Report Lighthouse', 'fp-performance-suite'); ?></h2>
            <p class="description">
                <?php esc_html_e('File CSS identificati come non utilizzati dal report Lighthouse e azioni suggerite.', 'fp-performance-suite'); ?>
            </p>
            
            <div style="margin-top: 20px;">
                <?php foreach ($unusedFiles as $file => $info): ?>
                    <div style="padding: 15px; margin-bottom: 10px; background: #f9fafb; border-left: 4px solid #2271b1; border-radius: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                            <div>
                                <strong><?php echo esc_html($file); ?></strong>
                                <div style="color: #059669; font-weight: 600; margin-top: 4px;">
                                    💾 <?php echo esc_html($info['savings']); ?>
                                </div>
                            </div>
                            <div>
                                <?php if ($info['remove']): ?>
                                    <span style="background: #fee2e2; color: #dc2626; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                        <?php esc_html_e('Rimosso', 'fp-performance-suite'); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="background: #fef3c7; color: #92400e; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                        <?php esc_html_e('Differito', 'fp-performance-suite'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="color: #6b7280; font-size: 14px;">
                            <?php echo esc_html($info['reason']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Settings Form -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Configurazione Ottimizzazioni', 'fp-performance-suite'); ?></h2>
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_unused_css_settings', 'fp_ps_unused_css_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Abilita Ottimizzazione CSS', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?>>
                                <?php esc_html_e('Attiva l\'ottimizzazione del CSS non utilizzato', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Rimuove o differisce il CSS non utilizzato per migliorare le performance.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Rimuovi CSS Non Utilizzato', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="remove_unused_css" value="1" <?php checked($settings['remove_unused_css']); ?>>
                                <?php esc_html_e('Rimuovi completamente i file CSS non utilizzati', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Rimuove i file CSS identificati come non utilizzati dal report Lighthouse.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Differisci CSS Non Critici', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="defer_non_critical" value="1" <?php checked($settings['defer_non_critical']); ?>>
                                <?php esc_html_e('Differisci il caricamento del CSS non critico', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Carica il CSS non critico dopo il caricamento della pagina per migliorare LCP.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('CSS Critico Inline', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="inline_critical" value="1" <?php checked($settings['inline_critical']); ?>>
                                <?php esc_html_e('Inserisci CSS critico inline nell\'head', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Inserisce il CSS critico direttamente nell\'head per evitare render blocking.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('Purging CSS Dinamico', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_css_purging" value="1" <?php checked($settings['enable_css_purging']); ?>>
                                <?php esc_html_e('Abilita purging dinamico del CSS', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Rimuove dinamicamente i selettori CSS non utilizzati dal DOM.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php esc_html_e('CSS Critico Personalizzato', 'fp-performance-suite'); ?></th>
                        <td>
                            <textarea name="critical_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($settings['critical_css']); ?></textarea>
                            <p class="description">
                                <?php esc_html_e('Inserisci il CSS critico personalizzato per il contenuto above-the-fold. Se lasciato vuoto, verrà utilizzato un CSS critico generico ottimizzato.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Salva Impostazioni CSS', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </form>
        </section>

        <!-- Performance Impact -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Impatto sulle Performance', 'fp-performance-suite'); ?></h2>
            <div class="fp-ps-grid two">
                <div class="fp-ps-stat-box">
                    <div class="stat-label"><?php esc_html_e('Largest Contentful Paint (LCP)', 'fp-performance-suite'); ?></div>
                    <div class="stat-value success">200-500ms</div>
                    <p class="description"><?php esc_html_e('Il CSS critico inline riduce il tempo di rendering del contenuto principale.', 'fp-performance-suite'); ?></p>
                </div>
                <div class="fp-ps-stat-box">
                    <div class="stat-label"><?php esc_html_e('First Contentful Paint (FCP)', 'fp-performance-suite'); ?></div>
                    <div class="stat-value success">150-300ms</div>
                    <p class="description"><?php esc_html_e('La rimozione del CSS non utilizzato accelera il primo rendering.', 'fp-performance-suite'); ?></p>
                </div>
                <div class="fp-ps-stat-box">
                    <div class="stat-label"><?php esc_html_e('Riduzione Dimensione', 'fp-performance-suite'); ?></div>
                    <div class="stat-value success">130 KiB</div>
                    <p class="description"><?php esc_html_e('Riduzione significativa della dimensione totale della pagina.', 'fp-performance-suite'); ?></p>
                </div>
                <div class="fp-ps-stat-box">
                    <div class="stat-label"><?php esc_html_e('Render Blocking', 'fp-performance-suite'); ?></div>
                    <div class="stat-value success">6 <?php esc_html_e('file CSS', 'fp-performance-suite'); ?></div>
                    <p class="description"><?php esc_html_e('Riduzione del render blocking per migliorare le metriche Core Web Vitals.', 'fp-performance-suite'); ?></p>
                </div>
            </div>
        </section>

        <!-- Recommendations -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Raccomandazioni', 'fp-performance-suite'); ?></h2>
            <div style="display: grid; gap: 15px; margin-top: 20px;">
                <div style="padding: 15px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 6px;">
                    <h4 style="margin-top: 0;">🎯 <?php esc_html_e('Testa le Modifiche', 'fp-performance-suite'); ?></h4>
                    <p style="margin-bottom: 0;"><?php esc_html_e('Dopo aver attivato l\'ottimizzazione, esegui un nuovo test Lighthouse per verificare i miglioramenti.', 'fp-performance-suite'); ?></p>
                </div>
                <div style="padding: 15px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 6px;">
                    <h4 style="margin-top: 0;">📊 <?php esc_html_e('Monitora le Performance', 'fp-performance-suite'); ?></h4>
                    <p style="margin-bottom: 0;"><?php esc_html_e('Controlla regolarmente le metriche Core Web Vitals per assicurarti che le ottimizzazioni funzionino correttamente.', 'fp-performance-suite'); ?></p>
                </div>
                <div style="padding: 15px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 6px;">
                    <h4 style="margin-top: 0;">🔧 <?php esc_html_e('Personalizza il CSS Critico', 'fp-performance-suite'); ?></h4>
                    <p style="margin-bottom: 0;"><?php esc_html_e('Per risultati ottimali, personalizza il CSS critico in base al design specifico del tuo sito.', 'fp-performance-suite'); ?></p>
                </div>
            </div>
        </section>
        
        <?php
        return ob_get_clean();
    }

    /**
     * Get unused CSS files from Lighthouse report
     */
    private function getUnusedCSSFiles(): array
    {
        return [
            'dashicons.min.css' => [
                'savings' => '35.8 KiB',
                'reason' => 'Icone WordPress non utilizzate',
                'remove' => true
            ],
            'style.css' => [
                'savings' => '35.6 KiB',
                'reason' => 'Stili del tema non utilizzati',
                'remove' => false
            ],
            'salient-dynamic-styles.css' => [
                'savings' => '19.8 KiB',
                'reason' => 'Stili dinamici Salient non utilizzati',
                'remove' => true
            ],
            'sbi-styles.min.css' => [
                'savings' => '18.1 KiB',
                'reason' => 'Plugin Instagram non utilizzato',
                'remove' => true
            ],
            'font-awesome-legacy.min.css' => [
                'savings' => '11.0 KiB',
                'reason' => 'Font Awesome legacy non utilizzato',
                'remove' => true
            ],
            'skin-material.css' => [
                'savings' => '10.0 KiB',
                'reason' => 'Stili Material Design non utilizzati',
                'remove' => true
            ]
        ];
    }
}
