<?php

/**
 * Sanitizer Interface
 * 
 * Defines contract for sanitization service
 *
 * @package FP\PerfSuite\Core\Sanitization
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Sanitization;

interface SanitizerInterface
{
    /**
     * Sanitize data
     * 
     * @param array $data Data to sanitize
     * @param array $rules Sanitization rules keyed by field
     * @return array Sanitized data
     */
    public function sanitize(array $data, array $rules): array;
    
    /**
     * Sanitize a single value
     * 
     * @param mixed $value Value to sanitize
     * @param string $type Sanitization type
     * @return mixed Sanitized value
     */
    public function sanitizeValue($value, string $type);
}









