<?php

namespace FP\PerfSuite\Services\Assets\Combiners;

use function array_values;
use function function_exists;
use function is_array;
use function is_object;
use function is_string;
use function wp_dequeue_script;
use function wp_enqueue_script;
use function wp_register_script;

/**
 * JavaScript Asset Combiner
 * 
 * Combines multiple JS files into a single file to reduce HTTP requests
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class JsCombiner extends AssetCombinerBase
{
    private const COMBINED_SCRIPT_HANDLE = 'fp-ps-combined-scripts';
    private const COMBINED_FOOTER_SCRIPT_HANDLE = 'fp-ps-combined-scripts-footer';

    /** @var array{head:bool,footer:bool} */
    private array $combined = ['head' => false, 'footer' => false];

    protected function getExtension(): string
    {
        return 'js';
    }

    protected function getType(): string
    {
        return 'js';
    }

    /**
     * Combine JavaScript files
     * 
     * @param bool $footer Whether to combine footer scripts
     * @return bool True if combination was successful
     */
    public function combine(bool $footer = false): bool
    {
        $key = $footer ? 'footer' : 'head';

        if ($this->combined[$key]) {
            return true;
        }

        if (!function_exists('wp_register_script') || !function_exists('wp_enqueue_script') || !function_exists('wp_dequeue_script')) {
            return false;
        }

        global $wp_scripts;

        if (!($wp_scripts instanceof \WP_Scripts)) {
            return false;
        }

        $result = $this->combineDependencyGroup($wp_scripts, $footer ? 1 : 0);

        if (null === $result) {
            return false;
        }

        $handle = $footer ? self::COMBINED_FOOTER_SCRIPT_HANDLE : self::COMBINED_SCRIPT_HANDLE;
        wp_register_script($handle, $result['url'], [], null, $footer);
        wp_enqueue_script($handle);

        $this->replaceDependencies($wp_scripts, $result['handles'], $handle);

        foreach ($result['handles'] as $handleToRemove) {
            wp_dequeue_script($handleToRemove);
        }

        $this->combined[$key] = true;
        return true;
    }

    /**
     * Combine dependency group
     * 
     * @param \WP_Dependencies $collection
     * @param int $group Group identifier (0 for head, 1 for footer)
     * @return array{handles:array<int,string>,url:string}|null
     */
    private function combineDependencyGroup($collection, int $group): ?array
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

            if (!$this->matchesGroup($item, $group)) {
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
     * Check if item matches the target group
     * 
     * @param object $item
     * @param int $group
     * @return bool
     */
    private function matchesGroup(object $item, int $group): bool
    {
        $itemGroup = 0;

        if (isset($item->extra) && is_array($item->extra) && isset($item->extra['group'])) {
            $itemGroup = (int) $item->extra['group'];
        }

        return $itemGroup === $group;
    }

    /**
     * Check if scripts have been combined
     * 
     * @param bool|null $footer Check specific location, or null for any
     */
    public function isCombined(?bool $footer = null): bool
    {
        if (null === $footer) {
            return $this->combined['head'] || $this->combined['footer'];
        }

        return $this->combined[$footer ? 'footer' : 'head'];
    }

    /**
     * Reset combination state
     * 
     * @param bool|null $footer Reset specific location, or null for all
     */
    public function reset(?bool $footer = null): void
    {
        if (null === $footer) {
            $this->combined = ['head' => false, 'footer' => false];
        } else {
            $this->combined[$footer ? 'footer' : 'head'] = false;
        }
    }
}