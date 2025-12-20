<?php

/**
 * Debug REST Controller
 * 
 * Handles debug-related REST API endpoints
 *
 * @package FP\PerfSuite\Http\Controllers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Controllers;

use FP\PerfSuite\Services\Logs\DebugToggler;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class DebugController extends BaseController
{
    /** @var DebugToggler Debug toggler service */
    private DebugToggler $debugToggler;
    
    /**
     * Constructor
     * 
     * @param mixed $container Service container (Container or ServiceContainer)
     */
    public function __construct($container)
    {
        parent::__construct($container);
        $this->debugToggler = $this->getService(DebugToggler::class);
    }
    
    /**
     * Toggle debug mode
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function toggle(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        $enabled = (bool) $this->getParam($request, 'enabled');
        $log = (bool) $this->getParam($request, 'log');
        
        try {
            $result = $this->debugToggler->toggle($enabled, $log);
            
            if (!$result) {
                return $this->error(__('Unable to toggle debug mode', 'fp-performance-suite'), 'toggle_failed', 400);
            }
            
            return $this->success([
                'status' => $this->debugToggler->status(),
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to toggle debug mode', $e);
            return $this->error(__('Failed to toggle debug mode', 'fp-performance-suite'), 'toggle_failed', 500);
        }
    }
}
