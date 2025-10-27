<?php

namespace FP\PerfSuite\Services\Security;

use FP\PerfSuite\Utils\Logger;

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
        // Solo nel frontend
        if (!is_admin()) {
            add_action('init', [$this, 'addSecurityHeaders']);
            add_action('wp_loaded', [$this, 'updateHtaccess']);
        }
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
        
        // FIX TEMPORANEO: Disabilita temporaneamente per evitare crash
        Logger::warning('updateHtaccess() temporaneamente disabilitato per evitare crash');
        return;
        
        $htaccess_file = ABSPATH . '.htaccess';
        
        // PROTEZIONE SHARED HOSTING: Verifica permessi e backup
        
        // 1. Verifica esistenza file
        if (!file_exists($htaccess_file)) {
            Logger::warning('.htaccess non trovato, skip aggiornamento regole sicurezza');
            return;
        }
        
        // 2. CRITICO: Verifica permessi di scrittura PRIMA di procedere
        if (!is_writable($htaccess_file)) {
            Logger::error('.htaccess non scrivibile - permessi insufficienti su shared hosting');
            
            // Mostra avviso admin una sola volta
            if (!get_transient('fp_ps_htaccess_warning_shown')) {
                add_action('admin_notices', function() {
                    if (current_user_can('manage_options')) {
                        echo '<div class="notice notice-error is-dismissible">
                            <p><strong>FP Performance Suite - Errore Permessi:</strong> Impossibile modificare .htaccess. Contatta il supporto hosting per i permessi di scrittura.</p>
                        </div>';
                    }
                });
                set_transient('fp_ps_htaccess_warning_shown', true, WEEK_IN_SECONDS);
            }
            
            return;
        }
        
        // 3. Leggi contenuto attuale
        $htaccess_content = @file_get_contents($htaccess_file);
        if ($htaccess_content === false) {
            Logger::error('Impossibile leggere .htaccess - errore I/O');
            return;
        }
        
        // 4. Verifica se regole già presenti
        if (strpos($htaccess_content, '# FP Performance Security Rules') !== false) {
            Logger::debug('Regole sicurezza .htaccess già presenti, skip');
            return;
        }
        
        // 5. BACKUP AUTOMATICO prima di modificare
        $backup_file = $htaccess_file . '.fp-backup-' . date('Y-m-d-H-i-s');
        $backup_success = @copy($htaccess_file, $backup_file);
        
        if ($backup_success) {
            Logger::info('Backup .htaccess creato: ' . basename($backup_file));
            
            // Mantieni solo ultimi 5 backup per risparmiare spazio
            $this->cleanupOldBackups();
        } else {
            Logger::warning('Impossibile creare backup .htaccess - procedo comunque con cautela');
        }
        
        // 6. Aggiungi regole sicurezza
        try {
            $new_rules = $this->getSecurityRules();
            $updated_content = $htaccess_content . "\n" . $new_rules;
            
            // 7. Scrivi con gestione errori
            $write_result = @file_put_contents($htaccess_file, $updated_content);
            
            if ($write_result === false) {
                Logger::error('Impossibile scrivere .htaccess - operazione fallita');
                
                // Tenta ripristino backup se disponibile
                if ($backup_success) {
                    @copy($backup_file, $htaccess_file);
                    Logger::info('Backup .htaccess ripristinato dopo errore scrittura');
                }
                
                return;
            }
            
            Logger::info('Regole sicurezza .htaccess applicate con successo');
            
        } catch (\Throwable $e) {
            Logger::error('Errore durante aggiornamento .htaccess: ' . $e->getMessage());
            
            // Ripristina backup in caso di errore
            if ($backup_success && file_exists($backup_file)) {
                @copy($backup_file, $htaccess_file);
                Logger::info('Backup .htaccess ripristinato dopo eccezione');
            }
        }
    }
    
    /**
     * Rimuove vecchi backup .htaccess (mantiene ultimi 5)
     */
    private function cleanupOldBackups(): void
    {
        $backup_pattern = ABSPATH . '.htaccess.fp-backup-*';
        $backups = glob($backup_pattern);
        
        if (!$backups || count($backups) <= 5) {
            return;
        }
        
        // Ordina per data (più vecchi prima)
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Rimuovi i più vecchi lasciando solo ultimi 5
        $to_remove = array_slice($backups, 0, -5);
        foreach ($to_remove as $old_backup) {
            @unlink($old_backup);
            Logger::debug('Rimosso vecchio backup: ' . basename($old_backup));
        }
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
     * Restituisce le impostazioni di sicurezza
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        $defaults = [
            'enabled' => false,
            'canonical_redirect' => [
                'enabled' => false,
                'force_https' => false,
                'force_www' => false,
                'domain' => '',
            ],
            'security_headers' => [
                'enabled' => $this->security_headers,
                'hsts' => false,
                'hsts_max_age' => 31536000,
                'hsts_subdomains' => false,
                'hsts_preload' => false,
                'x_content_type_options' => true,
                'x_frame_options' => 'SAMEORIGIN',
                'referrer_policy' => 'strict-origin-when-cross-origin',
                'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
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
                'protect_htaccess' => true,
                'protect_php_files' => false,
            ],
            'xmlrpc_disabled' => false,
            'hotlink_protection' => [
                'enabled' => false,
                'allow_google' => true,
                'allowed_domains' => [],
            ],
            'advanced' => [
                'custom_htaccess_rules' => '',
            ],
        ];
        
        $saved = get_option('fp_ps_htaccess_security', []);
        
        // Merge ricorsivo per assicurare che tutti i valori di default esistano
        return array_replace_recursive($defaults, $saved);
    }
    
    /**
     * Restituisce lo stato della sicurezza
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        $htaccess_file = ABSPATH . '.htaccess';
        $htaccess_has_rules = false;
        
        if (file_exists($htaccess_file)) {
            $content = file_get_contents($htaccess_file);
            $htaccess_has_rules = strpos($content, '# FP Performance Security Rules') !== false;
        }
        
        return [
            'enabled' => $this->cache_rules || $this->security_headers,
            'cache_rules' => $this->cache_rules,
            'security_headers' => $this->security_headers,
            'htaccess_exists' => file_exists($htaccess_file),
            'htaccess_writable' => is_writable($htaccess_file),
            'htaccess_has_rules' => $htaccess_has_rules,
        ];
    }
    
    /**
     * Aggiorna le impostazioni di sicurezza
     * 
     * @param array $settings Nuove impostazioni
     * @return bool Successo o fallimento
     */
    public function update(array $settings): bool
    {
        return update_option('fp_ps_htaccess_security', $settings);
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}