<?php

namespace FP\PerfSuite\Utils;

use function error_log;

class Htaccess
{
    private Fs $fs;

    private const LOG_PREFIX = '[FP Performance Suite] ';

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    public function isSupported(): bool
    {
        if (!function_exists('got_mod_rewrite')) {
            $helper = ABSPATH . 'wp-admin/includes/misc.php';
            if (is_readable($helper)) {
                require_once $helper;
            }
        }

        if (!function_exists('got_mod_rewrite') || !got_mod_rewrite()) {
            return false;
        }

        $file = ABSPATH . '.htaccess';
        if ($this->fs->exists($file)) {
            return is_writable($file);
        }

        return is_writable(ABSPATH);
    }

    public function backup(string $file): ?string
    {
        try {
            if (!$this->fs->exists($file)) {
                return null;
            }
            $backup = $file . '.bak-' . gmdate('YmdHis');
            $this->fs->copy($file, $backup, true);
            return $backup;
        } catch (\Throwable $e) {
            error_log(self::LOG_PREFIX . 'Failed to back up .htaccess: ' . $e->getMessage());
            return null;
        }
    }

    public function injectRules(string $section, string $rules): bool
    {
        $file = ABSPATH . '.htaccess';
        try {
            $existing = $this->fs->exists($file) ? $this->fs->getContents($file) : '';
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $normalizedRules = trim($rules);
            $block = $markerStart . PHP_EOL . $normalizedRules . PHP_EOL . $markerEnd;
            $pattern = sprintf('/%s\s*.*?\s*%s/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));

            $updated = $existing;
            $hasBlock = preg_match($pattern, $existing, $matches) === 1;
            if ($hasBlock && isset($matches[0]) && trim($matches[0]) === trim($block)) {
                return true;
            }

            if ($hasBlock) {
                $updated = (string) preg_replace($pattern, $block, $existing, 1);
            } else {
                $trimmed = rtrim($existing);
                $prefix = $trimmed === '' ? '' : $trimmed . PHP_EOL . PHP_EOL;
                $updated = $prefix . $block . PHP_EOL;
            }

            $this->backup($file);
            return $this->fs->putContents($file, $updated);
        } catch (\Throwable $e) {
            error_log(self::LOG_PREFIX . 'Failed to inject .htaccess rules: ' . $e->getMessage());
            return false;
        }
    }

    public function removeSection(string $section): bool
    {
        $file = ABSPATH . '.htaccess';
        try {
            if (!$this->fs->exists($file)) {
                return false;
            }
            $existing = $this->fs->getContents($file);
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $pattern = sprintf('/%s\s*.*?\s*%s\n?/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
            if (preg_match($pattern, $existing) !== 1) {
                return false;
            }
            $updated = preg_replace($pattern, '', $existing, 1);
            $this->backup($file);
            return $this->fs->putContents($file, (string) $updated);
        } catch (\Throwable $e) {
            error_log(self::LOG_PREFIX . 'Failed to remove .htaccess section: ' . $e->getMessage());
            return false;
        }
    }

    public function hasSection(string $section): bool
    {
        $file = ABSPATH . '.htaccess';
        try {
            if (!$this->fs->exists($file)) {
                return false;
            }
            $contents = $this->fs->getContents($file);
            $markerStart = "# BEGIN {$section}";
            $markerEnd = "# END {$section}";
            $pattern = sprintf('/%s\s*.*?\s*%s/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
            return preg_match($pattern, $contents) === 1;
        } catch (\Throwable $e) {
            error_log(self::LOG_PREFIX . 'Failed to read .htaccess: ' . $e->getMessage());
            return false;
        }
    }
}
