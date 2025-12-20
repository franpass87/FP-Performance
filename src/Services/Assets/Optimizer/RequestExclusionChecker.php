<?php

namespace FP\PerfSuite\Services\Assets\Optimizer;

use function defined;
use function function_exists;
use function is_customize_preview;
use function is_preview;
use function rest_get_url_prefix;
use function sanitize_text_field;
use function strpos;
use function wp_unslash;

/**
 * Verifica se una richiesta deve essere esclusa dall'ottimizzazione
 * 
 * @package FP\PerfSuite\Services\Assets\Optimizer
 * @author Francesco Passeri
 */
class RequestExclusionChecker
{
    /**
     * Check if current request should skip optimization
     */
    public function isRestOrAjaxRequest(): bool
    {
        // Check for REST API request
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return true;
        }

        // Check for AJAX request
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return true;
        }

        // Check for WP-Cron request
        if (defined('DOING_CRON') && DOING_CRON) {
            return true;
        }

        // Check for Customizer preview
        if (function_exists('is_customize_preview') && is_customize_preview()) {
            return true;
        }

        // Check for post preview
        if (function_exists('is_preview') && is_preview()) {
            return true;
        }

        // Check URL patterns
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
            
            // Check for WP-JSON in URL
            $restPrefix = rest_get_url_prefix();
            if (strpos($requestUri, '/' . $restPrefix . '/') !== false || strpos($requestUri, '/' . $restPrefix) !== false) {
                return true;
            }

            // Exclude critical WordPress files and endpoints
            $excludeFiles = [
                '/xmlrpc.php',
                '/wp-cron.php',
                '/wp-login.php',
                '/wp-signup.php',
                '/wp-trackback.php',
                '/wp-comments-post.php',
                '/wp-sitemap',
                'sitemap.xml',
                '/feed/',
                '/rss/',
                '/atom/',
            ];

            // WooCommerce critical pages
            $woocommercePages = [
                '/cart',
                '/checkout',
                '/my-account',
                '/wc-ajax',
                '/wc-api',
                '?add-to-cart=',
            ];

            // Easy Digital Downloads
            $eddPages = [
                '/edd-ajax',
                '/edd-api',
                '/purchase',
                '?edd_action=',
            ];

            // MemberPress, LearnDash, bbPress, BuddyPress
            $otherPluginPages = [
                '/membership',
                '/mepr',
                '/courses/',
                '/lessons/',
                '/forums/',
                '/members/',
                '/groups/',
                '/activity/',
            ];

            $excludePluginPages = array_merge($excludeFiles, $woocommercePages, $eddPages, $otherPluginPages);

            foreach ($excludePluginPages as $pattern) {
                if (strpos($requestUri, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica se escludere le ottimizzazioni per il plugin FP Privacy & Cookie Policy
     */
    public function shouldExcludeForPrivacyPlugin(): bool
    {
        // Controlla se il plugin FP Privacy è attivo
        if (!defined('FP_PRIVACY_VERSION')) {
            return false; // Plugin non attivo, procedi normalmente
        }

        // Controlla se il cookie di consenso esiste
        if (isset($_COOKIE['fp_consent_state_id'])) {
            // Consenso già dato, procedi normalmente
            return false;
        }

        // Banner cookie attivo e consenso non dato, escludi ottimizzazioni
        return true;
    }

    /**
     * Verifica se un asset appartiene al plugin FP Privacy & Cookie Policy
     */
    public function isPrivacyPluginAsset(string $handle, string $src): bool
    {
        // Controlla handle che contengono "fp-privacy" o "fp_privacy"
        if (strpos($handle, 'fp-privacy') !== false || strpos($handle, 'fp_privacy') !== false) {
            return true;
        }

        // Controlla URL che contengono il path del plugin
        if (strpos($src, 'FP-Privacy-and-Cookie-Policy') !== false || 
            strpos($src, 'fp-privacy-cookie-policy') !== false ||
            strpos($src, '/plugins/fp-privacy') !== false) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se un asset appartiene al plugin FP Restaurant Reservations
     */
    public function isReservationsPluginAsset(string $handle, string $src): bool
    {
        // Controlla handle che contengono "fp-resv", "fp_resv", "flatpickr"
        if (strpos($handle, 'fp-resv') !== false || 
            strpos($handle, 'fp_resv') !== false ||
            strpos($handle, 'flatpickr') !== false) {
            return true;
        }

        // Controlla URL che contengono il path del plugin
        if (strpos($src, 'FP-Restaurant-Reservations') !== false || 
            strpos($src, 'fp-restaurant-reservations') !== false ||
            strpos($src, '/plugins/fp-resv') !== false) {
            return true;
        }

        return false;
    }
}















