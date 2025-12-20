<?php

/**
 * Base REST Controller
 * 
 * Base class for all REST API controllers with common functionality
 *
 * @package FP\PerfSuite\Http\Controllers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Controllers;

use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\ServiceContainer;
use FP\PerfSuite\Utils\Capabilities;
use FP\PerfSuite\Utils\Logger;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

abstract class BaseController
{
    /** @var Container|ServiceContainer Service container */
    protected $container;
    
    /**
     * Constructor
     * 
     * @param Container|ServiceContainer $container Service container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }
    
    /**
     * Get service from container (compatible with both Container types)
     * 
     * @param string $id Service identifier
     * @return mixed
     */
    protected function getService(string $id)
    {
        if ($this->container instanceof Container) {
            return $this->container->get($id);
        }
        return $this->container->get($id);
    }
    
    /**
     * Check permissions
     * 
     * @param WP_REST_Request $request Request object
     * @return bool True if user has permission
     */
    protected function permissionCheck(WP_REST_Request $request): bool
    {
        $requiredCapability = Capabilities::required();
        if (!current_user_can($requiredCapability)) {
            Logger::warning('REST API permission denied: insufficient capabilities');
            return false;
        }
        
        return true;
    }
    
    /**
     * Send success response
     * 
     * @param mixed $data Response data
     * @param int $status HTTP status code
     * @return WP_REST_Response
     */
    protected function success($data = null, int $status = 200): WP_REST_Response
    {
        $response = [
            'success' => true,
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return new WP_REST_Response($response, $status);
    }
    
    /**
     * Send error response
     * 
     * @param string $message Error message
     * @param string $code Error code
     * @param int $status HTTP status code
     * @return WP_Error
     */
    protected function error(string $message, string $code = 'error', int $status = 400): WP_Error
    {
        return new WP_Error($code, $message, ['status' => $status]);
    }
    
    /**
     * Get request parameter
     * 
     * @param WP_REST_Request $request Request object
     * @param string $key Parameter key
     * @param mixed $default Default value
     * @return mixed Parameter value
     */
    protected function getParam(WP_REST_Request $request, string $key, $default = null)
    {
        return $request->get_param($key) ?? $default;
    }
}
