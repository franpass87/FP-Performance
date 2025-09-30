<?php

namespace FP\PerfSuite\Utils;

use WP_Filesystem_Base;

class Fs
{
    private ?WP_Filesystem_Base $fs = null;

    private function ensure(): WP_Filesystem_Base
    {
        global $wp_filesystem;
        if ($this->fs instanceof WP_Filesystem_Base) {
            return $this->fs;
        }

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        $this->fs = $wp_filesystem;

        return $this->fs;
    }

    public function putContents(string $file, string $contents): bool
    {
        $fs = $this->ensure();
        return $fs->put_contents($file, $contents, FS_CHMOD_FILE);
    }

    public function getContents(string $file): string
    {
        $fs = $this->ensure();
        return (string) $fs->get_contents($file);
    }

    public function exists(string $file): bool
    {
        $fs = $this->ensure();
        return $fs->exists($file);
    }

    public function mkdir(string $path): bool
    {
        $fs = $this->ensure();
        return $fs->mkdir($path, FS_CHMOD_DIR);
    }

    public function delete(string $path): bool
    {
        $fs = $this->ensure();
        return $fs->delete($path, true);
    }

    public function copy(string $source, string $destination, bool $overwrite = true): bool
    {
        $fs = $this->ensure();
        return $fs->copy($source, $destination, $overwrite, FS_CHMOD_FILE);
    }
}
