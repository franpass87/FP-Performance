<?php

/**
 * Activation Handler
 * 
 * Handles plugin activation logic.
 * Delegates to ActivationService for actual implementation.
 *
 * @package FP\PerfSuite\Kernel
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Kernel;

use FP\PerfSuite\Core\Bootstrap\ActivationService;

class ActivationHandler
{
    /**
     * Handle plugin activation
     * 
     * @return void
     */
    public static function handle(): void
    {
        ActivationService::handle();
    }
}










