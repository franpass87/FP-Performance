<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Htaccess;

use function headers_list;
use function is_admin;
use function is_array;
use function is_string;
use function is_user_logged_in;
use function wp_cache_get_cookies_values;
use function wp_unslash;

class Headers
{
    private const OPTION = 'fp_ps_browser_cache';
    private Htaccess $htaccess;
    private Env $env;

    public function __construct(Htaccess $htaccess, Env $env)
    {
        $this->htaccess = $htaccess;
        $this->env = $env;
    }

    public function register(): void
    {
        $settings = $this->settings();
        if (empty($settings['enabled'])) {
            return;
        }

        if (!is_admin()) {
            add_action('send_headers', function (): void {
                if (
                    (function_exists('wp_doing_ajax') && wp_doing_ajax()) ||
                    (function_exists('rest_doing_request') && rest_doing_request()) || is_user_logged_in()
                ) {
                    return;
                }

                if ($this->hasPrivateCookies()) {
                    return;
                }

                $uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
                if (is_string($uri) && ($uri === '/wp-login.php' || strpos($uri, 'wp-login.php') !== false || strpos($uri, 'wp-signup.php') !== false)) {
                    return;
                }

                if (function_exists('headers_list')) {
                    foreach (headers_list() as $header) {
                        if (stripos($header, 'set-cookie:') === 0) {
                            return;
                        }
                    }
                }

                $settings = $this->settings();
                if (empty($settings['enabled'])) {
                    return;
                }

                // SICUREZZA BUG #25: Sanitizza header values per prevenire injection
                $headers = [
                    'Cache-Control' => $this->sanitizeHeaderValue($settings['headers']['Cache-Control']),
                    'Expires' => $this->formatExpiresHeader($settings['expires_ttl']),
                ];

                foreach ($headers as $header => $value) {
                    if (!headers_sent() && !empty($value)) {
                        header($header . ': ' . $value, true);
                    }
                }
            });
        }

        if (!empty($settings['htaccess']) && $this->env->isApache() && $this->htaccess->isSupported()) {
            $this->applyHtaccess($settings['htaccess']);
        }
    }

    /**
     * @return array{
     *     enabled:bool,
     *     headers:array<string,string>,
     *     expires_ttl:int,
     *     htaccess:string
     * }
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'cache_control' => 'public, max-age=31536000',
            'expires_ttl' => 31536000,
            'htaccess' => $this->defaultHtaccess(),
        ];

        $options = get_option(self::OPTION, []);
        $storedHeaders = $options['headers'] ?? [];
        if (!is_array($storedHeaders)) {
            $storedHeaders = is_string($storedHeaders) && $storedHeaders !== ''
                ? ['Cache-Control' => $storedHeaders]
                : [];
        }

        if ($storedHeaders === [] && isset($options['cache_control']) && is_string($options['cache_control'])) {
            $storedHeaders['Cache-Control'] = $options['cache_control'];
        }

        $cacheControl = (string) ($storedHeaders['Cache-Control'] ?? $defaults['cache_control']);
        $ttl = isset($options['expires_ttl']) ? (int) $options['expires_ttl'] : null;

        if ($ttl === null) {
            $legacy = $storedHeaders['Expires'] ?? null;
            if (is_numeric($legacy)) {
                $ttl = (int) $legacy;
            } elseif (is_string($legacy)) {
                $ttl = strtotime($legacy) - time();
            }
            if ($ttl === null || $ttl <= 0) {
                $ttl = $defaults['expires_ttl'];
            }
        }

        $htaccess = $this->normalizeHtaccess($options['htaccess'] ?? $defaults['htaccess']);

        return [
            'enabled' => !empty($options['enabled']),
            'headers' => [
                'Cache-Control' => $cacheControl,
                'Expires' => $this->formatExpiresHeader($ttl),
            ],
            'expires_ttl' => $ttl,
            'htaccess' => $htaccess,
        ];
    }

    public function update(array $settings): void
    {
        $current = $this->settings();
        $cacheControl = isset($settings['headers']['Cache-Control'])
            ? sanitize_text_field($settings['headers']['Cache-Control'])
            : $current['headers']['Cache-Control'];
        $ttl = isset($settings['expires_ttl']) ? max(0, (int) $settings['expires_ttl']) : $current['expires_ttl'];
        $htaccess = isset($settings['htaccess']) ? $this->normalizeHtaccess($settings['htaccess']) : $current['htaccess'];

        $new = [
            'enabled' => !empty($settings['enabled']),
            'headers' => ['Cache-Control' => $cacheControl],
            'expires_ttl' => $ttl,
            'htaccess' => $htaccess,
        ];

        update_option(self::OPTION, $new);

        if (!$new['enabled']) {
            $this->htaccess->removeSection('FP-Performance-Suite');
        }
    }

    public function applyHtaccess(string $rules): void
    {
        $this->htaccess->injectRules('FP-Performance-Suite', $this->normalizeHtaccess($rules));
    }

    public function status(): array
    {
        $settings = $this->settings();
        return [
            'enabled' => !empty($settings['enabled']),
            'headers' => $settings['headers'],
            'expires_ttl' => $settings['expires_ttl'],
            'htaccess_applied' => !empty($settings['enabled']) && $this->htaccess->hasSection('FP-Performance-Suite'),
        ];
    }

    private function defaultHtaccess(): string
    {
        return <<<HTACCESS
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

<IfModule mod_headers.c>
    <FilesMatch "\.(js|css|png|jpg|jpeg|gif|webp|svg)$">
        Header set Cache-Control "public, max-age=2592000"
    </FilesMatch>
</IfModule>
HTACCESS;
    }

    private function formatExpiresHeader(int $ttl): string
    {
        return gmdate('D, d M Y H:i:s', time() + max(0, $ttl)) . ' GMT';
    }

    private function normalizeHtaccess($rules): string
    {
        if (!is_string($rules)) {
            return '';
        }

        $rules = wp_unslash($rules);
        $rules = preg_replace('/<\?(php)?/i', '', $rules) ?? $rules;
        $rules = str_replace(["\r\n", "\r"], "\n", $rules);
        return trim($rules);
    }

    private function hasPrivateCookies(): bool
    {
        if (function_exists('wp_cache_get_cookies_values') && wp_cache_get_cookies_values() !== '') {
            return true;
        }

        foreach ($_COOKIE as $name => $value) {
            if (is_string($name) && strpos($name, 'comment_author_') === 0) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Sanitizza header value per prevenire header injection
     * 
     * @param string $value Header value da sanitizzare
     * @return string Header value sicuro
     */
    private function sanitizeHeaderValue(string $value): string
    {
        // SICUREZZA: Rimuovi newline per prevenire header injection
        $value = str_replace(["\r", "\n", "\0"], '', $value);
        
        // Rimuovi caratteri di controllo non ASCII
        $value = preg_replace('/[^\x20-\x7E]/', '', $value);
        
        // Trim spazi
        $value = trim($value);
        
        return $value;
    }
}
