<?php

namespace FP\PerfSuite\Services\ML;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use FP\PerfSuite\Services\ML\PatternLearner\StatisticsCalculator;
use FP\PerfSuite\Services\ML\PatternLearner\PatternStorage;
use FP\PerfSuite\Services\ML\PatternLearner\LoadPatternLearner;
use FP\PerfSuite\Services\ML\PatternLearner\MemoryPatternLearner;
use FP\PerfSuite\Services\ML\PatternLearner\ErrorPatternLearner;
use FP\PerfSuite\Services\ML\PatternLearner\TemporalPatternLearner;
use FP\PerfSuite\Services\ML\PatternLearner\DevicePatternLearner;
use FP\PerfSuite\Utils\Logger;

/**
 * Pattern Learner Service
 * 
 * Apprende pattern dai dati di performance per migliorare predizioni
 * 
 * @author Francesco Passeri
 * @link https://francescopasseri.com
 */
class PatternLearner
{
    private StatisticsCalculator $calculator;
    private PatternStorage $storage;
    private LoadPatternLearner $loadLearner;
    private MemoryPatternLearner $memoryLearner;
    private ErrorPatternLearner $errorLearner;
    private TemporalPatternLearner $temporalLearner;
    private DevicePatternLearner $deviceLearner;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Constructor
     * 
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->optionsRepo = $optionsRepo;
        $this->calculator = new StatisticsCalculator();
        $this->storage = new PatternStorage($optionsRepo);
        $this->loadLearner = new LoadPatternLearner($this->calculator);
        $this->memoryLearner = new MemoryPatternLearner($this->calculator);
        $this->errorLearner = new ErrorPatternLearner($this->calculator);
        $this->temporalLearner = new TemporalPatternLearner($this->calculator);
        $this->deviceLearner = new DevicePatternLearner($this->calculator);
    }

    /**
     * Apprende pattern dai dati di performance
     */
    public function learnPatterns(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $patterns = [];
        
        // Pattern di carico
        $patterns = array_merge($patterns, $this->loadLearner->learn($data));
        
        // Pattern di memoria
        $patterns = array_merge($patterns, $this->memoryLearner->learn($data));
        
        // Pattern di errori
        $patterns = array_merge($patterns, $this->errorLearner->learn($data));
        
        // Pattern temporali
        $patterns = array_merge($patterns, $this->temporalLearner->learn($data));
        
        // Pattern per dispositivo
        $patterns = array_merge($patterns, $this->deviceLearner->learn($data));
        
        // Salva pattern appresi
        $this->storage->save($patterns);
        
        Logger::info('Patterns learned', ['patterns_count' => count($patterns)]);
        
        return $patterns;
    }

    // Metodi rimossi - ora gestiti dalle classi PatternLearner
    // learnLoadPatterns() -> LoadPatternLearner::learn()
    // learnMemoryPatterns() -> MemoryPatternLearner::learn()
    // learnErrorPatterns() -> ErrorPatternLearner::learn()
    // learnTemporalPatterns() -> TemporalPatternLearner::learn()
    // learnDevicePatterns() -> DevicePatternLearner::learn()
    // findHighLoadTimes() -> LoadPatternLearner::findHighLoadTimes()
    // findPluginLoadCorrelation() -> LoadPatternLearner::findPluginLoadCorrelation()
    // detectMemoryGrowth() -> MemoryPatternLearner::detectMemoryGrowth()
    // detectMemorySpikes() -> MemoryPatternLearner::detectMemorySpikes()
    // findRecurringErrors() -> ErrorPatternLearner::findRecurringErrors()
    // findErrorLoadCorrelation() -> ErrorPatternLearner::findErrorLoadCorrelation()
    // analyzeDailyPatterns() -> TemporalPatternLearner::analyzeDailyPatterns()
    // analyzeHourlyPatterns() -> TemporalPatternLearner::analyzeHourlyPatterns()
    // analyzeDeviceDifferences() -> DevicePatternLearner::analyzeDeviceDifferences()
    // calculateLinearTrend() -> StatisticsCalculator::calculateLinearTrend()
    // calculateStandardDeviation() -> StatisticsCalculator::calculateStandardDeviation()
    // calculateCorrelation() -> StatisticsCalculator::calculateCorrelation()
    // calculateConfidence() -> StatisticsCalculator::calculateConfidence()
    // saveLearnedPatterns() -> PatternStorage::save()

    /**
     * Ottiene le impostazioni
     */
    public function getSettings(): array
    {
        $defaults = [
            'enabled' => false,
            'min_data_points' => 10,
            'confidence_threshold' => 0.7,
            'pattern_retention_days' => 30
        ];
        
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get('fp_ps_pattern_learner', $defaults);
        }
        
        // Fallback to direct option call for backward compatibility
        return get_option('fp_ps_pattern_learner', $defaults);
    }
    
    /**
     * Alias di getSettings() per compatibilità
     */
    public function settings(): array
    {
        return $this->getSettings();
    }
    
    /**
     * Registra il servizio
     */
    public function register(): void
    {
        // PatternLearner non ha hook specifici da registrare
        // È utilizzato principalmente per apprendimento on-demand
    }
}