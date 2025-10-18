<?php

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
use FP\PerfSuite\Services\Score\Scorer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class ScorerTest extends TestCase
{
    private function createScorerMocks(array $config = []): Scorer
    {
        $pageCache = $this->createConfiguredMock(
            PageCache::class, 
            ['isEnabled' => $config['page_cache'] ?? true, 'status' => ['enabled' => $config['page_cache'] ?? true]]
        );
        
        $headers = $this->createConfiguredMock(
            Headers::class, 
            ['status' => ['enabled' => $config['headers'] ?? true, 'headers' => []]]
        );
        
        $optimizer = $this->createConfiguredMock(
            Optimizer::class, 
            ['status' => [
                'minify_html' => $config['minify_html'] ?? true, 
                'defer_js' => $config['defer_js'] ?? true, 
                'async_js' => false, 
                'remove_emojis' => $config['remove_emojis'] ?? true, 
                'heartbeat_admin' => $config['heartbeat'] ?? 60
            ]]
        );
        
        $webp = $this->createConfiguredMock(
            WebPConverter::class, 
            ['status' => ['enabled' => $config['webp'] ?? true, 'coverage' => $config['webp_coverage'] ?? 95.0]]
        );
        
        $cleaner = $this->createConfiguredMock(
            Cleaner::class, 
            ['status' => ['overhead_mb' => $config['overhead'] ?? 2.0]]
        );
        
        $debug = $this->createConfiguredMock(
            DebugToggler::class, 
            ['status' => ['log_file' => $config['log_file'] ?? '']]
        );
        
        $lazyLoad = $this->createConfiguredMock(
            LazyLoadManager::class, 
            ['status' => ['enabled' => $config['lazy_load'] ?? true, 'images_enabled' => true, 'iframes_enabled' => false]]
        );
        
        $fontOptimizer = $this->createConfiguredMock(
            FontOptimizer::class, 
            ['status' => ['enabled' => $config['fonts'] ?? true, 'google_fonts_optimized' => true, 'preload_enabled' => false]]
        );
        
        $imageOptimizer = $this->createConfiguredMock(
            ImageOptimizer::class, 
            ['status' => ['enabled' => $config['images'] ?? true, 'force_dimensions' => true, 'aspect_ratio' => false]]
        );
        
        $thirdParty = $this->createConfiguredMock(
            ThirdPartyScriptManager::class, 
            ['status' => ['enabled' => $config['third_party'] ?? true, 'managed_scripts' => 2]]
        );

        return new Scorer(
            $pageCache, 
            $headers, 
            $optimizer, 
            $webp, 
            $cleaner, 
            $debug,
            $lazyLoad,
            $fontOptimizer,
            $imageOptimizer,
            $thirdParty
        );
    }

    public function testScoreWithAllOptimizations(): void
    {
        $scorer = $this->createScorerMocks();
        $result = $scorer->calculate();
        
        $this->assertSame(100, $result['total']);
        $this->assertArrayHasKey('breakdown', $result);
        $this->assertArrayHasKey('breakdown_detailed', $result);
        $this->assertArrayHasKey('suggestions', $result);
        
        // Verifica che breakdown_detailed abbia la struttura corretta
        foreach ($result['breakdown_detailed'] as $label => $details) {
            $this->assertArrayHasKey('current', $details);
            $this->assertArrayHasKey('max', $details);
            $this->assertArrayHasKey('percentage', $details);
            $this->assertArrayHasKey('status', $details);
            $this->assertArrayHasKey('suggestion', $details);
            $this->assertSame('complete', $details['status']);
        }
    }

    public function testScoreSuggestsWhenDisabled(): void
    {
        $scorer = $this->createScorerMocks([
            'page_cache' => false,
            'headers' => false,
            'minify_html' => false,
            'defer_js' => false,
            'remove_emojis' => false,
            'heartbeat' => 30,
            'webp' => false,
            'webp_coverage' => 10.0,
            'overhead' => 30.0,
            'log_file' => __FILE__,
            'lazy_load' => false,
            'fonts' => false,
            'images' => false,
            'third_party' => false
        ]);
        
        $result = $scorer->calculate();
        
        $this->assertLessThan(60, $result['total']);
        $this->assertNotEmpty($result['suggestions']);
        
        // Verifica che ci siano suggerimenti nel breakdown_detailed
        $hasSuggestions = false;
        foreach ($result['breakdown_detailed'] as $label => $details) {
            if ($details['suggestion'] !== null) {
                $hasSuggestions = true;
                $this->assertIsString($details['suggestion']);
                $this->assertNotEmpty($details['suggestion']);
            }
        }
        $this->assertTrue($hasSuggestions, 'Dovrebbero esserci suggerimenti quando le ottimizzazioni sono disabilitate');
    }

    public function testBreakdownDetailedStructure(): void
    {
        $scorer = $this->createScorerMocks();
        $result = $scorer->calculate();
        
        // Verifica che tutte le categorie siano presenti
        $expectedCategories = [
            'GZIP/Brotli',
            'Browser cache headers',
            'Page cache',
            'Asset optimization',
            'WebP coverage',
            'Database health',
            'Heartbeat throttling',
            'Emoji & embeds',
            'Critical CSS',
            'Logs hygiene'
        ];
        
        foreach ($expectedCategories as $category) {
            $this->assertArrayHasKey($category, $result['breakdown_detailed']);
        }
    }
}
