<?php

namespace FP\PerfSuite\Services\DB;

use FP\PerfSuite\Utils\Env;
use wpdb;
use function __;
use function add_action;
use function add_filter;
use function apply_filters;
use function delete_site_transient;
use function delete_transient;
use function get_option;
use function has_filter;
use function sanitize_key;
use function time;
use function update_option;
use function wp_clear_scheduled_hook;
use function wp_delete_comment;
use function wp_delete_post;
use function wp_next_scheduled;
use function wp_schedule_event;

class Cleaner
{
    private const OPTION = 'fp_ps_db';
    public const CRON_HOOK = 'fp_ps_db_cleanup';
    private Env $env;

    public function __construct(Env $env)
    {
        $this->env = $env;
    }

    public function register(): void
    {
        $this->primeSchedules();
        add_action(self::CRON_HOOK, [$this, 'runScheduledCleanup']);
        add_action('update_option_' . self::OPTION, [$this, 'reschedule'], 10, 2);
        $this->maybeSchedule();
    }

    public function primeSchedules(): void
    {
        if (!has_filter('cron_schedules', [$this, 'registerSchedules'])) {
            add_filter('cron_schedules', [$this, 'registerSchedules']);
        }
    }

    /**
     * @return array{schedule:string,batch:int}
     */
    public function settings(): array
    {
        $defaults = [
            'schedule' => 'manual',
            'batch' => 200,
        ];
        return wp_parse_args(get_option(self::OPTION, []), $defaults);
    }

    public function update(array $settings): void
    {
        $current = $this->settings();
        $new = [
            'schedule' => sanitize_key($settings['schedule'] ?? $current['schedule']),
            'batch' => isset($settings['batch']) ? max(50, (int) $settings['batch']) : $current['batch'],
        ];
        update_option(self::OPTION, $new);
    }

    /**
     * @param array $schedules
     * @return array
     */
    public function registerSchedules(array $schedules): array
    {
        $schedules['fp_ps_weekly'] = [
            'interval' => WEEK_IN_SECONDS,
            'display' => __('Once Weekly (FP Performance Suite)', 'fp-performance-suite'),
        ];
        $schedules['fp_ps_monthly'] = [
            'interval' => 30 * DAY_IN_SECONDS,
            'display' => __('Once Monthly (FP Performance Suite)', 'fp-performance-suite'),
        ];
        return $schedules;
    }

    /**
     * @param mixed $old
     * @param mixed $value
     */
    public function reschedule($old, $value): void
    {
        $this->maybeSchedule(true);
    }

    public function maybeSchedule(bool $force = false): void
    {
        if ($force) {
            wp_clear_scheduled_hook(self::CRON_HOOK);
        }

        $settings = $this->settings();
        if (($settings['schedule'] ?? 'manual') === 'manual') {
            wp_clear_scheduled_hook(self::CRON_HOOK);
            return;
        }

        $recurrence = $settings['schedule'] === 'monthly' ? 'fp_ps_monthly' : 'fp_ps_weekly';
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, $recurrence, self::CRON_HOOK);
        }
    }

    public function runScheduledCleanup(): void
    {
        $scope = apply_filters('fp_ps_db_scheduled_scope', [
            'revisions',
            'auto_drafts',
            'trash_posts',
            'spam_comments',
            'expired_transients',
        ]);
        $results = $this->cleanup($scope, false, $this->settings()['batch']);
        update_option('fp_ps_db_last_report', [
            'time' => time(),
            'scope' => $scope,
            'results' => $results,
        ]);
    }

    /**
     * @param array<int,string> $scope
     * @return array<string, mixed>
     */
    public function cleanup(array $scope, bool $dryRun = true, ?int $batch = null): array
    {
        global $wpdb;
        $batch = $batch ?: $this->settings()['batch'];
        $results = [];

        foreach ($scope as $task) {
            switch ($task) {
                case 'revisions':
                    $results['revisions'] = $this->cleanupPosts($wpdb, "post_type = 'revision'", $dryRun, $batch);
                    break;
                case 'auto_drafts':
                    $results['auto_drafts'] = $this->cleanupPosts($wpdb, "post_status = 'auto-draft'", $dryRun, $batch);
                    break;
                case 'trash_posts':
                    $results['trash_posts'] = $this->cleanupPosts($wpdb, "post_status = 'trash'", $dryRun, $batch);
                    break;
                case 'spam_comments':
                    $results['spam_comments'] = $this->cleanupComments($wpdb, ['spam', 'trash'], $dryRun, $batch);
                    break;
                case 'expired_transients':
                    $results['expired_transients'] = $this->cleanupTransients($wpdb, $dryRun, $batch);
                    break;
                case 'orphan_postmeta':
                    $results['orphan_postmeta'] = $this->cleanupMeta($wpdb, $wpdb->postmeta, $wpdb->posts, 'post_id', 'ID', $dryRun, $batch);
                    break;
                case 'orphan_termmeta':
                    $results['orphan_termmeta'] = $this->cleanupMeta($wpdb, $wpdb->termmeta, $wpdb->terms, 'term_id', 'term_id', $dryRun, $batch);
                    break;
                case 'orphan_usermeta':
                    $results['orphan_usermeta'] = $this->cleanupMeta($wpdb, $wpdb->usermeta, $wpdb->users, 'user_id', 'ID', $dryRun, $batch);
                    break;
                case 'optimize_tables':
                    $results['optimize_tables'] = $this->optimizeTables($wpdb, $dryRun);
                    break;
            }
        }

        return $results;
    }

    /**
     * @return array<string,int>
     */
    private function cleanupPosts(wpdb $wpdb, string $where, bool $dryRun, int $batch): array
    {
        $table = $wpdb->posts;
        $sql = $wpdb->prepare("SELECT ID FROM {$table} WHERE {$where} LIMIT %d", $batch);
        $ids = $wpdb->get_col($sql);
        $count = count($ids);
        if (!$dryRun && $count > 0) {
            foreach ($ids as $id) {
                wp_delete_post((int) $id, true);
            }
        }
        return ['found' => $count, 'deleted' => $dryRun ? 0 : $count];
    }

    /**
     * @param array<int,string> $statuses
     * @return array<string,int>
     */
    private function cleanupComments(wpdb $wpdb, array $statuses, bool $dryRun, int $batch): array
    {
        $table = $wpdb->comments;
        $placeholders = implode(',', array_fill(0, count($statuses), '%s'));
        $params = array_merge($statuses, [$batch]);
        $query = $wpdb->prepare("SELECT comment_ID FROM {$table} WHERE comment_approved IN ({$placeholders}) LIMIT %d", $params);
        $ids = $wpdb->get_col($query);
        $count = count($ids);
        if (!$dryRun && $count > 0) {
            foreach ($ids as $id) {
                wp_delete_comment((int) $id, true);
            }
        }
        return ['found' => $count, 'deleted' => $dryRun ? 0 : $count];
    }

    private function cleanupTransients(wpdb $wpdb, bool $dryRun, int $batch): array
    {
        $time = time();
        $sql = $wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d LIMIT %d", '_transient_timeout_%', $time, $batch);
        $names = $wpdb->get_col($sql);
        $count = count($names);
        $deleted = 0;
        if (!$dryRun && $count > 0) {
            foreach ($names as $name) {
                $key = str_replace('_transient_timeout_', '', $name);
                delete_transient($key);
                $deleted++;
            }
        }

        $siteCount = 0;
        $siteDeleted = 0;
        if ($this->env->isMultisite()) {
            $siteSql = $wpdb->prepare("SELECT meta_key FROM {$wpdb->sitemeta} WHERE meta_key LIKE %s AND meta_value < %d LIMIT %d", '_site_transient_timeout_%', $time, $batch);
            $siteNames = $wpdb->get_col($siteSql);
            $siteCount = count($siteNames);
            if (!$dryRun && $siteCount > 0) {
                foreach ($siteNames as $metaKey) {
                    $key = str_replace('_site_transient_timeout_', '', $metaKey);
                    delete_site_transient($key);
                    $siteDeleted++;
                }
            }
        }

        return [
            'found' => $count + $siteCount,
            'deleted' => $dryRun ? 0 : $deleted + $siteDeleted,
            'site_found' => $siteCount,
            'site_deleted' => $dryRun ? 0 : $siteDeleted,
        ];
    }

    private function cleanupMeta(wpdb $wpdb, string $metaTable, string $parentTable, string $foreignKey, string $parentKey, bool $dryRun, int $batch): array
    {
        $sql = $wpdb->prepare("SELECT m.meta_id FROM {$metaTable} m LEFT JOIN {$parentTable} p ON m.{$foreignKey} = p.{$parentKey} WHERE p.{$parentKey} IS NULL LIMIT %d", $batch);
        $ids = $wpdb->get_col($sql);
        $count = count($ids);
        if (!$dryRun && $count > 0) {
            $placeholders = implode(',', array_fill(0, $count, '%d'));
            $wpdb->query($wpdb->prepare("DELETE FROM {$metaTable} WHERE meta_id IN ({$placeholders})", $ids));
        }
        return ['found' => $count, 'deleted' => $dryRun ? 0 : $count];
    }

    private function optimizeTables(wpdb $wpdb, bool $dryRun): array
    {
        $tables = array_filter(
            (array) $wpdb->get_col('SHOW TABLES'),
            static function ($table) use ($wpdb) {
                return is_string($table) && strpos($table, $wpdb->prefix) === 0;
            }
        );
        $run = [];
        foreach ($tables as $table) {
            if (!$dryRun) {
                $wpdb->query("OPTIMIZE TABLE `{$table}`");
            }
            $run[] = $table;
        }
        return ['tables' => $run];
    }

    public function overhead(): float
    {
        global $wpdb;
        $status = $wpdb->get_results('SHOW TABLE STATUS');
        $overhead = 0.0;
        foreach ($status as $row) {
            if (!empty($row->Data_free)) {
                $overhead += (float) $row->Data_free;
            }
        }
        return $overhead / 1024 / 1024; // MB
    }

    public function status(): array
    {
        $report = get_option('fp_ps_db_last_report', []);
        return [
            'overhead_mb' => round($this->overhead(), 2),
            'schedule' => $this->settings()['schedule'],
            'last_run' => isset($report['time']) ? (int) $report['time'] : 0,
            'last_scope' => $report['scope'] ?? [],
        ];
    }
}
