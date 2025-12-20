<?php

namespace FP\PerfSuite\Services\Intelligence\CDNExclusionSync;

use function __;
use function sprintf;
use function count;

/**
 * Valida configurazione CDN
 * 
 * @package FP\PerfSuite\Services\Intelligence\CDNExclusionSync
 * @author Francesco Passeri
 */
class CDNValidator
{
    private CDNProviderDetector $detector;

    public function __construct(CDNProviderDetector $detector)
    {
        $this->detector = $detector;
    }

    /**
     * Valida configurazione CDN
     */
    public function validate(): array
    {
        $validation = [
            'providers_detected' => 0,
            'api_configured' => 0,
            'issues' => [],
            'recommendations' => [],
        ];

        $providers = $this->detector->detectActiveProviders();
        $validation['providers_detected'] = count($providers);

        foreach ($providers as $provider) {
            if ($provider['api_available']) {
                $validation['api_configured']++;
            } else {
                $validation['issues'][] = sprintf(
                    __('API non configurata per %s', 'fp-performance-suite'),
                    $provider['name']
                );
            }
        }

        if ($validation['providers_detected'] === 0) {
            $validation['recommendations'][] = __('Nessun CDN rilevato. Considera l\'utilizzo di un CDN per migliorare le performance', 'fp-performance-suite');
        }

        if ($validation['api_configured'] === 0 && $validation['providers_detected'] > 0) {
            $validation['recommendations'][] = __('Configura le API CDN per abilitare la sincronizzazione automatica delle esclusioni', 'fp-performance-suite');
        }

        return $validation;
    }
}















