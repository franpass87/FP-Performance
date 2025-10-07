<?php

namespace FP\PerfSuite\Services\Assets\ResourceHints;

use function array_merge;
use function array_unique;
use function esc_url_raw;
use function is_array;
use function is_string;
use function pathinfo;
use function parse_url;
use function preg_split;
use function strtolower;
use function trim;
use const PATHINFO_EXTENSION;
use const PHP_URL_PATH;

/**
 * Resource Hints Manager
 * 
 * Manages DNS prefetch and preload resource hints for better performance
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class ResourceHintsManager
{
    /** @var array<int, string> */
    private array $dnsPrefetchUrls = [];

    /** @var array<int, string> */
    private array $preloadUrls = [];

    /**
     * Add DNS prefetch hint
     * 
     * @param array<int, mixed> $hints Current hints
     * @param string $relation Relation type
     * @return array<int, mixed> Modified hints
     */
    public function addDnsPrefetch(array $hints, string $relation): array
    {
        if ('dns-prefetch' !== $relation) {
            return $hints;
        }

        return array_unique(array_merge($hints, $this->dnsPrefetchUrls));
    }

    /**
     * Add preload resource hints
     * 
     * @param array<int, mixed> $hints Current hints
     * @param string $relation Relation type
     * @return array<int, mixed> Modified hints
     */
    public function addPreloadHints(array $hints, string $relation): array
    {
        if ('preload' !== $relation) {
            return $hints;
        }

        return $this->mergePreloadHints($hints, $this->formatPreloadHints($this->preloadUrls));
    }

    /**
     * Set DNS prefetch URLs
     * 
     * @param array<int, string>|string $urls URLs to prefetch
     */
    public function setDnsPrefetchUrls($urls): void
    {
        $this->dnsPrefetchUrls = $this->sanitizeUrlList($urls);
    }

    /**
     * Set preload URLs
     * 
     * @param array<int, string>|string $urls URLs to preload
     */
    public function setPreloadUrls($urls): void
    {
        $this->preloadUrls = $this->sanitizeUrlList($urls);
    }

    /**
     * Sanitize URL list
     * 
     * @param mixed $value
     * @return array<int, string>
     */
    private function sanitizeUrlList($value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[\r\n,]+/', $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        $urls = [];
        foreach ($value as $entry) {
            if (is_array($entry)) {
                $entry = $entry['href'] ?? $entry['url'] ?? null;
            }

            if (!is_string($entry)) {
                continue;
            }
            $trimmed = trim($entry);
            if ($trimmed === '') {
                continue;
            }
            $sanitized = esc_url_raw($trimmed);
            if ($sanitized === '') {
                continue;
            }
            $urls[] = $sanitized;
        }

        return array_values(array_unique($urls));
    }

    /**
     * Format URLs as preload hints
     * 
     * @param array<int, string> $urls
     * @return array<int, array<string, mixed>>
     */
    private function formatPreloadHints(array $urls): array
    {
        $formatted = [];

        foreach ($urls as $url) {
            if (!is_string($url) || $url === '') {
                continue;
            }

            $formatted[] = [
                'href' => $url,
                'as' => $this->guessPreloadType($url),
            ];
        }

        return $formatted;
    }

    /**
     * Merge preload hints avoiding duplicates
     * 
     * @param array<int, mixed> $existing
     * @param array<int, array<string, mixed>> $additional
     * @return array<int, array<string, mixed>>
     */
    private function mergePreloadHints(array $existing, array $additional): array
    {
        $merged = [];
        $seen = [];

        foreach (array_merge($existing, $additional) as $hint) {
            if (is_array($hint) && isset($hint['href'])) {
                $href = (string) $hint['href'];
                if ($href === '') {
                    continue;
                }

                $as = isset($hint['as']) && is_string($hint['as']) && $hint['as'] !== ''
                    ? strtolower($hint['as'])
                    : $this->guessPreloadType($href);

                $key = strtolower($href) . '|' . $as;
                $extras = array_diff_key($hint, ['href' => true, 'as' => true]);
                $entry = ['href' => $href, 'as' => $as] + $extras;
                if (isset($seen[$key])) {
                    $index = $seen[$key];
                    $currentExtras = array_diff_key($merged[$index], ['href' => true, 'as' => true]);
                    if (!empty($extras) && empty($currentExtras)) {
                        $merged[$index] = $entry;
                    }
                    continue;
                }
                $seen[$key] = count($merged);

                $merged[] = $entry;
                continue;
            }

            if (is_string($hint) && $hint !== '') {
                $href = $hint;
                $as = $this->guessPreloadType($href);
                $key = strtolower($href) . '|' . $as;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = count($merged);

                $merged[] = ['href' => $href, 'as' => $as];
            }
        }

        return $merged;
    }

    /**
     * Guess preload resource type from URL
     * 
     * @param string $url Resource URL
     * @return string Resource type
     */
    private function guessPreloadType(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'css':
                return 'style';
            case 'js':
            case 'mjs':
            case 'cjs':
                return 'script';
            case 'woff':
            case 'woff2':
            case 'ttf':
            case 'otf':
                return 'font';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'webp':
            case 'avif':
            case 'svg':
                return 'image';
            case 'mp4':
            case 'webm':
            case 'mov':
                return 'video';
            case 'mp3':
            case 'ogg':
            case 'wav':
                return 'audio';
            default:
                return 'fetch';
        }
    }
}