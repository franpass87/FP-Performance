<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Admin\Components\PageIntro;
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
 * - General Settings
 * - Access Control
 * - Import/Export
 * - Quick Tests
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
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
            'breadcrumbs' => [__('Configuration', 'fp-performance-suite'), __('Settings', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        try {
            // Handle form submissions
            $formHandler = new FormHandler($this->container);
            $message = $formHandler->handle();
            
            $importExportHandler = new ImportExportHandler($this->container);
            $importStatus = $importExportHandler->handleImport();
            
            // Prepare export data
            $export = $importExportHandler->prepareExport();
        } catch (\Throwable $e) {
            return '<div class="notice notice-error"><p><strong>Errore Settings:</strong> ' . esc_html($e->getMessage()) . '</p></div>';
        }
        
        // Tab corrente
        $current_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
        $valid_tabs = ['general', 'access', 'importexport', 'test'];
        if (!in_array($current_tab, $valid_tabs, true)) {
            $current_tab = 'general';
        }
        
        // Mantieni il tab dopo il POST
        if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '') && !empty($_POST['current_tab'] ?? '')) {
            $current_tab = sanitize_key(wp_unslash($_POST['current_tab']));
        }
        
        // Load Settings data
        $pluginOptions = get_option('fp_ps_settings', [
            'allowed_role' => 'administrator',
            'safety_mode' => true,
            'require_critical_css' => true,
        ]);
        $criticalCss = get_option('fp_ps_critical_css', '');

        ob_start();
        ?>
        
        <?php
        // Intro Box con PageIntro Component
        echo PageIntro::render(
            '⚙️',
            __('Settings', 'fp-performance-suite'),
            __('Configura le impostazioni generali del plugin: accesso utenti, notifiche, report automatici e impostazioni avanzate.', 'fp-performance-suite')
        );
        ?>
        
        <!-- Navigazione Tabs -->
        <div class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="?page=fp-performance-suite-settings&tab=general" 
               class="nav-tab <?php echo $current_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                ⚙️ <?php esc_html_e('Generali', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-settings&tab=access" 
               class="nav-tab <?php echo $current_tab === 'access' ? 'nav-tab-active' : ''; ?>">
                🔐 <?php esc_html_e('Controllo Accessi', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=fp-performance-suite-settings&tab=importexport" 
               class="nav-tab <?php echo $current_tab === 'importexport' ? 'nav-tab-active' : ''; ?>">
                📥 <?php esc_html_e('Import/Export', 'fp-performance-suite'); ?>
            </a>
        </div>
        
        <!-- Notice per tabs migrate -->
        <div class="notice notice-info inline" style="margin-bottom: 20px;">
            <p>
                <strong>ℹ️ <?php esc_html_e('Tabs Spostate:', 'fp-performance-suite'); ?></strong>
                <?php esc_html_e('Le tabs Logs e Diagnostics sono state spostate nella pagina', 'fp-performance-suite'); ?> 
                <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-monitoring')); ?>">
                    📈 <?php esc_html_e('Monitoring', 'fp-performance-suite'); ?>
                </a>
                <?php esc_html_e('per una migliore organizzazione.', 'fp-performance-suite'); ?>
            </p>
        </div>

        <!-- Tab Descriptions -->
        <?php if ($current_tab === 'general') : ?>
            <div class="fp-ps-tab-description" style="background: #f3f4f6; border-left: 4px solid #6b7280; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; color: #374151;">
                    <strong>⚙️ Impostazioni Generali:</strong> 
                    <?php esc_html_e('Configura le impostazioni globali del plugin, modalità sicurezza e funzionalità avanzate.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php elseif ($current_tab === 'access') : ?>
            <div class="fp-ps-tab-description" style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; color: #92400e;">
                    <strong>🔐 Controllo Accessi:</strong> 
                    <?php esc_html_e('Gestisci i permessi di accesso al plugin e configura chi può modificare le impostazioni.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php elseif ($current_tab === 'importexport') : ?>
            <div class="fp-ps-tab-description" style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; color: #1e40af;">
                    <strong>📥 Import/Export:</strong> 
                    <?php esc_html_e('Esporta tutte le configurazioni del plugin in formato JSON per backup o migrazione su altri siti.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php elseif ($current_tab === 'test') : ?>
            <div class="fp-ps-tab-description" style="background: #e0e7ff; border-left: 4px solid #667eea; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; color: #3730a3;">
                    <strong>🧪 Test Funzionalità:</strong> 
                    <?php esc_html_e('Verifica in tempo reale che tutte le funzionalità del plugin siano effettivamente attive e funzionanti.', 'fp-performance-suite'); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($message) : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- TAB: General Settings -->
        <div class="fp-ps-tab-content" data-tab="general" style="display: <?php echo $current_tab === 'general' ? 'block' : 'none'; ?>;">
            <section class="fp-ps-card">
                <h2><?php esc_html_e('Impostazioni Generali', 'fp-performance-suite'); ?></h2>
                <form method="post" action="?page=fp-performance-suite-settings&tab=<?php echo esc_attr($current_tab); ?>">
                    <?php wp_nonce_field('fp-ps-settings', 'fp_ps_settings_nonce'); ?>
                    <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                    
                    <label class="fp-ps-toggle">
                        <span class="info">
                            <strong><?php esc_html_e('Modalità Sicura', 'fp-performance-suite'); ?></strong>
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">✓</span>
                                        <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Previene operazioni potenzialmente pericolose e aggiunge ulteriori controlli di sicurezza.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Mantieni sempre attivo per maggiore sicurezza, non ha impatti negativi sulle performance.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                        </span>
                        <input type="checkbox" name="safety_mode" value="1" <?php checked($pluginOptions['safety_mode']); ?> />
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
                            <span class="fp-ps-risk-indicator green">
                                <div class="fp-ps-risk-tooltip green">
                                    <div class="fp-ps-risk-tooltip-title">
                                        <span class="icon">ℹ️</span>
                                        <?php esc_html_e('Consigliato', 'fp-performance-suite'); ?>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Quando attivo, il sistema segnala l’assenza di CSS critico come ottimizzazione mancante.', 'fp-performance-suite'); ?></div>
                                    </div>
                                    <div class="fp-ps-risk-tooltip-section">
                                        <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Suggerimento', 'fp-performance-suite'); ?></div>
                                        <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Disattiva solo se il tema gestisce già il CSS critico o non è necessario monitorarlo.', 'fp-performance-suite'); ?></div>
                                    </div>
                                </div>
                            </span>
                        </span>
                        <input type="checkbox" name="require_critical_css" value="1" <?php checked(!empty($pluginOptions['require_critical_css'])); ?> />
                    </label>
                    
                    <p><button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?></button></p>
                </form>
            </section>
        </div>
        
        <!-- TAB: Access Control -->
        <div class="fp-ps-tab-content" data-tab="access" style="display: <?php echo $current_tab === 'access' ? 'block' : 'none'; ?>;">
            <section class="fp-ps-card">
                <h2><?php esc_html_e('Controllo Accessi', 'fp-performance-suite'); ?></h2>
                <form method="post" action="?page=fp-performance-suite-settings&tab=<?php echo esc_attr($current_tab); ?>">
                    <?php wp_nonce_field('fp-ps-settings', 'fp_ps_settings_nonce'); ?>
                    <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                    
                    <p>
                        <label for="allowed_role"><?php esc_html_e('Ruolo minimo per gestire il plugin', 'fp-performance-suite'); ?></label>
                        <small style="display: block; color: #666; margin-bottom: 5px;">
                            <?php esc_html_e('Seleziona il ruolo minimo richiesto per accedere e modificare le impostazioni del plugin.', 'fp-performance-suite'); ?>
                        </small>
                        <select name="allowed_role" id="allowed_role" class="regular-text">
                            <option value="administrator" <?php selected($pluginOptions['allowed_role'], 'administrator'); ?>><?php esc_html_e('Administrator (Solo amministratori)', 'fp-performance-suite'); ?></option>
                            <option value="editor" <?php selected($pluginOptions['allowed_role'], 'editor'); ?>><?php esc_html_e('Editor (Editor e superiori)', 'fp-performance-suite'); ?></option>
                        </select>
                    </p>
                    
                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
                        <p style="margin: 0;">
                            <strong>⚠️ <?php esc_html_e('Attenzione:', 'fp-performance-suite'); ?></strong><br>
                            <?php esc_html_e('Modificare questa impostazione può impedire l\'accesso al plugin per alcuni utenti. Assicurati di avere sempre almeno un amministratore con accesso.', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                    
                    <p><button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni', 'fp-performance-suite'); ?></button></p>
                </form>
            </section>
        </div>
        
        <!-- TAB: Import/Export -->
        <div class="fp-ps-tab-content" data-tab="importexport" style="display: <?php echo $current_tab === 'importexport' ? 'block' : 'none'; ?>;">
            <section class="fp-ps-card">
                <h2><?php esc_html_e('📤 Esporta Configurazioni', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Copia il JSON seguente per creare un backup delle tue configurazioni o per migrare su un altro sito.', 'fp-performance-suite'); ?></p>
                <textarea class="large-text code" rows="10" readonly onclick="this.select();"><?php echo esc_textarea(wp_json_encode($export, JSON_PRETTY_PRINT)); ?></textarea>
                <p>
                    <button type="button" class="button" onclick="navigator.clipboard.writeText(this.previousElementSibling.previousElementSibling.value); alert('<?php esc_attr_e('Configurazioni copiate negli appunti!', 'fp-performance-suite'); ?>');">
                        📋 <?php esc_html_e('Copia negli Appunti', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </section>
            
            <section class="fp-ps-card">
                <h2><?php esc_html_e('📥 Importa Configurazioni', 'fp-performance-suite'); ?></h2>
                <?php if ($importStatus) : ?>
                    <div class="notice <?php echo strpos($importStatus, '✅') !== false ? 'notice-success' : 'notice-error'; ?>">
                        <p><?php echo esc_html($importStatus); ?></p>
                    </div>
                <?php endif; ?>
                <p><?php esc_html_e('Incolla qui un JSON di configurazione esportato in precedenza.', 'fp-performance-suite'); ?></p>
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
                        <p style="margin: 0;">
                            <strong>⚠️ <?php esc_html_e('Attenzione:', 'fp-performance-suite'); ?></strong><br>
                            <?php esc_html_e('L\'importazione sovrascriverà le configurazioni attuali. Crea un backup prima di procedere.', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                </form>
            </section>
        </div>
        
        <!-- TAB: Test -->
        <div class="fp-ps-tab-content" data-tab="test" style="display: <?php echo $current_tab === 'test' ? 'block' : 'none'; ?>;">
            <?php 
            $serviceTester = new ServiceTester($this->container);
            echo $serviceTester->render(); 
            ?>
        </div>
        
        <script>
        // Gestione visibilità tab
        jQuery(document).ready(function($) {
            // Mostra/nascondi tab in base all'URL
            $('.nav-tab').on('click', function(e) {
                var href = $(this).attr('href');
                if (!href) {
                    return;
                }
                var tabMatch = href.split('tab=');
                if (tabMatch.length < 2) {
                    return;
                }
                var tab = tabMatch[1].split('&')[0]; // Rimuovi eventuali altri parametri
                if (!tab) {
                    return;
                }
                $('.fp-ps-tab-content').hide();
                $('.fp-ps-tab-content[data-tab="' + tab + '"]').show();
            });
        });
        </script>
        
        <?php
        return (string) ob_get_clean();
    }

    // Metodi normalizeBrowserCacheImport(), normalizePageCacheImport(), normalizeAssetSettingsImport(),
    // resolveBoolean(), parseInteger(), interpretBoolean() rimossi - ora gestiti da:
    // - Settings/DataNormalizer
    
    // Metodo renderTestFunctionalityTab() rimosso - ora gestito da ServiceTester
}
