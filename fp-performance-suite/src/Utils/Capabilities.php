<?php

namespace FP\PerfSuite\Utils;

use function apply_filters;
use function get_option;

class Capabilities
{
    public static function required(): string
    {
        $settings = get_option('fp_ps_settings', ['allowed_role' => 'administrator']);
        $role = $settings['allowed_role'] ?? 'administrator';
        switch ($role) {
            case 'editor':
                $capability = 'edit_pages';
                break;
            default:
                $capability = 'manage_options';
                break;
        }

        return (string) apply_filters('fp_ps_required_capability', $capability, $role);
    }
}
