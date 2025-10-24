<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Admin\Pages\BasePage;
use FP\PerfSuite\Services\Assets\ExternalResourceCacheManager;

/**
 * External Cache Admin Page
 * 
 * Pagina admin per gestire la cache delle risorse esterne
 *
 * @package FP\PerfSuite\Admin\Pages
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ExternalCache extends BasePage
{
    private ExternalResourceCacheManager $cacheManager;

    public function __construct()
    {
        $this->cacheManager = new ExternalResourceCacheManager();
    }

    /**
     * Registra la pagina
     */
    public function register(): void
    {
        add_submenu_page(
            'fp-performance',
            __('External Cache', 'fp-performance'),
            __('🌐 External Cache', 'fp-performance'),
            'manage_options',
            'fp-external-cache',
            [$this, 'render']
        );
    }

    /**
     * Renderizza la pagina
     */
    public function render(): void
    {
        $this->handleFormSubmission();
        $settings = $this->cacheManager->getSettings();
        $stats = $this->cacheManager->getCacheStats();
        $resources = $this->cacheManager->detectExternalResources();
        
        ?>
        <div class="wrap">
            <h1><?php _e('🌐 External Resource Cache', 'fp-performance'); ?></h1>
            <p class="description">
                <?php _e('Gestisci gli header di cache per risorse esterne (JavaScript, CSS, font) per migliorare le performance e risolvere problemi di TTL inefficienti.', 'fp-performance'); ?>
            </p>

            <?php $this->renderStatusCards($stats); ?>
            <?php $this->renderResourceTable($resources); ?>
            <?php $this->renderSettingsForm($settings); ?>
        </div>
        <?php
    }

    /**
     * Gestisce l'invio del form
     */
    private function handleFormSubmission(): void
    {
        if (!isset($_POST['fp_external_cache_nonce']) || !wp_verify_nonce($_POST['fp_external_cache_nonce'], 'fp_external_cache_save')) {
            return;
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Non hai i permessi per eseguire questa azione.', 'fp-performance'));
        }

        $settings = [
            'enabled' => !empty($_POST['enabled']),
            'js_ttl' => (int) ($_POST['js_ttl'] ?? 31536000),
            'css_ttl' => (int) ($_POST['css_ttl'] ?? 31536000),
            'font_ttl' => (int) ($_POST['font_ttl'] ?? 31536000),
            'image_ttl' => (int) ($_POST['image_ttl'] ?? 31536000),
            'aggressive_mode' => !empty($_POST['aggressive_mode']),
            'preload_critical' => !empty($_POST['preload_critical']),
            'cache_control_headers' => !empty($_POST['cache_control_headers']),
            'custom_domains' => array_filter(array_map('sanitize_text_field', $_POST['custom_domains'] ?? [])),
            'exclude_domains' => array_filter(array_map('sanitize_text_field', $_POST['exclude_domains'] ?? [])),
        ];

        $this->cacheManager->updateSettings($settings);

        echo '<div class="notice notice-success"><p>' . __('Impostazioni salvate con successo!', 'fp-performance') . '</p></div>';
    }

    /**
     * Renderizza le card di stato
     */
    private function renderStatusCards(array $stats): void
    {
        ?>
        <div class="fp-status-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1rem 0;">
            <div class="fp-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #333;">📊 Risorse Totali</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #0073aa;"><?php echo $stats['total_resources']; ?></div>
            </div>
            
            <div class="fp-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #333;">✅ In Cache</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #46b450;"><?php echo $stats['cached_resources']; ?></div>
            </div>
            
            <div class="fp-card" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1rem;">
                <h3 style="margin: 0 0 0.5rem 0; color: #333;">📈 Ratio Cache</h3>
                <div style="font-size: 2rem; font-weight: bold; color: #00a0d2;"><?php echo $stats['cache_ratio']; ?>%</div>
            </div>
        </div>
        <?php
    }

    /**
     * Renderizza la tabella delle risorse
     */
    private function renderResourceTable(array $resources): void
    {
        ?>
        <div class="fp-resources-section" style="margin: 2rem 0;">
            <h2><?php _e('🔍 Risorse Esterne Rilevate', 'fp-performance'); ?></h2>
            
            <?php if (empty($resources['js']) && empty($resources['css']) && empty($resources['fonts'])): ?>
                <p><?php _e('Nessuna risorsa esterna rilevata. Visita il frontend del sito per rilevare le risorse.', 'fp-performance'); ?></p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="widefat" style="margin-top: 1rem;">
                        <thead>
                            <tr>
                                <th><?php _e('Tipo', 'fp-performance'); ?></th>
                                <th><?php _e('Handle', 'fp-performance'); ?></th>
                                <th><?php _e('Dominio', 'fp-performance'); ?></th>
                                <th><?php _e('URL', 'fp-performance'); ?></th>
                                <th><?php _e('Stato Cache', 'fp-performance'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $type => $typeResources): ?>
                                <?php foreach ($typeResources as $resource): ?>
                                    <tr>
                                        <td>
                                            <span class="fp-badge fp-badge-<?php echo $type; ?>" style="
                                                display: inline-block; 
                                                padding: 0.25rem 0.5rem; 
                                                border-radius: 4px; 
                                                font-size: 0.8rem; 
                                                font-weight: bold;
                                                background: <?php echo $type === 'js' ? '#0073aa' : ($type === 'css' ? '#46b450' : '#00a0d2'); ?>;
                                                color: white;
                                            ">
                                                <?php echo strtoupper($type); ?>
                                            </span>
                                        </td>
                                        <td><code><?php echo esc_html($resource['handle']); ?></code></td>
                                        <td><strong><?php echo esc_html($resource['domain']); ?></strong></td>
                                        <td>
                                            <a href="<?php echo esc_url($resource['src']); ?>" target="_blank" style="color: #0073aa;">
                                                <?php echo esc_html($this->truncateUrl($resource['src'], 50)); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($this->cacheManager->shouldCacheResource($resource['src'], $type)): ?>
                                                <span style="color: #46b450;">✅ Cached</span>
                                            <?php else: ?>
                                                <span style="color: #dc3232;">❌ Not Cached</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Renderizza il form delle impostazioni
     */
    private function renderSettingsForm(array $settings): void
    {
        ?>
        <div class="fp-settings-section" style="margin: 2rem 0;">
            <h2><?php _e('⚙️ Configurazione Cache', 'fp-performance'); ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_external_cache_save', 'fp_external_cache_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php _e('Abilita Cache Esterna', 'fp-performance'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?> />
                            <p class="description">
                                <?php _e('Abilita la gestione automatica degli header di cache per risorse esterne.', 'fp-performance'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="js_ttl"><?php _e('TTL JavaScript (secondi)', 'fp-performance'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="js_ttl" name="js_ttl" value="<?php echo $settings['js_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php _e('Durata cache per file JavaScript esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="css_ttl"><?php _e('TTL CSS (secondi)', 'fp-performance'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="css_ttl" name="css_ttl" value="<?php echo $settings['css_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php _e('Durata cache per file CSS esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="font_ttl"><?php _e('TTL Font (secondi)', 'fp-performance'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="font_ttl" name="font_ttl" value="<?php echo $settings['font_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php _e('Durata cache per font esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="aggressive_mode"><?php _e('Modalità Aggressiva', 'fp-performance'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="aggressive_mode" name="aggressive_mode" value="1" <?php checked($settings['aggressive_mode']); ?> />
                            <p class="description">
                                <?php _e('Abilita preload automatico per risorse critiche e header di cache più aggressivi.', 'fp-performance'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="preload_critical"><?php _e('Preload Risorse Critiche', 'fp-performance'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="preload_critical" name="preload_critical" value="1" <?php checked($settings['preload_critical']); ?> />
                            <p class="description">
                                <?php _e('Aggiunge header Link preload per risorse critiche identificate automaticamente.', 'fp-performance'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cache_control_headers"><?php _e('Header Cache-Control', 'fp-performance'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="cache_control_headers" name="cache_control_headers" value="1" <?php checked($settings['cache_control_headers']); ?> />
                            <p class="description">
                                <?php _e('Aggiunge header Cache-Control personalizzati per migliorare la compatibilità con i browser.', 'fp-performance'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <div class="fp-domain-management" style="margin: 2rem 0;">
                    <h3><?php _e('🌐 Gestione Domini', 'fp-performance'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="custom_domains"><?php _e('Domini Personalizzati', 'fp-performance'); ?></label>
                            </th>
                            <td>
                                <textarea id="custom_domains" name="custom_domains[]" rows="3" cols="50" 
                                          placeholder="esempio.com&#10;cdn.example.com&#10;fonts.googleapis.com"><?php 
                                    echo esc_textarea(implode("\n", $settings['custom_domains'])); 
                                ?></textarea>
                                <p class="description">
                                    <?php _e('Un dominio per riga. Se specificato, solo questi domini verranno cachati.', 'fp-performance'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="exclude_domains"><?php _e('Domini Esclusi', 'fp-performance'); ?></label>
                            </th>
                            <td>
                                <textarea id="exclude_domains" name="exclude_domains[]" rows="3" cols="50" 
                                          placeholder="ads.example.com&#10;tracking.example.com"><?php 
                                    echo esc_textarea(implode("\n", $settings['exclude_domains'])); 
                                ?></textarea>
                                <p class="description">
                                    <?php _e('Domini da escludere dalla cache. Un dominio per riga.', 'fp-performance'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="fp-actions" style="margin: 2rem 0;">
                    <?php submit_button(__('💾 Salva Impostazioni', 'fp-performance'), 'primary', 'submit', false); ?>
                    
                    <button type="button" class="button" onclick="location.reload();" style="margin-left: 1rem;">
                        <?php _e('🔄 Rileva Risorse', 'fp-performance'); ?>
                    </button>
                    
                    <button type="button" class="button" onclick="if(confirm('Sei sicuro?')) { window.location.href = '<?php echo admin_url('admin.php?page=fp-external-cache&action=clear-cache'); ?>'; }" style="margin-left: 1rem;">
                        <?php _e('🗑️ Pulisci Cache', 'fp-performance'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }

    /**
     * Tronca un URL per la visualizzazione
     */
    private function truncateUrl(string $url, int $length): string
    {
        return strlen($url) > $length ? substr($url, 0, $length) . '...' : $url;
    }
}
