<?php

namespace FP\PerfSuite\Initialization;

use FP\PerfSuite\Utils\OptionHelper;

/**
 * Gestisce l'inizializzazione delle opzioni di default
 * 
 * @package FP\PerfSuite\Initialization
 * @author Francesco Passeri
 */
class DefaultOptionsManager
{
    /**
     * Assicura che tutte le opzioni di default esistano
     */
    public function ensureDefaults(): void
    {
        $this->ensureMobileDefaults();
        $this->ensureMLDefaults();
        $this->ensureSecurityDefaults();
        $this->ensureMediaDefaults();
        $this->ensureCompressionDefaults();
        $this->ensureBackendDefaults();
        $this->ensureDatabaseDefaults();
        $this->ensureCacheDefaults();
        $this->ensureAdvancedDefaults();
        $this->ensureCoreDefaults();
    }

    /**
     * Inizializza opzioni mobile
     */
    private function ensureMobileDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_mobile_optimizer', [
            'enabled' => false,
            'disable_animations' => false,
            'remove_unnecessary_scripts' => false,
            'optimize_touch_targets' => false,
            'enable_responsive_images' => false
        ]);

        $this->setDefaultIfNotExists('fp_ps_touch_optimizer', [
            'enabled' => false,
            'disable_hover_effects' => false,
            'improve_touch_targets' => false,
            'optimize_scroll' => false,
            'prevent_zoom' => false
        ]);

        $this->setDefaultIfNotExists('fp_ps_responsive_images', [
            'enabled' => false,
            'enable_lazy_loading' => false,
            'optimize_srcset' => false,
            'max_mobile_width' => 768,
            'max_content_image_width' => '100%'
        ]);

        $this->setDefaultIfNotExists('fp_ps_mobile_cache', [
            'enabled' => false,
            'enable_mobile_cache_headers' => false,
            'enable_resource_caching' => false,
            'cache_mobile_css' => false,
            'cache_mobile_js' => false,
            'html_cache_duration' => 300,
            'css_cache_duration' => 3600,
            'js_cache_duration' => 3600
        ]);
    }

    /**
     * Inizializza opzioni ML
     */
    private function ensureMLDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_ml_predictor', [
            'enabled' => false,
            'data_retention_days' => 30,
            'prediction_threshold' => 0.7,
            'anomaly_threshold' => 0.8,
            'pattern_confidence_threshold' => 0.8
        ]);

        $this->setDefaultIfNotExists('fp_ps_pattern_learner', [
            'enabled' => false,
            'min_data_points' => 10,
            'confidence_threshold' => 0.7,
            'learning_rate' => 0.1,
            'max_patterns' => 100
        ]);

        $this->setDefaultIfNotExists('fp_ps_anomaly_detector', [
            'enabled' => false,
            'confidence_threshold' => 0.7,
            'z_score_threshold' => 2.0,
            'window_size' => 24,
            'sensitivity' => 'medium'
        ]);

        $this->setDefaultIfNotExists('fp_ps_auto_tuner', [
            'enabled' => false,
            'tuning_frequency' => '6hourly',
            'aggressive_mode' => false,
            'performance_threshold' => 0.8,
            'auto_rollback' => true
        ]);
    }

    /**
     * Inizializza opzioni security
     */
    private function ensureSecurityDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_htaccess_security', [
            'enabled' => false,
            'ssl_redirect' => [
                'enabled' => false,
                'force_https' => false,
                'force_www' => false,
                'domain' => ''
            ],
            'security_headers' => [
                'enabled' => false,
                'hsts' => false,
                'hsts_max_age' => 31536000,
                'hsts_subdomains' => false,
                'hsts_preload' => false,
                'x_content_type_options' => false,
                'x_frame_options' => 'SAMEORIGIN',
                'referrer_policy' => 'strict-origin-when-cross-origin',
                'permissions_policy' => 'camera=(), microphone=(), geolocation=()'
            ],
            'cache_rules' => [
                'enabled' => false,
                'html_cache' => false,
                'fonts_cache' => false,
                'fonts_max_age' => 31536000,
                'images_max_age' => 31536000,
                'css_js_max_age' => 2592000
            ],
            'cors' => [
                'enabled' => false,
                'fonts_origin' => '*',
                'svg_origin' => '*'
            ],
            'file_protection' => [
                'enabled' => false,
                'protect_hidden_files' => false,
                'protect_wp_config' => false,
                'protect_htaccess' => false,
                'protect_readme' => false
            ]
        ]);
    }

    /**
     * Inizializza opzioni media
     */
    private function ensureMediaDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_media_optimizer', [
            'enabled' => false,
            'generate_sizes' => false,
            'js_detection' => false,
            'lazy_loading' => false,
            'quality' => 75,
            'lossy' => true
        ]);
    }

    /**
     * Inizializza opzioni compression
     */
    private function ensureCompressionDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_compression_deflate_enabled', false);
    }

    /**
     * Inizializza opzioni backend
     */
    private function ensureBackendDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_backend_optimizer', [
            'enabled' => false,
            'admin_bar' => [
                'disable_frontend' => false,
                'disable_wordpress_logo' => false,
                'disable_updates' => false,
                'disable_comments' => false,
                'disable_new' => false,
                'disable_customize' => false,
            ],
            'dashboard' => [
                'disable_welcome' => false,
                'disable_quick_press' => false,
                'disable_activity' => false,
                'disable_primary' => false,
                'disable_secondary' => false,
                'disable_site_health' => false,
                'disable_php_update' => false,
            ],
            'heartbeat' => [
                'dashboard' => 'default',
                'editor' => 'default',
                'frontend' => 'default',
            ],
            'heartbeat_interval' => 60,
            'admin_ajax' => [
                'disable_heartbeat' => false,
                'disable_autosave' => false,
                'disable_post_lock' => false,
            ]
        ]);
    }

    /**
     * Inizializza opzioni database
     */
    private function ensureDatabaseDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_db', [
            'enabled' => false,
            'schedule' => 'manual',
            'batch' => 200,
            'cleanup_scope' => [],
            'query_monitor' => [
                'enabled' => false,
                'log_queries' => false,
                'log_slow_queries' => false,
                'slow_query_threshold' => 0.5
            ],
            'optimization' => [
                'auto_optimize' => false,
                'optimize_on_cleanup' => false,
                'repair_tables' => false
            ]
        ]);
    }

    /**
     * Inizializza opzioni cache
     */
    private function ensureCacheDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_cdn', [
            'enabled' => false,
            'url' => '',
            'provider' => 'custom'
        ]);

        $this->setDefaultIfNotExists('fp_ps_object_cache_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_edge_cache_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_page_cache', [
            'enabled' => false,
            'ttl' => 3600
        ]);

        $this->setDefaultIfNotExists('fp_ps_browser_cache', [
            'enabled' => false
        ]);
    }

    /**
     * Inizializza opzioni avanzate
     */
    private function ensureAdvancedDefaults(): void
    {
        $this->setDefaultIfNotExists('fp_ps_monitoring_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_reports_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_third_party_detector_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_presets_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_intelligence_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_compatibility_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_ai_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_pwa_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_http2_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_critical_css_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_smart_delivery_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_html_minification_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_script_optimization_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_wordpress_optimization_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_resource_hints_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_dependency_resolution_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_lazy_loading_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_font_optimization_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_image_optimization_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_auto_font_optimization_enabled', false);
        $this->setDefaultIfNotExists('fp_ps_lighthouse_font_optimization_enabled', false);
    }

    /**
     * Inizializza opzioni core
     */
    private function ensureCoreDefaults(): void
    {
        // Scoring Services - ABILITATI di default (necessari per Overview)
        $this->setDefaultIfNotExists('fp_ps_scoring_enabled', true);
    }

    /**
     * Imposta un valore di default se l'opzione non esiste
     * 
     * @param string $optionName Nome dell'opzione
     * @param mixed $defaultValue Valore di default
     */
    private function setDefaultIfNotExists(string $optionName, $defaultValue): void
    {
        if (!OptionHelper::exists($optionName)) {
            update_option($optionName, $defaultValue, false);
        }
    }
}
















