<?php

namespace FP\PerfSuite\Services\Cache;

use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Htaccess;

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

        add_action('send_headers', function () use ($settings) {
            foreach ($settings['headers'] as $header => $value) {
                if (!headers_sent()) {
                    header($header . ': ' . $value, true);
                }
            }
        });

        if (!empty($settings['htaccess']) && $this->env->isApache() && $this->htaccess->isSupported()) {
            $this->applyHtaccess($settings['htaccess']);
        }
    }

    /**
     * @return array{enabled:bool,headers:array<string,string>,htaccess:string}
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'headers' => [
                'Cache-Control' => 'public, max-age=31536000',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            ],
            'htaccess' => $this->defaultHtaccess(),
        ];

        $options = get_option(self::OPTION, []);
        $options['headers'] = isset($options['headers']) ? (array) $options['headers'] : [];
        return wp_parse_args($options, $defaults);
    }

    public function update(array $settings): void
    {
        $current = $this->settings();
        $new = [
            'enabled' => !empty($settings['enabled']),
            'headers' => array_map('sanitize_text_field', $settings['headers'] ?? $current['headers']),
            'htaccess' => isset($settings['htaccess']) ? sanitize_textarea_field($settings['htaccess']) : $current['htaccess'],
        ];

        update_option(self::OPTION, $new);

        if (!$new['enabled']) {
            $this->htaccess->removeSection('FP-Performance-Suite');
        }
    }

    public function applyHtaccess(string $rules): void
    {
        $this->htaccess->injectRules('FP-Performance-Suite', $rules);
    }

    public function status(): array
    {
        $settings = $this->settings();
        return [
            'enabled' => !empty($settings['enabled']),
            'headers' => $settings['headers'],
            'htaccess_applied' => !empty($settings['enabled']) && $this->env->isApache(),
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
}
