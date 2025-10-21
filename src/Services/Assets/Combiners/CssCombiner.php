<?php

namespace FP\PerfSuite\Services\Assets\Combiners;

use function array_flip;
use function array_values;
use function function_exists;
use function is_array;
use function is_object;
use function is_string;
use function wp_dequeue_style;
use function wp_enqueue_style;
use function wp_register_style;

/**
 * CSS Asset Combiner
 *
 * Combines multiple CSS files into a single file to reduce HTTP requests
 *
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class CssCombiner extends AssetCombinerBase
{
    private const COMBINED_STYLE_HANDLE = 'fp-ps-combined-styles';
    private bool $combined = false;

    protected function getExtension(): string
    {
        return 'css';
    }

    protected function getType(): string
    {
        return 'css';
    }

    /**
     * Combine CSS styles
     *
     * @return bool True if combination was successful
     */
    public function combine(): bool
    {
        if ($this->combined) {
            return true;
        }

        if (!function_exists('wp_register_style') || !function_exists('wp_enqueue_style') || !function_exists('wp_dequeue_style')) {
            return false;
        }

        global $wp_styles;

        if (!($wp_styles instanceof \WP_Styles)) {
            return false;
        }

        $result = $this->combineDependencyGroup($wp_styles);

        if (null === $result) {
            return false;
        }

        wp_register_style(self::COMBINED_STYLE_HANDLE, $result['url'], [], null);
        wp_enqueue_style(self::COMBINED_STYLE_HANDLE);

        $this->replaceDependencies($wp_styles, $result['handles'], self::COMBINED_STYLE_HANDLE);

        foreach ($result['handles'] as $handle) {
            wp_dequeue_style($handle);
        }

        $this->combined = true;
        return true;
    }

    /**
     * Combine dependency group
     *
     * @param \WP_Dependencies $collection
     * @return array{handles:array<int,string>,url:string}|null
     */
    private function combineDependencyGroup($collection): ?array
    {
        if (!is_object($collection) || empty($collection->queue) || empty($collection->registered)) {
            return null;
        }

        $queue = is_array($collection->queue) ? $collection->queue : (array) $collection->queue;
        if (empty($queue)) {
            return null;
        }

        // Build position map
        $positions = [];
        foreach (array_values($queue) as $index => $handle) {
            if (!is_string($handle) || '' === $handle) {
                continue;
            }
            if (!isset($positions[$handle])) {
                $positions[$handle] = $index;
            }
        }

        // Find combinable candidates
        $candidates = [];
        foreach ($queue as $handle) {
            if (!is_string($handle) || '' === $handle) {
                continue;
            }
            if (!isset($collection->registered[$handle])) {
                continue;
            }

            $item = $collection->registered[$handle];

            if (!is_object($item)) {
                continue;
            }

            if (!$this->isDependencyCombinable($item)) {
                continue;
            }

            $source = $this->resolveDependencySource($collection, $item);

            if (null === $source) {
                continue;
            }

            $deps = $this->dependencyResolver->normalizeDependencies($item->deps ?? []);

            $candidates[$handle] = [
                'handle' => $handle,
                'path' => $source['path'],
                'url' => $source['url'],
                'deps' => $deps,
            ];
        }

        if (count($candidates) < 2) {
            return null;
        }

        // Filter out candidates with external dependencies
        $queueLookup = [];
        foreach ($queue as $queuedHandle) {
            if (is_string($queuedHandle) && '' !== $queuedHandle) {
                $queueLookup[$queuedHandle] = true;
            }
        }

        $candidates = $this->dependencyResolver->filterExternalDependencies($candidates, $queueLookup);

        if (count($candidates) < 2) {
            return null;
        }

        // Resolve dependency order
        $ordered = $this->dependencyResolver->resolveDependencies($candidates, $positions);

        if (null === $ordered) {
            return null;
        }

        // Build files array
        $files = [];
        foreach ($ordered as $handle) {
            $files[] = [
                'handle' => $handle,
                'path' => $candidates[$handle]['path'],
                'url' => $candidates[$handle]['url'],
            ];
        }

        if (count($files) < 2) {
            return null;
        }

        return $this->writeCombinedAsset($files);
    }

    /**
     * Check if styles have been combined
     */
    public function isCombined(): bool
    {
        return $this->combined;
    }

    /**
     * Reset combination state
     */
    public function reset(): void
    {
        $this->combined = false;
    }
}
