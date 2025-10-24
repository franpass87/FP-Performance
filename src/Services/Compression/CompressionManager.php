<?php

namespace FP\PerfSuite\Services\Compression;

class CompressionManager
{
    private $gzip;
    private $brotli;
    private $minify_html;
    private $minify_css;
    private $minify_js;
    
    public function __construct($gzip = true, $brotli = false, $minify_html = true, $minify_css = true, $minify_js = true)
    {
        $this->gzip = $gzip;
        $this->brotli = $brotli;
        $this->minify_html = $minify_html;
        $this->minify_css = $minify_css;
        $this->minify_js = $minify_js;
    }
    
    public function init()
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
    
    public function enableGzip()
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        if (!headers_sent() && extension_loaded('zlib')) {
            ob_start('ob_gzhandler');
        }
    }
    
    public function enableBrotli()
    {
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        if (!headers_sent() && extension_loaded('brotli')) {
            ob_start('ob_brotli_handler');
        }
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
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}