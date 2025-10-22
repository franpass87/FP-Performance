<?php

namespace FP\PerfSuite\Admin\Pages\Assets\Tabs;

use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector;
use FP\PerfSuite\Services\Assets\Http2ServerPush;
use FP\PerfSuite\Services\Assets\SmartAssetDelivery;

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

        // Initialize services using container
        $thirdPartyScripts = $container->get(ThirdPartyScriptManager::class);
        $scriptDetector = new ThirdPartyScriptDetector($thirdPartyScripts);
        $http2Push = $container->get(Http2ServerPush::class);
        $smartDelivery = $container->get(SmartAssetDelivery::class);

        $thirdPartyStatus = $thirdPartyScripts->status();
        $http2Settings = $http2Push->getSettings();
        $smartDeliverySettings = $smartDelivery->getSettings();

        ob_start();
        ?>
        <!-- TAB: Third-Party -->
        <div class="fp-ps-tab-content" data-tab="thirdparty">
        
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
                    </span>
                    <input type="checkbox" name="third_party_enabled" value="1" <?php checked($thirdPartySettings['enabled']); ?> />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Delay All Third-Party Scripts', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="third_party_delay_all" value="1" <?php checked($thirdPartySettings['delay_all']); ?> />
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
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 20px 0;">
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
                    ];
                    
                    foreach ($scripts as $key => $label) {
                        $scriptKey = 'third_party_' . str_replace('_', '', $key);
                        $isEnabled = $thirdPartySettings['scripts'][$key]['enabled'] ?? false;
                        ?>
                        <label style="display: flex; align-items: center; padding: 10px; background: #f9f9f9; border-radius: 6px;">
                            <input type="checkbox" name="<?php echo esc_attr($scriptKey); ?>" value="1" <?php checked($isEnabled); ?> style="margin-right: 8px;" />
                            <span><?php echo esc_html($label); ?></span>
                        </label>
                        <?php
                    }
                    ?>
                </div>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Third-Party Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <!-- HTTP/2 Server Push Section -->
        <section class="fp-ps-card" style="margin-top: 30px;">
            <h2><?php esc_html_e('HTTP/2 Server Push', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="http2_push" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable HTTP/2 Server Push', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_enabled" value="1" <?php checked($http2Settings['enabled']); ?> />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push CSS files', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_css" value="1" <?php checked($http2Settings['push_critical_css']); ?> />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push JavaScript files', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_js" value="1" <?php checked($http2Settings['push_critical_js']); ?> />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push font files', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_fonts" value="1" <?php checked($http2Settings['push_fonts']); ?> />
                </label>
                
                <p>
                    <label for="http2_max_resources"><?php esc_html_e('Maximum resources to push', 'fp-performance-suite'); ?></label>
                    <input type="number" name="http2_max_resources" id="http2_max_resources" value="<?php echo esc_attr($http2Settings['max_push_assets']); ?>" min="1" max="20" />
                </p>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save HTTP/2 Settings', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
        </section>
        
        <!-- Smart Asset Delivery Section -->
        <section class="fp-ps-card" style="margin-top: 30px;">
            <h2><?php esc_html_e('Smart Asset Delivery', 'fp-performance-suite'); ?></h2>
            <form method="post" action="?page=fp-performance-suite-assets&tab=<?php echo esc_attr($current_tab); ?>">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="smart_delivery" />
                <input type="hidden" name="current_tab" value="<?php echo esc_attr($current_tab); ?>" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable Smart Asset Delivery', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="smart_delivery_enabled" value="1" <?php checked($smartDeliverySettings['enabled']); ?> />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Adaptive image quality', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="smart_adaptive_images" value="1" <?php checked($smartDeliverySettings['adapt_images']); ?> />
                </label>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Adaptive video quality', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="smart_adaptive_videos" value="1" <?php checked($smartDeliverySettings['adapt_videos']); ?> />
                </label>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin: 20px 0;">
                    <p>
                        <label for="smart_quality_slow"><?php esc_html_e('Quality for slow connections (%)', 'fp-performance-suite'); ?></label>
                        <input type="number" name="smart_quality_slow" id="smart_quality_slow" value="<?php echo esc_attr($smartDeliverySettings['slow_quality']); ?>" min="10" max="100" />
                    </p>
                    <p>
                        <label for="smart_quality_fast"><?php esc_html_e('Quality for fast connections (%)', 'fp-performance-suite'); ?></label>
                        <input type="number" name="smart_quality_fast" id="smart_quality_fast" value="<?php echo esc_attr($smartDeliverySettings['fast_quality']); ?>" min="10" max="100" />
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
