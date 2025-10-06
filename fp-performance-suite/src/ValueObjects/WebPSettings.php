<?php

namespace FP\PerfSuite\ValueObjects;

/**
 * WebP Settings Value Object
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
final class WebPSettings
{
    public const MIN_QUALITY = 1;
    public const MAX_QUALITY = 100;
    public const DEFAULT_QUALITY = 82;

    public readonly bool $enabled;
    public readonly int $quality;
    public readonly bool $keepOriginal;
    public readonly bool $lossy;

    public function __construct(
        bool $enabled,
        int $quality = self::DEFAULT_QUALITY,
        bool $keepOriginal = true,
        bool $lossy = true
    ) {
        if ($quality < self::MIN_QUALITY || $quality > self::MAX_QUALITY) {
            throw new \InvalidArgumentException(
                sprintf('Quality must be between %d and %d', self::MIN_QUALITY, self::MAX_QUALITY)
            );
        }

        $this->enabled = $enabled;
        $this->quality = $quality;
        $this->keepOriginal = $keepOriginal;
        $this->lossy = $lossy;
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            !empty($data['enabled']),
            (int)($data['quality'] ?? self::DEFAULT_QUALITY),
            $data['keep_original'] ?? true,
            $data['lossy'] ?? true
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'enabled' => $this->enabled,
            'quality' => $this->quality,
            'keep_original' => $this->keepOriginal,
            'lossy' => $this->lossy,
        ];
    }

    /**
     * Create with updated quality
     */
    public function withQuality(int $quality): self
    {
        return new self($this->enabled, $quality, $this->keepOriginal, $this->lossy);
    }

    /**
     * Create with updated enabled status
     */
    public function withEnabled(bool $enabled): self
    {
        return new self($enabled, $this->quality, $this->keepOriginal, $this->lossy);
    }

    /**
     * Get compression mode description
     */
    public function getCompressionMode(): string
    {
        return $this->lossy ? 'Lossy' : 'Lossless';
    }

    /**
     * Get quality level description
     */
    public function getQualityLevel(): string
    {
        if ($this->quality >= 90) {
            return 'Maximum';
        }
        if ($this->quality >= 80) {
            return 'High';
        }
        if ($this->quality >= 60) {
            return 'Medium';
        }
        return 'Low';
    }
}
