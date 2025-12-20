<?php

/**
 * Database REST Controller
 * 
 * Handles database-related REST API endpoints
 *
 * @package FP\PerfSuite\Http\Controllers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Controllers;

use FP\PerfSuite\Services\DB\Cleaner;
use FP\PerfSuite\Utils\Logger;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class DatabaseController extends BaseController
{
    /** @var Cleaner Database cleaner service */
    private Cleaner $cleaner;
    
    /**
     * Constructor
     * 
     * @param mixed $container Service container (Container or ServiceContainer)
     */
    public function __construct($container)
    {
        parent::__construct($container);
        $this->cleaner = $this->getService(Cleaner::class);
    }
    
    /**
     * Run database cleanup
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function cleanup(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        $scope = $this->sanitizeCleanupScope((array) $this->getParam($request, 'scope', []));
        $dryRun = filter_var(
            $this->getParam($request, 'dryRun', true),
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
        $dryRun = $dryRun === null ? true : $dryRun;
        $batch = $this->getParam($request, 'batch');
        $batch = $batch ? (int) $batch : null;
        
        if (empty($scope)) {
            return $this->error(__('Scope is required', 'fp-performance-suite'), 'missing_scope', 400);
        }
        
        Logger::info('DB cleanup requested via REST API', [
            'scope' => $scope,
            'dryRun' => $dryRun,
            'batch' => $batch,
        ]);
        
        try {
            $result = $this->cleaner->cleanup($scope, $dryRun, $batch);
            
            if (isset($result['error'])) {
                return $this->error($result['error'], 'cleanup_failed', 429);
            }
            
            return $this->success($result);
        } catch (\Throwable $e) {
            Logger::error('Database cleanup failed', $e);
            return $this->error(__('Database cleanup failed', 'fp-performance-suite'), 'cleanup_failed', 500);
        }
    }
    
    /**
     * Sanitize cleanup scope
     * 
     * @param array $scope Scope array
     * @return array Sanitized scope
     */
    private function sanitizeCleanupScope(array $scope): array
    {
        $allowed = [
            'revisions',
            'auto_drafts',
            'trash_posts',
            'spam_comments',
            'expired_transients',
            'orphan_postmeta',
            'orphan_termmeta',
            'orphan_usermeta',
            'optimize_tables',
        ];
        
        return array_intersect($scope, $allowed);
    }
}
