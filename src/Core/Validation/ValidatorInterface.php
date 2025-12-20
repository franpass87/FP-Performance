<?php

/**
 * Validator Interface
 * 
 * Defines contract for validation service
 *
 * @package FP\PerfSuite\Core\Validation
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Validation;

interface ValidatorInterface
{
    /**
     * Validate data against rules
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return ValidationResult Validation result
     */
    public function validate(array $data, array $rules): ValidationResult;
    
    /**
     * Validate a single value
     * 
     * @param mixed $value Value to validate
     * @param string|array $rule Validation rule(s)
     * @return bool True if valid, false otherwise
     */
    public function validateValue($value, $rule): bool;
}









