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
        ob_start([$this, 'minify']);
        $this->bufferStarted = true;
    }

    /**
     * End output buffering
     */
    public function endBuffer(): void
    {
        if (!$this->bufferStarted) {
            return;
        }
        if (ob_get_level() > 0) {
            ob_end_flush();
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
