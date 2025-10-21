<?php

namespace FP\PerfSuite\Services\Compatibility;

/**
 * Theme Detector
 * 
 * Rileva il tema e page builder attivi per ottimizzazioni specifiche
 *
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ThemeDetector
{
    private ?string $currentTheme = null;
    private ?array $activePageBuilders = null;

    /**
     * Ottiene il tema corrente
     */
    public function getCurrentTheme(): string
    {
        if ($this->currentTheme === null) {
            $theme = wp_get_theme();
            $this->currentTheme = $theme->get_template();
        }

        return $this->currentTheme;
    }

    /**
     * Ottiene il nome del tema
     */
    public function getThemeName(): string
    {
        $theme = wp_get_theme();
        return $theme->get('Name');
    }

    /**
     * Verifica se un tema specifico è attivo
     */
    public function isTheme(string $themeName): bool
    {
        return $this->getCurrentTheme() === $themeName;
    }

    /**
     * Verifica se uno dei temi specificati è attivo
     */
    public function isAnyTheme(array $themeNames): bool
    {
        $current = $this->getCurrentTheme();
        return in_array($current, $themeNames, true);
    }

    /**
     * Rileva i page builder attivi
     */
    public function getActivePageBuilders(): array
    {
        if ($this->activePageBuilders !== null) {
            return $this->activePageBuilders;
        }

        $this->activePageBuilders = [];

        // Elementor
        if (defined('ELEMENTOR_VERSION') || class_exists('\\Elementor\\Plugin')) {
            $this->activePageBuilders[] = 'elementor';
        }

        // Divi Builder
        if (function_exists('et_setup_theme') || defined('ET_BUILDER_VERSION')) {
            $this->activePageBuilders[] = 'divi';
        }

        // Beaver Builder
        if (class_exists('FLBuilder') || class_exists('FLBuilderModel')) {
            $this->activePageBuilders[] = 'beaver-builder';
        }

        // WPBakery (Visual Composer)
        if (defined('WPB_VC_VERSION') || class_exists('Vc_Manager')) {
            $this->activePageBuilders[] = 'wpbakery';
        }

        // Oxygen Builder
        if (defined('CT_VERSION') || class_exists('CT_Component')) {
            $this->activePageBuilders[] = 'oxygen';
        }

        // Gutenberg (Block Editor)
        if (function_exists('has_blocks') && has_blocks()) {
            $this->activePageBuilders[] = 'gutenberg';
        }

        // Bricks Builder
        if (defined('BRICKS_VERSION') || class_exists('\\Bricks\\Database')) {
            $this->activePageBuilders[] = 'bricks';
        }

        // Brizy
        if (class_exists('Brizy_Editor') || defined('BRIZY_VERSION')) {
            $this->activePageBuilders[] = 'brizy';
        }

        // Zion Builder
        if (class_exists('\\ZionBuilder\\Plugin')) {
            $this->activePageBuilders[] = 'zion';
        }

        return $this->activePageBuilders;
    }

    /**
     * Verifica se un page builder è attivo
     */
    public function hasPageBuilder(string $builder): bool
    {
        return in_array($builder, $this->getActivePageBuilders(), true);
    }

    /**
     * Verifica se ci sono page builder attivi
     */
    public function hasAnyPageBuilder(): bool
    {
        return !empty($this->getActivePageBuilders());
    }

    /**
     * Ottiene temi popolari noti
     */
    public function getKnownThemes(): array
    {
        return [
            // Temi premium popolari
            'avada' => 'Avada',
            'enfold' => 'Enfold',
            'divi' => 'Divi',
            'salient' => 'Salient',
            'bridge' => 'Bridge',
            'betheme' => 'BeTheme',
            'the7' => 'The7',
            'flatsome' => 'Flatsome',
            'jupiter' => 'Jupiter',
            'x' => 'X Theme',
            
            // Temi WordPress.org popolari
            'astra' => 'Astra',
            'generatepress' => 'GeneratePress',
            'oceanwp' => 'OceanWP',
            'neve' => 'Neve',
            'kadence' => 'Kadence',
            'blocksy' => 'Blocksy',
            'twentytwentyfour' => 'Twenty Twenty-Four',
            'twentytwentythree' => 'Twenty Twenty-Three',
            
            // Temi WooCommerce
            'storefront' => 'Storefront',
            'woodmart' => 'WoodMart',
            'porto' => 'Porto',
        ];
    }

    /**
     * Verifica se il tema è un tema noto
     */
    public function isKnownTheme(): bool
    {
        $known = $this->getKnownThemes();
        return isset($known[$this->getCurrentTheme()]);
    }

    /**
     * Ottiene le caratteristiche del tema corrente
     */
    public function getThemeFeatures(): array
    {
        $theme = $this->getCurrentTheme();
        $features = [
            'ajax_navigation' => false,
            'lazy_loading' => false,
            'custom_fonts' => false,
            'page_builder' => $this->hasAnyPageBuilder(),
            'woocommerce' => $this->hasWooCommerce(),
        ];

        // Caratteristiche specifiche per tema
        switch ($theme) {
            case 'avada':
            case 'salient':
            case 'the7':
                $features['ajax_navigation'] = true;
                $features['lazy_loading'] = true;
                $features['custom_fonts'] = true;
                break;

            case 'divi':
            case 'enfold':
                $features['lazy_loading'] = true;
                $features['custom_fonts'] = true;
                break;

            case 'astra':
            case 'generatepress':
            case 'kadence':
                $features['lightweight'] = true;
                break;
        }

        return $features;
    }

    /**
     * Verifica se WooCommerce è attivo
     */
    public function hasWooCommerce(): bool
    {
        return class_exists('WooCommerce') || function_exists('WC');
    }

    /**
     * Verifica se è un tema e-commerce
     */
    public function isEcommerceTheme(): bool
    {
        if (!$this->hasWooCommerce()) {
            return false;
        }

        $ecommerceThemes = [
            'storefront',
            'woodmart',
            'flatsome',
            'porto',
            'shopkeeper',
            'electro',
        ];

        return $this->isAnyTheme($ecommerceThemes);
    }

    /**
     * Ottiene scripts critici del tema/builder
     */
    public function getCriticalScripts(): array
    {
        $critical = [];
        $builders = $this->getActivePageBuilders();

        foreach ($builders as $builder) {
            switch ($builder) {
                case 'elementor':
                    $critical[] = 'elementor-frontend';
                    $critical[] = 'elementor-waypoints';
                    break;

                case 'divi':
                    $critical[] = 'divi-custom-script';
                    $critical[] = 'et-builder-modules-script';
                    break;

                case 'beaver-builder':
                    $critical[] = 'fl-builder-layout';
                    break;

                case 'wpbakery':
                    $critical[] = 'wpb_composer_front_js';
                    break;

                case 'oxygen':
                    $critical[] = 'oxygen-universal-js';
                    break;

                case 'bricks':
                    $critical[] = 'bricks-scripts';
                    break;
            }
        }

        // Scripts critici tema-specifici
        $theme = $this->getCurrentTheme();
        switch ($theme) {
            case 'avada':
                $critical[] = 'avada';
                $critical[] = 'avada-header';
                break;

            case 'salient':
                $critical[] = 'salient-main';
                $critical[] = 'salient-init';
                break;

            case 'enfold':
                $critical[] = 'avia-default';
                $critical[] = 'avia-compat';
                break;
        }

        return array_unique($critical);
    }

    /**
     * Ottiene styles critici del tema/builder
     */
    public function getCriticalStyles(): array
    {
        $critical = [];
        $builders = $this->getActivePageBuilders();

        foreach ($builders as $builder) {
            switch ($builder) {
                case 'elementor':
                    $critical[] = 'elementor-frontend';
                    $critical[] = 'elementor-post';
                    break;

                case 'divi':
                    $critical[] = 'divi-style';
                    break;

                case 'beaver-builder':
                    $critical[] = 'fl-builder-layout';
                    break;

                case 'wpbakery':
                    $critical[] = 'js_composer_front';
                    break;

                case 'bricks':
                    $critical[] = 'bricks-frontend';
                    break;
            }
        }

        return array_unique($critical);
    }

    /**
     * Verifica se una risorsa è critica per il tema/builder
     */
    public function isCriticalAsset(string $handle, string $type = 'script'): bool
    {
        $critical = $type === 'script' ? $this->getCriticalScripts() : $this->getCriticalStyles();
        
        // Check exact match
        if (in_array($handle, $critical, true)) {
            return true;
        }

        // Check partial match per compatibilità
        foreach ($critical as $criticalHandle) {
            if (strpos($handle, $criticalHandle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Reset cache
     */
    public function resetCache(): void
    {
        $this->currentTheme = null;
        $this->activePageBuilders = null;
    }
}

