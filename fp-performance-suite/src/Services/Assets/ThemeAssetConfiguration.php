<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Services\Compatibility\ThemeDetector;

/**
 * Theme Asset Configuration
 * 
 * Configura asset specifici per tema e page builder
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ThemeAssetConfiguration
{
    private ThemeDetector $detector;

    public function __construct(ThemeDetector $detector)
    {
        $this->detector = $detector;
    }

    /**
     * Registra la configurazione degli asset
     */
    public function register(): void
    {
        add_filter('fp_ps_asset_configuration', [$this, 'getAssetConfiguration']);
        add_filter('fp_ps_preload_assets', [$this, 'getPreloadAssets']);
        add_filter('fp_ps_async_assets', [$this, 'getAsyncAssets']);
    }

    /**
     * Ottiene la configurazione asset per il tema corrente
     */
    public function getAssetConfiguration(array $config): array
    {
        $theme = $this->detector->getCurrentTheme();

        $themeConfig = [
            'critical_scripts' => $this->detector->getCriticalScripts(),
            'critical_styles' => $this->detector->getCriticalStyles(),
            'defer_scripts' => $this->getDeferScripts(),
            'async_scripts' => $this->getAsyncScripts(),
            'preload' => $this->getPreloadAssets([]),
            'prefetch' => $this->getPrefetchAssets(),
        ];

        return array_merge_recursive($config, $themeConfig);
    }

    /**
     * Ottiene gli script che possono essere differiti
     */
    private function getDeferScripts(): array
    {
        $theme = $this->detector->getCurrentTheme();
        $defer = [];

        switch ($theme) {
            case 'astra':
                $defer = [
                    'astra-addon-js',
                ];
                break;

            case 'generatepress':
                $defer = [
                    'generate-menu-js',
                    'generate-back-to-top',
                ];
                break;

            case 'oceanwp':
                $defer = [
                    'oceanwp-main',
                    'oceanwp-sidebar-mobile-menu',
                ];
                break;

            case 'kadence':
                $defer = [
                    'kadence-navigation',
                ];
                break;
        }

        // Aggiungi script dei page builder che possono essere differiti
        $builders = $this->detector->getActivePageBuilders();
        foreach ($builders as $builder) {
            if ($builder === 'gutenberg') {
                $defer[] = 'wp-block-library';
            }
        }

        return $defer;
    }

    /**
     * Ottiene gli script che possono essere caricati async
     */
    private function getAsyncScripts(): array
    {
        $async = [
            // Analytics comuni
            'google-analytics',
            'gtag',
            'ga',
            'google-tag-manager',
            
            // Social
            'facebook-jssdk',
            'twitter-widgets',
            'linkedin-platform',
            
            // Tracking
            'hotjar',
            'mouseflow',
            'crazy-egg',
            
            // Chat
            'tawk-to',
            'intercom',
            'drift',
        ];

        return $async;
    }

    /**
     * Ottiene gli asset da preload
     */
    public function getPreloadAssets(array $preload): array
    {
        $theme = $this->detector->getCurrentTheme();
        $themePreload = [];

        // Preload font comuni per tema
        switch ($theme) {
            case 'astra':
                if (file_exists(get_template_directory() . '/assets/fonts/astra.woff2')) {
                    $themePreload[] = [
                        'href' => get_template_directory_uri() . '/assets/fonts/astra.woff2',
                        'as' => 'font',
                        'type' => 'font/woff2',
                        'crossorigin' => 'anonymous',
                    ];
                }
                break;

            case 'generatepress':
                // GeneratePress usa system fonts, niente da preload
                break;

            case 'avada':
                $themePreload[] = [
                    'href' => get_template_directory_uri() . '/assets/fonts/icomoon.woff',
                    'as' => 'font',
                    'type' => 'font/woff',
                    'crossorigin' => 'anonymous',
                ];
                break;
        }

        // Preload CSS critici per page builder
        $builders = $this->detector->getActivePageBuilders();
        foreach ($builders as $builder) {
            switch ($builder) {
                case 'elementor':
                    $themePreload[] = [
                        'href' => plugins_url('elementor/assets/css/frontend.min.css'),
                        'as' => 'style',
                    ];
                    break;

                case 'divi':
                    $themePreload[] = [
                        'href' => get_template_directory_uri() . '/style.css',
                        'as' => 'style',
                    ];
                    break;
            }
        }

        return array_merge($preload, $themePreload);
    }

    /**
     * Ottiene gli asset da prefetch
     */
    private function getPrefetchAssets(): array
    {
        $prefetch = [];

        // Prefetch pagine comuni
        if (is_front_page()) {
            // Nella home, prefetch pagine importanti
            $prefetch[] = [
                'href' => get_permalink(get_option('page_for_posts')),
                'as' => 'document',
            ];
        }

        // WooCommerce: prefetch cart nella pagina prodotto
        if ($this->detector->hasWooCommerce() && function_exists('is_product') && is_product()) {
            $prefetch[] = [
                'href' => wc_get_cart_url(),
                'as' => 'document',
            ];
        }

        return $prefetch;
    }

    /**
     * Ottiene la strategia di caricamento per un asset
     */
    public function getLoadStrategy(string $handle, string $type = 'script'): string
    {
        // Critico = carica subito
        if ($this->detector->isCriticalAsset($handle, $type)) {
            return 'immediate';
        }

        // Analytics e tracking = async
        $asyncPatterns = ['analytics', 'gtag', 'ga-', 'facebook', 'twitter', 'tracking'];
        foreach ($asyncPatterns as $pattern) {
            if (strpos($handle, $pattern) !== false) {
                return 'async';
            }
        }

        // Default = defer
        return 'defer';
    }

    /**
     * Verifica se un asset deve essere combinato
     */
    public function shouldCombine(string $handle, string $type = 'script'): bool
    {
        // Non combinare asset critici
        if ($this->detector->isCriticalAsset($handle, $type)) {
            return false;
        }

        // Non combinare asset esterni
        $wp_scripts = wp_scripts();
        $wp_styles = wp_styles();
        
        $obj = $type === 'script' ? $wp_scripts : $wp_styles;
        
        if (!isset($obj->registered[$handle])) {
            return false;
        }

        $src = $obj->registered[$handle]->src;
        
        // Se è un URL esterno, non combinare
        if (strpos($src, '//') !== false && strpos($src, home_url()) === false) {
            return false;
        }

        return true;
    }

    /**
     * Ottiene i gruppi di combinazione per gli asset
     */
    public function getCombineGroups(string $type = 'script'): array
    {
        $groups = [
            'theme' => [],
            'plugins' => [],
            'wordpress' => [],
        ];

        $obj = $type === 'script' ? wp_scripts() : wp_styles();

        foreach ($obj->queue as $handle) {
            if (!isset($obj->registered[$handle])) {
                continue;
            }

            $src = $obj->registered[$handle]->src;

            if (strpos($src, get_template_directory_uri()) !== false) {
                $groups['theme'][] = $handle;
            } elseif (strpos($src, WP_PLUGIN_URL) !== false) {
                $groups['plugins'][] = $handle;
            } elseif (strpos($src, includes_url()) !== false) {
                $groups['wordpress'][] = $handle;
            }
        }

        return $groups;
    }

    /**
     * Ottiene configurazione ottimizzazione per il tema corrente
     */
    public function getOptimizationConfig(): array
    {
        $theme = $this->detector->getCurrentTheme();
        $features = $this->detector->getThemeFeatures();

        $config = [
            'minify_html' => true,
            'minify_css' => true,
            'minify_js' => true,
            'combine_css' => false, // Disabilitato di default, può causare problemi
            'combine_js' => false,  // Disabilitato di default
            'defer_js' => true,
            'lazy_load' => !$features['lazy_loading'], // Se tema ha lazy loading nativo, disabilita
            'remove_query_strings' => true,
            'dns_prefetch' => true,
            'preconnect' => true,
        ];

        // Configurazioni specifiche per tema
        $lightweightThemes = ['astra', 'generatepress', 'kadence', 'blocksy'];
        if (in_array($theme, $lightweightThemes, true)) {
            // Temi leggeri possono gestire più ottimizzazioni
            $config['combine_css'] = true;
        }

        // Temi complessi: sii più conservativo
        $complexThemes = ['avada', 'divi', 'the7', 'enfold'];
        if (in_array($theme, $complexThemes, true)) {
            $config['minify_html'] = false; // Può causare problemi con builder
            $config['combine_css'] = false;
            $config['combine_js'] = false;
        }

        return $config;
    }

    /**
     * Ottiene la priorità di caricamento per un asset
     */
    public function getLoadPriority(string $handle, string $type = 'script'): int
    {
        // Critico = alta priorità (10)
        if ($this->detector->isCriticalAsset($handle, $type)) {
            return 10;
        }

        // jQuery e dipendenze = media priorità (50)
        if (strpos($handle, 'jquery') !== false) {
            return 50;
        }

        // Analytics e tracking = bassa priorità (100)
        $lowPriority = ['analytics', 'gtag', 'facebook', 'twitter'];
        foreach ($lowPriority as $pattern) {
            if (strpos($handle, $pattern) !== false) {
                return 100;
            }
        }

        // Default = media priorità
        return 50;
    }
}

