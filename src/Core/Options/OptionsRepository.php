<?php

/**
 * Options Repository
 * 
 * Centralized options management with type safety, validation, defaults, and migration support.
 * Replaces direct get_option()/update_option() calls throughout the plugin.
 *
 * @package FP\PerfSuite\Core\Options
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Options;

use FP\PerfSuite\Core\Validation\ValidatorInterface;
use FP\PerfSuite\Core\Sanitization\SanitizerInterface;

class OptionsRepository implements OptionsRepositoryInterface
{
    /** @var string Option name prefix */
    private string $prefix;
    
    /** @var array<string, mixed> Options cache */
    private array $cache = [];
    
    /** @var array<string, mixed> Default values */
    private array $defaults = [];
    
    /** @var OptionsMigrator|null Migrator instance */
    private ?OptionsMigrator $migrator = null;
    
    /** @var ValidatorInterface|null Validator instance */
    private ?ValidatorInterface $validator = null;
    
    /** @var SanitizerInterface|null Sanitizer instance */
    private ?SanitizerInterface $sanitizer = null;
    
    /** @var array<string, string> Option type hints (key => type) */
    private array $typeHints = [];
    
    /**
     * Constructor
     * 
     * @param string $prefix Option name prefix (e.g., 'fp_ps_')
     * @param ValidatorInterface|null $validator Optional validator for option validation
     * @param SanitizerInterface|null $sanitizer Optional sanitizer for option sanitization
     */
    public function __construct(string $prefix = 'fp_ps_', ?ValidatorInterface $validator = null, ?SanitizerInterface $sanitizer = null)
    {
        $this->prefix = $prefix;
        $this->validator = $validator;
        $this->sanitizer = $sanitizer;
    }
    
    /**
     * Get an option value
     * 
     * @param string $key Option key (with or without prefix)
     * @param mixed $default Default value if option doesn't exist
     * @return mixed Option value
     */
    public function get(string $key, $default = null)
    {
        $key = $this->normalizeKey($key);
        
        // Check cache first
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        
        // Check defaults
        if (array_key_exists($key, $this->defaults)) {
            $default = $this->defaults[$key];
        }
        
        // Get from WordPress
        $value = get_option($key, $default);
        
        // Cache the value
        $this->cache[$key] = $value;
        
        return $value;
    }
    
    /**
     * Set an option value
     * 
     * @param string $key Option key (with or without prefix)
     * @param mixed $value Option value
     * @param bool $autoload Whether to autoload the option
     * @return bool True on success, false on failure
     */
    public function set(string $key, $value, bool $autoload = true): bool
    {
        $key = $this->normalizeKey($key);
        
        // Sanitize value if sanitizer is available
        if ($this->sanitizer !== null) {
            $value = $this->sanitizeValue($key, $value);
        }
        
        // Validate value if validator is available
        if ($this->validator !== null) {
            if (!$this->validateValue($key, $value)) {
                return false;
            }
        }
        
        $result = update_option($key, $value, $autoload);
        
        // Update cache
        if ($result) {
            $this->cache[$key] = $value;
            
            // Fire cache invalidation hook
            do_action('fp_ps_option_updated', $key, $value);
            do_action('fp_ps_options_cache_invalidated', $key);
        }
        
        return $result;
    }
    
    /**
     * Delete an option
     * 
     * @param string $key Option key (with or without prefix)
     * @return bool True on success, false on failure
     */
    public function delete(string $key): bool
    {
        $key = $this->normalizeKey($key);
        
        $result = delete_option($key);
        
        // Remove from cache
        if ($result && array_key_exists($key, $this->cache)) {
            unset($this->cache[$key]);
        }
        
        return $result;
    }
    
    /**
     * Check if an option exists
     * 
     * @param string $key Option key (with or without prefix)
     * @return bool True if option exists, false otherwise
     */
    public function has(string $key): bool
    {
        $key = $this->normalizeKey($key);
        
        // Check cache first
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key] !== false;
        }
        
        return get_option($key, null) !== null;
    }
    
    /**
     * Get all options (with optional prefix filter)
     * 
     * @param string|null $prefix Optional prefix to filter options (defaults to repository prefix)
     * @return array<string, mixed> Array of options
     */
    public function all(?string $prefix = null): array
    {
        $prefix = $prefix ?? $this->prefix;
        
        global $wpdb;
        
        // Query all options with prefix
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like($prefix) . '%'
            ),
            ARRAY_A
        );
        
        $result = [];
        foreach ($options as $option) {
            $key = $option['option_name'];
            $value = maybe_unserialize($option['option_value']);
            $result[$key] = $value;
            
            // Cache values
            $this->cache[$key] = $value;
        }
        
        return $result;
    }
    
    /**
     * Migrate options from one version to another
     * 
     * @param string $fromVersion Source version
     * @param string $toVersion Target version
     * @return bool True on success, false on failure
     */
    public function migrate(string $fromVersion, string $toVersion): bool
    {
        if ($this->migrator === null) {
            $this->migrator = new OptionsMigrator($this);
        }
        
        return $this->migrator->migrate($fromVersion, $toVersion);
    }
    
    /**
     * Clear options cache
     * 
     * @return void
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
    
    /**
     * Set default values
     * 
     * @param array<string, mixed> $defaults Default values
     * @return void
     */
    public function setDefaults(array $defaults): void
    {
        $this->defaults = array_merge($this->defaults, $defaults);
    }
    
    /**
     * Normalize option key (ensure prefix)
     * 
     * @param string $key Option key
     * @return string Normalized key with prefix
     */
    private function normalizeKey(string $key): string
    {
        // If key already has prefix, return as-is
        if (strpos($key, $this->prefix) === 0) {
            return $key;
        }
        
        return $this->prefix . $key;
    }
    
    /**
     * Get option prefix
     * 
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }
    
    /**
     * Set type hint for an option
     * 
     * @param string $key Option key
     * @param string $type Type hint ('string', 'int', 'bool', 'array', 'object')
     * @return void
     */
    public function setTypeHint(string $key, string $type): void
    {
        $key = $this->normalizeKey($key);
        $this->typeHints[$key] = $type;
    }
    
    /**
     * Get type hint for an option
     * 
     * @param string $key Option key
     * @return string|null Type hint or null if not set
     */
    public function getTypeHint(string $key): ?string
    {
        $key = $this->normalizeKey($key);
        return $this->typeHints[$key] ?? null;
    }
    
    /**
     * Set multiple type hints at once
     * 
     * @param array<string, string> $typeHints Array of key => type
     * @return void
     */
    public function setTypeHints(array $typeHints): void
    {
        foreach ($typeHints as $key => $type) {
            $this->setTypeHint($key, $type);
        }
    }
    
    /**
     * Get option with type casting
     * 
     * @template T
     * @param string $key Option key
     * @param mixed $default Default value
     * @param string|null $type Optional type hint (overrides registered type hint)
     * @return T|mixed
     */
    public function getTyped(string $key, $default = null, ?string $type = null)
    {
        $value = $this->get($key, $default);
        
        $type = $type ?? $this->getTypeHint($key);
        
        if ($type !== null) {
            $value = $this->castValue($value, $type);
        }
        
        return $value;
    }
    
    /**
     * Cast value to specified type
     * 
     * @param mixed $value Value to cast
     * @param string $type Target type
     * @return mixed Casted value
     */
    private function castValue($value, string $type)
    {
        switch (strtolower($type)) {
            case 'string':
                return (string) $value;
            case 'int':
            case 'integer':
                return (int) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'array':
                return (array) $value;
            case 'object':
                return (object) $value;
            default:
                return $value;
        }
    }
    
    /**
     * Validate option value
     * 
     * @param string $key Option key
     * @param mixed $value Value to validate
     * @return bool True if valid, false otherwise
     */
    private function validateValue(string $key, $value): bool
    {
        if ($this->validator === null) {
            return true;
        }
        
        $type = $this->getTypeHint($key);
        
        if ($type !== null) {
            // Use validateValue method for single value validation
            return $this->validator->validateValue($value, $type);
        }
        
        return true;
    }
    
    /**
     * Sanitize option value
     * 
     * @param string $key Option key
     * @param mixed $value Value to sanitize
     * @return mixed Sanitized value
     */
    private function sanitizeValue(string $key, $value)
    {
        if ($this->sanitizer === null) {
            return $value;
        }
        
        $type = $this->getTypeHint($key);
        
        if ($type !== null) {
            return $this->sanitizer->sanitizeValue($value, $type);
        }
        
        return $value;
    }
    
    /**
     * Invalidate cache for a specific option
     * 
     * @param string $key Option key
     * @return void
     */
    public function invalidateCache(string $key): void
    {
        $key = $this->normalizeKey($key);
        
        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
        }
        
        do_action('fp_ps_options_cache_invalidated', $key);
    }
}













