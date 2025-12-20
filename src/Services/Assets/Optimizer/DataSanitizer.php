<?php

namespace FP\PerfSuite\Services\Assets\Optimizer;

use function array_key_exists;
use function esc_url_raw;
use function filter_var;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function preg_replace;
use function preg_split;
use function trim;
use function array_unique;
use function array_values;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * Sanitizza i dati per l'Asset Optimizer
 * 
 * @package FP\PerfSuite\Services\Assets\Optimizer
 * @author Francesco Passeri
 */
class DataSanitizer
{
    /**
     * Sanitize URL list (comma or newline separated)
     *
     * @param mixed $value
     * @return array<int, string>
     */
    public function sanitizeUrlList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $urls = [];
        foreach ($value as $entry) {
            if (is_array($entry)) {
                $entry = $entry['href'] ?? $entry['url'] ?? null;
            }

            if (!is_string($entry)) {
                continue;
            }
            $trimmed = trim($entry);
            if ($trimmed === '') {
                continue;
            }
            $sanitized = esc_url_raw($trimmed);
            if ($sanitized === '') {
                continue;
            }
            $urls[] = $sanitized;
        }

        return array_values(array_unique($urls));
    }

    /**
     * Sanitize handle list (comma or newline separated)
     *
     * @param mixed $value
     * @return array<int, string>
     */
    public function sanitizeHandleList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $handles = [];
        foreach ($value as $handle) {
            if (!is_string($handle)) {
                continue;
            }
            $trimmed = trim($handle);
            if ($trimmed === '') {
                continue;
            }
            // Sanitize handle (allow alphanumeric, dash, underscore)
            $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $trimmed);
            if ($sanitized === '') {
                continue;
            }
            $handles[] = $sanitized;
        }

        return array_values(array_unique($handles));
    }

    /**
     * Risolve un flag dalle impostazioni
     */
    public function resolveFlag(array $settings, string $key, bool $current): bool
    {
        if (!array_key_exists($key, $settings)) {
            return $current;
        }

        return $this->interpretFlag($settings[$key], $current);
    }

    /**
     * Interpreta un valore come flag booleano
     * 
     * @param mixed $value
     */
    public function interpretFlag($value, bool $fallback): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return false;
            }

            $filtered = filter_var($trimmed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtered !== null) {
                return $filtered;
            }
        }

        return $fallback;
    }
}















