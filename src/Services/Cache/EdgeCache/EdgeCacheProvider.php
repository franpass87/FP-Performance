<?php

namespace FP\PerfSuite\Services\Cache\EdgeCache;

/**
 * Edge Cache Provider Interface
 *
 * @package FP\PerfSuite\Services\Cache\EdgeCache
 * @author Francesco Passeri
 */
interface EdgeCacheProvider
{
    /**
     * Purge entire cache
     *
     * @return bool True if successful
     */
    public function purgeAll(): bool;

    /**
     * Purge specific URLs
     *
     * @param array $urls Array of URLs to purge
     * @return bool True if successful
     */
    public function purgeUrls(array $urls): bool;

    /**
     * Purge by tag/prefix
     *
     * @param array $tags Array of cache tags
     * @return bool True if successful
     */
    public function purgeTags(array $tags): bool;

    /**
     * Test connection to provider
     *
     * @return array{success:bool,message:string,info?:array}
     */
    public function testConnection(): array;

    /**
     * Get cache statistics
     *
     * @return array Cache stats
     */
    public function getStats(): array;

    /**
     * Get provider name
     *
     * @return string Provider name
     */
    public function getName(): string;
}
