<?php

use FP\PerfSuite\Services\Assets\Optimizer;
use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Services\Cache\PageCache;
use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Services\Logs\DebugToggler;
use FP\PerfSuite\Services\Media\WebPConverter;
use FP\PerfSuite\Services\Score\Scorer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class ScorerTest extends TestCase
{
    public function testScoreWithAllOptimizations(): void
    {
        $pageCache = $this->createConfiguredMock(PageCache::class, ['isEnabled' => true, 'status' => ['enabled' => true]]);
        $headers = $this->createConfiguredMock(Headers::class, ['status' => ['enabled' => true, 'headers' => []]]);
        $optimizer = $this->createConfiguredMock(Optimizer::class, ['status' => ['minify_html' => true, 'defer_js' => true, 'async_js' => false, 'remove_emojis' => true, 'heartbeat_admin' => 60]]);
        $webp = $this->createConfiguredMock(WebPConverter::class, ['status' => ['enabled' => true, 'coverage' => 95.0]]);
        $cleaner = $this->createConfiguredMock(Cleaner::class, ['status' => ['overhead_mb' => 2.0]]);
        $debug = $this->createConfiguredMock(DebugToggler::class, ['status' => ['log_file' => '']]);

        $scorer = new Scorer($pageCache, $headers, $optimizer, $webp, $cleaner, $debug);
        $result = $scorer->calculate();
        $this->assertSame(100, $result['total']);
    }

    public function testScoreSuggestsWhenDisabled(): void
    {
        $pageCache = $this->createConfiguredMock(PageCache::class, ['isEnabled' => false]);
        $headers = $this->createConfiguredMock(Headers::class, ['status' => ['enabled' => false, 'headers' => []]]);
        $optimizer = $this->createConfiguredMock(Optimizer::class, ['status' => ['minify_html' => false, 'defer_js' => false, 'async_js' => false, 'remove_emojis' => false, 'heartbeat_admin' => 30]]);
        $webp = $this->createConfiguredMock(WebPConverter::class, ['status' => ['enabled' => false, 'coverage' => 10.0]]);
        $cleaner = $this->createConfiguredMock(Cleaner::class, ['status' => ['overhead_mb' => 30.0]]);
        $debug = $this->createConfiguredMock(DebugToggler::class, ['status' => ['log_file' => __FILE__]]);

        $scorer = new Scorer($pageCache, $headers, $optimizer, $webp, $cleaner, $debug);
        $result = $scorer->calculate();
        $this->assertLessThan(60, $result['total']);
        $this->assertNotEmpty($result['suggestions']);
    }
}
