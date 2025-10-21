<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\LighthouseFontOptimizer;

/**
 * Lighthouse Font Optimization Admin Page
 * 
 * Gestisce le ottimizzazioni specifiche per i font identificati nel report Lighthouse
 * con potenziale risparmio di 180ms.
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class LighthouseFontOptimization
{
    private $optimizer;
    private $container;

    public function __construct($container = null)
    {
        $this->container = $container;
        $this->optimizer = new LighthouseFontOptimizer();
        $this->registerHooks();
    }

    /**
     * Register admin hooks
     */
    private function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'handleFormSubmission']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
    }

    /**
     * Add admin menu
     */
    public function addAdminMenu(): void
    {
        add_submenu_page(
            'fp-performance-suite',
            __('Lighthouse Font Optimization', 'fp-performance-suite'),
            __('Lighthouse Fonts', 'fp-performance-suite'),
            'manage_options',
            'fp-lighthouse-font-optimization',
            [$this, 'renderPage']
        );
    }

    /**
     * Handle form submission
     */
    public function handleFormSubmission(): void
    {
        if (!isset($_POST['fp_lighthouse_font_nonce']) || 
            !wp_verify_nonce($_POST['fp_lighthouse_font_nonce'], 'fp_lighthouse_font_settings')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = [
            'enabled' => !empty($_POST['enabled']),
            'preload_critical_fonts' => !empty($_POST['preload_critical_fonts']),
            'inject_font_display' => !empty($_POST['inject_font_display']),
            'preconnect_providers' => !empty($_POST['preconnect_providers']),
            'optimize_site_fonts' => !empty($_POST['optimize_site_fonts']),
        ];

        // Gestisci font personalizzati
        if (!empty($_POST['custom_fonts'])) {
            $customFonts = [];
            $fontData = $_POST['custom_fonts'];
            
            for ($i = 0; $i < count($fontData['url']); $i++) {
                if (!empty($fontData['url'][$i])) {
                    $customFonts[] = [
                        'url' => sanitize_url($fontData['url'][$i]),
                        'type' => sanitize_text_field($fontData['type'][$i] ?? 'font/woff2'),
                        'crossorigin' => !empty($fontData['crossorigin'][$i]),
                        'priority' => sanitize_text_field($fontData['priority'][$i] ?? 'medium'),
                        'savings' => sanitize_text_field($fontData['savings'][$i] ?? '0ms')
                    ];
                }
            }
            
            $settings['custom_lighthouse_fonts'] = $customFonts;
        }

        $this->optimizer->updateSettings($settings);

        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 __('Lighthouse Font Optimization settings saved successfully!', 'fp-performance-suite') . 
                 '</p></div>';
        });
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueueAdminScripts(string $hook): void
    {
        if (strpos($hook, 'fp-lighthouse-font-optimization') === false) {
            return;
        }

        wp_enqueue_style('fp-lighthouse-font-admin', plugin_dir_url(__FILE__) . '../assets/css/lighthouse-font-admin.css', [], '1.0.0');
        wp_enqueue_script('fp-lighthouse-font-admin', plugin_dir_url(__FILE__) . '../assets/js/lighthouse-font-admin.js', ['jquery'], '1.0.0', true);
    }

    /**
     * Render admin page (required by Menu system)
     */
    public function render(): void
    {
        $this->renderPage();
    }

    /**
     * Render admin page
     */
    public function renderPage(): void
    {
        $settings = $this->optimizer->getSettings();
        $status = $this->optimizer->status();
        $totalSavings = $this->optimizer->getTotalExpectedSavings();
        $criticalFonts = $this->getCriticalFontsData();

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Lighthouse Font Optimization', 'fp-performance-suite'); ?></h1>
            
            <div class="fp-lighthouse-overview">
                <div class="fp-overview-card">
                    <h3><?php esc_html_e('🎯 Lighthouse Report Analysis', 'fp-performance-suite'); ?></h3>
                    <p><?php esc_html_e('Ottimizzazioni specifiche per i font identificati nel report Lighthouse con potenziale risparmio di 180ms.', 'fp-performance-suite'); ?></p>
                    
                    <div class="fp-metrics-grid">
                        <div class="fp-metric">
                            <span class="fp-metric-value"><?php echo esc_html($totalSavings); ?></span>
                            <span class="fp-metric-label"><?php esc_html_e('Risparmio Totale Stimato', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-metric">
                            <span class="fp-metric-value"><?php echo count($criticalFonts); ?></span>
                            <span class="fp-metric-label"><?php esc_html_e('Font Critici Identificati', 'fp-performance-suite'); ?></span>
                        </div>
                        <div class="fp-metric">
                            <span class="fp-metric-value"><?php echo $status['enabled'] ? '✅' : '❌'; ?></span>
                            <span class="fp-metric-label"><?php esc_html_e('Ottimizzazione Attiva', 'fp-performance-suite'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <form method="post" action="">
                <?php wp_nonce_field('fp_lighthouse_font_settings', 'fp_lighthouse_font_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Abilita Ottimizzazione Lighthouse', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?> />
                                <?php esc_html_e('Attiva le ottimizzazioni specifiche per i font identificati nel report Lighthouse', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Applica font-display: swap e preload per i font problematici identificati nell\'audit.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Preload Font Critici', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="preload_critical_fonts" value="1" <?php checked($settings['preload_critical_fonts']); ?> />
                                <?php esc_html_e('Precarica i font critici identificati nel report Lighthouse', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Riduce il tempo di caricamento dei font critici con fetchpriority="high".', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Iniezione Font Display CSS', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="inject_font_display" value="1" <?php checked($settings['inject_font_display']); ?> />
                                <?php esc_html_e('Inietta font-display: swap nel CSS per evitare FOIT/FOUT', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Aggiunge automaticamente font-display: swap ai font problematici.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Preconnect Provider Font', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="preconnect_providers" value="1" <?php checked($settings['preconnect_providers']); ?> />
                                <?php esc_html_e('Aggiungi preconnect ai provider dei font problematici', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Migliora la velocità di connessione ai provider di font esterni.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Ottimizzazione Font del Sito', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="optimize_site_fonts" value="1" <?php checked($settings['optimize_site_fonts']); ?> />
                                <?php esc_html_e('Ottimizza i font locali del sito', 'fp-performance-suite'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('Applica ottimizzazioni specifiche ai font caricati dal tema del sito.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <h3><?php esc_html_e('Font Critici Identificati nel Report Lighthouse', 'fp-performance-suite'); ?></h3>
                
                <div class="fp-lighthouse-fonts-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Font', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Tipo', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Risparmio Stimato', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Priorità', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Stato', 'fp-performance-suite'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($criticalFonts as $font): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($font['name']); ?></strong><br>
                                    <small><?php echo esc_html($font['url']); ?></small>
                                </td>
                                <td><?php echo esc_html($font['type']); ?></td>
                                <td>
                                    <span class="fp-savings-badge"><?php echo esc_html($font['savings']); ?></span>
                                </td>
                                <td>
                                    <span class="fp-priority-badge fp-priority-<?php echo esc_attr($font['priority']); ?>">
                                        <?php echo esc_html($font['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($font['exists']): ?>
                                        <span class="fp-status-ok">✅ <?php esc_html_e('Rilevato', 'fp-performance-suite'); ?></span>
                                    <?php else: ?>
                                        <span class="fp-status-missing">❌ <?php esc_html_e('Non trovato', 'fp-performance-suite'); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <h3><?php esc_html_e('Font Personalizzati', 'fp-performance-suite'); ?></h3>
                <p class="description">
                    <?php esc_html_e('Aggiungi font personalizzati identificati nel tuo report Lighthouse.', 'fp-performance-suite'); ?>
                </p>

                <div id="fp-custom-fonts-container">
                    <?php
                    $customFonts = $settings['custom_lighthouse_fonts'] ?? [];
                    if (empty($customFonts)) {
                        $customFonts = [['url' => '', 'type' => 'font/woff2', 'crossorigin' => false, 'priority' => 'medium', 'savings' => '0ms']];
                    }
                    
                    foreach ($customFonts as $index => $font):
                    ?>
                    <div class="fp-custom-font-row">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php esc_html_e('URL Font', 'fp-performance-suite'); ?></th>
                                <td>
                                    <input type="url" name="custom_fonts[url][]" value="<?php echo esc_attr($font['url']); ?>" 
                                           placeholder="https://example.com/font.woff2" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Tipo MIME', 'fp-performance-suite'); ?></th>
                                <td>
                                    <select name="custom_fonts[type][]">
                                        <option value="font/woff2" <?php selected($font['type'], 'font/woff2'); ?>>WOFF2</option>
                                        <option value="font/woff" <?php selected($font['type'], 'font/woff'); ?>>WOFF</option>
                                        <option value="font/ttf" <?php selected($font['type'], 'font/ttf'); ?>>TTF</option>
                                        <option value="font/otf" <?php selected($font['type'], 'font/otf'); ?>>OTF</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Priorità', 'fp-performance-suite'); ?></th>
                                <td>
                                    <select name="custom_fonts[priority][]">
                                        <option value="high" <?php selected($font['priority'], 'high'); ?>><?php esc_html_e('Alta', 'fp-performance-suite'); ?></option>
                                        <option value="medium" <?php selected($font['priority'], 'medium'); ?>><?php esc_html_e('Media', 'fp-performance-suite'); ?></option>
                                        <option value="low" <?php selected($font['priority'], 'low'); ?>><?php esc_html_e('Bassa', 'fp-performance-suite'); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Risparmio Stimato', 'fp-performance-suite'); ?></th>
                                <td>
                                    <input type="text" name="custom_fonts[savings][]" value="<?php echo esc_attr($font['savings']); ?>" 
                                           placeholder="180ms" class="small-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php esc_html_e('Crossorigin', 'fp-performance-suite'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="custom_fonts[crossorigin][]" value="1" <?php checked($font['crossorigin']); ?> />
                                        <?php esc_html_e('Richiede crossorigin', 'fp-performance-suite'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                        <button type="button" class="button fp-remove-font-row"><?php esc_html_e('Rimuovi Font', 'fp-performance-suite'); ?></button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <button type="button" id="fp-add-custom-font" class="button"><?php esc_html_e('Aggiungi Font Personalizzato', 'fp-performance-suite'); ?></button>

                <?php submit_button(__('Salva Impostazioni', 'fp-performance-suite')); ?>
            </form>

            <div class="fp-lighthouse-info">
                <h3><?php esc_html_e('ℹ️ Informazioni Lighthouse Font Optimization', 'fp-performance-suite'); ?></h3>
                <div class="fp-info-grid">
                    <div class="fp-info-card">
                        <h4><?php esc_html_e('Font Display', 'fp-performance-suite'); ?></h4>
                        <p><?php esc_html_e('Imposta font-display: swap per evitare FOIT (Flash of Invisible Text) e migliorare la percezione della velocità.', 'fp-performance-suite'); ?></p>
                    </div>
                    <div class="fp-info-card">
                        <h4><?php esc_html_e('Preload Critico', 'fp-performance-suite'); ?></h4>
                        <p><?php esc_html_e('Precarica i font critici con alta priorità per ridurre il tempo di caricamento e il render delay.', 'fp-performance-suite'); ?></p>
                    </div>
                    <div class="fp-info-card">
                        <h4><?php esc_html_e('Preconnect Provider', 'fp-performance-suite'); ?></h4>
                        <p><?php esc_html_e('Stabilisce connessioni anticipate ai provider di font esterni per ridurre la latenza.', 'fp-performance-suite'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .fp-lighthouse-overview {
            margin: 20px 0;
        }
        
        .fp-overview-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .fp-metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .fp-metric {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .fp-metric-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #0073aa;
        }
        
        .fp-metric-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .fp-lighthouse-fonts-table {
            margin: 20px 0;
        }
        
        .fp-savings-badge {
            background: #00a32a;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .fp-priority-badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .fp-priority-high {
            background: #d63638;
            color: white;
        }
        
        .fp-priority-medium {
            background: #dba617;
            color: white;
        }
        
        .fp-priority-low {
            background: #00a32a;
            color: white;
        }
        
        .fp-status-ok {
            color: #00a32a;
            font-weight: bold;
        }
        
        .fp-status-missing {
            color: #d63638;
            font-weight: bold;
        }
        
        .fp-custom-font-row {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .fp-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .fp-info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Aggiungi font personalizzato
            $('#fp-add-custom-font').on('click', function() {
                var template = `
                    <div class="fp-custom-font-row">
                        <table class="form-table">
                            <tr>
                                <th scope="row">URL Font</th>
                                <td>
                                    <input type="url" name="custom_fonts[url][]" value="" 
                                           placeholder="https://example.com/font.woff2" class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Tipo MIME</th>
                                <td>
                                    <select name="custom_fonts[type][]">
                                        <option value="font/woff2">WOFF2</option>
                                        <option value="font/woff">WOFF</option>
                                        <option value="font/ttf">TTF</option>
                                        <option value="font/otf">OTF</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Priorità</th>
                                <td>
                                    <select name="custom_fonts[priority][]">
                                        <option value="high">Alta</option>
                                        <option value="medium" selected>Media</option>
                                        <option value="low">Bassa</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Risparmio Stimato</th>
                                <td>
                                    <input type="text" name="custom_fonts[savings][]" value="" 
                                           placeholder="180ms" class="small-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">Crossorigin</th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="custom_fonts[crossorigin][]" value="1" />
                                        Richiede crossorigin
                                    </label>
                                </td>
                            </tr>
                        </table>
                        <button type="button" class="button fp-remove-font-row">Rimuovi Font</button>
                    </div>
                `;
                
                $('#fp-custom-fonts-container').append(template);
            });
            
            // Rimuovi font personalizzato
            $(document).on('click', '.fp-remove-font-row', function() {
                $(this).closest('.fp-custom-font-row').remove();
            });
        });
        </script>
        <?php
    }

    /**
     * Ottiene i dati dei font critici
     */
    private function getCriticalFontsData(): array
    {
        return [
            [
                'name' => 'Gill Sans Light',
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/useanyfont/939GillSans-Light.woff2'),
                'type' => 'WOFF2',
                'savings' => '180ms',
                'priority' => 'high',
                'exists' => file_exists(get_stylesheet_directory() . '/fonts/useanyfont/939GillSans-Light.woff2')
            ],
            [
                'name' => 'Gill Sans Regular',
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/useanyfont/2090GillSans.woff2'),
                'type' => 'WOFF2',
                'savings' => '150ms',
                'priority' => 'high',
                'exists' => file_exists(get_stylesheet_directory() . '/fonts/useanyfont/2090GillSans.woff2')
            ],
            [
                'name' => 'FontAwesome 4.2',
                'url' => home_url('/wp-content/themes/' . get_stylesheet() . '/fonts/fontawesome-webfont.woff'),
                'type' => 'WOFF',
                'savings' => '130ms',
                'priority' => 'medium',
                'exists' => file_exists(get_stylesheet_directory() . '/fonts/fontawesome-webfont.woff')
            ],
            [
                'name' => 'FontAwesome Brands',
                'url' => 'https://use.fontawesome.com/releases/v6.0.0/webfonts/fa-brands-400.woff2',
                'type' => 'WOFF2',
                'savings' => '30ms',
                'priority' => 'low',
                'exists' => true
            ],
            [
                'name' => 'FontAwesome Solid',
                'url' => 'https://use.fontawesome.com/releases/v6.0.0/webfonts/fa-solid-900.woff2',
                'type' => 'WOFF2',
                'savings' => '20ms',
                'priority' => 'low',
                'exists' => true
            ]
        ];
    }
}
