<?php

namespace FP\PerfSuite\Admin\Pages\Cache\Tabs;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Services\Assets\ExternalResourceCacheManager;

use function __;
use function admin_url;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function esc_url;
use function implode;
use function strtoupper;
use function submit_button;
use function wp_nonce_field;

/**
 * Render della tab External Cache per Cache page
 * 
 * @package FP\PerfSuite\Admin\Pages\Cache\Tabs
 * @author Francesco Passeri
 */
class ExternalCacheTab
{
    private ServiceContainer $container;

    public function __construct(ServiceContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Tronca un URL per la visualizzazione
     */
    private function truncateUrl(string $url, int $length): string
    {
        return strlen($url) > $length ? substr($url, 0, $length) . '...' : $url;
    }

    /**
     * Render della tab External Cache
     */
    public function render(string $message = ''): string
    {
        ob_start();
        
        try {
            $cacheManager = new ExternalResourceCacheManager();
            $settings = $cacheManager->getSettings();
            $stats = $cacheManager->getCacheStats();
            $resources = $cacheManager->detectExternalResources();
        } catch (\Throwable $e) {
            return '<div class="notice notice-error"><p>' . esc_html($e->getMessage()) . '</p></div>';
        }
        
        ?>
        
        <?php if (!empty($message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- External Cache Status Cards -->
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

        <!-- External Resources Table -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('🔍 Risorse Esterne Rilevate', 'fp-performance-suite'); ?></h2>
            
            <?php if (empty($resources['js']) && empty($resources['css']) && empty($resources['fonts'])): ?>
                <p><?php esc_html_e('Nessuna risorsa esterna rilevata. Visita il frontend del sito per rilevare le risorse.', 'fp-performance-suite'); ?></p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="widefat" style="margin-top: 1rem;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Tipo', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Handle', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Dominio', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('URL', 'fp-performance-suite'); ?></th>
                                <th><?php esc_html_e('Stato Cache', 'fp-performance-suite'); ?></th>
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
                                            <?php if ($cacheManager->shouldCacheResource($resource['src'], $type)): ?>
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
        </section>

        <!-- External Cache Settings Form -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('⚙️ Configurazione Cache Esterna', 'fp-performance-suite'); ?></h2>
            
            <form method="post" action="">
                <?php wp_nonce_field('fp_external_cache_save', 'fp_external_cache_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enabled"><?php esc_html_e('Abilita Cache Esterna', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="enabled" name="enabled" value="1" <?php checked($settings['enabled']); ?> />
                            <p class="description">
                                <?php esc_html_e('Abilita la gestione automatica degli header di cache per risorse esterne.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="js_ttl"><?php esc_html_e('TTL JavaScript (secondi)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="js_ttl" name="js_ttl" value="<?php echo $settings['js_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php esc_html_e('Durata cache per file JavaScript esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="css_ttl"><?php esc_html_e('TTL CSS (secondi)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="css_ttl" name="css_ttl" value="<?php echo $settings['css_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php esc_html_e('Durata cache per file CSS esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="font_ttl"><?php esc_html_e('TTL Font (secondi)', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="font_ttl" name="font_ttl" value="<?php echo $settings['font_ttl']; ?>" 
                                   min="3600" max="63072000" step="3600" style="width: 200px;" />
                            <p class="description">
                                <?php esc_html_e('Durata cache per font esterni. Valore consigliato: 31536000 (1 anno).', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="aggressive_mode"><?php esc_html_e('Modalità Aggressiva', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="aggressive_mode" name="aggressive_mode" value="1" <?php checked($settings['aggressive_mode']); ?> />
                            <p class="description">
                                <?php esc_html_e('Abilita preload automatico per risorse critiche e header di cache più aggressivi.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="preload_critical"><?php esc_html_e('Preload Risorse Critiche', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="preload_critical" name="preload_critical" value="1" <?php checked($settings['preload_critical']); ?> />
                            <p class="description">
                                <?php esc_html_e('Aggiunge header Link preload per risorse critiche identificate automaticamente.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="cache_control_headers"><?php esc_html_e('Header Cache-Control', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="cache_control_headers" name="cache_control_headers" value="1" <?php checked($settings['cache_control_headers']); ?> />
                            <p class="description">
                                <?php esc_html_e('Aggiunge header Cache-Control personalizzati per migliorare la compatibilità con i browser.', 'fp-performance-suite'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <div class="fp-domain-management" style="margin: 2rem 0;">
                    <h3><?php esc_html_e('🌐 Gestione Domini', 'fp-performance-suite'); ?></h3>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="custom_domains"><?php esc_html_e('Domini Personalizzati', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <textarea id="custom_domains" name="custom_domains[]" rows="3" cols="50" 
                                          placeholder="esempio.com&#10;cdn.example.com&#10;fonts.googleapis.com"><?php 
                                    echo esc_textarea(implode("\n", $settings['custom_domains'])); 
                                ?></textarea>
                                <p class="description">
                                    <?php esc_html_e('Un dominio per riga. Se specificato, solo questi domini verranno cachati.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="exclude_domains"><?php esc_html_e('Domini Esclusi', 'fp-performance-suite'); ?></label>
                            </th>
                            <td>
                                <textarea id="exclude_domains" name="exclude_domains[]" rows="3" cols="50" 
                                          placeholder="ads.example.com&#10;tracking.example.com"><?php 
                                    echo esc_textarea(implode("\n", $settings['exclude_domains'])); 
                                ?></textarea>
                                <p class="description">
                                    <?php esc_html_e('Domini da escludere dalla cache. Un dominio per riga.', 'fp-performance-suite'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="fp-actions" style="margin: 2rem 0;">
                    <?php submit_button(__('💾 Salva Impostazioni', 'fp-performance-suite'), 'primary', 'submit', false); ?>
                    
                    <button type="button" class="button" onclick="location.reload();" style="margin-left: 1rem;">
                        <?php esc_html_e('🔄 Rileva Risorse', 'fp-performance-suite'); ?>
                    </button>
                    
                    <button type="button" class="button" onclick="if(confirm('Sei sicuro?')) { window.location.href = '<?php echo admin_url('admin.php?page=fp-performance-suite-cache&tab=external&action=clear-cache'); ?>'; }" style="margin-left: 1rem;">
                        <?php esc_html_e('🗑️ Pulisci Cache', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <?php
        return ob_get_clean();
    }
}

