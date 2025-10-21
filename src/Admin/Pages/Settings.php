<?php

namespace FP\PerfSuite\Admin\Pages;

use function __;
use function esc_attr;
use function esc_attr_e;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function get_option;
use function sanitize_text_field;
use function sanitize_email;
use function selected;
use function checked;
use function update_option;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;
use function array_map;
use function array_filter;
use function explode;
use function implode;
use function trim;

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
            'breadcrumbs' => [__('Configurazione', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        // Gestisci il reset dei permessi se richiesto
        if (isset($_GET['fp_reset_permissions']) && $_GET['fp_reset_permissions'] === '1') {
            if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'fp_reset_permissions')) {
                update_option('fp_ps_settings', ['allowed_role' => 'administrator']);
                $message = '<div class="notice notice-success"><p><strong>' . 
                          esc_html__('✅ Permessi reimpostati con successo!', 'fp-performance-suite') . 
                          '</strong> ' . 
                          esc_html__('Le impostazioni dei permessi sono state ripristinate ai valori predefiniti.', 'fp-performance-suite') . 
                          '</p></div>';
            }
        }
        
        $options = get_option('fp_ps_settings', [
            'allowed_role' => 'administrator',
            'safety_mode' => true,
            'exclude_urls' => '',
            'exclude_roles' => [],
            'log_level' => 'ERROR',
            'log_retention_days' => 30,
            'notification_email' => get_option('admin_email'),
            'enable_notifications' => false,
            'dev_mode' => false,
            'mobile_separate' => false,
        ]);
        $criticalCss = get_option('fp_ps_critical_css', '');
        
        if (!isset($message)) {
            $message = '';
        }
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_settings_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_settings_nonce']), 'fp-ps-settings')) {
            $options['allowed_role'] = sanitize_text_field($_POST['allowed_role'] ?? 'administrator');
            $options['safety_mode'] = !empty($_POST['safety_mode']);
            
            // Global Exclusions
            $options['exclude_urls'] = sanitize_text_field(wp_unslash($_POST['exclude_urls'] ?? ''));
            $options['exclude_roles'] = array_map('sanitize_text_field', $_POST['exclude_roles'] ?? []);
            
            // Logging Settings
            $options['log_level'] = sanitize_text_field($_POST['log_level'] ?? 'ERROR');
            $options['log_retention_days'] = (int) ($_POST['log_retention_days'] ?? 30);
            
            // Notifications
            $options['enable_notifications'] = !empty($_POST['enable_notifications']);
            $options['notification_email'] = sanitize_email($_POST['notification_email'] ?? get_option('admin_email'));
            
            // Development Mode
            $options['dev_mode'] = !empty($_POST['dev_mode']);
            
            // Mobile Settings
            $options['mobile_separate'] = !empty($_POST['mobile_separate']);
            
            // Handle Export
            if (isset($_POST['export_settings'])) {
                $this->exportSettings();
                exit;
            }
            
            // Handle Import
            if (isset($_FILES['import_file']) && !empty($_FILES['import_file']['tmp_name'])) {
                $importResult = $this->importSettings($_FILES['import_file']);
                if ($importResult['success']) {
                    $message = __('Settings imported successfully!', 'fp-performance-suite');
                } else {
                    $message = sprintf(__('Import failed: %s', 'fp-performance-suite'), $importResult['error']);
                }
            }
            
            update_option('fp_ps_settings', $options);
            update_option('fp_ps_critical_css', wp_unslash($_POST['critical_css'] ?? ''));
            
            // Update Logger level
            \FP\PerfSuite\Utils\Logger::setLevel($options['log_level']);
            
            $message = __('Settings saved.', 'fp-performance-suite');
        }
        
        // Tab system
        $validTabs = ['settings', 'import-export'];
        $currentTab = isset($_GET['tab']) && in_array($_GET['tab'], $validTabs, true) 
            ? sanitize_key($_GET['tab']) 
            : 'settings';
        
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- Tab Navigation -->
        <nav class="nav-tab-wrapper wp-clearfix" style="margin-bottom: 20px;">
            <a href="?page=<?php echo esc_attr($this->slug()); ?>&tab=settings" 
               class="nav-tab <?php echo $currentTab === 'settings' ? 'nav-tab-active' : ''; ?>">
                ⚙️ <?php esc_html_e('Plugin Settings', 'fp-performance-suite'); ?>
            </a>
            <a href="?page=<?php echo esc_attr($this->slug()); ?>&tab=import-export" 
               class="nav-tab <?php echo $currentTab === 'import-export' ? 'nav-tab-active' : ''; ?>">
                📥 <?php esc_html_e('Import/Export', 'fp-performance-suite'); ?>
            </a>
        </nav>
        
        <form method="post">
            <input type="hidden" name="current_tab" value="<?php echo esc_attr($currentTab); ?>" />
            
            <!-- Tab: Plugin Settings -->
            <div class="tab-content" style="<?php echo $currentTab !== 'settings' ? 'display:none;' : ''; ?>">
            <?php wp_nonce_field('fp-ps-settings', 'fp_ps_settings_nonce'); ?>
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Access Control', 'fp-performance-suite'); ?></h2>
                <p>
                    <label for="allowed_role"><?php esc_html_e('Minimum role to manage plugin', 'fp-performance-suite'); ?></label>
                    <select name="allowed_role" id="allowed_role">
                        <option value="administrator" <?php selected($options['allowed_role'], 'administrator'); ?>><?php esc_html_e('Administrator', 'fp-performance-suite'); ?></option>
                        <option value="editor" <?php selected($options['allowed_role'], 'editor'); ?>><?php esc_html_e('Editor', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Safety mode', 'fp-performance-suite'); ?></strong>
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
                    <input type="checkbox" name="safety_mode" value="1" <?php checked($options['safety_mode']); ?> />
                </label>
                <p>
                    <label for="critical_css"><?php esc_html_e('Critical CSS placeholder', 'fp-performance-suite'); ?></label>
                    <textarea name="critical_css" id="critical_css" rows="6" class="large-text code" placeholder="<?php esc_attr_e('Paste above-the-fold CSS or snippet reference.', 'fp-performance-suite'); ?>"><?php echo esc_textarea($criticalCss); ?></textarea>
                </p>
        </section>
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Global Exclusions', 'fp-performance-suite'); ?></h2>
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-bottom: 20px;">
                <p style="margin: 0;">
                    <strong>💡 <?php esc_html_e('Suggerimento:', 'fp-performance-suite'); ?></strong>
                    <?php 
                    echo sprintf(
                        __('Per gestire le esclusioni in modo più avanzato con il sistema intelligente Smart Auto-Exclusions, vai alla pagina %s', 'fp-performance-suite'),
                        '<a href="' . admin_url('admin.php?page=fp-performance-suite-exclusions') . '" style="color: #2271b1; font-weight: 600;">🤖 Gestisci Esclusioni</a>'
                    );
                    ?>
                </p>
            </div>
                <p>
                    <label for="exclude_urls"><?php esc_html_e('Exclude URLs from all optimizations', 'fp-performance-suite'); ?></label>
                    <textarea name="exclude_urls" id="exclude_urls" rows="5" class="large-text" placeholder="<?php esc_attr_e('One URL per line. Examples:\n/checkout/\n/my-account/\n/cart/', 'fp-performance-suite'); ?>"><?php echo esc_textarea($options['exclude_urls']); ?></textarea>
                    <span class="description"><?php esc_html_e('URLs or patterns to exclude from all plugin optimizations. Use one URL per line. Supports wildcards (*).', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label><?php esc_html_e('Exclude optimizations for user roles', 'fp-performance-suite'); ?></label><br>
                    <?php
                    $roles = wp_roles()->get_names();
                    $excludedRoles = $options['exclude_roles'] ?? [];
                    foreach ($roles as $roleKey => $roleName) :
                    ?>
                        <label style="display: inline-block; margin-right: 15px;">
                            <input type="checkbox" name="exclude_roles[]" value="<?php echo esc_attr($roleKey); ?>" <?php checked(in_array($roleKey, $excludedRoles, true)); ?>>
                            <?php echo esc_html($roleName); ?>
                        </label>
                    <?php endforeach; ?>
                    <br>
                    <span class="description"><?php esc_html_e('Users with these roles will not be affected by plugin optimizations (useful for admins during development).', 'fp-performance-suite'); ?></span>
                </p>
        </section>

        <section class="fp-ps-card">
            <h2><?php esc_html_e('Logging & Debugging', 'fp-performance-suite'); ?></h2>
                <p>
                    <label for="log_level"><?php esc_html_e('Log Level', 'fp-performance-suite'); ?></label>
                    <select name="log_level" id="log_level">
                        <option value="ERROR" <?php selected($options['log_level'], 'ERROR'); ?>><?php esc_html_e('ERROR - Only errors', 'fp-performance-suite'); ?></option>
                        <option value="WARNING" <?php selected($options['log_level'], 'WARNING'); ?>><?php esc_html_e('WARNING - Errors and warnings', 'fp-performance-suite'); ?></option>
                        <option value="INFO" <?php selected($options['log_level'], 'INFO'); ?>><?php esc_html_e('INFO - Informational messages', 'fp-performance-suite'); ?></option>
                        <option value="DEBUG" <?php selected($options['log_level'], 'DEBUG'); ?>><?php esc_html_e('DEBUG - All messages (verbose)', 'fp-performance-suite'); ?></option>
                    </select>
                    <span class="description"><?php esc_html_e('Higher levels will write more entries to the log. Use DEBUG only during troubleshooting.', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label for="log_retention_days"><?php esc_html_e('Log Retention Period', 'fp-performance-suite'); ?></label>
                    <input type="number" name="log_retention_days" id="log_retention_days" value="<?php echo esc_attr($options['log_retention_days']); ?>" min="1" max="365" class="small-text">
                    <span><?php esc_html_e('days', 'fp-performance-suite'); ?></span>
                    <span class="description"><?php esc_html_e('Logs older than this will be automatically deleted.', 'fp-performance-suite'); ?></span>
                </p>
        </section>

        <section class="fp-ps-card">
            <h2><?php esc_html_e('Email Notifications', 'fp-performance-suite'); ?></h2>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable Email Notifications', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="enable_notifications" value="1" <?php checked($options['enable_notifications']); ?> />
                </label>
                <p class="description"><?php esc_html_e('Receive email alerts for critical errors and important events.', 'fp-performance-suite'); ?></p>
                <p>
                    <label for="notification_email"><?php esc_html_e('Notification Email', 'fp-performance-suite'); ?></label>
                    <input type="email" name="notification_email" id="notification_email" value="<?php echo esc_attr($options['notification_email']); ?>" class="regular-text">
                    <span class="description"><?php esc_html_e('Email address to receive notifications.', 'fp-performance-suite'); ?></span>
                </p>
        </section>
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Developer Options', 'fp-performance-suite'); ?></h2>
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong><?php esc_html_e('Development Mode', 'fp-performance-suite'); ?></strong>
                    <span class="fp-ps-risk-indicator red">
                        <div class="fp-ps-risk-tooltip red">
                            <div class="fp-ps-risk-tooltip-title">
                                <span class="icon">🔴</span>
                                <?php esc_html_e('Solo per Sviluppo', 'fp-performance-suite'); ?>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Disabilita tutte le cache, abilita logging verboso e mostra informazioni di debug. Solo per ambiente di sviluppo.', 'fp-performance-suite'); ?></div>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Effetti', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text">
                                    <?php esc_html_e('• Disabilita page cache<br>• Disabilita browser cache<br>• Log level impostato a DEBUG<br>• Mostra informazioni tecniche', 'fp-performance-suite'); ?>
                                </div>
                            </div>
                            <div class="fp-ps-risk-tooltip-section">
                                <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Avviso', 'fp-performance-suite'); ?></div>
                                <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ MAI ATTIVARE IN PRODUZIONE! Le performance saranno drasticamente ridotte.', 'fp-performance-suite'); ?></div>
                            </div>
                        </div>
                    </span>
                </span>
                <input type="checkbox" name="dev_mode" value="1" <?php checked($options['dev_mode']); ?> />
            </label>
            <p class="description" style="color: #d63638; font-weight: 600;">
                <?php esc_html_e('⚠️ ATTENZIONE: Questa modalità riduce significativamente le performance. Da usare solo durante lo sviluppo!', 'fp-performance-suite'); ?>
            </p>
        </section>

        <section class="fp-ps-card">
            <h2><?php esc_html_e('Mobile Optimization', 'fp-performance-suite'); ?></h2>
            <label class="fp-ps-toggle">
                <span class="info">
                    <strong><?php esc_html_e('Separate Mobile/Desktop Settings', 'fp-performance-suite'); ?></strong>
                </span>
                <input type="checkbox" name="mobile_separate" value="1" <?php checked($options['mobile_separate']); ?> />
            </label>
            <p class="description">
                <?php esc_html_e('Abilita cache separata e ottimizzazioni diverse per dispositivi mobile e desktop. Migliora le performance mobile con impostazioni più aggressive.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($options['mobile_separate']) : ?>
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 15px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Impostazioni Mobile Attive:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('Cache separata per mobile e desktop', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Lazy load più aggressivo su mobile', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Qualità immagini ottimizzata per connessioni lente', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Script di terze parti ritardati più a lungo', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
            <?php endif; ?>
        </section>
        
        </div><!-- End Tab: Plugin Settings -->
        
        <!-- Tab: Import/Export -->
        <div class="tab-content" style="<?php echo $currentTab !== 'import-export' ? 'display:none;' : ''; ?>">
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Import / Export Configuration', 'fp-performance-suite'); ?></h2>
            <p><?php esc_html_e('Export your current configuration to a JSON file or import a previously saved configuration. Perfect for migrating settings between sites or creating backups.', 'fp-performance-suite'); ?></p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div style="border: 2px solid #00a32a; border-radius: 4px; padding: 20px;">
                    <h3 style="margin-top: 0; color: #00a32a;">📤 <?php esc_html_e('Export Settings', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Download all current plugin settings as a JSON file.', 'fp-performance-suite'); ?></p>
                    <button type="submit" name="export_settings" class="button button-secondary button-large" style="width: 100%;">
                        <?php esc_html_e('Export Configuration', 'fp-performance-suite'); ?>
                    </button>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e('Exports: Settings, Cache config, Asset optimization, WebP, Lazy Load, Custom Presets', 'fp-performance-suite'); ?>
                    </p>
                </div>
                
                <div style="border: 2px solid #2271b1; border-radius: 4px; padding: 20px;">
                    <h3 style="margin-top: 0; color: #2271b1;">📥 <?php esc_html_e('Import Settings', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Upload a previously exported JSON configuration file.', 'fp-performance-suite'); ?></p>
                    <input type="file" name="import_file" accept=".json" style="margin-bottom: 10px; width: 100%;">
                    <button type="submit" name="import_settings" class="button button-secondary button-large" style="width: 100%;">
                        <?php esc_html_e('Import Configuration', 'fp-performance-suite'); ?>
                    </button>
                    <p class="description" style="margin-top: 10px; color: #d63638;">
                        <?php esc_html_e('⚠️ Warning: This will overwrite your current settings!', 'fp-performance-suite'); ?>
                    </p>
                </div>
            </div>
            
            <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Use Cases:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php esc_html_e('Backup configuration before major changes', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Clone settings to another WordPress site', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Share optimized configurations with team', 'fp-performance-suite'); ?></li>
                    <li><?php esc_html_e('Quick restore after testing different settings', 'fp-performance-suite'); ?></li>
                </ul>
            </div>
        </section>
        
        </div><!-- End Tab: Import/Export -->
        
        <div class="fp-ps-card">
            <p><button type="submit" class="button button-primary button-large"><?php esc_html_e('Save All Settings', 'fp-performance-suite'); ?></button></p>
        </div>
        </form>
        <?php
        return (string) ob_get_clean();
    }
    
    /**
     * Export all settings to JSON file
     */
    private function exportSettings(): void
    {
        $export = [
            'version' => FP_PERF_SUITE_VERSION,
            'exported' => current_time('mysql'),
            'site_url' => get_site_url(),
            'settings' => [
                'fp_ps_settings' => get_option('fp_ps_settings', []),
                'fp_ps_page_cache' => get_option('fp_ps_page_cache', []),
                'fp_ps_browser_cache' => get_option('fp_ps_browser_cache', []),
                'fp_ps_asset_optimizer' => get_option('fp_ps_asset_optimizer', []),
                'fp_ps_webp' => get_option('fp_ps_webp', []),
                'fp_ps_avif' => get_option('fp_ps_avif', []),
                'fp_ps_lazy_load' => get_option('fp_ps_lazy_load', []),
                'fp_ps_image_optimizer' => get_option('fp_ps_image_optimizer', []),
                'fp_ps_critical_css' => get_option('fp_ps_critical_css', ''),
                'fp_ps_cdn' => get_option('fp_ps_cdn', []),
                'fp_ps_compression' => get_option('fp_ps_compression', []),
                'fp_ps_monitoring' => get_option('fp_ps_monitoring', []),
                'fp_ps_cwv' => get_option('fp_ps_cwv', []),
                'fp_ps_custom_presets' => get_option('fp_ps_custom_presets', []),
                'fp_ps_performance_budget' => get_option('fp_ps_performance_budget', []),
            ],
        ];
        
        $filename = 'fp-performance-suite-config-' . date('Y-m-d-His') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo wp_json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
    
    /**
     * Import settings from JSON file
     */
    private function importSettings(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => __('File upload error', 'fp-performance-suite')];
        }
        
        if ($file['type'] !== 'application/json' && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'json') {
            return ['success' => false, 'error' => __('Invalid file type. Only JSON files are allowed.', 'fp-performance-suite')];
        }
        
        $content = file_get_contents($file['tmp_name']);
        if ($content === false) {
            return ['success' => false, 'error' => __('Could not read file', 'fp-performance-suite')];
        }
        
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => __('Invalid JSON format', 'fp-performance-suite')];
        }
        
        if (!isset($data['settings']) || !is_array($data['settings'])) {
            return ['success' => false, 'error' => __('Invalid configuration file structure', 'fp-performance-suite')];
        }
        
        // Import all settings
        foreach ($data['settings'] as $option_name => $option_value) {
            update_option($option_name, $option_value);
        }
        
        return ['success' => true];
    }
}
