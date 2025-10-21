<?php
/**
 * Responsive Images Configuration Page
 *
 * @package FP\PerfSuite\Views\Admin
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

defined('ABSPATH') || exit;

use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;

$optimizer = new ResponsiveImageOptimizer();
$status = $optimizer->status();
$settings = $optimizer->getSettings();
$message = '';

if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_responsive_images_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_responsive_images_nonce']), 'fp_ps_responsive_images')) {
    if (isset($_POST['save_settings'])) {
        $new_settings = [
            'enabled' => !empty($_POST['enabled']),
            'generate_sizes' => !empty($_POST['generate_sizes']),
            'js_detection' => !empty($_POST['js_detection']),
            'min_width' => (int) ($_POST['min_width'] ?? 200),
            'min_height' => (int) ($_POST['min_height'] ?? 200),
            'quality' => (int) ($_POST['quality'] ?? 82),
        ];
        $optimizer->updateSettings($new_settings);
        $settings = $optimizer->getSettings();
        $status = $optimizer->status();
        $message = __('Responsive Images settings saved.', 'fp-performance-suite');
    } elseif (isset($_POST['reset_settings'])) {
        $optimizer->resetSettings();
        $settings = $optimizer->getSettings();
        $status = $optimizer->status();
        $message = __('Settings reset to defaults.', 'fp-performance-suite');
    }
}
?>

<?php if ($message) : ?>
    <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
<?php endif; ?>

<!-- Stato Generale -->
<section class="fp-ps-card">
    <h2>🖼️ <?php esc_html_e('Responsive Images Optimization', 'fp-performance-suite'); ?></h2>
    <p class="description"><?php esc_html_e('Ottimizza automaticamente la dimensione delle immagini servite in base alle dimensioni effettive di visualizzazione per ridurre la banda e migliorare LCP.', 'fp-performance-suite'); ?></p>
    
    <!-- Status Overview Grid -->
    <div class="fp-ps-grid three" style="margin: 20px 0;">
        <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; border-left: 4px solid #0ea5e9;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="font-size: 24px;">⚙️</span>
                <strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong>
            </div>
            <div style="font-size: 20px; font-weight: 600; color: <?php echo $status['enabled'] ? '#16a34a' : '#dc2626'; ?>;">
                <?php echo $status['enabled'] ? __('✓ Attivo', 'fp-performance-suite') : __('✗ Disattivo', 'fp-performance-suite'); ?>
            </div>
        </div>
        
        <div style="background: #fef3c7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="font-size: 24px;">📐</span>
                <strong><?php esc_html_e('Dimensioni Minime:', 'fp-performance-suite'); ?></strong>
            </div>
            <div style="font-size: 18px; font-weight: 600; color: #92400e;">
                <?php echo esc_html($status['min_dimensions']); ?>
            </div>
        </div>
        
        <div style="background: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 4px solid #22c55e;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                <span style="font-size: 24px;">📊</span>
                <strong><?php esc_html_e('Qualità:', 'fp-performance-suite'); ?></strong>
            </div>
            <div style="font-size: 20px; font-weight: 600; color: #166534;">
                <?php echo esc_html($status['quality']); ?>%
            </div>
        </div>
    </div>
</section>

<!-- Configuration Form -->
<section class="fp-ps-card">
    <h2><?php esc_html_e('⚙️ Configurazione', 'fp-performance-suite'); ?></h2>
    <p class="description"><?php esc_html_e('Configura il comportamento dell\'ottimizzazione responsive delle immagini.', 'fp-performance-suite'); ?></p>
    
    <form method="post" action="">
        <?php wp_nonce_field('fp_ps_responsive_images', 'fp_ps_responsive_images_nonce'); ?>
        
        <div class="fp-ps-grid two">
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong><?php esc_html_e('Abilita Responsive Images', 'fp-performance-suite'); ?></strong>
                    <span class="fp-ps-risk-indicator green">
                        <div class="fp-ps-risk-tooltip green">
                            <div class="fp-ps-risk-tooltip-title">
                                <span class="icon">✓</span>
                                <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Ottimizza automaticamente la dimensione delle immagini basandosi sulle dimensioni di visualizzazione.', 'fp-performance-suite'); ?></div>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Migliora LCP e audit Lighthouse "Properly size images". Riduce il trasferimento dati del 40-60%.', 'fp-performance-suite'); ?></div>
                            </div>
                        </div>
                    </span>
                    <small><?php esc_html_e('Attiva l\'ottimizzazione automatica della dimensione delle immagini', 'fp-performance-suite'); ?></small>
                </span>
                <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?> data-risk="green" />
            </label>
            
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong><?php esc_html_e('Genera dimensioni mancanti', 'fp-performance-suite'); ?></strong>
                    <span class="fp-ps-risk-indicator amber">
                        <div class="fp-ps-risk-tooltip amber">
                            <div class="fp-ps-risk-tooltip-title">
                                <span class="icon">⚠</span>
                                <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?></div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Genera automaticamente varianti di dimensioni ottimizzate quando necessario.', 'fp-performance-suite'); ?></div>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Attenzione', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Può aumentare temporaneamente il carico del server quando genera nuove dimensioni.', 'fp-performance-suite'); ?></div>
                            </div>
                        </div>
                    </span>
                    <small><?php esc_html_e('Crea automaticamente dimensioni ottimizzate al bisogno', 'fp-performance-suite'); ?></small>
                </span>
                <input type="checkbox" name="generate_sizes" value="1" <?php checked($settings['generate_sizes']); ?> data-risk="amber" />
            </label>
            
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong><?php esc_html_e('Rilevamento JavaScript', 'fp-performance-suite'); ?></strong>
                    <span class="fp-ps-risk-indicator green">
                        <div class="fp-ps-risk-tooltip green">
                            <div class="fp-ps-risk-tooltip-title">
                                <span class="icon">✓</span>
                                <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Usa JavaScript per rilevare le dimensioni effettive di visualizzazione per un\'ottimizzazione più precisa.', 'fp-performance-suite'); ?></div>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Ottimizzazione più accurata specialmente su dispositivi diversi e orientamenti.', 'fp-performance-suite'); ?></div>
                            </div>
                        </div>
                    </span>
                    <small><?php esc_html_e('Rileva dimensioni reali tramite JavaScript per maggiore precisione', 'fp-performance-suite'); ?></small>
                </span>
                <input type="checkbox" name="js_detection" value="1" <?php checked($settings['js_detection']); ?> data-risk="green" />
            </label>
        </div>
        
        <h3 style="margin-top: 30px;"><?php esc_html_e('Parametri di Ottimizzazione', 'fp-performance-suite'); ?></h3>
        
        <div class="fp-ps-grid two">
            <div>
                <label for="min_width">
                    <strong><?php esc_html_e('Larghezza minima (px)', 'fp-performance-suite'); ?></strong>
                </label>
                <input type="number" name="min_width" id="min_width" value="<?php echo esc_attr($settings['min_width']); ?>" min="100" max="2000" step="50" class="regular-text" />
                <p class="description"><?php esc_html_e('Larghezza minima immagine da considerare per l\'ottimizzazione', 'fp-performance-suite'); ?></p>
            </div>
            
            <div>
                <label for="min_height">
                    <strong><?php esc_html_e('Altezza minima (px)', 'fp-performance-suite'); ?></strong>
                </label>
                <input type="number" name="min_height" id="min_height" value="<?php echo esc_attr($settings['min_height']); ?>" min="100" max="2000" step="50" class="regular-text" />
                <p class="description"><?php esc_html_e('Altezza minima immagine da considerare per l\'ottimizzazione', 'fp-performance-suite'); ?></p>
            </div>
            
            <div style="grid-column: 1 / -1;">
                <label for="quality">
                    <strong><?php esc_html_e('Qualità immagini (%)', 'fp-performance-suite'); ?></strong>
                </label>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <input type="range" name="quality" id="quality" value="<?php echo esc_attr($settings['quality']); ?>" min="60" max="100" step="5" style="flex: 1;" />
                    <span id="quality-value" style="min-width: 50px; font-weight: 600; font-size: 18px; color: #2271b1;"><?php echo esc_html($settings['quality']); ?>%</span>
                </div>
                <p class="description"><?php esc_html_e('Qualità per le immagini ottimizzate generate (più alto = migliore qualità, file più grandi)', 'fp-performance-suite'); ?></p>
            </div>
        </div>
        
        <p style="margin-top: 30px;">
            <button type="submit" name="save_settings" class="button button-primary button-large">
                <?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?>
            </button>
            <button type="submit" name="reset_settings" class="button button-secondary" style="margin-left: 10px;">
                <?php esc_html_e('Ripristina Default', 'fp-performance-suite'); ?>
            </button>
        </p>
    </form>
</section>

<!-- Performance Impact -->
<section class="fp-ps-card">
    <h2><?php esc_html_e('📊 Impatto sulle Performance', 'fp-performance-suite'); ?></h2>
    <p class="description"><?php esc_html_e('Benefici dell\'ottimizzazione responsive delle immagini.', 'fp-performance-suite'); ?></p>
    
    <div class="fp-ps-grid three">
        <div style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">📊</div>
            <h3 style="color: white; margin: 0 0 10px 0;"><?php esc_html_e('Lighthouse Score', 'fp-performance-suite'); ?></h3>
            <p style="margin: 0; opacity: 0.95; font-size: 14px;"><?php esc_html_e('Migliora l\'audit "Properly size images" servendo immagini delle dimensioni appropriate.', 'fp-performance-suite'); ?></p>
        </div>
        
        <div style="padding: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 8px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">⚡</div>
            <h3 style="color: white; margin: 0 0 10px 0;"><?php esc_html_e('LCP Migliorato', 'fp-performance-suite'); ?></h3>
            <p style="margin: 0; opacity: 0.95; font-size: 14px;"><?php esc_html_e('Riduce Largest Contentful Paint servendo immagini più piccole quando appropriato.', 'fp-performance-suite'); ?></p>
        </div>
        
        <div style="padding: 20px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border-radius: 8px; text-align: center;">
            <div style="font-size: 48px; margin-bottom: 10px;">💾</div>
            <h3 style="color: white; margin: 0 0 10px 0;"><?php esc_html_e('Risparmio Banda', 'fp-performance-suite'); ?></h3>
            <p style="margin: 0; opacity: 0.95; font-size: 14px;"><?php esc_html_e('Riduce l\'utilizzo della banda servendo immagini dimensionate per il contesto di visualizzazione.', 'fp-performance-suite'); ?></p>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="fp-ps-card">
    <h2><?php esc_html_e('🔍 Come Funziona', 'fp-performance-suite'); ?></h2>
    <p class="description"><?php esc_html_e('Processo di ottimizzazione automatica delle immagini responsive.', 'fp-performance-suite'); ?></p>
    
    <div class="fp-ps-grid two" style="gap: 20px;">
        <div style="display: flex; align-items: start; gap: 15px; padding: 20px; background: #f0f9ff; border-radius: 8px;">
            <div style="width: 40px; height: 40px; background: #0ea5e9; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0;">1</div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 8px 0; color: #0c4a6e;"><?php esc_html_e('Rilevamento', 'fp-performance-suite'); ?></h4>
                <p style="margin: 0; color: #475569; font-size: 14px;"><?php esc_html_e('Rileva le dimensioni di visualizzazione delle immagini tramite analisi CSS e misurazione JavaScript.', 'fp-performance-suite'); ?></p>
            </div>
        </div>
        
        <div style="display: flex; align-items: start; gap: 15px; padding: 20px; background: #fef3c7; border-radius: 8px;">
            <div style="width: 40px; height: 40px; background: #f59e0b; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0;">2</div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 8px 0; color: #92400e;"><?php esc_html_e('Analisi', 'fp-performance-suite'); ?></h4>
                <p style="margin: 0; color: #78350f; font-size: 14px;"><?php esc_html_e('Confronta le dimensioni di visualizzazione con le dimensioni originali per determinare se è necessaria l\'ottimizzazione.', 'fp-performance-suite'); ?></p>
            </div>
        </div>
        
        <div style="display: flex; align-items: start; gap: 15px; padding: 20px; background: #f0fdf4; border-radius: 8px;">
            <div style="width: 40px; height: 40px; background: #22c55e; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0;">3</div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 8px 0; color: #166534;"><?php esc_html_e('Ottimizzazione', 'fp-performance-suite'); ?></h4>
                <p style="margin: 0; color: #15803d; font-size: 14px;"><?php esc_html_e('Genera o seleziona la dimensione dell\'immagine più appropriata per il contesto di visualizzazione.', 'fp-performance-suite'); ?></p>
            </div>
        </div>
        
        <div style="display: flex; align-items: start; gap: 15px; padding: 20px; background: #fdf2f8; border-radius: 8px;">
            <div style="width: 40px; height: 40px; background: #ec4899; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0;">4</div>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 8px 0; color: #be185d;"><?php esc_html_e('Consegna', 'fp-performance-suite'); ?></h4>
                <p style="margin: 0; color: #9f1239; font-size: 14px;"><?php esc_html_e('Serve l\'immagine ottimizzata, riducendo la banda utilizzata e migliorando le performance.', 'fp-performance-suite'); ?></p>
            </div>
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
