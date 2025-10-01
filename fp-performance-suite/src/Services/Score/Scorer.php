<?php

namespace FP\PerfSuite\Services\Score;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Services\Media\WebPConverter;
use function __;

class Scorer
{
    private PageCache $pageCache;
    private Headers $headers;
    private Optimizer $optimizer;
    private WebPConverter $webp;
    private Cleaner $cleaner;
    private DebugToggler $debugToggler;

    public function __construct(PageCache $pageCache, Headers $headers, Optimizer $optimizer, WebPConverter $webp, Cleaner $cleaner, DebugToggler $debugToggler)
    {
        $this->pageCache = $pageCache;
        $this->headers = $headers;
        $this->optimizer = $optimizer;
        $this->webp = $webp;
        $this->cleaner = $cleaner;
        $this->debugToggler = $debugToggler;
    }

    /**
     * @return array{total:int,breakdown:array<string,int>,suggestions:array<int,string>}
     */
    public function calculate(): array
    {
        $score = 0;
        $breakdown = [];
        $suggestions = [];

        [$points, $suggestion] = $this->gzipScore();
        $score += $points;
        $breakdown[__('GZIP/Brotli', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->browserCacheScore();
        $score += $points;
        $breakdown[__('Browser cache headers', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->pageCacheScore();
        $score += $points;
        $breakdown[__('Page cache', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->assetsScore();
        $score += $points;
        $breakdown[__('Asset optimization', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->webpScore();
        $score += $points;
        $breakdown[__('WebP coverage', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->databaseScore();
        $score += $points;
        $breakdown[__('Database health', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->heartbeatScore();
        $score += $points;
        $breakdown[__('Heartbeat throttling', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->emojiScore();
        $score += $points;
        $breakdown[__('Emoji & embeds', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->criticalCssScore();
        $score += $points;
        $breakdown[__('Critical CSS', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        [$points, $suggestion] = $this->logScore();
        $score += $points;
        $breakdown[__('Logs hygiene', 'fp-performance-suite')] = $points;
        if ($suggestion) {
            $suggestions[] = $suggestion;
        }

        return [
            'total' => min(100, $score),
            'breakdown' => $breakdown,
            'suggestions' => $suggestions,
        ];
    }

    public function activeOptimizations(): array
    {
        $active = [];
        if ($this->pageCache->isEnabled()) {
            $active[] = __('Page caching enabled', 'fp-performance-suite');
        }
        $headerStatus = $this->headers->status();
        if (!empty($headerStatus['enabled'])) {
            $active[] = __('Browser cache headers applied', 'fp-performance-suite');
        }
        $assetStatus = $this->optimizer->status();
        if (!empty($assetStatus['defer_js'])) {
            $active[] = __('Defer JS active', 'fp-performance-suite');
        }
        if (!empty($assetStatus['remove_emojis'])) {
            $active[] = __('Emoji scripts removed', 'fp-performance-suite');
        }
        $webpStatus = $this->webp->status();
        if (!empty($webpStatus['enabled'])) {
            $active[] = __('WebP conversion enabled', 'fp-performance-suite');
        }
        return $active;
    }

    private function gzipScore(): array
    {
        $enabled = false;
        if (function_exists('ini_get')) {
            $outputCompression = ini_get('zlib.output_compression');
            if ($outputCompression && (int) $outputCompression === 1) {
                $enabled = true;
            }
            if (!$enabled) {
                $handler = ini_get('output_handler');
                if (is_string($handler) && stripos($handler, 'ob_gzhandler') !== false) {
                    $enabled = true;
                }
            }
        }

        if (!$enabled && function_exists('headers_list')) {
            foreach (headers_list() as $header) {
                if (stripos($header, 'content-encoding:') === 0) {
                    $enabled = true;
                    break;
                }
            }
        }

        if (!$enabled && function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (is_array($modules) && (in_array('mod_deflate', $modules, true) || in_array('mod_brotli', $modules, true))) {
                $enabled = true;
            }
        }

        if ($enabled) {
            return [10, null];
        }
        return [0, __('Enable GZIP or Brotli compression via server configuration.', 'fp-performance-suite')];
    }

    private function browserCacheScore(): array
    {
        $status = $this->headers->status();
        if (!empty($status['enabled'])) {
            return [10, null];
        }
        return [2, __('Activate browser caching headers for static assets.', 'fp-performance-suite')];
    }

    private function pageCacheScore(): array
    {
        if ($this->pageCache->isEnabled()) {
            return [15, null];
        }
        return [5, __('Enable page caching to store HTML output on disk.', 'fp-performance-suite')];
    }

    private function assetsScore(): array
    {
        $status = $this->optimizer->status();
        $points = 0;
        $suggestions = [];
        if (!empty($status['minify_html'])) {
            $points += 5;
        } else {
            $suggestions[] = __('Enable HTML/CSS minification to reduce payload.', 'fp-performance-suite');
        }
        if (!empty($status['defer_js'])) {
            $points += 5;
        } else {
            $suggestions[] = __('Consider deferring non-critical JavaScript.', 'fp-performance-suite');
        }
        return [$points, implode(' ', $suggestions) ?: null];
    }

    private function webpScore(): array
    {
        $status = $this->webp->status();
        $coverage = $status['coverage'];
        if ($coverage >= 80) {
            return [15, null];
        }
        if ($coverage >= 40) {
            return [8, __('Run bulk WebP conversion to improve coverage.', 'fp-performance-suite')];
        }
        return [3, __('Enable WebP conversion to serve modern formats.', 'fp-performance-suite')];
    }

    private function databaseScore(): array
    {
        $overhead = $this->cleaner->status()['overhead_mb'];
        if ($overhead < 5) {
            return [10, null];
        }
        if ($overhead < 20) {
            return [6, __('Schedule routine database cleanup to reduce overhead.', 'fp-performance-suite')];
        }
        return [2, __('Run database optimization to reclaim space.', 'fp-performance-suite')];
    }

    private function heartbeatScore(): array
    {
        $status = $this->optimizer->status();
        $interval = (int) $status['heartbeat_admin'];
        if ($interval >= 60) {
            return [5, null];
        }
        if ($interval >= 40) {
            return [3, __('Increase admin heartbeat interval to reduce CPU load.', 'fp-performance-suite')];
        }
        return [1, __('Throttle heartbeat on shared hosting to 60 seconds.', 'fp-performance-suite')];
    }

    private function emojiScore(): array
    {
        $status = $this->optimizer->status();
        $removed = !empty($status['remove_emojis']);
        $embedsDisabled = !has_action('wp_head', 'wp_oembed_add_discovery_links');
        if ($removed && $embedsDisabled) {
            return [5, null];
        }
        return [2, __('Disable emojis and embeds to reduce front-end requests.', 'fp-performance-suite')];
    }

    private function criticalCssScore(): array
    {
        $critical = get_option('fp_ps_critical_css', '');
        if (!empty($critical)) {
            return [5, null];
        }
        return [0, __('Add critical CSS placeholder for above-the-fold styles.', 'fp-performance-suite')];
    }

    private function logScore(): array
    {
        $status = $this->debugToggler->status();
        $file = $status['log_file'];
        if (!$file || !file_exists($file)) {
            return [15, null];
        }
        $mtime = filemtime($file);
        if ($mtime && $mtime < strtotime('-1 day')) {
            return [15, null];
        }
        $size = filesize($file);
        if ($size < 10240) {
            return [12, __('Review debug log for warnings in the last 24 hours.', 'fp-performance-suite')];
        }
        return [6, __('Log file is noisy. Resolve errors and clear debug.log.', 'fp-performance-suite')];
    }
}
