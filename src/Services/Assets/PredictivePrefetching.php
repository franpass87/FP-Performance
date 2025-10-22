<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\Logger;

/**
 * Predictive Prefetching
 * 
 * Precarica pagine basandosi sul comportamento utente
 *
 * @package FP\PerfSuite\Services\Assets
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PredictivePrefetching
{
    private const OPTION_KEY = 'fp_ps_predictive_prefetch';
    private static bool $registered = false;

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Evita registrazioni multiple
        if (self::$registered) {
            return;
        }
        
        $settings = $this->getSettings();

        if (empty($settings['enabled'])) {
            return;
        }

        add_action('wp_footer', [$this, 'injectPrefetchScript'], 999);

        self::$registered = true;
        
        Logger::debug('Predictive Prefetching registered');
    }

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'strategy' => 'hover', // hover, viewport, aggressive
            'hover_delay' => 100, // ms
            'prefetch_limit' => 5,
            'ignore_patterns' => ['/wp-admin/', '/cart/', '/checkout/'],
        ];

        $options = get_option(self::OPTION_KEY, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function updateSettings(array $settings): bool
    {
        $current = $this->getSettings();
        $updated = wp_parse_args($settings, $current);

        return update_option(self::OPTION_KEY, $updated);
    }

    /**
     * Inietta script prefetch
     */
    public function injectPrefetchScript(): void
    {
        if (is_admin()) {
            return;
        }

        $settings = $this->getSettings();
        ?>
        <script>
        (function() {
            const strategy = '<?php echo esc_js($settings['strategy']); ?>';
            const hoverDelay = <?php echo (int) $settings['hover_delay']; ?>;
            const prefetchLimit = <?php echo (int) $settings['prefetch_limit']; ?>;
            const ignorePatterns = <?php echo wp_json_encode($settings['ignore_patterns']); ?>;
            
            let prefetchedUrls = new Set();
            let hoverTimeout;

            const shouldIgnore = (url) => {
                return ignorePatterns.some(pattern => url.includes(pattern));
            };

            const prefetchUrl = (url) => {
                if (prefetchedUrls.has(url) || shouldIgnore(url)) {
                    return;
                }

                if (prefetchedUrls.size >= prefetchLimit) {
                    return;
                }

                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                link.as = 'document';
                
                document.head.appendChild(link);
                prefetchedUrls.add(url);
                
                console.log('Prefetched:', url);
            };

            if (strategy === 'hover') {
                document.addEventListener('mouseover', (e) => {
                    const link = e.target.closest('a');
                    if (!link || !link.href) return;

                    const url = new URL(link.href);
                    if (url.origin !== location.origin) return;

                    clearTimeout(hoverTimeout);
                    hoverTimeout = setTimeout(() => {
                        prefetchUrl(link.href);
                    }, hoverDelay);
                });

                document.addEventListener('mouseout', () => {
                    clearTimeout(hoverTimeout);
                });
            }

            else if (strategy === 'viewport') {
                if ('IntersectionObserver' in window) {
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const link = entry.target;
                                const url = new URL(link.href);
                                if (url.origin === location.origin) {
                                    prefetchUrl(link.href);
                                }
                                observer.unobserve(link);
                            }
                        });
                    });

                    document.querySelectorAll('a[href]').forEach(link => {
                        observer.observe(link);
                    });
                }
            }

            else if (strategy === 'aggressive') {
                document.querySelectorAll('a[href]').forEach(link => {
                    const url = new URL(link.href);
                    if (url.origin === location.origin && !shouldIgnore(link.href)) {
                        prefetchUrl(link.href);
                    }
                });
            }
        })();
        </script>
        <?php
    }

    /**
     * Status
     */
    public function status(): array
    {
        return [
            'enabled' => !empty($this->getSettings()['enabled']),
            'settings' => $this->getSettings(),
        ];
    }
}

