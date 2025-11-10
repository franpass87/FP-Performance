<?php

namespace FP\PerfSuite\Admin;

class Assets
{
    public function boot(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        // BUGFIX #21: Inline CSS override per tooltip fix (garantisce applicazione immediata)
        add_action('admin_head', [$this, 'inlineTooltipFix'], 999);
    }
    
    /**
     * BUGFIX #21: CSS inline per fix immediato tooltip
     * Garantisce che i tooltip siano sempre visibili, anche con browser cache aggressiva
     */
    public function inlineTooltipFix(): void
    {
        // Solo sulle pagine FP Performance
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'fp-performance-suite') === false) {
            return;
        }
        
        echo '<style id="fp-ps-tooltip-fix">
            /* BUGFIX #21: Fix tooltip overflow e visibility */
            .fp-ps-card {
                overflow: visible !important;
            }
            
            .fp-ps-risk-tooltip {
                position: absolute !important;
                max-width: 450px !important;
                min-width: 320px !important;
                padding: 16px 20px !important;
                z-index: 999999999 !important;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35), 0 0 0 2px rgba(255, 255, 255, 0.15) !important;
                border-radius: 10px !important;
            }
            
            .fp-ps-risk-tooltip::after {
                left: var(--arrow-left, 50%) !important;
            }
            
            .fp-ps-risk-tooltip[data-arrow-position="top"]::after {
                top: auto !important;
                bottom: 100% !important;
                border-top-color: transparent !important;
                border-bottom-color: #1e293b !important;
            }
        </style>';
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

        // Enqueue modular JavaScript (ES6 modules)
        // BUGFIX: Aggiunto 'jquery' perché Overview.php usa jQuery inline (riga 670)
        wp_enqueue_script(
            'fp-performance-suite-admin',
            $base_url . '/wp-content/plugins/FP-Performance/assets/js/main.js',
            ['wp-i18n', 'jquery'],
            FP_PERF_SUITE_VERSION,
            true
        );

        // Add type="module" attribute for ES6 modules
        add_filter('script_loader_tag', [$this, 'addModuleType'], 10, 3);
        
        // Enqueue risk tooltip positioner
        wp_enqueue_script(
            'fp-performance-suite-risk-tooltip',
            $base_url . '/wp-content/plugins/FP-Performance/assets/js/risk-tooltip-positioner.js',
            [],
            FP_PERF_SUITE_VERSION,
            true
        );

        // Localize script data for JavaScript modules
        wp_localize_script('fp-performance-suite-admin', 'fpPerfSuite', [
            'restUrl' => esc_url_raw(get_rest_url(null, 'fp-ps/v1/')),
            // BUGFIX #29: Usa $base_url per includere porta corretta ed evitare CORS
            'ajaxUrl' => $base_url . '/wp-admin/admin-ajax.php',
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