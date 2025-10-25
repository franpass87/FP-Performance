<?php

namespace FP\PerfSuite\Services\Assets;

/**
 * HTML Minification Service
 *
 * Removes unnecessary whitespace, newlines, and tabs from HTML output
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class HtmlMinifier
{
    private bool $bufferStarted = false;

    /**
     * Start output buffering for HTML minification
     */
    public function startBuffer(): void
    {
        if ($this->bufferStarted) {
            return;
        }
        
        // LOGGING PER DEBUG
        error_log("[FP-PerfSuite] HtmlMinifier::startBuffer() called - is_admin(): " . (is_admin() ? 'TRUE' : 'FALSE'));
        
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            error_log("[FP-PerfSuite] HtmlMinifier::startBuffer() SKIPPED - in admin");
            return;
        }
        
        // SICUREZZA: Verifica che non ci siano buffer attivi
        if (ob_get_level() > 0) {
            return;
        }
        
        // SICUREZZA: Rimuoviamo error suppression e gestiamo errori correttamente
        try {
            $started = ob_start([$this, 'minify']);
            if ($started) {
                $this->bufferStarted = true;
            } else {
                // Log dell'errore se necessario
                error_log('FP Performance Suite: Failed to start output buffer for HTML minification');
            }
        } catch (\Exception $e) {
            error_log('FP Performance Suite: Exception in HTML minification: ' . $e->getMessage());
        }
    }

    /**
     * End output buffering
     */
    public function endBuffer(): void
    {
        if (!$this->bufferStarted) {
            return;
        }
        
        // SICUREZZA: Verifica che il buffer sia ancora attivo e gestisce errori
        if (ob_get_level() > 0) {
            try {
                ob_end_flush();
            } catch (\Exception $e) {
                error_log('FP Performance Suite: Exception ending output buffer: ' . $e->getMessage());
            }
        }
        
        $this->bufferStarted = false;
    }

    /**
     * Minify HTML content
     *
     * Protegge contenuto sensibile alla formattazione come <pre>, <textarea>, <code>, <script>, <style>
     *
     * @param string $html HTML content to minify
     * @return string Minified HTML
     */
    public function minify(string $html): string
    {
        // FIX BUG #14: Proteggi contenuto sensibile alla formattazione
        $protected = [];
        $index = 0;
        
        // Estrai e proteggi tag che non devono essere minificati
        // Include: <pre>, <textarea>, <code>, <script>, <style>
        $html = preg_replace_callback(
            '/<(pre|textarea|code|script|style)(\s[^>]*)?>.*?<\/\1>/is',
            function($matches) use (&$protected, &$index) {
                $placeholder = '___PROTECTED_CONTENT_' . $index . '___';
                $protected[$placeholder] = $matches[0];
                $index++;
                return $placeholder;
            },
            $html
        );
        
        // Ora minifica il resto del contenuto
        $search = [
            '/\>[\n\r\t ]+/s',    // Remove whitespace after tags
            '/[\n\r\t ]+\</s',    // Remove whitespace before tags
            '/\s{2,}/',           // Replace multiple spaces with single space
        ];
        $replace = ['>', '<', ' '];
        $html = preg_replace($search, $replace, $html) ?? $html;
        
        // Ripristina il contenuto protetto
        foreach ($protected as $placeholder => $original) {
            $html = str_replace($placeholder, $original, $html);
        }
        
        return $html;
    }

    /**
     * Check if buffering has started
     */
    public function isBuffering(): bool
    {
        return $this->bufferStarted;
    }
}
