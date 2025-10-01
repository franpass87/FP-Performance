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

            public function exposeNormalizeAssetSettingsImport(array $incoming, array $defaults): array
            {
                return $this->normalizeAssetSettingsImport($incoming, $defaults);
            }

            public function exposeNormalizePageCacheImport(array $incoming, array $defaults): array
            {
                return $this->normalizePageCacheImport($incoming, $defaults);
            }

            public function exposeNormalizeWebpImport(array $incoming, array $defaults): array
            {
                return $this->normalizeWebpImport($incoming, $defaults);
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
            'enabled' => true,
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
            'enabled' => true,
            'headers' => ['Cache-Control' => 'public, max-age=31536000'],
            'expires_ttl' => 31536000,
            'htaccess' => 'Default rules',
        ], $result);
    }

    public function testNormalizeBrowserCacheImportIgnoresNonNumericTtl(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => false,
            'headers' => ['Cache-Control' => 'public, max-age=1200'],
            'expires_ttl' => 1200,
            'htaccess' => 'Default rules',
        ];

        $result = $tools->exposeNormalizeBrowserCacheImport([
            'expires_ttl' => 'soon',
        ], $defaults);

        $this->assertSame(1200, $result['expires_ttl']);
    }

    public function testNormalizeBrowserCacheImportPreservesEnabledWhenMissing(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'headers' => ['Cache-Control' => 'public, max-age=600'],
            'expires_ttl' => 600,
            'htaccess' => 'Default rules',
        ];

        $result = $tools->exposeNormalizeBrowserCacheImport([
            'headers' => 'public, max-age=1200',
        ], $defaults);

        $this->assertSame([
            'enabled' => true,
            'headers' => ['Cache-Control' => 'public, max-age=1200'],
            'expires_ttl' => 600,
            'htaccess' => 'Default rules',
        ], $result);
    }

    public function testNormalizeBrowserCacheImportCoercesStringBooleans(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'headers' => ['Cache-Control' => 'public, max-age=600'],
            'expires_ttl' => 600,
            'htaccess' => 'Default rules',
        ];

        $result = $tools->exposeNormalizeBrowserCacheImport([
            'enabled' => 'false',
        ], $defaults);

        $this->assertFalse($result['enabled']);
    }

    public function testNormalizeBrowserCacheImportSupportsLegacyCacheControlKey(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'headers' => ['Cache-Control' => 'public, max-age=31536000'],
            'expires_ttl' => 31536000,
            'htaccess' => 'Default rules',
        ];

        $result = $tools->exposeNormalizeBrowserCacheImport([
            'cache_control' => 'private, max-age=0',
        ], $defaults);

        $this->assertSame('private, max-age=0', $result['headers']['Cache-Control']);
    }

    public function testNormalizeBrowserCacheImportHonorsCaseInsensitiveHeaderKey(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'headers' => ['Cache-Control' => 'public, max-age=31536000'],
            'expires_ttl' => 31536000,
            'htaccess' => 'Default rules',
        ];

        $result = $tools->exposeNormalizeBrowserCacheImport([
            'headers' => ['cache-control' => 'private, max-age=0'],
        ], $defaults);

        $this->assertSame('private, max-age=0', $result['headers']['Cache-Control']);
    }

    public function testNormalizeAssetSettingsImportPreservesFlagsWhenMissing(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'minify_html' => true,
            'defer_js' => true,
            'async_js' => false,
            'remove_emojis' => true,
            'dns_prefetch' => ['https://existing.test'],
            'preload' => ['https://existing.test/app.js'],
            'heartbeat_admin' => 60,
            'combine_css' => true,
            'combine_js' => true,
        ];

        $incoming = [
            'dns_prefetch' => ['https://new.test'],
            'combine_js' => false,
            'heartbeat_admin' => '120',
        ];

        $result = $tools->exposeNormalizeAssetSettingsImport($incoming, $defaults);

        $this->assertSame([
            'minify_html' => true,
            'defer_js' => true,
            'async_js' => false,
            'remove_emojis' => true,
            'dns_prefetch' => ['https://new.test'],
            'preload' => ['https://existing.test/app.js'],
            'heartbeat_admin' => 120,
            'combine_css' => true,
            'combine_js' => false,
        ], $result);
    }

    public function testNormalizeAssetSettingsImportHonorsExplicitBooleanFalse(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'minify_html' => true,
            'defer_js' => true,
            'async_js' => true,
            'remove_emojis' => true,
            'dns_prefetch' => [],
            'preload' => [],
            'heartbeat_admin' => 45,
            'combine_css' => true,
            'combine_js' => true,
        ];

        $incoming = [
            'minify_html' => false,
            'async_js' => '0',
            'combine_css' => 0,
        ];

        $result = $tools->exposeNormalizeAssetSettingsImport($incoming, $defaults);

        $this->assertSame([
            'minify_html' => false,
            'defer_js' => true,
            'async_js' => false,
            'remove_emojis' => true,
            'dns_prefetch' => [],
            'preload' => [],
            'heartbeat_admin' => 45,
            'combine_css' => false,
            'combine_js' => true,
        ], $result);
    }

    public function testNormalizeAssetSettingsImportCoercesStringBooleans(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'minify_html' => true,
            'defer_js' => true,
            'async_js' => true,
            'remove_emojis' => true,
            'dns_prefetch' => [],
            'preload' => [],
            'heartbeat_admin' => 60,
            'combine_css' => true,
            'combine_js' => true,
        ];

        $incoming = [
            'minify_html' => 'false',
            'async_js' => 'no',
            'combine_js' => '0',
        ];

        $result = $tools->exposeNormalizeAssetSettingsImport($incoming, $defaults);

        $this->assertFalse($result['minify_html']);
        $this->assertFalse($result['async_js']);
        $this->assertFalse($result['combine_js']);
    }

    public function testNormalizeAssetSettingsImportIgnoresInvalidHeartbeatInterval(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'minify_html' => true,
            'defer_js' => true,
            'async_js' => false,
            'remove_emojis' => true,
            'dns_prefetch' => ['https://existing.test'],
            'preload' => ['https://existing.test/app.js'],
            'heartbeat_admin' => 60,
            'combine_css' => true,
            'combine_js' => true,
        ];

        $incoming = [
            'heartbeat_admin' => 'soon',
        ];

        $result = $tools->exposeNormalizeAssetSettingsImport($incoming, $defaults);

        $this->assertSame(60, $result['heartbeat_admin']);
    }

    public function testNormalizeAssetSettingsImportIgnoresNegativeHeartbeatInterval(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'minify_html' => true,
            'defer_js' => true,
            'async_js' => false,
            'remove_emojis' => true,
            'dns_prefetch' => ['https://existing.test'],
            'preload' => ['https://existing.test/app.js'],
            'heartbeat_admin' => 45,
            'combine_css' => true,
            'combine_js' => true,
        ];

        $incoming = [
            'heartbeat_admin' => -15,
        ];

        $result = $tools->exposeNormalizeAssetSettingsImport($incoming, $defaults);

        $this->assertSame(45, $result['heartbeat_admin']);
    }

    public function testNormalizePageCacheImportPreservesFlagsWhenMissing(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'ttl' => 600,
        ];

        $result = $tools->exposeNormalizePageCacheImport([
            'ttl' => 1200,
        ], $defaults);

        $this->assertSame([
            'enabled' => true,
            'ttl' => 1200,
        ], $result);
    }

    public function testNormalizePageCacheImportFallsBackOnInvalidTtl(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => false,
            'ttl' => 600,
        ];

        $result = $tools->exposeNormalizePageCacheImport([
            'enabled' => true,
            'ttl' => -50,
        ], $defaults);

        $this->assertSame([
            'enabled' => true,
            'ttl' => 600,
        ], $result);
    }

    public function testNormalizePageCacheImportFallsBackOnNonNumericTtl(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'ttl' => 900,
        ];

        $result = $tools->exposeNormalizePageCacheImport([
            'ttl' => 'later',
        ], $defaults);

        $this->assertSame([
            'enabled' => true,
            'ttl' => 900,
        ], $result);
    }

    public function testNormalizeWebpImportPreservesSettingsWhenMissing(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'quality' => 82,
            'keep_original' => true,
            'lossy' => false,
        ];

        $result = $tools->exposeNormalizeWebpImport([
            'quality' => 105,
        ], $defaults);

        $this->assertSame([
            'enabled' => true,
            'quality' => 100,
            'keep_original' => true,
            'lossy' => false,
        ], $result);
    }

    public function testNormalizeWebpImportHonorsExplicitFlags(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => true,
            'quality' => 82,
            'keep_original' => true,
            'lossy' => true,
        ];

        $result = $tools->exposeNormalizeWebpImport([
            'enabled' => false,
            'quality' => 45,
            'keep_original' => false,
            'lossy' => false,
        ], $defaults);

        $this->assertSame([
            'enabled' => false,
            'quality' => 45,
            'keep_original' => false,
            'lossy' => false,
        ], $result);
    }

    public function testNormalizeWebpImportIgnoresNonNumericQuality(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => false,
            'quality' => 82,
            'keep_original' => false,
            'lossy' => false,
        ];

        $result = $tools->exposeNormalizeWebpImport([
            'quality' => 'high',
        ], $defaults);

        $this->assertSame(82, $result['quality']);
    }

    public function testNormalizeWebpImportCoercesStringBooleans(): void
    {
        $tools = $this->makeTools();
        $defaults = [
            'enabled' => false,
            'quality' => 82,
            'keep_original' => false,
            'lossy' => false,
        ];

        $result = $tools->exposeNormalizeWebpImport([
            'enabled' => 'true',
            'keep_original' => 'off',
            'lossy' => 'yes',
        ], $defaults);

        $this->assertTrue($result['enabled']);
        $this->assertFalse($result['keep_original']);
        $this->assertTrue($result['lossy']);
    }
}
