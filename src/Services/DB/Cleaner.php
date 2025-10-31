<?php

namespace FP\PerfSuite\Services\DB;

class Cleaner
{
    public const CRON_HOOK = 'fp_clean_database';
    
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
     * Restituisce le impostazioni del database cleaner
     * 
     * @return array Array con le impostazioni
     */
    public function settings(): array
    {
        return [
            'clean_revisions' => $this->clean_revisions,
            'clean_spam' => $this->clean_spam,
            'clean_trash' => $this->clean_trash,
            'clean_transients' => $this->clean_transients ?? false,
            'optimize_tables' => $this->optimize_tables ?? false,
        ];
    }
    
    /**
     * Aggiorna le impostazioni del cleaner
     * 
     * @param array $settings Array con le impostazioni da aggiornare
     * @return bool True se salvato con successo
     */
    public function update(array $settings): bool
    {
        $currentSettings = get_option('fp_ps_db_cleaner_settings', []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['schedule'])) {
            $allowedSchedules = ['manual', 'daily', 'weekly', 'monthly'];
            if (!in_array($newSettings['schedule'], $allowedSchedules, true)) {
                $newSettings['schedule'] = 'manual';
            }
        }
        
        if (isset($newSettings['batch'])) {
            $newSettings['batch'] = max(10, min(10000, (int) $newSettings['batch']));
        }
        
        $result = update_option('fp_ps_db_cleaner_settings', $newSettings, false);
        
        // Aggiorna proprietà interne se presenti
        if (isset($newSettings['clean_revisions'])) {
            $this->clean_revisions = (bool) $newSettings['clean_revisions'];
        }
        if (isset($newSettings['clean_spam'])) {
            $this->clean_spam = (bool) $newSettings['clean_spam'];
        }
        if (isset($newSettings['clean_trash'])) {
            $this->clean_trash = (bool) $newSettings['clean_trash'];
        }
        
        return $result;
    }
    
    /**
     * Restituisce lo stato del database cleaner
     * 
     * @return array Array con 'enabled', 'overhead_mb' e altre informazioni
     */
    public function status(): array
    {
        global $wpdb;
        
        // Calcola l'overhead del database
        $overhead = 0;
        $tables = $wpdb->get_results('SHOW TABLE STATUS', ARRAY_A);
        if ($tables) {
            foreach ($tables as $table) {
                if (isset($table['Data_free'])) {
                    $overhead += $table['Data_free'];
                }
            }
        }
        $overhead_mb = round($overhead / 1024 / 1024, 2);
        
        return [
            'enabled' => $this->clean_revisions || $this->clean_spam || $this->clean_trash,
            'overhead_mb' => $overhead_mb,
            'clean_revisions' => $this->clean_revisions,
            'clean_spam' => $this->clean_spam,
            'clean_trash' => $this->clean_trash,
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
    }
    
    /**
     * Esegue la pulizia del database in base allo scope specificato
     * 
     * @param array $scope Array con le operazioni di pulizia da eseguire
     * @param bool $dryRun Se true, simula la pulizia senza eseguirla realmente
     * @param int|null $batch Numero massimo di elementi da processare (opzionale)
     * @return array Array con i risultati della pulizia
     */
    public function cleanup(array $scope, bool $dryRun = true, ?int $batch = null): array
    {
        global $wpdb;
        
        $results = [
            'success' => true,
            'dry_run' => $dryRun,
            'operations' => [],
            'total_cleaned' => 0,
        ];
        
        try {
            foreach ($scope as $operation) {
                $count = 0;
                
                switch ($operation) {
                    case 'revisions':
                        if ($this->clean_revisions) {
                            $count = $dryRun ? $this->countRevisions() : $this->cleanRevisions();
                        }
                        break;
                        
                    case 'spam_comments':
                        if ($this->clean_spam) {
                            $count = $dryRun ? $this->countSpam() : $this->cleanSpam();
                        }
                        break;
                        
                    case 'trash_posts':
                        if ($this->clean_trash) {
                            $count = $dryRun ? $this->countTrash() : $this->cleanTrash();
                        }
                        break;
                        
                    case 'auto_drafts':
                        $count = $dryRun ? $this->countAutoDrafts() : $this->cleanAutoDrafts();
                        break;
                        
                    case 'expired_transients':
                        $count = $dryRun ? $this->countExpiredTransients() : $this->cleanExpiredTransients();
                        break;
                        
                    case 'orphan_postmeta':
                        $count = $dryRun ? $this->countOrphanPostmeta() : $this->cleanOrphanPostmeta();
                        break;
                        
                    case 'orphan_termmeta':
                        $count = $dryRun ? $this->countOrphanTermmeta() : $this->cleanOrphanTermmeta();
                        break;
                        
                    case 'orphan_usermeta':
                        $count = $dryRun ? $this->countOrphanUsermeta() : $this->cleanOrphanUsermeta();
                        break;
                        
                    case 'optimize_tables':
                        if (!$dryRun) {
                            $count = $this->optimizeTables();
                        }
                        break;
                }
                
                $results['operations'][$operation] = $count;
                $results['total_cleaned'] += $count;
            }
        } catch (\Throwable $e) {
            $results['success'] = false;
            $results['error'] = $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Conta le revisioni da pulire
     */
    private function countRevisions(): int
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
    private function countSpam(): int
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
    private function countTrash(): int
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
    private function countAutoDrafts(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->posts} 
            WHERE post_status = 'auto-draft' 
            AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
    }
    
    /**
     * Pulisce le bozze automatiche
     */
    private function cleanAutoDrafts(): int
    {
        global $wpdb;
        $drafts = $wpdb->get_results("
            SELECT ID FROM {$wpdb->posts} 
            WHERE post_status = 'auto-draft' 
            AND post_date < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        
        $cleaned = 0;
        foreach ($drafts as $draft) {
            if (wp_delete_post($draft->ID, true)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Conta i transient scaduti
     */
    private function countExpiredTransients(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_timeout_%' 
            AND option_value < UNIX_TIMESTAMP()
        ");
    }
    
    /**
     * Pulisce i transient scaduti
     */
    private function cleanExpiredTransients(): int
    {
        global $wpdb;
        
        // Ottieni i transient scaduti
        $expired = $wpdb->get_results("
            SELECT option_name FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_timeout_%' 
            AND option_value < UNIX_TIMESTAMP()
        ");
        
        $cleaned = 0;
        foreach ($expired as $transient) {
            $name = str_replace('_transient_timeout_', '', $transient->option_name);
            if (delete_transient($name)) {
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Conta i postmeta orfani
     */
    private function countOrphanPostmeta(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
        ");
    }
    
    /**
     * Pulisce i postmeta orfani
     */
    private function cleanOrphanPostmeta(): int
    {
        global $wpdb;
        return (int) $wpdb->query("
            DELETE pm FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
        ");
    }
    
    /**
     * Conta i termmeta orfani
     */
    private function countOrphanTermmeta(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->termmeta} tm
            LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
            WHERE t.term_id IS NULL
        ");
    }
    
    /**
     * Pulisce i termmeta orfani
     */
    private function cleanOrphanTermmeta(): int
    {
        global $wpdb;
        return (int) $wpdb->query("
            DELETE tm FROM {$wpdb->termmeta} tm
            LEFT JOIN {$wpdb->terms} t ON tm.term_id = t.term_id
            WHERE t.term_id IS NULL
        ");
    }
    
    /**
     * Conta i usermeta orfani
     */
    private function countOrphanUsermeta(): int
    {
        global $wpdb;
        return (int) $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->usermeta} um
            LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
            WHERE u.ID IS NULL
        ");
    }
    
    /**
     * Pulisce i usermeta orfani
     */
    private function cleanOrphanUsermeta(): int
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
    private function optimizeTables(): int
    {
        global $wpdb;
        
        $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        $optimized = 0;
        
        foreach ($tables as $table) {
            $tableName = $table[0];
            if (strpos($tableName, $wpdb->prefix) === 0) {
                $wpdb->query("OPTIMIZE TABLE `{$tableName}`");
                $optimized++;
            }
        }
        
        return $optimized;
    }
}