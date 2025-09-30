<?php

namespace FP\PerfSuite\Services\Logs;

use FP\PerfSuite\Utils\Fs;
use FP\PerfSuite\Utils\Env;

class DebugToggler
{
    private Fs $fs;
    private Env $env;

    public function __construct(Fs $fs, Env $env)
    {
        $this->fs = $fs;
        $this->env = $env;
    }

    public function status(): array
    {
        return [
            'WP_DEBUG' => defined('WP_DEBUG') ? (bool) WP_DEBUG : false,
            'WP_DEBUG_LOG' => defined('WP_DEBUG_LOG') ? (bool) WP_DEBUG_LOG : false,
            'log_file' => defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? WP_CONTENT_DIR . '/debug.log' : '',
        ];
    }

    public function toggle(bool $enabled, bool $log = true): bool
    {
        $config = ABSPATH . 'wp-config.php';
        if (!$this->fs->exists($config)) {
            return false;
        }

        $original = $this->fs->getContents($config);
        $this->backup($config, $original);

        $replacements = [
            "define('WP_DEBUG', true);" => "define('WP_DEBUG', " . ($enabled ? 'true' : 'false') . ");",
            "define('WP_DEBUG_LOG', true);" => "define('WP_DEBUG_LOG', " . ($log ? 'true' : 'false') . ");",
        ];

        $updated = $original;
        foreach ($replacements as $needle => $replacement) {
            $count = 0;
            if (strpos($updated, $needle) !== false) {
                $updated = str_replace($needle, $replacement, $updated);
            } else {
                $updated = preg_replace("/define\('(WP_DEBUG|WP_DEBUG_LOG)',\s*(true|false)\);/", $replacement, $updated, 1, $count);
                if ($count === 0) {
                    $updated = preg_replace('/(<\?php)/', "$1\n{$replacement}", $updated, 1);
                }
            }
        }

        return $this->fs->putContents($config, $updated);
    }

    public function backup(string $config, string $contents): void
    {
        $backupFile = $config . '.fp-backup-' . gmdate('YmdHis');
        $this->fs->putContents($backupFile, $contents);
    }

    public function revertLatest(): bool
    {
        $config = ABSPATH . 'wp-config.php';
        $pattern = $config . '.fp-backup-*';
        $backups = glob($pattern);
        if (empty($backups)) {
            return false;
        }
        rsort($backups);
        $latest = $backups[0];
        $contents = $this->fs->getContents($latest);
        $this->fs->putContents($config, $contents);
        return true;
    }
}
