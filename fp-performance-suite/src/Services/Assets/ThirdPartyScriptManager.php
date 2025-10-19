<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

use function add_filter;
use function get_option;
use function update_option;
use function wp_parse_args;

/**
 * Third-Party Script Manager
 *
 * Manages loading of third-party scripts (analytics, social, ads)
 * with options for delay loading and conditional execution
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 */
class ThirdPartyScriptManager
{
    private const OPTION = 'fp_ps_third_party_scripts';

    public function register(): void
    {
        $settings = $this->settings();

        if (!$settings['enabled']) {
            return;
        }

        // CRITICAL: Don't delay scripts on checkout, cart, payment pages!
        if ($this->isCriticalPage()) {
            return;
        }

        // Filter script tags to apply delays
        add_filter('script_loader_tag', [$this, 'filterScriptTag'], 10, 3);
        
        // Add delayed loading script
        add_action('wp_footer', [$this, 'injectDelayedLoader'], 999);
    }

    /**
     * Check if current page is critical (checkout, payment, etc.)
     *
     * @return bool
     */
    private function isCriticalPage(): bool
    {
        // WooCommerce critical pages
        if (function_exists('is_checkout') && is_checkout()) {
            return true;
        }

        if (function_exists('is_cart') && is_cart()) {
            return true;
        }

        if (function_exists('is_account_page') && is_account_page()) {
            return true;
        }

        if (function_exists('is_wc_endpoint_url') && is_wc_endpoint_url()) {
            return true;
        }

        // Check URL patterns for payment/checkout
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            
            $criticalPatterns = [
                '/checkout',
                '/cart',
                '/payment',
                '/stripe',
                '/paypal',
                '/subscription',
                '/booking',
                '/my-account',
                '/wc-ajax',
                '/wc-api',
                '?add-to-cart=',
                // EDD
                '/edd-ajax',
                '/purchase',
                // MemberPress
                '/membership',
                '/register',
                // Forms
                '/contact',
                '/form',
                // LMS quiz (interactive)
                '/quiz/',
                '/lesson/',
            ];

            foreach ($criticalPatterns as $pattern) {
                if (strpos($requestUri, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get current settings
     *
     * @return array{enabled:bool,delay_all:bool,delay_timeout:int,scripts:array,load_on:string}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'delay_all' => false,
            'delay_timeout' => 5000, // ms
            'load_on' => 'interaction', // interaction, scroll, timeout
            'scripts' => [
                'google_analytics' => [
                    'enabled' => false,
                    'patterns' => ['google-analytics.com/analytics.js', 'googletagmanager.com/gtag/js', 'googletagmanager.com/gtm.js'],
                    'delay' => true,
                ],
                'facebook_pixel' => [
                    'enabled' => false,
                    'patterns' => ['connect.facebook.net'],
                    'delay' => true,
                ],
                'google_ads' => [
                    'enabled' => false,
                    'patterns' => ['googleadservices.com', 'googlesyndication.com'],
                    'delay' => true,
                ],
                'hotjar' => [
                    'enabled' => false,
                    'patterns' => ['static.hotjar.com'],
                    'delay' => true,
                ],
                'intercom' => [
                    'enabled' => false,
                    'patterns' => ['widget.intercom.io', 'js.intercomcdn.com'],
                    'delay' => true,
                ],
                'youtube' => [
                    'enabled' => false,
                    'patterns' => ['youtube.com/iframe_api', 'youtube.com/embed'],
                    'delay' => true,
                ],
                'linkedin_insight' => [
                    'enabled' => false,
                    'patterns' => ['snap.licdn.com', 'platform.linkedin.com'],
                    'delay' => true,
                ],
                'twitter_pixel' => [
                    'enabled' => false,
                    'patterns' => ['static.ads-twitter.com', 'analytics.twitter.com'],
                    'delay' => true,
                ],
                'tiktok_pixel' => [
                    'enabled' => false,
                    'patterns' => ['analytics.tiktok.com'],
                    'delay' => true,
                ],
                'pinterest_tag' => [
                    'enabled' => false,
                    'patterns' => ['ct.pinterest.com', 'pintrk.com'],
                    'delay' => true,
                ],
                'hubspot' => [
                    'enabled' => false,
                    'patterns' => ['js.hs-scripts.com', 'js.hubspot.com', 'js.hs-analytics.net'],
                    'delay' => true,
                ],
                'zendesk' => [
                    'enabled' => false,
                    'patterns' => ['static.zdassets.com', 'ekr.zdassets.com'],
                    'delay' => true,
                ],
                'drift' => [
                    'enabled' => false,
                    'patterns' => ['js.driftt.com'],
                    'delay' => true,
                ],
                'crisp' => [
                    'enabled' => false,
                    'patterns' => ['client.crisp.chat'],
                    'delay' => true,
                ],
                'tidio' => [
                    'enabled' => false,
                    'patterns' => ['code.tidio.co'],
                    'delay' => true,
                ],
                'segment' => [
                    'enabled' => false,
                    'patterns' => ['cdn.segment.com'],
                    'delay' => true,
                ],
                'mixpanel' => [
                    'enabled' => false,
                    'patterns' => ['cdn.mxpnl.com'],
                    'delay' => true,
                ],
                'mailchimp' => [
                    'enabled' => false,
                    'patterns' => ['chimpstatic.com/mcjs-connected'],
                    'delay' => true,
                ],
                'stripe' => [
                    'enabled' => false,
                    'patterns' => ['js.stripe.com'],
                    'delay' => true,
                ],
                'paypal' => [
                    'enabled' => false,
                    'patterns' => ['paypal.com/sdk/js'],
                    'delay' => true,
                ],
                'recaptcha' => [
                    'enabled' => false,
                    'patterns' => ['google.com/recaptcha', 'gstatic.com/recaptcha'],
                    'delay' => true,
                ],
                'google_maps' => [
                    'enabled' => false,
                    'patterns' => ['maps.googleapis.com', 'maps.google.com'],
                    'delay' => true,
                ],
                'microsoft_clarity' => [
                    'enabled' => false,
                    'patterns' => ['clarity.ms'],
                    'delay' => true,
                ],
                'vimeo' => [
                    'enabled' => false,
                    'patterns' => ['player.vimeo.com', 'vimeocdn.com'],
                    'delay' => true,
                ],
                // TOP 5 servizi ad alto valore
                'tawk_to' => [
                    'enabled' => false,
                    'patterns' => ['embed.tawk.to'],
                    'delay' => true,
                ],
                'optimizely' => [
                    'enabled' => false,
                    'patterns' => ['cdn.optimizely.com', 'logx.optimizely.com'],
                    'delay' => true,
                ],
                'trustpilot' => [
                    'enabled' => false,
                    'patterns' => ['widget.trustpilot.com', 'invoca.tpcdn.com'],
                    'delay' => true,
                ],
                'klaviyo' => [
                    'enabled' => false,
                    'patterns' => ['static.klaviyo.com', 'a.klaviyo.com'],
                    'delay' => true,
                ],
                'onetrust' => [
                    'enabled' => false,
                    'patterns' => ['cdn.cookielaw.org', 'optanon.blob.core.windows.net'],
                    'delay' => true,
                ],
                // Altri servizi popolari
                'calendly' => [
                    'enabled' => false,
                    'patterns' => ['assets.calendly.com', 'calendly.com/assets'],
                    'delay' => true,
                ],
                'fullstory' => [
                    'enabled' => false,
                    'patterns' => ['fullstory.com/s/fs.js', 'rs.fullstory.com'],
                    'delay' => true,
                ],
                'snapchat_pixel' => [
                    'enabled' => false,
                    'patterns' => ['sc-static.net/scevent.min.js', 'tr.snapchat.com'],
                    'delay' => true,
                ],
                'soundcloud' => [
                    'enabled' => false,
                    'patterns' => ['w.soundcloud.com', 'widget.sndcdn.com'],
                    'delay' => true,
                ],
                'klarna' => [
                    'enabled' => false,
                    'patterns' => ['js.klarna.com', 'x.klarnacdn.net'],
                    'delay' => true,
                ],
                'spotify' => [
                    'enabled' => false,
                    'patterns' => ['open.spotify.com/embed', 'embed.spotify.com'],
                    'delay' => true,
                ],
                'livechat' => [
                    'enabled' => false,
                    'patterns' => ['cdn.livechatinc.com', 'cdn.livechat-files.com'],
                    'delay' => true,
                ],
                'activecampaign' => [
                    'enabled' => false,
                    'patterns' => ['trackcmp.net', 'trackcmp.com'],
                    'delay' => true,
                ],
                'userway' => [
                    'enabled' => false,
                    'patterns' => ['cdn.userway.org', 'userway.org/widget.js'],
                    'delay' => true,
                ],
                'typeform' => [
                    'enabled' => false,
                    'patterns' => ['embed.typeform.com', 'typeform.com/embed'],
                    'delay' => true,
                ],
            ],
        ];

        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    /**
     * Update settings
     *
     * @param array $settings New settings
     */
    public function update(array $settings): void
    {
        $current = $this->settings();

        $delayTimeout = isset($settings['delay_timeout']) ? (int)$settings['delay_timeout'] : $current['delay_timeout'];
        $delayTimeout = max(0, min(30000, $delayTimeout));

        $new = [
            'enabled' => !empty($settings['enabled']),
            'delay_all' => !empty($settings['delay_all']),
            'delay_timeout' => $delayTimeout,
            'load_on' => $settings['load_on'] ?? $current['load_on'],
            'scripts' => isset($settings['scripts']) ? array_merge($current['scripts'], $settings['scripts']) : $current['scripts'],
        ];

        update_option(self::OPTION, $new);
    }

    /**
     * Filter script tag to add delay attributes
     *
     * @param string $tag Script tag
     * @param string $handle Script handle
     * @param string $src Script source
     * @return string Modified tag
     */
    public function filterScriptTag(string $tag, string $handle, string $src): string
    {
        $settings = $this->settings();

        // Check if script should be delayed
        if (!$this->shouldDelayScript($src, $settings)) {
            return $tag;
        }

        // Add data attribute for delayed loading
        $tag = str_replace(' src=', ' data-fp-delayed-src=', $tag);
        $tag = str_replace('<script', '<script data-fp-delayed="true" type="text/plain"', $tag);

        Logger::debug('Third-party script marked for delay', [
            'handle' => $handle,
            'src' => basename($src),
        ]);

        return $tag;
    }

    /**
     * Check if script should be delayed
     *
     * @param string $src Script source
     * @param array $settings Settings
     * @return bool True if should delay
     */
    private function shouldDelayScript(string $src, array $settings): bool
    {
        // Delay all if enabled (except WordPress core scripts)
        if ($settings['delay_all']) {
            if (strpos($src, '/wp-includes/') !== false || strpos($src, '/wp-admin/') !== false) {
                return false;
            }
            return true;
        }

        // Check against configured script patterns
        foreach ($settings['scripts'] as $scriptConfig) {
            if (empty($scriptConfig['enabled']) || empty($scriptConfig['delay'])) {
                continue;
            }

            if (!isset($scriptConfig['patterns']) || !is_array($scriptConfig['patterns'])) {
                continue;
            }

            foreach ($scriptConfig['patterns'] as $pattern) {
                if (strpos($src, $pattern) !== false) {
                    return true;
                }
            }
        }

        // Check against custom scripts
        $customScripts = $this->getCustomScripts();
        foreach ($customScripts as $customScript) {
            if (empty($customScript['enabled']) || empty($customScript['delay'])) {
                continue;
            }

            foreach ($customScript['patterns'] as $pattern) {
                if (strpos($src, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Inject delayed script loader
     */
    public function injectDelayedLoader(): void
    {
        $settings = $this->settings();
        $loadOn = $settings['load_on'];
        $timeout = $settings['delay_timeout'];

        ?>
        <script>
        (function() {
            'use strict';
            
            var delayedScripts = document.querySelectorAll('script[data-fp-delayed="true"]');
            var scriptsLoaded = false;
            
            if (delayedScripts.length === 0) {
                return;
            }
            
            function loadDelayedScripts() {
                if (scriptsLoaded) {
                    return;
                }
                
                scriptsLoaded = true;
                
                console.log('[FP Performance] Loading delayed scripts (' + delayedScripts.length + ')');
                
                delayedScripts.forEach(function(script) {
                    var src = script.getAttribute('data-fp-delayed-src');
                    
                    if (!src) {
                        return;
                    }
                    
                    var newScript = document.createElement('script');
                    newScript.src = src;
                    
                    // Copy attributes
                    Array.from(script.attributes).forEach(function(attr) {
                        if (attr.name !== 'data-fp-delayed' && attr.name !== 'data-fp-delayed-src' && attr.name !== 'type') {
                            newScript.setAttribute(attr.name, attr.value);
                        }
                    });
                    
                    newScript.type = 'text/javascript';
                    
                    // Replace old script with new one
                    script.parentNode.replaceChild(newScript, script);
                });
                
                // Fire custom event
                var event = new Event('fp_delayed_scripts_loaded');
                document.dispatchEvent(event);
            }
            
            <?php if ($loadOn === 'interaction'): ?>
            // Load on first user interaction
            var events = ['mousemove', 'scroll', 'keydown', 'click', 'touchstart'];
            var triggerLoad = function() {
                loadDelayedScripts();
                events.forEach(function(event) {
                    window.removeEventListener(event, triggerLoad);
                });
            };
            
            events.forEach(function(event) {
                window.addEventListener(event, triggerLoad, { passive: true, once: true });
            });
            
            // Fallback timeout
            setTimeout(loadDelayedScripts, <?php echo $timeout; ?>);
            
            <?php elseif ($loadOn === 'scroll'): ?>
            // Load on scroll
            window.addEventListener('scroll', function() {
                loadDelayedScripts();
            }, { passive: true, once: true });
            
            // Fallback timeout
            setTimeout(loadDelayedScripts, <?php echo $timeout; ?>);
            
            <?php else: ?>
            // Load after timeout
            setTimeout(loadDelayedScripts, <?php echo $timeout; ?>);
            <?php endif; ?>
        })();
        </script>
        <?php
    }

    /**
     * Get list of detected third-party scripts on a page
     *
     * @param string $html Page HTML
     * @return array List of detected scripts
     */
    public function detectScripts(string $html): array
    {
        $detected = [];
        
        preg_match_all('/<script[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
        
        if (empty($matches[1])) {
            return $detected;
        }

        $settings = $this->settings();

        foreach ($matches[1] as $src) {
            foreach ($settings['scripts'] as $name => $config) {
                if (!isset($config['patterns']) || !is_array($config['patterns'])) {
                    continue;
                }
                
                foreach ($config['patterns'] as $pattern) {
                    if (strpos($src, $pattern) !== false) {
                        $detected[] = [
                            'name' => $name,
                            'src' => $src,
                            'managed' => $config['enabled'] ?? false,
                        ];
                        break 2;
                    }
                }
            }
        }

        return $detected;
    }

    /**
     * Get custom scripts
     *
     * @return array Custom scripts
     */
    public function getCustomScripts(): array
    {
        return get_option('fp_ps_custom_scripts', []);
    }

    /**
     * Get status
     *
     * @return array{enabled:bool,delay_all:bool,managed_scripts:int,custom_scripts:int}
     */
    public function status(): array
    {
        $settings = $this->settings();
        
        $managedCount = 0;
        foreach ($settings['scripts'] as $config) {
            if (!empty($config['enabled'])) {
                $managedCount++;
            }
        }

        // Count custom scripts
        $customScripts = $this->getCustomScripts();
        $customCount = 0;
        foreach ($customScripts as $custom) {
            if (!empty($custom['enabled'])) {
                $customCount++;
            }
        }

        return [
            'enabled' => $settings['enabled'],
            'delay_all' => $settings['delay_all'],
            'managed_scripts' => $managedCount,
            'custom_scripts' => $customCount,
        ];
    }
}
