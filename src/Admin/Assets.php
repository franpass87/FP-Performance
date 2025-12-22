<?php

namespace FP\PerfSuite\Admin;

class Assets
{
    public function boot(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    /**
     * Ottiene l'URL base corretto includendo la porta se presente
     * Questo risolve problemi CORS su Local con porte non standard
     *
     * @return string URL base completo di protocollo e porta
     */
    private function getCorrectBaseUrl(): string
    {
        $protocol = is_ssl() ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        // HTTP_HOST include già la porta se presente (es: "fp-development.local:10005")
        return $protocol . $host;
    }

    public function enqueue(string $hook): void
    {
        if (strpos($hook, 'fp-performance-suite') === false) {
            return;
        }

        // BUGFIX: Rileva automaticamente porta corretta per evitare CORS su Local
        $base_url = $this->getCorrectBaseUrl();

        // Enqueue modular CSS (uses @import for sub-modules)
        wp_enqueue_style(
            'fp-performance-suite-admin',
            $base_url . '/wp-content/plugins/FP-Performance/assets/css/admin.css',
            [],
            FP_PERF_SUITE_VERSION
        );

        // Versione con cache busting basata su filemtime
        $scriptVersion = FP_PERF_SUITE_VERSION;
        $scriptPath = FP_PERF_SUITE_DIR . '/assets/js/main.js';
        $tooltipPath = FP_PERF_SUITE_DIR . '/assets/js/components/tooltip.js';
        if (is_readable($scriptPath)) {
            $scriptVersion .= '-' . filemtime($scriptPath);
        }
        // Aggiungi anche timestamp di tooltip.js per forzare reload
        if (is_readable($tooltipPath)) {
            $scriptVersion .= '-' . filemtime($tooltipPath);
        }

        // Enqueue modular JavaScript (ES6 modules)
        // BUGFIX: Aggiunto 'jquery' perché Overview.php usa jQuery inline (riga 670)
        wp_enqueue_script(
            'fp-performance-suite-admin',
            $base_url . '/wp-content/plugins/FP-Performance/assets/js/main.js',
            ['wp-i18n', 'jquery'],
            $scriptVersion,
            true
        );

        // Listener globale per intercettare errori JS e facilitarne il debug
        wp_add_inline_script(
            'fp-performance-suite-admin',
            'window.addEventListener("error",function(event){if(!event || !event.message){return;}if(window.console&&window.console.warn){console.warn("[FP Performance Suite] JS error:",event.message,"source:",event.filename,"line:",event.lineno);}});',
            'before'
        );

        // Add type="module" attribute for ES6 modules
        add_filter('script_loader_tag', [$this, 'addModuleType'], 10, 3);
        
        // NOTE: risk-tooltip-positioner.js è stato sostituito da tooltip.js (modulo ES6)
        // che viene caricato tramite main.js per evitare conflitti

        // Localize script data for JavaScript modules
        $tooltipVersion = '';
        if (is_readable($tooltipPath)) {
            $tooltipVersion = filemtime($tooltipPath);
        }
        wp_localize_script('fp-performance-suite-admin', 'fpPerfSuite', [
            'restUrl' => esc_url_raw(get_rest_url(null, 'fp-ps/v1/')),
            // BUGFIX #29: Usa $base_url per includere porta corretta ed evitare CORS
            'ajaxUrl' => $base_url . '/wp-admin/admin-ajax.php',
            'tooltipVersion' => $tooltipVersion, // Timestamp per cache busting moduli ES6
            'confirmLabel' => __('Type PROCEDI to confirm high-risk actions', 'fp-performance-suite'),
            'cancelledLabel' => __('Action cancelled', 'fp-performance-suite'),
            'messages' => [
                'logsError' => __('Unable to load log data.', 'fp-performance-suite'),
                'presetError' => __('Unable to apply preset.', 'fp-performance-suite'),
                'presetSuccess' => __('Preset applied successfully!', 'fp-performance-suite'),
            ],
        ]);
    }

    /**
     * Add type="module" to our script tag for ES6 module support
     *
     * @param string $tag    The script tag
     * @param string $handle The script handle
     * @param string $src    The script source URL
     * @return string Modified script tag
     */
    public function addModuleType(string $tag, string $handle, string $src): string
    {
        if ('fp-performance-suite-admin' === $handle) {
            $tag = str_replace('<script ', '<script type="module" ', $tag);
        }
        return $tag;
    }

}