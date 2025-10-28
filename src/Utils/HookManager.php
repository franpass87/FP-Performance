<?php

namespace FP\PerfSuite\Utils;

/**
 * Hook Manager per prevenire la doppia registrazione degli hook WordPress
 * 
 * @package FP\PerfSuite\Utils
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class HookManager
{
    /** @var array<string, bool> Hook già registrati */
    private static array $registeredHooks = [];
    
    /** @var array<string, bool> Filtri già registrati */
    private static array $registeredFilters = [];
    
    /**
     * Registra un'azione solo se non è già stata registrata
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return bool True se registrato, false se già registrato
     */
    public static function addActionOnce(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $key = self::getHookKey($hook, $callback, $priority, $acceptedArgs);
        
        if (isset(self::$registeredHooks[$key])) {
            return false; // Già registrato
        }
        
        add_action($hook, $callback, $priority, $acceptedArgs);
        self::$registeredHooks[$key] = true;
        
        return true;
    }
    
    /**
     * Registra un filtro solo se non è già stato registrato
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return bool True se registrato, false se già registrato
     */
    public static function addFilterOnce(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $key = self::getHookKey($hook, $callback, $priority, $acceptedArgs);
        
        if (isset(self::$registeredFilters[$key])) {
            return false; // Già registrato
        }
        
        add_filter($hook, $callback, $priority, $acceptedArgs);
        self::$registeredFilters[$key] = true;
        
        return true;
    }
    
    /**
     * Verifica se un hook è già stato registrato
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return bool True se già registrato
     */
    public static function isActionRegistered(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $key = self::getHookKey($hook, $callback, $priority, $acceptedArgs);
        return isset(self::$registeredHooks[$key]);
    }
    
    /**
     * Verifica se un filtro è già stato registrato
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return bool True se già registrato
     */
    public static function isFilterRegistered(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        $key = self::getHookKey($hook, $callback, $priority, $acceptedArgs);
        return isset(self::$registeredFilters[$key]);
    }
    
    /**
     * Genera una chiave unica per l'hook
     * 
     * @param string $hook Hook name
     * @param callable $callback Callback function
     * @param int $priority Priority
     * @param int $acceptedArgs Number of accepted arguments
     * @return string Chiave unica
     */
    private static function getHookKey(string $hook, callable $callback, int $priority, int $acceptedArgs): string
    {
        $callbackString = '';
        
        if (is_array($callback)) {
            $callbackString = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            $callbackString .= '::' . $callback[1];
        } elseif (is_string($callback)) {
            $callbackString = $callback;
        } elseif (is_object($callback)) {
            $callbackString = get_class($callback);
        }
        
        return md5($hook . '|' . $callbackString . '|' . $priority . '|' . $acceptedArgs);
    }
    
    /**
     * Resetta tutti gli hook registrati (per debug)
     */
    public static function reset(): void
    {
        self::$registeredHooks = [];
        self::$registeredFilters = [];
    }
    
    /**
     * Ottiene il numero di hook registrati
     * 
     * @return array{actions: int, filters: int}
     */
    public static function getStats(): array
    {
        return [
            'actions' => count(self::$registeredHooks),
            'filters' => count(self::$registeredFilters),
        ];
    }
}
