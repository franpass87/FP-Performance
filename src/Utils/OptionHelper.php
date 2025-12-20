<?php

namespace FP\PerfSuite\Utils;

/**
 * Helper class per gestire opzioni WordPress in modo consistente
 * 
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 */
class OptionHelper
{
    /**
     * Verifica se un'opzione è abilitata
     * 
     * @param string $optionName Nome dell'opzione
     * @param string $key Chiave da controllare (default: 'enabled')
     * @param bool $fallback Valore di fallback se l'opzione non esiste
     * @return bool
     */
    public static function isEnabled(string $optionName, string $key = 'enabled', bool $fallback = false): bool
    {
        $option = get_option($optionName, []);
        
        if (!is_array($option)) {
            return $fallback;
        }
        
        return !empty($option[$key]) ?? $fallback;
    }
    
    /**
     * Ottiene un'opzione con default
     * 
     * @param string $optionName Nome dell'opzione
     * @param mixed $default Valore di default
     * @return mixed
     */
    public static function get(string $optionName, $default = [])
    {
        return get_option($optionName, $default);
    }
    
    /**
     * Ottiene un valore annidato da un'opzione
     * 
     * @param string $optionName Nome dell'opzione
     * @param string $path Path separato da punti (es: 'settings.enabled')
     * @param mixed $default Valore di default
     * @return mixed
     */
    public static function getNested(string $optionName, string $path, $default = null)
    {
        $option = get_option($optionName, []);
        
        if (!is_array($option)) {
            return $default;
        }
        
        $keys = explode('.', $path);
        $value = $option;
        
        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    /**
     * Verifica se un'opzione esiste e ha un valore non vuoto
     * 
     * @param string $optionName Nome dell'opzione
     * @return bool
     */
    public static function exists(string $optionName): bool
    {
        return get_option($optionName) !== false;
    }
    
    /**
     * Verifica se un'opzione ha un valore specifico
     * 
     * @param string $optionName Nome dell'opzione
     * @param string $key Chiave da controllare
     * @param mixed $value Valore da confrontare
     * @return bool
     */
    public static function hasValue(string $optionName, string $key, $value): bool
    {
        $option = get_option($optionName, []);
        
        if (!is_array($option)) {
            return false;
        }
        
        return isset($option[$key]) && $option[$key] === $value;
    }
}
















