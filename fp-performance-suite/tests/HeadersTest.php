<?php

use FP\PerfSuite\Services\Cache\Headers;
use FP\PerfSuite\Utils\Env;
use FP\PerfSuite\Utils\Htaccess;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class HeadersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['__wp_options'] = [];
    }

    private function makeHeaders(): Headers
    {
        $htaccess = new class extends Htaccess {
            public function __construct()
            {
                // Skip parent initialization for tests.
            }
        };

        return new Headers($htaccess, new Env());
    }

    public function testSettingsHandlesStringHeaderStorage(): void
    {
        update_option('fp_ps_browser_cache', ['enabled' => true, 'headers' => 'public, max-age=600']);

        $headers = $this->makeHeaders();
        $settings = $headers->settings();

        $this->assertSame('public, max-age=600', $settings['headers']['Cache-Control']);
    }

    public function testSettingsHandlesLegacyCacheControlOption(): void
    {
        update_option('fp_ps_browser_cache', ['enabled' => true, 'cache_control' => 'public, max-age=120']);

        $headers = $this->makeHeaders();
        $settings = $headers->settings();

        $this->assertSame('public, max-age=120', $settings['headers']['Cache-Control']);
    }
}
