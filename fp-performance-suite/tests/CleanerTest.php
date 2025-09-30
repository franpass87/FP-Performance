<?php

use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Utils\Env;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

class DummyWpdb
{
    public string $posts = 'wp_posts';
    public string $comments = 'wp_comments';
    public string $options = 'wp_options';
    public string $postmeta = 'wp_postmeta';
    public string $termmeta = 'wp_termmeta';
    public string $terms = 'wp_terms';
    public string $usermeta = 'wp_usermeta';
    public string $users = 'wp_users';
    public array $queries = [];

    public function prepare(string $query, ...$args): string
    {
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
        $formatted = [];
        foreach ($args as $arg) {
            if (is_numeric($arg)) {
                $formatted[] = (int) $arg;
            } else {
                $formatted[] = "'" . $arg . "'";
            }
        }
        $query = preg_replace('/%d/', '%s', $query);
        return vsprintf($query, $formatted);
    }

    public function get_col(string $query): array
    {
        $this->queries[] = $query;
        if (strpos($query, 'FROM wp_posts') !== false) {
            return [1, 2];
        }
        if (strpos($query, 'FROM wp_comments') !== false) {
            return [3];
        }
        if (strpos($query, 'wp_options') !== false) {
            return ['_transient_timeout_test'];
        }
        if (strpos($query, 'wp_postmeta') !== false) {
            return [5];
        }
        if (strpos($query, 'SHOW TABLES') !== false) {
            return ['wp_posts', 'wp_options'];
        }
        return [];
    }

    public function query(string $query): int
    {
        $this->queries[] = $query;
        return 1;
    }

    public function get_results(string $query): array
    {
        if (strpos($query, 'SHOW TABLE STATUS') !== false) {
            return [(object) ['Data_free' => 1024], (object) ['Data_free' => 2048]];
        }
        return [];
    }
}

final class CleanerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['wpdb'] = new DummyWpdb();
        $GLOBALS['__hooks'] = [];
        $GLOBALS['__cron'] = [];
        $GLOBALS['__wp_options'] = [];
    }

    public function testCleanupDryRun(): void
    {
        $cleaner = new Cleaner(new Env());
        $result = $cleaner->cleanup(['revisions', 'spam_comments'], true, 100);
        $this->assertSame(2, $result['revisions']['found']);
        $this->assertSame(0, $result['revisions']['deleted']);
        $this->assertSame(1, $result['spam_comments']['found']);
    }

    public function testCleanupDeletesWhenNotDryRun(): void
    {
        $cleaner = new Cleaner(new Env());
        $result = $cleaner->cleanup(['revisions'], false, 50);
        $this->assertSame(2, $result['revisions']['deleted']);
    }

    public function testOverheadCalculation(): void
    {
        $cleaner = new Cleaner(new Env());
        $overhead = $cleaner->overhead();
        $this->assertGreaterThan(0, $overhead);
    }

    public function testPrimeSchedulesAddsIntervalsOnce(): void
    {
        $cleaner = new Cleaner(new Env());
        $cleaner->primeSchedules();

        $schedules = apply_filters('cron_schedules', []);
        $this->assertArrayHasKey('fp_ps_weekly', $schedules);
        $this->assertArrayHasKey('fp_ps_monthly', $schedules);
        $this->assertTrue(has_filter('cron_schedules', [$cleaner, 'registerSchedules']));

        $before = count($GLOBALS['__hooks']['cron_schedules'] ?? []);
        $cleaner->primeSchedules();
        $after = count($GLOBALS['__hooks']['cron_schedules'] ?? []);

        $this->assertSame($before, $after, 'Prime schedules should not duplicate callbacks.');
    }
}
