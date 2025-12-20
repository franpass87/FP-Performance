<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Admin\Components\PageIntro; // BUGFIX: Mancava import
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Services\Compatibility\SalientWPBakeryOptimizer;
use FP\PerfSuite\Admin\ThemeHints;

/**
 * Theme Optimization Page
 * 
 * Pagina dedicata alle ottimizzazioni specifiche per tema/page builder
 * Con focus su Salient + WPBakery
 * 
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 * @since 1.7.0
 */
class ThemeOptimization extends AbstractPage
{
    private ThemeDetector $detector;
    private ?SalientWPBakeryOptimizer $salientOptimizer = null;
    private ThemeHints $hints;

    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
        $this->detector = $container->get(ThemeDetector::class);
        $this->hints = new ThemeHints($this->detector);
        
        // Carica optimizer solo se Salient/WPBakery sono attivi
        if ($this->detector->isTheme('salient') && $this->detector->hasPageBuilder('wpbakery')) {
            $this->salientOptimizer = $container->get(SalientWPBakeryOptimizer::class);
        }
    }

    public function slug(): string
    {
        return 'fp-performance-suite-theme-optimization';
    }

    public function title(): string
    {
        return __('🎨 Ottimizzazioni Tema', 'fp-performance-suite');
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
                __('Compatibility', 'fp-performance-suite'), 
                __('Theme Optimization', 'fp-performance-suite')
            ],
        ];
    }

    protected function content(): string
    {
        $message = '';
        
        // Handle form submission
        if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && isset($_POST['fp_ps_theme_opt_nonce'])) {
            if (wp_verify_nonce(wp_unslash($_POST['fp_ps_theme_opt_nonce']), 'fp-ps-theme-optimization')) {
                $message = $this->handleFormSubmission();
            }
        }

        ob_start();
        ?>
        
        <?php
        // Intro Box con PageIntro Component
        echo PageIntro::render(
            '🎨',
            __('Theme Optimization', 'fp-performance-suite'),
            __('Ottimizzazioni specifiche per il tuo tema e page builder. FP Performance rileva automaticamente tema e builder per applicare le migliori ottimizzazioni.', 'fp-performance-suite')
        );
        ?>
        
        <div class="fp-ps-theme-optimization-page">
            <?php if ($message): ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Theme Detection Card -->
            <div class="fp-ps-card mb-4">
                <div class="fp-ps-card-header">
                    <h2>🔍 Tema e Page Builder Rilevati</h2>
                </div>
                <div class="fp-ps-card-body">
                    <?php $this->renderThemeDetection(); ?>
                </div>
            </div>

            <?php if ($this->salientOptimizer): ?>
                <!-- Salient + WPBakery Optimization -->
                <div class="fp-ps-card mb-4">
                    <div class="fp-ps-card-header">
                        <h2>⚡ Ottimizzazioni Salient + WPBakery</h2>
                    </div>
                    <div class="fp-ps-card-body">
                        <?php $this->renderSalientOptimizations(); ?>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="fp-ps-card mb-4">
                    <div class="fp-ps-card-header">
                        <h2>📊 Statistiche e Info</h2>
                    </div>
                    <div class="fp-ps-card-body">
                        <?php $this->renderStats(); ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Generic Theme Recommendations -->
                <div class="fp-ps-card mb-4">
                    <div class="fp-ps-card-header">
                        <h2>💡 Raccomandazioni Generali</h2>
                    </div>
                    <div class="fp-ps-card-body">
                        <?php $this->renderGenericRecommendations(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Documentation Link -->
            <div class="fp-ps-card">
                <div class="fp-ps-card-body">
                    <p style="margin: 0;">
                        📚 <strong>Documentazione completa:</strong> 
                        <a href="<?php echo esc_url(FP_PERF_SUITE_DIR . '/docs/01-user-guides/CONFIGURAZIONE_SALIENT_WPBAKERY.md'); ?>" target="_blank">
                            Guida Salient + WPBakery
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <?php echo ThemeHints::renderTooltipScript(); ?>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderizza la sezione di rilevamento tema
     */
    private function renderThemeDetection(): void
    {
        $themeName = $this->detector->getThemeName();
        $themeSlug = $this->detector->getCurrentTheme();
        $builders = $this->detector->getActivePageBuilders();
        $features = $this->detector->getThemeFeatures();
        ?>
        <div class="fp-ps-theme-info">
            <table class="widefat">
                <tbody>
                    <tr>
                        <td style="width: 30%; font-weight: 600;">🎨 Tema Attivo:</td>
                        <td>
                            <code><?php echo esc_html($themeName); ?></code>
                            <span style="color: #666; margin-left: 10px;">(<?php echo esc_html($themeSlug); ?>)</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">🛠️ Page Builder:</td>
                        <td>
                            <?php if (empty($builders)): ?>
                                <span style="color: #999;">Nessun page builder rilevato</span>
                            <?php else: ?>
                                <?php foreach ($builders as $builder): ?>
                                    <span class="fp-ps-badge" style="background: #3b82f6; color: white; padding: 4px 10px; border-radius: 4px; margin-right: 8px; display: inline-block;">
                                        <?php echo esc_html(ucfirst($builder)); ?>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600;">✨ Caratteristiche:</td>
                        <td>
                            <?php if (empty($features)): ?>
                                <span style="color: #999;">Nessuna caratteristica speciale rilevata</span>
                            <?php else: ?>
                                <ul style="margin: 0; padding-left: 20px;">
                                    <?php if ($features['ajax_navigation'] ?? false): ?>
                                        <li>AJAX Navigation</li>
                                    <?php endif; ?>
                                    <?php if ($features['lazy_loading'] ?? false): ?>
                                        <li>Lazy Loading Nativo</li>
                                    <?php endif; ?>
                                    <?php if ($features['custom_fonts'] ?? false): ?>
                                        <li>Custom Fonts</li>
                                    <?php endif; ?>
                                    <?php if ($features['woocommerce'] ?? false): ?>
                                        <li>WooCommerce</li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Renderizza le ottimizzazioni Salient
     */
    private function renderSalientOptimizations(): void
    {
        $config = $this->salientOptimizer->getConfig();
        ?>
        <form method="post" action="">
            <?php wp_nonce_field('fp-ps-theme-optimization', 'fp_ps_theme_opt_nonce'); ?>
            
            <div class="fp-ps-notice info" style="margin-bottom: 20px;">
                <p>
                    <strong>🎯 Ottimizzazioni Specifiche per Salient + WPBakery</strong><br>
                    Queste impostazioni applicano fix e ottimizzazioni specifiche per massimizzare le performance 
                    del tema Salient con WPBakery Page Builder.
                </p>
            </div>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="salient_enabled">
                                Abilita Ottimizzazioni
                                <?php echo RiskMatrix::renderIndicator('salient_optimizer'); ?>
                            </label>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="salient_enabled" id="salient_enabled" value="1" 
                                    <?php checked($config['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('salient_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Abilita tutte le ottimizzazioni specifiche per Salient + WPBakery
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Ottimizza Script
                            <?php echo RiskMatrix::renderIndicator('salient_optimizer'); ?>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="optimize_scripts" value="1" 
                                    <?php checked($config['optimize_scripts']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('salient_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Sposta script non critici nel footer ed escludi script importanti dal defer
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Ottimizza Stili
                            <?php echo RiskMatrix::renderIndicator('salient_optimizer'); ?>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="optimize_styles" value="1" 
                                    <?php checked($config['optimize_styles']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('salient_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Ottimizza caricamento CSS e rimuovi stili non necessari
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Fix CLS (Layout Shift)
                            <?php echo RiskMatrix::renderIndicator('salient_optimizer'); ?>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="fix_cls" value="1" 
                                    <?php checked($config['fix_cls']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('salient_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Previene spostamenti di layout causati da slider e animazioni Salient
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Ottimizza Animazioni
                            <?php echo RiskMatrix::renderIndicator('salient_optimizer'); ?>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="optimize_animations" value="1" 
                                    <?php checked($config['optimize_animations']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('salient_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Usa Intersection Observer per animazioni solo quando visibili
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Ottimizza Parallax
                            <?php echo RiskMatrix::renderIndicator('salient_optimizer'); ?>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="optimize_parallax" value="1" 
                                    <?php checked($config['optimize_parallax']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('salient_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Disabilita parallax su connessioni lente (2G/3G)
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Precarica Asset Critici
                            <?php echo RiskMatrix::renderIndicator('salient_optimizer'); ?>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="preload_critical_assets" value="1" 
                                    <?php checked($config['preload_critical_assets']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('salient_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Precarica font icons e asset critici di Salient
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            Cache Contenuto Builder
                            <?php echo RiskMatrix::renderIndicator('wpbakery_optimizer'); ?>
                        </th>
                        <td>
                            <label class="fp-ps-toggle">
                                <input type="checkbox" name="cache_builder_content" value="1" 
                                    <?php checked($config['cache_builder_content']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('wpbakery_optimizer')); ?>">
                                <span class="fp-ps-toggle-slider"></span>
                            </label>
                            <p class="description">
                                Purge automatico cache quando si salva con WPBakery
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    💾 Salva Configurazione
                </button>
            </p>
        </form>
        <?php
    }

    /**
     * Renderizza statistiche
     */
    private function renderStats(): void
    {
        $stats = $this->salientOptimizer->getStats();
        ?>
        <div class="fp-ps-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="fp-ps-stat-card" style="background: #f0f9ff; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                <div style="font-size: 24px; margin-bottom: 8px;">🎨</div>
                <div style="font-size: 14px; color: #666; margin-bottom: 4px;">Tema</div>
                <div style="font-size: 18px; font-weight: 600;"><?php echo esc_html($stats['theme']); ?></div>
            </div>

            <div class="fp-ps-stat-card" style="background: #f0fdf4; padding: 20px; border-radius: 8px; border-left: 4px solid #22c55e;">
                <div style="font-size: 24px; margin-bottom: 8px;">🛠️</div>
                <div style="font-size: 14px; color: #666; margin-bottom: 4px;">Page Builder</div>
                <div style="font-size: 18px; font-weight: 600;"><?php echo esc_html($stats['builder']); ?></div>
            </div>

            <div class="fp-ps-stat-card" style="background: #fef3c7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <div style="font-size: 24px; margin-bottom: 8px;">⚡</div>
                <div style="font-size: 14px; color: #666; margin-bottom: 4px;">Script Critici Protetti</div>
                <div style="font-size: 18px; font-weight: 600;"><?php echo count($stats['critical_scripts']); ?></div>
            </div>

            <div class="fp-ps-stat-card" style="background: #fae8ff; padding: 20px; border-radius: 8px; border-left: 4px solid #a855f7;">
                <div style="font-size: 24px; margin-bottom: 8px;">🔤</div>
                <div style="font-size: 14px; color: #666; margin-bottom: 4px;">Font Precaricati</div>
                <div style="font-size: 18px; font-weight: 600;"><?php echo count($stats['critical_fonts']); ?></div>
            </div>
        </div>

        <div style="margin-top: 30px;">
            <h3>Script Critici Protetti</h3>
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #e5e7eb;">
                <?php foreach ($stats['critical_scripts'] as $script): ?>
                    <code style="display: inline-block; background: white; padding: 4px 8px; margin: 4px; border-radius: 4px; border: 1px solid #d1d5db;">
                        <?php echo esc_html($script); ?>
                    </code>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizza raccomandazioni generiche
     */
    private function renderGenericRecommendations(): void
    {
        ?>
        <div class="fp-ps-notice warning">
            <p>
                <strong>ℹ️ Tema Non Ottimizzato Automaticamente</strong><br>
                Il tuo tema (<strong><?php echo esc_html($this->detector->getThemeName()); ?></strong>) 
                non ha ottimizzazioni automatiche specifiche al momento.
            </p>
        </div>

        <h3>Raccomandazioni Generali:</h3>
        <ul style="list-style: disc; padding-left: 25px; line-height: 1.8;">
            <li>Abilita <strong>Object Cache</strong> se il tuo hosting lo supporta (Redis/Memcached)</li>
            <li>Usa <strong>Edge Cache</strong> (Cloudflare) per ridurre il carico sul server</li>
            <li>Abilita <strong>Lazy Loading</strong> per immagini e iframe</li>
            <li>Configura <strong>Critical CSS</strong> per migliorare il First Contentful Paint</li>
            <li>Monitora i <strong>Core Web Vitals</strong> per identificare problemi</li>
            <li>Ritarda script di <strong>terze parti</strong> (Analytics, Pixel, ecc.)</li>
        </ul>

        <p style="margin-top: 20px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-overview')); ?>" class="button button-primary">
                🎯 Vai alla Dashboard Principale
            </a>
        </p>
        <?php
    }

    /**
     * Gestisce il submit del form
     */
    private function handleFormSubmission(): string
    {
        if (!$this->salientOptimizer) {
            return __('Impossibile salvare: Salient/WPBakery non rilevati.', 'fp-performance-suite');
        }

        $config = [
            'enabled' => !empty($_POST['salient_enabled']),
            'optimize_scripts' => !empty($_POST['optimize_scripts']),
            'optimize_styles' => !empty($_POST['optimize_styles']),
            'fix_cls' => !empty($_POST['fix_cls']),
            'optimize_animations' => !empty($_POST['optimize_animations']),
            'optimize_parallax' => !empty($_POST['optimize_parallax']),
            'preload_critical_assets' => !empty($_POST['preload_critical_assets']),
            'cache_builder_content' => !empty($_POST['cache_builder_content']),
        ];

        $this->salientOptimizer->updateConfig($config);

        return __('✅ Configurazione Salient/WPBakery salvata con successo!', 'fp-performance-suite');
    }
}
