<?php

namespace FP\PerfSuite\Services\Security;

class HtaccessSecurity
{
    private $cache_rules;
    private $security_headers;
    
    public function __construct($cache_rules = true, $security_headers = true)
    {
        $this->cache_rules = $cache_rules;
        $this->security_headers = $security_headers;
    }
    
    public function init()
    {
        add_action('init', [$this, 'addSecurityHeaders']);
        add_action('wp_loaded', [$this, 'updateHtaccess']);
    }
    
    public function addSecurityHeaders()
    {
        if (!$this->security_headers) {
            return;
        }
        
        if (!headers_sent()) {
            // Security headers
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        }
    }
    
    public function updateHtaccess()
    {
        if (!$this->cache_rules) {
            return;
        }
        
        $htaccess_file = ABSPATH . '.htaccess';
        
        if (!file_exists($htaccess_file)) {
            return;
        }
        
        $htaccess_content = file_get_contents($htaccess_file);
        $new_rules = $this->getSecurityRules();
        
        // Check if rules already exist
        if (strpos($htaccess_content, '# FP Performance Security Rules') !== false) {
            return;
        }
        
        // Add security rules
        $htaccess_content .= "\n" . $new_rules;
        
        file_put_contents($htaccess_file, $htaccess_content);
    }
    
    private function getSecurityRules()
    {
        return '
# FP Performance Security Rules
<IfModule mod_headers.c>
    # Security Headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
    
    # Cache Headers
    <FilesMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">
        Header set Cache-Control "public, max-age=31536000"
    </FilesMatch>
    
    <FilesMatch "\.(html|htm)$">
        Header set Cache-Control "public, max-age=3600"
    </FilesMatch>
</IfModule>

# Prevent access to sensitive files
<FilesMatch "\.(htaccess|htpasswd|ini|log|sh|sql|tar|gz)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Prevent directory browsing
Options -Indexes

# Prevent access to wp-config.php
<Files wp-config.php>
    Order allow,deny
    Deny from all
</Files>
';
    }
    
    public function getSecurityMetrics()
    {
        return [
            'cache_rules' => $this->cache_rules,
            'security_headers' => $this->security_headers,
            'htaccess_writable' => is_writable(ABSPATH . '.htaccess')
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}