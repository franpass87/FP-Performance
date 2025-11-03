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
        
        // FIX: Non bloccare se ci sono buffer, lavora con loro
        // WordPress e molti plugin usano output buffering
        if (ob_get_level() > 5) { // Solo se troppi buffer nested
            error_log('[FP-PerfSuite] HtmlMinifier: Too many output buffers (' . ob_get_level() . '), skipping');
            return;
        }
        
        // Verifica che non ci siano conflitti
        $handlers = ob_list_handlers();
        if (in_array('ob_gzhandler', $handlers, true)) {
            error_log('[FP-PerfSuite] HtmlMinifier: ob_gzhandler already active, may conflict');
        }
        
        // SICUREZZA: Rimuoviamo error suppression e gestiamo errori correttamente
        try {
            $started = ob_start([$this, 'minify']);
            if ($started) {
                $this->bufferStarted = true;
                error_log('[FP-PerfSuite] HtmlMinifier: Output buffer started successfully');
            } else {
                error_log('FP Performance Suite: Failed to start output buffer for HTML minification');
            }
        } catch (\Throwable $e) {
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
        
        // PROTEZIONE: Banner cookie FP Privacy Plugin
        // Proteggi il banner e il modal per evitare interferenze
        $html = preg_replace_callback(
            '/<div[^>]*id=["\']fp-privacy-banner[^>]*>.*?<\/div>/is',
            function($matches) use (&$protected, &$index) {
                $placeholder = '___FP_PRIVACY_BANNER_' . $index . '___';
                $protected[$placeholder] = $matches[0];
                $index++;
                return $placeholder;
            },
            $html
        );

        $html = preg_replace_callback(
            '/<div[^>]*id=["\']fp-privacy-modal[^>]*>.*?<\/div>/is',
            function($matches) use (&$protected, &$index) {
                $placeholder = '___FP_PRIVACY_MODAL_' . $index . '___';
                $protected[$placeholder] = $matches[0];
                $index++;
                return $placeholder;
            },
            $html
        );

        // Proteggi anche il root container del banner
        $html = preg_replace_callback(
            '/<div[^>]*data-fp-privacy-banner[^>]*>.*?<\/div>/is',
            function($matches) use (&$protected, &$index) {
                $placeholder = '___FP_PRIVACY_ROOT_' . $index . '___';
                $protected[$placeholder] = $matches[0];
                $index++;
                return $placeholder;
            },
            $html
        );
        
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
