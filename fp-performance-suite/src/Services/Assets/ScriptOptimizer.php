<?php

namespace FP\PerfSuite\Services\Assets;

use function apply_filters;
use function in_array;
use function is_admin;
use function str_replace;
use function strpos;

/**
 * JavaScript Script Tag Optimizer
 *
 * Adds defer and async attributes to script tags for better loading performance
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ScriptOptimizer
{
    /** @var array<int, string> */
    private array $skipHandles = [
        // Core jQuery (must load first)
        'jquery', 'jquery-core', 'jquery-migrate', 'jquery-ui-core',
        
        // WooCommerce (critical for checkout)
        'wc-checkout', 'wc-cart', 'wc-cart-fragments', 'wc-add-to-cart',
        'wc-add-to-cart-variation', 'woocommerce', 'wc-single-product',
        'wc-country-select', 'wc-address-i18n', 'selectWoo',
        
        // Payment Gateways (must load synchronously!)
        'stripe', 'stripe-js', 'stripe-v3', 'stripe-checkout',
        'paypal-sdk', 'paypal-checkout-sdk', 'paypal-button',
        'square', 'square-js', 'authorize-net', 'braintree',
        'mollie-components', 'klarna-payments',
        
        // Forms (validation must work)
        'contact-form-7', 'wpcf7-recaptcha',
        'gform_gravityforms', 'gform_conditional_logic', 'gform_json',
        'wpforms', 'wpforms-validation', 'ninja-forms',
        'elementor-frontend', 'elementor-pro-frontend',
        
        // LMS (quiz, video, interactive)
        'learndash', 'learndash-script', 'learndash-front',
        'tutor', 'tutor-frontend', 'sensei-frontend',
        'lifterlms', 'llms-student',
        
        // Multivendor (dashboard critical)
        'dokan-scripts', 'dokan-vendor-dashboard',
        'wcfm-scripts', 'wcfm-admin',
        'wc-vendors', 'wcv-frontend',
        
        // reCAPTCHA (must load before form)
        'google-recaptcha', 'recaptcha', 'google-invisible-recaptcha',
        
        // Other critical
        'wc-password-strength-meter', 'password-strength-meter',
        'wp-mediaelement', 'mediaelement',
    ];

    /**
     * Filter script tag to add defer/async attributes
     *
     * @param string $tag Original script tag
     * @param string $handle Script handle
     * @param string $src Script source URL
     * @param bool $defer Whether to add defer attribute
     * @param bool $async Whether to add async attribute
     * @return string Modified script tag
     */
    public function filterScriptTag(string $tag, string $handle, string $src, bool $defer = false, bool $async = false): string
    {
        if (is_admin()) {
            return $tag;
        }

        $skipHandles = apply_filters('fp_ps_defer_skip_handles', $this->skipHandles);

        if (in_array($handle, $skipHandles, true)) {
            return $tag;
        }

        if ($defer && strpos($tag, ' defer') === false) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }

        if ($async && strpos($tag, ' async') === false) {
            $tag = str_replace('<script ', '<script async ', $tag);
        }

        return $tag;
    }

    /**
     * Set custom skip handles
     *
     * @param array<int, string> $handles
     */
    public function setSkipHandles(array $handles): void
    {
        $this->skipHandles = $handles;
    }

    /**
     * Get current skip handles
     *
     * @return array<int, string>
     */
    public function getSkipHandles(): array
    {
        return $this->skipHandles;
    }
}
