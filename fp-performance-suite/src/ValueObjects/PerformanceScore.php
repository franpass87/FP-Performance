<?php

namespace FP\PerfSuite\ValueObjects;

/**
 * Performance Score Value Object
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
final class PerformanceScore
{
    public const MAX_SCORE = 100;
    public const GRADE_A = 90;
    public const GRADE_B = 75;
    public const GRADE_C = 60;
    public const GRADE_D = 45;

    public readonly int $total;
    public readonly array $breakdown;
    public readonly array $suggestions;

    public function __construct(int $total, array $breakdown, array $suggestions = [])
    {
        if ($total < 0 || $total > self::MAX_SCORE) {
            throw new \InvalidArgumentException(
                sprintf('Score must be between 0 and %d', self::MAX_SCORE)
            );
        }

        $this->total = $total;
        $this->breakdown = $breakdown;
        $this->suggestions = $suggestions;
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['total'] ?? 0),
            $data['breakdown'] ?? [],
            $data['suggestions'] ?? []
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'breakdown' => $this->breakdown,
            'suggestions' => $this->suggestions,
            'grade' => $this->getGrade(),
            'color' => $this->getColor(),
        ];
    }

    /**
     * Get letter grade
     */
    public function getGrade(): string
    {
        if ($this->total >= self::GRADE_A) {
            return 'A';
        }
        if ($this->total >= self::GRADE_B) {
            return 'B';
        }
        if ($this->total >= self::GRADE_C) {
            return 'C';
        }
        if ($this->total >= self::GRADE_D) {
            return 'D';
        }
        return 'F';
    }

    /**
     * Get color for score
     */
    public function getColor(): string
    {
        if ($this->total >= self::GRADE_A) {
            return '#10b981'; // Green
        }
        if ($this->total >= self::GRADE_B) {
            return '#3b82f6'; // Blue
        }
        if ($this->total >= self::GRADE_C) {
            return '#f59e0b'; // Orange
        }
        if ($this->total >= self::GRADE_D) {
            return '#ef4444'; // Red
        }
        return '#991b1b'; // Dark Red
    }

    /**
     * Get status message
     */
    public function getStatusMessage(): string
    {
        if ($this->total >= self::GRADE_A) {
            return __('Excellent performance!', 'fp-performance-suite');
        }
        if ($this->total >= self::GRADE_B) {
            return __('Good performance', 'fp-performance-suite');
        }
        if ($this->total >= self::GRADE_C) {
            return __('Acceptable performance', 'fp-performance-suite');
        }
        if ($this->total >= self::GRADE_D) {
            return __('Needs improvement', 'fp-performance-suite');
        }
        return __('Poor performance - immediate action required', 'fp-performance-suite');
    }

    /**
     * Check if score is passing
     */
    public function isPassing(): bool
    {
        return $this->total >= self::GRADE_C;
    }

    /**
     * Get emoji representation
     */
    public function getEmoji(): string
    {
        if ($this->total >= self::GRADE_A) {
            return '🎉';
        }
        if ($this->total >= self::GRADE_B) {
            return '✅';
        }
        if ($this->total >= self::GRADE_C) {
            return '⚠️';
        }
        if ($this->total >= self::GRADE_D) {
            return '🔴';
        }
        return '🚨';
    }

    /**
     * Get percentage
     */
    public function getPercentage(): int
    {
        return $this->total;
    }

    /**
     * Compare with another score
     */
    public function compareTo(PerformanceScore $other): int
    {
        return $this->total <=> $other->total;
    }

    /**
     * Check if improved from another score
     */
    public function isImprovedFrom(PerformanceScore $other): bool
    {
        return $this->total > $other->total;
    }

    /**
     * Get improvement delta
     */
    public function getDelta(PerformanceScore $other): int
    {
        return $this->total - $other->total;
    }
}
