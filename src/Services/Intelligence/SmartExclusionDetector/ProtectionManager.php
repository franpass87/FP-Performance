<?php

namespace FP\PerfSuite\Services\Intelligence\SmartExclusionDetector;

/**
 * Gestisce le protezioni built-in e il filtraggio
 * 
 * @package FP\PerfSuite\Services\Intelligence\SmartExclusionDetector
 * @author Francesco Passeri
 */
class ProtectionManager
{
    /**
     * Ottieni tutte le protezioni built-in nel PageCache
     * 
     * @return array Lista di pattern già protetti di default
     */
    public function getBuiltInProtections(): array
    {
        return [
            // WordPress Core - già protetti nel PageCache
            'core' => [
                '/xmlrpc.php',
                '/wp-cron.php',
                '/wp-login.php',
                '/wp-signup.php',
                '/wp-trackback.php',
                '/wp-comments-post.php',
                '/wp-sitemap',
                'sitemap.xml',
                'sitemap_index.xml',
                '/feed/',
                '/rss/',
                '/atom/',
                'robots.txt',
                '/wp-json/',
                '/wp-admin/',
            ],

            // WooCommerce - già protetto
            'woocommerce' => [
                '/cart',
                '/checkout',
                '/my-account',
                '/add-to-cart',
                '/remove-from-cart',
                '/wc-ajax',
                '/wc-api',
            ],

            // Easy Digital Downloads - già protetto
            'edd' => [
                '/edd-ajax',
                '/edd-api',
                '/purchase',
                '/downloads',
            ],

            // MemberPress - già protetto
            'memberpress' => [
                '/membership',
                '/register',
                '/mepr',
            ],

            // LearnDash - già protetto
            'learndash' => [
                '/courses/',
                '/lessons/',
                '/topic/',
                '/quiz/',
            ],

            // bbPress - già protetto
            'bbpress' => [
                '/forums/',
                '/forum/',
                '/topic/',
                '/reply/',
            ],

            // BuddyPress - già protetto
            'buddypress' => [
                '/members/',
                '/groups/',
                '/activity/',
                '/profile/',
            ],

            // Conditional tags già protetti
            'conditional_tags' => [
                'is_cart()',
                'is_checkout()',
                'is_account_page()',
                'is_preview()',
                'is_customize_preview()',
                'is_feed()',
                'is_search()',
                'is_404()',
            ],
        ];
    }

    /**
     * Filtra gli URL che sono già protetti di default
     * 
     * @param array $detected URL rilevati
     * @param array $protected URL già protetti
     * @return array URL filtrati (solo quelli non ancora protetti)
     */
    public function filterOutProtected(array $detected, array $protected): array
    {
        $filtered = [];
        $allProtectedPatterns = [];

        // Appiattisci l'array delle protezioni
        foreach ($protected as $category => $patterns) {
            if ($category !== 'conditional_tags') {
                $allProtectedPatterns = array_merge($allProtectedPatterns, $patterns);
            }
        }

        // Filtra i rilevati
        foreach ($detected as $item) {
            $isProtected = false;
            $pattern = $item['pattern'] ?? '';

            foreach ($allProtectedPatterns as $protectedPattern) {
                if (stripos($pattern, $protectedPattern) !== false || stripos($protectedPattern, $pattern) !== false) {
                    $isProtected = true;
                    break;
                }
            }

            if (!$isProtected) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }
}















