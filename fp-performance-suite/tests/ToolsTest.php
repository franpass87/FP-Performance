<?php

use FP\PerfSuite\Admin\Pages\Tools;
use FP\PerfSuite\ServiceContainer;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap.php';

final class ToolsTest extends TestCase
{
    private function makeTools(): Tools
    {
        $container = new ServiceContainer();

        return new class($container) extends Tools {
            public function exposeNormalizeBrowserCacheImport(array $incoming, array $defaults): array
            {
                return $this->normalizeBrowserCacheImport($incoming, $defaults);
            }
        };
    }

    public function testNormalizeBrowserCacheImportHandlesStringHeaders(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'headers' => ['Cache-Control' => 'public, max-age=31536000'],
            'expires_ttl' => 31536000,
            'htaccess' => 'Default rules',
        ];

        $result = $tools->exposeNormalizeBrowserCacheImport([
            'enabled' => true,
            'headers' => 'public, max-age=600',
            'expires_ttl' => '600',
            'htaccess' => 'Custom rules',
        ], $defaults);

        $this->assertSame([
            'enabled' => true,
            'headers' => ['Cache-Control' => 'public, max-age=600'],
            'expires_ttl' => 600,
            'htaccess' => 'Custom rules',
        ], $result);
    }

    public function testNormalizeBrowserCacheImportFallsBackForInvalidStructures(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'headers' => ['Cache-Control' => 'public, max-age=31536000'],
            'expires_ttl' => 31536000,
            'htaccess' => 'Default rules',
        ];

        $result = $tools->exposeNormalizeBrowserCacheImport([
            'headers' => ['Cache-Control' => ['unexpected']],
            'expires_ttl' => -5,
            'htaccess' => ['not-a-string'],
        ], $defaults);

        $this->assertSame([
            'enabled' => false,
            'headers' => ['Cache-Control' => 'public, max-age=31536000'],
            'expires_ttl' => 31536000,
            'htaccess' => 'Default rules',
        ], $result);
    }
}
