<?php

/**
 * Deactivation Handler
 * 
 * Handles plugin deactivation logic.
 * Delegates to DeactivationService for actual implementation.
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

use FP\PerfSuite\Core\Bootstrap\DeactivationService;

class DeactivationHandler
{
    /**
     * Handle plugin deactivation
     * 
     * @return void
     */
    public static function handle(): void
    {
        DeactivationService::handle();
    }
}










