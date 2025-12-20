<?php

/**
 * Options Bridge
 * 
 * Bridge functions to allow gradual migration from direct get_option() calls
 * to OptionsRepository. Provides helper functions that can be used during migration.
 *
 * @package FP\PerfSuite\Core\Options
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Core\Options;

/**
 * Bridge functions for options migration
 * 
 * @deprecated Use OptionsRepository directly via dependency injection
 */
class OptionsBridge
{
    /** @var OptionsRepositoryInterface|null Repository instance */
    private static ?OptionsRepositoryInterface $repository = null;
    
    /**
     * Get repository instance
     * 
     * @return OptionsRepositoryInterface
     */
    private static function getRepository(): OptionsRepositoryInterface
    {
        if (self::$repository === null) {
            // Try to get from container if available
            if (class_exists('\FP\PerfSuite\Plugin')) {
                try {
                    $container = \FP\PerfSuite\Plugin::container();
                    if ($container->has(OptionsRepositoryInterface::class)) {
                        self::$repository = $container->get(OptionsRepositoryInterface::class);
                    }
                } catch (\Throwable $e) {
                    // Fallback to creating new instance
                }
            }
            
            // Fallback: create new instance
            if (self::$repository === null) {
                self::$repository = new OptionsRepository('fp_ps_');
                $defaults = OptionsDefaults::getAll();
                self::$repository->setDefaults($defaults);
            }
        }
        
        return self::$repository;
    }
    
    /**
     * Get option value (bridge function)
     * 
     * @param string $key Option key (with or without prefix)
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return self::getRepository()->get($key, $default);
    }
    
    /**
     * Set option value (bridge function)
     * 
     * @param string $key Option key
     * @param mixed $value Option value
     * @param bool $autoload Whether to autoload
     * @return bool
     */
    public static function set(string $key, $value, bool $autoload = true): bool
    {
        return self::getRepository()->set($key, $value, $autoload);
    }
    
    /**
     * Check if option exists (bridge function)
     * 
     * @param string $key Option key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return self::getRepository()->has($key);
    }
    
    /**
     * Delete option (bridge function)
     * 
     * @param string $key Option key
     * @return bool
     */
    public static function delete(string $key): bool
    {
        return self::getRepository()->delete($key);
    }
}









