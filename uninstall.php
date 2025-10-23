<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$optionKeys = [
    'fp_ps_page_cache',
    'fp_ps_browser_cache',
    'fp_ps_assets',
    'fp_ps_webp',
    'fp_ps_db',
    'fp_ps_settings',
    'fp_ps_preset',
    'fp_ps_critical_css',
    'fp_ps_mobile_optimizer',
    'fp_ps_touch_optimizer',
    'fp_ps_mobile_cache',
    'fp_ps_responsive_images',
    'fp_ps_ml_predictor',
    'fp_ps_pattern_learner',
    'fp_ps_anomaly_detector',
    'fp_ps_auto_tuner',
    'fp_ps_htaccess_security',
    'fp_ps_media_optimizer',
    'fp_ps_compression_deflate_enabled',
    'fp_ps_backend_optimizer',
    'fp_ps_cdn',
    'fp_ps_object_cache_enabled',
    'fp_ps_edge_cache_enabled',
    'fp_ps_monitoring_enabled',
    'fp_ps_reports_enabled',
    'fp_ps_scoring_enabled',
    'fp_ps_presets_enabled',
    'fp_ps_intelligence_enabled',
    'fp_ps_compatibility_enabled',
    'fp_ps_ai_enabled',
    'fp_ps_pwa_enabled',
    'fp_ps_http2_enabled',
    'fp_ps_critical_css_enabled',
    'fp_ps_smart_delivery_enabled',
    'fp_ps_html_minification_enabled',
    'fp_ps_script_optimization_enabled',
    'fp_ps_wordpress_optimization_enabled',
    'fp_ps_resource_hints_enabled',
    'fp_ps_dependency_resolution_enabled',
    'fp_ps_lazy_loading_enabled',
    'fp_ps_font_optimization_enabled',
    'fp_ps_image_optimization_enabled',
    'fp_ps_auto_font_optimization_enabled',
    'fp_ps_lighthouse_font_optimization_enabled',
    'fp_ps_third_party_detector_enabled',
    'fp_ps_batch_dom_updates_enabled',
    'fp_ps_css_optimization_enabled',
    'fp_ps_jquery_optimization_enabled',
    'fp_ps_predictive_prefetch',
    'fp_ps_third_party_scripts',
    'fp_ps_asset_optimization_enabled',
    'fp_ps_webp_enabled',
    'fp_ps_avif',
    'fp_ps_compression_enabled',
    'fp_ps_compression_brotli_enabled',
    'fp_perfsuite_activation_error',
    'fp_perfsuite_version'
];

foreach ($optionKeys as $key) {
    delete_option($key);
}

// Pulisci anche i transients
global $wpdb;
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_fp_ps_%'");
$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_fp_ps_%'");

// Pulisci i cron jobs
wp_clear_scheduled_hook('fp_ps_db_cleanup');
wp_clear_scheduled_hook('fp_ps_webp_queue');
wp_clear_scheduled_hook('fp_ps_ml_6hourly');
