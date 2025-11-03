<?php

namespace FP\PerfSuite\Services\Compression;

use FP\PerfSuite\Utils\Logger;

/**
 * Compression Manager Service
 * 
 * Gestisce compressione GZIP/Brotli
 * 
 * @package FP\PerfSuite\Services\Compression
 */
class CompressionManager
{
    private bool $gzip;
    private bool $brotli;
    private bool $minify_html;
    private bool $minify_css;
    private bool $minify_js;
    
    /**
     * Costruttore
     * 
     * @param bool $gzip Abilita GZIP
     * @param bool $brotli Abilita Brotli
     * @param bool $minify_html Abilita minify HTML (deprecated)
     * @param bool $minify_css Abilita minify CSS (deprecated)
     * @param bool $minify_js Abilita minify JS (deprecated)
     */
    public function __construct(bool $gzip = true, bool $brotli = false, bool $minify_html = true, bool $minify_css = true, bool $minify_js = true)
    {
        $this->gzip = $gzip;
        $this->brotli = $brotli;
        $this->minify_html = $minify_html;
        $this->minify_css = $minify_css;
        $this->minify_js = $minify_js;
    }
    
    /**
     * Inizializza il servizio
     */
    public function init(): void
    {
        if ($this->gzip) {
            add_action('init', [$this, 'enableGzip']);
        }
        
        if ($this->brotli) {
            add_action('init', [$this, 'enableBrotli']);
        }
        
        // DISABILITATO: Conflitto con HtmlMinifier
        // if ($this->minify_html) {
        //     add_action('wp_loaded', [$this, 'minifyHTML']);
        // }
        
        // DISABILITATO: Conflitto con Assets/Optimizer
        // La minificazione CSS/JS è gestita da Assets/Optimizer
        // if ($this->minify_css) {
        //     add_action('wp_enqueue_scripts', [$this, 'minifyCSS']);
        // }
        
        // if ($this->minify_js) {
        //     add_action('wp_enqueue_scripts', [$this, 'minifyJS']);
        // }
    }
    
    /**
     * Abilita compressione GZIP
     * 
     * FIX: Previene doppio ob_start
     */
    public function enableGzip(): void
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        // FIX: Verifica se GZIP già attivo
        if ($this->isGzipActive()) {
            Logger::debug('GZIP already active, skipping');
            return;
        }
        
        // Verifica headers e estensione
        if (headers_sent()) {
            Logger::warning('Headers already sent, cannot enable GZIP');
            return;
        }
        
        if (!extension_loaded('zlib')) {
            Logger::warning('zlib extension not loaded, cannot enable GZIP');
            return;
        }
        
        // Avvia compressione GZIP
        if (ob_start('ob_gzhandler')) {
            Logger::debug('GZIP compression enabled');
        } else {
            Logger::error('Failed to start GZIP compression');
        }
    }
    
    /**
     * Abilita compressione Brotli
     * 
     * FIX: Previene doppio ob_start
     */
    public function enableBrotli(): void
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        // FIX: Verifica se Brotli già attivo
        if ($this->isBrotliActive()) {
            Logger::debug('Brotli already active, skipping');
            return;
        }
        
        // Verifica headers e estensione
        if (headers_sent()) {
            Logger::warning('Headers already sent, cannot enable Brotli');
            return;
        }
        
        if (!extension_loaded('brotli') || !function_exists('brotli_compress')) {
            Logger::warning('Brotli extension not available');
            return;
        }
        
        // Avvia compressione Brotli
        if (ob_start('brotli_compress')) {
            Logger::debug('Brotli compression enabled');
        } else {
            Logger::error('Failed to start Brotli compression');
        }
    }
    
    /**
     * Verifica se GZIP è già attivo
     * 
     * @return bool True se già attivo
     */
    private function isGzipActive(): bool
    {
        $handlers = ob_list_handlers();
        return in_array('ob_gzhandler', $handlers, true) || 
               in_array('zlib output compression', $handlers, true);
    }
    
    /**
     * Verifica se Brotli è già attivo
     * 
     * @return bool True se già attivo
     */
    private function isBrotliActive(): bool
    {
        $handlers = ob_list_handlers();
        return in_array('brotli_compress', $handlers, true);
    }
    
    public function minifyHTML()
    {
        // DISABILITATO: Conflitto con HtmlMinifier
        // Il minificazione HTML è gestita da Assets/Optimizer
        return;
        
        if (!is_admin()) {
            ob_start([$this, 'minifyHTMLCallback']);
        }
    }
    
    public function minifyHTMLCallback($buffer)
    {
        if (!$this->minify_html) {
            return $buffer;
        }
        
        // Remove HTML comments
        $buffer = preg_replace('/<!--(?!\s*(?:\[if [^]]+]|<!|>))(?:(?!-->).)*-->/s', '', $buffer);
        
        // Remove whitespace
        $buffer = preg_replace('/\s+/', ' ', $buffer);
        $buffer = preg_replace('/>\s+</', '><', $buffer);
        
        return $buffer;
    }
    
    public function minifyCSS()
    {
        // DISABILITATO: Conflitto con Assets/Optimizer
        // La minificazione CSS è gestita da Assets/Optimizer
        return;
        
        if ($this->minify_css) {
            add_filter('style_loader_tag', [$this, 'minifyCSSCallback'], 10, 2);
        }
    }
    
    public function minifyCSSCallback($tag, $handle)
    {
        if (strpos($handle, 'minified') !== false) {
            return $tag;
        }
        
        // Add minification class
        $tag = str_replace('rel="stylesheet"', 'rel="stylesheet" class="minified"', $tag);
        
        return $tag;
    }
    
    public function minifyJS()
    {
        // DISABILITATO: Conflitto con Assets/Optimizer
        // La minificazione JS è gestita da Assets/Optimizer
        return;
        
        if ($this->minify_js) {
            add_filter('script_loader_tag', [$this, 'minifyJSCallback'], 10, 2);
        }
    }
    
    public function minifyJSCallback($tag, $handle)
    {
        if (strpos($handle, 'minified') !== false) {
            return $tag;
        }
        
        // Add minification class
        $tag = str_replace('src=', 'class="minified" src=', $tag);
        
        return $tag;
    }
    
    public function getCompressionMetrics()
    {
        return [
            'gzip_enabled' => $this->gzip,
            'brotli_enabled' => $this->brotli,
            'minify_html' => $this->minify_html,
            'minify_css' => $this->minify_css,
            'minify_js' => $this->minify_js
        ];
    }
    
    /**
     * Restituisce lo stato della compressione
     * 
     * @return array Array con 'enabled' e altre informazioni
     */
    public function status(): array
    {
        return [
            'enabled' => $this->gzip || $this->minify_html || $this->minify_css || $this->minify_js,
            'gzip_enabled' => $this->gzip,
            'brotli_enabled' => $this->brotli,
            'minify_html' => $this->minify_html,
            'minify_css' => $this->minify_css,
            'minify_js' => $this->minify_js,
        ];
    }
    
    /**
     * Restituisce informazioni dettagliate sulla compressione
     * 
     * @return array Array con informazioni dettagliate
     */
    public function getInfo(): array
    {
        return [
            'gzip_supported' => function_exists('gzencode'),
            'brotli_supported' => function_exists('brotli_compress'),
            'gzip_enabled' => $this->gzip,
            'brotli_enabled' => $this->brotli,
            'minify_html' => $this->minify_html,
            'minify_css' => $this->minify_css,
            'minify_js' => $this->minify_js,
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