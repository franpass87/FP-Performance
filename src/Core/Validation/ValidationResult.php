<?php

/**
 * Validation Result
 * 
 * Contains validation results and errors
 *
 * @package FP\PerfSuite\Core\Validation
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Validation;

class ValidationResult
{
    /** @var bool Whether validation passed */
    private bool $valid;
    
    /** @var array<string, string[]> Validation errors keyed by field */
    private array $errors = [];
    
    /** @var array Validated data */
    private array $data = [];
    
    /**
     * Constructor
     * 
     * @param bool $valid Whether validation passed
     * @param array $errors Validation errors
     * @param array $data Validated data
     */
    public function __construct(bool $valid = true, array $errors = [], array $data = [])
    {
        $this->valid = $valid;
        $this->errors = $errors;
        $this->data = $data;
    }
    
    /**
     * Check if validation passed
     * 
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->valid;
    }
    
    /**
     * Check if validation failed
     * 
     * @return bool
     */
    public function isInvalid(): bool
    {
        return !$this->valid;
    }
    
    /**
     * Get all validation errors
     * 
     * @return array<string, string[]> Errors keyed by field
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get errors for a specific field
     * 
     * @param string $field Field name
     * @return string[] Array of error messages
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Get first error message
     * 
     * @return string|null First error message or null
     */
    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return reset($fieldErrors);
            }
        }
        
        return null;
    }
    
    /**
     * Add an error
     * 
     * @param string $field Field name
     * @param string $message Error message
     * @return void
     */
    public function addError(string $field, string $message): void
    {
        $this->valid = false;
        
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        
        $this->errors[$field][] = $message;
    }
    
    /**
     * Get validated data
     * 
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Set validated data
     * 
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}









