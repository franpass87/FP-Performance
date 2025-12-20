<?php

namespace FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

/**
 * Rileva URL sensibili da escludere dalla cache
 * 
 * @package FP\PerfSuite\Services\Intelligence\SmartExclusionDetector
 * @author Francesco Passeri
 */
class UrlDetector
{
    /**
     * Pattern sensibili comuni
     */
    private const SENSITIVE_URL_PATTERNS = [
        // E-commerce
        '/cart', '/checkout', '/order', '/payment', '/billing',
        '/add-to-cart', '/remove-from-cart', '/update-cart',
        '/wc-ajax', '/edd-ajax',
        
        // User areas
        '/account', '/profile', '/dashboard', '/admin',
        '/login', '/logout', '/register', '/signup',
        '/my-account', '/user', '/member',
        
        // Forms & Actions
        '/contact', '/submit', '/form', '/action',
        '/wp-admin', '/wp-login', '/wp-json',
        
        // Dynamic content
        '/search', '/filter', '/ajax', '/api',
        '/preview', '/customize',
    ];

    /**
     * Rileva URL sensibili standard
     */
    public function detectStandardSensitiveUrls(): array
    {
        $detected = [];
        $homeUrl = home_url();

        foreach (self::SENSITIVE_URL_PATTERNS as $pattern) {
            $detected[] = [
                'url' => $homeUrl . $pattern,
                'pattern' => $pattern,
                'reason' => $this->getReasonForPattern($pattern),
                'confidence' => 0.8,
                'type' => 'standard',
            ];
        }

        return $detected;
    }

    /**
     * Rileva URL basandosi sui plugin attivi
     */
    public function detectPluginBasedUrls(): array
    {
        $detected = [];
        $homeUrl = home_url();

        // WooCommerce
        if (class_exists('WooCommerce')) {
            $wooPages = [
                '/shop' => __('WooCommerce shop page', 'fp-performance-suite'),
                '/product' => __('WooCommerce product pages', 'fp-performance-suite'),
                '/product-category' => __('WooCommerce category pages', 'fp-performance-suite'),
            ];

            foreach ($wooPages as $page => $reason) {
                $detected[] = [
                    'url' => $homeUrl . $page,
                    'pattern' => $page,
                    'reason' => $reason,
                    'confidence' => 0.9,
                    'type' => 'woocommerce',
                ];
            }
        }

        // Easy Digital Downloads
        if (class_exists('Easy_Digital_Downloads')) {
            $eddPages = [
                '/downloads' => __('EDD downloads page', 'fp-performance-suite'),
                '/purchase' => __('EDD purchase page', 'fp-performance-suite'),
            ];

            foreach ($eddPages as $page => $reason) {
                $detected[] = [
                    'url' => $homeUrl . $page,
                    'pattern' => $page,
                    'reason' => $reason,
                    'confidence' => 0.9,
                    'type' => 'edd',
                ];
            }
        }

        // MemberPress
        if (class_exists('MeprOptions')) {
            $detected[] = [
                'url' => $homeUrl . '/membership',
                'pattern' => '/membership',
                'reason' => __('MemberPress membership pages', 'fp-performance-suite'),
                'confidence' => 0.9,
                'type' => 'memberpress',
            ];
        }

        return $detected;
    }

    /**
     * Rileva da comportamento utente (errori, slow pages)
     */
    public function detectFromBehavior(): array
    {
        // Placeholder per future implementazioni basate su ML/Analytics
        return [];
    }

    /**
     * Ottiene la ragione per un pattern
     */
    private function getReasonForPattern(string $pattern): string
    {
        $reasons = [
            '/cart' => __('Shopping cart - dynamic content', 'fp-performance-suite'),
            '/checkout' => __('Checkout page - sensitive data', 'fp-performance-suite'),
            '/order' => __('Order pages - user-specific', 'fp-performance-suite'),
            '/payment' => __('Payment processing - sensitive', 'fp-performance-suite'),
            '/account' => __('User account - private data', 'fp-performance-suite'),
            '/profile' => __('User profile - private data', 'fp-performance-suite'),
            '/dashboard' => __('User dashboard - dynamic', 'fp-performance-suite'),
            '/login' => __('Login page - session-based', 'fp-performance-suite'),
            '/logout' => __('Logout action - must process', 'fp-performance-suite'),
            '/search' => __('Search results - dynamic', 'fp-performance-suite'),
            '/ajax' => __('AJAX requests - dynamic', 'fp-performance-suite'),
            '/api' => __('API endpoints - dynamic', 'fp-performance-suite'),
        ];

        return $reasons[$pattern] ?? __('Sensitive URL pattern', 'fp-performance-suite');
    }

    /**
     * Verifica se un URL esiste
     */
    public function urlExists(string $url): bool
    {
        $response = wp_remote_head($url, [
            'timeout' => 5,
            'redirection' => 0,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        return $statusCode >= 200 && $statusCode < 400;
    }
}















