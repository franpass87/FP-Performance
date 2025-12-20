<?php

namespace FP\PerfSuite\Services\DB\Cleaner;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function wp_next_scheduled;
use function wp_schedule_event;
use function wp_unschedule_event;
use function time;

/**
 * Gestisce la pianificazione delle pulizie
 * 
 * @package FP\PerfSuite\Services\DB\Cleaner
 * @author Francesco Passeri
 */
class SchedulerManager
{
    public const CRON_HOOK = 'fp_clean_database';

    private string $schedule;
    private int $batch;
    
    /**
     * @var OptionsRepositoryInterface|null
     */
    private $optionsRepo;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository instance
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->loadSettings();
    }

    /**
     * Carica le impostazioni dello scheduler
     */
    private function loadSettings(): void
    {
        $dbSettings = $this->getOption('fp_ps_db', []);
        $cleanerSettings = $this->getOption('fp_ps_db_cleaner_settings', []);

        $this->schedule = $cleanerSettings['schedule'] ?? $dbSettings['schedule'] ?? 'manual';
        $this->batch = isset($cleanerSettings['batch'])
            ? max(50, min(1000, (int) $cleanerSettings['batch']))
            : (isset($dbSettings['batch']) ? (int) $dbSettings['batch'] : 200);
    }

    /**
     * Registra gli schedule personalizzati
     */
    public function registerCronSchedules(array $schedules): array
    {
        if (!isset($schedules['weekly'])) {
            $schedules['weekly'] = [
                'interval' => 604800,
                'display' => __('Once Weekly', 'fp-performance-suite'),
            ];
        }
        
        if (!isset($schedules['monthly'])) {
            $schedules['monthly'] = [
                'interval' => 2635200,
                'display' => __('Once Monthly', 'fp-performance-suite'),
            ];
        }
        
        return $schedules;
    }

    /**
     * Assicura che lo schedule sia attivo
     */
    public function ensureSchedule(bool $force = false): void
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        
        if ($this->schedule === 'manual') {
            if ($timestamp) {
                wp_unschedule_event($timestamp, self::CRON_HOOK);
            }
            return;
        }
        
        if ($force && $timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
            $timestamp = false;
        }
        
        if (!$timestamp) {
            wp_schedule_event(time(), $this->schedule, self::CRON_HOOK);
        }
    }

    /**
     * Ottiene lo scope pianificato
     */
    public function getScheduledScope(): array
    {
        $dbSettings = $this->getOption('fp_ps_db', []);
        
        return [
            'revisions' => !empty($dbSettings['clean_revisions']),
            'spam_comments' => !empty($dbSettings['clean_spam']),
            'trash_posts' => !empty($dbSettings['clean_trash']),
            'auto_drafts' => true,
            'expired_transients' => true,
        ];
    }

    /**
     * Ottiene lo schedule corrente
     */
    public function getSchedule(): string
    {
        return $this->schedule;
    }

    /**
     * Ottiene il batch size
     */
    public function getBatch(): int
    {
        return $this->batch;
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
}















