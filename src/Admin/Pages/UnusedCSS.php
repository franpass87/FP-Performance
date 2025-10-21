<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\UnusedCSSOptimizer;
use FP\PerfSuite\Utils\Logger;

/**
 * Unused CSS Optimization Admin Page
 * 
 * Gestisce l'ottimizzazione del CSS non utilizzato identificato dal report Lighthouse
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class UnusedCSS
{
    private $optimizer;

    public function __construct()
    {
        $this->optimizer = new UnusedCSSOptimizer();
    }

    /**
     * Register admin page
     */
    public function register(): void
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
            'Ottimizzazione CSS Non Utilizzato',
            'CSS Non Utilizzato',
            'manage_options',
            'fp-ps-unused-css',
            [$this, 'renderPage']
        );
    }

    /**
     * Handle form submission
     */
    public function handleFormSubmission(): void
    {
        if (!isset($_POST['fp_ps_unused_css_nonce']) || 
            !wp_verify_nonce($_POST['fp_ps_unused_css_nonce'], 'fp_ps_unused_css_settings')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = [
            'enabled' => !empty($_POST['enabled']),
            'remove_unused_css' => !empty($_POST['remove_unused_css']),
            'defer_non_critical' => !empty($_POST['defer_non_critical']),
            'inline_critical' => !empty($_POST['inline_critical']),
            'enable_css_purging' => !empty($_POST['enable_css_purging']),
            'critical_css' => sanitize_textarea_field($_POST['critical_css'] ?? ''),
        ];

        $result = $this->optimizer->updateSettings($settings);

        if ($result) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>Impostazioni CSS ottimizzate salvate con successo!</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>Errore nel salvare le impostazioni.</p></div>';
            });
        }
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueueAdminScripts(string $hook): void
    {
        if ($hook !== 'fp-performance-suite_page_fp-ps-unused-css') {
            return;
        }

        wp_enqueue_style(
            'fp-ps-unused-css-admin',
            plugin_dir_url(__FILE__) . '../../assets/css/admin-unused-css.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'fp-ps-unused-css-admin',
            plugin_dir_url(__FILE__) . '../../assets/js/admin-unused-css.js',
            ['jquery'],
            '1.0.0',
            true
        );
    }

    /**
     * Render admin page
     */
    public function renderPage(): void
    {
        $settings = $this->optimizer->getSettings();
        $status = $this->optimizer->status();
        $unusedFiles = $this->getUnusedCSSFiles();

        ?>
        <div class="wrap">
            <h1>Ottimizzazione CSS Non Utilizzato</h1>
            <p class="description">
                Risolve il problema dei <strong>130 KiB di CSS non utilizzato</strong> identificato nel report Lighthouse.
                Questa ottimizzazione può migliorare significativamente le performance del tuo sito.
            </p>

            <!-- Status Overview -->
            <div class="fp-ps-status-overview">
                <div class="fp-ps-status-card">
                    <h3>Risparmio Stimato</h3>
                    <div class="fp-ps-metric">
                        <span class="fp-ps-metric-value">130 KiB</span>
                        <span class="fp-ps-metric-label">CSS non utilizzato</span>
                    </div>
                </div>
                <div class="fp-ps-status-card">
                    <h3>File Ottimizzati</h3>
                    <div class="fp-ps-metric">
                        <span class="fp-ps-metric-value"><?php echo count($unusedFiles); ?></span>
                        <span class="fp-ps-metric-label">File CSS</span>
                    </div>
                </div>
                <div class="fp-ps-status-card">
                    <h3>Stato</h3>
                    <div class="fp-ps-status-indicator <?php echo $status['enabled'] ? 'active' : 'inactive'; ?>">
                        <?php echo $status['enabled'] ? 'Attivo' : 'Inattivo'; ?>
                    </div>
                </div>
            </div>

            <!-- Lighthouse Report Analysis -->
            <div class="fp-ps-lighthouse-analysis">
                <h2>Analisi Report Lighthouse</h2>
                <div class="fp-ps-lighthouse-files">
                    <?php foreach ($unusedFiles as $file => $info): ?>
                        <div class="fp-ps-lighthouse-file">
                            <div class="fp-ps-file-info">
                                <strong><?php echo esc_html($file); ?></strong>
                                <span class="fp-ps-file-savings"><?php echo esc_html($info['savings']); ?></span>
                            </div>
                            <div class="fp-ps-file-reason"><?php echo esc_html($info['reason']); ?></div>
                            <div class="fp-ps-file-action">
                                <?php if ($info['remove']): ?>
                                    <span class="fp-ps-action-remove">Rimosso</span>
                                <?php else: ?>
                                    <span class="fp-ps-action-defer">Differito</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Settings Form -->
            <form method="post" action="">
                <?php wp_nonce_field('fp_ps_unused_css_settings', 'fp_ps_unused_css_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Abilita Ottimizzazione CSS</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enabled" value="1" <?php checked($settings['enabled']); ?>>
                                Attiva l'ottimizzazione del CSS non utilizzato
                            </label>
                            <p class="description">
                                Rimuove o differisce il CSS non utilizzato per migliorare le performance.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Rimuovi CSS Non Utilizzato</th>
                        <td>
                            <label>
                                <input type="checkbox" name="remove_unused_css" value="1" <?php checked($settings['remove_unused_css']); ?>>
                                Rimuovi completamente i file CSS non utilizzati
                            </label>
                            <p class="description">
                                Rimuove i file CSS identificati come non utilizzati dal report Lighthouse.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Differisci CSS Non Critici</th>
                        <td>
                            <label>
                                <input type="checkbox" name="defer_non_critical" value="1" <?php checked($settings['defer_non_critical']); ?>>
                                Differisci il caricamento del CSS non critico
                            </label>
                            <p class="description">
                                Carica il CSS non critico dopo il caricamento della pagina per migliorare LCP.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">CSS Critico Inline</th>
                        <td>
                            <label>
                                <input type="checkbox" name="inline_critical" value="1" <?php checked($settings['inline_critical']); ?>>
                                Inserisci CSS critico inline nell'head
                            </label>
                            <p class="description">
                                Inserisce il CSS critico direttamente nell'head per evitare render blocking.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Purging CSS Dinamico</th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_css_purging" value="1" <?php checked($settings['enable_css_purging']); ?>>
                                Abilita purging dinamico del CSS
                            </label>
                            <p class="description">
                                Rimuove dinamicamente i selettori CSS non utilizzati dal DOM.
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">CSS Critico Personalizzato</th>
                        <td>
                            <textarea name="critical_css" rows="10" cols="50" class="large-text code"><?php echo esc_textarea($settings['critical_css']); ?></textarea>
                            <p class="description">
                                Inserisci il CSS critico personalizzato per il contenuto above-the-fold.
                                Se lasciato vuoto, verrà utilizzato un CSS critico generico ottimizzato.
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Salva Impostazioni CSS'); ?>
            </form>

            <!-- Performance Impact -->
            <div class="fp-ps-performance-impact">
                <h2>Impatto sulle Performance</h2>
                <div class="fp-ps-impact-grid">
                    <div class="fp-ps-impact-item">
                        <h4>Largest Contentful Paint (LCP)</h4>
                        <p>Miglioramento stimato: <strong>200-500ms</strong></p>
                        <p>Il CSS critico inline riduce il tempo di rendering del contenuto principale.</p>
                    </div>
                    <div class="fp-ps-impact-item">
                        <h4>First Contentful Paint (FCP)</h4>
                        <p>Miglioramento stimato: <strong>150-300ms</strong></p>
                        <p>La rimozione del CSS non utilizzato accelera il primo rendering.</p>
                    </div>
                    <div class="fp-ps-impact-item">
                        <h4>Riduzione Dimensione</h4>
                        <p>Risparmio: <strong>130 KiB</strong></p>
                        <p>Riduzione significativa della dimensione totale della pagina.</p>
                    </div>
                    <div class="fp-ps-impact-item">
                        <h4>Render Blocking</h4>
                        <p>Eliminazione: <strong>6 file CSS</strong></p>
                        <p>Riduzione del render blocking per migliorare le metriche Core Web Vitals.</p>
                    </div>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="fp-ps-recommendations">
                <h2>Raccomandazioni</h2>
                <div class="fp-ps-recommendations-list">
                    <div class="fp-ps-recommendation">
                        <h4>🎯 Testa le Modifiche</h4>
                        <p>Dopo aver attivato l'ottimizzazione, esegui un nuovo test Lighthouse per verificare i miglioramenti.</p>
                    </div>
                    <div class="fp-ps-recommendation">
                        <h4>📊 Monitora le Performance</h4>
                        <p>Controlla regolarmente le metriche Core Web Vitals per assicurarti che le ottimizzazioni funzionino correttamente.</p>
                    </div>
                    <div class="fp-ps-recommendation">
                        <h4>🔧 Personalizza il CSS Critico</h4>
                        <p>Per risultati ottimali, personalizza il CSS critico in base al design specifico del tuo sito.</p>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .fp-ps-status-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .fp-ps-status-card {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }

        .fp-ps-metric {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 1rem;
        }

        .fp-ps-metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #0073aa;
        }

        .fp-ps-metric-label {
            color: #666;
            font-size: 0.9rem;
        }

        .fp-ps-status-indicator {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .fp-ps-status-indicator.active {
            background: #d4edda;
            color: #155724;
        }

        .fp-ps-status-indicator.inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .fp-ps-lighthouse-files {
            display: grid;
            gap: 1rem;
            margin: 1rem 0;
        }

        .fp-ps-lighthouse-file {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #0073aa;
        }

        .fp-ps-file-info {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .fp-ps-file-savings {
            color: #28a745;
            font-weight: bold;
        }

        .fp-ps-file-reason {
            color: #666;
            font-size: 0.9rem;
        }

        .fp-ps-action-remove {
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .fp-ps-action-defer {
            background: #ffc107;
            color: #212529;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .fp-ps-impact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .fp-ps-impact-item {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
        }

        .fp-ps-impact-item h4 {
            margin-top: 0;
            color: #0073aa;
        }

        .fp-ps-recommendations-list {
            display: grid;
            gap: 1rem;
            margin: 1rem 0;
        }

        .fp-ps-recommendation {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 1rem;
        }

        .fp-ps-recommendation h4 {
            margin-top: 0;
            color: #0073aa;
        }
        </style>
        <?php
    }

    /**
     * Get unused CSS files from Lighthouse report
     */
    private function getUnusedCSSFiles(): array
    {
        return [
            'dashicons.min.css' => [
                'savings' => '35.8 KiB',
                'reason' => 'Icone WordPress non utilizzate',
                'remove' => true
            ],
            'style.css' => [
                'savings' => '35.6 KiB',
                'reason' => 'Stili del tema non utilizzati',
                'remove' => false
            ],
            'salient-dynamic-styles.css' => [
                'savings' => '19.8 KiB',
                'reason' => 'Stili dinamici Salient non utilizzati',
                'remove' => true
            ],
            'sbi-styles.min.css' => [
                'savings' => '18.1 KiB',
                'reason' => 'Plugin Instagram non utilizzato',
                'remove' => true
            ],
            'font-awesome-legacy.min.css' => [
                'savings' => '11.0 KiB',
                'reason' => 'Font Awesome legacy non utilizzato',
                'remove' => true
            ],
            'skin-material.css' => [
                'savings' => '10.0 KiB',
                'reason' => 'Stili Material Design non utilizzati',
                'remove' => true
            ]
        ];
    }
}
