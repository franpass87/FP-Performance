<?php

namespace FP\PerfSuite\Services\Compatibility;

use FP\PerfSuite\Utils\Logger;

/**
 * Compatibility Filters
 * 
 * Applica filtri e hook specifici per compatibilità con temi e plugin
 *
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CompatibilityFilters
{
    private ThemeDetector $detector;

    public function __construct(ThemeDetector $detector)
    {
        $this->detector = $detector;
    }

    /**
     * Registra i filtri di compatibilità
     */
    public function register(): void
    {
        // Filtri generali
        add_filter('fp_ps_defer_js_exclusions', [$this, 'addDeferExclusions']);
        add_filter('fp_ps_minify_html_exclusions', [$this, 'addMinifyExclusions']);
        add_filter('fp_ps_cache_exclusions', [$this, 'addCacheExclusions']);
        add_filter('fp_ps_critical_assets', [$this, 'addCriticalAssets']);

        // Filtri specifici per tema
        $this->registerThemeSpecificFilters();

        // Filtri specifici per page builder
        $this->registerPageBuilderFilters();

        // Filtri per WooCommerce
        if ($this->detector->hasWooCommerce()) {
            $this->registerWooCommerceFilters();
        }

        Logger::debug('Compatibility filters registered', [
            'theme' => $this->detector->getCurrentTheme(),
            'builders' => $this->detector->getActivePageBuilders(),
        ]);
    }

    /**
     * Aggiunge esclusioni per defer JS
     */
    public function addDeferExclusions(array $exclusions): array
    {
        // Scripts critici del tema/builder
        $criticalScripts = $this->detector->getCriticalScripts();
        $exclusions = array_merge($exclusions, $criticalScripts);

        // jQuery sempre escluso per compatibilità
        $exclusions[] = 'jquery';
        $exclusions[] = 'jquery-core';
        $exclusions[] = 'jquery-migrate';

        // Scripts comuni che non devono essere differiti
        $exclusions[] = 'wp-polyfill';
        $exclusions[] = 'regenerator-runtime';
        $exclusions[] = 'wp-hooks';

        return array_unique($exclusions);
    }

    /**
     * Aggiunge esclusioni per minify HTML
     */
    public function addMinifyExclusions(array $exclusions): array
    {
        $theme = $this->detector->getCurrentTheme();

        // Alcuni temi hanno problemi con minify HTML
        $problematicThemes = ['divi', 'avada', 'enfold'];
        
        if (in_array($theme, $problematicThemes, true)) {
            $exclusions[] = 'data-settings';
            $exclusions[] = 'data-elementor-settings';
        }

        return $exclusions;
    }

    /**
     * Aggiunge esclusioni per la cache
     */
    public function addCacheExclusions(array $exclusions): array
    {
        // WooCommerce
        if ($this->detector->hasWooCommerce()) {
            $exclusions[] = '/cart/';
            $exclusions[] = '/checkout/';
            $exclusions[] = '/my-account/';
            $exclusions[] = '/addons/';
            $exclusions[] = '?add-to-cart=';
            $exclusions[] = '?removed_item=';
        }

        // Membership plugins
        if (class_exists('MeprUser')) {
            $exclusions[] = '/members/';
            $exclusions[] = '/account/';
        }

        // bbPress
        if (function_exists('is_bbpress')) {
            $exclusions[] = '/forums/';
        }

        // BuddyPress
        if (function_exists('bp_is_active')) {
            $exclusions[] = '/members/';
            $exclusions[] = '/activity/';
        }

        return array_unique($exclusions);
    }

    /**
     * Aggiunge asset critici che non devono essere ottimizzati
     */
    public function addCriticalAssets(array $assets): array
    {
        $scripts = $this->detector->getCriticalScripts();
        $styles = $this->detector->getCriticalStyles();

        return array_merge($assets, $scripts, $styles);
    }

    /**
     * Registra filtri specifici per tema
     */
    private function registerThemeSpecificFilters(): void
    {
        $theme = $this->detector->getCurrentTheme();

        switch ($theme) {
            case 'avada':
                $this->registerAvadaFilters();
                break;

            case 'divi':
                $this->registerDiviFilters();
                break;

            case 'enfold':
                $this->registerEnfoldFilters();
                break;

            case 'salient':
                $this->registerSalientFilters();
                break;

            case 'astra':
                $this->registerAstraFilters();
                break;

            case 'generatepress':
                $this->registerGeneratePressFilters();
                break;
        }
    }

    /**
     * Filtri per Avada
     */
    private function registerAvadaFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'avada';
            $exclusions[] = 'fusion';
            $exclusions[] = 'isotope';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?fb-edit=';
            return $exclusions;
        });
    }

    /**
     * Filtri per Divi
     */
    private function registerDiviFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'divi-custom-script';
            $exclusions[] = 'et-builder-modules';
            $exclusions[] = 'et-core-common';
            return $exclusions;
        });

        add_filter('fp_ps_minify_html_exclusions', function ($exclusions) {
            $exclusions[] = 'et_pb_';
            $exclusions[] = 'data-et-';
            return $exclusions;
        });
    }

    /**
     * Filtri per Enfold
     */
    private function registerEnfoldFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'avia';
            return $exclusions;
        });
    }

    /**
     * Filtri per Salient
     */
    private function registerSalientFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'salient';
            $exclusions[] = 'nectar';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?salient_ajax=';
            return $exclusions;
        });
    }

    /**
     * Filtri per Astra
     */
    private function registerAstraFilters(): void
    {
        // Astra è lightweight, poche esclusioni necessarie
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'astra-';
            return $exclusions;
        });
    }

    /**
     * Filtri per GeneratePress
     */
    private function registerGeneratePressFilters(): void
    {
        // GeneratePress è lightweight, poche esclusioni necessarie
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'generate-';
            return $exclusions;
        });
    }

    /**
     * Registra filtri per page builder
     */
    private function registerPageBuilderFilters(): void
    {
        $builders = $this->detector->getActivePageBuilders();

        foreach ($builders as $builder) {
            switch ($builder) {
                case 'elementor':
                    $this->registerElementorFilters();
                    break;

                case 'beaver-builder':
                    $this->registerBeaverBuilderFilters();
                    break;

                case 'wpbakery':
                    $this->registerWPBakeryFilters();
                    break;

                case 'oxygen':
                    $this->registerOxygenFilters();
                    break;

                case 'bricks':
                    $this->registerBricksFilters();
                    break;
            }
        }
    }

    /**
     * Filtri per Elementor
     */
    private function registerElementorFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'elementor-frontend';
            $exclusions[] = 'elementor-waypoints';
            $exclusions[] = 'swiper';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?elementor-preview=';
            $exclusions[] = '?ver=';
            return $exclusions;
        });

        add_filter('fp_ps_minify_html_exclusions', function ($exclusions) {
            $exclusions[] = 'data-elementor-';
            $exclusions[] = 'elementor-element';
            return $exclusions;
        });
    }

    /**
     * Filtri per Beaver Builder
     */
    private function registerBeaverBuilderFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'fl-builder';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?fl_builder';
            return $exclusions;
        });
    }

    /**
     * Filtri per WPBakery
     */
    private function registerWPBakeryFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'wpb_composer';
            $exclusions[] = 'vc_';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?vc_editable=';
            return $exclusions;
        });
    }

    /**
     * Filtri per Oxygen
     */
    private function registerOxygenFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'oxygen';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?ct_builder=';
            $exclusions[] = '?oxygen_iframe=';
            return $exclusions;
        });
    }

    /**
     * Filtri per Bricks
     */
    private function registerBricksFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'bricks-';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?bricks=';
            return $exclusions;
        });
    }

    /**
     * Registra filtri per WooCommerce
     */
    private function registerWooCommerceFilters(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'wc-';
            $exclusions[] = 'woocommerce';
            $exclusions[] = 'wc-add-to-cart';
            $exclusions[] = 'wc-cart-fragments';
            return $exclusions;
        });

        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '/shop/';
            $exclusions[] = '/product/';
            $exclusions[] = '?wc-ajax=';
            $exclusions[] = '?add_to_wishlist=';
            return $exclusions;
        });

        // Non minificare HTML nelle pagine WooCommerce critiche
        add_filter('fp_ps_should_minify_html', function ($should) {
            if (function_exists('is_cart') && is_cart()) {
                return false;
            }
            if (function_exists('is_checkout') && is_checkout()) {
                return false;
            }
            return $should;
        });
    }
}

