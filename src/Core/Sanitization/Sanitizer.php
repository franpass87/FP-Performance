<?php

/**
 * Sanitizer Service
 * 
 * Provides sanitization for form data and options using WordPress functions
 *
 * @package FP\PerfSuite\Core\Sanitization
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Sanitization;

class Sanitizer implements SanitizerInterface
{
    /**
     * Sanitize data
     * 
     * @param array $data Data to sanitize
     * @param array $rules Sanitization rules keyed by field
     * @return array Sanitized data
     */
    public function sanitize(array $data, array $rules): array
    {
        $sanitized = [];
        
        foreach ($rules as $field => $type) {
            $value = $data[$field] ?? null;
            
            if ($value === null) {
                continue;
            }
            
            $sanitized[$field] = $this->sanitizeValue($value, $type);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize a single value
     * 
     * @param mixed $value Value to sanitize
     * @param string $type Sanitization type
     * @return mixed Sanitized value
     */
    public function sanitizeValue($value, string $type)
    {
        switch ($type) {
            case 'text':
            case 'string':
                return sanitize_text_field((string) $value);
                
            case 'textarea':
                return sanitize_textarea_field((string) $value);
                
            case 'email':
                return sanitize_email((string) $value);
                
            case 'url':
                return esc_url_raw((string) $value);
                
            case 'key':
                return sanitize_key((string) $value);
                
            case 'integer':
            case 'int':
                return (int) $value;
                
            case 'float':
                return (float) $value;
                
            case 'boolean':
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                
            case 'array':
                if (!is_array($value)) {
                    return [];
                }
                return array_map('sanitize_text_field', $value);
                
            case 'html':
                return wp_kses_post($value);
                
            case 'css':
                return wp_strip_all_tags($value);
                
            case 'js':
                return esc_js($value);
                
            case 'attribute':
                return esc_attr((string) $value);
                
            default:
                return sanitize_text_field((string) $value);
        }
    }
}









