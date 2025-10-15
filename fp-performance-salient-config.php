<?php
/**
 * FP Performance Suite - Configurazione Ottimale per Salient + WPBakery
 * 
 * Inserisci questo file in wp-content/mu-plugins/
 * oppure copia il contenuto in functions.php del tema child
 * 
 * @package FP Performance Suite
 * @version 1.3.0
 * @author Francesco Passeri
 */

defined('ABSPATH') || exit;

/**
 * Configurazione servizi FP Performance Suite per Salient + WPBakery
 */
add_action('init', function() {
    // Verifica che il plugin sia attivo
    if (!class_exists('FP\PerfSuite\Plugin')) {
        return;
    }
    
    $container = \FP\PerfSuite\Plugin::container();
    
    // ========================================
    // 1. OBJECT CACHE (Redis/Memcached)
    // ========================================
    if (class_exists('Redis') || class_exists('Memcached')) {
        try {
            $objectCache = $container->get(\FP\PerfSuite\Services\Cache\ObjectCacheManager::class);
            $objectCache->update([
                'enabled' => true,
                'driver' => 'auto', // auto-detect Redis o Memcached
                'prefix' => 'salient_fps_',
                'timeout' => 2,
            ]);
        } catch (Exception $e) {
            error_log('FP Performance: Object Cache setup failed - ' . $e->getMessage());
        }
    }
    
    // ========================================
    // 2. EDGE CACHE (Cloudflare/Fastly/CloudFront)
    // ========================================
    // Decommentare e configurare se si usa un CDN
    /*
    try {
        $edgeCache = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
        $edgeCache->update([
            'enabled' => true,
            'provider' => 'cloudflare', // cloudflare, fastly, cloudfront
            'auto_purge' => true,
            'cloudflare' => [
                'api_token' => 'YOUR_CLOUDFLARE_API_TOKEN',
                'zone_id' => 'YOUR_CLOUDFLARE_ZONE_ID',
            ],
        ]);
    } catch (Exception $e) {
        error_log('FP Performance: Edge Cache setup failed - ' . $e->getMessage());
    }
    */
    
    // ========================================
    // 3. CORE WEB VITALS MONITOR
    // ========================================
    try {
        $cwv = $container->get(\FP\PerfSuite\Services\Monitoring\CoreWebVitalsMonitor::class);
        $cwv->update([
            'enabled' => true,
            'sample_rate' => 0.5, // 50% degli utenti
            'track_lcp' => true,
            'track_fid' => true,
            'track_cls' => true, // Importante per Salient (animazioni)
            'track_fcp' => true,
            'track_ttfb' => true,
            'track_inp' => true,
            'alert_threshold_lcp' => 2500,
            'alert_threshold_fid' => 100,
            'alert_threshold_cls' => 0.1, // Strict per Salient
            'alert_email' => get_option('admin_email'),
        ]);
    } catch (Exception $e) {
        error_log('FP Performance: Core Web Vitals setup failed - ' . $e->getMessage());
    }
    
    // ========================================
    // 4. THIRD-PARTY SCRIPT MANAGER
    // ========================================
    try {
        $scripts = $container->get(\FP\PerfSuite\Services\Assets\ThirdPartyScriptManager::class);
        $scripts->update([
            'enabled' => true,
            'delay_all' => false, // NON ritardare tutto (incompatibile con Salient)
            'load_on' => 'interaction', // Carica al primo scroll/click
            'delay_timeout' => 3000,
            'scripts' => [
                'google_analytics' => ['enabled' => true, 'delay' => true],
                'facebook_pixel' => ['enabled' => true, 'delay' => true],
                'google_ads' => ['enabled' => true, 'delay' => true],
                'hotjar' => ['enabled' => true, 'delay' => true],
            ],
        ]);
    } catch (Exception $e) {
        error_log('FP Performance: Third-Party Scripts setup failed - ' . $e->getMessage());
    }
    
    // ========================================
    // 5. SMART ASSET DELIVERY (Network-aware)
    // ========================================
    try {
        $smart = $container->get(\FP\PerfSuite\Services\Assets\SmartAssetDelivery::class);
        $smart->update([
            'enabled' => true,
            'detect_connection' => true,
            'save_data_mode' => true,
            'adaptive_images' => true,
            'adaptive_videos' => true,
            'quality_slow' => 50,      // 2G
            'quality_moderate' => 65,  // 3G
            'quality_fast' => 85,      // 4G
        ]);
    } catch (Exception $e) {
        error_log('FP Performance: Smart Delivery setup failed - ' . $e->getMessage());
    }
    
    // ========================================
    // 6. HTTP/2 SERVER PUSH
    // ========================================
    try {
        $http2 = $container->get(\FP\PerfSuite\Services\Assets\Http2ServerPush::class);
        $http2->update([
            'enabled' => true,
            'push_css' => true,
            'push_js' => false, // IMPORTANTE: disattivato per Salient (usa jQuery pesantemente)
            'push_fonts' => true,
            'max_resources' => 5,
            'critical_only' => true,
        ]);
    } catch (Exception $e) {
        error_log('FP Performance: HTTP/2 Push setup failed - ' . $e->getMessage());
    }
    
    // ========================================
    // 7. AVIF CONVERTER (Attivare dopo test!)
    // ========================================
    // ATTENZIONE: Testare prima in staging!
    // Decommentare solo dopo aver verificato compatibilità
    /*
    try {
        $avif = $container->get(\FP\PerfSuite\Services\Media\AVIFConverter::class);
        $avif->update([
            'enabled' => true,
            'quality' => 75,
            'auto_deliver' => false, // IMPORTANTE: false inizialmente, testare prima
            'keep_original' => true,
            'strip_metadata' => false,
        ]);
    } catch (Exception $e) {
        error_log('FP Performance: AVIF setup failed - ' . $e->getMessage());
    }
    */
    
    // ========================================
    // 8. PREDICTIVE PREFETCHING
    // ========================================
    try {
        $prefetch = $container->get(\FP\PerfSuite\Services\Assets\PredictivePrefetching::class);
        $prefetch->update([
            'enabled' => true,
            'strategy' => 'hover', // Hover è più sicuro per Salient
            'delay' => 200,
            'max_prefetch' => 2, // Limitato per evitare overload
            'exclude_patterns' => [
                '/wp-admin/',
                '/cart/',
                '/checkout/',
                '?vc_editable', // Editor WPBakery
                '/portfolio/', // Portfolio può essere pesante
            ],
        ]);
    } catch (Exception $e) {
        error_log('FP Performance: Prefetching setup failed - ' . $e->getMessage());
    }
    
}, 20); // Priorità 20 per eseguire dopo init del tema

/**
 * Esclusioni script critici Salient da delay
 */
add_filter('fp_ps_third_party_script_delay', function($should_delay, $src) {
    // Script Salient che NON devono essere ritardati
    $salient_critical = [
        'modernizr',
        'touchswipe',
        'jquery',
        'salient-',
        'nectar-',
        'wpbakery',
        'vc_',
        'js_composer',
    ];
    
    foreach ($salient_critical as $pattern) {
        if (stripos($src, $pattern) !== false) {
            return false; // NON ritardare
        }
    }
    
    return $should_delay;
}, 10, 2);

/**
 * Font Salient da pushare con HTTP/2
 */
add_filter('fp_ps_http2_critical_fonts', function($fonts) {
    $theme_uri = get_template_directory_uri();
    
    // Font icon Salient
    $salient_fonts = [
        $theme_uri . '/css/fonts/icomoon.woff2',
        $theme_uri . '/css/fonts/fontello.woff2',
        $theme_uri . '/css/fonts/iconsmind.woff2',
    ];
    
    foreach ($salient_fonts as $font_url) {
        $fonts[] = [
            'url' => $font_url,
            'as' => 'font',
            'type' => 'font/woff2',
        ];
    }
    
    return $fonts;
});

/**
 * Disabilita ottimizzazioni nell'editor WPBakery
 */
add_filter('fp_ps_disable_optimizations', function($disable) {
    // Editor frontend WPBakery
    if (isset($_GET['vc_editable']) || isset($_GET['vc_action'])) {
        return true;
    }
    
    // Preview WPBakery
    if (isset($_GET['vc_post_id'])) {
        return true;
    }
    
    return $disable;
});

/**
 * Auto-purge Edge Cache quando cambiano opzioni Salient
 */
add_action('updated_option', function($option_name) {
    // Verifica se è un'opzione Salient
    if (strpos($option_name, 'salient') === false && 
        strpos($option_name, 'nectar') === false) {
        return;
    }
    
    try {
        $container = \FP\PerfSuite\Plugin::container();
        $edge = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
        
        if ($edge->status()['enabled']) {
            $edge->purgeAll();
            error_log('FP Performance: Edge cache purged after Salient option update');
        }
    } catch (Exception $e) {
        error_log('FP Performance: Edge cache purge failed - ' . $e->getMessage());
    }
});

/**
 * Purge Edge Cache dopo salvataggio pagina WPBakery
 */
add_action('save_post', function($post_id, $post) {
    // Solo per post pubblici
    if ($post->post_status !== 'publish') {
        return;
    }
    
    // Solo se usa WPBakery
    $vc_active = get_post_meta($post_id, '_wpb_vc_js_status', true);
    if ($vc_active !== 'true') {
        return;
    }
    
    try {
        $container = \FP\PerfSuite\Plugin::container();
        $edge = $container->get(\FP\PerfSuite\Services\Cache\EdgeCacheManager::class);
        
        if ($edge->status()['enabled']) {
            $urls = [
                get_permalink($post_id),
                home_url('/'),
            ];
            
            $edge->purgeUrls($urls);
            error_log('FP Performance: Edge cache purged for post #' . $post_id);
        }
    } catch (Exception $e) {
        error_log('FP Performance: Edge cache purge failed - ' . $e->getMessage());
    }
}, 10, 2);

/**
 * Forza dimensioni su immagini Salient per ridurre CLS
 */
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment) {
    // Aggiungi width/height se mancanti
    if (empty($attr['width']) || empty($attr['height'])) {
        $meta = wp_get_attachment_metadata($attachment->ID);
        if ($meta && isset($meta['width']) && isset($meta['height'])) {
            $attr['width'] = $meta['width'];
            $attr['height'] = $meta['height'];
        }
    }
    
    return $attr;
}, 10, 2);

/**
 * Disabilita parallax Salient su connessioni lente
 */
add_action('wp_footer', function() {
    ?>
    <script>
    (function() {
        if (!navigator.connection) return;
        
        var connection = navigator.connection;
        var slowConnections = ['slow-2g', '2g'];
        
        if (slowConnections.includes(connection.effectiveType) || connection.saveData) {
            // Disabilita parallax e animazioni pesanti su connessioni lente
            if (typeof jQuery !== 'undefined') {
                jQuery(document).ready(function($) {
                    // Rimuovi parallax Salient
                    $('.nectar-parallax-scene').removeClass('nectar-parallax-scene');
                    $('[data-parallax]').removeAttr('data-parallax');
                    
                    // Disabilita video background
                    $('.nectar-video-wrap').remove();
                    
                    console.log('[FP Performance] Heavy animations disabled for slow connection');
                });
            }
        }
    })();
    </script>
    <?php
}, 999);

/**
 * Log configurazione attiva (solo per admin)
 */
add_action('admin_notices', function() {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Mostra solo una volta al giorno
    $last_shown = get_transient('fp_perf_config_notice');
    if ($last_shown) {
        return;
    }
    
    set_transient('fp_perf_config_notice', time(), DAY_IN_SECONDS);
    
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <strong>✅ FP Performance Suite configurato per Salient + WPBakery</strong><br>
            Servizi attivi: Object Cache, Core Web Vitals, Third-Party Scripts, Smart Delivery, HTTP/2 Push, Prefetching<br>
            <a href="<?php echo admin_url('admin.php?page=fp-performance'); ?>">Visualizza dashboard performance</a>
        </p>
    </div>
    <?php
});
