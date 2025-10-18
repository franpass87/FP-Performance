<?php

namespace FP\PerfSuite\Services\Intelligence;

/**
 * Smart Exclusion Detector
 * 
 * Sistema intelligente che rileva automaticamente:
 * - URL sensibili da escludere dalla cache
 * - Script critici da non ottimizzare
 * - Pattern comuni di e-commerce, form, etc.
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class SmartExclusionDetector
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
     * Script critici comuni (non ottimizzare)
     */
    private const CRITICAL_SCRIPTS = [
        'jquery', 'jquery-core', 'jquery-migrate',
        'wp-polyfill', 'regenerator-runtime', 'wp-hooks',
        'stripe', 'paypal', 'google-recaptcha',
        'analytics', 'gtag', 'facebook-pixel',
        'livechat', 'intercom', 'crisp',
    ];

    /**
     * Plugin patterns che richiedono esclusioni
     */
    private const PLUGIN_PATTERNS = [
        'woocommerce' => [
            'urls' => ['/cart', '/checkout', '/my-account', '/wc-ajax'],
            'scripts' => ['wc-', 'woocommerce'],
        ],
        'easy-digital-downloads' => [
            'urls' => ['/checkout', '/purchase', '/edd-ajax'],
            'scripts' => ['edd-'],
        ],
        'learndash' => [
            'urls' => ['/courses', '/lessons', '/quizzes'],
            'scripts' => ['learndash'],
        ],
        'memberpress' => [
            'urls' => ['/account', '/membership'],
            'scripts' => ['mepr-'],
        ],
        'bbpress' => [
            'urls' => ['/forums', '/topics'],
            'scripts' => ['bbpress'],
        ],
        'buddypress' => [
            'urls' => ['/members', '/groups', '/activity'],
            'scripts' => ['bp-'],
        ],
    ];

    /**
     * Rileva automaticamente URL da escludere
     */
    public function detectSensitiveUrls(): array
    {
        $exclusions = [
            'auto_detected' => [],
            'plugin_based' => [],
            'user_behavior' => [],
        ];

        // 1. Rileva URL sensibili standard
        $exclusions['auto_detected'] = $this->detectStandardSensitiveUrls();

        // 2. Rileva basandosi sui plugin attivi
        $exclusions['plugin_based'] = $this->detectPluginBasedUrls();

        // 3. Rileva da comportamento utente (errori, slow pages)
        $exclusions['user_behavior'] = $this->detectFromBehavior();

        return $exclusions;
    }

    /**
     * Rileva script critici da non ottimizzare
     */
    public function detectCriticalScripts(): array
    {
        $critical = [
            'always_exclude' => self::CRITICAL_SCRIPTS,
            'plugin_critical' => [],
            'dependency_critical' => [],
        ];

        // Rileva script critici dei plugin attivi
        $critical['plugin_critical'] = $this->detectPluginCriticalScripts();

        // Analizza dipendenze per trovare script critici
        $critical['dependency_critical'] = $this->analyzeDependencies();

        return $critical;
    }

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
            'jquery' => __('jQuery - core dependency for many scripts', 'fp-performance-suite'),
            'jquery-migrate' => __('jQuery Migrate - compatibility layer', 'fp-performance-suite'),
            'wp-polyfill' => __('WordPress polyfills - required for modern JS', 'fp-performance-suite'),
            'regenerator-runtime' => __('Required for async/await support', 'fp-performance-suite'),
            'wp-hooks' => __('WordPress hooks system', 'fp-performance-suite'),
            'stripe' => __('Stripe payment - must load from official CDN', 'fp-performance-suite'),
            'paypal' => __('PayPal - must load from official CDN', 'fp-performance-suite'),
            'google-recaptcha' => __('Google reCAPTCHA - must load from Google', 'fp-performance-suite'),
            'woocommerce' => __('WooCommerce scripts - critical for shop', 'fp-performance-suite'),
            'elementor' => __('Elementor scripts - may break editor', 'fp-performance-suite'),
            'contact-form-7' => __('Contact Form 7 - may break forms', 'fp-performance-suite'),
        ];

        // Analizza gli script registrati
        foreach ($wp_scripts->registered as $handle => $script) {
            foreach ($criticalJsPatterns as $pattern => $reason) {
                if (stripos($handle, $pattern) !== false || (isset($script->src) && stripos($script->src, $pattern) !== false)) {
                    $exclude['plugin_specific'][] = [
                        'handle' => $handle,
                        'src' => $script->src ?? '',
                        'reason' => $reason,
                        'confidence' => 0.9,
                    ];
                    break;
                }
            }
        }

        // Rileva script con molte dipendenze (probabilmente critici)
        foreach ($wp_scripts->registered as $handle => $script) {
            if (!empty($script->deps) && count($script->deps) >= 2) {
                // Controlla se ha jQuery come dipendenza
                if (in_array('jquery', $script->deps, true) || in_array('jquery-core', $script->deps, true)) {
                    $exclude['core_dependencies'][] = [
                        'handle' => $handle,
                        'src' => $script->src ?? '',
                        'reason' => sprintf(__('Depends on %d other scripts including jQuery', 'fp-performance-suite'), count($script->deps)),
                        'confidence' => 0.75,
                        'dependencies' => $script->deps,
                    ];
                }
            }
        }

        // Rileva script con codice inline localizzato
        foreach ($wp_scripts->registered as $handle => $script) {
            if (!empty($script->extra) && (isset($script->extra['data']) || isset($script->extra['before']) || isset($script->extra['after']))) {
                $exclude['inline_dependent'][] = [
                    'handle' => $handle,
                    'src' => $script->src ?? '',
                    'reason' => __('Has inline code dependencies - may break if deferred', 'fp-performance-suite'),
                    'confidence' => 0.8,
                ];
            }
        }

        return $exclude;
    }

    /**
     * Analizza una pagina e suggerisce esclusioni
     */
    public function analyzePage(string $url): array
    {
        $suggestions = [
            'should_exclude_cache' => false,
            'should_exclude_minify' => false,
            'reason' => '',
            'confidence' => 0,
        ];

        // Analisi URL
        $urlAnalysis = $this->analyzeUrl($url);
        
        // Analisi contenuto (se possibile)
        $contentAnalysis = $this->analyzePageContent($url);

        // Combina analisi
        $suggestions = $this->combineAnalysis($urlAnalysis, $contentAnalysis);

        return $suggestions;
    }

    /**
     * Rileva URL sensibili standard
     */
    private function detectStandardSensitiveUrls(): array
    {
        $siteUrl = get_site_url();
        $detected = [];

        foreach (self::SENSITIVE_URL_PATTERNS as $pattern) {
            // Controlla se l'URL esiste nel sito
            if ($this->urlExists($siteUrl . $pattern)) {
                $detected[] = [
                    'url' => $pattern,
                    'reason' => $this->getReasonForPattern($pattern),
                    'confidence' => 0.9,
                ];
            }
        }

        return $detected;
    }

    /**
     * Rileva URL basandosi sui plugin attivi
     */
    private function detectPluginBasedUrls(): array
    {
        $detected = [];
        $activePlugins = get_option('active_plugins', []);

        foreach (self::PLUGIN_PATTERNS as $pluginKey => $patterns) {
            // Controlla se il plugin è attivo
            $isActive = false;
            foreach ($activePlugins as $plugin) {
                if (strpos($plugin, $pluginKey) !== false) {
                    $isActive = true;
                    break;
                }
            }

            if ($isActive && isset($patterns['urls'])) {
                foreach ($patterns['urls'] as $url) {
                    $detected[] = [
                        'url' => $url,
                        'reason' => sprintf(__('Required by %s plugin', 'fp-performance-suite'), $pluginKey),
                        'plugin' => $pluginKey,
                        'confidence' => 0.95,
                    ];
                }
            }
        }

        return $detected;
    }

    /**
     * Rileva da comportamento utente (errori, pagine lente)
     */
    private function detectFromBehavior(): array
    {
        global $wpdb;
        
        $detected = [];
        $table = $wpdb->prefix . 'fp_ps_performance_history';

        // Controlla se la tabella esiste
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            return $detected;
        }

        // Trova pagine con errori frequenti
        $errorPages = $wpdb->get_results(
            "SELECT url, COUNT(*) as error_count
             FROM {$table}
             WHERE has_error = 1
             AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY url
             HAVING error_count > 3
             ORDER BY error_count DESC
             LIMIT 10",
            ARRAY_A
        );

        foreach ($errorPages as $page) {
            $detected[] = [
                'url' => $page['url'],
                'reason' => sprintf(__('Frequent errors detected (%d in 7 days)', 'fp-performance-suite'), $page['error_count']),
                'confidence' => 0.8,
                'type' => 'error_based',
            ];
        }

        return $detected;
    }

    /**
     * Rileva script critici dei plugin
     */
    private function detectPluginCriticalScripts(): array
    {
        $critical = [];
        $activePlugins = get_option('active_plugins', []);

        foreach (self::PLUGIN_PATTERNS as $pluginKey => $patterns) {
            $isActive = false;
            foreach ($activePlugins as $plugin) {
                if (strpos($plugin, $pluginKey) !== false) {
                    $isActive = true;
                    break;
                }
            }

            if ($isActive && isset($patterns['scripts'])) {
                foreach ($patterns['scripts'] as $scriptPattern) {
                    $critical[] = [
                        'pattern' => $scriptPattern,
                        'plugin' => $pluginKey,
                        'reason' => sprintf(__('Critical for %s functionality', 'fp-performance-suite'), $pluginKey),
                    ];
                }
            }
        }

        return $critical;
    }

    /**
     * Analizza dipendenze degli script
     */
    private function analyzeDependencies(): array
    {
        global $wp_scripts;
        
        if (!isset($wp_scripts) || !is_object($wp_scripts)) {
            return [];
        }

        $critical = [];
        
        // Script con molte dipendenze sono probabilmente critici
        foreach ($wp_scripts->registered as $handle => $script) {
            if (!empty($script->deps) && count($script->deps) >= 3) {
                $critical[] = [
                    'handle' => $handle,
                    'reason' => sprintf(__('Has %d dependencies - likely critical', 'fp-performance-suite'), count($script->deps)),
                    'dependencies' => $script->deps,
                ];
            }
        }

        return $critical;
    }

    /**
     * Analizza URL
     */
    private function analyzeUrl(string $url): array
    {
        $analysis = [
            'is_sensitive' => false,
            'matched_patterns' => [],
            'confidence' => 0,
        ];

        foreach (self::SENSITIVE_URL_PATTERNS as $pattern) {
            if (strpos($url, $pattern) !== false) {
                $analysis['is_sensitive'] = true;
                $analysis['matched_patterns'][] = $pattern;
                $analysis['confidence'] = 0.9;
                break;
            }
        }

        return $analysis;
    }

    /**
     * Analizza contenuto pagina
     */
    private function analyzePageContent(string $url): array
    {
        $analysis = [
            'has_forms' => false,
            'has_payment' => false,
            'has_user_data' => false,
            'confidence' => 0,
        ];

        // Prova a fare una richiesta alla pagina
        $response = wp_remote_get($url, ['timeout' => 5, 'sslverify' => false]);
        
        if (is_wp_error($response)) {
            return $analysis;
        }

        $body = wp_remote_retrieve_body($response);

        // Cerca pattern nel contenuto
        if (preg_match('/<form/i', $body)) {
            $analysis['has_forms'] = true;
            $analysis['confidence'] += 0.3;
        }

        if (preg_match('/payment|checkout|cart/i', $body)) {
            $analysis['has_payment'] = true;
            $analysis['confidence'] += 0.4;
        }

        if (preg_match('/user|account|profile|login/i', $body)) {
            $analysis['has_user_data'] = true;
            $analysis['confidence'] += 0.3;
        }

        return $analysis;
    }

    /**
     * Combina analisi
     */
    private function combineAnalysis(array $urlAnalysis, array $contentAnalysis): array
    {
        $suggestions = [
            'should_exclude_cache' => false,
            'should_exclude_minify' => false,
            'reason' => '',
            'confidence' => 0,
        ];

        // Se URL è sensibile, escludi dalla cache
        if ($urlAnalysis['is_sensitive']) {
            $suggestions['should_exclude_cache'] = true;
            $suggestions['reason'] = __('Sensitive URL pattern detected', 'fp-performance-suite');
            $suggestions['confidence'] = $urlAnalysis['confidence'];
        }

        // Se ha payment o user data, escludi
        if ($contentAnalysis['has_payment'] || $contentAnalysis['has_user_data']) {
            $suggestions['should_exclude_cache'] = true;
            $suggestions['should_exclude_minify'] = true;
            $suggestions['reason'] = __('Contains sensitive user/payment data', 'fp-performance-suite');
            $suggestions['confidence'] = max($suggestions['confidence'], $contentAnalysis['confidence']);
        }

        return $suggestions;
    }

    /**
     * Controlla se URL esiste
     */
    private function urlExists(string $url): bool
    {
        $response = wp_remote_head($url, ['timeout' => 3, 'sslverify' => false]);
        
        if (is_wp_error($response)) {
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        return $code >= 200 && $code < 400;
    }

    /**
     * Ottieni ragione per pattern
     */
    private function getReasonForPattern(string $pattern): string
    {
        $reasons = [
            '/cart' => __('Shopping cart - dynamic content', 'fp-performance-suite'),
            '/checkout' => __('Checkout page - contains sensitive data', 'fp-performance-suite'),
            '/account' => __('User account area - personalized content', 'fp-performance-suite'),
            '/login' => __('Login page - CSRF tokens and nonces', 'fp-performance-suite'),
            '/search' => __('Search results - dynamic content', 'fp-performance-suite'),
            '/ajax' => __('AJAX endpoint - always dynamic', 'fp-performance-suite'),
        ];

        foreach ($reasons as $key => $reason) {
            if (strpos($pattern, $key) !== false) {
                return $reason;
            }
        }

        return __('Potentially sensitive URL', 'fp-performance-suite');
    }

    /**
     * Applica automaticamente esclusioni suggerite
     */
    public function autoApplyExclusions(bool $dryRun = true): array
    {
        $results = [
            'applied' => 0,
            'skipped' => 0,
            'exclusions' => [],
        ];

        $detected = $this->detectSensitiveUrls();
        
        foreach ($detected as $category => $items) {
            foreach ($items as $item) {
                // Applica solo se confidence >= 0.8
                if ($item['confidence'] >= 0.8) {
                    if (!$dryRun) {
                        $this->addExclusion($item['url'], [
                            'type' => 'automatic',
                            'reason' => $item['reason'],
                            'confidence' => $item['confidence'],
                            'plugin' => $item['plugin'] ?? '',
                        ]);
                    }
                    $results['applied']++;
                    $results['exclusions'][] = $item;
                } else {
                    $results['skipped']++;
                }
            }
        }

        return $results;
    }

    /**
     * Aggiungi esclusione con metadata
     */
    private function addExclusion(string $url, array $metadata = []): void
    {
        // Salva l'esclusione con metadata separato
        $trackedExclusions = get_option('fp_ps_tracked_exclusions', []);
        
        $exclusionId = md5($url . time());
        
        $trackedExclusions[$exclusionId] = [
            'id' => $exclusionId,
            'url' => $url,
            'type' => $metadata['type'] ?? 'automatic',
            'reason' => $metadata['reason'] ?? '',
            'confidence' => $metadata['confidence'] ?? 0.8,
            'plugin' => $metadata['plugin'] ?? '',
            'applied_at' => time(),
        ];
        
        update_option('fp_ps_tracked_exclusions', $trackedExclusions);
        
        // Aggiungi anche alla cache page (backward compatibility)
        $settings = get_option('fp_ps_page_cache', []);
        $currentExclusions = $settings['exclude_urls'] ?? '';
        
        $exclusionsList = array_filter(explode("\n", $currentExclusions));
        
        if (!in_array($url, $exclusionsList, true)) {
            $exclusionsList[] = $url;
            $settings['exclude_urls'] = implode("\n", $exclusionsList);
            update_option('fp_ps_page_cache', $settings);
        }
    }
    
    /**
     * Ottieni tutte le esclusioni applicate con metadata
     */
    public function getAppliedExclusions(): array
    {
        $trackedExclusions = get_option('fp_ps_tracked_exclusions', []);
        
        // Ordina per data di applicazione (più recenti prima)
        usort($trackedExclusions, function($a, $b) {
            return $b['applied_at'] - $a['applied_at'];
        });
        
        return $trackedExclusions;
    }
    
    /**
     * Rimuovi una esclusione
     */
    public function removeExclusion(string $exclusionId): bool
    {
        $trackedExclusions = get_option('fp_ps_tracked_exclusions', []);
        
        if (!isset($trackedExclusions[$exclusionId])) {
            return false;
        }
        
        $url = $trackedExclusions[$exclusionId]['url'];
        
        // Rimuovi dai tracked
        unset($trackedExclusions[$exclusionId]);
        update_option('fp_ps_tracked_exclusions', $trackedExclusions);
        
        // Rimuovi anche dalla cache page
        $settings = get_option('fp_ps_page_cache', []);
        $currentExclusions = $settings['exclude_urls'] ?? '';
        
        $exclusionsList = array_filter(explode("\n", $currentExclusions));
        $exclusionsList = array_filter($exclusionsList, fn($item) => $item !== $url);
        
        $settings['exclude_urls'] = implode("\n", $exclusionsList);
        update_option('fp_ps_page_cache', $settings);
        
        return true;
    }
    
    /**
     * Aggiungi esclusione manuale
     */
    public function addManualExclusion(string $url, string $reason = ''): void
    {
        $this->addExclusion($url, [
            'type' => 'manual',
            'reason' => $reason ?: __('Manual exclusion', 'fp-performance-suite'),
            'confidence' => 1.0,
        ]);
    }
}
