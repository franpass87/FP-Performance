<?php

namespace FP\PerfSuite\Services\Security;

use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Logger;

/**
 * Gestisce le regole di sicurezza e ottimizzazione nel file .htaccess
 * 
 * Implementa:
 * - Redirect canonico HTTPS + WWW unificato
 * - Security headers (X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, HSTS)
 * - Cache ottimizzata (HTML, font, immagini, SVG)
 * - CORS per font/SVG
 * - Protezione file sensibili
 * - Opzione anti-hotlink
 * - Opzione disabilitazione XML-RPC
 * 
 * NOTA: La compressione (Brotli/Gzip) è gestita dal servizio CompressionManager separato
 * 
 * @since 1.3.4
 */
class HtaccessSecurity
{
    private const OPTION = 'fp_ps_htaccess_security';
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

        if ($this->env->isApache() && $this->htaccess->isSupported()) {
            $this->applyRules($settings);
        }
    }

    /**
     * Ottiene le impostazioni correnti
     * 
     * @return array{
     *     enabled: bool,
     *     canonical_redirect: array,
     *     security_headers: array,
     *     cache_rules: array,
     *     cors: array,
     *     file_protection: array,
     *     xmlrpc_disabled: bool,
     *     hotlink_protection: array
     * }
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'canonical_redirect' => [
                'enabled' => false,
                'force_https' => true,
                'force_www' => true,
                'domain' => $this->getDomain(),
            ],
            'security_headers' => [
                'enabled' => false,
                'hsts' => true,
                'hsts_max_age' => 31536000,
                'hsts_subdomains' => true,
                'hsts_preload' => false, // Disabilitato di default per sicurezza
                'x_content_type_options' => true,
                'x_frame_options' => 'SAMEORIGIN',
                'referrer_policy' => 'strict-origin-when-cross-origin',
                'permissions_policy' => 'camera=(), microphone=(), geolocation=()',
            ],
            'cache_rules' => [
                'enabled' => false,
                'html_cache' => false, // HTML no-cache per sicurezza
                'fonts_cache' => true,
                'fonts_max_age' => 31536000, // 1 anno
                'images_max_age' => 31536000, // 1 anno
                'css_js_max_age' => 2592000, // 1 mese
            ],
            'cors' => [
                'enabled' => false,
                'fonts_origin' => '*',
                'svg_origin' => '*',
            ],
            'file_protection' => [
                'enabled' => false,
                'protect_hidden_files' => true,
                'protect_wp_config' => true,
            ],
            'xmlrpc_disabled' => false,
            'hotlink_protection' => [
                'enabled' => false,
                'allowed_domains' => [],
                'allow_google' => true,
            ],
        ];

        $options = get_option(self::OPTION, []);
        return wp_parse_args($options, $defaults);
    }

    /**
     * Aggiorna le impostazioni
     */
    public function update(array $settings): void
    {
        $current = $this->settings();
        
        // Preserva le cache_rules esistenti se non vengono fornite nel form
        if (!isset($settings['cache_rules']) && isset($current['cache_rules'])) {
            $settings['cache_rules'] = $current['cache_rules'];
        }
        
        $new = wp_parse_args($settings, $current);

        update_option(self::OPTION, $new);

        if (!$new['enabled']) {
            $this->htaccess->removeSection('FP-Performance-Security');
        } else {
            $this->applyRules($new);
        }

        Logger::info('Htaccess security settings updated', ['enabled' => $new['enabled']]);
    }

    /**
     * Applica tutte le regole al file .htaccess
     */
    private function applyRules(array $settings): void
    {
        $rules = [];

        // 1. Redirect canonico HTTPS + WWW
        if (!empty($settings['canonical_redirect']['enabled'])) {
            $rules[] = $this->buildCanonicalRedirect($settings['canonical_redirect']);
        }

        // 2. Security Headers
        if (!empty($settings['security_headers']['enabled'])) {
            $rules[] = $this->buildSecurityHeaders($settings['security_headers']);
        }

        // 3. Cache Rules
        if (!empty($settings['cache_rules']['enabled'])) {
            $rules[] = $this->buildCacheRules($settings['cache_rules']);
        }

        // 4. Compression: RIMOSSA - Ora gestita da CompressionManager per evitare conflitti
        
        // 5. CORS Headers
        if (!empty($settings['cors']['enabled'])) {
            $rules[] = $this->buildCorsRules($settings['cors']);
        }

        // 6. File Protection
        if (!empty($settings['file_protection']['enabled'])) {
            $rules[] = $this->buildFileProtection($settings['file_protection']);
        }

        // 7. XML-RPC Disabilitato
        if (!empty($settings['xmlrpc_disabled'])) {
            $rules[] = $this->buildXmlRpcProtection();
        }

        // 8. Hotlink Protection
        if (!empty($settings['hotlink_protection']['enabled'])) {
            $rules[] = $this->buildHotlinkProtection($settings['hotlink_protection']);
        }

        $combinedRules = implode("\n\n", array_filter($rules));
        $this->safeHtaccessWrite($combinedRules);
    }

    /**
     * Scrive regole .htaccess in modo sicuro con file lock
     * 
     * @param string $rules Regole da scrivere
     * @return bool True se scrittura riuscita
     */
    private function safeHtaccessWrite(string $rules): bool
    {
        $lockFile = ABSPATH . '.htaccess.lock';
        $lock = fopen($lockFile, 'c+');
        
        if (!$lock) {
            Logger::error('Failed to create .htaccess lock file', ['file' => $lockFile]);
            return false;
        }
        
        // Acquire exclusive lock (non-blocking)
        if (!flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            Logger::debug('.htaccess file locked by another process');
            return false; // Another process is writing
        }
        
        try {
            // Write .htaccess rules safely
            $result = $this->htaccess->injectRules('FP-Performance-Security', $rules);
            
            if (!$result) {
                Logger::error('Failed to write .htaccess rules');
                return false;
            }
            
            Logger::debug('.htaccess rules written safely');
            return true;
        } finally {
            // Always release lock
            flock($lock, LOCK_UN);
            fclose($lock);
            @unlink($lockFile);
        }
    }

    /**
     * Costruisce il redirect canonico HTTPS + WWW unificato
     */
    private function buildCanonicalRedirect(array $config): string
    {
        $domain = $config['domain'] ?? $this->getDomain();
        $forceHttps = !empty($config['force_https']);
        $forceWww = !empty($config['force_www']);

        if (!$forceHttps && !$forceWww) {
            return '';
        }

        $targetDomain = $forceWww ? 'www.' . $domain : $domain;
        $targetScheme = $forceHttps ? 'https' : 'http';

        $rules = "# === Redirect Canonico Unificato ===\n";
        $rules .= "# Forza " . ($forceHttps ? "HTTPS" : "HTTP");
        if ($forceWww || !$forceWww) {
            $rules .= " + " . ($forceWww ? "WWW" : "non-WWW");
        }
        $rules .= " in un solo redirect 301\n";
        $rules .= "RewriteEngine On\n";

        if ($forceHttps && $forceWww) {
            // Forza HTTPS + WWW
            $rules .= "RewriteCond %{HTTPS} !=on [OR]\n";
            $rules .= "RewriteCond %{HTTP_HOST} !^www\\." . preg_quote($domain, '/') . "$ [NC]\n";
            $rules .= "RewriteRule ^ https://www." . $domain . "%{REQUEST_URI} [L,R=301]";
        } elseif ($forceHttps && !$forceWww) {
            // Forza HTTPS + non-WWW
            $rules .= "RewriteCond %{HTTPS} !=on [OR]\n";
            $rules .= "RewriteCond %{HTTP_HOST} ^www\\. [NC]\n";
            $rules .= "RewriteRule ^ https://" . $domain . "%{REQUEST_URI} [L,R=301]";
        } elseif (!$forceHttps && $forceWww) {
            // Forza WWW (senza HTTPS)
            $rules .= "RewriteCond %{HTTP_HOST} !^www\\." . preg_quote($domain, '/') . "$ [NC]\n";
            $rules .= "RewriteRule ^ http://www." . $domain . "%{REQUEST_URI} [L,R=301]";
        }

        return $rules;
    }

    /**
     * Costruisce gli security headers
     */
    private function buildSecurityHeaders(array $config): string
    {
        $rules = "# === Security Headers ===\n";
        $rules .= "<IfModule mod_headers.c>\n";

        // HSTS
        if (!empty($config['hsts'])) {
            $hstsValue = "max-age=" . (int)($config['hsts_max_age'] ?? 31536000);
            if (!empty($config['hsts_subdomains'])) {
                $hstsValue .= "; includeSubDomains";
            }
            if (!empty($config['hsts_preload'])) {
                $hstsValue .= "; preload";
            }
            $rules .= "  Header always set Strict-Transport-Security \"{$hstsValue}\"\n";
        }

        // X-Content-Type-Options
        if (!empty($config['x_content_type_options'])) {
            $rules .= "  Header always set X-Content-Type-Options \"nosniff\"\n";
        }

        // X-Frame-Options
        if (!empty($config['x_frame_options'])) {
            $value = strtoupper($config['x_frame_options']);
            if (in_array($value, ['DENY', 'SAMEORIGIN'], true)) {
                $rules .= "  Header always set X-Frame-Options \"{$value}\"\n";
            }
        }

        // Referrer-Policy
        if (!empty($config['referrer_policy'])) {
            $rules .= "  Header always set Referrer-Policy \"{$config['referrer_policy']}\"\n";
        }

        // Permissions-Policy
        if (!empty($config['permissions_policy'])) {
            $rules .= "  Header always set Permissions-Policy \"{$config['permissions_policy']}\"\n";
        }

        $rules .= "</IfModule>";

        return $rules;
    }

    /**
     * Costruisce le regole di cache ottimizzate
     */
    private function buildCacheRules(array $config): string
    {
        $rules = "# === Cache Ottimizzata ===\n";

        // HTML: cache breve o no-cache
        if (empty($config['html_cache'])) {
            $rules .= "# HTML: no-cache per contenuti dinamici\n";
            $rules .= "<IfModule mod_headers.c>\n";
            $rules .= "  <FilesMatch \"\\.(html|htm)$\">\n";
            $rules .= "    Header set Cache-Control \"private, max-age=0, no-cache, must-revalidate\"\n";
            $rules .= "  </FilesMatch>\n";
            $rules .= "</IfModule>\n\n";
        }

        // Font, immagini, CSS/JS: long-term caching
        $rules .= "<IfModule mod_expires.c>\n";
        $rules .= "  ExpiresActive On\n\n";

        if (!empty($config['fonts_cache'])) {
            $fontsAge = $config['fonts_max_age'] ?? 31536000;
            $rules .= "  # Font: cache a lungo termine\n";
            $rules .= "  ExpiresByType font/woff2 \"access plus {$fontsAge} seconds\"\n";
            $rules .= "  ExpiresByType font/woff \"access plus {$fontsAge} seconds\"\n";
            $rules .= "  ExpiresByType font/ttf \"access plus {$fontsAge} seconds\"\n";
            $rules .= "  ExpiresByType font/otf \"access plus {$fontsAge} seconds\"\n";
            $rules .= "  ExpiresByType application/font-woff2 \"access plus {$fontsAge} seconds\"\n";
            $rules .= "  ExpiresByType application/font-woff \"access plus {$fontsAge} seconds\"\n\n";
        }

        $imagesAge = $config['images_max_age'] ?? 31536000;
        $rules .= "  # Immagini: cache a lungo termine\n";
        $rules .= "  ExpiresByType image/webp \"access plus {$imagesAge} seconds\"\n";
        $rules .= "  ExpiresByType image/png \"access plus {$imagesAge} seconds\"\n";
        $rules .= "  ExpiresByType image/jpeg \"access plus {$imagesAge} seconds\"\n";
        $rules .= "  ExpiresByType image/jpg \"access plus {$imagesAge} seconds\"\n";
        $rules .= "  ExpiresByType image/gif \"access plus {$imagesAge} seconds\"\n";
        $rules .= "  ExpiresByType image/svg+xml \"access plus {$imagesAge} seconds\"\n\n";

        $cssJsAge = $config['css_js_max_age'] ?? 2592000;
        $rules .= "  # CSS/JS: cache media durata\n";
        $rules .= "  ExpiresByType text/css \"access plus {$cssJsAge} seconds\"\n";
        $rules .= "  ExpiresByType application/javascript \"access plus {$cssJsAge} seconds\"\n";
        $rules .= "  ExpiresByType text/javascript \"access plus {$cssJsAge} seconds\"\n";

        $rules .= "</IfModule>";

        // Aggiungi regole per risorse esterne se abilitato
        if (!empty($config['external_cache'])) {
            $rules .= "\n" . $this->buildExternalCacheRules($config);
        }

        return $rules;
    }

    /**
     * Costruisce le regole per risorse esterne
     */
    private function buildExternalCacheRules(array $config): string
    {
        $rules = "# === Cache Risorse Esterne ===\n";
        
        // Header per risorse esterne JavaScript
        $jsTtl = $config['external_js_ttl'] ?? 31536000;
        $rules .= "<IfModule mod_headers.c>\n";
        $rules .= "  # Header per JavaScript esterni\n";
        $rules .= "  <FilesMatch \"\\.(js|mjs)$\">\n";
        $rules .= "    Header set Cache-Control \"public, max-age={$jsTtl}, immutable\"\n";
        $rules .= "    Header set X-External-Cache \"enabled\"\n";
        $rules .= "  </FilesMatch>\n\n";
        
        // Header per CSS esterni
        $cssTtl = $config['external_css_ttl'] ?? 31536000;
        $rules .= "  # Header per CSS esterni\n";
        $rules .= "  <FilesMatch \"\\.css$\">\n";
        $rules .= "    Header set Cache-Control \"public, max-age={$cssTtl}, immutable\"\n";
        $rules .= "    Header set X-External-Cache \"enabled\"\n";
        $rules .= "  </FilesMatch>\n\n";
        
        // Header per font esterni
        $fontTtl = $config['external_font_ttl'] ?? 31536000;
        $rules .= "  # Header per font esterni\n";
        $rules .= "  <FilesMatch \"\\.(woff2?|ttf|otf|eot)$\">\n";
        $rules .= "    Header set Cache-Control \"public, max-age={$fontTtl}, immutable\"\n";
        $rules .= "    Header set Access-Control-Allow-Origin \"*\"\n";
        $rules .= "    Header set X-External-Cache \"enabled\"\n";
        $rules .= "  </FilesMatch>\n";
        
        $rules .= "</IfModule>\n\n";
        
        // Regole Expires per risorse esterne
        $rules .= "<IfModule mod_expires.c>\n";
        $rules .= "  # Expires per risorse esterne\n";
        $rules .= "  ExpiresByType application/javascript \"access plus {$jsTtl} seconds\"\n";
        $rules .= "  ExpiresByType text/javascript \"access plus {$jsTtl} seconds\"\n";
        $rules .= "  ExpiresByType text/css \"access plus {$cssTtl} seconds\"\n";
        $rules .= "  ExpiresByType font/woff2 \"access plus {$fontTtl} seconds\"\n";
        $rules .= "  ExpiresByType font/woff \"access plus {$fontTtl} seconds\"\n";
        $rules .= "  ExpiresByType font/ttf \"access plus {$fontTtl} seconds\"\n";
        $rules .= "  ExpiresByType font/otf \"access plus {$fontTtl} seconds\"\n";
        $rules .= "</IfModule>";
        
        return $rules;
    }

    /**
     * Costruisce le regole di compressione Brotli + Deflate
     * 
     * @deprecated Metodo deprecato - La compressione è ora gestita da CompressionManager
     */
    private function buildCompressionRules(array $config): string
    {
        // La compressione è ora gestita dalla pagina Compression dedicata
        // tramite il servizio CompressionManager per evitare conflitti
        return '';
    }

    /**
     * Costruisce le regole CORS per font e SVG
     */
    private function buildCorsRules(array $config): string
    {
        $fontsOrigin = $config['fonts_origin'] ?? '*';
        $svgOrigin = $config['svg_origin'] ?? '*';

        $rules = "# === CORS Headers ===\n";
        $rules .= "# Permette caricamento font/SVG da CDN o sottodomini\n";
        $rules .= "<IfModule mod_headers.c>\n";
        $rules .= "  <FilesMatch \"\\.(woff2?|ttf|otf|eot)$\">\n";
        $rules .= "    Header always set Access-Control-Allow-Origin \"{$fontsOrigin}\"\n";
        $rules .= "  </FilesMatch>\n";
        $rules .= "  <FilesMatch \"\\.svg$\">\n";
        $rules .= "    Header always set Access-Control-Allow-Origin \"{$svgOrigin}\"\n";
        $rules .= "  </FilesMatch>\n";
        $rules .= "</IfModule>";

        return $rules;
    }

    /**
     * Costruisce le regole di protezione file sensibili
     */
    private function buildFileProtection(array $config): string
    {
        $rules = "# === Protezione File Sensibili ===\n";

        // File nascosti (.env, .git, ecc.)
        if (!empty($config['protect_hidden_files'])) {
            $rules .= "# Blocca accesso a file nascosti\n";
            $rules .= "<FilesMatch \"^\\.\">\n";
            $rules .= "  Require all denied\n";
            $rules .= "</FilesMatch>\n\n";
        }

        // wp-config.php
        if (!empty($config['protect_wp_config'])) {
            $rules .= "# Protegge wp-config.php\n";
            $rules .= "<Files wp-config.php>\n";
            $rules .= "  Require all denied\n";
            $rules .= "</Files>";
        }

        return rtrim($rules);
    }

    /**
     * Costruisce la regola di protezione XML-RPC
     */
    private function buildXmlRpcProtection(): string
    {
        return "# === Protezione XML-RPC ===\n" .
               "# Disabilita XML-RPC se non usi Jetpack o app WP\n" .
               "<Files xmlrpc.php>\n" .
               "  Require all denied\n" .
               "</Files>";
    }

    /**
     * Costruisce le regole anti-hotlink
     */
    private function buildHotlinkProtection(array $config): string
    {
        $domain = $this->getDomain();
        $allowedDomains = $config['allowed_domains'] ?? [];
        $allowGoogle = !empty($config['allow_google']);

        $rules = "# === Anti-Hotlink Immagini ===\n";
        $rules .= "# Blocca l'uso delle immagini da altri siti\n";
        $rules .= "RewriteEngine On\n";
        $rules .= "RewriteCond %{HTTP_REFERER} !^$\n";
        $rules .= "RewriteCond %{HTTP_REFERER} !" . preg_quote($domain, '/') . " [NC]\n";

        if ($allowGoogle) {
            $rules .= "RewriteCond %{HTTP_REFERER} !google\\.[a-z\\.]+ [NC]\n";
        }

        foreach ($allowedDomains as $allowedDomain) {
            $rules .= "RewriteCond %{HTTP_REFERER} !" . preg_quote($allowedDomain, '/') . " [NC]\n";
        }

        $rules .= "RewriteRule \\.(jpe?g|png|gif|webp)$ - [F,NC]";

        return $rules;
    }

    /**
     * Ottiene il dominio corrente del sito
     */
    private function getDomain(): string
    {
        $siteUrl = get_site_url();
        $parsed = parse_url($siteUrl);
        $host = $parsed['host'] ?? '';

        // Rimuovi www. se presente
        return str_replace('www.', '', $host);
    }

    /**
     * Verifica lo stato delle regole applicate
     */
    public function status(): array
    {
        $settings = $this->settings();
        return [
            'enabled' => !empty($settings['enabled']),
            'section_applied' => $this->htaccess->hasSection('FP-Performance-Security'),
            'canonical_redirect' => $settings['canonical_redirect']['enabled'] ?? false,
            'security_headers' => $settings['security_headers']['enabled'] ?? false,
            'cache_rules' => $settings['cache_rules']['enabled'] ?? false,
            'compression' => [
                'brotli' => $settings['compression']['brotli_enabled'] ?? false,
                'deflate' => $settings['compression']['deflate_enabled'] ?? false,
            ],
            'cors' => $settings['cors']['enabled'] ?? false,
            'file_protection' => $settings['file_protection']['enabled'] ?? false,
            'xmlrpc_disabled' => $settings['xmlrpc_disabled'] ?? false,
            'hotlink_protection' => $settings['hotlink_protection']['enabled'] ?? false,
        ];
    }
}

