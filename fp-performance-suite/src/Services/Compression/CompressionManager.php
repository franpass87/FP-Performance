<?php

namespace FP\PerfSuite\Services\Compression;

use FP\PerfSuite\Utils\Htaccess;
use FP\PerfSuite\Utils\Logger;

/**
 * Compression Manager - Gestisce compressione Brotli e Gzip
 *
 * @package FP\PerfSuite\Services\Compression
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CompressionManager
{
    private Htaccess $htaccess;
    private const SECTION_NAME = 'FP Performance Compression';

    public function __construct(Htaccess $htaccess)
    {
        $this->htaccess = $htaccess;
    }

    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // Hook per applicare le regole di compressione se abilitato
        add_action('init', [$this, 'maybeApplyRules']);
    }

    /**
     * Applica le regole di compressione se abilitato
     */
    public function maybeApplyRules(): void
    {
        if ($this->isEnabled()) {
            $this->applyCompressionRules();
        }
    }

    /**
     * Verifica se la compressione è abilitata
     */
    public function isEnabled(): bool
    {
        return (bool) get_option('fp_ps_compression_enabled', false);
    }

    /**
     * Verifica se Brotli è abilitato
     */
    public function isBrotliEnabled(): bool
    {
        return (bool) get_option('fp_ps_compression_brotli_enabled', false);
    }

    /**
     * Verifica se Brotli è supportato dal server
     */
    public function isBrotliSupported(): bool
    {
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            return in_array('mod_brotli', $modules, true);
        }
        
        // Controlla se il modulo è menzionato nella configurazione
        if (function_exists('apache_get_version')) {
            return strpos(strtolower(apache_get_version()), 'brotli') !== false;
        }

        return false;
    }

    /**
     * Verifica se Gzip/Deflate è supportato dal server
     */
    public function isGzipSupported(): bool
    {
        // Controlla zlib PHP
        if (function_exists('gzencode')) {
            return true;
        }

        // Controlla mod_deflate di Apache
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            return in_array('mod_deflate', $modules, true) || in_array('mod_gzip', $modules, true);
        }

        return false;
    }

    /**
     * Verifica se la compressione è attualmente attiva
     */
    public function isActive(): bool
    {
        // Controlla zlib.output_compression PHP
        $zlibCompression = ini_get('zlib.output_compression');
        if ($zlibCompression && (int) $zlibCompression === 1) {
            return true;
        }

        // Controlla moduli Apache
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (in_array('mod_deflate', $modules, true) || in_array('mod_brotli', $modules, true)) {
                return true;
            }
        }

        // Controlla se le regole sono presenti in .htaccess
        return $this->htaccess->hasSection(self::SECTION_NAME);
    }

    /**
     * Abilita la compressione
     */
    public function enable(): bool
    {
        try {
            update_option('fp_ps_compression_enabled', true);
            
            if ($this->htaccess->isSupported()) {
                $result = $this->applyCompressionRules();
                
                if ($result) {
                    Logger::info('Compressione Brotli/Gzip abilitata');
                    do_action('fp_ps_compression_enabled');
                    return true;
                }
                
                Logger::warning('Impossibile applicare le regole di compressione in .htaccess');
                return false;
            }
            
            Logger::info('Compressione abilitata (senza modifiche .htaccess)');
            return true;
            
        } catch (\Throwable $e) {
            Logger::error('Errore durante l\'abilitazione della compressione', $e);
            return false;
        }
    }

    /**
     * Disabilita la compressione
     */
    public function disable(): bool
    {
        try {
            update_option('fp_ps_compression_enabled', false);
            
            if ($this->htaccess->isSupported() && $this->htaccess->hasSection(self::SECTION_NAME)) {
                $result = $this->htaccess->removeSection(self::SECTION_NAME);
                
                if ($result) {
                    Logger::info('Compressione Brotli/Gzip disabilitata');
                    do_action('fp_ps_compression_disabled');
                    return true;
                }
                
                Logger::warning('Impossibile rimuovere le regole di compressione da .htaccess');
                return false;
            }
            
            Logger::info('Compressione disabilitata');
            return true;
            
        } catch (\Throwable $e) {
            Logger::error('Errore durante la disabilitazione della compressione', $e);
            return false;
        }
    }

    /**
     * Applica le regole di compressione in .htaccess
     */
    private function applyCompressionRules(): bool
    {
        if (!$this->htaccess->isSupported()) {
            return false;
        }

        $rules = $this->generateCompressionRules();
        return $this->htaccess->injectRules(self::SECTION_NAME, $rules);
    }

    /**
     * Genera le regole di compressione per .htaccess
     */
    private function generateCompressionRules(): string
    {
        $rules = [];

        // Escludi endpoint admin critici dalla compressione
        $rules[] = '# Escludi endpoint admin critici dalla compressione';
        $rules[] = '<FilesMatch "(admin-post\.php|admin-ajax\.php|upload\.php)$">';
        $rules[] = '    <IfModule mod_deflate.c>';
        $rules[] = '        SetEnv no-gzip 1';
        $rules[] = '    </IfModule>';
        $rules[] = '    <IfModule mod_brotli.c>';
        $rules[] = '        SetEnv no-brotli 1';
        $rules[] = '    </IfModule>';
        $rules[] = '</FilesMatch>';
        $rules[] = '';

        // Regole Brotli (preferito se supportato)
        if ($this->isBrotliSupported()) {
            $rules[] = '<IfModule mod_brotli.c>';
            $rules[] = '    AddOutputFilterByType BROTLI_COMPRESS text/html text/plain text/xml text/css text/javascript';
            $rules[] = '    AddOutputFilterByType BROTLI_COMPRESS application/xml application/xhtml+xml application/rss+xml';
            $rules[] = '    AddOutputFilterByType BROTLI_COMPRESS application/javascript application/x-javascript';
            $rules[] = '    AddOutputFilterByType BROTLI_COMPRESS application/json';
            $rules[] = '    AddOutputFilterByType BROTLI_COMPRESS application/font-woff application/font-woff2';
            $rules[] = '    AddOutputFilterByType BROTLI_COMPRESS image/svg+xml';
            $rules[] = '</IfModule>';
            $rules[] = '';
        }

        // Regole Gzip/Deflate (fallback universale)
        $rules[] = '<IfModule mod_deflate.c>';
        $rules[] = '    # Comprimi HTML, CSS, JavaScript, Text, XML e Font';
        $rules[] = '    AddOutputFilterByType DEFLATE text/html';
        $rules[] = '    AddOutputFilterByType DEFLATE text/plain';
        $rules[] = '    AddOutputFilterByType DEFLATE text/xml';
        $rules[] = '    AddOutputFilterByType DEFLATE text/css';
        $rules[] = '    AddOutputFilterByType DEFLATE text/javascript';
        $rules[] = '    AddOutputFilterByType DEFLATE application/xml';
        $rules[] = '    AddOutputFilterByType DEFLATE application/xhtml+xml';
        $rules[] = '    AddOutputFilterByType DEFLATE application/rss+xml';
        $rules[] = '    AddOutputFilterByType DEFLATE application/javascript';
        $rules[] = '    AddOutputFilterByType DEFLATE application/x-javascript';
        $rules[] = '    AddOutputFilterByType DEFLATE application/json';
        $rules[] = '    AddOutputFilterByType DEFLATE application/ld+json';
        $rules[] = '    AddOutputFilterByType DEFLATE application/font-woff';
        $rules[] = '    AddOutputFilterByType DEFLATE application/font-woff2';
        $rules[] = '    AddOutputFilterByType DEFLATE image/svg+xml';
        $rules[] = '';
        $rules[] = '    # Gestisci browser con bug di compressione';
        $rules[] = '    BrowserMatch ^Mozilla/4 gzip-only-text/html';
        $rules[] = '    BrowserMatch ^Mozilla/4\\.0[678] no-gzip';
        $rules[] = '    BrowserMatch \\bMSIE !no-gzip !gzip-only-text/html';
        $rules[] = '';
        $rules[] = '    # Assicurati che i proxy memorizzino entrambe le versioni';
        $rules[] = '    Header append Vary User-Agent env=!dont-vary';
        $rules[] = '</IfModule>';

        return implode(PHP_EOL, $rules);
    }

    /**
     * Ottieni lo stato della compressione
     */
    public function status(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'active' => $this->isActive(),
            'brotli_supported' => $this->isBrotliSupported(),
            'brotli_enabled' => $this->isBrotliEnabled(),
            'gzip_supported' => $this->isGzipSupported(),
            'htaccess_supported' => $this->htaccess->isSupported(),
            'has_rules' => $this->htaccess->hasSection(self::SECTION_NAME),
        ];
    }

    /**
     * Ottieni informazioni dettagliate sulla compressione
     */
    public function getInfo(): array
    {
        $info = [
            'status' => $this->status(),
            'modules' => [],
            'php_settings' => [],
        ];

        // Informazioni sui moduli Apache
        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            $info['modules'] = [
                'mod_brotli' => in_array('mod_brotli', $modules, true),
                'mod_deflate' => in_array('mod_deflate', $modules, true),
                'mod_gzip' => in_array('mod_gzip', $modules, true),
            ];
        }

        // Impostazioni PHP
        $info['php_settings'] = [
            'zlib.output_compression' => ini_get('zlib.output_compression'),
            'gzencode_available' => function_exists('gzencode'),
        ];

        return $info;
    }
}
