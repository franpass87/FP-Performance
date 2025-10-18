<?php

namespace FP\PerfSuite\Services\Score;

use FP\PerfSuite\Services\Assets\CriticalCss;
use FP\PerfSuite\Services\Assets\FontOptimizer;
use FP\PerfSuite\Services\Assets\ImageOptimizer;
use FP\PerfSuite\Services\Assets\LazyLoadManager;
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Assets\ThirdPartyScriptManager;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\ObjectCacheManager;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\CDN\CdnManager;
use FP\PerfSuite\Services\Compression\CompressionManager;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Services\Media\WebPConverter;

use function __;
use function _n;
use function apache_get_modules;
use function apply_filters;
use function file_exists;
use function filemtime;
use function filesize;
use function function_exists;
use function get_option;
use function has_action;
use function headers_list;
use function in_array;
use function ini_get;
use function is_array;
use function is_string;
use function sprintf;
use function stripos;
use function strtotime;
use function trim;

class Scorer
{
    private PageCache $pageCache;
    private Headers $headers;
    private Optimizer $optimizer;
    private WebPConverter $webp;
    private Cleaner $cleaner;
    private DebugToggler $debugToggler;
    private LazyLoadManager $lazyLoad;
    private FontOptimizer $fontOptimizer;
    private ImageOptimizer $imageOptimizer;
    private ThirdPartyScriptManager $thirdPartyScripts;
    private ?ObjectCacheManager $objectCache = null;
    private ?CdnManager $cdnManager = null;
    private ?CriticalCss $criticalCss = null;
    private ?CompressionManager $compression = null;

    public function __construct(
        PageCache $pageCache,
        Headers $headers,
        Optimizer $optimizer,
        WebPConverter $webp,
        Cleaner $cleaner,
        DebugToggler $debugToggler,
        LazyLoadManager $lazyLoad,
        FontOptimizer $fontOptimizer,
        ImageOptimizer $imageOptimizer,
        ThirdPartyScriptManager $thirdPartyScripts,
        ?ObjectCacheManager $objectCache = null,
        ?CdnManager $cdnManager = null,
        ?CriticalCss $criticalCss = null,
        ?CompressionManager $compression = null
    ) {
        $this->pageCache = $pageCache;
        $this->headers = $headers;
        $this->optimizer = $optimizer;
        $this->webp = $webp;
        $this->cleaner = $cleaner;
        $this->debugToggler = $debugToggler;
        $this->lazyLoad = $lazyLoad;
        $this->fontOptimizer = $fontOptimizer;
        $this->imageOptimizer = $imageOptimizer;
        $this->thirdPartyScripts = $thirdPartyScripts;
        $this->objectCache = $objectCache;
        $this->cdnManager = $cdnManager;
        $this->criticalCss = $criticalCss;
        $this->compression = $compression;
    }

    /**
     * @return array{total:int,breakdown:array<string,int>,breakdown_detailed:array<string,array>,suggestions:array<int,string>}
     */
    public function calculate(): array
    {
        $score = 0;
        $breakdown = [];
        $breakdownDetailed = [];
        $suggestions = [];

        $categories = [
            'gzip' => [__('GZIP/Brotli', 'fp-performance-suite'), 10],
            'browserCache' => [__('Browser cache headers', 'fp-performance-suite'), 10],
            'pageCache' => [__('Page cache', 'fp-performance-suite'), 15],
            'assets' => [__('Asset optimization', 'fp-performance-suite'), 20],
            'webp' => [__('WebP coverage', 'fp-performance-suite'), 15],
            'database' => [__('Database health', 'fp-performance-suite'), 10],
            'heartbeat' => [__('Heartbeat throttling', 'fp-performance-suite'), 5],
            'emoji' => [__('Emoji & embeds', 'fp-performance-suite'), 5],
            'criticalCss' => [__('Critical CSS', 'fp-performance-suite'), 5],
            'log' => [__('Logs hygiene', 'fp-performance-suite'), 15],
        ];

        foreach ($categories as $method => [$label, $maxPoints]) {
            [$points, $suggestion] = $this->{$method . 'Score'}();
            $score += $points;
            $breakdown[$label] = $points;
            
            $percentage = $maxPoints > 0 ? round(($points / $maxPoints) * 100) : 100;
            $status = $points >= $maxPoints ? 'complete' : ($points > 0 ? 'partial' : 'missing');
            
            $breakdownDetailed[$label] = [
                'current' => $points,
                'max' => $maxPoints,
                'percentage' => $percentage,
                'status' => $status,
                'suggestion' => $suggestion,
            ];
            
            if ($suggestion) {
                $suggestions[] = $suggestion;
            }
        }

        return [
            'total' => min(100, $score),
            'breakdown' => $breakdown,
            'breakdown_detailed' => $breakdownDetailed,
            'suggestions' => $suggestions,
        ];
    }

    public function activeOptimizations(): array
    {
        $active = [];
        
        // Cache
        if ($this->pageCache->isEnabled()) {
            $active[] = __('Page caching enabled', 'fp-performance-suite');
        }
        $headerStatus = $this->headers->status();
        if (!empty($headerStatus['enabled'])) {
            $active[] = __('Browser cache headers applied', 'fp-performance-suite');
        }
        
        // Object Cache (Redis/Memcached)
        if ($this->objectCache !== null) {
            $objectCacheSettings = $this->objectCache->settings();
            if (!empty($objectCacheSettings['enabled'])) {
                $driver = ucfirst($objectCacheSettings['driver']);
                $active[] = sprintf(__('%s object cache active', 'fp-performance-suite'), $driver);
            }
        }
        
        // Compression
        if ($this->compression !== null) {
            $compressionStatus = $this->compression->status();
            if (!empty($compressionStatus['enabled']) && !empty($compressionStatus['active'])) {
                if (!empty($compressionStatus['brotli_supported'])) {
                    $active[] = __('Brotli compression active', 'fp-performance-suite');
                } else {
                    $active[] = __('GZIP compression active', 'fp-performance-suite');
                }
            }
        }
        
        // Asset Optimization
        $assetStatus = $this->optimizer->status();
        if (!empty($assetStatus['minify_html'])) {
            $active[] = __('HTML minification active', 'fp-performance-suite');
        }
        if (!empty($assetStatus['defer_js'])) {
            $active[] = __('Defer JS active', 'fp-performance-suite');
        }
        if (!empty($assetStatus['async_js'])) {
            $active[] = __('Async JS active', 'fp-performance-suite');
        }
        if (!empty($assetStatus['combine_css'])) {
            $active[] = __('CSS combination active', 'fp-performance-suite');
        }
        if (!empty($assetStatus['combine_js'])) {
            $active[] = __('JS combination active', 'fp-performance-suite');
        }
        if (!empty($assetStatus['remove_emojis'])) {
            $active[] = __('Emoji scripts removed', 'fp-performance-suite');
        }
        
        // Critical CSS
        if ($this->criticalCss !== null) {
            $criticalStatus = $this->criticalCss->status();
            if (!empty($criticalStatus['enabled'])) {
                $active[] = sprintf(
                    __('Critical CSS active (%.1f KB)', 'fp-performance-suite'),
                    $criticalStatus['size_kb']
                );
            }
        }
        
        // Lazy Loading
        $lazyLoadStatus = $this->lazyLoad->status();
        if (!empty($lazyLoadStatus['enabled'])) {
            if (!empty($lazyLoadStatus['images_enabled'])) {
                $active[] = __('Lazy loading images enabled', 'fp-performance-suite');
            }
            if (!empty($lazyLoadStatus['iframes_enabled'])) {
                $active[] = __('Lazy loading iframes enabled', 'fp-performance-suite');
            }
        }
        
        // Font Optimization
        $fontStatus = $this->fontOptimizer->status();
        if (!empty($fontStatus['enabled'])) {
            if (!empty($fontStatus['google_fonts_optimized'])) {
                $active[] = __('Google Fonts optimized', 'fp-performance-suite');
            }
            if (!empty($fontStatus['preload_enabled'])) {
                $active[] = __('Font preloading active', 'fp-performance-suite');
            }
        }
        
        // Image Optimization
        $imageStatus = $this->imageOptimizer->status();
        if (!empty($imageStatus['enabled'])) {
            if (!empty($imageStatus['force_dimensions'])) {
                $active[] = __('Image dimensions optimization active', 'fp-performance-suite');
            }
            if (!empty($imageStatus['aspect_ratio'])) {
                $active[] = __('Image aspect-ratio CSS active', 'fp-performance-suite');
            }
        }
        
        // Third-Party Scripts
        $thirdPartyStatus = $this->thirdPartyScripts->status();
        if (!empty($thirdPartyStatus['enabled'])) {
            $managedCount = $thirdPartyStatus['managed_scripts'];
            if ($managedCount > 0) {
                $active[] = sprintf(
                    _n(
                        'Third-party scripts manager active (%d script)',
                        'Third-party scripts manager active (%d scripts)',
                        $managedCount,
                        'fp-performance-suite'
                    ),
                    $managedCount
                );
            }
        }
        
        // WebP
        $webpStatus = $this->webp->status();
        if (!empty($webpStatus['enabled'])) {
            $active[] = __('WebP conversion enabled', 'fp-performance-suite');
        }
        
        // CDN
        if ($this->cdnManager !== null) {
            $cdnStatus = $this->cdnManager->status();
            if (!empty($cdnStatus['enabled'])) {
                $provider = $cdnStatus['provider'] !== 'custom' ? ucfirst($cdnStatus['provider']) : __('Custom', 'fp-performance-suite');
                $active[] = sprintf(__('CDN active (%s)', 'fp-performance-suite'), $provider);
            }
        }
        
        return $active;
    }

    private function gzipScore(): array
    {
        $enabled = false;
        $hasEvidence = false;

        if (in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            return [10, null];
        }

        if (function_exists('ini_get')) {
            $outputCompression = ini_get('zlib.output_compression');
            if ($outputCompression && (int) $outputCompression === 1) {
                $enabled = true;
                $hasEvidence = true;
            }
            if (!$enabled) {
                $handler = ini_get('output_handler');
                if (is_string($handler) && stripos($handler, 'ob_gzhandler') !== false) {
                    $enabled = true;
                    $hasEvidence = true;
                }
            }
        }

        if (!$enabled && function_exists('headers_list')) {
            foreach (headers_list() as $header) {
                if (stripos($header, 'content-encoding:') === 0) {
                    $enabled = true;
                    $hasEvidence = true;
                    break;
                }
            }
        }

        if (function_exists('apache_get_modules')) {
            $modules = apache_get_modules();
            if (is_array($modules)) {
                $hasEvidence = true;
                if (in_array('mod_deflate', $modules, true) || in_array('mod_brotli', $modules, true)) {
                    $enabled = true;
                }
            }
        }

        $enabled = (bool) apply_filters('fp_ps_gzip_enabled', $enabled);
        $hasEvidence = (bool) apply_filters('fp_ps_gzip_detection_evidence', $hasEvidence);

        if ($enabled || !$hasEvidence) {
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
        $lazyLoadStatus = $this->lazyLoad->status();
        $fontStatus = $this->fontOptimizer->status();
        $imageStatus = $this->imageOptimizer->status();
        
        $points = 0;
        $suggestions = [];
        
        // HTML minification (5 points)
        if (!empty($status['minify_html'])) {
            $points += 5;
        } else {
            $suggestions[] = __('Enable HTML/CSS minification to reduce payload.', 'fp-performance-suite');
        }
        
        // JS deferring (5 points)
        if (!empty($status['defer_js'])) {
            $points += 5;
        } else {
            $suggestions[] = __('Consider deferring non-critical JavaScript.', 'fp-performance-suite');
        }
        
        // Lazy loading (3 points)
        if (!empty($lazyLoadStatus['enabled']) && !empty($lazyLoadStatus['images_enabled'])) {
            $points += 3;
        } else {
            $suggestions[] = __('Enable lazy loading for images to improve initial load time.', 'fp-performance-suite');
        }
        
        // Font optimization (2 points)
        if (!empty($fontStatus['enabled']) && !empty($fontStatus['google_fonts_optimized'])) {
            $points += 2;
        }
        
        // Image optimization (2 points)
        if (!empty($imageStatus['enabled']) && !empty($imageStatus['force_dimensions'])) {
            $points += 2;
        }
        
        // Third-party scripts (3 points)
        $thirdPartyStatus = $this->thirdPartyScripts->status();
        if (!empty($thirdPartyStatus['enabled']) && $thirdPartyStatus['managed_scripts'] > 0) {
            $points += 3;
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
        $hasCritical = is_string($critical) && trim($critical) !== '';
        $required = (bool) apply_filters('fp_ps_require_critical_css', false);

        if ($hasCritical || !$required) {
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
