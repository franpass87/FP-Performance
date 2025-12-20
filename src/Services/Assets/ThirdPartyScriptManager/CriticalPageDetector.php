<?php

namespace FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;

/**
 * Rileva pagine critiche dove non ritardare gli script
 * 
 * @package FP\PerfSuite\Services\Assets\ThirdPartyScriptManager
 * @author Francesco Passeri
 */
class CriticalPageDetector
{
    /**
     * Verifica se la pagina corrente è critica
     */
    public function isCriticalPage(): bool
    {
        // WooCommerce critical pages
        if (function_exists('is_woocommerce')) {
            if (is_cart() || is_checkout() || is_account_page()) {
                return true;
            }
        }

        // EDD critical pages
        if (function_exists('edd_is_checkout')) {
            if (edd_is_checkout()) {
                return true;
            }
        }

        // Contact forms (Contact Form 7, Gravity Forms, etc.)
        if (is_page()) {
            global $post;
            if ($post && (
                has_shortcode($post->post_content, 'contact-form-7') ||
                has_shortcode($post->post_content, 'gravityform') ||
                has_shortcode($post->post_content, 'wpforms')
            )) {
                return true;
            }
        }

        // Check URL patterns
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (!empty($requestUri)) {
            $criticalPatterns = [
                '/checkout',
                '/cart',
                '/payment',
                '/order',
                '/account',
                '/login',
                '/register',
                '/wp-login',
                '/wp-admin',
                // LMS quiz (interactive)
                '/quiz/',
                '/lesson/',
            ];

            foreach ($criticalPatterns as $pattern) {
                if (strpos($requestUri, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}















