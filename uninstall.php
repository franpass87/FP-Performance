<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$optionKeys = [
    'fp_ps_page_cache',
    'fp_ps_browser_cache',
    'fp_ps_assets',
    'fp_ps_webp',
    'fp_ps_db',
    'fp_ps_settings',
    'fp_ps_preset',
    'fp_ps_critical_css',
];

foreach ($optionKeys as $key) {
    delete_option($key);
}

delete_option('fp_perfsuite_version');
