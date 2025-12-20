<?php

namespace FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

/**
 * Rileva CSS da escludere dall'ottimizzazione
 * 
 * @package FP\PerfSuite\Services\Intelligence\SmartExclusionDetector
 * @author Francesco Passeri
 */
class CssDetector
{
    /**
     * Rileva CSS da escludere dall'ottimizzazione
     */
    public function detectExcludeCss(): array
    {
        global $wp_styles;

        $exclude = [
            'plugin_specific' => [],
            'critical_files' => [],
            'admin_styles' => [],
        ];

        if (!isset($wp_styles) || !is_object($wp_styles)) {
            return $exclude;
        }

        // Pattern CSS che non dovrebbero essere ottimizzati
        $criticalCssPatterns = [
            'admin-bar' => __('WordPress admin bar styles', 'fp-performance-suite'),
            'elementor' => __('Elementor styles - may break layout', 'fp-performance-suite'),
            'wp-block-library' => __('Gutenberg blocks - critical for content', 'fp-performance-suite'),
            'dashicons' => __('WordPress icons', 'fp-performance-suite'),
            'woocommerce' => __('WooCommerce styles - critical for shop', 'fp-performance-suite'),
            'edd-' => __('Easy Digital Downloads styles', 'fp-performance-suite'),
            'learndash' => __('LearnDash styles', 'fp-performance-suite'),
            'contact-form-7' => __('Contact Form 7 - may break forms', 'fp-performance-suite'),
            'gravityforms' => __('Gravity Forms - may break forms', 'fp-performance-suite'),
        ];

        // Analizza gli stili registrati
        foreach ($wp_styles->registered as $handle => $style) {
            foreach ($criticalCssPatterns as $pattern => $reason) {
                if (stripos($handle, $pattern) !== false || (isset($style->src) && stripos($style->src, $pattern) !== false)) {
                    $exclude['plugin_specific'][] = [
                        'handle' => $handle,
                        'src' => $style->src ?? '',
                        'reason' => $reason,
                        'confidence' => 0.85,
                    ];
                    break;
                }
            }
        }

        // Rileva CSS admin
        foreach ($wp_styles->registered as $handle => $style) {
            if (stripos($handle, 'admin') !== false || (isset($style->src) && stripos($style->src, '/wp-admin/') !== false)) {
                $exclude['admin_styles'][] = [
                    'handle' => $handle,
                    'src' => $style->src ?? '',
                    'reason' => __('Admin-only styles should not be optimized', 'fp-performance-suite'),
                    'confidence' => 0.95,
                ];
            }
        }

        // Rileva CSS critici del tema
        $themeUrl = get_stylesheet_directory_uri();
        foreach ($wp_styles->registered as $handle => $style) {
            if (isset($style->src) && strpos($style->src, $themeUrl) !== false) {
                if (stripos($handle, 'style') !== false || stripos($handle, 'main') !== false) {
                    $exclude['critical_files'][] = [
                        'handle' => $handle,
                        'src' => $style->src,
                        'reason' => __('Main theme stylesheet - critical for layout', 'fp-performance-suite'),
                        'confidence' => 0.8,
                    ];
                }
            }
        }

        return $exclude;
    }
}















