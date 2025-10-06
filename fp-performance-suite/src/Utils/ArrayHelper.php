<?php

namespace FP\PerfSuite\Utils;

/**
 * Array manipulation helpers
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ArrayHelper
{
    /**
     * Get value from array using dot notation
     * 
     * @example get(['user' => ['name' => 'John']], 'user.name') => 'John'
     */
    public static function get(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        if (strpos($key, '.') === false) {
            return $default;
        }

        $keys = explode('.', $key);
        $value = $array;

        foreach ($keys as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set value in array using dot notation
     */
    public static function set(array &$array, string $key, $value): void
    {
        if (strpos($key, '.') === false) {
            $array[$key] = $value;
            return;
        }

        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $current[$segment] = $value;
                return;
            }

            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }
    }

    /**
     * Filter array by keys
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Remove keys from array
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Pluck an array of values from array
     */
    public static function pluck(array $array, string $column, ?string $key = null): array
    {
        $results = [];

        foreach ($array as $item) {
            $value = is_object($item) ? ($item->$column ?? null) : ($item[$column] ?? null);
            
            if ($key !== null) {
                $keyValue = is_object($item) ? ($item->$key ?? null) : ($item[$key] ?? null);
                $results[$keyValue] = $value;
            } else {
                $results[] = $value;
            }
        }

        return $results;
    }

    /**
     * Flatten multi-dimensional array
     */
    public static function flatten(array $array, int $depth = PHP_INT_MAX): array
    {
        $result = [];

        foreach ($array as $item) {
            if (!is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, self::flatten($item, $depth - 1));
            }
        }

        return $result;
    }

    /**
     * Group array items by key
     */
    public static function groupBy(array $array, string $key): array
    {
        $groups = [];

        foreach ($array as $item) {
            $groupKey = is_object($item) ? ($item->$key ?? 'other') : ($item[$key] ?? 'other');
            
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
            }
            
            $groups[$groupKey][] = $item;
        }

        return $groups;
    }

    /**
     * Check if array is associative
     */
    public static function isAssoc(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Recursively merge arrays
     */
    public static function mergeRecursive(array ...$arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                    $result[$key] = self::mergeRecursive($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }

    /**
     * Sort array by column
     */
    public static function sortBy(array $array, string $column, bool $descending = false): array
    {
        usort($array, function($a, $b) use ($column, $descending) {
            $aVal = is_object($a) ? ($a->$column ?? null) : ($a[$column] ?? null);
            $bVal = is_object($b) ? ($b->$column ?? null) : ($b[$column] ?? null);
            
            $comparison = $aVal <=> $bVal;
            return $descending ? -$comparison : $comparison;
        });

        return $array;
    }
}
