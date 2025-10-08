<?php

namespace FP\PerfSuite\Enums;

/**
 * Cache Type Enumeration
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
enum CacheType: string
{
    case PAGE = 'page';
    case BROWSER = 'browser';
    case OBJECT = 'object';
    case TRANSIENT = 'transient';
    case ASSET = 'asset';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::PAGE => __('Page Cache', 'fp-performance-suite'),
            self::BROWSER => __('Browser Cache', 'fp-performance-suite'),
            self::OBJECT => __('Object Cache', 'fp-performance-suite'),
            self::TRANSIENT => __('Transient Cache', 'fp-performance-suite'),
            self::ASSET => __('Asset Cache', 'fp-performance-suite'),
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::PAGE => __('Stores rendered HTML pages on disk', 'fp-performance-suite'),
            self::BROWSER => __('Controls browser caching via headers', 'fp-performance-suite'),
            self::OBJECT => __('Caches database query results in memory', 'fp-performance-suite'),
            self::TRANSIENT => __('WordPress transient cache for temporary data', 'fp-performance-suite'),
            self::ASSET => __('Combined and minified CSS/JS files', 'fp-performance-suite'),
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match ($this) {
            self::PAGE => '📄',
            self::BROWSER => '🌐',
            self::OBJECT => '💾',
            self::TRANSIENT => '⏱️',
            self::ASSET => '🎨',
        };
    }
}
