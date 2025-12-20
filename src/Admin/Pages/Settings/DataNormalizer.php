<?php

namespace FP\PerfSuite\Admin\Pages\Settings;

use function array_key_exists;
use function is_array;
use function is_string;
use function is_int;
use function is_float;
use function is_bool;
use function trim;
use function strcasecmp;
use function filter_var;
use function FILTER_VALIDATE_INT;
use function FILTER_VALIDATE_BOOLEAN;
use function FILTER_NULL_ON_FAILURE;

/**
 * Normalizza i dati importati per garantire la validità
 * 
 * @package FP\PerfSuite\Admin\Pages\Settings
 * @author Francesco Passeri
 */
class DataNormalizer
{
    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,headers:array{Cache-Control:string},expires_ttl:int,htaccess:string}
     */
    public function normalizeBrowserCacheImport(array $incoming, array $defaults): array
    {
        $defaultHeaders = [];
        if (isset($defaults['headers']) && is_array($defaults['headers'])) {
            $defaultHeaders = $defaults['headers'];
        }

        $defaultCacheControl = isset($defaultHeaders['Cache-Control']) && is_string($defaultHeaders['Cache-Control'])
            ? trim($defaultHeaders['Cache-Control'])
            : 'public, max-age=31536000';

        $defaultTtl = isset($defaults['expires_ttl']) ? (int) $defaults['expires_ttl'] : 31536000;
        $defaultHtaccess = isset($defaults['htaccess']) && is_string($defaults['htaccess'])
            ? $defaults['htaccess']
            : '';

        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $headerValue = $incoming['headers'] ?? [];
        if (is_string($headerValue)) {
            $headerValue = ['Cache-Control' => $headerValue];
        }

        $cacheControl = $defaultCacheControl;
        if (is_array($headerValue)) {
            if (isset($headerValue['Cache-Control']) && is_string($headerValue['Cache-Control'])) {
                $trimmed = trim($headerValue['Cache-Control']);
                if ($trimmed !== '') {
                    $cacheControl = $trimmed;
                }
            } else {
                foreach ($headerValue as $key => $value) {
                    if (!is_string($key) || strcasecmp($key, 'Cache-Control') !== 0) {
                        continue;
                    }

                    if (is_string($value)) {
                        $trimmed = trim($value);
                        if ($trimmed !== '') {
                            $cacheControl = $trimmed;
                            break;
                        }
                    }
                }
            }
        }

        if ($cacheControl === $defaultCacheControl && isset($incoming['cache_control']) && is_string($incoming['cache_control'])) {
            $legacy = trim($incoming['cache_control']);
            if ($legacy !== '') {
                $cacheControl = $legacy;
            }
        }

        $ttl = $defaultTtl;
        if (array_key_exists('expires_ttl', $incoming)) {
            $parsedTtl = $this->parseInteger($incoming['expires_ttl']);
            if ($parsedTtl !== null && $parsedTtl >= 0) {
                $ttl = $parsedTtl;
            }
        }

        $htaccess = $defaultHtaccess;
        if (array_key_exists('htaccess', $incoming) && is_string($incoming['htaccess'])) {
            $htaccess = $incoming['htaccess'];
        }

        return [
            'enabled' => $enabled,
            'headers' => ['Cache-Control' => $cacheControl],
            'expires_ttl' => $ttl,
            'htaccess' => $htaccess,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array{enabled:bool,ttl:int}
     */
    public function normalizePageCacheImport(array $incoming, array $defaults): array
    {
        $defaultTtl = isset($defaults['ttl']) ? (int) $defaults['ttl'] : 3600;

        $enabled = $this->resolveBoolean($incoming, 'enabled', !empty($defaults['enabled']));

        $ttl = $defaultTtl;
        if (array_key_exists('ttl', $incoming)) {
            $parsedTtl = $this->parseInteger($incoming['ttl']);
            if ($parsedTtl !== null && $parsedTtl >= 0) {
                $ttl = $parsedTtl;
            }
        }

        return [
            'enabled' => $enabled,
            'ttl' => $ttl,
        ];
    }

    /**
     * @param array<string, mixed> $incoming
     * @param array<string, mixed> $defaults
     * @return array<string, mixed>
     */
    public function normalizeAssetSettingsImport(array $incoming, array $defaults): array
    {
        $settings = $defaults;

        foreach (['minify_html', 'defer_js', 'async_js', 'remove_emojis', 'combine_css', 'combine_js'] as $flag) {
            if (array_key_exists($flag, $incoming)) {
                $settings[$flag] = $this->interpretBoolean($incoming[$flag], !empty($defaults[$flag] ?? false));
            }
        }

        foreach (['dns_prefetch', 'preload'] as $listKey) {
            if (array_key_exists($listKey, $incoming)) {
                $settings[$listKey] = $incoming[$listKey];
            }
        }

        if (array_key_exists('heartbeat_admin', $incoming)) {
            $parsedHeartbeat = $this->parseInteger($incoming['heartbeat_admin']);
            if ($parsedHeartbeat !== null && $parsedHeartbeat >= 0) {
                $settings['heartbeat_admin'] = $parsedHeartbeat;
            }
        }

        return $settings;
    }

    /**
     * @param array<string, mixed> $source
     */
    private function resolveBoolean(array $source, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $source)) {
            return $default;
        }

        return $this->interpretBoolean($source[$key], $default);
    }

    /**
     * @param mixed $value
     */
    private function parseInteger($value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            $filtered = filter_var($trimmed, FILTER_VALIDATE_INT);
            if ($filtered !== false) {
                return (int) $filtered;
            }
        }

        return null;
    }

    /**
     * @param mixed $value
     */
    private function interpretBoolean($value, bool $fallback): bool
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
















