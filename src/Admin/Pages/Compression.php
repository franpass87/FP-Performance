<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Admin\Components\InfoBox;
use FP\PerfSuite\Services\Compression\CompressionManager;

use function __;
use function wp_verify_nonce;
use function wp_unslash;
use function sanitize_key;
use function sanitize_text_field;
use function esc_html;
use function esc_attr;

/**
 * Compression Admin Page
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class Compression extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-compression';
    }

    public function title(): string
    {
        return __('Compression', 'fp-performance-suite');
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
                __('FP Performance', 'fp-performance-suite'),
                __('Compression', 'fp-performance-suite'),
            ],
        ];
    }

    protected function content(): string
    {
        // Get compression service
        $compression = $this->container->get(CompressionManager::class);
        $status = $compression->status();
        $info = $compression->getInfo();

        // Handle form submission
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['fp_ps_nonce'])) {
            $message = $this->handleSave();
        } else {
            $message = '';
        }

        // Check for messages from URL (from admin_post handlers)
        if (isset($_GET['message'])) {
            $message = sanitize_text_field(wp_unslash($_GET['message']));
        }
        
        // Check for legacy success/error messages from URL
        if (isset($_GET['updated']) && sanitize_key(wp_unslash($_GET['updated'] ?? '')) === '1') {
            $message = __('Compression settings saved.', 'fp-performance-suite');
        }
        if (isset($_GET['error']) && sanitize_key(wp_unslash($_GET['error'] ?? '')) === '1') {
            $message = __('Error saving compression settings.', 'fp-performance-suite');
        }

        ob_start();
        ?>
        
        <?php
        // Intro Box con PageIntro Component
        echo PageIntro::render(
            '🗜️',
            __('Compression', 'fp-performance-suite'),
            __('Riduci le dimensioni dei file con compressione GZIP e Brotli. Minifica HTML, CSS e JavaScript per velocizzare il caricamento.', 'fp-performance-suite')
        );
        ?>
        
        <?php
        // Mostra legenda rischi
        echo RiskLegend::renderLegend();
        ?>
        
        <?php if ($message) : ?>
            <?php 
            $is_error = strpos($message, 'Error') === 0 || strpos($message, 'Errore') === 0;
            $notice_class = $is_error ? 'notice-error' : 'notice-success';
            ?>
            <div class="notice <?php echo esc_attr($notice_class); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Stato Compressione -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Stato Compressione', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Verifica il supporto del server per i diversi algoritmi di compressione.', 'fp-performance-suite'); ?></p>
            
            <div class="fp-ps-grid three" style="margin: 20px 0;">
                <div class="fp-ps-stat-box" style="<?php echo esc_attr($status['gzip_supported'] ? 'border-left: 4px solid #46b450;' : 'border-left: 4px solid #dc3232;'); ?>">
                    <div class="stat-label" style="margin-bottom: 10px;"><?php esc_html_e('Supporto Gzip', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 24px; color: <?php echo esc_attr($status['gzip_supported'] ? '#46b450' : '#dc3232'); ?>;">
                        <?php echo esc_html($status['gzip_supported'] ? '✅ ' . __('Supportato', 'fp-performance-suite') : '❌ ' . __('Non Supportato', 'fp-performance-suite')); ?>
                    </div>
                </div>

                <div class="fp-ps-stat-box" style="<?php echo esc_attr($status['brotli_supported'] ? 'border-left: 4px solid #46b450;' : 'border-left: 4px solid #f0b429;'); ?>">
                    <div class="stat-label" style="margin-bottom: 10px;"><?php esc_html_e('Supporto Brotli', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 24px; color: <?php echo esc_attr($status['brotli_supported'] ? '#46b450' : '#f0b429'); ?>;">
                        <?php echo esc_html($status['brotli_supported'] ? '✅ ' . __('Supportato', 'fp-performance-suite') : '⚠️ ' . __('Non Supportato', 'fp-performance-suite')); ?>
                    </div>
                </div>

                <div class="fp-ps-stat-box" style="<?php echo esc_attr($status['enabled'] ? 'border-left: 4px solid #46b450;' : 'border-left: 4px solid #72aee6;'); ?>">
                    <div class="stat-label" style="margin-bottom: 10px;"><?php esc_html_e('Stato Attuale', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 24px; color: <?php echo esc_attr($status['enabled'] ? '#46b450' : '#72aee6'); ?>;">
                        <?php echo esc_html($status['enabled'] ? '✅ ' . __('Attiva', 'fp-performance-suite') : 'ℹ️ ' . __('Disattiva', 'fp-performance-suite')); ?>
                    </div>
                </div>
            </div>

            <?php if (!$status['gzip_supported']) : ?>
                <div class="notice notice-error inline" style="margin-top: 20px;">
                    <p>
                        <strong>⚠️ <?php esc_html_e('Gzip non supportato', 'fp-performance-suite'); ?></strong><br>
                        <?php esc_html_e('Il server non supporta la compressione Gzip. Contatta il tuo hosting provider per abilitarla.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!$status['brotli_supported'] && $status['gzip_supported']) : ?>
                <div class="notice notice-info inline" style="margin-top: 20px;">
                    <p>
                        <strong>💡 <?php esc_html_e('Brotli consigliato', 'fp-performance-suite'); ?></strong><br>
                        <?php esc_html_e('Il tuo server supporta Gzip ma non Brotli. Considera di abilitare Brotli per una compressione ancora migliore (15-20% in più).', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>
        </section>

        <!-- Impostazioni Compressione -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Impostazioni Compressione', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Configura quali algoritmi di compressione utilizzare.', 'fp-performance-suite'); ?></p>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('fp_ps_save_compression', 'fp_ps_nonce'); ?>
                <input type="hidden" name="action" value="fp_ps_save_compression">

                <div style="margin: 20px 0;">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong>
                                <?php esc_html_e('Abilita Compressione', 'fp-performance-suite'); ?>
                                <?php echo RiskMatrix::renderIndicator('gzip_enabled'); ?>
                            </strong>
                            <small><?php esc_html_e('Abilita la compressione Gzip/Brotli per ridurre la dimensione dei file trasferiti', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="compression[enabled]" value="1" <?php checked($status['enabled'], true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('gzip_enabled')); ?>" />
                    </label>
                </div>

                <div style="margin: 20px 0;">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong>
                                <?php esc_html_e('Abilita Compressione Gzip/Deflate', 'fp-performance-suite'); ?>
                                <?php echo RiskMatrix::renderIndicator('gzip_enabled'); ?>
                            </strong>
                            <small><?php esc_html_e('Compressione standard supportata da tutti i browser e server', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="compression[deflate_enabled]" value="1" <?php checked($status['deflate_enabled'], true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('gzip_enabled')); ?>" />
                    </label>
                </div>

                <?php if ($status['brotli_supported']) : ?>
                    <div style="margin: 20px 0;">
                        <label class="fp-ps-toggle">
                            <span class="info">
                                <strong>
                                    <?php esc_html_e('Abilita Compressione Brotli', 'fp-performance-suite'); ?>
                                    <?php echo RiskMatrix::renderIndicator('brotli_enabled'); ?>
                                </strong>
                                <small><?php esc_html_e('Brotli offre una compressione migliore rispetto a Gzip (15-20% in più)', 'fp-performance-suite'); ?></small>
                            </span>
                            <input type="checkbox" name="compression[brotli_enabled]" value="1" <?php checked($status['brotli_enabled'], true); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('brotli_enabled')); ?>" />
                        </label>
                    </div>

                    <div style="margin: 20px 0 20px 40px;" id="brotli-quality-container">
                        <label for="brotli_quality" style="display: block; margin-bottom: 10px;">
                            <strong><?php esc_html_e('Qualità Compressione Brotli', 'fp-performance-suite'); ?></strong>
                        </label>
                        <input type="range" name="compression[brotli_quality]" id="brotli_quality" min="1" max="11" value="<?php echo esc_attr($status['brotli_quality']); ?>" style="width: 300px; vertical-align: middle;" />
                        <span id="brotli_quality_value" style="margin-left: 15px; font-weight: bold; font-size: 16px;"><?php echo esc_html($status['brotli_quality']); ?></span>
                        <p class="description" style="margin-top: 8px;">
                            <?php esc_html_e('1 = Veloce ma compressione minore | 5 = Bilanciato (consigliato) | 11 = Massima compressione ma più lento', 'fp-performance-suite'); ?>
                        </p>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            var qualityInput = document.getElementById('brotli_quality');
                            var qualityValue = document.getElementById('brotli_quality_value');
                            
                            qualityInput.addEventListener('input', function() {
                                qualityValue.textContent = this.value;
                            });
                        });
                    </script>
                <?php endif; ?>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                    <button type="submit" class="button button-primary button-large">
                        <?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>

        <!-- Informazioni Server -->
        <?php if (!empty($info)) : ?>
            <section class="fp-ps-card">
                <h2><?php esc_html_e('Informazioni Server', 'fp-performance-suite'); ?></h2>
                <p class="description"><?php esc_html_e('Dettagli tecnici sulla configurazione del server.', 'fp-performance-suite'); ?></p>
                
                <div style="margin: 20px 0;">
                    <?php foreach ($info as $key => $value) : ?>
                        <div style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #3b82f6;">
                            <h3 style="margin: 0 0 15px 0; color: #3b82f6; font-size: 18px;">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>
                            </h3>
                            
                            <?php if (is_array($value)) : ?>
                                <div style="display: grid; gap: 10px;">
                                    <?php foreach ($value as $subKey => $subValue) : ?>
                                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
                                            <span style="font-weight: 500; color: #374151;">
                                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $subKey))); ?>
                                            </span>
                                            <span style="color: #64748b; font-family: monospace;">
                                                <?php if (is_bool($subValue)) : ?>
                                                    <span style="color: <?php echo $subValue ? '#10b981' : '#ef4444'; ?>; font-weight: bold;">
                                                        <?php echo $subValue ? '✅ Sì' : '❌ No'; ?>
                                                    </span>
                                                <?php elseif (is_numeric($subValue)) : ?>
                                                    <?php echo esc_html($subValue); ?>
                                                <?php else : ?>
                                                    <?php echo esc_html($subValue ?: 'Non impostato'); ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else : ?>
                                <div style="padding: 8px 12px; background: white; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    <span style="color: #64748b; font-family: monospace;">
                                        <?php echo esc_html($value); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php
        // Info Box Benefici con InfoBox Component
        echo InfoBox::renderWithGrid(
            '💡 ' . __('Vantaggi della Compressione', 'fp-performance-suite'),
            [
                [
                    'title' => '📊 ' . __('Risparmio Banda', 'fp-performance-suite'),
                    'content' => __('Con Gzip puoi ridurre i file HTML, CSS e JavaScript del 60-80%. Con Brotli fino all\'85%. Questo significa meno banda consumata e costi hosting ridotti.', 'fp-performance-suite')
                ],
                [
                    'title' => '🚀 ' . __('Velocità', 'fp-performance-suite'),
                    'content' => __('Pagine più leggere = caricamento più veloce. Gli utenti vedranno il sito caricarsi 2-3 volte più rapidamente, migliorando l\'esperienza e il punteggio SEO.', 'fp-performance-suite')
                ],
                [
                    'title' => '📱 ' . __('Mobile', 'fp-performance-suite'),
                    'content' => __('Su connessioni mobili lente, la compressione è essenziale. Utenti con 3G/4G beneficeranno enormemente della riduzione dei dati trasferiti.', 'fp-performance-suite')
                ],
                [
                    'title' => '💰 ' . __('Costi', 'fp-performance-suite'),
                    'content' => __('Riducendo la banda utilizzata, puoi risparmiare sui costi di hosting e CDN. Alcuni provider fatturano in base al traffico trasferito.', 'fp-performance-suite')
                ]
            ],
            InfoBox::TYPE_INFO,
            2 // 2 colonne
        );
        ?>

        <?php
        return (string) ob_get_clean();
    }

    /**
     * Handle form submission
     */
    public function handleSave(): string
    {
        // Verify nonce
        if (!isset($_POST['fp_ps_nonce']) || !wp_verify_nonce(wp_unslash($_POST['fp_ps_nonce']), 'fp_ps_save_compression')) {
            return __('Security error: invalid nonce. Please reload the page and try again.', 'fp-performance-suite');
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            return __('Permission denied. You do not have sufficient permissions to save these settings.', 'fp-performance-suite');
        }

        try {
            // Get compression service
            $compression = $this->container->get(CompressionManager::class);
            
            // Save compression settings
            $compressionPost = $_POST['compression'] ?? [];
            $enabled = !empty($compressionPost['enabled']);
            $deflate_enabled = !empty($compressionPost['deflate_enabled']);
            $brotli_enabled = !empty($compressionPost['brotli_enabled']);
            $brotli_quality = isset($compressionPost['brotli_quality']) 
                ? max(1, min(11, (int)$compressionPost['brotli_quality'])) 
                : 5;
            
            // Update settings
            update_option('fp_ps_compression_enabled', $enabled);
            update_option('fp_ps_compression_deflate_enabled', $deflate_enabled);
            update_option('fp_ps_compression_brotli_enabled', $brotli_enabled);
            update_option('fp_ps_compression_brotli_quality', $brotli_quality);
            
            // Apply settings
            if ($enabled) {
                $compression->enable();
            } else {
                $compression->disable();
            }
            
            return __('Compression settings saved successfully!', 'fp-performance-suite');
            
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'Compression save error');
            return sprintf(
                __('Error saving compression settings: %s. Please try again.', 'fp-performance-suite'),
                $e->getMessage()
            );
        }
    }
}
