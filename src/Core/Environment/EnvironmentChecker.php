<?php

/**
 * Environment Checker
 * 
 * Checks PHP version, WordPress version, extensions, and system requirements
 *
 * @package FP\PerfSuite\Core\Environment
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Environment;

class EnvironmentChecker
{
    /** @var array<string> Required PHP extensions */
    private const REQUIRED_EXTENSIONS = ['json', 'mbstring', 'fileinfo'];
    
    /** @var string Minimum PHP version */
    private const MIN_PHP_VERSION = '7.4.0';
    
    /** @var string Minimum WordPress version */
    private const MIN_WP_VERSION = '5.8';
    
    /**
     * Check if environment meets all requirements
     * 
     * @return bool True if all requirements met
     */
    public function check(): bool
    {
        return $this->checkAll();
    }
    
    /**
     * Check if environment meets all requirements
     * 
     * @return bool True if all requirements met
     */
    public function checkAll(): bool
    {
        return $this->checkPhpVersion()
            && $this->checkWordPressVersion()
            && $this->checkExtensions()
            && $this->checkFunctions();
    }
    
    /**
     * Check PHP version
     * 
     * @return bool
     */
    public function checkPhpVersion(): bool
    {
        return version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '>=');
    }
    
    /**
     * Check WordPress version
     * 
     * @return bool
     */
    public function checkWordPressVersion(): bool
    {
        global $wp_version;
        return version_compare($wp_version, self::MIN_WP_VERSION, '>=');
    }
    
    /**
     * Check required PHP extensions
     * 
     * @return bool
     */
    public function checkExtensions(): bool
    {
        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            if (!extension_loaded($ext)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check required WordPress functions
     * 
     * @return bool
     */
    public function checkFunctions(): bool
    {
        $required = [
            'add_action',
            'get_option',
            'update_option',
            'wp_upload_dir',
        ];
        
        foreach ($required as $func) {
            if (!function_exists($func)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get all environment errors
     * 
     * @return array<string> Array of error messages
     */
    public function getErrors(): array
    {
        $errors = [];
        
        if (!$this->checkPhpVersion()) {
            $errors[] = sprintf(
                __('PHP %s or higher is required. Current version: %s', 'fp-performance-suite'),
                self::MIN_PHP_VERSION,
                PHP_VERSION
            );
        }
        
        if (!$this->checkWordPressVersion()) {
            global $wp_version;
            $errors[] = sprintf(
                __('WordPress %s or higher is required. Current version: %s', 'fp-performance-suite'),
                self::MIN_WP_VERSION,
                $wp_version
            );
        }
        
        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            if (!extension_loaded($ext)) {
                $errors[] = sprintf(
                    __('Required PHP extension not found: %s', 'fp-performance-suite'),
                    $ext
                );
            }
        }
        
        return $errors;
    }
    
    /**
     * Check if current request is admin
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return is_admin();
    }
    
    /**
     * Check if current request is frontend
     * 
     * @return bool
     */
    public function isFrontend(): bool
    {
        return !is_admin() && !$this->isAjax() && !$this->isCli();
    }
    
    /**
     * Check if current request is AJAX
     * 
     * @return bool
     */
    public function isAjax(): bool
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }
    
    /**
     * Check if current request is WP-CLI
     * 
     * @return bool
     */
    public function isCli(): bool
    {
        return defined('WP_CLI') && WP_CLI;
    }
    
    /**
     * Check if current request is REST API
     * 
     * @return bool
     */
    public function isRest(): bool
    {
        return function_exists('wp_is_json_request') && wp_is_json_request();
    }
    
    /**
     * Check if current request is cron
     * 
     * @return bool
     */
    public function isCron(): bool
    {
        return defined('DOING_CRON') && DOING_CRON;
    }
}









