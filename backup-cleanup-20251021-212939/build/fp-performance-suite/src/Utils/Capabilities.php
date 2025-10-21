<?php

namespace FP\PerfSuite\Utils;

use function apply_filters;
use function get_option;
use function current_user_can;

class Capabilities
{
    public static function required(): string
    {
        // Ottieni le impostazioni con un fallback sicuro
        $settings = get_option('fp_ps_settings', []);
        
        // Se le impostazioni non sono un array o sono vuote, usa il default
        if (!is_array($settings)) {
            $settings = ['allowed_role' => 'administrator'];
        }
        
        $role = $settings['allowed_role'] ?? 'administrator';
        
        // Valida che il ruolo sia uno dei valori accettati
        if (!in_array($role, ['administrator', 'editor'], true)) {
            $role = 'administrator';
        }
        
        // Log per debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[FP Performance Suite] Capabilities::required() chiamato');
            error_log('[FP Performance Suite] Ruolo configurato: ' . $role);
        }
        
        // Determina la capability in base al ruolo
        switch ($role) {
            case 'editor':
                $capability = 'edit_pages';
                break;
            case 'administrator':
            default:
                $capability = 'manage_options';
                break;
        }

        // Applica filtri per permettere personalizzazioni
        $filtered_capability = (string) apply_filters('fp_ps_required_capability', $capability, $role);
        
        // Validazione finale: assicurati che la capability non sia vuota
        if (empty($filtered_capability)) {
            $filtered_capability = 'manage_options';
        }
        
        // Log per debug
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[FP Performance Suite] Capability calcolata: ' . $filtered_capability);
            error_log('[FP Performance Suite] Utente corrente ha la capability: ' . (current_user_can($filtered_capability) ? 'SI' : 'NO'));
        }
        
        return $filtered_capability;
    }
}
