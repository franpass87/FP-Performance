<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs;

use FP\PerfSuite\Utils\ErrorHandler;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\Http2ServerPush;
use FP\PerfSuite\Services\Assets\SmartAssetDelivery;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Admin\Components\StatusIndicator;
use FP\PerfSuite\Admin\Pages\Assets\Tabs\Components\ScriptDetectorComponent;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_js;
use function esc_textarea;
use function home_url;
use function wp_nonce_field;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function wp_verify_nonce;
use function wp_safe_redirect;
use function admin_url;
use function set_transient;
use function get_transient;
use function delete_transient;

class ThirdPartyTab
{
    public function render(array $data): string
    {
        $current_tab = $data['current_tab'];
        $thirdPartySettings = $data['thirdPartySettings'];
        $criticalScripts = $data['criticalScripts'] ?? null;
        $container = $data['container'];

        // Initialize services from container
        $thirdPartyScripts = $container->get(ThirdPartyScriptManager::class);
        $http2Push = $container->get(Http2ServerPush::class);
        $smartDelivery = $container->get(SmartAssetDelivery::class);

        $thirdPartyStatus = $thirdPartyScripts->status();
        $http2Settings = $http2Push->settings();
        $smartDeliverySettings = $smartDelivery->settings();

        ob_start();
        ?>
        <!-- TAB: Third-Party -->
        <div class="fp-ps-tab-content <?php echo $current_tab === 'thirdparty' ? 'active' : ''; ?>" data-tab="thirdparty">
        
        <?php
        // Mostra legenda rischi
        echo RiskLegend::renderLegend();
        ?>
        
        <!-- Third-Party Scripts Section -->
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Third-Party Scripts Management', 'fp-performance-suite'); ?></h2>
            
            <!-- BUGFIX #19: Rilevatore spostato più in alto per migliore UX -->
            <!-- Script Detector & Manager - MOVED HERE -->
            <?php 
            $scriptDetector = new ScriptDetectorComponent($thirdPartyScripts, $thirdPartySettings);
            echo $scriptDetector->render();
            $thirdPartySettings = $scriptDetector->getUpdatedSettings(); // Aggiorna settings dopo le azioni
            ?>
            
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="third_party" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <h3 style="margin-top: 40px; padding-top: 30px; border-top: 3px solid #e2e8f0;">
                    ⚙️ <?php esc_html_e('Configurazione Manuale (Avanzato)', 'fp-performance-suite'); ?>
                </h3>
                <p style="color: #64748b; margin-bottom: 20px;">
                    <?php esc_html_e('Configura manualmente il ritardo degli script di terze parti. Usa il rilevatore automatico sopra per trovare gli script presenti sul tuo sito.', 'fp-performance-suite'); ?>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable Third-Party Scripts Delay', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('third_party_enabled'); ?>
                        <small><?php esc_html_e('Abilita gestione e ottimizzazione script esterni', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="third_party_enabled" value="1" <?php checked($thirdPartySettings['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('third_party_enabled')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Delay All Third-Party Scripts', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('third_party_delay_loading'); ?>
                        <small><?php esc_html_e('Ritarda caricamento fino a interazione utente', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="third_party_delay_loading" value="1" <?php checked($thirdPartySettings['delay_loading']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('third_party_delay_loading')); ?>" />
                </label>
                
                <p>
                    <label for="third_party_timeout"><?php esc_html_e('Delay timeout (ms)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="third_party_timeout" id="third_party_timeout" value="<?php echo esc_attr($thirdPartySettings['delay_timeout']); ?>" min="1000" step="500" />
                </p>
                
                <p>
                    <label for="third_party_load_on"><?php esc_html_e('Load scripts on', 'fp-performance-suite'); ?></label>
                    <select name="third_party_load_on" id="third_party_load_on">
                        <option value="interaction" <?php selected($thirdPartySettings['load_on'], 'interaction'); ?>><?php esc_html_e('User interaction', 'fp-performance-suite'); ?></option>
                        <option value="scroll" <?php selected($thirdPartySettings['load_on'], 'scroll'); ?>><?php esc_html_e('Page scroll', 'fp-performance-suite'); ?></option>
                        <option value="timeout" <?php selected($thirdPartySettings['load_on'], 'timeout'); ?>><?php esc_html_e('Timeout only', 'fp-performance-suite'); ?></option>
                    </select>
                </p>
                
                <h3><?php esc_html_e('Individual Script Management', 'fp-performance-suite'); ?></h3>
                <div class="fp-ps-scripts-grid">
                    <?php
                    // BUGFIX #19b: Aggiunte icone per identificare visivamente i servizi
                    $scripts = [
                        'google_analytics' => '📊 ' . __('Google Analytics', 'fp-performance-suite'),
                        'facebook_pixel' => '👥 ' . __('Facebook Pixel', 'fp-performance-suite'),
                        'google_ads' => '🎯 ' . __('Google Ads', 'fp-performance-suite'),
                        'hotjar' => '🔥 ' . __('Hotjar', 'fp-performance-suite'),
                        'intercom' => '💬 ' . __('Intercom', 'fp-performance-suite'),
                        'youtube' => '📹 ' . __('YouTube', 'fp-performance-suite'),
                        'linkedin_insight' => '💼 ' . __('LinkedIn Insight', 'fp-performance-suite'),
                        'twitter_pixel' => '🐦 ' . __('Twitter Pixel', 'fp-performance-suite'),
                        'tiktok_pixel' => '🎵 ' . __('TikTok Pixel', 'fp-performance-suite'),
                        'pinterest_tag' => '📌 ' . __('Pinterest Tag', 'fp-performance-suite'),
                        'hubspot' => '🧲 ' . __('HubSpot', 'fp-performance-suite'),
                        'zendesk' => '🎧 ' . __('Zendesk', 'fp-performance-suite'),
                        'drift' => '💬 ' . __('Drift', 'fp-performance-suite'),
                        'crisp' => '💬 ' . __('Crisp', 'fp-performance-suite'),
                        'tidio' => '💬 ' . __('Tidio', 'fp-performance-suite'),
                        'segment' => '📊 ' . __('Segment', 'fp-performance-suite'),
                        'mixpanel' => '📈 ' . __('Mixpanel', 'fp-performance-suite'),
                        'mailchimp' => '📧 ' . __('Mailchimp', 'fp-performance-suite'),
                        'stripe' => '💳 ' . __('Stripe', 'fp-performance-suite'),
                        'paypal' => '💰 ' . __('PayPal', 'fp-performance-suite'),
                        'recaptcha' => '🔒 ' . __('reCAPTCHA', 'fp-performance-suite'),
                        'google_maps' => '🗺️ ' . __('Google Maps', 'fp-performance-suite'),
                        'microsoft_clarity' => '🔍 ' . __('Microsoft Clarity', 'fp-performance-suite'),
                        'vimeo' => '🎬 ' . __('Vimeo', 'fp-performance-suite'),
                        'tawk_to' => '💬 ' . __('Tawk.to', 'fp-performance-suite'),
                        'optimizely' => '🧪 ' . __('Optimizely', 'fp-performance-suite'),
                        'trustpilot' => '⭐ ' . __('Trustpilot', 'fp-performance-suite'),
                        'klaviyo' => '📧 ' . __('Klaviyo', 'fp-performance-suite'),
                        'onetrust' => '🛡️ ' . __('OneTrust', 'fp-performance-suite'),
                        'calendly' => '📅 ' . __('Calendly', 'fp-performance-suite'),
                        'fullstory' => '📹 ' . __('FullStory', 'fp-performance-suite'),
                        'snapchat_pixel' => '👻 ' . __('Snapchat Pixel', 'fp-performance-suite'),
                        'soundcloud' => '🎧 ' . __('SoundCloud', 'fp-performance-suite'),
                        'klarna' => '💳 ' . __('Klarna', 'fp-performance-suite'),
                        'spotify' => '🎵 ' . __('Spotify', 'fp-performance-suite'),
                        'livechat' => '💬 ' . __('LiveChat', 'fp-performance-suite'),
                        'activecampaign' => '📧 ' . __('ActiveCampaign', 'fp-performance-suite'),
                        'userway' => '♿ ' . __('UserWay', 'fp-performance-suite'),
                        'typeform' => '📝 ' . __('Typeform', 'fp-performance-suite'),
                        'brevo' => '📧 ' . __('Brevo', 'fp-performance-suite'),
                        'wonderpush' => '🔔 ' . __('WonderPush', 'fp-performance-suite'),
                    ];
                    
                    foreach ($scripts as $key => $label) {
                        $scriptKey = 'third_party_' . str_replace('_', '', $key);
                        $isEnabled = $thirdPartySettings['scripts'][$key]['enabled'] ?? false;
                        ?>
                        <label class="fp-ps-script-item">
                            <input type="checkbox" name="<?php echo esc_attr($scriptKey); ?>" value="1" <?php checked($isEnabled); ?> class="fp-ps-script-checkbox" />
                            <span><?php echo esc_html($label); ?></span>
                        </label>
                        <?php
                    }
                    ?>
                </div>
                
                <h3 style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #e2e8f0;">
                    <?php esc_html_e('Script Exclusions (Advanced)', 'fp-performance-suite'); ?>
                </h3>
                <p style="color: #64748b;">
                    <?php esc_html_e('Aggiungi pattern per escludere script specifici dal delay (es: trustindex, widget.trustindex.io)', 'fp-performance-suite'); ?>
                </p>
                
                <div style="background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 15px 0; border-radius: 6px;">
                    <p style="margin: 0; color: #1e3a8a; font-size: 14px;">
                        <strong>💡 Come Funziona:</strong>
                        <?php esc_html_e('Puoi aggiungere MULTIPLI pattern, uno per riga. Es:', 'fp-performance-suite'); ?>
                    </p>
                    <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #1e3a8a; font-size: 13px;">
                        <li><code>trustindex</code> → Blocca tutti gli script che contengono "trustindex"</li>
                        <li><code>widget.trustindex.io</code> → Più specifico, solo widget Trustindex</li>
                        <li><code>livechat</code> → Esclude chat widget</li>
                        <li><code>recaptcha</code> → Non ritarda Google reCAPTCHA</li>
                    </ul>
                </div>
                
                <?php
                // Mostra pattern correnti come badge cliccabili
                $currentExclusionsList = array_filter(array_map('trim', explode("\n", $thirdPartySettings['exclusions'] ?? '')));
                if (!empty($currentExclusionsList)):
                ?>
                <div style="background: #f0fdf4; border: 2px solid #16a34a; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <strong style="color: #14532d; font-size: 14px;">
                            ✅ <?php esc_html_e('Pattern Attualmente Esclusi', 'fp-performance-suite'); ?>
                            <span style="background: #16a34a; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px; margin-left: 8px;">
                                <?php echo count($currentExclusionsList); ?>
                            </span>
                        </strong>
                        <form method="post" style="margin: 0;">
                            <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                            <input type="hidden" name="detector_action" value="clear_all_exclusions" />
                            <button type="submit" class="button button-small" style="color: #dc2626; font-size: 12px;" onclick="return confirm('Sei sicuro di voler rimuovere TUTTE le esclusioni?');">
                                🗑️ <?php esc_html_e('Rimuovi Tutte', 'fp-performance-suite'); ?>
                            </button>
                        </form>
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php foreach ($currentExclusionsList as $pattern): ?>
                        <div style="background: white; border: 1px solid #16a34a; border-radius: 6px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px;">
                            <code style="color: #14532d; background: transparent; padding: 0;"><?php echo esc_html($pattern); ?></code>
                            <form method="post" style="margin: 0; display: inline;">
                                <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                                <input type="hidden" name="detector_action" value="remove_exclusion" />
                                <input type="hidden" name="script_pattern" value="<?php echo esc_attr($pattern); ?>" />
                                <button type="submit" style="background: none; border: none; color: #dc2626; cursor: pointer; padding: 0; font-size: 16px; line-height: 1;" title="Rimuovi questo pattern">
                                    ×
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <label for="third_party_exclusions" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 15px;">
                    📝 <?php esc_html_e('Pattern da Escludere (uno per riga)', 'fp-performance-suite'); ?>
                </label>
                <textarea 
                    name="third_party_exclusions" 
                    id="third_party_exclusions" 
                    rows="8" 
                    style="width: 100%; max-width: 700px; font-family: monospace; font-size: 13px; padding: 12px; border: 2px solid #cbd5e1; border-radius: 6px; line-height: 1.6;"
                    placeholder="trustindex&#10;widget.trustindex.io&#10;cdn.trustindex.io&#10;livechat&#10;tawk.to&#10;recaptcha&#10;calendly&#10;typeform"
                ><?php echo esc_textarea($thirdPartySettings['exclusions'] ?? ''); ?></textarea>
                <p class="description" style="font-size: 13px; color: #64748b;">
                    ✅ <?php esc_html_e('Gli script che contengono QUALSIASI di questi pattern NON verranno ritardati.', 'fp-performance-suite'); ?><br>
                    ⚡ <?php esc_html_e('Modifica direttamente nel campo sopra per aggiungere/rimuovere multipli pattern in una volta.', 'fp-performance-suite'); ?>
                </p>
                
                <div class="fp-ps-info-section">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Third-Party Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <!-- HTTP/2 Server Push Status Cards -->
        <div class="fp-ps-grid three fp-ps-mb-xl fp-ps-mt-xl">
            <?php 
            echo StatusIndicator::renderCard(
                $http2Settings['enabled'] ? 'success' : 'inactive',
                __('HTTP/2 Server Push', 'fp-performance-suite'),
                $http2Settings['enabled'] ? __('Attivo e funzionante', 'fp-performance-suite') : __('Non attivo', 'fp-performance-suite'),
                $http2Settings['enabled'] ? '✅ ON' : '⚫ OFF'
            );
            
            $pushTypesCount = 0;
            if ($http2Settings['push_css']) $pushTypesCount++;
            if ($http2Settings['push_js']) $pushTypesCount++;
            if ($http2Settings['push_fonts']) $pushTypesCount++;
            if ($http2Settings['push_images']) $pushTypesCount++;
            
            echo StatusIndicator::renderCard(
                $pushTypesCount > 0 ? 'success' : 'warning',
                __('Tipi di Asset', 'fp-performance-suite'),
                __('Tipi di asset configurati per push', 'fp-performance-suite'),
                '📦 ' . esc_html($pushTypesCount) . '/4'
            );
            
            echo StatusIndicator::renderCard(
                $http2Settings['critical_only'] ? 'success' : 'warning',
                __('Modalità', 'fp-performance-suite'),
                $http2Settings['critical_only'] ? __('Solo risorse critiche', 'fp-performance-suite') : __('Tutti gli asset', 'fp-performance-suite'),
                $http2Settings['critical_only'] ? '🎯 Critico' : '📋 Tutti'
            );
            ?>
        </div>
        
        <!-- HTTP/2 Server Push Section -->
        <section class="fp-ps-card fp-ps-mt-xl">
            <h2><?php esc_html_e('HTTP/2 Server Push', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="http2_push" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable HTTP/2 Server Push', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('http2_push_enabled'); ?>
                        <small><?php esc_html_e('Attiva HTTP/2 Server Push per asset critici', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="http2_push_enabled" value="1" <?php checked($http2Settings['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('http2_push_enabled')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push CSS files', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('http2_push_css'); ?>
                        <small><?php esc_html_e('Push file CSS critici', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="http2_push_css" value="1" <?php checked($http2Settings['push_css']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('http2_push_css')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push JavaScript files', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('http2_push_js'); ?>
                        <small><?php esc_html_e('Push file JavaScript critici', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="http2_push_js" value="1" <?php checked($http2Settings['push_js']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('http2_push_js')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push font files', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('http2_push_fonts'); ?>
                        <small><?php esc_html_e('Push font critici (woff2)', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="http2_push_fonts" value="1" <?php checked($http2Settings['push_fonts']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('http2_push_fonts')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push critical images', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('http2_push_images'); ?>
                        <small><?php esc_html_e('Push immagini critiche (logo, hero)', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="http2_push_images" value="1" <?php checked($http2Settings['push_images']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('http2_push_images')); ?>" />
                </label>
                
                <p>
                    <label for="http2_max_resources"><?php esc_html_e('Maximum resources to push', 'fp-performance-suite'); ?></label>
                    <input type="number" name="http2_max_resources" id="http2_max_resources" value="<?php echo esc_attr($http2Settings['max_resources']); ?>" min="1" max="20" />
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push only critical resources', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('http2_critical_only'); ?>
                        <small><?php esc_html_e('Push solo risorse critiche identificate automaticamente', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="http2_critical_only" value="1" <?php checked($http2Settings['critical_only']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('http2_critical_only')); ?>" />
                </label>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save HTTP/2 Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <!-- Smart Asset Delivery Status Cards -->
        <div class="fp-ps-grid three fp-ps-mb-xl fp-ps-mt-xl">
            <?php 
            echo StatusIndicator::renderCard(
                $smartDeliverySettings['enabled'] ? 'success' : 'inactive',
                __('Smart Asset Delivery', 'fp-performance-suite'),
                $smartDeliverySettings['enabled'] ? __('Ottimizzazione attiva', 'fp-performance-suite') : __('Non attivo', 'fp-performance-suite'),
                $smartDeliverySettings['enabled'] ? '✅ ON' : '⚫ OFF'
            );
            
            $featuresCount = 0;
            if ($smartDeliverySettings['detect_connection']) $featuresCount++;
            if ($smartDeliverySettings['save_data_mode']) $featuresCount++;
            if ($smartDeliverySettings['adaptive_images']) $featuresCount++;
            if ($smartDeliverySettings['adaptive_videos']) $featuresCount++;
            
            echo StatusIndicator::renderCard(
                $featuresCount >= 3 ? 'success' : ($featuresCount > 0 ? 'warning' : 'inactive'),
                __('Funzionalità Attive', 'fp-performance-suite'),
                __('Numero di ottimizzazioni abilitate', 'fp-performance-suite'),
                '🎯 ' . esc_html($featuresCount) . '/4'
            );
            
            echo StatusIndicator::renderCard(
                $smartDeliverySettings['detect_connection'] ? 'success' : 'warning',
                __('Rilevamento Rete', 'fp-performance-suite'),
                $smartDeliverySettings['detect_connection'] ? __('Velocità rilevata automaticamente', 'fp-performance-suite') : __('Nessun rilevamento', 'fp-performance-suite'),
                $smartDeliverySettings['detect_connection'] ? '📡 Auto' : '❌ Off'
            );
            ?>
        </div>
        
        <!-- Smart Asset Delivery Section -->
        <section class="fp-ps-card fp-ps-mt-xl">
            <h2><?php esc_html_e('Smart Asset Delivery', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="smart_delivery" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable Smart Asset Delivery', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('smart_delivery_enabled'); ?>
                        <small><?php esc_html_e('Consegna intelligente asset basata su connessione', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="smart_delivery_enabled" value="1" <?php checked($smartDeliverySettings['enabled']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('smart_delivery_enabled')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Detect connection speed', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('smart_detect_connection'); ?>
                        <small><?php esc_html_e('Rileva automaticamente velocità di connessione', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="smart_detect_connection" value="1" <?php checked($smartDeliverySettings['detect_connection']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('smart_detect_connection')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Respect save-data mode', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('smart_save_data_mode'); ?>
                        <small><?php esc_html_e('Rispetta modalità "risparmio dati" del browser', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="smart_save_data_mode" value="1" <?php checked($smartDeliverySettings['save_data_mode']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('smart_save_data_mode')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Adaptive image quality', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('smart_adaptive_images'); ?>
                        <small><?php esc_html_e('Adatta qualità immagini in base a connessione', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="smart_adaptive_images" value="1" <?php checked($smartDeliverySettings['adaptive_images']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('smart_adaptive_images')); ?>" />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Adaptive video quality', 'fp-performance-suite'); ?></strong>
                        <?php echo RiskMatrix::renderIndicator('smart_adaptive_videos'); ?>
                        <small><?php esc_html_e('Adatta qualità video in base a connessione', 'fp-performance-suite'); ?></small>
                    </span>
                    <input type="checkbox" name="smart_adaptive_videos" value="1" <?php checked($smartDeliverySettings['adaptive_videos']); ?> data-risk="<?php echo esc_attr(RiskMatrix::getRiskLevel('smart_adaptive_videos')); ?>" />
                </label>
                
                <div class="fp-ps-push-grid">
                    <p>
                        <label for="smart_quality_slow"><?php esc_html_e('Quality for slow connections (%)', 'fp-performance-suite'); ?></label>
                        <input type="number" name="smart_quality_slow" id="smart_quality_slow" value="<?php echo esc_attr($smartDeliverySettings['quality_slow']); ?>" min="10" max="100" />
                    </p>
                    <p>
                        <label for="smart_quality_moderate"><?php esc_html_e('Quality for moderate connections (%)', 'fp-performance-suite'); ?></label>
                        <input type="number" name="smart_quality_moderate" id="smart_quality_moderate" value="<?php echo esc_attr($smartDeliverySettings['quality_moderate']); ?>" min="10" max="100" />
                    </p>
                    <p>
                        <label for="smart_quality_fast"><?php esc_html_e('Quality for fast connections (%)', 'fp-performance-suite'); ?></label>
                        <input type="number" name="smart_quality_fast" id="smart_quality_fast" value="<?php echo esc_attr($smartDeliverySettings['quality_fast']); ?>" min="10" max="100" />
                    </p>
                </div>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Smart Delivery Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        </div>
        <!-- Close TAB: Third-Party -->
        <?php
        return ob_get_clean();
    }
    
    // Metodo renderScriptDetector() rimosso - ora gestito da ScriptDetectorComponent
}
