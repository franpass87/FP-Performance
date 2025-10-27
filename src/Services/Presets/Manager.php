<?php

namespace FP\PerfSuite\Services\Presets;

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Utils\Logger;

use function __;

class Manager
{
    private const OPTION = 'fp_ps_preset';

    private PageCache $pageCache;
    private Headers $headers;
    private Optimizer $optimizer;
    private Cleaner $cleaner;
    private DebugToggler $debugToggler;

    /**
     * Registra gli hook del servizio
     */
    public function register(): void
    {
        // PresetManager non ha hook specifici da registrare
        // È utilizzato principalmente per gestione preset on-demand
    }

    public function __construct(PageCache $pageCache, Headers $headers, Optimizer $optimizer, Cleaner $cleaner, DebugToggler $debugToggler)
    {
        $this->pageCache = $pageCache;
        $this->headers = $headers;
        $this->optimizer = $optimizer;
        $this->cleaner = $cleaner;
        $this->debugToggler = $debugToggler;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function presets(): array
    {
        return [
            'shared-hosting' => [
                'label' => __('Shared Hosting (Sicuro)', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 1800],
                    'browser_cache' => ['enabled' => true],
                    'assets' => [
                        'minify_html' => true,
                        'minify_css' => true,
                        'minify_js' => false, // Evitare su shared
                        'defer_js' => true,
                        'combine_css' => false, // Troppo pesante
                        'combine_js' => false,  // Troppo pesante
                        'async_js' => false,
                        'lazy_load_images' => true,
                        'preload' => [],
                    ],
                    'db' => [
                        'batch' => 50, // Batch piccoli per evitare timeout
                        'enabled' => true,
                        'cleanup_revisions' => true,
                        'cleanup_drafts' => false,
                        'cleanup_spam' => true,
                        'cleanup_trash' => true,
                    ],
                    'heartbeat' => 90, // Riduce carico server
                    'disabled_services' => [
                        'HtaccessSecurity', // Permessi spesso insufficienti
                        'ObjectCacheManager', // Raramente disponibile
                        'MLPredictor', // Troppo pesante
                        'AutoTuner', // Troppo pesante
                        'CriticalCssAutomation', // Troppo pesante
                        'ImageOptimizer', // Può causare timeout
                        'JavaScriptTreeShaker', // CPU intensive
                    ],
                ],
            ],
            'generale' => [
                'label' => __('Generale', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 3600],
                    'browser_cache' => ['enabled' => true],
                    'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false],
                    'db' => ['batch' => 200],
                    'heartbeat' => 60,
                ],
            ],
            'balanced' => [
                'label' => __('Bilanciato (Raccomandato)', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 3600],
                    'browser_cache' => ['enabled' => true],
                    'assets' => [
                        'minify_html' => true,
                        'minify_css' => true,
                        'minify_js' => true,
                        'defer_js' => true,
                        'combine_css' => false,
                        'combine_js' => false,
                        'lazy_load_images' => true,
                    ],
                    'db' => ['batch' => 200],
                    'heartbeat' => 60,
                ],
            ],
            'aggressive' => [
                'label' => __('Aggressivo (VPS/Dedicato)', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 7200],
                    'browser_cache' => ['enabled' => true],
                    'assets' => [
                        'minify_html' => true,
                        'minify_css' => true,
                        'minify_js' => true,
                        'defer_js' => true,
                        'combine_css' => true,
                        'combine_js' => true,
                        'lazy_load_images' => true,
                        'critical_css' => true,
                    ],
                    'db' => ['batch' => 500],
                    'heartbeat' => 30,
                ],
            ],
            'ionos' => [
                'label' => __('IONOS', 'fp-performance-suite'),
                'config' => [
                    'page_cache' => ['enabled' => true, 'ttl' => 1800],
                    'browser_cache' => ['enabled' => false],
                    'assets' => ['minify_html' => true, 'defer_js' => true, 'combine_css' => false, 'combine_js' => false, 'async_js' => false],
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
                    'db' => ['batch' => 100],
                    'heartbeat' => 90,
                ],
            ],
        ];
    }

    public function apply(string $id): array
    {
        Logger::info('Attempting to apply preset', ['preset_id' => $id]);
        
        $preset = $this->presets()[$id] ?? null;
        if (!$preset) {
            Logger::error('Preset not found', ['preset_id' => $id]);
            return ['error' => __('Preset not found', 'fp-performance-suite')];
        }

        try {
            $config = $preset['config'];
            Logger::debug('Preset config loaded', ['config' => $config]);
            
            // Save current settings for potential rollback
            Logger::debug('Retrieving current settings for rollback');
            try {
                $previous = [
                    'page_cache' => $this->pageCache->settings(),
                    'browser_cache' => $this->headers->settings(),
                    'assets' => $this->optimizer->settings(),
                    'db' => $this->cleaner->settings(),
                ];
                Logger::debug('Current settings retrieved successfully');
            } catch (\Throwable $e) {
                Logger::error('Failed to retrieve current settings', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                throw new \RuntimeException('Failed to retrieve current settings: ' . $e->getMessage(), 0, $e);
            }
            
            // Apply preset settings
            Logger::debug('Applying page cache settings');
            try {
                $this->pageCache->update($config['page_cache'] ?? []);
                Logger::debug('Page cache settings applied');
            } catch (\Throwable $e) {
                Logger::error('Failed to update page cache', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                throw new \RuntimeException('Failed to update page cache: ' . $e->getMessage(), 0, $e);
            }
            
            if (isset($config['browser_cache'])) {
                Logger::debug('Applying browser cache settings');
                try {
                    $this->headers->update(array_merge($this->headers->settings(), ['enabled' => !empty($config['browser_cache']['enabled'])]));
                    Logger::debug('Browser cache settings applied');
                } catch (\Throwable $e) {
                    Logger::error('Failed to update browser cache', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    throw new \RuntimeException('Failed to update browser cache: ' . $e->getMessage(), 0, $e);
                }
            }
            
            // Merge assets and heartbeat settings into a single update call
            Logger::debug('Applying asset settings');
            try {
                $assetSettings = array_merge($this->optimizer->settings(), $config['assets'] ?? []);
                if (isset($config['heartbeat'])) {
                    $assetSettings['heartbeat_admin'] = $config['heartbeat'];
                }
                $this->optimizer->update($assetSettings);
                Logger::debug('Asset settings applied');
            } catch (\Throwable $e) {
                Logger::error('Failed to update asset settings', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                throw new \RuntimeException('Failed to update asset settings: ' . $e->getMessage(), 0, $e);
            }
            
            
            if (isset($config['db'])) {
                Logger::debug('Applying database settings');
                try {
                    $this->cleaner->update(array_merge($this->cleaner->settings(), $config['db']));
                    Logger::debug('Database settings applied');
                } catch (\Throwable $e) {
                    Logger::error('Failed to update database settings', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                    throw new \RuntimeException('Failed to update database settings: ' . $e->getMessage(), 0, $e);
                }
            }

            Logger::debug('Saving preset metadata');
            try {
                update_option(self::OPTION, [
                    'active' => $id,
                    'applied_at' => time(),
                    'previous' => $previous,
                ]);
                Logger::debug('Preset metadata saved');
            } catch (\Throwable $e) {
                Logger::error('Failed to save preset metadata', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                throw new \RuntimeException('Failed to save preset metadata: ' . $e->getMessage(), 0, $e);
            }
            
            Logger::info('Preset applied successfully', ['preset_id' => $id]);
            return ['success' => true];
        } catch (\Throwable $e) {
            Logger::error('Failed to apply preset', ['preset_id' => $id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return ['error' => sprintf(__('Failed to apply preset: %s', 'fp-performance-suite'), $e->getMessage())];
        }
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
