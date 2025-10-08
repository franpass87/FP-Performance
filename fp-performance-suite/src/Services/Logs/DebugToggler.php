<?php

namespace FP\PerfSuite\Services\Logs;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Env;

use function error_log;
use function get_option;
use function update_option;

class DebugToggler
{
    private const OPTION_LOG_EXPRESSION = 'fp_ps_debug_log_value';
    private Fs $fs;
    private Env $env;

    public function __construct(Fs $fs, Env $env)
    {
        $this->fs = $fs;
        $this->env = $env;
    }

    public function status(): array
    {
        $logEnabled = defined('WP_DEBUG_LOG') ? (bool) WP_DEBUG_LOG : false;
        $logPath = '';
        if (defined('WP_DEBUG_LOG')) {
            if (is_string(WP_DEBUG_LOG) && WP_DEBUG_LOG !== '') {
                $logPath = WP_DEBUG_LOG;
            } elseif (WP_DEBUG_LOG) {
                $logPath = WP_CONTENT_DIR . '/debug.log';
            }
        }
        return [
            'WP_DEBUG' => defined('WP_DEBUG') ? (bool) WP_DEBUG : false,
            'WP_DEBUG_LOG' => $logEnabled,
            'log_file' => $logPath,
        ];
    }

    public function toggle(bool $enabled, bool $log = true): bool
    {
        $config = ABSPATH . 'wp-config.php';
        $lockFile = WP_CONTENT_DIR . '/fp-ps-config.lock';
        $lock = null;

        try {
            // Acquire lock to prevent concurrent modifications
            $lock = fopen($lockFile, 'c+');
            if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
                error_log('[FP Performance Suite] Failed to acquire lock for wp-config.php modification');
                return false;
            }

            if (!$this->fs->exists($config)) {
                return false;
            }

            $original = $this->fs->getContents($config);
            $this->backup($config, $original);

            $updated = $original;
            $existingLogValue = $this->extractConstant($original, 'WP_DEBUG_LOG');
            $parsedLogValue = $this->parseConstant($existingLogValue);
            $existingRaw = $existingLogValue !== null ? trim($existingLogValue) : null;

            if (!$log && $existingRaw !== null && $existingRaw !== '' && strtolower($existingRaw) !== 'false') {
                update_option(self::OPTION_LOG_EXPRESSION, $existingRaw);
            }

            $map = [
                'WP_DEBUG' => $enabled ? 'true' : 'false',
                'WP_DEBUG_LOG' => $log ? $this->determineLogValue($parsedLogValue) : 'false',
            ];

            foreach ($map as $constant => $value) {
                $replacement = "define('{$constant}', {$value});";
                $pattern = "/define\s*\(\s*['\"]{$constant}['\"]\s*,\s*([^\)]*)\);/i";
                if (preg_match($pattern, $updated)) {
                    $updated = preg_replace($pattern, $replacement, $updated, 1);
                    continue;
                }
                $updated = preg_replace('/(<\?php)/', "$1\n{$replacement}", $updated, 1);
            }

            $result = $this->fs->putContents($config, $updated);
            if ($result && function_exists('wp_opcache_invalidate')) {
                wp_opcache_invalidate($config, true);
            }
            return $result;
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Failed to toggle debug mode: ' . $e->getMessage());
            return false;
        } finally {
            // Always release lock
            if ($lock) {
                flock($lock, LOCK_UN);
                fclose($lock);
                @unlink($lockFile);
            }
        }
    }

    public function backup(string $config, string $contents): void
    {
        try {
            $backupFile = $config . '.fp-backup-' . gmdate('YmdHis');
            $this->fs->putContents($backupFile, $contents);
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Failed to back up wp-config.php: ' . $e->getMessage());
        }
    }

    public function revertLatest(): bool
    {
        $config = ABSPATH . 'wp-config.php';
        $pattern = $config . '.fp-backup-*';
        try {
            $backups = glob($pattern);
            if (empty($backups)) {
                return false;
            }
            rsort($backups);
            $latest = $backups[0];
            $contents = $this->fs->getContents($latest);
            $result = $this->fs->putContents($config, $contents);
            if ($result && function_exists('wp_opcache_invalidate')) {
                wp_opcache_invalidate($config, true);
            }
            return $result;
        } catch (\Throwable $e) {
            error_log('[FP Performance Suite] Failed to restore wp-config.php backup: ' . $e->getMessage());
            return false;
        }
    }

    private function determineLogValue(?array $parsed): string
    {
        if ($parsed) {
            if ($parsed['type'] === 'string' && $parsed['value'] !== '') {
                return $this->exportValue($parsed['value']);
            }
            if ($parsed['type'] === 'raw' && $parsed['value'] !== '') {
                return $parsed['value'];
            }
            if ($parsed['type'] === 'bool') {
                if ($parsed['value']) {
                    return 'true';
                }
                $stored = get_option(self::OPTION_LOG_EXPRESSION, '');
                if (is_string($stored) && trim($stored) !== '') {
                    return trim($stored);
                }
                return 'true';
            }
        }
        if (defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG) && WP_DEBUG_LOG !== '') {
            return $this->exportValue(WP_DEBUG_LOG);
        }
        $stored = get_option(self::OPTION_LOG_EXPRESSION, '');
        if (is_string($stored) && trim($stored) !== '') {
            return trim($stored);
        }
        return 'true';
    }

    private function exportValue(string $value): string
    {
        return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], $value) . "'";
    }

    private function extractConstant(string $contents, string $constant): ?string
    {
        $pattern = "/define\s*\(\s*['\"]{$constant}['\"]\s*,\s*(.+?)\s*\);/is";
        if (preg_match($pattern, $contents, $matches)) {
            return $matches[1] ?? null;
        }
        return null;
    }

    private function parseConstant(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }
        $lower = strtolower($trimmed);
        if ($lower === 'true' || $lower === 'false') {
            return ['type' => 'bool', 'value' => $lower === 'true'];
        }
        if (preg_match("/^['\"](.*)['\"]$/s", $trimmed, $matches)) {
            return ['type' => 'string', 'value' => stripslashes($matches[1] ?? '')];
        }
        return ['type' => 'raw', 'value' => $trimmed];
    }
}
