<?php

/**
 * Cache REST Controller
 * 
 * Handles cache-related REST API endpoints
 *
 * @package FP\PerfSuite\Http\Controllers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Controllers;

use FP\PerfSuite\Services\Cache\PageCache;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class CacheController extends BaseController
{
    /** @var PageCache Page cache service */
    private PageCache $pageCache;
    
    /**
     * Constructor
     * 
     * @param mixed $container Service container (Container or ServiceContainer)
     */
    public function __construct($container)
    {
        parent::__construct($container);
        $this->pageCache = $this->getService(PageCache::class);
    }
    
    /**
     * Purge all cache
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function purgeAll(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        try {
            $result = $this->pageCache->purgeAll();
            
            return $this->success([
                'message' => __('Cache purged successfully', 'fp-performance-suite'),
                'items_purged' => $result['count'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to purge cache', $e);
            return $this->error(__('Failed to purge cache', 'fp-performance-suite'), 'purge_failed', 500);
        }
    }
    
    /**
     * Purge cache for specific URL
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function purgeUrl(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        $url = $this->getParam($request, 'url');
        
        if (empty($url)) {
            return $this->error(__('URL is required', 'fp-performance-suite'), 'missing_url', 400);
        }
        
        try {
            $result = $this->pageCache->purgeUrl($url);
            
            return $this->success([
                'message' => __('Cache purged for URL', 'fp-performance-suite'),
                'url' => $url,
                'success' => $result,
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to purge cache for URL', $e);
            return $this->error(__('Failed to purge cache for URL', 'fp-performance-suite'), 'purge_failed', 500);
        }
    }
    
    /**
     * Purge cache for post
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function purgePost(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        $postId = (int) $this->getParam($request, 'post_id');
        
        if ($postId <= 0) {
            return $this->error(__('Valid post ID is required', 'fp-performance-suite'), 'invalid_post_id', 400);
        }
        
        try {
            $result = $this->pageCache->purgePost($postId);
            
            return $this->success([
                'message' => __('Cache purged for post', 'fp-performance-suite'),
                'post_id' => $postId,
                'success' => $result,
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to purge cache for post', $e);
            return $this->error(__('Failed to purge cache for post', 'fp-performance-suite'), 'purge_failed', 500);
        }
    }
    
    /**
     * Purge cache by pattern
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function purgePattern(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        $pattern = $this->getParam($request, 'pattern');
        
        if (empty($pattern)) {
            return $this->error(__('Pattern is required', 'fp-performance-suite'), 'missing_pattern', 400);
        }
        
        try {
            $result = $this->pageCache->purgePattern($pattern);
            
            return $this->success([
                'message' => __('Cache purged for pattern', 'fp-performance-suite'),
                'pattern' => $pattern,
                'items_purged' => $result['count'] ?? 0,
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to purge cache for pattern', $e);
            return $this->error(__('Failed to purge cache for pattern', 'fp-performance-suite'), 'purge_failed', 500);
        }
    }
}
