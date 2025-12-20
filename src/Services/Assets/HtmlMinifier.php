<?php

namespace FP\PerfSuite\Services\Assets;

use FP\PerfSuite\Utils\ErrorHandler;

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
        
        // NON attivare nell'admin di WordPress
        if (is_admin()) {
            return;
        }
        
        // FIX: Non bloccare se ci sono buffer, lavora con loro
        // WordPress e molti plugin usano output buffering
        if (ob_get_level() > 5) { // Solo se troppi buffer nested
            return;
        }
        
        // Verifica che non ci siano conflitti
        $handlers = ob_list_handlers();
        if (in_array('ob_gzhandler', $handlers, true)) {
            // Potenziale conflitto, ma continuiamo
        }
        
        // SICUREZZA: Rimuoviamo error suppression e gestiamo errori correttamente
        try {
            $started = ob_start([$this, 'minify']);
            if ($started) {
                $this->bufferStarted = true;
            }
        } catch (\Throwable $e) {
            ErrorHandler::handleSilently($e, 'HTML minification start buffer');
            // Se fallisce, disabilita automaticamente per evitare errori fatali
            $this->autoDisableOnError('minify_html');
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
                ErrorHandler::handleSilently($e, 'HTML minification end buffer');
                // Se fallisce, disabilita automaticamente per evitare errori fatali
                $this->autoDisableOnError('minify_html');
            }
        }
        
        $this->bufferStarted = false;
    }
    
    /**
     * Disabilita automaticamente l'opzione se causa errori fatali
     */
    private function autoDisableOnError(string $optionKey): void
    {
        try {
            $assets = get_option('fp_ps_assets', []);
            if (is_array($assets) && isset($assets[$optionKey])) {
                $assets[$optionKey] = false;
                update_option('fp_ps_assets', $assets, false);
                if (function_exists('error_log')) {
                    error_log(sprintf(
                        '[FP-Performance] Auto-disabled %s due to fatal error',
                        $optionKey
                    ));
                }
            }
        } catch (\Throwable $e) {
            // Silently fail - non vogliamo errori nel disabilitare
        }
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
