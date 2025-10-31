<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs;

use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\Http2ServerPush;
use FP\PerfSuite\Services\Assets\SmartAssetDelivery;
use FP\PerfSuite\Admin\RiskMatrix;
use FP\PerfSuite\Admin\Components\RiskLegend;
use FP\PerfSuite\Admin\Components\StatusIndicator;

use function __;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function wp_nonce_field;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function wp_verify_nonce;
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

        // Initialize services
        $thirdPartyScripts = new ThirdPartyScriptManager();
        $http2Push = new Http2ServerPush();
        $smartDelivery = new SmartAssetDelivery();

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
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="third_party" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
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
                    $scripts = [
                        'google_analytics' => __('Google Analytics', 'fp-performance-suite'),
                        'facebook_pixel' => __('Facebook Pixel', 'fp-performance-suite'),
                        'google_ads' => __('Google Ads', 'fp-performance-suite'),
                        'hotjar' => __('Hotjar', 'fp-performance-suite'),
                        'intercom' => __('Intercom', 'fp-performance-suite'),
                        'youtube' => __('YouTube', 'fp-performance-suite'),
                        'linkedin_insight' => __('LinkedIn Insight', 'fp-performance-suite'),
                        'twitter_pixel' => __('Twitter Pixel', 'fp-performance-suite'),
                        'tiktok_pixel' => __('TikTok Pixel', 'fp-performance-suite'),
                        'pinterest_tag' => __('Pinterest Tag', 'fp-performance-suite'),
                        'hubspot' => __('HubSpot', 'fp-performance-suite'),
                        'zendesk' => __('Zendesk', 'fp-performance-suite'),
                        'drift' => __('Drift', 'fp-performance-suite'),
                        'crisp' => __('Crisp', 'fp-performance-suite'),
                        'tidio' => __('Tidio', 'fp-performance-suite'),
                        'segment' => __('Segment', 'fp-performance-suite'),
                        'mixpanel' => __('Mixpanel', 'fp-performance-suite'),
                        'mailchimp' => __('Mailchimp', 'fp-performance-suite'),
                        'stripe' => __('Stripe', 'fp-performance-suite'),
                        'paypal' => __('PayPal', 'fp-performance-suite'),
                        'recaptcha' => __('reCAPTCHA', 'fp-performance-suite'),
                        'google_maps' => __('Google Maps', 'fp-performance-suite'),
                        'microsoft_clarity' => __('Microsoft Clarity', 'fp-performance-suite'),
                        'vimeo' => __('Vimeo', 'fp-performance-suite'),
                        'tawk_to' => __('Tawk.to', 'fp-performance-suite'),
                        'optimizely' => __('Optimizely', 'fp-performance-suite'),
                        'trustpilot' => __('Trustpilot', 'fp-performance-suite'),
                        'klaviyo' => __('Klaviyo', 'fp-performance-suite'),
                        'onetrust' => __('OneTrust', 'fp-performance-suite'),
                        'calendly' => __('Calendly', 'fp-performance-suite'),
                        'fullstory' => __('FullStory', 'fp-performance-suite'),
                        'snapchat_pixel' => __('Snapchat Pixel', 'fp-performance-suite'),
                        'soundcloud' => __('SoundCloud', 'fp-performance-suite'),
                        'klarna' => __('Klarna', 'fp-performance-suite'),
                        'spotify' => __('Spotify', 'fp-performance-suite'),
                        'livechat' => __('LiveChat', 'fp-performance-suite'),
                        'activecampaign' => __('ActiveCampaign', 'fp-performance-suite'),
                        'userway' => __('UserWay', 'fp-performance-suite'),
                        'typeform' => __('Typeform', 'fp-performance-suite'),
                        'brevo' => __('Brevo', 'fp-performance-suite'),
                        'wonderpush' => __('WonderPush', 'fp-performance-suite'),
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
        
        <!-- Script Detector & Manager -->
        <section class="fp-ps-card fp-ps-mt-xl" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); border: 2px solid #cbd5e1;">
            <h2 style="margin-top: 0; display: flex; align-items: center; gap: 10px;">
                🔍 <?php esc_html_e('Rilevatore Script di Terze Parti', 'fp-performance-suite'); ?>
                <span style="background: #3b82f6; color: white; padding: 4px 12px; border-radius: 20px; font-size: 14px; font-weight: normal;">
                    AUTO
                </span>
            </h2>
            <p style="color: #475569; font-size: 15px; line-height: 1.6;">
                <?php esc_html_e('Scansiona automaticamente la homepage per rilevare script esterni (Trustindex, chat widget, analytics, ecc.) e gestiscili con un click.', 'fp-performance-suite'); ?>
            </p>
            
            <!-- Quick Add Buttons -->
            <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #e2e8f0;">
                <h3 style="margin: 0 0 15px 0; color: #1e293b; font-size: 16px;">
                    ⚡ <?php esc_html_e('Pattern Comuni (Quick Add)', 'fp-performance-suite'); ?>
                </h3>
                <p style="margin: 0 0 15px 0; color: #64748b; font-size: 13px;">
                    <?php esc_html_e('Clicca per aggiungere rapidamente pattern comuni alle esclusioni:', 'fp-performance-suite'); ?>
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    <?php
                    $quickPatterns = [
                        'trustindex' => ['label' => '⭐ Trustindex', 'patterns' => "trustindex\nwidget.trustindex.io\ncdn.trustindex.io"],
                        'livechat' => ['label' => '💬 LiveChat', 'patterns' => "livechat\ntawk.to\nzendesk"],
                        'recaptcha' => ['label' => '🔒 reCAPTCHA', 'patterns' => "recaptcha\ngoogle.com/recaptcha"],
                        'calendly' => ['label' => '📅 Calendly', 'patterns' => "calendly\nassets.calendly.com"],
                        'typeform' => ['label' => '📝 Typeform', 'patterns' => "typeform\nembed.typeform.com"],
                        'stripe' => ['label' => '💳 Stripe', 'patterns' => "stripe\njs.stripe.com"],
                        'maps' => ['label' => '🗺️ Google Maps', 'patterns' => "maps.googleapis.com\nmaps.google.com"],
                        'reviews' => ['label' => '⭐ Tutti i Widget Recensioni', 'patterns' => "trustindex\ntripadvisor\ntrustscore\nreviews\nrecensioni"],
                    ];
                    
                    foreach ($quickPatterns as $key => $data):
                    ?>
                    <form method="post" style="margin: 0; display: inline-block;">
                        <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                        <input type="hidden" name="detector_action" value="quick_add" />
                        <input type="hidden" name="quick_patterns" value="<?php echo esc_attr($data['patterns']); ?>" />
                        <button type="submit" class="button" style="background: white; border: 2px solid #cbd5e1; color: #1e293b; font-size: 13px; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                            <?php echo $data['label']; ?>
                        </button>
                    </form>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Scan Button -->
            <form method="post" action="?page=fp-performance-suite-assets&tab=thirdparty">
                <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                <input type="hidden" name="detector_action" value="scan" />
                
                <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #3b82f6;">
                    <button type="submit" class="button button-primary button-large" style="font-size: 16px; padding: 10px 24px;">
                        🔍 <?php esc_html_e('Scansiona Homepage Ora', 'fp-performance-suite'); ?>
                    </button>
                    <span style="color: #64748b; font-size: 14px; margin-left: 15px;">
                        <?php esc_html_e('Rileva automaticamente tutti gli script di terze parti caricati sulla homepage', 'fp-performance-suite'); ?>
                    </span>
                </div>
            </form>
            
            <?php
            // Gestisci scan
            if (isset($_POST['detector_action']) && $_POST['detector_action'] === 'scan' && 
                isset($_POST['fp_ps_detector_nonce']) && wp_verify_nonce($_POST['fp_ps_detector_nonce'], 'fp-ps-detector')) {
                
                $detector = new \FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector($thirdPartyScripts);
                $analysis = $detector->analyzePage(home_url());
                
                if (!empty($analysis['scripts'])) {
                    $detected = $analysis['scripts'];
                    
                    // Salva i risultati della scansione
                    set_transient('fp_ps_detected_scripts', $detected, HOUR_IN_SECONDS);
                    
                    echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                    echo '<p style="margin: 0; color: #14532d;"><strong>✅ Scansione completata!</strong> Trovati ' . count($detected) . ' script di terze parti.</p>';
                    echo '</div>';
                } else {
                    echo '<div style="background: #fef3c7; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                    echo '<p style="margin: 0; color: #713f12;"><strong>⚠️ Nessuno script rilevato.</strong> Prova a visitare prima la homepage per caricare gli script, poi torna qui e scansiona di nuovo.</p>';
                    echo '</div>';
                }
            }
            
            // Gestisci aggiungi a esclusioni (singolo)
            if (isset($_POST['detector_action']) && $_POST['detector_action'] === 'add_exclusion' && 
                isset($_POST['fp_ps_detector_nonce']) && wp_verify_nonce($_POST['fp_ps_detector_nonce'], 'fp-ps-detector') &&
                isset($_POST['script_pattern'])) {
                
                $currentSettings = $thirdPartyScripts->settings();
                $exclusions = $currentSettings['exclusions'] ?? '';
                $newPattern = sanitize_text_field($_POST['script_pattern']);
                
                // Aggiungi se non esiste già
                if (!empty($newPattern) && stripos($exclusions, $newPattern) === false) {
                    $exclusions .= (!empty($exclusions) ? "\n" : '') . $newPattern;
                    $thirdPartyScripts->updateSettings(['exclusions' => $exclusions]);
                    $thirdPartySettings = $thirdPartyScripts->settings(); // Ricarica
                    
                    echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                    echo '<p style="margin: 0; color: #14532d;"><strong>✅ Pattern aggiunto alle esclusioni:</strong> <code>' . esc_html($newPattern) . '</code></p>';
                    echo '<p style="margin: 5px 0 0 0; color: #14532d; font-size: 13px;">Questo script NON verrà più ritardato.</p>';
                    echo '</div>';
                }
            }
            
            // Gestisci rimuovi da esclusioni (singolo)
            if (isset($_POST['detector_action']) && $_POST['detector_action'] === 'remove_exclusion' && 
                isset($_POST['fp_ps_detector_nonce']) && wp_verify_nonce($_POST['fp_ps_detector_nonce'], 'fp-ps-detector') &&
                isset($_POST['script_pattern'])) {
                
                $currentSettings = $thirdPartyScripts->settings();
                $exclusions = $currentSettings['exclusions'] ?? '';
                $patternToRemove = sanitize_text_field($_POST['script_pattern']);
                
                // Rimuovi pattern dalla lista
                $exclusionsList = array_filter(array_map('trim', explode("\n", $exclusions)));
                $exclusionsList = array_filter($exclusionsList, function($pattern) use ($patternToRemove) {
                    return stripos($pattern, $patternToRemove) === false;
                });
                
                $newExclusions = implode("\n", $exclusionsList);
                $thirdPartyScripts->updateSettings(['exclusions' => $newExclusions]);
                $thirdPartySettings = $thirdPartyScripts->settings(); // Ricarica
                
                echo '<div style="background: #fee; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                echo '<p style="margin: 0; color: #991b1b;"><strong>⚠️ Pattern rimosso dalle esclusioni:</strong> <code>' . esc_html($patternToRemove) . '</code></p>';
                echo '<p style="margin: 5px 0 0 0; color: #991b1b; font-size: 13px;">Questo script VERRÀ ritardato se la funzionalità è attiva.</p>';
                echo '</div>';
            }
            
            // Gestisci aggiungi TUTTI a esclusioni
            if (isset($_POST['detector_action']) && $_POST['detector_action'] === 'add_all_exclusions' && 
                isset($_POST['fp_ps_detector_nonce']) && wp_verify_nonce($_POST['fp_ps_detector_nonce'], 'fp-ps-detector')) {
                
                $detectedScripts = get_transient('fp_ps_detected_scripts');
                if (!empty($detectedScripts)) {
                    $currentSettings = $thirdPartyScripts->settings();
                    $exclusions = $currentSettings['exclusions'] ?? '';
                    $exclusionsList = array_filter(array_map('trim', explode("\n", $exclusions)));
                    
                    $addedCount = 0;
                    foreach ($detectedScripts as $script) {
                        $host = parse_url($script['src'], PHP_URL_HOST);
                        if (!empty($host) && !in_array($host, $exclusionsList, true)) {
                            $exclusionsList[] = $host;
                            $addedCount++;
                        }
                    }
                    
                    if ($addedCount > 0) {
                        $newExclusions = implode("\n", $exclusionsList);
                        $thirdPartyScripts->updateSettings(['exclusions' => $newExclusions]);
                        
                        echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                        echo '<p style="margin: 0; color: #14532d;"><strong>✅ Aggiunti ' . $addedCount . ' pattern alle esclusioni!</strong></p>';
                        echo '<p style="margin: 10px 0 0 0; color: #14532d;">Gli script rilevati NON verranno più ritardati.</p>';
                        echo '</div>';
                        
                        // Ricarica settings per mostrare textarea aggiornata
                        $thirdPartySettings = $thirdPartyScripts->settings();
                    } else {
                        echo '<div style="background: #fef3c7; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                        echo '<p style="margin: 0; color: #713f12;"><strong>ℹ️ Tutti gli script sono già nelle esclusioni.</strong></p>';
                        echo '</div>';
                    }
                }
            }
            
            // Mostra script rilevati
            $detectedScripts = get_transient('fp_ps_detected_scripts');
            if (!empty($detectedScripts)) {
                // Raggruppa per categoria
                $byCategory = [];
                foreach ($detectedScripts as $script) {
                    $cat = $script['category'] ?? 'other';
                    if (!isset($byCategory[$cat])) {
                        $byCategory[$cat] = [];
                    }
                    $byCategory[$cat][] = $script;
                }
                
                // Badge categoria
                $categoryLabels = [
                    'analytics' => ['icon' => '📊', 'label' => 'Analytics', 'color' => '#3b82f6'],
                    'marketing' => ['icon' => '🎯', 'label' => 'Marketing', 'color' => '#8b5cf6'],
                    'chat' => ['icon' => '💬', 'label' => 'Chat', 'color' => '#10b981'],
                    'social' => ['icon' => '👥', 'label' => 'Social', 'color' => '#ec4899'],
                    'payment' => ['icon' => '💳', 'label' => 'Pagamenti', 'color' => '#f59e0b'],
                    'security' => ['icon' => '🔒', 'label' => 'Sicurezza', 'color' => '#ef4444'],
                    'media' => ['icon' => '🎬', 'label' => 'Media', 'color' => '#06b6d4'],
                    'fonts' => ['icon' => '🔤', 'label' => 'Font', 'color' => '#6366f1'],
                    'cdn' => ['icon' => '🌐', 'label' => 'CDN', 'color' => '#14b8a6'],
                    'other' => ['icon' => '📦', 'label' => 'Altro', 'color' => '#64748b'],
                ];
                ?>
                <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px solid #3b82f6;">
                    
                    <!-- Statistiche -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #e2e8f0;">
                        <h3 style="margin: 0; color: #1e293b; font-size: 18px;">
                            📊 <?php esc_html_e('Script Rilevati', 'fp-performance-suite'); ?>
                        </h3>
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #3b82f6;">
                                    <?php echo count($detectedScripts); ?>
                                </div>
                                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">
                                    Totale Script
                                </div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 24px; font-weight: bold; color: #8b5cf6;">
                                    <?php echo count($byCategory); ?>
                                </div>
                                <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">
                                    Categorie
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filtri Categoria -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <?php foreach ($byCategory as $cat => $scripts): 
                                $catInfo = $categoryLabels[$cat] ?? $categoryLabels['other'];
                            ?>
                            <span style="background: <?php echo $catInfo['color']; ?>; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                <span><?php echo $catInfo['icon']; ?></span>
                                <?php echo esc_html($catInfo['label']); ?>
                                <span style="background: rgba(255,255,255,0.3); padding: 2px 6px; border-radius: 10px; font-size: 11px;">
                                    <?php echo count($scripts); ?>
                                </span>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <h3 style="margin-top: 0; color: #1e293b;">
                        📋 <?php esc_html_e('Lista Dettagliata', 'fp-performance-suite'); ?>
                    </h3>
                    
                    <div style="display: grid; gap: 15px; margin-top: 20px;">
                        <?php 
                        $currentExclusions = $thirdPartySettings['exclusions'] ?? '';
                        
                        foreach ($detectedScripts as $script): 
                            $cat = $script['category'] ?? 'other';
                            $catInfo = $categoryLabels[$cat] ?? $categoryLabels['other'];
                            $scriptHost = parse_url($script['src'], PHP_URL_HOST);
                            $isExcluded = !empty($currentExclusions) && stripos($currentExclusions, $scriptHost) !== false;
                        ?>
                            <div style="background: <?php echo $isExcluded ? '#f0fdf4' : '#f8fafc'; ?>; border: 2px solid <?php echo $isExcluded ? '#16a34a' : '#e2e8f0'; ?>; border-radius: 8px; padding: 15px; transition: all 0.2s;">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <!-- Header con nome e badge -->
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; flex-wrap: wrap;">
                                            <strong style="font-size: 16px; color: #1e293b;">
                                                <?php echo esc_html($script['name']); ?>
                                            </strong>
                                            
                                            <!-- Badge Categoria -->
                                            <span style="background: <?php echo $catInfo['color']; ?>; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                                                <?php echo $catInfo['icon'] . ' ' . esc_html($catInfo['label']); ?>
                                            </span>
                                            
                                            <!-- Stato Gestione -->
                                            <?php if ($isExcluded): ?>
                                                <span style="background: #16a34a; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                                    ✓ ESCLUSO DAL DELAY
                                                </span>
                                            <?php elseif (!empty($script['managed'])): ?>
                                                <span style="background: #3b82f6; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                                    ⏱️ RITARDATO
                                                </span>
                                            <?php else: ?>
                                                <span style="background: #eab308; color: white; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold;">
                                                    ⚠ NON GESTITO
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- URL completo -->
                                        <div style="background: white; padding: 8px 12px; border-radius: 6px; margin-bottom: 8px; border: 1px solid #e2e8f0;">
                                            <div style="font-size: 11px; color: #94a3b8; text-transform: uppercase; margin-bottom: 4px;">
                                                URL Script:
                                            </div>
                                            <div style="font-size: 12px; color: #475569; font-family: monospace; word-break: break-all;">
                                                <?php echo esc_html($script['src']); ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Host Pattern -->
                                        <div style="background: #fffbeb; padding: 8px 12px; border-radius: 6px; border: 1px solid #fde68a;">
                                            <div style="font-size: 11px; color: #92400e; text-transform: uppercase; margin-bottom: 4px;">
                                                Pattern Suggerito:
                                            </div>
                                            <div style="font-size: 13px; color: #78350f; font-family: monospace; font-weight: 600;">
                                                <?php echo esc_html($scriptHost); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Bottoni Azione -->
                                    <div style="display: flex; flex-direction: column; gap: 8px; margin-left: 20px;">
                                        <?php if ($isExcluded): ?>
                                            <form method="post" style="margin: 0;">
                                                <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                                                <input type="hidden" name="detector_action" value="remove_exclusion" />
                                                <input type="hidden" name="script_pattern" value="<?php echo esc_attr($scriptHost); ?>" />
                                                <button type="submit" class="button button-small" style="background: #dc2626; color: white; border: none; white-space: nowrap; padding: 8px 16px; font-size: 13px;" title="Rimuovi dalle esclusioni">
                                                    ❌ Rimuovi Esclusione
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" style="margin: 0;">
                                                <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                                                <input type="hidden" name="detector_action" value="add_exclusion" />
                                                <input type="hidden" name="script_pattern" value="<?php echo esc_attr($scriptHost); ?>" />
                                                <button type="submit" class="button button-small" style="background: #16a34a; color: white; border: none; white-space: nowrap; padding: 8px 16px; font-size: 13px;" title="Aggiungi alle esclusioni - NON verrà ritardato">
                                                    ➕ Escludi dal Delay
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <button 
                                            onclick="navigator.clipboard.writeText('<?php echo esc_js($scriptHost); ?>'); this.innerHTML='✓ Copiato!'; setTimeout(() => this.innerHTML='📋 Copia Pattern', 1500);"
                                            class="button button-small" 
                                            style="background: #64748b; color: white; border: none; white-space: nowrap; padding: 8px 16px; font-size: 13px;" 
                                            title="Copia il pattern negli appunti"
                                        >
                                            📋 Copia Pattern
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #dbeafe; border-left: 4px solid #3b82f6; border-radius: 6px;">
                        <p style="margin: 0; color: #1e3a8a; font-size: 14px;">
                            <strong>💡 Suggerimento:</strong>
                            <?php esc_html_e('Clicca "➕ Escludi" per aggiungere uno script singolo, oppure usa "➕➕ Aggiungi Tutti" per escludere tutti gli script trovati in una volta.', 'fp-performance-suite'); ?>
                        </p>
                    </div>
                    
                    <div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                        <form method="post" style="margin: 0;">
                            <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                            <input type="hidden" name="detector_action" value="add_all_exclusions" />
                            <button type="submit" class="button button-primary" style="background: #16a34a; border-color: #16a34a;">
                                ➕➕ <?php esc_html_e('Aggiungi Tutti alle Esclusioni', 'fp-performance-suite'); ?>
                            </button>
                        </form>
                        
                        <form method="post" style="margin: 0;">
                            <?php wp_nonce_field('fp-ps-detector', 'fp_ps_detector_nonce'); ?>
                            <input type="hidden" name="detector_action" value="clear_all" />
                            <button type="submit" class="button" style="color: #dc2626;">
                                🗑️ <?php esc_html_e('Cancella Tutti i Risultati', 'fp-performance-suite'); ?>
                            </button>
                        </form>
                    </div>
                </div>
                <?php
            }
            
            // Gestisci Quick Add
            if (isset($_POST['detector_action']) && $_POST['detector_action'] === 'quick_add' && 
                isset($_POST['fp_ps_detector_nonce']) && wp_verify_nonce($_POST['fp_ps_detector_nonce'], 'fp-ps-detector') &&
                isset($_POST['quick_patterns'])) {
                
                $currentSettings = $thirdPartyScripts->settings();
                $exclusions = $currentSettings['exclusions'] ?? '';
                $newPatterns = sanitize_textarea_field($_POST['quick_patterns']);
                
                // Merge pattern (rimuovi duplicati)
                $existingList = array_filter(array_map('trim', explode("\n", $exclusions)));
                $newList = array_filter(array_map('trim', explode("\n", $newPatterns)));
                $mergedList = array_unique(array_merge($existingList, $newList));
                
                $newExclusions = implode("\n", $mergedList);
                $thirdPartyScripts->updateSettings(['exclusions' => $newExclusions]);
                $thirdPartySettings = $thirdPartyScripts->settings();
                
                $addedCount = count($mergedList) - count($existingList);
                
                echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                echo '<p style="margin: 0; color: #14532d;"><strong>✅ Quick Add completato!</strong> Aggiunti ' . $addedCount . ' nuovi pattern.</p>';
                echo '</div>';
            }
            
            // Gestisci rimuovi TUTTE le esclusioni
            if (isset($_POST['detector_action']) && $_POST['detector_action'] === 'clear_all_exclusions' && 
                isset($_POST['fp_ps_detector_nonce']) && wp_verify_nonce($_POST['fp_ps_detector_nonce'], 'fp-ps-detector')) {
                
                $thirdPartyScripts->updateSettings(['exclusions' => '']);
                $thirdPartySettings = $thirdPartyScripts->settings(); // Ricarica
                
                echo '<div style="background: #fee; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                echo '<p style="margin: 0; color: #991b1b;"><strong>⚠️ TUTTE le esclusioni sono state rimosse.</strong></p>';
                echo '<p style="margin: 5px 0 0 0; color: #991b1b; font-size: 13px;">Tutti gli script di terze parti verranno ritardati se la funzionalità è attiva.</p>';
                echo '</div>';
            }
            
            // Gestisci azioni
            if (isset($_POST['detector_action']) && isset($_POST['fp_ps_detector_nonce']) && 
                wp_verify_nonce($_POST['fp_ps_detector_nonce'], 'fp-ps-detector')) {
                
                if ($_POST['detector_action'] === 'clear_all') {
                    delete_transient('fp_ps_detected_scripts');
                    echo '<div style="background: #d1f2eb; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0; border-radius: 6px;">';
                    echo '<p style="margin: 0; color: #14532d;"><strong>✅ Risultati cancellati.</strong></p>';
                    echo '</div>';
                    echo '<script>window.location.href = window.location.href.split("?")[0] + "?page=fp-performance-suite-assets&tab=thirdparty";</script>';
                }
                
                if ($_POST['detector_action'] === 'clear_detected') {
                    $detectedScripts = get_transient('fp_ps_detected_scripts') ?: [];
                    // Rimuovi quello specifico (implementazione futura)
                    delete_transient('fp_ps_detected_scripts');
                    echo '<script>window.location.href = window.location.href.split("?")[0] + "?page=fp-performance-suite-assets&tab=thirdparty";</script>';
                }
            }
            ?>
            
            <?php if (empty($detectedScripts)): ?>
                <div style="background: white; padding: 30px; text-align: center; border-radius: 8px; margin: 20px 0; border: 2px dashed #cbd5e1;">
                    <p style="color: #64748b; font-size: 16px; margin: 0;">
                        🔍 <?php esc_html_e('Nessuno script rilevato. Clicca "Scansiona Homepage Ora" per iniziare.', 'fp-performance-suite'); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <div style="background: #fef3c7; border-left: 4px solid #eab308; padding: 15px; margin: 20px 0; border-radius: 6px;">
                <p style="margin: 0; color: #713f12; font-size: 14px;">
                    <strong>⚠️ Importante:</strong>
                    <?php esc_html_e('Il rilevatore analizza la homepage. Se usi script solo su pagine specifiche (es: checkout), potrebbero non essere rilevati. In quel caso, aggiungi manualmente il pattern nella sezione "Script Exclusions" sopra.', 'fp-performance-suite'); ?>
                </p>
            </div>
        </section>
        
        </div>
        <!-- Close TAB: Third-Party -->
        <?php
        return ob_get_clean();
    }
}
