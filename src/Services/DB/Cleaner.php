<?php

namespace FP\PerfSuite\Services\DB;

class Cleaner
{
    private $clean_revisions;
    private $clean_spam;
    private $clean_trash;
    
    public function __construct($clean_revisions = true, $clean_spam = true, $clean_trash = true)
    {
        $this->clean_revisions = $clean_revisions;
        $this->clean_spam = $clean_spam;
        $this->clean_trash = $clean_trash;
    }
    
    public function init()
    {
        add_action('wp_scheduled_delete', [$this, 'cleanDatabase']);
        add_action('fp_clean_database', [$this, 'cleanDatabase']);
    }
    
    public function cleanDatabase()
    {
        $cleaned = 0;
        
        if ($this->clean_revisions) {
            $cleaned += $this->cleanRevisions();
        }
        
        if ($this->clean_spam) {
            $cleaned += $this->cleanSpam();
        }
        
        if ($this->clean_trash) {
            $cleaned += $this->cleanTrash();
        }
        
        return $cleaned;
    }
    
    private function cleanRevisions()
    {
        global $wpdb;
        
        $revisions = $wpdb->get_results("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'revision' 
            AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $cleaned = 0;
        foreach ($revisions as $revision) {
            if (wp_delete_post($revision->ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    private function cleanSpam()
    {
        global $wpdb;
        
        $spam_comments = $wpdb->get_results("
            SELECT comment_ID FROM {$wpdb->comments} 
            WHERE comment_approved = 'spam'
        ");
        
        $cleaned = 0;
        foreach ($spam_comments as $comment) {
            if (wp_delete_comment($comment->comment_ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    private function cleanTrash()
    {
        global $wpdb;
        
        $trash_posts = $wpdb->get_results("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_status = 'trash' 
            AND post_date < DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        
        $cleaned = 0;
        foreach ($trash_posts as $post) {
            if (wp_delete_post($post->ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    public function getCleanerMetrics()
    {
        global $wpdb;
        
        $revisions_count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_type = 'revision'
        ");
        
        $spam_count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->comments} 
            WHERE comment_approved = 'spam'
        ");
        
        $trash_count = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_status = 'trash'
        ");
        
        return [
            'revisions_count' => $revisions_count,
            'spam_count' => $spam_count,
            'trash_count' => $trash_count,
            'clean_revisions' => $this->clean_revisions,
            'clean_spam' => $this->clean_spam,
            'clean_trash' => $this->clean_trash
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
}