<?php

/**
 * Machine Learning Service Provider
 * 
 * Registers ML and pattern learning services
 *
 * @package FP\PerfSuite\Providers
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */

namespace FP\PerfSuite\Providers;

use FP\PerfSuite\Core\Logging\LoggerInterface;
use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Kernel\Container;
use FP\PerfSuite\Kernel\ServiceProviderInterface;

class MLServiceProvider implements ServiceProviderInterface
{
    /**
     * Register ML services
     * 
     * @param Container $container
     */
    public function register(Container $container): void
    {
        // Pattern Learner (with OptionsRepository injection)
        $container->singleton(
            \FP\PerfSuite\Services\ML\PatternLearner::class,
            function(Container $c) {
                $optionsRepo = $c->has(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    ? $c->get(\FP\PerfSuite\Core\Options\OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\ML\PatternLearner($optionsRepo);
            }
        );
        
        // Anomaly Detector
        $container->singleton(
            \FP\PerfSuite\Services\ML\AnomalyDetector::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\ML\AnomalyDetector($optionsRepo);
            }
        );
        
        // ML Predictor
        $container->singleton(
            \FP\PerfSuite\Services\ML\MLPredictor::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                $logger = $c->has(LoggerInterface::class)
                    ? $c->get(LoggerInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\ML\MLPredictor(
                    $c,
                    $c->get(\FP\PerfSuite\Services\ML\PatternLearner::class),
                    $c->get(\FP\PerfSuite\Services\ML\AnomalyDetector::class),
                    $optionsRepo,
                    $logger
                );
            }
        );
        
        // Auto Tuner
        $container->singleton(
            \FP\PerfSuite\Services\ML\AutoTuner::class,
            function(Container $c) {
                $optionsRepo = $c->has(OptionsRepositoryInterface::class)
                    ? $c->get(OptionsRepositoryInterface::class)
                    : null;
                return new \FP\PerfSuite\Services\ML\AutoTuner(
                    $c,
                    $c->get(\FP\PerfSuite\Services\ML\MLPredictor::class),
                    $c->get(\FP\PerfSuite\Services\ML\PatternLearner::class),
                    $optionsRepo
                );
            }
        );
    }
    
    /**
     * Boot ML services
     * 
     * @param Container $container
     */
    public function boot(Container $container): void
    {
        // ML services will be initialized based on enabled state
    }
    
    /**
     * Get provider priority
     * 
     * @return int
     */
    public function priority(): int
    {
        return 30; // As per plan: MLServiceProvider priority 30
    }
    
    /**
     * Check if provider should load
     * 
     * @return bool
     */
    public function shouldLoad(): bool
    {
        return true;
    }
}
