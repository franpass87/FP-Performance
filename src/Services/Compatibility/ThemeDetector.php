<?php

namespace FP\PerfSuite\Services\Compatibility;

use FP\PerfSuite\Utils\Logger;

/**
 * Theme & Plugin Detector
 *
 * Detects active theme and page builders for optimal configuration
 *
 * @package FP\PerfSuite\Services\Compatibility
 * @author Francesco Passeri
 */
class ThemeDetector
{
    /**
     * Get active theme information
     *
     * @return array{name:string,slug:string,version:string,parent?:string}
     */
    public function getActiveTheme(): array
    {
        $theme = wp_get_theme();
        
        $info = [
            'name' => $theme->get('Name'),
            'slug' => $theme->get_stylesheet(),
            'version' => $theme->get('Version'),
        ];
        
        // Check if child theme
        if ($theme->parent()) {
            $info['parent'] = $theme->parent()->get('Name');
            $info['parent_slug'] = $theme->parent()->get_stylesheet();
        }
        
        return $info;
    }
    
    /**
     * Detect active page builder
     *
     * @return array{name:string,slug:string,detected:bool}
     */
    public function detectPageBuilder(): array
    {
        $builders = [
            'wpbakery' => [
                'name' => 'WPBakery Page Builder',
                'check' => function() {
                    return defined('WPB_VC_VERSION') || class_exists('Vc_Manager');
                },
            ],
            'elementor' => [
                'name' => 'Elementor',
                'check' => function() {
                    return defined('ELEMENTOR_VERSION') || class_exists('\Elementor\Plugin');
                },
            ],
            'divi' => [
                'name' => 'Divi Builder',
                'check' => function() {
                    return defined('ET_BUILDER_VERSION') || function_exists('et_divi_fonts_url');
                },
            ],
            'beaver' => [
                'name' => 'Beaver Builder',
                'check' => function() {
                    return class_exists('FLBuilder');
                },
            ],
            'oxygen' => [
                'name' => 'Oxygen',
                'check' => function() {
                    return defined('CT_VERSION');
                },
            ],
            'gutenberg' => [
                'name' => 'Gutenberg (Block Editor)',
                'check' => function() {
                    return function_exists('register_block_type');
                },
            ],
        ];
        
        foreach ($builders as $slug => $builder) {
            if ($builder['check']()) {
                return [
                    'name' => $builder['name'],
                    'slug' => $slug,
                    'detected' => true,
                ];
            }
        }
        
        return [
            'name' => 'None',
            'slug' => 'none',
            'detected' => false,
        ];
    }
    
    /**
     * Check if specific theme is active
     *
     * @param string $themeSlug Theme slug to check
     * @return bool True if theme is active
     */
    public function isThemeActive(string $themeSlug): bool
    {
        $theme = $this->getActiveTheme();
        
        return $theme['slug'] === $themeSlug || 
               ($theme['parent_slug'] ?? '') === $themeSlug ||
               stripos($theme['name'], $themeSlug) !== false;
    }
    
    /**
     * Check if Salient theme is active
     *
     * @return bool True if Salient is active
     */
    public function isSalient(): bool
    {
        return $this->isThemeActive('salient');
    }
    
    /**
     * Check if Avada theme is active
     *
     * @return bool True if Avada is active
     */
    public function isAvada(): bool
    {
        return $this->isThemeActive('avada');
    }
    
    /**
     * Check if Divi theme is active
     *
     * @return bool True if Divi is active
     */
    public function isDivi(): bool
    {
        return $this->isThemeActive('divi') || defined('ET_BUILDER_VERSION');
    }
    
    /**
     * Check if Astra theme is active
     *
     * @return bool True if Astra is active
     */
    public function isAstra(): bool
    {
        return $this->isThemeActive('astra');
    }
    
    /**
     * Get recommended configuration based on detected environment
     *
     * @return array Configuration recommendations
     */
    public function getRecommendedConfig(): array
    {
        $theme = $this->getActiveTheme();
        $builder = $this->detectPageBuilder();
        
        $config = [
            'theme' => $theme,
            'page_builder' => $builder,
            'recommendations' => [],
        ];
        
        // Salient specific recommendations
        if ($this->isSalient()) {
            $config['recommendations'] = $this->getSalientRecommendations($builder);
        }
        // Avada specific recommendations
        elseif ($this->isAvada()) {
            $config['recommendations'] = $this->getAvadaRecommendations($builder);
        }
        // Divi specific recommendations
        elseif ($this->isDivi()) {
            $config['recommendations'] = $this->getDiviRecommendations();
        }
        // Astra specific recommendations
        elseif ($this->isAstra()) {
            $config['recommendations'] = $this->getAstraRecommendations($builder);
        }
        // Generic recommendations
        else {
            $config['recommendations'] = $this->getGenericRecommendations($builder);
        }
        
        return $config;
    }
    
    /**
     * Get Salient specific recommendations
     *
     * @param array $builder Page builder info
     * @return array Recommendations
     */
    private function getSalientRecommendations(array $builder): array
    {
        return [
            'object_cache' => [
                'enabled' => true,
                'priority' => 'high',
                'reason' => 'Salient fa molte query per opzioni tema',
            ],
            'edge_cache' => [
                'enabled' => true,
                'priority' => 'high',
                'reason' => 'Riduce drasticamente TTFB',
            ],
            'third_party_scripts' => [
                'enabled' => true,
                'delay_all' => false,
                'priority' => 'high',
                'reason' => 'Salient carica molti script esterni',
                'exclude_patterns' => [
                    'salient-',
                    'nectar-',
                    'jquery',
                    'modernizr',
                ],
            ],
            'http2_push' => [
                'enabled' => true,
                'push_js' => false,
                'priority' => 'medium',
                'reason' => 'jQuery dependencies - push solo CSS e font',
            ],
            'smart_delivery' => [
                'enabled' => true,
                'priority' => 'high',
                'reason' => 'Ottimizza per mobile e connessioni lente',
            ],
            'core_web_vitals' => [
                'enabled' => true,
                'priority' => 'high',
                'reason' => 'Monitoraggio CLS critico per animazioni Salient',
                'alert_threshold_cls' => 0.1,
            ],
            'avif_converter' => [
                'enabled' => false,
                'auto_deliver' => false,
                'priority' => 'low',
                'reason' => 'Testare prima in staging - slider/lightbox potrebbero avere problemi',
            ],
            'service_worker' => [
                'enabled' => false,
                'priority' => 'low',
                'reason' => 'Non compatibile con ' . ($builder['name'] ?? 'builder dinamico'),
            ],
        ];
    }
    
    /**
     * Get Avada specific recommendations
     *
     * @param array $builder Page builder info
     * @return array Recommendations
     */
    private function getAvadaRecommendations(array $builder): array
    {
        return [
            'object_cache' => [
                'enabled' => true,
                'priority' => 'high',
                'reason' => 'Avada Theme Options fa molte query',
            ],
            'third_party_scripts' => [
                'enabled' => true,
                'priority' => 'high',
                'exclude_patterns' => ['fusion-', 'avada-', 'jquery'],
            ],
            'http2_push' => [
                'enabled' => true,
                'push_js' => false,
                'priority' => 'medium',
            ],
            'avif_converter' => [
                'enabled' => false,
                'priority' => 'low',
                'reason' => 'Testare con Fusion Builder',
            ],
        ];
    }
    
    /**
     * Get Divi specific recommendations
     *
     * @return array Recommendations
     */
    private function getDiviRecommendations(): array
    {
        return [
            'object_cache' => [
                'enabled' => true,
                'priority' => 'high',
            ],
            'third_party_scripts' => [
                'enabled' => true,
                'priority' => 'high',
                'exclude_patterns' => ['et-', 'divi-', 'jquery'],
            ],
            'service_worker' => [
                'enabled' => false,
                'reason' => 'Incompatibile con Divi Visual Builder',
            ],
        ];
    }
    
    /**
     * Get Astra specific recommendations
     *
     * @param array $builder Page builder info
     * @return array Recommendations
     */
    private function getAstraRecommendations(array $builder): array
    {
        return [
            'object_cache' => [
                'enabled' => true,
                'priority' => 'medium',
            ],
            'avif_converter' => [
                'enabled' => true,
                'auto_deliver' => true,
                'priority' => 'high',
                'reason' => 'Astra è leggero, AVIF funziona bene',
            ],
            'service_worker' => [
                'enabled' => $builder['slug'] !== 'wpbakery',
                'priority' => 'medium',
            ],
        ];
    }
    
    /**
     * Get generic recommendations
     *
     * @param array $builder Page builder info
     * @return array Recommendations
     */
    private function getGenericRecommendations(array $builder): array
    {
        return [
            'object_cache' => [
                'enabled' => true,
                'priority' => 'medium',
            ],
            'edge_cache' => [
                'enabled' => true,
                'priority' => 'medium',
            ],
            'third_party_scripts' => [
                'enabled' => true,
                'priority' => 'high',
            ],
            'smart_delivery' => [
                'enabled' => true,
                'priority' => 'high',
            ],
            'core_web_vitals' => [
                'enabled' => true,
                'priority' => 'high',
            ],
        ];
    }
    
    /**
     * Log detection results
     */
    public function logDetection(): void
    {
        $theme = $this->getActiveTheme();
        $builder = $this->detectPageBuilder();
        
        Logger::info('Theme/Builder detected', [
            'theme' => $theme['name'],
            'theme_slug' => $theme['slug'],
            'builder' => $builder['name'],
            'builder_slug' => $builder['slug'],
        ]);
    }
}
