<?php

namespace FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;

/**
 * Gestisce l'iniezione del loader per script ritardati
 * 
 * @package FP\PerfSuite\Services\Assets\ThirdPartyScriptManager
 * @author Francesco Passeri
 */
class DelayedLoader
{
    /**
     * Inietta il loader per script ritardati
     */
    public function inject(array $settings): void
    {
        $loadOn = $settings['load_on'] ?? 'interaction';
        $delayTimeout = $settings['delay_timeout'] ?? 5000;
        
        ?>
        <script>
            (function() {
                function loadDelayedScripts() {
                    const scripts = document.querySelectorAll('script[data-fp-delayed="true"]');
                    scripts.forEach(function(script) {
                        const src = script.getAttribute('data-fp-delayed-src');
                        if (src) {
                            const newScript = document.createElement('script');
                            newScript.src = src;
                            newScript.async = true;
                            script.parentNode.replaceChild(newScript, script);
                        }
                    });
                }
                
                <?php if ($loadOn === 'interaction'): ?>
                // Load on first user interaction
                ['mousedown', 'touchstart', 'keydown'].forEach(function(event) {
                    document.addEventListener(event, function() {
                        loadDelayedScripts();
                    }, { once: true, passive: true });
                });
                <?php elseif ($loadOn === 'scroll'): ?>
                // Load on scroll
                let scrolled = false;
                window.addEventListener('scroll', function() {
                    if (!scrolled) {
                        scrolled = true;
                        loadDelayedScripts();
                    }
                }, { once: true, passive: true });
                <?php elseif ($loadOn === 'timeout'): ?>
                // Load after timeout
                setTimeout(function() {
                    loadDelayedScripts();
                }, <?php echo (int) $delayTimeout; ?>);
                <?php endif; ?>
            })();
        </script>
        <?php
    }
}















