<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Admin\BackendOptimizer;
use FP\PerfSuite\Admin\Components\StatusIndicator;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function selected;
use function sanitize_text_field;
use function wp_nonce_field;
use function wp_unslash;

class Backend extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-backend';
    }

    public function title(): string
    {
        return __('Ottimizzazione Backend', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Ottimizzazione', 'fp-performance-suite'), __('Backend', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $optimizer = $this->container->get(BackendOptimizer::class);
        $message = '';

        // Gestione form submission
        if ('POST' === $_SERVER['REQUEST_METHOD'] && 
            isset($_POST['fp_ps_backend_nonce']) && 
            wp_verify_nonce(wp_unslash($_POST['fp_ps_backend_nonce']), 'fp-ps-backend')) {
            
            $newOptions = [
                // Heartbeat
                'heartbeat_enabled' => !empty($_POST['heartbeat_enabled']),
                'heartbeat_location_dashboard' => sanitize_text_field($_POST['heartbeat_location_dashboard'] ?? 'default'),
                'heartbeat_location_frontend' => sanitize_text_field($_POST['heartbeat_location_frontend'] ?? 'disable'),
                'heartbeat_location_editor' => sanitize_text_field($_POST['heartbeat_location_editor'] ?? 'default'),
                'heartbeat_interval' => max(15, (int) ($_POST['heartbeat_interval'] ?? 60)),
                
                // Post Revisions
                'limit_post_revisions' => !empty($_POST['limit_post_revisions']),
                'post_revisions_limit' => max(1, (int) ($_POST['post_revisions_limit'] ?? 5)),
                
                // Autosave
                'autosave_interval' => max(60, (int) ($_POST['autosave_interval'] ?? 120)),
                
                // Dashboard Widgets
                'disable_dashboard_widgets' => !empty($_POST['disable_dashboard_widgets']),
                
                // Admin Scripts
                'disable_admin_scripts' => !empty($_POST['disable_admin_scripts']),
                
                // Admin Notices
                'disable_admin_notices' => !empty($_POST['disable_admin_notices']),
                
                // AJAX Optimization
                'optimize_admin_ajax' => !empty($_POST['optimize_admin_ajax']),
                
                // Items per page
                'limit_admin_items' => !empty($_POST['limit_admin_items']),
                'items_per_page' => max(10, min(50, (int) ($_POST['items_per_page'] ?? 20))),
            ];

            if ($optimizer->updateOptions($newOptions)) {
                $message = __('Impostazioni salvate con successo!', 'fp-performance-suite');
            } else {
                $message = __('Errore durante il salvataggio delle impostazioni.', 'fp-performance-suite');
            }
        }

        // Reset to defaults
        if ('POST' === $_SERVER['REQUEST_METHOD'] && 
            isset($_POST['fp_ps_backend_reset']) && 
            wp_verify_nonce(wp_unslash($_POST['fp_ps_backend_reset']), 'fp-ps-backend-reset')) {
            
            if ($optimizer->resetToDefaults()) {
                $message = __('Impostazioni ripristinate ai valori predefiniti.', 'fp-performance-suite');
            }
        }

        $options = $optimizer->getOptions();
        $stats = $optimizer->getStats();
        $recommendations = $optimizer->getRecommendations();

        ob_start();
        ?>

        <?php if ($message): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <div class="fp-perf-backend-header" style="background: #fff; padding: 20px; margin-bottom: 20px; border-left: 4px solid #2271b1;">
            <h2 style="margin-top: 0;">🚀 <?php esc_html_e('Ottimizzazione Backend di WordPress', 'fp-performance-suite'); ?></h2>
            <p style="font-size: 15px; color: #555;">
                <?php esc_html_e('Migliora le performance dell\'area amministrativa riducendo script non necessari, controllando il Heartbeat API e ottimizzando varie funzionalità di WordPress.', 'fp-performance-suite'); ?>
            </p>
            
            <div class="fp-ps-status-overview">
                <?php
                // Determina lo stato Heartbeat
                $heartbeatStatus = $stats['heartbeat_status'] === 'active' ? 'success' : 'inactive';
                echo StatusIndicator::renderCard(
                    $heartbeatStatus,
                    __('Heartbeat API', 'fp-performance-suite'),
                    __('Controllo richieste AJAX periodiche', 'fp-performance-suite'),
                    ucfirst($stats['heartbeat_status'])
                );
                
                // Limiti revisioni
                $revisionsStatus = $stats['post_revisions_limit'] <= 5 ? 'success' : ($stats['post_revisions_limit'] <= 10 ? 'warning' : 'error');
                echo StatusIndicator::renderCard(
                    $revisionsStatus,
                    __('Revisioni Post', 'fp-performance-suite'),
                    __('Limite revisioni memorizzate', 'fp-performance-suite'),
                    $stats['post_revisions_limit']
                );
                
                // Intervallo autosave
                $autosaveStatus = $stats['autosave_interval'] >= 120 ? 'success' : 'warning';
                echo StatusIndicator::renderCard(
                    $autosaveStatus,
                    __('Intervallo Autosave', 'fp-performance-suite'),
                    __('Frequenza salvataggio automatico', 'fp-performance-suite'),
                    $stats['autosave_interval']
                );
                
                // Ottimizzazioni attive
                $optimizationsPercentage = ($stats['optimizations_active'] / 7) * 100;
                $optimizationsStatus = StatusIndicator::autoStatus($optimizationsPercentage, 70, 40);
                echo StatusIndicator::renderCard(
                    $optimizationsStatus,
                    __('Ottimizzazioni Attive', 'fp-performance-suite'),
                    __('Funzionalità abilitate', 'fp-performance-suite'),
                    $stats['optimizations_active'] . '/7'
                );
                ?>
            </div>
        </div>

        <?php if (!empty($recommendations)): ?>
            <div class="notice notice-info" style="margin-bottom: 20px;">
                <h3><?php esc_html_e('💡 Raccomandazioni:', 'fp-performance-suite'); ?></h3>
                <ul style="margin: 10px 0; padding-left: 25px;">
                    <?php foreach ($recommendations as $rec): ?>
                        <li><?php echo esc_html($rec); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field('fp-ps-backend', 'fp_ps_backend_nonce'); ?>

            <!-- Heartbeat Control -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">💓 <?php esc_html_e('WordPress Heartbeat API', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Il Heartbeat API è utilizzato per l\'autosalvataggio, notifiche e altre funzioni real-time. Limitarlo può ridurre il carico del server del 20-30%.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="heartbeat_enabled">
                                    <?php esc_html_e('Abilita Controllo Heartbeat', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="heartbeat_enabled" id="heartbeat_enabled" value="1" 
                                    <?php checked($options['heartbeat_enabled']); ?>>
                                <p class="description">
                                    <?php esc_html_e('Attiva il controllo granulare del Heartbeat API per ridurre le richieste AJAX.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="heartbeat-settings" style="<?php echo !$options['heartbeat_enabled'] ? 'display:none;' : ''; ?>">
                            <th scope="row">
                                <label for="heartbeat_location_dashboard">
                                    <?php esc_html_e('Dashboard', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <select name="heartbeat_location_dashboard" id="heartbeat_location_dashboard">
                                    <option value="default" <?php selected($options['heartbeat_location_dashboard'], 'default'); ?>>
                                        <?php esc_html_e('Default (15s)', 'fp-performance-suite'); ?>
                                    </option>
                                    <option value="slow" <?php selected($options['heartbeat_location_dashboard'], 'slow'); ?>>
                                        <?php esc_html_e('Rallentato', 'fp-performance-suite'); ?>
                                    </option>
                                    <option value="disable" <?php selected($options['heartbeat_location_dashboard'], 'disable'); ?>>
                                        <?php esc_html_e('Disabilitato', 'fp-performance-suite'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr class="heartbeat-settings" style="<?php echo !$options['heartbeat_enabled'] ? 'display:none;' : ''; ?>">
                            <th scope="row">
                                <label for="heartbeat_location_frontend">
                                    <?php esc_html_e('Frontend', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <select name="heartbeat_location_frontend" id="heartbeat_location_frontend">
                                    <option value="default" <?php selected($options['heartbeat_location_frontend'], 'default'); ?>>
                                        <?php esc_html_e('Default (15s)', 'fp-performance-suite'); ?>
                                    </option>
                                    <option value="slow" <?php selected($options['heartbeat_location_frontend'], 'slow'); ?>>
                                        <?php esc_html_e('Rallentato', 'fp-performance-suite'); ?>
                                    </option>
                                    <option value="disable" <?php selected($options['heartbeat_location_frontend'], 'disable'); ?>>
                                        <?php esc_html_e('Disabilitato (Raccomandato)', 'fp-performance-suite'); ?>
                                    </option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr class="heartbeat-settings" style="<?php echo !$options['heartbeat_enabled'] ? 'display:none;' : ''; ?>">
                            <th scope="row">
                                <label for="heartbeat_location_editor">
                                    <?php esc_html_e('Editor Post', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <select name="heartbeat_location_editor" id="heartbeat_location_editor">
                                    <option value="default" <?php selected($options['heartbeat_location_editor'], 'default'); ?>>
                                        <?php esc_html_e('Default (15s)', 'fp-performance-suite'); ?>
                                    </option>
                                    <option value="slow" <?php selected($options['heartbeat_location_editor'], 'slow'); ?>>
                                        <?php esc_html_e('Rallentato', 'fp-performance-suite'); ?>
                                    </option>
                                    <option value="disable" <?php selected($options['heartbeat_location_editor'], 'disable'); ?>>
                                        <?php esc_html_e('Disabilitato', 'fp-performance-suite'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('⚠️ Attenzione: disabilitare nell\'editor potrebbe impedire l\'autosalvataggio automatico.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="heartbeat-settings" style="<?php echo !$options['heartbeat_enabled'] ? 'display:none;' : ''; ?>">
                            <th scope="row">
                                <label for="heartbeat_interval">
                                    <?php esc_html_e('Intervallo (secondi)', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" name="heartbeat_interval" id="heartbeat_interval" 
                                    value="<?php echo esc_attr($options['heartbeat_interval']); ?>" 
                                    min="15" max="300" step="15" class="small-text">
                                <p class="description">
                                    <?php esc_html_e('Intervallo personalizzato per heartbeat "rallentato". Default WordPress: 15s, Raccomandato: 60s.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Post Revisions -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">📝 <?php esc_html_e('Revisioni Post', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Limita il numero di revisioni salvate per ogni post per ridurre la dimensione del database.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="limit_post_revisions">
                                    <?php esc_html_e('Limita Revisioni', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="limit_post_revisions" id="limit_post_revisions" value="1" 
                                    <?php checked($options['limit_post_revisions']); ?>>
                                <p class="description">
                                    <?php esc_html_e('Attiva il limite di revisioni per ogni post.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="revision-settings" style="<?php echo !$options['limit_post_revisions'] ? 'display:none;' : ''; ?>">
                            <th scope="row">
                                <label for="post_revisions_limit">
                                    <?php esc_html_e('Numero Massimo Revisioni', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" name="post_revisions_limit" id="post_revisions_limit" 
                                    value="<?php echo esc_attr($options['post_revisions_limit']); ?>" 
                                    min="1" max="20" class="small-text">
                                <p class="description">
                                    <?php esc_html_e('Raccomandato: 3-5 revisioni. WordPress di default: illimitate.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Autosave -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">💾 <?php esc_html_e('Autosalvataggio', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Configura l\'intervallo di autosalvataggio automatico dei post.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="autosave_interval">
                                    <?php esc_html_e('Intervallo Autosave (secondi)', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" name="autosave_interval" id="autosave_interval" 
                                    value="<?php echo esc_attr($options['autosave_interval']); ?>" 
                                    min="60" max="600" step="30" class="small-text">
                                <p class="description">
                                    <?php esc_html_e('WordPress di default: 60s. Raccomandato: 120s per ridurre le richieste AJAX.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Dashboard Widgets -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">📊 <?php esc_html_e('Widget Dashboard', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Rimuovi widget non necessari dalla dashboard per velocizzare il caricamento.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="disable_dashboard_widgets">
                                    <?php esc_html_e('Rimuovi Widget Non Essenziali', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="disable_dashboard_widgets" id="disable_dashboard_widgets" value="1" 
                                    <?php checked($options['disable_dashboard_widgets']); ?>>
                                <p class="description">
                                    <?php esc_html_e('Rimuove widget di WordPress, Yoast SEO, WooCommerce, Jetpack, ecc.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Admin Scripts -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">📜 <?php esc_html_e('Script Admin', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Riduci il carico di script nell\'area amministrativa.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="disable_admin_scripts">
                                    <?php esc_html_e('Ottimizza Script Admin', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="disable_admin_scripts" id="disable_admin_scripts" value="1" 
                                    <?php checked($options['disable_admin_scripts']); ?>>
                                <p class="description">
                                    <?php esc_html_e('Rimuove script non necessari in base al contesto della pagina admin.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Admin Notices -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">🔕 <?php esc_html_e('Notifiche Admin', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Nascondi notifiche non critiche di plugin di terze parti.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="disable_admin_notices">
                                    <?php esc_html_e('Nascondi Notifiche Non Critiche', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="disable_admin_notices" id="disable_admin_notices" value="1" 
                                    <?php checked($options['disable_admin_notices']); ?>>
                                <p class="description">
                                    <?php esc_html_e('Le notifiche di errore critiche di WordPress verranno comunque visualizzate.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- AJAX Optimization -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">⚡ <?php esc_html_e('Ottimizzazione AJAX', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Ottimizza le richieste AJAX nell\'area admin.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="optimize_admin_ajax">
                                    <?php esc_html_e('Ottimizza AJAX Admin', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="optimize_admin_ajax" id="optimize_admin_ajax" value="1" 
                                    <?php checked($options['optimize_admin_ajax']); ?>>
                                <p class="description">
                                    <?php esc_html_e('Aumenta il limite di memoria per operazioni admin pesanti.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Items per Page -->
            <div class="postbox" style="margin-bottom: 20px;">
                <div class="postbox-header">
                    <h2 class="hndle">📋 <?php esc_html_e('Elementi per Pagina', 'fp-performance-suite'); ?></h2>
                </div>
                <div class="inside">
                    <p class="description">
                        <?php esc_html_e('Limita il numero di elementi visualizzati per pagina nelle liste admin.', 'fp-performance-suite'); ?>
                    </p>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="limit_admin_items">
                                    <?php esc_html_e('Limita Elementi', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" name="limit_admin_items" id="limit_admin_items" value="1" 
                                    <?php checked($options['limit_admin_items']); ?>>
                                <p class="description">
                                    <?php esc_html_e('Limita elementi in liste post, pagine e commenti.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr class="items-settings" style="<?php echo !$options['limit_admin_items'] ? 'display:none;' : ''; ?>">
                            <th scope="row">
                                <label for="items_per_page">
                                    <?php esc_html_e('Numero Elementi', 'fp-performance-suite'); ?>
                                </label>
                            </th>
                            <td>
                                <input type="number" name="items_per_page" id="items_per_page" 
                                    value="<?php echo esc_attr($options['items_per_page']); ?>" 
                                    min="10" max="50" class="small-text">
                                <p class="description">
                                    <?php esc_html_e('Raccomandato: 20. Min: 10, Max: 50.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <p class="submit">
                <button type="submit" class="button button-primary button-large">
                    <?php esc_html_e('Salva Modifiche', 'fp-performance-suite'); ?>
                </button>
            </p>
        </form>

        <!-- Reset Form -->
        <form method="post" action="" style="margin-top: 20px;">
            <?php wp_nonce_field('fp-ps-backend-reset', 'fp_ps_backend_reset'); ?>
            <p class="submit">
                <button type="submit" class="button button-secondary" 
                    onclick="return confirm('<?php esc_attr_e('Sei sicuro di voler ripristinare tutte le impostazioni ai valori predefiniti?', 'fp-performance-suite'); ?>');">
                    <?php esc_html_e('Ripristina Impostazioni Predefinite', 'fp-performance-suite'); ?>
                </button>
            </p>
        </form>

        <script>
        jQuery(document).ready(function($) {
            // Toggle heartbeat settings
            $('#heartbeat_enabled').on('change', function() {
                $('.heartbeat-settings').toggle(this.checked);
            });
            
            // Toggle revision settings
            $('#limit_post_revisions').on('change', function() {
                $('.revision-settings').toggle(this.checked);
            });
            
            // Toggle items settings
            $('#limit_admin_items').on('change', function() {
                $('.items-settings').toggle(this.checked);
            });
        });
        </script>

        <?php
        return ob_get_clean();
    }
}

