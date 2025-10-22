<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Compression\CompressionManager;

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

        // Check for success/error messages
        $message = '';
        if (isset($_GET['updated']) && $_GET['updated'] === '1') {
            $message = __('Compression settings saved.', 'fp-performance-suite');
        }
        if (isset($_GET['error']) && $_GET['error'] === '1') {
            $message = __('Error saving compression settings.', 'fp-performance-suite');
        }

        ob_start();
        ?>
        
        <!-- Pannello Introduttivo -->
        <div class="fp-ps-page-intro" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin: 0 0 15px 0; color: white; font-size: 28px;">
                🗜️ Compressione
            </h2>
            <p style="font-size: 18px; line-height: 1.6; margin-bottom: 25px; opacity: 0.95;">
                <?php esc_html_e('La compressione riduce drasticamente la dimensione dei file trasferiti, accelerando il caricamento del sito.', 'fp-performance-suite'); ?>
            </p>
            
            <div class="fp-ps-grid three" style="gap: 20px;">
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">🗜️</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Gzip', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Compressione standard supportata da tutti i browser. Riduce i file del 60-80%.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">⚡</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Brotli', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Compressione moderna ancora più efficace. Riduzione file fino al 85%.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; backdrop-filter: blur(10px);">
                    <div style="font-size: 32px; margin-bottom: 10px;">🚀</div>
                    <strong style="display: block; margin-bottom: 8px; font-size: 16px;"><?php esc_html_e('Performance', 'fp-performance-suite'); ?></strong>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9; line-height: 1.5;">
                        <?php esc_html_e('Caricamento pagine 3x più veloce e risparmio banda fino al 70%.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </div>

        <?php if ($message) : ?>
            <div class="notice notice-<?php echo isset($_GET['error']) ? 'error' : 'success'; ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Stato Compressione -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Stato Compressione', 'fp-performance-suite'); ?></h2>
            <p class="description"><?php esc_html_e('Verifica il supporto del server per i diversi algoritmi di compressione.', 'fp-performance-suite'); ?></p>
            
            <div class="fp-ps-grid three" style="margin: 20px 0;">
                <div class="fp-ps-stat-box" style="<?php echo $status['gzip_supported'] ? 'border-left: 4px solid #46b450;' : 'border-left: 4px solid #dc3232;'; ?>">
                    <div class="stat-label" style="margin-bottom: 10px;"><?php esc_html_e('Supporto Gzip', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 24px; color: <?php echo $status['gzip_supported'] ? '#46b450' : '#dc3232'; ?>;">
                        <?php echo $status['gzip_supported'] ? '✅ ' . __('Supportato', 'fp-performance-suite') : '❌ ' . __('Non Supportato', 'fp-performance-suite'); ?>
                    </div>
                </div>

                <div class="fp-ps-stat-box" style="<?php echo $status['brotli_supported'] ? 'border-left: 4px solid #46b450;' : 'border-left: 4px solid #f0b429;'; ?>">
                    <div class="stat-label" style="margin-bottom: 10px;"><?php esc_html_e('Supporto Brotli', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 24px; color: <?php echo $status['brotli_supported'] ? '#46b450' : '#f0b429'; ?>;">
                        <?php echo $status['brotli_supported'] ? '✅ ' . __('Supportato', 'fp-performance-suite') : '⚠️ ' . __('Non Supportato', 'fp-performance-suite'); ?>
                    </div>
                </div>

                <div class="fp-ps-stat-box" style="<?php echo $status['enabled'] ? 'border-left: 4px solid #46b450;' : 'border-left: 4px solid #72aee6;'; ?>">
                    <div class="stat-label" style="margin-bottom: 10px;"><?php esc_html_e('Stato Attuale', 'fp-performance-suite'); ?></div>
                    <div class="stat-value" style="font-size: 24px; color: <?php echo $status['enabled'] ? '#46b450' : '#72aee6'; ?>;">
                        <?php echo $status['enabled'] ? '✅ ' . __('Attiva', 'fp-performance-suite') : 'ℹ️ ' . __('Disattiva', 'fp-performance-suite'); ?>
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
                            <strong><?php esc_html_e('Abilita Compressione', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Abilita la compressione Gzip/Brotli per ridurre la dimensione dei file trasferiti', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="compression[enabled]" value="1" <?php checked($status['enabled'], true); ?> />
                    </label>
                </div>

                <div style="margin: 20px 0;">
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Abilita Compressione Gzip/Deflate', 'fp-performance-suite'); ?></strong>
                            <small><?php esc_html_e('Compressione standard supportata da tutti i browser e server', 'fp-performance-suite'); ?></small>
                        </span>
                        <input type="checkbox" name="compression[deflate_enabled]" value="1" <?php checked($status['deflate_enabled'], true); ?> />
                    </label>
                </div>

                <?php if ($status['brotli_supported']) : ?>
                    <div style="margin: 20px 0;">
                        <label class="fp-ps-toggle">
                            <span class="info">
                                <strong><?php esc_html_e('Abilita Compressione Brotli', 'fp-performance-suite'); ?></strong>
                                <small><?php esc_html_e('Brotli offre una compressione migliore rispetto a Gzip (15-20% in più)', 'fp-performance-suite'); ?></small>
                            </span>
                            <input type="checkbox" name="compression[brotli_enabled]" value="1" <?php checked($status['brotli_enabled'], true); ?> />
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
                
                <div class="fp-ps-grid two" style="margin: 20px 0;">
                    <?php foreach ($info as $key => $value) : ?>
                        <div style="padding: 15px; background: #f8f9fa; border-radius: 4px; border-left: 3px solid #3b82f6;">
                            <strong style="display: block; margin-bottom: 5px; color: #3b82f6;">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $key))); ?>
                            </strong>
                            <span style="color: #64748b;">
                                <?php echo esc_html($value); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Info Box Benefici -->
        <section class="fp-ps-card" style="background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 100%); border: none;">
            <h2 style="color: #1e40af;"><?php esc_html_e('💡 Vantaggi della Compressione', 'fp-performance-suite'); ?></h2>
            
            <div class="fp-ps-grid two" style="gap: 20px; margin: 20px 0;">
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #1e40af;">📊 Risparmio Banda</h3>
                    <p style="margin: 0; line-height: 1.6;">
                        <?php esc_html_e('Con Gzip puoi ridurre i file HTML, CSS e JavaScript del 60-80%. Con Brotli fino all\'85%. Questo significa meno banda consumata e costi hosting ridotti.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #1e40af;">🚀 Velocità</h3>
                    <p style="margin: 0; line-height: 1.6;">
                        <?php esc_html_e('Pagine più leggere = caricamento più veloce. Gli utenti vedranno il sito caricarsi 2-3 volte più rapidamente, migliorando l\'esperienza e il punteggio SEO.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #1e40af;">📱 Mobile</h3>
                    <p style="margin: 0; line-height: 1.6;">
                        <?php esc_html_e('Su connessioni mobili lente, la compressione è essenziale. Utenti con 3G/4G beneficeranno enormemente della riduzione dei dati trasferiti.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: #1e40af;">💰 Costi</h3>
                    <p style="margin: 0; line-height: 1.6;">
                        <?php esc_html_e('Riducendo la banda utilizzata, puoi risparmiare sui costi di hosting e CDN. Alcuni provider fatturano in base al traffico trasferito.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
        </section>

        <?php
        return (string) ob_get_clean();
    }

    /**
     * Handle form submission
     */
    public function handleSave(): void
    {
        // Verify nonce
        if (!isset($_POST['fp_ps_nonce']) || !wp_verify_nonce($_POST['fp_ps_nonce'], 'fp_ps_save_compression')) {
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&error=1'));
            exit;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&error=1'));
            exit;
        }

        try {
            // Get compression service
            $compression = $this->container->get(CompressionManager::class);
            
            // Save compression settings
            $enabled = !empty($_POST['compression']['enabled']);
            $deflate_enabled = !empty($_POST['compression']['deflate_enabled']);
            $brotli_enabled = !empty($_POST['compression']['brotli_enabled']);
            $brotli_quality = isset($_POST['compression']['brotli_quality']) 
                ? max(1, min(11, (int)$_POST['compression']['brotli_quality'])) 
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
            
            // Redirect with success
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&updated=1'));
            exit;
            
        } catch (\Exception $e) {
            error_log('[FP Performance Suite] Compression save error: ' . $e->getMessage());
            wp_redirect(admin_url('admin.php?page=fp-performance-suite-compression&error=1'));
            exit;
        }
    }
}
