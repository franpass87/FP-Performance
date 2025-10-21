<?php

namespace FP\PerfSuite\Enums;

/**
 * Hosting Preset Enumeration
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
enum HostingPreset: string
{
    case GENERAL = 'generale';
    case IONOS = 'ionos';
    case ARUBA = 'aruba';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::GENERAL => __('General', 'fp-performance-suite'),
            self::IONOS => __('IONOS', 'fp-performance-suite'),
            self::ARUBA => __('Aruba', 'fp-performance-suite'),
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::GENERAL => __('Balanced settings for most shared hosting providers', 'fp-performance-suite'),
            self::IONOS => __('Optimized for IONOS hosting environment', 'fp-performance-suite'),
            self::ARUBA => __('Optimized for Aruba hosting environment', 'fp-performance-suite'),
        };
    }

    /**
     * Get configuration array
     */
    public function config(): array
    {
        return match ($this) {
            self::GENERAL => [
                'page_cache' => ['enabled' => true, 'ttl' => 3600],
                'browser_cache' => ['enabled' => true],
                'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false],
                'webp' => ['enabled' => true, 'quality' => 75, 'lossy' => true],
                'db' => ['batch' => 200],
                'heartbeat' => 60,
            ],
            self::IONOS => [
                'page_cache' => ['enabled' => true, 'ttl' => 1800],
                'browser_cache' => ['enabled' => false],
                'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false, 'async_js' => false],
                'webp' => ['enabled' => true, 'quality' => 80],
                'db' => ['batch' => 150],
                'heartbeat' => 80,
            ],
            self::ARUBA => [
                'page_cache' => ['enabled' => true, 'ttl' => 900],
                'browser_cache' => ['enabled' => true],
                'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false, 'preload' => []],
                'webp' => ['enabled' => true, 'quality' => 70],
                'db' => ['batch' => 100],
                'heartbeat' => 90,
            ],
        };
    }

    /**
     * Get all available presets
     */
    public static function all(): array
    {
        return [
            self::GENERAL,
            self::IONOS,
            self::ARUBA,
        ];
    }

    /**
     * Get preset from string
     */
    public static function fromString(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Check if preset is recommended for current environment
     */
    public function isRecommended(): bool
    {
        $serverSoftware = strtolower($_SERVER['SERVER_SOFTWARE'] ?? '');

        return match ($this) {
            self::IONOS => strpos($serverSoftware, 'ionos') !== false,
            self::ARUBA => strpos($serverSoftware, 'aruba') !== false,
            self::GENERAL => true,
        };
    }
}
