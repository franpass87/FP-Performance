<?php

/**
 * Capability Middleware
 * 
 * Middleware for REST API authentication and capability checking
 *
 * @package FP\PerfSuite\Http\Middleware
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Middleware;

use FP\PerfSuite\Utils\Capabilities;
use WP_REST_Request;

class CapabilityMiddleware
{
    /**
     * Handle request - check user capabilities
     * 
     * @param WP_REST_Request $request Request object
     * @return bool|WP_Error True if authorized, WP_Error otherwise
     */
    public function handle(WP_REST_Request $request)
    {
        $requiredCapability = Capabilities::required();
        
        if (!current_user_can($requiredCapability)) {
            return new \WP_Error(
                'rest_forbidden',
                __('You do not have permission to access this resource.', 'fp-performance-suite'),
                ['status' => 403]
            );
        }
        
        return true;
    }
}









