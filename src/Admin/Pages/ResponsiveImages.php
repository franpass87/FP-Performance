<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Admin\Pages\AbstractPage;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\ResponsiveImageOptimizer;
use FP\PerfSuite\Admin\Components\StatusIndicator;

use function wp_verify_nonce;
use function wp_redirect;
use function admin_url;
use function add_query_arg;
use function esc_html;
use function esc_html_e;
use function esc_attr;
use function checked;
use function wp_nonce_field;
use function current_user_can;

/**
 * Responsive Images Configuration Page
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ResponsiveImages extends AbstractPage
{
    private ResponsiveImageOptimizer $optimizer;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->optimizer = new ResponsiveImageOptimizer();
    }

    public function render(): void
    {
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fp_ps_responsive_images_nonce'])) {
            $this->handleSave();
        }

        parent::render();
    }

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        if (!wp_verify_nonce($_POST['fp_ps_responsive_images_nonce'] ?? '', 'fp_ps_responsive_images')) {
            wp_die(__('Security check failed.', 'fp-performance-suite'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions.', 'fp-performance-suite'));
        }

        $settings = [];

        // Handle enabled setting
        $settings['enabled'] = !empty($_POST['enabled']);

        // Handle generate_sizes setting
        $settings['generate_sizes'] = !empty($_POST['generate_sizes']);

        // Handle js_detection setting
        $settings['js_detection'] = !empty($_POST['js_detection']);

        // Handle min_width setting
        $minWidth = (int) ($_POST['min_width'] ?? 300);
        $settings['min_width'] = max(100, min(2000, $minWidth));

        // Handle min_height setting
        $minHeight = (int) ($_POST['min_height'] ?? 300);
        $settings['min_height'] = max(100, min(2000, $minHeight));

        // Handle quality setting
        $quality = (int) ($_POST['quality'] ?? 85);
        $settings['quality'] = max(60, min(100, $quality));

        // Check if reset was requested
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

        // Update settings
        $result = $this->optimizer->updateSettings($settings);

        if ($result) {
            $message = !empty($_POST['reset_settings']) 
                ? __('Settings reset to defaults successfully.', 'fp-performance-suite')
                : __('Settings saved successfully.', 'fp-performance-suite');
            
            $redirectUrl = add_query_arg([
                'page' => 'fp-performance-suite-responsive-images',
                'message' => 'success',
                'message_text' => urlencode($message)
            ], admin_url('admin.php'));
        } else {
            $redirectUrl = add_query_arg([
                'page' => 'fp-performance-suite-responsive-images',
                'message' => 'error',
                'message_text' => urlencode(__('Failed to save settings.', 'fp-performance-suite'))
            ], admin_url('admin.php'));
        }

        wp_redirect($redirectUrl);
        exit;
    }

    /**
     * Get page slug (implementazione metodo astratto)
     */
    public function slug(): string
    {
        return 'fp-performance-suite-responsive-images';
    }

    /**
     * Get page title (implementazione metodo astratto)
     */
    public function title(): string
    {
        return __('Responsive Images', 'fp-performance-suite');
    }

    /**
     * Get view path (implementazione metodo astratto)
     */
    public function view(): string
    {
        return FP_PERF_SUITE_DIR . '/views/admin-page.php';
    }

    /**
     * Get data for the view
     */
    protected function data(): array
    {
        return [
            'title' => $this->title(),
            'breadcrumbs' => [
                __('Performance', 'fp-performance-suite'),
                __('Responsive Images', 'fp-performance-suite'),
            ],
        ];
    }

    /**
     * Get page content (implementazione metodo astratto)
     */
    protected function content(): string
    {
        $status = $this->optimizer->status();
        $settings = $this->optimizer->getSettings();

        ob_start();
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
        return ob_get_clean();
    }

    /**
     * Backward compatibility - alias per slug()
     */
    public function getSlug(): string
    {
        return $this->slug();
    }

    /**
     * Backward compatibility - alias per title()
     */
    public function getTitle(): string
    {
        return $this->title();
    }
}
