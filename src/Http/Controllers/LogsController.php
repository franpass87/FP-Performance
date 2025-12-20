<?php

/**
 * Logs REST Controller
 * 
 * Handles log-related REST API endpoints
 *
 * @package FP\PerfSuite\Http\Controllers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Controllers;

use FP\PerfSuite\Services\Logs\RealtimeLog;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class LogsController extends BaseController
{
    /** @var RealtimeLog Realtime log service */
    private RealtimeLog $realtimeLog;
    
    /**
     * Constructor
     * 
     * @param mixed $container Service container (Container or ServiceContainer)
     */
    public function __construct($container)
    {
        parent::__construct($container);
        $this->realtimeLog = $this->getService(RealtimeLog::class);
    }
    
    /**
     * Get log tail
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function tail(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        $lines = (int) $this->getParam($request, 'lines', 200);
        $level = $this->getParam($request, 'level', '');
        $query = $this->getParam($request, 'query', '');
        
        // Validate lines parameter
        if ($lines < 1 || $lines > RealtimeLog::MAX_LINES) {
            $lines = min(max(1, $lines), RealtimeLog::MAX_LINES);
        }
        
        try {
            $logs = $this->realtimeLog->tail($lines, $level, $query);
            
            return $this->success([
                'logs' => $logs,
                'count' => count($logs),
                'lines' => $lines,
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to get log tail', $e);
            return $this->error(__('Failed to retrieve logs', 'fp-performance-suite'), 'logs_failed', 500);
        }
    }
}
