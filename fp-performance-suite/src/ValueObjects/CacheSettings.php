<?php

namespace FP\PerfSuite\ValueObjects;

/**
 * Cache Settings Value Object
 * 
 * Immutable object representing cache configuration
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
final class CacheSettings
{
    public const MIN_TTL = 60;
    public const MAX_TTL = 86400 * 30; // 30 days
    public const DEFAULT_TTL = 3600;

    public readonly bool $enabled;
    public readonly int $ttl;

    public function __construct(bool $enabled, int $ttl)
    {
        if ($ttl < 0) {
            throw new \InvalidArgumentException('TTL cannot be negative');
        }

        if ($enabled && $ttl > 0 && $ttl < self::MIN_TTL) {
            throw new \InvalidArgumentException(
                sprintf('TTL must be at least %d seconds when cache is enabled', self::MIN_TTL)
            );
        }

        if ($ttl > self::MAX_TTL) {
            throw new \InvalidArgumentException(
                sprintf('TTL cannot exceed %d seconds', self::MAX_TTL)
            );
        }

        $this->enabled = $enabled;
        $this->ttl = $ttl;
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            !empty($data['enabled']),
            (int)($data['ttl'] ?? self::DEFAULT_TTL)
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'ttl' => $this->ttl,
        ];
    }

    /**
     * Create with updated enabled status
     */
    public function withEnabled(bool $enabled): self
    {
        return new self($enabled, $this->ttl);
    }

    /**
     * Create with updated TTL
     */
    public function withTtl(int $ttl): self
    {
        return new self($this->enabled, $ttl);
    }

    /**
     * Check if cache is active (enabled with valid TTL)
     */
    public function isActive(): bool
    {
        return $this->enabled && $this->ttl > 0;
    }

    /**
     * Get TTL in human readable format
     */
    public function getTtlHumanReadable(): string
    {
        if ($this->ttl < 60) {
            return sprintf('%d seconds', $this->ttl);
        }

        if ($this->ttl < 3600) {
            return sprintf('%d minutes', round($this->ttl / 60));
        }

        if ($this->ttl < 86400) {
            return sprintf('%d hours', round($this->ttl / 3600));
        }

        return sprintf('%d days', round($this->ttl / 86400));
    }
}
