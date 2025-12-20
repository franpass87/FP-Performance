<?php

namespace FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;

/**
 * Rileva script di terze parti nella pagina
 * 
 * @package FP\PerfSuite\Services\Assets\ThirdPartyScriptManager
 * @author Francesco Passeri
 */
class ScriptDetector
{
    /**
     * Rileva script nella pagina HTML
     */
    public function detectScripts(string $html, array $settings): array
    {
        $detected = [];
        
        preg_match_all('/<script[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);
        
        if (empty($matches[1])) {
            return $detected;
        }

        foreach ($matches[1] as $src) {
            foreach ($settings['scripts'] as $name => $config) {
                if (!isset($config['patterns']) || !is_array($config['patterns'])) {
                    continue;
                }
                
                foreach ($config['patterns'] as $pattern) {
                    if (strpos($src, $pattern) !== false) {
                        $detected[] = [
                            'name' => $name,
                            'src' => $src,
                            'managed' => $config['enabled'] ?? false,
                        ];
                        break 2;
                    }
                }
            }
        }

        return $detected;
    }
}















