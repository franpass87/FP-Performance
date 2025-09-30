<?php

namespace FP\PerfSuite\Utils;

class Htaccess
{
    private Fs $fs;

    public function __construct(Fs $fs)
    {
        $this->fs = $fs;
    }

    public function isSupported(): bool
    {
        return function_exists('got_mod_rewrite') && got_mod_rewrite() && is_writable(ABSPATH . '.htaccess');
    }

    public function backup(string $file): ?string
    {
        if (!$this->fs->exists($file)) {
            return null;
        }
        $backup = $file . '.bak-' . gmdate('YmdHis');
        $this->fs->copy($file, $backup, true);
        return $backup;
    }

    public function injectRules(string $section, string $rules): bool
    {
        $file = ABSPATH . '.htaccess';
        $existing = $this->fs->exists($file) ? $this->fs->getContents($file) : '';
        $markerStart = "# BEGIN {$section}";
        $markerEnd = "# END {$section}";

        $pattern = sprintf('/%s.*?%s/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
        $block = $markerStart . PHP_EOL . trim($rules) . PHP_EOL . $markerEnd;

        if (preg_match($pattern, $existing)) {
            $updated = preg_replace($pattern, $block, $existing);
        } else {
            $updated = $existing . PHP_EOL . $block . PHP_EOL;
        }

        $this->backup($file);
        return $this->fs->putContents($file, (string) $updated);
    }

    public function removeSection(string $section): bool
    {
        $file = ABSPATH . '.htaccess';
        if (!$this->fs->exists($file)) {
            return false;
        }
        $existing = $this->fs->getContents($file);
        $markerStart = "# BEGIN {$section}";
        $markerEnd = "# END {$section}";
        $pattern = sprintf('/%s.*?%s\n?/s', preg_quote($markerStart, '/'), preg_quote($markerEnd, '/'));
        $updated = preg_replace($pattern, '', $existing);
        $this->backup($file);
        return $this->fs->putContents($file, (string) $updated);
    }
}
