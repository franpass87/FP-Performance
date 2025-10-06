<?php

namespace FP\PerfSuite\Enums;

/**
 * CDN Provider Enumeration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
enum CdnProvider: string
{
    case CUSTOM = 'custom';
    case CLOUDFLARE = 'cloudflare';
    case BUNNYCDN = 'bunnycdn';
    case STACKPATH = 'stackpath';
    case CLOUDFRONT = 'cloudfront';
    case FASTLY = 'fastly';

    /**
     * Get provider name
     */
    public function name(): string
    {
        return match($this) {
            self::CUSTOM => __('Custom CDN', 'fp-performance-suite'),
            self::CLOUDFLARE => 'CloudFlare',
            self::BUNNYCDN => 'BunnyCDN',
            self::STACKPATH => 'StackPath',
            self::CLOUDFRONT => 'Amazon CloudFront',
            self::FASTLY => 'Fastly',
        };
    }

    /**
     * Get setup URL
     */
    public function setupUrl(): string
    {
        return match($this) {
            self::CUSTOM => '',
            self::CLOUDFLARE => 'https://cloudflare.com',
            self::BUNNYCDN => 'https://bunny.net',
            self::STACKPATH => 'https://stackpath.com',
            self::CLOUDFRONT => 'https://aws.amazon.com/cloudfront/',
            self::FASTLY => 'https://fastly.com',
        };
    }

    /**
     * Check if supports API purge
     */
    public function supportsApiPurge(): bool
    {
        return match($this) {
            self::CLOUDFLARE, self::BUNNYCDN, self::STACKPATH, self::FASTLY => true,
            self::CUSTOM, self::CLOUDFRONT => false,
        };
    }

    /**
     * Check if requires API credentials
     */
    public function requiresApiKey(): bool
    {
        return $this->supportsApiPurge();
    }

    /**
     * Get required fields for setup
     */
    public function requiredFields(): array
    {
        $base = ['url'];
        
        if ($this->requiresApiKey()) {
            $base[] = 'api_key';
        }

        if ($this === self::CLOUDFLARE || $this === self::BUNNYCDN) {
            $base[] = 'zone_id';
        }

        return $base;
    }

    /**
     * Get icon/logo
     */
    public function icon(): string
    {
        return match($this) {
            self::CUSTOM => '⚙️',
            self::CLOUDFLARE => '☁️',
            self::BUNNYCDN => '🐰',
            self::STACKPATH => '📦',
            self::CLOUDFRONT => '☁️',
            self::FASTLY => '⚡',
        };
    }
}
