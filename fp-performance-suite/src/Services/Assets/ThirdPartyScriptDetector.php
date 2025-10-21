<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Third Party Script Detector
 * 
 * Rileva automaticamente script di terze parti e suggerisce ottimizzazioni
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ThirdPartyScriptDetector
{
    private ThirdPartyScriptManager $manager;
    private array $detectedScripts = [];

    /**
     * Pattern noti per script terze parti
     */
    private array $knownPatterns = [
        'google-analytics' => [
            'patterns' => ['googletagmanager.com/gtag/', 'google-analytics.com/analytics.js'],
            'category' => 'analytics',
            'name' => 'Google Analytics',
        ],
        'gtm' => [
            'patterns' => ['googletagmanager.com/gtm.js'],
            'category' => 'analytics',
            'name' => 'Google Tag Manager',
        ],
        'facebook-pixel' => [
            'patterns' => ['connect.facebook.net/en_US/fbevents.js'],
            'category' => 'marketing',
            'name' => 'Facebook Pixel',
        ],
        'google-ads' => [
            'patterns' => ['googleadservices.com/pagead/conversion'],
            'category' => 'marketing',
            'name' => 'Google Ads',
        ],
        'hotjar' => [
            'patterns' => ['static.hotjar.com/c/hotjar-'],
            'category' => 'analytics',
            'name' => 'Hotjar',
        ],
        'intercom' => [
            'patterns' => ['widget.intercom.io/widget/'],
            'category' => 'chat',
            'name' => 'Intercom',
        ],
        'drift' => [
            'patterns' => ['js.driftt.com/include/'],
            'category' => 'chat',
            'name' => 'Drift',
        ],
        'tawk-to' => [
            'patterns' => ['embed.tawk.to/'],
            'category' => 'chat',
            'name' => 'Tawk.to',
        ],
        'stripe' => [
            'patterns' => ['js.stripe.com/v'],
            'category' => 'payment',
            'name' => 'Stripe',
        ],
        'paypal' => [
            'patterns' => ['paypal.com/sdk/js'],
            'category' => 'payment',
            'name' => 'PayPal',
        ],
        'recaptcha' => [
            'patterns' => ['google.com/recaptcha/'],
            'category' => 'security',
            'name' => 'reCAPTCHA',
        ],
        'youtube' => [
            'patterns' => ['youtube.com/iframe_api', 'youtube.com/embed/'],
            'category' => 'media',
            'name' => 'YouTube',
        ],
        'vimeo' => [
            'patterns' => ['player.vimeo.com/api/player.js'],
            'category' => 'media',
            'name' => 'Vimeo',
        ],
        'twitter' => [
            'patterns' => ['platform.twitter.com/widgets.js'],
            'category' => 'social',
            'name' => 'Twitter',
        ],
        'linkedin' => [
            'patterns' => ['platform.linkedin.com/in.js'],
            'category' => 'social',
            'name' => 'LinkedIn',
        ],
        'cloudflare' => [
            'patterns' => ['cdnjs.cloudflare.com'],
            'category' => 'cdn',
            'name' => 'Cloudflare CDN',
        ],
        'jsdelivr' => [
            'patterns' => ['cdn.jsdelivr.net'],
            'category' => 'cdn',
            'name' => 'jsDelivr CDN',
        ],
        'font-awesome' => [
            'patterns' => ['use.fontawesome.com', 'kit.fontawesome.com'],
            'category' => 'fonts',
            'name' => 'Font Awesome',
        ],
        'google-fonts' => [
            'patterns' => ['fonts.googleapis.com'],
            'category' => 'fonts',
            'name' => 'Google Fonts',
        ],
    ];

    public function __construct(ThirdPartyScriptManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Scan automatico
        add_action('wp_footer', [$this, 'detectScripts'], PHP_INT_MAX);
        add_action('admin_footer', [$this, 'detectScripts'], PHP_INT_MAX);

        Logger::debug('Third Party Script Detector registered');
    }

    /**
     * Rileva script terze parti
     */
    public function detectScripts(): void
    {
        global $wp_scripts;

        if (!$wp_scripts instanceof \WP_Scripts) {
            return;
        }

        foreach ($wp_scripts->queue as $handle) {
            if (!isset($wp_scripts->registered[$handle])) {
                continue;
            }

            $src = $wp_scripts->registered[$handle]->src;
            
            // Salta script interni
            if ($this->isInternalScript($src)) {
                continue;
            }

            // Identifica script
            $identified = $this->identifyScript($src);
            
            if ($identified) {
                $this->detectedScripts[$handle] = $identified;
                
                // Registra automaticamente con il manager
                $this->manager->registerService($identified['id'], [
                    'name' => $identified['name'],
                    'category' => $identified['category'],
                    'strategy' => $this->suggestStrategy($identified['category']),
                    'detected_automatically' => true,
                ]);
            }
        }

        // Log scripts rilevati
        if (!empty($this->detectedScripts)) {
            Logger::info('Third party scripts detected', [
                'count' => count($this->detectedScripts),
                'scripts' => array_keys($this->detectedScripts),
            ]);
        }
    }

    /**
     * Verifica se script è interno
     */
    private function isInternalScript(string $src): bool
    {
        $homeUrl = home_url();
        $wpIncludes = includes_url();
        $wpContent = content_url();

        // URL relativo = interno
        if (strpos($src, '//') === false) {
            return true;
        }

        // Controlla se appartiene al sito
        return strpos($src, $homeUrl) !== false ||
               strpos($src, $wpIncludes) !== false ||
               strpos($src, $wpContent) !== false;
    }

    /**
     * Identifica script da pattern
     */
    private function identifyScript(string $src): ?array
    {
        foreach ($this->knownPatterns as $id => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (strpos($src, $pattern) !== false) {
                    return [
                        'id' => $id,
                        'name' => $config['name'],
                        'category' => $config['category'],
                        'src' => $src,
                    ];
                }
            }
        }

        // Script esterno non identificato
        if (strpos($src, '//') !== false && !$this->isInternalScript($src)) {
            return [
                'id' => 'unknown-' . md5($src),
                'name' => 'Unknown Third Party',
                'category' => 'other',
                'src' => $src,
            ];
        }

        return null;
    }

    /**
     * Suggerisce strategia di caricamento
     */
    private function suggestStrategy(string $category): string
    {
        $strategies = [
            'analytics' => 'on-interaction',
            'marketing' => 'on-interaction',
            'chat' => 'on-interaction',
            'social' => 'lazy',
            'media' => 'lazy',
            'payment' => 'critical',
            'security' => 'critical',
            'fonts' => 'preload',
            'cdn' => 'default',
            'other' => 'lazy',
        ];

        return $strategies[$category] ?? 'lazy';
    }

    /**
     * Scansiona HTML per script aggiuntivi
     */
    public function scanHtml(string $html): array
    {
        $scripts = [];

        // Cerca tag script
        preg_match_all('/<script[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

        foreach ($matches[1] as $src) {
            if ($this->isInternalScript($src)) {
                continue;
            }

            $identified = $this->identifyScript($src);
            if ($identified) {
                $scripts[] = $identified;
            }
        }

        // Cerca script inline che caricano terze parti
        preg_match_all('/<script[^>]*>(.*?)<\/script>/is', $html, $inlineMatches);

        foreach ($inlineMatches[1] as $inlineScript) {
            // Cerca URL esterni in script inline
            preg_match_all('/(https?:)?\/\/[^\s\'"<>]+/i', $inlineScript, $urlMatches);
            
            foreach ($urlMatches[0] as $url) {
                $identified = $this->identifyScript($url);
                if ($identified) {
                    $scripts[] = $identified;
                }
            }
        }

        return array_unique($scripts, SORT_REGULAR);
    }

    /**
     * Analizza pagina
     */
    public function analyzePage(string $url): array
    {
        $response = wp_remote_get($url, ['timeout' => 30]);

        if (is_wp_error($response)) {
            return [
                'error' => $response->get_error_message(),
            ];
        }

        $html = wp_remote_retrieve_body($response);
        $scripts = $this->scanHtml($html);

        // Analizza impatto
        $analysis = [
            'url' => $url,
            'scripts' => $scripts,
            'total_count' => count($scripts),
            'by_category' => $this->groupByCategory($scripts),
            'recommendations' => $this->generateRecommendations($scripts),
        ];

        return $analysis;
    }

    /**
     * Raggruppa per categoria
     */
    private function groupByCategory(array $scripts): array
    {
        $byCategory = [];

        foreach ($scripts as $script) {
            $category = $script['category'];
            
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = [];
            }

            $byCategory[$category][] = $script;
        }

        return $byCategory;
    }

    /**
     * Genera raccomandazioni
     */
    private function generateRecommendations(array $scripts): array
    {
        $recommendations = [];

        $byCategory = $this->groupByCategory($scripts);

        foreach ($byCategory as $category => $categoryScripts) {
            $count = count($categoryScripts);

            switch ($category) {
                case 'analytics':
                    if ($count > 2) {
                        $recommendations[] = [
                            'type' => 'warning',
                            'category' => $category,
                            'message' => sprintf('Rilevati %d script analytics. Considera di consolidare con Google Tag Manager.', $count),
                            'priority' => 'high',
                        ];
                    } else {
                        $recommendations[] = [
                            'type' => 'info',
                            'category' => $category,
                            'message' => 'Carica script analytics on-interaction per migliorare FCP.',
                            'priority' => 'medium',
                        ];
                    }
                    break;

                case 'chat':
                    $recommendations[] = [
                        'type' => 'info',
                        'category' => $category,
                        'message' => 'Widget chat dovrebbero essere caricati on-interaction.',
                        'priority' => 'medium',
                    ];
                    break;

                case 'social':
                    $recommendations[] = [
                        'type' => 'info',
                        'category' => $category,
                        'message' => 'Widget social possono essere caricati lazy.',
                        'priority' => 'low',
                    ];
                    break;

                case 'fonts':
                    $recommendations[] = [
                        'type' => 'info',
                        'category' => $category,
                        'message' => 'Usa font-display: swap e preload per font critici.',
                        'priority' => 'high',
                    ];
                    break;
            }
        }

        return $recommendations;
    }

    /**
     * Ottiene script rilevati
     */
    public function getDetectedScripts(): array
    {
        return $this->detectedScripts;
    }

    /**
     * Ottiene statistiche
     */
    public function getStats(): array
    {
        $detected = $this->getDetectedScripts();
        $byCategory = $this->groupByCategory(array_values($detected));

        return [
            'total_detected' => count($detected),
            'by_category' => array_map('count', $byCategory),
            'scripts' => $detected,
        ];
    }

    /**
     * Report completo
     */
    public function getReport(): array
    {
        $stats = $this->getStats();
        $scripts = array_values($this->getDetectedScripts());

        return [
            'stats' => $stats,
            'scripts' => $scripts,
            'recommendations' => $this->generateRecommendations($scripts),
        ];
    }
}

