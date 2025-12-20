<?php

namespace FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;

use FP\PerfSuite\Utils\Logger;
use function get_option;

/**
 * Verifica se uno script deve essere ritardato
 * 
 * @package FP\PerfSuite\Services\Assets\ThirdPartyScriptManager
 * @author Francesco Passeri
 */
class ScriptDelayChecker
{
    /**
     * Verifica se uno script deve essere ritardato
     */
    public function shouldDelayScript(string $src, array $settings, array $customScripts = []): bool
    {
        // CONTROLLO ESCLUSIONI - Prima di tutto
        if (!empty($settings['exclusions'])) {
            $exclusions = array_filter(array_map('trim', explode("\n", $settings['exclusions'])));
            foreach ($exclusions as $pattern) {
                if (!empty($pattern) && stripos($src, $pattern) !== false) {
                    Logger::debug('Script escluso dal delay (pattern match)', [
                        'src' => basename($src),
                        'pattern' => $pattern,
                    ]);
                    return false; // NON ritardare questo script
                }
            }
        }
        
        // Delay all if enabled (except WordPress core scripts)
        if ($settings['delay_all']) {
            if (strpos($src, '/wp-includes/') !== false || strpos($src, '/wp-admin/') !== false) {
                return false;
            }
            return true;
        }

        // Check against configured script patterns
        foreach ($settings['scripts'] as $scriptConfig) {
            if (empty($scriptConfig['enabled']) || empty($scriptConfig['delay'])) {
                continue;
            }

            if (!isset($scriptConfig['patterns']) || !is_array($scriptConfig['patterns'])) {
                continue;
            }

            foreach ($scriptConfig['patterns'] as $pattern) {
                if (strpos($src, $pattern) !== false) {
                    return true;
                }
            }
        }

        // Check against custom scripts
        foreach ($customScripts as $customScript) {
            if (empty($customScript['enabled']) || empty($customScript['delay'])) {
                continue;
            }

            if (!isset($customScript['patterns']) || !is_array($customScript['patterns'])) {
                continue;
            }

            foreach ($customScript['patterns'] as $pattern) {
                if (strpos($src, $pattern) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}

