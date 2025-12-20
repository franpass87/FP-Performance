<?php

namespace FP\PerfSuite\Services\Intelligence\CDNExclusionSync;

use FP\PerfSuite\Core\Options\OptionsRepositoryInterface;
use function get_option;
use function __;
use function count;

/**
 * Genera report di sincronizzazione CDN
 * 
 * @package FP\PerfSuite\Services\Intelligence\CDNExclusionSync
 * @author Francesco Passeri
 */
class CDNReportGenerator
{
    private CDNValidator $validator;
    private CDNRulesGenerator $rulesGenerator;
    
    /** @var OptionsRepositoryInterface|null Options repository (injected) */
    private ?OptionsRepositoryInterface $optionsRepo = null;

    /**
     * Constructor
     * 
     * @param CDNValidator $validator CDN validator
     * @param CDNRulesGenerator $rulesGenerator Rules generator
     * @param OptionsRepositoryInterface|null $optionsRepo Options repository (optional for backward compatibility)
     */
    public function __construct(CDNValidator $validator, CDNRulesGenerator $rulesGenerator, ?OptionsRepositoryInterface $optionsRepo = null)
    {
        $this->validator = $validator;
        $this->rulesGenerator = $rulesGenerator;
        $this->optionsRepo = $optionsRepo;
    }

    /**
     * Genera report di sincronizzazione CDN
     */
    public function generate(array $exclusions): array
    {
        $validation = $this->validator->validate();
        $rules = $this->rulesGenerator->generate($exclusions);

        return [
            'cdn_status' => $validation,
            'exclusions_count' => count($exclusions),
            'rules_generated' => count($rules['rules']),
            'last_sync' => $this->getOption('fp_ps_cdn_last_sync', 0),
            'sync_frequency' => $this->getRecommendedSyncFrequency($exclusions),
            'recommendations' => $this->generateRecommendations($validation, $exclusions),
        ];
    }

    /**
     * Ottieni frequenza di sincronizzazione raccomandata
     */
    private function getRecommendedSyncFrequency(array $exclusions): string
    {
        $exclusionsCount = count($exclusions);
        
        if ($exclusionsCount > 20) {
            return 'daily';
        } elseif ($exclusionsCount > 10) {
            return 'weekly';
        } else {
            return 'monthly';
        }
    }

    /**
     * Genera raccomandazioni CDN
     */
    private function generateRecommendations(array $validation, array $exclusions): array
    {
        $recommendations = [];

        if ($validation['providers_detected'] === 0) {
            $recommendations[] = [
                'priority' => 'high',
                'type' => 'cdn_setup',
                'message' => __('Configura un CDN per migliorare le performance globali', 'fp-performance-suite'),
            ];
        }

        if ($validation['api_configured'] === 0 && $validation['providers_detected'] > 0) {
            $recommendations[] = [
                'priority' => 'medium',
                'type' => 'api_configuration',
                'message' => __('Configura le API CDN per abilitare la sincronizzazione automatica', 'fp-performance-suite'),
            ];
        }

        if (count($exclusions) > 50) {
            $recommendations[] = [
                'priority' => 'low',
                'type' => 'exclusion_review',
                'message' => __('Rivedi le esclusioni CDN per ottimizzare la cache', 'fp-performance-suite'),
            ];
        }

        return $recommendations;
    }

    /**
     * Get option value (with fallback)
     * 
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    private function getOption(string $key, $default = null)
    {
        if ($this->optionsRepo !== null) {
            return $this->optionsRepo->get($key, $default);
        }
        return get_option($key, $default);
    }
}






