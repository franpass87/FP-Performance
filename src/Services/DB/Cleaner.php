<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Utils\Logger;
use FP\PerfSuite\Services\DB\Cleaner\CleanupOperations;
use FP\PerfSuite\Services\DB\Cleaner\CleanupCounter;
use FP\PerfSuite\Services\DB\Cleaner\SchedulerManager;

class Cleaner
{
    public const CRON_HOOK = 'fp_clean_database';
    private const LAST_RUN_KEY = 'fp_ps_db_last_run';
    private const SETTINGS_KEY = 'fp_ps_db_cleaner_settings';
    private const DB_KEY = 'fp_ps_db';
    
    private $clean_revisions;
    private $clean_spam;
    private $clean_trash;
    private int $lastRun = 0;
    private CleanupOperations $operations;
    private CleanupCounter $counter;
    private SchedulerManager $scheduler;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;
    
    public function __construct($clean_revisions = true, $clean_spam = true, $clean_trash = true, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->clean_revisions = $clean_revisions;
        $this->clean_spam = $clean_spam;
        $this->clean_trash = $clean_trash;
        $this->optionsRepo = $optionsRepo;
        $this->lastRun = (int) $this->getOption(self::LAST_RUN_KEY, 0);
        
        $this->operations = new CleanupOperations();
        $this->counter = new CleanupCounter();
        $this->scheduler = new SchedulerManager($this->optionsRepo);
    }
    
    /**
     * Helper method per ottenere opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = [])
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option($key, $default);
    }
    
    /**
     * Helper method per salvare opzioni con fallback
     * 
     * @param string $key Option key
     * @param mixed $value Value to save
     * @return bool
     */
    private function setOption(string $key, $value): bool
    {
        if ($this->optionsRepo !== null) {
            $this->optionsRepo->set($key, $value);
            return true;
        }
        
        // Fallback to direct option call for backward compatibility
        return update_option($key, $value, false);
    }
    
    public function init()
    {
        add_action('wp_scheduled_delete', [$this, 'cleanDatabase']);
        add_action('fp_clean_database', [$this, 'cleanDatabase']);
        add_filter('cron_schedules', [$this->scheduler, 'registerCronSchedules']);
    }
    
    public function cleanDatabase()
    {
        $scope = $this->scheduler->getScheduledScope();
        $results = $this->cleanup($scope, false, $this->scheduler->getBatch());

        if (!empty($this->getDbOption('optimization')['optimize_on_cleanup'])) {
            $this->cleanup(['optimize_tables'], false, $this->batch);
        }

        $this->pruneDebugLog();

        return $results['total_cleaned'] ?? 0;
    }
    
    // Metodi cleanRevisions(), cleanSpam(), cleanTrash() rimossi - ora gestiti da CleanupOperations
    
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
            'schedule' => $this->schedule,
            'batch' => $this->batch,
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
        $currentSettings = $this->getOption(self::SETTINGS_KEY, []);
        $newSettings = array_merge($currentSettings, $settings);
        
        // Validazione
        if (isset($newSettings['schedule'])) {
            $allowedSchedules = ['manual', 'daily', 'weekly', 'monthly'];
            if (!in_array($newSettings['schedule'], $allowedSchedules, true)) {
                $newSettings['schedule'] = 'manual';
            }
        }
        
        if (isset($newSettings['batch'])) {
            $newSettings['batch'] = max(50, min(1000, (int) $newSettings['batch']));
        }
        
        $result = $this->setOption(self::SETTINGS_KEY, $newSettings);
        
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

        if (isset($newSettings['schedule'])) {
            $this->schedule = $newSettings['schedule'];
        }

        if (isset($newSettings['batch'])) {
            $this->batch = (int) $newSettings['batch'];
        }

        $dbSettings = $this->getDbOption();
        $dbSettings['schedule'] = $this->schedule;
        $dbSettings['batch'] = $this->batch;
        $this->setOption(self::DB_KEY, $dbSettings);

        $this->ensureSchedule(true);
        
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
            'schedule' => $this->schedule,
            'batch' => $this->batch,
            'last_run' => $this->lastRun,
            'next_run' => wp_next_scheduled(self::CRON_HOOK) ?: null,
        ];
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        $this->init();
        $this->scheduler->ensureSchedule();
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
                            $count = $dryRun ? $this->counter->countRevisions() : $this->operations->cleanRevisions();
                        }
                        break;
                        
                    case 'spam_comments':
                        if ($this->clean_spam) {
                            $count = $dryRun ? $this->counter->countSpam() : $this->operations->cleanSpam();
                        }
                        break;
                        
                    case 'trash_posts':
                        if ($this->clean_trash) {
                            $count = $dryRun ? $this->counter->countTrash() : $this->operations->cleanTrash();
                        }
                        break;
                        
                    case 'auto_drafts':
                        $count = $dryRun ? $this->counter->countAutoDrafts() : $this->operations->cleanAutoDrafts();
                        break;
                        
                    case 'expired_transients':
                        $count = $dryRun ? $this->counter->countExpiredTransients() : $this->operations->cleanExpiredTransients();
                        break;
                        
                    case 'orphan_postmeta':
                        $count = $dryRun ? $this->counter->countOrphanPostmeta() : $this->operations->cleanOrphanPostmeta();
                        break;
                        
                    case 'orphan_termmeta':
                        $count = $dryRun ? $this->counter->countOrphanTermmeta() : $this->operations->cleanOrphanTermmeta();
                        break;
                        
                    case 'orphan_usermeta':
                        $count = $dryRun ? $this->counter->countOrphanUsermeta() : $this->operations->cleanOrphanUsermeta();
                        break;
                        
                    case 'optimize_tables':
                        if (!$dryRun) {
                            $count = $this->operations->optimizeTables();
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

        if (!$dryRun) {
            $this->lastRun = time();
            $this->setOption(self::LAST_RUN_KEY, $this->lastRun);
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

    public function registerCronSchedules(array $schedules): array
    {
        if (!isset($schedules['fp_ps_weekly'])) {
            $schedules['fp_ps_weekly'] = [
                'interval' => WEEK_IN_SECONDS,
                'display' => __('Once Weekly (FP Performance)', 'fp-performance-suite'),
            ];
        }

        if (!isset($schedules['fp_ps_monthly'])) {
            $schedules['fp_ps_monthly'] = [
                'interval' => 30 * DAY_IN_SECONDS,
                'display' => __('Once Monthly (FP Performance)', 'fp-performance-suite'),
            ];
        }

        return $schedules;
    }

    private function ensureSchedule(bool $force = false): void
    {
        if ($force) {
            wp_clear_scheduled_hook(self::CRON_HOOK);
        }

        if ($this->schedule === 'manual') {
            wp_clear_scheduled_hook(self::CRON_HOOK);
            return;
        }

        if (wp_next_scheduled(self::CRON_HOOK) && !$force) {
            return;
        }

        $recurrence = $this->schedule === 'monthly' ? 'fp_ps_monthly' : 'fp_ps_weekly';
        if ($this->schedule === 'daily') {
            $recurrence = 'daily';
        }

        if (!in_array($recurrence, array_keys(apply_filters('cron_schedules', [])), true)) {
            // Ensure schedules are registered
            add_filter('cron_schedules', [$this, 'registerCronSchedules']);
        }

        wp_schedule_event(time() + HOUR_IN_SECONDS, $recurrence, self::CRON_HOOK);
    }

    private function getScheduledScope(): array
    {
        $dbSettings = $this->getDbOption();
        $scope = $dbSettings['cleanup_scope'] ?? [];

        if (empty($scope) || !is_array($scope)) {
            $scope = [
                'revisions',
                'auto_drafts',
                'trash_posts',
                'spam_comments',
                'expired_transients',
            ];
        }

        return array_unique($scope);
    }

    private function pruneDebugLog(): void
    {
        $logFile = WP_CONTENT_DIR . '/debug.log';
        $maxSize = apply_filters('fp_ps_debug_log_max_size_kb', 1024); // 1 MB default

        if (!file_exists($logFile)) {
            return;
        }

        $sizeKb = (int) (filesize($logFile) / 1024);
        if ($sizeKb <= $maxSize) {
            return;
        }

        $archiveDir = WP_CONTENT_DIR . '/fp-ps-logs';
        if (!is_dir($archiveDir)) {
            wp_mkdir_p($archiveDir);
        }

        $archiveFile = trailingslashit($archiveDir) . 'debug-' . gmdate('Ymd-His') . '.log';

        if (@rename($logFile, $archiveFile)) {
            touch($logFile);
        } else {
            // Fallback: truncate file
            $handle = @fopen($logFile, 'w');
            if ($handle) {
                fclose($handle);
            }
        }
    }

    private function getDbOption(?string $key = null)
    {
        $settings = $this->getOption(self::DB_KEY, []);
        if ($key === null) {
            return $settings;
        }
        return $settings[$key] ?? [];
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
        
        // SECURITY FIX: Usa whitelist invece di validazione regex
        // Ottieni lista tabelle WordPress ufficiali
        $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
        
        if (!is_array($tables) || empty($tables)) {
            return 0;
        }
        
        // Crea whitelist di tabelle valide
        $allowedTables = [];
        foreach ($tables as $table) {
            $tableName = $table[0];
            
            // Solo tabelle con prefix WordPress
            if (strpos($tableName, $wpdb->prefix) === 0) {
                // Validazione strict: solo alfanumerici e underscore
                if (preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                    $allowedTables[] = $tableName;
                }
            }
        }
        
        $optimized = 0;
        
        foreach ($allowedTables as $tableName) {
            // SECURITY: Usa backtick escaping per nomi tabella
            // Essendo in whitelist validata, è safe
            $escaped = '`' . str_replace('`', '``', $tableName) . '`';
            
            $result = $wpdb->query("OPTIMIZE TABLE {$escaped}");
            
            if ($result !== false) {
                $optimized++;
                Logger::debug('Table ottimizzata', ['table' => $tableName]);
            } else {
                Logger::warning('Impossibile ottimizzare tabella', [
                    'table' => $tableName,
                    'error' => $wpdb->last_error,
                ]);
            }
        }
        
        if ($optimized > 0) {
            Logger::info("Database optimization completata: {$optimized} tabelle ottimizzate");
        }
        
        return $optimized;
    }
}