<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Media\WebPConverter;

use function __;
use function array_key_exists;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function filter_var;
use function get_option;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function json_decode;
use function sanitize_key;
use function sanitize_text_field;
use function selected;
use function checked;
use function sprintf;
use function strcasecmp;
use function trim;
use function update_option;
use function wp_json_encode;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_INT;

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
        $pageCache = $this->container->get(PageCache::class);
        $headers = $this->container->get(Headers::class);
        $webp = $this->container->get(WebPConverter::class);
        $optimizer = $this->container->get(Optimizer::class);
        $cleaner = $this->container->get(Cleaner::class);
        
        $message = '';
        $importStatus = '';
        $headerDefaults = $headers->settings();
        
        // Handle Settings form submission (General Settings + Access Control)
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_settings_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_settings_nonce']), 'fp-ps-settings')) {
            $pluginOptions = get_option('fp_ps_settings', []);
            $pluginOptions['allowed_role'] = sanitize_text_field($_POST['allowed_role'] ?? 'administrator');
            $pluginOptions['safety_mode'] = !empty($_POST['safety_mode']);
            update_option('fp_ps_settings', $pluginOptions);
            update_option('fp_ps_critical_css', wp_unslash($_POST['critical_css'] ?? ''));
            $message = __('Impostazioni salvate con successo.', 'fp-performance-suite');
        }
        
        // Handle Import/Export form submission
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_import_nonce'])) {
            $nonce = wp_unslash($_POST['fp_ps_import_nonce']);
            if (!is_string($nonce) || !wp_verify_nonce($nonce, 'fp-ps-import')) {
                $importStatus = __('Verifica di sicurezza fallita. Riprova.', 'fp-performance-suite');
            } elseif (isset($_POST['import_json'])) {
                $json = wp_unslash($_POST['settings_json'] ?? '');
                $data = json_decode($json, true);
                if (is_array($data)) {
                    $prepared = [];
                    $valid = true;
                    $allowed = [
                        'fp_ps_page_cache',
                        'fp_ps_browser_cache',
                        'fp_ps_assets',
                        'fp_ps_webp',
                        'fp_ps_db',
                    ];
                    foreach ($allowed as $option) {
                        if (!array_key_exists($option, $data)) {
                            continue;
                        }
                        if (!is_array($data[$option])) {
                            $valid = false;
                            break;
                        }
                        switch ($option) {
                            case 'fp_ps_page_cache':
                                $prepared[$option] = $this->normalizePageCacheImport(
                                    $data[$option],
                                    $pageCache->settings()
                                );
                                break;
                            case 'fp_ps_browser_cache':
                                $prepared[$option] = $this->normalizeBrowserCacheImport(
                                    $data[$option],
                                    $headerDefaults
                                );
                                break;
                            case 'fp_ps_assets':
                                $assetDefaults = $optimizer->settings();
                                $prepared[$option] = $this->normalizeAssetSettingsImport($data[$option], $assetDefaults);
                                break;
                            case 'fp_ps_webp':
                                $prepared[$option] = $this->normalizeWebpImport(
                                    $data[$option],
                                    $webp->settings()
                                );
                                break;
                            case 'fp_ps_db':
                                $prepared[$option] = [
                                    'schedule' => sanitize_key($data[$option]['schedule'] ?? $cleaner->settings()['schedule']),
                                    'batch' => isset($data[$option]['batch']) ? (int) $data[$option]['batch'] : $cleaner->settings()['batch'],
                                ];
                                break;
                        }
                    }

                    if ($valid) {
                        if (isset($prepared['fp_ps_page_cache'])) {
                            $pageCache->update($prepared['fp_ps_page_cache']);
                        }
                        if (isset($prepared['fp_ps_browser_cache'])) {
                            $headers->update($prepared['fp_ps_browser_cache']);
                        }
                        if (isset($prepared['fp_ps_assets'])) {
                            $optimizer->update($prepared['fp_ps_assets']);
                        }
                        if (isset($prepared['fp_ps_webp'])) {
                            $webp->update($prepared['fp_ps_webp']);
                        }
                        if (isset($prepared['fp_ps_db'])) {
                            $cleaner->update($prepared['fp_ps_db']);
                        }
                        $importStatus = __('✅ Impostazioni importate con successo.', 'fp-performance-suite');
                    } else {
                        $importStatus = __('❌ Payload JSON non valido.', 'fp-performance-suite');
                    }
                } else {
                    $importStatus = __('❌ Payload JSON non valido.', 'fp-performance-suite');
                }
            }
        }

        // Prepare export data
        $export = [
            'fp_ps_page_cache' => $pageCache->settings(),
            'fp_ps_browser_cache' => $headers->settings(),
            'fp_ps_assets' => $optimizer->settings(),
            'fp_ps_webp' => $webp->settings(),
            'fp_ps_db' => $cleaner->settings(),
        ];
        
        // Prepare diagnostics tests
        $tests = [
            __('Page cache abilitata', 'fp-performance-suite') => $pageCache->isEnabled() ? __('✅ Pass', 'fp-performance-suite') : __('❌ Disabilitata', 'fp-performance-suite'),
            __('Header browser cache', 'fp-performance-suite') => $headers->status()['enabled'] ? __('✅ Pass', 'fp-performance-suite') : __('❌ Mancanti', 'fp-performance-suite'),
            __('Copertura WebP', 'fp-performance-suite') => sprintf('%0.2f%%', $webp->status()['coverage']),
        ];
        
        // Tab corrente
        $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
        $valid_tabs = ['general', 'access', 'importexport', 'diagnostics'];
        if (!in_array($current_tab, $valid_tabs, true)) {
            $current_tab = 'general';
        }
        
        // Mantieni il tab dopo il POST
        if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['current_tab'])) {
            $current_tab = sanitize_key($_POST['current_tab']);
        }
        
        // Load Settings data
        $pluginOptions = get_option('fp_ps_settings', [
            'allowed_role' => 'administrator',
            'safety_mode' => true,
        ]);
        $criticalCss = get_option('fp_ps_critical_css', '');

        ob_start();
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
            <a href="?page=fp-performance-suite-settings&tab=diagnostics" 
               class="nav-tab <?php echo $current_tab === 'diagnostics' ? 'nav-tab-active' : ''; ?>">
                🔍 <?php esc_html_e('Test Rapidi', 'fp-performance-suite'); ?>
            </a>
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
        <?php elseif ($current_tab === 'diagnostics') : ?>
            <div class="fp-ps-tab-description" style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                <p style="margin: 0; color: #065f46;">
                    <strong>🔍 Test Rapidi:</strong> 
                    <?php esc_html_e('Verifica rapidamente lo stato delle funzionalità principali. Per test approfonditi, visita la pagina Diagnostica completa.', 'fp-performance-suite'); ?>
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
        
        <!-- TAB: Quick Diagnostics -->
        <div class="fp-ps-tab-content" data-tab="diagnostics" style="display: <?php echo $current_tab === 'diagnostics' ? 'block' : 'none'; ?>;">
            <section class="fp-ps-card">
                <h2><?php esc_html_e('🔍 Test Rapidi di Sistema', 'fp-performance-suite'); ?></h2>
                <p><?php esc_html_e('Verifica veloce dello stato delle funzionalità principali.', 'fp-performance-suite'); ?></p>
                <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 60%;"><?php esc_html_e('Funzionalità', 'fp-performance-suite'); ?></th>
                            <th style="width: 40%;"><?php esc_html_e('Stato', 'fp-performance-suite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tests as $label => $value) : ?>
                            <tr>
                                <td><strong><?php echo esc_html($label); ?></strong></td>
                                <td><?php echo esc_html($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="margin-top: 20px;">
                    <a href="<?php echo admin_url('admin.php?page=fp-performance-suite-diagnostics'); ?>" class="button button-primary">
                        🔍 <?php esc_html_e('Vai alla Diagnostica Completa', 'fp-performance-suite'); ?>
                    </a>
                </p>
            </section>
        </div>
        
        <script>
        // Gestione visibilità tab
        jQuery(document).ready(function($) {
            // Mostra/nascondi tab in base all'URL
            $('.nav-tab').on('click', function(e) {
                var tab = $(this).attr('href').split('tab=')[1];
                $('.fp-ps-tab-content').hide();
                $('.fp-ps-tab-content[data-tab="' + tab + '"]').show();
            });
        });
        </script>
        
        <?php
        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,headers:array{Cache-Control:string},expires_ttl:int,htaccess:string}
     */
    protected function normalizeBrowserCacheImport(array $incoming, array $defaults): array
    {
        $defaultHeaders = [];
        if (isset($defaults['headers']) && is_array($defaults['headers'])) {
            $defaultHeaders = $defaults['headers'];
        }

        $defaultCacheControl = isset($defaultHeaders['Cache-Control']) && is_string($defaultHeaders['Cache-Control'])
            ? trim($defaultHeaders['Cache-Control'])
            : 'public, max-age=31536000';

        $defaultTtl = isset($defaults['expires_ttl']) ? (int) $defaults['expires_ttl'] : 31536000;
        $defaultHtaccess = isset($defaults['htaccess']) && is_string($defaults['htaccess'])
            ? $defaults['htaccess']
            : '';

        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $headerValue = $incoming['headers'] ?? [];
        if (is_string($headerValue)) {
            $headerValue = ['Cache-Control' => $headerValue];
        }

        $cacheControl = $defaultCacheControl;
        if (is_array($headerValue)) {
            if (isset($headerValue['Cache-Control']) && is_string($headerValue['Cache-Control'])) {
                $trimmed = trim($headerValue['Cache-Control']);
                if ($trimmed !== '') {
                    $cacheControl = $trimmed;
                }
            } else {
                foreach ($headerValue as $key => $value) {
                    if (!is_string($key) || strcasecmp($key, 'Cache-Control') !== 0) {
                        continue;
                    }

                    if (is_string($value)) {
                        $trimmed = trim($value);
                        if ($trimmed !== '') {
                            $cacheControl = $trimmed;
                            break;
                        }
                    }
                }
            }
        }

        if ($cacheControl === $defaultCacheControl && isset($incoming['cache_control']) && is_string($incoming['cache_control'])) {
            $legacy = trim($incoming['cache_control']);
            if ($legacy !== '') {
                $cacheControl = $legacy;
            }
        }

        $ttl = $defaultTtl;
        if (array_key_exists('expires_ttl', $incoming)) {
            $parsedTtl = $this->parseInteger($incoming['expires_ttl']);
            if ($parsedTtl !== null && $parsedTtl >= 0) {
                $ttl = $parsedTtl;
            }
        }

        $htaccess = $defaultHtaccess;
        if (array_key_exists('htaccess', $incoming) && is_string($incoming['htaccess'])) {
            $htaccess = $incoming['htaccess'];
        }

        return [
            'enabled' => $enabled,
            'headers' => ['Cache-Control' => $cacheControl],
            'expires_ttl' => $ttl,
            'htaccess' => $htaccess,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,ttl:int}
     */
    protected function normalizePageCacheImport(array $incoming, array $defaults): array
    {
        $defaultTtl = isset($defaults['ttl']) ? (int) $defaults['ttl'] : 3600;

        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $ttl = $defaultTtl;
        if (array_key_exists('ttl', $incoming)) {
            $parsedTtl = $this->parseInteger($incoming['ttl']);
            if ($parsedTtl !== null && $parsedTtl >= 0) {
                $ttl = $parsedTtl;
            }
        }

        return [
            'enabled' => $enabled,
            'ttl' => $ttl,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,quality:int,keep_original:bool,lossy:bool}
     */
    protected function normalizeWebpImport(array $incoming, array $defaults): array
    {
        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $defaultQuality = isset($defaults['quality']) ? (int) $defaults['quality'] : 82;
        $quality = $defaultQuality;
        if (array_key_exists('quality', $incoming)) {
            $parsedQuality = $this->parseInteger($incoming['quality']);
            if ($parsedQuality !== null) {
                $quality = max(1, min(100, $parsedQuality));
            }
        }

        $keepOriginal = array_key_exists('keep_original', $defaults)
            ? (bool) $defaults['keep_original']
            : false;
        if (array_key_exists('keep_original', $incoming)) {
            $keepOriginal = $this->interpretBoolean($incoming['keep_original'], $keepOriginal);
        }

        $lossy = array_key_exists('lossy', $defaults)
            ? (bool) $defaults['lossy']
            : false;
        if (array_key_exists('lossy', $incoming)) {
            $lossy = $this->interpretBoolean($incoming['lossy'], $lossy);
        }

        return [
            'enabled' => $enabled,
            'quality' => $quality,
            'keep_original' => $keepOriginal,
            'lossy' => $lossy,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    protected function normalizeAssetSettingsImport(array $incoming, array $defaults): array
    {
        $settings = $defaults;

        foreach (['minify_html', 'defer_js', 'async_js', 'remove_emojis', 'combine_css', 'combine_js'] as $flag) {
            if (array_key_exists($flag, $incoming)) {
                $settings[$flag] = $this->interpretBoolean($incoming[$flag], !empty($defaults[$flag] ?? false));
            }
        }

        foreach (['dns_prefetch', 'preload'] as $listKey) {
            if (array_key_exists($listKey, $incoming)) {
                $settings[$listKey] = $incoming[$listKey];
            }
        }

        if (array_key_exists('heartbeat_admin', $incoming)) {
            $parsedHeartbeat = $this->parseInteger($incoming['heartbeat_admin']);
            if ($parsedHeartbeat !== null && $parsedHeartbeat >= 0) {
                $settings['heartbeat_admin'] = $parsedHeartbeat;
            }
        }

        return $settings;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function resolveBoolean(array $source, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return $this->interpretBoolean($source[$key], $default);
    }

    /**
     * @param mixed $value
     */
    private function parseInteger($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            $filtered = filter_var($trimmed, FILTER_VALIDATE_INT);
            if ($filtered !== false) {
                return (int) $filtered;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function interpretBoolean($value, bool $fallback): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return false;
            }

            $filtered = filter_var($trimmed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtered !== null) {
                return $filtered;
            }
        }

        return $fallback;
    }
}
