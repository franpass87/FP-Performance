<?php

namespace FP\PerfSuite\Utils;

class Env
{
    public function isMultisite(): bool
    {
        return function_exists('is_multisite') && is_multisite();
    }

    public function isCli(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }

    public function serverSoftware(): string
    {
        return isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : '';
    }

    public function isApache(): bool
    {
        return stripos($this->serverSoftware(), 'Apache') !== false;
    }
}
