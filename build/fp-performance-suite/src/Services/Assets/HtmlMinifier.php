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
     * @param string $html HTML content to minify
     * @return string Minified HTML
     */
    public function minify(string $html): string
    {
        $search = [
            '/\>[\n\r\t ]+/s',    // Remove whitespace after tags
            '/[\n\r\t ]+\</s',    // Remove whitespace before tags
            '/\s{2,}/',           // Replace multiple spaces with single space
        ];
        $replace = ['>', '<', ' '];
        return preg_replace($search, $replace, $html) ?? $html;
    }

    /**
     * Check if buffering has started
     */
    public function isBuffering(): bool
    {
        return $this->bufferStarted;
    }
}
