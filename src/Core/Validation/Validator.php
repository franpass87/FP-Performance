<?php

/**
 * Validator Service
 * 
 * Provides rule-based validation for form data and options
 *
 * @package FP\PerfSuite\Core\Validation
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Validation;

class Validator implements ValidatorInterface
{
    /**
     * Validate data against rules
     * 
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return ValidationResult Validation result
     */
    public function validate(array $data, array $rules): ValidationResult
    {
        $result = new ValidationResult(true);
        $validated = [];
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            // Handle multiple rules (pipe-separated or array)
            $ruleList = is_string($fieldRules) ? explode('|', $fieldRules) : (array) $fieldRules;
            
            foreach ($ruleList as $rule) {
                $ruleName = $this->parseRuleName($rule);
                $ruleParams = $this->parseRuleParams($rule);
                
                if (!$this->applyRule($ruleName, $value, $ruleParams)) {
                    $result->addError($field, $this->getErrorMessage($ruleName, $field, $ruleParams));
                    $result = new ValidationResult(false, $result->getErrors());
                } else {
                    $validated[$field] = $value;
                }
            }
        }
        
        $result->setData($validated);
        return $result;
    }
    
    /**
     * Validate a single value
     * 
     * @param mixed $value Value to validate
     * @param string|array $rule Validation rule(s)
     * @return bool True if valid, false otherwise
     */
    public function validateValue($value, $rule): bool
    {
        $rules = is_string($rule) ? explode('|', $rule) : (array) $rule;
        
        foreach ($rules as $r) {
            $ruleName = $this->parseRuleName($r);
            $ruleParams = $this->parseRuleParams($r);
            
            if (!$this->applyRule($ruleName, $value, $ruleParams)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Apply a validation rule
     * 
     * @param string $ruleName Rule name
     * @param mixed $value Value to validate
     * @param array $params Rule parameters
     * @return bool True if valid
     */
    private function applyRule(string $ruleName, $value, array $params): bool
    {
        switch ($ruleName) {
            case 'required':
                return !empty($value) || $value === 0 || $value === '0';
                
            case 'string':
                return is_string($value) || is_numeric($value);
                
            case 'integer':
                return is_int($value) || (is_string($value) && ctype_digit($value));
                
            case 'boolean':
                return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true);
                
            case 'email':
                return is_string($value) && is_email($value);
                
            case 'url':
                return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false;
                
            case 'min':
                $min = (int) ($params[0] ?? 0);
                if (is_numeric($value)) {
                    return (float) $value >= $min;
                }
                if (is_string($value)) {
                    return strlen($value) >= $min;
                }
                return false;
                
            case 'max':
                $max = (int) ($params[0] ?? PHP_INT_MAX);
                if (is_numeric($value)) {
                    return (float) $value <= $max;
                }
                if (is_string($value)) {
                    return strlen($value) <= $max;
                }
                return false;
                
            case 'in':
                return in_array($value, $params, true);
                
            default:
                return true; // Unknown rule passes
        }
    }
    
    /**
     * Parse rule name from rule string
     * 
     * @param string $rule Rule string (e.g., "required|min:5")
     * @return string Rule name
     */
    private function parseRuleName(string $rule): string
    {
        $parts = explode(':', $rule, 2);
        return trim($parts[0]);
    }
    
    /**
     * Parse rule parameters
     * 
     * @param string $rule Rule string
     * @return array Rule parameters
     */
    private function parseRuleParams(string $rule): array
    {
        $parts = explode(':', $rule, 2);
        if (!isset($parts[1])) {
            return [];
        }
        
        return array_map('trim', explode(',', $parts[1]));
    }
    
    /**
     * Get error message for a rule
     * 
     * @param string $ruleName Rule name
     * @param string $field Field name
     * @param array $params Rule parameters
     * @return string Error message
     */
    private function getErrorMessage(string $ruleName, string $field, array $params): string
    {
        $messages = [
            'required' => sprintf(__('The %s field is required.', 'fp-performance-suite'), $field),
            'email' => sprintf(__('The %s must be a valid email address.', 'fp-performance-suite'), $field),
            'url' => sprintf(__('The %s must be a valid URL.', 'fp-performance-suite'), $field),
            'min' => sprintf(__('The %s must be at least %s.', 'fp-performance-suite'), $field, $params[0] ?? ''),
            'max' => sprintf(__('The %s must not exceed %s.', 'fp-performance-suite'), $field, $params[0] ?? ''),
        ];
        
        return $messages[$ruleName] ?? sprintf(__('The %s field is invalid.', 'fp-performance-suite'), $field);
    }
}









