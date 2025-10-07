<?php

namespace FP\PerfSuite\Services\Assets\Combiners;

use function array_column;
use function array_values;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filesize;
use function filemtime;
use function home_url;
use function implode;
use function is_array;
use function is_object;
use function is_readable;
use function is_string;
use function ltrim;
use function md5;
use function parse_url;
use function rtrim;
use function sprintf;
use function str_replace;
use function strtolower;
use function strlen;
use function strpos;
use function strtok;
use function substr;
use function trailingslashit;
use function wp_mkdir_p;
use function wp_parse_url;
use function wp_upload_dir;
use const LOCK_EX;
use const PHP_URL_HOST;
use const PHP_URL_PATH;
use const PHP_URL_SCHEME;

/**
 * Base Asset Combiner
 * 
 * Provides common functionality for CSS and JS combiners
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
abstract class AssetCombinerBase
{
    protected DependencyResolver $dependencyResolver;

    public function __construct(DependencyResolver $dependencyResolver)
    {
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Get asset type extension
     */
    abstract protected function getExtension(): string;

    /**
     * Get asset type identifier
     */
    abstract protected function getType(): string;

    /**
     * Check if dependency item is combinable
     * 
     * @param object $item Dependency item
     * @return bool
     */
    protected function isDependencyCombinable(object $item): bool
    {
        if (!isset($item->src) || !is_string($item->src) || '' === trim($item->src)) {
            return false;
        }

        $extra = [];
        if (isset($item->extra) && is_array($item->extra)) {
            $extra = $item->extra;
        }

        if (!empty($extra['conditional']) || !empty($extra['data']) || !empty($extra['before']) || !empty($extra['after'])) {
            return false;
        }

        return true;
    }

    /**
     * Resolve dependency source to local file path
     * 
     * @param \WP_Dependencies $collection
     * @param object $item
     * @return array{path:string,url:string}|null
     */
    protected function resolveDependencySource($collection, object $item): ?array
    {
        $src = is_string($item->src ?? null) ? trim($item->src) : '';

        if ('' === $src) {
            return null;
        }

        $url = $src;

        if (0 === strpos($url, '//')) {
            $scheme = wp_parse_url(home_url(), PHP_URL_SCHEME) ?: 'https';
            $url = $scheme . ':' . $url;
        } elseif (false === strpos($url, '://')) {
            $base = is_string($collection->base_url ?? null) ? $collection->base_url : '';
            if ('' !== $base) {
                $url = rtrim($base, '/') . '/' . ltrim($url, '/');
            } else {
                $url = home_url('/' . ltrim($url, '/'));
            }
        }

        $sanitized = strtok($url, '?') ?: $url;
        $parsed = wp_parse_url($sanitized);

        if (!is_array($parsed) || empty($parsed['path'])) {
            return null;
        }

        $home = wp_parse_url(home_url());
        if (!empty($parsed['host']) && !empty($home['host'])) {
            if (strtolower($parsed['host']) !== strtolower((string) $home['host'])) {
                return null;
            }
        }

        $path = $parsed['path'];

        $home = wp_parse_url(home_url());
        if (is_array($home) && !empty($home['path'])) {
            $homePath = rtrim((string) $home['path'], '/');
            if ('' !== $homePath) {
                if ($path === $homePath) {
                    $path = '';
                } elseif (0 === strpos($path, $homePath . '/')) {
                    $path = substr($path, strlen($homePath));
                }
            }
        }

        if ('' === $path) {
            return null;
        }

        $path = ABSPATH . ltrim($path, '/');

        if (!file_exists($path) || !is_readable($path)) {
            return null;
        }

        return [
            'path' => $path,
            'url' => $sanitized,
        ];
    }

    /**
     * Write combined asset to file
     * 
     * @param array<int,array{handle:string,path:string,url:string}> $files
     * @return array{handles:array<int,string>,url:string}|null
     */
    protected function writeCombinedAsset(array $files): ?array
    {
        $uploads = wp_upload_dir();

        if (!is_array($uploads) || empty($uploads['basedir']) || empty($uploads['baseurl'])) {
            return null;
        }

        $targetDir = trailingslashit($uploads['basedir']) . 'fp-performance-suite';

        if (!wp_mkdir_p($targetDir)) {
            return null;
        }

        $hashParts = [];

        foreach ($files as $file) {
            $mtime = @filemtime($file['path']);
            $size = @filesize($file['path']);
            $hashParts[] = $file['url'] . '|' . ($mtime ?: 0) . '|' . ($size ?: 0);
        }

        $hash = md5(implode('|', $hashParts));
        $extension = $this->getExtension();
        $type = $this->getType();
        $filename = sprintf('combined-%s-%s.%s', $type, $hash, $extension);
        $fullPath = trailingslashit($targetDir) . $filename;

        $handles = array_column($files, 'handle');
        $url = trailingslashit($uploads['baseurl']) . 'fp-performance-suite/' . $filename;

        if (file_exists($fullPath) && is_readable($fullPath)) {
            return [
                'handles' => $handles,
                'url' => $url,
            ];
        }

        $contents = '';

        foreach ($files as $file) {
            if (!is_readable($file['path'])) {
                return null;
            }

            $asset = file_get_contents($file['path']);

            if (false === $asset) {
                return null;
            }

            $contents .= '/* ' . $file['handle'] . " */\n" . $asset . "\n";
        }

        $bytesWritten = file_put_contents($fullPath, $contents, LOCK_EX);

        if (false === $bytesWritten) {
            return null;
        }

        return [
            'handles' => $handles,
            'url' => $url,
        ];
    }

    /**
     * Replace dependencies after combination
     * 
     * @param \WP_Dependencies $collection
     * @param array<int, string> $replacedHandles
     * @param string $replacement
     */
    protected function replaceDependencies($collection, array $replacedHandles, string $replacement): void
    {
        if (!is_object($collection) || empty($collection->registered) || empty($replacedHandles)) {
            return;
        }

        $lookup = array_flip($replacedHandles);

        foreach ($collection->registered as $handle => $item) {
            if (!is_object($item) || isset($lookup[$handle])) {
                continue;
            }

            $depsProperty = $item->deps ?? [];
            $deps = is_array($depsProperty) ? $depsProperty : (array) $depsProperty;
            $updated = false;

            foreach ($deps as &$dependency) {
                if (isset($lookup[$dependency]) && $dependency !== $replacement) {
                    $dependency = $replacement;
                    $updated = true;
                }
            }

            unset($dependency);

            if ($updated) {
                $item->deps = array_values(array_unique($deps));
            }
        }
    }
}