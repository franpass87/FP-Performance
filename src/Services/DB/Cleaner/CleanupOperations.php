<?php

namespace FP\PerfSuite\Services\DB\Cleaner;

/**
 * Operazioni di pulizia del database
 * 
 * @package FP\PerfSuite\Services\DB\Cleaner
 * @author Francesco Passeri
 */
class CleanupOperations
{
    /**
     * Pulisce le revisioni
     */
    public function cleanRevisions(int $limit = 500): int
    {
        global $wpdb;
        
        $revisions = $wpdb->get_results($wpdb->prepare("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'revision' 
            AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
            LIMIT %d
        ", $limit));
        
        $cleaned = 0;
        foreach ($revisions as $revision) {
            if (wp_delete_post($revision->ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }

    /**
     * Pulisce i commenti spam
     */
    public function cleanSpam(int $limit = 500): int
    {
        global $wpdb;
        
        $spam_comments = $wpdb->get_results($wpdb->prepare("
            SELECT comment_ID FROM {$wpdb->comments} 
            WHERE comment_approved = 'spam'
            LIMIT %d
        ", $limit));
        
        $cleaned = 0;
        foreach ($spam_comments as $comment) {
            if (wp_delete_comment($comment->comment_ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }

    /**
     * Pulisce i post nel cestino
     */
    public function cleanTrash(int $limit = 500): int
    {
        global $wpdb;
        
        $trash_posts = $wpdb->get_results($wpdb->prepare("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_status = 'trash' 
            AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
            LIMIT %d
        ", $limit));
        
        $cleaned = 0;
        foreach ($trash_posts as $post) {
            if (wp_delete_post($post->ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }

    /**
     * Pulisce le bozze automatiche
     */
    public function cleanAutoDrafts(): int
    {
        global $wpdb;
        
        $auto_drafts = $wpdb->get_results("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_status = 'auto-draft' 
            AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)
            LIMIT 500
        ");
        
        $cleaned = 0;
        foreach ($auto_drafts as $draft) {
            if (wp_delete_post($draft->ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }

    /**
     * Pulisce i transient scaduti
     */
    public function cleanExpiredTransients(): int
    {
        global $wpdb;
        
        $cleaned = $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_timeout_%'
            AND option_value < UNIX_TIMESTAMP()
        ");
        
        $wpdb->query("
            DELETE FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_%'
            AND option_name NOT LIKE '_transient_timeout_%'
            AND option_name NOT IN (
                SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
                FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_timeout_%'
            )
        ");
        
        return (int) $cleaned;
    }

    /**
     * Pulisce i postmeta orfani
     */
    public function cleanOrphanPostmeta(): int
    {
        global $wpdb;
        
        return (int) $wpdb->query("
            DELETE pm FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
        ");
    }

    /**
     * Pulisce i termmeta orfani
     */
    public function cleanOrphanTermmeta(): int
    {
        global $wpdb;
        
        return (int) $wpdb->query("
            DELETE tm FROM {$wpdb->termmeta} tm
            LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
            WHERE t.term_id IS NULL
        ");
    }

    /**
     * Pulisce i usermeta orfani
     */
    public function cleanOrphanUsermeta(): int
    {
        global $wpdb;
        
        return (int) $wpdb->query("
            DELETE um FROM {$wpdb->usermeta} um
            LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
            WHERE u.ID IS NULL
        ");
    }

    /**
     * Ottimizza le tabelle del database
     */
    public function optimizeTables(): int
    {
        global $wpdb;
        
        $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
        $optimized = 0;
        
        foreach ($tables as $table) {
            $tableName = $table[0];
            $wpdb->query("OPTIMIZE TABLE `{$tableName}`");
            $optimized++;
        }
        
        return $optimized;
    }
}















