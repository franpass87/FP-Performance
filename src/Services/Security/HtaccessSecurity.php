<?php

namespace FP\PerfSuite\Services\Security;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger as StaticLogger;

class HtaccessSecurity
{
    private $cache_rules;
    private $security_headers;
    private ?OptionsRepositoryInterface $optionsRepo = null;
    private ?LoggerInterface $logger = null;
    
    /**
     * Costruttore
     * 
     * @param bool $cache_rules Abilita regole cache
     * @param bool $security_headers Abilita security headers
     * @param OptionsRepositoryInterface|null $optionsRepo Repository opzionale per gestione opzioni
     * @param LoggerInterface|null $logger Logger opzionale per logging
     */
    public function __construct($cache_rules = true, $security_headers = true, ?OptionsRepositoryInterface $optionsRepo = null, ?LoggerInterface $logger = null)
    {
        $this->cache_rules = $cache_rules;
        $this->security_headers = $security_headers;
        $this->optionsRepo = $optionsRepo;
        $this->logger = $logger;
    }
    
    /**
     * Helper per logging con fallback
     * 
     * @param string $level Log level
     * @param string $message Message
     * @param array $context Context
     * @param \Throwable|null $exception Optional exception
     */
    private function log(string $level, string $message, array $context = [], ?\Throwable $exception = null): void
    {
        if ($this->logger !== null) {
            if ($exception !== null && method_exists($this->logger, $level)) {
                $this->logger->$level($message, $context, $exception);
            } else {
                $this->logger->$level($message, $context);
            }
        } else {
            StaticLogger::$level($message, $context);
        }
    }
    
    /**
     * Helper per ottenere opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $default Valore di default
     * @return mixed Valore opzione
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
    
    /**
     * Helper per salvare opzioni con fallback
     * 
     * @param string $key Chiave opzione
     * @param mixed $value Valore opzione
     * @param bool $autoload Se autoload
     * @return bool True se salvato con successo
     */
    private function setOption(string $key, $value, bool $autoload = true): bool
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->set($key, $value, $autoload);
        }
        return update_option($key, $value, $autoload);
    }
    
    public function init()
    {
        // Solo nel frontend
        if (!is_admin()) {
            // BUGFIX #23a: 'send_headers' Ã¨ MOLTO piÃ¹ presto di 'init' per gli header HTTP
            add_action('send_headers', [$this, 'addSecurityHeaders'], 1);
            add_action('wp_loaded', [$this, 'updateHtaccess']);
        }
        
        // BUGFIX #23b: Disabilita XML-RPC se richiesto
        $settings = $this->settings();
        if (!empty($settings['xmlrpc_disabled'])) {
            add_filter('xmlrpc_enabled', '__return_false', 999);
            add_filter('wp_xmlrpc_server_class', '__return_false', 999);
        }
    }
    
    public function addSecurityHeaders()
    {
        // Carica settings freschi per questa richiesta
        $settings = $this->settings();
        
        if (empty($settings['security_headers']['enabled'])) {
            return;
        }
        
        if (headers_sent()) {
            $this->log('warning', 'Headers già inviati, impossibile aggiungere security headers');
            return;
        }
        
        $headers = $settings['security_headers'];
        
        // BUGFIX #23a: Invia header configurabili invece di hardcoded
        if (!empty($headers['x_content_type_options'])) {
            header('X-Content-Type-Options: nosniff');
        }
        
        if (!empty($headers['x_frame_options'])) {
            $frameOption = $headers['x_frame_options'] ?? 'SAMEORIGIN';
            header('X-Frame-Options: ' . $frameOption);
        }
        
        // X-XSS-Protection (deprecated ma ancora utile per browser vecchi)
        header('X-XSS-Protection: 1; mode=block');
        
        if (!empty($headers['referrer_policy'])) {
            $policy = $headers['referrer_policy'] ?? 'strict-origin-when-cross-origin';
            header('Referrer-Policy: ' . $policy);
        }
        
        if (!empty($headers['permissions_policy'])) {
            header('Permissions-Policy: ' . $headers['permissions_policy']);
        }
        
        // HSTS
        if (!empty($headers['hsts'])) {
            $maxAge = $headers['hsts_max_age'] ?? 31536000;
            $hsts = "max-age={$maxAge}";
            if (!empty($headers['hsts_subdomains'])) {
                $hsts .= '; includeSubDomains';
            }
            if (!empty($headers['hsts_preload'])) {
                $hsts .= '; preload';
            }
            header('Strict-Transport-Security: ' . $hsts);
        }
        
        $this->log('debug', 'Security headers inviati', ['headers' => array_keys($headers)]);
    }
    
    public function updateHtaccess()
    {
        if (!$this->cache_rules) {
            return;
        }
        
        // FIX: Aumenta memory limit temporaneamente per questa operazione
        $original_memory = ini_get('memory_limit');
        @ini_set('memory_limit', '512M');
        
        try {
            $htaccess_file = ABSPATH . '.htaccess';
        
        // PROTEZIONE SHARED HOSTING: Verifica permessi e backup
        
        // 1. Verifica esistenza file
        if (!file_exists($htaccess_file)) {
            $this->log('warning', '.htaccess non trovato, skip aggiornamento regole sicurezza');
            return;
        }
        
        // 2. CRITICO: Verifica permessi di scrittura PRIMA di procedere
        if (!is_writable($htaccess_file)) {
            $this->log('error', '.htaccess non scrivibile - permessi insufficienti su shared hosting');
            
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
            $this->log('error', 'Impossibile leggere .htaccess - errore I/O');
            return;
        }
        
        // 4. Verifica se regole già presenti
        if (strpos($htaccess_content, '# FP Performance Security Rules') !== false) {
            $this->log('debug', 'Regole sicurezza .htaccess già presenti, skip');
            return;
        }
        
        // 5. BACKUP AUTOMATICO prima di modificare
        $backup_file = $htaccess_file . '.fp-backup-' . date('Y-m-d-H-i-s');
        $backup_success = @copy($htaccess_file, $backup_file);
        
        if ($backup_success) {
            $this->log('info', 'Backup .htaccess creato: ' . basename($backup_file));
            
            // Mantieni solo ultimi 5 backup per risparmiare spazio
            $this->cleanupOldBackups();
        } else {
            $this->log('warning', 'Impossibile creare backup .htaccess - procedo comunque con cautela');
        }
        
            // 6. Aggiungi regole sicurezza
            $new_rules = $this->getSecurityRules();
            $updated_content = $htaccess_content . "\n" . $new_rules;
            
            // 7. Scrivi con gestione errori
            $write_result = @file_put_contents($htaccess_file, $updated_content);
            
            if ($write_result === false) {
                $this->log('error', 'Impossibile scrivere .htaccess - operazione fallita');
                
                // Tenta ripristino backup se disponibile
                if ($backup_success) {
                    @copy($backup_file, $htaccess_file);
                    $this->log('info', 'Backup .htaccess ripristinato dopo errore scrittura');
                }
                
                return;
            }
            
            $this->log('info', 'Regole sicurezza .htaccess applicate con successo');
            
        } catch (\Throwable $e) {
            $this->log('error', 'Errore durante aggiornamento .htaccess: ' . $e->getMessage(), [], $e);
            
            // Ripristina backup in caso di errore
            if ($backup_success && file_exists($backup_file)) {
                @copy($backup_file, $htaccess_file);
                $this->log('info', 'Backup .htaccess ripristinato dopo eccezione');
            }
        } finally {
            // FIX: Ripristina memory limit originale
            if ($original_memory) {
                @ini_set('memory_limit', $original_memory);
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
        
        // Ordina per data (piÃ¹ vecchi prima)
        usort($backups, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });
        
        // Rimuovi i piÃ¹ vecchi lasciando solo ultimi 5
        $to_remove = array_slice($backups, 0, -5);
        foreach ($to_remove as $old_backup) {
            @unlink($old_backup);
            $this->log('debug', 'Rimosso vecchio backup: ' . basename($old_backup));
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
        
        $saved = $this->getOption('fp_ps_htaccess_security', []);
        
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
        $result = $this->setOption('fp_ps_htaccess_security', $settings);
        
        if ($result) {
            // FIX: Reinizializza il servizio per applicare immediatamente le modifiche
            $this->forceInit();
        }
        
        return $result;
    }
    
    /**
     * Forza l'inizializzazione del servizio
     * FIX: Ricarica le impostazioni e reinizializza il servizio
     */
    public function forceInit(): void
    {
        // Rimuovi hook esistenti
        remove_action('send_headers', [$this, 'addSecurityHeaders'], 1);
        remove_action('wp_loaded', [$this, 'updateHtaccess']);
        remove_filter('xmlrpc_enabled', '__return_false', 999);
        remove_filter('wp_xmlrpc_server_class', '__return_false', 999);
        
        // Reinizializza
        $this->init();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}