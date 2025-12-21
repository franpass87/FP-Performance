<?php

namespace FP\PerfSuite\Admin\Pages\Settings;

use FP\PerfSuite\Admin\Form\AbstractFormHandler;
use FP\PerfSuite\ServiceContainer;

use function update_option;
use function get_option;
use function __;

/**
 * Gestisce le submission dei form per Settings page
 * 
 * @package FP\PerfSuite\Admin\Pages\Settings
 * @author Francesco Passeri
 */
class FormHandler extends AbstractFormHandler
{
    /**
     * Gestisce il form delle impostazioni generali
     * 
     * @return string Messaggio di risultato (vuoto se nessun form processato)
     */
    public function handle(): string
    {
        if (!$this->isPost()) {
            return '';
        }

        if (!$this->verifyNonce('fp_ps_settings_nonce', 'fp-ps-settings')) {
            return '';
        }

        try {
            $pluginOptions = get_option('fp_ps_settings', []);
            $pluginOptions['allowed_role'] = $this->sanitizeInput('allowed_role', 'text') ?? 'administrator';
            $pluginOptions['safety_mode'] = isset($_POST['safety_mode']) ? ($this->sanitizeInput('safety_mode', 'bool') ?? false) : false;
            $pluginOptions['require_critical_css'] = isset($_POST['require_critical_css']) ? ($this->sanitizeInput('require_critical_css', 'bool') ?? false) : false;
            update_option('fp_ps_settings', $pluginOptions);
            
            $criticalCss = $this->sanitizeInput('critical_css', 'textarea') ?? '';
            update_option('fp_ps_critical_css', $criticalCss);

            $message = __('Impostazioni salvate con successo.', 'fp-performance-suite');
            return $this->successMessage($message);
        } catch (\Throwable $e) {
            return $this->handleError($e, 'Settings form');
        }
    }
}

