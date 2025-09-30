<?php

namespace FP\PerfSuite\Services\Presets;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Services\Media\WebPConverter;
use function __;

class Manager
{
    private const OPTION = 'fp_ps_preset';

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
     * @return array<string, array<string, mixed>>
     */
    public function presets(): array
    {
        return [
            'generale' => [
                'label' => __('Generale', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 3600],
                    'browser_cache' => ['enabled' => true],
                    'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false],
                    'webp' => ['enabled' => true, 'quality' => 75, 'lossy' => true],
                    'db' => ['batch' => 200],
                    'heartbeat' => 60,
                ],
            ],
            'ionos' => [
                'label' => __('IONOS', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 1800],
                    'browser_cache' => ['enabled' => false],
                    'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false, 'async_js' => false],
                    'webp' => ['enabled' => true, 'quality' => 80],
                    'db' => ['batch' => 150],
                    'heartbeat' => 80,
                ],
            ],
            'aruba' => [
                'label' => __('Aruba', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 900],
                    'browser_cache' => ['enabled' => true],
                    'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false, 'preload' => []],
                    'webp' => ['enabled' => true, 'quality' => 70],
                    'db' => ['batch' => 100],
                    'heartbeat' => 90,
                ],
            ],
        ];
    }

    public function apply(string $id): array
    {
        $preset = $this->presets()[$id] ?? null;
        if (!$preset) {
            return ['error' => __('Preset not found', 'fp-performance-suite')];
        }

        $config = $preset['config'];
        $previous = [
            'page_cache' => $this->pageCache->settings(),
            'browser_cache' => $this->headers->settings(),
            'assets' => $this->optimizer->settings(),
            'webp' => $this->webp->settings(),
            'db' => $this->cleaner->settings(),
        ];
        $this->pageCache->update($config['page_cache']);
        $this->headers->update(array_merge($this->headers->settings(), ['enabled' => !empty($config['browser_cache']['enabled'])]));
        $this->optimizer->update(array_merge($this->optimizer->settings(), $config['assets']));
        $this->webp->update(array_merge($this->webp->settings(), $config['webp']));
        $this->cleaner->update(array_merge($this->cleaner->settings(), $config['db']));
        $this->optimizer->update(array_merge($this->optimizer->settings(), ['heartbeat_admin' => $config['heartbeat']]));

        update_option(self::OPTION, [
            'active' => $id,
            'applied_at' => time(),
            'previous' => $previous,
        ]);
        return ['success' => true];
    }

    public function rollback(): bool
    {
        $option = get_option(self::OPTION, []);
        if (empty($option['previous'])) {
            return false;
        }
        $prev = $option['previous'];
        $this->pageCache->update($prev['page_cache']);
        $this->headers->update($prev['browser_cache']);
        $this->optimizer->update($prev['assets']);
        $this->webp->update($prev['webp']);
        $this->cleaner->update($prev['db']);
        update_option(self::OPTION, ['active' => null, 'applied_at' => time(), 'previous' => []]);
        return true;
    }

    public function getActivePreset(): ?string
    {
        $option = get_option(self::OPTION, []);
        return $option['active'] ?? null;
    }

    public function labelFor(string $id): string
    {
        $presets = $this->presets();
        return $presets[$id]['label'] ?? ucfirst($id);
    }
}
