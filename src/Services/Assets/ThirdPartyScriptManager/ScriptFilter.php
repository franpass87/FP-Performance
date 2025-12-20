<?php

namespace FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;

use FP\PerfSuite\Utils\Logger;

/**
 * Filtra gli script tag per applicare delay loading
 * 
 * @package FP\PerfSuite\Services\Assets\ThirdPartyScriptManager
 * @author Francesco Passeri
 */
class ScriptFilter
{
    private ScriptDelayChecker $delayChecker;

    public function __construct(ScriptDelayChecker $delayChecker)
    {
        $this->delayChecker = $delayChecker;
    }

    /**
     * Filtra il tag script
     */
    public function filterScriptTag(string $tag, string $handle, string $src, array $settings, array $customScripts = []): string
    {
        // ESCLUSIONE: Script del plugin FP Restaurant Reservations
        // Non ritardare mai gli script necessari per il form di prenotazione
        if (strpos($handle, 'fp-resv') !== false || 
            strpos($handle, 'fp_resv') !== false ||
            strpos($handle, 'flatpickr') !== false ||
            strpos($src, 'FP-Restaurant-Reservations') !== false || 
            strpos($src, 'fp-restaurant-reservations') !== false) {
            return $tag;
        }

        // Check if script should be delayed
        if (!$this->delayChecker->shouldDelayScript($src, $settings, $customScripts)) {
            return $tag;
        }

        // Add data attribute for delayed loading
        $tag = str_replace(' src=', ' data-fp-delayed-src=', $tag);
        $tag = str_replace('<script', '<script data-fp-delayed="true" type="text/plain"', $tag);

        Logger::debug('Third-party script marked for delay', [
            'handle' => $handle,
            'src' => basename($src),
        ]);

        return $tag;
    }
}

