<?php

namespace FP\PerfSuite\Services\Compatibility;

use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Logger;

/**
 * Theme Compatibility Manager
 * 
 * Gestisce la compatibilità con temi e plugin popolari
 *
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ThemeCompatibility
{
    private ServiceContainer $container;
    private ThemeDetector $detector;
    private static bool $registered = false;

    public function __construct(ServiceContainer $container, ThemeDetector $detector)
    {
        $this->container = $container;
        $this->detector = $detector;
    }

    /**
     * Registra gli hook di compatibilità
     */
    public function register(): void
    {
        // Evita registrazioni multiple
        if (self::$registered) {
            return;
        }
        
        // Applica fix specifici per tema
        add_action('init', [$this, 'applyThemeFixes'], 5);
        
        // Applica fix per page builder
        add_action('init', [$this, 'applyPageBuilderFixes'], 5);

        // Compatibilità WooCommerce
        if ($this->detector->hasWooCommerce()) {
            add_action('init', [$this, 'applyWooCommerceFixes'], 5);
        }

        // Hook per disabilitare ottimizzazioni su editor
        add_filter('fp_ps_should_optimize', [$this, 'shouldOptimize']);

        self::$registered = true;
        
        Logger::debug('Theme compatibility initialized', [
            'theme' => $this->detector->getThemeName(),
            'builders' => $this->detector->getActivePageBuilders(),
        ]);
    }

    /**
     * Verifica se le ottimizzazioni devono essere applicate
     */
    public function shouldOptimize(bool $should): bool
    {
        // Non ottimizzare negli editor dei page builder
        if ($this->isPageBuilderEditor()) {
            return false;
        }

        // Non ottimizzare nel customizer
        if (is_customize_preview()) {
            return false;
        }

        // Non ottimizzare in admin
        if (is_admin()) {
            return false;
        }

        return $should;
    }

    /**
     * Verifica se siamo in un editor di page builder
     */
    private function isPageBuilderEditor(): bool
    {
        // Elementor
        if (isset($_GET['elementor-preview']) || isset($_GET['elementor_library'])) {
            return true;
        }

        // Divi Builder
        if (isset($_GET['et_fb']) || isset($_GET['et_pb_preview'])) {
            return true;
        }

        // Beaver Builder
        if (isset($_GET['fl_builder'])) {
            return true;
        }

        // WPBakery
        if (isset($_GET['vc_editable']) || isset($_GET['vc_action'])) {
            return true;
        }

        // Oxygen
        if (isset($_GET['ct_builder']) || isset($_GET['oxygen_iframe'])) {
            return true;
        }

        // Bricks
        if (isset($_GET['bricks']) && $_GET['bricks'] === 'run') {
            return true;
        }

        // Brizy
        if (isset($_GET['brizy-edit']) || isset($_GET['brizy-edit-iframe'])) {
            return true;
        }

        return false;
    }

    /**
     * Applica fix specifici per tema
     */
    public function applyThemeFixes(): void
    {
        $theme = $this->detector->getCurrentTheme();

        switch ($theme) {
            case 'avada':
                $this->fixAvada();
                break;

            case 'divi':
                $this->fixDivi();
                break;

            case 'enfold':
                $this->fixEnfold();
                break;

            case 'salient':
                $this->fixSalient();
                break;

            case 'the7':
                $this->fixThe7();
                break;

            case 'astra':
                $this->fixAstra();
                break;
        }
    }

    /**
     * Fix per Avada
     */
    private function fixAvada(): void
    {
        // Avada ha lazy loading nativo
        add_filter('fp_ps_lazy_load_images', '__return_false');
        
        // Preserva gli script AJAX di Avada
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'avada-';
            $exclusions[] = 'fusion-';
            $exclusions[] = 'isotope';
            $exclusions[] = 'jquery-easing';
            return $exclusions;
        });
    }

    /**
     * Fix per Divi
     */
    private function fixDivi(): void
    {
        // Preserva gli attributi data di Divi
        add_filter('fp_ps_minify_html_exclusions', function ($exclusions) {
            $exclusions[] = 'data-et-';
            $exclusions[] = 'et_pb_';
            return $exclusions;
        });

        // Preserva gli script del builder
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'divi-';
            $exclusions[] = 'et-';
            return $exclusions;
        });
    }

    /**
     * Fix per Enfold
     */
    private function fixEnfold(): void
    {
        // Preserva gli script Avia
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'avia-';
            return $exclusions;
        });
    }

    /**
     * Fix per Salient
     */
    private function fixSalient(): void
    {
        // Salient ha animazioni complesse
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'salient-';
            $exclusions[] = 'nectar-';
            return $exclusions;
        });

        // Preserva AJAX navigation
        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '?salient_ajax';
            return $exclusions;
        });
    }

    /**
     * Fix per The7
     */
    private function fixThe7(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'the7-';
            $exclusions[] = 'dt-';
            return $exclusions;
        });
    }

    /**
     * Fix per Astra
     */
    private function fixAstra(): void
    {
        // Astra è già ottimizzato, poche modifiche necessarie
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'astra-addon-';
            return $exclusions;
        });
    }

    /**
     * Applica fix per page builder
     */
    public function applyPageBuilderFixes(): void
    {
        $builders = $this->detector->getActivePageBuilders();

        foreach ($builders as $builder) {
            switch ($builder) {
                case 'elementor':
                    $this->fixElementor();
                    break;

                case 'beaver-builder':
                    $this->fixBeaverBuilder();
                    break;

                case 'wpbakery':
                    $this->fixWPBakery();
                    break;

                case 'oxygen':
                    $this->fixOxygen();
                    break;

                case 'bricks':
                    $this->fixBricks();
                    break;
            }
        }
    }

    /**
     * Fix per Elementor
     */
    private function fixElementor(): void
    {
        // Non minificare HTML nelle pagine Elementor in edit
        add_filter('fp_ps_should_minify_html', function ($should) {
            if (isset($_GET['elementor-preview'])) {
                return false;
            }
            return $should;
        });

        // Preserva settings Elementor
        add_filter('fp_ps_minify_html_exclusions', function ($exclusions) {
            $exclusions[] = 'data-elementor-';
            $exclusions[] = 'data-settings';
            return $exclusions;
        });
    }

    /**
     * Fix per Beaver Builder
     */
    private function fixBeaverBuilder(): void
    {
        add_filter('fp_ps_should_minify_html', function ($should) {
            if (isset($_GET['fl_builder'])) {
                return false;
            }
            return $should;
        });
    }

    /**
     * Fix per WPBakery
     */
    private function fixWPBakery(): void
    {
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'wpb_composer';
            $exclusions[] = 'vc_';
            return $exclusions;
        });
    }

    /**
     * Fix per Oxygen
     */
    private function fixOxygen(): void
    {
        add_filter('fp_ps_should_optimize', function ($should) {
            if (isset($_GET['ct_builder']) || isset($_GET['oxygen_iframe'])) {
                return false;
            }
            return $should;
        });
    }

    /**
     * Fix per Bricks
     */
    private function fixBricks(): void
    {
        add_filter('fp_ps_should_optimize', function ($should) {
            if (isset($_GET['bricks'])) {
                return false;
            }
            return $should;
        });
    }

    /**
     * Applica fix per WooCommerce
     */
    public function applyWooCommerceFixes(): void
    {
        // Non cachare pagine dinamiche
        add_filter('fp_ps_cache_exclusions', function ($exclusions) {
            $exclusions[] = '/cart/';
            $exclusions[] = '/checkout/';
            $exclusions[] = '/my-account/';
            $exclusions[] = '?add-to-cart=';
            return $exclusions;
        });

        // Non minificare HTML su cart/checkout per preservare form
        add_filter('fp_ps_should_minify_html', function ($should) {
            if (function_exists('is_cart') && is_cart()) {
                return false;
            }
            if (function_exists('is_checkout') && is_checkout()) {
                return false;
            }
            return $should;
        });

        // Preserva script WooCommerce critici
        add_filter('fp_ps_defer_js_exclusions', function ($exclusions) {
            $exclusions[] = 'wc-';
            $exclusions[] = 'woocommerce';
            $exclusions[] = 'wc-add-to-cart';
            $exclusions[] = 'wc-cart-fragments';
            return $exclusions;
        });
    }

    /**
     * Ottiene il livello di compatibilità per il tema corrente
     */
    public function getCompatibilityLevel(): array
    {
        $theme = $this->detector->getCurrentTheme();
        $known = $this->detector->isKnownTheme();

        if (!$known) {
            return [
                'level' => 'unknown',
                'message' => __('Tema non riconosciuto. Le ottimizzazioni potrebbero richiedere configurazione manuale.', 'fp-performance-suite'),
                'color' => 'warning',
            ];
        }

        // Temi completamente testati
        $fullyTested = ['astra', 'generatepress', 'kadence', 'oceanwp'];
        if (in_array($theme, $fullyTested, true)) {
            return [
                'level' => 'full',
                'message' => __('Piena compatibilità. Tutte le ottimizzazioni possono essere abilitate.', 'fp-performance-suite'),
                'color' => 'success',
            ];
        }

        // Temi parzialmente testati
        $partiallyTested = ['avada', 'divi', 'enfold', 'salient', 'the7'];
        if (in_array($theme, $partiallyTested, true)) {
            return [
                'level' => 'partial',
                'message' => __('Buona compatibilità. Alcuni fix automatici sono applicati.', 'fp-performance-suite'),
                'color' => 'info',
            ];
        }

        return [
            'level' => 'basic',
            'message' => __('Compatibilità base. Test le ottimizzazioni una per una.', 'fp-performance-suite'),
            'color' => 'warning',
        ];
    }
}

