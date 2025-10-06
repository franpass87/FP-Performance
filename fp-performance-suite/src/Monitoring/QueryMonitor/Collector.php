<?php

namespace FP\PerfSuite\Monitoring\QueryMonitor;

use FP\PerfSuite\Plugin;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Media\WebPConverter;

/**
 * Query Monitor Collector for FP Performance Suite
 */
class Collector extends \QM_Collector
{
    public $id = 'fp_performance';

    public function name(): string
    {
        return __('FP Performance', 'fp-performance-suite');
    }

    public function process(): void
    {
        try {
            $container = Plugin::container();
            
            // Cache metrics
            $pageCache = $container->get(PageCache::class);
            $cacheStatus = $pageCache->status();
            
            $this->data['cache'] = [
                'enabled' => $cacheStatus['enabled'],
                'files' => $cacheStatus['files'],
                'hit' => isset($_SERVER['HTTP_X_FP_PAGE_CACHE']) && $_SERVER['HTTP_X_FP_PAGE_CACHE'] === 'HIT',
            ];
            
            // Asset optimization metrics
            $optimizer = $container->get(Optimizer::class);
            $assetStatus = $optimizer->status();
            
            $this->data['assets'] = [
                'minify_html' => $assetStatus['minify_html'],
                'defer_js' => $assetStatus['defer_js'],
                'combine_css' => $assetStatus['combine_css'],
                'combine_js' => $assetStatus['combine_js'],
            ];
            
            // WebP metrics
            $webp = $container->get(WebPConverter::class);
            $webpStatus = $webp->status();
            
            $this->data['webp'] = [
                'enabled' => $webpStatus['enabled'],
                'coverage' => round($webpStatus['coverage'], 1),
            ];
            
            // Performance metrics
            $this->data['performance'] = [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'memory_limit' => $this->getMemoryLimit(),
            ];
            
            // Custom tracked metrics
            $this->data['custom_metrics'] = \FP\PerfSuite\Monitoring\QueryMonitor::getMetrics();
            
        } catch (\Throwable $e) {
            $this->data['error'] = $e->getMessage();
        }
    }

    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $unit = strtolower(substr($limit, -1));
        $value = (int)$limit;
        
        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }
        
        return $value;
    }
}
