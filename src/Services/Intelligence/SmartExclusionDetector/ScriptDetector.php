<?php

namespace FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

/**
 * Rileva script critici da non ottimizzare
 * 
 * @package FP\PerfSuite\Services\Intelligence\SmartExclusionDetector
 * @author Francesco Passeri
 */
class ScriptDetector
{
    /**
     * Script critici che non dovrebbero mai essere ottimizzati
     */
    private const CRITICAL_SCRIPTS = [
        'jquery-core',
        'jquery',
        'jquery-migrate',
        'wp-polyfill',
        'regenerator-runtime',
        'wp-hooks',
    ];

    /**
     * Rileva script critici dei plugin attivi
     */
    public function detectPluginCriticalScripts(): array
    {
        $critical = [];

        // WooCommerce
        if (class_exists('WooCommerce')) {
            $critical[] = [
                'handle' => 'wc-cart-fragments',
                'reason' => __('Required for WooCommerce cart functionality', 'fp-performance-suite'),
                'confidence' => 0.95,
            ];
        }

        // Elementor
        if (defined('ELEMENTOR_VERSION')) {
            $critical[] = [
                'handle' => 'elementor-frontend',
                'reason' => __('Required for Elementor animations and interactions', 'fp-performance-suite'),
                'confidence' => 0.9,
            ];
        }

        // Contact Form 7
        if (function_exists('wpcf7_enqueue_scripts')) {
            $critical[] = [
                'handle' => 'contact-form-7',
                'reason' => __('Required for Contact Form 7 functionality', 'fp-performance-suite'),
                'confidence' => 0.85,
            ];
        }

        return $critical;
    }

    /**
     * Analizza dipendenze per trovare script critici
     */
    public function analyzeDependencies(): array
    {
        global $wp_scripts;
        $critical = [];

        if (!isset($wp_scripts) || !is_object($wp_scripts)) {
            return $critical;
        }

        // Trova script con molte dipendenze (probabilmente critici)
        foreach ($wp_scripts->registered as $handle => $script) {
            if (isset($script->deps) && count($script->deps) > 3) {
                $critical[] = [
                    'handle' => $handle,
                    'reason' => sprintf(
                        __('Has %d dependencies - likely critical', 'fp-performance-suite'),
                        count($script->deps)
                    ),
                    'confidence' => 0.7,
                    'dependencies' => $script->deps,
                ];
            }
        }

        return $critical;
    }

    /**
     * Rileva JavaScript da escludere dall'ottimizzazione
     */
    public function detectExcludeJs(): array
    {
        global $wp_scripts;

        $exclude = [
            'core_dependencies' => [],
            'plugin_specific' => [],
            'inline_dependent' => [],
        ];

        if (!isset($wp_scripts) || !is_object($wp_scripts)) {
            return $exclude;
        }

        // Pattern JS che non dovrebbero essere ottimizzati
        $criticalJsPatterns = [
            'jquery' => __('jQuery - core dependency', 'fp-performance-suite'),
            'react' => __('React - framework dependency', 'fp-performance-suite'),
            'vue' => __('Vue - framework dependency', 'fp-performance-suite'),
            'angular' => __('Angular - framework dependency', 'fp-performance-suite'),
            'elementor' => __('Elementor - page builder', 'fp-performance-suite'),
            'woocommerce' => __('WooCommerce - e-commerce functionality', 'fp-performance-suite'),
            'stripe' => __('Stripe - payment processing', 'fp-performance-suite'),
            'paypal' => __('PayPal - payment processing', 'fp-performance-suite'),
            'recaptcha' => __('reCAPTCHA - security', 'fp-performance-suite'),
            'google-maps' => __('Google Maps - mapping', 'fp-performance-suite'),
        ];

        // Analizza gli script registrati
        foreach ($wp_scripts->registered as $handle => $script) {
            // Core dependencies
            if (in_array($handle, self::CRITICAL_SCRIPTS, true)) {
                $exclude['core_dependencies'][] = [
                    'handle' => $handle,
                    'src' => $script->src ?? '',
                    'reason' => __('Core WordPress dependency', 'fp-performance-suite'),
                    'confidence' => 1.0,
                ];
                continue;
            }

            // Plugin specific
            foreach ($criticalJsPatterns as $pattern => $reason) {
                if (stripos($handle, $pattern) !== false || (isset($script->src) && stripos($script->src, $pattern) !== false)) {
                    $exclude['plugin_specific'][] = [
                        'handle' => $handle,
                        'src' => $script->src ?? '',
                        'reason' => $reason,
                        'confidence' => 0.85,
                    ];
                    break;
                }
            }

            // Inline dependent (script con extra data)
            if (isset($script->extra) && !empty($script->extra)) {
                $exclude['inline_dependent'][] = [
                    'handle' => $handle,
                    'src' => $script->src ?? '',
                    'reason' => __('Has inline data - may break if optimized', 'fp-performance-suite'),
                    'confidence' => 0.75,
                ];
            }
        }

        return $exclude;
    }

    /**
     * Ottiene la lista degli script critici
     */
    public function getCriticalScripts(): array
    {
        return self::CRITICAL_SCRIPTS;
    }
}















