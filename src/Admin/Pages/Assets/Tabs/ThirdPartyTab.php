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
use function wp_nonce_field;

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
}
