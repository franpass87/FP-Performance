<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\Components\PageIntro;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Pages\Settings\FormHandler;
use FP\PerfSuite\Admin\Pages\Settings\ImportExportHandler;
use FP\PerfSuite\Admin\Pages\Settings\ServiceTester;

use function __;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function esc_url;
use function get_option;
use function sanitize_key;
use function selected;
use function checked;
use function admin_url;
use function wp_json_encode;
use function wp_nonce_field;
use function wp_unslash;

/**
 * Settings Page - Configurazione Unificata
 * 
 * Unisce le funzionalità di Tools.php e Settings.php in un'unica interfaccia con tab:
 * - General: Impostazioni generali del plugin
 * - Import/Export: Importa/Esporta configurazioni
 * - Service Tester: Test dei servizi
 */
class Settings extends AbstractPage
{
    public function __construct(ServiceContainer $container)
    {
        parent::__construct($container);
    }

    public function slug(): string
    {
        return 'fp-performance-suite-settings';
    }

    public function title(): string
    {
        return __('Settings', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Settings', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        ob_start();
        
        $current_tab = sanitize_key(wp_unslash($_GET['tab'] ?? 'general'));
        $formHandler = new FormHandler($this->container);
        $importExportHandler = new ImportExportHandler($this->container);
        $serviceTester = new ServiceTester($this->container);
        
        // Gestisci form submissions
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $formHandler->handle();
            $importExportHandler->handle();
        }
        
        // Carica impostazioni
        $pluginOptions = get_option('fp_ps_plugin_options', []);
        $criticalCss = get_option('fp_ps_critical_css', '');
        
        ?>
        
        <div class="wrap">
            <?php
            echo PageIntro::render(
                '⚙️',
                __('Settings', 'fp-performance-suite'),
                __('Configurazione generale del plugin, import/export impostazioni e test servizi.', 'fp-performance-suite')
            );
            ?>
            
            <!-- Tabs Navigation -->
            <nav class="nav-tab-wrapper" style="margin-top: 20px;">
                <a href="?page=fp-performance-suite-settings&tab=general" 
                   class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('General', 'fp-performance-suite'); ?>
                </a>
                <a href="?page=fp-performance-suite-settings&tab=import-export" 
                   class="nav-tab <?php echo $current_tab === 'import-export' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Import/Export', 'fp-performance-suite'); ?>
                </a>
                <a href="?page=fp-performance-suite-settings&tab=service-tester" 
                   class="nav-tab <?php echo $current_tab === 'service-tester' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e('Service Tester', 'fp-performance-suite'); ?>
                </a>
            </nav>
            
            <!-- TAB: General -->
            <?php if ($current_tab === 'general'): ?>
            <section class="fp-ps-card" style="margin-top: 20px;">
                <h2><?php esc_html_e('Impostazioni Generali', 'fp-performance-suite'); ?></h2>
                
                <form method="post" action="?page=fp-performance-suite-settings&tab=<?php echo esc_attr($current_tab); ?>">
                    <?php wp_nonce_field('fp-ps-settings', 'fp_ps_settings_nonce'); ?>
                    <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                    
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Modalità Sicura', 'fp-performance-suite'); ?></strong>
                            <?php echo RiskMatrix::renderIndicator('safety_mode_enabled'); ?>
                        </span>
                        <input type="checkbox" name="safety_mode" value="1" <?php checked($pluginOptions['safety_mode']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('safety_mode_enabled')); ?>" />
                    </label>
                    
                    <p>
                        <label for="critical_css"><?php esc_html_e('Critical CSS', 'fp-performance-suite'); ?></label>
                        <small style="display: block; color: #666; margin-bottom: 5px;">
                            <?php esc_html_e('CSS critico da inserire inline per il rendering above-the-fold. Lascia vuoto se utilizzi la generazione automatica nella pagina Advanced.', 'fp-performance-suite'); ?>
                        </small>
                        <textarea name="critical_css" id="critical_css" rows="8" class="large-text code" placeholder="<?php esc_attr_e('Incolla qui il CSS critico per above-the-fold...', 'fp-performance-suite'); ?>"><?php echo esc_textarea($criticalCss); ?></textarea>
                    </p>

                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Richiedi Critical CSS', 'fp-performance-suite'); ?></strong>
                            <?php echo RiskMatrix::renderIndicator('require_critical_css_setting'); ?>
                        </span>
                        <input type="checkbox" name="require_critical_css" value="1" <?php checked(!empty($pluginOptions['require_critical_css'])); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('require_critical_css_setting')); ?>" />
                    </label>
                    
                    <p><button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?></button></p>
                </form>
            </section>
            <?php endif; ?>
            
            <!-- TAB: Import/Export -->
            <?php if ($current_tab === 'import-export'): ?>
            <section class="fp-ps-card" style="margin-top: 20px;">
                <h2><?php esc_html_e('Import/Export Configurazioni', 'fp-performance-suite'); ?></h2>
                
                <!-- Export -->
                <div style="margin-bottom: 30px;">
                    <h3><?php esc_html_e('Esporta Configurazioni', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Esporta tutte le impostazioni del plugin in formato JSON.', 'fp-performance-suite'); ?></p>
                    <form method="post" action="?page=fp-performance-suite-settings&tab=<?php echo esc_attr($current_tab); ?>">
                        <?php wp_nonce_field('fp-ps-export', 'fp_ps_export_nonce'); ?>
                        <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                        <button type="submit" name="export_json" value="1" class="button button-primary">
                            📤 <?php esc_html_e('Esporta Configurazioni', 'fp-performance-suite'); ?>
                        </button>
                    </form>
                </div>
                
                <!-- Import -->
                <div>
                    <h3><?php esc_html_e('Importa Configurazioni', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Importa configurazioni da un file JSON. ATTENZIONE: Questo sovrascriverà tutte le impostazioni attuali.', 'fp-performance-suite'); ?></p>
                <form method="post" action="?page=fp-performance-suite-settings&tab=<?php echo esc_attr($current_tab); ?>">
                    <?php wp_nonce_field('fp-ps-import', 'fp_ps_import_nonce'); ?>
                    <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                    <textarea name="settings_json" rows="8" class="large-text code" placeholder="<?php esc_attr_e('Incolla qui il JSON delle configurazioni da importare...', 'fp-performance-suite'); ?>"></textarea>
                    <p>
                        <button type="submit" name="import_json" value="1" class="button button-primary" data-risk="amber">
                            📥 <?php esc_html_e('Importa Configurazioni', 'fp-performance-suite'); ?>
                        </button>
                    </p>
                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 15px; border-radius: 4px;">
                        <strong>⚠️ <?php esc_html_e('Attenzione:', 'fp-performance-suite'); ?></strong>
                        <?php esc_html_e('L\'importazione sovrascriverà tutte le impostazioni attuali. Assicurati di avere un backup prima di procedere.', 'fp-performance-suite'); ?>
                    </div>
                </form>
                </div>
            </section>
            <?php endif; ?>
            
            <!-- TAB: Service Tester -->
            <?php if ($current_tab === 'service-tester'): ?>
            <section class="fp-ps-card" style="margin-top: 20px;">
                <h2><?php esc_html_e('Service Tester', 'fp-performance-suite'); ?></h2>
                <?php echo $serviceTester->render(); ?>
            </section>
            <?php endif; ?>
        </div>
        
        <?php
        return ob_get_clean();
    }
}
