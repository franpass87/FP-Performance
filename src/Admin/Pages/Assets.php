<?php

namespace FP\PerfSuite\Admin\Pages;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptDetector;
use FP\PerfSuite\Services\Assets\Http2ServerPush;
use FP\PerfSuite\Services\Assets\SmartAssetDelivery;
use FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;
use FP\PerfSuite\Services\Intelligence\CriticalAssetsDetector;
use FP\PerfSuite\Services\Compatibility\ThemeDetector;
use FP\PerfSuite\Admin\ThemeHints;

use function __;
use function array_filter;
use function array_map;
use function checked;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_textarea;
use function explode;
use function implode;
use function trim;
use function wp_nonce_field;
use function wp_unslash;
use function wp_verify_nonce;

class Assets extends AbstractPage
{
    public function slug(): string
    {
        return 'fp-performance-suite-assets';
    }

    public function title(): string
    {
        return __('Assets Optimization', 'fp-performance-suite');
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
            'breadcrumbs' => [__('Optimization', 'fp-performance-suite'), __('Assets', 'fp-performance-suite')],
        ];
    }

    protected function content(): string
    {
        $optimizer = $this->container->get(Optimizer::class);
        $fontOptimizer = $this->container->get(FontOptimizer::class);
        $thirdPartyScripts = $this->container->get(ThirdPartyScriptManager::class);
        $scriptDetector = new ThirdPartyScriptDetector($thirdPartyScripts);
        $http2Push = $this->container->get(Http2ServerPush::class);
        $smartDelivery = $this->container->get(SmartAssetDelivery::class);
        $message = '';
        
        // Carica le impostazioni correnti PRIMA del blocco POST
        // per permettere la lettura delle esclusioni esistenti quando si applicano le nuove
        $settings = $optimizer->settings();
        $fontSettings = $fontOptimizer->getSettings();
        $thirdPartySettings = $thirdPartyScripts->settings();
        
        // Smart Script Detector
        $smartDetector = new SmartExclusionDetector();
        $criticalScripts = null;
        $excludeCss = null;
        $excludeJs = null;
        
        // Critical Assets Detector
        $assetsDetector = new CriticalAssetsDetector();
        $criticalAssets = null;
        
        // Theme-specific hints
        $themeDetector = $this->container->get(ThemeDetector::class);
        $hints = new ThemeHints($themeDetector);
        
        // Carica risultati salvati nei transient (se esistono)
        $criticalScripts = get_transient('fp_ps_critical_scripts_detected');
        $excludeCss = get_transient('fp_ps_exclude_css_detected');
        $excludeJs = get_transient('fp_ps_exclude_js_detected');
        $criticalAssets = get_transient('fp_ps_critical_assets_detected');
        
        // Handle success messages from redirects
        if (isset($_GET['msg'])) {
            $msgType = sanitize_text_field($_GET['msg']);
            $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
            
            switch ($msgType) {
                case 'scripts_detected':
                    $message = __('Critical scripts detected! Review suggestions below.', 'fp-performance-suite');
                    break;
                case 'css_detected':
                    $message = __('CSS files to exclude detected! Review suggestions below.', 'fp-performance-suite');
                    break;
                case 'js_detected':
                    $message = __('JavaScript files to exclude detected! Review suggestions below.', 'fp-performance-suite');
                    break;
                case 'assets_detected':
                    $message = __('Critical assets detected! Review suggestions below.', 'fp-performance-suite');
                    break;
                case 'scripts_applied':
                    $message = sprintf(
                        __('Successfully applied %d critical scripts to exclusion list!', 'fp-performance-suite'),
                        $count
                    );
                    break;
                case 'css_applied':
                    $message = sprintf(
                        __('Successfully applied %d CSS files to exclusion list!', 'fp-performance-suite'),
                        $count
                    );
                    break;
                case 'js_applied':
                    $message = sprintf(
                        __('Successfully applied %d JavaScript files to exclusion list!', 'fp-performance-suite'),
                        $count
                    );
                    break;
                case 'assets_applied':
                    $message = sprintf(
                        __('Successfully applied %d critical assets to preload list!', 'fp-performance-suite'),
                        $count
                    );
                    break;
            }
        }
        
        if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['fp_ps_assets_nonce']) && wp_verify_nonce(wp_unslash($_POST['fp_ps_assets_nonce']), 'fp-ps-assets')) {
            
            // ============================================
            // SEZIONE CENTRALIZZATA AUTOMAZIONI AI
            // ============================================
            
            // Auto-detect critical scripts
            if (isset($_POST['auto_detect_scripts'])) {
                $criticalScripts = $smartDetector->detectCriticalScripts();
                set_transient('fp_ps_critical_scripts_detected', $criticalScripts, 300);
                wp_safe_redirect(add_query_arg('msg', 'scripts_detected', $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // Auto-detect CSS to exclude
            if (isset($_POST['auto_detect_exclude_css'])) {
                $excludeCss = $smartDetector->detectExcludeCss();
                set_transient('fp_ps_exclude_css_detected', $excludeCss, 300);
                wp_safe_redirect(add_query_arg('msg', 'css_detected', $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // Auto-detect JS to exclude
            if (isset($_POST['auto_detect_exclude_js'])) {
                $excludeJs = $smartDetector->detectExcludeJs();
                set_transient('fp_ps_exclude_js_detected', $excludeJs, 300);
                wp_safe_redirect(add_query_arg('msg', 'js_detected', $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // Auto-detect critical assets
            if (isset($_POST['auto_detect_critical_assets'])) {
                $criticalAssets = $assetsDetector->detectCriticalAssets();
                set_transient('fp_ps_critical_assets_detected', $criticalAssets, 300);
                wp_safe_redirect(add_query_arg('msg', 'assets_detected', $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // Apply critical scripts suggestions
            if (isset($_POST['apply_critical_suggestions'])) {
                $criticalScripts = $smartDetector->detectCriticalScripts();
                $excludeScripts = [];
                
                // Collect all scripts to exclude
                if (!empty($criticalScripts['always_exclude'])) {
                    $excludeScripts = array_merge($excludeScripts, $criticalScripts['always_exclude']);
                }
                if (!empty($criticalScripts['plugin_critical'])) {
                    foreach ($criticalScripts['plugin_critical'] as $item) {
                        $excludeScripts[] = $item['pattern'];
                    }
                }
                if (!empty($criticalScripts['dependency_critical'])) {
                    foreach ($criticalScripts['dependency_critical'] as $item) {
                        $excludeScripts[] = $item['handle'];
                    }
                }
                
                // Merge with current textarea value (preserves user's manual edits)
                $currentExclude = isset($_POST['exclude_js']) ? wp_unslash($_POST['exclude_js']) : (!empty($settings['exclude_js']) ? $settings['exclude_js'] : '');
                $currentExcludeArray = array_filter(array_map('trim', explode("\n", $currentExclude)));
                $mergedExclude = array_unique(array_merge($currentExcludeArray, $excludeScripts));
                
                $optimizer->update(['exclude_js' => implode("\n", $mergedExclude)]);
                
                wp_safe_redirect(add_query_arg(['msg' => 'scripts_applied', 'count' => count($excludeScripts)], $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // Apply CSS exclusions suggestions
            if (isset($_POST['apply_css_exclusions'])) {
                $excludeCss = $smartDetector->detectExcludeCss();
                $cssToExclude = [];
                
                // Collect CSS with high confidence
                foreach (['plugin_specific', 'critical_files', 'admin_styles'] as $category) {
                    if (!empty($excludeCss[$category])) {
                        foreach ($excludeCss[$category] as $item) {
                            if ($item['confidence'] >= 0.7) {
                                $cssToExclude[] = $item['handle'];
                            }
                        }
                    }
                }
                
                // Merge with current textarea value (preserves user's manual edits)
                $currentExclude = isset($_POST['exclude_css']) ? wp_unslash($_POST['exclude_css']) : (!empty($settings['exclude_css']) ? $settings['exclude_css'] : '');
                $currentExcludeArray = array_filter(array_map('trim', explode("\n", $currentExclude)));
                $mergedExclude = array_unique(array_merge($currentExcludeArray, $cssToExclude));
                
                $optimizer->update(['exclude_css' => implode("\n", $mergedExclude)]);
                
                wp_safe_redirect(add_query_arg(['msg' => 'css_applied', 'count' => count($cssToExclude)], $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // Apply JS exclusions suggestions
            if (isset($_POST['apply_js_exclusions'])) {
                $excludeJs = $smartDetector->detectExcludeJs();
                $jsToExclude = [];
                
                // Collect JS with high confidence
                foreach (['core_dependencies', 'plugin_specific', 'inline_dependent'] as $category) {
                    if (!empty($excludeJs[$category])) {
                        foreach ($excludeJs[$category] as $item) {
                            if ($item['confidence'] >= 0.7) {
                                $jsToExclude[] = $item['handle'];
                            }
                        }
                    }
                }
                
                // Merge with current textarea value (preserves user's manual edits)
                $currentExclude = isset($_POST['exclude_js']) ? wp_unslash($_POST['exclude_js']) : (!empty($settings['exclude_js']) ? $settings['exclude_js'] : '');
                $currentExcludeArray = array_filter(array_map('trim', explode("\n", $currentExclude)));
                $mergedExclude = array_unique(array_merge($currentExcludeArray, $jsToExclude));
                
                $optimizer->update(['exclude_js' => implode("\n", $mergedExclude)]);
                
                wp_safe_redirect(add_query_arg(['msg' => 'js_applied', 'count' => count($jsToExclude)], $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // Apply critical assets suggestions
            if (isset($_POST['apply_critical_assets_suggestions'])) {
                $result = $assetsDetector->autoApplyCriticalAssets(false, $optimizer);
                wp_safe_redirect(add_query_arg(['msg' => 'assets_applied', 'count' => $result['applied']], $_SERVER['REQUEST_URI']));
                exit;
            }
            
            // ============================================
            // SEZIONE SALVATAGGIO IMPOSTAZIONI STANDARD
            // ============================================
            
            // Determina quale form è stato inviato
            $formType = sanitize_text_field($_POST['form_type'] ?? '');
            
            if ($formType === 'delivery') {
                // Salva solo le impostazioni di delivery
                $dnsPrefetch = array_filter(array_map('trim', explode("\n", wp_unslash($_POST['dns_prefetch'] ?? ''))));
                $preload = array_filter(array_map('trim', explode("\n", wp_unslash($_POST['preload'] ?? ''))));
                
                // Preserva le esclusioni esistenti se i campi non sono presenti nel POST
                // Questo previene la sovrascrittura accidentale delle esclusioni dell'automazione
                // Se i campi sono presenti ma vuoti, l'utente intenzionalmente li ha cancellati
                $excludeCss = isset($_POST['exclude_css']) 
                    ? wp_unslash($_POST['exclude_css']) 
                    : (!empty($settings['exclude_css']) ? $settings['exclude_css'] : '');
                $excludeJs = isset($_POST['exclude_js']) 
                    ? wp_unslash($_POST['exclude_js']) 
                    : (!empty($settings['exclude_js']) ? $settings['exclude_js'] : '');
                
                $optimizer->update([
                    'minify_html' => !empty($_POST['minify_html']),
                    'defer_js' => !empty($_POST['defer_js']),
                    'async_js' => !empty($_POST['async_js']),
                    'remove_emojis' => !empty($_POST['remove_emojis']),
                    'dns_prefetch' => $dnsPrefetch,
                    'preload' => $preload,
                    'heartbeat_admin' => (int) ($_POST['heartbeat_admin'] ?? 60),
                    'combine_css' => !empty($_POST['combine_css']),
                    'combine_js' => !empty($_POST['combine_js']),
                    'exclude_css' => $excludeCss,
                    'exclude_js' => $excludeJs,
                    'minify_inline_css' => !empty($_POST['minify_inline_css']),
                    'minify_inline_js' => !empty($_POST['minify_inline_js']),
                    'remove_comments' => !empty($_POST['remove_comments']),
                    'optimize_google_fonts' => !empty($_POST['optimize_google_fonts_assets']),
                    'preload_critical_assets' => !empty($_POST['preload_critical_assets']),
                    'critical_assets_list' => array_filter(array_map('trim', explode("\n", wp_unslash($_POST['critical_assets'] ?? '')))),
                ]);
                
                // Ricarica le impostazioni dopo il salvataggio per mostrare i valori aggiornati
                $settings = $optimizer->settings();
                
                $message = __('Delivery settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'pagespeed') {
                // Salva solo le impostazioni PageSpeed (font)
                $fontOptimizer->updateSettings([
                    'enabled' => !empty($_POST['font_optimizer_enabled']),
                    'optimize_google_fonts' => !empty($_POST['optimize_google_fonts']),
                    'preload_fonts' => !empty($_POST['preload_fonts']),
                    'preconnect_providers' => !empty($_POST['preconnect_providers']),
                ]);
                
                // Ricarica le impostazioni dopo il salvataggio
                $fontSettings = $fontOptimizer->getSettings();
                
                $message = __('Font Optimizer settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'third_party') {
                // Salva solo le impostazioni degli script di terze parti
                $thirdPartyScripts->update([
                    'enabled' => !empty($_POST['third_party_enabled']),
                    'delay_all' => !empty($_POST['third_party_delay_all']),
                    'delay_timeout' => (int) ($_POST['third_party_timeout'] ?? 5000),
                    'load_on' => sanitize_text_field($_POST['third_party_load_on'] ?? 'interaction'),
                    'scripts' => [
                        'google_analytics' => ['enabled' => !empty($_POST['third_party_ga']), 'delay' => true],
                        'facebook_pixel' => ['enabled' => !empty($_POST['third_party_fb']), 'delay' => true],
                        'google_ads' => ['enabled' => !empty($_POST['third_party_ads']), 'delay' => true],
                        'hotjar' => ['enabled' => !empty($_POST['third_party_hotjar']), 'delay' => true],
                        'intercom' => ['enabled' => !empty($_POST['third_party_intercom']), 'delay' => true],
                        'youtube' => ['enabled' => !empty($_POST['third_party_youtube']), 'delay' => true],
                        'linkedin_insight' => ['enabled' => !empty($_POST['third_party_linkedin']), 'delay' => true],
                        'twitter_pixel' => ['enabled' => !empty($_POST['third_party_twitter']), 'delay' => true],
                        'tiktok_pixel' => ['enabled' => !empty($_POST['third_party_tiktok']), 'delay' => true],
                        'pinterest_tag' => ['enabled' => !empty($_POST['third_party_pinterest']), 'delay' => true],
                        'hubspot' => ['enabled' => !empty($_POST['third_party_hubspot']), 'delay' => true],
                        'zendesk' => ['enabled' => !empty($_POST['third_party_zendesk']), 'delay' => true],
                        'drift' => ['enabled' => !empty($_POST['third_party_drift']), 'delay' => true],
                        'crisp' => ['enabled' => !empty($_POST['third_party_crisp']), 'delay' => true],
                        'tidio' => ['enabled' => !empty($_POST['third_party_tidio']), 'delay' => true],
                        'segment' => ['enabled' => !empty($_POST['third_party_segment']), 'delay' => true],
                        'mixpanel' => ['enabled' => !empty($_POST['third_party_mixpanel']), 'delay' => true],
                        'mailchimp' => ['enabled' => !empty($_POST['third_party_mailchimp']), 'delay' => true],
                        'stripe' => ['enabled' => !empty($_POST['third_party_stripe']), 'delay' => true],
                        'paypal' => ['enabled' => !empty($_POST['third_party_paypal']), 'delay' => true],
                        'recaptcha' => ['enabled' => !empty($_POST['third_party_recaptcha']), 'delay' => true],
                        'google_maps' => ['enabled' => !empty($_POST['third_party_gmaps']), 'delay' => true],
                        'microsoft_clarity' => ['enabled' => !empty($_POST['third_party_clarity']), 'delay' => true],
                        'vimeo' => ['enabled' => !empty($_POST['third_party_vimeo']), 'delay' => true],
                        'tawk_to' => ['enabled' => !empty($_POST['third_party_tawk']), 'delay' => true],
                        'optimizely' => ['enabled' => !empty($_POST['third_party_optimizely']), 'delay' => true],
                        'trustpilot' => ['enabled' => !empty($_POST['third_party_trustpilot']), 'delay' => true],
                        'klaviyo' => ['enabled' => !empty($_POST['third_party_klaviyo']), 'delay' => true],
                        'onetrust' => ['enabled' => !empty($_POST['third_party_onetrust']), 'delay' => true],
                        'calendly' => ['enabled' => !empty($_POST['third_party_calendly']), 'delay' => true],
                        'fullstory' => ['enabled' => !empty($_POST['third_party_fullstory']), 'delay' => true],
                        'snapchat_pixel' => ['enabled' => !empty($_POST['third_party_snapchat']), 'delay' => true],
                        'soundcloud' => ['enabled' => !empty($_POST['third_party_soundcloud']), 'delay' => true],
                        'klarna' => ['enabled' => !empty($_POST['third_party_klarna']), 'delay' => true],
                        'spotify' => ['enabled' => !empty($_POST['third_party_spotify']), 'delay' => true],
                        'livechat' => ['enabled' => !empty($_POST['third_party_livechat']), 'delay' => true],
                        'activecampaign' => ['enabled' => !empty($_POST['third_party_activecampaign']), 'delay' => true],
                        'userway' => ['enabled' => !empty($_POST['third_party_userway']), 'delay' => true],
                        'typeform' => ['enabled' => !empty($_POST['third_party_typeform']), 'delay' => true],
                    ],
                ]);
                
                // Ricarica le impostazioni dopo il salvataggio
                $thirdPartySettings = $thirdPartyScripts->settings();
                
                $message = __('Third-Party Script settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'http2_push') {
                $http2Push->update([
                    'enabled' => !empty($_POST['http2_push_enabled']),
                    'push_css' => !empty($_POST['http2_push_css']),
                    'push_js' => !empty($_POST['http2_push_js']),
                    'push_fonts' => !empty($_POST['http2_push_fonts']),
                    'push_images' => !empty($_POST['http2_push_images']),
                    'max_resources' => (int) ($_POST['http2_max_resources'] ?? 10),
                    'critical_only' => !empty($_POST['http2_critical_only']),
                ]);
                $message = __('HTTP/2 Server Push settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'smart_delivery') {
                $smartDelivery->update([
                    'enabled' => !empty($_POST['smart_delivery_enabled']),
                    'detect_connection' => !empty($_POST['smart_detect_connection']),
                    'save_data_mode' => !empty($_POST['smart_save_data_mode']),
                    'adaptive_images' => !empty($_POST['smart_adaptive_images']),
                    'adaptive_videos' => !empty($_POST['smart_adaptive_videos']),
                    'quality_slow' => (int) ($_POST['smart_quality_slow'] ?? 50),
                    'quality_moderate' => (int) ($_POST['smart_quality_moderate'] ?? 70),
                    'quality_fast' => (int) ($_POST['smart_quality_fast'] ?? 85),
                ]);
                $message = __('Smart Asset Delivery settings saved.', 'fp-performance-suite');
            } elseif ($formType === 'script_detector') {
                // Handle script detector actions
                if (isset($_POST['action_scan'])) {
                    $scriptDetector->scanHomepage();
                    $message = __('Scan completed! Check suggestions below.', 'fp-performance-suite');
                } elseif (isset($_POST['action_add_custom'])) {
                    $scriptDetector->addCustomScript([
                        'name' => sanitize_text_field($_POST['script_name'] ?? ''),
                        'patterns' => array_filter(array_map('trim', explode("\n", wp_unslash($_POST['script_patterns'] ?? '')))),
                        'enabled' => !empty($_POST['script_enabled']),
                        'delay' => !empty($_POST['script_delay']),
                    ]);
                    $message = __('Custom script added successfully!', 'fp-performance-suite');
                } elseif (isset($_POST['action_auto_add'])) {
                    $hash = sanitize_text_field($_POST['script_hash'] ?? '');
                    if ($scriptDetector->autoAddFromSuggestion($hash)) {
                        $message = __('Script automatically added to managed list!', 'fp-performance-suite');
                    }
                } elseif (isset($_POST['action_dismiss'])) {
                    $hash = sanitize_text_field($_POST['script_hash'] ?? '');
                    $scriptDetector->dismissScript($hash);
                    $message = __('Suggestion dismissed.', 'fp-performance-suite');
                } elseif (isset($_POST['action_remove_custom'])) {
                    $key = sanitize_key($_POST['custom_key'] ?? '');
                    if ($scriptDetector->removeCustomScript($key)) {
                        $message = __('Custom script removed.', 'fp-performance-suite');
                    }
                }
            }
        }
        
        // Le impostazioni principali sono già state caricate all'inizio
        // Carichiamo solo le impostazioni rimanenti necessarie per il rendering
        $thirdPartyStatus = $thirdPartyScripts->status();
        $http2Settings = $http2Push->settings();
        $smartDeliverySettings = $smartDelivery->settings();
        ob_start();
        ?>
        <?php if ($message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($message); ?></p></div>
        <?php endif; ?>
        
        <!-- Auto-Configuration Section for Assets -->
        <section class="fp-ps-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <h2 style="color: white; margin-top: 0;">
                🤖 <?php esc_html_e('Configurazione Automatica Intelligente Assets', 'fp-performance-suite'); ?>
            </h2>
            <p style="color: rgba(255,255,255,0.9); font-size: 15px;">
                <?php esc_html_e('Sistema AI che rileva, consiglia e applica automaticamente le migliori impostazioni di ottimizzazione per CSS, JavaScript e asset critici del tuo sito.', 'fp-performance-suite'); ?>
            </p>
            
            <div style="background: rgba(255,255,255,0.15); border-radius: 8px; padding: 20px; margin: 20px 0; backdrop-filter: blur(10px);">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo $criticalScripts ? count(array_merge(
                                $criticalScripts['always_exclude'] ?? [],
                                $criticalScripts['plugin_critical'] ?? [],
                                $criticalScripts['dependency_critical'] ?? []
                            )) : '0'; ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('Script Critici Rilevati', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo $excludeCss ? count(array_merge(
                                $excludeCss['plugin_specific'] ?? [],
                                $excludeCss['critical_files'] ?? [],
                                $excludeCss['admin_styles'] ?? []
                            )) : '0'; ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('CSS da Escludere', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo $excludeJs ? count(array_merge(
                                $excludeJs['plugin_specific'] ?? [],
                                $excludeJs['core_dependencies'] ?? [],
                                $excludeJs['inline_dependent'] ?? []
                            )) : '0'; ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('JavaScript da Escludere', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 32px; font-weight: bold; color: #ffd700;">
                            <?php echo $criticalAssets ? ($criticalAssets['summary']['total_assets'] ?? 0) : '0'; ?>
                        </div>
                        <div style="font-size: 13px; opacity: 0.9; margin-top: 5px;">
                            <?php esc_html_e('Asset Critici Rilevati', 'fp-performance-suite'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <button type="submit" name="auto_detect_scripts" value="1" class="button button-secondary" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); color: white; padding: 10px 20px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        🔍 <?php esc_html_e('Rileva Script Critici', 'fp-performance-suite'); ?>
                    </button>
                    <button type="submit" name="auto_detect_exclude_css" value="1" class="button button-secondary" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); color: white; padding: 10px 20px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        🎨 <?php esc_html_e('Rileva CSS da Escludere', 'fp-performance-suite'); ?>
                    </button>
                    <button type="submit" name="auto_detect_exclude_js" value="1" class="button button-secondary" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); color: white; padding: 10px 20px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        ⚡ <?php esc_html_e('Rileva JS da Escludere', 'fp-performance-suite'); ?>
                    </button>
                    <button type="submit" name="auto_detect_critical_assets" value="1" class="button button-secondary" style="background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); color: white; padding: 10px 20px; font-weight: 600; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                        🚀 <?php esc_html_e('Rileva Asset Critici', 'fp-performance-suite'); ?>
                    </button>
                </div>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; margin-top: 15px;">
                    <?php if ($criticalScripts) : ?>
                    <button type="submit" name="apply_critical_suggestions" value="1" class="button button-primary" style="background: #ffd700; border: none; color: #1e3a8a; padding: 10px 20px; font-weight: 700; box-shadow: 0 4px 15px rgba(255,215,0,0.4);">
                        ✨ <?php esc_html_e('Applica Script Critici', 'fp-performance-suite'); ?>
                    </button>
                    <?php endif; ?>
                    <?php if ($excludeCss) : ?>
                    <button type="submit" name="apply_css_exclusions" value="1" class="button button-primary" style="background: #ffd700; border: none; color: #1e3a8a; padding: 10px 20px; font-weight: 700; box-shadow: 0 4px 15px rgba(255,215,0,0.4);">
                        ✨ <?php esc_html_e('Applica Esclusioni CSS', 'fp-performance-suite'); ?>
                    </button>
                    <?php endif; ?>
                    <?php if ($excludeJs) : ?>
                    <button type="submit" name="apply_js_exclusions" value="1" class="button button-primary" style="background: #ffd700; border: none; color: #1e3a8a; padding: 10px 20px; font-weight: 700; box-shadow: 0 4px 15px rgba(255,215,0,0.4);">
                        ✨ <?php esc_html_e('Applica Esclusioni JS', 'fp-performance-suite'); ?>
                    </button>
                    <?php endif; ?>
                    <?php if ($criticalAssets) : ?>
                    <button type="submit" name="apply_critical_assets_suggestions" value="1" class="button button-primary" style="background: #ffd700; border: none; color: #1e3a8a; padding: 10px 20px; font-weight: 700; box-shadow: 0 4px 15px rgba(255,215,0,0.4);">
                        ✨ <?php esc_html_e('Applica Asset Critici', 'fp-performance-suite'); ?>
                    </button>
                    <?php endif; ?>
                    <button type="button" class="button" onclick="document.getElementById('fp-ps-assets-auto-config-details').style.display = document.getElementById('fp-ps-assets-auto-config-details').style.display === 'none' ? 'block' : 'none';" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white;">
                        📋 <?php esc_html_e('Mostra Dettagli', 'fp-performance-suite'); ?>
                    </button>
                </div>
            </form>
            
            <!-- Dettagli Suggerimenti -->
            <div id="fp-ps-assets-auto-config-details" style="display: none; margin-top: 25px; background: rgba(255,255,255,0.95); color: #333; border-radius: 8px; padding: 20px;">
                <h3 style="margin-top: 0; color: #667eea;">
                    <?php esc_html_e('Dettagli Suggerimenti Rilevati', 'fp-performance-suite'); ?>
                </h3>
                
                <!-- Script Critici -->
                <?php if ($criticalScripts) : ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #0ea5e9;">🔍 <?php esc_html_e('Script Critici Rilevati', 'fp-performance-suite'); ?></h4>
                    
                    <?php if (!empty($criticalScripts['always_exclude'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🛡️ <?php esc_html_e('Always Exclude (Core Dependencies)', 'fp-performance-suite'); ?>
                        </h5>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
                            <?php foreach ($criticalScripts['always_exclude'] as $script) : ?>
                                <span style="background: #dc2626; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                                    <?php echo esc_html($script); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($criticalScripts['plugin_critical'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🔌 <?php esc_html_e('Plugin Critical Scripts', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach ($criticalScripts['plugin_critical'] as $item) : ?>
                            <div style="background: #fef3c7; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html($item['pattern']); ?></strong> - 
                                <em><?php echo esc_html($item['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($criticalScripts['dependency_critical'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🔗 <?php esc_html_e('High-Dependency Scripts', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($criticalScripts['dependency_critical'], 0, 5) as $item) : ?>
                            <div style="background: #e0e7ff; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html($item['handle']); ?></strong> - 
                                <em><?php echo esc_html($item['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- CSS da Escludere -->
                <?php if ($excludeCss) : ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #f59e0b;">🎨 <?php esc_html_e('CSS Files to Exclude Detected', 'fp-performance-suite'); ?></h4>
                    
                    <?php if (!empty($excludeCss['plugin_specific'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🔌 <?php esc_html_e('Plugin-Specific CSS', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($excludeCss['plugin_specific'], 0, 10) as $item) : ?>
                            <div style="background: #e0f2fe; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html($item['handle']); ?></strong>
                                <?php if (!empty($item['src'])) : ?>
                                    <br><small style="color: #555;"><?php echo esc_html($item['src']); ?></small>
                                <?php endif; ?>
                                <br><em style="color: #b45309;"><?php echo esc_html($item['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($excludeCss['critical_files'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🎯 <?php esc_html_e('Critical Theme Files', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($excludeCss['critical_files'], 0, 5) as $item) : ?>
                            <div style="background: #e0f2fe; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html($item['handle']); ?></strong> - 
                                <em><?php echo esc_html($item['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- JavaScript da Escludere -->
                <?php if ($excludeJs) : ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #10b981;">⚡ <?php esc_html_e('JavaScript Files to Exclude Detected', 'fp-performance-suite'); ?></h4>
                    
                    <?php if (!empty($excludeJs['plugin_specific'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🔌 <?php esc_html_e('Plugin-Specific JavaScript', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($excludeJs['plugin_specific'], 0, 10) as $item) : ?>
                            <div style="background: #fef3c7; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html($item['handle']); ?></strong>
                                <?php if (!empty($item['src'])) : ?>
                                    <br><small style="color: #555;"><?php echo esc_html($item['src']); ?></small>
                                <?php endif; ?>
                                <br><em style="color: #b45309;"><?php echo esc_html($item['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($excludeJs['core_dependencies'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🔗 <?php esc_html_e('Core Dependencies', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($excludeJs['core_dependencies'], 0, 5) as $item) : ?>
                            <div style="background: #e0e7ff; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html($item['handle']); ?></strong> - 
                                <em><?php echo esc_html($item['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Asset Critici -->
                <?php if ($criticalAssets) : ?>
                <div style="margin-bottom: 20px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <h4 style="margin-top: 0; color: #f59e0b;">🚀 <?php esc_html_e('Critical Assets Detected', 'fp-performance-suite'); ?></h4>
                    
                    <p style="font-size: 13px; color: #666;">
                        <?php printf(
                            __('Trovati %d asset critici: %d CSS, %d immagini, %d font, %d JS', 'fp-performance-suite'),
                            $criticalAssets['summary']['total_assets'],
                            $criticalAssets['summary']['by_type']['css'],
                            $criticalAssets['summary']['by_type']['images'],
                            $criticalAssets['summary']['by_type']['fonts'],
                            $criticalAssets['summary']['by_type']['js']
                        ); ?>
                    </p>
                    
                    <?php if (!empty($criticalAssets['css'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🎨 <?php esc_html_e('Critical CSS Files', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($criticalAssets['css'], 0, 5) as $asset) : ?>
                            <div style="background: #e0f2fe; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html(basename($asset['url'])); ?></strong><br>
                                <small style="color: #555;"><?php echo esc_html($asset['url']); ?></small><br>
                                <em style="color: #0369a1;"><?php echo esc_html($asset['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($criticalAssets['images'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🖼️ <?php esc_html_e('Critical Images', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($criticalAssets['images'], 0, 5) as $asset) : ?>
                            <div style="background: #fef3c7; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html(basename($asset['url'])); ?></strong>
                                <?php if (isset($asset['dimensions'])) : ?>
                                    <span style="color: #666;">(<?php echo esc_html($asset['dimensions']); ?>)</span>
                                <?php endif; ?>
                                <br><em style="color: #b45309;"><?php echo esc_html($asset['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (!empty($criticalAssets['fonts'])) : ?>
                        <h5 style="font-size: 13px; text-transform: uppercase; color: #666; margin-top: 15px;">
                            🔤 <?php esc_html_e('Critical Fonts', 'fp-performance-suite'); ?>
                        </h5>
                        <?php foreach (array_slice($criticalAssets['fonts'], 0, 5) as $asset) : ?>
                            <div style="background: #f3e8ff; padding: 8px; margin: 5px 0; border-radius: 4px; font-size: 12px;">
                                <strong><?php echo esc_html(basename($asset['url'])); ?></strong><br>
                                <em style="color: #7c3aed;"><?php echo esc_html($asset['reason']); ?></em>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!$criticalScripts && !$excludeCss && !$excludeJs && !$criticalAssets) : ?>
                <div style="padding: 15px; background: #e7f5ff; border-left: 4px solid #2271b1; border-radius: 4px; text-align: center;">
                    <p style="margin: 0; color: #2271b1;">
                        ℹ️ <?php esc_html_e('Nessun suggerimento disponibile. Clicca sui pulsanti di rilevamento sopra per analizzare il tuo sito.', 'fp-performance-suite'); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="fp-ps-card">
            <h2><?php esc_html_e('Delivery', 'fp-performance-suite'); ?></h2>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="delivery" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify HTML output', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove spazi bianchi e commenti HTML per ridurre la dimensione della pagina.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Potrebbe causare problemi con JavaScript inline o alcuni builder.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Consigliato: Attiva e testa accuratamente tutte le pagine del sito.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="minify_html" value="1" <?php checked($settings['minify_html']); ?> />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Defer JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Posticipa l\'esecuzione degli script JavaScript dopo il caricamento della pagina.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Può causare errori con script che dipendono da jQuery o altri script caricati prima.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚡ Consigliato: Migliora significativamente il First Contentful Paint. Testa animazioni e funzionalità interattive.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="defer_js" value="1" <?php checked($settings['defer_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Async JavaScript', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Carica gli script in modo asincrono senza bloccare il rendering.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Gli script potrebbero eseguirsi in ordine diverso, causando errori di dipendenza.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('⚠️ Usa con cautela: Non combinare con Defer. Testa approfonditamente.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="async_js" value="1" <?php checked($settings['async_js']); ?> data-risk="amber" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine CSS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red">
                            <div class="fp-ps-risk-tooltip red">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">🔴</span>
                                    <?php esc_html_e('Rischio Alto', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Combina tutti i file CSS in un unico file per ridurre le richieste HTTP.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Alto rischio di rottura del layout, problemi con media queries e specificity CSS.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ Sconsigliato: Usa HTTP/2 invece, che gestisce meglio file multipli. Attiva solo se assolutamente necessario e testa molto bene.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="combine_css" value="1" <?php checked($settings['combine_css']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Combine JS files', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator red">
                            <div class="fp-ps-risk-tooltip red">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">🔴</span>
                                    <?php esc_html_e('Rischio Alto', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Combina tutti i file JavaScript in un unico file.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Molto alto rischio di errori JavaScript, problemi di dipendenze e conflitti tra script.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('❌ Sconsigliato: Può rompere funzionalità critiche. Meglio usare il defer invece della combinazione.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="combine_js" value="1" <?php checked($settings['combine_js']); ?> data-risk="red" />
                </label>
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove emojis script', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rimuove lo script WordPress per il supporto emoji legacy. I browser moderni li supportano nativamente.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Riduce le richieste HTTP senza rischi. Attiva sempre.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="remove_emojis" value="1" <?php checked($settings['remove_emojis']); ?> data-risk="green" />
                </label>
                <p>
                    <label for="heartbeat_admin"><?php esc_html_e('Heartbeat interval (seconds)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="heartbeat_admin" id="heartbeat_admin" value="<?php echo esc_attr((string) $settings['heartbeat_admin']); ?>" min="15" step="5" />
                </p>
                <p>
                    <label for="dns_prefetch"><?php esc_html_e('DNS Prefetch domains (one per line)', 'fp-performance-suite'); ?></label>
                    <textarea name="dns_prefetch" id="dns_prefetch" rows="4" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['dns_prefetch'])); ?></textarea>
                </p>
                <p>
                    <label for="preload"><?php esc_html_e('Preload resources (full URLs)', 'fp-performance-suite'); ?></label>
                    <textarea name="preload" id="preload" rows="4" class="large-text code"><?php echo esc_textarea(implode("\n", $settings['preload'])); ?></textarea>
                </p>
                <p>
                    <label for="exclude_css"><?php esc_html_e('Exclude CSS from optimization', 'fp-performance-suite'); ?></label>
                    <textarea name="exclude_css" id="exclude_css" rows="4" class="large-text code" placeholder="<?php esc_attr_e('One handle or URL per line. Examples:\nstyle-handle\n/wp-content/themes/mytheme/custom.css', 'fp-performance-suite'); ?>"><?php echo esc_textarea($settings['exclude_css'] ?? ''); ?></textarea>
                    <span class="description"><?php esc_html_e('CSS files to exclude from minification and combine. Use script handle or partial URL.', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label for="exclude_js"><?php esc_html_e('Exclude JavaScript from optimization', 'fp-performance-suite'); ?></label>
                    <textarea name="exclude_js" id="exclude_js" rows="4" class="large-text code" placeholder="<?php esc_attr_e('One handle or URL per line. Examples:\njquery\njquery-core\n/wp-includes/js/jquery/jquery.js', 'fp-performance-suite'); ?>"><?php echo esc_textarea($settings['exclude_js'] ?? ''); ?></textarea>
                    <span class="description"><?php esc_html_e('JavaScript files to exclude from defer/async/combine. Use script handle or partial URL.', 'fp-performance-suite'); ?></span>
                </p>
                
                <div style="margin: 20px 0; padding: 15px; background: #f8fafc; border-radius: 6px;">
                    <button type="submit" class="button button-primary button-large">
                        💾 <?php esc_html_e('Save Exclusions', 'fp-performance-suite'); ?>
                    </button>
                    <span class="description" style="margin-left: 15px; color: #666;">
                        <?php esc_html_e('Save manual changes to CSS and JavaScript exclusions', 'fp-performance-suite'); ?>
                    </span>
                </div>
                
                <h3 style="margin-top: 30px;"><?php esc_html_e('Advanced Minification Options', 'fp-performance-suite'); ?></h3>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify inline CSS', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="minify_inline_css" value="1" <?php checked($settings['minify_inline_css'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Minify CSS code embedded directly in HTML <style> tags. Reduces HTML size.', 'fp-performance-suite'); ?>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Minify inline JavaScript', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="minify_inline_js" value="1" <?php checked($settings['minify_inline_js'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Minify JavaScript code embedded in HTML <script> tags. Use with caution.', 'fp-performance-suite'); ?>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Remove CSS/JS comments', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="remove_comments" value="1" <?php checked($settings['remove_comments'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Strip all comments from CSS and JavaScript files during optimization.', 'fp-performance-suite'); ?>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Optimize Google Fonts loading', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="optimize_google_fonts_assets" value="1" <?php checked($settings['optimize_google_fonts'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Add display=swap and preconnect hints for Google Fonts. Improves FCP score.', 'fp-performance-suite'); ?>
                </p>
                
                <h3 style="margin-top: 30px;"><?php esc_html_e('Critical Assets Preloading', 'fp-performance-suite'); ?></h3>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Enable critical assets preloading', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="preload_critical_assets" value="1" <?php checked($settings['preload_critical_assets'] ?? false); ?> />
                </label>
                <p class="description" style="margin-left: 30px;">
                    <?php esc_html_e('Preload the most important assets to improve initial page load time.', 'fp-performance-suite'); ?>
                </p>
                
                <p>
                    <label for="critical_assets">
                        <?php esc_html_e('Critical assets to preload', 'fp-performance-suite'); ?>
                        <?php if (!empty($settings['critical_assets_list'])) : ?>
                            <span style="background: #059669; color: white; padding: 3px 8px; border-radius: 10px; font-size: 11px; margin-left: 8px;">
                                ✓ <?php echo count($settings['critical_assets_list']); ?> asset<?php echo count($settings['critical_assets_list']) > 1 ? 's' : ''; ?> configurati
                            </span>
                        <?php endif; ?>
                    </label>
                    <textarea name="critical_assets" id="critical_assets" rows="8" class="large-text" placeholder="<?php esc_attr_e('/wp-content/themes/mytheme/style.css\n/wp-content/themes/mytheme/hero-image.jpg\n/wp-content/themes/mytheme/main.js', 'fp-performance-suite'); ?>"><?php echo esc_textarea(implode("\n", $settings['critical_assets_list'] ?? [])); ?></textarea>
                    <span class="description">
                        <?php esc_html_e('Full URLs or paths of critical assets to preload. One per line. Use for above-the-fold resources only.', 'fp-performance-suite'); ?>
                        <?php if (!empty($settings['critical_assets_list'])) : ?>
                            <br><strong style="color: #059669;">✓ <?php esc_html_e('Asset preloading is active', 'fp-performance-suite'); ?></strong>
                        <?php endif; ?>
                    </span>
                </p>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin: 20px 0;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Minification Best Practices:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Test inline minification thoroughly - can break some scripts', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Only preload 2-3 truly critical assets (hero image, main CSS)', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Too many preload tags can actually slow down the page', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Use browser DevTools to identify render-blocking resources', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p>
                    <button type="submit" class="button button-primary button-large"><?php esc_html_e('Save Delivery Settings', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2><?php esc_html_e('Font Optimization', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">v1.2.0</span></h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Ottimizza il caricamento dei font per migliorare FCP e CLS', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="pagespeed" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita ottimizzazione font', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Ottimizza il caricamento dei font con display=swap e preconnect.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Migliora FCP e elimina CLS causato dai font. Previene il flash di testo invisibile.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Consigliato: Specialmente per siti con Google Fonts. Impatto PageSpeed: +5-8 punti.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="font_optimizer_enabled" value="1" <?php checked($fontSettings['enabled']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Ottimizza Google Fonts (display=swap)', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="optimize_google_fonts" value="1" <?php checked($fontSettings['optimize_google_fonts']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Preload font critici', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="preload_fonts" value="1" <?php checked($fontSettings['preload_fonts']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Preconnect a font providers', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="preconnect_providers" value="1" <?php checked($fontSettings['preconnect_providers']); ?> />
                </label>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Impatto previsto su PageSpeed:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Font Optimization: +5-8 punti Mobile', 'fp-performance-suite'); ?></li>
                    </ul>
                    <p style="margin: 10px 0 0 0; color: #555; font-size: 13px;">
                        <?php esc_html_e('Per ottimizzazioni immagini (Lazy Loading, dimensioni, WebP, AVIF) vai alla sezione', 'fp-performance-suite'); ?> 
                        <a href="<?php echo esc_url(admin_url('admin.php?page=fp-performance-suite-media')); ?>"><?php esc_html_e('Media Optimization', 'fp-performance-suite'); ?></a>.
                    </p>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Font', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>
                🔌 <?php esc_html_e('Third-Party Script Manager', 'fp-performance-suite'); ?> 
                <span class="fp-ps-badge green" style="font-size: 0.7em;">v1.2.0</span>
                <?php echo $hints->renderInlineHint('third_party_scripts'); ?>
            </h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Gestisce il caricamento ritardato di script di terze parti (analytics, social, ads) per migliorare i tempi di caricamento iniziali e i Core Web Vitals.', 'fp-performance-suite'); ?></p>
            
            <?php if ($thirdPartyStatus['enabled']): ?>
                <div class="notice notice-info inline" style="margin: 15px 0;">
                    <p>
                        <strong><?php esc_html_e('Stato:', 'fp-performance-suite'); ?></strong>
                        <?php printf(
                            esc_html__('Attivo - Gestione di %d script di terze parti', 'fp-performance-suite'),
                            $thirdPartyStatus['managed_scripts']
                        ); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="third_party" />
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Third-Party Script Manager', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Ritarda il caricamento di script di terze parti fino all\'interazione dell\'utente o dopo un timeout.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Migliora significativamente TTI, TBT e FCP. Riduce il blocking time iniziale del 40-60%.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Consiglio', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('✅ Altamente consigliato: Essenziale per siti con analytics e pixel di tracking. Impatto PageSpeed: +8-12 punti.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="third_party_enabled" value="1" <?php checked($thirdPartySettings['enabled']); ?> />
                </label>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;" />
                
                <h3><?php esc_html_e('Impostazioni Generali', 'fp-performance-suite'); ?></h3>
                
                <p>
                    <label for="third_party_load_on"><?php esc_html_e('Carica script al', 'fp-performance-suite'); ?></label>
                    <select name="third_party_load_on" id="third_party_load_on">
                        <option value="interaction" <?php selected($thirdPartySettings['load_on'], 'interaction'); ?>><?php esc_html_e('Prima interazione utente (consigliato)', 'fp-performance-suite'); ?></option>
                        <option value="scroll" <?php selected($thirdPartySettings['load_on'], 'scroll'); ?>><?php esc_html_e('Primo scroll', 'fp-performance-suite'); ?></option>
                        <option value="timeout" <?php selected($thirdPartySettings['load_on'], 'timeout'); ?>><?php esc_html_e('Timeout fisso', 'fp-performance-suite'); ?></option>
                    </select>
                    <span class="description"><?php esc_html_e('Quando caricare gli script ritardati', 'fp-performance-suite'); ?></span>
                </p>
                
                <p>
                    <label for="third_party_timeout"><?php esc_html_e('Timeout fallback (ms)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="third_party_timeout" id="third_party_timeout" value="<?php echo esc_attr((string) $thirdPartySettings['delay_timeout']); ?>" min="1000" max="30000" step="1000" style="width: 100px;" />
                    <span class="description"><?php esc_html_e('Carica gli script dopo questo tempo anche senza interazione', 'fp-performance-suite'); ?></span>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Ritarda tutti gli script (modalità aggressiva)', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Attenzione: ritarda TUTTI gli script tranne quelli di WordPress core. Usare solo se sai cosa stai facendo.', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="third_party_delay_all" value="1" <?php checked($thirdPartySettings['delay_all']); ?> />
                </label>
                
                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ddd;" />
                
                <h3><?php esc_html_e('Script Gestiti', 'fp-performance-suite'); ?></h3>
                <p class="description"><?php esc_html_e('Seleziona quali script di terze parti ritardare automaticamente:', 'fp-performance-suite'); ?></p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📊 Google Analytics</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('GA4, GTM, Universal Analytics', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_ga" value="1" <?php checked($thirdPartySettings['scripts']['google_analytics']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>👍 Facebook Pixel</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Meta Pixel, FB Events', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_fb" value="1" <?php checked($thirdPartySettings['scripts']['facebook_pixel']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💰 Google Ads</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('AdWords, AdSense', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_ads" value="1" <?php checked($thirdPartySettings['scripts']['google_ads']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🔥 Hotjar</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Heatmaps, Recordings', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_hotjar" value="1" <?php checked($thirdPartySettings['scripts']['hotjar']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💬 Intercom</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Live Chat, Support', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_intercom" value="1" <?php checked($thirdPartySettings['scripts']['intercom']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>▶️ YouTube</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Video embeds, iframe API', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_youtube" value="1" <?php checked($thirdPartySettings['scripts']['youtube']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💼 LinkedIn Insight</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Insight Tag, Conversion', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_linkedin" value="1" <?php checked($thirdPartySettings['scripts']['linkedin_insight']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🐦 Twitter/X Pixel</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Twitter Ads, Analytics', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_twitter" value="1" <?php checked($thirdPartySettings['scripts']['twitter_pixel']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🎵 TikTok Pixel</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('TikTok Analytics', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_tiktok" value="1" <?php checked($thirdPartySettings['scripts']['tiktok_pixel']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📌 Pinterest Tag</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Pinterest Conversion', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_pinterest" value="1" <?php checked($thirdPartySettings['scripts']['pinterest_tag']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🧡 HubSpot</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Marketing, CRM, Analytics', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_hubspot" value="1" <?php checked($thirdPartySettings['scripts']['hubspot']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🎧 Zendesk</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Support, Live Chat', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_zendesk" value="1" <?php checked($thirdPartySettings['scripts']['zendesk']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💬 Drift</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Conversational Marketing', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_drift" value="1" <?php checked($thirdPartySettings['scripts']['drift']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💬 Crisp</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Live Chat, Messaging', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_crisp" value="1" <?php checked($thirdPartySettings['scripts']['crisp']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💬 Tidio</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Live Chat, Chatbots', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_tidio" value="1" <?php checked($thirdPartySettings['scripts']['tidio']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📊 Segment</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Customer Data Platform', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_segment" value="1" <?php checked($thirdPartySettings['scripts']['segment']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📈 Mixpanel</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Product Analytics', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_mixpanel" value="1" <?php checked($thirdPartySettings['scripts']['mixpanel']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📧 Mailchimp</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Email Marketing', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_mailchimp" value="1" <?php checked($thirdPartySettings['scripts']['mailchimp']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💳 Stripe</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Payment Processing', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_stripe" value="1" <?php checked($thirdPartySettings['scripts']['stripe']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💳 PayPal</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Payment Processing', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_paypal" value="1" <?php checked($thirdPartySettings['scripts']['paypal']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🤖 reCAPTCHA</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Google reCAPTCHA', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_recaptcha" value="1" <?php checked($thirdPartySettings['scripts']['recaptcha']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🗺️ Google Maps</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Maps API, Embed', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_gmaps" value="1" <?php checked($thirdPartySettings['scripts']['google_maps']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🔍 Microsoft Clarity</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Session Recording, Heatmaps', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_clarity" value="1" <?php checked($thirdPartySettings['scripts']['microsoft_clarity']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>▶️ Vimeo</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Video Player, Embed', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_vimeo" value="1" <?php checked($thirdPartySettings['scripts']['vimeo']['enabled'] ?? false); ?> />
                    </label>
                </div>
                
                <h4 style="margin-top: 25px; color: #1d2327; font-size: 14px; font-weight: 600; border-bottom: 2px solid #2271b1; padding-bottom: 8px;">
                    🔥 <?php esc_html_e('Servizi Ad Alto Impatto (Consigliati)', 'fp-performance-suite'); ?>
                </h4>
                <p class="description" style="margin: 10px 0 15px 0;"><?php esc_html_e('Servizi particolarmente pesanti che beneficiano molto del ritardo.', 'fp-performance-suite'); ?></p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
                    <label class="fp-ps-toggle" style="background: #fff7ed; padding: 12px; border-radius: 4px; border: 2px solid #f59e0b;">
                        <span class="info">
                            <strong>💬 Tawk.to</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Free Live Chat - Popolarissimo', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_tawk" value="1" <?php checked($thirdPartySettings['scripts']['tawk_to']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #fff7ed; padding: 12px; border-radius: 4px; border: 2px solid #f59e0b;">
                        <span class="info">
                            <strong>🧪 Optimizely</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('A/B Testing, Experimentation', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_optimizely" value="1" <?php checked($thirdPartySettings['scripts']['optimizely']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #fff7ed; padding: 12px; border-radius: 4px; border: 2px solid #f59e0b;">
                        <span class="info">
                            <strong>⭐ Trustpilot</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Reviews Widget, Trust Badge', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_trustpilot" value="1" <?php checked($thirdPartySettings['scripts']['trustpilot']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #fff7ed; padding: 12px; border-radius: 4px; border: 2px solid #f59e0b;">
                        <span class="info">
                            <strong>📧 Klaviyo</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('E-commerce Email Marketing', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_klaviyo" value="1" <?php checked($thirdPartySettings['scripts']['klaviyo']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #fff7ed; padding: 12px; border-radius: 4px; border: 2px solid #f59e0b;">
                        <span class="info">
                            <strong>🍪 OneTrust</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Cookie Consent GDPR/CCPA', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_onetrust" value="1" <?php checked($thirdPartySettings['scripts']['onetrust']['enabled'] ?? false); ?> />
                    </label>
                </div>
                
                <h4 style="margin-top: 25px; color: #1d2327; font-size: 14px; font-weight: 600; border-bottom: 2px solid #10b981; padding-bottom: 8px;">
                    ➕ <?php esc_html_e('Altri Servizi Popolari', 'fp-performance-suite'); ?>
                </h4>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📅 Calendly</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Appointment Scheduling', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_calendly" value="1" <?php checked($thirdPartySettings['scripts']['calendly']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🎬 FullStory</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Session Replay, Analytics', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_fullstory" value="1" <?php checked($thirdPartySettings['scripts']['fullstory']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>👻 Snapchat Pixel</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Snap Ads, Conversions', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_snapchat" value="1" <?php checked($thirdPartySettings['scripts']['snapchat_pixel']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🎵 SoundCloud</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Audio Player, Embed', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_soundcloud" value="1" <?php checked($thirdPartySettings['scripts']['soundcloud']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💳 Klarna</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Buy Now Pay Later', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_klarna" value="1" <?php checked($thirdPartySettings['scripts']['klarna']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>🎵 Spotify</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Music Player, Embed', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_spotify" value="1" <?php checked($thirdPartySettings['scripts']['spotify']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>💬 LiveChat</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Customer Support Chat', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_livechat" value="1" <?php checked($thirdPartySettings['scripts']['livechat']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📊 ActiveCampaign</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Marketing Automation', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_activecampaign" value="1" <?php checked($thirdPartySettings['scripts']['activecampaign']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>♿ UserWay</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Accessibility Widget', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_userway" value="1" <?php checked($thirdPartySettings['scripts']['userway']['enabled'] ?? false); ?> />
                    </label>
                    
                    <label class="fp-ps-toggle" style="background: #f9f9f9; padding: 12px; border-radius: 4px; border: 1px solid #ddd;">
                        <span class="info">
                            <strong>📋 Typeform</strong>
                            <span class="description" style="font-size: 12px;"><?php esc_html_e('Interactive Forms, Surveys', 'fp-performance-suite'); ?></span>
                        </span>
                        <input type="checkbox" name="third_party_typeform" value="1" <?php checked($thirdPartySettings['scripts']['typeform']['enabled'] ?? false); ?> />
                    </label>
                </div>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Impatto previsto su PageSpeed:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Time to Interactive (TTI): -30-50%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Total Blocking Time (TBT): -40-60%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('First Contentful Paint (FCP): -10-20%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Punteggio PageSpeed Mobile: +8-12 punti', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Third-Party Scripts', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <?php
        // Get detector data
        $suggestions = $scriptDetector->getSuggestions();
        $customScripts = $scriptDetector->getCustomScripts();
        $stats = $scriptDetector->getStats();
        ?>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>🤖 <?php esc_html_e('Auto-Detect & Custom Scripts', 'fp-performance-suite'); ?> <span class="fp-ps-badge green" style="font-size: 0.7em;">NEW AI</span></h2>
            <p style="color: #666; margin-bottom: 20px;">
                <?php esc_html_e('Il sistema rileva automaticamente script di terze parti non gestiti e ti suggerisce di aggiungerli. Puoi anche aggiungere script personalizzati manualmente.', 'fp-performance-suite'); ?>
            </p>
            
            <?php if ($stats['total_custom'] > 0 || $stats['total_suggestions'] > 0): ?>
            <div style="background: #f0f9ff; border-left: 4px solid #0891b2; padding: 15px; margin-bottom: 20px;">
                <p style="margin: 0; font-weight: 600; color: #0891b2;">📊 <?php esc_html_e('Statistiche Rilevamento:', 'fp-performance-suite'); ?></p>
                <ul style="margin: 10px 0 0 20px; color: #555;">
                    <li><?php printf(esc_html__('Script rilevati totali: %d', 'fp-performance-suite'), $stats['total_detected']); ?></li>
                    <li><?php printf(esc_html__('Script custom attivi: %d', 'fp-performance-suite'), $stats['total_custom']); ?></li>
                    <li><?php printf(esc_html__('Nuovi suggerimenti: %d', 'fp-performance-suite'), $stats['total_suggestions']); ?></li>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Scan Button -->
            <form method="post" style="margin-bottom: 20px;">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="script_detector" />
                <button type="submit" name="action_scan" class="button button-secondary">
                    🔍 <?php esc_html_e('Scansiona Homepage Ora', 'fp-performance-suite'); ?>
                </button>
                <p class="description" style="margin-top: 8px;">
                    <?php esc_html_e('La scansione automatica viene effettuata ogni giorno. Usa questo pulsante per una scansione immediata.', 'fp-performance-suite'); ?>
                </p>
            </form>
            
            <!-- Suggestions -->
            <?php if (!empty($suggestions)): ?>
            <h3 style="margin-top: 25px; border-bottom: 2px solid #10b981; padding-bottom: 8px;">
                💡 <?php esc_html_e('Script Rilevati - Suggerimenti', 'fp-performance-suite'); ?>
            </h3>
            <p class="description" style="margin-bottom: 15px;">
                <?php esc_html_e('Questi script sono stati rilevati sul tuo sito ma non sono ancora gestiti. Puoi aggiungerli alla lista o ignorarli.', 'fp-performance-suite'); ?>
            </p>
            
            <div style="display: grid; gap: 15px;">
                <?php foreach (array_slice($suggestions, 0, 10) as $suggestion): 
                    $categoryColors = [
                        'analytics' => '#3b82f6',
                        'advertising' => '#f59e0b',
                        'chat' => '#10b981',
                        'social' => '#8b5cf6',
                        'payment' => '#ef4444',
                        'video' => '#ec4899',
                        'forms' => '#14b8a6',
                        'unknown' => '#6b7280',
                    ];
                    $color = $categoryColors[$suggestion['category']] ?? '#6b7280';
                ?>
                <div style="background: #fff; border: 1px solid #ddd; border-left: 4px solid <?php echo $color; ?>; padding: 15px; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0 0 8px 0; color: #1d2327;">
                                <?php echo esc_html($suggestion['suggested_name']); ?>
                                <span style="background: <?php echo $color; ?>; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; margin-left: 8px;">
                                    <?php echo esc_html($suggestion['category']); ?>
                                </span>
                                <?php if ($suggestion['priority'] >= 50): ?>
                                <span style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px; margin-left: 4px;">
                                    HIGH PRIORITY
                                </span>
                                <?php endif; ?>
                            </h4>
                            <p style="margin: 4px 0; font-size: 12px; color: #666;">
                                <strong><?php esc_html_e('Dominio:', 'fp-performance-suite'); ?></strong> 
                                <code><?php echo esc_html($suggestion['domain']); ?></code>
                            </p>
                            <p style="margin: 4px 0; font-size: 12px; color: #666;">
                                <strong><?php esc_html_e('Rilevato:', 'fp-performance-suite'); ?></strong> 
                                <?php echo $suggestion['occurrences']; ?> volte
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 11px; color: #999;">
                                <code><?php echo esc_html($suggestion['src']); ?></code>
                            </p>
                        </div>
                        <div style="display: flex; gap: 8px; margin-left: 15px;">
                            <form method="post" style="margin: 0;">
                                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                                <input type="hidden" name="form_type" value="script_detector" />
                                <input type="hidden" name="script_hash" value="<?php echo esc_attr($suggestion['hash']); ?>" />
                                <button type="submit" name="action_auto_add" class="button button-primary button-small">
                                    ✅ <?php esc_html_e('Aggiungi', 'fp-performance-suite'); ?>
                                </button>
                            </form>
                            <form method="post" style="margin: 0;">
                                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                                <input type="hidden" name="form_type" value="script_detector" />
                                <input type="hidden" name="script_hash" value="<?php echo esc_attr($suggestion['hash']); ?>" />
                                <button type="submit" name="action_dismiss" class="button button-link-delete button-small">
                                    ❌ <?php esc_html_e('Ignora', 'fp-performance-suite'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($suggestions) > 10): ?>
            <p style="margin-top: 15px; color: #666;">
                <?php printf(esc_html__('Mostrati %d di %d suggerimenti. I più importanti sono elencati per primi.', 'fp-performance-suite'), 10, count($suggestions)); ?>
            </p>
            <?php endif; ?>
            <?php endif; ?>
            
            <!-- Custom Scripts List -->
            <?php if (!empty($customScripts)): ?>
            <h3 style="margin-top: 30px; border-bottom: 2px solid #8b5cf6; padding-bottom: 8px;">
                🎯 <?php esc_html_e('Script Custom Gestiti', 'fp-performance-suite'); ?>
            </h3>
            
            <div style="display: grid; gap: 10px; margin-top: 15px;">
                <?php foreach ($customScripts as $key => $script): ?>
                <div style="background: #faf5ff; border: 1px solid #e9d5ff; padding: 12px; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?php echo esc_html($script['name']); ?></strong>
                        <?php if (!empty($script['enabled'])): ?>
                        <span style="background: #10b981; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 8px;">ATTIVO</span>
                        <?php else: ?>
                        <span style="background: #6b7280; color: white; padding: 2px 6px; border-radius: 3px; font-size: 10px; margin-left: 8px;">DISATTIVO</span>
                        <?php endif; ?>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #666;">
                            <?php echo esc_html(implode(', ', $script['patterns'])); ?>
                        </p>
                    </div>
                    <form method="post" style="margin: 0;">
                        <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                        <input type="hidden" name="form_type" value="script_detector" />
                        <input type="hidden" name="custom_key" value="<?php echo esc_attr($key); ?>" />
                        <button type="submit" name="action_remove_custom" class="button button-link-delete button-small" onclick="return confirm('Rimuovere questo script custom?');">
                            🗑️ <?php esc_html_e('Rimuovi', 'fp-performance-suite'); ?>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Add Custom Script Form -->
            <h3 style="margin-top: 30px; border-bottom: 2px solid #6366f1; padding-bottom: 8px;">
                ➕ <?php esc_html_e('Aggiungi Script Personalizzato', 'fp-performance-suite'); ?>
            </h3>
            
            <form method="post" style="margin-top: 15px;">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="script_detector" />
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="script_name"><?php esc_html_e('Nome Script', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="script_name" name="script_name" class="regular-text" placeholder="es. My Custom Service" required />
                            <p class="description"><?php esc_html_e('Nome identificativo per lo script', 'fp-performance-suite'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="script_patterns"><?php esc_html_e('Pattern URL', 'fp-performance-suite'); ?></label>
                        </th>
                        <td>
                            <textarea id="script_patterns" name="script_patterns" rows="3" class="large-text code" placeholder="example.com/script.js&#10;cdn.example.com" required></textarea>
                            <p class="description"><?php esc_html_e('Inserisci uno o più pattern URL (uno per riga). Es: example.com/script.js', 'fp-performance-suite'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Impostazioni', 'fp-performance-suite'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="script_enabled" value="1" checked />
                                <?php esc_html_e('Abilita subito', 'fp-performance-suite'); ?>
                            </label>
                            <br />
                            <label>
                                <input type="checkbox" name="script_delay" value="1" checked />
                                <?php esc_html_e('Ritarda caricamento', 'fp-performance-suite'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <button type="submit" name="action_add_custom" class="button button-primary">
                        ➕ <?php esc_html_e('Aggiungi Script Custom', 'fp-performance-suite'); ?>
                    </button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>
                ⚡ <?php esc_html_e('HTTP/2 Server Push', 'fp-performance-suite'); ?> 
                <span class="fp-ps-badge green" style="font-size: 0.7em;">Advanced</span>
                <?php echo $hints->renderInlineHint('http2_push'); ?>
            </h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Push automatico di risorse critiche via HTTP/2 Server Push per eliminare round-trip e accelerare il rendering.', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="http2_push" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita HTTP/2 Server Push', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator amber">
                            <div class="fp-ps-risk-tooltip amber">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">⚠</span>
                                    <?php esc_html_e('Rischio Medio', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Invia risorse critiche al browser prima ancora che le richieda, eliminando latenza.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Riduce FCP del 20-30%, elimina round-trip per risorse critiche.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Rischi', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Può sprecare banda se push risorse già in cache. Richiede HTTP/2 attivo sul server.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="http2_push_enabled" value="1" <?php checked($http2Settings['enabled']); ?> />
                </label>
                
                <h3><?php esc_html_e('Tipi di Risorse da Pushare', 'fp-performance-suite'); ?></h3>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push CSS critici', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_css" value="1" <?php checked($http2Settings['push_css']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push JavaScript critici', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_js" value="1" <?php checked($http2Settings['push_js']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push Fonts', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="http2_push_fonts" value="1" <?php checked($http2Settings['push_fonts']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Push Immagini critiche', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('(sconsigliato: troppo pesanti)', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="http2_push_images" value="1" <?php checked($http2Settings['push_images']); ?> />
                </label>
                
                <p>
                    <label for="http2_max_resources"><?php esc_html_e('Max risorse da pushare', 'fp-performance-suite'); ?></label>
                    <input type="number" name="http2_max_resources" id="http2_max_resources" value="<?php echo esc_attr((string) $http2Settings['max_resources']); ?>" min="1" max="20" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Consigliato: 5-10', 'fp-performance-suite'); ?></span>
                </p>
                
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Push solo risorse critiche', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Raccomandato: push solo above-the-fold', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="http2_critical_only" value="1" <?php checked($http2Settings['critical_only']); ?> />
                </label>
                
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #856404;"><?php esc_html_e('⚠️ Nota importante:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #856404;">
                        <li><?php esc_html_e('Richiede HTTP/2 abilitato sul server', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Testa sempre l\'impatto con strumenti come WebPageTest', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('HTTP/3 renderà questa tecnica meno necessaria', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni HTTP/2 Push', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <section class="fp-ps-card" style="margin-top: 20px;">
            <h2>
                📱 <?php esc_html_e('Smart Asset Delivery', 'fp-performance-suite'); ?> 
                <span class="fp-ps-badge green" style="font-size: 0.7em;">Advanced</span>
                <?php echo $hints->renderInlineHint('smart_delivery'); ?>
            </h2>
            <p style="color: #666; margin-bottom: 20px;"><?php esc_html_e('Adatta automaticamente la qualità e il tipo di assets in base alla connessione dell\'utente (2G, 3G, 4G, Save-Data).', 'fp-performance-suite'); ?></p>
            <form method="post">
                <?php wp_nonce_field('fp-ps-assets', 'fp_ps_assets_nonce'); ?>
                <input type="hidden" name="form_type" value="smart_delivery" />
                <label class="fp-ps-toggle">
                    <span class="info">
                        <strong><?php esc_html_e('Abilita Smart Delivery', 'fp-performance-suite'); ?></strong>
                        <span class="fp-ps-risk-indicator green">
                            <div class="fp-ps-risk-tooltip green">
                                <div class="fp-ps-risk-tooltip-title">
                                    <span class="icon">✓</span>
                                    <?php esc_html_e('Rischio Basso', 'fp-performance-suite'); ?>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Descrizione', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Rileva automaticamente la velocità di connessione e adatta qualità immagini/video.', 'fp-performance-suite'); ?></div>
                                </div>
                                <div class="fp-ps-risk-tooltip-section">
                                    <div class="fp-ps-risk-tooltip-label"><?php esc_html_e('Benefici', 'fp-performance-suite'); ?></div>
                                    <div class="fp-ps-risk-tooltip-text"><?php esc_html_e('Esperienza ottimale per tutti gli utenti, da mobile 2G a fibra ottica. Riduce consumo dati su reti lente.', 'fp-performance-suite'); ?></div>
                                </div>
                            </div>
                        </span>
                    </span>
                    <input type="checkbox" name="smart_delivery_enabled" value="1" <?php checked($smartDeliverySettings['enabled']); ?> />
                </label>
                
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Rileva tipo connessione', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Usa Network Information API', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="smart_detect_connection" value="1" <?php checked($smartDeliverySettings['detect_connection']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Rispetta Save-Data mode', 'fp-performance-suite'); ?></strong>
                        <span class="description"><?php esc_html_e('Riduce qualità se utente ha attivato risparmio dati', 'fp-performance-suite'); ?></span>
                    </span>
                    <input type="checkbox" name="smart_save_data_mode" value="1" <?php checked($smartDeliverySettings['save_data_mode']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Immagini adaptive', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="smart_adaptive_images" value="1" <?php checked($smartDeliverySettings['adaptive_images']); ?> />
                </label>
                <label class="fp-ps-toggle" style="margin-left: 30px;">
                    <span class="info">
                        <strong><?php esc_html_e('Video adaptive', 'fp-performance-suite'); ?></strong>
                    </span>
                    <input type="checkbox" name="smart_adaptive_videos" value="1" <?php checked($smartDeliverySettings['adaptive_videos']); ?> />
                </label>
                
                <h3><?php esc_html_e('Qualità per Tipo di Connessione', 'fp-performance-suite'); ?></h3>
                <p>
                    <label for="smart_quality_slow"><?php esc_html_e('Qualità connessione lenta (2G)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="smart_quality_slow" id="smart_quality_slow" value="<?php echo esc_attr((string) $smartDeliverySettings['quality_slow']); ?>" min="20" max="100" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Default: 50 (bassa qualità, carica veloce)', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label for="smart_quality_moderate"><?php esc_html_e('Qualità connessione moderata (3G)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="smart_quality_moderate" id="smart_quality_moderate" value="<?php echo esc_attr((string) $smartDeliverySettings['quality_moderate']); ?>" min="20" max="100" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Default: 70 (media qualità)', 'fp-performance-suite'); ?></span>
                </p>
                <p>
                    <label for="smart_quality_fast"><?php esc_html_e('Qualità connessione veloce (4G+)', 'fp-performance-suite'); ?></label>
                    <input type="number" name="smart_quality_fast" id="smart_quality_fast" value="<?php echo esc_attr((string) $smartDeliverySettings['quality_fast']); ?>" min="20" max="100" style="width: 80px;" />
                    <span class="description"><?php esc_html_e('Default: 85 (alta qualità)', 'fp-performance-suite'); ?></span>
                </p>
                
                <div style="background: #e7f5ff; border-left: 4px solid #2271b1; padding: 15px; margin-top: 20px;">
                    <p style="margin: 0; font-weight: 600; color: #2271b1;"><?php esc_html_e('💡 Benefici Smart Delivery:', 'fp-performance-suite'); ?></p>
                    <ul style="margin: 10px 0 0 20px; color: #555;">
                        <li><?php esc_html_e('Ottimizzazione automatica per ogni utente', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Riduzione consumo dati su mobile fino al 70%', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Esperienza fluida anche su reti lente', 'fp-performance-suite'); ?></li>
                        <li><?php esc_html_e('Supporto Network Information API e Save-Data', 'fp-performance-suite'); ?></li>
                    </ul>
                </div>
                
                <p style="margin-top: 20px;">
                    <button type="submit" class="button button-primary"><?php esc_html_e('Salva Impostazioni Smart Delivery', 'fp-performance-suite'); ?></button>
                </p>
            </form>
        </section>
        
        <?php echo ThemeHints::renderTooltipScript(); ?>
        <?php
        return (string) ob_get_clean();
    }
}
