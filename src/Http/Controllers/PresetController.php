<?php

/**
 * Preset REST Controller
 * 
 * Handles preset-related REST API endpoints
 *
 * @package FP\PerfSuite\Http\Controllers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Http\Controllers;

use FP\PerfSuite\Services\Presets\Manager as PresetManager;
use FP\PerfSuite\Utils\Logger;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class PresetController extends BaseController
{
    /** @var PresetManager Preset manager service */
    private PresetManager $presetManager;
    
    /**
     * Constructor
     * 
     * @param mixed $container Service container (Container or ServiceContainer)
     */
    public function __construct($container)
    {
        parent::__construct($container);
        $this->presetManager = $this->getService(PresetManager::class);
    }
    
    /**
     * Apply a preset
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function apply(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        $presetId = $this->getParam($request, 'id');
        
        if (empty($presetId)) {
            return $this->error(__('Preset ID is required', 'fp-performance-suite'), 'missing_preset_id', 400);
        }
        
        try {
            $result = $this->presetManager->apply($presetId);
            
            if ($result['success']) {
                return $this->success([
                    'message' => __('Preset applied successfully', 'fp-performance-suite'),
                    'preset_id' => $presetId,
                    'changes' => $result['changes'] ?? [],
                ]);
            }
            
            return $this->error(
                $result['message'] ?? __('Failed to apply preset', 'fp-performance-suite'),
                'preset_apply_failed',
                400
            );
        } catch (\Throwable $e) {
            Logger::error('Failed to apply preset', $e);
            return $this->error(__('Failed to apply preset', 'fp-performance-suite'), 'preset_apply_failed', 500);
        }
    }
    
    /**
     * Rollback preset
     * 
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response|WP_Error
     */
    public function rollback(WP_REST_Request $request)
    {
        if (!$this->permissionCheck($request)) {
            return $this->error(__('Insufficient permissions', 'fp-performance-suite'), 'permission_denied', 403);
        }
        
        try {
            $result = $this->presetManager->rollback();
            
            if ($result['success']) {
                return $this->success([
                    'message' => __('Preset rolled back successfully', 'fp-performance-suite'),
                ]);
            }
            
            return $this->error(
                $result['message'] ?? __('No preset to rollback', 'fp-performance-suite'),
                'rollback_failed',
                400
            );
        } catch (\Throwable $e) {
            Logger::error('Failed to rollback preset', $e);
            return $this->error(__('Failed to rollback preset', 'fp-performance-suite'), 'rollback_failed', 500);
        }
    }
}
