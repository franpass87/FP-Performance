<?php

/**
 * Input Sanitizer Utility
 * 
 * Utility centralizzata per sanitizzazione input POST/GET.
 * Fornisce metodi consistenti e type-safe per la sanitizzazione.
 * 
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Utils;

class InputSanitizer
{
    /**
     * Sanitizza valore POST/GET in modo sicuro
     * 
     * @param string $key Chiave dell'input
     * @param string $type Tipo di sanitizzazione
     * @param array|null $source Array sorgente (default: $_POST)
     * @return mixed Valore sanitizzato o null se non presente
     */
    public static function sanitize(string $key, string $type = 'text', ?array $source = null): mixed
    {
        $source = $source ?? $_POST;

        if (!isset($source[$key])) {
            return null;
        }

        $value = wp_unslash($source[$key]);

        return match($type) {
            'text' => sanitize_text_field($value),
            'textarea' => sanitize_textarea_field($value),
            'email' => sanitize_email($value),
            'url' => esc_url_raw($value),
            'int' => absint($value),
            'float' => (float) $value,
            'bool' => !empty($value),
            'array' => array_map('sanitize_text_field', (array) $value),
            'html' => wp_kses_post($value),
            'raw' => $value, // Solo per casi speciali, usare con cautela
            default => sanitize_text_field($value)
        };
    }

    /**
     * Sanitizza array di valori secondo schema
     * 
     * @param array $data Dati da sanitizzare
     * @param array $schema Schema: ['key' => 'type', ...]
     * @return array Array sanitizzato
     */
    public static function sanitizeArray(array $data, array $schema): array
    {
        $sanitized = [];
        foreach ($schema as $key => $type) {
            if (isset($data[$key])) {
                $sanitized[$key] = self::sanitize($key, $type, $data);
            }
        }
        return $sanitized;
    }

    /**
     * Sanitizza tutti i valori di un array con lo stesso tipo
     * 
     * @param array $data Array da sanitizzare
     * @param string $type Tipo di sanitizzazione
     * @return array Array sanitizzato
     */
    public static function sanitizeAll(array $data, string $type = 'text'): array
    {
        return array_map(function($value) use ($type) {
            return self::sanitize('', $type, ['value' => $value]) ?? $value;
        }, $data);
    }
}




