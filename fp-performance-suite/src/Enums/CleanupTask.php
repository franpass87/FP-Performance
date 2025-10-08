<?php

namespace FP\PerfSuite\Enums;

/**
 * Database Cleanup Task Enumeration
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
enum CleanupTask: string
{
    case REVISIONS = 'revisions';
    case AUTO_DRAFTS = 'auto_drafts';
    case TRASH_POSTS = 'trash_posts';
    case SPAM_COMMENTS = 'spam_comments';
    case EXPIRED_TRANSIENTS = 'expired_transients';
    case ORPHAN_POSTMETA = 'orphan_postmeta';
    case ORPHAN_TERMMETA = 'orphan_termmeta';
    case ORPHAN_USERMETA = 'orphan_usermeta';
    case OPTIMIZE_TABLES = 'optimize_tables';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::REVISIONS => __('Post Revisions', 'fp-performance-suite'),
            self::AUTO_DRAFTS => __('Auto-drafts', 'fp-performance-suite'),
            self::TRASH_POSTS => __('Trashed Posts', 'fp-performance-suite'),
            self::SPAM_COMMENTS => __('Spam Comments', 'fp-performance-suite'),
            self::EXPIRED_TRANSIENTS => __('Expired Transients', 'fp-performance-suite'),
            self::ORPHAN_POSTMETA => __('Orphan Post Meta', 'fp-performance-suite'),
            self::ORPHAN_TERMMETA => __('Orphan Term Meta', 'fp-performance-suite'),
            self::ORPHAN_USERMETA => __('Orphan User Meta', 'fp-performance-suite'),
            self::OPTIMIZE_TABLES => __('Optimize Tables', 'fp-performance-suite'),
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::REVISIONS => __('Remove old post revisions to save space', 'fp-performance-suite'),
            self::AUTO_DRAFTS => __('Delete auto-saved drafts', 'fp-performance-suite'),
            self::TRASH_POSTS => __('Permanently delete trashed posts', 'fp-performance-suite'),
            self::SPAM_COMMENTS => __('Remove spam and trashed comments', 'fp-performance-suite'),
            self::EXPIRED_TRANSIENTS => __('Clean up expired transient options', 'fp-performance-suite'),
            self::ORPHAN_POSTMETA => __('Remove metadata for deleted posts', 'fp-performance-suite'),
            self::ORPHAN_TERMMETA => __('Remove metadata for deleted terms', 'fp-performance-suite'),
            self::ORPHAN_USERMETA => __('Remove metadata for deleted users', 'fp-performance-suite'),
            self::OPTIMIZE_TABLES => __('Run OPTIMIZE TABLE on all tables', 'fp-performance-suite'),
        };
    }

    /**
     * Get risk level
     */
    public function riskLevel(): string
    {
        return match ($this) {
            self::REVISIONS, self::AUTO_DRAFTS, self::EXPIRED_TRANSIENTS => 'green',
            self::TRASH_POSTS, self::SPAM_COMMENTS => 'amber',
            self::ORPHAN_POSTMETA, self::ORPHAN_TERMMETA, self::ORPHAN_USERMETA, self::OPTIMIZE_TABLES => 'red',
        };
    }

    /**
     * Check if task is safe (low risk)
     */
    public function isSafe(): bool
    {
        return $this->riskLevel() === 'green';
    }

    /**
     * Get recommended for scheduled cleanup
     */
    public static function recommendedForScheduled(): array
    {
        return [
            self::REVISIONS,
            self::AUTO_DRAFTS,
            self::TRASH_POSTS,
            self::SPAM_COMMENTS,
            self::EXPIRED_TRANSIENTS,
        ];
    }

    /**
     * Get all tasks
     */
    public static function all(): array
    {
        return [
            self::REVISIONS,
            self::AUTO_DRAFTS,
            self::TRASH_POSTS,
            self::SPAM_COMMENTS,
            self::EXPIRED_TRANSIENTS,
            self::ORPHAN_POSTMETA,
            self::ORPHAN_TERMMETA,
            self::ORPHAN_USERMETA,
            self::OPTIMIZE_TABLES,
        ];
    }

    /**
     * Get tasks by risk level
     */
    public static function byRiskLevel(string $level): array
    {
        return array_filter(self::all(), function ($task) use ($level) {
            return $task->riskLevel() === $level;
        });
    }
}
