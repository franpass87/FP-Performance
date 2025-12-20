<?php

/**
 * Score REST Controller
 * 
 * Handles performance score REST API endpoints
 *
 * @package FP\PerfSuite\Http\Controllers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Controllers;

use FP\PerfSuite\Services\Score\Scorer;
use FP\PerfSuite\Utils\Logger;
use WP_REST_Request;
use WP_REST_Response;

class ScoreController extends BaseController
{
    /** @var Scorer Score service */
    private Scorer $scorer;
    
    /**
     * Constructor
     * 
     * @param mixed $container Service container (Container or ServiceContainer)
     */
    public function __construct($container)
    {
        parent::__construct($container);
        $this->scorer = $this->getService(Scorer::class);
    }
    
    /**
     * Get performance score
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function getScore(WP_REST_Request $request): WP_REST_Response
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        try {
            $score = $this->scorer->calculate();
            return $this->success($score);
        } catch (\Throwable $e) {
            Logger::error('Failed to calculate score', $e);
            return $this->error(__('Failed to calculate score', 'fp-performance-suite'), 'score_failed', 500);
        }
    }
}
