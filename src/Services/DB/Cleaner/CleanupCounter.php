<?php

namespace FP\PerfSuite\Services\DB\Cleaner;

/**
 * Conta gli elementi da pulire
 * 
 * @package FP\PerfSuite\Services\DB\Cleaner
 * @author Francesco Passeri
 */
class CleanupCounter
{
    /**
     * Conta le revisioni
     */
    public function countRevisions(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'revision'
            AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }

    /**
     * Conta i commenti spam
     */
    public function countSpam(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->comments} 
            WHERE comment_approved = 'spam'
        ");
    }

    /**
     * Conta i post nel cestino
     */
    public function countTrash(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_status = 'trash'
            AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
    }

    /**
     * Conta le bozze automatiche
     */
    public function countAutoDrafts(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_status = 'auto-draft'
            AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
    }

    /**
     * Conta i transient scaduti
     */
    public function countExpiredTransients(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_timeout_%'
            AND option_value < UNIX_TIMESTAMP()
        ");
    }

    /**
     * Conta i postmeta orfani
     */
    public function countOrphanPostmeta(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
        ");
    }

    /**
     * Conta i termmeta orfani
     */
    public function countOrphanTermmeta(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->termmeta} tm
            LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
            WHERE t.term_id IS NULL
        ");
    }

    /**
     * Conta i usermeta orfani
     */
    public function countOrphanUsermeta(): int
    {
        global $wpdb;
        
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->usermeta} um
            LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
            WHERE u.ID IS NULL
        ");
    }
}















